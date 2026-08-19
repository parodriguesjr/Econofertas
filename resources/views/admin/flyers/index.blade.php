@extends('layouts.admin')
@section('title', 'Folhetos')
@section('content')
<div class="admin-title"><div><p class="eyebrow">Publicações</p><h1>Folhetos</h1></div><a class="button" href="{{ route('admin.flyers.create') }}">+ Novo folheto</a></div>
<div class="table-wrap"><table><thead><tr><th>Folheto</th><th>Supermercado</th><th>Validade</th><th>Status</th><th class="actions">Ações</th></tr></thead><tbody>
@forelse($flyers as $flyer)<tr><td><strong>{{ $flyer->title }}</strong><small>PDF</small></td><td><span class="color-dot" style="background:{{ $flyer->supermarket->color }}"></span>{{ $flyer->supermarket->name }}</td><td>{{ $flyer->ends_at?->format('d/m/Y') ?? 'Sem prazo' }}</td><td><span class="badge {{ $flyer->is_published ? 'on' : 'off' }}">{{ $flyer->is_published ? 'Publicado' : 'Rascunho' }}</span></td><td class="actions"><a href="{{ route('admin.flyers.edit', $flyer) }}">Editar</a><form method="POST" action="{{ route('admin.flyers.destroy', $flyer) }}" onsubmit="return confirm('Excluir este folheto?')">@csrf @method('DELETE')<button class="danger-link">Excluir</button></form></td></tr>
@empty<tr><td colspan="5">Nenhum folheto cadastrado.</td></tr>@endforelse
</tbody></table></div><div class="pagination">{{ $flyers->links() }}</div>
@endsection
