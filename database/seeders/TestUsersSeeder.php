<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Contrat;
use App\Models\Facture;
use App\Models\FactureLigne;
use App\Models\Reglement;
use App\Models\Rappel;
use App\Models\ClientDocument;
use App\Models\MandatSepa;
use App\Models\Box;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();
        if (!$tenant) {
            $this->command->error('Aucun tenant trouvé. Exécutez d\'abord CompleteDemoSeeder.');
            return;
        }

        // Récupérer un admin pour created_by
        $adminUser = User::where('type_user', 'admin_tenant')->first();
        if (!$adminUser) {
            $adminUser = User::where('tenant_id', $tenant->id)->first();
        }
        $createdBy = $adminUser ? $adminUser->id : 1;

        // Récupérer des boxes disponibles
        $boxes = Box::where('tenant_id', $tenant->id)->where('statut', 'libre')->take(5)->get();

        if ($boxes->count() < 5) {
            $this->command->warn('Pas assez de boxes disponibles. Création de boxes supplémentaires...');
            // Créer des boxes supplémentaires si nécessaire
            for ($i = $boxes->count(); $i < 5; $i++) {
                $boxes->push(Box::create([
                    'tenant_id' => $tenant->id,
                    'numero' => 'TEST' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                    'famille_id' => Box::first()->famille_id,
                    'emplacement_id' => Box::first()->emplacement_id,
                    'surface' => 5,
                    'volume' => 12.5,
                    'prix_mensuel' => 75.00,
                    'statut' => 'occupe',
                ]));
            }
        }

        $scenarios = [
            [
                'nom' => 'Testeur',
                'prenom' => 'Premium',
                'email' => 'test.premium@boxibox.com',
                'scenario' => 'Client premium avec tout en règle',
                'description' => 'Contrat actif, toutes factures payées, mandat SEPA valide, documents complets',
                'factures_payees' => 12,
                'factures_impayees' => 0,
                'has_sepa' => true,
                'documents_count' => 5,
            ],
            [
                'nom' => 'Testeur',
                'prenom' => 'Retardataire',
                'email' => 'test.retard@boxibox.com',
                'scenario' => 'Client avec retards de paiement',
                'description' => 'Contrat actif, plusieurs factures impayées, relances envoyées, pas de SEPA',
                'factures_payees' => 3,
                'factures_impayees' => 5,
                'has_sepa' => false,
                'documents_count' => 2,
            ],
            [
                'nom' => 'Testeur',
                'prenom' => 'Nouveau',
                'email' => 'test.nouveau@boxibox.com',
                'scenario' => 'Nouveau client récent',
                'description' => 'Contrat signé il y a 1 mois, 1 facture payée, en cours de setup SEPA',
                'factures_payees' => 1,
                'factures_impayees' => 0,
                'has_sepa' => false,
                'documents_count' => 3,
            ],
            [
                'nom' => 'Testeur',
                'prenom' => 'Mixte',
                'email' => 'test.mixte@boxibox.com',
                'scenario' => 'Client avec situation mixte',
                'description' => 'Contrat actif, mélange de factures payées/impayées, SEPA actif mais relances',
                'factures_payees' => 6,
                'factures_impayees' => 2,
                'has_sepa' => true,
                'documents_count' => 4,
            ],
            [
                'nom' => 'Testeur',
                'prenom' => 'Complet',
                'email' => 'test.complet@boxibox.com',
                'scenario' => 'Client pour test complet',
                'description' => 'Tous types de statuts, historique riche pour tester toutes les vues',
                'factures_payees' => 8,
                'factures_impayees' => 3,
                'has_sepa' => true,
                'documents_count' => 6,
            ],
        ];

        foreach ($scenarios as $index => $scenario) {
            // Créer le client
            $client = Client::create([
                'tenant_id' => $tenant->id,
                'type_client' => 'particulier',
                'nom' => $scenario['nom'],
                'prenom' => $scenario['prenom'],
                'email' => $scenario['email'],
                'telephone' => '0612345' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'telephone_urgence' => '0712345' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'adresse' => ($index + 1) . ' Avenue des Tests',
                'code_postal' => '75' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'ville' => 'Paris',
                'pays' => 'France',
                'is_active' => true,
            ]);

            // Créer l'utilisateur
            $user = User::create([
                'name' => $scenario['prenom'] . ' ' . $scenario['nom'],
                'email' => $scenario['email'],
                'password' => Hash::make('test123'),
                'type_user' => 'client_final',
                'client_id' => $client->id,
                'tenant_id' => $tenant->id,
                'is_active' => true,
            ]);

            // Assigner le rôle Client
            $user->assignRole('Client');

            $box = $boxes[$index];
            $box->update(['statut' => 'occupe']);

            // Créer le contrat
            $dateDebut = match($index) {
                2 => now()->subMonth(), // Nouveau client
                default => now()->subMonths(rand(6, 12))
            };

            $contrat = Contrat::create([
                'tenant_id' => $tenant->id,
                'client_id' => $client->id,
                'box_id' => $box->id,
                'numero_contrat' => 'TEST' . now()->format('Y') . str_pad($client->id, 4, '0', STR_PAD_LEFT),
                'date_debut' => $dateDebut,
                'date_fin' => $dateDebut->copy()->addYear(),
                'duree_type' => 'determine',
                'prix_mensuel' => $box->prix_mensuel,
                'caution' => $box->prix_mensuel * 2,
                'statut' => 'actif',
            ]);

            // Créer les factures payées
            for ($f = 1; $f <= $scenario['factures_payees']; $f++) {
                $dateEmission = now()->subMonths($scenario['factures_payees'] - $f + 1);
                $montantHT = $box->prix_mensuel;
                $montantTVA = $montantHT * 0.20;
                $montantTTC = $montantHT + $montantTVA;

                $facture = Facture::create([
                    'tenant_id' => $tenant->id,
                    'client_id' => $client->id,
                    'contrat_id' => $contrat->id,
                    'numero_facture' => 'FTEST' . now()->format('Y') . str_pad(($client->id * 100) + $f, 6, '0', STR_PAD_LEFT),
                    'date_emission' => $dateEmission,
                    'date_echeance' => $dateEmission->copy()->addDays(30),
                    'montant_ht' => $montantHT,
                    'montant_tva' => $montantTVA,
                    'taux_tva' => 20,
                    'montant_ttc' => $montantTTC,
                    'statut' => 'payee',
                ]);

                // Créer le règlement
                // Créer la ligne de facture
                FactureLigne::create([
                    'facture_id' => $facture->id,
                    'designation' => "Location box {$box->numero} - {$dateEmission->format('F Y')}",
                    'quantite' => 1,
                    'prix_unitaire' => $montantHT,
                    'taux_tva' => 20,
                ]);

                Reglement::create([
                    'tenant_id' => $tenant->id,
                    'client_id' => $client->id,
                    'facture_id' => $facture->id,
                    'date_reglement' => $dateEmission->copy()->addDays(rand(5, 25)),
                    'montant' => $montantTTC,
                    'mode_paiement' => ['virement', 'prelevement', 'cb', 'cheque'][rand(0, 3)],
                    'reference' => 'RTEST' . str_pad($facture->id, 8, '0', STR_PAD_LEFT),
                    'statut' => 'valide',
                    'created_by' => $createdBy,
                ]);
            }

            // Créer les factures impayées
            for ($f = 1; $f <= $scenario['factures_impayees']; $f++) {
                $dateEmission = now()->subMonths($f);
                $montantHT = $box->prix_mensuel;
                $montantTVA = $montantHT * 0.20;
                $montantTTC = $montantHT + $montantTVA;

                $facture = Facture::create([
                    'tenant_id' => $tenant->id,
                    'client_id' => $client->id,
                    'contrat_id' => $contrat->id,
                    'numero_facture' => 'FTEST' . now()->format('Y') . str_pad(($client->id * 100) + $scenario['factures_payees'] + $f, 6, '0', STR_PAD_LEFT),
                    'date_emission' => $dateEmission,
                    'date_echeance' => $dateEmission->copy()->addDays(30),
                    'montant_ht' => $montantHT,
                    'montant_tva' => $montantTVA,
                    'taux_tva' => 20,
                    'montant_ttc' => $montantTTC,
                    'statut' => 'en_retard',
                ]);

                // Créer la ligne de facture
                FactureLigne::create([
                    'facture_id' => $facture->id,
                    'designation' => "Location box {$box->numero} - {$dateEmission->format('F Y')}",
                    'quantite' => 1,
                    'prix_unitaire' => $montantHT,
                    'taux_tva' => 20,
                ]);

                // Créer les relances
                if ($scenario['factures_impayees'] > 0 && $index !== 2) {
                    $niveauRelance = $f <= 1 ? 1 : ($f <= 3 ? 2 : 3);
                    Rappel::create([
                        'tenant_id' => $tenant->id,
                        'facture_id' => $facture->id,
                        'client_id' => $client->id,
                        'niveau' => $niveauRelance,
                        'mode_envoi' => ['email', 'courrier'][rand(0, 1)],
                        'date_rappel' => now()->subDays(rand(5, 20)),
                        'statut' => 'envoye',
                        'montant_du' => $facture->montant_ttc,
                        'notes' => "Relance niveau $niveauRelance pour facture {$facture->numero_facture}",
                    ]);
                }
            }

            // Créer les documents
            for ($d = 1; $d <= $scenario['documents_count']; $d++) {
                $types = ['contrat', 'piece_identite', 'justificatif_domicile', 'rib', 'correspondance'];
                ClientDocument::create([
                    'client_id' => $client->id,
                    'nom_fichier' => "document_test_{$d}.pdf",
                    'nom_original' => "Document Test {$d}.pdf",
                    'type_document' => $types[($d - 1) % count($types)],
                    'chemin_fichier' => "documents/clients/{$client->id}/test_{$d}.pdf",
                    'taille' => rand(50000, 500000),
                    'mime_type' => 'application/pdf',
                    'uploaded_by' => $createdBy,
                ]);
            }

            // Créer le mandat SEPA si nécessaire
            if ($scenario['has_sepa']) {
                MandatSepa::create([
                    'client_id' => $client->id,
                    'tenant_id' => $tenant->id,
                    'contrat_id' => $contrat->id,
                    'rum' => 'TEST' . now()->format('Ymd') . str_pad($client->id, 4, '0', STR_PAD_LEFT),
                    'type_mandat' => 'core',
                    'statut' => 'valide',
                    'date_signature' => now()->subMonths(rand(1, 6)),
                    'iban' => 'FR76TEST' . str_pad($client->id, 19, '0', STR_PAD_LEFT),
                    'bic' => 'TESTFRPP',
                    'titulaire_compte' => $client->prenom . ' ' . $client->nom,
                    'adresse_debiteur' => $client->adresse . ', ' . $client->code_postal . ' ' . $client->ville,
                    'is_active' => true,
                ]);
            }

            $this->command->info("✅ Testeur créé: {$scenario['email']} - {$scenario['scenario']}");
        }

        $this->command->info('');
        $this->command->info('🎉 5 COMPTES TESTEURS CRÉÉS AVEC SUCCÈS!');
        $this->command->info('');
        $this->command->info('📧 COMPTES TESTEURS (mot de passe: test123):');
        foreach ($scenarios as $scenario) {
            $this->command->info("   {$scenario['email']} - {$scenario['scenario']}");
        }
        $this->command->info('');
        $this->command->info('🌐 URL: http://127.0.0.1:8000');
    }
}
