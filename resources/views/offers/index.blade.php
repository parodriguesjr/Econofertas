@extends('layouts.app')
@section('title', 'Ofertas de Nova Odessa - Econofertas')
@section('content')
<section class="offers-head"><div class="container">
    <p class="eyebrow">Nova Odessa/SP</p><h1>Ofertas automáticas</h1>
    <form class="offer-search" method="get"><label><span>Buscar produto</span><input name="q" value="{{ $query }}" placeholder="Ex.: arroz, leite, detergente"></label><button class="button" type="submit">Buscar</button></form>
</div></section>
<section class="market-section"><div class="container">
    <div class="section-heading"><h2>Produtos e encartes</h2><span>{{ $offers->total() }} resultados</span></div>
    <div class="offer-grid">
        @forelse($offers as $offer)
            <article class="offer-card"><a href="{{ $offer->source_url }}" target="_blank" rel="noopener noreferrer">
                <div class="offer-image"><img src="{{ $offer->image_url }}" alt="{{ $offer->name }}" loading="lazy"></div>
                <div class="offer-body"><small style="--market-color: {{ $offer->supermarket->color }}">{{ $offer->supermarket->name }} · Nova Odessa</small><h3>{{ $offer->name }}</h3>
                    @if($offer->price !== null)<strong>R$ {{ number_format($offer->price, 2, ',', '.') }}</strong>@else<span>Ver encarte oficial</span>@endif
                </div>
            </a></article>
        @empty
            <div class="empty-state"><strong>Nenhuma oferta encontrada.</strong><p>Tente outro nome de produto.</p></div>
        @endforelse
    </div>{{ $offers->links() }}
</div></section>
@endsection
