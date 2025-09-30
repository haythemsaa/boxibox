@extends('layouts.app')

@section('title', 'Dashboard SuperAdmin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Dashboard SuperAdmin</h1>
        <a href="{{ route('superadmin.tenants.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nouveau Tenant
        </a>
    </div>

    <!-- Statistiques globales -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-primary">{{ $stats['total_tenants'] }}</div>
                <div class="stat-label">Total Tenants</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-success">{{ $stats['tenants_actifs'] }}</div>
                <div class="stat-label">Actifs</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-danger">{{ $stats['tenants_expires'] }}</div>
                <div class="stat-label">Expirés</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-warning">{{ $stats['tenants_suspendus'] }}</div>
                <div class="stat-label">Suspendus</div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card text-center">
                <div class="stat-number text-success">{{ number_format($stats['ca_mensuel_total'], 2) }} €</div>
                <div class="stat-label">CA Mensuel Total</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card text-center">
                <div class="stat-number text-info">{{ $stats['total_boxes'] }}</div>
                <div class="stat-label">Total Boxes</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card text-center">
                <div class="stat-number text-primary">{{ $stats['total_users'] }}</div>
                <div class="stat-label">Total Utilisateurs</div>
            </div>
        </div>
    </div>

    <!-- Liste des tenants -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Tenants récents</h5>
            <a href="{{ route('superadmin.tenants.index') }}" class="btn btn-sm btn-outline-primary">
                Voir tous
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Entreprise</th>
                            <th>Email</th>
                            <th>Plan</th>
                            <th>Prix/mois</th>
                            <th>Boxes</th>
                            <th>Clients</th>
                            <th>Contrats</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tenants as $tenant)
                        <tr>
                            <td><strong>{{ $tenant->nom_entreprise }}</strong></td>
                            <td>{{ $tenant->email }}</td>
                            <td>
                                <span class="badge bg-info">{{ $tenant->getPlanName() }}</span>
                            </td>
                            <td><strong>{{ number_format($tenant->prix_mensuel, 2) }} €</strong></td>
                            <td>{{ $tenant->boxes_count ?? 0 }}/{{ $tenant->max_boxes }}</td>
                            <td>{{ $tenant->clients_count ?? 0 }}</td>
                            <td>{{ $tenant->contrats_count ?? 0 }}</td>
                            <td>{!! $tenant->getStatutBadge() !!}</td>
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
                                        <button type="submit" class="btn btn-sm btn-secondary" title="Suspendre">
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
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="fas fa-building fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Aucun tenant</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $tenants->links() }}
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
    height: 100%;
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

.table th {
    background-color: #f8f9fa;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.875rem;
    letter-spacing: 0.5px;
}
</style>
@endsection