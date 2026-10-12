@extends('layouts.app')
@section('title', 'Editar galeria de '.$veiculo->nome)
@section('content')
@php($fotosPrincipais = $veiculo->fotos->whereNull('galeria_veiculo_id')->whereNull('manutencao_id')->values())
@php($fotoDestaque = $fotosPrincipais->firstWhere('principal', true) ?? $fotosPrincipais->first())
@php($edicaoGaleria = true)

<div class="page-head gallery-detail-head">
    <div><a class="back" href="{{route('veiculos.show',$veiculo)}}">← {{$veiculo->nome}}</a><span class="eyebrow">ADMINISTRAÇÃO DE FOTOS</span><h1>Editar galerias</h1><p>Adicione fotos, escolha o destaque e organize os álbuns deste veículo.</p></div>
    <div class="actions">@if($fotosPrincipais->isNotEmpty())<button class="btn" type="button" data-gallery-open>Ver em tela cheia</button>@endif<a class="btn primary" href="{{route('veiculos.show',$veiculo)}}">Concluir edição</a></div>
</div>

<section class="panel gallery-panel">
    <div class="panel-head"><div><span class="eyebrow">GALERIA PRINCIPAL</span><h2>Fotos de apresentação</h2><p>Selecione a capa do carro, ajuste o enquadramento ou remova fotografias.</p></div>@if($fotosPrincipais->isNotEmpty())<form id="bulk-delete-main" class="bulk-photo-form" method="POST" action="{{route('veiculos.fotos.destroy-many',$veiculo)}}" onsubmit="return confirm('Excluir todas as fotos selecionadas?')">@csrf @method('DELETE')<button class="btn" type="button" data-select-all>Selecionar todas</button><button class="btn danger" disabled data-bulk-delete><span data-bulk-count>0</span> selecionadas</button></form>@endif</div>
    <div class="gallery" data-selectable-gallery>@foreach($fotosPrincipais as $foto)<figure><label class="gallery-photo-select"><input type="checkbox" name="fotos[]" value="{{$foto->id}}" form="bulk-delete-main"><span>✓</span></label><button class="gallery-card-photo" type="button" data-gallery-open-index="{{$loop->index}}"><img src="{{$foto->inline_url}}" alt="{{$veiculo->nome}}"></button><figcaption><span>{{$foto->principal?'Foto de destaque':($foto->manutencao_id?'Registro de serviço':'Foto do veículo')}}</span><form method="POST" action="{{route('veiculos.fotos.destroy',[$veiculo,$foto])}}" onsubmit="return confirm('Remover esta fotografia?')">@csrf @method('DELETE')<button aria-label="Remover fotografia">×</button></form></figcaption></figure>@endforeach</div>
    <form class="gallery-form" enctype="multipart/form-data" method="POST" action="{{route('veiculos.fotos.store',$veiculo)}}">@csrf<label class="gallery-dropzone"><input required type="file" name="fotos[]" accept="image/*" multiple><span class="gallery-drop-icon">+</span><strong>Adicionar fotografias à galeria principal</strong><small>Selecione até 40 imagens de uma vez</small></label></form>
</section>

@include('veiculos._galerias-alternativas')
@include('veiculos._galeria_modal')
@endsection
