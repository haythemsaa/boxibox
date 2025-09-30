@extends('layouts.app')

@section('title', 'Créer un Tenant')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Créer un nouveau Tenant</h5>
                    <a href="{{ route('superadmin.tenants.index') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('superadmin.tenants.store') }}" method="POST">
                        @csrf

                        <h6 class="mb-3 text-primary"><i class="fas fa-building me-2"></i>Informations Entreprise</h6>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nom_entreprise" class="form-label">Nom Entreprise *</label>
                                <input type="text" class="form-control @error('nom_entreprise') is-invalid @enderror"
                                    id="nom_entreprise" name="nom_entreprise" value="{{ old('nom_entreprise') }}" required>
                                @error('nom_entreprise')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    id="email" name="email" value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="telephone" class="form-label">Téléphone</label>
                                <input type="text" class="form-control @error('telephone') is-invalid @enderror"
                                    id="telephone" name="telephone" value="{{ old('telephone') }}">
                                @error('telephone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="siret" class="form-label">SIRET</label>
                                <input type="text" class="form-control @error('siret') is-invalid @enderror"
                                    id="siret" name="siret" value="{{ old('siret') }}" maxlength="14">
                                @error('siret')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="pays" class="form-label">Pays</label>
                                <input type="text" class="form-control @error('pays') is-invalid @enderror"
                                    id="pays" name="pays" value="{{ old('pays', 'France') }}">
                                @error('pays')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="adresse" class="form-label">Adresse</label>
                                <textarea class="form-control @error('adresse') is-invalid @enderror"
                                    id="adresse" name="adresse" rows="2">{{ old('adresse') }}</textarea>
                                @error('adresse')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="code_postal" class="form-label">Code Postal</label>
                                <input type="text" class="form-control @error('code_postal') is-invalid @enderror"
                                    id="code_postal" name="code_postal" value="{{ old('code_postal') }}">
                                @error('code_postal')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="ville" class="form-label">Ville</label>
                                <input type="text" class="form-control @error('ville') is-invalid @enderror"
                                    id="ville" name="ville" value="{{ old('ville') }}">
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
                                    <option value="">Sélectionner...</option>
                                    <option value="gratuit" {{ old('plan') == 'gratuit' ? 'selected' : '' }}>Gratuit</option>
                                    <option value="starter" {{ old('plan') == 'starter' ? 'selected' : '' }}>Starter</option>
                                    <option value="business" {{ old('plan') == 'business' ? 'selected' : '' }}>Business</option>
                                    <option value="enterprise" {{ old('plan') == 'enterprise' ? 'selected' : '' }}>Enterprise</option>
                                </select>
                                @error('plan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="prix_mensuel" class="form-label">Prix Mensuel (€) *</label>
                                <input type="number" step="0.01" class="form-control @error('prix_mensuel') is-invalid @enderror"
                                    id="prix_mensuel" name="prix_mensuel" value="{{ old('prix_mensuel', 0) }}" required>
                                @error('prix_mensuel')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="max_boxes" class="form-label">Max Boxes *</label>
                                <input type="number" class="form-control @error('max_boxes') is-invalid @enderror"
                                    id="max_boxes" name="max_boxes" value="{{ old('max_boxes', 10) }}" required>
                                @error('max_boxes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="max_users" class="form-label">Max Utilisateurs *</label>
                                <input type="number" class="form-control @error('max_users') is-invalid @enderror"
                                    id="max_users" name="max_users" value="{{ old('max_users', 2) }}" required>
                                @error('max_users')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="date_debut_abonnement" class="form-label">Date Début *</label>
                                <input type="date" class="form-control @error('date_debut_abonnement') is-invalid @enderror"
                                    id="date_debut_abonnement" name="date_debut_abonnement" value="{{ old('date_debut_abonnement', date('Y-m-d')) }}" required>
                                @error('date_debut_abonnement')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="date_fin_abonnement" class="form-label">Date Fin</label>
                                <input type="date" class="form-control @error('date_fin_abonnement') is-invalid @enderror"
                                    id="date_fin_abonnement" name="date_fin_abonnement" value="{{ old('date_fin_abonnement') }}">
                                @error('date_fin_abonnement')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr>

                        <h6 class="mb-3 text-primary"><i class="fas fa-user-shield me-2"></i>Compte Administrateur</h6>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="admin_name" class="form-label">Nom Complet *</label>
                                <input type="text" class="form-control @error('admin_name') is-invalid @enderror"
                                    id="admin_name" name="admin_name" value="{{ old('admin_name') }}" required>
                                @error('admin_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="admin_email" class="form-label">Email Admin *</label>
                                <input type="email" class="form-control @error('admin_email') is-invalid @enderror"
                                    id="admin_email" name="admin_email" value="{{ old('admin_email') }}" required>
                                @error('admin_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="admin_password" class="form-label">Mot de passe *</label>
                                <input type="password" class="form-control @error('admin_password') is-invalid @enderror"
                                    id="admin_password" name="admin_password" required>
                                @error('admin_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="admin_password_confirmation" class="form-label">Confirmer mot de passe *</label>
                                <input type="password" class="form-control"
                                    id="admin_password_confirmation" name="admin_password_confirmation" required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('superadmin.tenants.index') }}" class="btn btn-secondary me-2">Annuler</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Créer le Tenant
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection