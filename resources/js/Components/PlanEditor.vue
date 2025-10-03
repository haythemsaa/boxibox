<template>
    <div class="plan-editor-container">
        <!-- Barre d'outils avancée -->
        <div class="toolbar card mb-3">
            <div class="card-body">
                <div class="row align-items-center mb-2">
                    <div class="col-md-3">
                        <label class="form-label small mb-1">
                            <i class="fas fa-image me-1"></i>Image de fond (Plan)
                        </label>
                        <input
                            type="file"
                            @change="handleBackgroundUpload"
                            accept="image/*"
                            class="form-control form-control-sm"
                        />
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1">
                            <i class="fas fa-search me-1"></i>Zoom
                        </label>
                        <div class="d-flex align-items-center gap-2">
                            <input
                                v-model.number="zoom"
                                type="range"
                                min="25"
                                max="300"
                                step="25"
                                class="form-range"
                            />
                            <span class="badge bg-secondary">{{ zoom }}%</span>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1">
                            <i class="fas fa-th me-1"></i>Grille
                        </label>
                        <div class="input-group input-group-sm">
                            <div class="form-check form-switch me-2">
                                <input
                                    v-model="showGrid"
                                    type="checkbox"
                                    class="form-check-input"
                                    id="showGrid"
                                />
                            </div>
                            <input
                                v-model.number="gridSize"
                                type="number"
                                min="10"
                                max="100"
                                step="10"
                                class="form-control"
                                :disabled="!showGrid"
                            />
                            <span class="input-group-text">px</span>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1">
                            <i class="fas fa-magnet me-1"></i>Magnétisme
                        </label>
                        <div class="form-check form-switch">
                            <input
                                v-model="snapToGrid"
                                type="checkbox"
                                class="form-check-input"
                                id="snapToGrid"
                            />
                            <label class="form-check-label" for="snapToGrid">
                                Aimanter à la grille
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3 text-end">
                        <div class="btn-group btn-group-sm me-2" role="group">
                            <button
                                @click="undo"
                                :disabled="!canUndo"
                                class="btn btn-outline-secondary"
                                title="Annuler (Ctrl+Z)"
                            >
                                <i class="fas fa-undo"></i>
                            </button>
                            <button
                                @click="redo"
                                :disabled="!canRedo"
                                class="btn btn-outline-secondary"
                                title="Rétablir (Ctrl+Y)"
                            >
                                <i class="fas fa-redo"></i>
                            </button>
                        </div>
                        <button @click="saveLayout" class="btn btn-success btn-sm me-2">
                            <i class="fas fa-save me-1"></i>Enregistrer
                        </button>
                        <button @click="resetLayout" class="btn btn-danger btn-sm">
                            <i class="fas fa-trash me-1"></i>Réinitialiser
                        </button>
                    </div>
                </div>

                <!-- Barre d'outils secondaire -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="d-flex gap-2 align-items-center">
                            <span class="small text-muted">Mode:</span>
                            <div class="btn-group btn-group-sm" role="group">
                                <input type="radio" class="btn-check" id="modeSelect" v-model="editMode" value="select" autocomplete="off">
                                <label class="btn btn-outline-primary" for="modeSelect">
                                    <i class="fas fa-mouse-pointer"></i> Sélection
                                </label>

                                <input type="radio" class="btn-check" id="modePan" v-model="editMode" value="pan" autocomplete="off">
                                <label class="btn btn-outline-primary" for="modePan">
                                    <i class="fas fa-hand-paper"></i> Déplacer
                                </label>
                            </div>

                            <span class="ms-3 small text-muted">Affichage:</span>
                            <div class="form-check form-check-inline">
                                <input v-model="showLabels" class="form-check-input" type="checkbox" id="showLabels">
                                <label class="form-check-label small" for="showLabels">
                                    <i class="fas fa-tag"></i> Étiquettes
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input v-model="showDimensions" class="form-check-input" type="checkbox" id="showDimensions">
                                <label class="form-check-label small" for="showDimensions">
                                    <i class="fas fa-ruler-combined"></i> Dimensions
                                </label>
                            </div>

                            <span class="ms-3 small">
                                <i class="fas fa-info-circle text-info me-1"></i>
                                <span v-if="selectedBoxes.length === 0">Aucune sélection</span>
                                <span v-else-if="selectedBoxes.length === 1">1 box sélectionnée</span>
                                <span v-else>{{ selectedBoxes.length }} boxes sélectionnées</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Canvas principal -->
            <div class="col-md-9">
                <div class="editor-workspace card">
                    <div class="card-body p-2">
                        <div class="canvas-wrapper" @wheel="handleWheel">
                            <div
                                ref="canvas"
                                class="canvas"
                                :style="{
                                    backgroundImage: backgroundImage ? `url(${backgroundImage})` : 'none',
                                    backgroundSize: 'contain',
                                    backgroundRepeat: 'no-repeat',
                                    backgroundPosition: 'center',
                                    width: canvasWidth + 'px',
                                    height: canvasHeight + 'px',
                                    transform: `scale(${zoom / 100})`,
                                    transformOrigin: 'top left',
                                    cursor: editMode === 'pan' ? 'grab' : 'default'
                                }"
                                @mousedown="handleCanvasMouseDown"
                                @click="handleCanvasClick"
                            >
                                <!-- Grille -->
                                <svg
                                    v-if="showGrid"
                                    class="grid-overlay"
                                    :width="canvasWidth"
                                    :height="canvasHeight"
                                >
                                    <defs>
                                        <pattern
                                            id="grid"
                                            :width="gridSize"
                                            :height="gridSize"
                                            patternUnits="userSpaceOnUse"
                                        >
                                            <path
                                                :d="`M ${gridSize} 0 L 0 0 0 ${gridSize}`"
                                                fill="none"
                                                stroke="#dee2e6"
                                                stroke-width="1"
                                            />
                                        </pattern>
                                    </defs>
                                    <rect width="100%" height="100%" fill="url(#grid)" />
                                </svg>

                                <!-- Zone de sélection multiple -->
                                <div
                                    v-if="selectionBox"
                                    class="selection-box"
                                    :style="{
                                        left: selectionBox.x + 'px',
                                        top: selectionBox.y + 'px',
                                        width: selectionBox.width + 'px',
                                        height: selectionBox.height + 'px'
                                    }"
                                ></div>

                                <!-- Boxes -->
                                <div
                                    v-for="box in localBoxes"
                                    :key="box.id"
                                    class="box-element"
                                    :class="{
                                        'selected': isSelected(box),
                                        'libre': box.statut === 'libre',
                                        'occupe': box.statut === 'occupe',
                                        'reserve': box.statut === 'reserve',
                                        'maintenance': box.statut === 'maintenance'
                                    }"
                                    :style="{
                                        left: box.plan_x + 'px',
                                        top: box.plan_y + 'px',
                                        width: box.plan_width + 'px',
                                        height: box.plan_height + 'px',
                                        transform: box.rotation ? `rotate(${box.rotation}deg)` : 'none'
                                    }"
                                    @mousedown.stop="startDrag(box, $event)"
                                    @click.stop="selectBox(box, $event)"
                                    @dblclick.stop="editBoxProperties(box)"
                                >
                                    <!-- Label de la box -->
                                    <div class="box-label" v-if="showLabels">
                                        <strong>{{ box.numero }}</strong>
                                    </div>

                                    <!-- Dimensions -->
                                    <div class="box-dimensions" v-if="showDimensions">
                                        <small>{{ box.surface }}m² - {{ box.prix_mensuel }}€</small>
                                    </div>

                                    <!-- Icône de statut -->
                                    <div class="box-status-icon">
                                        <i v-if="box.statut === 'libre'" class="fas fa-check-circle text-success"></i>
                                        <i v-else-if="box.statut === 'occupe'" class="fas fa-lock text-danger"></i>
                                        <i v-else-if="box.statut === 'reserve'" class="fas fa-clock text-warning"></i>
                                        <i v-else-if="box.statut === 'maintenance'" class="fas fa-wrench text-info"></i>
                                    </div>

                                    <!-- Poignées de redimensionnement -->
                                    <template v-if="isSelected(box)">
                                        <div
                                            v-for="handle in resizeHandles"
                                            :key="handle"
                                            :class="`resize-handle ${handle}`"
                                            @mousedown.stop="startResize(box, handle, $event)"
                                        ></div>

                                        <!-- Poignée de rotation -->
                                        <div
                                            class="rotate-handle"
                                            @mousedown.stop="startRotate(box, $event)"
                                            title="Rotation"
                                        >
                                            <i class="fas fa-sync-alt"></i>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Minimap -->
                <div class="minimap card mt-2" v-if="backgroundImage">
                    <div class="card-body p-2">
                        <div class="minimap-canvas" :style="{ backgroundImage: `url(${backgroundImage})` }">
                            <div
                                v-for="box in localBoxes"
                                :key="'mini-' + box.id"
                                class="minimap-box"
                                :class="box.statut"
                                :style="{
                                    left: (box.plan_x / canvasWidth * 100) + '%',
                                    top: (box.plan_y / canvasHeight * 100) + '%',
                                    width: (box.plan_width / canvasWidth * 100) + '%',
                                    height: (box.plan_height / canvasHeight * 100) + '%'
                                }"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panneau latéral -->
            <div class="col-md-3">
                <!-- Propriétés de la box sélectionnée -->
                <div class="card mb-3" v-if="selectedBoxes.length === 1">
                    <div class="card-header bg-primary text-white py-2">
                        <h6 class="mb-0">
                            <i class="fas fa-cog me-1"></i>Propriétés
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <label class="form-label small mb-1">Box</label>
                            <input
                                type="text"
                                class="form-control form-control-sm"
                                :value="selectedBoxes[0].numero"
                                disabled
                            />
                        </div>
                        <div class="row">
                            <div class="col-6 mb-2">
                                <label class="form-label small mb-1">X (px)</label>
                                <input
                                    v-model.number="selectedBoxes[0].plan_x"
                                    type="number"
                                    class="form-control form-control-sm"
                                    @change="saveState"
                                />
                            </div>
                            <div class="col-6 mb-2">
                                <label class="form-label small mb-1">Y (px)</label>
                                <input
                                    v-model.number="selectedBoxes[0].plan_y"
                                    type="number"
                                    class="form-control form-control-sm"
                                    @change="saveState"
                                />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-2">
                                <label class="form-label small mb-1">Largeur</label>
                                <input
                                    v-model.number="selectedBoxes[0].plan_width"
                                    type="number"
                                    class="form-control form-control-sm"
                                    min="50"
                                    @change="saveState"
                                />
                            </div>
                            <div class="col-6 mb-2">
                                <label class="form-label small mb-1">Hauteur</label>
                                <input
                                    v-model.number="selectedBoxes[0].plan_height"
                                    type="number"
                                    class="form-control form-control-sm"
                                    min="40"
                                    @change="saveState"
                                />
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small mb-1">Rotation (°)</label>
                            <input
                                v-model.number="selectedBoxes[0].rotation"
                                type="number"
                                class="form-control form-control-sm"
                                min="0"
                                max="360"
                                step="15"
                                @change="saveState"
                            />
                        </div>
                        <div class="mb-2">
                            <label class="form-label small mb-1">Statut</label>
                            <div class="badge w-100" :class="{
                                'bg-success': selectedBoxes[0].statut === 'libre',
                                'bg-danger': selectedBoxes[0].statut === 'occupe',
                                'bg-warning': selectedBoxes[0].statut === 'reserve',
                                'bg-info': selectedBoxes[0].statut === 'maintenance'
                            }">
                                {{ selectedBoxes[0].statut }}
                            </div>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">
                                Surface: {{ selectedBoxes[0].surface }}m²<br>
                                Prix: {{ selectedBoxes[0].prix_mensuel }}€/mois
                            </small>
                        </div>
                        <hr>
                        <div class="d-grid gap-2">
                            <button @click="alignToGrid(selectedBoxes[0])" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-border-all me-1"></i>Aligner sur grille
                            </button>
                            <button @click="duplicateBox(selectedBoxes[0])" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-copy me-1"></i>Dupliquer
                            </button>
                            <button @click="removeFromPlan(selectedBoxes[0])" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-times me-1"></i>Retirer du plan
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Actions multiples -->
                <div class="card mb-3" v-else-if="selectedBoxes.length > 1">
                    <div class="card-header bg-primary text-white py-2">
                        <h6 class="mb-0">
                            <i class="fas fa-layer-group me-1"></i>Sélection multiple ({{ selectedBoxes.length }})
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button @click="alignSelectedBoxes('left')" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-align-left me-1"></i>Aligner à gauche
                            </button>
                            <button @click="alignSelectedBoxes('right')" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-align-right me-1"></i>Aligner à droite
                            </button>
                            <button @click="alignSelectedBoxes('top')" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-align-left me-1" style="transform: rotate(90deg)"></i>Aligner en haut
                            </button>
                            <button @click="alignSelectedBoxes('bottom')" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-align-right me-1" style="transform: rotate(90deg)"></i>Aligner en bas
                            </button>
                            <button @click="distributeSelectedBoxes('horizontal')" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrows-alt-h me-1"></i>Distribuer horizontalement
                            </button>
                            <button @click="distributeSelectedBoxes('vertical')" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrows-alt-v me-1"></i>Distribuer verticalement
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Liste des boxes disponibles -->
                <div class="card">
                    <div class="card-header bg-secondary text-white py-2">
                        <h6 class="mb-0">
                            <i class="fas fa-box me-1"></i>Boxes disponibles
                        </h6>
                    </div>
                    <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                        <div class="list-group list-group-flush">
                            <div
                                v-for="box in availableBoxes"
                                :key="'available-' + box.id"
                                class="list-group-item list-group-item-action p-2"
                                :class="{ 'active': isSelected(box) }"
                                @click="selectBox(box)"
                                style="cursor: pointer;"
                            >
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ box.numero }}</strong>
                                        <span class="badge ms-2" :class="{
                                            'bg-success': box.statut === 'libre',
                                            'bg-danger': box.statut === 'occupe',
                                            'bg-warning': box.statut === 'reserve'
                                        }">{{ box.statut }}</span>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted d-block">{{ box.surface }}m²</small>
                                        <small class="text-muted">{{ box.prix_mensuel }}€</small>
                                    </div>
                                </div>
                                <small class="text-muted" v-if="box.emplacement">
                                    <i class="fas fa-map-marker-alt me-1"></i>{{ box.emplacement.nom }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Légende -->
                <div class="card mt-3">
                    <div class="card-header py-2">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle me-1"></i>Légende
                        </h6>
                    </div>
                    <div class="card-body p-2">
                        <div class="d-flex align-items-center mb-1">
                            <div class="legend-color bg-success"></div>
                            <small>Libre</small>
                        </div>
                        <div class="d-flex align-items-center mb-1">
                            <div class="legend-color bg-danger"></div>
                            <small>Occupé</small>
                        </div>
                        <div class="d-flex align-items-center mb-1">
                            <div class="legend-color bg-warning"></div>
                            <small>Réservé</small>
                        </div>
                        <div class="d-flex align-items-center mb-1">
                            <div class="legend-color bg-info"></div>
                            <small>Maintenance</small>
                        </div>
                        <hr class="my-2">
                        <small class="text-muted">
                            <i class="fas fa-keyboard me-1"></i><strong>Raccourcis:</strong><br>
                            Ctrl+Z: Annuler<br>
                            Ctrl+Y: Rétablir<br>
                            Suppr: Retirer du plan<br>
                            Ctrl+D: Dupliquer<br>
                            Ctrl+A: Tout sélectionner
                        </small>
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
        emplacementId: {
            type: Number,
            required: true
        },
        initialBackgroundImage: {
            type: String,
            default: null
        }
    },

    data() {
        return {
            localBoxes: [],
            backgroundImage: this.initialBackgroundImage,
            zoom: 100,
            showGrid: true,
            gridSize: 20,
            snapToGrid: true,
            canvasWidth: 1200,
            canvasHeight: 800,
            selectedBoxes: [],
            dragging: false,
            resizing: false,
            rotating: false,
            dragStartX: 0,
            dragStartY: 0,
            currentBox: null,
            currentHandle: null,
            resizeHandles: ['nw', 'ne', 'sw', 'se', 'n', 's', 'e', 'w'],
            editMode: 'select',
            showLabels: true,
            showDimensions: true,
            selectionBox: null,
            history: [],
            historyIndex: -1,
            maxHistory: 50
        };
    },

    computed: {
        availableBoxes() {
            return this.localBoxes.filter(box => box.emplacement_id === this.emplacementId);
        },
        canUndo() {
            return this.historyIndex > 0;
        },
        canRedo() {
            return this.historyIndex < this.history.length - 1;
        }
    },

    mounted() {
        this.initializeBoxes();
        this.saveState();

        // Event listeners
        document.addEventListener('mousemove', this.handleMouseMove);
        document.addEventListener('mouseup', this.handleMouseUp);
        document.addEventListener('keydown', this.handleKeyDown);
    },

    beforeUnmount() {
        document.removeEventListener('mousemove', this.handleMouseMove);
        document.removeEventListener('mouseup', this.handleMouseUp);
        document.removeEventListener('keydown', this.handleKeyDown);
    },

    methods: {
        initializeBoxes() {
            this.localBoxes = this.boxes.map(box => ({
                ...box,
                plan_x: box.plan_x || 100,
                plan_y: box.plan_y || 100,
                plan_width: box.plan_width || 100,
                plan_height: box.plan_height || 80,
                rotation: box.rotation || 0
            }));
        },

        handleBackgroundUpload(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.backgroundImage = e.target.result;
                    this.saveState();
                };
                reader.readAsDataURL(file);
            }
        },

        selectBox(box, event = null) {
            if (event && event.ctrlKey) {
                // Multi-sélection
                const index = this.selectedBoxes.findIndex(b => b.id === box.id);
                if (index > -1) {
                    this.selectedBoxes.splice(index, 1);
                } else {
                    this.selectedBoxes.push(box);
                }
            } else {
                this.selectedBoxes = [box];
            }
        },

        isSelected(box) {
            return this.selectedBoxes.some(b => b.id === box.id);
        },

        startDrag(box, event) {
            if (this.editMode !== 'select') return;

            if (!this.isSelected(box)) {
                this.selectBox(box, event);
            }

            this.dragging = true;
            this.dragStartX = event.clientX;
            this.dragStartY = event.clientY;

            // Sauvegarder positions initiales
            this.selectedBoxes.forEach(b => {
                b.dragStartX = b.plan_x;
                b.dragStartY = b.plan_y;
            });
        },

        startResize(box, handle, event) {
            this.resizing = true;
            this.currentBox = box;
            this.currentHandle = handle;
            this.dragStartX = event.clientX;
            this.dragStartY = event.clientY;
            this.initialWidth = box.plan_width;
            this.initialHeight = box.plan_height;
            this.initialX = box.plan_x;
            this.initialY = box.plan_y;
        },

        startRotate(box, event) {
            this.rotating = true;
            this.currentBox = box;
            const rect = event.target.closest('.box-element').getBoundingClientRect();
            this.centerX = rect.left + rect.width / 2;
            this.centerY = rect.top + rect.height / 2;
        },

        handleMouseMove(event) {
            if (this.dragging && this.selectedBoxes.length > 0) {
                const deltaX = (event.clientX - this.dragStartX) / (this.zoom / 100);
                const deltaY = (event.clientY - this.dragStartY) / (this.zoom / 100);

                this.selectedBoxes.forEach(box => {
                    let newX = box.dragStartX + deltaX;
                    let newY = box.dragStartY + deltaY;

                    if (this.snapToGrid) {
                        newX = Math.round(newX / this.gridSize) * this.gridSize;
                        newY = Math.round(newY / this.gridSize) * this.gridSize;
                    }

                    box.plan_x = Math.max(0, Math.min(this.canvasWidth - box.plan_width, newX));
                    box.plan_y = Math.max(0, Math.min(this.canvasHeight - box.plan_height, newY));
                });
            } else if (this.resizing && this.currentBox) {
                const deltaX = (event.clientX - this.dragStartX) / (this.zoom / 100);
                const deltaY = (event.clientY - this.dragStartY) / (this.zoom / 100);

                this.handleResize(deltaX, deltaY);
            } else if (this.rotating && this.currentBox) {
                const angle = Math.atan2(
                    event.clientY - this.centerY,
                    event.clientX - this.centerX
                ) * 180 / Math.PI;
                this.currentBox.rotation = Math.round((angle + 90) / 15) * 15;
            }
        },

        handleResize(deltaX, deltaY) {
            const box = this.currentBox;
            const handle = this.currentHandle;

            switch (handle) {
                case 'se':
                    box.plan_width = Math.max(50, this.initialWidth + deltaX);
                    box.plan_height = Math.max(40, this.initialHeight + deltaY);
                    break;
                case 'sw':
                    box.plan_width = Math.max(50, this.initialWidth - deltaX);
                    box.plan_height = Math.max(40, this.initialHeight + deltaY);
                    box.plan_x = this.initialX + deltaX;
                    break;
                case 'ne':
                    box.plan_width = Math.max(50, this.initialWidth + deltaX);
                    box.plan_height = Math.max(40, this.initialHeight - deltaY);
                    box.plan_y = this.initialY + deltaY;
                    break;
                case 'nw':
                    box.plan_width = Math.max(50, this.initialWidth - deltaX);
                    box.plan_height = Math.max(40, this.initialHeight - deltaY);
                    box.plan_x = this.initialX + deltaX;
                    box.plan_y = this.initialY + deltaY;
                    break;
                case 'e':
                    box.plan_width = Math.max(50, this.initialWidth + deltaX);
                    break;
                case 'w':
                    box.plan_width = Math.max(50, this.initialWidth - deltaX);
                    box.plan_x = this.initialX + deltaX;
                    break;
                case 'n':
                    box.plan_height = Math.max(40, this.initialHeight - deltaY);
                    box.plan_y = this.initialY + deltaY;
                    break;
                case 's':
                    box.plan_height = Math.max(40, this.initialHeight + deltaY);
                    break;
            }

            if (this.snapToGrid) {
                box.plan_width = Math.round(box.plan_width / this.gridSize) * this.gridSize;
                box.plan_height = Math.round(box.plan_height / this.gridSize) * this.gridSize;
            }
        },

        handleMouseUp() {
            if (this.dragging || this.resizing || this.rotating) {
                this.saveState();
            }
            this.dragging = false;
            this.resizing = false;
            this.rotating = false;
            this.currentBox = null;
            this.currentHandle = null;
        },

        handleCanvasClick(event) {
            if (event.target === this.$refs.canvas || event.target.tagName === 'svg' || event.target.tagName === 'rect') {
                this.selectedBoxes = [];
            }
        },

        handleCanvasMouseDown(event) {
            if (this.editMode === 'select' && event.target === this.$refs.canvas) {
                // Début de sélection multiple par zone
                const rect = this.$refs.canvas.getBoundingClientRect();
                const x = (event.clientX - rect.left) / (this.zoom / 100);
                const y = (event.clientY - rect.top) / (this.zoom / 100);

                this.selectionBox = { x, y, width: 0, height: 0 };
            }
        },

        handleWheel(event) {
            if (event.ctrlKey) {
                event.preventDefault();
                const delta = event.deltaY > 0 ? -25 : 25;
                this.zoom = Math.max(25, Math.min(300, this.zoom + delta));
            }
        },

        handleKeyDown(event) {
            // Ctrl+Z: Undo
            if (event.ctrlKey && event.key === 'z') {
                event.preventDefault();
                this.undo();
            }
            // Ctrl+Y: Redo
            else if (event.ctrlKey && event.key === 'y') {
                event.preventDefault();
                this.redo();
            }
            // Delete: Retirer du plan
            else if (event.key === 'Delete' && this.selectedBoxes.length > 0) {
                this.selectedBoxes.forEach(box => this.removeFromPlan(box));
            }
            // Ctrl+D: Dupliquer
            else if (event.ctrlKey && event.key === 'd' && this.selectedBoxes.length === 1) {
                event.preventDefault();
                this.duplicateBox(this.selectedBoxes[0]);
            }
            // Ctrl+A: Sélectionner tout
            else if (event.ctrlKey && event.key === 'a') {
                event.preventDefault();
                this.selectedBoxes = [...this.availableBoxes];
            }
        },

        alignToGrid(box) {
            box.plan_x = Math.round(box.plan_x / this.gridSize) * this.gridSize;
            box.plan_y = Math.round(box.plan_y / this.gridSize) * this.gridSize;
            box.plan_width = Math.round(box.plan_width / this.gridSize) * this.gridSize;
            box.plan_height = Math.round(box.plan_height / this.gridSize) * this.gridSize;
            this.saveState();
        },

        alignSelectedBoxes(direction) {
            if (this.selectedBoxes.length < 2) return;

            const positions = this.selectedBoxes.map(b => {
                switch(direction) {
                    case 'left': return b.plan_x;
                    case 'right': return b.plan_x + b.plan_width;
                    case 'top': return b.plan_y;
                    case 'bottom': return b.plan_y + b.plan_height;
                }
            });

            const target = direction === 'left' || direction === 'top'
                ? Math.min(...positions)
                : Math.max(...positions);

            this.selectedBoxes.forEach(box => {
                switch(direction) {
                    case 'left':
                        box.plan_x = target;
                        break;
                    case 'right':
                        box.plan_x = target - box.plan_width;
                        break;
                    case 'top':
                        box.plan_y = target;
                        break;
                    case 'bottom':
                        box.plan_y = target - box.plan_height;
                        break;
                }
            });

            this.saveState();
        },

        distributeSelectedBoxes(direction) {
            if (this.selectedBoxes.length < 3) return;

            const sorted = [...this.selectedBoxes].sort((a, b) => {
                return direction === 'horizontal'
                    ? a.plan_x - b.plan_x
                    : a.plan_y - b.plan_y;
            });

            const first = sorted[0];
            const last = sorted[sorted.length - 1];
            const totalSpace = direction === 'horizontal'
                ? (last.plan_x + last.plan_width) - first.plan_x
                : (last.plan_y + last.plan_height) - first.plan_y;

            const totalBoxSize = sorted.reduce((sum, box) => {
                return sum + (direction === 'horizontal' ? box.plan_width : box.plan_height);
            }, 0);

            const gap = (totalSpace - totalBoxSize) / (sorted.length - 1);

            let currentPos = direction === 'horizontal' ? first.plan_x : first.plan_y;

            sorted.forEach(box => {
                if (direction === 'horizontal') {
                    box.plan_x = currentPos;
                    currentPos += box.plan_width + gap;
                } else {
                    box.plan_y = currentPos;
                    currentPos += box.plan_height + gap;
                }
            });

            this.saveState();
        },

        duplicateBox(box) {
            const newBox = {
                ...box,
                plan_x: box.plan_x + 20,
                plan_y: box.plan_y + 20
            };
            this.localBoxes.push(newBox);
            this.selectedBoxes = [newBox];
            this.saveState();
        },

        removeFromPlan(box) {
            box.plan_x = null;
            box.plan_y = null;
            this.selectedBoxes = this.selectedBoxes.filter(b => b.id !== box.id);
            this.saveState();
        },

        editBoxProperties(box) {
            this.selectBox(box);
        },

        saveState() {
            const state = JSON.stringify(this.localBoxes.map(b => ({
                id: b.id,
                plan_x: b.plan_x,
                plan_y: b.plan_y,
                plan_width: b.plan_width,
                plan_height: b.plan_height,
                rotation: b.rotation
            })));

            // Supprimer l'historique après l'index actuel
            this.history = this.history.slice(0, this.historyIndex + 1);

            // Ajouter le nouvel état
            this.history.push(state);

            // Limiter la taille de l'historique
            if (this.history.length > this.maxHistory) {
                this.history.shift();
            } else {
                this.historyIndex++;
            }
        },

        undo() {
            if (this.canUndo) {
                this.historyIndex--;
                this.restoreState(this.history[this.historyIndex]);
            }
        },

        redo() {
            if (this.canRedo) {
                this.historyIndex++;
                this.restoreState(this.history[this.historyIndex]);
            }
        },

        restoreState(state) {
            const savedBoxes = JSON.parse(state);
            savedBoxes.forEach(saved => {
                const box = this.localBoxes.find(b => b.id === saved.id);
                if (box) {
                    Object.assign(box, saved);
                }
            });
        },

        saveLayout() {
            const boxesData = this.localBoxes
                .filter(box => box.plan_x !== null && box.plan_y !== null)
                .map(box => ({
                    id: box.id,
                    plan_x: box.plan_x,
                    plan_y: box.plan_y,
                    plan_width: box.plan_width,
                    plan_height: box.plan_height
                }));

            this.$emit('save', {
                emplacement_id: this.emplacementId,
                background_image: this.backgroundImage,
                canvas_width: this.canvasWidth,
                canvas_height: this.canvasHeight,
                boxes: boxesData
            });
        },

        resetLayout() {
            if (confirm('Êtes-vous sûr de vouloir réinitialiser le plan ?')) {
                this.localBoxes.forEach(box => {
                    box.plan_x = null;
                    box.plan_y = null;
                    box.rotation = 0;
                });
                this.backgroundImage = null;
                this.selectedBoxes = [];
                this.saveState();
            }
        }
    }
};
</script>

