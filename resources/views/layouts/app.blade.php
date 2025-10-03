<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Boxibox') }}</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            background: #343a40;
            padding-top: 20px;
            transition: all 0.3s;
        }

        .sidebar .nav-link {
            color: #adb5bd;
            padding: 0.75rem 1.5rem;
            border-radius: 0;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: #495057;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s;
        }

        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid #dee2e6;
            margin-bottom: 1rem;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #495057;
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .progress-circle {
            width: 80px;
            height: 80px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="text-center text-white mb-4">
            <h4><i class="fas fa-archive"></i> Boxibox</h4>
        </div>

        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
            </li>

            <!-- Gestion Commerciale -->
            <li class="nav-item">
                <a class="nav-link text-white-50 small fw-bold text-uppercase" href="#" style="pointer-events: none;">
                    Gestion Commerciale
                </a>
            </li>
            @can('view_prospects')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('prospects.*') ? 'active' : '' }}" href="{{ route('prospects.index') }}">
                    <i class="fas fa-user-plus me-2"></i> Prospects
                </a>
            </li>
            @endcan

            @can('view_clients')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}" href="{{ route('clients.index') }}">
                    <i class="fas fa-users me-2"></i> Clients
                </a>
            </li>
            @endcan

            @can('view_contrats')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('contrats.*') ? 'active' : '' }}" href="{{ route('contrats.index') }}">
                    <i class="fas fa-file-contract me-2"></i> Contrats
                </a>
            </li>
            @endcan

            <!-- Gestion Financière -->
            <li class="nav-item">
                <a class="nav-link text-white-50 small fw-bold text-uppercase mt-3" href="#" style="pointer-events: none;">
                    Gestion Financière
                </a>
            </li>
            @can('view_factures')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('factures.*') ? 'active' : '' }}" href="{{ route('factures.index') }}">
                    <i class="fas fa-file-invoice me-2"></i> Factures
                </a>
            </li>
            @endcan

            @can('view_reglements')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('reglements.*') ? 'active' : '' }}" href="{{ route('reglements.index') }}">
                    <i class="fas fa-credit-card me-2"></i> Règlements
                </a>
            </li>
            @endcan

            @can('view_sepa')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('sepa.*') ? 'active' : '' }}" href="{{ route('sepa.index') }}">
                    <i class="fas fa-university me-2"></i> SEPA
                </a>
            </li>
            @endcan

            <!-- Gestion Technique -->
            <li class="nav-item">
                <a class="nav-link text-white-50 small fw-bold text-uppercase mt-3" href="#" style="pointer-events: none;">
                    Gestion Technique
                </a>
            </li>
            @can('view_boxes')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('boxes.index') ? 'active' : '' }}" href="{{ route('boxes.index') }}">
                    <i class="fas fa-th-large me-2"></i> Boxes (Liste)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('boxes.plan') ? 'active' : '' }}" href="{{ route('boxes.plan') }}">
                    <i class="fas fa-map me-2"></i> Plan Interactif
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('boxes.plan.editor') ? 'active' : '' }}" href="{{ route('boxes.plan.editor') }}">
                    <i class="fas fa-edit me-2"></i> Éditeur de Plan
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('boxes.plan.editor.advanced') ? 'active' : '' }}" href="{{ route('boxes.plan.editor.advanced') }}">
                    <i class="fas fa-pencil-ruler me-2"></i> Éditeur Avancé
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('boxes.floorplan.designer') ? 'active' : '' }}" href="{{ route('boxes.floorplan.designer') }}">
                    <i class="fas fa-pen-fancy me-2"></i> Designer de Salle
                </a>
            </li>
            @endcan

            @can('view_emplacements')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('emplacements.*') ? 'active' : '' }}" href="{{ route('emplacements.index') }}">
                    <i class="fas fa-map-marker-alt me-2"></i> Emplacements
                </a>
            </li>
            @endcan

            @can('view_box_familles')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('box-familles.*') ? 'active' : '' }}" href="{{ route('box-familles.index') }}">
                    <i class="fas fa-tags me-2"></i> Familles de Boxes
                </a>
            </li>
            @endcan

            <!-- Administration -->
            <li class="nav-item">
                <a class="nav-link text-white-50 small fw-bold text-uppercase mt-3" href="#" style="pointer-events: none;">
                    Administration
                </a>
            </li>
            @can('view_users')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                    <i class="fas fa-users-cog me-2"></i> Utilisateurs
                </a>
            </li>
            @endcan

            @can('view_statistics')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.statistics') ? 'active' : '' }}" href="{{ route('admin.statistics') }}">
                    <i class="fas fa-chart-line me-2"></i> Statistiques
                </a>
            </li>
            @endcan

            @can('view_signatures')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('signatures.*') ? 'active' : '' }}" href="{{ route('signatures.index') }}">
                    <i class="fas fa-signature me-2"></i> Signatures
                </a>
            </li>
            @endcan
        </ul>

        <!-- User Menu -->
        <div class="position-absolute bottom-0 w-100 p-3">
            <div class="dropdown">
                <button class="btn btn-outline-light btn-sm dropdown-toggle w-100" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-user me-2"></i> {{ Auth::user()->name }}
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fas fa-cog me-2"></i> Paramètres</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item"><i class="fas fa-sign-out-alt me-2"></i> Déconnexion</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>@yield('title')</h1>
            <div>
                @yield('actions')
            </div>
        </div>

        <!-- Alerts -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Content -->
        @yield('content')
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>