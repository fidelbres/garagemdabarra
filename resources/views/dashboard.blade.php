@extends('layouts.app')
@section('title', 'Visão geral')
@section('content')
<div class="page-head"><div><span class="eyebrow">MINHA COLEÇÃO</span><h1>Bem-vindo à garagem.</h1><p>Acompanhe seus carros, os serviços realizados e os próximos cuidados.</p></div><a class="btn primary" href="{{ route('manutencoes.create') }}">＋ Registrar serviço</a></div>
<div class="stats">
    <div class="stat"><span class="stat-icon gold">♙</span><div><small>Colecionadores</small><strong>{{ $proprietarios }}</strong><a href="{{ route('proprietarios.index') }}">Ver pessoas →</a></div></div>
    <div class="stat"><span class="stat-icon blue">◇</span><div><small>Carros na garagem</small><strong>{{ $veiculos }}</strong><a href="{{ route('veiculos.index') }}">Ver garagem →</a></div></div>
    <div class="stat"><span class="stat-icon green">⚒</span><div><small>Serviços neste mês</small><strong>{{ $manutencoesMes }}</strong><a href="{{ route('manutencoes.index') }}">Ver histórico →</a></div></div>
    <div class="stat"><span class="stat-icon red">!</span><div><small>Atenções em 30 dias</small><strong>{{ $alertas }}</strong><span>Preventivas próximas</span></div></div>
</div>
<div class="grid-two">
    <section class="panel"><div class="panel-head"><div><h2>Próximas manutenções</h2><p>Prazos vencidos ou nos próximos 30 dias</p></div></div>
        @forelse($proximas as $m)<a class="timeline-row" href="{{ route('veiculos.show',$m->veiculo) }}"><span class="date-box {{ $m->proxima_data_prevista->isPast() ? 'late':'' }}"><strong>{{ $m->proxima_data_prevista->format('d') }}</strong><small>{{ mb_strtoupper($m->proxima_data_prevista->translatedFormat('M')) }}</small></span><div><strong>{{ $m->tipo?->nome ?? 'Manutenção' }}</strong><span>{{ $m->veiculo->nome }} · {{ $m->veiculo->proprietario->nome }}</span></div><span class="badge {{ $m->proxima_data_prevista->isPast() ? 'danger':'warning' }}">{{ $m->proxima_data_prevista->isPast() ? 'Vencida' : $m->proxima_data_prevista->diffForHumans() }}</span></a>@empty<div class="empty"><span>✓</span><h3>Tudo em dia</h3><p>Nenhuma manutenção prevista para os próximos 30 dias.</p></div>@endforelse
    </section>
    <section class="panel"><div class="panel-head"><div><h2>Atividade recente</h2><p>Últimos serviços registrados</p></div><a href="{{ route('manutencoes.index') }}">Ver tudo</a></div>
        @forelse($recentes as $m)<a class="activity-row" href="{{ route('veiculos.show',$m->veiculo) }}"><span class="activity-icon">⚒</span><div><strong>{{ $m->tipo?->nome ?? 'Serviço avulso' }}</strong><span>{{ $m->veiculo->nome }} · {{ number_format($m->quilometragem,0,',','.') }} km</span></div><time>{{ $m->data_manutencao->format('d/m/Y') }}</time></a>@empty<div class="empty"><span>⚒</span><h3>Comece o histórico</h3><p>Registre o primeiro serviço realizado em um dos seus carros.</p></div>@endforelse
    </section>
</div>
@endsection
