@extends('layouts.app')

@section('title', 'Plan des Boxes')

@section('actions')
@can('edit_boxes')
    <div class="btn-group" role="group">
        <button type="button" class="btn btn-secondary" id="toggleEditMode">
            <i class="fas fa-edit me-2"></i>Mode Édition
        </button>
        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#legendModal">
            <i class="fas fa-info-circle me-2"></i>Légende
        </button>
    </div>
@endcan
@endsection

@section('content')
<div class="container-fluid">
    <!-- Filtres et statistiques -->
    <div class="row mb-4">
        <div class="col-md-8">
            <!-- Filtres -->
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Emplacement</label>
                            <select id="filterEmplacement" class="form-select">
                                <option value="">Tous les emplacements</option>
                                @foreach($emplacements as $emplacement)
                                    <option value="{{ $emplacement->id }}">{{ $emplacement->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Famille</label>
                            <select id="filterFamille" class="form-select">
                                <option value="">Toutes les familles</option>
                                @foreach($familles as $famille)
                                    <option value="{{ $famille->id }}">{{ $famille->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Statut</label>
                            <select id="filterStatut" class="form-select">
                                <option value="">Tous les statuts</option>
                                <option value="libre">Libre</option>
                                <option value="occupe">Occupé</option>
                                <option value="reserve">Réservé</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="hors_service">Hors service</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Recherche</label>
                            <input type="text" id="searchBox" class="form-control" placeholder="Numéro de box...">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <!-- Statistiques -->
            <div class="card">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 col-lg-3">
                            <div class="border-end">
                                <div class="fs-6 fw-bold text-success" id="stat-libres">0</div>
                                <small class="text-muted">Libres</small>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="border-end">
                                <div class="fs-6 fw-bold text-danger" id="stat-occupees">0</div>
                                <small class="text-muted">Occupées</small>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="border-end">
                                <div class="fs-6 fw-bold text-warning" id="stat-reservees">0</div>
                                <small class="text-muted">Réservées</small>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="fs-6 fw-bold text-info" id="stat-total">0</div>
                            <small class="text-muted">Total</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Onglets par emplacement -->
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="emplacementTabs" role="tablist">
                @foreach($emplacements as $index => $emplacement)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $index === 0 ? 'active' : '' }}"
                                id="tab-{{ $emplacement->id }}"
                                data-bs-toggle="tab"
                                data-bs-target="#content-{{ $emplacement->id }}"
                                type="button"
                                role="tab">
                            {{ $emplacement->nom }}
                            <span class="badge bg-secondary ms-2">
                                {{ $emplacement->boxes()->count() }}
                            </span>
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="card-body p-0">
            <div class="tab-content" id="emplacementTabContent">
                @foreach($emplacements as $index => $emplacement)
                    <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                         id="content-{{ $emplacement->id }}"
                         role="tabpanel">

                        <!-- Plan de l'emplacement avec zoom et pan -->
                        <div class="position-relative bg-light overflow-hidden"
                             style="height: 600px;"
                             id="plan-{{ $emplacement->id }}">

                            <!-- Contrôles de zoom -->
                            <div class="position-absolute top-0 end-0 m-3" style="z-index: 100;">
                                <div class="btn-group-vertical shadow-sm" role="group">
                                    <button class="btn btn-sm btn-light zoom-in" data-plan="plan-{{ $emplacement->id }}" title="Zoom avant">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <button class="btn btn-sm btn-light zoom-reset" data-plan="plan-{{ $emplacement->id }}" title="Réinitialiser">
                                        <i class="fas fa-expand"></i>
                                    </button>
                                    <button class="btn btn-sm btn-light zoom-out" data-plan="plan-{{ $emplacement->id }}" title="Zoom arrière">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Conteneur transformable (zoom et pan) -->
                            <div class="plan-canvas position-relative"
                                 style="transform-origin: 0 0; transition: transform 0.2s ease; width: 1200px; height: 800px;">

                                <!-- Grid pour aide au positionnement -->
                                <div class="position-absolute w-100 h-100 opacity-25"
                                     style="background-image: repeating-linear-gradient(0deg, transparent, transparent 24px, #dee2e6 24px, #dee2e6 25px),
                                            repeating-linear-gradient(90deg, transparent, transparent 24px, #dee2e6 24px, #dee2e6 25px);">
                                </div>

                            <!-- Boxes de cet emplacement -->
                            @foreach($boxes->where('emplacement.id', $emplacement->id) as $box)
                                <div class="box-item position-absolute"
                                     data-box-id="{{ $box['id'] }}"
                                     data-statut="{{ $box['statut'] }}"
                                     data-famille="{{ $box['famille']['id'] }}"
                                     data-emplacement="{{ $box['emplacement']['id'] }}"
                                     data-numero="{{ $box['numero'] }}"
                                     data-surface="{{ $box['surface'] }}"
                                     data-volume="{{ $box['volume'] ?? 0 }}"
                                     data-prix="{{ $box['prix_mensuel'] }}"
                                     data-client="{{ $box['client']['nom'] ?? '' }}"
                                     data-contrat="{{ $box['contrat']['reference'] ?? '' }}"
                                     data-date-fin="{{ $box['contrat']['date_fin'] ?? '' }}"
                                     style="left: {{ $box['coordonnees_x'] ?? 50 }}px;
                                            top: {{ $box['coordonnees_y'] ?? 50 }}px;
                                            width: {{ max(50, min(150, $box['surface'] * 10)) }}px;
                                            height: {{ max(40, min(100, $box['surface'] * 8)) }}px;">

                                    <div class="box-content h-100 d-flex flex-column justify-content-center align-items-center text-white rounded shadow-sm cursor-pointer"
                                         style="background-color: {{ getBoxColor($box['statut']) }}; font-size: 0.8rem;">

                                        <!-- Numéro de box -->
                                        <div class="fw-bold">{{ $box['numero'] }}</div>

                                        <!-- Surface -->
                                        <div style="font-size: 0.7rem;">{{ $box['surface'] }}m²</div>

                                        <!-- Client si occupé -->
                                        @if($box['client'])
                                            <div style="font-size: 0.6rem;" class="text-truncate w-100 px-1 text-center">
                                                {{ $box['client']['nom'] }}
                                            </div>
                                        @endif

                                        <!-- Indicateur de statut -->
                                        <div class="position-absolute top-0 end-0 m-1">
                                            <i class="fas {{ getStatusIcon($box['statut']) }}" style="font-size: 0.7rem;"></i>
                                        </div>
                                    </div>

                                    <!-- Info-bulle enrichie (masquée par défaut) -->
                                    <div class="box-tooltip position-absolute bg-white border rounded shadow-lg p-3 d-none"
                                         style="z-index: 1000; min-width: 300px; left: 100%; margin-left: 10px; top: 0;">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="mb-0 text-dark">Box {{ $box['numero'] }}</h6>
                                            <span class="badge" style="background-color: {{ getBoxColor($box['statut']) }}">
                                                {{ ucfirst($box['statut']) }}
                                            </span>
                                        </div>
                                        <hr class="my-2">
                                        <div class="text-dark small">
                                            <div class="mb-2">
                                                <strong><i class="fas fa-ruler-combined text-primary me-2"></i>Dimensions:</strong>
                                                <div class="ms-4">Surface: {{ $box['surface'] }} m²</div>
                                                @if($box['volume'] ?? false)
                                                    <div class="ms-4">Volume: {{ $box['volume'] }} m³</div>
                                                @endif
                                            </div>
                                            <div class="mb-2">
                                                <strong><i class="fas fa-euro-sign text-success me-2"></i>Tarif:</strong>
                                                <div class="ms-4">{{ number_format($box['prix_mensuel'], 2) }}€/mois</div>
                                            </div>
                                            @if($box['client'])
                                                <div class="mb-2">
                                                    <strong><i class="fas fa-user text-info me-2"></i>Client:</strong>
                                                    <div class="ms-4">{{ $box['client']['nom'] }}</div>
                                                </div>
                                            @endif
                                            @if($box['contrat'] ?? false)
                                                <div class="mb-2">
                                                    <strong><i class="fas fa-file-contract text-warning me-2"></i>Contrat:</strong>
                                                    <div class="ms-4">Réf: {{ $box['contrat']['reference'] }}</div>
                                                    @if($box['contrat']['date_fin'])
                                                        <div class="ms-4">Fin: {{ $box['contrat']['date_fin'] }}</div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                        <hr class="my-2">
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-sm btn-primary flex-fill" onclick="boxPlan.showBoxDetails({{ $box['id'] }})">
                                                <i class="fas fa-eye me-1"></i>Détails
                                            </button>
                                            @if($box['statut'] === 'libre')
                                                <button class="btn btn-sm btn-success flex-fill">
                                                    <i class="fas fa-plus me-1"></i>Louer
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            </div> <!-- Fin plan-canvas -->

                            <!-- Zone de drop pour nouvelles boxes (mode édition) -->
                            <div class="drop-zone d-none position-absolute w-100 h-100"
                                 data-emplacement="{{ $emplacement->id }}"
                                 style="border: 2px dashed #6c757d; background: rgba(108, 117, 125, 0.1);">
                                <div class="d-flex align-items-center justify-content-center h-100">
                                    <span class="text-muted">
                                        <i class="fas fa-plus-circle me-2"></i>
                                        Cliquez pour ajouter une box
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Toolbar flottant (mode édition) -->
    <div id="editToolbar" class="position-fixed bottom-0 start-50 translate-middle-x bg-dark text-white p-3 rounded-top shadow d-none" style="z-index: 1050;">
        <div class="d-flex align-items-center gap-3">
            <span><i class="fas fa-edit me-2"></i>Mode Édition Actif</span>
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-outline-light" id="savePositions">
                    <i class="fas fa-save me-1"></i>Sauvegarder
                </button>
                <button type="button" class="btn btn-outline-light" id="cancelEdit">
                    <i class="fas fa-times me-1"></i>Annuler
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de détails de box -->
<div class="modal fade" id="boxModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="boxModalTitle">Détails de la box</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="boxModalBody">
                <!-- Contenu chargé dynamiquement -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <div id="boxModalActions">
                    <!-- Actions dynamiques -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal légende -->
<div class="modal fade" id="legendModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Légende du plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-6">
                        <h6>Statuts des boxes</h6>
                        <div class="d-flex align-items-center mb-2">
                            <div class="rounded me-2" style="width: 20px; height: 20px; background-color: #28a745;"></div>
                            <span>Libre</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <div class="rounded me-2" style="width: 20px; height: 20px; background-color: #dc3545;"></div>
                            <span>Occupé</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <div class="rounded me-2" style="width: 20px; height: 20px; background-color: #ffc107;"></div>
                            <span>Réservé</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <div class="rounded me-2" style="width: 20px; height: 20px; background-color: #fd7e14;"></div>
                            <span>Maintenance</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <div class="rounded me-2" style="width: 20px; height: 20px; background-color: #6c757d;"></div>
                            <span>Hors service</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <h6>Actions</h6>
                        <p><strong>Clic simple :</strong> Voir les détails</p>
                        <p><strong>Mode édition :</strong> Déplacer les boxes</p>
                        <p><strong>Glisser-déposer :</strong> Repositionner</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
class BoxPlan {
    constructor() {
        this.boxes = @json($boxes);
        this.editMode = false;
        this.originalPositions = {};
        this.zoomLevel = 1;
        this.panX = 0;
        this.panY = 0;
        this.isPanning = false;
        this.startX = 0;
        this.startY = 0;
        this.currentTooltip = null;
        this.init();
    }

    init() {
        this.bindEvents();
        this.updateStats();
        this.setupDragAndDrop();
        this.setupZoomAndPan();
        this.setupTooltips();
    }

    bindEvents() {
        // Filtres
        document.getElementById('filterEmplacement').addEventListener('change', () => this.applyFilters());
        document.getElementById('filterFamille').addEventListener('change', () => this.applyFilters());
        document.getElementById('filterStatut').addEventListener('change', () => this.applyFilters());
        document.getElementById('searchBox').addEventListener('input', () => this.applyFilters());

        // Mode édition
        const toggleBtn = document.getElementById('toggleEditMode');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => this.toggleEditMode());
        }
        document.getElementById('savePositions').addEventListener('click', () => this.savePositions());
        document.getElementById('cancelEdit').addEventListener('click', () => this.cancelEdit());

        // Clic sur les boxes
        document.addEventListener('click', (e) => {
            if (e.target.closest('.box-item') && !e.target.closest('.box-tooltip')) {
                const boxElement = e.target.closest('.box-item');
                if (!this.editMode) {
                    this.showBoxDetails(boxElement.dataset.boxId);
                }
            }
        });
    }

    setupZoomAndPan() {
        // Boutons de zoom
        document.querySelectorAll('.zoom-in').forEach(btn => {
            btn.addEventListener('click', () => this.zoom(1.2, btn.dataset.plan));
        });
        document.querySelectorAll('.zoom-out').forEach(btn => {
            btn.addEventListener('click', () => this.zoom(0.8, btn.dataset.plan));
        });
        document.querySelectorAll('.zoom-reset').forEach(btn => {
            btn.addEventListener('click', () => this.resetZoom(btn.dataset.plan));
        });

        // Pan avec la souris (glisser-déposer)
        document.querySelectorAll('[id^="plan-"]').forEach(planContainer => {
            const canvas = planContainer.querySelector('.plan-canvas');

            planContainer.addEventListener('mousedown', (e) => {
                // Ne pas activer le pan si on clique sur une box ou en mode édition
                if (e.target.closest('.box-item') || this.editMode) return;

                this.isPanning = true;
                this.startX = e.clientX - this.panX;
                this.startY = e.clientY - this.panY;
                planContainer.style.cursor = 'grabbing';
                e.preventDefault();
            });

            planContainer.addEventListener('mousemove', (e) => {
                if (!this.isPanning) return;

                this.panX = e.clientX - this.startX;
                this.panY = e.clientY - this.startY;
                this.updateTransform(canvas);
            });

            planContainer.addEventListener('mouseup', () => {
                this.isPanning = false;
                planContainer.style.cursor = 'grab';
            });

            planContainer.addEventListener('mouseleave', () => {
                this.isPanning = false;
                planContainer.style.cursor = 'default';
            });

            // Zoom avec la molette de la souris
            planContainer.addEventListener('wheel', (e) => {
                e.preventDefault();
                const delta = e.deltaY > 0 ? 0.9 : 1.1;
                this.zoom(delta, planContainer.id);
            }, { passive: false });

            // Curseur de grab par défaut
            planContainer.style.cursor = 'grab';
        });
    }

    zoom(factor, planId) {
        this.zoomLevel *= factor;
        // Limiter le zoom entre 0.5x et 3x
        this.zoomLevel = Math.max(0.5, Math.min(3, this.zoomLevel));

        const planContainer = document.getElementById(planId);
        const canvas = planContainer.querySelector('.plan-canvas');
        this.updateTransform(canvas);
    }

    resetZoom(planId) {
        this.zoomLevel = 1;
        this.panX = 0;
        this.panY = 0;

        const planContainer = document.getElementById(planId);
        const canvas = planContainer.querySelector('.plan-canvas');
        this.updateTransform(canvas);
    }

    updateTransform(canvas) {
        canvas.style.transform = `translate(${this.panX}px, ${this.panY}px) scale(${this.zoomLevel})`;
    }

    setupTooltips() {
        // Afficher l'info-bulle au survol
        document.querySelectorAll('.box-item').forEach(boxItem => {
            let hoverTimeout;

            boxItem.addEventListener('mouseenter', (e) => {
                if (this.editMode) return;

                // Délai de 300ms avant d'afficher le tooltip
                hoverTimeout = setTimeout(() => {
                    this.showTooltip(boxItem);
                }, 300);
            });

            boxItem.addEventListener('mouseleave', () => {
                clearTimeout(hoverTimeout);
                this.hideTooltip(boxItem);
            });
        });

        // Fermer le tooltip si on clique ailleurs
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.box-item')) {
                this.hideAllTooltips();
            }
        });
    }

    showTooltip(boxItem) {
        this.hideAllTooltips();
        const tooltip = boxItem.querySelector('.box-tooltip');
        if (tooltip) {
            tooltip.classList.remove('d-none');
            this.currentTooltip = tooltip;

            // Ajuster la position si le tooltip sort de l'écran
            const rect = tooltip.getBoundingClientRect();
            if (rect.right > window.innerWidth) {
                tooltip.style.left = 'auto';
                tooltip.style.right = '100%';
                tooltip.style.marginLeft = '0';
                tooltip.style.marginRight = '10px';
            }
        }
    }

    hideTooltip(boxItem) {
        const tooltip = boxItem.querySelector('.box-tooltip');
        if (tooltip) {
            tooltip.classList.add('d-none');
            // Réinitialiser la position
            tooltip.style.left = '100%';
            tooltip.style.right = 'auto';
            tooltip.style.marginLeft = '10px';
            tooltip.style.marginRight = '0';
        }
    }

    hideAllTooltips() {
        document.querySelectorAll('.box-tooltip').forEach(tooltip => {
            tooltip.classList.add('d-none');
        });
        this.currentTooltip = null;
    }

    setupDragAndDrop() {
        const boxes = document.querySelectorAll('.box-item');
        boxes.forEach(box => {
            box.addEventListener('mousedown', (e) => {
                if (this.editMode) {
                    this.startDrag(e, box);
                }
            });
        });
    }

    startDrag(e, element) {
        e.preventDefault();
        const startX = e.clientX;
        const startY = e.clientY;
        const elementRect = element.getBoundingClientRect();
        const containerRect = element.parentElement.getBoundingClientRect();

        const offsetX = startX - elementRect.left;
        const offsetY = startY - elementRect.top;

        const onMouseMove = (moveEvent) => {
            const newX = moveEvent.clientX - containerRect.left - offsetX;
            const newY = moveEvent.clientY - containerRect.top - offsetY;

            element.style.left = Math.max(0, Math.min(containerRect.width - elementRect.width, newX)) + 'px';
            element.style.top = Math.max(0, Math.min(containerRect.height - elementRect.height, newY)) + 'px';
        };

        const onMouseUp = () => {
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);
            element.classList.remove('dragging');
        };

        element.classList.add('dragging');
        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('mouseup', onMouseUp);
    }

    toggleEditMode() {
        this.editMode = !this.editMode;
        const button = document.getElementById('toggleEditMode');
        const toolbar = document.getElementById('editToolbar');

        if (this.editMode) {
            button.innerHTML = '<i class="fas fa-eye me-2"></i>Mode Visualisation';
            button.classList.remove('btn-secondary');
            button.classList.add('btn-warning');
            toolbar.classList.remove('d-none');

            // Sauvegarder les positions actuelles
            this.saveCurrentPositions();

            // Activer le style de drag
            document.querySelectorAll('.box-item').forEach(box => {
                box.classList.add('draggable');
                box.style.cursor = 'move';
            });
        } else {
            button.innerHTML = '<i class="fas fa-edit me-2"></i>Mode Édition';
            button.classList.remove('btn-warning');
            button.classList.add('btn-secondary');
            toolbar.classList.add('d-none');

            // Désactiver le style de drag
            document.querySelectorAll('.box-item').forEach(box => {
                box.classList.remove('draggable');
                box.style.cursor = 'pointer';
            });
        }
    }

    saveCurrentPositions() {
        document.querySelectorAll('.box-item').forEach(box => {
            const boxId = box.dataset.boxId;
            this.originalPositions[boxId] = {
                left: box.style.left,
                top: box.style.top
            };
        });
    }

    savePositions() {
        const positions = {};
        document.querySelectorAll('.box-item').forEach(box => {
            const boxId = box.dataset.boxId;
            positions[boxId] = {
                x: parseInt(box.style.left),
                y: parseInt(box.style.top)
            };
        });

        // Envoyer les nouvelles positions au serveur
        fetch('/technique/boxes/update-positions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ positions })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.toggleEditMode();
                this.showAlert('Positions sauvegardées avec succès', 'success');
            } else {
                this.showAlert('Erreur lors de la sauvegarde', 'danger');
            }
        })
        .catch(error => {
            this.showAlert('Erreur lors de la sauvegarde', 'danger');
        });
    }

    cancelEdit() {
        // Restaurer les positions originales
        Object.keys(this.originalPositions).forEach(boxId => {
            const box = document.querySelector(`[data-box-id="${boxId}"]`);
            if (box) {
                box.style.left = this.originalPositions[boxId].left;
                box.style.top = this.originalPositions[boxId].top;
            }
        });

        this.toggleEditMode();
    }

    applyFilters() {
        const emplacement = document.getElementById('filterEmplacement').value;
        const famille = document.getElementById('filterFamille').value;
        const statut = document.getElementById('filterStatut').value;
        const search = document.getElementById('searchBox').value.toLowerCase();

        document.querySelectorAll('.box-item').forEach(box => {
            let visible = true;

            if (emplacement && box.dataset.emplacement !== emplacement) visible = false;
            if (famille && box.dataset.famille !== famille) visible = false;
            if (statut && box.dataset.statut !== statut) visible = false;
            if (search && !box.querySelector('.fw-bold').textContent.toLowerCase().includes(search)) visible = false;

            box.style.display = visible ? 'block' : 'none';
        });

        this.updateStats();
    }

    updateStats() {
        const visibleBoxes = document.querySelectorAll('.box-item:not([style*="display: none"])');

        const stats = {
            total: visibleBoxes.length,
            libres: 0,
            occupees: 0,
            reservees: 0
        };

        visibleBoxes.forEach(box => {
            const statut = box.dataset.statut;
            if (statut === 'libre') stats.libres++;
            else if (statut === 'occupe') stats.occupees++;
            else if (statut === 'reserve') stats.reservees++;
        });

        document.getElementById('stat-total').textContent = stats.total;
        document.getElementById('stat-libres').textContent = stats.libres;
        document.getElementById('stat-occupees').textContent = stats.occupees;
        document.getElementById('stat-reservees').textContent = stats.reservees;
    }

    showBoxDetails(boxId) {
        // Charger les détails de la box via AJAX
        fetch(`/technique/boxes/${boxId}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('boxModalBody').innerHTML = html;
                const modal = new bootstrap.Modal(document.getElementById('boxModal'));
                modal.show();
            })
            .catch(error => {
                this.showAlert('Erreur lors du chargement des détails', 'danger');
            });
    }

    showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
        alertDiv.style.zIndex = '9999';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(alertDiv);

        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
}

// Fonctions utilitaires pour les couleurs et icônes
const boxColors = {
    'libre': '#28a745',
    'occupe': '#dc3545',
    'reserve': '#ffc107',
    'maintenance': '#fd7e14',
    'hors_service': '#6c757d'
};

const statusIcons = {
    'libre': 'fa-unlock',
    'occupe': 'fa-lock',
    'reserve': 'fa-clock',
    'maintenance': 'fa-tools',
    'hors_service': 'fa-ban'
};

// Initialiser le plan au chargement de la page
document.addEventListener('DOMContentLoaded', () => {
    window.boxPlan = new BoxPlan();
});
</script>

<style>
.box-item {
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
    user-select: none;
}

.box-item:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2) !important;
    z-index: 50;
}

.box-item.draggable {
    cursor: move !important;
}

.box-item.dragging {
    opacity: 0.7;
    z-index: 1000;
}

.drop-zone {
    pointer-events: none;
}

.tab-content {
    min-height: 600px;
}

.plan-canvas {
    will-change: transform;
}

[id^="plan-"] {
    user-select: none;
}

/* Animation douce pour le zoom */
.plan-canvas {
    transition: transform 0.2s ease-out;
}

/* Style pour les boutons de zoom */
.zoom-in:hover, .zoom-out:hover, .zoom-reset:hover {
    transform: scale(1.1);
    transition: transform 0.1s;
}

/* Tooltip animation */
.box-tooltip {
    animation: tooltipFadeIn 0.2s ease-in;
}

@keyframes tooltipFadeIn {
    from {
        opacity: 0;
        transform: translateX(-10px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Indicateur de niveau de zoom */
.zoom-indicator {
    position: absolute;
    bottom: 10px;
    left: 10px;
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 0.8rem;
    z-index: 100;
}

</style>
@endpush