<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Réservation Box {{ $box->numero }} - Boxibox</title>

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

        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
        }

        .step {
            flex: 1;
            text-align: center;
            padding: 15px;
            position: relative;
        }

        .step.active {
            background: #f8f9fa;
            border-radius: 10px;
        }

        .step-number {
            width: 40px;
            height: 40px;
            background: #e9ecef;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .step.active .step-number {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .price-summary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 30px;
            border-radius: 15px;
            position: sticky;
            top: 20px;
        }

        .form-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .discount-badge {
            background: #28a745;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            display: inline-block;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-dark navbar-custom mb-4">
        <div class="container">
            <a class="navbar-brand" href="{{ route('public.booking.index') }}">
                <i class="fas fa-archive"></i> Boxibox
            </a>
            <div class="text-white">
                <i class="fas fa-phone"></i> Support
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        <!-- Step Indicator -->
        <div class="step-indicator">
            <div class="step active">
                <div class="step-number">1</div>
                <div>Informations</div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div>Paiement</div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div>Confirmation</div>
            </div>
        </div>

        <!-- Alerts -->
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

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Erreurs de validation :</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <!-- Formulaire -->
            <div class="col-lg-8">
                <form action="{{ route('public.booking.process', $box) }}" method="POST" id="bookingForm">
                    @csrf

                    <!-- Informations personnelles -->
                    <div class="form-section">
                        <h4 class="mb-4">
                            <i class="fas fa-user text-primary"></i> Vos informations
                        </h4>

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Civilité *</label>
                                <select name="civilite" class="form-select" required>
                                    <option value="">Choisir...</option>
                                    <option value="M" {{ old('civilite') == 'M' ? 'selected' : '' }}>M.</option>
                                    <option value="Mme" {{ old('civilite') == 'Mme' ? 'selected' : '' }}>Mme</option>
                                    <option value="Autre" {{ old('civilite') == 'Autre' ? 'selected' : '' }}>Autre</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Prénom *</label>
                                <input type="text" name="prenom" class="form-control" value="{{ old('prenom') }}" required>
                            </div>

                            <div class="col-md-5 mb-3">
                                <label class="form-label">Nom *</label>
                                <input type="text" name="nom" class="form-control" value="{{ old('nom') }}" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Téléphone *</label>
                                <input type="tel" name="telephone" class="form-control" value="{{ old('telephone') }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Adresse *</label>
                            <input type="text" name="adresse" class="form-control" value="{{ old('adresse') }}" required>
                        </div>

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Code postal *</label>
                                <input type="text" name="code_postal" class="form-control" value="{{ old('code_postal') }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ville *</label>
                                <input type="text" name="ville" class="form-control" value="{{ old('ville') }}" required>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">Pays *</label>
                                <input type="text" name="pays" class="form-control" value="{{ old('pays', 'France') }}" required>
                            </div>
                        </div>
                    </div>

                    <!-- Détails du contrat -->
                    <div class="form-section">
                        <h4 class="mb-4">
                            <i class="fas fa-file-contract text-primary"></i> Détails du contrat
                        </h4>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date de début *</label>
                                <input type="date" name="date_debut" class="form-control"
                                       value="{{ old('date_debut', date('Y-m-d')) }}"
                                       min="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Durée (en mois) *</label>
                                <select name="duree_mois" id="dureeMois" class="form-select" required>
                                    <option value="1" {{ old('duree_mois') == 1 ? 'selected' : '' }}>1 mois</option>
                                    <option value="3" {{ old('duree_mois') == 3 ? 'selected' : '' }}>3 mois (-5%)</option>
                                    <option value="6" {{ old('duree_mois') == 6 ? 'selected' : '' }}>6 mois (-10%)</option>
                                    <option value="12" {{ old('duree_mois') == 12 ? 'selected' : '' }}>12 mois (-15%)</option>
                                    <option value="24" {{ old('duree_mois') == 24 ? 'selected' : '' }}>24 mois (-15%)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Mode de paiement -->
                    <div class="form-section">
                        <h4 class="mb-4">
                            <i class="fas fa-credit-card text-primary"></i> Mode de paiement
                        </h4>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="mode_paiement"
                                           id="carte" value="carte" {{ old('mode_paiement', 'carte') == 'carte' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="carte">
                                        <i class="fab fa-cc-visa"></i> Carte bancaire
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="mode_paiement"
                                           id="sepa" value="sepa" {{ old('mode_paiement') == 'sepa' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="sepa">
                                        <i class="fas fa-university"></i> Prélèvement SEPA
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="mode_paiement"
                                           id="virement" value="virement" {{ old('mode_paiement') == 'virement' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="virement">
                                        <i class="fas fa-exchange-alt"></i> Virement bancaire
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CGV -->
                    <div class="form-section">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="accepte_cgv"
                                   id="accepteCGV" value="1" {{ old('accepte_cgv') ? 'checked' : '' }} required>
                            <label class="form-check-label" for="accepteCGV">
                                J'accepte les <a href="#" target="_blank">Conditions Générales de Vente</a> *
                            </label>
                        </div>
                    </div>

                    <!-- Boutons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('public.booking.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Retour
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-lock"></i> Confirmer et payer
                        </button>
                    </div>
                </form>
            </div>

            <!-- Résumé -->
            <div class="col-lg-4">
                <div class="price-summary">
                    <h4 class="mb-4">
                        <i class="fas fa-shopping-cart"></i> Résumé
                    </h4>

                    <div class="mb-3">
                        <strong>Box sélectionné :</strong><br>
                        {{ $famille->nom }} - Box {{ $box->numero }}
                    </div>

                    <div class="mb-3">
                        <strong>Surface :</strong><br>
                        {{ $famille->surface }} m²
                    </div>

                    <div class="mb-3">
                        <strong>Tarif de base :</strong><br>
                        <span class="h5">{{ number_format($tarifMensuel, 2) }} €/mois</span>
                    </div>

                    <div class="mb-3" id="discountInfo" style="display: none;">
                        <span class="discount-badge">
                            <i class="fas fa-tag"></i> Réduction : <span id="discountValue">0</span>%
                        </span>
                    </div>

                    <hr class="bg-white">

                    <div class="mb-3">
                        <strong>Tarif mensuel après réduction :</strong><br>
                        <span class="h4" id="finalMonthlyPrice">{{ number_format($tarifMensuel, 2) }} €</span>
                    </div>

                    <div class="mb-3">
                        <strong>Durée :</strong><br>
                        <span id="durationDisplay">1 mois</span>
                    </div>

                    <hr class="bg-white">

                    <div>
                        <strong>Total à payer :</strong><br>
                        <span class="h3" id="totalPrice">{{ number_format($tarifMensuel, 2) }} €</span>
                    </div>

                    <div class="mt-4">
                        <small>
                            <i class="fas fa-info-circle"></i> Paiement 100% sécurisé
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const basePrice = {{ $tarifMensuel }};
        const boxId = {{ $box->id }};

        document.getElementById('dureeMois').addEventListener('change', function() {
            updatePricing();
        });

        function updatePricing() {
            const dureeMois = parseInt(document.getElementById('dureeMois').value);

            // Calcul de la réduction
            let reduction = 0;
            if (dureeMois >= 12) {
                reduction = 15;
            } else if (dureeMois >= 6) {
                reduction = 10;
            } else if (dureeMois >= 3) {
                reduction = 5;
            }

            // Affichage de la réduction
            if (reduction > 0) {
                document.getElementById('discountInfo').style.display = 'block';
                document.getElementById('discountValue').textContent = reduction;
            } else {
                document.getElementById('discountInfo').style.display = 'none';
            }

            // Calculs
            const tarifMensuel = basePrice * (1 - reduction / 100);
            const montantTotal = tarifMensuel * dureeMois;

            // Mise à jour affichage
            document.getElementById('finalMonthlyPrice').textContent = tarifMensuel.toFixed(2) + ' €';
            document.getElementById('totalPrice').textContent = montantTotal.toFixed(2) + ' €';
            document.getElementById('durationDisplay').textContent = dureeMois + ' mois';
        }

        // Initialiser au chargement
        updatePricing();
    </script>
</body>
</html>
