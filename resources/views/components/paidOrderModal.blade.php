@use(Database\Seeders\Status)

<div class="modal fade" id="paidOrderModal-{{$orderId}}" data-bs-keyboard="false" tabindex="-1"
     aria-labelledby="paidOrderModalLabel-{{$orderId}}" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paidOrderModalLabel-{{$orderId}}">Confirmer le paiement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if (session()->exists('paidError-'.$orderId))
                    <div class="alert alert-danger">
                        {{session('paidError-'.$orderId)}}
                    </div>
                @endif
                <form id="paidOrder-{{$orderId}}" class="ajax-form" method="POST" action="{{route('orders.action.paid', $orderId)}}" autocomplete="off">
                    @csrf
                    <input type="hidden" name="modalId" value="paidOrderModal-{{$orderId}}">

                    <p>Confirmez-vous le paiement de cette commande ?</p>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Commande</dt>
                        <dd class="col-sm-8">N°{{$order->getOrderNumber()}} - {{$order->getTitle()}}</dd>

                        <dt class="col-sm-4">Montant</dt>
                        <dd class="col-sm-8">{{$order->getCostFormatted()}}</dd>

                        <dt class="col-sm-4">Fournisseur</dt>
                        <dd class="col-sm-8">{{$order->getSupplier()->getCompanyName()}}</dd>
                    </dl>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" form="paidOrder-{{$orderId}}" class="btn btn-success">Confirmer le paiement</button>
            </div>
        </div>
    </div>
</div>
