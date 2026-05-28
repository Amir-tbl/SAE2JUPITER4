{{-- Modal Suivi Details SF --}}
<div class="modal fade" id="suiviDetailsSFModal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal-lg">
<div class="modal-content">

<div class="modal-header text-white" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 0;">
    <h5 class="modal-title">{{ $order->getOrderNumber() }}</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
    {{-- Resume commande --}}
    <div class="row g-2 mb-4 p-3 rounded" style="background: #f8fafc;">
        <div class="col-4"><small class="text-muted">N° Commande</small><p class="fw-bold mb-0">{{ $order->getOrderNumber() }}</p></div>
        <div class="col-4"><small class="text-muted">Demandeur</small><p class="fw-bold mb-0">{{ $order->author?->getFirstname() }} {{ $order->author?->getLastname() }}</p></div>
        <div class="col-4"><small class="text-muted">Departement</small><p class="fw-bold mb-0">{{ $order->department?->getName() ?? '-' }}</p></div>
        <div class="col-4"><small class="text-muted">Fournisseur</small><p class="fw-bold mb-0">{{ $order->supplier?->getCompanyName() ?? '-' }}</p></div>
        <div class="col-4"><small class="text-muted">Montant TTC</small><p class="fw-bold mb-0" style="color: var(--navy);">{{ number_format($order->total_ttc ?? 0, 2, ',', ' ') }} &euro;</p></div>
        <div class="col-4"><small class="text-muted">Statut</small><p class="mb-0"><span class="badge {{ $order->getStatus()->getBadgeClass() }}">{{ $order->getStatus()->getLabel() }}</span></p></div>
    </div>

    {{-- Description --}}
    @if($order->getDescription())
    <div class="p-3 rounded mb-3" style="background: #fffbea;">
        <small class="text-muted fw-bold text-uppercase" style="font-size: 11px;">Description</small>
        <p class="mb-0 mt-1 small">{{ $order->getDescription() }}</p>
    </div>
    @endif

    {{-- Table articles --}}
    @if($order->articles->isNotEmpty())
    <div class="table-responsive mb-3">
        <table class="table table-sm mb-0" style="background: rgba(183,157,137,0.07);">
            <thead style="background: rgba(255,255,255,0.38);">
                <tr>
                    <th>Désignation</th>
                    <th class="text-end">Qté</th>
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
                    <td class="text-end">{{ number_format($article->total_ttc, 2, ',', ' ') }} &euro;</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="d-flex justify-content-between align-items-center mt-2">
            <small class="text-muted">HT : {{ number_format($order->total_ht ?? 0, 2, ',', ' ') }} &euro; | TVA : {{ number_format($order->total_vat ?? 0, 2, ',', ' ') }} &euro;</small>
            <span class="fw-bold fs-5" style="color: var(--navy);">Total TTC : {{ number_format($order->total_ttc ?? 0, 2, ',', ' ') }} &euro;</span>
        </div>
    </div>
    @endif

    {{-- Receveur --}}
    @if($order->receiver_name)
    <div class="mb-3">
        <label class="text-muted small fw-bold text-uppercase">Receptionne par</label>
        <p class="mb-0 fw-semibold">{{ $order->receiver_name }}</p>
    </div>
    @endif

    {{-- BC Signé --}}
    @if($order->getUrlSignedPurchaseOrder())
    <div class="mb-3 p-3 rounded-3" style="background: #ecfdf5; border: 1px solid #10b981;">
        <label class="fw-bold small text-uppercase mb-2" style="color: #059669;">
            <svg width="16" height="16" fill="#059669" viewBox="0 0 16 16" class="me-1"><path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/></svg>
            Bon de commande signé
        </label>
        <div>
            <a href="{{ $order->getUrlSignedPurchaseOrder() }}" target="_blank" class="btn btn-sm text-white" style="background: #10b981; border-radius: 18px;">Consulter le BC signé (PDF)</a>
        </div>
    </div>
    @endif

    {{-- Pièces jointes --}}
    @php
        $docs = [];
        if ($order->getUrlQuote()) $docs[] = ['label' => 'Devis', 'url' => $order->getUrlQuote()];
        if ($order->getUrlPurchaseOrder()) $docs[] = ['label' => 'Bon de commande (original)', 'url' => $order->getUrlPurchaseOrder()];
        if ($order->getUrlDeliveryNote()) $docs[] = ['label' => 'Bon de livraison', 'url' => $order->getUrlDeliveryNote()];
    @endphp
    @if(count($docs) > 0)
    <div class="mb-4">
        <label class="text-muted small fw-bold text-uppercase mb-2">Pièces jointes</label>
        <div class="d-flex gap-2 flex-wrap">
            @foreach($docs as $doc)
            <a href="{{ $doc['url'] }}" target="_blank" class="btn btn-sm btn-outline-secondary" style="border-radius: 18px;">
                <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16" class="me-1"><path d="M4.5 3a2.5 2.5 0 0 1 5 0v9a1.5 1.5 0 0 1-3 0V5a.5.5 0 0 1 1 0v7a.5.5 0 0 0 1 0V3a1.5 1.5 0 1 0-3 0v9a2.5 2.5 0 0 0 5 0V5a.5.5 0 0 1 1 0v7a3.5 3.5 0 1 1-7 0z"/></svg>
                {{ $doc['label'] }}
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Commentaires --}}
    @include('components.commentsSection', ['order' => $order])

    {{-- Actions rapides (conditionnelles) --}}
    <div class="d-flex gap-2 flex-wrap mb-4">
        @if($order->getStatus() == \Database\Seeders\Status::BON_DE_COMMANDE_SIGNE)
            <button type="button" class="btn text-white btn-sm btn-load-modal" style="background: var(--navy); border-radius: 18px;"
                    data-url="{{ route('order.modal.envoiBC', ['id' => $order->getId()]) }}">
                Envoyer BC au fournisseur
            </button>
        @endif

        @if(in_array($order->getStatus(), [\Database\Seeders\Status::COMMANDE, \Database\Seeders\Status::COMMANDE_AVEC_REPONSE]))
            <button type="button" class="btn btn-outline-secondary btn-sm btn-load-modal" style="border-radius: 18px;"
                    data-url="{{ route('order.modal.relance', ['id' => $order->getId()]) }}">
                Relancer fournisseur
            </button>
        @endif

        @if($order->getStatus() == \Database\Seeders\Status::SERVICE_FAIT)
            <button type="button" class="btn text-white btn-sm btn-load-modal" style="background: var(--navy); border-radius: 18px;"
                    data-url="{{ route('order.modal.paiement', ['id' => $order->getId()]) }}">
                Declencher paiement
            </button>
        @endif
    </div>

    {{-- Timeline / Historique --}}
    <label class="text-muted small fw-bold text-uppercase mb-2">Historique</label>
    <div style="max-height: 250px; overflow-y: auto;">
        @foreach($order->logs->sortByDesc('created_at') as $log)
        <div class="d-flex align-items-start mb-2">
            <div class="me-2 mt-1">
                <span class="badge bg-primary rounded-circle p-1" style="width: 8px; height: 8px;"></span>
            </div>
            <div>
                <p class="mb-0 small">{{ $log->content }}</p>
                <small class="text-muted">
                    {{ $log->author ? $log->author->getFirstname() . ' ' . $log->author->getLastname() : 'Systeme' }}
                    &middot; {{ $log->created_at->format('d/m/Y H:i') }}
                </small>
            </div>
        </div>
        @endforeach
    </div>
</div>

</div>
</div>
</div>
