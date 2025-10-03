@extends('layouts.client')

@section('title', 'Mes Factures')

@section('content')
<div class="mb-4">
    <h1 class="h3">Mes Factures et Avoirs</h1>
    <p class="text-muted">Consultez et téléchargez vos factures et avoirs</p>
</div>

<!-- Statistiques -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card stat-card border-start border-primary border-4" data-aos="fade-up" data-aos-delay="0">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-file-invoice fa-2x text-primary"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="text-muted small">Total Factures</div>
                        <div class="h4 mb-0">{{ $stats['total'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stat-card border-start border-success border-4" data-aos="fade-up" data-aos-delay="100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle fa-2x text-success"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="text-muted small">Payées</div>
                        <div class="h4 mb-0">{{ $stats['payees'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stat-card border-start border-danger border-4" data-aos="fade-up" data-aos-delay="200">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle fa-2x text-danger"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="text-muted small">Impayées</div>
                        <div class="h4 mb-0">{{ $stats['impayees'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stat-card border-start border-warning border-4" data-aos="fade-up" data-aos-delay="300">
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
</div>

<!-- Filtres -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('client.factures') }}" class="row g-3">
            <div class="col-md-2">
                <label class="form-label small text-muted">Type</label>
                <select name="type" class="form-select">
                    <option value="">Tous</option>
                    <option value="facture" {{ request('type') == 'facture' ? 'selected' : '' }}>Factures</option>
                    <option value="avoir" {{ request('type') == 'avoir' ? 'selected' : '' }}>Avoirs</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Statut</label>
                <select name="statut" class="form-select">
                    <option value="">Tous les statuts</option>
                    <option value="paye" {{ request('statut') == 'paye' ? 'selected' : '' }}>Payée</option>
                    <option value="impaye" {{ request('statut') == 'impaye' ? 'selected' : '' }}>Impayée</option>
                    <option value="en_attente" {{ request('statut') == 'en_attente' ? 'selected' : '' }}>En attente</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Date début</label>
                <input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Date fin</label>
                <input type="date" name="date_fin" class="form-control" value="{{ request('date_fin') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Année</label>
                <select name="annee" class="form-select">
                    <option value="">Toutes</option>
                    @for($year = date('Y'); $year >= date('Y') - 5; $year--)
                        <option value="{{ $year }}" {{ request('annee') == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-1"></i>Filtrer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Liste des factures -->
<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Liste des Factures et Avoirs</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="facturesTable" class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Type</th>
                        <th>Numéro</th>
                        <th>Date Émission</th>
                        <th>Contrat/Box</th>
                        <th class="text-end">Montant HT</th>
                        <th class="text-end">TVA</th>
                        <th class="text-end">Montant TTC</th>
                        <th>Date Échéance</th>
                        <th>Statut Paiement</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($factures as $facture)
                    <tr class="{{ $facture->statut == 'impaye' && $facture->date_echeance < now() ? 'table-danger' : '' }}">
                        <td>
                            @if($facture->type == 'avoir')
                                <span class="badge bg-info">
                                    <i class="fas fa-undo me-1"></i>Avoir
                                </span>
                            @else
                                <span class="badge bg-primary">
                                    <i class="fas fa-file-invoice me-1"></i>Facture
                                </span>
                            @endif
                        </td>
                        <td>
                            <strong class="text-primary">{{ $facture->numero_facture }}</strong>
                        </td>
                        <td>
                            {{ $facture->date_emission->format('d/m/Y') }}
                            <br>
                            <small class="text-muted">{{ $facture->date_emission->diffForHumans() }}</small>
                        </td>
                        <td>
                            @if($facture->contrat)
                                <a href="{{ route('client.contrats.show', $facture->contrat) }}" class="text-decoration-none">
                                    {{ $facture->contrat->numero_contrat }}
                                </a>
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-box me-1"></i>Box {{ $facture->contrat->box->numero ?? 'N/A' }}
                                </small>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td class="text-end">{{ number_format($facture->montant_ht, 2) }} €</td>
                        <td class="text-end">
                            <small class="text-muted">{{ number_format($facture->montant_tva, 2) }} €</small>
                        </td>
                        <td class="text-end">
                            <strong class="text-dark">{{ number_format($facture->montant_ttc, 2) }} €</strong>
                        </td>
                        <td>
                            {{ $facture->date_echeance->format('d/m/Y') }}
                            <br>
                            @php
                                $joursRestants = now()->diffInDays($facture->date_echeance, false);
                            @endphp
                            @if($facture->statut != 'paye')
                                @if($joursRestants > 0)
                                    <small class="text-success">{{ $joursRestants }} jours</small>
                                @elseif($joursRestants == 0)
                                    <small class="text-warning">Aujourd'hui</small>
                                @else
                                    <small class="text-danger">En retard ({{ abs($joursRestants) }}j)</small>
                                @endif
                            @endif
                        </td>
                        <td>
                            @if($facture->statut == 'paye')
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i>Payée
                                </span>
                            @elseif($facture->statut == 'impaye')
                                @if($facture->date_echeance < now())
                                    <span class="badge bg-danger">
                                        <i class="fas fa-exclamation-circle me-1"></i>Impayée (Retard)
                                    </span>
                                @else
                                    <span class="badge bg-warning">
                                        <i class="fas fa-clock me-1"></i>À payer
                                    </span>
                                @endif
                            @elseif($facture->statut == 'en_attente')
                                <span class="badge bg-info">
                                    <i class="fas fa-hourglass-half me-1"></i>En attente
                                </span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($facture->statut) }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('client.factures.show', $facture) }}"
                                   class="btn btn-outline-primary"
                                   title="Voir les détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('client.factures.pdf', $facture) }}"
                                   class="btn btn-outline-secondary"
                                   title="Télécharger PDF">
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-5">
                            <i class="fas fa-file-invoice fa-3x text-muted mb-3 d-block"></i>
                            <p class="text-muted mb-1">Aucune facture trouvée</p>
                            <small class="text-muted">Vos factures apparaîtront ici</small>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($factures->hasPages())
    <div class="card-footer bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                Affichage de {{ $factures->firstItem() }} à {{ $factures->lastItem() }} sur {{ $factures->total() }} factures
            </div>
            <div>
                {{ $factures->links() }}
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Informations -->
@if($stats['impayees'] > 0)
<div class="alert alert-warning mt-4">
    <h6 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Factures en attente de paiement</h6>
    <p class="mb-2 small">
        Vous avez <strong>{{ $stats['impayees'] }}</strong> facture(s) impayée(s) pour un montant total de <strong>{{ number_format($stats['montant_du'], 2) }} €</strong>.
    </p>
    <p class="mb-0 small">
        Pour régler vos factures rapidement, vous pouvez mettre en place un <a href="{{ route('client.sepa') }}" class="alert-link">prélèvement automatique SEPA</a>.
    </p>
</div>
@endif

@push('styles')
<style>
.table th {
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table-danger {
    background-color: rgba(220, 53, 69, 0.1);
}

.stat-card {
    opacity: 0;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTables for factures table
    $('#facturesTable').DataTable({
        responsive: true,
        order: [[2, 'desc']], // Trier par date d'émission
        columnDefs: [
            { orderable: false, targets: [0, 9] } // Désactiver tri pour type et actions
        ],
        pageLength: 25,
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
    });

    // Animate stats cards on load
    $('.stat-card').each(function(index) {
        $(this).delay(100 * index).animate({opacity: 1}, 500);
    });
});
</script>
@endpush
@endsection