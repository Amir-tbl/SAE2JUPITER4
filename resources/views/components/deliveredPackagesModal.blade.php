@use(Database\Seeders\Status)

<div class="modal fade" id="deliveredPackagesModal-{{$orderId}}" data-bs-keyboard="false" tabindex="-1"
     aria-labelledby="deliveredPackagesModalLabel-{{$orderId}}" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deliveredPackagesModalLabel-{{$orderId}}">Marquer des colis comme livrés</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if (session()->exists('deliveredPackagesError-'.$orderId))
                    <div class="alert alert-danger">
                        {{session('deliveredPackagesError-'.$orderId)}}
                    </div>
                @endif
                <form id="deliveredPackages-{{$orderId}}" class="ajax-form" method="POST" action="{{route('orders.action.deliveredPackages', $orderId)}}" autocomplete="off">
                    @csrf
                    <input type="hidden" name="modalId" value="deliveredPackagesModal-{{$orderId}}">

                    <p>Sélectionnez les colis livrés pour la commande <strong>N°{{$order->getOrderNumber()}}</strong> :</p>

                    @if($packages->isEmpty())
                        <div class="alert alert-info">Aucun colis enregistré pour cette commande.</div>
                    @else
                        @foreach($packages as $package)
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="packages[]"
                                       value="{{$package->getId()}}" id="package-{{$package->getId()}}"
                                       @if($package->getShippingDate()) checked disabled @endif>
                                <label class="form-check-label" for="package-{{$package->getId()}}">
                                    {{$package->getName()}}
                                    @if($package->getShippingDate())
                                        <span class="badge bg-success">Livré</span>
                                    @endif
                                </label>
                            </div>
                        @endforeach
                    @endif
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" form="deliveredPackages-{{$orderId}}" class="btn btn-primary">Confirmer la livraison</button>
            </div>
        </div>
    </div>
</div>
