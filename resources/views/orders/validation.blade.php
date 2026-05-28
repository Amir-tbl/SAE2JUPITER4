@extends('base')
@section('page-title', 'Commandes a valider')

@section('content')
{{-- 3 KPIs --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="kpi-card">
            <div class="kpi-value">{{ $kpiTotal }}</div>
            <div class="kpi-label">En attente</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card">
            <div class="kpi-value">{{ $kpiMontantTotal }}</div>
            <div class="kpi-label">Montant total</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card">
            <div class="kpi-value" style="{{ $kpiUrgentes > 0 ? 'color: var(--badge-red);' : '' }}">{{ $kpiUrgentes }}</div>
            <div class="kpi-label">Urgentes (&gt; 5j)</div>
        </div>
    </div>
</div>

{{-- Filtres --}}
<div class="card mb-4" style="border-radius: var(--card-radius);">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('orders.validation') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">Departement</label>
                <select name="department" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->getId() }}" @selected(request('department') == $dept->getId())>{{ $dept->getName() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Fournisseur</label>
                <select name="supplier" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    @foreach($suppliers as $sup)
                        <option value="{{ $sup->getId() }}" @selected(request('supplier') == $sup->getId())>{{ $sup->getCompanyName() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Montant</label>
                <select name="montant" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    <option value="0-500" @selected(request('montant') == '0-500')>0 - 500 &euro;</option>
                    <option value="500-1000" @selected(request('montant') == '500-1000')>500 - 1 000 &euro;</option>
                    <option value="1000-2500" @selected(request('montant') == '1000-2500')>1 000 - 2 500 &euro;</option>
                    <option value="2500+" @selected(request('montant') == '2500+')>2 500 &euro;+</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-1">
                <button class="btn btn-sm text-white flex-fill" style="background: var(--navy); border-radius: 18px;">Filtrer</button>
                <a href="{{ route('orders.validation') }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 18px;">Reinitialiser</a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<table class="data-table">
    <thead>
        <tr>
            <th>N°</th>
            <th>Demandeur</th>
            <th>Departement</th>
            <th>Fournisseur</th>
            <th>Montant TTC</th>
            <th>Date demande</th>
            <th>Jours attente</th>
            <th>PJ</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($orders as $order)
            @php
                $pjCount = 0;
                if ($order->getAttribute('path_quote')) $pjCount++;
                if ($order->getAttribute('path_purchase_order')) $pjCount++;
                if ($order->getAttribute('path_delivery_note')) $pjCount++;
            @endphp
            <tr style="{{ $order->jours_attente > 5 ? 'background: rgba(239, 68, 68, 0.08);' : '' }}">
                <td class="fw-semibold">#{{ $order->getOrderNumber() }}</td>
                <td>{{ $order->author ? $order->author->getFirstName() . ' ' . $order->author->getLastName() : '-' }}</td>
                <td>{{ $order->department?->getName() ?? '—' }}</td>
                <td>{{ $order->supplier?->getCompanyName() ?? '—' }}</td>
                <td class="text-end">{{ number_format($order->total_ttc ?? 0, 2, ',', ' ') }} &euro;</td>
                <td>{{ $order->created_at->format('d/m/Y') }}</td>
                <td>
                    <span class="{{ $order->jours_attente > 5 ? 'fw-bold text-danger' : '' }}">{{ $order->jours_attente }}j</span>
                </td>
                <td>
                    <span class="pj-badge {{ $pjCount > 0 ? 'has-docs' : 'no-docs' }}">
                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M4.5 3a2.5 2.5 0 0 1 5 0v9a1.5 1.5 0 0 1-3 0V5a.5.5 0 0 1 1 0v7a.5.5 0 0 0 1 0V3a1.5 1.5 0 1 0-3 0v9a2.5 2.5 0 0 0 5 0V5a.5.5 0 0 1 1 0v7a3.5 3.5 0 1 1-7 0z"/></svg>
                        {{ $pjCount }}
                    </span>
                </td>
                <td>
                    <button class="btn btn-sm text-white btn-load-modal" style="background: var(--navy); border-radius: 18px;" data-url="{{ route('order.modal.validationSF', ['id' => $order->getId()]) }}">Consulter</button>
                </td>
            </tr>
        @empty
            <tr><td colspan="9" class="text-center text-muted py-4">Aucune commande en attente de validation.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
