@extends('layouts.app')

@section('title', __('app.contracts') . ' - ' . $contrat->numero_contrat)

@section('actions')
<div class="btn-group" role="group">
    @can('edit_contrats')
    <a href="{{ route('contrats.edit', $contrat) }}" class="btn btn-warning">
        <i class="fas fa-edit me-2"></i>{{ __('app.edit') }}
    </a>
    @endcan
    @if($contrat->statut == 'en_attente')
        @can('activate_contrats')
        <form action="{{ route('contrats.activate', $contrat) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-success">
                <i class="fas fa-check me-2"></i>{{ __('app.activate') }}
            </button>
        </form>
        @endcan
    @endif
    @if($contrat->statut == 'actif')
        @can('terminate_contrats')
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#terminateModal">
            <i class="fas fa-times-circle me-2"></i>{{ __('app.terminate') }}
        </button>
        @endcan
    @endif
    <a href="{{ route('contrats.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>{{ __('app.back') }}
    </a>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Statistiques rapides -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card text-center">
                <div class="stat-number text-primary">{{ $stats['factures_count'] }}</div>
                <div class="stat-label">{{ __('app.invoices') }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card text-center">
                <div class="stat-number text-success">{{ number_format($stats['montant_facture'] ?? 0, 2) }} €</div>
                <div class="stat-label">{{ __('app.total_invoiced') }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card text-center">
                <div class="stat-number text-info">
                    @if(isset($stats['derniere_facture']) && $stats['derniere_facture'])
                        {{ $stats['derniere_facture']->format('d/m/Y') }}
                    @else
                        N/A
                    @endif
                </div>
                <div class="stat-label">{{ __('app.last_invoice') }}</div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Informations du contrat -->
        <div class="col-md-5">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-file-contract me-2"></i>{{ __('app.contract_information') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">{{ __('app.contract_number') }}</label>
                        <div><strong>{{ $contrat->numero_contrat }}</strong></div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">{{ __('app.status') }}</label>
                        <div>
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
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="text-muted small">{{ __('app.start_date') }}</label>
                        <div>{{ $contrat->date_debut->format('d/m/Y') }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">{{ __('app.end_date') }}</label>
                        <div>
                            @if($contrat->date_fin)
                                {{ $contrat->date_fin->format('d/m/Y') }}
                            @else
                                <span class="text-muted">{{ __('app.undetermined') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">{{ __('app.duration_type') }}</label>
                        <div>
                            @if($contrat->duree_type == 'determinee')
                                {{ __('app.fixed_term') }}
                            @else
                                {{ __('app.indefinite') }}
                            @endif
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="text-muted small">{{ __('app.monthly_price') }}</label>
                        <div><strong class="text-primary">{{ number_format($contrat->prix_mensuel, 2) }} €</strong></div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">{{ __('app.deposit') }}</label>
                        <div>{{ number_format($contrat->caution ?? 0, 2) }} €</div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">{{ __('app.processing_fee') }}</label>
                        <div>{{ number_format($contrat->frais_dossier ?? 0, 2) }} €</div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="text-muted small">{{ __('app.billing_frequency') }}</label>
                        <div>
                            @if($contrat->periodicite_facturation == 'mensuelle')
                                {{ __('app.monthly') }}
                            @elseif($contrat->periodicite_facturation == 'trimestrielle')
                                {{ __('app.quarterly') }}
                            @elseif($contrat->periodicite_facturation == 'semestrielle')
                                {{ __('app.biannual') }}
                            @else
                                {{ __('app.annual') }}
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">{{ __('app.automatic_renewal') }}</label>
                        <div>
                            @if($contrat->renouvellement_automatique)
                                <span class="badge bg-success">{{ __('app.yes') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('app.no') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">{{ __('app.insurance_included') }}</label>
                        <div>
                            @if($contrat->assurance_incluse)
                                <span class="badge bg-success">{{ __('app.yes') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('app.no') }}</span>
                            @endif
                        </div>
                    </div>

                    @if($contrat->notes)
                    <hr>
                    <div class="mb-3">
                        <label class="text-muted small">{{ __('app.notes') }}</label>
                        <div class="text-muted">{{ $contrat->notes }}</div>
                    </div>
                    @endif

                    @if($contrat->date_signature)
                    <hr>
                    <div class="mb-3">
                        <label class="text-muted small">{{ __('app.signature_date') }}</label>
                        <div>{{ $contrat->date_signature->format('d/m/Y H:i') }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Informations liées -->
        <div class="col-md-7">
            <!-- Box -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-box me-2"></i>{{ __('app.box') }}
                    </h5>
                </div>
                <div class="card-body">
                    @if($contrat->box)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small">{{ __('app.box_number') }}</label>
                                    <div><strong>{{ $contrat->box->numero }}</strong></div>
                                </div>
                                <div class="mb-3">
                                    <label class="text-muted small">{{ __('app.type') }}</label>
                                    <div>{{ $contrat->box->famille->nom ?? 'N/A' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small">{{ __('app.surface') }}</label>
                                    <div>{{ $contrat->box->surface ?? 'N/A' }} m²</div>
                                </div>
                                <div class="mb-3">
                                    <label class="text-muted small">{{ __('app.floor') }}</label>
                                    <div>{{ $contrat->box->etage ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="text-end">
                            <a href="{{ route('boxes.show', $contrat->box) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye me-2"></i>{{ __('app.view_details') }}
                            </a>
                        </div>
                    @else
                        <p class="text-muted text-center py-4">{{ __('app.no_box_assigned') }}</p>
                    @endif
                </div>
            </div>

            <!-- Client -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i>{{ __('app.client') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small">{{ __('app.name') }}</label>
                                <div><strong>{{ $contrat->client->nom }} {{ $contrat->client->prenom }}</strong></div>
                            </div>
                            @if($contrat->client->raison_sociale)
                            <div class="mb-3">
                                <label class="text-muted small">{{ __('app.company_name') }}</label>
                                <div>{{ $contrat->client->raison_sociale }}</div>
                            </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small">{{ __('app.email') }}</label>
                                <div><a href="mailto:{{ $contrat->client->email }}">{{ $contrat->client->email }}</a></div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">{{ __('app.phone') }}</label>
                                <div><a href="tel:{{ $contrat->client->telephone }}">{{ $contrat->client->telephone }}</a></div>
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <a href="{{ route('clients.show', $contrat->client) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye me-2"></i>{{ __('app.view_details') }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Factures -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-file-invoice me-2"></i>{{ __('app.invoices') }}
                    </h5>
                    @can('create_factures')
                    <a href="{{ route('factures.create', ['contrat_id' => $contrat->id]) }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-2"></i>{{ __('app.new_invoice') }}
                    </a>
                    @endcan
                </div>
                <div class="card-body">
                    @if($contrat->factures->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>{{ __('app.invoice_number') }}</th>
                                        <th>{{ __('app.date') }}</th>
                                        <th>{{ __('app.amount') }}</th>
                                        <th>{{ __('app.status') }}</th>
                                        <th>{{ __('app.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($contrat->factures->take(10) as $facture)
                                    <tr>
                                        <td><strong>{{ $facture->numero_facture }}</strong></td>
                                        <td>{{ $facture->date_emission->format('d/m/Y') }}</td>
                                        <td>{{ number_format($facture->montant_ttc, 2) }} €</td>
                                        <td>
                                            @if($facture->statut == 'payee')
                                                <span class="badge bg-success">{{ __('app.paid') }}</span>
                                            @elseif($facture->statut == 'en_attente')
                                                <span class="badge bg-warning">{{ __('app.pending') }}</span>
                                            @else
                                                <span class="badge bg-danger">{{ __('app.overdue') }}</span>
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
                        @if($contrat->factures->count() > 10)
                            <div class="text-center mt-3">
                                <a href="{{ route('factures.index', ['contrat_id' => $contrat->id]) }}" class="btn btn-sm btn-outline-primary">
                                    {{ __('app.view_all_invoices') }} ({{ $contrat->factures->count() }})
                                </a>
                            </div>
                        @endif
                    @else
                        <p class="text-muted text-center py-4">
                            <i class="fas fa-file-invoice fa-3x mb-3 d-block"></i>
                            {{ __('app.no_invoices') }}
                        </p>
                    @endif
                </div>
            </div>

            <!-- Services -->
            @if($contrat->services && $contrat->services->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-concierge-bell me-2"></i>{{ __('app.services') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        @foreach($contrat->services as $service)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $service->nom }}</strong>
                                @if($service->description)
                                    <br><small class="text-muted">{{ $service->description }}</small>
                                @endif
                            </div>
                            <div>
                                <span class="badge bg-primary">{{ number_format($service->pivot->prix ?? $service->prix, 2) }} €</span>
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

<!-- Modal pour terminer le contrat -->
@can('terminate_contrats')
<div class="modal fade" id="terminateModal" tabindex="-1" aria-labelledby="terminateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('contrats.terminate', $contrat) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="terminateModalLabel">{{ __('app.terminate_contract') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="date_fin" class="form-label">{{ __('app.end_date') }}</label>
                        <input type="date" class="form-control" id="date_fin" name="date_fin" required>
                    </div>
                    <div class="mb-3">
                        <label for="motif" class="form-label">{{ __('app.reason') }}</label>
                        <textarea class="form-control" id="motif" name="motif" rows="3"></textarea>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>{{ __('app.terminate_contract_warning') }}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-danger">{{ __('app.terminate') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

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