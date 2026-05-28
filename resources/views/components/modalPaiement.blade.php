{{-- Modal Paiement --}}
<div class="modal fade" id="paiementModal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal-lg">
<div class="modal-content">

<div class="modal-header text-white" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 0;">
    <h5 class="modal-title">Declencher le paiement</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>
<form action="{{ route('order.action.markPaid', ['id' => $order->getId()]) }}" method="POST" class="ajax-form">
@csrf
<div class="modal-body">
    {{-- Resume commande --}}
    <div class="row g-2 mb-3 p-3 rounded" style="background: #f8fafc;">
        <div class="col-6"><small class="text-muted">N° Commande</small><p class="fw-bold mb-0">{{ $order->getOrderNumber() }}</p></div>
        <div class="col-6"><small class="text-muted">Fournisseur</small><p class="fw-bold mb-0">{{ $order->supplier?->getCompanyName() ?? '-' }}</p></div>
        <div class="col-6"><small class="text-muted">Date commande</small><p class="fw-bold mb-0">{{ $order->created_at->format('d/m/Y') }}</p></div>
        <div class="col-6"><small class="text-muted">Montant TTC</small><p class="fw-bold mb-0 fs-5" style="color: var(--navy);">{{ number_format($order->total_ttc ?? 0, 2, ',', ' ') }} &euro;</p></div>
    </div>

    {{-- Infos bancaires fournisseur --}}
    <div class="p-3 rounded mb-3" style="background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%); border: 1px solid #10b981;">
        <h6 class="fw-bold mb-3">Informations bancaires fournisseur</h6>
        <div class="row g-2">
            <div class="col-6"><small class="text-muted">Raison sociale</small><p class="mb-1 fw-semibold">{{ $order->supplier?->getCompanyName() ?? '-' }}</p></div>
            <div class="col-6"><small class="text-muted">SIRET</small><p class="mb-1 fw-semibold">{{ $order->supplier?->siret ?? 'Non renseigne' }}</p></div>
            <div class="col-12"><small class="text-muted">Email</small><p class="mb-1 fw-semibold">{{ $order->supplier?->getEmail() ?? '-' }}</p></div>
            <div class="col-8">
                <small class="text-muted">IBAN</small>
                <div class="d-flex align-items-center gap-2">
                    <p class="mb-0 fw-semibold font-monospace" id="ibanValue">{{ $order->supplier?->iban ?? 'Non renseigne' }}</p>
                    @if($order->supplier?->iban)
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="navigator.clipboard.writeText(document.getElementById('ibanValue').textContent.trim())" style="border-radius: 8px; font-size: 11px;">Copier</button>
                    @endif
                </div>
            </div>
            <div class="col-4"><small class="text-muted">BIC</small><p class="mb-0 fw-semibold font-monospace">{{ $order->supplier?->bic ?? 'Non renseigne' }}</p></div>
        </div>
    </div>

    <div class="p-3 rounded" style="background: #fff3cd; border-left: 4px solid #fbbf24;">
        <small>En cliquant "Marquer comme paye", vous confirmez que le virement a ete effectue.</small>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius: 18px;">Annuler</button>
    <button type="submit" class="btn text-white" style="background: var(--navy); border-radius: 18px;">Marquer comme paye</button>
</div>
</form>

</div>
</div>
</div>
