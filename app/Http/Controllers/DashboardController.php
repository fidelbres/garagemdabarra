<?php

namespace App\Http\Controllers;

use App\Models\Manutencao;
use App\Models\Proprietario;
use App\Models\Veiculo;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $hoje = Carbon::today();
        $limite = $hoje->copy()->addDays(30);
        $proximas = Manutencao::with(['veiculo.proprietario', 'tipo'])
            ->whereNotNull('proxima_data_prevista')
            ->whereDate('proxima_data_prevista', '<=', $limite)
            ->orderBy('proxima_data_prevista')->limit(8)->get();

        return view('dashboard', [
            'proprietarios' => Proprietario::where('ativo', true)->count(),
            'veiculos' => Veiculo::where('ativo', true)->count(),
            'manutencoesMes' => Manutencao::whereMonth('data_manutencao', now()->month)->whereYear('data_manutencao', now()->year)->count(),
            'alertas' => $proximas->count(),
            'proximas' => $proximas,
            'recentes' => Manutencao::with(['veiculo.proprietario', 'tipo.sistema'])->latest('data_manutencao')->limit(6)->get(),
        ]);
    }
}
