<?php

namespace Tests\Feature;

use App\Models\Proprietario;
use App\Models\SistemaVeiculo;
use App\Models\TipoManutencao;
use App\Models\User;
use App\Models\Veiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EspecificacoesVeiculoTest extends TestCase
{
    use RefreshDatabase;

    public function test_cadastra_aplicacao_do_veiculo_e_vincula_ao_servico(): void
    {
        $usuario = User::factory()->create(['tipo' => 'administrador', 'ativo' => true]);
        $proprietario = Proprietario::create(['nome' => 'Colecionador', 'ativo' => true]);
        $veiculo = Veiculo::create(['proprietario_id' => $proprietario->id, 'nome' => 'Vitara', 'marca' => 'Suzuki', 'modelo' => 'Vitara', 'ano' => 1997, 'placa' => 'ABC1D23', 'possui_4x4' => true, 'possui_ar_condicionado' => true, 'ativo' => true]);
        $sistema = SistemaVeiculo::create(['nome' => 'Motor', 'ordem' => 1, 'ativo' => true]);
        $servico = TipoManutencao::create(['sistema_veiculo_id' => $sistema->id, 'nome' => 'Troca do filtro de óleo', 'tipo_periodicidade' => 'nenhuma', 'ativo' => true]);

        $this->actingAs($usuario)->post(route('veiculos.especificacoes.store', $veiculo), [
            'tipo_manutencao_id' => $servico->id,
            'categoria' => 'filtros',
            'item' => 'Filtro de óleo',
            'especificacao_recomendada' => 'Rosca M20 x 1,5',
            'marca_preferida' => 'Tecfil',
            'referencia' => 'PSL55',
            'referencias_equivalentes' => 'Wega JFO-021',
            'marca_instalada' => 'Tecfil',
            'referencia_instalada' => 'PSL55',
            'instalado_em' => '2026-09-26',
            'quilometragem_instalacao' => 118000,
        ])->assertRedirect(route('veiculos.show', $veiculo));

        $this->assertDatabaseHas('especificacoes_veiculos', ['veiculo_id' => $veiculo->id, 'item' => 'Filtro de óleo', 'referencia' => 'PSL55']);
        $this->actingAs($usuario)->get(route('veiculos.show', $veiculo))->assertOk()->assertSee('Aplicações e especificações')->assertSee('PSL55');
        $this->actingAs($usuario)->get(route('veiculos.edit', $veiculo))->assertOk()->assertSee('Catálogo técnico')->assertSee('PSL55');
        $this->actingAs($usuario)->get(route('veiculos.index'))->assertOk()->assertSee('aria-label="Ar-condicionado"', false)->assertSee('4×4');
        $this->actingAs($usuario)->get(route('manutencoes.create', ['veiculo_id' => $veiculo->id]))->assertOk()->assertSee('Referências deste veículo')->assertSee('data-spec-service="'.$servico->id.'"', false);
    }
}
