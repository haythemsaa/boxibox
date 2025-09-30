<?php $__env->startSection('title', 'Gestion SEPA'); ?>

<?php $__env->startSection('actions'); ?>
<div class="btn-group" role="group">
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_mandats')): ?>
    <a href="<?php echo e(route('mandats.create')); ?>" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Nouveau mandat
    </a>
    <?php endif; ?>
    <div class="btn-group" role="group">
        <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
            <i class="fas fa-file-export me-2"></i>Exporter
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="<?php echo e(route('sepa.export', 'prelevement')); ?>">
                <i class="fas fa-download me-2"></i>Fichier de prélèvement
            </a></li>
            <li><a class="dropdown-item" href="<?php echo e(route('sepa.export', 'mandats')); ?>">
                <i class="fas fa-file-alt me-2"></i>Liste des mandats
            </a></li>
        </ul>
    </div>
    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#importModal">
        <i class="fas fa-file-import me-2"></i>Importer retours
    </button>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-primary"><?php echo e($stats['total'] ?? 0); ?></div>
                <div class="stat-label">Mandats Total</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-success"><?php echo e($stats['actifs'] ?? 0); ?></div>
                <div class="stat-label">Actifs</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-warning"><?php echo e($stats['en_attente'] ?? 0); ?></div>
                <div class="stat-label">En Attente</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-info"><?php echo e($stats['prelevements_mois'] ?? 0); ?></div>
                <div class="stat-label">Prélèvements ce mois</div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('sepa.index')); ?>" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Rechercher..." value="<?php echo e(request('search')); ?>">
                </div>
                <div class="col-md-3">
                    <select name="statut" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="brouillon" <?php echo e(request('statut') == 'brouillon' ? 'selected' : ''); ?>>Brouillon</option>
                        <option value="en_attente_signature" <?php echo e(request('statut') == 'en_attente_signature' ? 'selected' : ''); ?>>En attente signature</option>
                        <option value="actif" <?php echo e(request('statut') == 'actif' ? 'selected' : ''); ?>>Actif</option>
                        <option value="suspendu" <?php echo e(request('statut') == 'suspendu' ? 'selected' : ''); ?>>Suspendu</option>
                        <option value="revoque" <?php echo e(request('statut') == 'revoque' ? 'selected' : ''); ?>>Révoqué</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="type" class="form-select">
                        <option value="">Tous les types</option>
                        <option value="recurrent" <?php echo e(request('type') == 'recurrent' ? 'selected' : ''); ?>>Récurrent</option>
                        <option value="ponctuel" <?php echo e(request('type') == 'ponctuel' ? 'selected' : ''); ?>>Ponctuel</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i>Rechercher
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des mandats -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Référence Unique</th>
                            <th>Client</th>
                            <th>IBAN</th>
                            <th>Type</th>
                            <th>Date signature</th>
                            <th><?php echo e(__('app.status')); ?></th>
                            <th><?php echo e(__('app.actions')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $mandats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mandat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><strong><?php echo e($mandat->reference_unique_mandat); ?></strong></td>
                            <td>
                                <strong><?php echo e($mandat->client->nom); ?> <?php echo e($mandat->client->prenom); ?></strong>
                                <?php if($mandat->client->raison_sociale): ?>
                                    <br><small class="text-muted"><?php echo e($mandat->client->raison_sociale); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <code><?php echo e(substr($mandat->iban, 0, 4)); ?> **** **** <?php echo e(substr($mandat->iban, -4)); ?></code>
                            </td>
                            <td>
                                <?php if($mandat->type == 'recurrent'): ?>
                                    <span class="badge bg-primary">Récurrent</span>
                                <?php else: ?>
                                    <span class="badge bg-info">Ponctuel</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($mandat->date_signature): ?>
                                    <?php echo e($mandat->date_signature->format('d/m/Y')); ?>

                                <?php else: ?>
                                    <span class="text-muted">Non signé</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($mandat->statut == 'actif'): ?>
                                    <span class="badge bg-success">Actif</span>
                                <?php elseif($mandat->statut == 'en_attente_signature'): ?>
                                    <span class="badge bg-warning">En attente signature</span>
                                <?php elseif($mandat->statut == 'brouillon'): ?>
                                    <span class="badge bg-secondary">Brouillon</span>
                                <?php elseif($mandat->statut == 'suspendu'): ?>
                                    <span class="badge bg-warning">Suspendu</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Révoqué</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_mandats')): ?>
                                    <a href="<?php echo e(route('mandats.show', $mandat)); ?>" class="btn btn-sm btn-info" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit_mandats')): ?>
                                    <a href="<?php echo e(route('mandats.edit', $mandat)); ?>" class="btn btn-sm btn-warning" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if($mandat->statut == 'brouillon' || $mandat->statut == 'en_attente_signature'): ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('activate_mandats')): ?>
                                        <form action="<?php echo e(route('sepa.mandats.activate', $mandat)); ?>" method="POST" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="btn btn-sm btn-success" title="Activer">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete_mandats')): ?>
                                    <form action="<?php echo e(route('mandats.destroy', $mandat)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr ?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Aucun mandat SEPA trouvé</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                <?php echo e($mandats->links()); ?>

            </div>
        </div>
    </div>
</div>

<!-- Modal Import -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('sepa.import-returns')); ?>" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <div class="modal-header">
                    <h5 class="modal-title">Importer fichier de retour SEPA</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="file" class="form-label">Fichier XML PAIN.002</label>
                        <input type="file" class="form-control" id="file" name="file" accept=".xml" required>
                        <small class="text-muted">Format: PAIN.002 (retours de prélèvement)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-2"></i>Importer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.stat-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 0.875rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table th {
    background-color: #f8f9fa;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.875rem;
    letter-spacing: 0.5px;
}
</style>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp2025\htdocs\boxibox\resources\views/sepa/index.blade.php ENDPATH**/ ?>