@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-chart-pie me-2"></i>Rapport d'Occupation
            </h1>
            <p class="text-muted">Analyse du taux d'occupation et disponibilité des boxes</p>
        </div>
        <div>
            <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
            <button class="btn btn-danger" onclick="exportPDF('occupation')">
                <i class="fas fa-file-pdf"></i> Export PDF
            </button>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label">Date de référence</label>
                    <input type="date" name="date" class="form-control" value="{{ $date }}">
                </div>
                <div class="col-md-6">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Actualiser
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
                                Taux d'Occupation
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($tauxOccupation, 1) }}%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
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
                                Boxes Occupés
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $boxesOccupes }} / {{ $totalBoxes }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-th-large fa-2x text-gray-300"></i>
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
                                Boxes Libres
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $boxesLibres }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-unlock fa-2x text-gray-300"></i>
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
                                En Maintenance
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $boxesMaintenance }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tools fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Évolution de l'occupation -->
        <div class="col-xl-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Évolution du Taux d'Occupation (6 derniers mois)</h6>
                </div>
                <div class="card-body">
                    <canvas id="evolutionChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Répartition des statuts -->
        <div class="col-xl-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Répartition des Boxes</h6>
                </div>
                <div class="card-body">
                    <canvas id="statutChart"></canvas>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="fas fa-circle text-success"></i> Occupés</span>
                            <strong>{{ $boxesOccupes }} ({{ number_format(($boxesOccupes / $totalBoxes) * 100, 1) }}%)</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="fas fa-circle text-info"></i> Libres</span>
                            <strong>{{ $boxesLibres }} ({{ number_format(($boxesLibres / $totalBoxes) * 100, 1) }}%)</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="fas fa-circle text-primary"></i> Réservés</span>
                            <strong>{{ $boxesReserves }} ({{ number_format(($boxesReserves / $totalBoxes) * 100, 1) }}%)</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="fas fa-circle text-warning"></i> Maintenance</span>
                            <strong>{{ $boxesMaintenance }} ({{ number_format(($boxesMaintenance / $totalBoxes) * 100, 1) }}%)</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Occupation par Emplacement -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Taux d'Occupation par Emplacement</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th>Emplacement</th>
                            <th class="text-center">Total Boxes</th>
                            <th class="text-center">Occupés</th>
                            <th class="text-center">Taux d'Occupation</th>
                            <th>Progression</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($occupationParEmplacement as $item)
                        <tr>
                            <td><strong>{{ $item['emplacement'] }}</strong></td>
                            <td class="text-center">{{ $item['total'] }}</td>
                            <td class="text-center">{{ $item['occupes'] }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $item['taux'] >= 80 ? 'success' : ($item['taux'] >= 50 ? 'warning' : 'danger') }}">
                                    {{ number_format($item['taux'], 1) }}%
                                </span>
                            </td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-{{ $item['taux'] >= 80 ? 'success' : ($item['taux'] >= 50 ? 'warning' : 'danger') }}"
                                         role="progressbar"
                                         style="width: {{ $item['taux'] }}%"
                                         aria-valuenow="{{ $item['taux'] }}" aria-valuemin="0" aria-valuemax="100">
                                        {{ number_format($item['taux'], 1) }}%
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Occupation par Famille -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Taux d'Occupation par Famille de Boxes</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th>Famille</th>
                            <th class="text-center">Total Boxes</th>
                            <th class="text-center">Occupés</th>
                            <th class="text-center">Taux d'Occupation</th>
                            <th>Progression</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($occupationParFamille as $item)
                        <tr>
                            <td><strong>{{ $item['famille'] }}</strong></td>
                            <td class="text-center">{{ $item['total'] }}</td>
                            <td class="text-center">{{ $item['occupes'] }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $item['taux'] >= 80 ? 'success' : ($item['taux'] >= 50 ? 'warning' : 'danger') }}">
                                    {{ number_format($item['taux'], 1) }}%
                                </span>
                            </td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-{{ $item['taux'] >= 80 ? 'success' : ($item['taux'] >= 50 ? 'warning' : 'danger') }}"
                                         role="progressbar"
                                         style="width: {{ $item['taux'] }}%"
                                         aria-valuenow="{{ $item['taux'] }}" aria-valuemin="0" aria-valuemax="100">
                                        {{ number_format($item['taux'], 1) }}%
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Graphique Évolution
const evolutionCtx = document.getElementById('evolutionChart').getContext('2d');
new Chart(evolutionCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($evolutionOccupation->pluck('mois')) !!},
        datasets: [{
            label: 'Taux d\'Occupation',
            data: {!! json_encode($evolutionOccupation->pluck('taux')) !!},
            borderColor: 'rgb(28, 200, 138)',
            backgroundColor: 'rgba(28, 200, 138, 0.1)',
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
                max: 100,
                ticks: {
                    callback: function(value) {
                        return value + '%';
                    }
                }
            }
        }
    }
});

// Graphique Statuts
const statutCtx = document.getElementById('statutChart').getContext('2d');
new Chart(statutCtx, {
    type: 'doughnut',
    data: {
        labels: ['Occupés', 'Libres', 'Réservés', 'Maintenance'],
        datasets: [{
            data: [
                {{ $boxesOccupes }},
                {{ $boxesLibres }},
                {{ $boxesReserves }},
                {{ $boxesMaintenance }}
            ],
            backgroundColor: [
                'rgba(28, 200, 138, 0.8)',
                'rgba(54, 185, 204, 0.8)',
                'rgba(78, 115, 223, 0.8)',
                'rgba(246, 194, 62, 0.8)'
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
    window.location.href = `/reports/export-pdf?type=${type}&date={{ $date }}`;
}
</script>
@endpush
@endsection
