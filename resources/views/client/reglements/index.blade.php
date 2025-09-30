@extends('layouts.client')

@section('title', 'Mes Règlements')

@section('content')
<div class="mb-4">
    <h1 class="h3">Mes Règlements</h1>
    <p class="text-muted">Historique de vos paiements</p>
</div>

<!-- Statistiques -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card border-start border-success border-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-receipt fa-2x text-success"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="text-muted small">Total Règlements</div>
                        <div class="h4 mb-0">{{ $stats['total_reglements'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-start border-primary border-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-euro-sign fa-2x text-primary"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="text-muted small">Montant Total</div>
                        <div class="h4 mb-0">{{ number_format($stats['montant_total'], 2) }} €</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-start border-info border-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-calendar-month fa-2x text-info"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="text-muted small">Ce Mois</div>
                        <div class="h4 mb-0">{{ $stats['reglements_mois'] ?? 0 }}</div>
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
                        <i class="fas fa-chart-line fa-2x text-warning"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="text-muted small">Moyenne</div>
                        <div class="h4 mb-0">{{ number_format($stats['montant_moyen'] ?? 0, 2) }} €</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('client.reglements') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label small text-muted">Mode de paiement</label>
                <select name="mode_paiement" class="form-select">
                    <option value="">Tous les modes</option>
                    <option value="virement" {{ request('mode_paiement') == 'virement' ? 'selected' : '' }}>Virement</option>
                    <option value="cheque" {{ request('mode_paiement') == 'cheque' ? 'selected' : '' }}>Chèque</option>
                    <option value="carte" {{ request('mode_paiement') == 'carte' ? 'selected' : '' }}>Carte Bancaire</option>
                    <option value="prelevement" {{ request('mode_paiement') == 'prelevement' ? 'selected' : '' }}>Prélèvement SEPA</option>
                    <option value="especes" {{ request('mode_paiement') == 'especes' ? 'selected' : '' }}>Espèces</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Date de début</label>
                <input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Date de fin</label>
                <input type="date" name="date_fin" class="form-control" value="{{ request('date_fin') }}">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-1"></i>Filtrer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Liste des règlements -->
<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Historique des Règlements</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date Règlement</th>
                        <th>Facture Concernée</th>
                        <th>Mode de Paiement</th>
                        <th>Référence/N° Transaction</th>
                        <th class="text-end">Montant Payé</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reglements as $reglement)
                    <tr>
                        <td>
                            <strong>{{ $reglement->date_reglement->format('d/m/Y') }}</strong>
                            <br>
                            <small class="text-muted">{{ $reglement->date_reglement->diffForHumans() }}</small>
                        </td>
                        <td>
                            @if($reglement->facture)
                                <a href="{{ route('client.factures.show', $reglement->facture) }}" class="text-decoration-none">
                                    {{ $reglement->facture->numero_facture }}
                                </a>
                                <br>
                                <small class="text-muted">{{ $reglement->facture->date_emission->format('d/m/Y') }}</small>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if($reglement->mode_paiement == 'virement')
                                <i class="fas fa-exchange-alt text-primary me-1"></i>
                                <span>Virement</span>
                            @elseif($reglement->mode_paiement == 'cheque')
                                <i class="fas fa-money-check text-success me-1"></i>
                                <span>Chèque</span>
                            @elseif($reglement->mode_paiement == 'especes')
                                <i class="fas fa-money-bill-wave text-warning me-1"></i>
                                <span>Espèces</span>
                            @elseif($reglement->mode_paiement == 'carte')
                                <i class="fas fa-credit-card text-info me-1"></i>
                                <span>Carte Bancaire</span>
                            @elseif($reglement->mode_paiement == 'prelevement')
                                <i class="fas fa-university text-secondary me-1"></i>
                                <span>Prélèvement SEPA</span>
                            @else
                                <span>{{ ucfirst($reglement->mode_paiement) }}</span>
                            @endif
                        </td>
                        <td>
                            @if($reglement->reference)
                                <code class="text-dark">{{ $reglement->reference }}</code>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                            @if($reglement->notes)
                                <br>
                                <small class="text-muted">{{ Str::limit($reglement->notes, 30) }}</small>
                            @endif
                        </td>
                        <td class="text-end">
                            <strong class="text-success">{{ number_format($reglement->montant, 2) }} €</strong>
                        </td>
                        <td>
                            @if($reglement->statut == 'valide')
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i>Validé
                                </span>
                            @elseif($reglement->statut == 'en_attente')
                                <span class="badge bg-warning">
                                    <i class="fas fa-clock me-1"></i>En attente
                                </span>
                            @elseif($reglement->statut == 'refuse')
                                <span class="badge bg-danger">
                                    <i class="fas fa-times-circle me-1"></i>Refusé
                                </span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($reglement->statut) }}</span>
                            @endif
                        </td>
                        <td>
                            <a href="#" class="btn btn-sm btn-outline-primary" title="Reçu">
                                <i class="fas fa-file-invoice"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="fas fa-receipt fa-3x text-muted mb-3 d-block"></i>
                            <p class="text-muted mb-1">Aucun règlement trouvé</p>
                            <small class="text-muted">Vos paiements apparaîtront ici</small>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($reglements->hasPages())
    <div class="card-footer bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                Affichage de {{ $reglements->firstItem() }} à {{ $reglements->lastItem() }} sur {{ $reglements->total() }} règlements
            </div>
            <div>
                {{ $reglements->links() }}
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Informations -->
<div class="alert alert-info mt-4">
    <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Modes de paiement acceptés</h6>
    <p class="mb-2 small">
        Nous acceptons les modes de paiement suivants :
    </p>
    <div class="row small">
        <div class="col-md-4 mb-1">
            <i class="fas fa-university text-primary me-1"></i> Prélèvement automatique SEPA
        </div>
        <div class="col-md-4 mb-1">
            <i class="fas fa-credit-card text-info me-1"></i> Carte bancaire
        </div>
        <div class="col-md-4 mb-1">
            <i class="fas fa-exchange-alt text-success me-1"></i> Virement bancaire
        </div>
    </div>
    <p class="mb-0 small mt-2">
        Pour activer le prélèvement automatique, <a href="{{ route('client.sepa') }}" class="alert-link">créez un mandat SEPA</a>.
    </p>
</div>

@push('styles')
<style>
.table th {
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
</style>
@endpush
@endsection