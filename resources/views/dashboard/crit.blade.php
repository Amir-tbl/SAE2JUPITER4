@extends('base')

@section('page-title', 'Tableau de bord CRIT')

@section('content')
{{-- Bannière --}}
<div class="rounded-3 p-4 mb-4 text-white" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
    <h2 class="fw-bold mb-1">Bienvenue, Service Postal</h2>
    <p class="mb-0 opacity-75">Réception et distribution des colis</p>
</div>

{{-- 6 KPIs --}}
<div class="row g-3 mb-4">
    @php
        $kpis = [
            ['value' => $kpiEnAttente, 'label' => 'Colis en attente réception'],
            ['value' => $kpiRecusAujourdhui, 'label' => 'Reçus aujourd\'hui'],
            ['value' => $kpiADistribuer, 'label' => 'À distribuer'],
            ['value' => $kpiDistribuesAujourdhui, 'label' => 'Distribués aujourd\'hui'],
            ['value' => $kpiAnomalies, 'label' => 'Anomalies signalées'],
            ['value' => $kpiTotalMois, 'label' => 'Total traités ce mois'],
        ];
    @endphp
    @foreach($kpis as $i => $kpi)
    <div class="col-md-4 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-3">
                <div class="fw-bold" style="font-size: 32px;{{ $i === 4 ? ' color: #dc2626;' : '' }}">{{ $kpi['value'] }}</div>
                <div class="text-muted" style="font-size: 13px;">{{ $kpi['label'] }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Actions rapides --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h6 class="fw-bold text-uppercase text-muted small mb-3">Actions rapides</h6>
        <a href="/orders/reception" class="btn text-white me-2" style="background: var(--navy); border-radius: 18px;">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="me-1"><path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2zm3.564 1.426L5.596 5 8 5.961 14.154 3.5zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923zM7.443.184l-7.25 2.9A.5.5 0 0 0 0 3.669v8.662a.5.5 0 0 0 .305.462l7.25 2.9a.5.5 0 0 0 .372 0l7.25-2.9a.5.5 0 0 0 .305-.462V3.669a.5.5 0 0 0-.305-.462z"/></svg>
            Réceptionner un colis
        </a>
        <a href="/orders/distribution" class="btn btn-outline-secondary" style="border-radius: 18px;">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="me-1"><path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-3.998-.085A1.5 1.5 0 0 1 0 10.5zm1.294 7.456A2 2 0 0 1 4.732 11h5.536a2 2 0 0 1 .732-.732V3.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .294.456M12 10a2 2 0 0 1 1.732 1h.768a.5.5 0 0 0 .5-.5V8.35a.5.5 0 0 0-.11-.312l-1.48-1.85A.5.5 0 0 0 13.02 6H12zm-9 1a1 1 0 1 0 0 2 1 1 0 0 0 0-2m9 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2"/></svg>
            Distribuer un colis
        </a>
    </div>
</div>

{{-- Table : Colis en attente de réception --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="section-title mb-0">Colis en attente de réception</h6>
    <a href="/orders/reception" class="small fw-bold" style="color: #d97706; text-decoration: none;">Voir tout &rarr;</a>
</div>
@if($enAttente->isEmpty())
    <p class="text-muted mb-4">Aucun colis en attente.</p>
@else
    <table class="data-table mb-4">
        <thead>
            <tr>
                <th>N° Commande</th>
                <th>Destinataire</th>
                <th>Département</th>
                <th>Fournisseur</th>
                <th>Date exp.</th>
                <th>PJ</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($enAttente as $order)
            @php
                $pjCount = ($order->getAttribute('path_quote') ? 1 : 0) + ($order->getAttribute('path_purchase_order') ? 1 : 0) + ($order->getAttribute('path_delivery_note') ? 1 : 0);
            @endphp
            <tr>
                <td class="fw-semibold">{{ $order->getOrderNumber() }}</td>
                <td>{{ $order->author ? $order->author->getFirstName() . ' ' . $order->author->getLastName() : '—' }}</td>
                <td>{{ $order->department?->getName() ?? '—' }}</td>
                <td>{{ $order->supplier?->getCompanyName() ?? '—' }}</td>
                <td>{{ $order->created_at->format('d/m/Y') }}</td>
                <td>
                    <span class="badge b-grey" style="border-radius: 12px;">
                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M4.5 3a2.5 2.5 0 0 1 5 0v9a1.5 1.5 0 0 1-3 0V5a.5.5 0 0 1 1 0v7a.5.5 0 0 0 1 0V3a1.5 1.5 0 1 0-3 0v9a2.5 2.5 0 0 0 5 0V5a.5.5 0 0 1 1 0v7a3.5 3.5 0 1 1-7 0z"/></svg>
                        {{ $pjCount }}
                    </span>
                </td>
                <td>
                    <a href="/orders/reception" class="btn btn-sm text-white" style="background: var(--navy); border-radius: 18px;">Voir &rarr;</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif

{{-- Table : Colis à distribuer --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="section-title mb-0">Colis à distribuer</h6>
    <a href="/orders/distribution" class="small fw-bold" style="color: #d97706; text-decoration: none;">Voir tout &rarr;</a>
</div>
@if($aDistribuer->isEmpty())
    <p class="text-muted mb-4">Aucun colis à distribuer.</p>
@else
    <table class="data-table mb-4">
        <thead>
            <tr>
                <th>N° Commande</th>
                <th>Destinataire</th>
                <th>Département</th>
                <th>Lieu livraison</th>
                <th>Date réception</th>
                <th>Attente</th>
                <th>PJ</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($aDistribuer as $order)
            @php
                $recLog = $order->logs?->where('type', 'delivery')->filter(fn($l) => str_contains($l->content, 'réceptionné'))->last();
                $joursAttente = $recLog ? (int) $recLog->created_at->diffInDays(now()) : 0;
                $enRetard = $joursAttente > 2;
                $pjCount = ($order->getAttribute('path_quote') ? 1 : 0) + ($order->getAttribute('path_purchase_order') ? 1 : 0) + ($order->getAttribute('path_delivery_note') ? 1 : 0);
            @endphp
            <tr style="{{ $enRetard ? 'background: #fef2f2;' : '' }}">
                <td class="fw-semibold">
                    @if($enRetard)<span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #dc2626; margin-right: 4px;"></span>@endif
                    {{ $order->getOrderNumber() }}
                </td>
                <td>{{ $order->author ? $order->author->getFirstName() . ' ' . $order->author->getLastName() : '—' }}</td>
                <td>{{ $order->department?->getName() ?? '—' }}</td>
                <td>{{ $order->delivery_location ?? '—' }}</td>
                <td>{{ $recLog ? $recLog->created_at->format('d/m/Y') : '—' }}</td>
                <td>
                    @if($enRetard)
                        <span class="fw-bold" style="color: #dc2626;">{{ $joursAttente }}j</span>
                    @else
                        <span class="badge b-grey">{{ $joursAttente }}j</span>
                    @endif
                </td>
                <td>
                    <span class="badge b-grey" style="border-radius: 12px;">
                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M4.5 3a2.5 2.5 0 0 1 5 0v9a1.5 1.5 0 0 1-3 0V5a.5.5 0 0 1 1 0v7a.5.5 0 0 0 1 0V3a1.5 1.5 0 1 0-3 0v9a2.5 2.5 0 0 0 5 0V5a.5.5 0 0 1 1 0v7a3.5 3.5 0 1 1-7 0z"/></svg>
                        {{ $pjCount }}
                    </span>
                </td>
                <td>
                    <a href="/orders/distribution" class="btn btn-sm text-white" style="background: var(--navy); border-radius: 18px;">Distribuer &rarr;</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif

{{-- Stats mensuelles --}}
<h6 class="section-title mt-4">Statistiques du mois</h6>
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="text-center p-3" style="border-radius: 8px; background: #f8fafc;">
            <div class="fw-bold" style="font-size: 24px; color: #d97706;">{{ $statsReceptionnesMois }}</div>
            <div class="text-muted" style="font-size: 13px;">Colis réceptionnés</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="text-center p-3" style="border-radius: 8px; background: #f8fafc;">
            <div class="fw-bold" style="font-size: 24px; color: var(--navy);">{{ $statsDistribuesMois }}</div>
            <div class="text-muted" style="font-size: 13px;">Colis distribués</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="text-center p-3" style="border-radius: 8px; background: #f8fafc;">
            <div class="fw-bold" style="font-size: 24px; color: #D97706;">{{ $statsDelaiMoyen }}j</div>
            <div class="text-muted" style="font-size: 13px;">Délai moyen distribution</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="text-center p-3" style="border-radius: 8px; background: #f8fafc;">
            <div class="fw-bold" style="font-size: 24px; color: #059669;">{{ $topDepartment }}</div>
            <div class="text-muted" style="font-size: 13px;">Dept. le plus actif</div>
        </div>
    </div>
</div>
@endsection
