@extends('layouts.app')

@section('title', 'Contrat ' . $contrat->numero_contrat)

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <a href="{{ route('client.contrats') }}" class="btn btn-sm btn-secondary mb-2">
            <i class="fas fa-arrow-left me-2"></i>Retour
        </a>
        <h1 class="h3">Contrat {{ $contrat->numero_contrat }}</h1>
    </div>

    <div class="row">
        <!-- Informations du contrat -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-file-contract me-2"></i>Informations du Contrat</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Numéro:</th>
                            <td><strong>{{ $contrat->numero_contrat }}</strong></td>
                        </tr>
                        <tr>
                            <th>Date Début:</th>
                            <td>{{ $contrat->date_debut->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th>Date Fin:</th>
                            <td>{{ $contrat->date_fin ? $contrat->date_fin->format('d/m/Y') : 'Indéterminée' }}</td>
                        </tr>
                        <tr>
                            <th>Durée:</th>
                            <td>{{ $contrat->duree_mois ?? 'N/A' }} mois</td>
                        </tr>
                        <tr>
                            <th>Loyer Mensuel:</th>
                            <td><strong class="text-primary">{{ number_format($contrat->montant_loyer, 2) }} €</strong></td>
                        </tr>
                        <tr>
                            <th>Dépôt de Garantie:</th>
                            <td>{{ number_format($contrat->depot_garantie ?? 0, 2) }} €</td>
                        </tr>
                        <tr>
                            <th>Statut:</th>
                            <td>
                                @if($contrat->statut == 'actif')
                                    <span class="badge bg-success">Actif</span>
                                @elseif($contrat->statut == 'resilie')
                                    <span class="badge bg-danger">Résilié</span>
                                @else
                                    <span class="badge bg-warning">{{ ucfirst($contrat->statut) }}</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Informations du Box -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-box me-2"></i>Informations du Box</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Numéro:</th>
                            <td><strong>{{ $contrat->box->numero }}</strong></td>
                        </tr>
                        <tr>
                            <th>Famille:</th>
                            <td>
                                @if($contrat->box->famille)
                                    <span class="badge" style="background-color: {{ $contrat->box->famille->couleur ?? '#6c757d' }}">
                                        {{ $contrat->box->famille->nom }}
                                    </span>
                                @else
                                    N/A
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Emplacement:</th>
                            <td>{{ $contrat->box->emplacement->nom ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Surface:</th>
                            <td>{{ $contrat->box->surface }} m²</td>
                        </tr>
                        <tr>
                            <th>Volume:</th>
                            <td>{{ $contrat->box->volume ?? 'N/A' }} m³</td>
                        </tr>
                        <tr>
                            <th>Équipements:</th>
                            <td>{{ $contrat->box->equipements ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Factures associées -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Factures Associées</h5>
            <a href="{{ route('client.factures') }}" class="btn btn-sm btn-outline-primary">
                Voir toutes mes factures
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Numéro</th>
                            <th>Date Émission</th>
                            <th>Montant HT</th>
                            <th>Montant TTC</th>
                            <th>Date Échéance</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contrat->factures as $facture)
                        <tr>
                            <td><strong>{{ $facture->numero_facture }}</strong></td>
                            <td>{{ $facture->date_emission->format('d/m/Y') }}</td>
                            <td>{{ number_format($facture->montant_total_ht, 2) }} €</td>
                            <td><strong>{{ number_format($facture->montant_total_ttc, 2) }} €</strong></td>
                            <td>{{ $facture->date_echeance->format('d/m/Y') }}</td>
                            <td>
                                @if($facture->statut == 'paye')
                                    <span class="badge bg-success">Payée</span>
                                @elseif($facture->statut == 'impaye')
                                    <span class="badge bg-danger">Impayée</span>
                                @else
                                    <span class="badge bg-warning">En attente</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('client.factures.show', $facture) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('client.factures.pdf', $facture) }}" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-download"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Aucune facture pour ce contrat</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
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