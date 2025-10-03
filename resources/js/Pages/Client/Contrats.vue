<template>
    <ClientLayout title="Mes Contrats">
        <div class="mb-4">
            <h1 class="h3">Mes Contrats</h1>
            <p class="text-muted">Consultez l'ensemble de vos contrats de location</p>
        </div>

        <!-- Filtres et recherche -->
        <div class="card mb-4">
            <div class="card-body">
                <form @submit.prevent="applyFilters" class="row g-3">
                    <div class="col-md-4">
                        <input
                            type="text"
                            v-model="filters.search"
                            class="form-control"
                            placeholder="Rechercher par N° contrat ou box..."
                        >
                    </div>
                    <div class="col-md-3">
                        <select v-model="filters.statut" class="form-select">
                            <option value="">Tous les statuts</option>
                            <option value="actif">Actif</option>
                            <option value="en_cours">En cours</option>
                            <option value="resilie">Résilié</option>
                            <option value="termine">Terminé</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select v-model="filters.sort_by" class="form-select">
                            <option value="created_at">Date de création</option>
                            <option value="date_debut">Date d'entrée</option>
                            <option value="numero_contrat">N° contrat</option>
                            <option value="montant_loyer">Loyer</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i>Rechercher
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Liste des contrats -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>N° Contrat</th>
                                <th>N° Box</th>
                                <th>Type/Famille</th>
                                <th>Date Entrée</th>
                                <th>Date Fin</th>
                                <th class="text-end">Loyer TTC</th>
                                <th class="text-end">Caution</th>
                                <th>État</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="contrat in contrats.data" :key="contrat.id">
                                <td>
                                    <strong class="text-primary">{{ contrat.numero_contrat }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">Box {{ contrat.box.numero }}</span>
                                    <br>
                                    <small class="text-muted">{{ contrat.box.surface }} m²</small>
                                </td>
                                <td>
                                    <span
                                        v-if="contrat.box.famille"
                                        class="badge"
                                        :style="{ backgroundColor: contrat.box.famille.couleur || '#6c757d' }"
                                    >
                                        {{ contrat.box.famille.nom }}
                                    </span>
                                    <span v-else class="text-muted">N/A</span>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-map-marker-alt"></i>
                                        {{ contrat.box.emplacement?.nom || 'N/A' }}
                                    </small>
                                </td>
                                <td>
                                    {{ formatDate(contrat.date_debut) }}
                                    <br>
                                    <small class="text-muted">
                                        {{ timeAgo(contrat.date_debut) }}
                                    </small>
                                </td>
                                <td>
                                    <template v-if="contrat.date_fin">
                                        {{ formatDate(contrat.date_fin) }}
                                        <br>
                                        <small :class="getDaysRemainingClass(contrat.date_fin)">
                                            {{ getDaysRemainingText(contrat.date_fin) }}
                                        </small>
                                    </template>
                                    <span v-else class="text-muted">Indéterminée</span>
                                </td>
                                <td class="text-end">
                                    <strong class="text-primary">{{ formatAmount(contrat.montant_loyer) }} €</strong>
                                    <br>
                                    <small class="text-muted">/mois</small>
                                </td>
                                <td class="text-end">
                                    {{ formatAmount(contrat.depot_garantie) }} €
                                </td>
                                <td>
                                    <span :class="getStatusBadgeClass(contrat.statut)">
                                        <i :class="getStatusIcon(contrat.statut)" class="me-1"></i>
                                        {{ getStatusLabel(contrat.statut) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a
                                            :href="route('client.contrats.show', contrat.id)"
                                            class="btn btn-outline-primary"
                                            title="Voir les détails"
                                        >
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a
                                            :href="route('client.contrats.pdf', contrat.id)"
                                            class="btn btn-outline-secondary"
                                            title="Télécharger PDF"
                                        >
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!contrats.data || contrats.data.length === 0">
                                <td colspan="9" class="text-center py-5">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted mb-0">Aucun contrat trouvé</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="contrats.last_page > 1" class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Affichage de {{ contrats.from }} à {{ contrats.to }} sur {{ contrats.total }} contrats
                    </div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item" :class="{ disabled: !contrats.prev_page_url }">
                                <a class="page-link" @click.prevent="changePage(contrats.current_page - 1)">
                                    Précédent
                                </a>
                            </li>
                            <li
                                v-for="page in paginationPages"
                                :key="page"
                                class="page-item"
                                :class="{ active: page === contrats.current_page }"
                            >
                                <a class="page-link" @click.prevent="changePage(page)">{{ page }}</a>
                            </li>
                            <li class="page-item" :class="{ disabled: !contrats.next_page_url }">
                                <a class="page-link" @click.prevent="changePage(contrats.current_page + 1)">
                                    Suivant
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
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
        contrats: {
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
                search: this.queryParams.search || '',
                statut: this.queryParams.statut || '',
                sort_by: this.queryParams.sort_by || 'created_at',
                sort_order: this.queryParams.sort_order || 'desc'
            }
        };
    },

    computed: {
        paginationPages() {
            const pages = [];
            const current = this.contrats.current_page;
            const last = this.contrats.last_page;

            // Afficher 5 pages max autour de la page courante
            let start = Math.max(1, current - 2);
            let end = Math.min(last, current + 2);

            if (end - start < 4) {
                if (start === 1) {
                    end = Math.min(5, last);
                } else if (end === last) {
                    start = Math.max(1, last - 4);
                }
            }

            for (let i = start; i <= end; i++) {
                pages.push(i);
            }

            return pages;
        }
    },

    methods: {
        applyFilters() {
            router.get(route('client.contrats'), this.filters, {
                preserveState: true,
                preserveScroll: true
            });
        },

        changePage(page) {
            if (page < 1 || page > this.contrats.last_page) return;

            router.get(route('client.contrats'), {
                ...this.filters,
                page
            }, {
                preserveState: true,
                preserveScroll: true
            });
        },

        formatDate(date) {
            if (!date) return '';
            const d = new Date(date);
            return d.toLocaleDateString('fr-FR');
        },

        timeAgo(date) {
            if (!date) return '';
            const d = new Date(date);
            const now = new Date();
            const diffTime = Math.abs(now - d);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            if (diffDays < 30) {
                return `Il y a ${diffDays} jour${diffDays > 1 ? 's' : ''}`;
            } else if (diffDays < 365) {
                const months = Math.floor(diffDays / 30);
                return `Il y a ${months} mois`;
            } else {
                const years = Math.floor(diffDays / 365);
                return `Il y a ${years} an${years > 1 ? 's' : ''}`;
            }
        },

        getDaysRemainingText(dateFin) {
            if (!dateFin) return '';
            const d = new Date(dateFin);
            const now = new Date();
            const diffTime = d - now;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            if (diffDays > 0) {
                return `${diffDays} jour${diffDays > 1 ? 's' : ''}`;
            } else if (diffDays === 0) {
                return "Aujourd'hui";
            } else {
                return 'Expiré';
            }
        },

        getDaysRemainingClass(dateFin) {
            if (!dateFin) return '';
            const d = new Date(dateFin);
            const now = new Date();
            const diffTime = d - now;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            if (diffDays > 0) {
                return 'text-success';
            } else if (diffDays === 0) {
                return 'text-warning';
            } else {
                return 'text-danger';
            }
        },

        formatAmount(amount) {
            if (!amount) return '0.00';
            return parseFloat(amount).toFixed(2);
        },

        getStatusBadgeClass(statut) {
            const classes = {
                'actif': 'badge bg-success',
                'en_cours': 'badge bg-info',
                'resilie': 'badge bg-danger',
                'termine': 'badge bg-secondary'
            };
            return classes[statut] || 'badge bg-warning';
        },

        getStatusIcon(statut) {
            const icons = {
                'actif': 'fas fa-check-circle',
                'en_cours': 'fas fa-clock',
                'resilie': 'fas fa-times-circle',
                'termine': 'fas fa-flag-checkered'
            };
            return icons[statut] || 'fas fa-question';
        },

        getStatusLabel(statut) {
            const labels = {
                'actif': 'Actif',
                'en_cours': 'En cours',
                'resilie': 'Résilié',
                'termine': 'Terminé'
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
    color: #495057;
}

.table tbody tr {
    transition: background-color 0.2s ease;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}
</style>
