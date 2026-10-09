@extends('layouts.admin')
@section('title', 'Folhetos')
@section('content')
<div class="admin-title"><div><p class="eyebrow">Publicações</p><h1>Folhetos</h1></div><a class="button" href="{{ route('admin.flyers.create') }}">+ Novo folheto</a></div>
<div class="table-wrap"><table><thead><tr><th>Folheto</th><th>Supermercado</th><th>Validade</th><th>Status</th><th class="actions">Ações</th></tr></thead><tbody>
@forelse($flyers as $flyer)<tr><td><strong>{{ $flyer->title }}</strong><small>PDF</small></td><td><span class="color-dot" style="background:{{ $flyer->supermarket->color }}"></span>{{ $flyer->supermarket->name }}</td><td>{{ $flyer->ends_at?->format('d/m/Y') ?? 'Sem prazo' }}</td><td><span class="badge {{ $flyer->is_published ? 'on' : 'off' }}">{{ $flyer->is_published ? 'Publicado' : 'Rascunho' }}</span></td><td class="actions"><div class="flyer-actions"><button type="button" class="preview-link action-link" data-preview-url="{{ route('admin.flyers.preview', $flyer) }}" data-preview-title="{{ $flyer->title }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="2.5"/></svg><span>Visualizar</span></button><a class="action-link" href="{{ route('admin.flyers.edit', $flyer) }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 16.5-.8 4.3 4.3-.8L19 8.5 15.5 5 4 16.5Z"/><path d="m14.5 6 3.5 3.5"/></svg><span>Editar</span></a><form method="POST" action="{{ route('admin.flyers.destroy', $flyer) }}" onsubmit="return confirm('Excluir este folheto?')">@csrf @method('DELETE')<button class="danger-link">Excluir</button></form></div></td></tr>
@empty<tr><td colspan="5">Nenhum folheto cadastrado.</td></tr>@endforelse
</tbody></table></div><div class="pagination">{{ $flyers->links() }}</div>
<dialog class="flyer-preview-dialog" aria-labelledby="preview-title">
    <div class="flyer-preview-header"><strong id="preview-title">Visualização do folheto</strong><button type="button" class="preview-close" aria-label="Fechar visualização">×</button></div>
    <iframe title="Prévia do folheto" class="flyer-preview-frame"></iframe>
</dialog>
<script>
    const previewDialog = document.querySelector('.flyer-preview-dialog');
    const previewFrame = previewDialog.querySelector('.flyer-preview-frame');
    const previewTitle = previewDialog.querySelector('#preview-title');
    document.querySelectorAll('.preview-link').forEach((button) => button.addEventListener('click', () => {
        previewTitle.textContent = button.dataset.previewTitle;
        previewFrame.src = button.dataset.previewUrl;
        previewDialog.showModal();
    }));
    previewDialog.querySelector('.preview-close').addEventListener('click', () => previewDialog.close());
    previewDialog.addEventListener('close', () => { previewFrame.src = ''; });
    previewDialog.addEventListener('click', (event) => { if (event.target === previewDialog) previewDialog.close(); });
</script>
@endsection
