@extends('layouts.app')

@section('title', __('app.invoices'))

@section('actions')
@can('create_factures')
    <a href="{{ route('factures.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>{{ __('app.add') }} {{ __('app.invoices') }}
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
                <div class="stat-number text-warning">{{ $stats['en_attente'] ?? 0 }}</div>
                <div class="stat-label">{{ __('app.pending') }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-success">{{ $stats['payees'] ?? 0 }}</div>
                <div class="stat-label">{{ __('app.paid') }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-danger">{{ number_format($stats['montant_du'] ?? 0, 2) }} €</div>
                <div class="stat-label">Montant Dû</div>
            </div>
        </div>
    </div>

    <!-- Filtres et recherche -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('factures.index') }}" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="{{ __('app.search') }}..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="statut" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="brouillon" {{ request('statut') == 'brouillon' ? 'selected' : '' }}>{{ __('app.draft') }}</option>
                        <option value="en_attente" {{ request('statut') == 'en_attente' ? 'selected' : '' }}>{{ __('app.pending') }}</option>
                        <option value="payee" {{ request('statut') == 'payee' ? 'selected' : '' }}>{{ __('app.paid') }}</option>
                        <option value="en_retard" {{ request('statut') == 'en_retard' ? 'selected' : '' }}>En Retard</option>
                        <option value="annulee" {{ request('statut') == 'annulee' ? 'selected' : '' }}>{{ __('app.cancelled') }}</option>
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

    <!-- Liste des factures -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>N° Facture</th>
                            <th>{{ __('app.client') }}</th>
                            <th>{{ __('app.contract') }}</th>
                            <th>Date d'émission</th>
                            <th>Date d'échéance</th>
                            <th>Montant HT</th>
                            <th>Montant TTC</th>
                            <th>{{ __('app.status') }}</th>
                            <th>{{ __('app.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($factures as $facture)
                        <tr>
                            <td><strong>{{ $facture->numero_facture }}</strong></td>
                            <td>
                                <strong>{{ $facture->client->nom }} {{ $facture->client->prenom }}</strong>
                                @if($facture->client->raison_sociale)
                                    <br><small class="text-muted">{{ $facture->client->raison_sociale }}</small>
                                @endif
                            </td>
                            <td>
                                @if($facture->contrat)
                                    <span class="badge bg-info">{{ $facture->contrat->numero_contrat }}</span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>{{ $facture->date_emission->format('d/m/Y') }}</td>
                            <td>
                                {{ $facture->date_echeance->format('d/m/Y') }}
                                @if($facture->date_echeance->isPast() && $facture->statut != 'payee')
                                    <br><span class="badge bg-danger">En retard</span>
                                @endif
                            </td>
                            <td>{{ number_format($facture->montant_ht, 2) }} €</td>
                            <td><strong>{{ number_format($facture->montant_ttc, 2) }} €</strong></td>
                            <td>
                                @if($facture->statut == 'payee')
                                    <span class="badge bg-success">{{ __('app.paid') }}</span>
                                @elseif($facture->statut == 'en_attente')
                                    <span class="badge bg-warning">{{ __('app.pending') }}</span>
                                @elseif($facture->statut == 'en_retard')
                                    <span class="badge bg-danger">En Retard</span>
                                @elseif($facture->statut == 'brouillon')
                                    <span class="badge bg-secondary">{{ __('app.draft') }}</span>
                                @else
                                    <span class="badge bg-dark">{{ __('app.cancelled') }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    @can('view_factures')
                                    <a href="{{ route('factures.show', $facture) }}" class="btn btn-sm btn-info" title="{{ __('app.view') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endcan
                                    @can('edit_factures')
                                    <a href="{{ route('factures.edit', $facture) }}" class="btn btn-sm btn-warning" title="{{ __('app.edit') }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    @can('delete_factures')
                                    <form action="{{ route('factures.destroy', $facture) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('app.are_you_sure') }}')">
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
                                <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                                <p class="text-muted">{{ __('app.no_results') }}</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $factures->links() }}
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