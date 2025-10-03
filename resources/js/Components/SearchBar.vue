<template>
    <div class="search-bar-wrapper">
        <div class="position-relative">
            <input
                type="text"
                class="form-control"
                :placeholder="placeholder"
                v-model="query"
                @input="onInput"
                @focus="showResults = true"
                @blur="onBlur"
            />
            <i class="fas fa-search position-absolute search-icon"></i>
            <i
                v-if="loading"
                class="fas fa-spinner fa-spin position-absolute search-icon"
            ></i>

            <!-- Dropdown des résultats -->
            <div
                v-if="showResults && (results.length > 0 || query.length >= minChars)"
                class="search-results dropdown-menu show w-100"
            >
                <div v-if="loading" class="text-center p-3">
                    <span class="spinner-border spinner-border-sm me-2"></span>
                    Recherche en cours...
                </div>
                <div v-else-if="results.length === 0 && query.length >= minChars" class="p-3 text-muted">
                    Aucun résultat trouvé
                </div>
                <div v-else>
                    <a
                        v-for="result in results"
                        :key="result[itemKey]"
                        href="#"
                        class="dropdown-item"
                        @mousedown.prevent="selectResult(result)"
                    >
                        <slot name="result" :result="result">
                            {{ result[displayKey] }}
                        </slot>
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    placeholder: {
        type: String,
        default: 'Rechercher...',
    },
    endpoint: {
        type: String,
        required: true,
    },
    itemKey: {
        type: String,
        default: 'id',
    },
    displayKey: {
        type: String,
        default: 'name',
    },
    minChars: {
        type: Number,
        default: 2,
    },
    debounce: {
        type: Number,
        default: 300,
    },
    modelValue: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:modelValue', 'select', 'search']);

const query = ref(props.modelValue);
const results = ref([]);
const loading = ref(false);
const showResults = ref(false);
let debounceTimer = null;

watch(() => props.modelValue, (newValue) => {
    query.value = newValue;
});

const onInput = () => {
    emit('update:modelValue', query.value);

    if (query.value.length < props.minChars) {
        results.value = [];
        return;
    }

    loading.value = true;

    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        performSearch();
    }, props.debounce);
};

const performSearch = async () => {
    try {
        const response = await axios.get(props.endpoint, {
            params: { q: query.value },
        });

        results.value = response.data.data || response.data;
        emit('search', results.value);
    } catch (error) {
        console.error('Erreur de recherche:', error);
        results.value = [];
    } finally {
        loading.value = false;
    }
};

const selectResult = (result) => {
    query.value = result[props.displayKey];
    emit('update:modelValue', query.value);
    emit('select', result);
    showResults.value = false;
};

const onBlur = () => {
    setTimeout(() => {
        showResults.value = false;
    }, 200);
};
</script>

<style scoped>
.search-bar-wrapper {
    position: relative;
}

.search-icon {
    top: 50%;
    right: 12px;
    transform: translateY(-50%);
    pointer-events: none;
    color: #6c757d;
}

.search-results {
    position: absolute;
    top: 100%;
    left: 0;
    z-index: 1000;
    max-height: 300px;
    overflow-y: auto;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.dropdown-item {
    cursor: pointer;
}
</style>
