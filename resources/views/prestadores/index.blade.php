@extends('layouts.app')
@section('title','Prestadores e empresas')
@section('content')
<div class="page-head"><div><span class="eyebrow">REDE DE CONFIANÇA</span><h1>Prestadores e empresas</h1><p>Oficinas, lojas e profissionais que cuidam dos veículos.</p></div><a class="btn primary" href="{{route('prestadores.create')}}">＋ Novo prestador</a></div>
<form class="searchbar"><input name="busca" value="{{$busca}}" placeholder="Buscar por nome, local ou pessoa de contato"><button class="btn">Buscar</button></form>
<div class="cards-list">@forelse($prestadores as $prestador)<article class="person-card"><div class="avatar">⌖</div><div class="grow"><div class="provider-title"><strong>{{$prestador->nome}}</strong><span class="badge provider-{{$prestador->atuacao}}">{{['servicos'=>'Serviços','pecas'=>'Peças','ambos'=>'Serviços + peças'][$prestador->atuacao]??'Serviços + peças'}}</span></div><p>{{collect([$prestador->local,$prestador->endereco])->filter()->join(' · ') ?: 'Local não informado'}}</p><small>{{collect([$prestador->pessoa_contato,$prestador->telefone,$prestador->whatsapp])->filter()->join(' · ') ?: 'Contato não informado'}}</small></div><span class="badge {{$prestador->ativo?'success':'muted'}}">{{$prestador->ativo?'Ativo':'Inativo'}}</span><a class="btn" href="{{route('prestadores.edit',$prestador)}}">Editar</a></article>@empty<div class="empty panel"><h3>Nenhum prestador cadastrado</h3><p>Cadastre a primeira oficina, loja ou profissional.</p></div>@endforelse</div>
{{$prestadores->links()}}
@endsection
