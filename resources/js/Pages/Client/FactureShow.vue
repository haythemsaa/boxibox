<template>
    <ClientLayout :title="'Facture ' + facture.numero_facture">
        <div class="mb-4">
            <a :href="route('client.factures')" class="btn btn-sm btn-secondary mb-2">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3">Facture {{ facture.numero_facture }}</h1>
                <a :href="route('client.factures.pdf', facture.id)" class="btn btn-primary">
                    <i class="fas fa-download me-2"></i>Télécharger PDF
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Informations facture -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Informations</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <th width="40%">Numéro:</th>
                                    <td><strong>{{ facture.numero_facture }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Date Émission:</th>
                                    <td>{{ formatDate(facture.date_emission) }}</td>
                                </tr>
                                <tr>
                                    <th>Date Échéance:</th>
                                    <td>{{ formatDate(facture.date_echeance) }}</td>
                                </tr>
                                <tr>
                                    <th>Contrat:</th>
                                    <td>
                                        <a v-if="facture.contrat" :href="route('client.contrats.show', facture.contrat.id)">
                                            {{ facture.contrat.numero_contrat }}
                                        </a>
                                        <span v-else>N/A</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Statut:</th>
                                    <td>
                                        <span :class="getStatusBadgeClass(facture.statut)">
                                            {{ getStatusLabel(facture.statut) }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Montants -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-euro-sign me-2"></i>Montants</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <th width="40%">Montant HT:</th>
                                    <td>{{ formatAmount(facture.montant_ht) }} €</td>
                                </tr>
                                <tr>
                                    <th>TVA ({{ facture.taux_tva || 20 }}%):</th>
                                    <td>{{ formatAmount(facture.montant_tva) }} €</td>
                                </tr>
                                <tr class="table-active">
                                    <th><strong>Montant TTC:</strong></th>
                                    <td><strong class="text-primary fs-5">{{ formatAmount(facture.montant_ttc) }} €</strong></td>
                                </tr>
                                <tr v-if="montantRegle > 0">
                                    <th>Montant Réglé:</th>
                                    <td class="text-success">{{ formatAmount(montantRegle) }} €</td>
                                </tr>
                                <tr v-if="montantRegle > 0">
                                    <th>Reste à Payer:</th>
                                    <td class="text-danger">
                                        <strong>{{ formatAmount(resteAPayer) }} €</strong>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lignes de facture -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Détail de la Facture</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Désignation</th>
                                <th class="text-center">Quantité</th>
                                <th class="text-end">Prix Unitaire HT</th>
                                <th class="text-end">Total HT</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="ligne in facture.lignes" :key="ligne.id">
                                <td>{{ ligne.designation }}</td>
                                <td class="text-center">{{ ligne.quantite }}</td>
                                <td class="text-end">{{ formatAmount(ligne.prix_unitaire_ht) }} €</td>
                                <td class="text-end"><strong>{{ formatAmount(ligne.montant_total_ht) }} €</strong></td>
                            </tr>
                            <tr v-if="!facture.lignes || facture.lignes.length === 0">
                                <td colspan="4" class="text-center text-muted">Aucune ligne de facture</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Règlements -->
        <div v-if="facture.reglements && facture.reglements.length > 0" class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-money-bill me-2"></i>Règlements</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Mode</th>
                                <th>Référence</th>
                                <th class="text-end">Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="reglement in facture.reglements" :key="reglement.id">
                                <td>{{ formatDate(reglement.date_reglement) }}</td>
                                <td>
                                    <i :class="getPaymentIcon(reglement.mode_paiement)" class="me-1"></i>
                                    {{ getPaymentLabel(reglement.mode_paiement) }}
                                </td>
                                <td>{{ reglement.reference || 'N/A' }}</td>
                                <td class="text-end"><strong>{{ formatAmount(reglement.montant) }} €</strong></td>
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
        facture: {
            type: Object,
            required: true
        }
    },

    computed: {
        montantRegle() {
            if (!this.facture.reglements) return 0;
            return this.facture.reglements.reduce((sum, r) => sum + parseFloat(r.montant || 0), 0);
        },

        resteAPayer() {
            return parseFloat(this.facture.montant_ttc || 0) - this.montantRegle;
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
                'payee': 'badge bg-success',
                'paye': 'badge bg-success',
                'impayee': 'badge bg-danger',
                'impaye': 'badge bg-danger',
                'en_attente': 'badge bg-warning',
                'en_retard': 'badge bg-danger'
            };
            return classes[statut] || 'badge bg-secondary';
        },

        getStatusLabel(statut) {
            const labels = {
                'payee': 'Payée',
                'paye': 'Payée',
                'impayee': 'Impayée',
                'impaye': 'Impayée',
                'en_attente': 'En attente',
                'en_retard': 'En retard'
            };
            return labels[statut] || statut.charAt(0).toUpperCase() + statut.slice(1);
        },

        getPaymentIcon(mode) {
            const icons = {
                'virement': 'fas fa-exchange-alt',
                'cheque': 'fas fa-money-check',
                'especes': 'fas fa-money-bill-wave',
                'carte': 'fas fa-credit-card',
                'prelevement': 'fas fa-university'
            };
            return icons[mode] || 'fas fa-money-bill';
        },

        getPaymentLabel(mode) {
            const labels = {
                'virement': 'Virement',
                'cheque': 'Chèque',
                'especes': 'Espèces',
                'carte': 'Carte',
                'prelevement': 'Prélèvement'
            };
            return labels[mode] || mode.charAt(0).toUpperCase() + mode.slice(1);
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
