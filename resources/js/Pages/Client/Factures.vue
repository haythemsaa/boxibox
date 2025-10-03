<template>
    <div class="container-fluid">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">
                <i class="fas fa-file-invoice me-2"></i>
                Mes Factures
            </h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <button type="button" class="btn btn-sm btn-outline-primary" @click="refreshData">
                    <i class="fas fa-sync-alt me-1"></i>
                    Actualiser
                </button>
            </div>
        </div>

        <!-- Statistiques rapides -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-light">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Total Factures</h6>
                                <h3 class="mb-0">{{ factures.length }}</h3>
                            </div>
                            <i class="fas fa-file-invoice fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Payées</h6>
                                <h3 class="mb-0 text-success">{{ facturesnPayees }}</h3>
                            </div>
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">En retard</h6>
                                <h3 class="mb-0 text-danger">{{ facturesEnRetard }}</h3>
                            </div>
                            <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Montant Dû</h6>
                                <h3 class="mb-0 text-warning">{{ formatCurrency(montantDu) }}</h3>
                            </div>
                            <i class="fas fa-euro-sign fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tableau des factures -->
        <div class="card shadow">
            <div class="card-body">
                <DataTable
                    :columns="columns"
                    :data="factures"
                    :per-page="10"
                    search-placeholder="Rechercher par numéro, montant..."
                    :has-actions="true"
                >
                    <template #cell-numero_facture="{ value }">
                        <span class="badge bg-primary">{{ value }}</span>
                    </template>

                    <template #cell-date_emission="{ value }">
                        {{ formatDate(value) }}
                    </template>

                    <template #cell-date_echeance="{ value }">
                        {{ formatDate(value) }}
                    </template>

                    <template #cell-montant_ttc="{ value }">
                        <strong>{{ formatCurrency(value) }}</strong>
                    </template>

                    <template #cell-montant_regle="{ value }">
                        {{ formatCurrency(value) }}
                    </template>

                    <template #cell-statut="{ value }">
                        <span class="badge" :class="getStatutClass(value)">
                            {{ getStatutLabel(value) }}
                        </span>
                    </template>

                    <template #actions="{ item }">
                        <div class="btn-group btn-group-sm">
                            <a
                                :href="route('client.factures.show', item.id)"
                                class="btn btn-outline-primary"
                                title="Voir"
                            >
                                <i class="fas fa-eye"></i>
                            </a>
                            <a
                                :href="route('client.factures.pdf', item.id)"
                                class="btn btn-outline-secondary"
                                title="Télécharger PDF"
                            >
                                <i class="fas fa-download"></i>
                            </a>
                            <button
                                v-if="item.statut !== 'payee'"
                                @click="payerFacture(item)"
                                class="btn btn-outline-success"
                                title="Payer"
                            >
                                <i class="fas fa-credit-card"></i>
                            </button>
                        </div>
                    </template>
                </DataTable>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import DataTable from '@/Components/DataTable.vue';

const props = defineProps({
    factures: {
        type: Array,
        required: true,
    },
});

const columns = [
    { key: 'numero_facture', label: 'N° Facture', sortable: true },
    { key: 'date_emission', label: 'Date Émission', sortable: true },
    { key: 'date_echeance', label: 'Date Échéance', sortable: true },
    { key: 'montant_ttc', label: 'Montant TTC', sortable: true },
    { key: 'montant_regle', label: 'Montant Réglé', sortable: true },
    { key: 'statut', label: 'Statut', sortable: true },
];

const facturesnPayees = computed(() => {
    return props.factures.filter(f => f.statut === 'payee').length;
});

const facturesEnRetard = computed(() => {
    return props.factures.filter(f => f.statut === 'en_retard').length;
});

const montantDu = computed(() => {
    return props.factures
        .filter(f => f.statut !== 'payee')
        .reduce((sum, f) => sum + parseFloat(f.montant_ttc - f.montant_regle), 0);
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR',
    }).format(value || 0);
};

const formatDate = (value) => {
    if (!value) return '-';
    return new Date(value).toLocaleDateString('fr-FR');
};

const getStatutClass = (statut) => {
    const classes = {
        'brouillon': 'bg-secondary',
        'envoyee': 'bg-info',
        'payee': 'bg-success',
        'en_retard': 'bg-danger',
        'annulee': 'bg-dark',
    };
    return classes[statut] || 'bg-secondary';
};

const getStatutLabel = (statut) => {
    const labels = {
        'brouillon': 'Brouillon',
        'envoyee': 'Envoyée',
        'payee': 'Payée',
        'en_retard': 'En retard',
        'annulee': 'Annulée',
    };
    return labels[statut] || statut;
};

const route = (name, params = {}) => {
    return window.route(name, params);
};

const refreshData = () => {
    router.reload({ only: ['factures'] });
};

const payerFacture = (facture) => {
    if (confirm(`Voulez-vous payer la facture ${facture.numero_facture} ?`)) {
        // Rediriger vers la page de paiement
        window.location.href = route('client.factures.payer', facture.id);
    }
};
</script>
