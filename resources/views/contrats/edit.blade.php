@extends('layouts.app')

@section('title', __('app.edit') . ' ' . __('app.contracts'))

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i>{{ __('app.edit') }} {{ __('app.contracts') }}
                    </h5>
                    <a href="{{ route('contrats.show', $contrat) }}" class="btn btn-sm btn-secondary">
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

                    <form action="{{ route('contrats.update', $contrat) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Client et Box -->
                        <h6 class="border-bottom pb-2 mb-3">{{ __('app.general_information') }}</h6>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="client_id" class="form-label">{{ __('app.client') }} <span class="text-danger">*</span></label>
                                <select name="client_id" id="client_id" class="form-select @error('client_id') is-invalid @enderror" required>
                                    <option value="">{{ __('app.select_client') }}</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ old('client_id', $contrat->client_id) == $client->id ? 'selected' : '' }}>
                                            {{ $client->nom }} {{ $client->prenom }}
                                            @if($client->raison_sociale)
                                                - {{ $client->raison_sociale }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('client_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="box_id" class="form-label">{{ __('app.box') }} <span class="text-danger">*</span></label>
                                <select name="box_id" id="box_id" class="form-select @error('box_id') is-invalid @enderror" required>
                                    <option value="">{{ __('app.select_box') }}</option>
                                    @foreach($boxes as $box)
                                        <option value="{{ $box->id }}" {{ old('box_id', $contrat->box_id) == $box->id ? 'selected' : '' }}>
                                            {{ $box->numero }} - {{ $box->surface }}m² - {{ number_format($box->prix, 2) }}€
                                            @if($box->contrat_actif && $box->id != $contrat->box_id)
                                                ({{ __('app.occupied') }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('box_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">{{ __('app.available_boxes_only') }}</small>
                            </div>
                        </div>

                        <!-- Dates -->
                        <h6 class="border-bottom pb-2 mb-3">{{ __('app.dates') }}</h6>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="date_debut" class="form-label">{{ __('app.start_date') }} <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('date_debut') is-invalid @enderror"
                                       id="date_debut" name="date_debut"
                                       value="{{ old('date_debut', $contrat->date_debut->format('Y-m-d')) }}" required>
                                @error('date_debut')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="duree_type" class="form-label">{{ __('app.duration_type') }}</label>
                                <select name="duree_type" id="duree_type" class="form-select @error('duree_type') is-invalid @enderror">
                                    <option value="indeterminee" {{ old('duree_type', $contrat->duree_type) == 'indeterminee' ? 'selected' : '' }}>{{ __('app.indefinite') }}</option>
                                    <option value="determinee" {{ old('duree_type', $contrat->duree_type) == 'determinee' ? 'selected' : '' }}>{{ __('app.fixed_term') }}</option>
                                </select>
                                @error('duree_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="date_fin" class="form-label">{{ __('app.end_date') }}</label>
                                <input type="date" class="form-control @error('date_fin') is-invalid @enderror"
                                       id="date_fin" name="date_fin"
                                       value="{{ old('date_fin', $contrat->date_fin ? $contrat->date_fin->format('Y-m-d') : '') }}">
                                @error('date_fin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">{{ __('app.optional_for_indefinite') }}</small>
                            </div>
                        </div>

                        <!-- Prix -->
                        <h6 class="border-bottom pb-2 mb-3">{{ __('app.pricing') }}</h6>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="prix_mensuel" class="form-label">{{ __('app.monthly_price') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control @error('prix_mensuel') is-invalid @enderror"
                                           id="prix_mensuel" name="prix_mensuel"
                                           value="{{ old('prix_mensuel', $contrat->prix_mensuel) }}" required>
                                    <span class="input-group-text">€</span>
                                    @error('prix_mensuel')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="caution" class="form-label">{{ __('app.deposit') }}</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control @error('caution') is-invalid @enderror"
                                           id="caution" name="caution"
                                           value="{{ old('caution', $contrat->caution) }}">
                                    <span class="input-group-text">€</span>
                                    @error('caution')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="frais_dossier" class="form-label">{{ __('app.processing_fee') }}</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control @error('frais_dossier') is-invalid @enderror"
                                           id="frais_dossier" name="frais_dossier"
                                           value="{{ old('frais_dossier', $contrat->frais_dossier) }}">
                                    <span class="input-group-text">€</span>
                                    @error('frais_dossier')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Options -->
                        <h6 class="border-bottom pb-2 mb-3">{{ __('app.options') }}</h6>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="periodicite_facturation" class="form-label">{{ __('app.billing_frequency') }}</label>
                                <select name="periodicite_facturation" id="periodicite_facturation" class="form-select @error('periodicite_facturation') is-invalid @enderror">
                                    <option value="mensuelle" {{ old('periodicite_facturation', $contrat->periodicite_facturation) == 'mensuelle' ? 'selected' : '' }}>{{ __('app.monthly') }}</option>
                                    <option value="trimestrielle" {{ old('periodicite_facturation', $contrat->periodicite_facturation) == 'trimestrielle' ? 'selected' : '' }}>{{ __('app.quarterly') }}</option>
                                    <option value="semestrielle" {{ old('periodicite_facturation', $contrat->periodicite_facturation) == 'semestrielle' ? 'selected' : '' }}>{{ __('app.biannual') }}</option>
                                    <option value="annuelle" {{ old('periodicite_facturation', $contrat->periodicite_facturation) == 'annuelle' ? 'selected' : '' }}>{{ __('app.annual') }}</option>
                                </select>
                                @error('periodicite_facturation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="statut" class="form-label">{{ __('app.status') }}</label>
                                <select name="statut" id="statut" class="form-select @error('statut') is-invalid @enderror">
                                    <option value="brouillon" {{ old('statut', $contrat->statut) == 'brouillon' ? 'selected' : '' }}>{{ __('app.draft') }}</option>
                                    <option value="en_attente" {{ old('statut', $contrat->statut) == 'en_attente' ? 'selected' : '' }}>{{ __('app.pending') }}</option>
                                    <option value="actif" {{ old('statut', $contrat->statut) == 'actif' ? 'selected' : '' }}>{{ __('app.active') }}</option>
                                    <option value="suspendu" {{ old('statut', $contrat->statut) == 'suspendu' ? 'selected' : '' }}>{{ __('app.suspended') }}</option>
                                    <option value="termine" {{ old('statut', $contrat->statut) == 'termine' ? 'selected' : '' }}>{{ __('app.completed') }}</option>
                                    <option value="resilie" {{ old('statut', $contrat->statut) == 'resilie' ? 'selected' : '' }}>{{ __('app.cancelled') }}</option>
                                </select>
                                @error('statut')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="renouvellement_automatique" name="renouvellement_automatique" value="1" {{ old('renouvellement_automatique', $contrat->renouvellement_automatique) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="renouvellement_automatique">
                                        {{ __('app.automatic_renewal') }}
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="assurance_incluse" name="assurance_incluse" value="1" {{ old('assurance_incluse', $contrat->assurance_incluse) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="assurance_incluse">
                                        {{ __('app.insurance_included') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <h6 class="border-bottom pb-2 mb-3">{{ __('app.additional_information') }}</h6>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="notes" class="form-label">{{ __('app.notes') }}</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror"
                                          id="notes" name="notes" rows="4">{{ old('notes', $contrat->notes) }}</textarea>
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
                                <a href="{{ route('contrats.show', $contrat) }}" class="btn btn-secondary">
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
    const dureeType = document.getElementById('duree_type');
    const dateFin = document.getElementById('date_fin');

    function toggleDateFin() {
        if (dureeType.value === 'indeterminee') {
            dateFin.disabled = true;
            dateFin.value = '';
            dateFin.closest('.col-md-4').querySelector('small').textContent = '{{ __('app.not_applicable') }}';
        } else {
            dateFin.disabled = false;
            dateFin.closest('.col-md-4').querySelector('small').textContent = '{{ __('app.required_for_fixed_term') }}';
        }
    }

    dureeType.addEventListener('change', toggleDateFin);
    toggleDateFin(); // Initial call
});
</script>
@endsection