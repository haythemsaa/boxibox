<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FinancialReportExport;
use App\Exports\OccupationReportExport;
use Carbon\Carbon;

class GenerateMonthlyReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:generate-monthly {--month= : Mois au format YYYY-MM} {--email= : Email du destinataire}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Génère et envoie les rapports mensuels (financier + occupation)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $month = $this->option('month') ?? Carbon::now()->subMonth()->format('Y-m');
        $email = $this->option('email');

        $this->info("Génération des rapports pour {$month}...");

        $dateDebut = Carbon::parse($month)->startOfMonth();
        $dateFin = Carbon::parse($month)->endOfMonth();

        // Génération rapport financier
        $this->info("- Rapport financier...");
        $financialFile = "rapport_financier_{$month}.xlsx";
        Excel::store(
            new FinancialReportExport($dateDebut->format('Y-m-d'), $dateFin->format('Y-m-d')),
            $financialFile,
            'local'
        );

        // Génération rapport occupation
        $this->info("- Rapport occupation...");
        $occupationFile = "rapport_occupation_{$month}.xlsx";
        Excel::store(
            new OccupationReportExport($dateDebut->format('Y-m-d'), $dateFin->format('Y-m-d')),
            $occupationFile,
            'local'
        );

        $this->info("✓ Rapports générés avec succès");

        // Envoi par email si spécifié
        if ($email) {
            $this->info("Envoi par email à {$email}...");

            try {
                Mail::raw("Veuillez trouver ci-joint les rapports mensuels de {$month}.", function ($message) use ($email, $month, $financialFile, $occupationFile) {
                    $message->to($email)
                        ->subject("Rapports mensuels Boxibox - {$month}")
                        ->attach(storage_path("app/{$financialFile}"))
                        ->attach(storage_path("app/{$occupationFile}"));
                });

                $this->info("✓ Email envoyé avec succès");
            } catch (\Exception $e) {
                $this->error("Erreur lors de l'envoi de l'email : " . $e->getMessage());
            }
        }

        $this->info("Fichiers disponibles dans storage/app/");
        $this->info("- {$financialFile}");
        $this->info("- {$occupationFile}");

        return Command::SUCCESS;
    }
}
