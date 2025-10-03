<template>
    <div class="box-plan-container">
        <!-- Légende -->
        <div class="legend mb-4">
            <div class="d-flex flex-wrap gap-3 justify-content-center">
                <div class="legend-item">
                    <span class="legend-box bg-success"></span>
                    <span>Libre ({{ stats.libres }})</span>
                </div>
                <div class="legend-item">
                    <span class="legend-box bg-danger"></span>
                    <span>Occupé ({{ stats.occupes }})</span>
                </div>
                <div class="legend-item">
                    <span class="legend-box bg-warning"></span>
                    <span>Réservé ({{ stats.reserves }})</span>
                </div>
                <div class="legend-item">
                    <span class="legend-box bg-secondary"></span>
                    <span>Maintenance ({{ stats.maintenance }})</span>
                </div>
            </div>
        </div>

        <!-- Sélection de l'emplacement -->
        <div v-if="emplacements.length > 1" class="mb-4">
            <div class="btn-group w-100" role="group">
                <button
                    v-for="emplacement in emplacements"
                    :key="emplacement.id"
                    type="button"
                    class="btn"
                    :class="selectedEmplacement?.id === emplacement.id ? 'btn-primary' : 'btn-outline-primary'"
                    @click="selectEmplacement(emplacement)"
                >
                    {{ emplacement.nom }}
                    <span class="badge bg-light text-dark ms-2">
                        {{ getEmplacementBoxCount(emplacement.id) }}
                    </span>
                </button>
            </div>
        </div>

        <!-- Grille du plan -->
        <div class="plan-grid-wrapper" ref="planWrapper">
            <div
                class="plan-grid"
                :style="{
                    gridTemplateColumns: `repeat(${gridCols}, 1fr)`,
                    gridTemplateRows: `repeat(${gridRows}, 1fr)`
                }"
            >
                <div
                    v-for="box in filteredBoxes"
                    :key="box.id"
                    class="box-cell"
                    :class="getBoxClass(box)"
                    :style="getBoxStyle(box)"
                    @click="handleBoxClick(box)"
                    @mouseenter="showTooltip(box, $event)"
                    @mouseleave="hideTooltip"
                >
                    <div class="box-number">{{ box.numero }}</div>
                    <div class="box-surface">{{ box.surface }}m²</div>
                </div>
            </div>
        </div>

        <!-- Tooltip -->
        <div
            v-if="tooltip.show"
            class="box-tooltip"
            :style="{
                top: tooltip.y + 'px',
                left: tooltip.x + 'px'
            }"
        >
            <div class="tooltip-header" :class="getBoxClass(tooltip.box)">
                <strong>Box {{ tooltip.box.numero }}</strong>
            </div>
            <div class="tooltip-body">
                <div><strong>Surface:</strong> {{ tooltip.box.surface }} m²</div>
                <div v-if="tooltip.box.volume"><strong>Volume:</strong> {{ tooltip.box.volume }} m³</div>
                <div><strong>Prix:</strong> {{ formatCurrency(tooltip.box.prix_mensuel) }}/mois</div>
                <div><strong>Famille:</strong> {{ tooltip.box.famille?.nom || 'N/A' }}</div>
                <div><strong>Statut:</strong> {{ getStatutLabel(tooltip.box.statut) }}</div>

                <template v-if="tooltip.box.statut === 'occupe' && tooltip.box.contrat_actif">
                    <hr class="my-2">
                    <div class="text-danger">
                        <strong>Occupé par:</strong><br>
                        {{ tooltip.box.contrat_actif.client?.prenom }} {{ tooltip.box.contrat_actif.client?.nom }}
                    </div>
                    <div class="small">
                        Contrat: {{ tooltip.box.contrat_actif.numero_contrat }}<br>
                        Depuis: {{ formatDate(tooltip.box.contrat_actif.date_debut) }}
                    </div>
                </template>

                <template v-else-if="tooltip.box.statut === 'libre' && canCreateContract">
                    <hr class="my-2">
                    <div class="text-success small">
                        <i class="fas fa-hand-pointer me-1"></i>
                        Cliquez pour créer un contrat
                    </div>
                </template>
            </div>
        </div>

        <!-- Modal pour box occupé -->
        <div v-if="selectedBox && selectedBox.statut === 'occupe'" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5)">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-box me-2"></i>Box {{ selectedBox.numero }} - Occupé
                        </h5>
                        <button type="button" class="btn-close btn-close-white" @click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <div v-if="selectedBox.contrat_actif">
                            <h6>Informations du contrat</h6>
                            <table class="table table-borderless table-sm">
                                <tbody>
                                    <tr>
                                        <th>N° Contrat:</th>
                                        <td>{{ selectedBox.contrat_actif.numero_contrat }}</td>
                                    </tr>
                                    <tr>
                                        <th>Client:</th>
                                        <td>
                                            {{ selectedBox.contrat_actif.client?.prenom }}
                                            {{ selectedBox.contrat_actif.client?.nom }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Email:</th>
                                        <td>{{ selectedBox.contrat_actif.client?.email }}</td>
                                    </tr>
                                    <tr>
                                        <th>Téléphone:</th>
                                        <td>{{ selectedBox.contrat_actif.client?.telephone || 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Date début:</th>
                                        <td>{{ formatDate(selectedBox.contrat_actif.date_debut) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Date fin:</th>
                                        <td>{{ selectedBox.contrat_actif.date_fin ? formatDate(selectedBox.contrat_actif.date_fin) : 'Indéterminée' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Loyer:</th>
                                        <td>{{ formatCurrency(selectedBox.contrat_actif.montant_loyer) }}/mois</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="alert alert-warning">
                            Aucune information de contrat disponible
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeModal">Fermer</button>
                        <a
                            v-if="selectedBox.contrat_actif && canViewContract"
                            :href="getContractUrl(selectedBox.contrat_actif.id)"
                            class="btn btn-primary"
                        >
                            <i class="fas fa-eye me-1"></i>Voir le contrat
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal pour nouveau contrat (box libre) -->
        <div v-if="selectedBox && selectedBox.statut === 'libre' && canCreateContract" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5)">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-box me-2"></i>Box {{ selectedBox.numero }} - Disponible
                        </h5>
                        <button type="button" class="btn-close btn-close-white" @click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            Ce box est disponible à la location
                        </div>
                        <table class="table table-borderless table-sm">
                            <tbody>
                                <tr>
                                    <th>Surface:</th>
                                    <td>{{ selectedBox.surface }} m²</td>
                                </tr>
                                <tr v-if="selectedBox.volume">
                                    <th>Volume:</th>
                                    <td>{{ selectedBox.volume }} m³</td>
                                </tr>
                                <tr>
                                    <th>Prix mensuel:</th>
                                    <td><strong class="text-success">{{ formatCurrency(selectedBox.prix_mensuel) }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Famille:</th>
                                    <td>
                                        <span
                                            v-if="selectedBox.famille"
                                            class="badge"
                                            :style="{ backgroundColor: selectedBox.famille.couleur }"
                                        >
                                            {{ selectedBox.famille.nom }}
                                        </span>
                                        <span v-else>N/A</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Emplacement:</th>
                                    <td>{{ selectedBox.emplacement?.nom || 'N/A' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeModal">Fermer</button>
                        <a
                            :href="getNewContractUrl(selectedBox.id)"
                            class="btn btn-success"
                        >
                            <i class="fas fa-plus me-1"></i>Créer un contrat
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        boxes: {
            type: Array,
            required: true
        },
        emplacements: {
            type: Array,
            default: () => []
        },
        canCreateContract: {
            type: Boolean,
            default: false
        },
        canViewContract: {
            type: Boolean,
            default: true
        },
        isAdmin: {
            type: Boolean,
            default: false
        }
    },

    data() {
        return {
            selectedEmplacement: null,
            selectedBox: null,
            tooltip: {
                show: false,
                box: null,
                x: 0,
                y: 0
            }
        };
    },

    computed: {
        filteredBoxes() {
            if (!this.selectedEmplacement) {
                return this.boxes;
            }
            return this.boxes.filter(box => box.emplacement_id === this.selectedEmplacement.id);
        },

        gridCols() {
            if (this.filteredBoxes.length === 0) return 5;
            const maxX = Math.max(...this.filteredBoxes.map(b => b.coordonnees_x || 0));
            return Math.max(5, maxX + 1);
        },

        gridRows() {
            if (this.filteredBoxes.length === 0) return 5;
            const maxY = Math.max(...this.filteredBoxes.map(b => b.coordonnees_y || 0));
            return Math.max(5, maxY + 1);
        },

        stats() {
            const boxes = this.filteredBoxes;
            return {
                libres: boxes.filter(b => b.statut === 'libre').length,
                occupes: boxes.filter(b => b.statut === 'occupe').length,
                reserves: boxes.filter(b => b.statut === 'reserve').length,
                maintenance: boxes.filter(b => b.statut === 'maintenance').length
            };
        }
    },

    mounted() {
        if (this.emplacements.length > 0) {
            this.selectedEmplacement = this.emplacements[0];
        }
    },

    methods: {
        selectEmplacement(emplacement) {
            this.selectedEmplacement = emplacement;
        },

        getEmplacementBoxCount(emplacementId) {
            return this.boxes.filter(b => b.emplacement_id === emplacementId).length;
        },

        getBoxClass(box) {
            const classes = {
                'libre': 'box-libre',
                'occupe': 'box-occupe',
                'reserve': 'box-reserve',
                'maintenance': 'box-maintenance'
            };
            return classes[box.statut] || 'box-default';
        },

        getBoxStyle(box) {
            if (box.coordonnees_x !== null && box.coordonnees_y !== null) {
                return {
                    gridColumn: box.coordonnees_x + 1,
                    gridRow: box.coordonnees_y + 1
                };
            }
            return {};
        },

        handleBoxClick(box) {
            this.selectedBox = box;
            this.hideTooltip();
        },

        closeModal() {
            this.selectedBox = null;
        },

        showTooltip(box, event) {
            const rect = event.target.getBoundingClientRect();
            this.tooltip = {
                show: true,
                box: box,
                x: rect.left + rect.width / 2,
                y: rect.top - 10
            };
        },

        hideTooltip() {
            this.tooltip.show = false;
        },

        formatCurrency(amount) {
            return new Intl.NumberFormat('fr-FR', {
                style: 'currency',
                currency: 'EUR'
            }).format(amount || 0);
        },

        formatDate(date) {
            if (!date) return '';
            return new Date(date).toLocaleDateString('fr-FR');
        },

        getStatutLabel(statut) {
            const labels = {
                'libre': 'Libre',
                'occupe': 'Occupé',
                'reserve': 'Réservé',
                'maintenance': 'Maintenance'
            };
            return labels[statut] || statut;
        },

        getContractUrl(contractId) {
            if (this.isAdmin) {
                return route('contrats.show', contractId);
            }
            return route('client.contrats.show', contractId);
        },

        getNewContractUrl(boxId) {
            return route('contrats.create', { box_id: boxId });
        }
    }
};
</script>

<style scoped>
.box-plan-container {
    position: relative;
}

.legend {
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.legend-box {
    width: 30px;
    height: 30px;
    border-radius: 4px;
    border: 2px solid #dee2e6;
}

.plan-grid-wrapper {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    overflow: auto;
}

.plan-grid {
    display: grid;
    gap: 10px;
    min-height: 400px;
    max-width: 100%;
}

.box-cell {
    background: white;
    border: 2px solid #dee2e6;
    border-radius: 8px;
    padding: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    min-height: 80px;
    position: relative;
}

.box-cell:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 10;
}

.box-libre {
    background: #d4edda;
    border-color: #28a745;
}

.box-libre:hover {
    background: #c3e6cb;
}

.box-occupe {
    background: #f8d7da;
    border-color: #dc3545;
}

.box-occupe:hover {
    background: #f5c6cb;
}

.box-reserve {
    background: #fff3cd;
    border-color: #ffc107;
}

.box-reserve:hover {
    background: #ffeaa7;
}

.box-maintenance {
    background: #d6d8db;
    border-color: #6c757d;
}

.box-maintenance:hover {
    background: #c6c8ca;
}

.box-number {
    font-weight: bold;
    font-size: 1.1em;
    color: #333;
}

.box-surface {
    font-size: 0.85em;
    color: #666;
    margin-top: 4px;
}

.box-tooltip {
    position: fixed;
    background: white;
    border: 2px solid #dee2e6;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    z-index: 1000;
    min-width: 250px;
    transform: translate(-50%, -100%);
    pointer-events: none;
}

.tooltip-header {
    padding: 10px 15px;
    border-bottom: 1px solid #dee2e6;
    border-radius: 6px 6px 0 0;
    font-weight: bold;
}

.tooltip-header.box-libre {
    background: #28a745;
    color: white;
}

.tooltip-header.box-occupe {
    background: #dc3545;
    color: white;
}

.tooltip-header.box-reserve {
    background: #ffc107;
    color: #333;
}

.tooltip-header.box-maintenance {
    background: #6c757d;
    color: white;
}

.tooltip-body {
    padding: 15px;
    font-size: 0.9em;
}

.tooltip-body > div {
    margin-bottom: 5px;
}

.modal.show {
    display: block;
}
</style>
