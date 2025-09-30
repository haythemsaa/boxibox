@extends('layouts.app')

@section('title', 'Gestion SEPA')

@section('actions')
<div class="btn-group" role="group">
    @can('create_mandats')
    <a href="{{ route('mandats.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Nouveau mandat
    </a>
    @endcan
    <div class="btn-group" role="group">
        <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
            <i class="fas fa-file-export me-2"></i>Exporter
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('sepa.export', 'prelevement') }}">
                <i class="fas fa-download me-2"></i>Fichier de prélèvement
            </a></li>
            <li><a class="dropdown-item" href="{{ route('sepa.export', 'mandats') }}">
                <i class="fas fa-file-alt me-2"></i>Liste des mandats
            </a></li>
        </ul>
    </div>
    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#importModal">
        <i class="fas fa-file-import me-2"></i>Importer retours
    </button>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-primary">{{ $stats['total'] ?? 0 }}</div>
                <div class="stat-label">Mandats Total</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-success">{{ $stats['actifs'] ?? 0 }}</div>
                <div class="stat-label">Actifs</div>
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
                <div class="stat-number text-info">{{ $stats['prelevements_mois'] ?? 0 }}</div>
                <div class="stat-label">Prélèvements ce mois</div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('sepa.index') }}" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Rechercher..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="statut" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="brouillon" {{ request('statut') == 'brouillon' ? 'selected' : '' }}>Brouillon</option>
                        <option value="en_attente_signature" {{ request('statut') == 'en_attente_signature' ? 'selected' : '' }}>En attente signature</option>
                        <option value="actif" {{ request('statut') == 'actif' ? 'selected' : '' }}>Actif</option>
                        <option value="suspendu" {{ request('statut') == 'suspendu' ? 'selected' : '' }}>Suspendu</option>
                        <option value="revoque" {{ request('statut') == 'revoque' ? 'selected' : '' }}>Révoqué</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="type" class="form-select">
                        <option value="">Tous les types</option>
                        <option value="recurrent" {{ request('type') == 'recurrent' ? 'selected' : '' }}>Récurrent</option>
                        <option value="ponctuel" {{ request('type') == 'ponctuel' ? 'selected' : '' }}>Ponctuel</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i>Rechercher
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des mandats -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Référence Unique</th>
                            <th>Client</th>
                            <th>IBAN</th>
                            <th>Type</th>
                            <th>Date signature</th>
                            <th>{{ __('app.status') }}</th>
                            <th>{{ __('app.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mandats as $mandat)
                        <tr>
                            <td><strong>{{ $mandat->reference_unique_mandat }}</strong></td>
                            <td>
                                <strong>{{ $mandat->client->nom }} {{ $mandat->client->prenom }}</strong>
                                @if($mandat->client->raison_sociale)
                                    <br><small class="text-muted">{{ $mandat->client->raison_sociale }}</small>
                                @endif
                            </td>
                            <td>
                                <code>{{ substr($mandat->iban, 0, 4) }} **** **** {{ substr($mandat->iban, -4) }}</code>
                            </td>
                            <td>
                                @if($mandat->type == 'recurrent')
                                    <span class="badge bg-primary">Récurrent</span>
                                @else
                                    <span class="badge bg-info">Ponctuel</span>
                                @endif
                            </td>
                            <td>
                                @if($mandat->date_signature)
                                    {{ $mandat->date_signature->format('d/m/Y') }}
                                @else
                                    <span class="text-muted">Non signé</span>
                                @endif
                            </td>
                            <td>
                                @if($mandat->statut == 'actif')
                                    <span class="badge bg-success">Actif</span>
                                @elseif($mandat->statut == 'en_attente_signature')
                                    <span class="badge bg-warning">En attente signature</span>
                                @elseif($mandat->statut == 'brouillon')
                                    <span class="badge bg-secondary">Brouillon</span>
                                @elseif($mandat->statut == 'suspendu')
                                    <span class="badge bg-warning">Suspendu</span>
                                @else
                                    <span class="badge bg-danger">Révoqué</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    @can('view_mandats')
                                    <a href="{{ route('mandats.show', $mandat) }}" class="btn btn-sm btn-info" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endcan
                                    @can('edit_mandats')
                                    <a href="{{ route('mandats.edit', $mandat) }}" class="btn btn-sm btn-warning" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    @if($mandat->statut == 'brouillon' || $mandat->statut == 'en_attente_signature')
                                        @can('activate_mandats')
                                        <form action="{{ route('sepa.mandats.activate', $mandat) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="Activer">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    @endif
                                    @can('delete_mandats')
                                    <form action="{{ route('mandats.destroy', $mandat) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr ?')">
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
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Aucun mandat SEPA trouvé</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $mandats->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal Import -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('sepa.import-returns') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Importer fichier de retour SEPA</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="file" class="form-label">Fichier XML PAIN.002</label>
                        <input type="file" class="form-control" id="file" name="file" accept=".xml" required>
                        <small class="text-muted">Format: PAIN.002 (retours de prélèvement)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-2"></i>Importer
                    </button>
                </div>
            </form>
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