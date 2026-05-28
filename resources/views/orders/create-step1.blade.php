@extends('base')

@section('header')
    <div class="container d-block">
        <h1 class="h1">Nouvelle commande</h1>
        <p class="mb-0 opacity-75">Remplissez les informations de votre demande</p>
    </div>
@endsection

@section('content')
<div class="container mt-4 page-wizard">
    {{-- Stepper visuel --}}
    <div class="d-flex gap-2 mb-3 flex-wrap" id="wizardStepper">
        @foreach([
            [1, 'Infos generales'],
            [2, 'Fournisseur & Articles'],
            [3, 'Recapitulatif']
        ] as $step)
        <div class="flex-fill d-flex align-items-center gap-2 p-3 bg-white rounded-3 {{ $currentStep == $step[0] ? 'border-taupe shadow-sm' : '' }}"
             style="border: 1px solid rgba(0,0,0,.08); {{ $currentStep == $step[0] ? 'border-color: var(--taupe); box-shadow: 0 6px 18px rgba(0,0,0,.08);' : '' }}">
            <span class="d-inline-flex align-items-center justify-content-center rounded-circle fw-bold"
                  style="width: 32px; height: 32px; font-size: 14px; {{ $currentStep == $step[0] ? 'background: var(--navy); color: #fff;' : ($step[0] < $currentStep ? 'background: var(--badge-green); color: #fff;' : 'background: #E6E7EA; color: #1E2233;') }}">
                @if($step[0] < $currentStep)
                    <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z"/></svg>
                @else
                    {{ $step[0] }}
                @endif
            </span>
            <span class="fw-bold" style="font-size: 14px; color: #1E2233;">{{ $step[1] }}</span>
        </div>
        @endforeach
    </div>

    {{-- Formulaire --}}
    <form action="{{ route('orders.store.step1') }}" method="POST" class="card border-0 shadow-sm p-4" style="border-radius: 16px;" novalidate>
        @csrf
        <div class="row g-3">
            {{-- Demandeur (readonly) --}}
            <div class="col-md-6">
                <label class="form-label small fw-bold text-uppercase text-muted">Demandeur</label>
                <input type="text" class="form-control" value="{{ $user->getFirstname() }} {{ $user->getLastname() }}" readonly
                       style="border-radius: 12px; height: 44px; background: #f8fafc;">
            </div>

            {{-- Departement --}}
            <div class="col-md-6">
                <label class="form-label small fw-bold text-uppercase text-muted">Departement *</label>
                <select name="department_id" class="form-select @error('department_id') is-invalid @enderror" required
                        style="border-radius: 12px; height: 44px;">
                    <option value="">Selectionner...</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->getId() }}" @selected(old('department_id', $step1['department_id'] ?? '') == $dept->getId())>
                            {{ $dept->getName() }}
                        </option>
                    @endforeach
                </select>
                @error('department_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Titre --}}
            <div class="col-12">
                <label class="form-label small fw-bold text-uppercase text-muted">Titre de la demande *</label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" required
                       placeholder="Ex. Poste de travail complet salle B-204"
                       value="{{ old('title', $step1['title'] ?? '') }}"
                       style="border-radius: 12px; height: 44px;">
                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Description --}}
            <div class="col-12">
                <label class="form-label small fw-bold text-uppercase text-muted">Description</label>
                <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="4"
                          placeholder="Objectif de l'achat, contexte, precisions utiles..."
                          style="border-radius: 12px; min-height: 110px;">{{ old('description', $step1['description'] ?? '') }}</textarea>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Date livraison --}}
            <div class="col-md-6">
                <label class="form-label small fw-bold text-uppercase text-muted">Date livraison souhaitee *</label>
                <input type="date" name="desired_delivery_date" class="form-control @error('desired_delivery_date') is-invalid @enderror" required
                       min="{{ date('Y-m-d') }}"
                       value="{{ old('desired_delivery_date', $step1['desired_delivery_date'] ?? '') }}"
                       style="border-radius: 12px; height: 44px;">
                @error('desired_delivery_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Lieu de livraison (pleine largeur) --}}
            <div class="col-12">
                <label class="form-label small fw-bold text-uppercase text-muted">Lieu de livraison *</label>
                <input type="text" name="delivery_location" class="form-control @error('delivery_location') is-invalid @enderror" required
                       placeholder="Ex. Batiment A, Bureau 3.12"
                       value="{{ old('delivery_location', $step1['delivery_location'] ?? '') }}"
                       style="border-radius: 12px; height: 44px;">
                @error('delivery_location') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        {{-- Boutons --}}
        <div class="d-flex gap-2 justify-content-end mt-4 flex-wrap">
            <a href="{{ route('dashboard') }}" class="btn" style="background: #E6E7EA; color: #1E2233; border-radius: 8px; min-height: 44px; padding: 10px 16px;">Annuler</a>
            <button type="submit" class="btn text-white" style="background: var(--navy); border-radius: 18px; min-height: 44px; padding: 10px 16px;">Continuer</button>
        </div>
    </form>
</div>

<style>
.page-wizard .form-control:focus,
.page-wizard .form-select:focus {
    border-color: #3A6CF0;
    box-shadow: 0 0 0 2px rgba(58, 108, 240, 0.1);
}
.page-wizard .form-control.is-invalid,
.page-wizard .form-select.is-invalid {
    border-color: #E5484D;
    box-shadow: 0 0 0 2px rgba(229, 72, 77, 0.12) inset;
}
@media (max-width: 560px) {
    #wizardStepper { flex-direction: column !important; }
}
</style>
@endsection
