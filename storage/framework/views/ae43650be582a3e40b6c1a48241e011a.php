<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e(config('app.name', 'Boxibox')); ?></title>

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
                <a class="nav-link <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>" href="<?php echo e(route('dashboard')); ?>">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
            </li>

            <!-- Gestion Commerciale -->
            <li class="nav-item">
                <a class="nav-link text-white-50 small fw-bold text-uppercase" href="#" style="pointer-events: none;">
                    Gestion Commerciale
                </a>
            </li>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_prospects')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->routeIs('prospects.*') ? 'active' : ''); ?>" href="<?php echo e(route('prospects.index')); ?>">
                    <i class="fas fa-user-plus me-2"></i> Prospects
                </a>
            </li>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_clients')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->routeIs('clients.*') ? 'active' : ''); ?>" href="<?php echo e(route('clients.index')); ?>">
                    <i class="fas fa-users me-2"></i> Clients
                </a>
            </li>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_contrats')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->routeIs('contrats.*') ? 'active' : ''); ?>" href="<?php echo e(route('contrats.index')); ?>">
                    <i class="fas fa-file-contract me-2"></i> Contrats
                </a>
            </li>
            <?php endif; ?>

            <!-- Gestion Financière -->
            <li class="nav-item">
                <a class="nav-link text-white-50 small fw-bold text-uppercase mt-3" href="#" style="pointer-events: none;">
                    Gestion Financière
                </a>
            </li>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_factures')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->routeIs('factures.*') ? 'active' : ''); ?>" href="<?php echo e(route('factures.index')); ?>">
                    <i class="fas fa-file-invoice me-2"></i> Factures
                </a>
            </li>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_reglements')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->routeIs('reglements.*') ? 'active' : ''); ?>" href="<?php echo e(route('reglements.index')); ?>">
                    <i class="fas fa-credit-card me-2"></i> Règlements
                </a>
            </li>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_sepa')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->routeIs('sepa.*') ? 'active' : ''); ?>" href="<?php echo e(route('sepa.index')); ?>">
                    <i class="fas fa-university me-2"></i> SEPA
                </a>
            </li>
            <?php endif; ?>

            <!-- Gestion Technique -->
            <li class="nav-item">
                <a class="nav-link text-white-50 small fw-bold text-uppercase mt-3" href="#" style="pointer-events: none;">
                    Gestion Technique
                </a>
            </li>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_boxes')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->routeIs('boxes.*') ? 'active' : ''); ?>" href="<?php echo e(route('boxes.index')); ?>">
                    <i class="fas fa-th-large me-2"></i> Plan des Boxes
                </a>
            </li>
            <?php endif; ?>
        </ul>

        <!-- User Menu -->
        <div class="position-absolute bottom-0 w-100 p-3">
            <div class="dropdown">
                <button class="btn btn-outline-light btn-sm dropdown-toggle w-100" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-user me-2"></i> <?php echo e(Auth::user()->name); ?>

                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="<?php echo e(route('profile.edit')); ?>"><i class="fas fa-cog me-2"></i> Paramètres</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="<?php echo e(route('logout')); ?>">
                            <?php echo csrf_field(); ?>
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
            <h1><?php echo $__env->yieldContent('title'); ?></h1>
            <div>
                <?php echo $__env->yieldContent('actions'); ?>
            </div>
        </div>

        <!-- Alerts -->
        <?php if(session('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo e(session('success')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo e(session('error')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if($errors->any()): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Content -->
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html><?php /**PATH C:\xampp2025\htdocs\boxibox\resources\views/layouts/app.blade.php ENDPATH**/ ?>