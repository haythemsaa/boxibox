@extends('layouts.app')

@section('title', __('app.boxes'))

@section('actions')
<div class="btn-group" role="group">
    @can('create_boxes')
    <a href="{{ route('boxes.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>{{ __('app.add') }} Box
    </a>
    @endcan
    <a href="{{ route('boxes.plan') }}" class="btn btn-info">
        <i class="fas fa-map me-2"></i>Plan interactif
    </a>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="stat-card text-center">
                <div class="stat-number text-primary">{{ $stats['total'] }}</div>
                <div class="stat-label">{{ __('app.total') }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card text-center">
                <div class="stat-number text-success">{{ $stats['libres'] }}</div>
                <div class="stat-label">Libres</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card text-center">
                <div class="stat-number text-danger">{{ $stats['occupees'] }}</div>
                <div class="stat-label">Occupés</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card text-center">
                <div class="stat-number text-warning">{{ $stats['reservees'] }}</div>
                <div class="stat-label">Réservés</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card text-center">
                <div class="stat-number text-info">{{ $stats['surface_totale'] }} m²</div>
                <div class="stat-label">{{ __('app.surface') }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card text-center">
                <div class="stat-number text-success">{{ number_format($stats['ca_reel'], 2) }} €</div>
                <div class="stat-label">CA Réel</div>
            </div>
        </div>
    </div>

    <!-- Filtres et recherche -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('boxes.index') }}" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="{{ __('app.search') }}..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="statut" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="libre" {{ request('statut') == 'libre' ? 'selected' : '' }}>Libre</option>
                        <option value="occupe" {{ request('statut') == 'occupe' ? 'selected' : '' }}>Occupé</option>
                        <option value="reserve" {{ request('statut') == 'reserve' ? 'selected' : '' }}>Réservé</option>
                        <option value="maintenance" {{ request('statut') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        <option value="hors_service" {{ request('statut') == 'hors_service' ? 'selected' : '' }}>Hors service</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="famille_id" class="form-select">
                        <option value="">Toutes les familles</option>
                        @foreach($familles as $famille)
                            <option value="{{ $famille->id }}" {{ request('famille_id') == $famille->id ? 'selected' : '' }}>
                                {{ $famille->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="emplacement_id" class="form-select">
                        <option value="">Tous les emplacements</option>
                        @foreach($emplacements as $emplacement)
                            <option value="{{ $emplacement->id }}" {{ request('emplacement_id') == $emplacement->id ? 'selected' : '' }}>
                                {{ $emplacement->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="sort" class="form-select">
                        <option value="numero" {{ request('sort') == 'numero' ? 'selected' : '' }}>Par numéro</option>
                        <option value="surface" {{ request('sort') == 'surface' ? 'selected' : '' }}>Par surface</option>
                        <option value="prix" {{ request('sort') == 'prix' ? 'selected' : '' }}>Par prix</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des boxes -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Numéro</th>
                            <th>Famille</th>
                            <th>Emplacement</th>
                            <th>{{ __('app.surface') }}</th>
                            <th>Volume</th>
                            <th>Prix/mois</th>
                            <th>{{ __('app.status') }}</th>
                            <th>Client</th>
                            <th>{{ __('app.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($boxes as $box)
                        <tr>
                            <td><strong>{{ $box->numero }}</strong></td>
                            <td>
                                @if($box->famille)
                                    <span class="badge" style="background-color: {{ $box->famille->couleur_plan ?? '#6c757d' }}">
                                        {{ $box->famille->nom }}
                                    </span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($box->emplacement)
                                    {{ $box->emplacement->nom }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>{{ $box->surface }} m²</td>
                            <td>{{ $box->volume ?? 'N/A' }} m³</td>
                            <td><strong>{{ number_format($box->prix_mensuel ?? $box->prix_base ?? 0, 2) }} €</strong></td>
                            <td>
                                @if($box->statut == 'libre')
                                    <span class="badge bg-success">Libre</span>
                                @elseif($box->statut == 'occupe')
                                    <span class="badge bg-danger">Occupé</span>
                                @elseif($box->statut == 'reserve')
                                    <span class="badge bg-warning">Réservé</span>
                                @elseif($box->statut == 'maintenance')
                                    <span class="badge bg-secondary">Maintenance</span>
                                @else
                                    <span class="badge bg-dark">Hors service</span>
                                @endif
                            </td>
                            <td>
                                @if($box->contratActif && $box->contratActif->client)
                                    <a href="{{ route('clients.show', $box->contratActif->client) }}">
                                        {{ $box->contratActif->client->nom }} {{ $box->contratActif->client->prenom }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    @can('view_boxes')
                                    <a href="{{ route('boxes.show', $box) }}" class="btn btn-sm btn-info" title="{{ __('app.view') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endcan
                                    @can('edit_boxes')
                                    <a href="{{ route('boxes.edit', $box) }}" class="btn btn-sm btn-warning" title="{{ __('app.edit') }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    @if($box->statut == 'libre')
                                        @can('reserve_boxes')
                                        <form action="{{ route('boxes.reserve', $box) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-primary" title="Réserver">
                                                <i class="fas fa-lock"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    @elseif($box->statut == 'reserve')
                                        @can('liberer_boxes')
                                        <form action="{{ route('boxes.liberer', $box) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="Libérer">
                                                <i class="fas fa-unlock"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    @endif
                                    @can('delete_boxes')
                                    <form action="{{ route('boxes.destroy', $box) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('app.are_you_sure') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="{{ __('app.delete') }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="fas fa-box fa-3x text-muted mb-3"></i>
                                <p class="text-muted">{{ __('app.no_results') }}</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $boxes->links() }}
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
    font-size: 1.5rem;
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