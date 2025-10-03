<template>
    <ClientLayout title="Tableau de bord">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
            <h1 class="h3">
                <i class="fas fa-home me-2"></i>
                Bienvenue{{ $page.props.auth.user ? ', ' + $page.props.auth.user.name : '' }}
            </h1>
            <button type="button" class="btn btn-sm btn-outline-secondary" @click="refreshStats">
                <i class="fas fa-sync-alt me-1"></i>
                Actualiser
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Contrats Actifs
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ stats.contrats_actifs || 0 }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-file-contract fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Factures Impayées
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ stats.factures_impayees || 0 }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Montant Dû
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ formatCurrency(stats.montant_du) }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-euro-sign fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    SEPA Actif
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ stats.mandat_sepa_actif ? 'Oui' : 'Non' }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x" :class="stats.mandat_sepa_actif ? 'text-success' : 'text-gray-300'"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        <div v-if="stats.factures_impayees > 0" class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Attention !</strong> Vous avez {{ stats.factures_impayees }} facture(s) impayée(s) pour un montant de {{ formatCurrency(stats.montant_du) }}.
            <a :href="route('client.factures')" class="alert-link">Voir les factures</a>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

        <div v-if="!stats.mandat_sepa_actif" class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Simplifiez vos paiements !</strong> Configurez un prélèvement automatique SEPA.
            <a :href="route('client.sepa')" class="alert-link">Configurer maintenant</a>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

        <!-- Charts Row -->
        <div class="row mb-4">
            <!-- Factures Chart -->
            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-chart-pie me-2"></i>
                            Répartition des Factures
                        </h6>
                    </div>
                    <div class="card-body">
                        <canvas ref="facturesChart" height="200"></canvas>
                    </div>
                </div>
            </div>

            <!-- Payments Chart -->
            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-chart-bar me-2"></i>
                            Évolution des Paiements (6 derniers mois)
                        </h6>
                    </div>
                    <div class="card-body">
                        <canvas ref="paymentsChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contrats Actifs -->
        <div v-if="contratsActifs && contratsActifs.length > 0" class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-file-contract me-2"></i>
                    Mes Contrats Actifs
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>N° Contrat</th>
                                <th>Box</th>
                                <th>Date Début</th>
                                <th>Loyer Mensuel</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="contrat in contratsActifs" :key="contrat.id">
                                <td><strong>{{ contrat.numero_contrat }}</strong></td>
                                <td>
                                    <span v-if="contrat.box">
                                        Box {{ contrat.box.numero }}
                                        <span
                                            v-if="contrat.box.famille"
                                            class="badge ms-1"
                                            :style="{ backgroundColor: contrat.box.famille.couleur || '#6c757d' }"
                                        >
                                            {{ contrat.box.famille.nom }}
                                        </span>
                                    </span>
                                </td>
                                <td>{{ formatDate(contrat.date_debut) }}</td>
                                <td><strong class="text-primary">{{ formatAmount(contrat.montant_loyer) }} €</strong></td>
                                <td>
                                    <a :href="route('client.contrats.show', contrat.id)" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a :href="route('client.contrats')" class="btn btn-primary">
                        Voir tous mes contrats
                    </a>
                </div>
            </div>
        </div>

        <!-- Dernières Factures -->
        <div v-if="dernieresFactures && dernieresFactures.length > 0" class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-file-invoice me-2"></i>
                    Dernières Factures
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>N° Facture</th>
                                <th>Date Émission</th>
                                <th>Date Échéance</th>
                                <th class="text-end">Montant TTC</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="facture in dernieresFactures" :key="facture.id">
                                <td><strong>{{ facture.numero_facture }}</strong></td>
                                <td>{{ formatDate(facture.date_emission) }}</td>
                                <td>{{ formatDate(facture.date_echeance) }}</td>
                                <td class="text-end"><strong>{{ formatAmount(facture.montant_ttc) }} €</strong></td>
                                <td>
                                    <span :class="getStatusBadge(facture.statut)">
                                        {{ getStatusLabel(facture.statut) }}
                                    </span>
                                </td>
                                <td>
                                    <a :href="route('client.factures.show', facture.id)" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a :href="route('client.factures')" class="btn btn-primary">
                        Voir toutes mes factures
                    </a>
                </div>
            </div>
        </div>
    </ClientLayout>
