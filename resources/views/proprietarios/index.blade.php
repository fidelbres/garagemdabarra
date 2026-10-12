@extends('layouts.app')
@section('title','Proprietários')
@section('content')
<div class="page-head"><div><span class="eyebrow">PESSOAS E COLEÇÕES</span><h1>Colecionadores</h1><p>Pessoas responsáveis pelos veículos cadastrados na garagem.</p></div><a class="btn primary" href="{{ route('proprietarios.create') }}">＋ Novo colecionador</a></div>
<form class="searchbar"><input name="busca" value="{{ request('busca') }}" placeholder="Buscar por nome, e-mail ou WhatsApp"><button class="btn">Buscar</button></form>
<div class="cards-list">@forelse($proprietarios as $p)<a class="person-card" href="{{ route('proprietarios.show',$p) }}"><span class="avatar">{{ mb_strtoupper(mb_substr($p->nome,0,2)) }}</span><div class="grow"><h3>{{ $p->nome }}</h3><p>{{ $p->whatsapp ?: ($p->telefone ?: 'Telefone não informado') }} @if($p->email) · {{ $p->email }} @endif</p></div><div class="metric"><strong>{{ $p->veiculos_count }}</strong><small>{{ Str::plural('veículo',$p->veiculos_count) }}</small></div><span class="badge {{ $p->ativo?'success':'muted' }}">{{ $p->ativo?'Ativo':'Inativo' }}</span><span class="arrow">›</span></a>@empty<div class="empty panel"><h3>Nenhum colecionador encontrado</h3><p>Cadastre a primeira pessoa para começar a organizar a garagem.</p></div>@endforelse</div>
{{ $proprietarios->links() }}
@endsection
