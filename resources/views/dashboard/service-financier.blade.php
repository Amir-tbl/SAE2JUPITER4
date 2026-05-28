@extends('base')

@section('page-title', 'Tableau de bord')

@section('content')
{{-- Banniere verte gradient --}}
<div class="rounded-3 p-4 mb-4 text-white" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
    <h2 class="fw-bold mb-1">Bienvenue, {{ $user->getFirstName() }}</h2>
    <p class="mb-0 opacity-75">Validation et suivi des bons de commande</p>
</div>

{{-- 6 KPIs --}}
<div class="kpis-grid">
    @php
        $kpis = [
            ['value' => $kpiAValider, 'label' => 'A valider'],
            ['value' => $kpiAttenteSignature, 'label' => 'Attente signature'],
            ['value' => $kpiBcEnvoyes, 'label' => 'BC envoyes'],
            ['value' => $kpiLivreesMois, 'label' => 'Livrees ce mois'],
            ['value' => $kpiRetards, 'label' => 'Retards'],
            ['value' => $kpiAPayer, 'label' => 'A payer'],
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
    <a href="/orders/validation" class="quick-action-card">
        <div class="quick-action-icon" style="background: #D1FAE5; color: #059669;">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16"><path d="M10.854 7.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 9.793l2.646-2.647a.5.5 0 0 1 .708 0"/><path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1z"/><path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5z"/></svg>
        </div>
        <span>Valider des commandes</span>
    </a>
    <a href="/orders/suivi" class="quick-action-card">
        <div class="quick-action-icon" style="background: #E7ECF7; color: #1E2A52;">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16"><path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2z"/><path d="M5 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 5 8m0-2.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m0 5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5"/></svg>
        </div>
        <span>Suivi commandes</span>
    </a>
    <a href="/suppliers" class="quick-action-card">
        <div class="quick-action-icon" style="background: #FEF3C7; color: #D97706;">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16"><path d="M2 1a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1zm11 0H3v14h3v-2.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5V15h3z"/></svg>
        </div>
        <span>Fournisseurs</span>
    </a>
</div>

{{-- Commandes urgentes (DEVIS > 5 jours) --}}
<h6 class="section-title">Commandes urgentes</h6>
@if($urgentes->isEmpty())
    <p class="text-muted mb-4">Aucune commande urgente.</p>
@else
    <table class="data-table mb-4">
        <thead>
            <tr>
                <th>N°</th>
                <th>Demandeur</th>
                <th>Departement</th>
                <th>Fournisseur</th>
                <th>Montant</th>
                <th>Date</th>
                <th>Jours</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($urgentes as $order)
                <tr>
                    <td class="fw-semibold">#{{ $order->getOrderNumber() }}</td>
                    <td>{{ $order->author ? $order->author->getFirstName() . ' ' . $order->author->getLastName() : '-' }}</td>
                    <td>{{ $order->department?->getName() ?? '—' }}</td>
                    <td>{{ $order->supplier?->getCompanyName() ?? '—' }}</td>
                    <td>{{ number_format($order->total_ttc ?? 0, 2, ',', ' ') }} &euro;</td>
                    <td>{{ $order->created_at->format('d/m/Y') }}</td>
                    <td><span class="badge b-red">{{ (int) $order->created_at->diffInDays(now()) }}j</span></td>
                    <td>
                        <button class="btn btn-sm btn-ghost btn-load-modal" data-url="{{ route('orders.modal.viewDetails', ['id' => $order->getId()]) }}">Details</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

{{-- Commandes a valider --}}
<h6 class="section-title">Commandes à valider</h6>
@if($ordersAValider->isEmpty())
    <p class="text-muted">Aucune commande en attente de validation.</p>
@else
    <table class="data-table">
        <thead>
            <tr>
                <th>N°</th>
                <th>Demandeur</th>
                <th>Département</th>
                <th>Fournisseur</th>
                <th>Montant</th>
                <th>Date</th>
                <th>Jours</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ordersAValider as $order)
                @php $jours = (int) $order->created_at->diffInDays(now()); @endphp
                <tr style="{{ $jours > 5 ? 'background: rgba(239, 68, 68, 0.08);' : '' }}">
                    <td class="fw-semibold">#{{ $order->getOrderNumber() }}</td>
                    <td>{{ $order->author ? $order->author->getFirstName() . ' ' . $order->author->getLastName() : '-' }}</td>
                    <td>{{ $order->department?->getName() ?? '—' }}</td>
                    <td>{{ $order->supplier?->getCompanyName() ?? '—' }}</td>
                    <td>{{ number_format($order->total_ttc ?? 0, 2, ',', ' ') }} &euro;</td>
                    <td>{{ $order->created_at->format('d/m/Y') }}</td>
                    <td><span class="badge {{ $jours > 5 ? 'b-red' : 'b-grey' }}">{{ $jours }}j</span></td>
                    <td>
                        <button class="btn btn-sm text-white btn-load-modal" style="background: var(--navy); border-radius: 18px;" data-url="{{ route('order.modal.validationSF', ['id' => $order->getId()]) }}">Consulter</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <a href="/orders/validation" class="small text-decoration-none" style="color: #059669;">Voir toutes les commandes à valider →</a>
@endif

{{-- Stats mensuelles --}}
<h6 class="section-title mt-4">Statistiques du mois</h6>
<div class="stats-grid mb-4">
    <div class="stat-item" style="background: #f8fafc;">
        <div class="stat-value">{{ $statsMontantMois }}</div>
        <div class="stat-label">Montant total valide</div>
    </div>
    <div class="stat-item" style="background: #f8fafc;">
        <div class="stat-value">{{ $statsValidesMois }}</div>
        <div class="stat-label">Commandes traitees</div>
    </div>
    <div class="stat-item" style="background: #f8fafc;">
        <div class="stat-value">{{ $statsDelaiMoyen }}</div>
        <div class="stat-label">Delai moyen validation</div>
    </div>
    <div class="stat-item" style="background: #f8fafc;">
        <div class="stat-value">{{ $statsTauxValidation }}</div>
        <div class="stat-label">Taux de validation</div>
    </div>
</div>
@endsection
