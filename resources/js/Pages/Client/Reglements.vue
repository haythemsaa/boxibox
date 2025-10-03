<template>
    <ClientLayout title="Mes Règlements">
        <div class="mb-4">
            <h1 class="h3">Mes Règlements</h1>
            <p class="text-muted">Historique de vos paiements</p>
        </div>

        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card border-start border-success border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-receipt fa-2x text-success"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-muted small">Total Règlements</div>
                                <div class="h4 mb-0">{{ stats.total_reglements }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-start border-primary border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-euro-sign fa-2x text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-muted small">Montant Total</div>
                                <div class="h4 mb-0">{{ formatAmount(stats.montant_total) }} €</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-start border-info border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-calendar-month fa-2x text-info"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-muted small">Ce Mois</div>
                                <div class="h4 mb-0">{{ stats.reglements_mois || 0 }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-start border-warning border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-chart-line fa-2x text-warning"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-muted small">Moyenne</div>
                                <div class="h4 mb-0">{{ formatAmount(stats.montant_moyen || 0) }} €</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="card mb-4">
            <div class="card-body">
                <form @submit.prevent="applyFilters" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Mode de paiement</label>
                        <select v-model="filters.mode_paiement" class="form-select">
                            <option value="">Tous les modes</option>
                            <option value="virement">Virement</option>
                            <option value="cheque">Chèque</option>
                            <option value="carte">Carte Bancaire</option>
                            <option value="prelevement">Prélèvement SEPA</option>
                            <option value="especes">Espèces</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Date de début</label>
                        <input type="date" v-model="filters.date_debut" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Date de fin</label>
                        <input type="date" v-model="filters.date_fin" class="form-control">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i>Filtrer
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Liste des règlements -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Historique des Règlements</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date Règlement</th>
                                <th>Facture Concernée</th>
                                <th>Mode de Paiement</th>
                                <th>Référence/N° Transaction</th>
                                <th class="text-end">Montant Payé</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="reglement in reglements.data" :key="reglement.id">
                                <td>
                                    <strong>{{ formatDate(reglement.date_reglement) }}</strong>
                                    <br>
                                    <small class="text-muted">{{ timeAgo(reglement.date_reglement) }}</small>
                                </td>
                                <td>
                                    <a
                                        v-if="reglement.facture"
                                        :href="route('client.factures.show', reglement.facture.id)"
                                        class="text-decoration-none"
                                    >
                                        {{ reglement.facture.numero_facture }}
                                    </a>
                                    <span v-else class="text-muted">N/A</span>
                                    <br v-if="reglement.facture">
                                    <small v-if="reglement.facture" class="text-muted">
                                        {{ formatDate(reglement.facture.date_emission) }}
                                    </small>
                                </td>
                                <td>
                                    <i :class="getPaymentIcon(reglement.mode_paiement)" class="me-1"></i>
                                    <span>{{ getPaymentLabel(reglement.mode_paiement) }}</span>
                                </td>
                                <td>
                                    <code v-if="reglement.reference" class="text-dark">{{ reglement.reference }}</code>
                                    <span v-else class="text-muted">-</span>
                                    <br v-if="reglement.notes">
                                    <small v-if="reglement.notes" class="text-muted">
                                        {{ truncate(reglement.notes, 30) }}
                                    </small>
                                </td>
                                <td class="text-end">
                                    <strong class="text-success">{{ formatAmount(reglement.montant) }} €</strong>
                                </td>
                                <td>
                                    <span :class="getStatusBadge(reglement.statut)">
                                        <i :class="getStatusIcon(reglement.statut)" class="me-1"></i>
                                        {{ getStatusLabel(reglement.statut) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-outline-primary" title="Reçu">
                                        <i class="fas fa-file-invoice"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr v-if="!reglements.data || reglements.data.length === 0">
                                <td colspan="7" class="text-center py-5">
                                    <i class="fas fa-receipt fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted mb-1">Aucun règlement trouvé</p>
                                    <small class="text-muted">Vos paiements apparaîtront ici</small>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="reglements.last_page > 1" class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Affichage de {{ reglements.from }} à {{ reglements.to }} sur {{ reglements.total }} règlements
                    </div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item" :class="{ disabled: !reglements.prev_page_url }">
                                <a class="page-link" @click.prevent="changePage(reglements.current_page - 1)">
                                    Précédent
                                </a>
                            </li>
                            <li class="page-item" :class="{ disabled: !reglements.next_page_url }">
                                <a class="page-link" @click.prevent="changePage(reglements.current_page + 1)">
                                    Suivant
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Informations -->
        <div class="alert alert-info mt-4">
            <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Modes de paiement acceptés</h6>
            <p class="mb-2 small">
                Nous acceptons les modes de paiement suivants :
            </p>
            <div class="row small">
                <div class="col-md-4 mb-1">
                    <i class="fas fa-university text-primary me-1"></i> Prélèvement automatique SEPA
                </div>
                <div class="col-md-4 mb-1">
                    <i class="fas fa-credit-card text-info me-1"></i> Carte bancaire
                </div>
                <div class="col-md-4 mb-1">
                    <i class="fas fa-exchange-alt text-success me-1"></i> Virement bancaire
                </div>
            </div>
            <p class="mb-0 small mt-2">
                Pour activer le prélèvement automatique,
                <a :href="route('client.sepa')" class="alert-link">créez un mandat SEPA</a>.
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
        reglements: {
            type: Object,
            required: true
        },
        stats: {
            type: Object,
            required: true
        },
        queryParams: {
            type: Object,
            default: () => ({})
        }
    },

    data() {
        return {
            filters: {
                mode_paiement: this.queryParams.mode_paiement || '',
                date_debut: this.queryParams.date_debut || '',
                date_fin: this.queryParams.date_fin || ''
            }
        };
    },

    methods: {
        applyFilters() {
            router.get(route('client.reglements'), this.filters, {
                preserveState: true,
                preserveScroll: true
            });
        },

        changePage(page) {
            if (page < 1 || page > this.reglements.last_page) return;

            router.get(route('client.reglements'), {
                ...this.filters,
                page
            }, {
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

        getPaymentIcon(mode) {
            const icons = {
                virement: 'fas fa-exchange-alt text-primary',
                cheque: 'fas fa-money-check text-success',
                especes: 'fas fa-money-bill-wave text-warning',
                carte: 'fas fa-credit-card text-info',
                prelevement: 'fas fa-university text-secondary'
            };
            return icons[mode] || 'fas fa-question';
        },

        getPaymentLabel(mode) {
            const labels = {
                virement: 'Virement',
                cheque: 'Chèque',
                especes: 'Espèces',
                carte: 'Carte Bancaire',
                prelevement: 'Prélèvement SEPA'
            };
            return labels[mode] || mode.charAt(0).toUpperCase() + mode.slice(1);
        },

        getStatusBadge(statut) {
            const badges = {
                valide: 'badge bg-success',
                en_attente: 'badge bg-warning',
                refuse: 'badge bg-danger'
            };
            return badges[statut] || 'badge bg-secondary';
        },

        getStatusIcon(statut) {
            const icons = {
                valide: 'fas fa-check-circle',
                en_attente: 'fas fa-clock',
                refuse: 'fas fa-times-circle'
            };
            return icons[statut] || 'fas fa-question';
        },

        getStatusLabel(statut) {
            const labels = {
                valide: 'Validé',
                en_attente: 'En attente',
                refuse: 'Refusé'
            };
            return labels[statut] || statut.charAt(0).toUpperCase() + statut.slice(1);
        },

        truncate(str, length) {
            if (!str) return '';
            return str.length > length ? str.substring(0, length) + '...' : str;
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
