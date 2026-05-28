{{-- Modal Signature Directeur --}}
<div class="modal fade" id="signatureModal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal-lg">
<div class="modal-content">

<div class="modal-header text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
    <h5 class="modal-title fw-bold">Signer le bon de commande</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<form action="{{ route('orders.action.signature', ['id' => $order->getId()]) }}" method="POST" class="ajax-form">
@csrf
<div class="modal-body">

    {{-- Infos commande --}}
    <div class="row g-2 mb-4">
        <div class="col-4">
            <small class="text-muted d-block text-uppercase" style="font-size: 11px;">N° Commande</small>
            <span class="fw-bold">{{ $order->getOrderNumber() }}</span>
        </div>
        <div class="col-4">
            <small class="text-muted d-block text-uppercase" style="font-size: 11px;">Demandeur</small>
            <span class="fw-bold">{{ $order->author?->getFirstname() }} {{ $order->author?->getLastname() }}</span>
        </div>
        <div class="col-4">
            <small class="text-muted d-block text-uppercase" style="font-size: 11px;">Département</small>
            <span class="fw-bold">{{ $order->department?->getName() ?? '-' }}</span>
        </div>
        <div class="col-4">
            <small class="text-muted d-block text-uppercase" style="font-size: 11px;">Fournisseur</small>
            <span class="fw-bold">{{ $order->supplier?->getCompanyName() ?? '-' }}</span>
        </div>
        <div class="col-4">
            <small class="text-muted d-block text-uppercase" style="font-size: 11px;">Date demande</small>
            <span class="fw-bold">{{ $order->created_at->format('d/m/Y') }}</span>
        </div>
        <div class="col-4">
            <small class="text-muted d-block text-uppercase" style="font-size: 11px;">Attente</small>
            <span class="fw-bold text-danger">{{ (int) $order->created_at->diffInDays(now()) }} jours</span>
        </div>
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

    {{-- Pièces jointes --}}
    @php
        $docs = [];
        if ($order->getUrlQuote()) $docs[] = ['label' => 'Devis', 'url' => $order->getUrlQuote()];
        if ($order->getUrlPurchaseOrder()) $docs[] = ['label' => 'Bon de commande', 'url' => $order->getUrlPurchaseOrder()];
        if ($order->getUrlDeliveryNote()) $docs[] = ['label' => 'Bon de livraison', 'url' => $order->getUrlDeliveryNote()];
    @endphp
    @if(count($docs) > 0)
    <div class="mb-3">
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

    {{-- Commentaires existants --}}
    @include('components.commentsSection', ['order' => $order])

    {{-- Signature --}}
    <div class="mb-3">
        <label class="text-muted small fw-bold text-uppercase mb-2">Ma signature</label>

        {{-- Signature enregistrée --}}
        <div id="savedSignatureBlock" style="{{ $user->hasSignature() ? '' : 'display: none;' }}">
            <div class="p-3 rounded text-center" style="background: #f8fafc; border: 2px solid #e2e8f0;">
                @if($user->hasSignature())
                <img src="{{ $user->getSignatureUrl() }}" alt="Signature" id="savedSignatureImg" style="max-height: 120px; max-width: 100%;">
                @else
                <img src="" alt="Signature" id="savedSignatureImg" style="max-height: 120px; max-width: 100%; display: none;">
                @endif
                <div class="mt-2">
                    <button type="button" id="btnModifierSignature" class="btn btn-sm btn-outline-secondary" style="border-radius: 18px;">Modifier ma signature</button>
                </div>
            </div>
        </div>

        {{-- Zone de dessin --}}
        <div id="drawSignatureBlock" style="{{ $user->hasSignature() ? 'display: none;' : '' }}">
            <div style="background: #fff; border: 2px dashed #cbd5e1; border-radius: 12px; position: relative;">
                <canvas id="signatureCanvas" width="710" height="180" style="width: 100%; height: 180px; cursor: crosshair; display: block; border-radius: 10px;"></canvas>
                <button type="button" id="btnClearSignature" class="btn btn-sm btn-outline-secondary" style="position: absolute; top: 8px; right: 8px; border-radius: 18px; font-size: 11px;">Effacer</button>
            </div>
            <div class="d-flex gap-2 mt-2">
                <button type="button" id="btnSaveSignature" class="btn btn-sm text-white" style="background: #10b981; border-radius: 18px;">Enregistrer ma signature</button>
                @if($user->hasSignature())
                <button type="button" id="btnCancelDrawSignature" class="btn btn-sm btn-outline-secondary" style="border-radius: 18px;">Annuler</button>
                @endif
            </div>
        </div>

        <div id="signatureError" class="text-danger small mt-1" style="display: none;">
            Vous devez enregistrer une signature avant de signer.
        </div>
        <input type="hidden" name="signature_data" id="signatureData" value="{{ $user->hasSignature() ? 'saved' : '' }}">
    </div>

    {{-- Info --}}
    <div class="p-3 rounded-3 mb-3" style="background: #f0f9ff; border: 1px solid #0ea5e9;">
        <small style="color: #0369a1;">En signant ce bon de commande, vous autorisez l'envoi de la commande au fournisseur. Votre signature manuscrite sera apposée sur le document. Cette action est irréversible.</small>
    </div>

    {{-- Commentaire --}}
    <div class="mb-3">
        <label class="form-label small fw-bold">Commentaire <small class="fw-normal text-muted">(optionnel pour signature, obligatoire pour refus)</small></label>
        <textarea class="form-control" name="comment" id="commentFieldDir" rows="3" placeholder="Ajouter un commentaire..."
                  style="border-radius: 12px;"></textarea>
        <div id="commentErrorDir" class="text-danger small mt-1" style="display: none;">
            Le motif du refus est obligatoire.
        </div>
    </div>

    <input type="hidden" name="action" id="actionFieldDir" value="sign">
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius: 18px;">Annuler</button>
    <button type="button" class="btn btn-danger" id="btnRefuserDir" style="border-radius: 18px;">Refuser</button>
    <button type="button" class="btn text-white" id="btnSignerDir" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 18px;">
        Signer le bon de commande
    </button>
</div>
</form>

</div>
</div>
</div>
