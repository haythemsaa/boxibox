<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckPermissions extends Command
{
    protected $signature = 'boxibox:check-permissions';
    protected $description = 'Vérifie les permissions et la configuration du système';

    public function handle()
    {
        $this->info('🔍 Vérification de la configuration Boxibox...');
        $this->newLine();

        $allChecks = true;

        // Vérifier les permissions des dossiers
        $allChecks &= $this->checkDirectoryPermissions();

        // Vérifier la configuration de base de données
        $allChecks &= $this->checkDatabase();

        // Vérifier la configuration SEPA
        $allChecks &= $this->checkSepaConfiguration();

        // Vérifier les migrations
        $allChecks &= $this->checkMigrations();

        // Vérifier les seeders
        $allChecks &= $this->checkSeeders();

        $this->newLine();

        if ($allChecks) {
            $this->info('✅ Toutes les vérifications sont passées avec succès !');
            return 0;
        } else {
            $this->error('❌ Certaines vérifications ont échoué. Consultez les détails ci-dessus.');
            return 1;
        }
    }

    private function checkDirectoryPermissions()
    {
        $this->line('📁 Vérification des permissions de dossiers...');

        $directories = [
            'storage/app' => 'Documents et fichiers',
            'storage/logs' => 'Logs de l\'application',
            'storage/framework/cache' => 'Cache du framework',
            'storage/framework/sessions' => 'Sessions',
            'storage/framework/views' => 'Vues compilées',
            'bootstrap/cache' => 'Cache de bootstrap',
        ];

        $allOk = true;

        foreach ($directories as $dir => $description) {
            $path = base_path($dir);

            if (!file_exists($path)) {
                $this->warn("   ⚠️  Dossier manquant : {$dir} ({$description})");
                mkdir($path, 0775, true);
                $this->line("   ✅ Dossier créé : {$dir}");
            }

            if (is_writable($path)) {
                $this->line("   ✅ {$dir} - Accessible en écriture");
            } else {
                $this->error("   ❌ {$dir} - Non accessible en écriture");
                $allOk = false;
            }
        }

        return $allOk;
    }

    private function checkDatabase()
    {
        $this->line('🗄️ Vérification de la base de données...');

        try {
            \DB::connection()->getPdo();
            $this->line('   ✅ Connexion à la base de données réussie');

            $tables = \DB::select('SHOW TABLES');
            $this->line('   ✅ ' . count($tables) . ' tables trouvées');

            return true;

        } catch (\Exception $e) {
            $this->error('   ❌ Erreur de connexion : ' . $e->getMessage());
            return false;
        }
    }

    private function checkSepaConfiguration()
    {
        $this->line('💳 Vérification de la configuration SEPA...');

        $config = [
            'SEPA_CREDITOR_ID' => env('SEPA_CREDITOR_ID'),
            'SEPA_CREDITOR_NAME' => env('SEPA_CREDITOR_NAME'),
            'SEPA_CREDITOR_IBAN' => env('SEPA_CREDITOR_IBAN'),
            'SEPA_CREDITOR_BIC' => env('SEPA_CREDITOR_BIC'),
        ];

        $allOk = true;

        foreach ($config as $key => $value) {
            if (empty($value)) {
                $this->warn("   ⚠️  Configuration manquante : {$key}");
                $allOk = false;
            } else {
                $this->line("   ✅ {$key} configuré");
            }
        }

        // Vérifier le dossier SEPA
        $sepaDir = storage_path('app/sepa');
        if (!file_exists($sepaDir)) {
            mkdir($sepaDir, 0775, true);
            $this->line('   ✅ Dossier SEPA créé');
        }

        if (is_writable($sepaDir)) {
            $this->line('   ✅ Dossier SEPA accessible en écriture');
        } else {
            $this->error('   ❌ Dossier SEPA non accessible en écriture');
            $allOk = false;
        }

        return $allOk;
    }

    private function checkMigrations()
    {
        $this->line('🔄 Vérification des migrations...');

        try {
            $pending = \Artisan::call('migrate:status');
            $output = \Artisan::output();

            if (strpos($output, 'Pending') !== false) {
                $this->warn('   ⚠️  Des migrations sont en attente');
                return false;
            } else {
                $this->line('   ✅ Toutes les migrations sont appliquées');
                return true;
            }

        } catch (\Exception $e) {
            $this->error('   ❌ Erreur lors de la vérification des migrations : ' . $e->getMessage());
            return false;
        }
    }

    private function checkSeeders()
    {
        $this->line('🌱 Vérification des données initiales...');

        $checks = [
            'Rôles' => \App\Models\Role::count(),
            'Permissions' => \App\Models\Permission::count(),
            'Utilisateurs' => \App\Models\User::count(),
            'Box Familles' => \App\Models\BoxFamille::count(),
            'Emplacements' => \App\Models\Emplacement::count(),
            'Services' => \App\Models\Service::count(),
            'Boxes' => \App\Models\Box::count(),
        ];

        $allOk = true;

        foreach ($checks as $item => $count) {
            if ($count > 0) {
                $this->line("   ✅ {$item} : {$count} éléments");
            } else {
                $this->warn("   ⚠️  {$item} : Aucun élément trouvé");
                $allOk = false;
            }
        }

        return $allOk;
    }
}