@extends('base')

@section('page-title', 'Commandes')

@section('content')
@use(Database\Seeders\Status)
@use(Database\Seeders\PermissionValue)

{{-- Barre de recherche et filtres --}}
<div class="rounded-3 p-3 mb-4" style="background: white; border: 1px solid rgba(183,157,137,0.2);">
    <form method="GET" action="{{ url('/orders') }}">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold text-uppercase" style="font-size: 11px;">Recherche</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="N° commande, designation, devis..." value="{{ $search ?? '' }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-uppercase" style="font-size: 11px;">Statut</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Tous les statuts</option>
                    @foreach(Status::cases() as $s)
                        <option value="{{ $s->value }}" {{ ($statusFilter ?? '') == $s->value ? 'selected' : '' }}>{{ $s->getLabel() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm text-white" style="background: var(--navy);">Rechercher</button>
                @if(($search ?? '') || ($statusFilter ?? ''))
                    <a href="{{ url('/orders') }}" class="btn btn-sm btn-outline-secondary">Effacer</a>
                @endif
            </div>
            @if($user->hasPermission(PermissionValue::CREER_COMMANDES) && !empty($userDepartments) && $userDepartments->isNotEmpty())
            <div class="col-md-2 text-end">
                <a href="{{ route('orders.create.step1') }}" class="btn btn-sm text-white" style="background: var(--accent);">+ Nouvelle commande</a>
            </div>
            @endif
        </div>
    </form>
</div>

{{-- Pagination haut --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <span class="text-muted small">{{ $orders->total() }} commande{{ $orders->total() > 1 ? 's' : '' }}</span>
    <div>{{ $orders->links() }}</div>
</div>

{{-- Table des commandes --}}
<div class="table-responsive">
    <table class="data-table" id="ordersTable">
        <thead>
            <tr>
                <th>N°</th>
                <th>Designation</th>
                <th>Departement</th>
                <th>Fournisseur</th>
                <th>Montant TTC</th>
                <th>Statut</th>
                <th>Date creation</th>
                <th style="text-align: right;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
            <tr>
                <td class="fw-semibold text-nowrap">#{{ $order->getOrderNumber() }}</td>
                <td>{{ $order->getTitle() }}</td>
                <td>{{ $order->getDepartment()->getName() }}</td>
                <td>{{ $order->supplier?->getCompanyName() ?? '—' }}</td>
                <td class="text-nowrap">{{ number_format($order->total_ttc ?? 0, 2, ',', ' ') }} &euro;</td>
                <td>
                    <span class="badge {{ $order->getStatus()->getBadgeClass() }}">{{ $order->getStatus()->getLabel() }}</span>
                </td>
                <td class="text-nowrap">{{ \Carbon\Carbon::parse($order->getCreationDate())->format('d/m/Y') }}</td>
                <td style="text-align: right;">
                    <button class="btn btn-sm btn-ghost btn-load-modal" data-url="{{ route('orders.modal.viewDetails', ['id' => $order->getId()]) }}">Details</button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center text-muted py-4">Aucune commande trouvee.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination bas --}}
<div class="d-flex justify-content-end mt-3">
    {{ $orders->links() }}
</div>
@endsection
