<?php

namespace Tests\Feature;

use App\Models\Manutencao;
use App\Models\Proprietario;
use App\Models\TipoManutencao;
use App\Models\User;
use App\Models\Veiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FluxoManutencaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_registra_manutencao_e_calcula_as_proximas_previsoes(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $proprietario = Proprietario::create(['nome' => 'João da Silva', 'ativo' => true]);
        $veiculo = Veiculo::create(['proprietario_id' => $proprietario->id, 'nome' => 'Opala 1988', 'marca' => 'Chevrolet', 'modelo' => 'Opala Diplomata', 'ano' => 1988, 'quilometragem_atual' => 82500, 'ativo' => true]);
        $tipo = TipoManutencao::create(['nome' => 'Troca de óleo', 'tipo_periodicidade' => 'tempo_ou_quilometragem', 'intervalo_meses' => 6, 'intervalo_quilometros' => 5000, 'antecedencia_alerta_dias' => 30, 'antecedencia_alerta_quilometros' => 1000, 'ativo' => true]);
        $filtro = TipoManutencao::create(['nome' => 'Troca do filtro de ar', 'tipo_periodicidade' => 'nenhuma', 'antecedencia_alerta_dias' => 0, 'antecedencia_alerta_quilometros' => 0, 'ativo' => true]);

        $usuario = User::factory()->create(['tipo' => 'administrador', 'ativo' => true]);
        $this->actingAs($usuario)->post(route('manutencoes.store'), [
            'veiculo_id' => $veiculo->id,
            'tipo_manutencao_ids' => [$tipo->id, $filtro->id],
            'prestador_servico_id' => 'outro',
            'prestador_nome' => 'Casa do Óleo',
            'prestador_local' => 'Vitória - ES',
            'prestador_contato' => 'João',
            'natureza' => 'periodica',
            'data_manutencao' => '2026-08-15',
            'quilometragem' => 82500,
            'valor' => '1.750,50',
            'empresa_servico' => 'Clássicos da Barra',
            'local_servico' => 'Vila Velha - ES',
            'telefone_servico' => '(27) 3333-4444',
            'whatsapp_servico' => '(27) 99999-8888',
            'servico_executado' => 'Óleo e filtro substituídos.',
            'itens' => [
                ['tipo_manutencao_id' => $tipo->id, 'categoria' => 'peca', 'descricao' => 'Óleo do motor', 'quantidade' => 4, 'valor_unitario' => '65,00'],
                ['tipo_manutencao_id' => $filtro->id, 'categoria' => 'peca', 'descricao' => 'Filtro de ar', 'fornecedor_id' => 'outro', 'fornecedor_nome' => 'Auto Peças Imperial', 'quantidade' => 1, 'valor_unitario' => '60,00'],
                ['tipo_manutencao_id' => $filtro->id, 'categoria' => 'servico', 'descricao' => 'Mão de obra', 'quantidade' => 1, 'valor_unitario' => '1.490,50'],
            ],
            'categoria_fotos' => 'antes',
            'fotos' => [UploadedFile::fake()->createWithContent(
                'entrada.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='),
            )],
            'anexos' => [
                UploadedFile::fake()->create('nota-peca.pdf', 180, 'application/pdf'),
                UploadedFile::fake()->createWithContent(
                    'nota-servico.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='),
                ),
            ],
        ])->assertRedirect(route('veiculos.show', $veiculo));

        $manutencao = Manutencao::firstOrFail();
        $this->assertSame('2027-02-15', $manutencao->proxima_data_prevista->format('Y-m-d'));
        $this->assertSame(87500, $manutencao->proxima_quilometragem_prevista);
        $this->assertDatabaseHas('quilometragens', ['veiculo_id' => $veiculo->id, 'quilometragem' => 82500, 'origem' => 'manutencao']);
        $this->assertDatabaseHas('manutencoes', ['id' => $manutencao->id, 'empresa_servico' => 'Casa do Óleo', 'telefone_servico' => '2733334444', 'whatsapp_servico' => '27999998888']);
        $this->assertDatabaseHas('fotos_veiculos', ['veiculo_id' => $veiculo->id, 'manutencao_id' => $manutencao->id, 'categoria' => 'antes']);
        $this->assertDatabaseHas('manutencoes', ['id' => $manutencao->id, 'valor' => 1750.50]);
        $this->assertDatabaseHas('manutencao_tipo_manutencao', ['manutencao_id' => $manutencao->id, 'tipo_manutencao_id' => $tipo->id]);
        $this->assertDatabaseHas('manutencao_tipo_manutencao', ['manutencao_id' => $manutencao->id, 'tipo_manutencao_id' => $filtro->id]);
        $this->assertDatabaseHas('prestadores_servico', ['nome' => 'Casa do Óleo', 'pessoa_contato' => 'João']);
        $this->assertDatabaseHas('itens_manutencao', ['manutencao_id' => $manutencao->id, 'categoria' => 'peca', 'descricao' => 'Óleo do motor', 'valor_total' => 260.00]);
        $fornecedor = \App\Models\PrestadorServico::where('nome', 'Auto Peças Imperial')->firstOrFail();
        $this->assertSame('pecas', $fornecedor->atuacao);
        $this->assertDatabaseHas('itens_manutencao', ['manutencao_id' => $manutencao->id, 'descricao' => 'Filtro de ar', 'fornecedor_id' => $fornecedor->id]);
        $this->assertDatabaseHas('itens_manutencao', ['manutencao_id' => $manutencao->id, 'categoria' => 'servico', 'descricao' => 'Mão de obra', 'valor_total' => 1490.50]);
        $this->assertSame(3561.0, $manutencao->fresh()->custo_total);
        $this->actingAs($usuario)->get(route('pecas.index'))
            ->assertOk()
            ->assertSee('Óleo do motor')
            ->assertSee('Usada no serviço')
            ->assertSee('R$ 260,00');
        $this->actingAs($usuario)->get(route('veiculos.show', $veiculo))
            ->assertOk()
            ->assertSee('Fotos do serviço')
            ->assertSee('R$ 3.561,00')
            ->assertSee('Onde foi feito:')
            ->assertDontSee('Contatos dos serviços');
        $this->assertCount(2, $manutencao->anexos);
        foreach ($manutencao->anexos as $anexo) Storage::disk('local')->assertExists($anexo->arquivo);
        $primeiroAnexo = $manutencao->anexos->first();
        $this->actingAs($usuario)->get(route('manutencoes.edit', $manutencao))
            ->assertOk()
            ->assertSee('form="delete-maintenance-attachment-'.$primeiroAnexo->id.'"', false)
            ->assertSee('Fotos já salvas neste serviço')
            ->assertSee('form="delete-maintenance-photo-'.$manutencao->fotos->first()->id.'"', false)
            ->assertSee('Salvar no histórico');
        $this->actingAs($usuario)->put(route('manutencoes.update', $manutencao), [
            'veiculo_id' => $veiculo->id,
            'tipo_manutencao_ids' => [$tipo->id, $filtro->id],
            'natureza' => 'periodica',
            'data_manutencao' => '2026-08-15',
            'quilometragem' => 82600,
            'valor' => '1.750,50',
            'servico_executado' => 'Óleo e filtros atualizados.',
        ])->assertRedirect(route('veiculos.show', $veiculo));
        $this->assertDatabaseHas('manutencoes', ['id' => $manutencao->id, 'quilometragem' => 82600, 'servico_executado' => 'Óleo e filtros atualizados.']);
        $this->actingAs($usuario)->get(route('financeiro.index'))->assertOk()->assertSee('R$ 1.750,50');

        $veiculo->update(['tipo_uso' => 'colecao']);
        $veiculoDiario = Veiculo::create(['proprietario_id' => $proprietario->id, 'nome' => 'Vitara diário', 'tipo_uso' => 'uso_diario', 'marca' => 'Suzuki', 'modelo' => 'Vitara', 'ano' => 2020, 'quilometragem_atual' => 100000, 'ativo' => true]);
        Manutencao::create(['veiculo_id' => $veiculoDiario->id, 'registrado_por' => $usuario->id, 'natureza' => 'corretiva', 'data_manutencao' => '2026-09-01', 'quilometragem' => 100000, 'valor' => 250, 'servico_executado' => 'Teste de gasto diário.']);

        $this->actingAs($usuario)->get(route('financeiro.index', ['tipos_uso' => ['colecao']]))
            ->assertOk()->assertSee('R$ 1.750,50')->assertDontSee('R$ 2.000,50');
        $this->actingAs($usuario)->get(route('financeiro.index', ['tipos_uso' => ['uso_diario']]))
            ->assertOk()->assertSee('R$ 250,00')->assertDontSee('R$ 1.750,50');
        $this->actingAs($usuario)->get(route('financeiro.index', ['tipos_uso' => ['colecao', 'uso_diario']]))
            ->assertOk()->assertSee('R$ 2.000,50');
    }
}
