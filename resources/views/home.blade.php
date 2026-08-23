@extends('layouts.app')
@section('title', 'Econofertas - Ofertas de supermercados')
@section('content')
<section class="intro">
    <div class="container">
        <p class="eyebrow">Economize na próxima compra</p>
        <h1>Qual supermercado você quer consultar?</h1>
        <p class="lead">Escolha uma loja para ver os folhetos e ofertas disponíveis.</p>
        <a class="button offer-entry" href="{{ route('offers.index') }}">Ver ofertas por produto</a>
    </div>
</section>
<section class="market-section">
    <div class="container">
        <div class="section-heading">
            <h2>Supermercados</h2>
            <span>{{ $supermarkets->count() }} {{ $supermarkets->count() === 1 ? 'loja' : 'lojas' }}</span>
        </div>
        <div class="market-grid">
            @forelse($supermarkets as $supermarket)
                <a class="market-card" href="{{ route('supermarkets.show', $supermarket) }}" style="--market-color: {{ $supermarket->color }}">
                    <span class="market-initial">{{ mb_substr($supermarket->name, 0, 1) }}</span>
                    <span class="market-info">
                        <strong>{{ $supermarket->name }}</strong>
                        <small>{{ $supermarket->description ?: 'Confira as ofertas disponíveis.' }}</small>
                    </span>
                    <span class="market-count">{{ $supermarket->flyers_count }} {{ $supermarket->flyers_count === 1 ? 'folheto' : 'folhetos' }}</span>
                    <span class="arrow" aria-hidden="true">→</span>
                </a>
            @empty
                <div class="empty-state">
                    <strong>Nenhum supermercado disponível no momento.</strong>
                    <p>Volte em breve para conferir novas ofertas.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection
