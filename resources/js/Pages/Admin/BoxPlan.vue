<template>
    <div class="container-fluid py-4">
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">Plan Interactif des Boxes</h1>
                    <p class="text-muted">Visualisez et gérez tous vos boxes en temps réel</p>
                </div>
                <div>
                    <a :href="route('boxes.index')" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-list me-1"></i>Liste
                    </a>
                    <a :href="route('boxes.create')" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Nouveau Box
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistiques rapides -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-start border-success border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle fa-2x text-success"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-muted small">Boxes Libres</div>
                                <div class="h4 mb-0">{{ stats.libres }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-start border-danger border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-times-circle fa-2x text-danger"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-muted small">Boxes Occupés</div>
                                <div class="h4 mb-0">{{ stats.occupes }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-start border-primary border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-percentage fa-2x text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-muted small">Taux Occupation</div>
                                <div class="h4 mb-0">{{ tauxOccupation }}%</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-start border-info border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-box fa-2x text-info"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-muted small">Total Boxes</div>
                                <div class="h4 mb-0">{{ stats.total }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Plan interactif -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-map me-2"></i>Plan Interactif
                </h5>
            </div>
            <div class="card-body">
                <BoxPlanGrid
                    :boxes="boxes"
                    :emplacements="emplacements"
                    :can-create-contract="true"
                    :can-view-contract="true"
                    :is-admin="true"
                />
            </div>
        </div>

        <!-- Instructions -->
        <div class="alert alert-info mt-4">
            <h6 class="alert-heading">
                <i class="fas fa-info-circle me-2"></i>Comment utiliser le plan
            </h6>
            <ul class="mb-0 small">
                <li><strong>Vert :</strong> Box libre - Cliquez pour créer un nouveau contrat</li>
                <li><strong>Rouge :</strong> Box occupé - Cliquez pour voir les détails du contrat</li>
                <li><strong>Jaune :</strong> Box réservé - En attente de finalisation</li>
                <li><strong>Gris :</strong> Box en maintenance - Indisponible temporairement</li>
                <li><strong>Survol :</strong> Passez la souris sur un box pour voir un aperçu rapide</li>
            </ul>
        </div>
    </div>
</template>

<script>
import BoxPlanGrid from '@/Components/BoxPlanGrid.vue';

export default {
    components: {
        BoxPlanGrid
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
        stats: {
            type: Object,
            required: true
        }
    },

    computed: {
        tauxOccupation() {
            if (this.stats.total === 0) return 0;
            return Math.round((this.stats.occupes / this.stats.total) * 100);
        }
    }
};
</script>
