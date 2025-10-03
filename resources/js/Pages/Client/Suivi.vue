<template>
    <ClientLayout title="Suivi d'Activité">
        <div class="mb-4">
            <h1 class="h3">Suivi d'Activité</h1>
            <p class="text-muted">Chronologie de tous vos événements</p>
        </div>

        <!-- Filtres -->
        <div class="card mb-4">
            <div class="card-body">
                <form @submit.prevent="applyFilters" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Type d'événement</label>
                        <select v-model="filters.type" class="form-select">
                            <option value="">Tous les types</option>
                            <option value="contrat">Contrats</option>
                            <option value="facture">Factures</option>
                            <option value="reglement">Règlements</option>
                            <option value="relance">Relances</option>
                            <option value="document">Documents</option>
                            <option value="sepa">SEPA</option>
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

        <!-- Timeline des événements -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Timeline</h5>
            </div>
            <div class="card-body">
                <div v-if="evenements.data && evenements.data.length > 0" class="timeline">
                    <div
                        v-for="(event, index) in evenements.data"
                        :key="index"
                        class="timeline-item mb-4"
                    >
                        <div class="timeline-marker">
                            <span :class="`badge ${event.badge_class}`">
                                <i :class="`fas ${event.icon}`"></i>
                            </span>
                        </div>
                        <div class="timeline-content">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ event.titre }}</h6>
                                            <p class="text-muted mb-2">{{ event.description }}</p>
                                            <div class="row small text-muted">
                                                <div
                                                    v-for="(value, key) in event.details"
                                                    :key="key"
                                                    class="col-auto"
                                                >
                                                    <strong>{{ key }}:</strong> {{ value }}
                                                </div>
                                            </div>
                                            <div v-if="event.actions && event.actions.length > 0" class="mt-2">
                                                <a
                                                    v-for="(action, idx) in event.actions"
                                                    :key="idx"
                                                    :href="action.route"
                                                    class="btn btn-sm btn-outline-primary me-2"
                                                >
                                                    {{ action.label }}
                                                </a>
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0 ms-3 text-end">
                                            <small class="text-muted">
                                                {{ formatDate(event.date) }}
                                                <br>
                                                {{ formatTime(event.date) }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-5">
                    <i class="fas fa-history fa-3x text-muted mb-3 d-block"></i>
                    <p class="text-muted mb-1">Aucun événement trouvé</p>
                    <small class="text-muted">Modifiez vos filtres ou attendez de nouveaux événements</small>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="evenements.last_page > 1" class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Affichage de {{ evenements.from }} à {{ evenements.to }} sur {{ evenements.total }} événements
                    </div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item" :class="{ disabled: !evenements.prev_page_url }">
                                <a class="page-link" @click.prevent="changePage(evenements.current_page - 1)">
                                    Précédent
                                </a>
                            </li>
                            <li class="page-item" :class="{ disabled: !evenements.next_page_url }">
                                <a class="page-link" @click.prevent="changePage(evenements.current_page + 1)">
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
        evenements: {
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
                type: this.queryParams.type || '',
                date_debut: this.queryParams.date_debut || '',
                date_fin: this.queryParams.date_fin || ''
            }
        };
    },

    methods: {
        applyFilters() {
            router.get(route('client.suivi'), this.filters, {
                preserveState: true,
                preserveScroll: true
            });
        },

        changePage(page) {
            if (page < 1 || page > this.evenements.last_page) return;
            router.get(route('client.suivi'), {
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

        formatTime(date) {
            if (!date) return '';
            return new Date(date).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
        }
    }
};
</script>

<style scoped>
.timeline {
    position: relative;
    padding-left: 40px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
}

.timeline-marker {
    position: absolute;
    left: -40px;
    top: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    border-radius: 50%;
}

.timeline-marker .badge {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.timeline-content {
    position: relative;
}

.timeline-content .card {
    border-left: 3px solid #0d6efd;
}
</style>
