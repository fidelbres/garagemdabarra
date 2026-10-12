@php($rotulosAnexos=['crlv'=>'CRLV','consulta_historico'=>'Consulta de histórico veicular','laudo_vistoria'=>'Laudo de vistoria','crv_atpve'=>'CRV / ATPV-e'])
<section class="panel documents-panel">
    <div class="panel-head"><div><span class="eyebrow">DOCUMENTAÇÃO</span><h2>Documentos do veículo</h2><p>Arquivos da compra, propriedade e vistoria.</p></div><a href="{{route('veiculos.edit',$veiculo)}}">Gerenciar documentos →</a></div>
    <div class="document-list">
        @foreach($rotulosAnexos as $tipo=>$rotulo)
        @php($anexo=$veiculo->anexos->firstWhere('tipo',$tipo))
        <div class="document-item"><span class="document-icon">▤</span><div><strong>{{$rotulo}}</strong><small>{{$anexo?$anexo->nome_original:'Não anexado'}}</small></div>@if($anexo)<a class="btn" href="{{route('veiculos.anexos.download',[$veiculo,$anexo])}}">Abrir</a><form method="POST" action="{{route('veiculos.anexos.destroy',[$veiculo,$anexo])}}" onsubmit="return confirm('Remover este documento?')">@csrf @method('DELETE')<button class="document-remove" title="Remover">×</button></form>@endif</div>
        @endforeach
    </div>
    @if($veiculo->codigo_seguranca_crv)<div class="crv-security"><small>CÓDIGO DE SEGURANÇA DO CRV</small><code>{{$veiculo->codigo_seguranca_crv}}</code></div>@endif
</section>
