@extends('layouts.app')
@section('title', $supermarket->name . ' - Econofertas')
@section('content')
<section class="market-hero" style="--market-color: {{ $supermarket->color }}">
    <div class="container">
        <a class="back-link" href="{{ route('home') }}">← Todos os supermercados</a>
        <div class="market-title">
            <span class="market-initial large">{{ mb_substr($supermarket->name, 0, 1) }}</span>
            <div><p>Folhetos de ofertas</p><h1>{{ $supermarket->name }}</h1></div>
        </div>
    </div>
</section>
<section class="market-section">
    <div class="container">
        <div class="section-heading"><h2>Folhetos disponíveis</h2><span>Atualizados pela loja</span></div>
        <div class="flyer-grid">
            @forelse($flyers as $flyer)
                <article class="flyer-card">
                    <div class="flyer-preview" style="--market-color: {{ $supermarket->color }}">
                        <span>OFERTAS</span><strong>{{ $supermarket->name }}</strong><small>Folheto digital</small>
                    </div>
                    <div class="flyer-body">
                        <h3>{{ $flyer->title }}</h3>
                        <p>@if($flyer->ends_at) Válido até {{ $flyer->ends_at->format('d/m/Y') }} @else Consulte as condições no folheto @endif</p>
                        <a class="button" href="{{ route('flyers.view', $flyer) }}" target="_blank" rel="noopener">Abrir folheto <span>↗</span></a>
                    </div>
                </article>
            @empty
                <div class="empty-state"><strong>Não há folhetos ativos para esta loja.</strong><p>Novas ofertas serão publicadas em breve.</p></div>
            @endforelse
        </div>
    </div>
</section>
@endsection
