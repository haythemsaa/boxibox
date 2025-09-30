@extends('layouts.app')

@section('title', __('app.add') . ' ' . __('app.invoices'))

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-plus me-2"></i>{{ __('app.add') }} {{ __('app.invoices') }}
                    </h5>
                    <a href="{{ route('factures.index') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>{{ __('app.back') }}
                    </a>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('factures.store') }}" method="POST">
                        @csrf

                        <!-- Informations Générales -->
                        <h6 class="border-bottom pb-2 mb-3">Informations Générales</h6>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="client_id" class="form-label">Client <span class="text-danger">*</span></label>
                                <select class="form-select @error('client_id') is-invalid @enderror"
                                        id="client_id" name="client_id" required>
                                    <option value="">Sélectionner un client</option>
                                    @foreach(\App\Models\Client::orderBy('nom')->get() as $client)
                                        <option value="{{ $client->id }}"
                                                data-email="{{ $client->email }}"
                                                data-adresse="{{ $client->adresse }}"
                                                data-ville="{{ $client->ville }}"
                                                data-code-postal="{{ $client->code_postal }}"
                                                {{ old('client_id', request('client_id')) == $client->id ? 'selected' : '' }}>
                                            {{ $client->nom }} {{ $client->prenom }}
                                            @if($client->raison_sociale) - {{ $client->raison_sociale }} @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('client_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="contrat_id" class="form-label">Contrat</label>
                                <select class="form-select @error('contrat_id') is-invalid @enderror"
                                        id="contrat_id" name="contrat_id">
                                    <option value="">Aucun contrat (facture libre)</option>
                                    @foreach(\App\Models\Contrat::with('client')->where('statut', 'actif')->orderBy('numero_contrat')->get() as $contrat)
                                        <option value="{{ $contrat->id }}"
                                                data-client-id="{{ $contrat->client_id }}"
                                                data-prix="{{ $contrat->prix_mensuel }}"
                                                {{ old('contrat_id', request('contrat_id')) == $contrat->id ? 'selected' : '' }}>
                                            {{ $contrat->numero_contrat }} - {{ $contrat->client->nom }} {{ $contrat->client->prenom }} ({{ number_format($contrat->prix_mensuel, 2) }}€)
                                        </option>
                                    @endforeach
                                </select>
                                @error('contrat_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Dates -->
                        <h6 class="border-bottom pb-2 mb-3">Dates</h6>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="date_emission" class="form-label">Date d'émission <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('date_emission') is-invalid @enderror"
                                       id="date_emission" name="date_emission"
                                       value="{{ old('date_emission', date('Y-m-d')) }}" required>
                                @error('date_emission')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="date_echeance" class="form-label">Date d'échéance <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('date_echeance') is-invalid @enderror"
                                       id="date_echeance" name="date_echeance"
                                       value="{{ old('date_echeance', date('Y-m-d', strtotime('+30 days'))) }}" required>
                                @error('date_echeance')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="statut" class="form-label">Statut</label>
                                <select class="form-select @error('statut') is-invalid @enderror"
                                        id="statut" name="statut">
                                    <option value="brouillon" {{ old('statut', 'brouillon') == 'brouillon' ? 'selected' : '' }}>{{ __('app.draft') }}</option>
                                    <option value="en_attente" {{ old('statut') == 'en_attente' ? 'selected' : '' }}>{{ __('app.pending') }}</option>
                                    <option value="payee" {{ old('statut') == 'payee' ? 'selected' : '' }}>{{ __('app.paid') }}</option>
                                </select>
                                @error('statut')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Montants -->
                        <h6 class="border-bottom pb-2 mb-3">Montants</h6>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="montant_ht" class="form-label">Montant HT (€) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control @error('montant_ht') is-invalid @enderror"
                                       id="montant_ht" name="montant_ht"
                                       value="{{ old('montant_ht') }}" required>
                                @error('montant_ht')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="taux_tva" class="form-label">Taux TVA (%)</label>
                                <input type="number" step="0.01" class="form-control @error('taux_tva') is-invalid @enderror"
                                       id="taux_tva" name="taux_tva"
                                       value="{{ old('taux_tva', 20) }}">
                                @error('taux_tva')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="montant_ttc" class="form-label">Montant TTC (€) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control @error('montant_ttc') is-invalid @enderror"
                                       id="montant_ttc" name="montant_ttc"
                                       value="{{ old('montant_ttc') }}" required readonly>
                                @error('montant_ttc')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
                                               value="{{ old('lignes.0.description') }}" placeholder="Description de la ligne">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Quantité</label>
                                        <input type="number" step="0.01" class="form-control ligne-quantite"
                                               name="lignes[0][quantite]" value="{{ old('lignes.0.quantite', 1) }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Prix unitaire HT</label>
                                        <input type="number" step="0.01" class="form-control ligne-prix"
                                               name="lignes[0][prix_unitaire]" value="{{ old('lignes.0.prix_unitaire') }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Montant HT</label>
                                        <input type="number" step="0.01" class="form-control ligne-total"
                                               name="lignes[0][montant_ht]" value="{{ old('lignes.0.montant_ht') }}" readonly>
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
                                <label for="notes" class="form-label">{{ __('app.notes') }}</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror"
                                          id="notes" name="notes" rows="4">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Boutons -->
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>{{ __('app.save') }}
                                </button>
                                <a href="{{ route('factures.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>{{ __('app.cancel') }}
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
@endsection