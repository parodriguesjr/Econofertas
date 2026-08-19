@extends('layouts.admin')
@section('title', $flyer->exists ? 'Editar folheto' : 'Novo folheto')
@section('content')
<div class="admin-title"><div><a class="back-link dark" href="{{ route('admin.flyers.index') }}">← Folhetos</a><h1>{{ $flyer->exists ? 'Editar folheto' : 'Publicar novo folheto' }}</h1></div></div>
<form class="admin-form" method="POST" enctype="multipart/form-data" action="{{ $flyer->exists ? route('admin.flyers.update', $flyer) : route('admin.flyers.store') }}">@csrf @if($flyer->exists) @method('PUT') @endif
    <div class="field-grid"><label>Supermercado<select name="supermarket_id" required><option value="">Selecione uma loja</option>@foreach($supermarkets as $market)<option value="{{ $market->id }}" @selected(old('supermarket_id', $flyer->supermarket_id) == $market->id)>{{ $market->name }}</option>@endforeach</select>@error('supermarket_id')<small class="field-error">{{ $message }}</small>@enderror</label><label>Título do folheto<input name="title" value="{{ old('title', $flyer->title) }}" placeholder="Ofertas da semana" required>@error('title')<small class="field-error">{{ $message }}</small>@enderror</label></div>
    <label>Arquivo PDF<input class="file-input" type="file" name="pdf" accept="application/pdf" @required(!$flyer->exists)><small>{{ $flyer->exists ? 'Deixe em branco para manter o arquivo atual.' : 'PDF com até 20 MB.' }}</small>@error('pdf')<small class="field-error">{{ $message }}</small>@enderror</label>
    <div class="field-grid"><label>Início da exibição<input type="date" name="starts_at" value="{{ old('starts_at', $flyer->starts_at?->format('Y-m-d')) }}"></label><label>Fim da exibição<input type="date" name="ends_at" value="{{ old('ends_at', $flyer->ends_at?->format('Y-m-d')) }}">@error('ends_at')<small class="field-error">{{ $message }}</small>@enderror</label></div>
    <label class="check panel-check"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $flyer->exists ? $flyer->is_published : true))><span><strong>Publicar folheto</strong><small>Disponibilizar ao público durante o período definido</small></span></label>
    <div class="form-actions"><a class="secondary-button" href="{{ route('admin.flyers.index') }}">Cancelar</a><button class="button">{{ $flyer->exists ? 'Salvar alterações' : 'Publicar folheto' }}</button></div>
</form>
@endsection
