@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-bar me-2"></i>Rapports et Statistiques
        </h1>
        <p class="text-muted">Analysez les performances de votre entreprise avec des rapports détaillés</p>
    </div>

    <div class="row">
        <!-- Rapport Financier -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100">
                <div class="card-body">
                    <div class="text-center">
                        <div class="mb-3">
                            <i class="fas fa-euro-sign fa-3x text-primary"></i>
                        </div>
                        <h5 class="card-title">Rapport Financier</h5>
                        <p class="card-text text-muted small">
                            Chiffre d'affaires, factures, règlements et évolution financière
                        </p>
                        <a href="{{ route('reports.financial') }}" class="btn btn-primary">
                            <i class="fas fa-file-invoice-dollar"></i> Voir le rapport
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rapport Occupation -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100">
                <div class="card-body">
                    <div class="text-center">
                        <div class="mb-3">
                            <i class="fas fa-chart-pie fa-3x text-success"></i>
                        </div>
                        <h5 class="card-title">Rapport d'Occupation</h5>
                        <p class="card-text text-muted small">
                            Taux d'occupation, disponibilité et statistiques par emplacement
                        </p>
                        <a href="{{ route('reports.occupation') }}" class="btn btn-success">
                            <i class="fas fa-th-large"></i> Voir le rapport
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rapport Clients -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100">
                <div class="card-body">
                    <div class="text-center">
                        <div class="mb-3">
                            <i class="fas fa-users fa-3x text-info"></i>
                        </div>
                        <h5 class="card-title">Rapport Clients</h5>
                        <p class="card-text text-muted small">
                            Nouveaux clients, top clients, retards de paiement
                        </p>
                        <a href="{{ route('reports.clients') }}" class="btn btn-info">
                            <i class="fas fa-user-friends"></i> Voir le rapport
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rapport Accès -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100">
                <div class="card-body">
                    <div class="text-center">
                        <div class="mb-3">
                            <i class="fas fa-lock fa-3x text-warning"></i>
                        </div>
                        <h5 class="card-title">Rapport Sécurité & Accès</h5>
                        <p class="card-text text-muted small">
                            Logs d'accès, tentatives refusées et statistiques de sécurité
                        </p>
                        <a href="{{ route('reports.access') }}" class="btn btn-warning">
                            <i class="fas fa-key"></i> Voir le rapport
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rapports Planifiés -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-calendar-alt me-2"></i>Rapports Planifiés
            </h6>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#scheduleReportModal">
                <i class="fas fa-plus"></i> Planifier un rapport
            </button>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Fonctionnalité à venir :</strong> Planifiez des rapports automatiques envoyés par email quotidiennement, hebdomadairement ou mensuellement.
            </div>

            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Type de rapport</th>
                            <th>Fréquence</th>
                            <th>Destinataires</th>
                            <th>Prochaine exécution</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <p class="mb-0">Aucun rapport planifié pour le moment</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Exports Rapides -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-download me-2"></i>Exports Rapides
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Exporter toutes les données</h6>
                    <p class="text-muted small">Exportez l'ensemble de vos données au format Excel pour analyse externe</p>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-success" disabled>
                            <i class="fas fa-file-excel"></i> Export Excel (À venir)
                        </button>
                        <button class="btn btn-outline-danger" disabled>
                            <i class="fas fa-file-pdf"></i> Export PDF (À venir)
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <h6>Exporter une période spécifique</h6>
                    <form class="row g-2">
                        <div class="col-md-5">
                            <input type="date" class="form-control form-control-sm" placeholder="Date début">
                        </div>
                        <div class="col-md-5">
                            <input type="date" class="form-control form-control-sm" placeholder="Date fin">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-primary btn-sm w-100" disabled>
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Planification Rapport -->
<div class="modal fade" id="scheduleReportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Planifier un rapport automatique</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label class="form-label">Type de rapport</label>
                        <select class="form-select" required>
                            <option value="">Sélectionner...</option>
                            <option value="financial">Rapport Financier</option>
                            <option value="occupation">Rapport d'Occupation</option>
                            <option value="clients">Rapport Clients</option>
                            <option value="access">Rapport Sécurité & Accès</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Fréquence</label>
                        <select class="form-select" required>
                            <option value="daily">Quotidien</option>
                            <option value="weekly">Hebdomadaire</option>
                            <option value="monthly">Mensuel</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Destinataires (emails séparés par des virgules)</label>
                        <input type="text" class="form-control" placeholder="email1@example.com, email2@example.com">
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Cette fonctionnalité sera disponible prochainement
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" disabled>Planifier</button>
            </div>
        </div>
    </div>
</div>
@endsection
