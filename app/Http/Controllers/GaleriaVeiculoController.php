<?php

namespace App\Http\Controllers;

use App\Models\FotoVeiculo;
use App\Models\GaleriaVeiculo;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GaleriaVeiculoController extends Controller
{
    public function store(Request $request, Veiculo $veiculo)
    {
        $galeria = $veiculo->galerias()->create($this->dados($request));
        return redirect()->route('veiculos.galerias.show', [$veiculo, $galeria])->with('sucesso', 'Galeria criada. Agora adicione as fotografias.');
    }

    public function show(Veiculo $veiculo, GaleriaVeiculo $galeria)
    {
        abort_unless($galeria->veiculo_id === $veiculo->id, 404);
        $galeria->load('fotos');
        $veiculo->load(['fotos', 'galerias.fotos']);
        return view('veiculos.galeria', compact('veiculo', 'galeria'));
    }

    public function update(Request $request, Veiculo $veiculo, GaleriaVeiculo $galeria)
    {
        abort_unless($galeria->veiculo_id === $veiculo->id, 404);
        $galeria->update($this->dados($request));
        return back()->with('sucesso', 'Galeria atualizada.');
    }

    public function destroy(Veiculo $veiculo, GaleriaVeiculo $galeria)
    {
        abort_unless($galeria->veiculo_id === $veiculo->id, 404);
        foreach ($galeria->fotos as $foto) Storage::disk('public')->delete($foto->arquivo);
        $galeria->delete();
        return redirect()->route('veiculos.show', $veiculo)->with('sucesso', 'Galeria alternativa removida.');
    }

    public function fotos(Request $request, Veiculo $veiculo, GaleriaVeiculo $galeria)
    {
        abort_unless($galeria->veiculo_id === $veiculo->id, 404);
        $request->validate(['fotos' => ['required', 'array', 'max:40'], 'fotos.*' => ['image', 'max:5120']]);
        foreach ($request->file('fotos') as $foto) {
            $arquivo = $foto->store("veiculos/{$veiculo->id}/galerias/{$galeria->id}", 'public');
            FotoVeiculo::create(['veiculo_id' => $veiculo->id, 'galeria_veiculo_id' => $galeria->id, 'arquivo' => $arquivo, 'data_foto' => $galeria->data_registro ?? now(), 'categoria' => 'geral', 'principal' => false]);
        }
        return back()->with('sucesso', count($request->file('fotos')).' foto(s) adicionada(s).');
    }

    private function dados(Request $request): array
    {
        return $request->validate(['nome' => ['required', 'string', 'max:255'], 'data_registro' => ['nullable', 'date'], 'descricao' => ['nullable', 'string']]);
    }
}
