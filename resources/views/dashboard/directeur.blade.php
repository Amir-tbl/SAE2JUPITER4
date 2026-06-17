@extends('base')

@section('page-title', 'Tableau de bord')

@section('content')
{{-- Banniere violette gradient --}}
<div class="rounded-3 p-4 mb-4 text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <h2 class="fw-bold mb-1">Bienvenue, {{ $user->getFirstName() }} {{ $user->getLastName() }}</h2>
    <p class="mb-0 opacity-75">Directeur de l'IUT</p>
</div>

{{-- 6 KPIs --}}
<div class="kpis-grid">
    @php
        $kpis = [
            ['value' => $kpiEnAttente, 'label' => 'BC en attente'],
            ['value' => $kpiSignesAujourdhui, 'label' => "Signes aujourd'hui"],
            ['value' => $kpiSignesMois, 'label' => 'Signes ce mois'],
            ['value' => $kpiMontantMois, 'label' => 'Montant valide'],
            ['value' => $kpiUrgents, 'label' => 'BC urgents (>7j)'],
            ['value' => $kpiTotalTraites, 'label' => 'Total traites'],
        ];
    @endphp
    @foreach($kpis as $i => $kpi)
        <div class="kpi-card">
            <div class="kpi-value" style="{{ ($i === 4 && $kpi['value'] > 0) ? 'color: var(--badge-red);' : '' }}">{{ $kpi['value'] }}</div>
            <div class="kpi-label">{{ $kpi['label'] }}</div>
        </div>
    @endforeach
</div>

{{-- Actions rapides --}}
<div class="quick-actions">
    <a href="{{ route('orders.signature') }}" class="quick-action-card">
        <div class="quick-action-icon" style="background: #EDE9FE; color: #7C3AED;">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16"><path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.5.5 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11z"/></svg>
        </div>
        <span>Signer les BC en attente</span>
    </a>
    <a href="{{ route('orders.historique-signatures') }}" class="quick-action-card">
        <div class="quick-action-icon" style="background: #E7ECF7; color: #1E2A52;">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16"><path d="M8.515 1.019A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022zm2.004.45a7 7 0 0 0-.985-.299l.219-.976q.576.129 1.126.342zm1.37.71a7 7 0 0 0-.439-.27l.493-.87a8 8 0 0 1 .979.654l-.615.789a7 7 0 0 0-.418-.302zm1.834 1.79a7 7 0 0 0-.653-.796l.724-.69q.406.429.747.91zm.744 1.352a7 7 0 0 0-.214-.468l.893-.45a8 8 0 0 1 .45 1.088l-.95.313a7 7 0 0 0-.179-.483m.53 2.507a7 7 0 0 0-.1-1.025l.985-.17q.1.58.116 1.17zm-.131 1.538q.05-.254.081-.51l.993.123a8 8 0 0 1-.23 1.155l-.964-.267q.069-.247.12-.501m-.952 2.379q.276-.436.486-.908l.914.405q-.24.54-.555 1.038zm-1.398 1.8a7 7 0 0 0 .573-.756l.832.55a8 8 0 0 1-.652.861l-.753-.655m-1.103.692a7 7 0 0 0 .496-.44l.687.727a8 8 0 0 1-.964.857zm-.935.354a7 7 0 0 0 .354-.144l.404.915a8 8 0 0 1-.9.378zm-8.28-3.483A7 7 0 0 0 8 15a7 7 0 0 0 4.215-1.402L8 8.5V1a7 7 0 0 0-7 7 7 7 0 0 0 .967 3.583"/></svg>
        </div>
        <span>Consulter l'historique</span>
    </a>
</div>

{{-- BC en attente de signature (5 max) --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="section-title mb-0">BC en attente de signature</h6>
    <a href="{{ route('orders.signature') }}" class="small fw-bold" style="color: #667eea; text-decoration: none;">Voir tout &rarr;</a>
</div>
@if($bcEnAttente->isEmpty())
    <p class="text-muted mb-4">Aucun bon de commande en attente.</p>
@else
    <table class="data-table mb-4">
        <thead>
            <tr>
                <th>N° BC</th>
                <th>Demandeur</th>
                <th>Departement</th>
                <th>Fournisseur</th>
                <th>Montant TTC</th>
                <th>Date valid.</th>
                <th>PJ</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bcEnAttente as $order)
                @php
                    $pjCount = ($order->getAttribute('path_quote') ? 1 : 0) + ($order->getAttribute('path_purchase_order') ? 1 : 0) + ($order->getAttribute('path_delivery_note') ? 1 : 0);
                @endphp
                <tr style="{{ $order->isUrgent() ? 'background: #fef2f2;' : '' }}">
                    <td class="fw-semibold">
                        @if($order->isUrgent())<span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #ef4444; margin-right: 4px;"></span>@endif
                        {{ $order->getOrderNumber() }}
                    </td>
                    <td>{{ $order->author ? $order->author->getFirstName() . ' ' . $order->author->getLastName() : '—' }}</td>
                    <td>{{ $order->department?->getName() ?? '—' }}</td>
                    <td>{{ $order->supplier?->getCompanyName() ?? '—' }}</td>
                    <td>{{ number_format($order->total_ttc ?? 0, 2, ',', ' ') }} &euro;</td>
                    <td>{{ $order->updated_at->format('d/m/Y') }}</td>
                    <td>
                        <span class="badge b-grey" style="border-radius: 12px;">
                            <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M4.5 3a2.5 2.5 0 0 1 5 0v9a1.5 1.5 0 0 1-3 0V5a.5.5 0 0 1 1 0v7a.5.5 0 0 0 1 0V3a1.5 1.5 0 1 0-3 0v9a2.5 2.5 0 0 0 5 0V5a.5.5 0 0 1 1 0v7a3.5 3.5 0 1 1-7 0z"/></svg>
                            {{ $pjCount }}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm text-white btn-load-modal" style="background: var(--navy); border-radius: 18px;" data-url="{{ route('orders.modal.signature', ['id' => $order->getId()]) }}">Consulter</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

{{-- Statistiques du mois (4 blocs) --}}
<h6 class="section-title mt-4">Statistiques du mois</h6>
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="text-center p-3" style="border-radius: 8px; background: #f8fafc;">
            <div class="fw-bold" style="font-size: 24px; color: #7C3AED;">{{ $statsSignesMois }}</div>
            <div class="text-muted" style="font-size: 13px;">BC signes</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="text-center p-3" style="border-radius: 8px; background: #f8fafc;">
            <div class="fw-bold" style="font-size: 24px; color: var(--navy);">{{ $statsMontantSigne }}</div>
            <div class="text-muted" style="font-size: 13px;">Montant total</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="text-center p-3" style="border-radius: 8px; background: #f8fafc;">
            <div class="fw-bold" style="font-size: 24px; color: #D97706;">{{ $statsDelaiMoyen ?? 0 }}j</div>
            <div class="text-muted" style="font-size: 13px;">Delai moyen signature</div>
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
