@extends('layouts.client')

@section('title', 'Tableau de bord')

@section('content')
<div class="mb-4">
    <h1 class="h3">Bienvenue {{ $client->prenom }} {{ $client->nom }}</h1>
    <p class="text-muted">Accédez à toutes vos informations en un seul endroit</p>
</div>

<!-- Statistiques principales -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card border-start border-primary border-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-file-contract fa-2x text-primary"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="text-muted small">Contrats Actifs</div>
                        <div class="h4 mb-0">{{ $stats['contrats_actifs'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card border-start border-danger border-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="text-muted small">Factures Impayées</div>
                        <div class="h4 mb-0">{{ $stats['factures_impayees'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card border-start border-warning border-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-euro-sign fa-2x text-warning"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="text-muted small">Montant Dû</div>
                        <div class="h4 mb-0">{{ number_format($stats['montant_du'], 2) }} €</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card border-start border-success border-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-{{ $stats['mandat_sepa_actif'] ? 'check-circle' : 'times-circle' }} fa-2x text-{{ $stats['mandat_sepa_actif'] ? 'success' : 'secondary' }}"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="text-muted small">Prélèvement Auto</div>
                        <div class="h6 mb-0">{{ $stats['mandat_sepa_actif'] ? 'Actif' : 'Non configuré' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Mes Contrats Actifs -->
    <div class="col-lg-7 mb-4">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-file-contract me-2"></i>Mes Contrats Actifs</h5>
                <a href="{{ route('client.contrats') }}" class="btn btn-sm btn-outline-primary">
                    Voir tous <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body">
                @forelse($contratsActifs as $contrat)
                <div class="border rounded p-3 mb-3 hover-shadow">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="mb-1">
                                <i class="fas fa-box text-primary me-2"></i>
                                Box {{ $contrat->box->numero }} - {{ $contrat->numero_contrat }}
                            </h6>
                            <div class="text-muted small mb-2">
                                <span class="me-3">
                                    <i class="fas fa-tag me-1"></i>{{ $contrat->box->famille->nom ?? 'N/A' }}
                                </span>
                                <span class="me-3">
                                    <i class="fas fa-map-marker-alt me-1"></i>{{ $contrat->box->emplacement->nom ?? 'N/A' }}
                                </span>
                                <span>
                                    <i class="fas fa-ruler-combined me-1"></i>{{ $contrat->box->surface }} m²
                                </span>
                            </div>
                            <div class="text-muted small">
                                <i class="fas fa-calendar me-1"></i>
                                Depuis le {{ $contrat->date_debut->format('d/m/Y') }}
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="h5 text-primary mb-2">{{ number_format($contrat->montant_loyer, 2) }} €<small class="text-muted">/mois</small></div>
                            <a href="{{ route('client.contrats.show', $contrat) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye me-1"></i>Détails
                            </a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Aucun contrat actif</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Dernières Factures -->
    <div class="col-lg-5 mb-4">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Dernières Factures</h5>
                <a href="{{ route('client.factures') }}" class="btn btn-sm btn-outline-primary">
                    Voir toutes <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body">
                @forelse($dernieresFactures as $facture)
                <div class="border-bottom pb-3 mb-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <strong class="d-block">{{ $facture->numero_facture }}</strong>
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>{{ $facture->date_emission->format('d/m/Y') }}
                            </small>
                        </div>
                        <div>
                            @if($facture->statut == 'paye')
                                <span class="badge bg-success">Payée</span>
                            @elseif($facture->statut == 'impaye')
                                <span class="badge bg-danger">Impayée</span>
                            @else
                                <span class="badge bg-warning">En attente</span>
                            @endif
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="h6 mb-0 text-primary">{{ number_format($facture->montant_total_ttc, 2) }} €</span>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('client.factures.show', $facture) }}" class="btn btn-outline-primary" title="Voir">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('client.factures.pdf', $facture) }}" class="btn btn-outline-secondary" title="PDF">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Aucune facture</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Alertes & Actions rapides -->
@if($stats['factures_impayees'] > 0)
<div class="alert alert-warning d-flex align-items-center mb-4">
    <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
    <div class="flex-grow-1">
        <h5 class="alert-heading mb-1">Attention : Factures impayées</h5>
        <p class="mb-0">
            Vous avez <strong>{{ $stats['factures_impayees'] }}</strong> facture(s) impayée(s) pour un montant total de <strong>{{ number_format($stats['montant_du'], 2) }} €</strong>.
        </p>
    </div>
    <a href="{{ route('client.factures', ['statut' => 'impaye']) }}" class="btn btn-warning">
        Voir les factures
    </a>
</div>
@endif

@if(!$stats['mandat_sepa_actif'])
<div class="alert alert-info d-flex align-items-center">
    <i class="fas fa-info-circle fa-2x me-3"></i>
    <div class="flex-grow-1">
        <h5 class="alert-heading mb-1">Activez le prélèvement automatique</h5>
        <p class="mb-0">
            Simplifiez vos paiements en mettant en place un mandat SEPA pour les prélèvements automatiques.
        </p>
    </div>
    <a href="{{ route('client.sepa') }}" class="btn btn-info">
        Configurer
    </a>
</div>
@endif

<!-- Accès rapides -->
<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Accès Rapides</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <a href="{{ route('client.contrats') }}" class="text-decoration-none">
                    <div class="quick-link text-center p-3 rounded hover-shadow">
                        <i class="fas fa-file-contract fa-2x text-primary mb-2"></i>
                        <div class="fw-semibold">Mes Contrats</div>
                        <small class="text-muted">Consulter et télécharger</small>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('client.factures') }}" class="text-decoration-none">
                    <div class="quick-link text-center p-3 rounded hover-shadow">
                        <i class="fas fa-file-invoice fa-2x text-success mb-2"></i>
                        <div class="fw-semibold">Mes Factures</div>
                        <small class="text-muted">Historique et paiements</small>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('client.documents') }}" class="text-decoration-none">
                    <div class="quick-link text-center p-3 rounded hover-shadow">
                        <i class="fas fa-folder fa-2x text-warning mb-2"></i>
                        <div class="fw-semibold">Mes Fichiers</div>
                        <small class="text-muted">Documents partagés</small>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('client.profil') }}" class="text-decoration-none">
                    <div class="quick-link text-center p-3 rounded hover-shadow">
                        <i class="fas fa-user-cog fa-2x text-info mb-2"></i>
                        <div class="fw-semibold">Mon Profil</div>
                        <small class="text-muted">Modifier mes infos</small>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.hover-shadow {
    transition: all 0.2s ease;
}

.hover-shadow:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.quick-link {
    border: 1px solid #e9ecef;
    transition: all 0.2s ease;
    background: white;
}

.quick-link:hover {
    border-color: #0d6efd;
    background: #f8f9fa;
}
</style>
@endpush
@endsection