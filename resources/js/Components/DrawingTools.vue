<template>
    <div class="drawing-tools">
        <div class="card">
            <div class="card-header bg-dark text-white py-2">
                <h6 class="mb-0">
                    <i class="fas fa-pencil-ruler me-1"></i>Outils de Dessin
                </h6>
            </div>
            <div class="card-body p-2">
                <!-- Modes de dessin -->
                <div class="btn-group-vertical w-100 mb-3" role="group">
                    <input type="radio" class="btn-check" id="tool-select" v-model="currentTool" value="select" autocomplete="off">
                    <label class="btn btn-outline-primary btn-sm" for="tool-select">
                        <i class="fas fa-mouse-pointer me-1"></i>Sélection
                    </label>

                    <input type="radio" class="btn-check" id="tool-box" v-model="currentTool" value="box" autocomplete="off">
                    <label class="btn btn-outline-success btn-sm" for="tool-box">
                        <i class="fas fa-vector-square me-1"></i>Créer Box
                    </label>

                    <input type="radio" class="btn-check" id="tool-room" v-model="currentTool" value="room" autocomplete="off">
                    <label class="btn btn-outline-info btn-sm" for="tool-room">
                        <i class="fas fa-draw-polygon me-1"></i>Zone/Salle
                    </label>

                    <input type="radio" class="btn-check" id="tool-wall" v-model="currentTool" value="wall" autocomplete="off">
                    <label class="btn btn-outline-secondary btn-sm" for="tool-wall">
                        <i class="fas fa-grip-lines me-1"></i>Mur
                    </label>

                    <input type="radio" class="btn-check" id="tool-door" v-model="currentTool" value="door" autocomplete="off">
                    <label class="btn btn-outline-warning btn-sm" for="tool-door">
                        <i class="fas fa-door-open me-1"></i>Porte
                    </label>

                    <input type="radio" class="btn-check" id="tool-text" v-model="currentTool" value="text" autocomplete="off">
                    <label class="btn btn-outline-dark btn-sm" for="tool-text">
                        <i class="fas fa-font me-1"></i>Texte
                    </label>

                    <input type="radio" class="btn-check" id="tool-arrow" v-model="currentTool" value="arrow" autocomplete="off">
                    <label class="btn btn-outline-danger btn-sm" for="tool-arrow">
                        <i class="fas fa-arrow-right me-1"></i>Flèche
                    </label>
                </div>

                <!-- Options de l'outil sélectionné -->
                <div v-if="currentTool === 'box'" class="tool-options">
                    <h6 class="small text-muted mb-2">Options Box:</h6>
                    <div class="mb-2">
                        <label class="form-label small mb-1">Largeur (px)</label>
                        <input v-model.number="boxWidth" type="number" class="form-control form-control-sm" min="50">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small mb-1">Hauteur (px)</label>
                        <input v-model.number="boxHeight" type="number" class="form-control form-control-sm" min="40">
                    </div>
                    <div class="alert alert-info small p-2 mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Cliquez sur le plan pour placer une nouvelle box
                    </div>
                </div>

                <div v-else-if="currentTool === 'room'" class="tool-options">
                    <h6 class="small text-muted mb-2">Options Zone:</h6>
                    <div class="mb-2">
                        <label class="form-label small mb-1">Nom de la zone</label>
                        <input v-model="roomName" type="text" class="form-control form-control-sm" placeholder="Ex: Salle A">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small mb-1">Couleur</label>
                        <input v-model="roomColor" type="color" class="form-control form-control-sm">
                    </div>
                    <div class="alert alert-info small p-2 mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Dessinez en maintenant le clic
                    </div>
                </div>

                <div v-else-if="currentTool === 'wall'" class="tool-options">
                    <h6 class="small text-muted mb-2">Options Mur:</h6>
                    <div class="mb-2">
                        <label class="form-label small mb-1">Épaisseur (px)</label>
                        <input v-model.number="wallThickness" type="number" class="form-control form-control-sm" min="1" max="20">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small mb-1">Couleur</label>
                        <input v-model="wallColor" type="color" class="form-control form-control-sm">
                    </div>
                </div>

                <div v-else-if="currentTool === 'text'" class="tool-options">
                    <h6 class="small text-muted mb-2">Options Texte:</h6>
                    <div class="mb-2">
                        <label class="form-label small mb-1">Texte</label>
                        <input v-model="textContent" type="text" class="form-control form-control-sm" placeholder="Saisir le texte">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small mb-1">Taille</label>
                        <input v-model.number="textSize" type="number" class="form-control form-control-sm" min="8" max="72">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small mb-1">Couleur</label>
                        <input v-model="textColor" type="color" class="form-control form-control-sm">
                    </div>
                </div>

                <div v-else-if="currentTool === 'arrow'" class="tool-options">
                    <h6 class="small text-muted mb-2">Options Flèche:</h6>
                    <div class="mb-2">
                        <label class="form-label small mb-1">Épaisseur (px)</label>
                        <input v-model.number="arrowThickness" type="number" class="form-control form-control-sm" min="1" max="10">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small mb-1">Couleur</label>
                        <input v-model="arrowColor" type="color" class="form-control form-control-sm">
                    </div>
                </div>
            </div>
        </div>

        <!-- Layers / Calques -->
        <div class="card mt-3">
            <div class="card-header bg-secondary text-white py-2">
                <h6 class="mb-0">
                    <i class="fas fa-layer-group me-1"></i>Calques
                </h6>
            </div>
            <div class="card-body p-2">
                <div class="list-group list-group-flush">
                    <div
                        v-for="layer in layers"
                        :key="layer.id"
                        class="list-group-item p-2 d-flex align-items-center justify-content-between"
                        :class="{ 'active': layer.id === activeLayer }"
                    >
                        <div class="form-check">
                            <input
                                v-model="layer.visible"
                                type="checkbox"
                                class="form-check-input"
                                :id="'layer-' + layer.id"
                            />
                            <label class="form-check-label small" :for="'layer-' + layer.id">
                                {{ layer.name }}
                            </label>
                        </div>
                        <button
                            @click="setActiveLayer(layer.id)"
                            class="btn btn-sm btn-outline-primary"
                            :class="{ 'active': layer.id === activeLayer }"
                        >
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                </div>
                <button @click="addLayer" class="btn btn-sm btn-outline-success w-100 mt-2">
                    <i class="fas fa-plus me-1"></i>Nouveau calque
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            currentTool: 'select',
            boxWidth: 100,
            boxHeight: 80,
            roomName: '',
            roomColor: '#e3f2fd',
            wallThickness: 4,
            wallColor: '#333333',
            textContent: '',
            textSize: 14,
            textColor: '#000000',
            arrowThickness: 2,
            arrowColor: '#dc3545',
            layers: [
                { id: 1, name: 'Fond de plan', visible: true, locked: true },
                { id: 2, name: 'Zones/Salles', visible: true, locked: false },
                { id: 3, name: 'Boxes', visible: true, locked: false },
                { id: 4, name: 'Annotations', visible: true, locked: false }
            ],
            activeLayer: 3,
            nextLayerId: 5
        };
    },

    watch: {
        currentTool(newTool) {
            this.$emit('tool-changed', {
                tool: newTool,
                options: this.getToolOptions()
            });
        }
    },

    methods: {
        getToolOptions() {
            switch (this.currentTool) {
                case 'box':
                    return { width: this.boxWidth, height: this.boxHeight };
                case 'room':
                    return { name: this.roomName, color: this.roomColor };
                case 'wall':
                    return { thickness: this.wallThickness, color: this.wallColor };
                case 'text':
                    return { content: this.textContent, size: this.textSize, color: this.textColor };
                case 'arrow':
                    return { thickness: this.arrowThickness, color: this.arrowColor };
                default:
                    return {};
            }
        },

        setActiveLayer(layerId) {
            this.activeLayer = layerId;
            this.$emit('layer-changed', layerId);
        },

        addLayer() {
            const newLayer = {
                id: this.nextLayerId++,
                name: `Calque ${this.layers.length + 1}`,
                visible: true,
                locked: false
            };
            this.layers.push(newLayer);
            this.activeLayer = newLayer.id;
        }
    }
};
</script>

<style scoped>
.drawing-tools {
    position: sticky;
    top: 20px;
}

.tool-options {
    border-top: 1px solid #dee2e6;
    padding-top: 10px;
    margin-top: 10px;
}

.btn-check:checked + .btn {
    font-weight: bold;
}
</style>
