@extends('base')

@section('page-title', 'Tableau de bord')

@section('content')
{{-- Banniere rouge gradient --}}
<div class="rounded-3 p-4 mb-4 text-white" style="background: linear-gradient(135deg, #dc2626 0%, #e11d48 100%);">
    <h2 class="fw-bold mb-1">Bienvenue, {{ $user->getFirstName() }}</h2>
    <p class="mb-0 opacity-75">Controle total de la plateforme Suivi Colis</p>
</div>

{{-- 6 KPIs --}}
<div class="kpis-grid">
    @foreach($kpis as $kpi)
        <div class="kpi-card">
            <div class="kpi-value">{{ $kpi['value'] }}</div>
            <div class="kpi-label">{{ $kpi['label'] }}</div>
        </div>
    @endforeach
</div>

{{-- Actions rapides --}}
<div class="quick-actions">
    <a href="/users" class="quick-action-card">
        <div class="quick-action-icon" style="background: #E0E7FF; color: #3A6CF0;">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16"><path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1zm-7.978-1L7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002-.014.002zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0M6.936 9.28a6 6 0 0 0-1.23-.247A7 7 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.24 2.24 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816M4.92 10A5.5 5.5 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0m3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4"/></svg>
        </div>
        <span>Gerer les utilisateurs</span>
    </a>
    <a href="/orders" class="quick-action-card">
        <div class="quick-action-icon" style="background: #E7ECF7; color: #1E2A52;">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16"><path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2z"/><path d="M5 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 5 8m0-2.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m0 5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5"/></svg>
        </div>
        <span>Toutes les commandes</span>
    </a>
    <a href="/suppliers" class="quick-action-card">
        <div class="quick-action-icon" style="background: #D1FAE5; color: #2BAE66;">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16"><path d="M2 1a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1zm11 0H3v14h3v-2.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5V15h3z"/></svg>
        </div>
        <span>Fournisseurs</span>
    </a>
    <a href="/logs" class="quick-action-card">
        <div class="quick-action-icon" style="background: #FEF3C7; color: #D97706;">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16"><path d="M5 10.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5"/><path d="M3 0h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-1h1v1a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v1H1V2a2 2 0 0 1 2-2"/><path d="M1 5v-.5a.5.5 0 0 1 1 0V5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0V8h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0v.5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1z"/></svg>
        </div>
        <span>Logs & Audit</span>
    </a>
</div>

{{-- Statistiques par role --}}
<h6 class="section-title">Statistiques par role</h6>
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    @foreach($roleStats as $stat)
    <div style="padding: 16px; background: {{ $stat['gradient'] }}; border-radius: var(--card-radius); color: white;">
        <div style="font-size: 12px; opacity: 0.9; margin-bottom: 8px;">{{ $stat['label'] }}</div>
        <div style="font-size: 24px; font-weight: 700;">{{ $stat['users'] }} utilisateur{{ $stat['users'] > 1 ? 's' : '' }}</div>
        <div style="font-size: 13px; opacity: 0.9; margin-top: 4px;">{{ $stat['detail'] }}</div>
    </div>
    @endforeach
</div>

{{-- Activite par departement --}}
<h6 class="section-title">Activite par departement</h6>
<div class="table-responsive mb-4">
    <table class="data-table">
        <thead>
            <tr>
                <th>Departement</th>
                <th>Utilisateurs</th>
                <th>Commandes</th>
                <th>Montant total</th>
                <th>En cours</th>
            </tr>
        </thead>
        <tbody>
            @foreach($departments as $dept)
                <tr>
                    <td class="fw-semibold">{{ $dept['name'] }}</td>
                    <td>{{ $dept['users'] }}</td>
                    <td>{{ $dept['total'] }}</td>
                    <td>{{ $dept['montant'] }}</td>
                    <td><span class="badge b-blue">{{ $dept['en_cours'] }}</span></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Dernieres commandes --}}
<h6 class="section-title">Dernieres commandes (toutes interfaces)</h6>
@if($recentOrders->isEmpty())
    <p class="text-muted">Aucune commande.</p>
@else
    <div class="table-responsive mb-4">
        <table class="data-table">
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Demandeur</th>
                    <th>Departement</th>
                    <th>Fournisseur</th>
                    <th>Montant TTC</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th style="text-align:right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentOrders as $order)
                    <tr>
                        <td class="fw-semibold">#{{ $order->getOrderNumber() }}</td>
                        <td>{{ $order->author ? $order->author->getFirstName() . ' ' . $order->author->getLastName() : '—' }}</td>
                        <td>{{ $order->department?->getName() ?? '—' }}</td>
                        <td>{{ $order->supplier?->getCompanyName() ?? '—' }}</td>
                        <td>{{ number_format($order->total_ttc ?? 0, 2, ',', ' ') }} &euro;</td>
                        <td>{{ $order->created_at->format('d/m/Y') }}</td>
                        <td><span class="badge {{ $order->getStatus()->getBadgeClass() }}">{{ $order->getStatus()->getLabel() }}</span></td>
                        <td style="text-align:right;">
                            <button class="btn btn-sm btn-ghost btn-load-modal" data-url="{{ route('orders.modal.viewDetails', ['id' => $order->getId()]) }}">Voir</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

{{-- Statistiques systeme ce mois --}}
<h6 class="section-title">Statistiques systeme ce mois</h6>
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    @foreach($systemStats as $stat)
    <div style="padding: 16px; background: #f8fafc; border-radius: var(--card-radius);">
        <div style="font-size: 12px; color: #64748b; margin-bottom: 8px;">{{ $stat['label'] }}</div>
        <div style="font-size: 24px; font-weight: 700; color: var(--navy);">{{ $stat['value'] }}</div>
    </div>
    @endforeach
</div>
@endsection
