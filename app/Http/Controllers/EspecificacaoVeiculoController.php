<?php

namespace App\Http\Controllers;

use App\Models\EspecificacaoVeiculo;
use App\Models\TipoManutencao;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EspecificacaoVeiculoController extends Controller
{
    private const CATEGORIAS = ['pneus', 'oleos_fluidos', 'filtros', 'ignicao', 'bateria', 'correias', 'freios', 'outros'];

    public function create(Veiculo $veiculo)
    {
        return $this->form($veiculo, new EspecificacaoVeiculo(['veiculo_id' => $veiculo->id]));
    }

    public function store(Request $request, Veiculo $veiculo)
    {
        $veiculo->especificacoes()->create($this->dados($request));
        return redirect()->route('veiculos.show', $veiculo)->with('sucesso', 'Aplicação técnica cadastrada.');
    }

    public function edit(Veiculo $veiculo, EspecificacaoVeiculo $especificacao)
    {
        abort_unless($especificacao->veiculo_id === $veiculo->id, 404);
        return $this->form($veiculo, $especificacao);
    }

    public function update(Request $request, Veiculo $veiculo, EspecificacaoVeiculo $especificacao)
    {
        abort_unless($especificacao->veiculo_id === $veiculo->id, 404);
        $especificacao->update($this->dados($request));
        return redirect()->route('veiculos.show', $veiculo)->with('sucesso', 'Aplicação técnica atualizada.');
    }

    public function destroy(Veiculo $veiculo, EspecificacaoVeiculo $especificacao)
    {
        abort_unless($especificacao->veiculo_id === $veiculo->id, 404);
        $especificacao->delete();
        return back()->with('sucesso', 'Aplicação técnica removida.');
    }

    private function form(Veiculo $veiculo, EspecificacaoVeiculo $especificacao)
    {
        $tipos = TipoManutencao::with('sistema')->where('ativo', true)->orderBy('nome')->get();
        return view('veiculos.especificacao-form', ['veiculo' => $veiculo, 'especificacao' => $especificacao, 'tipos' => $tipos, 'categorias' => self::CATEGORIAS]);
    }

    private function dados(Request $request): array
    {
        return $request->validate([
            'tipo_manutencao_id' => ['nullable', 'exists:tipos_manutencao,id'],
            'categoria' => ['required', Rule::in(self::CATEGORIAS)],
            'item' => ['required', 'string', 'max:255'],
            'especificacao_recomendada' => ['nullable', 'string'],
            'marca_preferida' => ['nullable', 'string', 'max:255'],
            'referencia' => ['nullable', 'string', 'max:255'],
            'referencias_equivalentes' => ['nullable', 'string'],
            'marca_instalada' => ['nullable', 'string', 'max:255'],
            'modelo_instalado' => ['nullable', 'string', 'max:255'],
            'referencia_instalada' => ['nullable', 'string', 'max:255'],
            'instalado_em' => ['nullable', 'date'],
            'quilometragem_instalacao' => ['nullable', 'integer', 'min:0'],
            'observacoes' => ['nullable', 'string'],
        ]);
    }
}
