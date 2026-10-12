@extends('layouts.app')
@section('title','Catálogo de serviços')
@section('content')
<div class="page-head"><div><span class="eyebrow">CONFIGURAÇÃO TÉCNICA</span><h1>Sistemas e serviços</h1><p>O sistema organiza a área do veículo; o serviço define o trabalho e sua periodicidade.</p></div><a class="btn primary" href="{{ route('tipos-manutencao.create') }}">＋ Novo serviço</a></div>
<form class="searchbar" method="GET" action="{{ route('tipos-manutencao.index') }}">
    <input name="busca" value="{{ $busca }}" placeholder="Buscar por sistema, serviço ou descrição" aria-label="Buscar sistemas e serviços">
    @if($busca !== '')<a class="btn" href="{{ route('tipos-manutencao.index') }}">Limpar</a>@endif
    <button class="btn primary" type="submit">Buscar</button>
</form>
<section class="panel table-wrap"><table><thead><tr><th>Sistema</th><th>Serviço</th><th>Regra</th><th>Alerta</th><th>Status</th><th></th></tr></thead><tbody>@forelse($tipos as $t)<tr><td><span class="system-label"><b>{{$t->sistema?->icone}}</b>{{$t->sistema?->nome ?? 'Não classificado'}}</span></td><td><strong>{{ $t->nome }}</strong><small>{{ Str::limit($t->descricao,70) }}</small></td><td>@if($t->tipo_periodicidade==='nenhuma')Sem periodicidade @else @if($t->intervalo_meses)A cada {{ $t->intervalo_meses }} meses @endif @if($t->intervalo_meses&&$t->intervalo_quilometros) ou @endif @if($t->intervalo_quilometros){{ number_format($t->intervalo_quilometros,0,',','.') }} km @endif @endif</td><td>{{ $t->antecedencia_alerta_dias }} dias / {{ number_format($t->antecedencia_alerta_quilometros,0,',','.') }} km</td><td><span class="badge {{$t->ativo?'success':'muted'}}">{{$t->ativo?'Ativo':'Inativo'}}</span></td><td><a href="{{ route('tipos-manutencao.edit',$t) }}">Editar →</a></td></tr>@empty<tr><td colspan="6" class="empty-table">Nenhum sistema ou serviço encontrado para “{{ $busca }}”.</td></tr>@endforelse</tbody></table></section>{{ $tipos->links() }}
@endsection
