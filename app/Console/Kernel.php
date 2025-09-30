<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Génération automatique des factures le 1er de chaque mois à 06h00
        $schedule->command('boxibox:generate-invoices')
                 ->monthlyOn(1, '06:00')
                 ->withoutOverlapping()
                 ->onSuccess(function () {
                     \Log::info('Factures mensuelles générées avec succès');
                 })
                 ->onFailure(function () {
                     \Log::error('Échec de la génération des factures mensuelles');
                 });

        // Envoi des relances automatiques tous les jours à 09h00
        $schedule->command('boxibox:send-reminders')
                 ->dailyAt('09:00')
                 ->withoutOverlapping()
                 ->onSuccess(function () {
                     \Log::info('Relances automatiques traitées');
                 })
                 ->onFailure(function () {
                     \Log::error('Échec du traitement des relances');
                 });

        // Génération des fichiers SEPA tous les 15 du mois à 10h00
        $schedule->command('boxibox:generate-sepa')
                 ->monthlyOn(15, '10:00')
                 ->withoutOverlapping()
                 ->onSuccess(function () {
                     \Log::info('Fichier SEPA généré automatiquement');
                 })
                 ->onFailure(function () {
                     \Log::error('Échec de la génération SEPA automatique');
                 });

        // Nettoyage des logs anciens chaque semaine
        $schedule->command('log:clear --keep=30')
                 ->weekly()
                 ->at('02:00');

        // Sauvegarde de base de données quotidienne à 03h00
        $schedule->call(function () {
            $this->backupDatabase();
        })->dailyAt('03:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    /**
     * Sauvegarde de la base de données
     */
    private function backupDatabase()
    {
        try {
            $filename = 'backup_' . date('Y_m_d_H_i_s') . '.sql';
            $backupPath = storage_path('backups/' . $filename);

            // Créer le dossier si nécessaire
            if (!file_exists(dirname($backupPath))) {
                mkdir(dirname($backupPath), 0755, true);
            }

            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');

            $command = "mysqldump --user={$username} --password={$password} --host={$host} {$database} > {$backupPath}";

            exec($command, $output, $returnCode);

            if ($returnCode === 0) {
                \Log::info("Sauvegarde créée : {$filename}");

                // Supprimer les sauvegardes anciennes (garder 7 jours)
                $this->cleanOldBackups();
            } else {
                \Log::error("Échec de la sauvegarde de base de données");
            }

        } catch (\Exception $e) {
            \Log::error("Erreur lors de la sauvegarde : " . $e->getMessage());
        }
    }

    /**
     * Nettoie les anciennes sauvegardes
     */
    private function cleanOldBackups()
    {
        $backupDir = storage_path('backups');
        $files = glob($backupDir . '/backup_*.sql');

        if (count($files) > 7) {
            // Trier par date de création
            usort($files, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });

            // Supprimer les plus anciennes
            $toDelete = array_slice($files, 0, count($files) - 7);
            foreach ($toDelete as $file) {
                unlink($file);
            }
        }
    }
}