@extends('layouts.client')

@section('title', 'Mes Contrats')

@section('content')
<div class="mb-4">
    <h1 class="h3">Mes Contrats</h1>
    <p class="text-muted">Consultez l'ensemble de vos contrats de location</p>
</div>

<!-- Filtres et recherche -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('client.contrats') }}" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Rechercher par N° contrat ou box..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="statut" class="form-select">
                    <option value="">Tous les statuts</option>
                    <option value="actif" {{ request('statut') == 'actif' ? 'selected' : '' }}>Actif</option>
                    <option value="en_cours" {{ request('statut') == 'en_cours' ? 'selected' : '' }}>En cours</option>
                    <option value="resilie" {{ request('statut') == 'resilie' ? 'selected' : '' }}>Résilié</option>
                    <option value="termine" {{ request('statut') == 'termine' ? 'selected' : '' }}>Terminé</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="sort_by" class="form-select">
                    <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Date de création</option>
                    <option value="date_debut" {{ request('sort_by') == 'date_debut' ? 'selected' : '' }}>Date d'entrée</option>
                    <option value="numero_contrat" {{ request('sort_by') == 'numero_contrat' ? 'selected' : '' }}>N° contrat</option>
                    <option value="montant_loyer" {{ request('sort_by') == 'montant_loyer' ? 'selected' : '' }}>Loyer</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i>Rechercher
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Liste des contrats -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>N° Contrat</th>
                        <th>N° Box</th>
                        <th>Type/Famille</th>
                        <th>Date Entrée</th>
                        <th>Date Fin</th>
                        <th class="text-end">Loyer TTC</th>
                        <th class="text-end">Caution</th>
                        <th>État</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contrats as $contrat)
                    <tr>
                        <td>
                            <strong class="text-primary">{{ $contrat->numero_contrat }}</strong>
                        </td>
                        <td>
                            <span class="badge bg-secondary">Box {{ $contrat->box->numero }}</span>
                            <br>
                            <small class="text-muted">{{ $contrat->box->surface }} m²</small>
                        </td>
                        <td>
                            @if($contrat->box->famille)
                                <span class="badge" style="background-color: {{ $contrat->box->famille->couleur_plan ?? '#6c757d' }}">
                                    {{ $contrat->box->famille->nom }}
                                </span>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                            <br>
                            <small class="text-muted">
                                <i class="fas fa-map-marker-alt"></i>
                                {{ $contrat->box->emplacement->nom ?? 'N/A' }}
                            </small>
                        </td>
                        <td>
                            {{ $contrat->date_debut->format('d/m/Y') }}
                            <br>
                            <small class="text-muted">
                                {{ $contrat->date_debut->diffForHumans() }}
                            </small>
                        </td>
                        <td>
                            @if($contrat->date_fin)
                                {{ $contrat->date_fin->format('d/m/Y') }}
                                <br>
                                @php
                                    $joursRestants = now()->diffInDays($contrat->date_fin, false);
                                @endphp
                                @if($joursRestants > 0)
                                    <small class="text-success">{{ $joursRestants }} jours</small>
                                @elseif($joursRestants == 0)
                                    <small class="text-warning">Aujourd'hui</small>
                                @else
                                    <small class="text-danger">Expiré</small>
                                @endif
                            @else
                                <span class="text-muted">Indéterminée</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <strong class="text-primary">{{ number_format($contrat->montant_loyer ?? 0, 2) }} €</strong>
                            <br>
                            <small class="text-muted">/mois</small>
                        </td>
                        <td class="text-end">
                            {{ number_format($contrat->depot_garantie ?? 0, 2) }} €
                        </td>
                        <td>
                            @if($contrat->statut == 'actif')
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i>Actif
                                </span>
                            @elseif($contrat->statut == 'en_cours')
                                <span class="badge bg-info">
                                    <i class="fas fa-clock me-1"></i>En cours
                                </span>
                            @elseif($contrat->statut == 'resilie')
                                <span class="badge bg-danger">
                                    <i class="fas fa-times-circle me-1"></i>Résilié
                                </span>
                            @elseif($contrat->statut == 'termine')
                                <span class="badge bg-secondary">
                                    <i class="fas fa-flag-checkered me-1"></i>Terminé
                                </span>
                            @else
                                <span class="badge bg-warning">{{ ucfirst($contrat->statut) }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('client.contrats.show', $contrat) }}"
                                   class="btn btn-outline-primary"
                                   title="Voir les détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('client.contrats.pdf', $contrat) }}"
                                   class="btn btn-outline-secondary"
                                   title="Télécharger PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                            <p class="text-muted mb-0">Aucun contrat trouvé</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($contrats->hasPages())
    <div class="card-footer bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                Affichage de {{ $contrats->firstItem() }} à {{ $contrats->lastItem() }} sur {{ $contrats->total() }} contrats
            </div>
            <div>
                {{ $contrats->links() }}
            </div>
        </div>
    </div>
    @endif
</div>

@push('styles')
<style>
.table th {
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #495057;
}

.table tbody tr {
    transition: background-color 0.2s ease;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}
</style>
@endpush
@endsection