<?php

namespace Tests\Feature;

use App\Models\FotoVeiculo;
use App\Models\Proprietario;
use App\Models\User;
use App\Models\Veiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GaleriasVeiculoTest extends TestCase
{
    use RefreshDatabase;

    public function test_cria_galeria_alternativa_sem_alterar_o_destaque_principal(): void
    {
        Storage::fake('public');
        $usuario = User::factory()->create(['tipo' => 'administrador', 'ativo' => true]);
        $proprietario = Proprietario::create(['nome' => 'Colecionador', 'ativo' => true]);
        $veiculo = Veiculo::create(['proprietario_id' => $proprietario->id, 'nome' => 'Chevette 1976', 'marca' => 'Chevrolet', 'modelo' => 'Chevette', 'ano' => 1976, 'ativo' => true]);
        $principal = FotoVeiculo::create(['veiculo_id' => $veiculo->id, 'arquivo' => 'veiculos/principal.png', 'categoria' => 'geral', 'data_foto' => now(), 'principal' => true]);

        $this->actingAs($usuario)->post(route('veiculos.galerias.store', $veiculo), [
            'nome' => 'Restauração',
            'data_registro' => '2026-09-26',
            'descricao' => 'Etapas da restauração da carroceria.',
        ])->assertRedirect();

        $galeria = $veiculo->galerias()->firstOrFail();
        $this->actingAs($usuario)->post(route('veiculos.galerias.fotos', [$veiculo, $galeria]), [
            'fotos' => [UploadedFile::fake()->createWithContent('restauracao.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='))],
        ])->assertRedirect();

        $fotoAlternativa = $galeria->fotos()->firstOrFail();
        $this->assertFalse($fotoAlternativa->principal);
        $this->assertSame($principal->id, $veiculo->fresh()->fotoPrincipal->id);
        $this->assertDatabaseHas('galerias_veiculos', ['id' => $galeria->id, 'nome' => 'Restauração']);
        Storage::disk('public')->assertExists($fotoAlternativa->arquivo);

        $this->actingAs($usuario)
            ->put(route('veiculos.fotos.destaque', [$veiculo, $fotoAlternativa]), ['posicao_x' => 50, 'posicao_y' => 50])
            ->assertStatus(422);

        $this->actingAs($usuario)->get(route('veiculos.show', $veiculo))
            ->assertOk()
            ->assertSee('Galerias alternativas')
            ->assertSee('Restauração')
            ->assertSee('Editar galeria')
            ->assertDontSee('Selecionar todas')
            ->assertDontSee('Usar como destaque');
        $this->actingAs($usuario)->get(route('veiculos.galeria.edit', $veiculo))
            ->assertOk()
            ->assertSee('Editar galerias')
            ->assertSee('Selecionar todas')
            ->assertSee('Adicionar fotografias à galeria principal')
            ->assertSee('Editar enquadramento do destaque');
        $this->actingAs($usuario)->get(route('veiculos.galerias.show', [$veiculo, $galeria]))->assertOk()->assertSee('Etapas da restauração da carroceria.');

        Storage::disk('public')->put('veiculos/alternativa-2.png', 'imagem');
        $segundaAlternativa = FotoVeiculo::create(['veiculo_id' => $veiculo->id, 'galeria_veiculo_id' => $galeria->id, 'arquivo' => 'veiculos/alternativa-2.png', 'categoria' => 'geral', 'data_foto' => now(), 'principal' => false]);
        $this->actingAs($usuario)
            ->delete(route('veiculos.fotos.destroy-many', $veiculo), ['fotos' => [$fotoAlternativa->id, $segundaAlternativa->id]])
            ->assertRedirect(route('veiculos.galerias.show', [$veiculo, $galeria]));
        $this->assertDatabaseMissing('fotos_veiculos', ['id' => $fotoAlternativa->id]);
        $this->assertDatabaseMissing('fotos_veiculos', ['id' => $segundaAlternativa->id]);
        Storage::disk('public')->assertMissing('veiculos/alternativa-2.png');

        $this->actingAs($usuario)
            ->delete(route('veiculos.fotos.destroy', [$veiculo, $principal]))
            ->assertRedirect(route('veiculos.galeria.edit', $veiculo));
    }
}
