<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup {--keep=7 : Nombre de jours de backups à conserver}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crée une sauvegarde de la base de données';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Démarrage de la sauvegarde de la base de données...');

        $database = config('database.connections.' . config('database.default') . '.database');
        $username = config('database.connections.' . config('database.default') . '.username');
        $password = config('database.connections.' . config('database.default') . '.password');
        $host = config('database.connections.' . config('database.default') . '.host');

        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $filename = "backup_{$database}_{$timestamp}.sql";
        $backupPath = storage_path("app/backups/{$filename}");

        // Créer le dossier backups s'il n'existe pas
        if (!file_exists(storage_path('app/backups'))) {
            mkdir(storage_path('app/backups'), 0755, true);
        }

        // Construire la commande mysqldump
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows
            $command = sprintf(
                'mysqldump --user=%s --password=%s --host=%s %s > %s',
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($host),
                escapeshellarg($database),
                escapeshellarg($backupPath)
            );
        } else {
            // Linux/Unix
            $command = sprintf(
                'mysqldump -u%s -p%s -h%s %s > %s 2>&1',
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($host),
                escapeshellarg($database),
                escapeshellarg($backupPath)
            );
        }

        // Exécuter la commande
        $output = [];
        $returnVar = 0;
        exec($command, $output, $returnVar);

        if ($returnVar === 0 && file_exists($backupPath)) {
            $fileSize = round(filesize($backupPath) / 1024 / 1024, 2);
            $this->info("✓ Sauvegarde créée avec succès : {$filename}");
            $this->info("  Taille : {$fileSize} MB");
            $this->info("  Emplacement : {$backupPath}");

            // Compresser le backup
            if (extension_loaded('zlib')) {
                $this->info('Compression du backup...');
                $gzFilename = $backupPath . '.gz';

                $fp = fopen($backupPath, 'rb');
                $gz = gzopen($gzFilename, 'wb9');

                while (!feof($fp)) {
                    gzwrite($gz, fread($fp, 1024 * 512));
                }

                fclose($fp);
                gzclose($gz);

                if (file_exists($gzFilename)) {
                    unlink($backupPath); // Supprimer le fichier non compressé
                    $gzSize = round(filesize($gzFilename) / 1024 / 1024, 2);
                    $this->info("✓ Backup compressé : {$gzSize} MB");
                }
            }

            // Nettoyage des anciens backups
            $keepDays = $this->option('keep');
            $this->cleanOldBackups($keepDays);

            return Command::SUCCESS;
        } else {
            $this->error('✗ Erreur lors de la création de la sauvegarde');
            $this->error('Sortie : ' . implode("\n", $output));
            return Command::FAILURE;
        }
    }

    /**
     * Supprime les anciens backups
     */
    private function cleanOldBackups(int $keepDays)
    {
        $this->info("Nettoyage des backups de plus de {$keepDays} jours...");

        $backupDir = storage_path('app/backups');
        $files = glob($backupDir . '/backup_*.sql*');
        $deleted = 0;

        foreach ($files as $file) {
            if (filemtime($file) < strtotime("-{$keepDays} days")) {
                unlink($file);
                $deleted++;
            }
        }

        if ($deleted > 0) {
            $this->info("✓ {$deleted} ancien(s) backup(s) supprimé(s)");
        }
    }
}
