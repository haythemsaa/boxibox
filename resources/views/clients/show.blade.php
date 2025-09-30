@extends('layouts.app')

@section('title', __('app.clients') . ' - ' . $client->nom . ' ' . $client->prenom)

@section('actions')
<div class="btn-group" role="group">
    @can('edit_clients')
    <a href="{{ route('clients.edit', $client) }}" class="btn btn-warning">
        <i class="fas fa-edit me-2"></i>{{ __('app.edit') }}
    </a>
    @endcan
    <a href="{{ route('clients.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>{{ __('app.back') }}
    </a>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Statistiques rapides -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-primary">{{ $stats['contrats_actifs'] }}</div>
                <div class="stat-label">Contrats Actifs</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-warning">{{ $stats['factures_en_attente'] }}</div>
                <div class="stat-label">Factures en Attente</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-danger">{{ number_format($stats['montant_du'], 2) }} €</div>
                <div class="stat-label">Montant Dû</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-info">{{ $stats['documents_count'] }}</div>
                <div class="stat-label">Documents</div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Informations client -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i>Informations Client
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Type de client</label>
                        <div>
                            @if($client->type_client == 'particulier')
                                <span class="badge bg-info">Particulier</span>
                            @else
                                <span class="badge bg-warning">Entreprise</span>
                            @endif
                        </div>
                    </div>

                    @if($client->raison_sociale)
                    <div class="mb-3">
                        <label class="text-muted small">Raison sociale</label>
                        <div><strong>{{ $client->raison_sociale }}</strong></div>
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="text-muted small">{{ __('app.firstname') }} {{ __('app.lastname') }}</label>
                        <div><strong>{{ $client->prenom }} {{ $client->nom }}</strong></div>
                    </div>

                    @if($client->siret)
                    <div class="mb-3">
                        <label class="text-muted small">SIRET</label>
                        <div>{{ $client->siret }}</div>
                    </div>
                    @endif

                    <hr>

                    <div class="mb-3">
                        <label class="text-muted small">{{ __('app.email') }}</label>
                        <div>
                            <a href="mailto:{{ $client->email }}">
                                <i class="fas fa-envelope me-2"></i>{{ $client->email }}
                            </a>
                        </div>
                    </div>

                    @if($client->telephone)
                    <div class="mb-3">
                        <label class="text-muted small">{{ __('app.phone') }}</label>
                        <div>
                            <a href="tel:{{ $client->telephone }}">
                                <i class="fas fa-phone me-2"></i>{{ $client->telephone }}
                            </a>
                        </div>
                    </div>
                    @endif

                    <hr>

                    <div class="mb-3">
                        <label class="text-muted small">{{ __('app.address') }}</label>
                        <div>
                            {{ $client->adresse }}<br>
                            {{ $client->code_postal }} {{ $client->ville }}<br>
                            {{ $client->pays }}
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="text-muted small">{{ __('app.status') }}</label>
                        <div>
                            @if($client->is_active)
                                <span class="badge bg-success">{{ __('app.active') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('app.inactive') }}</span>
                            @endif
                        </div>
                    </div>

                    @if($client->notes)
                    <div class="mb-3">
                        <label class="text-muted small">{{ __('app.notes') }}</label>
                        <div class="text-muted">{{ $client->notes }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Contrats et autres informations -->
        <div class="col-md-8">
            <!-- Contrats -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-file-contract me-2"></i>{{ __('app.contracts') }}
                    </h5>
                    @can('create_contrats')
                    <a href="{{ route('contrats.create', ['client_id' => $client->id]) }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-2"></i>Nouveau Contrat
                    </a>
                    @endcan
                </div>
                <div class="card-body">
                    @if($client->contrats->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>N° Contrat</th>
                                        <th>Box</th>
                                        <th>Date Début</th>
                                        <th>Date Fin</th>
                                        <th>Prix Mensuel</th>
                                        <th>{{ __('app.status') }}</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($client->contrats as $contrat)
                                    <tr>
                                        <td><strong>{{ $contrat->numero_contrat }}</strong></td>
                                        <td>{{ $contrat->box->numero ?? 'N/A' }}</td>
                                        <td>{{ $contrat->date_debut->format('d/m/Y') }}</td>
                                        <td>{{ $contrat->date_fin ? $contrat->date_fin->format('d/m/Y') : 'Indéterminé' }}</td>
                                        <td>{{ number_format($contrat->prix_mensuel, 2) }} €</td>
                                        <td>
                                            @if($contrat->statut == 'actif')
                                                <span class="badge bg-success">Actif</span>
                                            @elseif($contrat->statut == 'en_attente')
                                                <span class="badge bg-warning">En attente</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $contrat->statut }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @can('view_contrats')
                                            <a href="{{ route('contrats.show', $contrat) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @endcan
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-4">
                            <i class="fas fa-file-contract fa-3x mb-3 d-block"></i>
                            Aucun contrat pour ce client
                        </p>
                    @endif
                </div>
            </div>

            <!-- Factures -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-file-invoice me-2"></i>{{ __('app.invoices') }}
                    </h5>
                </div>
                <div class="card-body">
                    @if($client->factures->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>N° Facture</th>
                                        <th>Date</th>
                                        <th>Montant TTC</th>
                                        <th>{{ __('app.status') }}</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($client->factures->take(10) as $facture)
                                    <tr>
                                        <td><strong>{{ $facture->numero_facture }}</strong></td>
                                        <td>{{ $facture->date_emission->format('d/m/Y') }}</td>
                                        <td>{{ number_format($facture->montant_ttc, 2) }} €</td>
                                        <td>
                                            @if($facture->statut == 'payee')
                                                <span class="badge bg-success">Payée</span>
                                            @elseif($facture->statut == 'en_attente')
                                                <span class="badge bg-warning">En attente</span>
                                            @else
                                                <span class="badge bg-danger">En retard</span>
                                            @endif
                                        </td>
                                        <td>
                                            @can('view_factures')
                                            <a href="{{ route('factures.show', $facture) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @endcan
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($client->factures->count() > 10)
                            <div class="text-center">
                                <a href="{{ route('factures.index', ['client_id' => $client->id]) }}" class="btn btn-sm btn-outline-primary">
                                    Voir toutes les factures ({{ $client->factures->count() }})
                                </a>
                            </div>
                        @endif
                    @else
                        <p class="text-muted text-center py-4">
                            <i class="fas fa-file-invoice fa-3x mb-3 d-block"></i>
                            Aucune facture pour ce client
                        </p>
                    @endif
                </div>
            </div>

            <!-- Documents -->
            @if($client->documents->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-folder me-2"></i>Documents
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        @foreach($client->documents as $document)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-file-pdf me-2"></i>
                                <strong>{{ $document->nom }}</strong>
                                <br>
                                <small class="text-muted">{{ $document->created_at->format('d/m/Y H:i') }}</small>
                            </div>
                            <div>
                                <a href="{{ Storage::url($document->chemin) }}" class="btn btn-sm btn-primary" target="_blank">
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
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

.card-header h5 {
    color: #333;
}
</style>
@endsection