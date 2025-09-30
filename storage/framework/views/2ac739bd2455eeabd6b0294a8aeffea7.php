<?php $__env->startSection('title', __('app.box') . ' - ' . $box->numero); ?>

<?php $__env->startSection('actions'); ?>
<div class="btn-group" role="group">
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit_boxes')): ?>
    <a href="<?php echo e(route('boxes.edit', $box)); ?>" class="btn btn-warning">
        <i class="fas fa-edit me-2"></i><?php echo e(__('app.edit')); ?>

    </a>
    <?php endif; ?>
    <?php if($box->statut == 'libre'): ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('reserve_boxes')): ?>
        <form action="<?php echo e(route('boxes.reserve', $box)); ?>" method="POST" class="d-inline">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn btn-info">
                <i class="fas fa-lock me-2"></i>Réserver
            </button>
        </form>
        <?php endif; ?>
    <?php elseif($box->statut == 'reserve'): ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('liberer_boxes')): ?>
        <form action="<?php echo e(route('boxes.liberer', $box)); ?>" method="POST" class="d-inline">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-unlock me-2"></i>Libérer
            </button>
        </form>
        <?php endif; ?>
    <?php endif; ?>
    <a href="<?php echo e(route('boxes.index')); ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i><?php echo e(__('app.back')); ?>

    </a>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Statistiques rapides -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-primary"><?php echo e($stats['contrats_actifs'] ?? 0); ?></div>
                <div class="stat-label">Contrats Actifs</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-success"><?php echo e(number_format($stats['revenus_mensuels'] ?? 0, 2)); ?> €</div>
                <div class="stat-label">Revenus Mensuels</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-info"><?php echo e($stats['jours_occupation'] ?? 0); ?></div>
                <div class="stat-label">Jours d'Occupation</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-warning"><?php echo e($stats['interventions_count'] ?? 0); ?></div>
                <div class="stat-label">Interventions</div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Informations du box -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-box me-2"></i>Informations du Box
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Numéro</label>
                        <div><strong class="fs-4"><?php echo e($box->numero); ?></strong></div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small"><?php echo e(__('app.status')); ?></label>
                        <div>
                            <?php if($box->statut == 'libre'): ?>
                                <span class="badge bg-success fs-6">Libre</span>
                            <?php elseif($box->statut == 'occupe'): ?>
                                <span class="badge bg-danger fs-6">Occupé</span>
                            <?php elseif($box->statut == 'reserve'): ?>
                                <span class="badge bg-warning fs-6">Réservé</span>
                            <?php elseif($box->statut == 'maintenance'): ?>
                                <span class="badge bg-secondary fs-6">Maintenance</span>
                            <?php else: ?>
                                <span class="badge bg-dark fs-6"><?php echo e($box->statut); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="text-muted small">Famille</label>
                        <div>
                            <?php if($box->famille): ?>
                                <strong><?php echo e($box->famille->nom); ?></strong>
                                <br><small class="text-muted"><?php echo e($box->famille->description); ?></small>
                            <?php else: ?>
                                <span class="text-muted">N/A</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">Emplacement</label>
                        <div>
                            <?php if($box->emplacement): ?>
                                <strong><?php echo e($box->emplacement->nom); ?></strong>
                                <?php if($box->emplacement->description): ?>
                                    <br><small class="text-muted"><?php echo e($box->emplacement->description); ?></small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">N/A</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="text-muted small"><?php echo e(__('app.surface')); ?></label>
                                <div><strong><?php echo e($box->surface); ?> m²</strong></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="text-muted small">Volume</label>
                                <div><strong><?php echo e($box->volume ?? 'N/A'); ?> m³</strong></div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">Prix de base</label>
                        <div><strong class="text-primary fs-5"><?php echo e(number_format($box->prix_base ?? 0, 2)); ?> €/mois</strong></div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">Prix mensuel</label>
                        <div><strong class="text-success fs-5"><?php echo e(number_format($box->prix_mensuel ?? 0, 2)); ?> €/mois</strong></div>
                    </div>

                    <?php if($box->description): ?>
                    <hr>
                    <div class="mb-3">
                        <label class="text-muted small">Description</label>
                        <div class="text-muted"><?php echo e($box->description); ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Contrats et historique -->
        <div class="col-md-8">
            <!-- Contrat actif -->
            <?php if($box->contratActif): ?>
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-file-contract me-2"></i>Contrat Actif
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small">N° Contrat</label>
                                <div><strong><?php echo e($box->contratActif->numero_contrat); ?></strong></div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">Client</label>
                                <div>
                                    <strong><?php echo e($box->contratActif->client->nom); ?> <?php echo e($box->contratActif->client->prenom); ?></strong>
                                    <?php if($box->contratActif->client->raison_sociale): ?>
                                        <br><small class="text-muted"><?php echo e($box->contratActif->client->raison_sociale); ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small">Date de début</label>
                                <div><?php echo e($box->contratActif->date_debut->format('d/m/Y')); ?></div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">Date de fin</label>
                                <div>
                                    <?php if($box->contratActif->date_fin): ?>
                                        <?php echo e($box->contratActif->date_fin->format('d/m/Y')); ?>

                                    <?php else: ?>
                                        <span class="text-muted">Indéterminée</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <a href="<?php echo e(route('contrats.show', $box->contratActif)); ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye me-2"></i>Voir le contrat
                        </a>
                        <a href="<?php echo e(route('clients.show', $box->contratActif->client)); ?>" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-user me-2"></i>Voir le client
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Historique des contrats -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>Historique des Contrats
                    </h5>
                </div>
                <div class="card-body">
                    <?php if($box->contrats && $box->contrats->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>N° Contrat</th>
                                        <th>Client</th>
                                        <th>Date Début</th>
                                        <th>Date Fin</th>
                                        <th>Prix</th>
                                        <th><?php echo e(__('app.status')); ?></th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $box->contrats->sortByDesc('date_debut'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $contrat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><strong><?php echo e($contrat->numero_contrat); ?></strong></td>
                                        <td><?php echo e($contrat->client->nom); ?> <?php echo e($contrat->client->prenom); ?></td>
                                        <td><?php echo e($contrat->date_debut->format('d/m/Y')); ?></td>
                                        <td>
                                            <?php if($contrat->date_fin): ?>
                                                <?php echo e($contrat->date_fin->format('d/m/Y')); ?>

                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e(number_format($contrat->prix_mensuel, 2)); ?> €</td>
                                        <td>
                                            <?php if($contrat->statut == 'actif'): ?>
                                                <span class="badge bg-success">Actif</span>
                                            <?php elseif($contrat->statut == 'termine'): ?>
                                                <span class="badge bg-secondary">Terminé</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning"><?php echo e($contrat->statut); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_contrats')): ?>
                                            <a href="<?php echo e(route('contrats.show', $contrat)); ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center py-4">
                            <i class="fas fa-file-contract fa-3x mb-3 d-block"></i>
                            Aucun contrat pour ce box
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Interventions techniques -->
            <?php if(isset($box->interventions) && $box->interventions->count() > 0): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-tools me-2"></i>Interventions Techniques
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th><?php echo e(__('app.status')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $box->interventions->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $intervention): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($intervention->date_intervention->format('d/m/Y')); ?></td>
                                    <td><?php echo e($intervention->type); ?></td>
                                    <td><?php echo e($intervention->description); ?></td>
                                    <td>
                                        <?php if($intervention->statut == 'terminee'): ?>
                                            <span class="badge bg-success">Terminée</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning"><?php echo e($intervention->statut); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
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
    font-size: 1.8rem;
    font-weight: bold;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 0.875rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.card-header h5 {
    color: #333;
}
</style>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp2025\htdocs\boxibox\resources\views/boxes/show.blade.php ENDPATH**/ ?>