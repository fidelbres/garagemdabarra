@extends('layouts.app')
@section('title',$tipo->exists?'Editar serviço':'Novo serviço')
@section('content')
<div class="page-head compact"><div><a class="back" href="{{ route('tipos-manutencao.index') }}">← Catálogo</a><span class="eyebrow">SISTEMA E SERVIÇO</span><h1>{{ $tipo->exists?'Editar serviço':'Novo serviço' }}</h1><p>Associe o trabalho ao sistema do veículo e defina quando ele deve ser recomendado novamente.</p></div></div>
<form class="panel form" method="POST" action="{{ $tipo->exists?route('tipos-manutencao.update',$tipo):route('tipos-manutencao.store') }}">@csrf @if($tipo->exists)@method('PUT')@endif
<div class="form-section"><div class="form-grid">
<label>Sistema do veículo *<select required name="sistema_veiculo_id" id="service-system-select"><option value="">Selecione</option>@foreach($sistemas as $sistema)<option value="{{$sistema->id}}" @selected(old('sistema_veiculo_id',$tipo->sistema_veiculo_id)==$sistema->id)>{{$sistema->icone}} {{$sistema->nome}}</option>@endforeach<option value="outro" @selected(old('sistema_veiculo_id')==='outro')>＋ Outro / cadastrar novo sistema</option></select></label>
<label id="service-system-custom-wrap" hidden>Nome do novo sistema *<input name="sistema_custom" id="service-system-custom" value="{{ old('sistema_custom') }}" placeholder="Ex.: Capota e acessórios"></label>
<label>Nome do serviço *<input required name="nome" value="{{ old('nome',$tipo->nome) }}"></label>
<label class="span-2">Descrição<textarea name="descricao" rows="3">{{ old('descricao',$tipo->descricao) }}</textarea></label>
<label>Regra de periodicidade *<select name="tipo_periodicidade"><option value="nenhuma" @selected(old('tipo_periodicidade',$tipo->tipo_periodicidade)==='nenhuma')>Sem periodicidade</option><option value="tempo" @selected(old('tipo_periodicidade',$tipo->tipo_periodicidade)==='tempo')>Por tempo</option><option value="quilometragem" @selected(old('tipo_periodicidade',$tipo->tipo_periodicidade)==='quilometragem')>Por quilometragem</option><option value="tempo_ou_quilometragem" @selected(old('tipo_periodicidade',$tipo->tipo_periodicidade)==='tempo_ou_quilometragem')>Tempo ou km, o que ocorrer primeiro</option></select></label>
<label>Status<select name="ativo"><option value="1" @selected(old('ativo',$tipo->ativo??1)==1)>Ativo</option><option value="0" @selected(old('ativo',$tipo->ativo??1)==0)>Inativo</option></select></label>
<label>Intervalo em meses<input min="1" type="number" name="intervalo_meses" value="{{ old('intervalo_meses',$tipo->intervalo_meses) }}"></label>
<label>Intervalo em quilômetros<input min="1" type="number" name="intervalo_quilometros" value="{{ old('intervalo_quilometros',$tipo->intervalo_quilometros) }}"></label>
<label>Alertar com antecedência de (dias)<input required min="0" type="number" name="antecedencia_alerta_dias" value="{{ old('antecedencia_alerta_dias',$tipo->antecedencia_alerta_dias??30) }}"></label>
<label>Alertar com antecedência de (km)<input required min="0" type="number" name="antecedencia_alerta_quilometros" value="{{ old('antecedencia_alerta_quilometros',$tipo->antecedencia_alerta_quilometros??1000) }}"></label>
</div></div><div class="form-actions"><a class="btn" href="{{ route('tipos-manutencao.index') }}">Cancelar</a><button class="btn primary">Salvar serviço</button></div></form>
@endsection
