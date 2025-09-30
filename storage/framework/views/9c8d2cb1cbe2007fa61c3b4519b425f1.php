<?php $__env->startSection('title', __('app.clients') . ' - ' . $client->nom . ' ' . $client->prenom); ?>

<?php $__env->startSection('actions'); ?>
<div class="btn-group" role="group">
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit_clients')): ?>
    <a href="<?php echo e(route('clients.edit', $client)); ?>" class="btn btn-warning">
        <i class="fas fa-edit me-2"></i><?php echo e(__('app.edit')); ?>

    </a>
    <?php endif; ?>
    <a href="<?php echo e(route('clients.index')); ?>" class="btn btn-secondary">
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
                <div class="stat-number text-primary"><?php echo e($stats['contrats_actifs']); ?></div>
                <div class="stat-label">Contrats Actifs</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-warning"><?php echo e($stats['factures_en_attente']); ?></div>
                <div class="stat-label">Factures en Attente</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-danger"><?php echo e(number_format($stats['montant_du'], 2)); ?> €</div>
                <div class="stat-label">Montant Dû</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-info"><?php echo e($stats['documents_count']); ?></div>
                <div class="stat-label">Documents</div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Informations client -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i>Informations Client
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Type de client</label>
                        <div>
                            <?php if($client->type_client == 'particulier'): ?>
                                <span class="badge bg-info">Particulier</span>
                            <?php else: ?>
                                <span class="badge bg-warning">Entreprise</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if($client->raison_sociale): ?>
                    <div class="mb-3">
                        <label class="text-muted small">Raison sociale</label>
                        <div><strong><?php echo e($client->raison_sociale); ?></strong></div>
                    </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="text-muted small"><?php echo e(__('app.firstname')); ?> <?php echo e(__('app.lastname')); ?></label>
                        <div><strong><?php echo e($client->prenom); ?> <?php echo e($client->nom); ?></strong></div>
                    </div>

                    <?php if($client->siret): ?>
                    <div class="mb-3">
                        <label class="text-muted small">SIRET</label>
                        <div><?php echo e($client->siret); ?></div>
                    </div>
                    <?php endif; ?>

                    <hr>

                    <div class="mb-3">
                        <label class="text-muted small"><?php echo e(__('app.email')); ?></label>
                        <div>
                            <a href="mailto:<?php echo e($client->email); ?>">
                                <i class="fas fa-envelope me-2"></i><?php echo e($client->email); ?>

                            </a>
                        </div>
                    </div>

                    <?php if($client->telephone): ?>
                    <div class="mb-3">
                        <label class="text-muted small"><?php echo e(__('app.phone')); ?></label>
                        <div>
                            <a href="tel:<?php echo e($client->telephone); ?>">
                                <i class="fas fa-phone me-2"></i><?php echo e($client->telephone); ?>

                            </a>
                        </div>
                    </div>
                    <?php endif; ?>

                    <hr>

                    <div class="mb-3">
                        <label class="text-muted small"><?php echo e(__('app.address')); ?></label>
                        <div>
                            <?php echo e($client->adresse); ?><br>
                            <?php echo e($client->code_postal); ?> <?php echo e($client->ville); ?><br>
                            <?php echo e($client->pays); ?>

                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="text-muted small"><?php echo e(__('app.status')); ?></label>
                        <div>
                            <?php if($client->is_active): ?>
                                <span class="badge bg-success"><?php echo e(__('app.active')); ?></span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?php echo e(__('app.inactive')); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if($client->notes): ?>
                    <div class="mb-3">
                        <label class="text-muted small"><?php echo e(__('app.notes')); ?></label>
                        <div class="text-muted"><?php echo e($client->notes); ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Contrats et autres informations -->
        <div class="col-md-8">
            <!-- Contrats -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-file-contract me-2"></i><?php echo e(__('app.contracts')); ?>

                    </h5>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_contrats')): ?>
                    <a href="<?php echo e(route('contrats.create', ['client_id' => $client->id])); ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-2"></i>Nouveau Contrat
                    </a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if($client->contrats->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>N° Contrat</th>
                                        <th>Box</th>
                                        <th>Date Début</th>
                                        <th>Date Fin</th>
                                        <th>Prix Mensuel</th>
                                        <th><?php echo e(__('app.status')); ?></th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $client->contrats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $contrat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><strong><?php echo e($contrat->numero_contrat); ?></strong></td>
                                        <td><?php echo e($contrat->box->numero ?? 'N/A'); ?></td>
                                        <td><?php echo e($contrat->date_debut->format('d/m/Y')); ?></td>
                                        <td><?php echo e($contrat->date_fin ? $contrat->date_fin->format('d/m/Y') : 'Indéterminé'); ?></td>
                                        <td><?php echo e(number_format($contrat->prix_mensuel, 2)); ?> €</td>
                                        <td>
                                            <?php if($contrat->statut == 'actif'): ?>
                                                <span class="badge bg-success">Actif</span>
                                            <?php elseif($contrat->statut == 'en_attente'): ?>
                                                <span class="badge bg-warning">En attente</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><?php echo e($contrat->statut); ?></span>
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
                            Aucun contrat pour ce client
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Factures -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-file-invoice me-2"></i><?php echo e(__('app.invoices')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <?php if($client->factures->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>N° Facture</th>
                                        <th>Date</th>
                                        <th>Montant TTC</th>
                                        <th><?php echo e(__('app.status')); ?></th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $client->factures->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $facture): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><strong><?php echo e($facture->numero_facture); ?></strong></td>
                                        <td><?php echo e($facture->date_emission->format('d/m/Y')); ?></td>
                                        <td><?php echo e(number_format($facture->montant_ttc, 2)); ?> €</td>
                                        <td>
                                            <?php if($facture->statut == 'payee'): ?>
                                                <span class="badge bg-success">Payée</span>
                                            <?php elseif($facture->statut == 'en_attente'): ?>
                                                <span class="badge bg-warning">En attente</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">En retard</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_factures')): ?>
                                            <a href="<?php echo e(route('factures.show', $facture)); ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if($client->factures->count() > 10): ?>
                            <div class="text-center">
                                <a href="<?php echo e(route('factures.index', ['client_id' => $client->id])); ?>" class="btn btn-sm btn-outline-primary">
                                    Voir toutes les factures (<?php echo e($client->factures->count()); ?>)
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-4">
                            <i class="fas fa-file-invoice fa-3x mb-3 d-block"></i>
                            Aucune facture pour ce client
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Documents -->
            <?php if($client->documents->count() > 0): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-folder me-2"></i>Documents
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?php $__currentLoopData = $client->documents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-file-pdf me-2"></i>
                                <strong><?php echo e($document->nom); ?></strong>
                                <br>
                                <small class="text-muted"><?php echo e($document->created_at->format('d/m/Y H:i')); ?></small>
                            </div>
                            <div>
                                <a href="<?php echo e(Storage::url($document->chemin)); ?>" class="btn btn-sm btn-primary" target="_blank">
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp2025\htdocs\boxibox\resources\views/clients/show.blade.php ENDPATH**/ ?>