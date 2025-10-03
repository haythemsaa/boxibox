<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\Contrat;
use App\Models\Facture;
use App\Models\Reglement;
use App\Models\Rappel;
use App\Models\Document;
use App\Models\MandatSepa;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Box;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class ClientPortalDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Récupérer le premier tenant ou en créer un
        $tenant = Tenant::first();
        if (!$tenant) {
            $tenant = Tenant::create([
                'nom_entreprise' => 'Demo Entreprise',
                'slug' => 'demo-entreprise',
                'email' => 'admin@demo-entreprise.com',
                'telephone' => '0123456789',
                'plan' => 'business',
                'statut_abonnement' => 'actif',
                'date_debut_abonnement' => now(),
                'date_fin_abonnement' => now()->addYear(),
            ]);
        }

        // Créer 3 clients de démonstration
        $clients = [];
        for ($i = 1; $i <= 3; $i++) {
            $client = Client::create([
                'tenant_id' => $tenant->id,
                'type_client' => 'particulier',
                'nom' => 'Démo' . $i,
                'prenom' => 'Client',
                'email' => "client{$i}@demo.com",
                'telephone' => '060000000' . $i,
                'telephone_urgence' => '070000000' . $i,
                'adresse' => $i . ' Rue de la Démonstration',
                'code_postal' => '75001',
                'ville' => 'Paris',
                'pays' => 'France',
                'is_active' => true,
            ]);

            // Créer un utilisateur pour le client
            User::create([
                'name' => $client->prenom . ' ' . $client->nom,
                'email' => $client->email,
                'password' => Hash::make('password'),
                'type_user' => 'client_final',
                'client_id' => $client->id,
                'tenant_id' => $tenant->id,
            ]);

            $clients[] = $client;
        }

        // Récupérer des boxes
        $boxes = Box::where('tenant_id', $tenant->id)->take(3)->get();
        if ($boxes->isEmpty()) {
            $this->command->warn('Aucun box trouvé. Les contrats ne seront pas créés.');
            return;
        }

        // Pour chaque client, créer contrats, factures, etc.
        foreach ($clients as $index => $client) {
            $box = $boxes[$index] ?? $boxes->first();

            // 1. CONTRATS (2 par client: 1 actif, 1 terminé)
            $contratActif = Contrat::create([
                'tenant_id' => $tenant->id,
                'client_id' => $client->id,
                'box_id' => $box->id,
                'numero_contrat' => 'CTR' . now()->format('Y') . str_pad($client->id * 10, 4, '0', STR_PAD_LEFT),
                'date_debut' => now()->subMonths(6),
                'date_fin' => now()->addMonths(6),
                'duree_mois' => 12,
                'montant_loyer' => 150.00,
                'depot_garantie' => 300.00,
                'statut' => 'actif',
                'type_contrat' => 'location',
            ]);

            $contratTermine = Contrat::create([
                'tenant_id' => $tenant->id,
                'client_id' => $client->id,
                'box_id' => $box->id,
                'numero_contrat' => 'CTR' . now()->subYear()->format('Y') . str_pad($client->id * 5, 4, '0', STR_PAD_LEFT),
                'date_debut' => now()->subYear(),
                'date_fin' => now()->subMonths(2),
                'duree_mois' => 10,
                'montant_loyer' => 120.00,
                'depot_garantie' => 240.00,
                'statut' => 'termine',
                'type_contrat' => 'location',
            ]);

            // 2. FACTURES (5 par client avec différents statuts)
            $factures = [];
            for ($f = 1; $f <= 5; $f++) {
                $dateEmission = now()->subMonths(6 - $f);
                $statut = match($f) {
                    1, 2, 3 => 'paye',
                    4 => 'impaye',
                    5 => 'impaye',
                };

                $montantHT = 150.00;
                $tauxTVA = 20;
                $montantTVA = $montantHT * ($tauxTVA / 100);
                $montantTTC = $montantHT + $montantTVA;

                $facture = Facture::create([
                    'tenant_id' => $tenant->id,
                    'client_id' => $client->id,
                    'contrat_id' => $contratActif->id,
                    'numero_facture' => 'FAC' . now()->format('Y') . str_pad(($client->id * 10) + $f, 6, '0', STR_PAD_LEFT),
                    'date_emission' => $dateEmission,
                    'date_echeance' => $dateEmission->copy()->addDays(30),
                    'montant_total_ht' => $montantHT,
                    'montant_tva' => $montantTVA,
                    'taux_tva' => $tauxTVA,
                    'montant_total_ttc' => $montantTTC,
                    'statut' => $statut,
                    'montant_regle' => $statut === 'paye' ? $montantTTC : 0,
                ]);

                $factures[] = $facture;

                // 3. RÈGLEMENTS pour factures payées
                if ($statut === 'paye') {
                    Reglement::create([
                        'tenant_id' => $tenant->id,
                        'facture_id' => $facture->id,
                        'date_reglement' => $dateEmission->copy()->addDays(rand(5, 25)),
                        'montant' => $montantTTC,
                        'mode_paiement' => ['virement', 'prelevement', 'carte', 'cheque'][rand(0, 3)],
                        'reference' => 'REG' . str_pad($facture->id, 8, '0', STR_PAD_LEFT),
                        'statut' => 'valide',
                    ]);
                }
            }

            // 4. RELANCES pour factures impayées
            $facturesImpayees = array_filter($factures, fn($f) => $f->statut === 'impaye');
            foreach ($facturesImpayees as $factureImpayee) {
                Rappel::create([
                    'tenant_id' => $tenant->id,
                    'facture_id' => $factureImpayee->id,
                    'client_id' => $client->id,
                    'niveau' => rand(1, 2),
                    'mode_envoi' => ['email', 'courrier'][rand(0, 1)],
                    'date_rappel' => now()->subDays(rand(5, 15)),
                    'statut' => 'envoye',
                    'montant_du' => $factureImpayee->montant_total_ttc,
                    'notes' => 'Relance automatique pour facture impayée',
                ]);
            }

            // 5. DOCUMENTS (3 par client)
            for ($d = 1; $d <= 3; $d++) {
                Document::create([
                    'tenant_id' => $tenant->id,
                    'client_id' => $client->id,
                    'nom' => "Document_Demo_{$d}.pdf",
                    'type_document' => ['contrat', 'facture', 'autre'][rand(0, 2)],
                    'chemin' => "documents/clients/{$client->id}/demo_{$d}.pdf",
                    'taille' => rand(50000, 500000),
                    'uploaded_by' => null,
                ]);
            }

            // 6. MANDAT SEPA (1 actif par client)
            MandatSepa::create([
                'client_id' => $client->id,
                'tenant_id' => $tenant->id,
                'contrat_id' => $contratActif->id,
                'rum' => 'BXB' . now()->format('Ymd') . str_pad($client->id, 4, '0', STR_PAD_LEFT),
                'type_mandat' => 'core',
                'statut' => 'actif',
                'date_signature' => now()->subMonths(5),
                'iban' => 'FR76' . str_pad($client->id, 23, '0', STR_PAD_LEFT),
                'bic' => 'BNPAFRPP',
                'titulaire_compte' => $client->prenom . ' ' . $client->nom,
                'adresse_debiteur' => $client->adresse . ', ' . $client->code_postal . ' ' . $client->ville,
                'is_active' => true,
            ]);
        }

        $this->command->info('✅ Données de démonstration créées avec succès!');
        $this->command->info('📧 Clients créés:');
        foreach ($clients as $index => $client) {
            $this->command->info("   - {$client->email} / password");
        }
    }
}