<style scoped>
.plan-editor-container {
    position: relative;
}

.canvas-wrapper {
    overflow: auto;
    max-height: 600px;
    background: #f8f9fa;
    border-radius: 4px;
}

.canvas {
    position: relative;
    background-color: white;
    border: 2px solid #dee2e6;
    margin: 10px;
}

.grid-overlay {
    position: absolute;
    top: 0;
    left: 0;
    pointer-events: none;
}

.selection-box {
    position: absolute;
    border: 2px dashed #0d6efd;
    background: rgba(13, 110, 253, 0.1);
    pointer-events: none;
}

.box-element {
    position: absolute;
    border: 2px solid #6c757d;
    background: rgba(255, 255, 255, 0.9);
    cursor: move;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    transition: box-shadow 0.2s;
    overflow: visible;
}

.box-element:hover {
    box-shadow: 0 0 10px rgba(0,0,0,0.3);
    z-index: 10;
}

.box-element.selected {
    border-color: #0d6efd;
    border-width: 3px;
    box-shadow: 0 0 15px rgba(13, 110, 253, 0.5);
    z-index: 100;
}

.box-element.libre {
    background: rgba(25, 135, 84, 0.15);
    border-color: #198754;
}

.box-element.occupe {
    background: rgba(220, 53, 69, 0.15);
    border-color: #dc3545;
}

