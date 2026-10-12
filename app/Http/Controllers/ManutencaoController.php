<?php

namespace App\Http\Controllers;

use App\Models\AnexoManutencao;
use App\Models\EspecificacaoVeiculo;
use App\Models\FotoVeiculo;
use App\Models\Manutencao;
use App\Models\PrestadorServico;
use App\Models\Quilometragem;
use App\Models\SistemaVeiculo;
use App\Models\TipoManutencao;
use App\Models\Veiculo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ManutencaoController extends Controller
{
    public function index(Request $request)
    {
        $manutencoes = Manutencao::with(['veiculo.proprietario', 'tipo.sistema', 'tipos.sistema', 'prestador'])
            ->when($request->filled('veiculo_id'), fn ($query) => $query->where('veiculo_id', $request->veiculo_id))
            ->latest('data_manutencao')->paginate(15)->withQueryString();

        return view('manutencoes.index', compact('manutencoes'));
    }

    public function create(Request $request)
    {
        return $this->form(new Manutencao(['veiculo_id' => $request->veiculo_id, 'data_manutencao' => today()]));
    }

    public function store(Request $request)
    {
        $this->validarFotos($request);
        $this->validarAnexos($request);
        $itens = $this->validarItens($request);
        $dados = $this->dados($request);
        $tipoIds = array_map('intval', $request->input('tipo_manutencao_ids', []));
        $manutencao = DB::transaction(function () use ($dados, $tipoIds) {
            $manutencao = Manutencao::create($this->previsao($dados, $tipoIds));
            $this->sincronizarServicos($manutencao, $tipoIds);
            Quilometragem::create(['veiculo_id' => $manutencao->veiculo_id, 'manutencao_id' => $manutencao->id, 'quilometragem' => $manutencao->quilometragem, 'registrada_em' => Carbon::parse($manutencao->data_manutencao)->endOfDay(), 'origem' => 'manutencao']);
            if ($manutencao->quilometragem >= $manutencao->veiculo->quilometragem_atual) {
                $manutencao->veiculo->update(['quilometragem_atual' => $manutencao->quilometragem]);
            }

            return $manutencao;
        });
        $this->salvarFotos($request, $manutencao);
        $this->salvarAnexos($request, $manutencao);
        $this->salvarItens($manutencao, $itens);

        return redirect()->route('veiculos.show', $manutencao->veiculo_id)->with('sucesso', 'Serviço registrado no histórico do veículo.');
    }

    public function edit(Manutencao $manutencao)
    {
        return $this->form($manutencao);
    }

    public function update(Request $request, Manutencao $manutencao)
    {
        $this->validarFotos($request);
        $this->validarAnexos($request);
        $itens = $this->validarItens($request);
        $dados = $this->dados($request);
        $tipoIds = array_map('intval', $request->input('tipo_manutencao_ids', []));
        DB::transaction(function () use ($manutencao, $dados, $tipoIds) {
            $manutencao->update($this->previsao($dados, $tipoIds));
            $this->sincronizarServicos($manutencao, $tipoIds);
        });
        $this->salvarFotos($request, $manutencao);
        $this->salvarAnexos($request, $manutencao);
        $this->salvarItens($manutencao, $itens, true);

        return redirect()->route('veiculos.show', $manutencao->veiculo_id)->with('sucesso', 'Registro do serviço atualizado.');
    }

    public function destroy(Manutencao $manutencao)
    {
        $veiculoId = $manutencao->veiculo_id;
        $manutencao->delete();

        return redirect()->route('veiculos.show', $veiculoId)->with('sucesso', 'Registro removido do histórico.');
    }

    public function baixarAnexo(Manutencao $manutencao, AnexoManutencao $anexo)
    {
        abort_unless($anexo->manutencao_id === $manutencao->id && Storage::disk('local')->exists($anexo->arquivo), 404);

        return Storage::disk('local')->download($anexo->arquivo, $anexo->nome_original);
    }

    public function excluirAnexo(Manutencao $manutencao, AnexoManutencao $anexo)
    {
        abort_unless($anexo->manutencao_id === $manutencao->id, 404);
        Storage::disk('local')->delete($anexo->arquivo);
        $anexo->delete();

        return back()->with('sucesso', 'Anexo removido do serviço.');
    }

    public function cadastrarServico(Request $request)
    {
        $dados = $request->validate([
            'sistema_veiculo_id' => ['required', 'integer', 'exists:sistemas_veiculo,id'],
            'nome' => ['required', 'string', 'max:255'],
        ]);
        $nome = trim($dados['nome']);
        $tipo = TipoManutencao::where('sistema_veiculo_id', $dados['sistema_veiculo_id'])
            ->whereRaw('LOWER(nome) = ?', [mb_strtolower($nome)])
            ->first();
        if ($tipo) {
            if (! $tipo->ativo) $tipo->update(['ativo' => true]);
        } else {
            $tipo = TipoManutencao::create([
                'sistema_veiculo_id' => $dados['sistema_veiculo_id'],
                'nome' => $nome,
                'tipo_periodicidade' => 'nenhuma',
                'antecedencia_alerta_dias' => 30,
                'antecedencia_alerta_quilometros' => 1000,
                'ativo' => true,
            ]);
        }
        $tipo->load('sistema');

        return response()->json(['id' => $tipo->id, 'nome' => $tipo->nome, 'sistema' => $tipo->sistema->nome]);
    }

    private function form(Manutencao $manutencao)
    {
        $manutencao->loadMissing(['anexos', 'fotos', 'tipos', 'itens.fornecedor']);
        $veiculos = Veiculo::with(['proprietario', 'fotoPrincipal'])->where('ativo', true)->orderBy('nome')->get();
        $sistemas = SistemaVeiculo::with(['servicos' => fn ($query) => $query->where('ativo', true)->orderBy('nome')])->where('ativo', true)->orderBy('ordem')->orderBy('nome')->get();
        $tipos = $sistemas->pluck('servicos')->flatten();
        $prestadores = PrestadorServico::where('ativo', true)->whereIn('atuacao', ['servicos', 'ambos'])->orderBy('nome')->get();
        $fornecedores = PrestadorServico::where('ativo', true)->whereIn('atuacao', ['pecas', 'ambos'])->orderBy('nome')->get();
        $especificacoes = EspecificacaoVeiculo::whereNotNull('tipo_manutencao_id')->orderBy('item')->get();

        return view('manutencoes.form', compact('manutencao', 'veiculos', 'tipos', 'sistemas', 'prestadores', 'fornecedores', 'especificacoes'));
    }

    private function previsao(array $dados, array $tipoIds): array
    {
        $tipos = TipoManutencao::whereKey($tipoIds)->get();
        $datas = $tipos->filter(fn ($tipo) => $tipo->intervalo_meses)->map(fn ($tipo) => Carbon::parse($dados['data_manutencao'])->addMonths($tipo->intervalo_meses)->toDateString());
        $quilometragens = $tipos->filter(fn ($tipo) => $tipo->intervalo_quilometros)->map(fn ($tipo) => $dados['quilometragem'] + $tipo->intervalo_quilometros);
        $dados['tipo_manutencao_id'] = $tipoIds[0] ?? null;
        $dados['proxima_data_prevista'] = $datas->sort()->first();
        $dados['proxima_quilometragem_prevista'] = $quilometragens->sort()->first();

        return $dados;
    }

    private function sincronizarServicos(Manutencao $manutencao, array $tipoIds): void
    {
        $tipos = TipoManutencao::whereKey($tipoIds)->get();
        $vinculos = $tipos->mapWithKeys(fn ($tipo) => [$tipo->id => [
            'proxima_data_prevista' => $tipo->intervalo_meses ? Carbon::parse($manutencao->data_manutencao)->addMonths($tipo->intervalo_meses)->toDateString() : null,
            'proxima_quilometragem_prevista' => $tipo->intervalo_quilometros ? $manutencao->quilometragem + $tipo->intervalo_quilometros : null,
        ]])->all();
        $manutencao->tipos()->sync($vinculos);
    }

    private function validarFotos(Request $request): void
    {
        $request->validate(['fotos' => ['nullable', 'array'], 'fotos.*' => ['image', 'max:5120'], 'categoria_fotos' => ['nullable', Rule::in(['antes', 'depois', 'componente'])]]);
    }

    private function salvarFotos(Request $request, Manutencao $manutencao): void
    {
        foreach ($request->file('fotos', []) as $foto) {
            $arquivo = $foto->store("veiculos/{$manutencao->veiculo_id}", 'public');
            FotoVeiculo::create(['veiculo_id' => $manutencao->veiculo_id, 'manutencao_id' => $manutencao->id, 'arquivo' => $arquivo, 'categoria' => $request->input('categoria_fotos', 'componente'), 'data_foto' => now(), 'principal' => false]);
        }
    }

    private function validarAnexos(Request $request): void
    {
        $request->validate([
            'anexos' => ['nullable', 'array', 'max:20'],
            'anexos.*' => ['file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ]);
    }

    private function salvarAnexos(Request $request, Manutencao $manutencao): void
    {
        foreach ($request->file('anexos', []) as $arquivo) {
            $caminho = $arquivo->store("documentos/manutencoes/{$manutencao->id}", 'local');
            $manutencao->anexos()->create([
                'nome_original' => $arquivo->getClientOriginalName(),
                'arquivo' => $caminho,
                'mime_type' => $arquivo->getMimeType(),
                'tamanho' => $arquivo->getSize(),
            ]);
        }
    }

    private function validarItens(Request $request): array
    {
        $servicosSelecionados = array_map('intval', array_filter($request->input('tipo_manutencao_ids', [])));
        $dados = $request->validate([
            'itens' => ['nullable', 'array', 'max:100'],
            'itens.*.tipo_manutencao_id' => ['nullable', 'integer', Rule::in($servicosSelecionados)],
            'itens.*.categoria' => ['nullable', Rule::in(['peca', 'servico'])],
            'itens.*.fornecedor_id' => ['nullable', function (string $attribute, mixed $value, \Closure $fail) {
                if ($value !== 'outro' && ! PrestadorServico::whereKey($value)->where('ativo', true)->exists()) $fail('O fornecedor selecionado é inválido.');
            }],
            'itens.*.fornecedor_nome' => ['nullable', 'required_if:itens.*.fornecedor_id,outro', 'string', 'max:255'],
            'itens.*.descricao' => ['nullable', 'string', 'max:255'],
            'itens.*.quantidade' => ['nullable', 'numeric', 'min:0.001', 'max:9999999'],
            'itens.*.valor_unitario' => ['nullable', 'string', 'max:30'],
        ])['itens'] ?? [];

        return array_values(array_filter($dados, fn ($item) => ! empty(trim($item['descricao'] ?? ''))));
    }

    private function salvarItens(Manutencao $manutencao, array $itens, bool $substituir = false): void
    {
        if ($substituir) $manutencao->itens()->delete();
        foreach ($itens as $item) {
            $valor = (string) ($item['valor_unitario'] ?? '0');
            if (str_contains($valor, ',')) $valor = str_replace(',', '.', str_replace('.', '', preg_replace('/[^0-9,.]/', '', $valor)));
            $quantidade = (float) ($item['quantidade'] ?? 1);
            $unitario = max(0, (float) $valor);
            $fornecedorId = $item['fornecedor_id'] ?? null;
            if ($fornecedorId === 'outro') {
                $nome = trim((string) $item['fornecedor_nome']);
                $fornecedor = PrestadorServico::whereRaw('LOWER(nome) = ?', [mb_strtolower($nome)])->first();
                if (! $fornecedor) $fornecedor = PrestadorServico::create(['nome' => $nome, 'atuacao' => 'pecas', 'ativo' => true]);
                elseif ($fornecedor->atuacao === 'servicos') $fornecedor->update(['atuacao' => 'ambos']);
                $fornecedorId = $fornecedor->id;
            }
            $manutencao->itens()->create([
                'tipo_manutencao_id' => $item['tipo_manutencao_id'] ?? null,
                'fornecedor_id' => $fornecedorId ?: null,
                'categoria' => $item['categoria'] ?? 'peca',
                'descricao' => trim($item['descricao']),
                'quantidade' => $quantidade,
                'valor_unitario' => $unitario,
                'valor_total' => round($quantidade * $unitario, 2),
            ]);
        }
    }

    private function dados(Request $request): array
    {
        $valor = $request->input('valor');
        if (is_string($valor) && str_contains($valor, ',')) {
            $valor = str_replace(',', '.', str_replace('.', '', preg_replace('/[^0-9,.]/', '', $valor)));
        }
        $tipoIds = array_filter($request->input('tipo_manutencao_ids', []));
        $descricaoAutomatica = TipoManutencao::whereKey($tipoIds)->orderBy('nome')->pluck('nome')->join(', ');
        $request->merge([
            'valor' => $valor === '' ? null : $valor,
            'telefone_servico' => $request->telefone_servico ? preg_replace('/\D/', '', $request->telefone_servico) : null,
            'whatsapp_servico' => $request->whatsapp_servico ? preg_replace('/\D/', '', $request->whatsapp_servico) : null,
            'servico_executado' => $request->filled('servico_executado') ? $request->servico_executado : $descricaoAutomatica,
        ]);
        $novoPrestador = $request->input('prestador_servico_id') === 'outro';
        $dados = $request->validate(['veiculo_id' => ['required', 'exists:veiculos,id'], 'tipo_manutencao_ids' => ['required', 'array', 'min:1'], 'tipo_manutencao_ids.*' => ['integer', 'distinct', 'exists:tipos_manutencao,id'], 'prestador_servico_id' => $novoPrestador ? ['required', Rule::in(['outro'])] : ['nullable', 'exists:prestadores_servico,id'], 'prestador_nome' => [$novoPrestador ? 'required' : 'nullable', 'string', 'max:255'], 'prestador_local' => ['nullable', 'string', 'max:255'], 'prestador_endereco' => ['nullable', 'string', 'max:255'], 'prestador_contato' => ['nullable', 'string', 'max:255'], 'natureza' => ['required', Rule::in(['periodica', 'avulsa'])], 'data_manutencao' => ['required', 'date'], 'quilometragem' => ['required', 'integer', 'min:0'], 'valor' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'], 'empresa_servico' => ['nullable', 'string', 'max:255'], 'local_servico' => ['nullable', 'string', 'max:255'], 'telefone_servico' => ['nullable', 'string', 'max:15'], 'whatsapp_servico' => ['nullable', 'string', 'max:15'], 'descricao_problema' => ['nullable', 'string'], 'servico_executado' => ['required', 'string'], 'observacoes' => ['nullable', 'string']]);
        if ($novoPrestador) {
            $prestador = PrestadorServico::create(['nome' => trim($dados['prestador_nome']), 'local' => $dados['prestador_local'] ?? null, 'endereco' => $dados['prestador_endereco'] ?? null, 'telefone' => $dados['telefone_servico'] ?? null, 'whatsapp' => $dados['whatsapp_servico'] ?? null, 'pessoa_contato' => $dados['prestador_contato'] ?? null, 'atuacao' => 'servicos', 'ativo' => true]);
            $dados['prestador_servico_id'] = $prestador->id;
            $dados['empresa_servico'] = $prestador->nome;
            $dados['local_servico'] = collect([$prestador->local, $prestador->endereco])->filter()->join(' · ');
        } elseif (! empty($dados['prestador_servico_id'])) {
            $prestador = PrestadorServico::find($dados['prestador_servico_id']);
            $dados['empresa_servico'] = $prestador->nome;
            $dados['local_servico'] = collect([$prestador->local, $prestador->endereco])->filter()->join(' · ');
            $dados['telefone_servico'] = $prestador->telefone;
            $dados['whatsapp_servico'] = $prestador->whatsapp;
        }
        unset($dados['tipo_manutencao_ids'], $dados['prestador_nome'], $dados['prestador_local'], $dados['prestador_endereco'], $dados['prestador_contato']);

        return $dados;
    }
}
