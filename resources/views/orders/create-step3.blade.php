@extends('base')

@section('header')
    <div class="container d-block">
        <h1 class="h1">Nouvelle commande</h1>
        <p class="mb-0 opacity-75">Verifiez et confirmez votre demande</p>
    </div>
@endsection

@section('content')
<div class="container mt-4 page-wizard">
    {{-- Info cards --}}
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 16px;">
                <small class="text-muted fw-bold text-uppercase">Demandeur</small>
                <p class="fw-bold mb-1 mt-1">{{ $user->getFirstname() }} {{ $user->getLastname() }}</p>
                <small class="text-muted">{{ $department->getName() }}</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 16px;">
                <small class="text-muted fw-bold text-uppercase">Livraison souhaitee</small>
                <p class="fw-bold mb-1 mt-1">{{ \Carbon\Carbon::parse($step1['desired_delivery_date'])->format('d/m/Y') }}</p>
                <small class="text-muted">{{ $step1['delivery_location'] }}</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 16px;">
                <small class="text-muted fw-bold text-uppercase">Fournisseur</small>
                <p class="fw-bold mb-1 mt-1">{{ $supplierName }}</p>
                @if($step2['supplier_id'] === 'new')
                    <small class="text-muted">{{ $step2['new_supplier_email'] ?? '' }}</small>
                @endif
            </div>
        </div>
    </div>

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

    {{-- Table recap articles --}}
    <div class="card border-0 shadow-sm p-4 mb-3" style="border-radius: 16px;">
        <h6 class="fw-bold mb-3">Articles commandes</h6>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead style="background: rgba(255,255,255,0.38);">
                    <tr>
                        <th>Article</th>
                        <th class="text-end">Qte</th>
                        <th class="text-end">PU</th>
                        <th class="text-end">TVA (%)</th>
                        <th class="text-end">Total ligne</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($step2['articles'] as $article)
                    @php
                        $ht = $article['quantity'] * $article['unit_price'];
                        $ttc = $ht * (1 + $article['vat_rate'] / 100);
                    @endphp
                    <tr>
                        <td>{{ $article['designation'] }}</td>
                        <td class="text-end">{{ $article['quantity'] }}</td>
                        <td class="text-end">{{ number_format($article['unit_price'], 2, ',', ' ') }} &euro;</td>
                        <td class="text-end">{{ $article['vat_rate'] }}%</td>
                        <td class="text-end fw-bold">{{ number_format($ttc, 2, ',', ' ') }} &euro;</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Totaux --}}
        <div class="d-flex flex-column align-items-end mt-3 gap-1">
            <div class="d-flex gap-3"><span class="text-muted">Sous-total HT</span><strong>{{ number_format($totalHt, 2, ',', ' ') }} &euro;</strong></div>
            <div class="d-flex gap-3"><span class="text-muted">TVA totale</span><strong>{{ number_format($totalVat, 2, ',', ' ') }} &euro;</strong></div>
            <div class="d-flex gap-3 pt-2" style="border-top: 2px solid var(--navy);"><span class="fw-bold fs-5">Total TTC</span><strong class="fs-5" style="color: var(--navy);">{{ number_format($totalTtc, 2, ',', ' ') }} &euro;</strong></div>
        </div>
    </div>

    {{-- Infos generales recap --}}
    <div class="card border-0 shadow-sm p-4 mb-3" style="border-radius: 16px;">
        <h6 class="fw-bold mb-3">Informations generales</h6>
        <dl class="row mb-0">
            <dt class="col-sm-4 text-muted">Titre</dt>
            <dd class="col-sm-8">{{ $step1['title'] }}</dd>
            @if($step1['description'])
                <dt class="col-sm-4 text-muted">Description</dt>
                <dd class="col-sm-8">{{ $step1['description'] }}</dd>
            @endif
            <dt class="col-sm-4 text-muted">N de devis</dt>
            <dd class="col-sm-8">{{ $step2['quote_num'] }}</dd>
        </dl>
    </div>

    @if($step2['supplier_id'] === 'new')
    <div class="card border-0 shadow-sm p-4 mb-3" style="border-radius: 16px;">
        <h6 class="fw-bold mb-3">Nouveau fournisseur</h6>
        <dl class="row mb-0">
            <dt class="col-sm-4 text-muted">Nom</dt>
            <dd class="col-sm-8">{{ $step2['new_supplier_name'] }}</dd>
            <dt class="col-sm-4 text-muted">Email</dt>
            <dd class="col-sm-8">{{ $step2['new_supplier_email'] }}</dd>
            <dt class="col-sm-4 text-muted">SIRET</dt>
            <dd class="col-sm-8">{{ $step2['new_supplier_siret'] }}</dd>
        </dl>
        <span class="badge mt-2" style="background: #fef3c7; color: #92400e;">En attente de validation</span>
    </div>
    @endif

    {{-- Confirmation --}}
    <div class="card border-0 shadow-sm p-4 mb-5" style="border-radius: 16px;">
        <form action="{{ route('orders.store.step3') }}" method="POST">
            @csrf
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="termsChk" required>
                <label class="form-check-label small" for="termsChk" style="color: #475169;">
                    Je confirme que les informations ci-dessus sont correctes.
                </label>
            </div>
            <div class="d-flex gap-2 justify-content-end flex-wrap">
                <a href="{{ route('orders.create.step2') }}" class="btn" style="background: #E6E7EA; color: #1E2233; border-radius: 8px; min-height: 44px; padding: 10px 16px;">Retour</a>
                <button type="submit" class="btn text-white" id="confirmBtn" disabled style="background: var(--navy); border-radius: 18px; min-height: 44px; padding: 10px 16px;">
                    Confirmer et creer l'envoi &rarr;
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.page-wizard .form-control:focus,
.page-wizard .form-select:focus {
    border-color: #3A6CF0;
    box-shadow: 0 0 0 2px rgba(58, 108, 240, 0.1);
}
@media (max-width: 560px) {
    #wizardStepper { flex-direction: column !important; }
}
</style>

<script>
document.getElementById('termsChk').addEventListener('change', function() {
    document.getElementById('confirmBtn').disabled = !this.checked;
});
</script>
@endsection
