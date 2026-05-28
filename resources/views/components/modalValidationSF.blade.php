{{-- Modal Validation SF --}}
<div class="modal fade" id="validationSFModal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal-lg">
<div class="modal-content">

<div class="modal-header text-white" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 0;">
    <h5 class="modal-title">Valider la commande</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<form action="{{ route('order.action.validationSF', ['id' => $order->getId()]) }}" method="POST" enctype="multipart/form-data" class="ajax-form">
@csrf
<div class="modal-body">

    {{-- Infos commande (grille 3x2) --}}
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
            <small class="text-muted d-block text-uppercase" style="font-size: 11px;">Departement</small>
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

    {{-- Commentaires existants --}}
    @include('components.commentsSection', ['order' => $order])

    {{-- Upload BC (obligatoire pour valider) --}}
    <div class="p-3 rounded mb-3" style="background: #fff3cd;">
        <small class="fw-bold">Bon de commande (obligatoire pour valider)</small>
        <p class="mb-2 small text-muted">Joignez le bon de commande genere pour cette demande.</p>
        <input type="file" class="form-control" name="purchase_order" accept=".pdf,.doc,.docx" id="bcFileInput"
               style="border-radius: 12px;">
        <div id="bcError" class="text-danger small mt-1" style="display: none;">
            Le bon de commande est obligatoire pour valider.
        </div>
    </div>

    {{-- Commentaire --}}
    <div class="mb-3">
        <label class="form-label small fw-bold">Commentaire <small class="fw-normal text-muted">(optionnel pour validation, obligatoire pour refus)</small></label>
        <textarea class="form-control" name="comment" id="commentField" rows="3" placeholder="Motif du refus ou remarques..."
                  style="border-radius: 12px;"></textarea>
        <div id="commentError" class="text-danger small mt-1" style="display: none;">
            Le motif du refus est obligatoire.
        </div>
    </div>

    <input type="hidden" name="action" id="actionField" value="validate">
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius: 18px;">Annuler</button>
    <button type="button" class="btn btn-danger" id="btnRefuserSF" style="border-radius: 18px;">Refuser</button>
    <button type="button" class="btn text-white" id="btnValiderSF" style="background: var(--navy); border-radius: 18px;">Valider</button>
</div>
</form>


</div>
</div>
</div>
