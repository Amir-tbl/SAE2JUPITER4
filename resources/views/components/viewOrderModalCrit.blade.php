@use(Database\Seeders\Status)

{{-- Modal de détails commande — vue CRIT (reception) --}}
<div class="modal fade" id="orderModalCrit{{ $orderId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 text-white" style="background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); border-radius: var(--card-radius) var(--card-radius) 0 0;">
                <div>
                    <h5 class="modal-title fw-bold mb-1">#{{ $order->getOrderNumber() }} - {{ $order->getTitle() }}</h5>
                    <span class="badge {{ $order->getStatus()->getBadgeClass() }}">{{ $order->getStatus()->getLabel() }}</span>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
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

                {{-- Dates --}}
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

                {{-- References --}}
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

                {{-- Receveur --}}
                @if($order->receiver_name)
                <div class="mb-3">
                    <label class="text-muted small fw-bold text-uppercase">Receptionne par</label>
                    <p class="mb-0 fw-semibold">{{ $order->receiver_name }}</p>
                </div>
                @endif

                {{-- Lieu de livraison (encadre) --}}
                @if($order->delivery_location)
                <div class="p-3 rounded mb-3" style="background: #f0f9ff; border: 2px solid #3b82f6;">
                    <label class="text-muted small fw-bold text-uppercase">Lieu de livraison</label>
                    <p class="mb-0 fw-bold fs-5">{{ $order->delivery_location }}</p>
                </div>
                @endif
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
