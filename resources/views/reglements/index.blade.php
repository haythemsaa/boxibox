@extends('layouts.app')

@section('title', 'Règlements')

@section('actions')
@can('create_reglements')
    <a href="{{ route('reglements.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Nouveau règlement
    </a>
@endcan
@endsection

@section('content')
<div class="container-fluid">
    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-primary">{{ $stats['total'] ?? 0 }}</div>
                <div class="stat-label">{{ __('app.total') }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-success">{{ $stats['valides'] ?? 0 }}</div>
                <div class="stat-label">Validés</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-warning">{{ $stats['en_attente'] ?? 0 }}</div>
                <div class="stat-label">En Attente</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-success">{{ number_format($stats['montant_total'] ?? 0, 2) }} €</div>
                <div class="stat-label">Montant Total</div>
            </div>
        </div>
    </div>

    <!-- Filtres et recherche -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reglements.index') }}" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Rechercher..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="statut" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="en_attente" {{ request('statut') == 'en_attente' ? 'selected' : '' }}>En Attente</option>
                        <option value="valide" {{ request('statut') == 'valide' ? 'selected' : '' }}>Validé</option>
                        <option value="refuse" {{ request('statut') == 'refuse' ? 'selected' : '' }}>Refusé</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="mode_paiement" class="form-select">
                        <option value="">Tous les modes</option>
                        <option value="virement" {{ request('mode_paiement') == 'virement' ? 'selected' : '' }}>Virement</option>
                        <option value="cheque" {{ request('mode_paiement') == 'cheque' ? 'selected' : '' }}>Chèque</option>
                        <option value="especes" {{ request('mode_paiement') == 'especes' ? 'selected' : '' }}>Espèces</option>
                        <option value="carte" {{ request('mode_paiement') == 'carte' ? 'selected' : '' }}>Carte bancaire</option>
                        <option value="prelevement" {{ request('mode_paiement') == 'prelevement' ? 'selected' : '' }}>Prélèvement</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i>{{ __('app.search') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des règlements -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Référence</th>
                            <th>Date</th>
                            <th>Client</th>
                            <th>Facture</th>
                            <th>Mode de paiement</th>
                            <th>Montant</th>
                            <th>{{ __('app.status') }}</th>
                            <th>{{ __('app.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reglements as $reglement)
                        <tr>
                            <td><strong>{{ $reglement->reference }}</strong></td>
                            <td>{{ $reglement->date_reglement->format('d/m/Y') }}</td>
                            <td>
                                @if($reglement->facture && $reglement->facture->client)
                                    <strong>{{ $reglement->facture->client->nom }} {{ $reglement->facture->client->prenom }}</strong>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($reglement->facture)
                                    <a href="{{ route('factures.show', $reglement->facture) }}">
                                        {{ $reglement->facture->numero_facture }}
                                    </a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($reglement->mode_paiement == 'virement')
                                    <i class="fas fa-university me-1"></i>Virement
                                @elseif($reglement->mode_paiement == 'cheque')
                                    <i class="fas fa-money-check me-1"></i>Chèque
                                @elseif($reglement->mode_paiement == 'especes')
                                    <i class="fas fa-money-bill me-1"></i>Espèces
                                @elseif($reglement->mode_paiement == 'carte')
                                    <i class="fas fa-credit-card me-1"></i>Carte
                                @else
                                    <i class="fas fa-exchange-alt me-1"></i>Prélèvement
                                @endif
                            </td>
                            <td><strong>{{ number_format($reglement->montant, 2) }} €</strong></td>
                            <td>
                                @if($reglement->statut == 'valide')
                                    <span class="badge bg-success">Validé</span>
                                @elseif($reglement->statut == 'en_attente')
                                    <span class="badge bg-warning">En Attente</span>
                                @else
                                    <span class="badge bg-danger">Refusé</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    @can('view_reglements')
                                    <a href="{{ route('reglements.show', $reglement) }}" class="btn btn-sm btn-info" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endcan
                                    @can('edit_reglements')
                                    <a href="{{ route('reglements.edit', $reglement) }}" class="btn btn-sm btn-warning" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    @can('delete_reglements')
                                    <form action="{{ route('reglements.destroy', $reglement) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-euro-sign fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Aucun règlement trouvé</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $reglements->links() }}
            </div>
        </div>
    </div>
</div>

<style>
.stat-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 0.875rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table th {
    background-color: #f8f9fa;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.875rem;
    letter-spacing: 0.5px;
}
</style>
@endsection