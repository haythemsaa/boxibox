@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-euro-sign me-2"></i>Rapport Financier
            </h1>
            <p class="text-muted">Analyse des performances financières</p>
        </div>
        <div>
            <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
            <button class="btn btn-danger" onclick="exportPDF('financial')">
                <i class="fas fa-file-pdf"></i> Export PDF
            </button>
            <button class="btn btn-success" onclick="exportExcel('financial')">
                <i class="fas fa-file-excel"></i> Export Excel
            </button>
        </div>
    </div>

    <!-- Filtres de période -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Date de début</label>
                    <input type="date" name="date_debut" class="form-control" value="{{ $dateDebut }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date de fin</label>
                    <input type="date" name="date_fin" class="form-control" value="{{ $dateFin }}">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Chiffre d'Affaires
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($ca, 2) }} €
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-euro-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Factures Émises
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $facturesEmises }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-invoice fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Montant Impayé
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($montantImpaye, 2) }} €
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Taux de Paiement
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $montantTotal > 0 ? number_format(($montantPaye / $montantTotal) * 100, 1) : 0 }}%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Évolution du CA -->
        <div class="col-xl-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Évolution du Chiffre d'Affaires</h6>
                </div>
                <div class="card-body">
                    <canvas id="caChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- CA par Mode de Paiement -->
        <div class="col-xl-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">CA par Mode de Paiement</h6>
                </div>
                <div class="card-body">
                    <canvas id="paymentMethodChart"></canvas>
                    <div class="mt-3">
                        @foreach($caParMode as $mode)
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ ucfirst($mode->mode_paiement) }}</span>
                            <strong>{{ number_format($mode->total, 2) }} €</strong>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau des Factures -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Statut des Factures</h6>
        </div>
        <div class="card-body">
            <div class="row text-center mb-3">
                <div class="col-md-4">
                    <h3 class="text-success">{{ $facturesPayees }}</h3>
                    <p class="text-muted">Payées</p>
                </div>
                <div class="col-md-4">
                    <h3 class="text-warning">{{ $facturesImpayees }}</h3>
                    <p class="text-muted">Impayées</p>
                </div>
                <div class="col-md-4">
                    <h3 class="text-primary">{{ $facturesEmises }}</h3>
                    <p class="text-muted">Total Émises</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Graphique Évolution CA
const caCtx = document.getElementById('caChart').getContext('2d');
new Chart(caCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($evolutionCA->pluck('mois')) !!},
        datasets: [{
            label: 'Chiffre d\'Affaires',
            data: {!! json_encode($evolutionCA->pluck('total')) !!},
            borderColor: 'rgb(78, 115, 223)',
            backgroundColor: 'rgba(78, 115, 223, 0.1)',
            tension: 0.3,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true
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

// Graphique CA par Mode de Paiement
const paymentCtx = document.getElementById('paymentMethodChart').getContext('2d');
new Chart(paymentCtx, {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($caParMode->pluck('mode_paiement')) !!},
        datasets: [{
            data: {!! json_encode($caParMode->pluck('total')) !!},
            backgroundColor: [
                'rgba(78, 115, 223, 0.8)',
                'rgba(28, 200, 138, 0.8)',
                'rgba(54, 185, 204, 0.8)',
                'rgba(246, 194, 62, 0.8)',
                'rgba(231, 74, 59, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

function exportPDF(type) {
    window.location.href = `/reports/export-pdf?type=${type}&date_debut={{ $dateDebut }}&date_fin={{ $dateFin }}`;
}

function exportExcel(type) {
    window.location.href = `/reports/export-excel?type=${type}&date_debut={{ $dateDebut }}&date_fin={{ $dateFin }}`;
}
</script>
@endpush
@endsection
