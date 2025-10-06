@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-lock me-2"></i>Rapport Sécurité & Accès
            </h1>
            <p class="text-muted">Analyse des logs d'accès et alertes de sécurité</p>
        </div>
        <div>
            <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
            <button class="btn btn-danger" onclick="exportPDF('access')">
                <i class="fas fa-file-pdf"></i> Export PDF
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
            <div class="card border-left-primary shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Accès
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $totalAccess }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-door-open fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Accès Autorisés
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $accessAutorises }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Accès Refusés
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $accessRefuses }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
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
                                Taux de Refus
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $totalAccess > 0 ? number_format(($accessRefuses / $totalAccess) * 100, 1) : 0 }}%
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

    <div class="row">
        <!-- Évolution des accès -->
        <div class="col-xl-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Évolution Quotidienne des Accès</h6>
                </div>
                <div class="card-body">
                    <canvas id="evolutionChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Accès par Méthode -->
        <div class="col-xl-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Accès par Méthode</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Méthode</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Autorisés</th>
                                    <th class="text-center">Refusés</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($accessParMethode as $methode)
                                <tr>
                                    <td>
                                        <i class="fas fa-{{ $methode->methode === 'pin' ? 'keyboard' : ($methode->methode === 'qr' ? 'qrcode' : 'id-card') }} me-2"></i>
                                        {{ strtoupper($methode->methode) }}
                                    </td>
                                    <td class="text-center"><strong>{{ $methode->total }}</strong></td>
                                    <td class="text-center">
                                        <span class="badge bg-success">{{ $methode->autorises }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-danger">{{ $methode->refuses }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top 10 Clients avec le plus d'accès -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Top 10 Clients avec le Plus d'Accès</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>Client</th>
                            <th>Email</th>
                            <th class="text-center">Nombre d'Accès</th>
                            <th>Fréquence</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topClientsAccess as $index => $log)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $log->client->nom ?? 'N/A' }} {{ $log->client->prenom ?? '' }}</strong>
                            </td>
                            <td>{{ $log->client->email ?? 'N/A' }}</td>
                            <td class="text-center">
                                <span class="badge bg-primary">{{ $log->total_acces }}</span>
                            </td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-info"
                                         role="progressbar"
                                         style="width: {{ ($log->total_acces / $topClientsAccess->first()->total_acces) * 100 }}%">
                                        {{ $log->total_acces }} accès
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

    <!-- Accès Refusés Récents -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>Accès Refusés Récents (20 derniers)
            </h6>
            <span class="badge bg-danger">{{ $accessRefusesRecents->count() }} tentatives</span>
        </div>
        <div class="card-body">
            @if($accessRefusesRecents->isEmpty())
                <div class="text-center py-4">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <p class="text-muted">Aucun accès refusé sur la période</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th>Date & Heure</th>
                                <th>Client</th>
                                <th>Box</th>
                                <th>Méthode</th>
                                <th>Raison du Refus</th>
                                <th>Code Utilisé</th>
                                <th class="text-center">Détails</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($accessRefusesRecents as $log)
                            <tr class="{{ $log->raison_refus === 'Code PIN inconnu' ? 'table-danger' : '' }}">
                                <td>
                                    <small>{{ $log->date_heure->format('d/m/Y H:i:s') }}</small>
                                </td>
                                <td>
                                    <strong>{{ $log->client->nom ?? 'Inconnu' }} {{ $log->client->prenom ?? '' }}</strong>
                                </td>
                                <td>
                                    {{ $log->box->numero ?? 'N/A' }}
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ strtoupper($log->methode) }}</span>
                                </td>
                                <td>
                                    <span class="text-danger">
                                        <i class="fas fa-exclamation-circle me-1"></i>
                                        {{ $log->raison_refus }}
                                    </span>
                                </td>
                                <td>
                                    <code>{{ $log->code_utilise }}</code>
                                </td>
                                <td class="text-center">
                                    @if($log->ip_address)
                                        <span class="badge bg-info" title="IP: {{ $log->ip_address }}">
                                            <i class="fas fa-map-marker-alt"></i> IP
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
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
        labels: {!! json_encode($evolutionAccess->pluck('jour')) !!},
        datasets: [
            {
                label: 'Accès Autorisés',
                data: {!! json_encode($evolutionAccess->pluck('autorises')) !!},
                borderColor: 'rgb(28, 200, 138)',
                backgroundColor: 'rgba(28, 200, 138, 0.1)',
                tension: 0.3,
                fill: true
            },
            {
                label: 'Accès Refusés',
                data: {!! json_encode($evolutionAccess->pluck('refuses')) !!},
                borderColor: 'rgb(231, 74, 59)',
                backgroundColor: 'rgba(231, 74, 59, 0.1)',
                tension: 0.3,
                fill: true
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'top'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

function exportPDF(type) {
    window.location.href = `/reports/export-pdf?type=${type}&date_debut={{ $dateDebut }}&date_fin={{ $dateFin }}`;
}
</script>
@endpush
@endsection
