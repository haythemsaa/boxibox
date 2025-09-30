<?php $__env->startSection('title', __('app.invoices') . ' - ' . $facture->numero_facture); ?>

<?php $__env->startSection('actions'); ?>
<div class="btn-group" role="group">
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit_factures')): ?>
    <a href="<?php echo e(route('factures.edit', $facture)); ?>" class="btn btn-warning">
        <i class="fas fa-edit me-2"></i><?php echo e(__('app.edit')); ?>

    </a>
    <?php endif; ?>
    <?php if($facture->statut != 'payee'): ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('pay_factures')): ?>
        <form action="<?php echo e(route('factures.marquer-payee', $facture)); ?>" method="POST" class="d-inline">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-check me-2"></i>Marquer payée
            </button>
        </form>
        <?php endif; ?>
    <?php endif; ?>
    <a href="<?php echo e(route('factures.pdf', $facture)); ?>" class="btn btn-danger" target="_blank">
        <i class="fas fa-file-pdf me-2"></i>PDF
    </a>
    <a href="<?php echo e(route('factures.index')); ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i><?php echo e(__('app.back')); ?>

    </a>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <!-- Informations de la facture -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-file-invoice me-2"></i>Facture <?php echo e($facture->numero_facture); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <!-- En-tête facture -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted">Client</h6>
                            <strong><?php echo e($facture->client->nom); ?> <?php echo e($facture->client->prenom); ?></strong>
                            <?php if($facture->client->raison_sociale): ?>
                                <br><?php echo e($facture->client->raison_sociale); ?>

                            <?php endif; ?>
                            <br><?php echo e($facture->client->adresse); ?>

                            <br><?php echo e($facture->client->code_postal); ?> <?php echo e($facture->client->ville); ?>

                            <br><?php echo e($facture->client->email); ?>

                        </div>
                        <div class="col-md-6 text-end">
                            <p class="mb-1"><strong>N° Facture:</strong> <?php echo e($facture->numero_facture); ?></p>
                            <p class="mb-1"><strong>Date d'émission:</strong> <?php echo e($facture->date_emission->format('d/m/Y')); ?></p>
                            <p class="mb-1"><strong>Date d'échéance:</strong> <?php echo e($facture->date_echeance->format('d/m/Y')); ?></p>
                            <?php if($facture->contrat): ?>
                                <p class="mb-1"><strong>Contrat:</strong> <?php echo e($facture->contrat->numero_contrat); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <hr>

                    <!-- Statut -->
                    <div class="mb-4">
                        <h6 class="text-muted"><?php echo e(__('app.status')); ?></h6>
                        <?php if($facture->statut == 'payee'): ?>
                            <span class="badge bg-success fs-5"><?php echo e(__('app.paid')); ?></span>
                            <?php if($facture->date_paiement): ?>
                                <small class="text-muted">le <?php echo e($facture->date_paiement->format('d/m/Y')); ?></small>
                            <?php endif; ?>
                        <?php elseif($facture->statut == 'en_attente'): ?>
                            <span class="badge bg-warning fs-5"><?php echo e(__('app.pending')); ?></span>
                        <?php elseif($facture->statut == 'en_retard'): ?>
                            <span class="badge bg-danger fs-5">En Retard</span>
                        <?php elseif($facture->statut == 'brouillon'): ?>
                            <span class="badge bg-secondary fs-5"><?php echo e(__('app.draft')); ?></span>
                        <?php else: ?>
                            <span class="badge bg-dark fs-5"><?php echo e(__('app.cancelled')); ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Lignes de facture -->
                    <?php if($facture->lignes && $facture->lignes->count() > 0): ?>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Description</th>
                                    <th class="text-center">Quantité</th>
                                    <th class="text-end">Prix unitaire HT</th>
                                    <th class="text-end">Montant HT</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $facture->lignes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ligne): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($ligne->description); ?></td>
                                    <td class="text-center"><?php echo e($ligne->quantite); ?></td>
                                    <td class="text-end"><?php echo e(number_format($ligne->prix_unitaire, 2)); ?> €</td>
                                    <td class="text-end"><?php echo e(number_format($ligne->montant_ht, 2)); ?> €</td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>

                    <!-- Totaux -->
                    <div class="row">
                        <div class="col-md-6 offset-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Sous-total HT:</strong></td>
                                    <td class="text-end"><?php echo e(number_format($facture->montant_ht, 2)); ?> €</td>
                                </tr>
                                <tr>
                                    <td><strong>TVA (<?php echo e($facture->taux_tva); ?>%):</strong></td>
                                    <td class="text-end"><?php echo e(number_format($facture->montant_tva, 2)); ?> €</td>
                                </tr>
                                <tr class="table-active">
                                    <td><strong>Total TTC:</strong></td>
                                    <td class="text-end"><strong class="fs-4 text-primary"><?php echo e(number_format($facture->montant_ttc, 2)); ?> €</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <?php if($facture->notes): ?>
                    <hr>
                    <div class="mb-3">
                        <h6 class="text-muted"><?php echo e(__('app.notes')); ?></h6>
                        <p class="text-muted"><?php echo e($facture->notes); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Informations complémentaires -->
        <div class="col-md-4">
            <!-- Informations client -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-user me-2"></i><?php echo e(__('app.client')); ?>

                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong><?php echo e($facture->client->nom); ?> <?php echo e($facture->client->prenom); ?></strong></p>
                    <?php if($facture->client->raison_sociale): ?>
                        <p class="mb-2 text-muted"><?php echo e($facture->client->raison_sociale); ?></p>
                    <?php endif; ?>
                    <p class="mb-1"><i class="fas fa-envelope me-2"></i><?php echo e($facture->client->email); ?></p>
                    <?php if($facture->client->telephone): ?>
                        <p class="mb-1"><i class="fas fa-phone me-2"></i><?php echo e($facture->client->telephone); ?></p>
                    <?php endif; ?>
                    <div class="mt-3">
                        <a href="<?php echo e(route('clients.show', $facture->client)); ?>" class="btn btn-sm btn-outline-primary w-100">
                            <i class="fas fa-eye me-2"></i>Voir le client
                        </a>
                    </div>
                </div>
            </div>

            <!-- Informations contrat -->
            <?php if($facture->contrat): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-file-contract me-2"></i><?php echo e(__('app.contract')); ?>

                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong><?php echo e($facture->contrat->numero_contrat); ?></strong></p>
                    <p class="mb-1"><strong>Box:</strong> <?php echo e($facture->contrat->box->numero ?? 'N/A'); ?></p>
                    <p class="mb-1"><strong>Prix mensuel:</strong> <?php echo e(number_format($facture->contrat->prix_mensuel, 2)); ?> €</p>
                    <div class="mt-3">
                        <a href="<?php echo e(route('contrats.show', $facture->contrat)); ?>" class="btn btn-sm btn-outline-primary w-100">
                            <i class="fas fa-eye me-2"></i>Voir le contrat
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Paiements -->
            <?php if($facture->paiements && $facture->paiements->count() > 0): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-euro-sign me-2"></i>Paiements
                    </h6>
                </div>
                <div class="card-body">
                    <?php $__currentLoopData = $facture->paiements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $paiement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="border-bottom pb-2 mb-2">
                        <p class="mb-1"><strong><?php echo e(number_format($paiement->montant, 2)); ?> €</strong></p>
                        <p class="mb-1 text-muted small"><?php echo e($paiement->date_paiement->format('d/m/Y')); ?></p>
                        <p class="mb-0 text-muted small"><?php echo e($paiement->mode_paiement); ?></p>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Historique -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-history me-2"></i>Historique
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-1 small"><strong>Créée le:</strong> <?php echo e($facture->created_at->format('d/m/Y H:i')); ?></p>
                    <p class="mb-0 small"><strong>Modifiée le:</strong> <?php echo e($facture->updated_at->format('d/m/Y H:i')); ?></p>
                    <?php if($facture->date_paiement): ?>
                        <p class="mb-0 small"><strong>Payée le:</strong> <?php echo e($facture->date_paiement->format('d/m/Y H:i')); ?></p>
                    <?php endif; ?>
                </div>
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

.card-header h6 {
    color: #333;
}
</style>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp2025\htdocs\boxibox\resources\views/factures/show.blade.php ENDPATH**/ ?>