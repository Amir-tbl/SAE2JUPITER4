@extends('base')

@section('page-title', 'Mon Profil')

@section('content')
{{-- Toast notification --}}
@if (session('success'))
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    <div id="profileToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <strong>{{ session('success') }}</strong>
                @if (session('mailSent'))
                    <div>Un mail de confirmation vous a ete envoye.</div>
                @endif
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const el = document.getElementById('profileToast');
        if (!el) return;
        const toast = new bootstrap.Toast(el, { delay: 5000 });
        toast.show();
    });
</script>
@endif

@if ($errors->any())
    <div class="alert alert-danger mb-4">
        <ul class="mb-0">
            @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Profil header --}}
<div class="card mb-4" style="border-radius: var(--card-radius); overflow: hidden;">
    <div class="card-body d-flex align-items-center gap-4 p-4">
        {{-- Avatar cercle avec initiales --}}
        <div style="width: 100px; height: 100px; border-radius: 50%; background: {{ ($isDirecteur ?? false) ? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' : 'var(--navy)' }}; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <span style="font-size: 36px; font-weight: 700; color: #fff;">{{ substr($user->getFirstName(), 0, 1) }}{{ substr($user->getLastName(), 0, 1) }}</span>
        </div>
        <div class="flex-grow-1">
            <h4 class="mb-1" style="font-size: 28px; font-weight: 800;">{{ $user->getFirstName() }} {{ $user->getLastName() }}</h4>
            @if($isDirecteur ?? false)
                <span class="badge" style="background: #ede9fe; color: #6d28d9; border-radius: 12px; padding: 4px 12px;">Directeur</span>
            @else
                <p class="mb-0 text-muted" style="font-size: 16px;">{{ $roles->first()?->getName() ?? 'Utilisateur' }} &middot; {{ $departments->first()?->getName() ?? '' }}</p>
            @endif
            @if($user->email)
                <p class="mb-0 mt-1" style="color: var(--navy);">{{ $user->email }}</p>
            @endif
        </div>
        <a href="{{ route('logout') }}" class="btn btn-outline-secondary" style="border-radius: 18px;">Se deconnecter</a>
    </div>
</div>

{{-- Stats --}}
<div class="stats-grid mb-4">
    @php
        if ($isDirecteur ?? false) {
            $statBlocks = [
                ['value' => $stats['bc_signes'], 'label' => 'BC signes'],
                ['value' => $stats['ce_mois'], 'label' => 'Signes ce mois'],
                ['value' => $stats['delai_moyen'], 'label' => 'Delai moyen'],
                ['value' => $stats['montant_total'], 'label' => 'Montant total'],
            ];
        } else {
            $statBlocks = [
                ['value' => $stats['total'], 'label' => 'Commandes creees'],
                ['value' => $stats['ce_mois'], 'label' => 'Ce mois-ci'],
                ['value' => $stats['en_cours'], 'label' => 'En attente'],
                ['value' => $stats['montant_total'], 'label' => 'Montant total'],
            ];
        }
    @endphp
    @foreach($statBlocks as $stat)
        <div class="stat-item">
            <div class="stat-value">{{ $stat['value'] }}</div>
            <div class="stat-label">{{ $stat['label'] }}</div>
        </div>
    @endforeach
</div>

{{-- Informations --}}
<div class="row g-4">
    {{-- Informations personnelles --}}
    <div class="col-md-6">
        <div class="card h-100" style="border-radius: var(--card-radius);">
            <div class="card-body">
                <h6 class="section-title mb-3">Informations personnelles</h6>
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase">Identifiant (CAS)</label>
                        <input class="form-control" value="{{ $user->login }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase">Prenom</label>
                        <input class="form-control" value="{{ $user->first_name }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase">Nom</label>
                        <input class="form-control" value="{{ $user->last_name }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase">Email</label>
                        <input name="email" type="email" class="form-control" value="{{ old('email', $user->email) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase">Telephone</label>
                        <input name="phone_number" type="text" class="form-control" value="{{ old('phone_number', $user->phone_number) }}">
                    </div>

                    <button class="btn btn-primary">Enregistrer</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Informations professionnelles --}}
    <div class="col-md-6">
        <div class="card h-100" style="border-radius: var(--card-radius);">
            <div class="card-body">
                <h6 class="section-title mb-3">Informations professionnelles</h6>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-uppercase">Roles</label>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($roles as $role)
                            <span class="badge b-blue">{{ $role->getName() }}</span>
                        @endforeach
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-uppercase">Departements</label>
                    <div class="d-flex flex-wrap gap-2">
                        @forelse($departments as $dept)
                            <span class="badge b-grey">{{ $dept->getName() }}</span>
                        @empty
                            <span class="text-muted small">Aucun departement</span>
                        @endforelse
                    </div>
                </div>

                {{-- Notifications toggle --}}
                <div class="mt-4 pt-3 border-top">
                    <h6 class="section-title mb-3">Notifications</h6>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="notifEmail" checked disabled>
                        <label class="form-check-label" for="notifEmail">
                            Notifications par email
                        </label>
                        <div class="text-muted small mt-1">Recevoir un email lors des modifications de profil</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
