@use(Database\Seeders\Status)
@use(App\Models\Role)
@use(App\Models\User)


<div class="modal fade" id="addPurchaseOrderModal-{{$orderId}}" data-bs-keyboard="false" tabindex="-1"
     aria-labelledby="addPurchaseOrderModalLabel-{{$orderId}}" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPurchaseOrderModalLabel-{{$orderId}}">Dépôt d'un bon de commande</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if (session()->exists('purchaseOrderError-'.$orderId))
                    <div class="alert alert-danger">
                        {{session('purchaseOrderError-'.$orderId)}}
                    </div>
                @endif
                <form id="addPurchaseOrder-{{$orderId}}" class="ajax-form" method="POST" enctype="multipart/form-data" action="{{route('orders.uploadPurchaseOrder', $orderId)}}" autocomplete="off">
                    @csrf
                    <input type="hidden" name="modalId" value="addPurchaseOrderModal-{{$orderId}}">

                    <label class="form-label fs-5">Sélectionnez un bon de commande :</label><br/>
                    <small>Fichiers acceptés : pdf, doc, docx jusqu'à 10MB</small>
                    <input type="file" name="purchase_order" class="form-control mb-3" accept="*,.pdf,.docx,.doc" required>

                    <div class="d-flex justify-content-start" title="À cocher si le directeur de l'IUT a signé le bon de commande">
                        <input class="form-check-input me-2" type="checkbox" name="signed"
                               id="checkboxSigned-{{$orderId}}" form="addPurchaseOrder-{{$orderId}}" @checked($canSign)>
                        <label class="form-check-label" for="checkboxSigned-{{$orderId}}">
                            Marquer comme signé par le directeur de l'IUT
                        </label>
                    </div>
                    <hr/>
                    <div class="d-flex justify-content-start" title="Cocher pour envoyer un mail automatique aux acteurs concernés lorsque le bon de commande sera déposé">
                        <input class="form-check-input me-2" type="checkbox" name="sendMail"
                               id="checkboxMail-{{$orderId}}" form="addPurchaseOrder-{{$orderId}}" checked>
                        <label class="form-check-label" for="checkboxMail-{{$orderId}}">
                            Envoyer un mail automatique
                        </label>
                    </div>
                    <div id="mailOptionsDiv-{{$orderId}}">
                        <label class="form-label fs-6"><a class="" data-bs-toggle="collapse"
                                                                                            href="#mailOptions-{{$orderId}}" role="button"
                                                                                            aria-expanded="false"
                                                                                            aria-controls="mailOptions-{{$orderId}}">Mail automatique
                                ></a></label>
                        <div class="collapse" id="mailOptions-{{$orderId}}">
                            <dl class="fw-light">
                                Modifiez le contenu par défaut pour l'adapter au besoin.<br/>
                                Les receveurs du mail automatique seront l'auteur de la commande et l'acteur chargé de la prochaine étape.
                            </dl>
                            <div class="mb-3">

                                <label for="email-address" class="col-form-label fs-6">Adresse e-mail utilisée</label>
                                <input type="text" class="form-control" id="email-address" value="{{$user->getEmail()}}"
                                       maxlength="255"/>
                            </div>
                            <div class="mb-3">
                                @php
                                    $signature = implode(', ', $user->getRoles()->map(fn (Role $role) => $role->getName())->toArray());

                                    // On utilise " " (double quotes) et \n pour les sauts de ligne pour que le code reste propre
                                    $defaultContent = "Madame, monsieur,\n" .
                                                      "Un bon de commande a été ajouté à la commande désignée \"{$order->getTitle()}\" de numéro {$order->getOrderNumber()}.\n\n" .
                                                      "{$user->getFullName()}\n" .
                                                      "{$signature},\n" .
                                                      "IUT de Villetaneuse, Sorbonne Paris Nord";
                                @endphp

                                <label for="mailContent-{{$orderId}}" class="col-form-label fs-6">Contenu :</label>
                                <textarea
                                    class="form-control"
                                    style="height: 200px"
                                    name="mailContent"
                                    id="mailContent-{{$orderId}}"
                                >{{$defaultContent}}</textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-between">
                <div class="d-flex justify-content-start" title="Passer la commande du statut {{ $order->getStatus() }} au statut de {{ Status::BON_DE_COMMANDE_NON_SIGNE }} ou de {{ Status::BON_DE_COMMANDE_SIGNE }} si le bon de commande est marqué comme signé.">
                    <input class="form-check-input me-2" name="nextStep" type="checkbox"
                           id="checkboxNextStep-{{$orderId}}" form="addPurchaseOrder-{{$orderId}}" checked>
                    <label class="form-check-label" for="checkboxNextStep-{{$orderId}}">
                        Passer au statut suivant
                    </label>
                </div>
                <div class="d-inline">
                    <button type="submit" form="addPurchaseOrder-{{$orderId}}" class="btn btn-primary">Sauvegarder</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    // TODO ajouter les modals avec le php
    const mailCheckBox = document.getElementById('checkboxMail-{{$orderId}}');
    const mailOptionsDiv = document.getElementById('mailOptionsDiv-{{$orderId}}');

    mailCheckBox.addEventListener('click', (event) => {
        console.debug(mailOptionsDiv.style);
        mailOptionsDiv.style = event.target.checked ? "display:block;" : "display:none;"
    });

</script>
