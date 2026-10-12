@if($fotosPrincipais->isNotEmpty())
<dialog class="vehicle-gallery-modal" id="vehicle-gallery-modal">
    <button type="button" class="gallery-modal-close" data-gallery-close aria-label="Fechar">×</button>
    <div class="gallery-modal-head"><span class="eyebrow">GALERIA</span><h2>{{$veiculo->nome}}</h2><span data-gallery-counter></span></div>
    <nav class="gallery-albums" aria-label="Álbuns de {{ $veiculo->nome }}">
        <a class="active" href="{{ route('veiculos.show', $veiculo) }}?visualizar=galeria" aria-current="page">
            @if($fotoDestaque)<img src="{{ $fotoDestaque->inline_url }}" alt="">@endif
            <span><strong>Apresentação</strong><small>{{ $fotosPrincipais->count() }} {{ $fotosPrincipais->count() === 1 ? 'foto' : 'fotos' }}</small></span>
        </a>
        @foreach($veiculo->galerias as $album)
            <a href="{{ route('veiculos.galerias.show', [$veiculo, $album]) }}?visualizar=galeria">
                @if($album->fotos->first())<img src="{{ $album->fotos->first()->inline_url }}" alt="">@endif
                <span><strong>{{ $album->nome }}</strong><small>@if($album->data_registro){{ $album->data_registro->format('d/m/Y') }} · @endif{{ $album->fotos->count() }} {{ $album->fotos->count() === 1 ? 'foto' : 'fotos' }}</small></span>
            </a>
        @endforeach
    </nav>
    <div class="gallery-stage">
        @foreach($fotosPrincipais as $foto)
        <article class="gallery-slide" data-gallery-slide data-photo-id="{{$foto->id}}" data-principal="{{$foto->principal?'1':'0'}}">
            <img src="{{$foto->inline_url}}" alt="{{$veiculo->nome}}">
            <div class="gallery-slide-caption"><span>{{$foto->manutencao_id?'Registro de serviço':'Foto do veículo'}}</span>@if($foto->principal)<b>Foto de destaque</b>@endif</div>
            @if($edicaoGaleria ?? false)<div class="gallery-slide-tools">
                @if($foto->principal)
                    <button class="btn" type="button" data-framing-toggle>Editar enquadramento do destaque</button>
                    <form class="framing-form" hidden method="POST" action="{{route('veiculos.fotos.destaque',[$veiculo,$foto])}}">@csrf @method('PUT')
                        <p class="framing-help">Ajuste o ponto principal da foto e confira os formatos reais antes de salvar.</p>
                        <div class="framing-preview-grid"><figure class="framing-preview card-format"><img src="{{$foto->inline_url}}" alt="Prévia no cartão" style="object-position:{{$foto->posicao_x}}% {{$foto->posicao_y}}%"><figcaption>Cartão da garagem</figcaption></figure><figure class="framing-preview hero-format"><img src="{{$foto->inline_url}}" alt="Prévia no cabeçalho" style="object-position:{{$foto->posicao_x}}% {{$foto->posicao_y}}%"><figcaption>Cabeçalho</figcaption></figure><figure class="framing-preview compact-format"><img src="{{$foto->inline_url}}" alt="Prévia compacta" style="object-position:{{$foto->posicao_x}}% {{$foto->posicao_y}}%"><figcaption>Prévia de serviço</figcaption></figure></div>
                        <label>Ponto focal horizontal <input type="range" min="0" max="100" name="posicao_x" value="{{$foto->posicao_x}}"></label>
                        <label>Ponto focal vertical <input type="range" min="0" max="100" name="posicao_y" value="{{$foto->posicao_y}}"></label>
                        <button class="btn primary">Salvar enquadramento</button>
                    </form>
                @else
                    <form method="POST" action="{{route('veiculos.fotos.destaque',[$veiculo,$foto])}}">@csrf @method('PUT')
                        <input type="hidden" name="posicao_x" value="{{$foto->posicao_x ?? 50}}"><input type="hidden" name="posicao_y" value="{{$foto->posicao_y ?? 50}}">
                        <button class="btn primary">Usar como destaque</button>
                    </form>
                @endif
            </div>@endif
        </article>
        @endforeach
        <button type="button" class="gallery-nav prev" data-gallery-prev aria-label="Foto anterior">‹</button>
        <button type="button" class="gallery-nav next" data-gallery-next aria-label="Próxima foto">›</button>
    </div>
    <div class="gallery-thumbs">@foreach($fotosPrincipais as $foto)<button type="button" data-gallery-thumb="{{$loop->index}}"><img src="{{$foto->inline_url}}" alt=""></button>@endforeach</div>
</dialog>
@endif
