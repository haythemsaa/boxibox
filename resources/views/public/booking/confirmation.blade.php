<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Réservation confirmée - Boxibox</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
        }

        .success-animation {
            text-align: center;
            padding: 60px 0;
        }

        .success-icon {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #28a745, #20c997);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            color: white;
            margin-bottom: 30px;
            animation: scaleIn 0.5s ease-out;
        }

        @keyframes scaleIn {
            0% {
                transform: scale(0);
            }
            100% {
                transform: scale(1);
            }
        }

        .info-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .timeline {
            position: relative;
            padding-left: 50px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 20px;
            top: 0;
            height: 100%;
            width: 2px;
            background: #e9ecef;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 30px;
        }

        .timeline-icon {
            position: absolute;
            left: -30px;
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <!-- Animation de succès -->
        <div class="success-animation">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            <h1 class="display-4 fw-bold text-success mb-3">Réservation confirmée !</h1>
            <p class="lead text-muted">
                Votre réservation a été enregistrée avec succès.
            </p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Informations du contrat -->
                <div class="info-card">
                    <h4 class="mb-4">
                        <i class="fas fa-file-contract text-primary"></i> Détails de votre réservation
                    </h4>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Numéro de contrat :</strong><br>
                            <span class="badge bg-primary fs-6">{{ $contrat->numero_contrat }}</span>
                        </div>
                        <div class="col-md-6">
                            <strong>Box réservé :</strong><br>
                            {{ $contrat->box->famille->nom }} - Box {{ $contrat->box->numero }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Date de début :</strong><br>
                            {{ $contrat->date_debut->format('d/m/Y') }}
                        </div>
                        <div class="col-md-6">
                            <strong>Date de fin :</strong><br>
                            {{ $contrat->date_fin->format('d/m/Y') }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Tarif mensuel :</strong><br>
                            <span class="text-primary fs-5">{{ number_format($contrat->prix_mensuel, 2) }} €</span>
                        </div>
                        <div class="col-md-6">
                            <strong>Durée :</strong><br>
                            {{ $contrat->duree_mois }} mois
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Statut :</strong>
                        @if($contrat->statut === 'en_attente')
                            En attente de validation du paiement
                        @else
                            {{ ucfirst($contrat->statut) }}
                        @endif
                    </div>
                </div>

                <!-- Prochaines étapes -->
                <div class="info-card">
                    <h4 class="mb-4">
                        <i class="fas fa-tasks text-primary"></i> Prochaines étapes
                    </h4>

                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold">Email de confirmation</h6>
                                <p class="text-muted mb-0">
                                    Vous allez recevoir un email de confirmation à l'adresse :
                                    <strong>{{ $contrat->client->email }}</strong>
                                </p>
                            </div>
                        </div>

                        @if($contrat->mode_paiement === 'virement')
                        <div class="timeline-item">
                            <div class="timeline-icon">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold">Paiement par virement</h6>
                                <p class="text-muted mb-0">
                                    Les coordonnées bancaires vous ont été envoyées par email.
                                    Votre box sera activé dès réception du paiement.
                                </p>
                            </div>
                        </div>
                        @endif

                        @if($contrat->mode_paiement === 'sepa')
                        <div class="timeline-item">
                            <div class="timeline-icon">
                                <i class="fas fa-university"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold">Mandat SEPA</h6>
                                <p class="text-muted mb-0">
                                    Un mandat de prélèvement SEPA vous sera envoyé à signer.
                                    Le premier prélèvement aura lieu sous 5 jours ouvrés.
                                </p>
                            </div>
                        </div>
                        @endif

                        <div class="timeline-item">
                            <div class="timeline-icon">
                                <i class="fas fa-key"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold">Code d'accès</h6>
                                <p class="text-muted mb-0">
                                    Votre code d'accès personnel vous sera communiqué par email
                                    dès validation du paiement.
                                </p>
                            </div>
                        </div>

                        <div class="timeline-item">
                            <div class="timeline-icon">
                                <i class="fas fa-sign-in-alt"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold">Espace client</h6>
                                <p class="text-muted mb-0">
                                    Connectez-vous à votre espace client pour suivre votre contrat,
                                    consulter vos factures et gérer votre box.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="d-flex gap-3 justify-content-center">
                    <a href="{{ route('login') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-sign-in-alt"></i> Accéder à mon espace
                    </a>
                    <a href="{{ route('public.booking.index') }}" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-home"></i> Retour à l'accueil
                    </a>
                </div>

                <!-- Aide -->
                <div class="text-center mt-5">
                    <p class="text-muted">
                        <i class="fas fa-question-circle"></i> Besoin d'aide ?
                        <a href="#" class="text-decoration-none">Contactez notre support</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
