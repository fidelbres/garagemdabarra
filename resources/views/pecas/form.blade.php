@extends('layouts.app')
@section('title',$peca->exists?'Editar compra':'Registrar peça comprada')
@section('content')
<div class="page-head compact"><div><a class="back" href="{{route('pecas.index')}}">← Peças compradas</a><span class="eyebrow">COMPRA AVULSA</span><h1>{{$peca->exists?'Editar compra':'Registrar peça ou acessório'}}</h1><p>Guarde a compra separadamente; ela não precisa estar ligada a uma manutenção.</p></div></div>
<form class="panel form" method="POST" action="{{$peca->exists?route('pecas.update',$peca):route('pecas.store')}}">@csrf @if($peca->exists)@method('PUT')@endif
<div class="form-section"><div class="form-grid">
<label>Veículo *<select required name="veiculo_id"><option value="">Selecione</option>@foreach($veiculos as $veiculo)<option value="{{$veiculo->id}}" @selected(old('veiculo_id',$peca->veiculo_id)==$veiculo->id)>{{$veiculo->nome}} — {{$veiculo->proprietario->nome}}</option>@endforeach</select></label>
<label>Data da compra *<input required type="date" name="data_compra" value="{{old('data_compra',$peca->data_compra?->format('Y-m-d')??today()->format('Y-m-d'))}}"></label>
<label class="span-2">Peça ou acessório *<input required name="descricao" value="{{old('descricao',$peca->descricao)}}" placeholder="Ex.: Jogo de rodas, retrovisor, carburador"></label>
<label>Marca<input name="marca" value="{{old('marca',$peca->marca)}}"></label><label>Código / referência<input name="codigo" value="{{old('codigo',$peca->codigo)}}"></label>
<label>Quantidade *<input required inputmode="decimal" name="quantidade" value="{{old('quantidade',$peca->quantidade??1)}}"></label><label>Valor total (R$)<input data-mask="currency" inputmode="decimal" name="valor_total" value="{{old('valor_total',$peca->valor_total)}}" placeholder="0,00"></label>
<label class="span-2">Fornecedor / loja<select name="prestador_servico_id"><option value="">Não informar</option>@foreach($fornecedores as $fornecedor)<option value="{{$fornecedor->id}}" @selected(old('prestador_servico_id',$peca->prestador_servico_id)==$fornecedor->id)>{{$fornecedor->nome}}{{$fornecedor->local?' — '.$fornecedor->local:''}}</option>@endforeach</select><small>Cadastre lojas em “Prestadores e empresas” com atuação em peças.</small></label>
<label class="span-2">Observações<textarea name="observacoes" rows="4">{{old('observacoes',$peca->observacoes)}}</textarea></label>
</div></div><div class="form-actions"><a class="btn" href="{{route('pecas.index')}}">Cancelar</a><button class="btn primary">Salvar compra</button></div></form>
@endsection
