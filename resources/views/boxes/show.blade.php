@extends('layouts.app')

@section('title', __('app.box') . ' - ' . $box->numero)

@section('actions')
<div class="btn-group" role="group">
    @can('edit_boxes')
    <a href="{{ route('boxes.edit', $box) }}" class="btn btn-warning">
        <i class="fas fa-edit me-2"></i>{{ __('app.edit') }}
    </a>
    @endcan
    @if($box->statut == 'libre')
        @can('reserve_boxes')
        <form action="{{ route('boxes.reserve', $box) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-info">
                <i class="fas fa-lock me-2"></i>Réserver
            </button>
        </form>
        @endcan
    @elseif($box->statut == 'reserve')
        @can('liberer_boxes')
        <form action="{{ route('boxes.liberer', $box) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-success">
                <i class="fas fa-unlock me-2"></i>Libérer
            </button>
        </form>
        @endcan
    @endif
    <a href="{{ route('boxes.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>{{ __('app.back') }}
    </a>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Statistiques rapides -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-primary">{{ $stats['contrats_actifs'] ?? 0 }}</div>
                <div class="stat-label">Contrats Actifs</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-success">{{ number_format($stats['revenus_mensuels'] ?? 0, 2) }} €</div>
                <div class="stat-label">Revenus Mensuels</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-info">{{ $stats['jours_occupation'] ?? 0 }}</div>
                <div class="stat-label">Jours d'Occupation</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-warning">{{ $stats['interventions_count'] ?? 0 }}</div>
                <div class="stat-label">Interventions</div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Informations du box -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-box me-2"></i>Informations du Box
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Numéro</label>
                        <div><strong class="fs-4">{{ $box->numero }}</strong></div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">{{ __('app.status') }}</label>
                        <div>
                            @if($box->statut == 'libre')
                                <span class="badge bg-success fs-6">Libre</span>
                            @elseif($box->statut == 'occupe')
                                <span class="badge bg-danger fs-6">Occupé</span>
                            @elseif($box->statut == 'reserve')
                                <span class="badge bg-warning fs-6">Réservé</span>
                            @elseif($box->statut == 'maintenance')
                                <span class="badge bg-secondary fs-6">Maintenance</span>
                            @else
                                <span class="badge bg-dark fs-6">{{ $box->statut }}</span>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="text-muted small">Famille</label>
                        <div>
                            @if($box->famille)
                                <strong>{{ $box->famille->nom }}</strong>
                                <br><small class="text-muted">{{ $box->famille->description }}</small>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">Emplacement</label>
                        <div>
                            @if($box->emplacement)
                                <strong>{{ $box->emplacement->nom }}</strong>
                                @if($box->emplacement->description)
                                    <br><small class="text-muted">{{ $box->emplacement->description }}</small>
                                @endif
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="text-muted small">{{ __('app.surface') }}</label>
                                <div><strong>{{ $box->surface }} m²</strong></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="text-muted small">Volume</label>
                                <div><strong>{{ $box->volume ?? 'N/A' }} m³</strong></div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">Prix de base</label>
                        <div><strong class="text-primary fs-5">{{ number_format($box->prix_base ?? 0, 2) }} €/mois</strong></div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">Prix mensuel</label>
                        <div><strong class="text-success fs-5">{{ number_format($box->prix_mensuel ?? 0, 2) }} €/mois</strong></div>
                    </div>

                    @if($box->description)
                    <hr>
                    <div class="mb-3">
                        <label class="text-muted small">Description</label>
                        <div class="text-muted">{{ $box->description }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Contrats et historique -->
        <div class="col-md-8">
            <!-- Contrat actif -->
            @if($box->contratActif)
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-file-contract me-2"></i>Contrat Actif
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small">N° Contrat</label>
                                <div><strong>{{ $box->contratActif->numero_contrat }}</strong></div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">Client</label>
                                <div>
                                    <strong>{{ $box->contratActif->client->nom }} {{ $box->contratActif->client->prenom }}</strong>
                                    @if($box->contratActif->client->raison_sociale)
                                        <br><small class="text-muted">{{ $box->contratActif->client->raison_sociale }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small">Date de début</label>
                                <div>{{ $box->contratActif->date_debut->format('d/m/Y') }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">Date de fin</label>
                                <div>
                                    @if($box->contratActif->date_fin)
                                        {{ $box->contratActif->date_fin->format('d/m/Y') }}
                                    @else
                                        <span class="text-muted">Indéterminée</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <a href="{{ route('contrats.show', $box->contratActif) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye me-2"></i>Voir le contrat
                        </a>
                        <a href="{{ route('clients.show', $box->contratActif->client) }}" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-user me-2"></i>Voir le client
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Historique des contrats -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>Historique des Contrats
                    </h5>
                </div>
                <div class="card-body">
                    @if($box->contrats && $box->contrats->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>N° Contrat</th>
                                        <th>Client</th>
                                        <th>Date Début</th>
                                        <th>Date Fin</th>
                                        <th>Prix</th>
                                        <th>{{ __('app.status') }}</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($box->contrats->sortByDesc('date_debut') as $contrat)
                                    <tr>
                                        <td><strong>{{ $contrat->numero_contrat }}</strong></td>
                                        <td>{{ $contrat->client->nom }} {{ $contrat->client->prenom }}</td>
                                        <td>{{ $contrat->date_debut->format('d/m/Y') }}</td>
                                        <td>
                                            @if($contrat->date_fin)
                                                {{ $contrat->date_fin->format('d/m/Y') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ number_format($contrat->prix_mensuel, 2) }} €</td>
                                        <td>
                                            @if($contrat->statut == 'actif')
                                                <span class="badge bg-success">Actif</span>
                                            @elseif($contrat->statut == 'termine')
                                                <span class="badge bg-secondary">Terminé</span>
                                            @else
                                                <span class="badge bg-warning">{{ $contrat->statut }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @can('view_contrats')
                                            <a href="{{ route('contrats.show', $contrat) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @endcan
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-4">
                            <i class="fas fa-file-contract fa-3x mb-3 d-block"></i>
                            Aucun contrat pour ce box
                        </p>
                    @endif
                </div>
            </div>

            <!-- Interventions techniques -->
            @if(isset($box->interventions) && $box->interventions->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-tools me-2"></i>Interventions Techniques
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>{{ __('app.status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($box->interventions->take(10) as $intervention)
                                <tr>
                                    <td>{{ $intervention->date_intervention->format('d/m/Y') }}</td>
                                    <td>{{ $intervention->type }}</td>
                                    <td>{{ $intervention->description }}</td>
                                    <td>
                                        @if($intervention->statut == 'terminee')
                                            <span class="badge bg-success">Terminée</span>
                                        @else
                                            <span class="badge bg-warning">{{ $intervention->statut }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
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
    font-size: 1.8rem;
    font-weight: bold;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 0.875rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.card-header h5 {
    color: #333;
}
</style>
@endsection