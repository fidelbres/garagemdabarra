<?php

namespace App\Http\Controllers;

use App\Models\Manutencao;
use App\Models\PecaComprada;
use App\Models\SistemaVeiculo;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class FinanceiroController extends Controller
{
    public function __invoke(Request $request)
    {
        $tiposUsoPermitidos = ['uso_diario', 'uso_eventual', 'colecao'];
        $tiposUso = array_values(array_intersect(
            $tiposUsoPermitidos,
            array_filter((array) $request->input('tipos_uso', []), 'is_string'),
        ));

        $filtrar = function ($query, string $campoData) use ($request, $tiposUso) {
            return $query
                ->when($request->filled('veiculo_id'), fn ($q) => $q->where('veiculo_id', $request->integer('veiculo_id')))
                ->when($tiposUso !== [], fn ($q) => $q->whereHas('veiculo', fn ($v) => $v->whereIn('tipo_uso', $tiposUso)))
                ->when($request->filled('data_inicio'), fn ($q) => $q->whereDate($campoData, '>=', $request->date('data_inicio')))
                ->when($request->filled('data_fim'), fn ($q) => $q->whereDate($campoData, '<=', $request->date('data_fim')));
        };

        $manutencoes = $filtrar(
            Manutencao::query()->where(fn ($q) => $q->whereNotNull('valor')->orWhereHas('itens'))
                ->when($request->filled('sistema_id'), fn ($q) => $q->whereHas('tipos', fn ($tipo) => $tipo->where('sistema_veiculo_id', $request->integer('sistema_id'))))
                ->with(['veiculo.proprietario', 'tipo.sistema', 'tipos.sistema', 'prestador', 'itens']),
            'data_manutencao'
        )->get()->map(function ($registro) {
            $registro->tipo_registro_financeiro = 'servico';
            $registro->data_financeira = $registro->data_manutencao;
            $registro->valor_financeiro = $registro->custo_total;
            return $registro;
        });

        $compras = collect();
        if (! $request->filled('sistema_id')) {
            $compras = $filtrar(PecaComprada::query()->whereNotNull('valor_total')->with(['veiculo.proprietario', 'fornecedor']), 'data_compra')
                ->get()->map(function ($registro) {
                    $registro->tipo_registro_financeiro = 'peca';
                    $registro->data_financeira = $registro->data_compra;
                    $registro->valor_financeiro = (float) $registro->valor_total;
                    return $registro;
                });
        }

        $todos = $manutencoes->concat($compras)->sortByDesc(fn ($item) => $item->data_financeira->format('Y-m-d').str_pad((string) $item->id, 10, '0', STR_PAD_LEFT))->values();
        $total = (float) $todos->sum('valor_financeiro');
        $quantidade = $todos->count();
        $maiorGasto = (float) ($todos->max('valor_financeiro') ?? 0);
        $porVeiculo = $todos->groupBy('veiculo_id')->map(fn ($itens, $veiculoId) => (object) ['veiculo_id' => (int) $veiculoId, 'veiculo' => $itens->first()->veiculo, 'total' => (float) $itens->sum('valor_financeiro')])->sortByDesc('total')->values();
        $pagina = LengthAwarePaginator::resolveCurrentPage();
        $registros = new LengthAwarePaginator($todos->forPage($pagina, 20)->values(), $quantidade, 20, $pagina, ['path' => $request->url(), 'query' => $request->query()]);

        return view('financeiro.index', [
            'registros' => $registros,
            'total' => $total,
            'quantidade' => $quantidade,
            'media' => $quantidade ? $total / $quantidade : 0,
            'maiorGasto' => $maiorGasto,
            'porVeiculo' => $porVeiculo,
            'veiculos' => Veiculo::orderBy('ano')->orderBy('nome')->get(),
            'sistemas' => SistemaVeiculo::where('ativo', true)->orderBy('ordem')->get(),
            'tiposUsoSelecionados' => $tiposUso,
        ]);
    }
}
