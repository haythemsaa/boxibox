@extends('layouts.app')

@section('title', 'Facture ' . $facture->numero_facture)

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <a href="{{ route('client.factures') }}" class="btn btn-sm btn-secondary mb-2">
            <i class="fas fa-arrow-left me-2"></i>Retour
        </a>
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h3">Facture {{ $facture->numero_facture }}</h1>
            <a href="{{ route('client.factures.pdf', $facture) }}" class="btn btn-primary">
                <i class="fas fa-download me-2"></i>Télécharger PDF
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Informations facture -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Informations</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Numéro:</th>
                            <td><strong>{{ $facture->numero_facture }}</strong></td>
                        </tr>
                        <tr>
                            <th>Date Émission:</th>
                            <td>{{ $facture->date_emission->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th>Date Échéance:</th>
                            <td>{{ $facture->date_echeance->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th>Contrat:</th>
                            <td>
                                @if($facture->contrat)
                                    <a href="{{ route('client.contrats.show', $facture->contrat) }}">
                                        {{ $facture->contrat->numero_contrat }}
                                    </a>
                                @else
                                    N/A
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Statut:</th>
                            <td>
                                @if($facture->statut == 'paye')
                                    <span class="badge bg-success">Payée</span>
                                @elseif($facture->statut == 'impaye')
                                    <span class="badge bg-danger">Impayée</span>
                                @else
                                    <span class="badge bg-warning">En attente</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Montants -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-euro-sign me-2"></i>Montants</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Montant HT:</th>
                            <td>{{ number_format($facture->montant_total_ht, 2) }} €</td>
                        </tr>
                        <tr>
                            <th>TVA ({{ $facture->taux_tva ?? 20 }}%):</th>
                            <td>{{ number_format($facture->montant_tva, 2) }} €</td>
                        </tr>
                        <tr class="table-active">
                            <th><strong>Montant TTC:</strong></th>
                            <td><strong class="text-primary fs-5">{{ number_format($facture->montant_total_ttc, 2) }} €</strong></td>
                        </tr>
                        @if($facture->montant_regle > 0)
                        <tr>
                            <th>Montant Réglé:</th>
                            <td class="text-success">{{ number_format($facture->montant_regle, 2) }} €</td>
                        </tr>
                        <tr>
                            <th>Reste à Payer:</th>
                            <td class="text-danger">
                                <strong>{{ number_format($facture->montant_total_ttc - $facture->montant_regle, 2) }} €</strong>
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Lignes de facture -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Détail de la Facture</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Désignation</th>
                            <th class="text-center">Quantité</th>
                            <th class="text-end">Prix Unitaire HT</th>
                            <th class="text-end">Total HT</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($facture->lignes as $ligne)
                        <tr>
                            <td>{{ $ligne->designation }}</td>
                            <td class="text-center">{{ $ligne->quantite }}</td>
                            <td class="text-end">{{ number_format($ligne->prix_unitaire_ht, 2) }} €</td>
                            <td class="text-end"><strong>{{ number_format($ligne->montant_total_ht, 2) }} €</strong></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">Aucune ligne de facture</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Règlements -->
    @if($facture->reglements->count() > 0)
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-money-bill me-2"></i>Règlements</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Mode</th>
                            <th>Référence</th>
                            <th class="text-end">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($facture->reglements as $reglement)
                        <tr>
                            <td>{{ $reglement->date_reglement->format('d/m/Y') }}</td>
                            <td>
                                @if($reglement->mode_paiement == 'virement')
                                    <i class="fas fa-exchange-alt me-1"></i>Virement
                                @elseif($reglement->mode_paiement == 'cheque')
                                    <i class="fas fa-money-check me-1"></i>Chèque
                                @elseif($reglement->mode_paiement == 'especes')
                                    <i class="fas fa-money-bill-wave me-1"></i>Espèces
                                @elseif($reglement->mode_paiement == 'carte')
                                    <i class="fas fa-credit-card me-1"></i>Carte
                                @elseif($reglement->mode_paiement == 'prelevement')
                                    <i class="fas fa-university me-1"></i>Prélèvement
                                @else
                                    {{ ucfirst($reglement->mode_paiement) }}
                                @endif
                            </td>
                            <td>{{ $reglement->reference ?? 'N/A' }}</td>
                            <td class="text-end"><strong>{{ number_format($reglement->montant, 2) }} €</strong></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
.table th {
    background-color: #f8f9fa;
    font-weight: 600;
}
</style>
@endsection