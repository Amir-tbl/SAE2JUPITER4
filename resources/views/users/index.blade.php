@extends('base')
@section('page-title', 'Gestion des utilisateurs')

@section('content')
{{-- Filtres --}}
<div class="rounded-3 p-3 mb-4" style="background: white; border: 1px solid rgba(183,157,137,0.2);">
    <div class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label small fw-bold text-uppercase" style="font-size: 11px;">Role</label>
            <select id="filterRole" class="form-select form-select-sm">
                <option value="">Tous les roles</option>
                @foreach($roles->where('is_department', false) as $role)
                    <option value="{{ $role->getName() }}">{{ $role->getName() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-bold text-uppercase" style="font-size: 11px;">Departement</label>
            <select id="filterDept" class="form-select form-select-sm">
                <option value="">Tous les departements</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->getName() }}">{{ $dept->getName() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-bold text-uppercase" style="font-size: 11px;">Recherche</label>
            <input type="text" id="filterSearch" class="form-control form-control-sm" placeholder="Nom, prenom, email...">
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button id="btnResetFilters" class="btn btn-sm btn-outline-secondary">Reinitialiser</button>
            <button class="btn btn-sm text-white" style="background: var(--navy);" data-bs-toggle="modal" data-bs-target="#modalCreateUser">Creer un utilisateur</button>
        </div>
    </div>
</div>

{{-- KPIs --}}
<div class="kpis-grid mb-4" style="grid-template-columns: repeat(4, 1fr);">
    <div class="kpi-card"><div class="kpi-value">{{ $totalUsers }}</div><div class="kpi-label">Total utilisateurs</div></div>
    <div class="kpi-card"><div class="kpi-value">{{ $activeUsers }}</div><div class="kpi-label">Avec role(s)</div></div>
    <div class="kpi-card"><div class="kpi-value">{{ $totalUsers - $activeUsers }}</div><div class="kpi-label">Sans role</div></div>
    <div class="kpi-card"><div class="kpi-value">{{ $departments->count() }}</div><div class="kpi-label">Departements</div></div>
</div>

{{-- Table utilisateurs --}}
<div class="table-responsive">
    <table class="data-table" id="usersTable">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Prenom</th>
                <th>Email</th>
                <th>Role(s)</th>
                <th>Departement(s)</th>
                <th>Inscription</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $u)
            @php
                $userRoles = $u->roles->where('is_department', false)->pluck('name')->join(', ');
                $userDepts = $u->roles->where('is_department', true)->pluck('name')->join(', ');
            @endphp
            <tr data-roles="{{ $userRoles }}" data-depts="{{ $userDepts }}" data-search="{{ strtolower($u->getLastName() . ' ' . $u->getFirstName() . ' ' . $u->getEmail()) }}">
                <td class="fw-semibold">{{ $u->getLastName() }}</td>
                <td>{{ $u->getFirstName() }}</td>
                <td>{{ $u->getEmail() }}</td>
                <td>
                    @foreach($u->roles->where('is_department', false) as $role)
                        <span class="badge b-blue">{{ $role->getName() }}</span>
                    @endforeach
                    @if($u->roles->where('is_department', false)->isEmpty())
                        <span class="badge b-grey">Aucun</span>
                    @endif
                </td>
                <td>
                    @foreach($u->roles->where('is_department', true) as $dept)
                        <span class="badge b-grey">{{ $dept->getName() }}</span>
                    @endforeach
                    @if($u->roles->where('is_department', true)->isEmpty())
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td>{{ \Carbon\Carbon::parse($u->getCreationDate())->format('d/m/Y') }}</td>
                <td style="text-align: right;">
                    <button class="btn btn-sm btn-ghost btn-load-modal" data-url="{{ route('users.modal.edit', ['id' => $u->getId()]) }}">Modifier</button>
                    <button class="btn btn-sm btn-ghost text-danger btn-delete-user" data-id="{{ $u->getId() }}" data-name="{{ $u->getFullName() }}">Supprimer</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Modal creation utilisateur --}}
<div class="modal fade" id="modalCreateUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header text-white" style="background: var(--navy);">
                <h5 class="modal-title">Creer un utilisateur</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formCreateUser">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Prenom</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nom</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Login</label>
                            <input type="text" name="login" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mot de passe</label>
                            <input type="password" name="password" class="form-control" required minlength="4">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Roles et departements</label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($roles as $role)
                                <label class="form-check-label d-flex align-items-center gap-1 px-2 py-1 rounded" style="background: #f8fafc; cursor: pointer;">
                                    <input type="checkbox" name="roles[]" value="{{ $role->id }}" class="form-check-input m-0">
                                    <span class="small">{{ $role->getName() }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn text-white" style="background: var(--navy);">Creer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filtres
    var filterRole = document.getElementById('filterRole');
    var filterDept = document.getElementById('filterDept');
    var filterSearch = document.getElementById('filterSearch');
    var rows = document.querySelectorAll('#usersTable tbody tr');

    function applyFilters() {
        var role = filterRole.value.toLowerCase();
        var dept = filterDept.value.toLowerCase();
        var search = filterSearch.value.toLowerCase();

        rows.forEach(function(row) {
            var rowRoles = (row.getAttribute('data-roles') || '').toLowerCase();
            var rowDepts = (row.getAttribute('data-depts') || '').toLowerCase();
            var rowSearch = (row.getAttribute('data-search') || '').toLowerCase();

            var matchRole = !role || rowRoles.indexOf(role) !== -1;
            var matchDept = !dept || rowDepts.indexOf(dept) !== -1;
            var matchSearch = !search || rowSearch.indexOf(search) !== -1;

            row.style.display = (matchRole && matchDept && matchSearch) ? '' : 'none';
        });
    }

    filterRole.addEventListener('change', applyFilters);
    filterDept.addEventListener('change', applyFilters);
    filterSearch.addEventListener('input', applyFilters);

    document.getElementById('btnResetFilters').addEventListener('click', function() {
        filterRole.value = '';
        filterDept.value = '';
        filterSearch.value = '';
        applyFilters();
    });

    // Creation utilisateur
    document.getElementById('formCreateUser').addEventListener('submit', function(e) {
        e.preventDefault();
        var form = this;
        var formData = new FormData(form);

        fetch('{{ route("users.store") }}', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.status === 'success') window.location.reload();
            else alert(data.message || 'Erreur');
        })
        .catch(function() { alert('Erreur reseau'); });
    });

    // Suppression utilisateur
    document.querySelectorAll('.btn-delete-user').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.getAttribute('data-id');
            var name = this.getAttribute('data-name');
            if (!confirm('Supprimer ' + name + ' ?')) return;

            var token = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value;

            fetch('/users/' + id, {
                method: 'DELETE',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': token }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.status === 'success') window.location.reload();
                else alert(data.message || 'Erreur');
            });
        });
    });
});
</script>
@endsection
