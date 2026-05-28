@extends('base')

@section('page-title', 'Fournisseurs')

@section('content')
    @use(Database\Seeders\PermissionValue)
    @use(App\Models\Role)
    @use(App\Models\Supplier)

    {{-- Messages flash --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Erreur lors de l'ajout :</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- 4 KPIs --}}
    <div class="kpis-grid mb-4">
        @php
            $kpis = [
                ['value' => $kpiTotal, 'label' => 'Total fournisseurs'],
                ['value' => $kpiActifs, 'label' => 'Actifs'],
                ['value' => $kpiCommandesMois, 'label' => 'Commandes ce mois'],
                ['value' => $kpiDelaiMoyen, 'label' => 'Delai moyen livraison'],
            ];
        @endphp
        @foreach($kpis as $kpi)
            <div class="kpi-card">
                <div class="kpi-value">{{ $kpi['value'] }}</div>
                <div class="kpi-label">{{ $kpi['label'] }}</div>
            </div>
        @endforeach
    </div>

    {{-- Recherche + bouton ajouter --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <form method="GET" action="{{ url('/suppliers') }}" class="d-flex gap-2 flex-grow-1" style="max-width: 500px;">
            <div class="position-relative flex-grow-1">
                <input type="text" name="search" class="form-control" placeholder="Rechercher un fournisseur..." value="{{ $search ?? '' }}">
            </div>
            <button type="submit" class="btn btn-primary">Rechercher</button>
            @if(isset($search) && $search)
                <a href="{{ url('/suppliers') }}" class="btn btn-secondary">Effacer</a>
            @endif
        </form>

        @if ($user->hasPermission(PermissionValue::GERER_FOURNISSEURS) || $user->hasPermission(PermissionValue::DEMANDER_AJOUT_FOURNISSEUR))
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="me-1">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3z"/>
                </svg>
                Ajouter
            </button>
            <x-supplierCreationModal :user="$user"/>
        @endif
    </div>

    {{-- Table --}}
    <table class="data-table">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Email</th>
                <th class="d-none d-md-table-cell">Telephone</th>
                <th>Nb commandes</th>
                <th>Montant total</th>
                <th>Statut</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($suppliers as $supplier)
                <tr style="{{ !$supplier->isValid() ? 'opacity: 0.6;' : '' }}">
                    <td><strong>{{ $supplier->getCompanyName() }}</strong></td>
                    <td><span class="small">{{ $supplier->getEmail() ?? '—' }}</span></td>
                    <td class="d-none d-md-table-cell"><span class="small">{{ $supplier->getPhoneNumber() ?? '—' }}</span></td>
                    <td>{{ $supplier->orders()->count() }}</td>
                    <td>{{ number_format($supplier->orders()->sum('total_ttc'), 2, ',', ' ') }} &euro;</td>
                    <td>
                        @if($supplier->isValid())
                            <span class="badge b-green">Actif</span>
                        @else
                            <span class="badge b-red">Inactif</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1 flex-wrap">
                            <button class="btn btn-sm btn-ghost btn-load-modal" data-url="{{ route('suppliers.modal.viewDetails', ['id' => $supplier->getId(), 'edit' => false]) }}">Détails</button>
                            @if($user->hasPermission(PermissionValue::GERER_FOURNISSEURS))
                                <button class="btn btn-sm btn-ghost btn-load-modal" data-url="{{ route('suppliers.modal.viewDetails', ['id' => $supplier->getId(), 'edit' => true]) }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/><path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/></svg>
                                </button>
                                <form method="POST" action="{{ route('suppliers.toggleValid', $supplier->getId()) }}" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    @if($supplier->isValid())
                                        <button type="submit" class="btn btn-sm" style="background: #fff3cd; color: #92400e; border: 1px solid #fbbf24; border-radius: 8px;" title="Suspendre">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M5.5 3.5A1.5 1.5 0 0 1 7 5v6a1.5 1.5 0 0 1-3 0V5a1.5 1.5 0 0 1 1.5-1.5m5 0A1.5 1.5 0 0 1 12 5v6a1.5 1.5 0 0 1-3 0V5a1.5 1.5 0 0 1 1.5-1.5"/></svg>
                                        </button>
                                    @else
                                        <button type="submit" class="btn btn-sm" style="background: #d4edda; color: #155724; border: 1px solid #2BAE66; border-radius: 8px;" title="Réactiver">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z"/></svg>
                                        </button>
                                    @endif
                                </form>
                                @if(!$supplier->orders()->exists())
                                    <form method="POST" action="{{ route('suppliers.destroy', $supplier->getId()) }}" class="d-inline" onsubmit="return confirm('Supprimer ce fournisseur ? Cette action est irréversible.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm" style="background: #fef2f2; color: #E5484D; border: 1px solid #E5484D; border-radius: 8px;" title="Supprimer">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/><path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1h2.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/></svg>
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Aucun fournisseur.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-3">
        {{ $suppliers->links() }}
    </div>
@endsection
