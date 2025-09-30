@extends('layouts.app')

@section('title', __('app.invoices') . ' - ' . $facture->numero_facture)

@section('actions')
<div class="btn-group" role="group">
    @can('edit_factures')
    <a href="{{ route('factures.edit', $facture) }}" class="btn btn-warning">
        <i class="fas fa-edit me-2"></i>{{ __('app.edit') }}
    </a>
    @endcan
    @if($facture->statut != 'payee')
        @can('pay_factures')
        <form action="{{ route('factures.marquer-payee', $facture) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-success">
                <i class="fas fa-check me-2"></i>Marquer payée
            </button>
        </form>
        @endcan
    @endif
    <a href="{{ route('factures.pdf', $facture) }}" class="btn btn-danger" target="_blank">
        <i class="fas fa-file-pdf me-2"></i>PDF
    </a>
    <a href="{{ route('factures.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>{{ __('app.back') }}
    </a>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Informations de la facture -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-file-invoice me-2"></i>Facture {{ $facture->numero_facture }}
                    </h5>
                </div>
                <div class="card-body">
                    <!-- En-tête facture -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted">Client</h6>
                            <strong>{{ $facture->client->nom }} {{ $facture->client->prenom }}</strong>
                            @if($facture->client->raison_sociale)
                                <br>{{ $facture->client->raison_sociale }}
                            @endif
                            <br>{{ $facture->client->adresse }}
                            <br>{{ $facture->client->code_postal }} {{ $facture->client->ville }}
                            <br>{{ $facture->client->email }}
                        </div>
                        <div class="col-md-6 text-end">
                            <p class="mb-1"><strong>N° Facture:</strong> {{ $facture->numero_facture }}</p>
                            <p class="mb-1"><strong>Date d'émission:</strong> {{ $facture->date_emission->format('d/m/Y') }}</p>
                            <p class="mb-1"><strong>Date d'échéance:</strong> {{ $facture->date_echeance->format('d/m/Y') }}</p>
                            @if($facture->contrat)
                                <p class="mb-1"><strong>Contrat:</strong> {{ $facture->contrat->numero_contrat }}</p>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <!-- Statut -->
                    <div class="mb-4">
                        <h6 class="text-muted">{{ __('app.status') }}</h6>
                        @if($facture->statut == 'payee')
                            <span class="badge bg-success fs-5">{{ __('app.paid') }}</span>
                            @if($facture->date_paiement)
                                <small class="text-muted">le {{ $facture->date_paiement->format('d/m/Y') }}</small>
                            @endif
                        @elseif($facture->statut == 'en_attente')
                            <span class="badge bg-warning fs-5">{{ __('app.pending') }}</span>
                        @elseif($facture->statut == 'en_retard')
                            <span class="badge bg-danger fs-5">En Retard</span>
                        @elseif($facture->statut == 'brouillon')
                            <span class="badge bg-secondary fs-5">{{ __('app.draft') }}</span>
                        @else
                            <span class="badge bg-dark fs-5">{{ __('app.cancelled') }}</span>
                        @endif
                    </div>

                    <!-- Lignes de facture -->
                    @if($facture->lignes && $facture->lignes->count() > 0)
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Description</th>
                                    <th class="text-center">Quantité</th>
                                    <th class="text-end">Prix unitaire HT</th>
                                    <th class="text-end">Montant HT</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($facture->lignes as $ligne)
                                <tr>
                                    <td>{{ $ligne->description }}</td>
                                    <td class="text-center">{{ $ligne->quantite }}</td>
                                    <td class="text-end">{{ number_format($ligne->prix_unitaire, 2) }} €</td>
                                    <td class="text-end">{{ number_format($ligne->montant_ht, 2) }} €</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    <!-- Totaux -->
                    <div class="row">
                        <div class="col-md-6 offset-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Sous-total HT:</strong></td>
                                    <td class="text-end">{{ number_format($facture->montant_ht, 2) }} €</td>
                                </tr>
                                <tr>
                                    <td><strong>TVA ({{ $facture->taux_tva }}%):</strong></td>
                                    <td class="text-end">{{ number_format($facture->montant_tva, 2) }} €</td>
                                </tr>
                                <tr class="table-active">
                                    <td><strong>Total TTC:</strong></td>
                                    <td class="text-end"><strong class="fs-4 text-primary">{{ number_format($facture->montant_ttc, 2) }} €</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($facture->notes)
                    <hr>
                    <div class="mb-3">
                        <h6 class="text-muted">{{ __('app.notes') }}</h6>
                        <p class="text-muted">{{ $facture->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Informations complémentaires -->
        <div class="col-md-4">
            <!-- Informations client -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-user me-2"></i>{{ __('app.client') }}
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>{{ $facture->client->nom }} {{ $facture->client->prenom }}</strong></p>
                    @if($facture->client->raison_sociale)
                        <p class="mb-2 text-muted">{{ $facture->client->raison_sociale }}</p>
                    @endif
                    <p class="mb-1"><i class="fas fa-envelope me-2"></i>{{ $facture->client->email }}</p>
                    @if($facture->client->telephone)
                        <p class="mb-1"><i class="fas fa-phone me-2"></i>{{ $facture->client->telephone }}</p>
                    @endif
                    <div class="mt-3">
                        <a href="{{ route('clients.show', $facture->client) }}" class="btn btn-sm btn-outline-primary w-100">
                            <i class="fas fa-eye me-2"></i>Voir le client
                        </a>
                    </div>
                </div>
            </div>

            <!-- Informations contrat -->
            @if($facture->contrat)
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-file-contract me-2"></i>{{ __('app.contract') }}
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>{{ $facture->contrat->numero_contrat }}</strong></p>
                    <p class="mb-1"><strong>Box:</strong> {{ $facture->contrat->box->numero ?? 'N/A' }}</p>
                    <p class="mb-1"><strong>Prix mensuel:</strong> {{ number_format($facture->contrat->prix_mensuel, 2) }} €</p>
                    <div class="mt-3">
                        <a href="{{ route('contrats.show', $facture->contrat) }}" class="btn btn-sm btn-outline-primary w-100">
                            <i class="fas fa-eye me-2"></i>Voir le contrat
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Paiements -->
            @if($facture->paiements && $facture->paiements->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-euro-sign me-2"></i>Paiements
                    </h6>
                </div>
                <div class="card-body">
                    @foreach($facture->paiements as $paiement)
                    <div class="border-bottom pb-2 mb-2">
                        <p class="mb-1"><strong>{{ number_format($paiement->montant, 2) }} €</strong></p>
                        <p class="mb-1 text-muted small">{{ $paiement->date_paiement->format('d/m/Y') }}</p>
                        <p class="mb-0 text-muted small">{{ $paiement->mode_paiement }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Historique -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-history me-2"></i>Historique
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-1 small"><strong>Créée le:</strong> {{ $facture->created_at->format('d/m/Y H:i') }}</p>
                    <p class="mb-0 small"><strong>Modifiée le:</strong> {{ $facture->updated_at->format('d/m/Y H:i') }}</p>
                    @if($facture->date_paiement)
                        <p class="mb-0 small"><strong>Payée le:</strong> {{ $facture->date_paiement->format('d/m/Y H:i') }}</p>
                    @endif
                </div>
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

.card-header h6 {
    color: #333;
}
</style>
@endsection