<?php $__env->startSection('title', __('app.invoices')); ?>

<?php $__env->startSection('actions'); ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_factures')): ?>
    <a href="<?php echo e(route('factures.create')); ?>" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i><?php echo e(__('app.add')); ?> <?php echo e(__('app.invoices')); ?>

    </a>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-primary"><?php echo e($stats['total'] ?? 0); ?></div>
                <div class="stat-label"><?php echo e(__('app.total')); ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-warning"><?php echo e($stats['en_attente'] ?? 0); ?></div>
                <div class="stat-label"><?php echo e(__('app.pending')); ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-success"><?php echo e($stats['payees'] ?? 0); ?></div>
                <div class="stat-label"><?php echo e(__('app.paid')); ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-danger"><?php echo e(number_format($stats['montant_du'] ?? 0, 2)); ?> €</div>
                <div class="stat-label">Montant Dû</div>
            </div>
        </div>
    </div>

    <!-- Filtres et recherche -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('factures.index')); ?>" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="<?php echo e(__('app.search')); ?>..." value="<?php echo e(request('search')); ?>">
                </div>
                <div class="col-md-3">
                    <select name="statut" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="brouillon" <?php echo e(request('statut') == 'brouillon' ? 'selected' : ''); ?>><?php echo e(__('app.draft')); ?></option>
                        <option value="en_attente" <?php echo e(request('statut') == 'en_attente' ? 'selected' : ''); ?>><?php echo e(__('app.pending')); ?></option>
                        <option value="payee" <?php echo e(request('statut') == 'payee' ? 'selected' : ''); ?>><?php echo e(__('app.paid')); ?></option>
                        <option value="en_retard" <?php echo e(request('statut') == 'en_retard' ? 'selected' : ''); ?>>En Retard</option>
                        <option value="annulee" <?php echo e(request('statut') == 'annulee' ? 'selected' : ''); ?>><?php echo e(__('app.cancelled')); ?></option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="client_id" class="form-select">
                        <option value=""><?php echo e(__('app.all_clients')); ?></option>
                        <?php if(isset($clients)): ?>
                            <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($client->id); ?>" <?php echo e(request('client_id') == $client->id ? 'selected' : ''); ?>>
                                    <?php echo e($client->nom); ?> <?php echo e($client->prenom); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
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

    <!-- Liste des factures -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>N° Facture</th>
                            <th><?php echo e(__('app.client')); ?></th>
                            <th><?php echo e(__('app.contract')); ?></th>
                            <th>Date d'émission</th>
                            <th>Date d'échéance</th>
                            <th>Montant HT</th>
                            <th>Montant TTC</th>
                            <th><?php echo e(__('app.status')); ?></th>
                            <th><?php echo e(__('app.actions')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $factures; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $facture): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><strong><?php echo e($facture->numero_facture); ?></strong></td>
                            <td>
                                <strong><?php echo e($facture->client->nom); ?> <?php echo e($facture->client->prenom); ?></strong>
                                <?php if($facture->client->raison_sociale): ?>
                                    <br><small class="text-muted"><?php echo e($facture->client->raison_sociale); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($facture->contrat): ?>
                                    <span class="badge bg-info"><?php echo e($facture->contrat->numero_contrat); ?></span>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($facture->date_emission->format('d/m/Y')); ?></td>
                            <td>
                                <?php echo e($facture->date_echeance->format('d/m/Y')); ?>

                                <?php if($facture->date_echeance->isPast() && $facture->statut != 'payee'): ?>
                                    <br><span class="badge bg-danger">En retard</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e(number_format($facture->montant_ht, 2)); ?> €</td>
                            <td><strong><?php echo e(number_format($facture->montant_ttc, 2)); ?> €</strong></td>
                            <td>
                                <?php if($facture->statut == 'payee'): ?>
                                    <span class="badge bg-success"><?php echo e(__('app.paid')); ?></span>
                                <?php elseif($facture->statut == 'en_attente'): ?>
                                    <span class="badge bg-warning"><?php echo e(__('app.pending')); ?></span>
                                <?php elseif($facture->statut == 'en_retard'): ?>
                                    <span class="badge bg-danger">En Retard</span>
                                <?php elseif($facture->statut == 'brouillon'): ?>
                                    <span class="badge bg-secondary"><?php echo e(__('app.draft')); ?></span>
                                <?php else: ?>
                                    <span class="badge bg-dark"><?php echo e(__('app.cancelled')); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_factures')): ?>
                                    <a href="<?php echo e(route('factures.show', $facture)); ?>" class="btn btn-sm btn-info" title="<?php echo e(__('app.view')); ?>">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit_factures')): ?>
                                    <a href="<?php echo e(route('factures.edit', $facture)); ?>" class="btn btn-sm btn-warning" title="<?php echo e(__('app.edit')); ?>">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete_factures')): ?>
                                    <form action="<?php echo e(route('factures.destroy', $facture)); ?>" method="POST" class="d-inline" onsubmit="return confirm('<?php echo e(__('app.are_you_sure')); ?>')">
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
                                <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                                <p class="text-muted"><?php echo e(__('app.no_results')); ?></p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                <?php echo e($factures->links()); ?>

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
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp2025\htdocs\boxibox\resources\views/factures/index.blade.php ENDPATH**/ ?>