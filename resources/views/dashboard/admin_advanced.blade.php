@extends('layouts.app')

@section('title', 'Dashboard Avancé')

@section('content')
<div class="container-fluid">
    <!-- En-tête avec actions -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="fas fa-chart-line text-primary"></i> Dashboard Avancé
            </h2>
            <p class="text-muted mb-0">Vue d'ensemble complète de votre activité</p>
        </div>
        <div class="btn-group">
            <button class="btn btn-outline-primary" onclick="refreshDashboard()">
                <i class="fas fa-sync-alt"></i> Actualiser
            </button>
            <button class="btn btn-outline-success" onclick="exportDashboard()">
                <i class="fas fa-file-excel"></i> Exporter
            </button>
        </div>
    </div>

    <!-- KPI Cards - Ligne 1 -->
    <div class="row mb-4">
        <!-- Chiffre d'affaires -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                CA du Mois
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['ca_mois'] ?? 0, 2) }} €
                            </div>
                            <div class="mt-2">
                                @php
                                    $variation = $stats['variation_ca'] ?? 0;
                                @endphp
                                @if($variation > 0)
                                    <span class="badge bg-success">
                                        <i class="fas fa-arrow-up"></i> +{{ number_format($variation, 1) }}%
                                    </span>
                                @elseif($variation < 0)
                                    <span class="badge bg-danger">
                                        <i class="fas fa-arrow-down"></i> {{ number_format($variation, 1) }}%
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-minus"></i> 0%
                                    </span>
                                @endif
                                <small class="text-muted ms-1">vs mois dernier</small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-euro-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Taux d'occupation -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Taux d'Occupation
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['taux_occupation'] ?? 0, 1) }}%
                            </div>
                            <div class="progress mt-2" style="height: 8px;">
                                <div class="progress-bar bg-success"
                                     role="progressbar"
                                     style="width: {{ $stats['taux_occupation'] ?? 0 }}%">
                                </div>
                            </div>
                            <small class="text-muted mt-1">
                                {{ $stats['boxes_occupes'] ?? 0 }}/{{ $stats['boxes_total'] ?? 0 }} boxes
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-warehouse fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Clients actifs -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Clients Actifs
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['clients_actifs'] ?? 0 }}
                            </div>
                            <div class="mt-2">
                                <span class="badge bg-primary">
                                    <i class="fas fa-user-plus"></i> {{ $stats['nouveaux_clients_mois'] ?? 0 }}
                                </span>
                                <small class="text-muted ms-1">ce mois</small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Impayés -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Impayés
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['montant_impayes'] ?? 0, 2) }} €
                            </div>
                            <div class="mt-2">
                                <span class="badge bg-danger">
                                    {{ $stats['nb_factures_impayees'] ?? 0 }} factures
                                </span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques - Ligne 2 -->
    <div class="row mb-4">
        <!-- Évolution CA 12 mois -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-area"></i> Évolution du CA (12 derniers mois)
                    </h6>
                    <div class="dropdown no-arrow">
                        <select class="form-select form-select-sm" id="caChartPeriod">
                            <option value="12">12 mois</option>
                            <option value="6">6 mois</option>
                            <option value="3">3 mois</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="caChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Top 5 Clients -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-trophy"></i> Top 5 Clients
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="topClientsChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Activité récente et Alertes - Ligne 3 -->
    <div class="row">
        <!-- Activité récente -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-clock"></i> Activité Récente
                    </h6>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <div class="timeline">
                        @foreach($activites_recentes ?? [] as $activite)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="me-3">
                                    <span class="badge {{ $activite['badge_class'] ?? 'bg-primary' }} rounded-circle"
                                          style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas {{ $activite['icon'] ?? 'fa-info' }}"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ $activite['titre'] ?? '' }}</h6>
                                    <p class="text-muted mb-1 small">{{ $activite['description'] ?? '' }}</p>
                                    <small class="text-muted">
                                        <i class="fas fa-clock"></i>
                                        {{ $activite['date']->diffForHumans() ?? '' }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Alertes et Actions requises -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-warning text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-bell"></i> Alertes & Actions Requises
                    </h6>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @forelse($alertes ?? [] as $alerte)
                    <div class="alert alert-{{ $alerte['type'] ?? 'info' }} d-flex align-items-center mb-3" role="alert">
                        <i class="fas {{ $alerte['icon'] ?? 'fa-exclamation-triangle' }} me-2"></i>
                        <div class="flex-grow-1">
                            <strong>{{ $alerte['titre'] ?? '' }}</strong>
                            <p class="mb-0 small">{{ $alerte['message'] ?? '' }}</p>
                        </div>
                        @if(isset($alerte['action_url']))
                        <a href="{{ $alerte['action_url'] }}" class="btn btn-sm btn-outline-{{ $alerte['type'] ?? 'primary' }}">
                            Action
                        </a>
                        @endif
                    </div>
                    @empty
                    <div class="text-center text-success py-4">
                        <i class="fas fa-check-circle fa-3x mb-3"></i>
                        <p class="mb-0">Aucune alerte. Tout est sous contrôle !</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques avancées - Ligne 4 -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie"></i> Statistiques Détaillées
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center mb-3">
                            <canvas id="statutBoxesChart" height="150"></canvas>
                            <h6 class="mt-2">Statut des Boxes</h6>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <canvas id="typeContratChart" height="150"></canvas>
                            <h6 class="mt-2">Types de Contrats</h6>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <canvas id="paiementsChart" height="150"></canvas>
                            <h6 class="mt-2">Modes de Paiement</h6>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <canvas id="sourceClientsChart" height="150"></canvas>
                            <h6 class="mt-2">Sources Clients</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-primary {
    border-left: 4px solid #4e73df !important;
}
.border-left-success {
    border-left: 4px solid #1cc88a !important;
}
.border-left-info {
    border-left: 4px solid #36b9cc !important;
}
.border-left-warning {
    border-left: 4px solid #f6c23e !important;
}

.timeline-item {
    position: relative;
}

.timeline-item:not(:last-child):before {
    content: '';
    position: absolute;
    left: 20px;
    top: 40px;
    height: calc(100% + 12px);
    width: 2px;
    background: #e3e6f0;
}
</style>

<script>
// Graphique CA
const caCtx = document.getElementById('caChart').getContext('2d');
new Chart(caCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($ca_labels ?? []) !!},
        datasets: [{
            label: 'Chiffre d\'Affaires',
            data: {!! json_encode($ca_data ?? []) !!},
            borderColor: 'rgb(78, 115, 223)',
            backgroundColor: 'rgba(78, 115, 223, 0.05)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value.toLocaleString() + ' €';
                    }
                }
            }
        }
    }
});

