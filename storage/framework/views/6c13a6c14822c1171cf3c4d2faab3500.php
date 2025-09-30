<?php $__env->startSection('title', __('app.add') . ' ' . __('app.invoices')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-plus me-2"></i><?php echo e(__('app.add')); ?> <?php echo e(__('app.invoices')); ?>

                    </h5>
                    <a href="<?php echo e(route('factures.index')); ?>" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i><?php echo e(__('app.back')); ?>

                    </a>
                </div>
                <div class="card-body">
                    <?php if($errors->any()): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li><?php echo e($error); ?></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo e(route('factures.store')); ?>" method="POST">
                        <?php echo csrf_field(); ?>

                        <!-- Informations Générales -->
                        <h6 class="border-bottom pb-2 mb-3">Informations Générales</h6>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="client_id" class="form-label">Client <span class="text-danger">*</span></label>
                                <select class="form-select <?php $__errorArgs = ['client_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        id="client_id" name="client_id" required>
                                    <option value="">Sélectionner un client</option>
                                    <?php $__currentLoopData = \App\Models\Client::orderBy('nom')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($client->id); ?>"
                                                data-email="<?php echo e($client->email); ?>"
                                                data-adresse="<?php echo e($client->adresse); ?>"
                                                data-ville="<?php echo e($client->ville); ?>"
                                                data-code-postal="<?php echo e($client->code_postal); ?>"
                                                <?php echo e(old('client_id', request('client_id')) == $client->id ? 'selected' : ''); ?>>
                                            <?php echo e($client->nom); ?> <?php echo e($client->prenom); ?>

                                            <?php if($client->raison_sociale): ?> - <?php echo e($client->raison_sociale); ?> <?php endif; ?>
                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['client_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-md-6">
                                <label for="contrat_id" class="form-label">Contrat</label>
                                <select class="form-select <?php $__errorArgs = ['contrat_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        id="contrat_id" name="contrat_id">
                                    <option value="">Aucun contrat (facture libre)</option>
                                    <?php $__currentLoopData = \App\Models\Contrat::with('client')->where('statut', 'actif')->orderBy('numero_contrat')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $contrat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($contrat->id); ?>"
                                                data-client-id="<?php echo e($contrat->client_id); ?>"
                                                data-prix="<?php echo e($contrat->prix_mensuel); ?>"
                                                <?php echo e(old('contrat_id', request('contrat_id')) == $contrat->id ? 'selected' : ''); ?>>
                                            <?php echo e($contrat->numero_contrat); ?> - <?php echo e($contrat->client->nom); ?> <?php echo e($contrat->client->prenom); ?> (<?php echo e(number_format($contrat->prix_mensuel, 2)); ?>€)
                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['contrat_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <!-- Dates -->
                        <h6 class="border-bottom pb-2 mb-3">Dates</h6>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="date_emission" class="form-label">Date d'émission <span class="text-danger">*</span></label>
                                <input type="date" class="form-control <?php $__errorArgs = ['date_emission'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       id="date_emission" name="date_emission"
                                       value="<?php echo e(old('date_emission', date('Y-m-d'))); ?>" required>
                                <?php $__errorArgs = ['date_emission'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-md-4">
                                <label for="date_echeance" class="form-label">Date d'échéance <span class="text-danger">*</span></label>
                                <input type="date" class="form-control <?php $__errorArgs = ['date_echeance'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       id="date_echeance" name="date_echeance"
                                       value="<?php echo e(old('date_echeance', date('Y-m-d', strtotime('+30 days')))); ?>" required>
                                <?php $__errorArgs = ['date_echeance'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-md-4">
                                <label for="statut" class="form-label">Statut</label>
                                <select class="form-select <?php $__errorArgs = ['statut'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        id="statut" name="statut">
                                    <option value="brouillon" <?php echo e(old('statut', 'brouillon') == 'brouillon' ? 'selected' : ''); ?>><?php echo e(__('app.draft')); ?></option>
                                    <option value="en_attente" <?php echo e(old('statut') == 'en_attente' ? 'selected' : ''); ?>><?php echo e(__('app.pending')); ?></option>
                                    <option value="payee" <?php echo e(old('statut') == 'payee' ? 'selected' : ''); ?>><?php echo e(__('app.paid')); ?></option>
                                </select>
                                <?php $__errorArgs = ['statut'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <!-- Montants -->
                        <h6 class="border-bottom pb-2 mb-3">Montants</h6>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="montant_ht" class="form-label">Montant HT (€) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control <?php $__errorArgs = ['montant_ht'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       id="montant_ht" name="montant_ht"
                                       value="<?php echo e(old('montant_ht')); ?>" required>
                                <?php $__errorArgs = ['montant_ht'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-md-4">
                                <label for="taux_tva" class="form-label">Taux TVA (%)</label>
                                <input type="number" step="0.01" class="form-control <?php $__errorArgs = ['taux_tva'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       id="taux_tva" name="taux_tva"
                                       value="<?php echo e(old('taux_tva', 20)); ?>">
                                <?php $__errorArgs = ['taux_tva'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-md-4">
                                <label for="montant_ttc" class="form-label">Montant TTC (€) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control <?php $__errorArgs = ['montant_ttc'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       id="montant_ttc" name="montant_ttc"
                                       value="<?php echo e(old('montant_ttc')); ?>" required readonly>
                                <?php $__errorArgs = ['montant_ttc'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <!-- Lignes de facture -->
                        <h6 class="border-bottom pb-2 mb-3">Détails de la facture</h6>
                        <div id="lignes-container">
                            <div class="ligne-facture border p-3 mb-3 rounded">
                                <div class="row">
                                    <div class="col-md-5">
                                        <label class="form-label">Description</label>
                                        <input type="text" class="form-control" name="lignes[0][description]"
                                               value="<?php echo e(old('lignes.0.description')); ?>" placeholder="Description de la ligne">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Quantité</label>
                                        <input type="number" step="0.01" class="form-control ligne-quantite"
                                               name="lignes[0][quantite]" value="<?php echo e(old('lignes.0.quantite', 1)); ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Prix unitaire HT</label>
                                        <input type="number" step="0.01" class="form-control ligne-prix"
                                               name="lignes[0][prix_unitaire]" value="<?php echo e(old('lignes.0.prix_unitaire')); ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Montant HT</label>
                                        <input type="number" step="0.01" class="form-control ligne-total"
                                               name="lignes[0][montant_ht]" value="<?php echo e(old('lignes.0.montant_ht')); ?>" readonly>
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="button" class="btn btn-danger btn-sm w-100 remove-ligne">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" id="add-ligne" class="btn btn-sm btn-success mb-3">
                            <i class="fas fa-plus me-2"></i>Ajouter une ligne
                        </button>

                        <!-- Notes -->
                        <h6 class="border-bottom pb-2 mb-3">Informations Complémentaires</h6>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="notes" class="form-label"><?php echo e(__('app.notes')); ?></label>
                                <textarea class="form-control <?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                          id="notes" name="notes" rows="4"><?php echo e(old('notes')); ?></textarea>
                                <?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <!-- Boutons -->
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i><?php echo e(__('app.save')); ?>

                                </button>
                                <a href="<?php echo e(route('factures.index')); ?>" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i><?php echo e(__('app.cancel')); ?>

                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let ligneIndex = 1;

    // Calculer le TTC automatiquement
    function calculerTTC() {
        const montantHT = parseFloat(document.getElementById('montant_ht').value) || 0;
        const tauxTVA = parseFloat(document.getElementById('taux_tva').value) || 0;
        const montantTTC = montantHT * (1 + tauxTVA / 100);
        document.getElementById('montant_ttc').value = montantTTC.toFixed(2);
    }

    document.getElementById('montant_ht').addEventListener('input', calculerTTC);
    document.getElementById('taux_tva').addEventListener('input', calculerTTC);

    // Calculer le total d'une ligne
    function calculerLigneTotal(ligne) {
        const quantite = parseFloat(ligne.querySelector('.ligne-quantite').value) || 0;
        const prix = parseFloat(ligne.querySelector('.ligne-prix').value) || 0;
        const total = quantite * prix;
        ligne.querySelector('.ligne-total').value = total.toFixed(2);

        // Recalculer le total HT de la facture
        calculerTotalFacture();
    }

    // Calculer le total de toutes les lignes
    function calculerTotalFacture() {
        let totalHT = 0;
        document.querySelectorAll('.ligne-total').forEach(input => {
            totalHT += parseFloat(input.value) || 0;
        });
        document.getElementById('montant_ht').value = totalHT.toFixed(2);
        calculerTTC();
    }

    // Ajouter une ligne
    document.getElementById('add-ligne').addEventListener('click', function() {
        const container = document.getElementById('lignes-container');
        const newLigne = document.querySelector('.ligne-facture').cloneNode(true);

        // Réinitialiser les valeurs
        newLigne.querySelectorAll('input').forEach(input => {
            if (input.name.includes('quantite')) {
                input.value = 1;
            } else {
                input.value = '';
            }
            input.name = input.name.replace(/\[\d+\]/, `[${ligneIndex}]`);
        });

        container.appendChild(newLigne);
        ligneIndex++;

        // Ajouter les événements
        attachLigneEvents(newLigne);
    });

    // Supprimer une ligne
    function attachLigneEvents(ligne) {
        ligne.querySelector('.ligne-quantite').addEventListener('input', function() {
            calculerLigneTotal(ligne);
        });
        ligne.querySelector('.ligne-prix').addEventListener('input', function() {
            calculerLigneTotal(ligne);
        });
        ligne.querySelector('.remove-ligne').addEventListener('click', function() {
            if (document.querySelectorAll('.ligne-facture').length > 1) {
                ligne.remove();
                calculerTotalFacture();
            } else {
                alert('Vous devez avoir au moins une ligne de facture');
            }
        });
    }

    // Attacher les événements à la première ligne
    document.querySelectorAll('.ligne-facture').forEach(ligne => {
        attachLigneEvents(ligne);
    });

    // Auto-remplir le montant depuis le contrat
    const contratSelect = document.getElementById('contrat_id');
    const clientSelect = document.getElementById('client_id');

    contratSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            const prix = selectedOption.dataset.prix;
            const clientId = selectedOption.dataset.clientId;

            if (prix) {
                document.getElementById('montant_ht').value = prix;
                calculerTTC();

                // Mettre à jour la première ligne
                const firstLigne = document.querySelector('.ligne-facture');
                firstLigne.querySelector('.ligne-quantite').value = 1;
                firstLigne.querySelector('.ligne-prix').value = prix;
                firstLigne.querySelector('input[name="lignes[0][description]"]').value = 'Location box - Mois de ' + new Date().toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' });
                calculerLigneTotal(firstLigne);
            }

            // Sélectionner le client correspondant
            if (clientId) {
                clientSelect.value = clientId;
            }
        }
    });

    // Filtrer les contrats par client
    clientSelect.addEventListener('change', function() {
        const clientId = this.value;
        const contratOptions = contratSelect.querySelectorAll('option');

        contratOptions.forEach(option => {
            if (option.value === '') {
                option.style.display = 'block';
            } else if (!clientId || option.dataset.clientId === clientId) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });

        // Réinitialiser la sélection du contrat si nécessaire
        if (contratSelect.value && contratSelect.options[contratSelect.selectedIndex].style.display === 'none') {
            contratSelect.value = '';
        }
    });
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp2025\htdocs\boxibox\resources\views/factures/create.blade.php ENDPATH**/ ?>