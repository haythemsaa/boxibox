@extends('layouts.client')

@section('title', 'Mon Profil')

@section('content')
<div class="mb-4">
    <h1 class="h3">Mon Profil</h1>
    <p class="text-muted">Consultez et modifiez vos informations personnelles</p>
</div>

<div class="row">
    <!-- Informations personnelles -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Informations Personnelles</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('client.profil.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <!-- Civilité -->
                        <div class="col-md-3 mb-3">
                            <label for="civilite" class="form-label">Civilité</label>
                            <select class="form-select @error('civilite') is-invalid @enderror"
                                    id="civilite"
                                    name="civilite">
                                <option value="M" {{ old('civilite', $client->civilite) == 'M' ? 'selected' : '' }}>M.</option>
                                <option value="Mme" {{ old('civilite', $client->civilite) == 'Mme' ? 'selected' : '' }}>Mme</option>
                                <option value="Mlle" {{ old('civilite', $client->civilite) == 'Mlle' ? 'selected' : '' }}>Mlle</option>
                            </select>
                            @error('civilite')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Nom -->
                        <div class="col-md-4 mb-3">
                            <label for="nom" class="form-label">Nom *</label>
                            <input type="text"
                                   class="form-control @error('nom') is-invalid @enderror"
                                   id="nom"
                                   name="nom"
                                   value="{{ old('nom', $client->nom) }}"
                                   required>
                            @error('nom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Prénom -->
                        <div class="col-md-5 mb-3">
                            <label for="prenom" class="form-label">Prénom *</label>
                            <input type="text"
                                   class="form-control @error('prenom') is-invalid @enderror"
                                   id="prenom"
                                   name="prenom"
                                   value="{{ old('prenom', $client->prenom) }}"
                                   required>
                            @error('prenom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <!-- Email -->
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   id="email"
                                   name="email"
                                   value="{{ old('email', $client->email) }}"
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Téléphone -->
                        <div class="col-md-6 mb-3">
                            <label for="telephone" class="form-label">Téléphone *</label>
                            <input type="tel"
                                   class="form-control @error('telephone') is-invalid @enderror"
                                   id="telephone"
                                   name="telephone"
                                   value="{{ old('telephone', $client->telephone) }}"
                                   required>
                            @error('telephone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Adresse -->
                    <h6 class="mb-3"><i class="fas fa-map-marker-alt me-2"></i>Adresse</h6>

                    <div class="mb-3">
                        <label for="adresse" class="form-label">Adresse *</label>
                        <input type="text"
                               class="form-control @error('adresse') is-invalid @enderror"
                               id="adresse"
                               name="adresse"
                               value="{{ old('adresse', $client->adresse) }}"
                               required>
                        @error('adresse')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <!-- Code Postal -->
                        <div class="col-md-4 mb-3">
                            <label for="code_postal" class="form-label">Code Postal *</label>
                            <input type="text"
                                   class="form-control @error('code_postal') is-invalid @enderror"
                                   id="code_postal"
                                   name="code_postal"
                                   value="{{ old('code_postal', $client->code_postal) }}"
                                   required>
                            @error('code_postal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Ville -->
                        <div class="col-md-8 mb-3">
                            <label for="ville" class="form-label">Ville *</label>
                            <input type="text"
                                   class="form-control @error('ville') is-invalid @enderror"
                                   id="ville"
                                   name="ville"
                                   value="{{ old('ville', $client->ville) }}"
                                   required>
                            @error('ville')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Pays -->
                    <div class="mb-3">
                        <label for="pays" class="form-label">Pays</label>
                        <input type="text"
                               class="form-control @error('pays') is-invalid @enderror"
                               id="pays"
                               name="pays"
                               value="{{ old('pays', $client->pays ?? 'France') }}">
                        @error('pays')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr class="my-4">

                    <!-- Informations complémentaires -->
                    <h6 class="mb-3"><i class="fas fa-info-circle me-2"></i>Informations Complémentaires</h6>

                    <div class="mb-3">
                        <label for="date_naissance" class="form-label">Date de Naissance</label>
                        <input type="date"
                               class="form-control @error('date_naissance') is-invalid @enderror"
                               id="date_naissance"
                               name="date_naissance"
                               value="{{ old('date_naissance', $client->date_naissance?->format('Y-m-d')) }}">
                        @error('date_naissance')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="entreprise" class="form-label">Entreprise</label>
                        <input type="text"
                               class="form-control @error('entreprise') is-invalid @enderror"
                               id="entreprise"
                               name="entreprise"
                               value="{{ old('entreprise', $client->entreprise) }}">
                        @error('entreprise')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Boutons -->
                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('client.dashboard') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Annuler
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Informations du compte -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="fas fa-id-card me-2"></i>Informations du Compte</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">N° Client</small>
                    <div><strong>{{ $client->numero_client }}</strong></div>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Date d'inscription</small>
                    <div>{{ $client->created_at->format('d/m/Y') }}</div>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Statut</small>
                    <div>
                        @if($client->statut == 'actif')
                            <span class="badge bg-success">Actif</span>
                        @else
                            <span class="badge bg-secondary">{{ ucfirst($client->statut) }}</span>
                        @endif
                    </div>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Type de compte</small>
                    <div>{{ ucfirst($client->type_client ?? 'Particulier') }}</div>
                </div>
            </div>
        </div>

        <!-- Sécurité -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Sécurité</h6>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-3">
                    Pour modifier votre mot de passe ou d'autres paramètres de sécurité, veuillez contacter l'administrateur.
                </p>
                <div class="d-grid">
                    <button class="btn btn-outline-primary btn-sm" disabled>
                        <i class="fas fa-key me-1"></i>Modifier le mot de passe
                    </button>
                </div>
            </div>
        </div>

        <!-- Contact -->
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="fas fa-envelope me-2"></i>Besoin d'aide ?</h6>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-2">
                    Pour toute question concernant votre compte ou vos contrats :
                </p>
                <div class="mb-2">
                    <i class="fas fa-phone text-primary me-2"></i>
                    <small><strong>01 XX XX XX XX</strong></small>
                </div>
                <div class="mb-2">
                    <i class="fas fa-envelope text-primary me-2"></i>
                    <small><a href="mailto:contact@boxibox.com">contact@boxibox.com</a></small>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.form-label {
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.card-header h5,
.card-header h6 {
    margin-bottom: 0;
}
</style>
@endpush
@endsection