</template>

<script>
import { onMounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

export default {
    components: {
        ClientLayout
    },

    props: {
        stats: {
            type: Object,
            default: () => ({})
        },
        contratsActifs: {
            type: Array,
            default: () => []
        },
        dernieresFactures: {
            type: Array,
            default: () => []
        }
    },

    setup(props) {
        const facturesChart = ref(null);
        const paymentsChart = ref(null);

        const formatCurrency = (value) => {
            return new Intl.NumberFormat('fr-FR', {
                style: 'currency',
                currency: 'EUR'
            }).format(value || 0);
        };

        const formatDate = (date) => {
            if (!date) return '';
            return new Date(date).toLocaleDateString('fr-FR');
        };

        const formatAmount = (amount) => {
            if (!amount) return '0.00';
            return parseFloat(amount).toFixed(2);
        };

        const refreshStats = () => {
            router.reload({ only: ['stats'] });
        };

        const getStatusBadge = (statut) => {
            const classes = {
                'payee': 'badge bg-success',
                'paye': 'badge bg-success',
                'impayee': 'badge bg-danger',
                'en_attente': 'badge bg-warning',
                'en_retard': 'badge bg-danger'
            };
            return classes[statut] || 'badge bg-secondary';
        };

        const getStatusLabel = (statut) => {
            const labels = {
                'payee': 'Payée',
                'paye': 'Payée',
                'impayee': 'Impayée',
                'en_attente': 'En attente',
                'en_retard': 'En retard'
            };
            return labels[statut] || statut;
        };

        const initCharts = () => {
            // Pie Chart - Répartition Factures
            if (facturesChart.value) {
                const factures_payees = props.stats.factures_payees || 0;
                const factures_impayees = props.stats.factures_impayees || 0;
                const factures_total = props.stats.factures_total || factures_payees + factures_impayees;
                const factures_en_attente = factures_total - factures_payees - factures_impayees;

                new Chart(facturesChart.value, {
                    type: 'doughnut',
                    data: {
                        labels: ['Payées', 'Impayées', 'En attente'],
                        datasets: [{
                            data: [factures_payees, factures_impayees, factures_en_attente],
                            backgroundColor: ['#28a745', '#dc3545', '#ffc107'],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }

            // Bar Chart - Évolution Paiements (données simulées pour l'instant)
            if (paymentsChart.value) {
                const moisActuel = new Date().getMonth();
                const labels = [];
                const data = [];

                for (let i = 5; i >= 0; i--) {
                    const date = new Date();
                    date.setMonth(moisActuel - i);
                    labels.push(date.toLocaleDateString('fr-FR', { month: 'short', year: 'numeric' }));
                    // Simulation - à remplacer par vraies données du backend
                    data.push(Math.random() * 1000 + 500);
                }

                new Chart(paymentsChart.value, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Montant payé (€)',
                            data: data,
                            backgroundColor: '#0d6efd',
                            borderColor: '#0d6efd',
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
                                    callback: function(value) {
                                        return value + ' €';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        };

        onMounted(() => {
            initCharts();
        });

        return {
            facturesChart,
            paymentsChart,
            formatCurrency,
            formatDate,
            formatAmount,
            refreshStats,
            getStatusBadge,
            getStatusLabel
        };
    }
};
</script>

<style scoped>
.border-left-primary {
    border-left: 0.25rem solid #0d6efd !important;
}

.border-left-success {
    border-left: 0.25rem solid #198754 !important;
}

.border-left-warning {
    border-left: 0.25rem solid #ffc107 !important;
}

.border-left-danger {
    border-left: 0.25rem solid #dc3545 !important;
}

.text-xs {
    font-size: 0.7rem;
}

.text-gray-300 {
    color: #dee2e6;
}

.text-gray-800 {
    color: #343a40;
}
</style>
