@extends('layouts.app')
@section('title',$prestador->exists?'Editar prestador':'Novo prestador')
@section('content')
<div class="page-head compact"><div><a class="back" href="{{route('prestadores.index')}}">← Prestadores</a><span class="eyebrow">PRESTADOR / EMPRESA</span><h1>{{$prestador->exists?'Editar cadastro':'Novo cadastro'}}</h1><p>Cadastre somente os dados necessários para localizar e contatar o prestador.</p></div></div>
<form class="panel form" method="POST" action="{{$prestador->exists?route('prestadores.update',$prestador):route('prestadores.store')}}">@csrf @if($prestador->exists)@method('PUT')@endif<div class="form-section"><div class="form-grid">
<label class="span-2">Nome da empresa ou profissional *<input required name="nome" value="{{old('nome',$prestador->nome)}}"></label>
<label>Local / cidade<input name="local" value="{{old('local',$prestador->local)}}" placeholder="Ex.: Vitória — ES"></label>
<label>Pessoa de contato<input name="pessoa_contato" value="{{old('pessoa_contato',$prestador->pessoa_contato)}}"></label>
<label class="span-2">Endereço<input name="endereco" value="{{old('endereco',$prestador->endereco)}}"></label>
<label>Telefone<input data-mask="phone" name="telefone" value="{{old('telefone',$prestador->telefone)}}"></label>
<label>WhatsApp<input data-mask="phone" name="whatsapp" value="{{old('whatsapp',$prestador->whatsapp)}}"></label>
<label>Atuação *<select required name="atuacao"><option value="servicos" @selected(old('atuacao',$prestador->atuacao??'ambos')==='servicos')>Serviços</option><option value="pecas" @selected(old('atuacao',$prestador->atuacao??'ambos')==='pecas')>Peças e acessórios</option><option value="ambos" @selected(old('atuacao',$prestador->atuacao??'ambos')==='ambos')>Serviços e peças</option></select></label>
<label>Status<select name="ativo"><option value="1" @selected(old('ativo',$prestador->ativo??1)==1)>Ativo</option><option value="0" @selected(old('ativo',$prestador->ativo??1)==0)>Inativo</option></select></label>
</div></div><div class="form-actions"><a class="btn" href="{{route('prestadores.index')}}">Cancelar</a><button class="btn primary">Salvar prestador</button></div></form>
@endsection
