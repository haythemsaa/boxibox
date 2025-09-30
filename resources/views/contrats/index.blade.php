@extends('layouts.app')

@section('title', __('app.contracts'))

@section('actions')
@can('create_contrats')
    <a href="{{ route('contrats.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>{{ __('app.add') }} {{ __('app.contracts') }}
    </a>
@endcan
@endsection

@section('content')
<div class="container-fluid">
    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-primary">{{ $stats['total'] }}</div>
                <div class="stat-label">{{ __('app.total') }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-success">{{ $stats['actifs'] }}</div>
                <div class="stat-label">{{ __('app.active') }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-warning">{{ $stats['en_cours'] ?? 0 }}</div>
                <div class="stat-label">En Cours</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-secondary">{{ $stats['termines'] }}</div>
                <div class="stat-label">{{ __('app.completed') }}</div>
            </div>
        </div>
    </div>

    <!-- Filtres et recherche -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('contrats.index') }}" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="{{ __('app.search') }}..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="statut" class="form-select">
                        <option value="">{{ __('app.all_statuses') }}</option>
                        <option value="brouillon" {{ request('statut') == 'brouillon' ? 'selected' : '' }}>{{ __('app.draft') }}</option>
                        <option value="en_attente" {{ request('statut') == 'en_attente' ? 'selected' : '' }}>{{ __('app.pending') }}</option>
                        <option value="actif" {{ request('statut') == 'actif' ? 'selected' : '' }}>{{ __('app.active') }}</option>
                        <option value="suspendu" {{ request('statut') == 'suspendu' ? 'selected' : '' }}>{{ __('app.suspended') }}</option>
                        <option value="termine" {{ request('statut') == 'termine' ? 'selected' : '' }}>{{ __('app.completed') }}</option>
                        <option value="resilie" {{ request('statut') == 'resilie' ? 'selected' : '' }}>{{ __('app.cancelled') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="client_id" class="form-select">
                        <option value="">{{ __('app.all_clients') }}</option>
                        @if(isset($clients))
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                    {{ $client->nom }} {{ $client->prenom }}
                                </option>
                            @endforeach
                        @endif
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

    <!-- Liste des contrats -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ __('app.contract_number') }}</th>
                            <th>{{ __('app.client') }}</th>
                            <th>{{ __('app.box') }}</th>
                            <th>{{ __('app.start_date') }}</th>
                            <th>{{ __('app.end_date') }}</th>
                            <th>{{ __('app.monthly_price') }}</th>
                            <th>{{ __('app.status') }}</th>
                            <th>{{ __('app.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contrats as $contrat)
                        <tr>
                            <td><strong>{{ $contrat->numero_contrat }}</strong></td>
                            <td>
                                <strong>{{ $contrat->client->nom }} {{ $contrat->client->prenom }}</strong>
                                @if($contrat->client->raison_sociale)
                                    <br><small class="text-muted">{{ $contrat->client->raison_sociale }}</small>
                                @endif
                            </td>
                            <td>
                                @if($contrat->box)
                                    <span class="badge bg-info">{{ $contrat->box->numero }}</span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>{{ $contrat->date_debut->format('d/m/Y') }}</td>
                            <td>
                                @if($contrat->date_fin)
                                    {{ $contrat->date_fin->format('d/m/Y') }}
                                @else
                                    <span class="text-muted">{{ __('app.undetermined') }}</span>
                                @endif
                            </td>
                            <td>{{ number_format($contrat->prix_mensuel, 2) }} €</td>
                            <td>
                                @if($contrat->statut == 'actif')
                                    <span class="badge bg-success">{{ __('app.active') }}</span>
                                @elseif($contrat->statut == 'en_attente')
                                    <span class="badge bg-warning">{{ __('app.pending') }}</span>
                                @elseif($contrat->statut == 'brouillon')
                                    <span class="badge bg-secondary">{{ __('app.draft') }}</span>
                                @elseif($contrat->statut == 'termine')
                                    <span class="badge bg-info">{{ __('app.completed') }}</span>
                                @elseif($contrat->statut == 'suspendu')
                                    <span class="badge bg-warning">{{ __('app.suspended') }}</span>
                                @else
                                    <span class="badge bg-danger">{{ __('app.cancelled') }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    @can('view_contrats')
                                    <a href="{{ route('contrats.show', $contrat) }}" class="btn btn-sm btn-info" title="{{ __('app.view') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endcan
                                    @can('edit_contrats')
                                    <a href="{{ route('contrats.edit', $contrat) }}" class="btn btn-sm btn-warning" title="{{ __('app.edit') }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    @can('delete_contrats')
                                    <form action="{{ route('contrats.destroy', $contrat) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('app.are_you_sure') }}')">
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
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-file-contract fa-3x text-muted mb-3"></i>
                                <p class="text-muted">{{ __('app.no_results') }}</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $contrats->links() }}
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