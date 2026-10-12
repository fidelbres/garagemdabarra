<section class="panel complementary-panel">
    <div class="panel-head"><div><span class="eyebrow">FICHA COMPLETA</span><h2>Informações complementares</h2><p>Identificação detalhada, compra, procedência e localização do veículo.</p></div><a href="{{ route('veiculos.edit',$veiculo) }}">Editar dados →</a></div>
    <div class="complementary-grid">
        <div><small>Modelo completo / versão</small><strong>{{ $veiculo->nome_modelo_completo ?: $veiculo->marca.' '.$veiculo->modelo }}</strong></div>
        <div><small>Motor</small><strong>{{ $veiculo->versao_motor ?: 'Não informado' }}</strong></div>
        <div><small>Combustível</small><strong>{{ ['gasolina'=>'Gasolina','etanol'=>'Álcool / etanol','flex'=>'Flex','diesel'=>'Diesel','gnv'=>'Gás natural (GNV)','eletrico'=>'Elétrico','hibrido'=>'Híbrido','outro'=>'Outro'][$veiculo->combustivel] ?? 'Não informado' }}</strong></div>
        <div><small>Câmbio</small><strong>{{ ['manual'=>'Manual','automatico'=>'Automático','automatizado'=>'Automatizado','cvt'=>'CVT','outro'=>'Outro'][$veiculo->cambio] ?? 'Não informado' }}</strong></div>
        <div><small>Tipo de uso</small><strong>{{ ['uso_diario'=>'Uso diário','uso_eventual'=>'Uso eventual','colecao'=>'Veículo de coleção'][$veiculo->tipo_uso] ?? 'Veículo de coleção' }}</strong></div>
        <div><small>Características</small><strong>{{ collect([$veiculo->possui_4x4 ? '4x4' : null, $veiculo->possui_ar_condicionado ? 'Ar-condicionado' : null, $veiculo->possui_manual ? 'Manual do veículo' : null])->filter()->join(' · ') ?: 'Nenhuma informada' }}</strong></div>
        <div><small>Número do motor</small><strong>{{ $veiculo->numero_motor ?: 'Não informado' }}</strong></div>
        <div><small>Cor</small><strong>{{ $veiculo->cor ?: 'Não informada' }}</strong></div>
        <div><small>Ano / modelo</small><strong>{{ $veiculo->ano ?: '—' }} / {{ $veiculo->ano_modelo ?: '—' }}</strong></div>
        <div><small>Renavam</small><strong>{{ $veiculo->renavam ?: 'Não informado' }}</strong></div>
        <div><small>Data da compra</small><strong>{{ $veiculo->data_compra?->format('d/m/Y') ?: 'Não informada' }}</strong></div>
        <div><small>Local da compra</small><strong>{{ $veiculo->local_compra ?: 'Não informado' }}</strong></div>
        <div><small>Local anterior / procedência</small><strong>{{ $veiculo->local_anterior ?: 'Não informado' }}</strong></div>
        <div><small>Local atual</small><strong>{{ $veiculo->local_atual ?: 'Não informado' }}</strong></div>
        <div><small>Cidade atual</small><strong>{{ collect([$veiculo->cidade_atual,$veiculo->estado_atual])->filter()->join(' / ') ?: 'Não informada' }}</strong></div>
    </div>
</section>
