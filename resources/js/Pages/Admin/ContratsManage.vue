<template>
    <ClientLayout title="Gestion des Contrats">
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3">
                    <i class="fas fa-file-contract me-2"></i>
                    Gestion des Contrats
                </h1>
                <a :href="route('contrats.create')" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    Nouveau Contrat
                </a>
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
                            placeholder="Rechercher N° contrat ou client..."
                        />
                    </div>
                    <div class="col-md-2">
                        <select v-model="filters.statut" class="form-select">
                            <option value="">Tous les statuts</option>
                            <option value="actif">Actif</option>
                            <option value="resilie">Résilié</option>
                            <option value="termine">Terminé</option>
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
                <h5 class="mb-0">Liste des Contrats ({{ contrats.total || contrats.length }})</h5>
            </div>
            <div class="card-body">
                <EditableTable
                    :data="contrats.data || contrats"
                    :columns="columns"
                    :editable="true"
                    :deletable="true"
                    :pagination="contrats.meta || null"
                    :update-route="route('contrats.update', ':id')"
                    empty-message="Aucun contrat trouvé"
                    @update="handleUpdate"
                    @delete="handleDelete"
                    @page-change="handlePageChange"
                >
                    <template #row-actions="{ row }">
                        <a
                            :href="route('contrats.show', row.id)"
                            class="btn btn-sm btn-outline-info"
                            title="Voir détails"
                        >
                            <i class="fas fa-eye"></i>
                        </a>
                        <a
                            :href="route('contrats.pdf', row.id)"
                            class="btn btn-sm btn-outline-secondary"
                            title="Télécharger PDF"
                            target="_blank"
                        >
                            <i class="fas fa-file-pdf"></i>
                        </a>
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
        contrats: {
            type: [Object, Array],
            required: true
        },
        queryParams: {
            type: Object,
            default: () => ({})
        }
    },

    setup(props) {
        const filters = reactive({
            search: props.queryParams.search || '',
            statut: props.queryParams.statut || ''
        });

        const columns = [
            {
                key: 'numero_contrat',
                label: 'N° Contrat',
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
                key: 'box_numero',
                label: 'Box',
                type: 'text',
                format: (value, row) => {
                    if (row.box) {
                        const familleColor = row.box.famille?.couleur || '#6c757d';
                        const familleNom = row.box.famille?.nom || '';
                        return `Box ${row.box.numero} <span class="badge ms-1" style="background-color: ${familleColor}">${familleNom}</span>`;
                    }
                    return '-';
                }
            },
            {
                key: 'date_debut',
                label: 'Date Début',
                type: 'date'
            },
            {
                key: 'date_fin',
                label: 'Date Fin',
                type: 'date'
            },
            {
                key: 'montant_loyer',
                label: 'Loyer Mensuel',
                type: 'currency',
                cellClass: 'text-end'
            },
            {
                key: 'statut',
                label: 'Statut',
                type: 'select',
                options: [
                    { value: 'actif', label: 'Actif' },
                    { value: 'resilie', label: 'Résilié' },
                    { value: 'termine', label: 'Terminé' }
                ],
                badges: {
                    'actif': { class: 'bg-success', label: 'Actif' },
                    'resilie': { class: 'bg-danger', label: 'Résilié' },
                    'termine': { class: 'bg-secondary', label: 'Terminé' }
                }
            }
        ];

        const applyFilters = () => {
            router.get(route('contrats.index'), filters, {
                preserveState: true,
                preserveScroll: true
            });
        };

        const resetFilters = () => {
            filters.search = '';
            filters.statut = '';
            applyFilters();
        };

        const handleUpdate = ({ id, data }) => {
            router.put(route('contrats.update', id), data, {
                preserveScroll: true,
                onSuccess: () => {
                    // Success message handled by flash
                }
            });
        };

        const handleDelete = (row) => {
            if (confirm(`Êtes-vous sûr de vouloir supprimer le contrat ${row.numero_contrat} ?`)) {
                router.delete(route('contrats.destroy', row.id), {
                    preserveScroll: true
                });
            }
        };

        const handlePageChange = (page) => {
            router.get(route('contrats.index'), { ...filters, page }, {
                preserveState: true,
                preserveScroll: true
            });
        };

        return {
            filters,
            columns,
            applyFilters,
            resetFilters,
            handleUpdate,
            handleDelete,
            handlePageChange
        };
    }
};
</script>

<style scoped>
.card-header h5 {
    color: #0d6efd;
}
</style>
