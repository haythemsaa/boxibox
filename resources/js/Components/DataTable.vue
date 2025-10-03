<template>
    <div class="datatable-wrapper">
        <!-- Contrôles de recherche et filtres -->
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-search"></i>
                    </span>
                    <input
                        type="text"
                        class="form-control"
                        v-model="searchQuery"
                        :placeholder="searchPlaceholder"
                        @input="onSearch"
                    >
                </div>
            </div>
            <div class="col-md-6 text-end">
                <slot name="actions"></slot>
            </div>
        </div>

        <!-- Tableau -->
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th
                            v-for="column in columns"
                            :key="column.key"
                            @click="column.sortable !== false && sort(column.key)"
                            :class="{ 'sortable': column.sortable !== false }"
                        >
                            {{ column.label }}
                            <i
                                v-if="column.sortable !== false && sortKey === column.key"
                                class="fas ms-1"
                                :class="sortOrder === 'asc' ? 'fa-sort-up' : 'fa-sort-down'"
                            ></i>
                            <i
                                v-else-if="column.sortable !== false"
                                class="fas fa-sort ms-1 text-muted"
                            ></i>
                        </th>
                        <th v-if="hasActions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in paginatedData" :key="item[itemKey]">
                        <td v-for="column in columns" :key="column.key">
                            <slot
                                :name="`cell-${column.key}`"
                                :item="item"
                                :value="getNestedValue(item, column.key)"
                            >
                                {{ formatCell(item, column) }}
                            </slot>
                        </td>
                        <td v-if="hasActions">
                            <slot name="actions" :item="item"></slot>
                        </td>
                    </tr>
                    <tr v-if="paginatedData.length === 0">
                        <td :colspan="columns.length + (hasActions ? 1 : 0)" class="text-center text-muted">
                            {{ emptyMessage }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="row align-items-center mt-3" v-if="filteredData.length > perPage">
            <div class="col-md-6">
                <div class="text-muted">
                    Affichage de {{ startItem }} à {{ endItem }} sur {{ filteredData.length }} résultats
                </div>
            </div>
            <div class="col-md-6">
                <nav>
                    <ul class="pagination justify-content-end mb-0">
                        <li class="page-item" :class="{ disabled: currentPage === 1 }">
                            <a class="page-link" href="#" @click.prevent="currentPage = 1">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                        </li>
                        <li class="page-item" :class="{ disabled: currentPage === 1 }">
                            <a class="page-link" href="#" @click.prevent="currentPage--">
                                <i class="fas fa-angle-left"></i>
                            </a>
                        </li>
                        <li
                            v-for="page in visiblePages"
                            :key="page"
                            class="page-item"
                            :class="{ active: currentPage === page }"
                        >
                            <a class="page-link" href="#" @click.prevent="currentPage = page">
                                {{ page }}
                            </a>
                        </li>
                        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                            <a class="page-link" href="#" @click.prevent="currentPage++">
                                <i class="fas fa-angle-right"></i>
                            </a>
                        </li>
                        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                            <a class="page-link" href="#" @click.prevent="currentPage = totalPages">
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
    columns: {
        type: Array,
        required: true,
    },
    data: {
        type: Array,
        required: true,
    },
    itemKey: {
        type: String,
        default: 'id',
    },
    perPage: {
        type: Number,
        default: 10,
    },
    searchable: {
        type: Boolean,
        default: true,
    },
    searchPlaceholder: {
        type: String,
        default: 'Rechercher...',
    },
    emptyMessage: {
        type: String,
        default: 'Aucune donnée disponible',
    },
    hasActions: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['search', 'sort']);

const searchQuery = ref('');
const sortKey = ref('');
const sortOrder = ref('asc');
const currentPage = ref(1);

// Données filtrées par recherche
const filteredData = computed(() => {
    if (!searchQuery.value) return props.data;

    const query = searchQuery.value.toLowerCase();
    return props.data.filter(item => {
        return props.columns.some(column => {
            const value = getNestedValue(item, column.key);
            return String(value).toLowerCase().includes(query);
        });
    });
});

// Données triées
const sortedData = computed(() => {
    if (!sortKey.value) return filteredData.value;

    return [...filteredData.value].sort((a, b) => {
        const aVal = getNestedValue(a, sortKey.value);
        const bVal = getNestedValue(b, sortKey.value);

        let comparison = 0;
        if (aVal > bVal) comparison = 1;
        if (aVal < bVal) comparison = -1;

        return sortOrder.value === 'asc' ? comparison : -comparison;
    });
});

// Pagination
const totalPages = computed(() => Math.ceil(filteredData.value.length / props.perPage));

const paginatedData = computed(() => {
    const start = (currentPage.value - 1) * props.perPage;
    const end = start + props.perPage;
    return sortedData.value.slice(start, end);
});

const startItem = computed(() => (currentPage.value - 1) * props.perPage + 1);
const endItem = computed(() => Math.min(currentPage.value * props.perPage, filteredData.value.length));

const visiblePages = computed(() => {
    const pages = [];
    const maxVisible = 5;
    let start = Math.max(1, currentPage.value - Math.floor(maxVisible / 2));
    let end = Math.min(totalPages.value, start + maxVisible - 1);

    if (end - start < maxVisible - 1) {
        start = Math.max(1, end - maxVisible + 1);
    }

    for (let i = start; i <= end; i++) {
        pages.push(i);
    }

    return pages;
});

// Méthodes
const getNestedValue = (obj, path) => {
    return path.split('.').reduce((acc, part) => acc && acc[part], obj);
};

const formatCell = (item, column) => {
    const value = getNestedValue(item, column.key);

    if (column.formatter && typeof column.formatter === 'function') {
        return column.formatter(value, item);
    }

    return value;
};

const sort = (key) => {
    if (sortKey.value === key) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortKey.value = key;
        sortOrder.value = 'asc';
    }
    emit('sort', { key: sortKey.value, order: sortOrder.value });
};

const onSearch = () => {
    currentPage.value = 1;
    emit('search', searchQuery.value);
};

// Réinitialiser la page quand les données changent
watch(() => props.data, () => {
    currentPage.value = 1;
});
</script>

<style scoped>
.sortable {
    cursor: pointer;
    user-select: none;
}

.sortable:hover {
    background-color: #f8f9fa;
}

.page-link {
    cursor: pointer;
}
</style>
