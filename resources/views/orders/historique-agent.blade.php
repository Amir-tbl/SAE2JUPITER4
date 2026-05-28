@extends('base')

@section('page-title', 'Historique')

@section('content')
{{-- Filtres --}}
<div class="card mb-4" style="border-radius: var(--card-radius);">
    <div class="card-body">
        <form method="GET" action="{{ route('orders.historique-agent') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-uppercase">Du</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-uppercase">Au</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-uppercase">Statut</label>
                <select name="status" class="form-select">
                    <option value="">Tous</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('status') == $status->value)>{{ $status->getLabel() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn text-white" style="background: var(--navy); border-radius: 18px;">Filtrer</button>
                <a href="{{ route('orders.historique-agent') }}" class="btn btn-outline-secondary" style="border-radius: 18px;">Reinitialiser</a>
                <a href="{{ route('orders.historique-agent.export', request()->query()) }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 18px;">
                    <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16" class="me-1"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/><path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/></svg>
                    CSV
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
@if($orders->isEmpty())
    <p class="text-muted">Aucune commande trouvee.</p>
@else
    <table class="data-table">
        <thead>
            <tr>
                <th>N°</th>
                <th>Demandeur</th>
                <th>Fournisseur</th>
                <th>Montant</th>
                <th>Date</th>
                <th>Statut</th>
                <th>PJ</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
                @php
                    $pjCount = 0;
                    if ($order->getAttribute('path_quote')) $pjCount++;
                    if ($order->getAttribute('path_purchase_order')) $pjCount++;
                    if ($order->getAttribute('path_delivery_note')) $pjCount++;
                @endphp
                <tr>
                    <td class="fw-semibold">#{{ $order->getOrderNumber() }}</td>
                    <td>{{ $order->author ? $order->author->getFirstName() . ' ' . $order->author->getLastName() : '-' }}</td>
                    <td>{{ $order->supplier?->getCompanyName() ?? '-' }}</td>
                    <td>{{ number_format($order->total_ttc ?? 0, 2, ',', ' ') }} &euro;</td>
                    <td>{{ $order->created_at->format('d/m/Y') }}</td>
                    <td><span class="badge {{ $order->getStatus()->getBadgeClass() }}">{{ $order->getStatus()->getLabel() }}</span></td>
                    <td>
                        <span class="pj-badge {{ $pjCount > 0 ? 'has-docs' : 'no-docs' }}">
                            <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M4.5 3a2.5 2.5 0 0 1 5 0v9a1.5 1.5 0 0 1-3 0V5a.5.5 0 0 1 1 0v7a.5.5 0 0 0 1 0V3a1.5 1.5 0 1 0-3 0v9a2.5 2.5 0 0 0 5 0V5a.5.5 0 0 1 1 0v7a3.5 3.5 0 1 1-7 0z"/></svg>
                            {{ $pjCount }}
                        </span>
                    </td>
                    <td class="text-nowrap">
                        @if(in_array($order->getStatus(), [\Database\Seeders\Status::COMMANDE, \Database\Seeders\Status::COMMANDE_AVEC_REPONSE]))
                            <button class="btn btn-sm btn-outline-secondary btn-load-modal" style="border-radius: 18px;" data-url="{{ route('order.modal.relance', ['id' => $order->getId()]) }}">Relancer</button>
                        @endif
                        <button class="btn btn-sm btn-ghost btn-load-modal" data-url="{{ route('orders.modal.viewDetails', ['id' => $order->getId()]) }}">Details</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-3">
        {{ $orders->links() }}
    </div>
@endif
@endsection
