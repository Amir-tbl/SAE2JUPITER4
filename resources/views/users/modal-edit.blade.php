{{-- Modal edition utilisateur --}}
<div class="modal fade" id="modalEditUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header text-white" style="background: var(--navy);">
                <h5 class="modal-title">Modifier — {{ $user->getFullName() }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form class="ajax-form" action="{{ route('users.update', ['id' => $user->getId()]) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Prenom</label>
                            <input type="text" name="first_name" class="form-control" value="{{ $user->getFirstName() }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nom</label>
                            <input type="text" name="last_name" class="form-control" value="{{ $user->getLastName() }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ $user->getEmail() }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nouveau mot de passe (optionnel)</label>
                            <input type="password" name="password" class="form-control" placeholder="Laisser vide pour ne pas changer">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Roles et departements</label>
                            <div class="d-flex flex-wrap gap-2">
                                @php $userRoleIds = $user->roles->pluck('id')->toArray(); @endphp
                                @foreach($roles as $role)
                                <label class="form-check-label d-flex align-items-center gap-1 px-2 py-1 rounded" style="background: #f8fafc; cursor: pointer;">
                                    <input type="checkbox" name="roles[]" value="{{ $role->id }}" class="form-check-input m-0" {{ in_array($role->id, $userRoleIds) ? 'checked' : '' }}>
                                    <span class="small">{{ $role->getName() }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn text-white" style="background: var(--navy);">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
