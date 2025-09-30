<?php $__env->startSection('title', 'Prospects'); ?>

<?php $__env->startSection('actions'); ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_prospects')): ?>
    <a href="<?php echo e(route('prospects.create')); ?>" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Nouveau Prospect
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
                <div class="stat-label">Total</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card text-center">
                <div class="stat-number text-info"><?php echo e($stats['nouveaux']); ?></div>
                <div class="stat-label">Nouveaux</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card text-center">
                <div class="stat-number text-warning"><?php echo e($stats['contactes']); ?></div>
                <div class="stat-label">Contactés</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card text-center">
                <div class="stat-number text-success"><?php echo e($stats['interesses']); ?></div>
                <div class="stat-label">Intéressés</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card text-center">
                <div class="stat-number text-primary"><?php echo e($stats['convertis']); ?></div>
                <div class="stat-label">Convertis</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card text-center">
                <div class="stat-number text-danger"><?php echo e($stats['perdus']); ?></div>
                <div class="stat-label">Perdus</div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Recherche</label>
                    <input type="text" name="search" class="form-control" value="<?php echo e(request('search')); ?>"
                           placeholder="Nom, email, téléphone...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Statut</label>
                    <select name="statut" class="form-select">
                        <option value="">Tous</option>
                        <option value="nouveau" <?php echo e(request('statut') == 'nouveau' ? 'selected' : ''); ?>>Nouveau</option>
                        <option value="contacte" <?php echo e(request('statut') == 'contacte' ? 'selected' : ''); ?>>Contacté</option>
                        <option value="interesse" <?php echo e(request('statut') == 'interesse' ? 'selected' : ''); ?>>Intéressé</option>
                        <option value="perdu" <?php echo e(request('statut') == 'perdu' ? 'selected' : ''); ?>>Perdu</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Origine</label>
                    <select name="origine" class="form-select">
                        <option value="">Toutes</option>
                        <option value="site_web" <?php echo e(request('origine') == 'site_web' ? 'selected' : ''); ?>>Site web</option>
                        <option value="telephone" <?php echo e(request('origine') == 'telephone' ? 'selected' : ''); ?>>Téléphone</option>
                        <option value="visite" <?php echo e(request('origine') == 'visite' ? 'selected' : ''); ?>>Visite</option>
                        <option value="recommandation" <?php echo e(request('origine') == 'recommandation' ? 'selected' : ''); ?>>Recommandation</option>
                        <option value="publicite" <?php echo e(request('origine') == 'publicite' ? 'selected' : ''); ?>>Publicité</option>
                        <option value="autre" <?php echo e(request('origine') == 'autre' ? 'selected' : ''); ?>>Autre</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Assigné à</label>
                    <select name="assigned_to" class="form-select">
                        <option value="">Tous</option>
                        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($user->id); ?>" <?php echo e(request('assigned_to') == $user->id ? 'selected' : ''); ?>>
                                <?php echo e($user->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">État</label>
                    <select name="converted" class="form-select">
                        <option value="">Tous</option>
                        <option value="0" <?php echo e(request('converted') === '0' ? 'selected' : ''); ?>>Non convertis</option>
                        <option value="1" <?php echo e(request('converted') === '1' ? 'selected' : ''); ?>>Convertis</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des prospects -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Contact</th>
                            <th>Origine</th>
                            <th>Statut</th>
                            <th>Assigné à</th>
                            <th>Dernière interaction</th>
                            <th>Relance prévue</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $prospects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prospect): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="<?php echo e($prospect->converted_to_client_at ? 'table-success' : ''); ?>">
                                <td>
                                    <strong><?php echo e($prospect->prenom); ?> <?php echo e($prospect->nom); ?></strong>
                                    <?php if($prospect->converted_to_client_at): ?>
                                        <span class="badge bg-success ms-2">Converti</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($prospect->email): ?>
                                        <div><i class="fas fa-envelope text-muted me-1"></i><?php echo e($prospect->email); ?></div>
                                    <?php endif; ?>
                                    <?php if($prospect->telephone): ?>
                                        <div><i class="fas fa-phone text-muted me-1"></i><?php echo e($prospect->telephone); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?php echo e(ucfirst(str_replace('_', ' ', $prospect->origine))); ?>

                                    </span>
                                </td>
                                <td>
                                    <?php
                                        $badgeClass = match($prospect->statut) {
                                            'nouveau' => 'bg-info',
                                            'contacte' => 'bg-warning',
                                            'interesse' => 'bg-success',
                                            'perdu' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                    ?>
                                    <span class="badge <?php echo e($badgeClass); ?>">
                                        <?php echo e(ucfirst($prospect->statut)); ?>

                                    </span>
                                </td>
                                <td>
                                    <?php echo e($prospect->assignedUser->name ?? '-'); ?>

                                </td>
                                <td>
                                    <?php if($prospect->interactions->count() > 0): ?>
                                        <?php echo e($prospect->interactions->first()->date_interaction->format('d/m/Y')); ?>

                                        <small class="text-muted d-block">
                                            <?php echo e($prospect->interactions->first()->type_interaction); ?>

                                        </small>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($prospect->date_relance): ?>
                                        <span class="badge <?php echo e($prospect->date_relance->isPast() ? 'bg-danger' : 'bg-warning'); ?>">
                                            <?php echo e($prospect->date_relance->format('d/m/Y')); ?>

                                        </span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="<?php echo e(route('prospects.show', $prospect)); ?>"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit_prospects')): ?>
                                            <?php if(!$prospect->converted_to_client_at): ?>
                                                <a href="<?php echo e(route('prospects.edit', $prospect)); ?>"
                                                   class="btn btn-sm btn-outline-secondary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('convert_prospects')): ?>
                                            <?php if(!$prospect->converted_to_client_at): ?>
                                                <button type="button" class="btn btn-sm btn-outline-success"
                                                        onclick="convertProspect(<?php echo e($prospect->id); ?>)">
                                                    <i class="fas fa-user-plus"></i>
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-users fa-2x mb-3"></i>
                                    <p>Aucun prospect trouvé.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php echo e($prospects->appends(request()->query())->links()); ?>

        </div>
    </div>
</div>

<!-- Modal de conversion -->
<div class="modal fade" id="convertModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Convertir en client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="convertForm">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Type de client</label>
                        <select name="type_client" class="form-select" required>
                            <option value="particulier">Particulier</option>
                            <option value="entreprise">Entreprise</option>
                        </select>
                    </div>
                    <p class="text-muted">
                        Les informations du prospect seront copiées dans la fiche client.
                        Vous pourrez les compléter après la conversion.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">Convertir</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function convertProspect(prospectId) {
    const form = document.getElementById('convertForm');
    form.action = `/commercial/prospects/${prospectId}/convert`;

    const modal = new bootstrap.Modal(document.getElementById('convertModal'));
    modal.show();
}
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp2025\htdocs\boxibox\resources\views/prospects/index.blade.php ENDPATH**/ ?>