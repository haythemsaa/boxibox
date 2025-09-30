<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Inscription - {{ config('app.name', 'Boxibox') }}</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 2rem 0;
        }

        .register-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 1000px;
            width: 100%;
        }

        .register-image {
            background: linear-gradient(45deg, #667eea, #764ba2);
            min-height: 600px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            padding: 2rem;
        }

        .register-form {
            padding: 3rem;
        }

        .brand-logo {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .brand-name {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .brand-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 10px;
            border: 2px solid #e9ecef;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 10px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .register-title {
            color: #333;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 500;
            color: #555;
        }

        .login-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .login-link:hover {
            color: #764ba2;
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .note {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid #28a745;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="register-container row g-0">
            <!-- Image Section -->
            <div class="col-lg-5 register-image">
                <div>
                    <div class="brand-logo">
                        <i class="fas fa-archive"></i>
                    </div>
                    <div class="brand-name">Boxibox</div>
                    <div class="brand-subtitle">Créez votre compte</div>
                    <div class="mt-4">
                        <p class="mb-3">Rejoignez des centaines d'entreprises qui font confiance à Boxibox</p>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-check me-2"></i> Interface intuitive et moderne</li>
                            <li class="mb-2"><i class="fas fa-check me-2"></i> Gestion complète des clients</li>
                            <li class="mb-2"><i class="fas fa-check me-2"></i> Facturation automatisée</li>
                            <li class="mb-2"><i class="fas fa-check me-2"></i> Exports SEPA intégrés</li>
                            <li class="mb-2"><i class="fas fa-check me-2"></i> Support technique inclus</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Register Form -->
            <div class="col-lg-7 register-form">
                <h2 class="register-title text-center">
                    <i class="fas fa-user-plus me-2"></i>
                    Créer un compte
                </h2>

                <!-- Note -->
                <div class="note">
                    <h6 class="mb-2"><i class="fas fa-info-circle me-2"></i>Information</h6>
                    <small class="text-muted">
                        L'inscription est actuellement limitée aux administrateurs. Contactez votre administrateur système pour obtenir un compte.
                    </small>
                </div>

                <!-- Errors -->
                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="row">
                        <!-- Name -->
                        <div class="col-md-12 mb-3">
                            <label for="name" class="form-label">
                                <i class="fas fa-user me-2"></i>Nom complet *
                            </label>
                            <input id="name" type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   name="name"
                                   value="{{ old('name') }}"
                                   required
                                   autocomplete="name"
                                   autofocus
                                   placeholder="John Doe">
                            @error('name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope me-2"></i>Adresse email *
                        </label>
                        <input id="email" type="email"
                               class="form-control @error('email') is-invalid @enderror"
                               name="email"
                               value="{{ old('email') }}"
                               required
                               autocomplete="email"
                               placeholder="john.doe@entreprise.com">
                        @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Phone -->
                    <div class="mb-3">
                        <label for="phone" class="form-label">
                            <i class="fas fa-phone me-2"></i>Téléphone
                        </label>
                        <input id="phone" type="tel"
                               class="form-control @error('phone') is-invalid @enderror"
                               name="phone"
                               value="{{ old('phone') }}"
                               autocomplete="tel"
                               placeholder="06 12 34 56 78">
                        @error('phone')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="row">
                        <!-- Password -->
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock me-2"></i>Mot de passe *
                            </label>
                            <input id="password" type="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   name="password"
                                   required
                                   autocomplete="new-password"
                                   placeholder="••••••••">
                            @error('password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <small class="text-muted">Minimum 8 caractères</small>
                        </div>

                        <!-- Confirm Password -->
                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">
                                <i class="fas fa-lock me-2"></i>Confirmer le mot de passe *
                            </label>
                            <input id="password_confirmation" type="password"
                                   class="form-control"
                                   name="password_confirmation"
                                   required
                                   autocomplete="new-password"
                                   placeholder="••••••••">
                        </div>
                    </div>

                    <!-- Terms -->
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="terms" id="terms" required>
                            <label class="form-check-label" for="terms">
                                J'accepte les <a href="#" class="login-link">conditions d'utilisation</a> et la <a href="#" class="login-link">politique de confidentialité</a>
                            </label>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus me-2"></i>
                            Créer mon compte
                        </button>
                    </div>
                </form>

                <!-- Login Link -->
                <div class="text-center">
                    <p class="mb-0">
                        Vous avez déjà un compte ?
                        <a href="{{ route('login') }}" class="login-link">
                            Se connecter
                        </a>
                    </p>
                </div>

                <!-- Footer -->
                <div class="text-center mt-4">
                    <small class="text-muted">
                        © {{ date('Y') }} Boxibox. Solution de gestion de self-stockage.
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>