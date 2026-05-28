@extends('base')
@section('page-title', 'Historique des signatures')

@section('content')
{{-- 4 KPIs --}}
<div class="kpis-grid">
    @php
        $kpis = [
            ['value' => $kpiTotalSignes, 'label' => 'Total signes'],
            ['value' => $kpiSignesCeMois, 'label' => 'Signes ce mois'],
            ['value' => $kpiMontantTotal, 'label' => 'Montant total'],
            ['value' => ($kpiDelaiMoyen ?? 0) . 'j', 'label' => 'Delai moyen'],
        ];
    @endphp
    @foreach($kpis as $kpi)
        <div class="kpi-card">
            <div class="kpi-value">{{ $kpi['value'] }}</div>
            <div class="kpi-label">{{ $kpi['label'] }}</div>
        </div>
    @endforeach
</div>

{{-- Filtres --}}
<div class="card mb-4" style="border-radius: var(--card-radius);">
    <div class="card-body">
        <form method="GET" action="{{ route('orders.historique-signatures') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-uppercase">Periode</label>
                <select name="periode" class="form-select">
                    <option value="">Toutes</option>
                    <option value="today" {{ request('periode') === 'today' ? 'selected' : '' }}>Aujourd'hui</option>
                    <option value="week" {{ request('periode') === 'week' ? 'selected' : '' }}>Cette semaine</option>
                    <option value="month" {{ request('periode') === 'month' ? 'selected' : '' }}>Ce mois</option>
                    <option value="year" {{ request('periode') === 'year' ? 'selected' : '' }}>Cette annee</option>
                </select>
            </div>
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
                <label class="form-label small fw-bold text-uppercase">Statut actuel</label>
                <select name="statut" class="form-select">
                    <option value="">Tous</option>
                    <option value="{{ \Database\Seeders\Status::BON_DE_COMMANDE_SIGNE->value }}" {{ request('statut') == \Database\Seeders\Status::BON_DE_COMMANDE_SIGNE->value ? 'selected' : '' }}>Signe (en attente envoi)</option>
                    <option value="{{ \Database\Seeders\Status::COMMANDE->value }}" {{ request('statut') == \Database\Seeders\Status::COMMANDE->value ? 'selected' : '' }}>BC envoye</option>
                    <option value="{{ \Database\Seeders\Status::COMMANDE_AVEC_REPONSE->value }}" {{ request('statut') == \Database\Seeders\Status::COMMANDE_AVEC_REPONSE->value ? 'selected' : '' }}>Expedie</option>
                    <option value="{{ \Database\Seeders\Status::SERVICE_FAIT->value }}" {{ request('statut') == \Database\Seeders\Status::SERVICE_FAIT->value ? 'selected' : '' }}>Livre</option>
                    <option value="{{ \Database\Seeders\Status::LIVRE_ET_PAYE->value }}" {{ request('statut') == \Database\Seeders\Status::LIVRE_ET_PAYE->value ? 'selected' : '' }}>Paye</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn text-white" style="background: var(--navy); border-radius: 18px;">Filtrer</button>
                <a href="{{ route('orders.historique-signatures') }}" class="btn btn-outline-secondary" style="border-radius: 18px;">Reinitialiser</a>
            </div>
        </form>
    </div>
</div>

{{-- Export CSV --}}
<div class="mb-3 d-flex justify-content-end">
    <a href="{{ route('orders.historique-signatures.export', request()->query()) }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 18px;">
        <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16" class="me-1"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/><path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/></svg>
        Exporter CSV
    </a>
</div>

{{-- Table --}}
@if($orders->isEmpty())
    <p class="text-muted">Aucune signature dans l'historique.</p>
@else
    <table class="data-table">
        <thead>
            <tr>
                <th>N° BC</th>
                <th>Demandeur</th>
                <th>Departement</th>
                <th>Fournisseur</th>
                <th>Montant TTC</th>
                <th>Date signature</th>
                <th>Delai</th>
                <th>PJ</th>
                <th>Statut actuel</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
                @php
                    $statusValue = $order->getStatus()->value ?? $order->status;
                    $badgeStyle = match($statusValue) {
                        \Database\Seeders\Status::BON_DE_COMMANDE_SIGNE->value => 'background: #3b82f6; color: #fff;',
                        \Database\Seeders\Status::COMMANDE->value => 'background: #8b5cf6; color: #fff;',
                        \Database\Seeders\Status::COMMANDE_AVEC_REPONSE->value => 'background: #f59e0b; color: #fff;',
                        \Database\Seeders\Status::PARTIELLEMENT_LIVRE->value, \Database\Seeders\Status::SERVICE_FAIT->value => 'background: #10b981; color: #fff;',
                        \Database\Seeders\Status::LIVRE_ET_PAYE->value => 'background: #2BAE66; color: #fff;',
                        \Database\Seeders\Status::BON_DE_COMMANDE_REFUSE->value => 'background: #ef4444; color: #fff;',
                        default => 'background: #6b7280; color: #fff;',
                    };
                    $pjCount = ($order->getAttribute('path_quote') ? 1 : 0) + ($order->getAttribute('path_purchase_order') ? 1 : 0) + ($order->getAttribute('path_delivery_note') ? 1 : 0);
                @endphp
                <tr>
                    <td class="fw-semibold">{{ $order->getOrderNumber() }}</td>
                    <td>{{ $order->author ? $order->author->getFirstName() . ' ' . $order->author->getLastName() : '—' }}</td>
                    <td>{{ $order->department?->getName() ?? '—' }}</td>
                    <td>{{ $order->supplier?->getCompanyName() ?? '—' }}</td>
                    <td>{{ number_format($order->total_ttc ?? 0, 2, ',', ' ') }} &euro;</td>
                    <td>{{ $order->updated_at->format('d/m/Y') }}</td>
                    <td><span class="badge b-grey">{{ $order->delai_signature }}j</span></td>
                    <td>
                        <span class="badge b-grey" style="border-radius: 12px;">
                            <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M4.5 3a2.5 2.5 0 0 1 5 0v9a1.5 1.5 0 0 1-3 0V5a.5.5 0 0 1 1 0v7a.5.5 0 0 0 1 0V3a1.5 1.5 0 1 0-3 0v9a2.5 2.5 0 0 0 5 0V5a.5.5 0 0 1 1 0v7a3.5 3.5 0 1 1-7 0z"/></svg>
                            {{ $pjCount }}
                        </span>
                    </td>
                    <td><span class="badge" style="{{ $badgeStyle }} border-radius: 12px; padding: 4px 10px;">{{ $order->getStatus()->getLabel() }}</span></td>
                    <td>
                        <button class="btn btn-sm btn-ghost btn-load-modal" data-url="{{ route('orders.modal.historiqueSignatureDetails', ['id' => $order->getId()]) }}">Voir details</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
@endsection
