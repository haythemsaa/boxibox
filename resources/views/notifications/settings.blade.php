@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-cog me-2"></i>Paramètres de Notifications
        </h1>
        <a href="{{ route('notifications.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Retour aux notifications
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('notifications.updateSettings') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Paramètres généraux -->
            <div class="col-md-12 mb-4">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-sliders-h me-2"></i>Paramètres Généraux</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="notifications_activees"
                                   name="notifications_activees" value="1"
                                   {{ $settings->notifications_activees ? 'checked' : '' }}>
                            <label class="form-check-label" for="notifications_activees">
                                <strong>Activer les notifications</strong>
                            </label>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label for="heure_debut_notifications" class="form-label">Heure de début</label>
                                <input type="time" class="form-control" id="heure_debut_notifications"
                                       name="heure_debut_notifications"
                                       value="{{ substr($settings->heure_debut_notifications, 0, 5) }}" required>
                                <small class="text-muted">Ne pas recevoir de notifications avant cette heure</small>
                            </div>
                            <div class="col-md-6">
                                <label for="heure_fin_notifications" class="form-label">Heure de fin</label>
                                <input type="time" class="form-control" id="heure_fin_notifications"
                                       name="heure_fin_notifications"
                                       value="{{ substr($settings->heure_fin_notifications, 0, 5) }}" required>
                                <small class="text-muted">Ne pas recevoir de notifications après cette heure</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notifications par Email -->
            <div class="col-md-4 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-envelope me-2"></i>Email</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="email_paiement_recu"
                                   name="email_paiement_recu" value="1"
                                   {{ $settings->email_paiement_recu ? 'checked' : '' }}>
                            <label class="form-check-label" for="email_paiement_recu">
                                Paiement reçu
                            </label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="email_paiement_retard"
                                   name="email_paiement_retard" value="1"
                                   {{ $settings->email_paiement_retard ? 'checked' : '' }}>
                            <label class="form-check-label" for="email_paiement_retard">
                                Paiement en retard
                            </label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="email_nouvelle_reservation"
                                   name="email_nouvelle_reservation" value="1"
                                   {{ $settings->email_nouvelle_reservation ? 'checked' : '' }}>
                            <label class="form-check-label" for="email_nouvelle_reservation">
                                Nouvelle réservation
                            </label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="email_fin_contrat"
                                   name="email_fin_contrat" value="1"
                                   {{ $settings->email_fin_contrat ? 'checked' : '' }}>
                            <label class="form-check-label" for="email_fin_contrat">
                                Fin de contrat proche
                            </label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="email_acces_refuse"
                                   name="email_acces_refuse" value="1"
                                   {{ $settings->email_acces_refuse ? 'checked' : '' }}>
                            <label class="form-check-label" for="email_acces_refuse">
                                Accès refusé
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notifications Push -->
            <div class="col-md-4 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-bell me-2"></i>Push (Navigateur)</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="push_paiement_recu"
                                   name="push_paiement_recu" value="1"
                                   {{ $settings->push_paiement_recu ? 'checked' : '' }}>
                            <label class="form-check-label" for="push_paiement_recu">
                                Paiement reçu
                            </label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="push_paiement_retard"
                                   name="push_paiement_retard" value="1"
                                   {{ $settings->push_paiement_retard ? 'checked' : '' }}>
                            <label class="form-check-label" for="push_paiement_retard">
                                Paiement en retard
                            </label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="push_nouvelle_reservation"
                                   name="push_nouvelle_reservation" value="1"
                                   {{ $settings->push_nouvelle_reservation ? 'checked' : '' }}>
                            <label class="form-check-label" for="push_nouvelle_reservation">
                                Nouvelle réservation
                            </label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="push_fin_contrat"
                                   name="push_fin_contrat" value="1"
                                   {{ $settings->push_fin_contrat ? 'checked' : '' }}>
                            <label class="form-check-label" for="push_fin_contrat">
                                Fin de contrat proche
                            </label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="push_acces_refuse"
                                   name="push_acces_refuse" value="1"
                                   {{ $settings->push_acces_refuse ? 'checked' : '' }}>
                            <label class="form-check-label" for="push_acces_refuse">
                                Accès refusé
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notifications SMS -->
            <div class="col-md-4 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-sms me-2"></i>SMS</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Les SMS sont facturés séparément. Utilisez-les avec parcimonie.
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="sms_paiement_retard"
                                   name="sms_paiement_retard" value="1"
                                   {{ $settings->sms_paiement_retard ? 'checked' : '' }}>
                            <label class="form-check-label" for="sms_paiement_retard">
                                Paiement en retard
                            </label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="sms_fin_contrat"
                                   name="sms_fin_contrat" value="1"
                                   {{ $settings->sms_fin_contrat ? 'checked' : '' }}>
                            <label class="form-check-label" for="sms_fin_contrat">
                                Fin de contrat proche
                            </label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="sms_acces_refuse"
                                   name="sms_acces_refuse" value="1"
                                   {{ $settings->sms_acces_refuse ? 'checked' : '' }}>
                            <label class="form-check-label" for="sms_acces_refuse">
                                Accès refusé
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save"></i> Enregistrer les paramètres
            </button>
        </div>
    </form>
</div>
@endsection
