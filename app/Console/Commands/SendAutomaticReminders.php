<?php

namespace App\Console\Commands;

use App\Models\Facture;
use App\Models\Rappel;
use App\Mail\RappelPaiementMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendAutomaticReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rappels:send-automatic
                            {--dry-run : Simuler sans envoyer réellement}
                            {--niveau= : Envoyer uniquement un niveau spécifique (1, 2 ou 3)}
                            {--force : Forcer l\'envoi même si déjà envoyé aujourd\'hui}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoie automatiquement les rappels de paiement pour les factures impayées';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Démarrage de l\'envoi automatique des rappels...');
        $this->newLine();

        $dryRun = $this->option('dry-run');
        $niveauFilter = $this->option('niveau');
        $force = $this->option('force');

        if ($dryRun) {
            $this->warn('⚠️  MODE SIMULATION - Aucun email ne sera envoyé');
            $this->newLine();
        }

        // Configuration des délais de rappel (en jours après échéance)
        $delais = [
            1 => 7,   // 1er rappel: 7 jours après échéance
            2 => 15,  // 2ème rappel: 15 jours après échéance
            3 => 30,  // Mise en demeure: 30 jours après échéance
        ];

        $totalEnvoyes = 0;
        $totalErreurs = 0;

        // Pour chaque niveau de rappel
        foreach ($delais as $niveau => $joursRetard) {
            // Si un niveau spécifique est demandé, ignorer les autres
            if ($niveauFilter && $niveauFilter != $niveau) {
                continue;
            }

            $this->info("📧 Traitement des rappels de niveau {$niveau} ({$joursRetard}+ jours de retard)...");

            // Récupérer les factures impayées avec le bon délai
            $factures = Facture::where('statut', 'en_retard')
                ->whereDate('date_echeance', '<=', now()->subDays($joursRetard))
                ->with(['client', 'contrat'])
                ->get();

            $this->info("   Trouvé: {$factures->count()} facture(s) éligible(s)");

            $envoyes = 0;
            $deja_envoyes = 0;

            foreach ($factures as $facture) {
                // Vérifier si un rappel de ce niveau n'a pas déjà été envoyé
                $rappelExistant = Rappel::where('facture_id', $facture->id)
                    ->where('niveau', $niveau)
                    ->when(!$force, function ($query) {
                        return $query->whereDate('date_rappel', '>=', now()->subDays(1));
                    })
                    ->first();

                if ($rappelExistant && !$force) {
                    $deja_envoyes++;
                    continue;
                }

                // Créer le rappel
                $rappel = Rappel::create([
                    'tenant_id' => $facture->tenant_id,
                    'facture_id' => $facture->id,
                    'client_id' => $facture->client_id,
                    'niveau' => $niveau,
                    'mode_envoi' => 'email',
                    'date_rappel' => now(),
                    'date_envoi' => $dryRun ? null : now(),
                    'statut' => $dryRun ? 'en_attente' : 'envoye',
                    'montant_du' => $facture->montant_ttc - $facture->montant_regle,
                    'montant' => $facture->montant_ttc - $facture->montant_regle,
                ]);

                // Envoyer l'email (sauf en dry-run)
                if (!$dryRun) {
                    try {
                        Mail::to($facture->client->email)
                            ->send(new RappelPaiementMail($rappel));

                        $this->line("   ✅ Rappel niveau {$niveau} envoyé à {$facture->client->email} (Facture: {$facture->numero_facture})");
                        $envoyes++;
                        $totalEnvoyes++;

                    } catch (\Exception $e) {
                        $this->error("   ❌ Erreur pour {$facture->client->email}: {$e->getMessage()}");
                        Log::error("Erreur envoi rappel", [
                            'facture_id' => $facture->id,
                            'client_email' => $facture->client->email,
                            'error' => $e->getMessage()
                        ]);

                        $rappel->update(['statut' => 'echec']);
                        $totalErreurs++;
                    }
                } else {
                    $this->line("   [SIMULATION] Rappel niveau {$niveau} pour {$facture->client->email} (Facture: {$facture->numero_facture})");
                    $envoyes++;
                    $totalEnvoyes++;
                }
            }

            if ($deja_envoyes > 0) {
                $this->comment("   ⏭️  {$deja_envoyes} rappel(s) déjà envoyé(s) récemment (ignorés)");
            }

            $this->info("   Résultat: {$envoyes} rappel(s) " . ($dryRun ? 'simulé(s)' : 'envoyé(s)'));
            $this->newLine();
        }

        // Résumé final
        $this->newLine();
        $this->info('═══════════════════════════════════════════════════');
        $this->info('📊 RÉSUMÉ');
        $this->info('═══════════════════════════════════════════════════');
        $this->info("✅ Total rappels " . ($dryRun ? 'simulés' : 'envoyés') . ": {$totalEnvoyes}");

        if ($totalErreurs > 0) {
            $this->error("❌ Total erreurs: {$totalErreurs}");
        }

        if ($dryRun) {
            $this->newLine();
            $this->warn('💡 Pour envoyer réellement, relancez sans --dry-run');
        }

        $this->newLine();
        $this->info('✨ Terminé!');

        return 0;
    }
}