// Graphique Top Clients
const topClientsCtx = document.getElementById('topClientsChart').getContext('2d');
new Chart(topClientsCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($top_clients_labels ?? []) !!},
        datasets: [{
            label: 'CA généré (€)',
            data: {!! json_encode($top_clients_data ?? []) !!},
            backgroundColor: [
                'rgba(28, 200, 138, 0.8)',
                'rgba(54, 185, 204, 0.8)',
                'rgba(246, 194, 62, 0.8)',
                'rgba(231, 74, 59, 0.8)',
                'rgba(133, 135, 150, 0.8)'
            ]
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

// Graphique Statut Boxes (Donut)
const statutBoxesCtx = document.getElementById('statutBoxesChart').getContext('2d');
new Chart(statutBoxesCtx, {
    type: 'doughnut',
    data: {
        labels: ['Occupés', 'Libres', 'Réservés', 'Maintenance'],
        datasets: [{
            data: {!! json_encode($statut_boxes_data ?? [0,0,0,0]) !!},
            backgroundColor: [
                'rgba(28, 200, 138, 0.8)',
                'rgba(54, 185, 204, 0.8)',
                'rgba(246, 194, 62, 0.8)',
                'rgba(231, 74, 59, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

function refreshDashboard() {
    location.reload();
}

function exportDashboard() {
    window.location.href = '{{ route("admin.dashboard.export") }}';
}
</script>
@endsection
