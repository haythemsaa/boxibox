@extends('layouts.app')

@section('title', __('app.edit') . ' ' . __('app.clients'))

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i>{{ __('app.edit') }} {{ __('app.clients') }}
                    </h5>
                    <a href="{{ route('clients.show', $client) }}" class="btn btn-sm btn-secondary">
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

                    <form action="{{ route('clients.update', $client) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Type de client -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Type de client <span class="text-danger">*</span></label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="type_client" id="type_particulier" value="particulier" {{ old('type_client', $client->type_client) == 'particulier' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary" for="type_particulier">
                                        <i class="fas fa-user me-2"></i>Particulier
                                    </label>

                                    <input type="radio" class="btn-check" name="type_client" id="type_entreprise" value="entreprise" {{ old('type_client', $client->type_client) == 'entreprise' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary" for="type_entreprise">
                                        <i class="fas fa-building me-2"></i>Entreprise
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Informations entreprise (si applicable) -->
                        <div id="entreprise_fields" style="display: {{ old('type_client', $client->type_client) == 'entreprise' ? 'block' : 'none' }};">
                            <h6 class="border-bottom pb-2 mb-3">Informations Entreprise</h6>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="raison_sociale" class="form-label">Raison sociale</label>
                                    <input type="text" class="form-control @error('raison_sociale') is-invalid @enderror"
                                           id="raison_sociale" name="raison_sociale"
                                           value="{{ old('raison_sociale', $client->raison_sociale) }}">
                                    @error('raison_sociale')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="siret" class="form-label">SIRET</label>
                                    <input type="text" class="form-control @error('siret') is-invalid @enderror"
                                           id="siret" name="siret"
                                           value="{{ old('siret', $client->siret) }}">
                                    @error('siret')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Informations personnelles -->
                        <h6 class="border-bottom pb-2 mb-3">Informations Personnelles</h6>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="nom" class="form-label">{{ __('app.lastname') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nom') is-invalid @enderror"
                                       id="nom" name="nom"
                                       value="{{ old('nom', $client->nom) }}" required>
                                @error('nom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="prenom" class="form-label">{{ __('app.firstname') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('prenom') is-invalid @enderror"
                                       id="prenom" name="prenom"
                                       value="{{ old('prenom', $client->prenom) }}" required>
                                @error('prenom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="date_naissance" class="form-label">Date de naissance</label>
                                <input type="date" class="form-control @error('date_naissance') is-invalid @enderror"
                                       id="date_naissance" name="date_naissance"
                                       value="{{ old('date_naissance', $client->date_naissance ? $client->date_naissance->format('Y-m-d') : '') }}">
                                @error('date_naissance')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Contact -->
                        <h6 class="border-bottom pb-2 mb-3">Contact</h6>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label">{{ __('app.email') }} <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       id="email" name="email"
                                       value="{{ old('email', $client->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="telephone" class="form-label">{{ __('app.phone') }}</label>
                                <input type="tel" class="form-control @error('telephone') is-invalid @enderror"
                                       id="telephone" name="telephone"
                                       value="{{ old('telephone', $client->telephone) }}">
                                @error('telephone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Adresse -->
                        <h6 class="border-bottom pb-2 mb-3">{{ __('app.address') }}</h6>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="adresse" class="form-label">{{ __('app.address') }}</label>
                                <input type="text" class="form-control @error('adresse') is-invalid @enderror"
                                       id="adresse" name="adresse"
                                       value="{{ old('adresse', $client->adresse) }}">
                                @error('adresse')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="code_postal" class="form-label">{{ __('app.postal_code') }}</label>
                                <input type="text" class="form-control @error('code_postal') is-invalid @enderror"
                                       id="code_postal" name="code_postal"
                                       value="{{ old('code_postal', $client->code_postal) }}">
                                @error('code_postal')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="ville" class="form-label">{{ __('app.city') }}</label>
                                <input type="text" class="form-control @error('ville') is-invalid @enderror"
                                       id="ville" name="ville"
                                       value="{{ old('ville', $client->ville) }}">
                                @error('ville')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="pays" class="form-label">{{ __('app.country') }}</label>
                                <input type="text" class="form-control @error('pays') is-invalid @enderror"
                                       id="pays" name="pays"
                                       value="{{ old('pays', $client->pays ?? 'France') }}">
                                @error('pays')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Statut et Notes -->
                        <h6 class="border-bottom pb-2 mb-3">Autres Informations</h6>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $client->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Client actif
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="notes" class="form-label">{{ __('app.notes') }}</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror"
                                          id="notes" name="notes" rows="4">{{ old('notes', $client->notes) }}</textarea>
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
                                <a href="{{ route('clients.show', $client) }}" class="btn btn-secondary">
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
    const typeParticulier = document.getElementById('type_particulier');
    const typeEntreprise = document.getElementById('type_entreprise');
    const entrepriseFields = document.getElementById('entreprise_fields');

    function toggleEntrepriseFields() {
        if (typeEntreprise.checked) {
            entrepriseFields.style.display = 'block';
        } else {
            entrepriseFields.style.display = 'none';
        }
    }

    typeParticulier.addEventListener('change', toggleEntrepriseFields);
    typeEntreprise.addEventListener('change', toggleEntrepriseFields);
});
</script>
@endsection