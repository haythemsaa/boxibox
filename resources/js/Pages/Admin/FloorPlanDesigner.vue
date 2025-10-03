<template>
    <div class="container-fluid py-4">
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-pen-fancy me-2"></i>Designer de Plan de Salle
                    </h1>
                    <p class="text-muted">Dessinez votre plan de salle et placez-y vos boxes de différentes tailles</p>
                </div>
                <div>
                    <a :href="route('boxes.plan')" class="btn btn-outline-secondary">
                        <i class="fas fa-eye me-1"></i>Voir le plan
                    </a>
                    <a :href="route('boxes.index')" class="btn btn-outline-primary ms-2">
                        <i class="fas fa-arrow-left me-1"></i>Retour
                    </a>
                </div>
            </div>
        </div>

        <!-- Sélection emplacement -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <label class="form-label mb-1">
                            <i class="fas fa-map-marker-alt me-1"></i>Sélectionner l'emplacement
                        </label>
                        <select
                            v-model="selectedEmplacementId"
                            class="form-select"
                            @change="loadPlan"
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
                        <div class="alert alert-success mb-0">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong><i class="fas fa-lightbulb me-1"></i>Instructions:</strong>
                                    <ol class="mb-0 mt-2 small">
                                        <li><strong>Stylo:</strong> Dessinez le contour à main levée</li>
                                        <li><strong>Polygone:</strong> Cliquez pour placer des points, double-clic pour terminer</li>
                                        <li><strong>Rectangle/Forme L:</strong> Formes prédéfinies rapides</li>
                                    </ol>
                                </div>
                                <div class="col-md-6">
                                    <strong><i class="fas fa-cube me-1"></i>Placer des boxes:</strong>
                                    <ol class="mb-0 mt-2 small" start="4">
                                        <li>Sélectionnez "Placer Box"</li>
                                        <li>Choisissez la taille (Petit, Moyen, Grand, Personnalisé)</li>
                                        <li>Cliquez sur le plan pour placer la box</li>
                                        <li>Redimensionnez avec les poignées bleues</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Designer -->
        <FloorPlanDrawer
            v-if="selectedEmplacementId"
            :key="'plan-' + selectedEmplacementId + '-' + planKey"
            :emplacement-id="selectedEmplacementId"
            :familles="familles"
            :initial-floor-plan="currentFloorPlan"
            :initial-boxes="currentBoxes"
            :initial-echelle="currentEchelle"
            @save="handleSave"
        />

        <!-- Message succès -->
        <div
            v-if="showSuccess"
            class="alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3"
            style="z-index: 9999;"
            role="alert"
        >
            <i class="fas fa-check-circle me-2"></i>
            Plan sauvegardé avec succès !
            <button type="button" class="btn-close" @click="showSuccess = false"></button>
        </div>

        <!-- Légende -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-1"></i>Légende des statuts
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <div style="width: 30px; height: 30px; background: rgba(25, 135, 84, 0.3); border: 2px solid #198754; border-radius: 4px;"></div>
                            <span class="ms-2">Libre</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <div style="width: 30px; height: 30px; background: rgba(220, 53, 69, 0.3); border: 2px solid #dc3545; border-radius: 4px;"></div>
                            <span class="ms-2">Occupé</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <div style="width: 30px; height: 30px; background: rgba(255, 193, 7, 0.3); border: 2px solid #ffc107; border-radius: 4px;"></div>
                            <span class="ms-2">Réservé</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <div style="width: 30px; height: 30px; background: rgba(13, 202, 240, 0.3); border: 2px solid #0dcaf0; border-radius: 4px;"></div>
                            <span class="ms-2">Maintenance</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import FloorPlanDrawer from '@/Components/FloorPlanDrawer.vue';
import { router } from '@inertiajs/vue3';

export default {
    components: {
        FloorPlanDrawer
    },

    props: {
        emplacements: {
            type: Array,
            required: true
        },
        familles: {
            type: Array,
            required: true
        },
        floorPlans: {
            type: Object,
            default: () => ({})
        },
        existingBoxes: {
            type: Object,
            default: () => ({})
        }
    },

    data() {
        return {
            selectedEmplacementId: this.emplacements[0]?.id || null,
            currentFloorPlan: [],
            currentBoxes: [],
            currentEchelle: 0.05,
            showSuccess: false,
            planKey: 0 // Pour forcer le rechargement du composant
        };
    },

    mounted() {
        if (this.selectedEmplacementId) {
            this.loadPlan();
        }
    },

    methods: {
        loadPlan() {
            const planData = this.floorPlans[this.selectedEmplacementId];
            if (planData) {
                this.currentFloorPlan = planData.floor_plan || [];
                this.currentEchelle = planData.echelle || 0.05;
            } else {
                this.currentFloorPlan = [];
                this.currentEchelle = 0.05;
            }

            // Charger les boxes existantes de cet emplacement
            this.currentBoxes = this.existingBoxes[this.selectedEmplacementId] || [];

            // Incrémenter la clé pour forcer le rechargement du composant
            this.planKey++;
        },

        handleSave(data) {
            router.post(route('boxes.floorplan.save'), data, {
                preserveScroll: true,
                onSuccess: (page) => {
                    this.showSuccess = true;

                    // Mettre à jour les données locales avec les nouvelles données du serveur
                    if (page.props.floorPlans) {
                        this.floorPlans = page.props.floorPlans;
                    }
                    if (page.props.existingBoxes) {
                        this.existingBoxes = page.props.existingBoxes;
                    }

                    // Recharger le plan actuel
                    this.loadPlan();

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
