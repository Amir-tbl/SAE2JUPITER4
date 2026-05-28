@use(Database\Seeders\Status)

<div class="modal fade" id="refuseOrderModal-{{$orderId}}" data-bs-keyboard="false" tabindex="-1"
     aria-labelledby="refuseOrderModalLabel-{{$orderId}}" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="refuseOrderModalLabel-{{$orderId}}">Refuser la commande</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if (session()->exists('refuseError-'.$orderId))
                    <div class="alert alert-danger">
                        {{session('refuseError-'.$orderId)}}
                    </div>
                @endif
                <form id="refuseOrder-{{$orderId}}" class="ajax-form" method="POST" action="{{route('orders.action.refuse', $orderId)}}" autocomplete="off">
                    @csrf
                    <input type="hidden" name="modalId" value="refuseOrderModal-{{$orderId}}">
                    <input type="hidden" name="about" value="{{$about}}">

                    <p>Vous vous apprêtez à refuser la commande <strong>N°{{$order->getOrderNumber()}}</strong> ({{$order->getTitle()}}).</p>

                    <div class="mb-3">
                        <label for="reason-{{$orderId}}" class="form-label">Motif du refus :</label>
                        <textarea class="form-control" id="reason-{{$orderId}}" name="reason" rows="4" required placeholder="Indiquez le motif du refus..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" form="refuseOrder-{{$orderId}}" class="btn btn-danger">Refuser</button>
            </div>
        </div>
    </div>
</div>
