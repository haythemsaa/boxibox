<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Espace Client') - BOXIBOX</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            font-family: 'Figtree', sans-serif;
            background-color: #f8f9fa;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: #0d6efd !important;
        }

        .sidebar {
            min-height: calc(100vh - 56px);
            background: white;
            box-shadow: 2px 0 5px rgba(0,0,0,0.05);
        }

        .sidebar .nav-link {
            color: #495057;
            padding: 0.8rem 1.5rem;
            border-left: 3px solid transparent;
            transition: all 0.2s;
        }

        .sidebar .nav-link:hover {
            background-color: #f8f9fa;
            border-left-color: #0d6efd;
            color: #0d6efd;
        }

        .sidebar .nav-link.active {
            background-color: #e7f1ff;
            border-left-color: #0d6efd;
            color: #0d6efd;
            font-weight: 600;
        }

        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }

        .main-content {
            padding: 2rem;
        }

        .alert {
            border-radius: 8px;
        }

        .card {
            border: none;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
            border-radius: 10px;
        }

        .user-info {
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .user-info strong {
            color: #0d6efd;
        }
    </style>

    @stack('styles')
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('client.dashboard') }}">
                <i class="fas fa-box-open me-2"></i>BOXIBOX
            </a>
            <span class="navbar-text text-white me-3">
                Espace Client
            </span>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>
                            {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('client.profil') }}">
                                <i class="fas fa-user me-2"></i>Mon Profil
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="user-info">
                        <small class="text-muted d-block">Connecté en tant que</small>
                        <strong>{{ Auth::user()->name }}</strong>
                    </div>

                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('client.dashboard') ? 'active' : '' }}"
                               href="{{ route('client.dashboard') }}">
                                <i class="fas fa-home"></i>
                                Tableau de bord
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('client.contrats*') ? 'active' : '' }}"
                               href="{{ route('client.contrats') }}">
                                <i class="fas fa-file-contract"></i>
                                Mes Contrats
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('client.sepa*') ? 'active' : '' }}"
                               href="{{ route('client.sepa') }}">
                                <i class="fas fa-university"></i>
                                Mandats SEPA
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('client.factures*') ? 'active' : '' }}"
                               href="{{ route('client.factures') }}">
                                <i class="fas fa-file-invoice"></i>
                                Factures & Avoirs
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('client.reglements*') ? 'active' : '' }}"
                               href="{{ route('client.reglements') }}">
                                <i class="fas fa-money-bill-wave"></i>
                                Règlements
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('client.relances*') ? 'active' : '' }}"
                               href="{{ route('client.relances') }}">
                                <i class="fas fa-bell"></i>
                                Relances
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('client.documents*') ? 'active' : '' }}"
                               href="{{ route('client.documents') }}">
                                <i class="fas fa-folder"></i>
                                Mes Fichiers
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('client.suivi*') ? 'active' : '' }}"
                               href="{{ route('client.suivi') }}">
                                <i class="fas fa-history"></i>
                                Suivi
                            </a>
                        </li>

                        <li class="nav-item mt-3">
                            <a class="nav-link {{ request()->routeIs('client.profil*') ? 'active' : '' }}"
                               href="{{ route('client.profil') }}">
                                <i class="fas fa-user-cog"></i>
                                Informations
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 main-content">
                <!-- Alerts -->
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Erreurs:</strong>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Page Content -->
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>
</html>