@extends('base')
@section('page-title', 'Bons de commande a signer')

@section('content')
{{-- 3 KPIs --}}
<div class="kpis-grid" style="grid-template-columns: repeat(3, 1fr);">
    @php
        $kpis = [
            ['value' => $kpiTotal, 'label' => 'Total a signer'],
            ['value' => $kpiUrgents, 'label' => 'Urgents (>7j)'],
            ['value' => $kpiMontantTotal, 'label' => 'Montant total'],
        ];
    @endphp
    @foreach($kpis as $i => $kpi)
        <div class="kpi-card">
            <div class="kpi-value" style="{{ ($i === 1 && $kpi['value'] > 0) ? 'color: var(--badge-red);' : '' }}">{{ $kpi['value'] }}</div>
            <div class="kpi-label">{{ $kpi['label'] }}</div>
        </div>
    @endforeach
</div>

{{-- Filtres --}}
<div class="card mb-4" style="border-radius: var(--card-radius);">
    <div class="card-body">
        <form method="GET" action="{{ route('orders.signature') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-uppercase">Departement</label>
                <select name="department" class="form-select">
                    <option value="">Tous</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department') == $dept->id ? 'selected' : '' }}>{{ $dept->getName() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-uppercase">Montant</label>
                <select name="montant" class="form-select">
                    <option value="">Tous</option>
                    <option value="0-1000" {{ request('montant') === '0-1000' ? 'selected' : '' }}>0 - 1 000 &euro;</option>
                    <option value="1000-5000" {{ request('montant') === '1000-5000' ? 'selected' : '' }}>1 000 - 5 000 &euro;</option>
                    <option value="5000-10000" {{ request('montant') === '5000-10000' ? 'selected' : '' }}>5 000 - 10 000 &euro;</option>
                    <option value="10000+" {{ request('montant') === '10000+' ? 'selected' : '' }}>10 000 &euro;+</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-uppercase">Priorite</label>
                <select name="priorite" class="form-select">
                    <option value="">Toutes</option>
                    <option value="urgent" {{ request('priorite') === 'urgent' ? 'selected' : '' }}>Urgentes uniquement</option>
                    <option value="normal" {{ request('priorite') === 'normal' ? 'selected' : '' }}>Non urgentes</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn text-white" style="background: var(--navy); border-radius: 18px;">Filtrer</button>
                <a href="{{ route('orders.signature') }}" class="btn btn-outline-secondary" style="border-radius: 18px;">Reinitialiser</a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
@if($orders->isEmpty())
    <p class="text-muted">Aucun bon de commande en attente de signature.</p>
@else
    <table class="data-table">
        <thead>
            <tr>
                <th>N° BC</th>
                <th>Demandeur</th>
                <th>Departement</th>
                <th>Fournisseur</th>
                <th>Montant TTC</th>
                <th>Date valid.</th>
                <th>Attente</th>
                <th>PJ</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
                @php
                    $pjCount = ($order->getAttribute('path_quote') ? 1 : 0) + ($order->getAttribute('path_purchase_order') ? 1 : 0) + ($order->getAttribute('path_delivery_note') ? 1 : 0);
                @endphp
                <tr style="{{ $order->jours_attente >= 7 ? 'background: #fef2f2;' : '' }}">
                    <td class="fw-semibold">
                        @if($order->jours_attente >= 7)<span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #ef4444; margin-right: 4px;"></span>@endif
                        {{ $order->getOrderNumber() }}
                    </td>
                    <td>{{ $order->author ? $order->author->getFirstName() . ' ' . $order->author->getLastName() : '—' }}</td>
                    <td>{{ $order->department?->getName() ?? '—' }}</td>
                    <td>{{ $order->supplier?->getCompanyName() ?? '—' }}</td>
                    <td>{{ number_format($order->total_ttc ?? 0, 2, ',', ' ') }} &euro;</td>
                    <td>{{ $order->updated_at->format('d/m/Y') }}</td>
                    <td>
                        @if($order->jours_attente >= 7)
                            <span class="fw-bold" style="color: #ef4444;">{{ $order->jours_attente }}j</span>
                        @else
                            <span class="badge b-grey">{{ $order->jours_attente }}j</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge b-grey" style="border-radius: 12px;">
                            <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M4.5 3a2.5 2.5 0 0 1 5 0v9a1.5 1.5 0 0 1-3 0V5a.5.5 0 0 1 1 0v7a.5.5 0 0 0 1 0V3a1.5 1.5 0 1 0-3 0v9a2.5 2.5 0 0 0 5 0V5a.5.5 0 0 1 1 0v7a3.5 3.5 0 1 1-7 0z"/></svg>
                            {{ $pjCount }}
                        </span>
                    </td>
                    <td class="text-nowrap">
                        <button class="btn btn-sm text-white btn-load-modal" style="background: var(--navy); border-radius: 18px;" data-url="{{ route('orders.modal.signature', ['id' => $order->getId()]) }}">Consulter</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
@endsection
