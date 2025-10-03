@extends('layouts.app')

@section('title', __('app.edit') . ' ' . __('app.invoices'))

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i>{{ __('app.edit') }} {{ __('app.invoices') }} #{{ $facture->numero_facture }}
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

                    <form action="{{ route('factures.update', $facture) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Informations Générales -->
                        <h6 class="border-bottom pb-2 mb-3">Informations Générales</h6>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="client_id" class="form-label">Client <span class="text-danger">*</span></label>
                                <select class="form-select @error('client_id') is-invalid @enderror"
                                        id="client_id" name="client_id" required>
                                    <option value="">Sélectionner un client</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}"
                                                {{ old('client_id', $facture->client_id) == $client->id ? 'selected' : '' }}>
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
                                    @foreach($contrats as $contrat)
                                        <option value="{{ $contrat->id }}"
                                                data-client-id="{{ $contrat->client_id }}"
                                                {{ old('contrat_id', $facture->contrat_id) == $contrat->id ? 'selected' : '' }}>
                                            {{ $contrat->numero_contrat }} - {{ $contrat->client->nom }} {{ $contrat->client->prenom }}
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
                                       value="{{ old('date_emission', $facture->date_emission) }}" required>
                                @error('date_emission')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="date_echeance" class="form-label">Date d'échéance <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('date_echeance') is-invalid @enderror"
                                       id="date_echeance" name="date_echeance"
                                       value="{{ old('date_echeance', $facture->date_echeance) }}" required>
                                @error('date_echeance')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="statut" class="form-label">Statut</label>
                                <select class="form-select @error('statut') is-invalid @enderror"
                                        id="statut" name="statut">
                                    <option value="brouillon" {{ old('statut', $facture->statut) == 'brouillon' ? 'selected' : '' }}>{{ __('app.draft') }}</option>
                                    <option value="en_attente" {{ old('statut', $facture->statut) == 'en_attente' ? 'selected' : '' }}>{{ __('app.pending') }}</option>
                                    <option value="payee" {{ old('statut', $facture->statut) == 'payee' ? 'selected' : '' }}>{{ __('app.paid') }}</option>
                                    <option value="en_retard" {{ old('statut', $facture->statut) == 'en_retard' ? 'selected' : '' }}>{{ __('app.overdue') }}</option>
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
                                       value="{{ old('montant_ht', $facture->montant_ht) }}" required>
                                @error('montant_ht')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="taux_tva" class="form-label">Taux TVA (%)</label>
                                <input type="number" step="0.01" class="form-control @error('taux_tva') is-invalid @enderror"
                                       id="taux_tva" name="taux_tva"
                                       value="{{ old('taux_tva', $facture->taux_tva) }}">
                                @error('taux_tva')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="montant_ttc" class="form-label">Montant TTC (€)</label>
                                <input type="text" class="form-control"
                                       id="montant_ttc"
                                       value="{{ number_format($facture->montant_ttc, 2) }}" readonly>
                            </div>
                        </div>

                        <!-- Description -->
                        <h6 class="border-bottom pb-2 mb-3">Description</h6>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="4">{{ old('description', $facture->description) }}</textarea>
                                @error('description')
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
                                <a href="{{ route('factures.show', $facture) }}" class="btn btn-secondary">
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
    // Calculer le TTC automatiquement
    function calculerTTC() {
        const montantHT = parseFloat(document.getElementById('montant_ht').value) || 0;
        const tauxTVA = parseFloat(document.getElementById('taux_tva').value) || 0;
        const montantTTC = montantHT * (1 + tauxTVA / 100);
        document.getElementById('montant_ttc').value = montantTTC.toFixed(2);
    }

    document.getElementById('montant_ht').addEventListener('input', calculerTTC);
    document.getElementById('taux_tva').addEventListener('input', calculerTTC);

    // Filtrer les contrats par client
    const clientSelect = document.getElementById('client_id');
    const contratSelect = document.getElementById('contrat_id');

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
