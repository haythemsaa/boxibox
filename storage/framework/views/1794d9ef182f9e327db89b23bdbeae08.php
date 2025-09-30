<?php $__env->startSection('title', __('app.contracts') . ' - ' . $contrat->numero_contrat); ?>

<?php $__env->startSection('actions'); ?>
<div class="btn-group" role="group">
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit_contrats')): ?>
    <a href="<?php echo e(route('contrats.edit', $contrat)); ?>" class="btn btn-warning">
        <i class="fas fa-edit me-2"></i><?php echo e(__('app.edit')); ?>

    </a>
    <?php endif; ?>
    <?php if($contrat->statut == 'en_attente'): ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('activate_contrats')): ?>
        <form action="<?php echo e(route('contrats.activate', $contrat)); ?>" method="POST" class="d-inline">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-check me-2"></i><?php echo e(__('app.activate')); ?>

            </button>
        </form>
        <?php endif; ?>
    <?php endif; ?>
    <?php if($contrat->statut == 'actif'): ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('terminate_contrats')): ?>
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#terminateModal">
            <i class="fas fa-times-circle me-2"></i><?php echo e(__('app.terminate')); ?>

        </button>
        <?php endif; ?>
    <?php endif; ?>
    <a href="<?php echo e(route('contrats.index')); ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i><?php echo e(__('app.back')); ?>

    </a>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Statistiques rapides -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card text-center">
                <div class="stat-number text-primary"><?php echo e($stats['factures_count']); ?></div>
                <div class="stat-label"><?php echo e(__('app.invoices')); ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card text-center">
                <div class="stat-number text-success"><?php echo e(number_format($stats['montant_facture'] ?? 0, 2)); ?> €</div>
                <div class="stat-label"><?php echo e(__('app.total_invoiced')); ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card text-center">
                <div class="stat-number text-info">
                    <?php if(isset($stats['derniere_facture']) && $stats['derniere_facture']): ?>
                        <?php echo e($stats['derniere_facture']->format('d/m/Y')); ?>

                    <?php else: ?>
                        N/A
                    <?php endif; ?>
                </div>
                <div class="stat-label"><?php echo e(__('app.last_invoice')); ?></div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Informations du contrat -->
        <div class="col-md-5">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-file-contract me-2"></i><?php echo e(__('app.contract_information')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small"><?php echo e(__('app.contract_number')); ?></label>
                        <div><strong><?php echo e($contrat->numero_contrat); ?></strong></div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small"><?php echo e(__('app.status')); ?></label>
                        <div>
                            <?php if($contrat->statut == 'actif'): ?>
                                <span class="badge bg-success"><?php echo e(__('app.active')); ?></span>
                            <?php elseif($contrat->statut == 'en_attente'): ?>
                                <span class="badge bg-warning"><?php echo e(__('app.pending')); ?></span>
                            <?php elseif($contrat->statut == 'brouillon'): ?>
                                <span class="badge bg-secondary"><?php echo e(__('app.draft')); ?></span>
                            <?php elseif($contrat->statut == 'termine'): ?>
                                <span class="badge bg-info"><?php echo e(__('app.completed')); ?></span>
                            <?php elseif($contrat->statut == 'suspendu'): ?>
                                <span class="badge bg-warning"><?php echo e(__('app.suspended')); ?></span>
                            <?php else: ?>
                                <span class="badge bg-danger"><?php echo e(__('app.cancelled')); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="text-muted small"><?php echo e(__('app.start_date')); ?></label>
                        <div><?php echo e($contrat->date_debut->format('d/m/Y')); ?></div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small"><?php echo e(__('app.end_date')); ?></label>
                        <div>
                            <?php if($contrat->date_fin): ?>
                                <?php echo e($contrat->date_fin->format('d/m/Y')); ?>

                            <?php else: ?>
                                <span class="text-muted"><?php echo e(__('app.undetermined')); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small"><?php echo e(__('app.duration_type')); ?></label>
                        <div>
                            <?php if($contrat->duree_type == 'determinee'): ?>
                                <?php echo e(__('app.fixed_term')); ?>

                            <?php else: ?>
                                <?php echo e(__('app.indefinite')); ?>

                            <?php endif; ?>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="text-muted small"><?php echo e(__('app.monthly_price')); ?></label>
                        <div><strong class="text-primary"><?php echo e(number_format($contrat->prix_mensuel, 2)); ?> €</strong></div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small"><?php echo e(__('app.deposit')); ?></label>
                        <div><?php echo e(number_format($contrat->caution ?? 0, 2)); ?> €</div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small"><?php echo e(__('app.processing_fee')); ?></label>
                        <div><?php echo e(number_format($contrat->frais_dossier ?? 0, 2)); ?> €</div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="text-muted small"><?php echo e(__('app.billing_frequency')); ?></label>
                        <div>
                            <?php if($contrat->periodicite_facturation == 'mensuelle'): ?>
                                <?php echo e(__('app.monthly')); ?>

                            <?php elseif($contrat->periodicite_facturation == 'trimestrielle'): ?>
                                <?php echo e(__('app.quarterly')); ?>

                            <?php elseif($contrat->periodicite_facturation == 'semestrielle'): ?>
                                <?php echo e(__('app.biannual')); ?>

                            <?php else: ?>
                                <?php echo e(__('app.annual')); ?>

                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small"><?php echo e(__('app.automatic_renewal')); ?></label>
                        <div>
                            <?php if($contrat->renouvellement_automatique): ?>
                                <span class="badge bg-success"><?php echo e(__('app.yes')); ?></span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?php echo e(__('app.no')); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small"><?php echo e(__('app.insurance_included')); ?></label>
                        <div>
                            <?php if($contrat->assurance_incluse): ?>
                                <span class="badge bg-success"><?php echo e(__('app.yes')); ?></span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?php echo e(__('app.no')); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if($contrat->notes): ?>
                    <hr>
                    <div class="mb-3">
                        <label class="text-muted small"><?php echo e(__('app.notes')); ?></label>
                        <div class="text-muted"><?php echo e($contrat->notes); ?></div>
                    </div>
                    <?php endif; ?>

                    <?php if($contrat->date_signature): ?>
                    <hr>
                    <div class="mb-3">
                        <label class="text-muted small"><?php echo e(__('app.signature_date')); ?></label>
                        <div><?php echo e($contrat->date_signature->format('d/m/Y H:i')); ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Informations liées -->
        <div class="col-md-7">
            <!-- Box -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-box me-2"></i><?php echo e(__('app.box')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <?php if($contrat->box): ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small"><?php echo e(__('app.box_number')); ?></label>
                                    <div><strong><?php echo e($contrat->box->numero); ?></strong></div>
                                </div>
                                <div class="mb-3">
                                    <label class="text-muted small"><?php echo e(__('app.type')); ?></label>
                                    <div><?php echo e($contrat->box->famille->nom ?? 'N/A'); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small"><?php echo e(__('app.surface')); ?></label>
                                    <div><?php echo e($contrat->box->surface ?? 'N/A'); ?> m²</div>
                                </div>
                                <div class="mb-3">
                                    <label class="text-muted small"><?php echo e(__('app.floor')); ?></label>
                                    <div><?php echo e($contrat->box->etage ?? 'N/A'); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="text-end">
                            <a href="<?php echo e(route('boxes.show', $contrat->box)); ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye me-2"></i><?php echo e(__('app.view_details')); ?>

                            </a>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center py-4"><?php echo e(__('app.no_box_assigned')); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Client -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i><?php echo e(__('app.client')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small"><?php echo e(__('app.name')); ?></label>
                                <div><strong><?php echo e($contrat->client->nom); ?> <?php echo e($contrat->client->prenom); ?></strong></div>
                            </div>
                            <?php if($contrat->client->raison_sociale): ?>
                            <div class="mb-3">
                                <label class="text-muted small"><?php echo e(__('app.company_name')); ?></label>
                                <div><?php echo e($contrat->client->raison_sociale); ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small"><?php echo e(__('app.email')); ?></label>
                                <div><a href="mailto:<?php echo e($contrat->client->email); ?>"><?php echo e($contrat->client->email); ?></a></div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small"><?php echo e(__('app.phone')); ?></label>
                                <div><a href="tel:<?php echo e($contrat->client->telephone); ?>"><?php echo e($contrat->client->telephone); ?></a></div>
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <a href="<?php echo e(route('clients.show', $contrat->client)); ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye me-2"></i><?php echo e(__('app.view_details')); ?>

                        </a>
                    </div>
                </div>
            </div>

            <!-- Factures -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-file-invoice me-2"></i><?php echo e(__('app.invoices')); ?>

                    </h5>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_factures')): ?>
                    <a href="<?php echo e(route('factures.create', ['contrat_id' => $contrat->id])); ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-2"></i><?php echo e(__('app.new_invoice')); ?>

                    </a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if($contrat->factures->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th><?php echo e(__('app.invoice_number')); ?></th>
                                        <th><?php echo e(__('app.date')); ?></th>
                                        <th><?php echo e(__('app.amount')); ?></th>
                                        <th><?php echo e(__('app.status')); ?></th>
                                        <th><?php echo e(__('app.actions')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $contrat->factures->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $facture): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><strong><?php echo e($facture->numero_facture); ?></strong></td>
                                        <td><?php echo e($facture->date_emission->format('d/m/Y')); ?></td>
                                        <td><?php echo e(number_format($facture->montant_ttc, 2)); ?> €</td>
                                        <td>
                                            <?php if($facture->statut == 'payee'): ?>
                                                <span class="badge bg-success"><?php echo e(__('app.paid')); ?></span>
                                            <?php elseif($facture->statut == 'en_attente'): ?>
                                                <span class="badge bg-warning"><?php echo e(__('app.pending')); ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-danger"><?php echo e(__('app.overdue')); ?></span>
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
                        <?php if($contrat->factures->count() > 10): ?>
                            <div class="text-center mt-3">
                                <a href="<?php echo e(route('factures.index', ['contrat_id' => $contrat->id])); ?>" class="btn btn-sm btn-outline-primary">
                                    <?php echo e(__('app.view_all_invoices')); ?> (<?php echo e($contrat->factures->count()); ?>)
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-4">
                            <i class="fas fa-file-invoice fa-3x mb-3 d-block"></i>
                            <?php echo e(__('app.no_invoices')); ?>

                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Services -->
            <?php if($contrat->services && $contrat->services->count() > 0): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-concierge-bell me-2"></i><?php echo e(__('app.services')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?php $__currentLoopData = $contrat->services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?php echo e($service->nom); ?></strong>
                                <?php if($service->description): ?>
                                    <br><small class="text-muted"><?php echo e($service->description); ?></small>
                                <?php endif; ?>
                            </div>
                            <div>
                                <span class="badge bg-primary"><?php echo e(number_format($service->pivot->prix ?? $service->prix, 2)); ?> €</span>
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

<!-- Modal pour terminer le contrat -->
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('terminate_contrats')): ?>
<div class="modal fade" id="terminateModal" tabindex="-1" aria-labelledby="terminateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('contrats.terminate', $contrat)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="terminateModalLabel"><?php echo e(__('app.terminate_contract')); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="date_fin" class="form-label"><?php echo e(__('app.end_date')); ?></label>
                        <input type="date" class="form-control" id="date_fin" name="date_fin" required>
                    </div>
                    <div class="mb-3">
                        <label for="motif" class="form-label"><?php echo e(__('app.reason')); ?></label>
                        <textarea class="form-control" id="motif" name="motif" rows="3"></textarea>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo e(__('app.terminate_contract_warning')); ?>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo e(__('app.cancel')); ?></button>
                    <button type="submit" class="btn btn-danger"><?php echo e(__('app.terminate')); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

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
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp2025\htdocs\boxibox\resources\views/contrats/show.blade.php ENDPATH**/ ?>