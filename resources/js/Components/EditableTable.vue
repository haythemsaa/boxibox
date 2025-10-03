<template>
    <div class="editable-table">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th v-for="column in columns" :key="column.key" :class="column.headerClass">
                            {{ column.label }}
                        </th>
                        <th v-if="editable">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, index) in localData" :key="row.id || index">
                        <td v-for="column in columns" :key="column.key" :class="column.cellClass">
                            <!-- Mode édition -->
                            <template v-if="editingRow === row.id">
                                <input
                                    v-if="column.type === 'text' || !column.type"
                                    v-model="editForm[column.key]"
                                    class="form-control form-control-sm"
                                    :type="column.inputType || 'text'"
                                />
                                <input
                                    v-else-if="column.type === 'number'"
                                    v-model="editForm[column.key]"
                                    class="form-control form-control-sm"
                                    type="number"
                                    step="0.01"
                                />
                                <input
                                    v-else-if="column.type === 'date'"
                                    v-model="editForm[column.key]"
                                    class="form-control form-control-sm"
                                    type="date"
                                />
                                <select
                                    v-else-if="column.type === 'select'"
                                    v-model="editForm[column.key]"
                                    class="form-select form-select-sm"
                                >
                                    <option v-for="option in column.options" :key="option.value" :value="option.value">
                                        {{ option.label }}
                                    </option>
                                </select>
                                <span v-else v-html="formatCell(row, column)"></span>
                            </template>

                            <!-- Mode lecture -->
                            <template v-else>
                                <span v-html="formatCell(row, column)"></span>
                            </template>
                        </td>

                        <!-- Actions -->
                        <td v-if="editable">
                            <div v-if="editingRow === row.id" class="btn-group btn-group-sm">
                                <button
                                    @click="saveRow(row)"
                                    class="btn btn-success"
                                    :disabled="saving"
                                >
                                    <i class="fas fa-check"></i>
                                </button>
                                <button
                                    @click="cancelEdit"
                                    class="btn btn-secondary"
                                    :disabled="saving"
                                >
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div v-else class="btn-group btn-group-sm">
                                <button
                                    @click="startEdit(row)"
                                    class="btn btn-outline-primary"
                                >
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button
                                    v-if="deletable"
                                    @click="$emit('delete', row)"
                                    class="btn btn-outline-danger"
                                >
                                    <i class="fas fa-trash"></i>
                                </button>
                                <slot name="row-actions" :row="row"></slot>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!localData || localData.length === 0">
                        <td :colspan="columns.length + (editable ? 1 : 0)" class="text-center text-muted">
                            {{ emptyMessage || 'Aucune donnée disponible' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="pagination" class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted">
                Affichage {{ pagination.from }} à {{ pagination.to }} sur {{ pagination.total }} résultats
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item" :class="{ disabled: !pagination.prev_page_url }">
                        <a class="page-link" href="#" @click.prevent="changePage(pagination.current_page - 1)">
                            Précédent
                        </a>
                    </li>
                    <li class="page-item active">
                        <span class="page-link">{{ pagination.current_page }}</span>
                    </li>
                    <li class="page-item" :class="{ disabled: !pagination.next_page_url }">
                        <a class="page-link" href="#" @click.prevent="changePage(pagination.current_page + 1)">
                            Suivant
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</template>

<script>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

export default {
    props: {
        data: {
            type: Array,
            required: true
        },
        columns: {
            type: Array,
            required: true
        },
        editable: {
            type: Boolean,
            default: false
        },
        deletable: {
            type: Boolean,
            default: false
        },
        updateRoute: {
            type: String,
            default: null
        },
        emptyMessage: {
            type: String,
            default: null
        },
        pagination: {
            type: Object,
            default: null
        }
    },

    emits: ['update', 'delete', 'page-change'],

    setup(props, { emit }) {
        const localData = ref([...props.data]);
        const editingRow = ref(null);
        const editForm = ref({});
        const saving = ref(false);

        watch(() => props.data, (newData) => {
            localData.value = [...newData];
        });

        const formatCell = (row, column) => {
            const value = row[column.key];

            if (column.format) {
                return column.format(value, row);
            }

            if (column.type === 'date' && value) {
                return new Date(value).toLocaleDateString('fr-FR');
            }

            if (column.type === 'number' && value !== null && value !== undefined) {
                return parseFloat(value).toFixed(2);
            }

            if (column.type === 'currency' && value !== null && value !== undefined) {
                return parseFloat(value).toFixed(2) + ' €';
            }

            if (column.type === 'badge' && column.badges) {
                const badge = column.badges[value];
                if (badge) {
                    return `<span class="badge ${badge.class}">${badge.label}</span>`;
                }
            }

            return value || '-';
        };

        const startEdit = (row) => {
            editingRow.value = row.id;
            editForm.value = { ...row };
        };

        const cancelEdit = () => {
            editingRow.value = null;
            editForm.value = {};
        };

        const saveRow = async (row) => {
            saving.value = true;

            if (props.updateRoute) {
                router.put(
                    props.updateRoute.replace(':id', row.id),
                    editForm.value,
                    {
                        preserveScroll: true,
                        onSuccess: () => {
                            editingRow.value = null;
                            editForm.value = {};
                            saving.value = false;
                        },
                        onError: () => {
                            saving.value = false;
                        }
                    }
                );
            } else {
                emit('update', { id: row.id, data: editForm.value });
                editingRow.value = null;
                editForm.value = {};
                saving.value = false;
            }
        };

        const changePage = (page) => {
            if (page >= 1 && page <= props.pagination.last_page) {
                emit('page-change', page);
            }
        };

        return {
            localData,
            editingRow,
            editForm,
            saving,
            formatCell,
            startEdit,
            cancelEdit,
            saveRow,
            changePage
        };
    }
};
</script>

<style scoped>
.editable-table .form-control-sm,
.editable-table .form-select-sm {
    font-size: 0.875rem;
}

.editable-table .btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.editable-table tbody tr:hover {
    background-color: #f8f9fa;
}
</style>
