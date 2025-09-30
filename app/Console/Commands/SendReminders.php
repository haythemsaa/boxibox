<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Facture;
use App\Models\Relance;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class SendReminders extends Command
{
    protected $signature = 'boxibox:send-reminders
                           {--dry-run : Simulation sans envoi réel}
                           {--force : Force l\'envoi même si déjà relancé}
                           {--client= : ID du client spécifique}';

    protected $description = 'Envoie les relances automatiques pour les factures en retard';

    public function handle()
    {
        try {
            $dryRun = $this->option('dry-run');
            $force = $this->option('force');
            $clientId = $this->option('client');

            $this->info("Traitement des relances automatiques...");

            if ($dryRun) {
                $this->warn("🔍 MODE SIMULATION - Aucune relance ne sera envoyée");
            }

            // Récupérer les factures en retard
            $facturesEnRetard = $this->getFacturesEnRetard($clientId);

            if ($facturesEnRetard->isEmpty()) {
                $this->info("✅ Aucune facture en retard nécessitant une relance.");
                return 0;
            }

            $this->info("📋 {$facturesEnRetard->count()} factures en retard trouvées");

            $results = [
                'premiere_relance' => 0,
                'deuxieme_relance' => 0,
                'mise_en_demeure' => 0,
                'skipped' => 0,
                'errors' => 0
            ];

            $progressBar = $this->output->createProgressBar($facturesEnRetard->count());
            $progressBar->start();

            foreach ($facturesEnRetard as $facture) {
                try {
                    $relanceType = $this->determineRelanceType($facture, $force);

                    if (!$relanceType) {
                        $results['skipped']++;
                    } else {
                        if (!$dryRun) {
                            $this->createAndSendRelance($facture, $relanceType);
                        }
                        $results[$relanceType]++;
                    }

                } catch (\Exception $e) {
                    $this->error("Erreur pour la facture {$facture->numero_facture}: " . $e->getMessage());
                    $results['errors']++;
                }

                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine();

            $this->displayResults($results, $dryRun);

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Erreur lors du traitement : " . $e->getMessage());
            return 1;
        }
    }

    private function getFacturesEnRetard($clientId = null)
    {
        $query = Facture::with(['client', 'relances'])
            ->whereIn('statut', ['emise', 'envoyee', 'en_retard'])
            ->where('date_echeance', '<', Carbon::today())
            ->whereHas('client', function($q) {
                $q->where('is_active', true);
            });

        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        return $query->get();
    }

    private function determineRelanceType(Facture $facture, $force = false)
    {
        $joursRetard = Carbon::today()->diffInDays($facture->date_echeance);
        $derniereRelance = $facture->relances()->orderBy('created_at', 'desc')->first();

        // Vérifier si une relance a déjà été envoyée récemment (sauf si forcé)
        if (!$force && $derniereRelance && $derniereRelance->created_at->isAfter(Carbon::today()->subDays(7))) {
            return null; // Pas de nouvelle relance
        }

        $nombreRelances = $facture->relances()->count();

        // Déterminer le type de relance selon le nombre de jours et relances précédentes
        if ($joursRetard >= 3 && $joursRetard <= 15 && $nombreRelances == 0) {
            return 'premiere_relance';
        }

        if ($joursRetard >= 15 && $joursRetard <= 30 && $nombreRelances <= 1) {
            return 'deuxieme_relance';
        }

        if ($joursRetard > 30 && $nombreRelances <= 2) {
            return 'mise_en_demeure';
        }

        return null;
    }

    private function createAndSendRelance(Facture $facture, $relanceType)
    {
        $niveau = match($relanceType) {
            'premiere_relance' => 1,
            'deuxieme_relance' => 2,
            'mise_en_demeure' => 3,
            default => 1
        };

        $contenu = $this->generateRelanceContent($facture, $relanceType);

        // Créer la relance
        $relance = Relance::create([
            'facture_id' => $facture->id,
            'client_id' => $facture->client_id,
            'type_relance' => $relanceType === 'mise_en_demeure' ? 'mise_en_demeure' : 'amiable',
            'niveau' => $niveau,
            'date_envoi' => Carbon::today(),
            'date_prevue' => Carbon::today(),
            'statut' => 'envoyee',
            'montant_penalite' => $this->calculatePenalite($facture),
            'contenu' => $contenu,
            'mode_envoi' => 'email',
            'adresse_envoi' => $facture->client->email,
            'created_by' => 1, // Système
        ]);

        // Mettre à jour le statut de la facture
        $facture->update(['statut' => 'en_retard']);

        // Envoyer l'email (simulation de l'envoi)
        $this->sendRelanceEmail($facture, $relance);
    }

    private function generateRelanceContent(Facture $facture, $relanceType)
    {
        $joursRetard = Carbon::today()->diffInDays($facture->date_echeance);

        return match($relanceType) {
            'premiere_relance' => "Première relance pour la facture {$facture->numero_facture} d'un montant de {$facture->montant_ttc}€, échue depuis {$joursRetard} jours.",

            'deuxieme_relance' => "Deuxième relance pour la facture {$facture->numero_facture} d'un montant de {$facture->montant_ttc}€. Le paiement n'ayant pas été effectué après notre première relance, nous vous demandons de régulariser votre situation dans les plus brefs délais.",

            'mise_en_demeure' => "Mise en demeure de payer la facture {$facture->numero_facture} d'un montant de {$facture->montant_ttc}€, échue depuis {$joursRetard} jours. À défaut de règlement sous 8 jours, nous engagerons une procédure de recouvrement.",

            default => "Relance pour la facture {$facture->numero_facture}."
        };
    }

    private function calculatePenalite(Facture $facture)
    {
        $tauxPenalite = config('boxibox.invoice.late_fee_rate', 1.5) / 100;
        $joursRetard = Carbon::today()->diffInDays($facture->date_echeance);

        // Pénalités uniquement après 30 jours
        if ($joursRetard <= 30) {
            return 0;
        }

        return round($facture->montant_ttc * $tauxPenalite, 2);
    }

    private function sendRelanceEmail(Facture $facture, Relance $relance)
    {
        // Ici on simule l'envoi d'email
        // Dans une vraie application, on utiliserait Mail::send()

        /*
        Mail::send('emails.relance', [
            'facture' => $facture,
            'relance' => $relance,
            'client' => $facture->client
        ], function($message) use ($facture) {
            $message->to($facture->client->email)
                    ->subject("Relance de paiement - Facture {$facture->numero_facture}");
        });
        */
    }

    private function displayResults($results, $dryRun)
    {
        $this->info("📊 Résumé des relances :");
        $this->line("   1️⃣  Premières relances : {$results['premiere_relance']}");
        $this->line("   2️⃣  Deuxièmes relances : {$results['deuxieme_relance']}");
        $this->line("   ⚖️  Mises en demeure : {$results['mise_en_demeure']}");
        $this->line("   ⏭️  Ignorées : {$results['skipped']}");
        $this->line("   ❌ Erreurs : {$results['errors']}");

        if ($dryRun) {
            $this->warn("🔍 Simulation terminée - Aucune relance envoyée");
        }
    }
}