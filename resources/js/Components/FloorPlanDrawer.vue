<template>
    <div class="floor-plan-drawer">
        <div class="row">
            <!-- Panneau latéral gauche avec liste des boxes -->
            <div class="col-md-3">
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-th-large me-2"></i>Familles de Boxes
                        </h6>
                    </div>
                    <div class="card-body p-2" style="max-height: 600px; overflow-y: auto;">
                        <div v-for="famille in familles" :key="famille.id" class="mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <strong class="small text-primary">{{ famille.nom }}</strong>
                                <span class="badge bg-info">{{ famille.surface_min }}-{{ famille.surface_max }}m²</span>
                            </div>
                            <div
                                class="box-template p-2 mb-2 border rounded bg-light"
                                draggable="true"
                                @dragstart="handleDragStart($event, famille)"
                                style="cursor: move;"
                            >
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-grip-vertical text-muted me-2"></i>
                                    <div class="flex-grow-1">
                                        <div class="small fw-bold">{{ famille.nom }}</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">
                                            {{ famille.surface_min }}m² - {{ famille.prix_base }}€/mois
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Liste des boxes placées -->
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-boxes me-2"></i>Boxes Placées ({{ boxes.length }})
                        </h6>
                    </div>
                    <div class="card-body p-2" style="max-height: 300px; overflow-y: auto;">
                        <div v-if="boxes.length === 0" class="text-muted text-center small py-3">
                            Aucune box placée
                        </div>
                        <div
                            v-for="box in boxes"
                            :key="box.id"
                            class="box-item p-2 mb-2 border rounded"
                            :class="{'border-primary bg-light': selectedBox?.id === box.id}"
                            @click="selectBoxFromList(box)"
                            style="cursor: pointer;"
                        >
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="small fw-bold">{{ box.numero }}</div>
                                    <div class="text-muted" style="font-size: 0.7rem;">
                                        {{ box.surface }}m² - {{ box.prix_mensuel }}€
                                    </div>
                                </div>
                                <span
                                    class="badge"
                                    :class="{
                                        'bg-success': box.statut === 'libre',
                                        'bg-danger': box.statut === 'occupé',
                                        'bg-warning': box.statut === 'réservé',
                                        'bg-info': box.statut === 'maintenance'
                                    }"
                                >
                                    {{ box.statut }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Zone de dessin principale -->
            <div class="col-md-9">
                <!-- Barre d'outils principale -->
                <div class="toolbar card mb-3">
            <div class="card-body py-2">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" id="tool-pen" v-model="currentTool" value="pen" autocomplete="off">
                            <label class="btn btn-outline-primary" for="tool-pen" title="Dessiner le plan à main levée">
                                <i class="fas fa-pen me-1"></i>Stylo
                            </label>

                            <input type="radio" class="btn-check" id="tool-polygon" v-model="currentTool" value="polygon" autocomplete="off">
                            <label class="btn btn-outline-primary" for="tool-polygon" title="Dessiner un polygone (cliquer pour ajouter des points)">
                                <i class="fas fa-draw-polygon me-1"></i>Polygone
                            </label>

                            <input type="radio" class="btn-check" id="tool-rect" v-model="currentTool" value="rectangle" autocomplete="off">
                            <label class="btn btn-outline-primary" for="tool-rect" title="Dessiner un rectangle">
                                <i class="fas fa-square me-1"></i>Rectangle
                            </label>

                            <input type="radio" class="btn-check" id="tool-lshape" v-model="currentTool" value="lshape" autocomplete="off">
                            <label class="btn btn-outline-primary" for="tool-lshape" title="Dessiner une forme en L">
                                <i class="fas fa-vector-square me-1"></i>Forme L
                            </label>

                            <input type="radio" class="btn-check" id="tool-box" v-model="currentTool" value="box" autocomplete="off">
                            <label class="btn btn-outline-success" for="tool-box" title="Placer une box">
                                <i class="fas fa-cube me-1"></i>Placer Box
                            </label>

                            <input type="radio" class="btn-check" id="tool-measure" v-model="currentTool" value="measure" autocomplete="off">
                            <label class="btn btn-outline-warning" for="tool-measure" title="Mesurer une distance">
                                <i class="fas fa-ruler me-1"></i>Mesure
                            </label>

                            <input type="radio" class="btn-check" id="tool-edit" v-model="currentTool" value="edit" autocomplete="off">
                            <label class="btn btn-outline-info" for="tool-edit" title="Éditer le plan">
                                <i class="fas fa-edit me-1"></i>Éditer Plan
                            </label>

                            <input type="radio" class="btn-check" id="tool-select" v-model="currentTool" value="select" autocomplete="off">
                            <label class="btn btn-outline-secondary" for="tool-select" title="Sélectionner">
                                <i class="fas fa-mouse-pointer me-1"></i>Sélection
                            </label>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="d-flex align-items-center gap-2">
                            <label class="small mb-0">Trait:</label>
                            <input v-model.number="strokeWidth" type="number" min="1" max="10" class="form-control form-control-sm" style="width: 50px;">
                            <input v-model="strokeColor" type="color" class="form-control form-control-sm" style="width: 40px;" title="Couleur">
                        </div>
                        <div class="form-check form-check-inline small mt-1">
                            <input class="form-check-input" type="checkbox" id="dashedLine" v-model="isDashed">
                            <label class="form-check-label" for="dashedLine">Pointillé</label>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="d-flex align-items-center gap-2">
                            <label class="small mb-0">Échelle:</label>
                            <input v-model.number="echelle" type="number" step="0.01" min="0.01" max="1" class="form-control form-control-sm" style="width: 60px;" title="Mètres par pixel">
                            <span class="small text-muted">m/px</span>
                        </div>
                    </div>

                    <div class="col-md-2 text-end">
                        <button
                            v-if="selectedShapeIndex !== null"
                            @click="deleteSelectedShape"
                            class="btn btn-sm btn-outline-warning me-1"
                            title="Supprimer la forme sélectionnée"
                        >
                            <i class="fas fa-trash"></i>
                        </button>
                        <button @click="clearPlan" class="btn btn-sm btn-outline-danger me-1" title="Effacer toutes les formes">
                            <i class="fas fa-eraser"></i>
                        </button>
                        <button @click="savePlan" class="btn btn-sm btn-success" title="Sauvegarder">
                            <i class="fas fa-save me-1"></i>Sauvegarder
                        </button>
                    </div>
                </div>

                <!-- Options pour placer box -->
                <div v-if="currentTool === 'box'" class="row mt-2 pt-2 border-top">
                    <div class="col-md-12">
                        <div class="d-flex gap-3 align-items-center">
                            <span class="small text-muted">Taille de la box:</span>
                            <div class="btn-group btn-group-sm" role="group">
                                <input type="radio" class="btn-check" id="size-small" v-model="boxSize" value="small" autocomplete="off">
                                <label class="btn btn-outline-info" for="size-small">
                                    Petit (60x50)
                                </label>
                                <input type="radio" class="btn-check" id="size-medium" v-model="boxSize" value="medium" autocomplete="off" checked>
                                <label class="btn btn-outline-info" for="size-medium">
                                    Moyen (100x80)
                                </label>
                                <input type="radio" class="btn-check" id="size-large" v-model="boxSize" value="large" autocomplete="off">
                                <label class="btn btn-outline-info" for="size-large">
                                    Grand (140x100)
                                </label>
                                <input type="radio" class="btn-check" id="size-xlarge" v-model="boxSize" value="xlarge" autocomplete="off">
                                <label class="btn btn-outline-info" for="size-xlarge">
                                    Très Grand (180x120)
                                </label>
                                <input type="radio" class="btn-check" id="size-custom" v-model="boxSize" value="custom" autocomplete="off">
                                <label class="btn btn-outline-info" for="size-custom">
                                    Personnalisé
                                </label>
                            </div>

                            <div v-if="boxSize === 'custom'" class="d-flex gap-2 align-items-center">
                                <input v-model.number="customBoxWidth" type="number" min="30" max="300" class="form-control form-control-sm" style="width: 70px;" placeholder="Largeur">
                                <span class="small">x</span>
                                <input v-model.number="customBoxHeight" type="number" min="30" max="300" class="form-control form-control-sm" style="width: 70px;" placeholder="Hauteur">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Canvas de dessin -->
        <div class="drawing-area card">
            <div class="card-body p-2">
                <!-- Indicateur d'échelle visuelle -->
                <div class="d-flex justify-content-between align-items-center mb-2 px-2">
                    <div class="d-flex align-items-center gap-3">
                        <div class="scale-indicator">
                            <svg width="120" height="30">
                                <line x1="10" y1="15" x2="110" y2="15" stroke="#333" stroke-width="2"/>
                                <line x1="10" y1="10" x2="10" y2="20" stroke="#333" stroke-width="2"/>
                                <line x1="110" y1="10" x2="110" y2="20" stroke="#333" stroke-width="2"/>
                                <text x="60" y="28" text-anchor="middle" font-size="11" fill="#333">
                                    {{ formatDistance(100 * echelle) }}
                                </text>
                            </svg>
                        </div>
                        <small class="text-muted">1 pixel = {{ (echelle * 100).toFixed(1) }} cm</small>
                    </div>
                    <div v-if="measurementResult" class="alert alert-warning mb-0 py-1 px-2 small">
                        <i class="fas fa-ruler me-1"></i>
                        Distance: <strong>{{ measurementResult }}</strong>
                    </div>
                </div>

                <div
                    class="canvas-wrapper"
                    @dragover.prevent
                    @drop="handleDrop"
                >
                    <svg
                        ref="canvas"
                        class="drawing-canvas"
                        :width="canvasWidth"
                        :height="canvasHeight"
                        @mousedown="handleMouseDown"
                        @mousemove="handleMouseMove"
                        @mouseup="handleMouseUp"
                        @click="handleClick"
                        :style="{ cursor: getCursor() }"
                    >
                        <!-- Grille de fond -->
                        <defs>
                            <pattern id="grid" width="20" height="20" patternUnits="userSpaceOnUse">
                                <path d="M 20 0 L 0 0 0 20" fill="none" stroke="#e0e0e0" stroke-width="0.5"/>
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" fill="url(#grid)" />

                        <!-- Plans dessinés (plusieurs formes possibles) -->
                        <g v-for="(shape, shapeIndex) in floorPlanShapes" :key="'shape-' + shapeIndex">
                            <path
                                :d="getPathD(shape.points)"
                                :fill="selectedShapeIndex === shapeIndex ? 'rgba(255, 193, 7, 0.3)' : 'rgba(173, 216, 230, 0.2)'"
                                :stroke="shape.color || strokeColor"
                                :stroke-width="shape.width || strokeWidth"
                                :stroke-dasharray="shape.dashed ? '10,5' : '0'"
                                stroke-linejoin="round"
                                stroke-linecap="round"
                                @click="selectShape(shapeIndex)"
                                :class="{ 'shape-selected': selectedShapeIndex === shapeIndex }"
                                style="cursor: pointer;"
                            />
                            <!-- Points du tracé - visibles en mode édition -->
                            <g v-if="currentTool === 'edit' && editingShapeIndex === shapeIndex">
                                <circle
                                    v-for="(point, index) in shape.points"
                                    :key="'point-' + shapeIndex + '-' + index"
                                    :cx="point.x"
                                    :cy="point.y"
                                    r="6"
                                    :fill="editingPointIndex === index ? '#dc3545' : '#0d6efd'"
                                    stroke="white"
                                    stroke-width="2"
                                    class="path-point editable"
                                    style="cursor: move;"
                                    @mousedown.stop="startDragPoint(shapeIndex, index, $event)"
                                    @contextmenu.prevent="removePoint(shapeIndex, index)"
                                />
                            </g>
                        </g>

                        <!-- Tracé en cours -->
                        <path
                            v-if="currentPath.length > 0"
                            :d="getPathD(currentPath)"
                            fill="none"
                            :stroke="strokeColor"
                            :stroke-width="strokeWidth"
                            :stroke-dasharray="isDashed ? '10,5' : '0'"
                            stroke-linejoin="round"
                            stroke-linecap="round"
                            opacity="0.7"
                        />

                        <!-- Ligne temporaire pour polygone -->
                        <line
                            v-if="currentTool === 'polygon' && currentPath.length > 0 && tempPoint"
                            :x1="currentPath[currentPath.length - 1].x"
                            :y1="currentPath[currentPath.length - 1].y"
                            :x2="tempPoint.x"
                            :y2="tempPoint.y"
                            stroke="#0d6efd"
                            stroke-width="2"
                            stroke-dasharray="5,5"
                        />

                        <!-- Rectangle en cours de dessin -->
                        <rect
                            v-if="currentTool === 'rectangle' && drawingRect"
                            :x="Math.min(drawingRect.startX, drawingRect.endX)"
                            :y="Math.min(drawingRect.startY, drawingRect.endY)"
                            :width="Math.abs(drawingRect.endX - drawingRect.startX)"
                            :height="Math.abs(drawingRect.endY - drawingRect.startY)"
                            fill="rgba(173, 216, 230, 0.2)"
                            :stroke="strokeColor"
                            :stroke-width="strokeWidth"
                        />

                        <!-- Boxes placées -->
                        <g v-for="box in boxes" :key="'box-' + box.id">
                            <rect
                                :x="box.x"
                                :y="box.y"
                                :width="box.width"
                                :height="box.height"
                                :fill="getBoxColor(box.statut)"
                                :stroke="getBoxStroke(box.statut)"
                                stroke-width="2"
                                rx="4"
                                class="box-rect"
                                :class="{ 'selected': selectedBox?.id === box.id }"
                                @click.stop="selectBox(box)"
                            />
                            <text
                                :x="box.x + box.width / 2"
                                :y="box.y + box.height / 2"
                                text-anchor="middle"
                                dominant-baseline="middle"
                                class="box-label"
                                font-size="12"
                                font-weight="bold"
                                fill="#333"
                            >
                                {{ box.numero }}
                            </text>
                            <text
                                :x="box.x + box.width / 2"
                                :y="box.y + box.height / 2 + 14"
                                text-anchor="middle"
                                dominant-baseline="middle"
                                font-size="10"
                                fill="#666"
                            >
                                {{ box.surface }}m²
                            </text>

                            <!-- Poignées de redimensionnement -->
                            <g v-if="selectedBox?.id === box.id">
                                <rect
                                    :x="box.x - 4"
                                    :y="box.y - 4"
                                    width="8"
                                    height="8"
                                    fill="#0d6efd"
                                    stroke="white"
                                    class="resize-handle"
                                    style="cursor: nw-resize"
                                    @mousedown.stop="startResize(box, 'nw', $event)"
                                />
                                <rect
                                    :x="box.x + box.width - 4"
                                    :y="box.y - 4"
                                    width="8"
                                    height="8"
                                    fill="#0d6efd"
                                    stroke="white"
                                    class="resize-handle"
                                    style="cursor: ne-resize"
                                    @mousedown.stop="startResize(box, 'ne', $event)"
                                />
                                <rect
                                    :x="box.x - 4"
                                    :y="box.y + box.height - 4"
                                    width="8"
                                    height="8"
                                    fill="#0d6efd"
                                    stroke="white"
                                    class="resize-handle"
                                    style="cursor: sw-resize"
                                    @mousedown.stop="startResize(box, 'sw', $event)"
                                />
                                <rect
                                    :x="box.x + box.width - 4"
                                    :y="box.y + box.height - 4"
                                    width="8"
                                    height="8"
                                    fill="#0d6efd"
                                    stroke="white"
                                    class="resize-handle"
                                    style="cursor: se-resize"
                                    @mousedown.stop="startResize(box, 'se', $event)"
                                />
                            </g>
                        </g>

                        <!-- Ligne de mesure -->
                        <g v-if="currentTool === 'measure'">
                            <line
                                v-if="measureLine.start"
                                :x1="measureLine.start.x"
                                :y1="measureLine.start.y"
                                :x2="measureLine.end ? measureLine.end.x : (tempPoint ? tempPoint.x : measureLine.start.x)"
                                :y2="measureLine.end ? measureLine.end.y : (tempPoint ? tempPoint.y : measureLine.start.y)"
                                stroke="#ff9800"
                                stroke-width="3"
                                stroke-dasharray="5,5"
                            />
                            <circle
                                v-if="measureLine.start"
                                :cx="measureLine.start.x"
                                :cy="measureLine.start.y"
                                r="6"
                                fill="#ff9800"
                                stroke="white"
                                stroke-width="2"
                            />
                            <circle
                                v-if="measureLine.end"
                                :cx="measureLine.end.x"
                                :cy="measureLine.end.y"
                                r="6"
                                fill="#ff9800"
                                stroke="white"
                                stroke-width="2"
                            />
                        </g>

                        <!-- Prévisualisation de la box à placer -->
                        <rect
                            v-if="currentTool === 'box' && previewBox"
                            :x="previewBox.x"
                            :y="previewBox.y"
                            :width="previewBox.width"
                            :height="previewBox.height"
                            fill="rgba(25, 135, 84, 0.3)"
                            stroke="#198754"
                            stroke-width="2"
                            stroke-dasharray="5,5"
                            pointer-events="none"
                        />
                    </svg>
                </div>

                <!-- Info barre -->
                <div class="info-bar mt-2 p-2 bg-light small">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Outil:</strong> {{ getToolName() }} |
                            <strong>Position:</strong> {{ mouseX }}, {{ mouseY }}
                            <span v-if="currentTool === 'polygon' && currentPath.length > 0" class="text-info ms-2">
                                <i class="fas fa-info-circle"></i> Double-clic pour terminer
                            </span>
                            <span v-if="currentTool === 'edit'" class="text-warning ms-2">
                                <i class="fas fa-info-circle"></i> Glissez les points | Clic droit pour supprimer
                            </span>
                        </div>
                        <div class="col-md-6 text-end">
                            <span class="badge bg-secondary">{{ floorPlanShapes.length }} Forme(s)</span>
                            <span class="badge bg-primary ms-1">{{ boxes.length }} Boxes</span>
                            <span class="badge bg-info ms-1">Surface: {{ totalSurface }}m²</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panneau propriétés box sélectionnée -->
        <div v-if="selectedBox" class="card mt-3">
            <div class="card-header bg-primary text-white py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-cog me-1"></i>Propriétés: {{ selectedBox.numero }}
                    </h6>
                    <button @click="deleteBox(selectedBox)" class="btn btn-sm btn-danger">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label small">Numéro</label>
                        <input v-model="selectedBox.numero" type="text" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">X</label>
                        <input v-model.number="selectedBox.x" type="number" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Y</label>
                        <input v-model.number="selectedBox.y" type="number" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Largeur</label>
                        <input v-model.number="selectedBox.width" type="number" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Hauteur</label>
                        <input v-model.number="selectedBox.height" type="number" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-4">
                        <label class="form-label small">Surface (m²)</label>
                        <input v-model.number="selectedBox.surface" type="number" step="0.1" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Prix mensuel (€)</label>
                        <input v-model.number="selectedBox.prix_mensuel" type="number" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Statut</label>
                        <select v-model="selectedBox.statut" class="form-select form-select-sm">
                            <option value="libre">Libre</option>
                            <option value="occupe">Occupé</option>
                            <option value="reserve">Réservé</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        emplacementId: {
            type: Number,
            required: true
        },
        familles: {
            type: Array,
            default: () => []
        },
        initialFloorPlan: {
            type: Array,
            default: () => []
        },
        initialBoxes: {
            type: Array,
            default: () => []
        },
        initialEchelle: {
            type: Number,
            default: 0.05
        }
    },

    data() {
        return {
            currentTool: 'pen',
            canvasWidth: 1200,
            canvasHeight: 800,
            strokeWidth: 3,
            strokeColor: '#333333',
            isDashed: false,

            // Dessin du plan - maintenant supporte plusieurs formes
            floorPlanShapes: Array.isArray(this.initialFloorPlan) && this.initialFloorPlan.length > 0 && typeof this.initialFloorPlan[0] === 'object' && this.initialFloorPlan[0].points
                ? [...this.initialFloorPlan]  // Nouveau format: tableau d'objets avec points
                : (this.initialFloorPlan.length > 0 ? [{ points: [...this.initialFloorPlan], color: '#333333', width: 3, dashed: false }] : []), // Ancien format: converti
            currentPath: [],
            isDrawing: false,
            tempPoint: null,
            drawingRect: null,
            selectedShapeIndex: null,

            // Édition du plan
            editingPointIndex: null,
            draggingPoint: false,
            editingShapeIndex: null,

            // Boxes
            boxes: [...this.initialBoxes],
            selectedBox: null,
            boxSize: 'medium',
            customBoxWidth: 100,
            customBoxHeight: 80,
            previewBox: null,
            nextBoxId: (this.initialBoxes.length > 0 ? Math.max(...this.initialBoxes.map(b => b.id || 0)) : 0) + 1,

            // Échelle
            echelle: this.initialEchelle, // mètres par pixel

            // Mesure
            measureLine: {
                start: null,
                end: null
            },
            measurementResult: null,

            // Drag-drop
            draggedFamille: null,

            // Resize
            resizing: false,
            resizeHandle: null,
            resizeStartX: 0,
            resizeStartY: 0,

            // Mouse
            mouseX: 0,
            mouseY: 0
        };
    },

    computed: {
        totalSurface() {
            return this.boxes.reduce((sum, box) => sum + parseFloat(box.surface || 0), 0).toFixed(2);
        }
    },

    methods: {
        getCursor() {
            if (this.currentTool === 'pen' || this.currentTool === 'polygon') return 'crosshair';
            if (this.currentTool === 'box') return 'copy';
            if (this.currentTool === 'measure') return 'crosshair';
            if (this.currentTool === 'edit') return 'move';
            if (this.currentTool === 'rectangle' || this.currentTool === 'lshape') return 'crosshair';
            return 'default';
        },

        getToolName() {
            const names = {
                pen: 'Stylo (dessin libre)',
                polygon: 'Polygone',
                rectangle: 'Rectangle',
                lshape: 'Forme en L',
                box: 'Placer Box',
                measure: 'Mesure de distance',
                edit: 'Éditer le plan',
                select: 'Sélection'
            };
            return names[this.currentTool];
        },

        formatDistance(meters) {
            if (meters >= 1) {
                return meters.toFixed(2) + 'm';
            } else {
                return (meters * 100).toFixed(0) + 'cm';
            }
        },

        handleMouseDown(event) {
            const rect = this.$refs.canvas.getBoundingClientRect();
            const x = event.clientX - rect.left;
            const y = event.clientY - rect.top;

            if (this.currentTool === 'pen') {
                this.isDrawing = true;
                this.currentPath = [{ x, y }];
            } else if (this.currentTool === 'rectangle') {
                this.isDrawing = true;
                this.drawingRect = { startX: x, startY: y, endX: x, endY: y };
            }
        },

        handleMouseMove(event) {
            const rect = this.$refs.canvas.getBoundingClientRect();
            this.mouseX = Math.round(event.clientX - rect.left);
            this.mouseY = Math.round(event.clientY - rect.top);

            // Déplacement d'un point du plan
            if (this.draggingPoint && this.editingShapeIndex !== null && this.editingPointIndex !== null) {
                this.floorPlanShapes[this.editingShapeIndex].points[this.editingPointIndex].x = this.mouseX;
                this.floorPlanShapes[this.editingShapeIndex].points[this.editingPointIndex].y = this.mouseY;
                return;
            }

            if (this.currentTool === 'pen' && this.isDrawing) {
                this.currentPath.push({ x: this.mouseX, y: this.mouseY });
            } else if (this.currentTool === 'polygon' && this.currentPath.length > 0) {
                this.tempPoint = { x: this.mouseX, y: this.mouseY };
            } else if (this.currentTool === 'rectangle' && this.isDrawing) {
                this.drawingRect.endX = this.mouseX;
                this.drawingRect.endY = this.mouseY;
            } else if (this.currentTool === 'box') {
                const size = this.getBoxSize();
                this.previewBox = {
                    x: this.mouseX - size.width / 2,
                    y: this.mouseY - size.height / 2,
                    width: size.width,
                    height: size.height
                };
            } else if (this.currentTool === 'measure' && this.measureLine.start && !this.measureLine.end) {
                // Mettre à jour le point temporaire pour l'aperçu de la ligne
                this.tempPoint = { x: this.mouseX, y: this.mouseY };
            }

            if (this.resizing && this.selectedBox) {
                this.handleResize(event);
            }
        },

        handleMouseUp() {
            // Arrêter le déplacement d'un point
            if (this.draggingPoint) {
                this.draggingPoint = false;
                this.editingPointIndex = null;
                return;
            }

            if (this.currentTool === 'pen' && this.isDrawing) {
                // Ajouter une nouvelle forme au tableau
                this.floorPlanShapes.push({
                    points: [...this.currentPath],
                    color: this.strokeColor,
                    width: this.strokeWidth,
                    dashed: this.isDashed
                });
                this.currentPath = [];
                this.isDrawing = false;
            } else if (this.currentTool === 'rectangle' && this.isDrawing) {
                const rect = this.drawingRect;
                const x1 = Math.min(rect.startX, rect.endX);
                const y1 = Math.min(rect.startY, rect.endY);
                const x2 = Math.max(rect.startX, rect.endX);
                const y2 = Math.max(rect.startY, rect.endY);

                // Ajouter le rectangle comme nouvelle forme
                this.floorPlanShapes.push({
                    points: [
                        { x: x1, y: y1 },
                        { x: x2, y: y1 },
                        { x: x2, y: y2 },
                        { x: x1, y: y2 },
                        { x: x1, y: y1 }
                    ],
                    color: this.strokeColor,
                    width: this.strokeWidth,
                    dashed: this.isDashed
                });

                this.drawingRect = null;
                this.isDrawing = false;
            }

            this.resizing = false;
            this.resizeHandle = null;
        },

        handleClick(event) {
            if (this.currentTool === 'measure') {
                const rect = this.$refs.canvas.getBoundingClientRect();
                const x = event.clientX - rect.left;
                const y = event.clientY - rect.top;

                if (!this.measureLine.start) {
                    // Premier clic - définir le point de départ
                    this.measureLine.start = { x, y };
                    this.measureLine.end = null;
                    this.measurementResult = null;
                } else if (!this.measureLine.end) {
                    // Deuxième clic - définir le point d'arrivée et calculer la distance
                    this.measureLine.end = { x, y };
                    const dx = this.measureLine.end.x - this.measureLine.start.x;
                    const dy = this.measureLine.end.y - this.measureLine.start.y;
                    const distancePixels = Math.sqrt(dx * dx + dy * dy);
                    const distanceMeters = distancePixels * this.echelle;
                    this.measurementResult = this.formatDistance(distanceMeters);
                } else {
                    // Troisième clic - réinitialiser
                    this.measureLine.start = null;
                    this.measureLine.end = null;
                    this.measurementResult = null;
                }
                return;
            }

            if (this.currentTool === 'polygon') {
                const rect = this.$refs.canvas.getBoundingClientRect();
                const x = event.clientX - rect.left;
                const y = event.clientY - rect.top;

                // Double-click pour terminer
                if (this.currentPath.length > 0) {
                    const lastPoint = this.currentPath[this.currentPath.length - 1];
                    const distance = Math.sqrt(Math.pow(x - lastPoint.x, 2) + Math.pow(y - lastPoint.y, 2));

                    if (distance < 10 && this.currentPath.length > 2) {
                        // Fermer le polygone et l'ajouter aux formes
                        this.floorPlanShapes.push({
                            points: [...this.currentPath, this.currentPath[0]],
                            color: this.strokeColor,
                            width: this.strokeWidth,
                            dashed: this.isDashed
                        });
                        this.currentPath = [];
                        this.tempPoint = null;
                        return;
                    }
                }

                this.currentPath.push({ x, y });
            } else if (this.currentTool === 'box') {
                this.placeBox();
            } else if (this.currentTool === 'lshape') {
                this.createLShape();
            }
        },

        getPathD(path) {
            if (path.length === 0) return '';

            let d = `M ${path[0].x} ${path[0].y}`;
            for (let i = 1; i < path.length; i++) {
                d += ` L ${path[i].x} ${path[i].y}`;
            }
            return d;
        },

        removePoint(index) {
            if (this.floorPlanPath.length > 2) {
                this.floorPlanPath.splice(index, 1);
            }
        },

        clearPlan() {
            if (confirm('Êtes-vous sûr de vouloir effacer le plan ?')) {
                this.floorPlanPath = [];
                this.currentPath = [];
                this.boxes = [];
                this.selectedBox = null;
            }
        },

        getBoxSize() {
            const sizes = {
                small: { width: 60, height: 50 },
                medium: { width: 100, height: 80 },
                large: { width: 140, height: 100 },
                xlarge: { width: 180, height: 120 },
                custom: { width: this.customBoxWidth, height: this.customBoxHeight }
            };
            return sizes[this.boxSize];
        },

        placeBox() {
            const size = this.getBoxSize();
            const newBox = {
                id: this.nextBoxId++,
                numero: `BOX-${this.nextBoxId - 1}`,
                x: this.mouseX - size.width / 2,
                y: this.mouseY - size.height / 2,
                width: size.width,
                height: size.height,
                surface: ((size.width * size.height) / 400).toFixed(2), // Conversion approximative
                prix_mensuel: 50,
                statut: 'libre'
            };
            this.boxes.push(newBox);
            this.selectedBox = newBox;
        },

        createLShape() {
            const x = this.mouseX;
            const y = this.mouseY;

            this.floorPlanPath = [
                { x: x, y: y },
                { x: x + 300, y: y },
                { x: x + 300, y: y + 150 },
                { x: x + 150, y: y + 150 },
                { x: x + 150, y: y + 300 },
                { x: x, y: y + 300 },
                { x: x, y: y }
            ];
        },

        selectBox(box) {
            if (this.currentTool === 'select' || this.currentTool === 'box') {
                this.selectedBox = box;
            }
        },

        deleteBox(box) {
            if (confirm(`Supprimer ${box.numero} ?`)) {
                const index = this.boxes.findIndex(b => b.id === box.id);
                if (index > -1) {
                    this.boxes.splice(index, 1);
                    this.selectedBox = null;
                }
            }
        },

        startResize(box, handle, event) {
            event.stopPropagation();
            this.resizing = true;
            this.resizeHandle = handle;
            this.resizeStartX = event.clientX;
            this.resizeStartY = event.clientY;
            this.selectedBox = box;
        },

        handleResize(event) {
            if (!this.resizing || !this.selectedBox) return;

            const deltaX = event.clientX - this.resizeStartX;
            const deltaY = event.clientY - this.resizeStartY;
            const box = this.selectedBox;

            switch (this.resizeHandle) {
                case 'se':
                    box.width = Math.max(30, box.width + deltaX);
                    box.height = Math.max(30, box.height + deltaY);
                    break;
                case 'sw':
                    box.width = Math.max(30, box.width - deltaX);
                    box.height = Math.max(30, box.height + deltaY);
                    box.x += deltaX;
                    break;
                case 'ne':
                    box.width = Math.max(30, box.width + deltaX);
                    box.height = Math.max(30, box.height - deltaY);
                    box.y += deltaY;
                    break;
                case 'nw':
                    box.width = Math.max(30, box.width - deltaX);
                    box.height = Math.max(30, box.height - deltaY);
                    box.x += deltaX;
                    box.y += deltaY;
                    break;
            }

            this.resizeStartX = event.clientX;
            this.resizeStartY = event.clientY;

            // Recalculer la surface
            box.surface = ((box.width * box.height) / 400).toFixed(2);
        },

        getBoxColor(statut) {
            const colors = {
                libre: 'rgba(25, 135, 84, 0.3)',
                occupe: 'rgba(220, 53, 69, 0.3)',
                reserve: 'rgba(255, 193, 7, 0.3)',
                maintenance: 'rgba(13, 202, 240, 0.3)'
            };
            return colors[statut] || colors.libre;
        },

        getBoxStroke(statut) {
            const colors = {
                libre: '#198754',
                occupe: '#dc3545',
                reserve: '#ffc107',
                maintenance: '#0dcaf0'
            };
            return colors[statut] || colors.libre;
        },

        savePlan() {
            // S'assurer que boxes est toujours un tableau
            const boxesToSave = Array.isArray(this.boxes) ? this.boxes : [];

            this.$emit('save', {
                emplacement_id: this.emplacementId,
                floor_plan: this.floorPlanShapes, // Sauvegarder toutes les formes
                boxes: boxesToSave,
                canvas_width: this.canvasWidth,
                canvas_height: this.canvasHeight,
                echelle: this.echelle
            });
        },

        clearPlan() {
            if (confirm('Êtes-vous sûr de vouloir effacer toutes les formes ?')) {
                this.floorPlanShapes = [];
                this.currentPath = [];
                this.selectedShapeIndex = null;
                this.editingShapeIndex = null;
            }
        },

        selectShape(index) {
            this.selectedShapeIndex = index;
            if (this.currentTool === 'edit') {
                this.editingShapeIndex = index;
            }
        },

        deleteSelectedShape() {
            if (this.selectedShapeIndex !== null) {
                this.floorPlanShapes.splice(this.selectedShapeIndex, 1);
                this.selectedShapeIndex = null;
                this.editingShapeIndex = null;
            }
        },

        // Drag and drop handlers
        handleDragStart(event, famille) {
            this.draggedFamille = famille;
            event.dataTransfer.effectAllowed = 'copy';
        },

        handleDrop(event) {
            if (!this.draggedFamille) return;

            const rect = this.$refs.canvas.getBoundingClientRect();
            const x = event.clientX - rect.left;
            const y = event.clientY - rect.top;

            // Calculer taille par défaut basée sur la surface de la famille
            const surfaceM2 = (this.draggedFamille.surface_min + this.draggedFamille.surface_max) / 2;
            const ratio = 1.25; // ratio largeur/hauteur
            const height = Math.sqrt(surfaceM2 / ratio) / this.echelle;
            const width = height * ratio;

            // Créer la nouvelle box
            const newBox = {
                id: this.nextBoxId++,
                numero: `${this.draggedFamille.nom.substring(0, 2).toUpperCase()}-${this.boxes.length + 1}`,
                x: x - width / 2,
                y: y - height / 2,
                width: Math.round(width),
                height: Math.round(height),
                surface: surfaceM2.toFixed(2),
                prix_mensuel: this.draggedFamille.prix_base || 0,
                statut: 'libre',
                famille_id: this.draggedFamille.id,
                famille_nom: this.draggedFamille.nom,
                is_from_db: false
            };

            this.boxes.push(newBox);
            this.draggedFamille = null;
        },

        selectBoxFromList(box) {
            this.selectedBox = box;
            this.currentTool = 'select';
        },

        // Édition des points du plan
        startDragPoint(shapeIndex, pointIndex, event) {
            this.editingShapeIndex = shapeIndex;
            this.editingPointIndex = pointIndex;
            this.draggingPoint = true;
            event.stopPropagation();
        },

        removePoint(shapeIndex, pointIndex) {
            if (this.floorPlanShapes[shapeIndex].points.length > 3) {
                this.floorPlanShapes[shapeIndex].points.splice(pointIndex, 1);
            }
        }
    }
};
</script>

