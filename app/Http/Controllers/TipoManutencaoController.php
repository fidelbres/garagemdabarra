<?php
namespace App\Http\Controllers;
use App\Models\SistemaVeiculo;
use App\Models\TipoManutencao;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class TipoManutencaoController extends Controller
{
    public function index(Request $request)
    {
        $busca = trim((string) $request->input('busca'));

        $tipos = TipoManutencao::with('sistema')
            ->when($busca !== '', function ($query) use ($busca) {
                $query->where(function ($query) use ($busca) {
                    $query->where('nome', 'like', "%{$busca}%")
                        ->orWhere('descricao', 'like', "%{$busca}%")
                        ->orWhereHas('sistema', fn ($sistema) => $sistema->where('nome', 'like', "%{$busca}%"));
                });
            })
            ->orderBy('nome')
            ->paginate(30)
            ->withQueryString();

        return view('tipos-manutencao.index', compact('tipos', 'busca'));
    }
    public function create(){return $this->form(new TipoManutencao);}
    public function store(Request $r){TipoManutencao::create($this->dados($r));return redirect()->route('tipos-manutencao.index')->with('sucesso','Serviço criado.');}
    public function edit(TipoManutencao $tipo){return $this->form($tipo);}
    public function update(Request $r,TipoManutencao $tipo){$tipo->update($this->dados($r));return redirect()->route('tipos-manutencao.index')->with('sucesso','Serviço atualizado.');}
    public function destroy(TipoManutencao $tipo){$tipo->delete();return back()->with('sucesso','Tipo arquivado.');}
    private function form(TipoManutencao $tipo){$sistemas=SistemaVeiculo::where('ativo',true)->orderBy('ordem')->orderBy('nome')->get();return view('tipos-manutencao.form',compact('tipo','sistemas'));}
    private function dados(Request $r): array
    {
        $novoSistema = $r->input('sistema_veiculo_id') === 'outro';

        $dados = $r->validate([
            'sistema_veiculo_id' => $novoSistema
                ? ['required', Rule::in(['outro'])]
                : ['required', 'exists:sistemas_veiculo,id'],
            'sistema_custom' => [
                $novoSistema ? 'required' : 'nullable',
                'string',
                'max:120',
                Rule::unique('sistemas_veiculo', 'nome'),
            ],
            'nome' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
            'tipo_periodicidade' => ['required', Rule::in(['nenhuma','tempo','quilometragem','tempo_ou_quilometragem'])],
            'intervalo_meses' => ['nullable', 'integer', 'min:1'],
            'intervalo_quilometros' => ['nullable', 'integer', 'min:1'],
            'antecedencia_alerta_dias' => ['required', 'integer', 'min:0'],
            'antecedencia_alerta_quilometros' => ['required', 'integer', 'min:0'],
            'ativo' => ['required', 'boolean'],
        ]);

        if ($novoSistema) {
            $sistema = SistemaVeiculo::create([
                'nome' => trim($dados['sistema_custom']),
                'icone' => '◇',
                'ordem' => ((int) SistemaVeiculo::max('ordem')) + 1,
                'ativo' => true,
            ]);
            $dados['sistema_veiculo_id'] = $sistema->id;
        }

        unset($dados['sistema_custom']);

        return $dados;
    }
}
