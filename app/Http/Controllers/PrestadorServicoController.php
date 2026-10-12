<?php

namespace App\Http\Controllers;

use App\Models\PrestadorServico;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PrestadorServicoController extends Controller
{
    public function index(Request $request)
    {
        $busca = trim((string) $request->input('busca'));
        $prestadores = PrestadorServico::when($busca !== '', fn ($q) => $q->where(fn ($q) => $q->where('nome', 'like', "%{$busca}%")->orWhere('local', 'like', "%{$busca}%")->orWhere('pessoa_contato', 'like', "%{$busca}%")))->orderBy('nome')->paginate(20)->withQueryString();
        return view('prestadores.index', compact('prestadores', 'busca'));
    }

    public function create() { return view('prestadores.form', ['prestador' => new PrestadorServico]); }
    public function store(Request $request) { PrestadorServico::create($this->dados($request)); return redirect()->route('prestadores.index')->with('sucesso', 'Prestador cadastrado.'); }
    public function edit(PrestadorServico $prestador) { return view('prestadores.form', compact('prestador')); }
    public function update(Request $request, PrestadorServico $prestador) { $prestador->update($this->dados($request)); return redirect()->route('prestadores.index')->with('sucesso', 'Prestador atualizado.'); }
    public function destroy(PrestadorServico $prestador) { $prestador->delete(); return back()->with('sucesso', 'Prestador arquivado.'); }

    private function dados(Request $request): array
    {
        $request->merge(['telefone' => $request->telefone ? preg_replace('/\D/', '', $request->telefone) : null, 'whatsapp' => $request->whatsapp ? preg_replace('/\D/', '', $request->whatsapp) : null]);
        return $request->validate(['nome' => ['required', 'string', 'max:255'], 'local' => ['nullable', 'string', 'max:255'], 'endereco' => ['nullable', 'string', 'max:255'], 'telefone' => ['nullable', 'string', 'max:15'], 'whatsapp' => ['nullable', 'string', 'max:15'], 'pessoa_contato' => ['nullable', 'string', 'max:255'], 'atuacao' => ['required', Rule::in(['servicos', 'pecas', 'ambos'])], 'ativo' => ['required', 'boolean']]);
    }
}