<style scoped>
.floor-plan-drawer {
    width: 100%;
}

.canvas-wrapper {
    overflow: auto;
    max-height: 700px;
    background: #f8f9fa;
    border: 2px solid #dee2e6;
    border-radius: 4px;
}

.drawing-canvas {
    display: block;
    background: white;
}

.shape-selected {
    filter: drop-shadow(0 0 8px rgba(255, 193, 7, 0.8));
    cursor: pointer;
}

.path-point {
    cursor: pointer;
    transition: all 0.2s;
}

.path-point:hover {
    r: 8;
}

.path-point.editable {
    cursor: move;
}

.path-point.editable:hover {
    r: 8;
    fill: #ffc107;
    filter: drop-shadow(0 0 3px rgba(255, 193, 7, 0.8));
}

.box-rect {
    cursor: move;
    transition: opacity 0.2s;
}

.box-rect:hover {
    opacity: 0.8;
}

.box-rect.selected {
    stroke: #0d6efd;
    stroke-width: 3;
    filter: drop-shadow(0 0 5px rgba(13, 110, 253, 0.5));
}

.box-label {
    pointer-events: none;
    user-select: none;
}

.resize-handle {
    cursor: pointer;
}

.resize-handle:hover {
    fill: #0b5ed7;
}

.box-template {
    transition: all 0.2s;
}

.box-template:hover {
    background-color: #e9ecef !important;
    transform: scale(1.02);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.box-item {
    transition: all 0.2s;
}

.box-item:hover {
    background-color: #f8f9fa !important;
    transform: translateX(2px);
}

.btn-check:checked + .btn {
    font-weight: bold;
}
</style>
