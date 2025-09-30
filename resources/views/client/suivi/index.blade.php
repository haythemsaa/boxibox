@extends('layouts.client')

@section('title', 'Suivi Chronologique')

@section('content')
<div class="mb-4">
    <h1 class="h3">Suivi Chronologique</h1>
    <p class="text-muted">Historique de tous vos événements et actions</p>
</div>

<!-- Filtres -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('client.suivi') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label small text-muted">Type d'événement</label>
                <select name="type" class="form-select">
                    <option value="">Tous les types</option>
                    <option value="contrat" {{ request('type') == 'contrat' ? 'selected' : '' }}>Contrats</option>
                    <option value="facture" {{ request('type') == 'facture' ? 'selected' : '' }}>Factures</option>
                    <option value="reglement" {{ request('type') == 'reglement' ? 'selected' : '' }}>Règlements</option>
                    <option value="relance" {{ request('type') == 'relance' ? 'selected' : '' }}>Relances</option>
                    <option value="document" {{ request('type') == 'document' ? 'selected' : '' }}>Documents</option>
                    <option value="sepa" {{ request('type') == 'sepa' ? 'selected' : '' }}>SEPA</option>
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

<!-- Timeline -->
<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Historique des Événements</h5>
    </div>
    <div class="card-body">
        @if($evenements->count() > 0)
        <div class="timeline">
            @foreach($evenements as $evenement)
            <div class="timeline-item">
                <div class="timeline-marker {{ $evenement['color'] ?? 'bg-secondary' }}">
                    <i class="fas {{ $evenement['icon'] ?? 'fa-circle' }} text-white"></i>
                </div>
                <div class="timeline-content">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="mb-1">
                                <span class="badge {{ $evenement['badge_class'] ?? 'bg-secondary' }} me-2">
                                    {{ $evenement['type_label'] }}
                                </span>
                                {{ $evenement['titre'] }}
                            </h6>
                            <p class="text-muted mb-2">{{ $evenement['description'] }}</p>
                        </div>
                        <small class="text-muted text-nowrap ms-3">
                            <i class="fas fa-clock me-1"></i>{{ $evenement['date']->format('d/m/Y H:i') }}
                        </small>
                    </div>

                    <!-- Détails supplémentaires -->
                    @if(isset($evenement['details']) && count($evenement['details']) > 0)
                    <div class="details-box">
                        @foreach($evenement['details'] as $key => $value)
                        <div class="row mb-1">
                            <div class="col-md-4">
                                <small class="text-muted">{{ $key }}:</small>
                            </div>
                            <div class="col-md-8">
                                <small><strong>{{ $value }}</strong></small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    <!-- Actions -->
                    @if(isset($evenement['actions']) && count($evenement['actions']) > 0)
                    <div class="mt-2">
                        @foreach($evenement['actions'] as $action)
                        <a href="{{ $action['url'] }}" class="btn btn-sm btn-outline-primary me-2">
                            <i class="fas {{ $action['icon'] }} me-1"></i>{{ $action['label'] }}
                        </a>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-5">
            <i class="fas fa-calendar-times fa-3x text-muted mb-3 d-block"></i>
            <p class="text-muted mb-1">Aucun événement trouvé</p>
            <small class="text-muted">Essayez de modifier vos filtres</small>
        </div>
        @endif
    </div>

    @if($evenements->hasPages())
    <div class="card-footer bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                Affichage de {{ $evenements->firstItem() }} à {{ $evenements->lastItem() }} sur {{ $evenements->total() }} événements
            </div>
            <div>
                {{ $evenements->links() }}
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Légende -->
<div class="card mt-3">
    <div class="card-body">
        <h6 class="mb-3"><i class="fas fa-info-circle me-2"></i>Légende</h6>
        <div class="row">
            <div class="col-md-2 mb-2">
                <span class="badge bg-primary"><i class="fas fa-file-contract me-1"></i>Contrat</span>
            </div>
            <div class="col-md-2 mb-2">
                <span class="badge bg-success"><i class="fas fa-file-invoice me-1"></i>Facture</span>
            </div>
            <div class="col-md-2 mb-2">
                <span class="badge bg-info"><i class="fas fa-credit-card me-1"></i>Règlement</span>
            </div>
            <div class="col-md-2 mb-2">
                <span class="badge bg-warning"><i class="fas fa-bell me-1"></i>Relance</span>
            </div>
            <div class="col-md-2 mb-2">
                <span class="badge bg-secondary"><i class="fas fa-file me-1"></i>Document</span>
            </div>
            <div class="col-md-2 mb-2">
                <span class="badge bg-dark"><i class="fas fa-university me-1"></i>SEPA</span>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline::before {
    content: '';
    position: absolute;
    top: 0;
    left: 30px;
    width: 3px;
    height: 100%;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    padding-left: 70px;
    padding-bottom: 30px;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: 18px;
    top: 0;
    width: 25px;
    height: 25px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 0 0 4px #fff, 0 0 0 6px #e9ecef;
    z-index: 1;
}

.timeline-content {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 15px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

.timeline-content:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateX(5px);
}

.details-box {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    padding: 10px;
    margin-top: 10px;
}

.bg-primary {
    background-color: #0d6efd !important;
}

.bg-success {
    background-color: #198754 !important;
}

.bg-info {
    background-color: #0dcaf0 !important;
}

.bg-warning {
    background-color: #ffc107 !important;
}

.bg-danger {
    background-color: #dc3545 !important;
}

.bg-secondary {
    background-color: #6c757d !important;
}

.bg-dark {
    background-color: #212529 !important;
}
</style>
@endpush
@endsection