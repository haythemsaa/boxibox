@extends('layouts.app')

@section('title', __('app.clients'))

@section('actions')
@can('create_clients')
    <a href="{{ route('clients.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>{{ __('app.add') }} {{ __('app.clients') }}
    </a>
@endcan
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
                <div class="stat-number text-success">{{ $stats['actifs'] }}</div>
                <div class="stat-label">{{ __('app.active') }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card text-center">
                <div class="stat-number text-info">{{ $stats['particuliers'] }}</div>
                <div class="stat-label">Particuliers</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card text-center">
                <div class="stat-number text-warning">{{ $stats['entreprises'] }}</div>
                <div class="stat-label">Entreprises</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card text-center">
                <div class="stat-number text-primary">{{ $stats['avec_contrats'] }}</div>
                <div class="stat-label">Avec Contrats</div>
            </div>
        </div>
    </div>

    <!-- Filtres et recherche -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('clients.index') }}" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Rechercher..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="type_client" class="form-select">
                        <option value="">Tous les types</option>
                        <option value="particulier" {{ request('type_client') == 'particulier' ? 'selected' : '' }}>Particulier</option>
                        <option value="entreprise" {{ request('type_client') == 'entreprise' ? 'selected' : '' }}>Entreprise</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="statut" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="actif" {{ request('statut') == 'actif' ? 'selected' : '' }}>{{ __('app.active') }}</option>
                        <option value="inactif" {{ request('statut') == 'inactif' ? 'selected' : '' }}>{{ __('app.inactive') }}</option>
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

    <!-- Liste des clients -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>{{ __('app.firstname') }} {{ __('app.lastname') }}</th>
                            <th>{{ __('app.email') }}</th>
                            <th>{{ __('app.phone') }}</th>
                            <th>Ville</th>
                            <th>{{ __('app.status') }}</th>
                            <th>Contrats</th>
                            <th>{{ __('app.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clients as $client)
                        <tr>
                            <td>{{ $client->id }}</td>
                            <td>
                                @if($client->type_client == 'particulier')
                                    <span class="badge bg-info">Particulier</span>
                                @else
                                    <span class="badge bg-warning">Entreprise</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $client->nom }} {{ $client->prenom }}</strong>
                                @if($client->raison_sociale)
                                    <br><small class="text-muted">{{ $client->raison_sociale }}</small>
                                @endif
                            </td>
                            <td>{{ $client->email }}</td>
                            <td>{{ $client->telephone }}</td>
                            <td>{{ $client->ville }}</td>
                            <td>
                                @if($client->is_active)
                                    <span class="badge bg-success">{{ __('app.active') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('app.inactive') }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ $client->contrats->count() }}</span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    @can('view_clients')
                                    <a href="{{ route('clients.show', $client) }}" class="btn btn-sm btn-info" title="{{ __('app.view') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endcan
                                    @can('edit_clients')
                                    <a href="{{ route('clients.edit', $client) }}" class="btn btn-sm btn-warning" title="{{ __('app.edit') }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    @can('delete_clients')
                                    <form action="{{ route('clients.destroy', $client) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('app.are_you_sure') }}')">
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
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <p class="text-muted">{{ __('app.no_results') }}</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $clients->links() }}
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