<template>
    <ClientLayout title="Gestion des Factures">
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3">
                    <i class="fas fa-file-invoice me-2"></i>
                    Gestion des Factures
                </h1>
                <a :href="route('admin.factures.create')" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    Nouvelle Facture
                </a>
            </div>
        </div>

        <!-- Stats rapides -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-left-success">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Payées</div>
                        <div class="h5 mb-0">{{ stats.payees || 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-left-warning">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">En Attente</div>
                        <div class="h5 mb-0">{{ stats.en_attente || 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-left-danger">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">En Retard</div>
                        <div class="h5 mb-0">{{ stats.en_retard || 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-left-primary">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Dû</div>
                        <div class="h5 mb-0">{{ formatCurrency(stats.total_du) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="card mb-4">
            <div class="card-body">
                <form @submit.prevent="applyFilters" class="row g-3">
                    <div class="col-md-3">
                        <input
                            type="text"
                            v-model="filters.search"
                            class="form-control"
                            placeholder="N° facture ou client..."
                        />
                    </div>
                    <div class="col-md-2">
                        <select v-model="filters.statut" class="form-select">
                            <option value="">Tous les statuts</option>
                            <option value="payee">Payée</option>
                            <option value="en_attente">En attente</option>
                            <option value="en_retard">En retard</option>
                            <option value="annulee">Annulée</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select v-model="filters.annee" class="form-select">
                            <option value="">Toutes les années</option>
                            <option value="2025">2025</option>
                            <option value="2024">2024</option>
                            <option value="2023">2023</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i>Filtrer
                        </button>
                    </div>
                    <div class="col-md-2">
                        <button type="button" @click="resetFilters" class="btn btn-secondary w-100">
                            <i class="fas fa-redo me-1"></i>Réinitialiser
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tableau Éditable -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Liste des Factures ({{ factures.total || factures.length }})</h5>
            </div>
            <div class="card-body">
                <EditableTable
                    :data="factures.data || factures"
                    :columns="columns"
                    :editable="true"
                    :deletable="false"
                    :pagination="factures.meta || null"
                    :update-route="route('admin.factures.update', ':id')"
                    empty-message="Aucune facture trouvée"
                    @update="handleUpdate"
                    @page-change="handlePageChange"
                >
                    <template #row-actions="{ row }">
                        <a
                            :href="route('admin.factures.show', row.id)"
                            class="btn btn-sm btn-outline-info"
                            title="Voir détails"
                        >
                            <i class="fas fa-eye"></i>
                        </a>
                        <a
                            :href="route('admin.factures.pdf', row.id)"
                            class="btn btn-sm btn-outline-secondary"
                            title="Télécharger PDF"
                            target="_blank"
                        >
                            <i class="fas fa-file-pdf"></i>
                        </a>
                        <button
                            v-if="row.statut !== 'payee'"
                            @click="marquerPayee(row)"
                            class="btn btn-sm btn-outline-success"
                            title="Marquer comme payée"
                        >
                            <i class="fas fa-check-circle"></i>
                        </button>
                        <button
                            @click="envoyerRelance(row)"
                            class="btn btn-sm btn-outline-warning"
                            title="Envoyer relance"
                        >
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </template>
                </EditableTable>
            </div>
        </div>
    </ClientLayout>
</template>

<script>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import EditableTable from '@/Components/EditableTable.vue';

export default {
    components: {
        ClientLayout,
        EditableTable
    },

    props: {
        factures: {
            type: [Object, Array],
            required: true
        },
        stats: {
            type: Object,
            default: () => ({})
        },
        queryParams: {
            type: Object,
            default: () => ({})
        }
    },

    setup(props) {
        const filters = reactive({
            search: props.queryParams.search || '',
            statut: props.queryParams.statut || '',
            annee: props.queryParams.annee || ''
        });

        const columns = [
            {
                key: 'numero_facture',
                label: 'N° Facture',
                type: 'text',
                format: (value) => `<strong>${value}</strong>`
            },
            {
                key: 'client_nom',
                label: 'Client',
                type: 'text',
                format: (value, row) => {
                    return row.client ? `${row.client.prenom} ${row.client.nom}` : '-';
                }
            },
            {
                key: 'contrat_numero',
                label: 'Contrat',
                type: 'text',
                format: (value, row) => {
                    return row.contrat ? row.contrat.numero_contrat : '-';
                }
            },
            {
                key: 'date_emission',
                label: 'Date Émission',
                type: 'date'
            },
            {
                key: 'date_echeance',
                label: 'Date Échéance',
                type: 'date'
            },
            {
                key: 'montant_ttc',
                label: 'Montant TTC',
                type: 'currency',
                cellClass: 'text-end'
            },
            {
                key: 'statut',
                label: 'Statut',
                type: 'select',
                options: [
                    { value: 'payee', label: 'Payée' },
                    { value: 'en_attente', label: 'En attente' },
                    { value: 'en_retard', label: 'En retard' },
                    { value: 'annulee', label: 'Annulée' }
                ],
                badges: {
                    'payee': { class: 'bg-success', label: 'Payée' },
                    'en_attente': { class: 'bg-warning', label: 'En attente' },
                    'en_retard': { class: 'bg-danger', label: 'En retard' },
                    'annulee': { class: 'bg-secondary', label: 'Annulée' }
                }
            }
        ];

        const formatCurrency = (value) => {
            return new Intl.NumberFormat('fr-FR', {
                style: 'currency',
                currency: 'EUR'
            }).format(value || 0);
        };

        const applyFilters = () => {
            router.get(route('admin.factures.index'), filters, {
                preserveState: true,
                preserveScroll: true
            });
        };

        const resetFilters = () => {
            filters.search = '';
            filters.statut = '';
            filters.annee = '';
            applyFilters();
        };

        const handleUpdate = ({ id, data }) => {
            router.put(route('admin.factures.update', id), data, {
                preserveScroll: true
            });
        };

        const handlePageChange = (page) => {
            router.get(route('admin.factures.index'), { ...filters, page }, {
                preserveState: true,
                preserveScroll: true
            });
        };

        const marquerPayee = (facture) => {
            if (confirm(`Marquer la facture ${facture.numero_facture} comme payée ?`)) {
                router.put(route('admin.factures.marquer-payee', facture.id), {}, {
                    preserveScroll: true
                });
            }
        };

        const envoyerRelance = (facture) => {
            if (confirm(`Envoyer une relance pour la facture ${facture.numero_facture} ?`)) {
                router.post(route('admin.factures.relance', facture.id), {}, {
                    preserveScroll: true
                });
            }
        };

        return {
            filters,
            columns,
            formatCurrency,
            applyFilters,
            resetFilters,
            handleUpdate,
            handlePageChange,
            marquerPayee,
            envoyerRelance
        };
    }
};
</script>

<style scoped>
.border-left-primary {
    border-left: 0.25rem solid #0d6efd !important;
}

.border-left-success {
    border-left: 0.25rem solid #198754 !important;
}

.border-left-warning {
    border-left: 0.25rem solid #ffc107 !important;
}

.border-left-danger {
    border-left: 0.25rem solid #dc3545 !important;
}

.text-xs {
    font-size: 0.7rem;
}

.card-header h5 {
    color: #0d6efd;
}
</style>
