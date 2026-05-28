@use(Database\Seeders\Status)
@use(App\Models\Role)
@use(App\Models\User)


<div class="modal fade refreshOnExt" id="successModal" data-bs-keyboard="false" tabindex="-1"
     aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="successModalTitle">Opération réussie</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-success mb-0">
                    {{$message}}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Ok</button>
            </div>
        </div>
    </div>
</div>
