@use(\Database\Seeders\PermissionValue)
<!-- Modal d'ajout de fournisseur -->
<div class="modal fade" id="addSupplierModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
     aria-labelledby="addSupplierModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSupplierModalLabel">Ajouter un fournisseur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addSupplierForm" method="POST" action="{{ route('suppliers.store') }}">
                @csrf
                    <div class="mb-3">
                        <label for="company-name" class="form-label">Nom de l'entreprise <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="company-name" name="company_name" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="siret" class="form-label">SIRET <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="siret" name="siret" maxlength="14" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" id="phone" name="phone_number">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="contact-name" class="form-label">Nom du contact</label>
                            <input type="text" class="form-control" id="contact-name" name="contact_name">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Adresse</label>
                        <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="iban" class="form-label">IBAN</label>
                            <input type="text" class="form-control" id="iban" name="iban" maxlength="34">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="bic" class="form-label">BIC</label>
                            <input type="text" class="form-control" id="bic" name="bic" maxlength="11">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="note" class="form-label">Note / Remarque</label>
                        <textarea class="form-control" id="note" name="note" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-between">
                @if($user->hasPermission(PermissionValue::GERER_FOURNISSEURS))
                    <div class="d-flex justify-content-start"
                         title="Marquer qu'il est possible de passer commande avec ce fournisseur">
                        <input class="form-check-input me-2" type="checkbox"
                               id="checkboxValidate" form="addSupplierForm" checked>
                        <label class="form-check-label" for="checkboxValidate">
                            Valider le fournisseur
                        </label>
                    </div>
                @else
                    <div class="alert alert-info mb-0" role="alert">
                        Le fournisseur devra d'abord être validé par le service financier.
                    </div>
                @endif
                <div class="d-inline">
                    <button type="reset" class="btn btn-secondary me-1" form="addSupplierForm"
                            data-bs-dismiss="modal">
                        Annuler
                    </button>
                    <button type="submit" form="addSupplierForm" class="btn btn-primary">Ajouter</button>
                </div>
            </div>
        </div>
    </div>
</div>
