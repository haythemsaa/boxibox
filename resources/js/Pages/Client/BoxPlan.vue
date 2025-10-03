<template>
    <ClientLayout title="Plan des Boxes">
        <div class="mb-4">
            <h1 class="h3">Plan des Boxes Disponibles</h1>
            <p class="text-muted">Découvrez nos boxes disponibles et leurs emplacements</p>
        </div>

        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-start border-success border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle fa-2x text-success"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-muted small">Boxes Disponibles</div>
                                <div class="h4 mb-0">{{ stats.libres }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-start border-primary border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-box fa-2x text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-muted small">Vos Boxes Actifs</div>
                                <div class="h4 mb-0">{{ mesBoxes }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-start border-info border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-map-marker-alt fa-2x text-info"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-muted small">Emplacements</div>
                                <div class="h4 mb-0">{{ emplacements.length }}</div>
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
                    :can-create-contract="false"
                    :can-view-contract="true"
                    :is-admin="false"
                />
            </div>
        </div>

        <!-- Informations -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="fas fa-info-circle me-2"></i>Légende
                        </h6>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <span class="badge bg-success me-2">Libre</span>
                                Box disponible à la location
                            </li>
                            <li class="mb-2">
                                <span class="badge bg-danger me-2">Occupé</span>
                                Box déjà loué
                            </li>
                            <li class="mb-2">
                                <span class="badge bg-warning me-2">Réservé</span>
                                Box en cours de réservation
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="fas fa-question-circle me-2"></i>Besoin d'aide ?
                        </h6>
                        <p class="mb-2">
                            Vous souhaitez louer un nouveau box ou avez des questions sur nos emplacements ?
                        </p>
                        <p class="mb-0">
                            <i class="fas fa-phone me-2"></i>Contactez-nous au <strong>01 23 45 67 89</strong><br>
                            <i class="fas fa-envelope me-2"></i>Email : <strong>contact@boxibox.com</strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mes boxes actifs -->
        <div v-if="contratsActifs && contratsActifs.length > 0" class="card mt-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-box-open me-2"></i>Mes Boxes Actifs
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>N° Box</th>
                                <th>Emplacement</th>
                                <th>Surface</th>
                                <th>Loyer</th>
                                <th>N° Contrat</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="contrat in contratsActifs" :key="contrat.id">
                                <td>
                                    <strong>{{ contrat.box.numero }}</strong>
                                </td>
                                <td>{{ contrat.box.emplacement?.nom || 'N/A' }}</td>
                                <td>{{ contrat.box.surface }} m²</td>
                                <td>{{ formatCurrency(contrat.montant_loyer) }}/mois</td>
                                <td>{{ contrat.numero_contrat }}</td>
                                <td>
                                    <a
                                        :href="route('client.contrats.show', contrat.id)"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        <i class="fas fa-eye"></i> Voir
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </ClientLayout>
</template>

<script>
import ClientLayout from '@/Layouts/ClientLayout.vue';
import BoxPlanGrid from '@/Components/BoxPlanGrid.vue';

export default {
    components: {
        ClientLayout,
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
        },
        contratsActifs: {
            type: Array,
            default: () => []
        }
    },

    computed: {
        mesBoxes() {
            return this.contratsActifs ? this.contratsActifs.length : 0;
        }
    },

    methods: {
        formatCurrency(amount) {
            return new Intl.NumberFormat('fr-FR', {
                style: 'currency',
                currency: 'EUR'
            }).format(amount || 0);
        }
    }
};
</script>