.box-element.reserve {
    background: rgba(255, 193, 7, 0.15);
    border-color: #ffc107;
}

.box-element.maintenance {
    background: rgba(13, 202, 240, 0.15);
    border-color: #0dcaf0;
}

.box-label {
    font-size: 14px;
    font-weight: bold;
    text-align: center;
    pointer-events: none;
}

.box-dimensions {
    font-size: 10px;
    text-align: center;
    color: #6c757d;
    pointer-events: none;
}

.box-status-icon {
    position: absolute;
    top: 4px;
    right: 4px;
    font-size: 16px;
    pointer-events: none;
}

.resize-handle {
    position: absolute;
    width: 10px;
    height: 10px;
    background: #0d6efd;
    border: 1px solid white;
    z-index: 101;
}

.resize-handle.nw { top: -5px; left: -5px; cursor: nw-resize; }
.resize-handle.ne { top: -5px; right: -5px; cursor: ne-resize; }
.resize-handle.sw { bottom: -5px; left: -5px; cursor: sw-resize; }
.resize-handle.se { bottom: -5px; right: -5px; cursor: se-resize; }
.resize-handle.n { top: -5px; left: 50%; margin-left: -5px; cursor: n-resize; }
.resize-handle.s { bottom: -5px; left: 50%; margin-left: -5px; cursor: s-resize; }
.resize-handle.e { top: 50%; right: -5px; margin-top: -5px; cursor: e-resize; }
.resize-handle.w { top: 50%; left: -5px; margin-top: -5px; cursor: w-resize; }

.rotate-handle {
    position: absolute;
    top: -30px;
    left: 50%;
    transform: translateX(-50%);
    width: 24px;
    height: 24px;
    background: #ffc107;
    border: 2px solid white;
    border-radius: 50%;
    cursor: grab;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 12px;
    z-index: 102;
}

.rotate-handle:hover {
    background: #ffca2c;
}

.minimap {
    max-width: 100%;
}

.minimap-canvas {
    position: relative;
    width: 100%;
    height: 120px;
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center;
    border: 1px solid #dee2e6;
}

.minimap-box {
    position: absolute;
    border: 1px solid #333;
    opacity: 0.7;
}

.minimap-box.libre { background: #198754; }
.minimap-box.occupe { background: #dc3545; }
.minimap-box.reserve { background: #ffc107; }
.minimap-box.maintenance { background: #0dcaf0; }

.legend-color {
    width: 20px;
    height: 20px;
    border: 1px solid #dee2e6;
    margin-right: 8px;
    border-radius: 3px;
}
</style>
