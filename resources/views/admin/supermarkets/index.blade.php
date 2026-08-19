@extends('layouts.admin')
@section('title', 'Supermercados')
@section('content')
<div class="admin-title"><div><p class="eyebrow">Catálogo</p><h1>Supermercados</h1></div><a class="button" href="{{ route('admin.supermarkets.create') }}">+ Novo supermercado</a></div>
<div class="table-wrap"><table><thead><tr><th>Loja</th><th>Status</th><th>Folhetos</th><th class="actions">Ações</th></tr></thead><tbody>
@forelse($supermarkets as $supermarket)<tr><td><span class="color-dot" style="background:{{ $supermarket->color }}"></span><strong>{{ $supermarket->name }}</strong><small>/{{ $supermarket->slug }}</small></td><td><span class="badge {{ $supermarket->is_active ? 'on' : 'off' }}">{{ $supermarket->is_active ? 'Ativo' : 'Oculto' }}</span></td><td>{{ $supermarket->flyers_count }}</td><td class="actions"><a href="{{ route('admin.supermarkets.edit', $supermarket) }}">Editar</a><form method="POST" action="{{ route('admin.supermarkets.destroy', $supermarket) }}" onsubmit="return confirm('Excluir este supermercado e todos os seus folhetos?')">@csrf @method('DELETE')<button class="danger-link">Excluir</button></form></td></tr>
@empty<tr><td colspan="4">Nenhum supermercado cadastrado.</td></tr>@endforelse
</tbody></table></div>
@endsection
