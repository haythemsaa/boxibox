<template>
    <div class="container-fluid py-4">
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-drafting-compass me-2"></i>Éditeur de Plan Avancé
                    </h1>
                    <p class="text-muted">Créez et personnalisez le plan de vos emplacements avec des outils professionnels</p>
                </div>
                <div>
                    <a :href="route('boxes.plan')" class="btn btn-outline-secondary">
                        <i class="fas fa-eye me-1"></i>Voir le plan
                    </a>
                    <a :href="route('boxes.index')" class="btn btn-outline-primary ms-2">
                        <i class="fas fa-arrow-left me-1"></i>Retour aux boxes
                    </a>
                </div>
            </div>
        </div>

        <!-- Sélection de l'emplacement -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <label class="form-label mb-1">
                            <i class="fas fa-map-marker-alt me-1"></i>Emplacement à éditer
                        </label>
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
                    <div class="col-md-9">
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-lightbulb me-2"></i>
                            <strong>Nouveautés :</strong>
                            Dessinez des zones, ajoutez des murs, portes et annotations. Créez de nouvelles boxes directement sur le plan !
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Barre d'outils principale -->
        <div class="card mb-3">
            <div class="card-body py-2">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex gap-3 align-items-center flex-wrap">
                        <!-- Upload image -->
                        <div>
                            <input
                                type="file"
                                ref="fileInput"
                                @change="handleBackgroundUpload"
                                accept="image/*"
                                class="d-none"
                            />
                            <button @click="$refs.fileInput.click()" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-image me-1"></i>Charger plan
                            </button>
                        </div>

                        <!-- Zoom -->
                        <div class="d-flex align-items-center gap-2">
                            <button @click="zoom = Math.max(25, zoom - 25)" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-search-minus"></i>
                            </button>
                            <span class="badge bg-secondary px-3">{{ zoom }}%</span>
                            <button @click="zoom = Math.min(300, zoom + 25)" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-search-plus"></i>
                            </button>
                        </div>

                        <!-- Grille -->
                        <div class="form-check form-switch">
                            <input v-model="showGrid" type="checkbox" class="form-check-input" id="gridToggle">
                            <label class="form-check-label" for="gridToggle">
                                <i class="fas fa-th me-1"></i>Grille
                            </label>
                        </div>

                        <!-- Snap to grid -->
                        <div class="form-check form-switch">
                            <input v-model="snapToGrid" type="checkbox" class="form-check-input" id="snapToggle">
                            <label class="form-check-label" for="snapToggle">
                                <i class="fas fa-magnet me-1"></i>Magnétisme
                            </label>
                        </div>

                        <!-- Undo/Redo -->
                        <div class="btn-group btn-group-sm">
                            <button @click="undo" :disabled="!canUndo" class="btn btn-outline-secondary" title="Annuler (Ctrl+Z)">
                                <i class="fas fa-undo"></i>
                            </button>
                            <button @click="redo" :disabled="!canRedo" class="btn btn-outline-secondary" title="Rétablir (Ctrl+Y)">
                                <i class="fas fa-redo"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button @click="savePlan" class="btn btn-sm btn-success">
                            <i class="fas fa-save me-1"></i>Enregistrer
                        </button>
                        <button @click="resetPlan" class="btn btn-sm btn-danger">
                            <i class="fas fa-trash me-1"></i>Réinitialiser
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Outils de dessin (gauche) -->
            <div class="col-md-2">
                <DrawingTools
                    @tool-changed="handleToolChange"
                    @layer-changed="handleLayerChange"
                />
            </div>

            <!-- Canvas principal (centre) -->
            <div class="col-md-7">
                <div class="card">
                    <div class="card-body p-2">
                        <div class="canvas-container" @wheel="handleWheel">
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
                                    cursor: getCursor()
                                }"
                                @mousedown="handleCanvasMouseDown"
                                @mousemove="handleCanvasMouseMove"
                                @mouseup="handleCanvasMouseUp"
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
                                        <pattern id="grid" :width="gridSize" :height="gridSize" patternUnits="userSpaceOnUse">
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

                                <!-- Éléments dessinés: Zones/Salles -->
                                <div
                                    v-for="room in rooms"
                                    :key="'room-' + room.id"
                                    class="room-element"
                                    :style="{
                                        left: room.x + 'px',
                                        top: room.y + 'px',
                                        width: room.width + 'px',
                                        height: room.height + 'px',
                                        backgroundColor: room.color,
                                        border: '2px dashed ' + room.color,
                                        opacity: 0.3
                                    }"
                                >
                                    <div class="room-label">{{ room.name }}</div>
                                </div>

                                <!-- Éléments dessinés: Murs -->
                                <svg class="drawing-overlay" :width="canvasWidth" :height="canvasHeight">
                                    <line
                                        v-for="wall in walls"
                                        :key="'wall-' + wall.id"
                                        :x1="wall.x1"
                                        :y1="wall.y1"
                                        :x2="wall.x2"
                                        :y2="wall.y2"
                                        :stroke="wall.color"
                                        :stroke-width="wall.thickness"
                                        stroke-linecap="round"
                                    />

                                    <!-- Flèches -->
                                    <g v-for="arrow in arrows" :key="'arrow-' + arrow.id">
                                        <line
                                            :x1="arrow.x1"
                                            :y1="arrow.y1"
                                            :x2="arrow.x2"
                                            :y2="arrow.y2"
                                            :stroke="arrow.color"
                                            :stroke-width="arrow.thickness"
                                            stroke-linecap="round"
                                        />
                                        <polygon
                                            :points="getArrowHead(arrow)"
                                            :fill="arrow.color"
                                        />
                                    </g>
                                </svg>

                                <!-- Portes -->
                                <div
                                    v-for="door in doors"
                                    :key="'door-' + door.id"
                                    class="door-element"
                                    :style="{
                                        left: door.x + 'px',
                                        top: door.y + 'px',
                                        width: '60px',
                                        height: '10px',
                                        backgroundColor: '#8B4513',
                                        borderRadius: '2px'
                                    }"
                                ></div>

                                <!-- Textes/Annotations -->
                                <div
                                    v-for="text in texts"
                                    :key="'text-' + text.id"
                                    class="text-element"
                                    :style="{
                                        left: text.x + 'px',
                                        top: text.y + 'px',
                                        fontSize: text.size + 'px',
                                        color: text.color,
                                        fontWeight: 'bold'
                                    }"
                                >
                                    {{ text.content }}
                                </div>

                                <!-- Forme en cours de dessin -->
                                <div
                                    v-if="currentShape"
                                    class="drawing-shape"
                                    :style="getCurrentShapeStyle()"
                                ></div>

                                <!-- Boxes existantes -->
                                <div
                                    v-for="box in localBoxes"
                                    :key="'box-' + box.id"
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
                                >
                                    <div class="box-label">
                                        <strong>{{ box.numero }}</strong>
                                    </div>
                                    <div class="box-dimensions">
                                        <small>{{ box.surface }}m²</small>
                                    </div>
                                    <div class="box-status-icon">
                                        <i v-if="box.statut === 'libre'" class="fas fa-check-circle text-success"></i>
                                        <i v-else-if="box.statut === 'occupe'" class="fas fa-lock text-danger"></i>
                                        <i v-else-if="box.statut === 'reserve'" class="fas fa-clock text-warning"></i>
                                    </div>

                                    <!-- Resize handles -->
                                    <template v-if="isSelected(box) && currentTool === 'select'">
                                        <div
                                            v-for="handle in resizeHandles"
                                            :key="handle"
                                            :class="`resize-handle ${handle}`"
                                            @mousedown.stop="startResize(box, handle, $event)"
                                        ></div>
                                        <div
                                            class="rotate-handle"
                                            @mousedown.stop="startRotate(box, $event)"
                                        >
                                            <i class="fas fa-sync-alt"></i>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Info barre -->
                        <div class="bg-light p-2 mt-2 small">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Outil:</strong> {{ getToolName() }} |
                                    <strong>Position:</strong> X: {{ mouseX }}, Y: {{ mouseY }}
                                </div>
                                <div class="col-md-6 text-end">
                                    <span class="badge bg-primary">{{ localBoxes.length }} Boxes</span>
                                    <span class="badge bg-info ms-1">{{ rooms.length }} Zones</span>
                                    <span class="badge bg-secondary ms-1">{{ walls.length }} Murs</span>
                                    <span class="badge bg-warning ms-1">{{ texts.length }} Annotations</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mini carte -->
                <div class="card mt-2" v-if="backgroundImage">
                    <div class="card-body p-2">
                        <div class="minimap" :style="{ backgroundImage: `url(${backgroundImage})` }">
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

            <!-- Panneau propriétés (droite) -->
            <div class="col-md-3">
                <!-- Propriétés box sélectionnée -->
                <div class="card mb-3" v-if="selectedBoxes.length === 1">
                    <div class="card-header bg-primary text-white py-2">
                        <h6 class="mb-0">
                            <i class="fas fa-cog me-1"></i>Propriétés Box
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <label class="form-label small mb-1">Numéro</label>
                            <input
                                v-model="selectedBoxes[0].numero"
                                type="text"
                                class="form-control form-control-sm"
                                disabled
                            />
                        </div>
                        <div class="row">
                            <div class="col-6 mb-2">
                                <label class="form-label small mb-1">X</label>
                                <input
                                    v-model.number="selectedBoxes[0].plan_x"
                                    type="number"
                                    class="form-control form-control-sm"
                                    @change="saveState"
                                />
                            </div>
                            <div class="col-6 mb-2">
                                <label class="form-label small mb-1">Y</label>
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
                                    @change="saveState"
                                />
                            </div>
                            <div class="col-6 mb-2">
                                <label class="form-label small mb-1">Hauteur</label>
                                <input
                                    v-model.number="selectedBoxes[0].plan_height"
                                    type="number"
                                    class="form-control form-control-sm"
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
                                @change="saveState"
                            />
                        </div>
                        <hr>
                        <div class="d-grid gap-2">
                            <button @click="deleteBox(selectedBoxes[0])" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-trash me-1"></i>Supprimer
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Liste boxes disponibles -->
                <div class="card">
                    <div class="card-header bg-success text-white py-2">
                        <h6 class="mb-0">
                            <i class="fas fa-box me-1"></i>Boxes ({{ availableBoxes.length }})
                        </h6>
                    </div>
                    <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                        <div class="list-group list-group-flush">
                            <div
                                v-for="box in availableBoxes"
                                :key="'list-' + box.id"
                                class="list-group-item list-group-item-action p-2"
                                :class="{ 'active': isSelected(box) }"
                                @click="selectBox(box)"
                                style="cursor: pointer;"
                            >
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong>{{ box.numero }}</strong>
                                        <span class="badge ms-1" :class="{
                                            'bg-success': box.statut === 'libre',
                                            'bg-danger': box.statut === 'occupe',
                                            'bg-warning': box.statut === 'reserve'
                                        }">
                                            {{ box.statut }}
                                        </span>
                                    </div>
                                    <small class="text-muted">{{ box.surface }}m²</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Raccourcis -->
                <div class="card mt-3">
                    <div class="card-header py-2">
                        <h6 class="mb-0">
                            <i class="fas fa-keyboard me-1"></i>Raccourcis
                        </h6>
                    </div>
                    <div class="card-body p-2">
                        <small class="text-muted">
                            <strong>Ctrl+Z:</strong> Annuler<br>
                            <strong>Ctrl+Y:</strong> Rétablir<br>
                            <strong>Suppr:</strong> Supprimer<br>
                            <strong>Échap:</strong> Annuler outil<br>
                            <strong>Ctrl+Scroll:</strong> Zoom
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Message de succès -->
        <div v-if="showSuccess" class="alert alert-success alert-dismissible fade show mt-3 position-fixed top-0 end-0 m-3" style="z-index: 9999;">
            <i class="fas fa-check-circle me-2"></i>
            Plan enregistré avec succès !
            <button type="button" class="btn-close" @click="showSuccess = false"></button>
        </div>
    </div>
