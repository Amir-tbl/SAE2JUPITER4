{{-- Modal Relance fournisseur --}}
<div class="modal fade" id="relanceModal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal-lg">
<div class="modal-content">

<div class="modal-header text-white" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 0;">
    <h5 class="modal-title">Relancer le fournisseur</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>
<form action="{{ route('order.action.relance', ['id' => $order->getId()]) }}" method="POST" class="ajax-form">
@csrf
<div class="modal-body">
    {{-- Resume --}}
    <div class="p-3 rounded mb-3" style="background: #f0f9ff;">
        <strong>{{ $order->getOrderNumber() }}</strong> — {{ $order->supplier?->getCompanyName() ?? '-' }} — {{ number_format($order->total_ttc ?? 0, 2, ',', ' ') }} &euro;
    </div>

    <div class="mb-3">
        <label class="form-label small fw-bold">Email fournisseur</label>
        <input type="email" class="form-control" name="supplier_email" required value="{{ $order->supplier?->getEmail() ?? '' }}" style="border-radius: 12px;">
    </div>
    <div class="mb-3">
        <label class="form-label small fw-bold">Objet</label>
        <input type="text" class="form-control" name="email_subject" style="border-radius: 12px;"
               value="Relance commande {{ $order->getOrderNumber() }} - IUT Villetaneuse">
    </div>
    <div class="mb-3">
        <label class="form-label small fw-bold">Message de relance</label>
        <textarea class="form-control" name="message" rows="4" required
                  style="border-radius: 12px;">Bonjour,

Nous nous permettons de vous relancer concernant la commande {{ $order->getOrderNumber() }}. Merci de nous indiquer l'avancement de l'expedition.

Cordialement,
Service Financier - IUT Villetaneuse</textarea>
    </div>

    <div class="p-3 rounded" style="background: #fff3cd;">
        <small>La relance sera enregistree dans l'historique et l'email sera envoye au fournisseur.</small>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius: 18px;">Annuler</button>
    <button type="submit" class="btn text-white" style="background: var(--navy); border-radius: 18px;">Envoyer la relance</button>
</div>
</form>

</div>
</div>
</div>
