{{-- Modal Envoi BC au fournisseur --}}
<div class="modal fade" id="envoiBCModal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal-lg">
<div class="modal-content">

<div class="modal-header text-white" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 0;">
    <h5 class="modal-title">Envoyer le BC au fournisseur</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>
<form action="{{ route('order.action.sendBC', ['id' => $order->getId()]) }}" method="POST" class="ajax-form">
@csrf
<div class="modal-body">
    {{-- Resume --}}
    <div class="p-3 rounded mb-3" style="background: #f0f9ff;">
        <strong>{{ $order->getOrderNumber() }}</strong> — {{ $order->supplier?->getCompanyName() ?? '-' }} — {{ number_format($order->total_ttc ?? 0, 2, ',', ' ') }} &euro;
    </div>

    <div class="mb-3">
        <label class="form-label small fw-bold">Email fournisseur</label>
        <input type="email" class="form-control" name="supplier_email" value="{{ $order->supplier?->getEmail() ?? '' }}" style="border-radius: 12px;">
    </div>
    <div class="mb-3">
        <label class="form-label small fw-bold">Objet</label>
        <input type="text" class="form-control" name="email_subject" style="border-radius: 12px;"
               value="Bon de commande signe - IUT Villetaneuse - {{ $order->getOrderNumber() }}">
    </div>
    <div class="mb-3">
        <label class="form-label small fw-bold">Message</label>
        <textarea class="form-control" name="email_body" rows="6" style="border-radius: 12px;">Bonjour,

Veuillez trouver ci-joint le bon de commande signe {{ $order->getOrderNumber() }} d'un montant de {{ number_format($order->total_ttc ?? 0, 2, ',', ' ') }} € TTC.

Merci de bien vouloir proceder a l'expedition dans les meilleurs delais.

Cordialement,
Service Financier - IUT Villetaneuse</textarea>
    </div>
    <div class="p-3 rounded" style="background: #fff3cd;">
        <small>Le bon de commande signe sera automatiquement joint a l'email.</small>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius: 18px;">Annuler</button>
    <button type="submit" class="btn text-white" style="background: var(--navy); border-radius: 18px;">Envoyer</button>
</div>
</form>

</div>
</div>
</div>
