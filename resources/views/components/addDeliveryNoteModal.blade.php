@use(Database\Seeders\Status)

<div class="modal fade" id="addDeliveryNoteModal-{{$orderId}}" data-bs-keyboard="false" tabindex="-1"
     aria-labelledby="addDeliveryNoteModalLabel-{{$orderId}}" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addDeliveryNoteModalLabel-{{$orderId}}">Ajouter un bon de livraison</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if (session()->exists('deliveryNoteError-'.$orderId))
                    <div class="alert alert-danger">
                        {{session('deliveryNoteError-'.$orderId)}}
                    </div>
                @endif
                <form id="addDeliveryNote-{{$orderId}}" class="ajax-form" method="POST" enctype="multipart/form-data" action="{{route('orders.action.uploadDeliveryNote', $orderId)}}" autocomplete="off">
                    @csrf
                    <input type="hidden" name="modalId" value="addDeliveryNoteModal-{{$orderId}}">

                    <label class="form-label fs-5">Sélectionnez un bon de livraison :</label><br/>
                    <small>Fichiers acceptés : pdf, doc, docx jusqu'à 10MB</small>
                    <input type="file" name="delivery_note" class="form-control mb-3" accept=".pdf,.docx,.doc" required>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" form="addDeliveryNote-{{$orderId}}" class="btn btn-primary">Envoyer</button>
            </div>
        </div>
    </div>
</div>
