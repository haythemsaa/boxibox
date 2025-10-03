<template>
    <ClientLayout :title="'Contrat ' + contrat.numero_contrat">
        <div class="mb-4">
            <a :href="route('client.contrats')" class="btn btn-sm btn-secondary mb-2">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3">Contrat {{ contrat.numero_contrat }}</h1>
                <a :href="route('client.contrats.pdf', contrat.id)" class="btn btn-primary">
                    <i class="fas fa-download me-2"></i>Télécharger PDF
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Informations du contrat -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-file-contract me-2"></i>Informations du Contrat</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <th width="40%">Numéro:</th>
                                    <td><strong>{{ contrat.numero_contrat }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Date Début:</th>
                                    <td>{{ formatDate(contrat.date_debut) }}</td>
                                </tr>
                                <tr>
                                    <th>Date Fin:</th>
                                    <td>{{ contrat.date_fin ? formatDate(contrat.date_fin) : 'Indéterminée' }}</td>
                                </tr>
                                <tr>
                                    <th>Durée:</th>
                                    <td>{{ contrat.duree_mois || 'N/A' }} mois</td>
                                </tr>
                                <tr>
                                    <th>Loyer Mensuel:</th>
                                    <td><strong class="text-primary">{{ formatAmount(contrat.montant_loyer) }} €</strong></td>
                                </tr>
                                <tr>
                                    <th>Dépôt de Garantie:</th>
                                    <td>{{ formatAmount(contrat.depot_garantie || 0) }} €</td>
                                </tr>
                                <tr>
                                    <th>Statut:</th>
                                    <td>
                                        <span :class="getStatusBadgeClass(contrat.statut)">
                                            {{ getStatusLabel(contrat.statut) }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Informations du Box -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-box me-2"></i>Informations du Box</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <th width="40%">Numéro:</th>
                                    <td><strong>{{ contrat.box.numero }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Famille:</th>
                                    <td>
                                        <span
                                            v-if="contrat.box.famille"
                                            class="badge"
                                            :style="{ backgroundColor: contrat.box.famille.couleur || '#6c757d' }"
                                        >
                                            {{ contrat.box.famille.nom }}
                                        </span>
                                        <span v-else>N/A</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Emplacement:</th>
                                    <td>{{ contrat.box.emplacement?.nom || 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Surface:</th>
                                    <td>{{ contrat.box.surface }} m²</td>
                                </tr>
                                <tr>
                                    <th>Volume:</th>
                                    <td>{{ contrat.box.volume || 'N/A' }} m³</td>
                                </tr>
                                <tr>
                                    <th>Statut:</th>
                                    <td>
                                        <span :class="getBoxStatusBadge(contrat.box.statut)">
                                            {{ getBoxStatusLabel(contrat.box.statut) }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Factures liées -->
        <div v-if="contrat.factures && contrat.factures.length > 0" class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>Factures Liées</h5>
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
                            <tr v-for="facture in contrat.factures" :key="facture.id">
                                <td><strong>{{ facture.numero_facture }}</strong></td>
                                <td>{{ formatDate(facture.date_emission) }}</td>
                                <td>{{ formatDate(facture.date_echeance) }}</td>
                                <td class="text-end"><strong>{{ formatAmount(facture.montant_ttc) }} €</strong></td>
                                <td>
                                    <span :class="getFactureStatusBadge(facture.statut)">
                                        {{ getFactureStatusLabel(facture.statut) }}
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
    </ClientLayout>
</template>

<script>
import ClientLayout from '@/Layouts/ClientLayout.vue';

export default {
    components: {
        ClientLayout
    },

    props: {
        contrat: {
            type: Object,
            required: true
        }
    },

    methods: {
        formatDate(date) {
            if (!date) return '';
            return new Date(date).toLocaleDateString('fr-FR');
        },

        formatAmount(amount) {
            if (!amount) return '0.00';
            return parseFloat(amount).toFixed(2);
        },

        getStatusBadgeClass(statut) {
            const classes = {
                'actif': 'badge bg-success',
                'resilie': 'badge bg-danger',
                'en_cours': 'badge bg-info',
                'termine': 'badge bg-secondary'
            };
            return classes[statut] || 'badge bg-warning';
        },

        getStatusLabel(statut) {
            const labels = {
                'actif': 'Actif',
                'resilie': 'Résilié',
                'en_cours': 'En cours',
                'termine': 'Terminé'
            };
            return labels[statut] || statut.charAt(0).toUpperCase() + statut.slice(1);
        },

        getBoxStatusBadge(statut) {
            const classes = {
                'libre': 'badge bg-success',
                'occupe': 'badge bg-danger',
                'reserve': 'badge bg-warning',
                'maintenance': 'badge bg-info'
            };
            return classes[statut] || 'badge bg-secondary';
        },

        getBoxStatusLabel(statut) {
            const labels = {
                'libre': 'Libre',
                'occupe': 'Occupé',
                'reserve': 'Réservé',
                'maintenance': 'Maintenance'
            };
            return labels[statut] || statut.charAt(0).toUpperCase() + statut.slice(1);
        },

        getFactureStatusBadge(statut) {
            const classes = {
                'payee': 'badge bg-success',
                'paye': 'badge bg-success',
                'impayee': 'badge bg-danger',
                'en_attente': 'badge bg-warning'
            };
            return classes[statut] || 'badge bg-secondary';
        },

        getFactureStatusLabel(statut) {
            const labels = {
                'payee': 'Payée',
                'paye': 'Payée',
                'impayee': 'Impayée',
                'en_attente': 'En attente'
            };
            return labels[statut] || statut;
        }
    }
};
</script>

<style scoped>
.table th {
    background-color: #f8f9fa;
    font-weight: 600;
}
</style>
