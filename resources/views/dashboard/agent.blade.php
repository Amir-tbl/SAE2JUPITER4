{{-- Tableau de bord de l'agent : indicateurs et commandes récentes du département --}}
@extends('base')

@section('page-title', 'Tableau de bord')

@section('content')
{{-- Banniere bleue gradient --}}
<div class="rounded-3 p-4 mb-4 text-white" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
    <h2 class="fw-bold mb-1">Bienvenue, {{ $user->getFirstName() }}</h2>
    <p class="mb-0 opacity-75">Gestion et suivi de vos demandes d'envoi</p>
</div>

{{-- 6 KPIs --}}
<div class="kpis-grid">
    @php
        $kpis = [
            ['value' => $kpiEnCours, 'label' => 'Commandes en cours'],
            ['value' => $kpiLivrees, 'label' => 'Commandes livrees'],
            ['value' => $kpiEnRetard, 'label' => 'En retard'],
            ['value' => $kpiAttenteValidation, 'label' => 'En attente de validation'],
            ['value' => $kpiCeMois, 'label' => 'Commandes ce mois'],
            ['value' => $kpiMontantTotal, 'label' => 'Montant total'],
        ];
    @endphp
    @foreach($kpis as $i => $kpi)
        <div class="kpi-card">
            <div class="kpi-value" style="{{ $i === 2 && $kpi['value'] > 0 ? 'color: var(--badge-red);' : '' }}">{{ $kpi['value'] }}</div>
            <div class="kpi-label">{{ $kpi['label'] }}</div>
        </div>
    @endforeach
</div>

{{-- Actions rapides --}}
<div class="quick-actions">
    <a href="{{ route('orders.create.step1') }}" class="quick-action-card">
        <div class="quick-action-icon" style="background: #E0E7FF; color: #3A6CF0;">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16"><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/></svg>
        </div>
        <span>Creer une commande</span>
    </a>
    <a href="/orders/historique-agent" class="quick-action-card">
        <div class="quick-action-icon" style="background: #E7ECF7; color: #1E2A52;">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16"><path d="M8.515 1.019A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022zm2.004.45a7 7 0 0 0-.985-.299l.219-.976q.576.129 1.126.342zm1.37.71a7 7 0 0 0-.439-.27l.493-.87a8 8 0 0 1 .979.654l-.615.789a7 7 0 0 0-.418-.302zm1.834 1.79a7 7 0 0 0-.653-.796l.724-.69q.406.429.747.91zm.744 1.352a7 7 0 0 0-.214-.468l.893-.45a8 8 0 0 1 .45 1.088l-.95.313a7 7 0 0 0-.179-.483m.53 2.507a7 7 0 0 0-.1-1.025l.985-.17q.1.58.116 1.17zm-.131 1.538q.05-.254.081-.51l.993.123a8 8 0 0 1-.23 1.155l-.964-.267q.069-.247.12-.501m-.952 2.379q.276-.436.486-.908l.914.405q-.24.54-.555 1.038zm-1.398 1.8a7 7 0 0 0 .573-.756l.832.55a8 8 0 0 1-.652.861l-.753-.655m-1.103.692a7 7 0 0 0 .496-.44l.687.727a8 8 0 0 1-.964.857zm-.935.354a7 7 0 0 0 .354-.144l.404.915a8 8 0 0 1-.9.378zm-8.28-3.483A7 7 0 0 0 8 15a7 7 0 0 0 4.215-1.402L8 8.5V1a7 7 0 0 0-7 7 7 7 0 0 0 .967 3.583"/></svg>
        </div>
        <span>Historique</span>
    </a>
</div>

{{-- Activite recente --}}
<h6 class="section-title">Activite recente</h6>
@if($recentLogs->isEmpty())
    <p class="text-muted mb-4">Aucune activite recente.</p>
@else
    <div class="activity-list mb-4">
        @foreach($recentLogs as $log)
            <div class="activity-item">
                <div class="activity-dot {{ $log->type ?? 'status_change' }}"></div>
                <div>
                    <p class="activity-text">{{ $log->getContent() }}</p>
                    <span class="activity-time">
                        {{ $log->author ? $log->author->getFirstName() . ' ' . $log->author->getLastName() : 'Systeme' }}
                        - {{ $log->created_at->format('d/m/Y H:i') }}
                    </span>
                </div>
            </div>
        @endforeach
    </div>
@endif

{{-- Table commandes recentes --}}
<h6 class="section-title">Commandes recentes</h6>
@if($recentOrders->isEmpty())
    <p class="text-muted">Aucune commande.</p>
@else
    <table class="data-table">
        <thead>
            <tr>
                <th>N°</th>
                <th>Fournisseur</th>
                <th>Date</th>
                <th>Montant</th>
                <th>Statut</th>
                <th>PJ</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recentOrders as $order)
                @php
                    $pjCount = 0;
                    if ($order->getAttribute('path_quote')) $pjCount++;
                    if ($order->getAttribute('path_purchase_order')) $pjCount++;
                    if ($order->getAttribute('path_delivery_note')) $pjCount++;
                @endphp
                <tr>
                    <td class="fw-semibold">#{{ $order->getOrderNumber() }}</td>
                    <td>{{ $order->supplier?->getCompanyName() ?? '-' }}</td>
                    <td>{{ $order->created_at->format('d/m/Y') }}</td>
                    <td>{{ number_format($order->total_ttc ?? 0, 2, ',', ' ') }} &euro;</td>
                    <td><span class="badge {{ $order->getStatus()->getBadgeClass() }}">{{ $order->getStatus()->getLabel() }}</span></td>
                    <td>
                        <span class="pj-badge {{ $pjCount > 0 ? 'has-docs' : 'no-docs' }}">
                            <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M4.5 3a2.5 2.5 0 0 1 5 0v9a1.5 1.5 0 0 1-3 0V5a.5.5 0 0 1 1 0v7a.5.5 0 0 0 1 0V3a1.5 1.5 0 1 0-3 0v9a2.5 2.5 0 0 0 5 0V5a.5.5 0 0 1 1 0v7a3.5 3.5 0 1 1-7 0z"/></svg>
                            {{ $pjCount }}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-ghost btn-load-modal" data-url="{{ route('orders.modal.viewDetails', ['id' => $order->getId()]) }}">Details</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
@endsection
