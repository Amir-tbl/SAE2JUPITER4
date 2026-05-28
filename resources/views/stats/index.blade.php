@extends('base')
@section('page-title', 'Statistiques')

@section('content')
{{-- KPIs globaux --}}
<div class="kpis-grid mb-4">
    <div class="kpi-card"><div class="kpi-value">{{ $totalOrders }}</div><div class="kpi-label">Total commandes</div></div>
    <div class="kpi-card"><div class="kpi-value">{{ number_format($totalMontant, 2, ',', ' ') }} &euro;</div><div class="kpi-label">Montant total</div></div>
    <div class="kpi-card"><div class="kpi-value">{{ $totalUsers }}</div><div class="kpi-label">Utilisateurs</div></div>
    <div class="kpi-card"><div class="kpi-value">{{ $totalSuppliers }}</div><div class="kpi-label">Fournisseurs actifs</div></div>
    <div class="kpi-card"><div class="kpi-value">{{ $delaiMoyen }}</div><div class="kpi-label">Delai moyen</div></div>
    <div class="kpi-card"><div class="kpi-value">{{ $tauxValidation }}</div><div class="kpi-label">Taux de validation</div></div>
</div>

<div class="row g-4">
    {{-- Par statut --}}
    <div class="col-md-6">
        <div class="rounded-3 p-3" style="background: white; border: 1px solid rgba(183,157,137,0.2);">
            <h6 class="text-muted text-uppercase small fw-bold mb-3">Commandes par statut</h6>
            <table class="table table-sm mb-0">
                <tbody>
                    @foreach($byStatus as $status => $count)
                    @php $statusEnum = \Database\Seeders\Status::tryFrom($status); @endphp
                    <tr>
                        <td><span class="badge {{ $statusEnum?->getBadgeClass() ?? 'b-grey' }}">{{ $statusEnum?->getLabel() ?? $status }}</span></td>
                        <td class="text-end fw-bold">{{ $count }}</td>
                        <td class="text-end text-muted small">{{ $totalOrders > 0 ? round(($count / $totalOrders) * 100) : 0 }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Par departement --}}
    <div class="col-md-6">
        <div class="rounded-3 p-3" style="background: white; border: 1px solid rgba(183,157,137,0.2);">
            <h6 class="text-muted text-uppercase small fw-bold mb-3">Montants par departement</h6>
            <table class="table table-sm mb-0">
                <thead>
                    <tr><th>Departement</th><th class="text-end">Commandes</th><th class="text-end">Montant TTC</th></tr>
                </thead>
                <tbody>
                    @foreach($byDepartment as $row)
                    <tr>
                        <td class="fw-semibold">{{ $row->getName() }}</td>
                        <td class="text-end">{{ $row->orders_count }}</td>
                        <td class="text-end fw-bold">{{ number_format($row->orders_sum_total_ttc ?? 0, 2, ',', ' ') }} &euro;</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Top fournisseurs --}}
    <div class="col-md-6">
        <div class="rounded-3 p-3" style="background: white; border: 1px solid rgba(183,157,137,0.2);">
            <h6 class="text-muted text-uppercase small fw-bold mb-3">Top fournisseurs</h6>
            <table class="table table-sm mb-0">
                <thead>
                    <tr><th>Fournisseur</th><th class="text-end">Commandes</th><th class="text-end">Montant TTC</th></tr>
                </thead>
                <tbody>
                    @foreach($topSuppliers as $supplier)
                    <tr>
                        <td class="fw-semibold">{{ $supplier->getCompanyName() }}</td>
                        <td class="text-end">{{ $supplier->orders_count }}</td>
                        <td class="text-end fw-bold">{{ number_format($supplier->orders_sum_total_ttc ?? 0, 2, ',', ' ') }} &euro;</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Evolution mensuelle --}}
    <div class="col-md-6">
        <div class="rounded-3 p-3" style="background: white; border: 1px solid rgba(183,157,137,0.2);">
            <h6 class="text-muted text-uppercase small fw-bold mb-3">Evolution mensuelle</h6>
            <table class="table table-sm mb-0">
                <thead>
                    <tr><th>Mois</th><th class="text-end">Commandes</th><th class="text-end">Montant TTC</th></tr>
                </thead>
                <tbody>
                    @foreach($monthly as $row)
                    <tr>
                        <td>{{ $row->month }}</td>
                        <td class="text-end fw-bold">{{ $row->count }}</td>
                        <td class="text-end">{{ number_format($row->montant ?? 0, 2, ',', ' ') }} &euro;</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
