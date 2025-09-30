<?php $__env->startSection('title', 'Règlements'); ?>

<?php $__env->startSection('actions'); ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_reglements')): ?>
    <a href="<?php echo e(route('reglements.create')); ?>" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Nouveau règlement
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
                <div class="stat-number text-success"><?php echo e($stats['valides'] ?? 0); ?></div>
                <div class="stat-label">Validés</div>
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
                <div class="stat-number text-success"><?php echo e(number_format($stats['montant_total'] ?? 0, 2)); ?> €</div>
                <div class="stat-label">Montant Total</div>
            </div>
        </div>
    </div>

    <!-- Filtres et recherche -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('reglements.index')); ?>" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Rechercher..." value="<?php echo e(request('search')); ?>">
                </div>
                <div class="col-md-3">
                    <select name="statut" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="en_attente" <?php echo e(request('statut') == 'en_attente' ? 'selected' : ''); ?>>En Attente</option>
                        <option value="valide" <?php echo e(request('statut') == 'valide' ? 'selected' : ''); ?>>Validé</option>
                        <option value="refuse" <?php echo e(request('statut') == 'refuse' ? 'selected' : ''); ?>>Refusé</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="mode_paiement" class="form-select">
                        <option value="">Tous les modes</option>
                        <option value="virement" <?php echo e(request('mode_paiement') == 'virement' ? 'selected' : ''); ?>>Virement</option>
                        <option value="cheque" <?php echo e(request('mode_paiement') == 'cheque' ? 'selected' : ''); ?>>Chèque</option>
                        <option value="especes" <?php echo e(request('mode_paiement') == 'especes' ? 'selected' : ''); ?>>Espèces</option>
                        <option value="carte" <?php echo e(request('mode_paiement') == 'carte' ? 'selected' : ''); ?>>Carte bancaire</option>
                        <option value="prelevement" <?php echo e(request('mode_paiement') == 'prelevement' ? 'selected' : ''); ?>>Prélèvement</option>
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

    <!-- Liste des règlements -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Référence</th>
                            <th>Date</th>
                            <th>Client</th>
                            <th>Facture</th>
                            <th>Mode de paiement</th>
                            <th>Montant</th>
                            <th><?php echo e(__('app.status')); ?></th>
                            <th><?php echo e(__('app.actions')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $reglements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reglement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><strong><?php echo e($reglement->reference); ?></strong></td>
                            <td><?php echo e($reglement->date_reglement->format('d/m/Y')); ?></td>
                            <td>
                                <?php if($reglement->facture && $reglement->facture->client): ?>
                                    <strong><?php echo e($reglement->facture->client->nom); ?> <?php echo e($reglement->facture->client->prenom); ?></strong>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($reglement->facture): ?>
                                    <a href="<?php echo e(route('factures.show', $reglement->facture)); ?>">
                                        <?php echo e($reglement->facture->numero_facture); ?>

                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($reglement->mode_paiement == 'virement'): ?>
                                    <i class="fas fa-university me-1"></i>Virement
                                <?php elseif($reglement->mode_paiement == 'cheque'): ?>
                                    <i class="fas fa-money-check me-1"></i>Chèque
                                <?php elseif($reglement->mode_paiement == 'especes'): ?>
                                    <i class="fas fa-money-bill me-1"></i>Espèces
                                <?php elseif($reglement->mode_paiement == 'carte'): ?>
                                    <i class="fas fa-credit-card me-1"></i>Carte
                                <?php else: ?>
                                    <i class="fas fa-exchange-alt me-1"></i>Prélèvement
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo e(number_format($reglement->montant, 2)); ?> €</strong></td>
                            <td>
                                <?php if($reglement->statut == 'valide'): ?>
                                    <span class="badge bg-success">Validé</span>
                                <?php elseif($reglement->statut == 'en_attente'): ?>
                                    <span class="badge bg-warning">En Attente</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Refusé</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_reglements')): ?>
                                    <a href="<?php echo e(route('reglements.show', $reglement)); ?>" class="btn btn-sm btn-info" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit_reglements')): ?>
                                    <a href="<?php echo e(route('reglements.edit', $reglement)); ?>" class="btn btn-sm btn-warning" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete_reglements')): ?>
                                    <form action="<?php echo e(route('reglements.destroy', $reglement)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr ?')">
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
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-euro-sign fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Aucun règlement trouvé</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                <?php echo e($reglements->links()); ?>

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
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp2025\htdocs\boxibox\resources\views/reglements/index.blade.php ENDPATH**/ ?>