</template>

<script>
import DrawingTools from '@/Components/DrawingTools.vue';
import { router } from '@inertiajs/vue3';

export default {
    components: {
        DrawingTools
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
            localBoxes: [],
            backgroundImage: null,
            zoom: 100,
            showGrid: true,
            gridSize: 20,
            snapToGrid: true,
            canvasWidth: 1200,
            canvasHeight: 800,
            selectedBoxes: [],
            currentTool: 'select',
            toolOptions: {},

            // Drawing elements
            rooms: [],
            walls: [],
            doors: [],
            texts: [],
            arrows: [],

            // Drawing state
            isDrawing: false,
            currentShape: null,
            mouseX: 0,
            mouseY: 0,

            // Drag state
            dragging: false,
            resizing: false,
            rotating: false,
            dragStartX: 0,
            dragStartY: 0,
            currentBox: null,
            resizeHandles: ['nw', 'ne', 'sw', 'se', 'n', 's', 'e', 'w'],

            // History
            history: [],
            historyIndex: -1,
            maxHistory: 50,

            // UI
            showSuccess: false,

            // IDs
            nextBoxId: 1000,
            nextRoomId: 1,
            nextWallId: 1,
            nextDoorId: 1,
            nextTextId: 1,
            nextArrowId: 1
        };
    },

    computed: {
        availableBoxes() {
            return this.localBoxes.filter(box => box.emplacement_id === this.selectedEmplacementId);
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
        this.loadEmplacementPlan();
        this.saveState();

        document.addEventListener('keydown', this.handleKeyDown);
    },

    beforeUnmount() {
        document.removeEventListener('keydown', this.handleKeyDown);
    },

    methods: {
        initializeBoxes() {
            this.localBoxes = this.boxes.map(box => ({
                ...box,
                plan_x: box.plan_x || null,
                plan_y: box.plan_y || null,
                plan_width: box.plan_width || 100,
                plan_height: box.plan_height || 80,
                rotation: box.rotation || 0
            }));
        },

        loadEmplacementPlan() {
            const layout = this.planLayouts[this.selectedEmplacementId];
            this.backgroundImage = layout?.background_image || null;
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

        handleToolChange(data) {
            this.currentTool = data.tool;
            this.toolOptions = data.options;
            this.selectedBoxes = [];
        },

        handleLayerChange(layerId) {
            console.log('Layer changed:', layerId);
        },

        getCursor() {
            switch (this.currentTool) {
                case 'box':
                case 'room':
                    return 'crosshair';
                case 'wall':
                case 'arrow':
                    return 'crosshair';
                case 'text':
                    return 'text';
                case 'door':
                    return 'pointer';
                default:
                    return 'default';
            }
        },

        getToolName() {
            const names = {
                select: 'Sélection',
                box: 'Créer Box',
                room: 'Zone/Salle',
                wall: 'Mur',
                door: 'Porte',
                text: 'Texte',
                arrow: 'Flèche'
            };
            return names[this.currentTool] || 'Inconnu';
        },

        handleCanvasMouseDown(event) {
            if (event.target !== this.$refs.canvas && !event.target.classList.contains('grid-overlay')) {
                return;
            }

            const rect = this.$refs.canvas.getBoundingClientRect();
            const x = Math.round((event.clientX - rect.left) / (this.zoom / 100));
            const y = Math.round((event.clientY - rect.top) / (this.zoom / 100));

            if (this.currentTool === 'room' || this.currentTool === 'wall' || this.currentTool === 'arrow') {
                this.isDrawing = true;
                this.currentShape = {
                    startX: x,
                    startY: y,
                    endX: x,
                    endY: y
                };
            }
        },

        handleCanvasMouseMove(event) {
            const rect = this.$refs.canvas.getBoundingClientRect();
            this.mouseX = Math.round((event.clientX - rect.left) / (this.zoom / 100));
            this.mouseY = Math.round((event.clientY - rect.top) / (this.zoom / 100));

            if (this.isDrawing && this.currentShape) {
                this.currentShape.endX = this.mouseX;
                this.currentShape.endY = this.mouseY;
            }

            // Handle drag/resize/rotate
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
            }
        },

        handleCanvasMouseUp() {
            if (this.isDrawing && this.currentShape) {
                this.finalizeShape();
            }
            this.isDrawing = false;

            if (this.dragging || this.resizing || this.rotating) {
                this.saveState();
            }
            this.dragging = false;
            this.resizing = false;
            this.rotating = false;
        },

        handleCanvasClick(event) {
            if (event.target !== this.$refs.canvas && !event.target.classList.contains('grid-overlay')) {
                return;
            }

            const rect = this.$refs.canvas.getBoundingClientRect();
            const x = Math.round((event.clientX - rect.left) / (this.zoom / 100));
            const y = Math.round((event.clientY - rect.top) / (this.zoom / 100));

            if (this.currentTool === 'box') {
                this.createBox(x, y);
            } else if (this.currentTool === 'door') {
                this.createDoor(x, y);
            } else if (this.currentTool === 'text') {
                this.createText(x, y);
            } else if (this.currentTool === 'select') {
                this.selectedBoxes = [];
            }
        },

        createBox(x, y) {
            const width = this.toolOptions.width || 100;
            const height = this.toolOptions.height || 80;

            let finalX = x;
            let finalY = y;

            if (this.snapToGrid) {
                finalX = Math.round(x / this.gridSize) * this.gridSize;
                finalY = Math.round(y / this.gridSize) * this.gridSize;
            }

            const newBox = {
                id: this.nextBoxId++,
                numero: `BOX-${this.nextBoxId}`,
                statut: 'libre',
                surface: ((width / 20) * (height / 20)).toFixed(2),
                prix_mensuel: 50,
                plan_x: finalX,
                plan_y: finalY,
                plan_width: width,
                plan_height: height,
                rotation: 0,
                emplacement_id: this.selectedEmplacementId,
                famille: { nom: 'Nouvelle', couleur: '#6c757d' },
                emplacement: { nom: this.emplacements.find(e => e.id === this.selectedEmplacementId)?.nom }
            };

            this.localBoxes.push(newBox);
            this.selectedBoxes = [newBox];
            this.saveState();
        },

        createDoor(x, y) {
            this.doors.push({
                id: this.nextDoorId++,
                x,
                y
            });
            this.saveState();
        },

        createText(x, y) {
            if (!this.toolOptions.content) {
                alert('Veuillez saisir un texte dans les options');
                return;
            }

            this.texts.push({
                id: this.nextTextId++,
                x,
                y,
                content: this.toolOptions.content,
                size: this.toolOptions.size || 14,
                color: this.toolOptions.color || '#000000'
            });
            this.saveState();
        },

        finalizeShape() {
            const shape = this.currentShape;
            const x = Math.min(shape.startX, shape.endX);
            const y = Math.min(shape.startY, shape.endY);
            const width = Math.abs(shape.endX - shape.startX);
            const height = Math.abs(shape.endY - shape.startY);

            if (width < 10 || height < 10) {
                this.currentShape = null;
                return;
            }

            if (this.currentTool === 'room') {
                this.rooms.push({
                    id: this.nextRoomId++,
                    x,
                    y,
                    width,
                    height,
                    name: this.toolOptions.name || `Zone ${this.nextRoomId}`,
                    color: this.toolOptions.color || '#e3f2fd'
                });
            } else if (this.currentTool === 'wall') {
                this.walls.push({
                    id: this.nextWallId++,
                    x1: shape.startX,
                    y1: shape.startY,
                    x2: shape.endX,
                    y2: shape.endY,
                    thickness: this.toolOptions.thickness || 4,
                    color: this.toolOptions.color || '#333333'
                });
            } else if (this.currentTool === 'arrow') {
                this.arrows.push({
                    id: this.nextArrowId++,
                    x1: shape.startX,
                    y1: shape.startY,
                    x2: shape.endX,
                    y2: shape.endY,
                    thickness: this.toolOptions.thickness || 2,
                    color: this.toolOptions.color || '#dc3545'
                });
            }

            this.currentShape = null;
            this.saveState();
        },

        getCurrentShapeStyle() {
            if (!this.currentShape) return {};

            const x = Math.min(this.currentShape.startX, this.currentShape.endX);
            const y = Math.min(this.currentShape.startY, this.currentShape.endY);
            const width = Math.abs(this.currentShape.endX - this.currentShape.startX);
            const height = Math.abs(this.currentShape.endY - this.currentShape.startY);

            return {
                position: 'absolute',
                left: x + 'px',
                top: y + 'px',
                width: width + 'px',
                height: height + 'px',
                border: '2px dashed #0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                pointerEvents: 'none'
            };
        },

        getArrowHead(arrow) {
            const angle = Math.atan2(arrow.y2 - arrow.y1, arrow.x2 - arrow.x1);
            const headLength = 15;
            const headAngle = Math.PI / 6;

            const x1 = arrow.x2 - headLength * Math.cos(angle - headAngle);
            const y1 = arrow.y2 - headLength * Math.sin(angle - headAngle);
            const x2 = arrow.x2 - headLength * Math.cos(angle + headAngle);
            const y2 = arrow.y2 - headLength * Math.sin(angle + headAngle);

            return `${arrow.x2},${arrow.y2} ${x1},${y1} ${x2},${y2}`;
        },

        selectBox(box, event = null) {
            if (this.currentTool !== 'select') return;

            if (event && event.ctrlKey) {
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
            if (this.currentTool !== 'select') return;

            if (!this.isSelected(box)) {
                this.selectBox(box, event);
            }

            this.dragging = true;
            this.dragStartX = event.clientX;
            this.dragStartY = event.clientY;

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

        deleteBox(box) {
            if (confirm(`Supprimer la box ${box.numero} ?`)) {
                const index = this.localBoxes.findIndex(b => b.id === box.id);
                if (index > -1) {
                    this.localBoxes.splice(index, 1);
                    this.selectedBoxes = [];
                    this.saveState();
                }
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
            if (event.ctrlKey && event.key === 'z') {
                event.preventDefault();
                this.undo();
            } else if (event.ctrlKey && event.key === 'y') {
                event.preventDefault();
                this.redo();
            } else if (event.key === 'Escape') {
                this.currentTool = 'select';
                this.isDrawing = false;
                this.currentShape = null;
            } else if (event.key === 'Delete' && this.selectedBoxes.length > 0) {
                this.selectedBoxes.forEach(box => this.deleteBox(box));
            }
        },

        saveState() {
            const state = JSON.stringify({
                boxes: this.localBoxes.map(b => ({
                    id: b.id,
                    plan_x: b.plan_x,
                    plan_y: b.plan_y,
                    plan_width: b.plan_width,
                    plan_height: b.plan_height,
                    rotation: b.rotation
                })),
                rooms: this.rooms,
                walls: this.walls,
                doors: this.doors,
                texts: this.texts,
                arrows: this.arrows
            });

            this.history = this.history.slice(0, this.historyIndex + 1);
            this.history.push(state);

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
            const saved = JSON.parse(state);

            saved.boxes.forEach(savedBox => {
                const box = this.localBoxes.find(b => b.id === savedBox.id);
                if (box) {
                    Object.assign(box, savedBox);
                }
            });

            this.rooms = saved.rooms || [];
            this.walls = saved.walls || [];
            this.doors = saved.doors || [];
            this.texts = saved.texts || [];
            this.arrows = saved.arrows || [];
        },

        savePlan() {
            const boxesData = this.localBoxes
                .filter(box => box.plan_x !== null && box.plan_y !== null)
                .map(box => ({
                    id: box.id,
                    plan_x: box.plan_x,
                    plan_y: box.plan_y,
                    plan_width: box.plan_width,
                    plan_height: box.plan_height
                }));

            router.post(route('boxes.plan.save'), {
                emplacement_id: this.selectedEmplacementId,
                background_image: this.backgroundImage,
                canvas_width: this.canvasWidth,
                canvas_height: this.canvasHeight,
                boxes: boxesData,
                rooms: this.rooms,
                walls: this.walls,
                doors: this.doors,
                texts: this.texts,
                arrows: this.arrows
            }, {
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
        },

        resetPlan() {
            if (confirm('Êtes-vous sûr de vouloir réinitialiser le plan ?')) {
                this.localBoxes.forEach(box => {
                    box.plan_x = null;
                    box.plan_y = null;
                });
                this.rooms = [];
                this.walls = [];
                this.doors = [];
                this.texts = [];
                this.arrows = [];
                this.backgroundImage = null;
                this.selectedBoxes = [];
                this.saveState();
            }
        }
    }
};
</script>

<style scoped>
.canvas-container {
    overflow: auto;
    max-height: 700px;
    background: #f8f9fa;
    border-radius: 4px;
}

.canvas {
    position: relative;
    background-color: white;
    border: 2px solid #dee2e6;
    margin: 10px;
}

.grid-overlay,
.drawing-overlay {
    position: absolute;
    top: 0;
    left: 0;
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

.box-label {
    font-size: 14px;
    font-weight: bold;
    pointer-events: none;
}

.box-dimensions {
    font-size: 10px;
    color: #6c757d;
    pointer-events: none;
}

.box-status-icon {
    position: absolute;
    top: 4px;
    right: 4px;
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

.room-element {
    position: absolute;
    pointer-events: none;
}

.room-label {
    position: absolute;
    top: 5px;
    left: 5px;
    font-weight: bold;
    color: #333;
    font-size: 14px;
}

.door-element,
.text-element {
    position: absolute;
    pointer-events: none;
}

.minimap {
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
</style>
