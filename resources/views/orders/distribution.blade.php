@extends('base')
@section('page-title', 'Distribution')

@section('content')
{{-- Filtres --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('orders.distribution') }}" class="row g-2 align-items-end">
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
                <label class="form-label small text-muted mb-1">Priorité</label>
                <select name="priority" class="form-select form-select-sm" style="min-width: 160px;">
                    <option value="">Toutes</option>
                    <option value="retard" {{ request('priority') === 'retard' ? 'selected' : '' }}>En retard > 2 jours</option>
                    <option value="normal" {{ request('priority') === 'normal' ? 'selected' : '' }}>Normal</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm text-white" style="background: var(--navy); border-radius: 18px;">Filtrer</button>
            </div>
            <div class="col-auto">
                <a href="{{ route('orders.distribution') }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 18px;">Réinitialiser</a>
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
                <div class="text-muted" style="font-size: 13px;">Total à distribuer</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="fw-bold" style="font-size: 32px; color: #dc2626;">{{ $kpiRetard }}</div>
                <div class="text-muted" style="font-size: 13px;">En retard (> 2j)</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="fw-bold" style="font-size: 32px;">{{ $kpiDistribuesAujourdhui }}</div>
                <div class="text-muted" style="font-size: 13px;">Distribués aujourd'hui</div>
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
                <th>N° Commande</th>
                <th>Destinataire</th>
                <th>Département</th>
                <th>Lieu livraison</th>
                <th>Date réception</th>
                <th>Attente (j)</th>
                <th>PJ</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
            @php
                $enRetard = $order->jours_attente > 2;
                $nbPj = ($order->path_quote ? 1 : 0) + ($order->path_purchase_order ? 1 : 0) + ($order->path_delivery_note ? 1 : 0);
            @endphp
            <tr style="{{ $enRetard ? 'background: #fef2f2;' : '' }}">
                <td class="fw-semibold">
                    @if($enRetard)<span class="badge text-white me-1" style="background:#dc2626; font-size:10px; border-radius:4px;">RETARD</span>@endif
                    #{{ $order->getOrderNumber() }}
                </td>
                <td>{{ $order->author ? $order->author->getFirstName() . ' ' . $order->author->getLastName() : '—' }}</td>
                <td>{{ $order->department?->getName() }}</td>
                <td>{{ $order->delivery_location ?? '—' }}</td>
                <td>{{ $order->reception_date ? $order->reception_date->format('d/m/Y') : '—' }}</td>
                <td class="{{ $enRetard ? 'fw-bold' : '' }}" style="{{ $enRetard ? 'color:#dc2626;' : '' }}">{{ $order->jours_attente }}j</td>
                <td>
                    @if($nbPj > 0)
                    <span class="badge b-grey">{{ $nbPj }}</span>
                    @else
                    <span class="text-muted">0</span>
                    @endif
                </td>
                <td class="text-nowrap">
                    <button class="btn btn-sm btn-outline-secondary btn-load-modal" data-url="{{ route('orders.modal.viewDetailsCrit', ['id' => $order->getId()]) }}" style="border-radius: 18px;">Voir</button>
                    <button class="btn btn-sm text-white" style="background: var(--navy); border-radius: 18px;" data-bs-toggle="modal" data-bs-target="#distribModal{{ $order->getId() }}">Distribuer</button>
                </td>
            </tr>

            {{-- Modal Distribution --}}
            <div class="modal fade" id="distribModal{{ $order->getId() }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('order.deliver', $order->getId()) }}">
                            @csrf
                            <div class="modal-header" style="background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: white;">
                                <h5 class="modal-title fw-bold">Distribuer le colis</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="p-3 rounded mb-3" style="background: #f8fafc;">
                                    <div class="row">
                                        <div class="col-6"><small class="text-muted">N° Commande</small><p class="fw-bold mb-0">#{{ $order->getOrderNumber() }}</p></div>
                                        <div class="col-6"><small class="text-muted">Lieu de livraison</small><p class="fw-bold mb-0">{{ $order->delivery_location ?? '—' }}</p></div>
                                    </div>
                                </div>

                                <div class="p-3 rounded mb-3" style="background: #f0fdf4;">
                                    <small>Remettez le colis au destinataire et faites-lui signer l'accusé de réception.</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Nom du receveur (optionnel)</label>
                                    <input type="text" class="form-control" name="receiver_name" placeholder="Nom de la personne qui récupère le colis">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius: 18px;">Annuler</button>
                                <button type="submit" class="btn text-white" style="background: var(--navy); border-radius: 18px;">Confirmer la distribution</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <tr><td colspan="8" class="text-center text-muted py-4">Aucun colis à distribuer.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
