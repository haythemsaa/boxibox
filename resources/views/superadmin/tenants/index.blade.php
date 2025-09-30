@extends('layouts.app')

@section('title', 'Gestion des Tenants')

@section('actions')
<a href="{{ route('superadmin.tenants.create') }}" class="btn btn-primary">
    <i class="fas fa-plus me-2"></i>Nouveau Tenant
</a>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('superadmin.tenants.index') }}" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Rechercher..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="plan" class="form-select">
                        <option value="">Tous les plans</option>
                        <option value="gratuit" {{ request('plan') == 'gratuit' ? 'selected' : '' }}>Gratuit</option>
                        <option value="starter" {{ request('plan') == 'starter' ? 'selected' : '' }}>Starter</option>
                        <option value="business" {{ request('plan') == 'business' ? 'selected' : '' }}>Business</option>
                        <option value="enterprise" {{ request('plan') == 'enterprise' ? 'selected' : '' }}>Enterprise</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="statut" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="actif" {{ request('statut') == 'actif' ? 'selected' : '' }}>Actif</option>
                        <option value="suspendu" {{ request('statut') == 'suspendu' ? 'selected' : '' }}>Suspendu</option>
                        <option value="expire" {{ request('statut') == 'expire' ? 'selected' : '' }}>Expiré</option>
                        <option value="annule" {{ request('statut') == 'annule' ? 'selected' : '' }}>Annulé</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Rechercher
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des tenants -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Entreprise</th>
                            <th>Email</th>
                            <th>Plan</th>
                            <th>Prix/mois</th>
                            <th>Boxes</th>
                            <th>Clients</th>
                            <th>Utilisateurs</th>
                            <th>Statut</th>
                            <th>Création</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tenants as $tenant)
                        <tr>
                            <td>{{ $tenant->id }}</td>
                            <td>
                                <strong>{{ $tenant->nom_entreprise }}</strong><br>
                                <small class="text-muted">{{ $tenant->slug }}</small>
                            </td>
                            <td>{{ $tenant->email }}</td>
                            <td>
                                <span class="badge bg-info">{{ $tenant->getPlanName() }}</span>
                            </td>
                            <td><strong>{{ number_format($tenant->prix_mensuel, 2) }} €</strong></td>
                            <td>
                                {{ $tenant->boxes_count ?? 0 }}/{{ $tenant->max_boxes }}
                                @if(($tenant->boxes_count ?? 0) >= $tenant->max_boxes)
                                <span class="badge bg-warning">MAX</span>
                                @endif
                            </td>
                            <td>{{ $tenant->clients_count ?? 0 }}</td>
                            <td>
                                {{ $tenant->users_count ?? 0 }}/{{ $tenant->max_users }}
                                @if(($tenant->users_count ?? 0) >= $tenant->max_users)
                                <span class="badge bg-warning">MAX</span>
                                @endif
                            </td>
                            <td>{!! $tenant->getStatutBadge() !!}</td>
                            <td>{{ $tenant->created_at->format('d/m/Y') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('superadmin.tenants.show', $tenant) }}" class="btn btn-sm btn-info" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('superadmin.tenants.edit', $tenant) }}" class="btn btn-sm btn-warning" title="Éditer">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($tenant->statut_abonnement === 'actif')
                                    <form action="{{ route('superadmin.tenants.suspend', $tenant) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-secondary" title="Suspendre" onclick="return confirm('Suspendre ce tenant ?')">
                                            <i class="fas fa-pause"></i>
                                        </button>
                                    </form>
                                    @else
                                    <form action="{{ route('superadmin.tenants.activate', $tenant) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Activer">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    @endif
                                    @if($tenant->statut_abonnement !== 'actif')
                                    <form action="{{ route('superadmin.tenants.destroy', $tenant) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce tenant ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center py-4">
                                <i class="fas fa-building fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Aucun tenant trouvé</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $tenants->links() }}
            </div>
        </div>
    </div>
</div>

<style>
.table th {
    background-color: #f8f9fa;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.875rem;
    letter-spacing: 0.5px;
}
</style>
@endsection