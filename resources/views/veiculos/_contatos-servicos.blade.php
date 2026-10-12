@php($servicosComContato = $veiculo->manutencoes->filter(fn($item) => $item->empresa_servico || $item->local_servico || $item->telefone_servico || $item->whatsapp_servico))
@if($servicosComContato->isNotEmpty())
<section class="panel service-contacts-panel">
    <div class="panel-head"><div><span class="eyebrow">ONDE FOI FEITO</span><h2>Contatos dos serviços</h2><p>Empresas e profissionais registrados no histórico deste carro.</p></div></div>
    <div class="service-contacts">
        @foreach($servicosComContato as $registro)
        <article>
            <div><small>{{ $registro->data_manutencao->format('d/m/Y') }} · {{ $registro->tipo?->nome ?? 'Serviço realizado' }}</small><strong>{{ $registro->empresa_servico ?: 'Profissional não informado' }}</strong><span>{{ $registro->local_servico ?: 'Local não informado' }}</span></div>
            <div class="contact-actions">
                @if($registro->telefone_servico)<a class="btn" href="tel:{{$registro->telefone_servico}}">☎ Telefone</a>@endif
                @if($registro->whatsapp_servico)<a class="btn primary" target="_blank" rel="noopener" href="https://wa.me/55{{$registro->whatsapp_servico}}">WhatsApp</a>@endif
            </div>
        </article>
        @endforeach
    </div>
</section>
@endif
