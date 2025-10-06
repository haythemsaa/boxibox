<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $famille->nom }} - Boxibox</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
        }

        .navbar-custom {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        }

        .header-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 60px 0;
            margin-bottom: 50px;
        }

        .box-item {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 20px;
        }

        .box-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }

        .available-badge {
            background: #28a745;
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: bold;
        }

        .feature-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="{{ route('public.booking.index') }}">
                <i class="fas fa-archive"></i> Boxibox
            </a>
            <a href="{{ route('login') }}" class="btn btn-outline-light">
                <i class="fas fa-sign-in-alt"></i> Connexion
            </a>
        </div>
    </nav>

    <!-- Header -->
    <div class="header-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="mb-3">
                        <a href="{{ route('public.booking.index') }}" class="text-white text-decoration-none">
                            <i class="fas fa-arrow-left"></i> Retour au catalogue
                        </a>
                    </div>
                    <h1 class="display-4 fw-bold">{{ $famille->nom }}</h1>
                    <p class="lead">{{ $famille->description ?? 'Box de stockage sécurisé' }}</p>

                    <div class="d-flex gap-4 mt-4">
                        <div>
                            <div class="h5 mb-0">{{ $famille->surface }} m²</div>
                            <small>Surface</small>
                        </div>
                        <div>
                            <div class="h5 mb-0">{{ number_format($famille->tarif_base ?? 0, 2) }} €</div>
                            <small>Par mois</small>
                        </div>
                        <div>
                            <div class="h5 mb-0">{{ $boxesDisponibles->count() }}</div>
                            <small>Disponibles</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 text-center">
                    <i class="fas fa-box" style="font-size: 150px; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="container mb-5">
        <!-- Caractéristiques -->
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto">
                <h3 class="mb-4">
                    <i class="fas fa-info-circle text-primary"></i> Caractéristiques
                </h3>

                <div class="row">
                    <div class="col-md-6">
                        <div class="feature-box">
                            <h5><i class="fas fa-ruler-combined text-primary"></i> Dimensions</h5>
                            <p class="mb-0">
                                <strong>{{ $famille->largeur ?? 'N/A' }}m</strong> (L) x
                                <strong>{{ $famille->longueur ?? 'N/A' }}m</strong> (l) x
                                <strong>{{ $famille->hauteur ?? 'N/A' }}m</strong> (H)
                            </p>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="feature-box">
                            <h5><i class="fas fa-cube text-primary"></i> Surface & Volume</h5>
                            <p class="mb-0">
                                <strong>{{ $famille->surface }} m²</strong> au sol<br>
                                <strong>{{ $famille->volume ?? 'N/A' }} m³</strong> de volume
                            </p>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12">
                        <div class="feature-box">
                            <h5><i class="fas fa-shield-alt text-primary"></i> Sécurité & Accès</h5>
                            <ul class="mb-0">
                                <li>Surveillance vidéo 24/7</li>
                                <li>Accès sécurisé par code personnel</li>
                                <li>Alarme anti-intrusion</li>
                                <li>Assurance disponible en option</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Boxes disponibles -->
        <div class="row">
            <div class="col-12">
                <h3 class="mb-4">
                    <i class="fas fa-warehouse text-primary"></i> Boxes disponibles
                    <span class="badge bg-success">{{ $boxesDisponibles->count() }}</span>
                </h3>

                @if($boxesDisponibles->count() > 0)
                    @foreach($boxesDisponibles as $box)
                        <div class="box-item">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <i class="fas fa-box fa-3x text-primary mb-2"></i>
                                        <h5 class="mb-0">Box {{ $box->numero }}</h5>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <h6 class="text-muted mb-2">Emplacement</h6>
                                    @if($box->emplacement)
                                        <p class="mb-0">
                                            <i class="fas fa-map-marker-alt text-primary"></i>
                                            {{ $box->emplacement->nom }}
                                            @if($box->emplacement->description)
                                                <br><small class="text-muted">{{ $box->emplacement->description }}</small>
                                            @endif
                                        </p>
                                    @else
                                        <p class="mb-0 text-muted">Non spécifié</p>
                                    @endif
                                </div>

                                <div class="col-md-3 text-center">
                                    <span class="available-badge">
                                        <i class="fas fa-check-circle"></i> Disponible
                                    </span>
                                </div>

                                <div class="col-md-2 text-end">
                                    <a href="{{ route('public.booking.form', $box) }}" class="btn btn-primary">
                                        <i class="fas fa-calendar-check"></i> Réserver
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Aucun box disponible dans cette catégorie pour le moment.
                        <a href="{{ route('public.booking.index') }}" class="alert-link">Voir d'autres tailles</a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Tarifs dégressifs -->
        <div class="row mt-5">
            <div class="col-lg-8 mx-auto">
                <h3 class="mb-4">
                    <i class="fas fa-tags text-primary"></i> Tarifs dégressifs
                </h3>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-primary">
                            <tr>
                                <th>Durée</th>
                                <th>Réduction</th>
                                <th>Tarif mensuel</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1 mois</td>
                                <td>-</td>
                                <td>{{ number_format($famille->tarif_base ?? 0, 2) }} €</td>
                                <td>{{ number_format($famille->tarif_base ?? 0, 2) }} €</td>
                            </tr>
                            <tr>
                                <td>3 mois</td>
                                <td class="text-success"><strong>-5%</strong></td>
                                <td>{{ number_format(($famille->tarif_base ?? 0) * 0.95, 2) }} €</td>
                                <td>{{ number_format(($famille->tarif_base ?? 0) * 0.95 * 3, 2) }} €</td>
                            </tr>
                            <tr>
                                <td>6 mois</td>
                                <td class="text-success"><strong>-10%</strong></td>
                                <td>{{ number_format(($famille->tarif_base ?? 0) * 0.90, 2) }} €</td>
                                <td>{{ number_format(($famille->tarif_base ?? 0) * 0.90 * 6, 2) }} €</td>
                            </tr>
                            <tr class="table-success">
                                <td><strong>12 mois</strong></td>
                                <td class="text-success"><strong>-15%</strong></td>
                                <td><strong>{{ number_format(($famille->tarif_base ?? 0) * 0.85, 2) }} €</strong></td>
                                <td><strong>{{ number_format(($famille->tarif_base ?? 0) * 0.85 * 12, 2) }} €</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p class="text-muted small">
                    <i class="fas fa-info-circle"></i> Les tarifs sont indicatifs et peuvent varier selon les promotions en cours.
                </p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-archive"></i> Boxibox</h5>
                    <p class="text-muted">Solution de stockage sécurisé</p>
                </div>
                <div class="col-md-6 text-end">
                    <a href="{{ route('public.booking.index') }}" class="text-white me-3">
                        <i class="fas fa-home"></i> Accueil
                    </a>
                    <a href="{{ route('login') }}" class="text-white">
                        <i class="fas fa-sign-in-alt"></i> Connexion
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
