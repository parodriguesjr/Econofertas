@extends('layouts.admin')
@section('title', $supermarket->exists ? 'Editar supermercado' : 'Novo supermercado')
@section('content')
<div class="admin-title"><div><a class="back-link dark" href="{{ route('admin.supermarkets.index') }}">← Supermercados</a><h1>{{ $supermarket->exists ? 'Editar supermercado' : 'Novo supermercado' }}</h1></div></div>
<form class="admin-form" method="POST" action="{{ $supermarket->exists ? route('admin.supermarkets.update', $supermarket) : route('admin.supermarkets.store') }}">@csrf @if($supermarket->exists) @method('PUT') @endif
    <div class="field-grid"><label>Nome da loja<input name="name" value="{{ old('name', $supermarket->name) }}" required>@error('name')<small class="field-error">{{ $message }}</small>@enderror</label><label>Identificador na URL<input name="slug" value="{{ old('slug', $supermarket->slug) }}" placeholder="gerado-automaticamente">@error('slug')<small class="field-error">{{ $message }}</small>@enderror</label></div>
    <label>Descrição<textarea name="description" rows="3" maxlength="500">{{ old('description', $supermarket->description) }}</textarea></label>
    <div class="field-grid"><label>Cor da loja<div class="color-field"><input type="color" name="color" value="{{ old('color', $supermarket->color ?: '#177245') }}"><span>Usada na identificação visual</span></div></label><label class="check panel-check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $supermarket->exists ? $supermarket->is_active : true))><span><strong>Loja ativa</strong><small>Exibir no site público</small></span></label></div>
    <div class="form-actions"><a class="secondary-button" href="{{ route('admin.supermarkets.index') }}">Cancelar</a><button class="button">Salvar supermercado</button></div>
</form>
@endsection
