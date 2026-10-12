@extends('layouts.app')
@section('title', $galeria->nome)
@section('content')
<div class="page-head gallery-detail-head">
    <div>
        <a class="back" href="{{ route('veiculos.show', $veiculo) }}">← {{ $veiculo->nome }}</a>
        <span class="eyebrow">GALERIA ALTERNATIVA</span>
        <h1>{{ $galeria->nome }}</h1>
        <p>@if($galeria->data_registro){{ $galeria->data_registro->format('d/m/Y') }} · @endif{{ $galeria->descricao ?: 'Registro fotográfico do veículo.' }}</p>
    </div>
    <div class="actions">
        @if($galeria->fotos->isNotEmpty())<button class="btn primary" type="button" data-gallery-open>▣ Ver em tela cheia</button>@endif
        @if($galeria->fotos->isNotEmpty())<form id="bulk-delete-alternative" class="bulk-photo-form" method="POST" action="{{route('veiculos.fotos.destroy-many',$veiculo)}}" onsubmit="return confirm('Excluir todas as fotos selecionadas?')">@csrf @method('DELETE')<button class="btn" type="button" data-select-all>Selecionar todas</button><button class="btn danger" disabled data-bulk-delete><span data-bulk-count>0</span> selecionadas</button></form>@endif
    </div>
</div>

<section class="panel alternative-gallery-management">
    <details class="gallery-metadata-editor">
        <summary>Editar nome, data e descrição</summary>
        <form class="form" method="POST" action="{{ route('veiculos.galerias.update', [$veiculo, $galeria]) }}">
            @csrf @method('PUT')
            <div class="form-grid">
                <label>Nome *<input required name="nome" value="{{ $galeria->nome }}"></label>
                <label>Data<input type="date" name="data_registro" value="{{ $galeria->data_registro?->format('Y-m-d') }}"></label>
                <label class="span-2">Descrição<textarea name="descricao" rows="2">{{ $galeria->descricao }}</textarea></label>
            </div>
            <div class="form-actions"><button class="btn primary">Salvar informações</button></div>
        </form>
    </details>

    <div class="gallery alternative-gallery-photos" data-selectable-gallery>
        @foreach($galeria->fotos as $foto)
            <figure>
                <label class="gallery-photo-select"><input type="checkbox" name="fotos[]" value="{{ $foto->id }}" form="bulk-delete-alternative"><span>✓</span></label>
                <button class="gallery-card-photo" type="button" data-gallery-open-index="{{ $loop->index }}"><img src="{{ $foto->inline_url }}" alt="{{ $galeria->nome }}"></button>
                <figcaption><span>Foto {{ $loop->iteration }}</span><form method="POST" action="{{ route('veiculos.fotos.destroy', [$veiculo, $foto]) }}" onsubmit="return confirm('Remover esta fotografia?')">@csrf @method('DELETE')<button>×</button></form></figcaption>
            </figure>
        @endforeach
    </div>

    <form class="gallery-form" enctype="multipart/form-data" method="POST" action="{{ route('veiculos.galerias.fotos', [$veiculo, $galeria]) }}">
        @csrf
        <label>Fotografias<input required type="file" name="fotos[]" accept="image/*" multiple></label>
    </form>

    <div class="danger-zone">
        <form method="POST" action="{{ route('veiculos.galerias.destroy', [$veiculo, $galeria]) }}" onsubmit="return confirm('Excluir esta galeria e todas as fotos dela?')">@csrf @method('DELETE')<button class="btn danger">Excluir galeria</button></form>
    </div>
</section>

@if($galeria->fotos->isNotEmpty())
<dialog class="vehicle-gallery-modal" id="vehicle-gallery-modal">
    <button type="button" class="gallery-modal-close" data-gallery-close aria-label="Fechar">×</button>
    <div class="gallery-modal-head"><span class="eyebrow">{{ mb_strtoupper($galeria->nome) }}</span><h2>{{ $veiculo->nome }}</h2><span data-gallery-counter></span></div>
    @php($fotosPrincipais = $veiculo->fotos->whereNull('galeria_veiculo_id')->whereNull('manutencao_id')->values())
    @php($fotoDestaque = $fotosPrincipais->firstWhere('principal', true) ?? $fotosPrincipais->first())
    <nav class="gallery-albums" aria-label="Álbuns de {{ $veiculo->nome }}">
        @if($fotosPrincipais->isNotEmpty())
            <a href="{{ route('veiculos.show', $veiculo) }}?visualizar=galeria">
                <img src="{{ $fotoDestaque->inline_url }}" alt="">
                <span><strong>Apresentação</strong><small>{{ $fotosPrincipais->count() }} {{ $fotosPrincipais->count() === 1 ? 'foto' : 'fotos' }}</small></span>
            </a>
        @endif
        @foreach($veiculo->galerias as $album)
            <a class="{{ $album->is($galeria) ? 'active' : '' }}" href="{{ route('veiculos.galerias.show', [$veiculo, $album]) }}?visualizar=galeria" @if($album->is($galeria))aria-current="page"@endif>
                @if($album->fotos->first())<img src="{{ $album->fotos->first()->inline_url }}" alt="">@endif
                <span><strong>{{ $album->nome }}</strong><small>@if($album->data_registro){{ $album->data_registro->format('d/m/Y') }} · @endif{{ $album->fotos->count() }} {{ $album->fotos->count() === 1 ? 'foto' : 'fotos' }}</small></span>
            </a>
        @endforeach
    </nav>
    <div class="gallery-stage">
        @foreach($galeria->fotos as $foto)
            <article class="gallery-slide" data-gallery-slide><img src="{{ $foto->inline_url }}" alt="{{ $galeria->nome }}"><div class="gallery-slide-caption"><span>{{ $galeria->nome }}</span>@if($galeria->data_registro)<b>{{ $galeria->data_registro->format('d/m/Y') }}</b>@endif</div></article>
        @endforeach
        <button type="button" class="gallery-nav prev" data-gallery-prev aria-label="Foto anterior">‹</button>
        <button type="button" class="gallery-nav next" data-gallery-next aria-label="Próxima foto">›</button>
    </div>
    <div class="gallery-thumbs">@foreach($galeria->fotos as $foto)<button type="button" data-gallery-thumb="{{ $loop->index }}"><img src="{{ $foto->inline_url }}" alt=""></button>@endforeach</div>
</dialog>
@endif
@endsection
