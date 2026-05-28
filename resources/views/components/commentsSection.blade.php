{{-- Section commentaires --}}
@if($order->comments->isNotEmpty())
<div class="mb-3">
    <label class="text-muted small fw-bold text-uppercase mb-2">Commentaires</label>
    <div style="max-height: 200px; overflow-y: auto;">
        @foreach($order->comments->sortByDesc('created_at') as $comment)
        <div class="d-flex align-items-start mb-2 p-2 rounded" style="background: #f8fafc;">
            <div class="me-2 mt-1">
                <div style="width: 28px; height: 28px; border-radius: 50%; background: var(--navy); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 600;">
                    {{ substr($comment->author?->getFirstName() ?? 'S', 0, 1) }}{{ substr($comment->author?->getLastName() ?? '', 0, 1) }}
                </div>
            </div>
            <div style="flex: 1;">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="small fw-bold">{{ $comment->author ? $comment->author->getFirstName() . ' ' . $comment->author->getLastName() : 'Système' }}</span>
                    <small class="text-muted">{{ $comment->created_at->format('d/m/Y H:i') }}</small>
                </div>
                <p class="mb-0 small mt-1">{{ $comment->content }}</p>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif
