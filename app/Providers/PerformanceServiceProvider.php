<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;

class PerformanceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Optimisation de la pagination
        \Illuminate\Pagination\Paginator::useBootstrapFive();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Désactiver les logs de requêtes en production pour performance
        if (config('app.env') === 'production') {
            DB::connection()->disableQueryLog();
        }

        // Partager des données communes à toutes les vues
        View::composer('*', function ($view) {
            // Version de l'application pour cache busting
            $view->with('app_version', config('app.version', '2.2.0'));
        });

        // Directive Blade pour formater les nombres
        Blade::directive('currency', function ($expression) {
            return "<?php echo number_format($expression, 2, ',', ' ') . ' €'; ?>";
        });

        // Directive Blade pour formater les dates
        Blade::directive('dateFormat', function ($expression) {
            return "<?php echo ($expression) ? $expression->format('d/m/Y') : '-'; ?>";
        });

        // Directive Blade pour formater les dates avec heure
        Blade::directive('datetimeFormat', function ($expression) {
            return "<?php echo ($expression) ? $expression->format('d/m/Y H:i') : '-'; ?>";
        });

        // Directive Blade pour badge statut
        Blade::directive('statusBadge', function ($expression) {
            return "<?php
                \$statusColors = [
                    'actif' => 'success',
                    'en_attente' => 'warning',
                    'termine' => 'secondary',
                    'annule' => 'danger',
                    'payée' => 'success',
                    'en_retard' => 'danger',
                    'brouillon' => 'secondary'
                ];
                \$status = $expression;
                \$color = \$statusColors[\$status] ?? 'secondary';
                echo '<span class=\"badge bg-' . \$color . '\">' . ucfirst(\$status) . '</span>';
            ?>";
        });
    }
}
