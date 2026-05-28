@extends('base')
@section('page-title', 'Historique CRIT')

@section('content')
{{-- Filtres --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('orders.historique-crit') }}" class="row g-2 align-items-end">
            <div class="col-auto">
                <label class="form-label small text-muted mb-1">Période</label>
                <select name="period" class="form-select form-select-sm" style="min-width: 140px;">
                    <option value="">Toutes</option>
                    <option value="today" {{ request('period') === 'today' ? 'selected' : '' }}>Aujourd'hui</option>
                    <option value="week" {{ request('period') === 'week' ? 'selected' : '' }}>Cette semaine</option>
                    <option value="month" {{ request('period') === 'month' ? 'selected' : '' }}>Ce mois</option>
                    <option value="year" {{ request('period') === 'year' ? 'selected' : '' }}>Cette année</option>
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label small text-muted mb-1">Département</label>
                <select name="department" class="form-select form-select-sm" style="min-width: 160px;">
                    <option value="">Tous</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department') == $dept->id ? 'selected' : '' }}>{{ $dept->getName() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label small text-muted mb-1">Type</label>
                <select name="type" class="form-select form-select-sm" style="min-width: 150px;">
                    <option value="">Tous</option>
                    <option value="receptionnes" {{ request('type') === 'receptionnes' ? 'selected' : '' }}>Réceptionnés</option>
                    <option value="distribues" {{ request('type') === 'distribues' ? 'selected' : '' }}>Distribués</option>
                    <option value="anomalie" {{ request('type') === 'anomalie' ? 'selected' : '' }}>Avec anomalie</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm text-white" style="background: var(--navy); border-radius: 18px;">Filtrer</button>
            </div>
            <div class="col-auto">
                <a href="{{ route('orders.historique-crit') }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 18px;">Réinitialiser</a>
            </div>
        </form>
    </div>
</div>

{{-- KPIs --}}
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="fw-bold" style="font-size: 32px;">{{ $kpiTotal }}</div>
                <div class="text-muted" style="font-size: 13px;">Total traités</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="fw-bold" style="font-size: 32px;">{{ $kpiMois }}</div>
                <div class="text-muted" style="font-size: 13px;">Ce mois</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="fw-bold" style="font-size: 32px;">{{ $kpiDelaiMoyen }}{{ is_numeric($kpiDelaiMoyen) ? 'j' : '' }}</div>
                <div class="text-muted" style="font-size: 13px;">Délai moyen distribution</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="fw-bold" style="font-size: 32px; color: #dc2626;">{{ $kpiAnomalies }}</div>
                <div class="text-muted" style="font-size: 13px;">Anomalies signalées</div>
            </div>
        </div>
    </div>
</div>

{{-- Export CSV --}}
<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('orders.historique-crit.export', request()->query()) }}" class="btn btn-outline-secondary btn-sm" style="border-radius: 18px;">
        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="me-1"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/><path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/></svg>
        Exporter CSV
    </a>
</div>

{{-- Table --}}
<div class="table-responsive">
    <table class="table table-sm" style="background: rgba(183,157,137,0.07);">
        <thead style="background: rgba(255,255,255,0.38);">
            <tr>
                <th>N° Commande</th>
                <th>Destinataire</th>
                <th>Département</th>
                <th>Fournisseur</th>
                <th>Date réception</th>
                <th>Date distribution</th>
                <th>Délai (j)</th>
                <th>PJ</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
            @php
                $nbPj = ($order->path_quote ? 1 : 0) + ($order->path_purchase_order ? 1 : 0) + ($order->path_delivery_note ? 1 : 0);
            @endphp
            <tr>
                <td class="fw-semibold">
                    @if($order->has_anomaly)
                    <svg width="14" height="14" fill="#dc2626" viewBox="0 0 16 16" class="me-1"><path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/></svg>
                    @endif
                    #{{ $order->getOrderNumber() }}
                </td>
                <td>{{ $order->author ? $order->author->getFirstName() . ' ' . $order->author->getLastName() : '—' }}</td>
                <td>{{ $order->department?->getName() }}</td>
                <td>{{ $order->supplier?->company_name ?? '—' }}</td>
                <td>{{ $order->reception_date ? $order->reception_date->format('d/m/Y H:i') : '—' }}</td>
                <td>{{ $order->distribution_date ? $order->distribution_date->format('d/m/Y H:i') : '—' }}</td>
                <td>{{ $order->delai_jours !== null ? $order->delai_jours . 'j' : '—' }}</td>
                <td>
                    @if($nbPj > 0)
                    <span class="badge b-grey">{{ $nbPj }}</span>
                    @else
                    <span class="text-muted">0</span>
                    @endif
                </td>
                <td>
                    <button class="btn btn-sm btn-outline-secondary btn-load-modal" data-url="/order/{{ $order->getId() }}/viewDetails" style="border-radius: 18px;">Voir</button>
                </td>
            </tr>
            @empty
            <tr><td colspan="9" class="text-center text-muted py-4">Aucun enregistrement.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
