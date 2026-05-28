@extends('base')
@section('page-title', 'Logs & Audit')

@section('content')
{{-- Filtres --}}
<div class="rounded-3 p-3 mb-4" style="background: white; border: 1px solid rgba(183,157,137,0.2);">
    <div class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label small fw-bold text-uppercase" style="font-size: 11px;">Type</label>
            <select id="filterType" class="form-select form-select-sm">
                <option value="">Tous les types</option>
                @foreach($types as $type)
                    <option value="{{ $type }}">{{ $type }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-bold text-uppercase" style="font-size: 11px;">Recherche</label>
            <input type="text" id="filterSearch" class="form-control form-control-sm" placeholder="Auteur, contenu...">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-bold text-uppercase" style="font-size: 11px;">Date</label>
            <input type="date" id="filterDate" class="form-control form-control-sm">
        </div>
        <div class="col-md-3">
            <button id="btnResetFilters" class="btn btn-sm btn-outline-secondary">Reinitialiser</button>
        </div>
    </div>
</div>

{{-- KPIs --}}
<div class="kpis-grid mb-4" style="grid-template-columns: repeat(3, 1fr);">
    <div class="kpi-card"><div class="kpi-value">{{ $totalLogs }}</div><div class="kpi-label">Total logs</div></div>
    <div class="kpi-card"><div class="kpi-value">{{ $todayLogs }}</div><div class="kpi-label">Aujourd'hui</div></div>
    <div class="kpi-card"><div class="kpi-value">{{ $types->count() }}</div><div class="kpi-label">Types distincts</div></div>
</div>

{{-- Table logs --}}
<div class="table-responsive">
    <table class="data-table" id="logsTable">
        <thead>
            <tr>
                <th>Date</th>
                <th>Auteur</th>
                <th>Type</th>
                <th>Contenu</th>
                <th>Commande</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
            @php
                $logDate = \Carbon\Carbon::parse($log->getCreationDate());
                $authorName = $log->author ? $log->author->getFirstName() . ' ' . $log->author->getLastName() : 'Systeme';
            @endphp
            <tr data-type="{{ $log->type ?? 'action' }}" data-date="{{ $logDate->format('Y-m-d') }}" data-search="{{ strtolower($authorName . ' ' . $log->getContent()) }}">
                <td class="text-nowrap">{{ $logDate->format('d/m/Y H:i') }}</td>
                <td>{{ $authorName }}</td>
                <td>
                    @php
                        $badgeClass = match($log->type) {
                            'delivery' => 'b-green',
                            'status_change' => 'b-blue',
                            'comment' => 'b-grey',
                            default => 'b-grey',
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ $log->type ?? 'action' }}</span>
                </td>
                <td>{{ \Illuminate\Support\Str::limit($log->getContent(), 100) }}</td>
                <td>
                    @if($log->order)
                    <a href="#" class="btn-load-modal fw-semibold" data-url="{{ route('orders.modal.viewDetails', ['id' => $log->order->getId()]) }}">
                        #{{ $log->order->getOrderNumber() }}
                    </a>
                    @else
                    <span class="text-muted">—</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var filterType = document.getElementById('filterType');
    var filterSearch = document.getElementById('filterSearch');
    var filterDate = document.getElementById('filterDate');
    var rows = document.querySelectorAll('#logsTable tbody tr');

    function applyFilters() {
        var type = filterType.value.toLowerCase();
        var search = filterSearch.value.toLowerCase();
        var date = filterDate.value;

        rows.forEach(function(row) {
            var rowType = (row.getAttribute('data-type') || '').toLowerCase();
            var rowSearch = (row.getAttribute('data-search') || '').toLowerCase();
            var rowDate = row.getAttribute('data-date') || '';

            var matchType = !type || rowType === type;
            var matchSearch = !search || rowSearch.indexOf(search) !== -1;
            var matchDate = !date || rowDate === date;

            row.style.display = (matchType && matchSearch && matchDate) ? '' : 'none';
        });
    }

    filterType.addEventListener('change', applyFilters);
    filterSearch.addEventListener('input', applyFilters);
    filterDate.addEventListener('change', applyFilters);

    document.getElementById('btnResetFilters').addEventListener('click', function() {
        filterType.value = '';
        filterSearch.value = '';
        filterDate.value = '';
        applyFilters();
    });
});
</script>
@endsection
