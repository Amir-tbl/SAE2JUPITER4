@extends('base')
@section('page-title', 'Réception colis')

@section('content')
{{-- Filtres --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('orders.reception') }}" class="row g-2 align-items-end">
            <div class="col-auto">
                <label class="form-label small text-muted mb-1">Département</label>
                <select name="department" class="form-select form-select-sm" style="min-width: 160px;">
                    <option value="">Tous</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department') == $dept->id ? 'selected' : '' }}>{{ $dept->getName() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label small text-muted mb-1">Fournisseur</label>
                <select name="supplier" class="form-select form-select-sm" style="min-width: 180px;">
                    <option value="">Tous</option>
                    @foreach($suppliers as $sup)
                    <option value="{{ $sup->id }}" {{ request('supplier') == $sup->id ? 'selected' : '' }}>{{ $sup->company_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm text-white" style="background: var(--navy); border-radius: 18px;">Filtrer</button>
            </div>
            <div class="col-auto">
                <a href="{{ route('orders.reception') }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 18px;">Réinitialiser</a>
            </div>
        </form>
    </div>
</div>

{{-- KPIs --}}
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="fw-bold" style="font-size: 32px;">{{ $kpiTotal }}</div>
                <div class="text-muted" style="font-size: 13px;">Total en attente</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="fw-bold" style="font-size: 32px;">{{ $kpiRecusAujourdhui }}</div>
                <div class="text-muted" style="font-size: 13px;">Reçus aujourd'hui</div>
            </div>
        </div>
    </div>
</div>

{{-- Flash --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Table --}}
<div class="table-responsive">
    <table class="table table-sm" style="background: rgba(183,157,137,0.07);">
        <thead style="background: rgba(255,255,255,0.38);">
            <tr>
                <th style="width:130px">N° Commande</th>
                <th style="width:140px">Destinataire</th>
                <th style="width:100px">Département</th>
                <th style="width:130px">Fournisseur</th>
                <th style="width:160px">N° Suivi</th>
                <th style="width:100px">Date exp.</th>
                <th style="width:80px">Transit</th>
                <th style="width:80px">PJ</th>
                <th style="width:160px">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
            @php
                $transitJours = (int) $order->created_at->diffInDays(now());
                $nbPj = ($order->path_quote ? 1 : 0) + ($order->path_purchase_order ? 1 : 0) + ($order->path_delivery_note ? 1 : 0);
            @endphp
            <tr>
                <td class="fw-semibold">#{{ $order->getOrderNumber() }}</td>
                <td>{{ $order->author ? $order->author->getFirstName() . ' ' . $order->author->getLastName() : '—' }}</td>
                <td>{{ $order->department?->getName() }}</td>
                <td>{{ $order->supplier?->company_name ?? '—' }}</td>
                <td class="text-muted small">{{ $order->getOrderNumber() }}</td>
                <td>{{ $order->created_at->format('d/m/Y') }}</td>
                <td>{{ $transitJours }}j</td>
                <td>
                    @if($nbPj > 0)
                    <span class="badge b-grey">{{ $nbPj }}</span>
                    @else
                    <span class="text-muted">0</span>
                    @endif
                </td>
                <td class="text-nowrap">
                    <button class="btn btn-sm btn-outline-secondary btn-load-modal" data-url="{{ route('orders.modal.viewDetailsCrit', ['id' => $order->getId()]) }}" style="border-radius: 18px;">Voir</button>
                    <button class="btn btn-sm text-white" style="background: var(--navy); border-radius: 18px;" data-bs-toggle="modal" data-bs-target="#receptionModal{{ $order->getId() }}">Réceptionner</button>
                </td>
            </tr>

            {{-- Modal Réception --}}
            <div class="modal fade" id="receptionModal{{ $order->getId() }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('order.receive', $order->getId()) }}">
                            @csrf
                            <div class="modal-header" style="background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: white;">
                                <h5 class="modal-title fw-bold">Réceptionner le colis</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="p-3 rounded mb-3" style="background: #f8fafc;">
                                    <div class="row">
                                        <div class="col-6"><small class="text-muted">N° Commande</small><p class="fw-bold mb-0">#{{ $order->getOrderNumber() }}</p></div>
                                        <div class="col-6"><small class="text-muted">Destinataire</small><p class="fw-bold mb-0">{{ $order->author ? $order->author->getFirstName() . ' ' . $order->author->getLastName() : '—' }}</p></div>
                                    </div>
                                </div>

                                @if($order->delivery_location)
                                <div class="p-3 rounded mb-3" style="background: #f0f9ff; border: 2px solid #3b82f6;">
                                    <small class="text-muted fw-bold text-uppercase">Lieu de livraison</small>
                                    <p class="mb-0 fw-bold fs-5">{{ $order->delivery_location }}</p>
                                </div>
                                @endif

                                <div class="p-3 rounded mb-3" style="background: #f0f9ff;">
                                    <small class="fw-bold">Vérifiez avant de réceptionner :</small>
                                    <ul class="mb-0 mt-1 small">
                                        <li>Le colis est physiquement présent</li>
                                        <li>L'emballage est intact</li>
                                        <li>Le bon de livraison correspond</li>
                                    </ul>
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius: 18px;">Annuler</button>
                                <button type="submit" class="btn text-white" style="background: var(--navy); border-radius: 18px;">Confirmer la réception</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <tr><td colspan="9" class="text-center text-muted py-4">Aucun colis en attente de réception.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
