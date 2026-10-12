<section class="panel alternative-galleries-panel">
    <div class="panel-head">
        <div>
            <span class="eyebrow">OUTROS REGISTROS</span>
            <h2>Galerias alternativas</h2>
            <p>Organize viagens, restaurações, encontros e outros momentos sem misturar com a galeria principal.</p>
        </div>
        @if($edicaoGaleria ?? false)<button class="btn" type="button" data-toggle-gallery-form>＋ Criar galeria</button>@endif
    </div>

    @if($edicaoGaleria ?? false)<form class="alternative-gallery-form" method="POST" action="{{ route('veiculos.galerias.store', $veiculo) }}" hidden>
        @csrf
        <div class="form-grid">
            <label>Nome da galeria *<input required name="nome" maxlength="255" placeholder="Ex.: Restauração 2026"></label>
            <label>Data<input type="date" name="data_registro"></label>
            <label class="span-2">Descrição<textarea name="descricao" rows="2" placeholder="Conte brevemente o que este registro representa"></textarea></label>
        </div>
        <div class="form-actions"><button class="btn primary">Criar e adicionar fotos</button></div>
    </form>@endif

    <div class="alternative-gallery-grid">
        @forelse($veiculo->galerias as $galeria)
            <a href="{{ route('veiculos.galerias.show', [$veiculo, $galeria]) }}" class="alternative-gallery-card">
                @if($capa = $galeria->fotos->first())
                    <img src="{{ $capa->inline_url }}" alt="{{ $galeria->nome }}">
                @else
                    <div class="alternative-gallery-empty">▧</div>
                @endif
                <div>
                    <small>{{ $galeria->data_registro?->format('d/m/Y') ?: 'Sem data' }}</small>
                    <h3>{{ $galeria->nome }}</h3>
                    <p>{{ $galeria->descricao ?: $galeria->fotos->count().' foto(s)' }}</p>
                    <strong>Ver galeria →</strong>
                </div>
            </a>
        @empty
            <div class="empty alternative-gallery-empty-state"><p>Nenhuma galeria alternativa criada.</p></div>
        @endforelse
    </div>
</section>
