<template>
    <div class="container-fluid">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">
                <i class="fas fa-tachometer-alt me-2"></i>
                Tableau de Bord Administrateur
            </h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <button type="button" class="btn btn-sm btn-outline-secondary" @click="refreshStats">
                    <i class="fas fa-sync-alt me-1"></i>
                    Actualiser
                </button>
            </div>
        </div>

        <!-- Statistiques principales -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Clients Actifs
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ stats.clients_actifs }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    CA du Mois
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ formatCurrency(stats.ca_mois) }}
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
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Taux d'Occupation
                                </div>
                                <div class="row no-gutters align-items-center">
                                    <div class="col-auto">
                                        <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">
                                            {{ stats.taux_occupation }}%
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="progress progress-sm mr-2">
                                            <div
                                                class="progress-bar bg-info"
                                                role="progressbar"
                                                :style="{ width: stats.taux_occupation + '%' }"
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Impayés
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ formatCurrency(stats.impayes) }}
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

        <!-- Graphiques -->
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-chart-line me-2"></i>
                            Évolution du Chiffre d'Affaires (12 derniers mois)
                        </h6>
                    </div>
                    <div class="card-body">
                        <div style="height: 300px;">
                            <LineChart
                                :labels="caEvolution.labels"
                                :datasets="caEvolution.datasets"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-chart-pie me-2"></i>
                            Top 5 Clients
                        </h6>
                    </div>
                    <div class="card-body">
                        <div style="height: 300px;">
                            <BarChart
                                :labels="topClients.labels"
                                :datasets="topClients.datasets"
                                :horizontal="true"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recherche globale -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-search me-2"></i>
                            Recherche Rapide
                        </h6>
                    </div>
                    <div class="card-body">
                        <SearchBar
                            v-model="searchQuery"
                            placeholder="Rechercher un client, une facture, un contrat..."
                            endpoint="/api/search"
                            @select="onSelectSearchResult"
                        >
                            <template #result="{ result }">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i :class="getResultIcon(result.type)" class="me-2"></i>
                                        <strong>{{ result.title }}</strong>
                                        <small class="text-muted d-block">{{ result.subtitle }}</small>
                                    </div>
                                    <span class="badge" :class="getResultBadgeClass(result.type)">
                                        {{ result.type }}
                                    </span>
                                </div>
                            </template>
                        </SearchBar>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import LineChart from '@/Components/LineChart.vue';
import BarChart from '@/Components/BarChart.vue';
import SearchBar from '@/Components/SearchBar.vue';

const props = defineProps({
    stats: Object,
    caEvolutionData: Array,
    topClientsData: Array,
});

const searchQuery = ref('');

const formatCurrency = (value) => {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR',
        minimumFractionDigits: 0,
    }).format(value || 0);
};

const caEvolution = computed(() => ({
    labels: props.caEvolutionData?.map(item => item.month) || [],
    datasets: [
        {
            label: 'Chiffre d\'Affaires',
            data: props.caEvolutionData?.map(item => item.ca) || [],
            borderColor: 'rgb(13, 110, 253)',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            fill: true,
        },
    ],
}));

const topClients = computed(() => ({
    labels: props.topClientsData?.map(client => client.nom) || [],
    datasets: [
        {
            label: 'CA Total',
            data: props.topClientsData?.map(client => client.factures_sum_montant_ttc) || [],
            backgroundColor: [
                'rgba(13, 110, 253, 0.8)',
                'rgba(25, 135, 84, 0.8)',
                'rgba(255, 193, 7, 0.8)',
                'rgba(220, 53, 69, 0.8)',
                'rgba(13, 202, 240, 0.8)',
            ],
        },
    ],
}));

const refreshStats = () => {
    router.reload({ only: ['stats', 'caEvolutionData', 'topClientsData'] });
};

const onSelectSearchResult = (result) => {
    console.log('Résultat sélectionné:', result);
    // Rediriger vers la page correspondante
    if (result.url) {
        window.location.href = result.url;
    }
};

const getResultIcon = (type) => {
    const icons = {
        'client': 'fas fa-user',
        'facture': 'fas fa-file-invoice',
        'contrat': 'fas fa-file-contract',
        'box': 'fas fa-box',
    };
    return icons[type] || 'fas fa-circle';
};

const getResultBadgeClass = (type) => {
    const classes = {
        'client': 'bg-primary',
        'facture': 'bg-success',
        'contrat': 'bg-info',
        'box': 'bg-warning',
    };
    return classes[type] || 'bg-secondary';
};
</script>

<style scoped>
.border-left-primary {
    border-left: 0.25rem solid #0d6efd !important;
}

.border-left-success {
    border-left: 0.25rem solid #198754 !important;
}

.border-left-info {
    border-left: 0.25rem solid #0dcaf0 !important;
}

.border-left-warning {
    border-left: 0.25rem solid #ffc107 !important;
}

.text-xs {
    font-size: 0.7rem;
}

.progress-sm {
    height: 0.5rem;
}
</style>
