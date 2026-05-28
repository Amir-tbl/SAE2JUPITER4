@use(Database\Seeders\Status)
@use(App\Models\Role)

<div class="modal fade" id="sentToSupplierModal-{{$orderId}}" data-bs-keyboard="false" tabindex="-1"
     aria-labelledby="sentToSupplierModalLabel-{{$orderId}}" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sentToSupplierModalLabel-{{$orderId}}">Envoi au fournisseur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if (session()->exists('sentToSupplierError-'.$orderId))
                    <div class="alert alert-danger">
                        {{session('sentToSupplierError-'.$orderId)}}
                    </div>
                @endif
                <form id="sentToSupplier-{{$orderId}}" class="ajax-form" method="POST" action="{{route('orders.action.sentToSupplier', $orderId)}}" autocomplete="off">
                    @csrf
                    <input type="hidden" name="modalId" value="sentToSupplierModal-{{$orderId}}">

                    <p>Confirmez-vous l'envoi du bon de commande au fournisseur pour la commande <strong>N°{{$order->getOrderNumber()}}</strong> ?</p>

                    <div class="mb-3">
                        <label class="form-label">Email du fournisseur :</label>
                        <input type="email" class="form-control" name="email" value="{{$supplierEmail}}" readonly>
                    </div>

                    <hr/>
                    <div class="d-flex justify-content-start">
                        <input class="form-check-input me-2" type="checkbox" name="sendMail"
                               id="checkboxSendMail-{{$orderId}}" form="sentToSupplier-{{$orderId}}" checked>
                        <label class="form-check-label" for="checkboxSendMail-{{$orderId}}">
                            Envoyer un mail automatique au fournisseur
                        </label>
                    </div>
                    <div class="collapse mt-2" id="mailContentCollapse-{{$orderId}}">
                        <div class="mb-3">
                            @php
                                $signature = implode(', ', $user->getRoles()->map(fn (Role $role) => $role->getName())->toArray());
                                $defaultContent = "Madame, monsieur,\n" .
                                    "Veuillez trouver ci-joint le bon de commande pour la commande \"{$order->getTitle()}\" N°{$order->getOrderNumber()}.\n\n" .
                                    "{$user->getFullName()}\n" .
                                    "{$signature},\n" .
                                    "IUT de Villetaneuse, Sorbonne Paris Nord";
                            @endphp
                            <label for="mailContent-{{$orderId}}" class="form-label">Contenu du mail :</label>
                            <textarea class="form-control" style="height: 150px" name="mail_content" id="mailContent-{{$orderId}}">{{$defaultContent}}</textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" form="sentToSupplier-{{$orderId}}" class="btn btn-primary">Confirmer l'envoi</button>
            </div>
        </div>
    </div>
</div>
