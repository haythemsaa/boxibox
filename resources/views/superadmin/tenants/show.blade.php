@extends('layouts.app')

@section('title', $tenant->nom_entreprise)

@section('actions')
<div class="btn-group" role="group">
    <a href="{{ route('superadmin.tenants.edit', $tenant) }}" class="btn btn-warning">
        <i class="fas fa-edit me-2"></i>Éditer
    </a>
    @if($tenant->statut_abonnement === 'actif')
    <form action="{{ route('superadmin.tenants.suspend', $tenant) }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-secondary" onclick="return confirm('Suspendre ce tenant ?')">
            <i class="fas fa-pause me-2"></i>Suspendre
        </button>
    </form>
    @else
    <form action="{{ route('superadmin.tenants.activate', $tenant) }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-success">
            <i class="fas fa-check me-2"></i>Activer
        </button>
    </form>
    @endif
</div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Informations Tenant -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-building me-2"></i>Informations Tenant</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Entreprise:</th>
                            <td>{{ $tenant->nom_entreprise }}</td>
                        </tr>
                        <tr>
                            <th>Slug:</th>
                            <td><code>{{ $tenant->slug }}</code></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td><a href="mailto:{{ $tenant->email }}">{{ $tenant->email }}</a></td>
                        </tr>
                        <tr>
                            <th>Téléphone:</th>
                            <td>{{ $tenant->telephone ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>SIRET:</th>
                            <td>{{ $tenant->siret ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Adresse:</th>
                            <td>
                                @if($tenant->adresse)
                                    {{ $tenant->adresse }}<br>
                                    {{ $tenant->code_postal }} {{ $tenant->ville }}<br>
                                    {{ $tenant->pays }}
                                @else
                                    N/A
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Abonnement</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Plan:</th>
                            <td><span class="badge bg-info">{{ $tenant->getPlanName() }}</span></td>
                        </tr>
                        <tr>
                            <th>Prix Mensuel:</th>
                            <td><strong>{{ number_format($tenant->prix_mensuel, 2) }} €</strong></td>
                        </tr>
                        <tr>
                            <th>Statut:</th>
                            <td>{!! $tenant->getStatutBadge() !!}</td>
                        </tr>
                        <tr>
                            <th>Date Début:</th>
                            <td>{{ $tenant->date_debut_abonnement ? $tenant->date_debut_abonnement->format('d/m/Y') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Date Fin:</th>
                            <td>
                                {{ $tenant->date_fin_abonnement ? $tenant->date_fin_abonnement->format('d/m/Y') : 'Indéterminée' }}
                                @if($tenant->date_fin_abonnement)
                                    @php
                                        $days = $tenant->getDaysUntilExpiration();
                                    @endphp
                                    @if($days !== null)
                                        <br><small class="text-{{ $days < 30 ? 'danger' : 'muted' }}">
                                            ({{ $days > 0 ? $days . ' jours restants' : 'Expiré' }})
                                        </small>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Max Boxes:</th>
                            <td>{{ $tenant->max_boxes }}</td>
                        </tr>
                        <tr>
                            <th>Max Utilisateurs:</th>
                            <td>{{ $tenant->max_users }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Statistiques</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="stat-card text-center">
                                <div class="stat-number text-primary">{{ $stats['total_boxes'] }}/{{ $tenant->max_boxes }}</div>
                                <div class="stat-label">Boxes</div>
                                <div class="progress mt-2" style="height: 5px;">
                                    <div class="progress-bar" role="progressbar"
                                        style="width: {{ $tenant->getUsagePercentage('boxes') }}%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="stat-card text-center">
                                <div class="stat-number text-success">{{ $stats['boxes_occupees'] }}</div>
                                <div class="stat-label">Boxes Occupées</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="stat-card text-center">
                                <div class="stat-number text-info">{{ $stats['total_clients'] }}</div>
                                <div class="stat-label">Clients</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="stat-card text-center">
                                <div class="stat-number text-warning">{{ $stats['total_contrats'] }}</div>
                                <div class="stat-label">Contrats</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="stat-card text-center">
                                <div class="stat-number text-success">{{ $stats['contrats_actifs'] }}</div>
                                <div class="stat-label">Contrats Actifs</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="stat-card text-center">
                                <div class="stat-number text-primary">{{ number_format($stats['ca_mensuel'], 2) }} €</div>
                                <div class="stat-label">CA Mensuel</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Utilisateurs -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-users me-2"></i>Utilisateurs ({{ $tenant->users->count() }}/{{ $tenant->max_users }})</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Type</th>
                            <th>Actif</th>
                            <th>Dernier login</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tenant->users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td><span class="badge bg-secondary">{{ $user->getTypeUserLabel() }}</span></td>
                            <td>
                                @if($user->is_active)
                                    <span class="badge bg-success">Actif</span>
                                @else
                                    <span class="badge bg-danger">Inactif</span>
                                @endif
                            </td>
                            <td>{{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Jamais' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">Aucun utilisateur</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Activités récentes -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Factures récentes</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Numéro</th>
                                    <th>Montant</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tenant->factures()->orderBy('created_at', 'desc')->take(5)->get() as $facture)
                                <tr>
                                    <td>{{ $facture->numero_facture }}</td>
                                    <td>{{ number_format($facture->montant_total_ttc, 2) }} €</td>
                                    <td>
                                        @if($facture->statut == 'paye')
                                            <span class="badge bg-success">Payée</span>
                                        @elseif($facture->statut == 'impaye')
                                            <span class="badge bg-danger">Impayée</span>
                                        @else
                                            <span class="badge bg-warning">En attente</span>
                                        @endif
                                    </td>
                                    <td>{{ $facture->created_at->format('d/m/Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Aucune facture</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-file-contract me-2"></i>Contrats récents</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Numéro</th>
                                    <th>Client</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tenant->contrats()->with('client')->orderBy('created_at', 'desc')->take(5)->get() as $contrat)
                                <tr>
                                    <td>{{ $contrat->numero_contrat }}</td>
                                    <td>{{ $contrat->client ? $contrat->client->nom . ' ' . $contrat->client->prenom : 'N/A' }}</td>
                                    <td>
                                        @if($contrat->statut == 'actif')
                                            <span class="badge bg-success">Actif</span>
                                        @elseif($contrat->statut == 'resilie')
                                            <span class="badge bg-danger">Résilié</span>
                                        @else
                                            <span class="badge bg-warning">{{ ucfirst($contrat->statut) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $contrat->created_at->format('d/m/Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Aucun contrat</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.stat-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
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
</style>
@endsection