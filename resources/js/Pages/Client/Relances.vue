<template>
    <ClientLayout title="Mes Relances">
        <div class="mb-4">
            <h1 class="h3">Mes Relances</h1>
            <p class="text-muted">Historique des rappels de paiement</p>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date Rappel</th>
                                <th>Facture Concernée</th>
                                <th>Type</th>
                                <th>Mode d'Envoi</th>
                                <th class="text-end">Montant</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="relance in relances.data" :key="relance.id">
                                <td>
                                    <strong>{{ formatDate(relance.date_rappel) }}</strong>
                                    <br>
                                    <small class="text-muted">{{ timeAgo(relance.date_rappel) }}</small>
                                </td>
                                <td>
                                    <a
                                        v-if="relance.facture"
                                        :href="route('client.factures.show', relance.facture.id)"
                                        class="text-decoration-none"
                                    >
                                        {{ relance.facture.numero_facture }}
                                    </a>
                                    <span v-else class="text-muted">N/A</span>
                                    <br v-if="relance.facture">
                                    <small v-if="relance.facture" class="text-muted">
                                        {{ formatDate(relance.facture.date_emission) }}
                                    </small>
                                </td>
                                <td>
                                    <span :class="getNiveauBadgeClass(relance.niveau)">
                                        {{ getNiveauLabel(relance.niveau) }}
                                    </span>
                                </td>
                                <td>
                                    <i :class="getModeIcon(relance.mode_envoi)" class="me-1"></i>
                                    {{ getModeLabel(relance.mode_envoi) }}
                                </td>
                                <td class="text-end">
                                    <strong class="text-danger">
                                        {{ formatAmount(relance.montant_du) }} €
                                    </strong>
                                </td>
                                <td>
                                    <span :class="getStatutBadgeClass(relance.statut)">
                                        <i :class="getStatutIcon(relance.statut)" class="me-1"></i>
                                        {{ getStatutLabel(relance.statut) }}
                                    </span>
                                </td>
                                <td>
                                    <a
                                        v-if="relance.document_path"
                                        href="#"
                                        class="btn btn-sm btn-outline-secondary"
                                        title="Télécharger"
                                    >
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                    <span v-else class="text-muted">-</span>
                                </td>
                            </tr>
                            <tr v-if="!relances.data || relances.data.length === 0">
                                <td colspan="7" class="text-center py-5">
                                    <i class="fas fa-bell fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted mb-1">Aucune relance</p>
                                    <small class="text-muted">Vos paiements sont à jour !</small>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="relances.last_page > 1" class="card-footer bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Affichage de {{ relances.from }} à {{ relances.to }} sur {{ relances.total }} relances
                        </div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item" :class="{ disabled: !relances.prev_page_url }">
                                    <a class="page-link" @click.prevent="changePage(relances.current_page - 1)">
                                        Précédent
                                    </a>
                                </li>
                                <li class="page-item" :class="{ disabled: !relances.next_page_url }">
                                    <a class="page-link" @click.prevent="changePage(relances.current_page + 1)">
                                        Suivant
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informations -->
        <div class="alert alert-info mt-4">
            <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>À propos des relances</h6>
            <p class="mb-0 small">
                Les relances sont envoyées automatiquement pour les factures impayées après l'échéance.
                Pour éviter les relances, pensez à activer le prélèvement automatique via
                <a :href="route('client.sepa')" class="alert-link">les mandats SEPA</a>.
            </p>
        </div>
    </ClientLayout>
</template>

<script>
import ClientLayout from '@/Layouts/ClientLayout.vue';
import { router } from '@inertiajs/vue3';

export default {
    components: {
        ClientLayout
    },

    props: {
        relances: {
            type: Object,
            required: true
        }
    },

    methods: {
        changePage(page) {
            if (page < 1 || page > this.relances.last_page) return;
            router.get(route('client.relances'), { page }, {
                preserveState: true,
                preserveScroll: true
            });
        },

        formatDate(date) {
            if (!date) return '';
            return new Date(date).toLocaleDateString('fr-FR');
        },

        timeAgo(date) {
            if (!date) return '';
            const d = new Date(date);
            const now = new Date();
            const diffTime = Math.abs(now - d);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            if (diffDays < 7) {
                return `Il y a ${diffDays} jour${diffDays > 1 ? 's' : ''}`;
            } else if (diffDays < 30) {
                const weeks = Math.floor(diffDays / 7);
                return `Il y a ${weeks} semaine${weeks > 1 ? 's' : ''}`;
            } else if (diffDays < 365) {
                const months = Math.floor(diffDays / 30);
                return `Il y a ${months} mois`;
            } else {
                const years = Math.floor(diffDays / 365);
                return `Il y a ${years} an${years > 1 ? 's' : ''}`;
            }
        },

        formatAmount(amount) {
            if (!amount) return '0.00';
            return parseFloat(amount).toFixed(2);
        },

        getNiveauBadgeClass(niveau) {
            const classes = {
                1: 'badge bg-info',
                2: 'badge bg-warning',
                3: 'badge bg-danger'
            };
            return classes[niveau] || 'badge bg-secondary';
        },

        getNiveauLabel(niveau) {
            const labels = {
                1: '1ère Relance',
                2: '2ème Relance',
                3: 'Mise en Demeure'
            };
            return labels[niveau] || `Niveau ${niveau}`;
        },

        getModeIcon(mode) {
            const icons = {
                email: 'fas fa-envelope text-primary',
                courrier: 'fas fa-mail-bulk text-info',
                sms: 'fas fa-sms text-success'
            };
            return icons[mode] || 'fas fa-question';
        },

        getModeLabel(mode) {
            const labels = {
                email: 'Email',
                courrier: 'Courrier',
                sms: 'SMS'
            };
            return labels[mode] || mode.charAt(0).toUpperCase() + mode.slice(1);
        },

        getStatutBadgeClass(statut) {
            const classes = {
                envoye: 'badge bg-success',
                en_attente: 'badge bg-warning',
                regle: 'badge bg-primary'
            };
            return classes[statut] || 'badge bg-secondary';
        },

        getStatutIcon(statut) {
            const icons = {
                envoye: 'fas fa-check',
                en_attente: 'fas fa-clock',
                regle: 'fas fa-check-double'
            };
            return icons[statut] || 'fas fa-question';
        },

        getStatutLabel(statut) {
            const labels = {
                envoye: 'Envoyé',
                en_attente: 'En attente',
                regle: 'Réglé'
            };
            return labels[statut] || statut.charAt(0).toUpperCase() + statut.slice(1);
        }
    }
};
</script>

<style scoped>
.table th {
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
</style>
