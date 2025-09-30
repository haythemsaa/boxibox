<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SepaService;
use Carbon\Carbon;

class GenerateSepaFile extends Command
{
    protected $signature = 'boxibox:generate-sepa {--date= : Date de prélèvement (YYYY-MM-DD)} {--factures= : IDs des factures séparés par des virgules}';

    protected $description = 'Génère un fichier SEPA pour les prélèvements automatiques';

    private $sepaService;

    public function __construct(SepaService $sepaService)
    {
        parent::__construct();
        $this->sepaService = $sepaService;
    }

    public function handle()
    {
        try {
            // Récupérer la date de prélèvement
            $dateOption = $this->option('date');
            $datePrelevement = $dateOption ? Carbon::parse($dateOption) : Carbon::tomorrow();

            // Récupérer les IDs de factures si spécifiés
            $facturesOption = $this->option('factures');
            $factureIds = $facturesOption ? explode(',', $facturesOption) : [];

            $this->info("Génération du fichier SEPA pour le {$datePrelevement->format('d/m/Y')}...");

            // Générer le fichier SEPA
            $result = $this->sepaService->generateSepaFile($datePrelevement, $factureIds);

            $this->info("✅ Fichier SEPA généré avec succès :");
            $this->line("   📁 Fichier : {$result['file_name']}");
            $this->line("   💰 Montant total : " . number_format($result['total_amount'], 2) . "€");
            $this->line("   📊 Nombre de prélèvements : {$result['prelevements_count']}");

            // Demander si on doit afficher le détail
            if ($this->confirm('Voulez-vous afficher le détail des prélèvements ?')) {
                $this->displayPrelevementDetails($result);
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Erreur lors de la génération : " . $e->getMessage());
            return 1;
        }
    }

    private function displayPrelevementDetails($result)
    {
        // Récupérer les prélèvements créés pour affichage
        $prelevements = \App\Models\PrelevementSepa::where('fichier_sepa', $result['file_name'])
            ->with(['facture.client', 'mandatSepa'])
            ->get();

        $headers = ['Client', 'Facture', 'Montant', 'IBAN', 'RUM'];
        $rows = [];

        foreach ($prelevements as $prelevement) {
            $rows[] = [
                $prelevement->facture->client->getFullNameAttribute(),
                $prelevement->facture->numero_facture,
                number_format($prelevement->montant, 2) . '€',
                $this->maskIban($prelevement->mandatSepa->iban),
                $prelevement->mandatSepa->rum
            ];
        }

        $this->table($headers, $rows);
    }

    private function maskIban($iban)
    {
        return substr($iban, 0, 4) . str_repeat('*', strlen($iban) - 8) . substr($iban, -4);
    }
}