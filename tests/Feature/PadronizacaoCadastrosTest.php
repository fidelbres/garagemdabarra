<?php

namespace Tests\Feature;

use App\Models\MarcaVeiculo;
use App\Models\ModeloVeiculo;
use App\Models\Proprietario;
use App\Models\User;
use App\Models\Veiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PadronizacaoCadastrosTest extends TestCase
{
    use RefreshDatabase;

    public function test_normaliza_documentos_e_cadastra_veiculo_pelo_catalogo(): void
    {
        $admin = User::factory()->create(['tipo' => 'administrador', 'ativo' => true]);
        $this->actingAs($admin)->post(route('proprietarios.store'), ['nome' => 'João da Silva', 'cpf' => '529.982.247-25', 'whatsapp' => '(31) 98765-4321', 'cep' => '30140-071', 'estado' => 'MG', 'ativo' => 1])->assertRedirect();
        $proprietario = Proprietario::firstOrFail();
        $this->assertDatabaseHas('proprietarios', ['cpf' => '52998224725', 'whatsapp' => '31987654321', 'cep' => '30140071']);
        $marca = MarcaVeiculo::create(['nome' => 'Chevrolet', 'ativo' => true]);
        $modelo = ModeloVeiculo::create(['marca_veiculo_id' => $marca->id, 'nome' => 'Opala', 'ativo' => true]);
        $this->actingAs($admin)->post(route('veiculos.store'), ['proprietario_id' => $proprietario->id, 'nome' => 'Diplomata', 'marca_veiculo_id' => $marca->id, 'modelo_veiculo_id' => $modelo->id, 'ano' => 1988, 'ano_modelo' => 1988, 'placa' => 'abc-1d23', 'quilometragem_atual' => 82500, 'ativo' => 1])->assertRedirect();
        $this->assertDatabaseHas('veiculos', ['marca' => 'Chevrolet', 'modelo' => 'Opala', 'placa' => 'ABC1D23', 'ano' => 1988]);
    }

    public function test_rejeita_cpf_invalido(): void
    {
        $admin = User::factory()->create(['tipo' => 'administrador', 'ativo' => true]);
        $this->actingAs($admin)->from(route('proprietarios.create'))->post(route('proprietarios.store'), ['nome' => 'Inválido', 'cpf' => '111.111.111-11', 'ativo' => 1])->assertSessionHasErrors('cpf');
    }

    public function test_cadastra_veiculo_com_primeira_foto_principal(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $admin = User::factory()->create(['tipo' => 'administrador', 'ativo' => true]);
        $proprietario = Proprietario::create(['nome' => 'Alexandre', 'ativo' => true]);

        $response = $this->actingAs($admin)->post(route('veiculos.store'), [
            'proprietario_id' => $proprietario->id,
            'nome' => 'Clássico de teste',
            'marca_veiculo_id' => 'outra',
            'marca_custom' => 'Ford',
            'modelo_veiculo_id' => 'outro',
            'modelo_custom' => 'Maverick',
            'ano' => 1976,
            'placa' => 'ABC1D23',
            'chassi' => 'CURTO123',
            'combustivel' => 'gasolina',
            'cambio' => 'manual',
            'tipo_uso' => 'uso_eventual',
            'possui_4x4' => 1,
            'possui_ar_condicionado' => 1,
            'possui_manual' => 1,
            'numero_motor' => 'MTR-1976-001',
            'quilometragem_atual' => 1000,
            'ativo' => 1,
            'codigo_seguranca_crv' => '12345678901',
            'anexos' => ['crlv' => UploadedFile::fake()->create('crlv.pdf', 120, 'application/pdf')],
            'foto' => UploadedFile::fake()->createWithContent(
                'maverick.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='),
            ),
        ]);

        $veiculo = Veiculo::firstOrFail();
        $foto = $veiculo->fotos()->firstOrFail();

        $response->assertRedirect(route('veiculos.show', $veiculo));
        $this->assertTrue($foto->principal);
        $this->assertSame('CURTO123', $veiculo->chassi);
        $this->assertSame('gasolina', $veiculo->combustivel);
        $this->assertSame('manual', $veiculo->cambio);
        $this->assertSame('uso_eventual', $veiculo->tipo_uso);
        $this->assertTrue($veiculo->possui_4x4);
        $this->assertTrue($veiculo->possui_ar_condicionado);
        $this->assertTrue($veiculo->possui_manual);
        $this->actingAs($admin)->get(route('veiculos.index'))
            ->assertOk()
            ->assertSee('Manual do veículo disponível');
        $this->assertSame('MTR-1976-001', $veiculo->numero_motor);
        Storage::disk('public')->assertExists($foto->arquivo);
        $anexo = $veiculo->anexos()->firstOrFail();
        Storage::disk('local')->assertExists($anexo->arquivo);
        $this->assertSame('crlv', $anexo->tipo);
        $this->actingAs($admin)->put(route('veiculos.fotos.destaque', [$veiculo, $foto]), ['posicao_x' => 35, 'posicao_y' => 62])->assertRedirect();
        $this->assertDatabaseHas('fotos_veiculos', ['id' => $foto->id, 'principal' => true, 'posicao_x' => 35, 'posicao_y' => 62]);
        $pagina = $this->actingAs($admin)->get(route('veiculos.show', $veiculo));
        $urlFoto = Storage::disk('public')->url($foto->arquivo);
        $pagina->assertOk()->assertSee($urlFoto, false);
        $this->assertGreaterThanOrEqual(2, substr_count($pagina->getContent(), $urlFoto), 'A foto deve aparecer no destaque e na galeria.');
    }
}
