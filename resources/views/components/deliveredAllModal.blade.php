@use(Database\Seeders\Status)

<div class="modal fade" id="deliveredAllModal-{{$orderId}}" data-bs-keyboard="false" tabindex="-1"
     aria-labelledby="deliveredAllModalLabel-{{$orderId}}" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deliveredAllModalLabel-{{$orderId}}">Confirmer le service fait</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if (session()->exists('deliveredAllError-'.$orderId))
                    <div class="alert alert-danger">
                        {{session('deliveredAllError-'.$orderId)}}
                    </div>
                @endif
                <form id="deliveredAll-{{$orderId}}" class="ajax-form" method="POST" enctype="multipart/form-data" action="{{route('orders.action.deliveredAll', $orderId)}}" autocomplete="off">
                    @csrf
                    <input type="hidden" name="modalId" value="deliveredAllModal-{{$orderId}}">

                    <p>Confirmez-vous que tous les colis de la commande <strong>N°{{$order->getOrderNumber()}}</strong> ont été livrés ?</p>
                    <p class="text-muted">Cette action marquera tous les colis comme livrés et passera la commande au statut "Service fait".</p>

                    <hr/>
                    <label class="form-label">Bon de livraison (optionnel) :</label><br/>
                    <small>Fichiers acceptés : pdf, doc, docx jusqu'à 10MB</small>
                    <input type="file" name="delivery_note" class="form-control mb-3" accept=".pdf,.docx,.doc">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" form="deliveredAll-{{$orderId}}" class="btn btn-success">Confirmer le service fait</button>
            </div>
        </div>
    </div>
</div>
