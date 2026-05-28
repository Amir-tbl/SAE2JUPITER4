@use(Database\Seeders\Status)

{{-- Modal de détails de commande (lecture seule) --}}
<div class="modal fade" id="orderModal{{ $orderId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            {{-- HEADER --}}
            <div class="modal-header border-0 text-white" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); border-radius: var(--card-radius) var(--card-radius) 0 0;">
                <div>
                    <h5 class="modal-title fw-bold mb-1">#{{ $order->getOrderNumber() }} - {{ $order->getTitle() }}</h5>
                    <span class="badge {{ $order->getStatus()->getBadgeClass() }}">{{ $order->getStatus()->getLabel() }}</span>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                {{-- MESSAGES FLASH --}}
                @if (session()->exists('orderError-'.$orderId))
                    <div class="alert alert-danger">
                        {{session()->get('orderError-'.$orderId)}}
                    </div>
                @endif
                @if (session()->exists('orderSuccess'))
                    <div class="alert alert-success">
                        {{session('orderSuccess')}}
                    </div>
                @endif


                {{-- =================================================================== --}}
                {{-- DETAILS DE LA COMMANDE --}}
                {{-- =================================================================== --}}
                    <div id="viewPart">
                        {{-- Demandeur --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="text-muted small fw-bold text-uppercase">Demandeur</label>
                                <p class="mb-0 fw-semibold">{{ $order->author ? $order->author->getFirstName() . ' ' . $order->author->getLastName() : '-' }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small fw-bold text-uppercase">Departement</label>
                                <p class="mb-0">{{ $order->getDepartment()->getName() }}</p>
                            </div>
                        </div>

                        {{-- Fournisseur --}}
                        <div class="mb-3">
                            <label class="text-muted small fw-bold text-uppercase">Fournisseur</label>
                            <p class="mb-0">{{ $order->supplier?->getCompanyName() ?? 'Non renseigne' }}</p>
                        </div>

                        {{-- Description --}}
                        <div class="mb-3">
                            <label class="text-muted small fw-bold text-uppercase">Description</label>
                            <div class="p-2 bg-light rounded border">
                                {{ $order->getDescription() }}
                            </div>
                        </div>

                        {{-- Montants HT / TTC --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="text-muted small fw-bold text-uppercase">Montant HT</label>
                                @php
                                    $totalHt = $order->articles->sum(fn($a) => $a->quantity * $a->unit_price);
                                @endphp
                                <p class="mb-0 fs-5">{{ number_format($totalHt, 2, ',', ' ') }} &euro;</p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small fw-bold text-uppercase">Montant TTC</label>
                                <p class="mb-0 fs-5 fw-bold" style="color: var(--navy);">{{ number_format($order->total_ttc ?? 0, 2, ',', ' ') }} &euro;</p>
                            </div>
                        </div>

                        {{-- N suivi / Reference devis --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="text-muted small fw-bold text-uppercase">Reference devis</label>
                                <p class="mb-0">{{ $order->getQuoteNumber() ?? 'Non renseigne' }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small fw-bold text-uppercase">N° Commande</label>
                                <p class="mb-0 fw-bold">#{{ $order->getOrderNumber() }}</p>
                            </div>
                        </div>

                        {{-- Dates commande / livraison --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="text-muted small fw-bold text-uppercase">Date de commande</label>
                                <p class="mb-0">{{ $order->getCreationDate() }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small fw-bold text-uppercase">Date livraison souhaitee</label>
                                <p class="mb-0">{{ $order->desired_delivery_date ? \Carbon\Carbon::parse($order->desired_delivery_date)->format('d/m/Y') : '-' }}</p>
                            </div>
                        </div>

                        {{-- Lieu livraison --}}
                        @if($order->delivery_location)
                        <div class="mb-3">
                            <label class="text-muted small fw-bold text-uppercase">Lieu de livraison</label>
                            <p class="mb-0 fw-semibold">{{ $order->delivery_location }}</p>
                        </div>
                        @endif

                        {{-- Receveur --}}
                        @if($order->receiver_name)
                        <div class="mb-3">
                            <label class="text-muted small fw-bold text-uppercase">Receptionne par</label>
                            <p class="mb-0 fw-semibold">{{ $order->receiver_name }}</p>
                        </div>
                        @endif

                        {{-- ARTICLES --}}
                        @if($order->articles->isNotEmpty())
                        <div class="mb-3">
                            <label class="text-muted small fw-bold text-uppercase mb-2">Articles commandes</label>
                            <div class="table-responsive">
                                <table class="table table-sm mb-0" style="background: rgba(183,157,137,0.07);">
                                    <thead style="background: rgba(255,255,255,0.38);">
                                        <tr>
                                            <th>Designation</th>
                                            <th class="text-end">Qte</th>
                                            <th class="text-end">PU HT</th>
                                            <th class="text-end">TVA</th>
                                            <th class="text-end">Total TTC</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($order->articles as $article)
                                        <tr>
                                            <td>{{ $article->designation }}</td>
                                            <td class="text-end">{{ $article->quantity }}</td>
                                            <td class="text-end">{{ number_format($article->unit_price, 2, ',', ' ') }} &euro;</td>
                                            <td class="text-end">{{ $article->vat_rate }}%</td>
                                            <td class="text-end fw-bold">{{ number_format($article->total_ttc, 2, ',', ' ') }} &euro;</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot style="background: rgba(255,255,255,0.38);">
                                        <tr>
                                            <td colspan="4" class="text-end fw-bold">Total TTC</td>
                                            <td class="text-end fw-bold" style="color: var(--navy);">{{ number_format($order->total_ttc, 2, ',', ' ') }} &euro;</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        @endif

                        {{-- PIECES JOINTES --}}
                        <div class="mb-3">
                            <label class="text-muted small fw-bold text-uppercase mb-2">Pieces jointes</label>
                            <div class="d-flex gap-2 flex-wrap">
                                @php
                                    $hasDocs = $order->getAttribute('path_quote') || $order->getAttribute('path_purchase_order') || $order->getAttribute('path_delivery_note');
                                @endphp

                                @if(!$hasDocs)
                                    <span class="text-muted fst-italic">Aucun document</span>
                                @else
                                    @if($order->getAttribute('path_quote'))
                                        <a href="{{ route('orders.download', ['id' => $order->getId(), 'type' => 'quote']) }}" target="_blank" class="btn btn-sm btn-outline-dark">
                                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5"/><path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1z"/></svg>
                                            Devis
                                        </a>
                                    @endif
                                    @if($order->getAttribute('path_purchase_order'))
                                        <a href="{{ route('orders.download', ['id' => $order->getId(), 'type' => 'purchase_order']) }}" target="_blank" class="btn btn-sm btn-outline-dark">
                                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M10.854 7.854a.5.5 0 0 0-.708-.708L7.5 9.793 6.354 8.646a.5.5 0 1 0-.708.708l1.5 1.5a.5.5 0 0 0 .708 0z"/><path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1z"/></svg>
                                            Bon de commande
                                        </a>
                                    @endif
                                    @if($order->getAttribute('path_delivery_note'))
                                        <a href="{{ route('orders.download', ['id' => $order->getId(), 'type' => 'delivery_note']) }}" target="_blank" class="btn btn-sm btn-outline-dark">
                                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2zm3.564 1.426L5.596 5 8 5.961 14.154 3.5zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923z"/></svg>
                                            Bon de livraison
                                        </a>
                                    @endif
                                @endif
                            </div>
                        </div>

                        {{-- BC SIGNE --}}
                        @if($order->getAttribute('path_purchase_order') && $order->getStatus()->value === \Database\Seeders\Status::BON_DE_COMMANDE_SIGNE->value)
                        <div class="mb-3">
                            <label class="text-muted small fw-bold text-uppercase mb-2">Bon de commande signe</label>
                            <div class="d-flex align-items-center gap-2">
                                <svg width="16" height="16" fill="var(--badge-green)" viewBox="0 0 16 16"><path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/></svg>
                                <span class="text-success fw-semibold small">BC signe par le directeur</span>
                            </div>
                        </div>
                        @endif

                        {{-- COMMENTAIRES --}}
                        @if($order->comments->isNotEmpty())
                        <div class="mb-3">
                            <label class="text-muted small fw-bold text-uppercase mb-2">Commentaires</label>
                            @foreach($order->comments as $comment)
                            <div class="border-start border-3 ps-3 mb-2" style="border-color: var(--taupe) !important;">
                                <p class="mb-0 small">{{ $comment->content }}</p>
                                <small class="text-muted">
                                    {{ $comment->author ? $comment->author->getFirstName() . ' ' . $comment->author->getLastName() : 'Systeme' }}
                                    &middot; {{ $comment->created_at->format('d/m/Y H:i') }}
                                </small>
                            </div>
                            @endforeach
                        </div>
                        @endif

                        {{-- TIMELINE --}}
                        <div class="mb-3">
                            <label class="text-muted small fw-bold text-uppercase mb-2">Historique</label>

                            @if($logs->isEmpty())
                                <p class="text-muted fst-italic">Aucun historique disponible.</p>
                            @else
                                <div class="timeline-container" style="max-height: 300px; overflow-y: auto;">
                                    @foreach($logs as $log)
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="me-3 mt-1">
                                                @switch($log->type ?? 'status_change')
                                                    @case('created')
                                                        <span class="badge bg-success rounded-circle p-2">
                                                            <svg width="14" height="14" fill="white" viewBox="0 0 16 16"><path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/></svg>
                                                        </span>
                                                        @break
                                                    @case('document')
                                                        <span class="badge bg-info rounded-circle p-2">
                                                            <svg width="14" height="14" fill="white" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0"/></svg>
                                                        </span>
                                                        @break
                                                    @case('delivery')
                                                        <span class="badge bg-warning rounded-circle p-2">
                                                            <svg width="14" height="14" fill="white" viewBox="0 0 16 16"><path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-3.998-.085A1.5 1.5 0 0 1 0 10.5z"/></svg>
                                                        </span>
                                                        @break
                                                    @case('edit')
                                                        <span class="badge bg-secondary rounded-circle p-2">
                                                            <svg width="14" height="14" fill="white" viewBox="0 0 16 16"><path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293z"/><path d="m13.354 4.691-2-2-9.143 9.143a.5.5 0 0 0-.12.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12z"/></svg>
                                                        </span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-primary rounded-circle p-2">
                                                            <svg width="14" height="14" fill="white" viewBox="0 0 16 16"><path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71z"/><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/></svg>
                                                        </span>
                                                @endswitch
                                            </div>

                                            <div class="flex-grow-1">
                                                <p class="mb-0 small">{{ $log->getContent() }}</p>
                                                <small class="text-muted">
                                                    {{ $log->author ? $log->author->getFirstName() . ' ' . $log->author->getLastName() : 'Systeme' }}
                                                    &middot; {{ $log->created_at->format('d/m/Y H:i') }}
                                                </small>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
            </div>

            <div class="modal-footer">
                @if(in_array($order->getStatus(), [Status::COMMANDE, Status::COMMANDE_AVEC_REPONSE]))
                    <button type="button" class="btn btn-outline-secondary btn-load-modal" style="border-radius: 18px;" data-url="{{ route('order.modal.relance', ['id' => $order->getId()]) }}">Relancer le fournisseur</button>
                @endif
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
