@extends('layouts.admin')
@section('title', 'Visão geral')
@section('content')
<div class="admin-title"><div><p class="eyebrow">Painel administrativo</p><h1>Visão geral</h1></div><a class="button" href="{{ route('admin.flyers.create') }}">+ Novo folheto</a></div>
<div class="stat-grid">
    <div class="stat"><span>Supermercados</span><strong>{{ $supermarketsCount }}</strong><a href="{{ route('admin.supermarkets.index') }}">Gerenciar</a></div>
    <div class="stat"><span>Folhetos cadastrados</span><strong>{{ $flyersCount }}</strong><a href="{{ route('admin.flyers.index') }}">Ver todos</a></div>
    <div class="stat"><span>Visíveis ao público</span><strong>{{ $activeFlyersCount }}</strong><a href="{{ route('home') }}" target="_blank">Abrir site</a></div>
</div>
<section class="admin-section"><h2>Ações rápidas</h2><div class="quick-actions"><a href="{{ route('admin.supermarkets.create') }}"><strong>Cadastrar supermercado</strong><span>Adicione uma nova loja à vitrine.</span></a><a href="{{ route('admin.flyers.create') }}"><strong>Publicar folheto</strong><span>Envie um PDF e defina sua validade.</span></a></div></section>
@endsection
