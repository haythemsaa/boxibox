<template>
    <div class="container-fluid py-4">
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">Éditeur de Plan</h1>
                    <p class="text-muted">Créez et personnalisez le plan de vos emplacements</p>
                </div>
                <div>
                    <a :href="route('boxes.plan')" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Retour au plan
                    </a>
                </div>
            </div>
        </div>

        <!-- Sélection de l'emplacement -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <label class="form-label">Emplacement à éditer</label>
                        <select
                            v-model="selectedEmplacementId"
                            class="form-select"
                            @change="loadEmplacementPlan"
                        >
                            <option
                                v-for="emplacement in emplacements"
                                :key="emplacement.id"
                                :value="emplacement.id"
                            >
                                {{ emplacement.nom }}
                            </option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Instructions:</strong>
                            <ul class="mb-0 mt-2 small">
                                <li>Uploadez une image de fond (plan de la salle)</li>
                                <li>Glissez-déposez les boxes sur le plan</li>
                                <li>Redimensionnez-les en tirant sur les coins</li>
                                <li>Cliquez sur "Enregistrer" pour sauvegarder</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Éditeur de plan -->
        <PlanEditor
            v-if="selectedEmplacementId"
            :boxes="boxes"
            :emplacement-id="selectedEmplacementId"
            :initial-background-image="currentBackgroundImage"
            @save="savePlan"
        />

        <!-- Message de succès -->
        <div v-if="showSuccess" class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            Le plan a été enregistré avec succès !
            <button type="button" class="btn-close" @click="showSuccess = false"></button>
        </div>
    </div>
</template>

<script>
import PlanEditor from '@/Components/PlanEditor.vue';
import { router } from '@inertiajs/vue3';

export default {
    components: {
        PlanEditor
    },

    props: {
        boxes: {
            type: Array,
            required: true
        },
        emplacements: {
            type: Array,
            required: true
        },
        planLayouts: {
            type: Object,
            default: () => ({})
        }
    },

    data() {
        return {
            selectedEmplacementId: this.emplacements[0]?.id || null,
            currentBackgroundImage: null,
            showSuccess: false
        };
    },

    mounted() {
        if (this.selectedEmplacementId) {
            this.loadEmplacementPlan();
        }
    },

    methods: {
        loadEmplacementPlan() {
            const layout = this.planLayouts[this.selectedEmplacementId];
            this.currentBackgroundImage = layout?.background_image || null;
        },

        savePlan(layoutData) {
            router.post(route('boxes.plan.save'), layoutData, {
                onSuccess: () => {
                    this.showSuccess = true;
                    setTimeout(() => {
                        this.showSuccess = false;
                    }, 3000);
                },
                onError: (errors) => {
                    alert('Erreur lors de la sauvegarde: ' + JSON.stringify(errors));
                }
            });
        }
    }
};
</script>
