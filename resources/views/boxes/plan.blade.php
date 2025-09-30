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

                        <!-- Plan de l'emplacement -->
                        <div class="position-relative bg-light"
                             style="height: 600px; overflow: auto;"
                             id="plan-{{ $emplacement->id }}">

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
                                     style="left: {{ $box['coordonnees_x'] ?? 50 }}px;
                                            top: {{ $box['coordonnees_y'] ?? 50 }}px;
                                            width: {{ max(50, min(150, $box['surface'] * 10)) }}px;
                                            height: {{ max(40, min(100, $box['surface'] * 8)) }}px;"
                                     title="{{ $box['numero'] }} - {{ $box['surface'] }}m² - {{ number_format($box['prix_mensuel'], 2) }}€">

                                    <div class="box-content h-100 d-flex flex-column justify-content-center align-items-center text-white rounded shadow-sm cursor-pointer"
                                         style="background-color: {{ $this->getBoxColor($box['statut']) }}; font-size: 0.8rem;">

                                        <!-- Numéro de box -->
                                        <div class="fw-bold">{{ $box['numero'] }}</div>

                                        <!-- Surface -->
                                        <div style="font-size: 0.7rem;">{{ $box['surface'] }}m²</div>

                                        <!-- Client si occupé -->
                                        @if($box['client'])
                                            <div style="font-size: 0.6rem;" class="text-truncate">
                                                {{ $box['client']['nom'] }}
                                            </div>
                                        @endif

                                        <!-- Indicateur de statut -->
                                        <div class="position-absolute top-0 end-0 m-1">
                                            <i class="fas {{ $this->getStatusIcon($box['statut']) }}" style="font-size: 0.7rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

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
        this.init();
    }

    init() {
        this.bindEvents();
        this.updateStats();
        this.setupDragAndDrop();
    }

    bindEvents() {
        // Filtres
        document.getElementById('filterEmplacement').addEventListener('change', () => this.applyFilters());
        document.getElementById('filterFamille').addEventListener('change', () => this.applyFilters());
        document.getElementById('filterStatut').addEventListener('change', () => this.applyFilters());
        document.getElementById('searchBox').addEventListener('input', () => this.applyFilters());

        // Mode édition
        document.getElementById('toggleEditMode').addEventListener('click', () => this.toggleEditMode());
        document.getElementById('savePositions').addEventListener('click', () => this.savePositions());
        document.getElementById('cancelEdit').addEventListener('click', () => this.cancelEdit());

        // Clic sur les boxes
        document.addEventListener('click', (e) => {
            if (e.target.closest('.box-item')) {
                const boxElement = e.target.closest('.box-item');
                if (!this.editMode) {
                    this.showBoxDetails(boxElement.dataset.boxId);
                }
            }
        });
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

@php
function getBoxColor($statut) {
    return match($statut) {
        'libre' => '#28a745',
        'occupe' => '#dc3545',
        'reserve' => '#ffc107',
        'maintenance' => '#fd7e14',
        'hors_service' => '#6c757d',
        default => '#6c757d'
    };
}

function getStatusIcon($statut) {
    return match($statut) {
        'libre' => 'fa-unlock',
        'occupe' => 'fa-lock',
        'reserve' => 'fa-clock',
        'maintenance' => 'fa-tools',
        'hors_service' => 'fa-ban',
        default => 'fa-question'
    };
}
@endphp
</style>
@endpush