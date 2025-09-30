<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Contrat;
use App\Models\Facture;
use App\Models\FactureLigne;
use Carbon\Carbon;

class GenerateInvoices extends Command
{
    protected $signature = 'boxibox:generate-invoices
                           {--month= : Mois pour la facturation (YYYY-MM)}
                           {--dry-run : Simulation sans création réelle}
                           {--contracts= : IDs des contrats séparés par des virgules}';

    protected $description = 'Génère les factures mensuelles pour les contrats actifs';

    public function handle()
    {
        try {
            // Récupérer le mois de facturation
            $monthOption = $this->option('month');
            $month = $monthOption ? Carbon::parse($monthOption . '-01') : Carbon::now()->startOfMonth();

            $dryRun = $this->option('dry-run');
            $contractsOption = $this->option('contracts');
            $contractIds = $contractsOption ? explode(',', $contractsOption) : [];

            $this->info("Génération des factures pour {$month->format('F Y')}...");

            if ($dryRun) {
                $this->warn("🔍 MODE SIMULATION - Aucune facture ne sera créée");
            }

            // Récupérer les contrats éligibles
            $contrats = $this->getContratsEligibles($month, $contractIds);

            if ($contrats->isEmpty()) {
                $this->warn("Aucun contrat éligible trouvé pour cette période.");
                return 0;
            }

            $this->info("📋 {$contrats->count()} contrats éligibles trouvés");

            // Barre de progression
            $progressBar = $this->output->createProgressBar($contrats->count());
            $progressBar->start();

            $results = [
                'created' => 0,
                'skipped' => 0,
                'errors' => 0,
                'total_amount' => 0
            ];

            foreach ($contrats as $contrat) {
                try {
                    // Vérifier si la facture existe déjà
                    $factureExistante = Facture::where('contrat_id', $contrat->id)
                        ->where('type_facture', 'loyer')
                        ->whereYear('date_emission', $month->year)
                        ->whereMonth('date_emission', $month->month)
                        ->first();

                    if ($factureExistante) {
                        $results['skipped']++;
                    } else {
                        if (!$dryRun) {
                            $facture = $this->createInvoiceForContract($contrat, $month);
                            $results['total_amount'] += $facture->montant_ttc;
                        }
                        $results['created']++;
                    }

                } catch (\Exception $e) {
                    $this->error("Erreur pour le contrat {$contrat->numero_contrat}: " . $e->getMessage());
                    $results['errors']++;
                }

                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine();

            // Afficher le résumé
            $this->displayResults($results, $dryRun);

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Erreur lors de la génération : " . $e->getMessage());
            return 1;
        }
    }

    private function getContratsEligibles(Carbon $month, array $contractIds = [])
    {
        $query = Contrat::with(['client', 'box', 'services'])
            ->where('statut', 'actif')
            ->where('date_debut', '<=', $month->endOfMonth());

        if (!empty($contractIds)) {
            $query->whereIn('id', $contractIds);
        }

        return $query->get();
    }

    private function createInvoiceForContract(Contrat $contrat, Carbon $month)
    {
        $numeroFacture = $this->generateNumeroFacture();
        $tauxTva = config('boxibox.invoice.tva_rate', 20.00);

        // Calculer les montants
        $montantHT = $contrat->prix_mensuel;
        if ($contrat->assurance_incluse) {
            $montantHT += $contrat->montant_assurance;
        }

        // Ajouter les services
        foreach ($contrat->services()->actif()->get() as $contratService) {
            $montantHT += $contratService->prix_total;
        }

        $montantTva = $montantHT * ($tauxTva / 100);
        $montantTTC = $montantHT + $montantTva;

        // Créer la facture
        $facture = Facture::create([
            'client_id' => $contrat->client_id,
            'contrat_id' => $contrat->id,
            'numero_facture' => $numeroFacture,
            'type_facture' => 'loyer',
            'date_emission' => $month->copy()->day(1),
            'date_echeance' => $month->copy()->day(1)->addDays(config('boxibox.invoice.due_days', 30)),
            'montant_ht' => $montantHT,
            'taux_tva' => $tauxTva,
            'montant_tva' => $montantTva,
            'montant_ttc' => $montantTTC,
            'statut' => 'emise',
        ]);

        // Créer les lignes de facture
        $this->createInvoiceLines($facture, $contrat, $month);

        return $facture;
    }

    private function createInvoiceLines(Facture $facture, Contrat $contrat, Carbon $month)
    {
        // Ligne pour le loyer
        FactureLigne::create([
            'facture_id' => $facture->id,
            'designation' => "Location box {$contrat->box->numero} - {$month->format('F Y')}",
            'quantite' => 1,
            'prix_unitaire' => $contrat->prix_mensuel,
            'taux_tva' => $facture->taux_tva,
        ]);

        // Ligne pour l'assurance si incluse
        if ($contrat->assurance_incluse && $contrat->montant_assurance > 0) {
            FactureLigne::create([
                'facture_id' => $facture->id,
                'designation' => "Assurance - {$month->format('F Y')}",
                'quantite' => 1,
                'prix_unitaire' => $contrat->montant_assurance,
                'taux_tva' => $facture->taux_tva,
            ]);
        }

        // Lignes pour les services
        foreach ($contrat->services()->actif()->get() as $contratService) {
            FactureLigne::create([
                'facture_id' => $facture->id,
                'designation' => "{$contratService->service->nom} - {$month->format('F Y')}",
                'quantite' => $contratService->quantite,
                'prix_unitaire' => $contratService->prix_unitaire,
                'taux_tva' => $facture->taux_tva,
            ]);
        }
    }

    private function generateNumeroFacture()
    {
        $prefix = config('boxibox.invoice.prefix', 'FAC');
        $length = config('boxibox.invoice.number_length', 6);
        $year = date('Y');

        $lastFacture = Facture::where('numero_facture', 'like', "{$prefix}{$year}%")
            ->orderBy('numero_facture', 'desc')
            ->first();

        if ($lastFacture) {
            $lastNumber = (int) substr($lastFacture->numero_facture, -$length);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $year . str_pad($newNumber, $length, '0', STR_PAD_LEFT);
    }

    private function displayResults($results, $dryRun)
    {
        $this->info("📊 Résumé de la génération :");
        $this->line("   ✅ Factures créées : {$results['created']}");
        $this->line("   ⏭️  Factures ignorées : {$results['skipped']}");
        $this->line("   ❌ Erreurs : {$results['errors']}");

        if (!$dryRun && $results['created'] > 0) {
            $this->line("   💰 Montant total : " . number_format($results['total_amount'], 2) . "€");
        }

        if ($dryRun) {
            $this->warn("🔍 Simulation terminée - Aucune facture créée");
        }
    }
}