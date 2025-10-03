<template>
    <ClientLayout title="Mes Mandats SEPA">
        <div class="mb-4">
            <h1 class="h3">Mes Mandats SEPA</h1>
            <p class="text-muted">Gérez vos moyens de paiement par prélèvement</p>
        </div>

        <div class="card">
            <div class="card-body">
                <div v-if="mandats && mandats.length > 0" class="row">
                    <div v-for="mandat in mandats" :key="mandat.id" class="col-md-6 mb-4">
                        <div class="card border">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <i class="fas fa-university me-2"></i>Mandat SEPA
                                </h6>
                                <span :class="getStatusBadge(mandat.statut)">
                                    {{ getStatusLabel(mandat.statut) }}
                                </span>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless table-sm">
                                    <tbody>
                                        <tr>
                                            <th width="40%">RUM:</th>
                                            <td><code>{{ mandat.rum }}</code></td>
                                        </tr>
                                        <tr>
                                            <th>Titulaire:</th>
                                            <td>{{ mandat.titulaire }}</td>
                                        </tr>
                                        <tr>
                                            <th>IBAN:</th>
                                            <td>
                                                <code>{{ maskIban(mandat.iban) }}</code>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>BIC:</th>
                                            <td><code>{{ mandat.bic }}</code></td>
                                        </tr>
                                        <tr>
                                            <th>Type:</th>
                                            <td>
                                                <span v-if="mandat.type_paiement === 'recurrent'" class="badge bg-primary">
                                                    Récurrent
                                                </span>
                                                <span v-else class="badge bg-info">
                                                    Ponctuel
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Date Signature:</th>
                                            <td>{{ mandat.date_signature ? formatDate(mandat.date_signature) : 'N/A' }}</td>
                                        </tr>
                                        <tr v-if="mandat.date_activation">
                                            <th>Date Activation:</th>
                                            <td>{{ formatDate(mandat.date_activation) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer bg-transparent">
                                <a :href="route('client.sepa.pdf', mandat.id)" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-download me-1"></i>Télécharger PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-5">
                    <i class="fas fa-university fa-4x text-muted mb-4"></i>
                    <h5 class="text-muted">Aucun mandat SEPA</h5>
                    <p class="text-muted mb-4">Vous n'avez pas encore configuré de prélèvement automatique</p>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Pour mettre en place un prélèvement automatique, contactez notre service commercial.
                    </div>
                </div>
            </div>
        </div>

        <div v-if="mandats && mandats.length > 0" class="alert alert-info mt-4">
            <h6><i class="fas fa-info-circle me-2"></i>Information</h6>
            <p class="mb-0">
                Les prélèvements SEPA permettent le paiement automatique de vos factures.
                Pour toute modification ou résiliation de mandat, veuillez contacter notre service commercial.
            </p>
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
        mandats: {
            type: Array,
            required: true
        },
        mandatActif: {
            type: Object,
            default: null
        }
    },

    methods: {
        getStatusBadge(statut) {
            const badges = {
                'actif': 'badge bg-success',
                'valide': 'badge bg-success',
                'suspendu': 'badge bg-warning',
                'annule': 'badge bg-danger',
                'en_attente': 'badge bg-info'
            };
            return badges[statut] || 'badge bg-secondary';
        },

        getStatusLabel(statut) {
            const labels = {
                'actif': 'Actif',
                'valide': 'Valide',
                'suspendu': 'Suspendu',
                'annule': 'Annulé',
                'en_attente': 'En attente'
            };
            return labels[statut] || statut.charAt(0).toUpperCase() + statut.slice(1);
        },

        maskIban(iban) {
            if (!iban || iban.length < 8) return iban;
            return `${iban.substr(0, 4)} **** **** **** ${iban.substr(-4)}`;
        },

        formatDate(date) {
            if (!date) return '';
            return new Date(date).toLocaleDateString('fr-FR');
        }
    }
};
</script>

<style scoped>
.table th {
    color: #6c757d;
    font-weight: 600;
}

.card.border {
    border: 1px solid #dee2e6 !important;
}
</style>
