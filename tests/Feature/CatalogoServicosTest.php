<?php

namespace Tests\Feature;

use App\Models\SistemaVeiculo;
use App\Models\TipoManutencao;
use App\Models\User;
use Database\Seeders\OficinaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogoServicosTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalogo_inclui_troca_da_bateria_na_parte_eletrica(): void
    {
        $this->seed(OficinaSeeder::class);

        $sistema = SistemaVeiculo::where('nome', 'Parte elétrica')->firstOrFail();

        $this->assertDatabaseHas('tipos_manutencao', [
            'sistema_veiculo_id' => $sistema->id,
            'nome' => 'Troca da bateria',
            'tipo_periodicidade' => 'tempo',
            'intervalo_meses' => 36,
            'ativo' => true,
        ]);

        $freios = SistemaVeiculo::where('nome', 'Freios')->firstOrFail();
        $this->assertDatabaseHas('tipos_manutencao', [
            'sistema_veiculo_id' => $freios->id,
            'nome' => 'Troca de pastilhas',
        ]);
        $this->assertDatabaseHas('tipos_manutencao', [
            'sistema_veiculo_id' => $freios->id,
            'nome' => 'Troca dos discos de freio',
        ]);
        $this->assertDatabaseMissing('tipos_manutencao', ['nome' => 'Troca de pastilhas ou lonas']);

        $alimentacao = SistemaVeiculo::where('nome', 'Alimentação e combustível')->firstOrFail();
        $this->assertDatabaseHas('tipos_manutencao', ['sistema_veiculo_id' => $alimentacao->id, 'nome' => 'Troca das linhas de combustível', 'ativo' => true]);
        $this->assertDatabaseHas('tipos_manutencao', ['sistema_veiculo_id' => $alimentacao->id, 'nome' => 'Troca da bomba de combustível', 'ativo' => true]);
        $this->assertDatabaseHas('tipos_manutencao', ['sistema_veiculo_id' => $alimentacao->id, 'nome' => 'Limpeza do TBI', 'ativo' => true]);
        $this->assertDatabaseHas('tipos_manutencao', ['sistema_veiculo_id' => $alimentacao->id, 'nome' => 'Troca do filtro de combustível', 'ativo' => true]);

        $estetica = SistemaVeiculo::where('nome', 'Estética e limpeza')->firstOrFail();
        foreach (['Lavagem completa do carro', 'Polimento', 'Troca de insulfilm'] as $servico) {
            $this->assertDatabaseHas('tipos_manutencao', ['sistema_veiculo_id' => $estetica->id, 'nome' => $servico, 'ativo' => true]);
        }
    }

    public function test_cria_novo_sistema_durante_o_cadastro_do_servico_e_permite_busca(): void
    {
        $admin = User::factory()->create(['tipo' => 'administrador', 'ativo' => true]);

        $response = $this->actingAs($admin)->post(route('tipos-manutencao.store'), [
            'sistema_veiculo_id' => 'outro',
            'sistema_custom' => 'Capota e acessórios',
            'nome' => 'Ajuste da capota conversível',
            'descricao' => 'Revisar travas e mecanismo da capota.',
            'tipo_periodicidade' => 'nenhuma',
            'antecedencia_alerta_dias' => 30,
            'antecedencia_alerta_quilometros' => 1000,
            'ativo' => 1,
        ]);

        $response->assertRedirect(route('tipos-manutencao.index'));
        $sistema = SistemaVeiculo::where('nome', 'Capota e acessórios')->firstOrFail();
        $this->assertDatabaseHas('tipos_manutencao', [
            'sistema_veiculo_id' => $sistema->id,
            'nome' => 'Ajuste da capota conversível',
        ]);

        $this->actingAs($admin)
            ->get(route('tipos-manutencao.index', ['busca' => 'Capota']))
            ->assertOk()
            ->assertSee('Ajuste da capota conversível');

        TipoManutencao::create([
            'sistema_veiculo_id' => $sistema->id,
            'nome' => 'Lubrificação das travas',
            'tipo_periodicidade' => 'nenhuma',
            'antecedencia_alerta_dias' => 0,
            'antecedencia_alerta_quilometros' => 0,
            'ativo' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('tipos-manutencao.index', ['busca' => 'Lubrificação']))
            ->assertOk()
            ->assertSee('Lubrificação das travas')
            ->assertDontSee('Ajuste da capota conversível');
    }

    public function test_cadastra_servico_rapidamente_durante_registro_de_manutencao(): void
    {
        $this->seed(OficinaSeeder::class);
        $admin = User::factory()->create(['tipo' => 'administrador', 'ativo' => true]);
        $freios = SistemaVeiculo::where('nome', 'Freios')->firstOrFail();

        $response = $this->actingAs($admin)->postJson(route('manutencoes.servicos.store'), [
            'sistema_veiculo_id' => $freios->id,
            'nome' => 'Troca da bomba de freio',
        ]);

        $response->assertOk()->assertJson(['nome' => 'Troca da bomba de freio', 'sistema' => 'Freios']);
        $this->assertDatabaseHas('tipos_manutencao', [
            'sistema_veiculo_id' => $freios->id,
            'nome' => 'Troca da bomba de freio',
            'ativo' => true,
        ]);
    }
}
