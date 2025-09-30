@extends('layouts.app')

@section('title', 'Mon Profil')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Mon Profil</li>
                    </ol>
                </div>
                <h4 class="page-title">Mon Profil</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <div class="flex-grow-1">
                            <div class="avatar-lg mb-3">
                                <span class="avatar-title bg-primary-lighten text-primary rounded-circle h1 my-0">
                                    {{ substr($user->name, 0, 1) }}
                                </span>
                            </div>

                            <h5 class="mb-1">{{ $user->name }}</h5>
                            <p class="text-muted mb-2">{{ $user->email }}</p>
                            <p class="text-muted mb-1">
                                <i class="mdi mdi-phone me-1"></i> {{ $user->phone ?? 'Non renseigné' }}
                            </p>

                            <div class="mt-3">
                                <span class="badge bg-success-lighten text-success">
                                    {{ $user->roles->first()->name ?? 'Utilisateur' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Informations du compte</h5>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <tbody>
                                <tr>
                                    <td>Créé le:</td>
                                    <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td>Dernière connexion:</td>
                                    <td>{{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Jamais' }}</td>
                                </tr>
                                <tr>
                                    <td>Email vérifié:</td>
                                    <td>
                                        @if($user->email_verified_at)
                                            <span class="badge bg-success">Oui</span>
                                        @else
                                            <span class="badge bg-warning">Non</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td>Statut:</td>
                                    <td>
                                        @if($user->is_active)
                                            <span class="badge bg-success">Actif</span>
                                        @else
                                            <span class="badge bg-danger">Inactif</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-pills nav-fill navtab-bg">
                        <li class="nav-item">
                            <a href="#profile-info" data-bs-toggle="tab" aria-expanded="true" class="nav-link active">
                                <i class="mdi mdi-account-circle d-md-none d-block"></i>
                                <span class="d-none d-md-block">Informations personnelles</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#change-password" data-bs-toggle="tab" aria-expanded="false" class="nav-link">
                                <i class="mdi mdi-lock-reset d-md-none d-block"></i>
                                <span class="d-none d-md-block">Changer le mot de passe</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#account-settings" data-bs-toggle="tab" aria-expanded="false" class="nav-link">
                                <i class="mdi mdi-settings-outline d-md-none d-block"></i>
                                <span class="d-none d-md-block">Paramètres</span>
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane show active" id="profile-info">
                            <form method="POST" action="{{ route('profile.update') }}">
                                @csrf
                                @method('patch')

                                <h5 class="mb-4 text-uppercase"><i class="mdi mdi-account-circle me-1"></i> Informations personnelles</h5>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Nom complet <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                                   id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Adresse email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                                   id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            @if($user->isDirty('email'))
                                                <small class="text-warning">
                                                    <i class="mdi mdi-alert-outline"></i>
                                                    Votre email sera marqué comme non vérifié après modification.
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">Téléphone</label>
                                            <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                                   id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                                                   placeholder="+33 1 23 45 67 89">
                                            @error('phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="text-end">
                                    <button type="submit" class="btn btn-success">
                                        <i class="mdi mdi-content-save"></i> Sauvegarder les modifications
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane" id="change-password">
                            <form method="POST" action="{{ route('password.update') }}">
                                @csrf
                                @method('put')

                                <h5 class="mb-4 text-uppercase"><i class="mdi mdi-lock-reset me-1"></i> Changer le mot de passe</h5>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="current_password" class="form-label">Mot de passe actuel <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                                                   id="current_password" name="current_password" required>
                                            @error('current_password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="password" class="form-label">Nouveau mot de passe <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                                   id="password" name="password" required>
                                            @error('password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">
                                                Minimum 8 caractères avec majuscules, minuscules et chiffres
                                            </small>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="password_confirmation" class="form-label">Confirmer le mot de passe <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control"
                                                   id="password_confirmation" name="password_confirmation" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="mdi mdi-lock-reset"></i> Changer le mot de passe
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane" id="account-settings">
                            <h5 class="mb-4 text-uppercase"><i class="mdi mdi-settings-outline me-1"></i> Paramètres du compte</h5>

                            <div class="alert alert-info">
                                <h6 class="alert-heading">Suppression du compte</h6>
                                <p class="mb-0">Si vous souhaitez supprimer définitivement votre compte, cette action est irréversible.
                                Toutes vos données seront perdues.</p>
                            </div>

                            <form method="POST" action="{{ route('profile.destroy') }}" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible.')">
                                @csrf
                                @method('delete')

                                <div class="mb-3">
                                    <label for="password_delete" class="form-label">Confirmez avec votre mot de passe</label>
                                    <input type="password" class="form-control @error('password', 'userDeletion') is-invalid @enderror"
                                           id="password_delete" name="password" placeholder="Mot de passe">
                                    @error('password', 'userDeletion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="text-end">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="mdi mdi-delete"></i> Supprimer définitivement mon compte
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection