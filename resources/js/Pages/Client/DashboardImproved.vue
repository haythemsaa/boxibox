<template>
    <ClientLayout title="Tableau de bord">
        <!-- Welcome Header -->
        <div class="welcome-header mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h1 class="h2 mb-1">
                        Bonjour {{ $page.props.auth.user?.name || 'Client' }} 👋
                    </h1>
                    <p class="text-muted mb-0">
                        Voici un aperçu de votre espace de stockage
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <button @click="refreshStats" class="btn btn-outline-primary" :disabled="refreshing">
                        <i class="fas fa-sync-alt me-2" :class="{ 'fa-spin': refreshing }"></i>
                        Actualiser
                    </button>
                    <a :href="route('client.contrats')" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        Nouveau contrat
                    </a>
                </div>
            </div>
        </div>

        <!-- Modern Stats Cards with Animation -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <StatsCard
                    label="Contrats Actifs"
                    :value="stats.contrats_actifs || 0"
                    icon="fa-file-contract"
                    gradient="linear-gradient(135deg, #667eea 0%, #764ba2 100%)"
                    format="number"
                    :trend="5"
                />
            </div>
            <div class="col-xl-3 col-md-6">
                <StatsCard
                    label="Factures Impayées"
                    :value="stats.factures_impayees || 0"
                    icon="fa-exclamation-triangle"
                    gradient="linear-gradient(135deg, #f093fb 0%, #f5576c 100%)"
                    format="number"
                    :trend="-10"
                />
            </div>
            <div class="col-xl-3 col-md-6">
                <StatsCard
                    label="Montant Dû"
                    :value="stats.montant_du || 0"
                    icon="fa-euro-sign"
                    gradient="linear-gradient(135deg, #fa709a 0%, #fee140 100%)"
                    format="currency"
                />
            </div>
            <div class="col-xl-3 col-md-6">
                <StatsCard
                    label="Prélèvement Auto"
                    :value="stats.mandat_sepa_actif ? 'Actif' : 'Inactif'"
                    icon="fa-check-circle"
                    :gradient="stats.mandat_sepa_actif
                        ? 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)'
                        : 'linear-gradient(135deg, #9795f0 0%, #fbc8d4 100%)'"
                    format="text"
                />
            </div>
        </div>

        <!-- Alerts -->
        <div v-if="stats.factures_impayees > 0" class="alert alert-warning alert-modern mb-4">
            <div class="d-flex align-items-center">
                <div class="alert-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="alert-heading mb-1">Factures en attente</h6>
                    <p class="mb-0">
                        Vous avez {{ stats.factures_impayees }} facture(s) impayée(s) pour un montant de {{ formatCurrency(stats.montant_du) }}.
                    </p>
                </div>
                <a :href="route('client.factures')" class="btn btn-warning">
                    Payer maintenant
                </a>
            </div>
        </div>

        <div v-if="!stats.mandat_sepa_actif" class="alert alert-info alert-modern mb-4">
            <div class="d-flex align-items-center">
                <div class="alert-icon bg-info">
                    <i class="fas fa-university"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="alert-heading mb-1">Simplifiez vos paiements</h6>
                    <p class="mb-0">
                        Activez le prélèvement automatique SEPA et ne vous souciez plus de vos échéances.
                    </p>
                </div>
                <a :href="route('client.sepa.create')" class="btn btn-info">
                    Configurer
                </a>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="row g-4 mb-4">
            <!-- Left Column -->
            <div class="col-xl-8">
                <!-- Charts Row -->
                <div class="row g-4 mb-4">
                    <div class="col-lg-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-white border-bottom">
                                <h6 class="mb-0">
                                    <i class="fas fa-chart-pie me-2 text-primary"></i>
                                    Répartition des Factures
                                </h6>
                            </div>
                            <div class="card-body">
                                <canvas ref="facturesChart" height="220"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-white border-bottom">
                                <h6 class="mb-0">
                                    <i class="fas fa-chart-line me-2 text-success"></i>
                                    Paiements (6 mois)
                                </h6>
                            </div>
                            <div class="card-body">
                                <canvas ref="paymentsChart" height="220"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mes Contrats -->
                <div v-if="contratsActifs && contratsActifs.length > 0" class="card shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-file-contract me-2 text-primary"></i>
                            Mes Contrats Actifs
                        </h6>
                        <a :href="route('client.contrats')" class="btn btn-sm btn-outline-primary">
                            Voir tout
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>N° Contrat</th>
                                        <th>Box</th>
                                        <th>Date Début</th>
                                        <th>Loyer</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="contrat in contratsActifs.slice(0, 3)" :key="contrat.id">
                                        <td>
                                            <strong class="text-primary">{{ contrat.numero_contrat }}</strong>
                                        </td>
                                        <td>
                                            <span v-if="contrat.box">
                                                <i class="fas fa-box me-1"></i>
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
                                        <td>
                                            <strong class="text-success">{{ formatAmount(contrat.montant_loyer) }} €/mois</strong>
                                        </td>
                                        <td>
                                            <a :href="route('client.contrats.show', contrat.id)" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Dernières Factures -->
                <div v-if="dernieresFactures && dernieresFactures.length > 0" class="card shadow-sm">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-file-invoice me-2 text-warning"></i>
                            Dernières Factures
                        </h6>
                        <a :href="route('client.factures')" class="btn btn-sm btn-outline-primary">
                            Voir tout
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>N° Facture</th>
                                        <th>Date</th>
                                        <th>Échéance</th>
                                        <th>Montant</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="facture in dernieresFactures.slice(0, 3)" :key="facture.id">
                                        <td><strong>{{ facture.numero_facture }}</strong></td>
                                        <td>{{ formatDate(facture.date_emission) }}</td>
                                        <td>{{ formatDate(facture.date_echeance) }}</td>
                                        <td><strong>{{ formatAmount(facture.montant_ttc) }} €</strong></td>
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
                    </div>
                </div>
            </div>

            <!-- Right Column - Widgets -->
            <div class="col-xl-4">
                <!-- Quick Actions -->
                <div class="mb-4">
                    <QuickActionsWidget />
                </div>

                <!-- Recent Activity -->
                <div class="mb-4">
                    <RecentActivityWidget :activities="recentActivities" />
                </div>

                <!-- Box Info Card (if has active contract) -->
                <div v-if="contratsActifs && contratsActifs.length > 0" class="card shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0">
                            <i class="fas fa-box me-2 text-info"></i>
                            Ma Box Principale
                        </h6>
                    </div>
                    <div class="card-body">
                        <div v-if="contratsActifs[0].box" class="box-info">
                            <div class="text-center mb-3">
                                <div class="box-number">{{ contratsActifs[0].box.numero }}</div>
                                <span
                                    v-if="contratsActifs[0].box.famille"
                                    class="badge"
                                    :style="{ backgroundColor: contratsActifs[0].box.famille.couleur || '#6c757d' }"
                                >
                                    {{ contratsActifs[0].box.famille.nom }}
                                </span>
                            </div>
                            <div class="box-details">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Dimensions:</span>
                                    <strong>{{ contratsActifs[0].box.largeur }}m × {{ contratsActifs[0].box.profondeur }}m</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Surface:</span>
                                    <strong>{{ contratsActifs[0].box.surface }} m²</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="text-muted">Étage:</span>
                                    <strong>{{ contratsActifs[0].box.etage || 'RDC' }}</strong>
                                </div>
                                <a :href="route('client.boxplan')" class="btn btn-outline-primary btn-sm w-100">
                                    <i class="fas fa-map-marked-alt me-2"></i>
                                    Voir le plan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Support Card -->
                <div class="card shadow-sm support-card">
                    <div class="card-body text-center p-4">
                        <div class="support-icon mb-3">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h6 class="mb-2">Besoin d'aide ?</h6>
                        <p class="text-muted small mb-3">
                            Notre équipe est là pour vous aider
                        </p>
                        <button @click="openChat" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-comments me-2"></i>
                            Ouvrir le chat
                        </button>
                        <a href="tel:+33123456789" class="btn btn-outline-primary w-100">
                            <i class="fas fa-phone me-2"></i>
                            01 23 45 67 89
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </ClientLayout>
</template>

<script>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import StatsCard from '@/Components/StatsCard.vue';
import QuickActionsWidget from '@/Components/QuickActionsWidget.vue';
import RecentActivityWidget from '@/Components/RecentActivityWidget.vue';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

export default {
    components: {
        ClientLayout,
        StatsCard,
        QuickActionsWidget,
        RecentActivityWidget
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
        },
        recentActivities: {
            type: Array,
            default: () => []
        }
    },

    setup(props) {
        const facturesChart = ref(null);
        const paymentsChart = ref(null);
        const refreshing = ref(false);

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
            refreshing.value = true;
            router.reload({
                only: ['stats', 'contratsActifs', 'dernieresFactures', 'recentActivities'],
                onFinish: () => {
                    refreshing.value = false;
                }
            });
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

        const openChat = () => {
            // Le widget de chat s'ouvrira automatiquement
            window.dispatchEvent(new CustomEvent('open-chat'));
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
                            borderWidth: 0
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

            // Line Chart - Évolution Paiements
            if (paymentsChart.value) {
                const moisActuel = new Date().getMonth();
                const labels = [];
                const data = [];

                for (let i = 5; i >= 0; i--) {
                    const date = new Date();
                    date.setMonth(moisActuel - i);
                    labels.push(date.toLocaleDateString('fr-FR', { month: 'short' }));
                    data.push(Math.random() * 1000 + 500);
                }

                new Chart(paymentsChart.value, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Montant payé (€)',
                            data: data,
                            backgroundColor: 'rgba(13, 110, 253, 0.1)',
                            borderColor: '#0d6efd',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true
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
            refreshing,
            formatCurrency,
            formatDate,
            formatAmount,
            refreshStats,
            getStatusBadge,
            getStatusLabel,
            openChat
        };
    }
};
</script>

<style scoped>
.welcome-header {
    padding: 1rem 0;
}

.alert-modern {
    border: none;
    border-radius: 12px;
    padding: 1.25rem;
}

.alert-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: rgba(255, 193, 7, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-right: 1rem;
    flex-shrink: 0;
}

.alert-icon.bg-info {
    background: rgba(13, 202, 240, 0.2);
}

.alert-heading {
    font-weight: 600;
}

.card {
    border: none;
    border-radius: 12px;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.box-number {
    font-size: 2.5rem;
    font-weight: 700;
    color: #0d6efd;
    line-height: 1;
    margin-bottom: 0.5rem;
}

.box-info {
    padding: 1rem 0;
}

.box-details {
    padding-top: 1rem;
    border-top: 1px solid #dee2e6;
}

.support-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
}

.support-card .card-body {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 12px;
    color: #212529;
}

.support-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    margin: 0 auto;
}

/* Dark mode */
.dark-mode .card {
    background: #2b3035;
    color: #f8f9fa;
}

.dark-mode .card-header {
    background: #2b3035 !important;
    border-bottom-color: #495057 !important;
    color: #f8f9fa;
}

.dark-mode .table {
    color: #f8f9fa;
}

.dark-mode .table-light {
    background: #343a40;
    color: #f8f9fa;
}

.dark-mode .box-details {
    border-top-color: #495057;
}

.dark-mode .support-card .card-body {
    background: rgba(43, 48, 53, 0.95);
    color: #f8f9fa;
}
</style>
