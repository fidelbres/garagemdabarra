<?php

namespace App\Http\Controllers;

use App\Models\Proprietario;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProprietarioController extends Controller
{
    public function index(Request $request)
    {
        $busca = $request->string('busca');
        $proprietarios = Proprietario::withCount('veiculos')
            ->when($busca->isNotEmpty(), fn ($q) => $q->where(fn ($q) => $q->where('nome', 'like', "%{$busca}%")->orWhere('email', 'like', "%{$busca}%")->orWhere('whatsapp', 'like', "%{$busca}%")))
            ->orderBy('nome')->paginate(12)->withQueryString();
        return view('proprietarios.index', compact('proprietarios'));
    }

    public function create() { return view('proprietarios.form', ['proprietario' => new Proprietario]); }
    public function store(Request $request) { $p = Proprietario::create($this->dados($request)); return redirect()->route('proprietarios.show', $p)->with('sucesso', 'Colecionador cadastrado.'); }
    public function show(Proprietario $proprietario) { $proprietario->load(['veiculos' => fn ($q) => $q->with('fotoPrincipal')->withCount('manutencoes')->orderBy('nome')]); return view('proprietarios.show', compact('proprietario')); }
    public function edit(Proprietario $proprietario) { return view('proprietarios.form', compact('proprietario')); }
    public function update(Request $request, Proprietario $proprietario) { $proprietario->update($this->dados($request, $proprietario)); return redirect()->route('proprietarios.show', $proprietario)->with('sucesso', 'Cadastro atualizado.'); }
    public function destroy(Proprietario $proprietario) { $proprietario->delete(); return redirect()->route('proprietarios.index')->with('sucesso', 'Colecionador arquivado.'); }

    private function dados(Request $request, ?Proprietario $proprietario = null): array
    {
        foreach (['telefone','whatsapp','cpf','cep'] as $campo) {
            if ($request->filled($campo)) $request->merge([$campo => preg_replace('/\D/', '', $request->input($campo))]);
        }
        return $request->validate([
            'nome' => ['required','string','max:255'], 'telefone' => ['nullable','digits_between:10,11'], 'whatsapp' => ['nullable','digits:11'],
            'email' => ['nullable','email','max:255'], 'cpf' => ['nullable','digits:11', function($a,$v,$fail){if(!$this->cpfValido($v))$fail('O CPF informado é inválido.');}, Rule::unique('proprietarios')->ignore($proprietario)],
            'data_nascimento' => ['nullable','date'], 'endereco' => ['nullable','string','max:255'], 'cidade' => ['nullable','string','max:100'],
            'estado' => ['nullable',Rule::in(['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'])], 'cep' => ['nullable','digits:8'], 'preferencia_contato' => ['nullable', Rule::in(['whatsapp','email','telefone','aplicativo'])],
            'observacoes' => ['nullable','string'], 'ativo' => ['required','boolean'],
        ]);
    }
    private function cpfValido(string $cpf): bool { if(strlen($cpf)!==11||preg_match('/^(\d)\1{10}$/',$cpf))return false;for($t=9;$t<11;$t++){$s=0;for($i=0;$i<$t;$i++)$s+=(int)$cpf[$i]*(($t+1)-$i);$d=((10*$s)%11)%10;if((int)$cpf[$t]!==$d)return false;}return true; }
}
