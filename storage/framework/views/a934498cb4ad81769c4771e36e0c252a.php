<?php $__env->startSection('title', __('app.clients')); ?>

<?php $__env->startSection('actions'); ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_clients')): ?>
    <a href="<?php echo e(route('clients.create')); ?>" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i><?php echo e(__('app.add')); ?> <?php echo e(__('app.clients')); ?>

    </a>
<?php endif; ?>
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
                <div class="stat-number text-success"><?php echo e($stats['actifs']); ?></div>
                <div class="stat-label"><?php echo e(__('app.active')); ?></div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card text-center">
                <div class="stat-number text-info"><?php echo e($stats['particuliers']); ?></div>
                <div class="stat-label">Particuliers</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card text-center">
                <div class="stat-number text-warning"><?php echo e($stats['entreprises']); ?></div>
                <div class="stat-label">Entreprises</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card text-center">
                <div class="stat-number text-primary"><?php echo e($stats['avec_contrats']); ?></div>
                <div class="stat-label">Avec Contrats</div>
            </div>
        </div>
    </div>

    <!-- Filtres et recherche -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('clients.index')); ?>" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Rechercher..." value="<?php echo e(request('search')); ?>">
                </div>
                <div class="col-md-3">
                    <select name="type_client" class="form-select">
                        <option value="">Tous les types</option>
                        <option value="particulier" <?php echo e(request('type_client') == 'particulier' ? 'selected' : ''); ?>>Particulier</option>
                        <option value="entreprise" <?php echo e(request('type_client') == 'entreprise' ? 'selected' : ''); ?>>Entreprise</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="statut" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="actif" <?php echo e(request('statut') == 'actif' ? 'selected' : ''); ?>><?php echo e(__('app.active')); ?></option>
                        <option value="inactif" <?php echo e(request('statut') == 'inactif' ? 'selected' : ''); ?>><?php echo e(__('app.inactive')); ?></option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i><?php echo e(__('app.search')); ?>

                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des clients -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th><?php echo e(__('app.firstname')); ?> <?php echo e(__('app.lastname')); ?></th>
                            <th><?php echo e(__('app.email')); ?></th>
                            <th><?php echo e(__('app.phone')); ?></th>
                            <th>Ville</th>
                            <th><?php echo e(__('app.status')); ?></th>
                            <th>Contrats</th>
                            <th><?php echo e(__('app.actions')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($client->id); ?></td>
                            <td>
                                <?php if($client->type_client == 'particulier'): ?>
                                    <span class="badge bg-info">Particulier</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">Entreprise</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo e($client->nom); ?> <?php echo e($client->prenom); ?></strong>
                                <?php if($client->raison_sociale): ?>
                                    <br><small class="text-muted"><?php echo e($client->raison_sociale); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($client->email); ?></td>
                            <td><?php echo e($client->telephone); ?></td>
                            <td><?php echo e($client->ville); ?></td>
                            <td>
                                <?php if($client->is_active): ?>
                                    <span class="badge bg-success"><?php echo e(__('app.active')); ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?php echo e(__('app.inactive')); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-primary"><?php echo e($client->contrats->count()); ?></span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_clients')): ?>
                                    <a href="<?php echo e(route('clients.show', $client)); ?>" class="btn btn-sm btn-info" title="<?php echo e(__('app.view')); ?>">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit_clients')): ?>
                                    <a href="<?php echo e(route('clients.edit', $client)); ?>" class="btn btn-sm btn-warning" title="<?php echo e(__('app.edit')); ?>">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete_clients')): ?>
                                    <form action="<?php echo e(route('clients.destroy', $client)); ?>" method="POST" class="d-inline" onsubmit="return confirm('<?php echo e(__('app.are_you_sure')); ?>')">
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
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <p class="text-muted"><?php echo e(__('app.no_results')); ?></p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                <?php echo e($clients->links()); ?>

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
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp2025\htdocs\boxibox\resources\views/clients/index.blade.php ENDPATH**/ ?>