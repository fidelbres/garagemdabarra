<?php

namespace App\Http\Controllers;

use App\Models\AnexoVeiculo;
use App\Models\FotoVeiculo;
use App\Models\MarcaVeiculo;
use App\Models\ModeloVeiculo;
use App\Models\Proprietario;
use App\Models\Quilometragem;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class VeiculoController extends Controller
{
    public function midia(string $arquivo)
    {
        abort_if(str_contains($arquivo, '..') || ! Storage::disk('public')->exists($arquivo), 404);

        return response()->file(Storage::disk('public')->path($arquivo), [
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }

    public function index(Request $request)
    {
        $busca = $request->string('busca');
        $veiculos = Veiculo::with(['proprietario', 'fotoPrincipal'])->withCount('manutencoes')->when($busca->isNotEmpty(), fn ($q) => $q->where(fn ($q) => $q->where('nome', 'like', "%{$busca}%")->orWhere('marca', 'like', "%{$busca}%")->orWhere('modelo', 'like', "%{$busca}%")->orWhereHas('proprietario', fn ($q) => $q->where('nome', 'like', "%{$busca}%"))))->orderByRaw('ano IS NULL')->orderBy('ano')->orderBy('nome')->paginate(12)->withQueryString();

        return view('veiculos.index', compact('veiculos'));
    }

    public function create(Request $request)
    {
        return $this->form(new Veiculo(['proprietario_id' => $request->proprietario_id]));
    }

    public function store(Request $request)
    {
        $request->validate(['foto' => ['nullable', 'image', 'max:5120']]);
        $this->validarAnexos($request);
        $v = DB::transaction(function () use ($request) {
            $v = Veiculo::create($this->dados($request));

            if ($v->quilometragem_atual > 0) {
                Quilometragem::create(['veiculo_id' => $v->id, 'quilometragem' => $v->quilometragem_atual, 'registrada_em' => now(), 'origem' => 'manual']);
            }

            if ($request->hasFile('foto')) {
                $arquivo = $request->file('foto')->store("veiculos/{$v->id}", 'public');
                FotoVeiculo::create(['veiculo_id' => $v->id, 'arquivo' => $arquivo, 'data_foto' => now(), 'categoria' => 'geral', 'principal' => true]);
            }

            return $v;
        });

        $this->salvarAnexos($request, $v);

        return redirect()->route('veiculos.show', $v)->with('sucesso', 'Veículo cadastrado.');
    }

    public function show(Veiculo $veiculo)
    {
        $veiculo->load(['proprietario', 'fotos', 'galerias.fotos', 'anexos', 'quilometragens' => fn ($q) => $q->latest('registrada_em')->limit(10), 'manutencoes' => fn ($q) => $q->with(['tipo.sistema', 'tipos.sistema', 'prestador', 'anexos', 'itens.tipo', 'itens.fornecedor', 'fotos'])->latest('data_manutencao'), 'pecasCompradas' => fn ($q) => $q->with('fornecedor')->latest('data_compra'), 'especificacoes' => fn ($q) => $q->with('tipoServico.sistema')->orderBy('categoria')->orderBy('item')]);

        return view('veiculos.show', compact('veiculo'));
    }

    public function edit(Veiculo $veiculo)
    {
        return $this->form($veiculo);
    }

    public function editarGaleria(Veiculo $veiculo)
    {
        $veiculo->load(['fotos', 'galerias.fotos']);

        return view('veiculos.galeria-edit', compact('veiculo'));
    }

    public function update(Request $request, Veiculo $veiculo)
    {
        $this->validarAnexos($request);
        $veiculo->update($this->dados($request, $veiculo));
        $this->salvarAnexos($request, $veiculo);

        return redirect()->route('veiculos.show', $veiculo)->with('sucesso', 'Veículo atualizado.');
    }

    public function destroy(Veiculo $veiculo)
    {
        $veiculo->delete();

        return redirect()->route('veiculos.index')->with('sucesso', 'Veículo arquivado.');
    }

    public function quilometragem(Request $request, Veiculo $veiculo)
    {
        $dados = $request->validate(['quilometragem' => ['required', 'integer', 'min:0'], 'registrada_em' => ['required', 'date'], 'observacoes' => ['nullable', 'string']]);
        DB::transaction(function () use ($dados, $veiculo) {
            Quilometragem::create($dados + ['veiculo_id' => $veiculo->id, 'origem' => 'manual']);
            if ($dados['quilometragem'] >= $veiculo->quilometragem_atual) {
                $veiculo->update(['quilometragem_atual' => $dados['quilometragem']]);
            }
        });

        return back()->with('sucesso', 'Quilometragem registrada.');
    }

    public function foto(Request $request, Veiculo $veiculo)
    {
        $dados = $request->validate([
            'foto' => ['nullable', 'image', 'max:5120', 'required_without:fotos'],
            'fotos' => ['nullable', 'array', 'max:40', 'required_without:foto'],
            'fotos.*' => ['image', 'max:5120'],
            'principal' => ['nullable', 'boolean'],
        ]);
        $arquivos = $request->hasFile('fotos') ? $request->file('fotos') : [$request->file('foto')];
        $primeiraFotoDoVeiculo = ! $veiculo->fotos()->whereNull('galeria_veiculo_id')->whereNull('manutencao_id')->exists();

        foreach ($arquivos as $indice => $foto) {
            $principal = ($indice === 0) && ((bool) ($dados['principal'] ?? false) || $primeiraFotoDoVeiculo);
            if ($principal) {
                $veiculo->fotos()->update(['principal' => false]);
            }
            $arquivo = $foto->store("veiculos/{$veiculo->id}", 'public');
            FotoVeiculo::create(['veiculo_id' => $veiculo->id, 'arquivo' => $arquivo, 'data_foto' => now(), 'categoria' => 'geral', 'principal' => $principal]);
        }

        $quantidade = count($arquivos);
        return back()->with('sucesso', $quantidade === 1 ? 'Foto adicionada à galeria.' : "{$quantidade} fotos adicionadas à galeria.");
    }

    public function excluirFoto(Veiculo $veiculo, FotoVeiculo $foto)
    {
        abort_unless($foto->veiculo_id === $veiculo->id, 404);
        $destino = $foto->manutencao_id
            ? route('manutencoes.edit', $foto->manutencao_id)
            : ($foto->galeria_veiculo_id
                ? route('veiculos.galerias.show', [$veiculo, $foto->galeria_veiculo_id])
                : route('veiculos.galeria.edit', $veiculo));
        Storage::disk('public')->delete($foto->arquivo);
        $foto->delete();

        return redirect($destino)->with('sucesso', 'Fotografia removida.');
    }

    public function excluirFotos(Request $request, Veiculo $veiculo)
    {
        $dados = $request->validate([
            'fotos' => ['required', 'array', 'min:1'],
            'fotos.*' => ['integer', 'distinct'],
        ]);
        $fotos = $veiculo->fotos()->whereKey($dados['fotos'])->get();
        abort_unless($fotos->count() === count($dados['fotos']), 404);

        $galerias = $fotos->pluck('galeria_veiculo_id')->unique();
        $galeriaId = $galerias->count() === 1 ? $galerias->first() : null;
        foreach ($fotos as $foto) {
            Storage::disk('public')->delete($foto->arquivo);
            $foto->delete();
        }

        $destino = $galeriaId
            ? route('veiculos.galerias.show', [$veiculo, $galeriaId])
            : route('veiculos.galeria.edit', $veiculo);

        return redirect($destino)->with('sucesso', $fotos->count().' fotografias removidas.');
    }

    public function destacarFoto(Request $request, Veiculo $veiculo, FotoVeiculo $foto)
    {
        abort_unless($foto->veiculo_id === $veiculo->id, 404);
        abort_if($foto->galeria_veiculo_id !== null, 422, 'Somente fotos da galeria principal podem ser usadas como destaque.');
        $dados = $request->validate(['posicao_x' => ['required', 'integer', 'between:0,100'], 'posicao_y' => ['required', 'integer', 'between:0,100']]);
        DB::transaction(function () use ($veiculo, $foto, $dados) {
            $veiculo->fotos()->update(['principal' => false]);
            $veiculo->fotos()->whereKey($foto->id)->update(['principal' => true, 'posicao_x' => $dados['posicao_x'], 'posicao_y' => $dados['posicao_y']]);
        });

        return back()->with('sucesso', 'Foto de destaque e enquadramento atualizados.');
    }

    public function baixarAnexo(Veiculo $veiculo, AnexoVeiculo $anexo)
    {
        abort_unless($anexo->veiculo_id === $veiculo->id && Storage::disk('local')->exists($anexo->arquivo), 404);
        return Storage::disk('local')->download($anexo->arquivo, $anexo->nome_original);
    }

    public function excluirAnexo(Veiculo $veiculo, AnexoVeiculo $anexo)
    {
        abort_unless($anexo->veiculo_id === $veiculo->id, 404);
        Storage::disk('local')->delete($anexo->arquivo);
        $anexo->delete();
        return back()->with('sucesso', 'Documento removido.');
    }

    private function form(Veiculo $veiculo)
    {
        $veiculo->loadMissing(['anexos', 'especificacoes.tipoServico.sistema']);
        $marcas = MarcaVeiculo::with(['modelos' => fn ($q) => $q->where('ativo', true)->orderBy('nome')])->where('ativo', true)->orderBy('nome')->get();

        return view('veiculos.form', ['veiculo' => $veiculo, 'proprietarios' => Proprietario::where('ativo', true)->orderBy('nome')->get(), 'marcas' => $marcas]);
    }

    private function dados(Request $request, ?Veiculo $veiculo = null): array
    {
        $request->merge(['placa' => $request->placa ? mb_strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $request->placa)) : null, 'chassi' => $request->chassi ? mb_strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $request->chassi)) : null, 'renavam' => $request->renavam ? preg_replace('/\D/', '', $request->renavam) : null]);
        $validarMarca = function ($attribute, $value, $fail) {
            if ($value && $value !== 'outra' && ! MarcaVeiculo::whereKey($value)->exists()) $fail('A marca selecionada é inválida.');
        };
        $validarModelo = function ($attribute, $value, $fail) {
            if ($value && $value !== 'outro' && ! ModeloVeiculo::whereKey($value)->exists()) $fail('O modelo selecionado é inválido.');
        };
        $d = $request->validate(['proprietario_id' => ['required', 'exists:proprietarios,id'], 'nome' => ['required', 'string', 'max:255'], 'tipo_uso' => ['nullable', Rule::in(['uso_diario', 'uso_eventual', 'colecao'])], 'marca_veiculo_id' => ['nullable', $validarMarca], 'marca_custom' => ['nullable', 'string', 'max:100'], 'modelo_veiculo_id' => ['nullable', $validarModelo], 'modelo_custom' => ['nullable', 'string', 'max:100'], 'nome_modelo_completo' => ['nullable', 'string', 'max:255'], 'versao_motor' => ['nullable', 'string', 'max:120'], 'combustivel' => ['nullable', Rule::in(['gasolina', 'etanol', 'flex', 'diesel', 'gnv', 'eletrico', 'hibrido', 'outro'])], 'cambio' => ['nullable', Rule::in(['manual', 'automatico', 'automatizado', 'cvt', 'outro'])], 'possui_4x4' => ['nullable', 'boolean'], 'possui_ar_condicionado' => ['nullable', 'boolean'], 'possui_manual' => ['nullable', 'boolean'], 'numero_motor' => ['nullable', 'string', 'max:80'], 'cor' => ['nullable', 'string', 'max:80'], 'ano' => ['required', 'integer', 'min:1886', 'max:'.(now()->year + 1)], 'ano_modelo' => ['nullable', 'integer', 'min:1886', 'max:'.(now()->year + 1)], 'placa' => ['required', 'string', 'max:7', Rule::unique('veiculos')->ignore($veiculo)], 'chassi' => ['nullable', 'string', 'max:50', Rule::unique('veiculos')->ignore($veiculo)], 'renavam' => ['nullable', 'digits:11', Rule::unique('veiculos')->ignore($veiculo)], 'codigo_seguranca_crv' => ['nullable', 'string', 'max:30'], 'quilometragem_atual' => ['required', 'integer', 'min:0'], 'data_compra' => ['nullable', 'date'], 'local_compra' => ['nullable', 'string', 'max:255'], 'local_anterior' => ['nullable', 'string', 'max:255'], 'local_atual' => ['nullable', 'string', 'max:255'], 'cidade_atual' => ['nullable', 'string', 'max:120'], 'estado_atual' => ['nullable', 'string', 'size:2'], 'observacoes' => ['nullable', 'string'], 'ativo' => ['required', 'boolean']]);
        $d['tipo_uso'] = $d['tipo_uso'] ?? 'colecao';
        $d['possui_4x4'] = (bool) ($d['possui_4x4'] ?? false);
        $d['possui_ar_condicionado'] = (bool) ($d['possui_ar_condicionado'] ?? false);
        $d['possui_manual'] = (bool) ($d['possui_manual'] ?? false);
        $d['marca_veiculo_id'] = ($d['marca_veiculo_id'] ?? null) === 'outra' ? null : ($d['marca_veiculo_id'] ?? null);
        $d['modelo_veiculo_id'] = ($d['modelo_veiculo_id'] ?? null) === 'outro' ? null : ($d['modelo_veiculo_id'] ?? null);
        $d['estado_atual'] = ! empty($d['estado_atual']) ? mb_strtoupper($d['estado_atual']) : null;
        $marca = $d['marca_veiculo_id'] ? MarcaVeiculo::find($d['marca_veiculo_id']) : null;
        $modelo = $d['modelo_veiculo_id'] ? ModeloVeiculo::find($d['modelo_veiculo_id']) : null;
        if (! $marca && ! $d['marca_custom']) {
            throw ValidationException::withMessages(['marca_veiculo_id' => 'Selecione ou informe a marca.']);
        }if (! $modelo && ! $d['modelo_custom']) {
            throw ValidationException::withMessages(['modelo_veiculo_id' => 'Selecione ou informe o modelo.']);
        }if ($modelo && $modelo->marca_veiculo_id !== $marca?->id) {
            throw ValidationException::withMessages(['modelo_veiculo_id' => 'O modelo não pertence à marca selecionada.']);
        }$d['marca'] = $marca?->nome ?? trim($d['marca_custom']);
        $d['modelo'] = $modelo?->nome ?? trim($d['modelo_custom']);
        unset($d['marca_custom'],$d['modelo_custom']);

        return $d;
    }

    private function validarAnexos(Request $request): void
    {
        $request->validate(['anexos' => ['nullable', 'array'], 'anexos.*' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240']]);
    }

    private function salvarAnexos(Request $request, Veiculo $veiculo): void
    {
        foreach ($request->file('anexos', []) as $tipo => $arquivo) {
            if (! in_array($tipo, ['crlv', 'consulta_historico', 'laudo_vistoria', 'crv_atpve'], true)) continue;
            $existente = $veiculo->anexos()->where('tipo', $tipo)->first();
            if ($existente) Storage::disk('local')->delete($existente->arquivo);
            $caminho = $arquivo->store("documentos/veiculos/{$veiculo->id}", 'local');
            $veiculo->anexos()->updateOrCreate(['tipo' => $tipo], ['nome_original' => $arquivo->getClientOriginalName(), 'arquivo' => $caminho, 'mime_type' => $arquivo->getMimeType(), 'tamanho' => $arquivo->getSize()]);
        }
    }
}
