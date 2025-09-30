<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Statistiques d'occupation -->
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="mb-3"><i class="fas fa-chart-pie me-2"></i> Statistiques d'occupation</h3>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="stat-card text-center">
                <div class="stat-number text-primary"><?php echo e($stats['occupation']['total_boxes']); ?></div>
                <div class="stat-label">Total des boxes</div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="stat-card text-center">
                <div class="stat-number text-success"><?php echo e($stats['occupation']['boxes_libres']); ?></div>
                <div class="stat-label">Boxes libres</div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="stat-card text-center">
                <div class="stat-number text-info"><?php echo e($stats['occupation']['boxes_occupes']); ?></div>
                <div class="stat-label">Boxes occupées</div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="stat-card text-center">
                <div class="stat-number text-warning"><?php echo e($stats['occupation']['boxes_reserves']); ?></div>
                <div class="stat-label">Boxes réservées</div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="stat-card text-center">
                <div class="stat-number text-dark"><?php echo e($stats['occupation']['clients_actifs']); ?></div>
                <div class="stat-label">Clients actifs</div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="stat-card text-center">
                <div class="stat-number text-secondary"><?php echo e($stats['occupation']['contrats_actifs']); ?></div>
                <div class="stat-label">Contrats actifs</div>
            </div>
        </div>
    </div>

    <!-- Taux d'occupation -->
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="mb-3"><i class="fas fa-percentage me-2"></i> Taux d'occupation</h3>
        </div>

        <div class="col-md-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5>Occupation par nombre</h5>
                        <div class="stat-number"><?php echo e($stats['surfaces']['taux_occupation_nombre']); ?>%</div>
                    </div>
                    <div class="progress-circle">
                        <canvas id="occupationNombreChart" width="80" height="80"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5>Occupation par surface</h5>
                        <div class="stat-number"><?php echo e($stats['surfaces']['taux_occupation_surface']); ?>%</div>
                    </div>
                    <div class="progress-circle">
                        <canvas id="occupationSurfaceChart" width="80" height="80"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques financières -->
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="mb-3"><i class="fas fa-euro-sign me-2"></i> Indicateurs financiers</h3>
        </div>

        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-success"><?php echo e(number_format($stats['financier']['ca_mensuel_ht'], 2)); ?>€</div>
                <div class="stat-label">CA mensuel HT</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-info"><?php echo e(number_format($stats['financier']['montant_assurances'], 2)); ?>€</div>
                <div class="stat-label">Assurances</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-primary"><?php echo e(number_format($stats['financier']['ca_maximal_ht'], 2)); ?>€</div>
                <div class="stat-label">CA maximal HT</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number"><?php echo e(number_format($stats['surfaces']['surface_totale'], 0)); ?> m²</div>
                <div class="stat-label">Surface totale</div>
            </div>
        </div>
    </div>

    <!-- Résumé mensuel -->
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="mb-3"><i class="fas fa-calendar-month me-2"></i> Résumé du mois</h3>
        </div>

        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-success"><?php echo e(number_format($stats['mensuel']['ca_ttc'], 2)); ?>€</div>
                <div class="stat-label">CA TTC</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-primary"><?php echo e($stats['mensuel']['nb_factures']); ?></div>
                <div class="stat-label">Factures émises</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-warning"><?php echo e(number_format($stats['mensuel']['montant_avoirs'], 2)); ?>€</div>
                <div class="stat-label">Avoirs</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-number text-info"><?php echo e(number_format($stats['mensuel']['encaissements'], 2)); ?>€</div>
                <div class="stat-label">Encaissements</div>
            </div>
        </div>
    </div>

    <!-- Graphiques -->
    <div class="row">
        <div class="col-md-6">
            <div class="stat-card">
                <h5 class="mb-3">Évolution du chiffre d'affaires</h5>
                <canvas id="caEvolutionChart" height="300"></canvas>
            </div>
        </div>

        <div class="col-md-6">
            <div class="stat-card">
                <h5 class="mb-3">Évolution des contrats</h5>
                <canvas id="contratsEvolutionChart" height="300"></canvas>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="stat-card">
                <h5 class="mb-3">Répartition par surface</h5>
                <canvas id="repartitionSurfaceChart" height="300"></canvas>
            </div>
        </div>

        <div class="col-md-6">
            <div class="stat-card">
                <h5 class="mb-3">État de santé du site</h5>
                <div class="d-flex justify-content-center align-items-center" style="height: 300px;">
                    <div class="text-center">
                        <i class="fas fa-heart text-success" style="font-size: 4rem;"></i>
                        <h4 class="mt-3 text-success">Excellent</h4>
                        <p class="text-muted">Taux d'occupation : <?php echo e($stats['surfaces']['taux_occupation_nombre']); ?>%</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    // Graphiques circulaires pour les taux d'occupation
    function createDoughnutChart(canvasId, percentage) {
        const ctx = document.getElementById(canvasId).getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [percentage, 100 - percentage],
                    backgroundColor: ['#28a745', '#e9ecef'],
                    borderWidth: 0
                }]
            },
            options: {
                cutout: '70%',
                responsive: false,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: false
                    }
                }
            }
        });
    }

    createDoughnutChart('occupationNombreChart', <?php echo e($stats['surfaces']['taux_occupation_nombre']); ?>);
    createDoughnutChart('occupationSurfaceChart', <?php echo e($stats['surfaces']['taux_occupation_surface']); ?>);

    // Évolution du CA
    const caCtx = document.getElementById('caEvolutionChart').getContext('2d');
    new Chart(caCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($charts['ca_evolution'], 'mois')); ?>,
            datasets: [{
                label: 'Chiffre d\'affaires (€)',
                data: <?php echo json_encode(array_column($charts['ca_evolution'], 'ca')); ?>,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Évolution des contrats
    const contratsCtx = document.getElementById('contratsEvolutionChart').getContext('2d');
    new Chart(contratsCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($charts['contrats_evolution'], 'mois')); ?>,
            datasets: [{
                label: 'Entrées',
                data: <?php echo json_encode(array_column($charts['contrats_evolution'], 'entrees')); ?>,
                backgroundColor: '#28a745'
            }, {
                label: 'Sorties',
                data: <?php echo json_encode(array_column($charts['contrats_evolution'], 'sorties')); ?>,
                backgroundColor: '#dc3545'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Répartition par surface
    const surfaceCtx = document.getElementById('repartitionSurfaceChart').getContext('2d');
    new Chart(surfaceCtx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($charts['repartition_surface']->pluck('tranche_surface')); ?>,
            datasets: [{
                data: <?php echo json_encode($charts['repartition_surface']->pluck('nombre')); ?>,
                backgroundColor: ['#007bff', '#28a745', '#ffc107', '#fd7e14', '#e83e8c']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp2025\htdocs\boxibox\resources\views/dashboard/index.blade.php ENDPATH**/ ?>