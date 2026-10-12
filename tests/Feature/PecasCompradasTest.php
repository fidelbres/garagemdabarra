<?php

namespace Tests\Feature;

use App\Models\PrestadorServico;
use App\Models\Proprietario;
use App\Models\User;
use App\Models\Veiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PecasCompradasTest extends TestCase
{
    use RefreshDatabase;

    public function test_registra_compra_separada_do_servico_e_classifica_fornecedor(): void
    {
        $usuario = User::factory()->create(['tipo' => 'administrador', 'ativo' => true]);
        $proprietario = Proprietario::create(['nome' => 'Colecionador', 'ativo' => true]);
        $veiculo = Veiculo::create(['proprietario_id' => $proprietario->id, 'nome' => 'Opala', 'marca' => 'Chevrolet', 'modelo' => 'Opala', 'ano' => 1978, 'placa' => 'ABC1D23', 'ativo' => true]);
        $fornecedor = PrestadorServico::create(['nome' => 'Loja de Clássicos', 'atuacao' => 'pecas', 'ativo' => true]);

        $this->actingAs($usuario)->post(route('pecas.store'), [
            'veiculo_id' => $veiculo->id,
            'prestador_servico_id' => $fornecedor->id,
            'descricao' => 'Jogo de rodas',
            'marca' => 'Mangels',
            'quantidade' => '4',
            'valor_total' => '2.500,00',
            'data_compra' => '2026-09-26',
        ])->assertRedirect(route('pecas.index'));

        $this->assertDatabaseHas('pecas_compradas', ['veiculo_id' => $veiculo->id, 'descricao' => 'Jogo de rodas', 'valor_total' => 2500]);
        $this->assertDatabaseCount('manutencoes', 0);
        $this->actingAs($usuario)->get(route('pecas.index'))->assertOk()->assertSee('Jogo de rodas')->assertSee('Loja de Clássicos');
        $this->actingAs($usuario)->get(route('veiculos.show', $veiculo))->assertOk()->assertSee('Peças compradas')->assertSee('Jogo de rodas');
        $this->actingAs($usuario)->get(route('prestadores.index'))->assertOk()->assertSee('Peças');
        $this->actingAs($usuario)->get(route('financeiro.index'))
            ->assertOk()
            ->assertSee('Jogo de rodas')
            ->assertSee('R$ 2.500,00');
    }
}
