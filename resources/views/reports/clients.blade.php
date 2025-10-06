@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-users me-2"></i>Rapport Clients
            </h1>
            <p class="text-muted">Analyse de la base clients et performances</p>
        </div>
        <div>
            <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
            <button class="btn btn-danger" onclick="exportPDF('clients')">
                <i class="fas fa-file-pdf"></i> Export PDF
            </button>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Clients
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total_clients'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Clients Actifs
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['clients_actifs'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Nouveaux ce Mois
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['nouveaux_ce_mois'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-plus fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Top 10 Clients par CA -->
        <div class="col-xl-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top 10 Clients par Chiffre d'Affaires</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Client</th>
                                    <th>Email</th>
                                    <th class="text-end">CA Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topClients as $index => $client)
                                <tr>
                                    <td>
                                        @if($index === 0)
                                            <i class="fas fa-trophy text-warning"></i>
                                        @elseif($index === 1)
                                            <i class="fas fa-medal text-secondary"></i>
                                        @elseif($index === 2)
                                            <i class="fas fa-medal text-warning" style="opacity: 0.7;"></i>
                                        @else
                                            {{ $index + 1 }}
                                        @endif
                                    </td>
                                    <td><strong>{{ $client->nom }} {{ $client->prenom }}</strong></td>
                                    <td>{{ $client->email }}</td>
                                    <td class="text-end">
                                        <span class="badge bg-success">{{ number_format($client->total_ca, 2) }} €</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Nouveaux Clients par Mois -->
        <div class="col-xl-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Nouveaux Clients (12 derniers mois)</h6>
                </div>
                <div class="card-body">
                    <canvas id="nouveauxClientsChart" height="280"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Clients avec Retards de Paiement -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>Clients avec Retards de Paiement
            </h6>
            <span class="badge bg-danger">{{ $clientsRetard->count() }} clients</span>
        </div>
        <div class="card-body">
            @if($clientsRetard->isEmpty())
                <div class="text-center py-4">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <p class="text-muted">Aucun client avec retard de paiement</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th>Client</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th class="text-center">Nb Factures Impayées</th>
                                <th class="text-end">Montant Dû</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($clientsRetard as $client)
                            <tr>
                                <td>
                                    <strong>{{ $client->nom }} {{ $client->prenom }}</strong>
                                    @if($client->montant_du > 500)
                                        <span class="badge bg-danger ms-2">Prioritaire</span>
                                    @endif
                                </td>
                                <td>{{ $client->email }}</td>
                                <td>{{ $client->telephone }}</td>
                                <td class="text-center">
                                    <span class="badge bg-warning">{{ $client->nb_factures_impayees }}</span>
                                </td>
                                <td class="text-end">
                                    <strong class="text-danger">{{ number_format($client->montant_du, 2) }} €</strong>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('clients.show', $client->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> Voir
                                    </a>
                                    <button class="btn btn-sm btn-warning" onclick="sendReminder({{ $client->id }})">
                                        <i class="fas fa-envelope"></i> Relancer
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light">
                            <tr>
                                <th colspan="4" class="text-end">Total Impayés :</th>
                                <th class="text-end">
                                    <strong class="text-danger">
                                        {{ number_format($clientsRetard->sum('montant_du'), 2) }} €
                                    </strong>
                                </th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Graphique Nouveaux Clients
const nouveauxCtx = document.getElementById('nouveauxClientsChart').getContext('2d');
new Chart(nouveauxCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($nouveauxClients->pluck('mois')->reverse()) !!},
        datasets: [{
            label: 'Nouveaux Clients',
            data: {!! json_encode($nouveauxClients->pluck('total')->reverse()) !!},
            backgroundColor: 'rgba(78, 115, 223, 0.8)',
            borderColor: 'rgba(78, 115, 223, 1)',
            borderWidth: 1
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
                    stepSize: 1
                }
            }
        }
    }
});

function exportPDF(type) {
    window.location.href = `/reports/export-pdf?type=${type}`;
}

function sendReminder(clientId) {
    if (confirm('Envoyer un rappel de paiement à ce client ?')) {
        fetch(`/clients/${clientId}/send-reminder`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Rappel envoyé avec succès');
            } else {
                alert('Erreur lors de l\'envoi du rappel');
            }
        })
        .catch(error => {
            alert('Erreur lors de l\'envoi du rappel');
        });
    }
}
</script>
@endpush
@endsection
