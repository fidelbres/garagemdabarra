<?php

namespace App\Http\Controllers;

use App\Models\ItemManutencao;
use App\Models\PecaComprada;
use App\Models\PrestadorServico;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PecaCompradaController extends Controller
{
    public function index(Request $request)
    {
        $compras = PecaComprada::with(['veiculo.proprietario', 'fornecedor'])
            ->when($request->filled('veiculo_id'), fn ($q) => $q->where('veiculo_id', $request->integer('veiculo_id')))
            ->get()->each(function ($peca) {
                $peca->origem_inventario = 'compra';
                $peca->data_inventario = $peca->data_compra;
                $peca->veiculo_inventario = $peca->veiculo;
                $peca->fornecedor_inventario = $peca->fornecedor?->nome;
                $peca->valor_inventario = $peca->valor_total;
            });
        $itens = ItemManutencao::with(['manutencao.veiculo.proprietario', 'manutencao.prestador', 'tipo.sistema', 'fornecedor'])
            ->where('categoria', 'peca')
            ->whereHas('manutencao', fn ($q) => $q->when($request->filled('veiculo_id'), fn ($q) => $q->where('veiculo_id', $request->integer('veiculo_id'))))
            ->get()->each(function ($peca) {
                $peca->origem_inventario = 'servico';
                $peca->data_inventario = $peca->manutencao->data_manutencao;
                $peca->veiculo_inventario = $peca->manutencao->veiculo;
                $peca->fornecedor_inventario = $peca->fornecedor?->nome ?: ($peca->manutencao->prestador?->nome ?: $peca->manutencao->empresa_servico);
                $peca->valor_inventario = $peca->valor_total;
            });
        $todas = $compras->concat($itens)->sortByDesc(fn ($peca) => $peca->data_inventario->format('Y-m-d').str_pad((string) $peca->id, 10, '0', STR_PAD_LEFT))->values();
        $pagina = LengthAwarePaginator::resolveCurrentPage();
        $pecas = new LengthAwarePaginator($todas->forPage($pagina, 20)->values(), $todas->count(), 20, $pagina, ['path' => $request->url(), 'query' => $request->query()]);

        return view('pecas.index', ['pecas' => $pecas, 'veiculos' => Veiculo::orderBy('nome')->get()]);
    }

    public function create(Request $request)
    {
        return $this->form(new PecaComprada(['veiculo_id' => $request->integer('veiculo_id') ?: null, 'data_compra' => today()]));
    }

    public function store(Request $request)
    {
        PecaComprada::create($this->dados($request));
        return redirect()->route('pecas.index')->with('sucesso', 'Peça comprada registrada.');
    }

    public function edit(PecaComprada $peca) { return $this->form($peca); }

    public function update(Request $request, PecaComprada $peca)
    {
        $peca->update($this->dados($request));
        return redirect()->route('pecas.index')->with('sucesso', 'Compra atualizada.');
    }

    public function destroy(PecaComprada $peca)
    {
        $peca->delete();
        return back()->with('sucesso', 'Compra arquivada.');
    }

    private function form(PecaComprada $peca)
    {
        $veiculos = Veiculo::with('proprietario')->orderBy('nome')->get();
        $fornecedores = PrestadorServico::where('ativo', true)->whereIn('atuacao', ['pecas', 'ambos'])->orderBy('nome')->get();
        return view('pecas.form', compact('peca', 'veiculos', 'fornecedores'));
    }

    private function dados(Request $request): array
    {
        foreach (['quantidade', 'valor_total'] as $campo) {
            if ($request->filled($campo)) {
                $valor = preg_replace('/[^0-9,.]/', '', (string) $request->input($campo));
                if (str_contains($valor, ',')) $valor = str_replace(',', '.', str_replace('.', '', $valor));
                $request->merge([$campo => $valor]);
            }
        }
        return $request->validate([
            'veiculo_id' => ['required', 'exists:veiculos,id'],
            'prestador_servico_id' => ['nullable', 'exists:prestadores_servico,id'],
            'descricao' => ['required', 'string', 'max:255'],
            'marca' => ['nullable', 'string', 'max:120'],
            'codigo' => ['nullable', 'string', 'max:120'],
            'quantidade' => ['required', 'numeric', 'min:0.01'],
            'valor_total' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'data_compra' => ['required', 'date'],
            'observacoes' => ['nullable', 'string'],
        ]);
    }
}
