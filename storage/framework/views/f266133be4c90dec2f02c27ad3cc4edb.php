<?php $__env->startSection('title', __('app.boxes')); ?>

<?php $__env->startSection('actions'); ?>
<div class="btn-group" role="group">
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_boxes')): ?>
    <a href="<?php echo e(route('boxes.create')); ?>" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i><?php echo e(__('app.add')); ?> Box
    </a>
    <?php endif; ?>
    <a href="<?php echo e(route('boxes.plan')); ?>" class="btn btn-info">
        <i class="fas fa-map me-2"></i>Plan interactif
    </a>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="stat-card text-center">
                <div class="stat-number text-primary"><?php echo e($stats['total']); ?></div>
                <div class="stat-label"><?php echo e(__('app.total')); ?></div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card text-center">
                <div class="stat-number text-success"><?php echo e($stats['libres']); ?></div>
                <div class="stat-label">Libres</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card text-center">
                <div class="stat-number text-danger"><?php echo e($stats['occupees']); ?></div>
                <div class="stat-label">Occupés</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card text-center">
                <div class="stat-number text-warning"><?php echo e($stats['reservees']); ?></div>
                <div class="stat-label">Réservés</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card text-center">
                <div class="stat-number text-info"><?php echo e($stats['surface_totale']); ?> m²</div>
                <div class="stat-label"><?php echo e(__('app.surface')); ?></div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card text-center">
                <div class="stat-number text-success"><?php echo e(number_format($stats['ca_reel'], 2)); ?> €</div>
                <div class="stat-label">CA Réel</div>
            </div>
        </div>
    </div>

    <!-- Filtres et recherche -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('boxes.index')); ?>" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="<?php echo e(__('app.search')); ?>..." value="<?php echo e(request('search')); ?>">
                </div>
                <div class="col-md-2">
                    <select name="statut" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="libre" <?php echo e(request('statut') == 'libre' ? 'selected' : ''); ?>>Libre</option>
                        <option value="occupe" <?php echo e(request('statut') == 'occupe' ? 'selected' : ''); ?>>Occupé</option>
                        <option value="reserve" <?php echo e(request('statut') == 'reserve' ? 'selected' : ''); ?>>Réservé</option>
                        <option value="maintenance" <?php echo e(request('statut') == 'maintenance' ? 'selected' : ''); ?>>Maintenance</option>
                        <option value="hors_service" <?php echo e(request('statut') == 'hors_service' ? 'selected' : ''); ?>>Hors service</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="famille_id" class="form-select">
                        <option value="">Toutes les familles</option>
                        <?php $__currentLoopData = $familles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $famille): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($famille->id); ?>" <?php echo e(request('famille_id') == $famille->id ? 'selected' : ''); ?>>
                                <?php echo e($famille->nom); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="emplacement_id" class="form-select">
                        <option value="">Tous les emplacements</option>
                        <?php $__currentLoopData = $emplacements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emplacement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($emplacement->id); ?>" <?php echo e(request('emplacement_id') == $emplacement->id ? 'selected' : ''); ?>>
                                <?php echo e($emplacement->nom); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="sort" class="form-select">
                        <option value="numero" <?php echo e(request('sort') == 'numero' ? 'selected' : ''); ?>>Par numéro</option>
                        <option value="surface" <?php echo e(request('sort') == 'surface' ? 'selected' : ''); ?>>Par surface</option>
                        <option value="prix" <?php echo e(request('sort') == 'prix' ? 'selected' : ''); ?>>Par prix</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des boxes -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Numéro</th>
                            <th>Famille</th>
                            <th>Emplacement</th>
                            <th><?php echo e(__('app.surface')); ?></th>
                            <th>Volume</th>
                            <th>Prix/mois</th>
                            <th><?php echo e(__('app.status')); ?></th>
                            <th>Client</th>
                            <th><?php echo e(__('app.actions')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $boxes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $box): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><strong><?php echo e($box->numero); ?></strong></td>
                            <td>
                                <?php if($box->famille): ?>
                                    <span class="badge" style="background-color: <?php echo e($box->famille->couleur ?? '#6c757d'); ?>">
                                        <?php echo e($box->famille->nom); ?>

                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($box->emplacement): ?>
                                    <?php echo e($box->emplacement->nom); ?>

                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($box->surface); ?> m²</td>
                            <td><?php echo e($box->volume ?? 'N/A'); ?> m³</td>
                            <td><strong><?php echo e(number_format($box->prix_mensuel ?? $box->prix_base ?? 0, 2)); ?> €</strong></td>
                            <td>
                                <?php if($box->statut == 'libre'): ?>
                                    <span class="badge bg-success">Libre</span>
                                <?php elseif($box->statut == 'occupe'): ?>
                                    <span class="badge bg-danger">Occupé</span>
                                <?php elseif($box->statut == 'reserve'): ?>
                                    <span class="badge bg-warning">Réservé</span>
                                <?php elseif($box->statut == 'maintenance'): ?>
                                    <span class="badge bg-secondary">Maintenance</span>
                                <?php else: ?>
                                    <span class="badge bg-dark">Hors service</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($box->contratActif && $box->contratActif->client): ?>
                                    <a href="<?php echo e(route('clients.show', $box->contratActif->client)); ?>">
                                        <?php echo e($box->contratActif->client->nom); ?> <?php echo e($box->contratActif->client->prenom); ?>

                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_boxes')): ?>
                                    <a href="<?php echo e(route('boxes.show', $box)); ?>" class="btn btn-sm btn-info" title="<?php echo e(__('app.view')); ?>">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit_boxes')): ?>
                                    <a href="<?php echo e(route('boxes.edit', $box)); ?>" class="btn btn-sm btn-warning" title="<?php echo e(__('app.edit')); ?>">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if($box->statut == 'libre'): ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('reserve_boxes')): ?>
                                        <form action="<?php echo e(route('boxes.reserve', $box)); ?>" method="POST" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="btn btn-sm btn-primary" title="Réserver">
                                                <i class="fas fa-lock"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    <?php elseif($box->statut == 'reserve'): ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('liberer_boxes')): ?>
                                        <form action="<?php echo e(route('boxes.liberer', $box)); ?>" method="POST" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="btn btn-sm btn-success" title="Libérer">
                                                <i class="fas fa-unlock"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete_boxes')): ?>
                                    <form action="<?php echo e(route('boxes.destroy', $box)); ?>" method="POST" class="d-inline" onsubmit="return confirm('<?php echo e(__('app.are_you_sure')); ?>')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-danger" title="<?php echo e(__('app.delete')); ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="fas fa-box fa-3x text-muted mb-3"></i>
                                <p class="text-muted"><?php echo e(__('app.no_results')); ?></p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                <?php echo e($boxes->links()); ?>

            </div>
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
    font-size: 1.5rem;
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
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp2025\htdocs\boxibox\resources\views/boxes/index.blade.php ENDPATH**/ ?>