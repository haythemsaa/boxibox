@extends('layouts.app')

@section('title', 'Éditer ' . $tenant->nom_entreprise)

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Éditer le Tenant: {{ $tenant->nom_entreprise }}</h5>
                    <a href="{{ route('superadmin.tenants.show', $tenant) }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('superadmin.tenants.update', $tenant) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <h6 class="mb-3 text-primary"><i class="fas fa-building me-2"></i>Informations Entreprise</h6>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nom_entreprise" class="form-label">Nom Entreprise *</label>
                                <input type="text" class="form-control @error('nom_entreprise') is-invalid @enderror"
                                    id="nom_entreprise" name="nom_entreprise" value="{{ old('nom_entreprise', $tenant->nom_entreprise) }}" required>
                                @error('nom_entreprise')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    id="email" name="email" value="{{ old('email', $tenant->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="telephone" class="form-label">Téléphone</label>
                                <input type="text" class="form-control @error('telephone') is-invalid @enderror"
                                    id="telephone" name="telephone" value="{{ old('telephone', $tenant->telephone) }}">
                                @error('telephone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="siret" class="form-label">SIRET</label>
                                <input type="text" class="form-control @error('siret') is-invalid @enderror"
                                    id="siret" name="siret" value="{{ old('siret', $tenant->siret) }}" maxlength="14">
                                @error('siret')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="pays" class="form-label">Pays</label>
                                <input type="text" class="form-control @error('pays') is-invalid @enderror"
                                    id="pays" name="pays" value="{{ old('pays', $tenant->pays) }}">
                                @error('pays')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="adresse" class="form-label">Adresse</label>
                                <textarea class="form-control @error('adresse') is-invalid @enderror"
                                    id="adresse" name="adresse" rows="2">{{ old('adresse', $tenant->adresse) }}</textarea>
                                @error('adresse')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="code_postal" class="form-label">Code Postal</label>
                                <input type="text" class="form-control @error('code_postal') is-invalid @enderror"
                                    id="code_postal" name="code_postal" value="{{ old('code_postal', $tenant->code_postal) }}">
                                @error('code_postal')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="ville" class="form-label">Ville</label>
                                <input type="text" class="form-control @error('ville') is-invalid @enderror"
                                    id="ville" name="ville" value="{{ old('ville', $tenant->ville) }}">
                                @error('ville')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr>

                        <h6 class="mb-3 text-primary"><i class="fas fa-credit-card me-2"></i>Abonnement</h6>
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="plan" class="form-label">Plan *</label>
                                <select class="form-select @error('plan') is-invalid @enderror" id="plan" name="plan" required>
                                    <option value="gratuit" {{ old('plan', $tenant->plan) == 'gratuit' ? 'selected' : '' }}>Gratuit</option>
                                    <option value="starter" {{ old('plan', $tenant->plan) == 'starter' ? 'selected' : '' }}>Starter</option>
                                    <option value="business" {{ old('plan', $tenant->plan) == 'business' ? 'selected' : '' }}>Business</option>
                                    <option value="enterprise" {{ old('plan', $tenant->plan) == 'enterprise' ? 'selected' : '' }}>Enterprise</option>
                                </select>
                                @error('plan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="prix_mensuel" class="form-label">Prix Mensuel (€) *</label>
                                <input type="number" step="0.01" class="form-control @error('prix_mensuel') is-invalid @enderror"
                                    id="prix_mensuel" name="prix_mensuel" value="{{ old('prix_mensuel', $tenant->prix_mensuel) }}" required>
                                @error('prix_mensuel')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="max_boxes" class="form-label">Max Boxes *</label>
                                <input type="number" class="form-control @error('max_boxes') is-invalid @enderror"
                                    id="max_boxes" name="max_boxes" value="{{ old('max_boxes', $tenant->max_boxes) }}" required>
                                @error('max_boxes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="max_users" class="form-label">Max Utilisateurs *</label>
                                <input type="number" class="form-control @error('max_users') is-invalid @enderror"
                                    id="max_users" name="max_users" value="{{ old('max_users', $tenant->max_users) }}" required>
                                @error('max_users')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="date_debut_abonnement" class="form-label">Date Début *</label>
                                <input type="date" class="form-control @error('date_debut_abonnement') is-invalid @enderror"
                                    id="date_debut_abonnement" name="date_debut_abonnement"
                                    value="{{ old('date_debut_abonnement', $tenant->date_debut_abonnement ? $tenant->date_debut_abonnement->format('Y-m-d') : '') }}" required>
                                @error('date_debut_abonnement')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="date_fin_abonnement" class="form-label">Date Fin</label>
                                <input type="date" class="form-control @error('date_fin_abonnement') is-invalid @enderror"
                                    id="date_fin_abonnement" name="date_fin_abonnement"
                                    value="{{ old('date_fin_abonnement', $tenant->date_fin_abonnement ? $tenant->date_fin_abonnement->format('Y-m-d') : '') }}">
                                @error('date_fin_abonnement')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="statut_abonnement" class="form-label">Statut *</label>
                                <select class="form-select @error('statut_abonnement') is-invalid @enderror" id="statut_abonnement" name="statut_abonnement" required>
                                    <option value="actif" {{ old('statut_abonnement', $tenant->statut_abonnement) == 'actif' ? 'selected' : '' }}>Actif</option>
                                    <option value="suspendu" {{ old('statut_abonnement', $tenant->statut_abonnement) == 'suspendu' ? 'selected' : '' }}>Suspendu</option>
                                    <option value="expire" {{ old('statut_abonnement', $tenant->statut_abonnement) == 'expire' ? 'selected' : '' }}>Expiré</option>
                                    <option value="annule" {{ old('statut_abonnement', $tenant->statut_abonnement) == 'annule' ? 'selected' : '' }}>Annulé</option>
                                </select>
                                @error('statut_abonnement')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                        {{ old('is_active', $tenant->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Compte actif
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('superadmin.tenants.show', $tenant) }}" class="btn btn-secondary me-2">Annuler</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Mettre à jour
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection