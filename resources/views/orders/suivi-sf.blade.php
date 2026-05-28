@extends('base')
@section('page-title', 'Suivi des commandes')

@section('content')
{{-- 5 KPIs --}}
<div class="kpis-grid mb-4">
    @php
        $kpis = [
            ['value' => $kpiBcNonSignes, 'label' => 'BC non signes'],
            ['value' => $kpiBcSignes, 'label' => 'BC signes'],
            ['value' => $kpiCommandes, 'label' => 'Envoyees fournisseur'],
            ['value' => $kpiServiceFait, 'label' => 'Service fait'],
            ['value' => $kpiPayees, 'label' => 'Payees'],
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
    <div class="card-body py-2">
        <form method="GET" action="{{ route('orders.suivi') }}" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small mb-1">Departement</label>
                <select name="department" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->getId() }}" @selected(request('department') == $dept->getId())>{{ $dept->getName() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Fournisseur</label>
                <select name="supplier" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    @foreach($suppliers as $sup)
                        <option value="{{ $sup->getId() }}" @selected(request('supplier') == $sup->getId())>{{ $sup->getCompanyName() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Statut</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('status') == $status->value)>{{ $status->getLabel() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Du</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Au</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button class="btn btn-sm text-white flex-fill" style="background: var(--navy); border-radius: 18px;">Filtrer</button>
                <a href="{{ route('orders.suivi') }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 18px;">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Export CSV --}}
<div class="d-flex justify-content-end mb-2">
    <a href="{{ route('orders.suivi.export', request()->query()) }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 18px;">
        <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16" class="me-1"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/><path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/></svg>
        Exporter CSV
    </a>
</div>

{{-- Table --}}
<table class="data-table">
    <thead>
        <tr>
            <th>N°</th>
            <th>Departement</th>
            <th>Fournisseur</th>
            <th>Montant TTC</th>
            <th>Statut</th>
            <th>PJ</th>
            <th>Derniere MAJ</th>
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
            <tr>
                <td class="fw-semibold">#{{ $order->getOrderNumber() }}</td>
                <td>{{ $order->department?->getName() ?? '—' }}</td>
                <td>{{ $order->supplier?->getCompanyName() ?? '—' }}</td>
                <td>{{ number_format($order->total_ttc ?? 0, 2, ',', ' ') }} &euro;</td>
                <td><span class="badge {{ $order->getStatus()->getBadgeClass() }}">{{ $order->getStatus()->getLabel() }}</span></td>
                <td>
                    <span class="pj-badge {{ $pjCount > 0 ? 'has-docs' : 'no-docs' }}">
                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M4.5 3a2.5 2.5 0 0 1 5 0v9a1.5 1.5 0 0 1-3 0V5a.5.5 0 0 1 1 0v7a.5.5 0 0 0 1 0V3a1.5 1.5 0 1 0-3 0v9a2.5 2.5 0 0 0 5 0V5a.5.5 0 0 1 1 0v7a3.5 3.5 0 1 1-7 0z"/></svg>
                        {{ $pjCount }}
                    </span>
                </td>
                <td>{{ $order->updated_at->format('d/m/Y') }}</td>
                <td class="text-nowrap">
                    @if($order->getStatus() == \Database\Seeders\Status::BON_DE_COMMANDE_SIGNE)
                        <button class="btn btn-sm text-white btn-load-modal" style="background: var(--navy); border-radius: 18px;" data-url="{{ route('order.modal.envoiBC', ['id' => $order->getId()]) }}">Envoyer BC</button>
                    @elseif(in_array($order->getStatus(), [\Database\Seeders\Status::COMMANDE, \Database\Seeders\Status::COMMANDE_AVEC_REPONSE]))
                        <button class="btn btn-sm btn-outline-secondary btn-load-modal" style="border-radius: 18px;" data-url="{{ route('order.modal.relance', ['id' => $order->getId()]) }}">Relancer</button>
                    @elseif($order->getStatus() == \Database\Seeders\Status::SERVICE_FAIT)
                        <button class="btn btn-sm text-white btn-load-modal" style="background: var(--navy); border-radius: 18px;" data-url="{{ route('order.modal.paiement', ['id' => $order->getId()]) }}">Payer</button>
                    @endif
                    <button class="btn btn-sm btn-ghost btn-load-modal" data-url="{{ route('order.modal.suiviDetailsSF', ['id' => $order->getId()]) }}">Voir</button>
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-muted py-4">Aucune commande dans le suivi.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="mt-3">
    {{ $orders->links() }}
</div>
@endsection
