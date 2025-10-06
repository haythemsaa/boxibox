<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Réservation en ligne - Boxibox</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
        }

        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 80px 0;
            margin-bottom: 50px;
        }

        .box-card {
            transition: transform 0.3s, box-shadow 0.3s;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .box-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }

        .price-tag {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .availability-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
        }

        .stats-section {
            background: #f8f9fa;
            padding: 40px 0;
            margin-bottom: 50px;
        }

        .stat-box {
            text-align: center;
            padding: 20px;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--primary-color);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.5rem;
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <h1 class="display-4 fw-bold mb-3">
                        <i class="fas fa-archive"></i> Réservez votre box en ligne
                    </h1>
                    <p class="lead mb-4">
                        Stockage sécurisé 24/7 • Réservation instantanée • Paiement en ligne
                    </p>
                    <div class="d-flex gap-3">
                        <a href="#catalogue" class="btn btn-light btn-lg">
                            <i class="fas fa-search"></i> Voir les boxes disponibles
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-sign-in-alt"></i> Espace Client
                        </a>
                    </div>
                </div>
                <div class="col-lg-5 text-center">
                    <i class="fas fa-warehouse" style="font-size: 200px; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <div class="stat-box">
                        <div class="stat-number">{{ $stats['boxes_disponibles'] }}</div>
                        <p class="text-muted mb-0">Boxes disponibles</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-box">
                        <div class="stat-number">{{ number_format($stats['taux_occupation'], 0) }}%</div>
                        <p class="text-muted mb-0">Taux d'occupation</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-box">
                        <div class="stat-number">24/7</div>
                        <p class="text-muted mb-0">Accès sécurisé</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Catalogue Section -->
    <div class="container mb-5" id="catalogue">
        <div class="text-center mb-5">
            <h2 class="fw-bold">
                <i class="fas fa-th-large text-primary"></i> Nos boxes disponibles
            </h2>
            <p class="text-muted">Choisissez le box qui correspond à vos besoins</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            @forelse($familles as $famille)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card box-card h-100">
                        <div class="card-body position-relative">
                            <div class="availability-badge">
                                <i class="fas fa-check-circle"></i> {{ $famille->disponibles_count }} disponibles
                            </div>

                            <div class="feature-icon">
                                <i class="fas fa-box"></i>
                            </div>

                            <h3 class="card-title text-center mb-3">{{ $famille->nom }}</h3>

                            <div class="mb-3">
                                <h4 class="text-center text-muted">
                                    {{ $famille->surface }} m²
                                </h4>
                            </div>

                            @if($famille->description)
                                <p class="text-muted small text-center mb-3">{{ $famille->description }}</p>
                            @endif

                            <div class="text-center mb-3">
                                <div class="price-tag">
                                    {{ number_format($famille->tarif_base ?? 0, 2) }} €<small>/mois</small>
                                </div>
                            </div>

                            <ul class="list-unstyled mb-4">
                                <li class="mb-2">
                                    <i class="fas fa-ruler-combined text-primary me-2"></i>
                                    Dimensions: {{ $famille->largeur ?? 'N/A' }}m x {{ $famille->longueur ?? 'N/A' }}m
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-arrows-alt-v text-primary me-2"></i>
                                    Hauteur: {{ $famille->hauteur ?? 'N/A' }}m
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-shield-alt text-primary me-2"></i>
                                    Sécurisé 24/7
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-key text-primary me-2"></i>
                                    Accès personnel
                                </li>
                            </ul>

                            <a href="{{ route('public.booking.famille', $famille) }}" class="btn btn-primary w-100">
                                <i class="fas fa-eye"></i> Voir les détails
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle fa-3x mb-3"></i>
                        <h4>Aucun box disponible pour le moment</h4>
                        <p class="mb-0">Veuillez nous contacter pour connaître les prochaines disponibilités.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Features Section -->
    <div class="bg-light py-5 mb-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Pourquoi choisir Boxibox ?</h2>
            </div>

            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="text-center">
                        <div class="feature-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <h5 class="fw-bold">Réservation instantanée</h5>
                        <p class="text-muted">Réservez votre box en quelques clics, 24h/24</p>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="text-center">
                        <div class="feature-icon">
                            <i class="fas fa-lock"></i>
                        </div>
                        <h5 class="fw-bold">Sécurité maximale</h5>
                        <p class="text-muted">Surveillance vidéo, alarme et accès sécurisé</p>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="text-center">
                        <div class="feature-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h5 class="fw-bold">Accès 24/7</h5>
                        <p class="text-muted">Accédez à votre box quand vous voulez</p>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="text-center">
                        <div class="feature-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <h5 class="fw-bold">Paiement en ligne</h5>
                        <p class="text-muted">Carte bancaire, virement ou prélèvement SEPA</p>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="text-center">
                        <div class="feature-icon">
                            <i class="fas fa-percent"></i>
                        </div>
                        <h5 class="fw-bold">Tarifs dégressifs</h5>
                        <p class="text-muted">Jusqu'à -15% pour 12 mois</p>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="text-center">
                        <div class="feature-icon">
                            <i class="fas fa-file-contract"></i>
                        </div>
                        <h5 class="fw-bold">Sans engagement</h5>
                        <p class="text-muted">Contrat flexible, résiliable à tout moment</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-archive"></i> Boxibox</h5>
                    <p class="text-muted">Solution de stockage sécurisé</p>
                </div>
                <div class="col-md-6 text-end">
                    <a href="{{ route('login') }}" class="text-white me-3">
                        <i class="fas fa-sign-in-alt"></i> Connexion
                    </a>
                    <a href="#" class="text-white">
                        <i class="fas fa-phone"></i> Contact
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
