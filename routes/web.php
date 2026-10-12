<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinanceiroController;
use App\Http\Controllers\EspecificacaoVeiculoController;
use App\Http\Controllers\GaleriaVeiculoController;
use App\Http\Controllers\ManutencaoController;
use App\Http\Controllers\PecaCompradaController;
use App\Http\Controllers\ProprietarioController;
use App\Http\Controllers\PrestadorServicoController;
use App\Http\Controllers\TipoManutencaoController;
use App\Http\Controllers\VeiculoController;
use Illuminate\Support\Facades\Route;

Route::get('midia/{arquivo}', [VeiculoController::class, 'midia'])->where('arquivo', '.*')->name('midia');

Route::middleware('guest')->group(function () {
    Route::get('entrar', [AuthController::class, 'create'])->name('login');
    Route::post('entrar', [AuthController::class, 'store'])->name('login.store');
    Route::get('auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google.redirect');
    Route::get('auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});
Route::middleware('auth')->group(function () {
    Route::post('sair', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('gastos', FinanceiroController::class)->name('financeiro.index');
    Route::resource('proprietarios', ProprietarioController::class);
    Route::resource('prestadores', PrestadorServicoController::class)->parameters(['prestadores' => 'prestador'])->except('show');
    Route::post('veiculos/{veiculo}/quilometragem', [VeiculoController::class, 'quilometragem'])->name('veiculos.quilometragem');
    Route::get('veiculos/{veiculo}/aplicacoes/nova', [EspecificacaoVeiculoController::class, 'create'])->name('veiculos.especificacoes.create');
    Route::post('veiculos/{veiculo}/aplicacoes', [EspecificacaoVeiculoController::class, 'store'])->name('veiculos.especificacoes.store');
    Route::get('veiculos/{veiculo}/aplicacoes/{especificacao}/editar', [EspecificacaoVeiculoController::class, 'edit'])->name('veiculos.especificacoes.edit');
    Route::put('veiculos/{veiculo}/aplicacoes/{especificacao}', [EspecificacaoVeiculoController::class, 'update'])->name('veiculos.especificacoes.update');
    Route::delete('veiculos/{veiculo}/aplicacoes/{especificacao}', [EspecificacaoVeiculoController::class, 'destroy'])->name('veiculos.especificacoes.destroy');
    Route::post('veiculos/{veiculo}/fotos', [VeiculoController::class, 'foto'])->name('veiculos.fotos.store');
    Route::get('veiculos/{veiculo}/galeria/editar', [VeiculoController::class, 'editarGaleria'])->name('veiculos.galeria.edit');
    Route::get('veiculos/{veiculo}/galeria/editar', [VeiculoController::class, 'editarGaleria'])->name('veiculos.galeria.edit');
    Route::put('veiculos/{veiculo}/fotos/{foto}/destaque', [VeiculoController::class, 'destacarFoto'])->name('veiculos.fotos.destaque');
    Route::delete('veiculos/{veiculo}/fotos', [VeiculoController::class, 'excluirFotos'])->name('veiculos.fotos.destroy-many');
    Route::delete('veiculos/{veiculo}/fotos/{foto}', [VeiculoController::class, 'excluirFoto'])->name('veiculos.fotos.destroy');
    Route::get('veiculos/{veiculo}/anexos/{anexo}', [VeiculoController::class, 'baixarAnexo'])->name('veiculos.anexos.download');
    Route::delete('veiculos/{veiculo}/anexos/{anexo}', [VeiculoController::class, 'excluirAnexo'])->name('veiculos.anexos.destroy');
    Route::post('veiculos/{veiculo}/galerias', [GaleriaVeiculoController::class, 'store'])->name('veiculos.galerias.store');
    Route::get('veiculos/{veiculo}/galerias/{galeria}', [GaleriaVeiculoController::class, 'show'])->name('veiculos.galerias.show');
    Route::put('veiculos/{veiculo}/galerias/{galeria}', [GaleriaVeiculoController::class, 'update'])->name('veiculos.galerias.update');
    Route::delete('veiculos/{veiculo}/galerias/{galeria}', [GaleriaVeiculoController::class, 'destroy'])->name('veiculos.galerias.destroy');
    Route::post('veiculos/{veiculo}/galerias/{galeria}/fotos', [GaleriaVeiculoController::class, 'fotos'])->name('veiculos.galerias.fotos');
    Route::resource('veiculos', VeiculoController::class);
    Route::resource('tipos-manutencao', TipoManutencaoController::class)->parameters(['tipos-manutencao' => 'tipo'])->except('show');
    Route::get('manutencoes/{manutencao}/anexos/{anexo}', [ManutencaoController::class, 'baixarAnexo'])->name('manutencoes.anexos.download');
    Route::delete('manutencoes/{manutencao}/anexos/{anexo}', [ManutencaoController::class, 'excluirAnexo'])->name('manutencoes.anexos.destroy');
    Route::post('manutencoes/servicos/rapido', [ManutencaoController::class, 'cadastrarServico'])->name('manutencoes.servicos.store');
    Route::resource('manutencoes', ManutencaoController::class)->parameters(['manutencoes' => 'manutencao'])->except('show');
    Route::resource('pecas', PecaCompradaController::class)->parameters(['pecas' => 'peca'])->except('show');
});
