@extends('layouts.app')

@section('title', __('app.add') . ' ' . __('app.contracts'))

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-plus me-2"></i>{{ __('app.add') }} {{ __('app.contracts') }}
                    </h5>
                    <a href="{{ route('contrats.index') }}" class="btn btn-sm btn-secondary">
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

                    <form action="{{ route('contrats.store') }}" method="POST">
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
                                        <option value="{{ $client->id }}" {{ old('client_id', request('client_id')) == $client->id ? 'selected' : '' }}>
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
                                <label for="box_id" class="form-label">Box <span class="text-danger">*</span></label>
                                <select class="form-select @error('box_id') is-invalid @enderror"
                                        id="box_id" name="box_id" required>
                                    <option value="">Sélectionner un box</option>
                                    @foreach(\App\Models\Box::where('statut', 'libre')->orderBy('numero')->get() as $box)
                                        <option value="{{ $box->id }}" {{ old('box_id') == $box->id ? 'selected' : '' }}>
                                            Box {{ $box->numero }} - {{ $box->surface }} m² - {{ number_format($box->famille->prix_base ?? 0, 2) }}€/mois
                                        </option>
                                    @endforeach
                                </select>
                                @error('box_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Dates -->
                        <h6 class="border-bottom pb-2 mb-3">Dates</h6>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="date_debut" class="form-label">Date de début <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('date_debut') is-invalid @enderror"
                                       id="date_debut" name="date_debut"
                                       value="{{ old('date_debut', date('Y-m-d')) }}" required>
                                @error('date_debut')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="duree_type" class="form-label">Type de durée</label>
                                <select class="form-select @error('duree_type') is-invalid @enderror"
                                        id="duree_type" name="duree_type">
                                    <option value="indetermine" {{ old('duree_type', 'indetermine') == 'indetermine' ? 'selected' : '' }}>Indéterminée</option>
                                    <option value="determine" {{ old('duree_type') == 'determine' ? 'selected' : '' }}>Déterminée</option>
                                </select>
                                @error('duree_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4" id="date_fin_group" style="display: {{ old('duree_type') == 'determine' ? 'block' : 'none' }};">
                                <label for="date_fin" class="form-label">Date de fin</label>
                                <input type="date" class="form-control @error('date_fin') is-invalid @enderror"
                                       id="date_fin" name="date_fin"
                                       value="{{ old('date_fin') }}">
                                @error('date_fin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Tarification -->
                        <h6 class="border-bottom pb-2 mb-3">Tarification</h6>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="prix_mensuel" class="form-label">Loyer mensuel (€) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control @error('prix_mensuel') is-invalid @enderror"
                                       id="prix_mensuel" name="prix_mensuel"
                                       value="{{ old('prix_mensuel') }}" required>
                                @error('prix_mensuel')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="caution" class="form-label">Caution (€)</label>
                                <input type="number" step="0.01" class="form-control @error('caution') is-invalid @enderror"
                                       id="caution" name="caution"
                                       value="{{ old('caution', 0) }}">
                                @error('caution')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="frais_dossier" class="form-label">Frais de dossier (€)</label>
                                <input type="number" step="0.01" class="form-control @error('frais_dossier') is-invalid @enderror"
                                       id="frais_dossier" name="frais_dossier"
                                       value="{{ old('frais_dossier', 0) }}">
                                @error('frais_dossier')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Options -->
                        <h6 class="border-bottom pb-2 mb-3">Options</h6>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="periodicite_facturation" class="form-label">Périodicité de facturation</label>
                                <select class="form-select @error('periodicite_facturation') is-invalid @enderror"
                                        id="periodicite_facturation" name="periodicite_facturation">
                                    <option value="mensuelle" {{ old('periodicite_facturation', 'mensuelle') == 'mensuelle' ? 'selected' : '' }}>Mensuelle</option>
                                    <option value="trimestrielle" {{ old('periodicite_facturation') == 'trimestrielle' ? 'selected' : '' }}>Trimestrielle</option>
                                    <option value="semestrielle" {{ old('periodicite_facturation') == 'semestrielle' ? 'selected' : '' }}>Semestrielle</option>
                                    <option value="annuelle" {{ old('periodicite_facturation') == 'annuelle' ? 'selected' : '' }}>Annuelle</option>
                                </select>
                                @error('periodicite_facturation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="statut" class="form-label">Statut</label>
                                <select class="form-select @error('statut') is-invalid @enderror"
                                        id="statut" name="statut">
                                    <option value="brouillon" {{ old('statut', 'brouillon') == 'brouillon' ? 'selected' : '' }}>Brouillon</option>
                                    <option value="en_attente" {{ old('statut') == 'en_attente' ? 'selected' : '' }}>En attente</option>
                                    <option value="actif" {{ old('statut') == 'actif' ? 'selected' : '' }}>Actif</option>
                                </select>
                                @error('statut')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="preavis_jours" class="form-label">Préavis (jours)</label>
                                <input type="number" class="form-control @error('preavis_jours') is-invalid @enderror"
                                       id="preavis_jours" name="preavis_jours"
                                       value="{{ old('preavis_jours', 30) }}">
                                @error('preavis_jours')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="renouvellement_automatique"
                                           name="renouvellement_automatique" value="1"
                                           {{ old('renouvellement_automatique', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="renouvellement_automatique">
                                        Renouvellement automatique
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="assurance_incluse"
                                           name="assurance_incluse" value="1"
                                           {{ old('assurance_incluse') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="assurance_incluse">
                                        Assurance incluse
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3" id="montant_assurance_group" style="display: {{ old('assurance_incluse') ? 'block' : 'none' }};">
                            <div class="col-md-4">
                                <label for="montant_assurance" class="form-label">Montant assurance (€/mois)</label>
                                <input type="number" step="0.01" class="form-control @error('montant_assurance') is-invalid @enderror"
                                       id="montant_assurance" name="montant_assurance"
                                       value="{{ old('montant_assurance', 0) }}">
                                @error('montant_assurance')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

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
                                <a href="{{ route('contrats.index') }}" class="btn btn-secondary">
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
    // Gestion durée type
    const dureeType = document.getElementById('duree_type');
    const dateFinGroup = document.getElementById('date_fin_group');

    function toggleDateFin() {
        if (dureeType.value === 'determine') {
            dateFinGroup.style.display = 'block';
        } else {
            dateFinGroup.style.display = 'none';
        }
    }

    dureeType.addEventListener('change', toggleDateFin);

    // Gestion assurance
    const assuranceIncluse = document.getElementById('assurance_incluse');
    const montantAssuranceGroup = document.getElementById('montant_assurance_group');

    function toggleMontantAssurance() {
        if (assuranceIncluse.checked) {
            montantAssuranceGroup.style.display = 'block';
        } else {
            montantAssuranceGroup.style.display = 'none';
        }
    }

    assuranceIncluse.addEventListener('change', toggleMontantAssurance);

    // Auto-remplir le prix depuis le box sélectionné
    const boxSelect = document.getElementById('box_id');
    const prixMensuelInput = document.getElementById('prix_mensuel');

    boxSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            const prixText = selectedOption.text.match(/(\d+(?:\.\d+)?)€\/mois/);
            if (prixText && prixText[1]) {
                prixMensuelInput.value = prixText[1];
            }
        }
    });
});
</script>
@endsection