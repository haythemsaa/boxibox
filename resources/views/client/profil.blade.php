@extends('layouts.app')

@section('title', 'Mon Profil')

@section('content')
<div class="container">
    <div class="mb-4">
        <h1 class="h3">Mon Profil</h1>
        <p class="text-muted">Gérez vos informations personnelles</p>
    </div>

    <div class="row">
        <!-- Informations Client -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Informations Personnelles</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('client.profil.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Nom Complet</label>
                            <input type="text" class="form-control" value="{{ $client->prenom }} {{ $client->nom }}" readonly>
                            <small class="text-muted">Pour modifier votre nom, contactez notre service.</small>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                id="email" name="email" value="{{ old('email', $client->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="telephone" class="form-label">Téléphone</label>
                                <input type="text" class="form-control @error('telephone') is-invalid @enderror"
                                    id="telephone" name="telephone" value="{{ old('telephone', $client->telephone) }}">
                                @error('telephone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="mobile" class="form-label">Mobile</label>
                                <input type="text" class="form-control @error('mobile') is-invalid @enderror"
                                    id="mobile" name="mobile" value="{{ old('mobile', $client->mobile) }}">
                                @error('mobile')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="adresse" class="form-label">Adresse</label>
                            <textarea class="form-control @error('adresse') is-invalid @enderror"
                                id="adresse" name="adresse" rows="2">{{ old('adresse', $client->adresse) }}</textarea>
                            @error('adresse')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="code_postal" class="form-label">Code Postal</label>
                                <input type="text" class="form-control @error('code_postal') is-invalid @enderror"
                                    id="code_postal" name="code_postal" value="{{ old('code_postal', $client->code_postal) }}">
                                @error('code_postal')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-5">
                                <label for="ville" class="form-label">Ville</label>
                                <input type="text" class="form-control @error('ville') is-invalid @enderror"
                                    id="ville" name="ville" value="{{ old('ville', $client->ville) }}">
                                @error('ville')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="pays" class="form-label">Pays</label>
                                <input type="text" class="form-control @error('pays') is-invalid @enderror"
                                    id="pays" name="pays" value="{{ old('pays', $client->pays ?? 'France') }}">
                                @error('pays')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Informations Compte -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-lock me-2"></i>Compte Utilisateur</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Email de connexion:</th>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <th>Dernière connexion:</th>
                            <td>{{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Jamais' }}</td>
                        </tr>
                        <tr>
                            <th>Compte créé le:</th>
                            <td>{{ $user->created_at->format('d/m/Y') }}</td>
                        </tr>
                    </table>

                    <hr>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Changement de mot de passe</strong><br>
                        Pour modifier votre mot de passe, rendez-vous dans les paramètres de votre profil utilisateur.
                    </div>

                    <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary w-100">
                        <i class="fas fa-key me-2"></i>Gérer mon mot de passe
                    </a>
                </div>
            </div>

            @if($client->type_client == 'entreprise')
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-building me-2"></i>Informations Entreprise</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Raison Sociale:</th>
                            <td>{{ $client->raison_sociale ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>SIRET:</th>
                            <td>{{ $client->siret ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>N° TVA:</th>
                            <td>{{ $client->numero_tva ?? 'N/A' }}</td>
                        </tr>
                    </table>
                    <small class="text-muted">
                        Pour modifier ces informations, contactez notre service commercial.
                    </small>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Aide -->
    <div class="card bg-light">
        <div class="card-body">
            <h5><i class="fas fa-question-circle me-2"></i>Besoin d'aide ?</h5>
            <p class="mb-0">
                Pour toute question ou modification ne pouvant être effectuée directement,
                n'hésitez pas à contacter notre service client.
            </p>
        </div>
    </div>
</div>
@endsection