<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\Contrat;
use App\Models\Facture;
use App\Models\FactureLigne;
use App\Models\Reglement;
use App\Models\Rappel;
use App\Models\ClientDocument;
use App\Models\MandatSepa;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Box;
use App\Models\BoxFamille;
use App\Models\Emplacement;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class CompleteDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Récupérer ou créer le tenant et un utilisateur admin
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
            $this->command->info('✅ Tenant créé: ' . $tenant->nom_entreprise);
        }

        // Récupérer un admin pour created_by
        $adminUser = User::where('type_user', 'admin_tenant')->first();
        if (!$adminUser) {
            $adminUser = User::where('tenant_id', $tenant->id)->first();
        }
        $createdBy = $adminUser ? $adminUser->id : 1;

        // 2. Créer des familles de boxes
        $familles = [];
        $familleData = [
            ['nom' => 'Petit', 'couleur_plan' => '#28a745', 'description' => '1-3 m²', 'surface_min' => 1, 'surface_max' => 3],
            ['nom' => 'Moyen', 'couleur_plan' => '#ffc107', 'description' => '4-7 m²', 'surface_min' => 4, 'surface_max' => 7],
            ['nom' => 'Grand', 'couleur_plan' => '#dc3545', 'description' => '8-15 m²', 'surface_min' => 8, 'surface_max' => 15],
        ];

        foreach ($familleData as $data) {
            $famille = BoxFamille::firstOrCreate(
                ['tenant_id' => $tenant->id, 'nom' => $data['nom']],
                [
                    'couleur_plan' => $data['couleur_plan'],
                    'description' => $data['description'],
                    'surface_min' => $data['surface_min'],
                    'surface_max' => $data['surface_max'],
                    'prix_base' => match($data['nom']) {
                        'Petit' => 50.00,
                        'Moyen' => 100.00,
                        'Grand' => 180.00,
                        default => 100.00
                    },
                ]
            );
            $familles[] = $famille;
        }
        $this->command->info('✅ Familles de boxes créées: ' . count($familles));

        // 3. Créer des emplacements
        $emplacements = [];
        foreach (['Bâtiment A - RDC', 'Bâtiment A - Étage 1', 'Bâtiment B - RDC'] as $nomEmp) {
            $emplacement = Emplacement::firstOrCreate(
                ['tenant_id' => $tenant->id, 'nom' => $nomEmp],
                []
            );
            $emplacements[] = $emplacement;
        }
        $this->command->info('✅ Emplacements créés: ' . count($emplacements));

        // 4. Créer des boxes
        $boxes = [];
        for ($i = 1; $i <= 6; $i++) {
            $famille = $familles[($i - 1) % 3];
            $emplacement = $emplacements[($i - 1) % 3];

            $surface = match($famille->nom) {
                'Petit' => rand(1, 3),
                'Moyen' => rand(4, 7),
                'Grand' => rand(8, 15),
                default => 5
            };

            $box = Box::firstOrCreate(
                ['tenant_id' => $tenant->id, 'numero' => 'B' . str_pad($i, 3, '0', STR_PAD_LEFT)],
                [
                    'famille_id' => $famille->id,
                    'emplacement_id' => $emplacement->id,
                    'surface' => $surface,
                    'volume' => $surface * 2.5,
                    'prix_mensuel' => $famille->prix_base,
                    'statut' => $i <= 3 ? 'occupe' : 'libre',
                ]
            );
            $boxes[] = $box;
        }
        $this->command->info('✅ Boxes créés: ' . count($boxes));

        // 5. Créer 3 clients de démonstration
        $clients = [];
        for ($i = 1; $i <= 3; $i++) {
            $client = Client::updateOrCreate(
                ['email' => "client{$i}@demo.com"],
                [
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
                ]
            );

            // Créer un utilisateur pour le client
            User::updateOrCreate(
                ['email' => $client->email],
                [
                'name' => $client->prenom . ' ' . $client->nom,
                'email' => $client->email,
                'password' => Hash::make('password'),
                'type_user' => 'client_final',
                'client_id' => $client->id,
                'tenant_id' => $tenant->id,
                'is_active' => true,
                ]
            );

            $clients[] = $client;
        }
        $this->command->info('✅ Clients créés: ' . count($clients));

        // 6. Pour chaque client, créer des données complètes
        foreach ($clients as $index => $client) {
            $box = $boxes[$index];

            // CONTRAT ACTIF
            $numeroContrat = 'CTR' . now()->format('Y') . str_pad($client->id * 10 + $box->id, 4, '0', STR_PAD_LEFT);
            $contratActif = Contrat::updateOrCreate(
                ['numero_contrat' => $numeroContrat],
                [
                    'tenant_id' => $tenant->id,
                    'client_id' => $client->id,
                    'box_id' => $box->id,
                    'date_debut' => now()->subMonths(6),
                    'date_fin' => now()->addMonths(6),
                    'duree_type' => 'determine',
                    'prix_mensuel' => $box->prix_mensuel,
                    'caution' => $box->prix_mensuel * 2,
                    'statut' => 'actif',
                ]
            );

            // FACTURES (6 par client : 4 payées, 2 impayées)
            $factures = [];
            for ($f = 1; $f <= 6; $f++) {
                $dateEmission = now()->subMonths(7 - $f);
                $statut = $f <= 4 ? 'payee' : 'en_retard';

                $montantHT = $box->prix_mensuel;
                $tauxTVA = 20;
                $montantTVA = $montantHT * ($tauxTVA / 100);
                $montantTTC = $montantHT + $montantTVA;

                $numeroFacture = 'FAC' . now()->format('Y') . str_pad(($client->id * 10) + $f, 6, '0', STR_PAD_LEFT);
                $facture = Facture::updateOrCreate(
                    ['numero_facture' => $numeroFacture],
                    [
                        'tenant_id' => $tenant->id,
                        'client_id' => $client->id,
                        'contrat_id' => $contratActif->id,
                        'date_emission' => $dateEmission,
                        'date_echeance' => $dateEmission->copy()->addDays(30),
                        'montant_ht' => $montantHT,
                        'montant_tva' => $montantTVA,
                        'taux_tva' => $tauxTVA,
                        'montant_ttc' => $montantTTC,
                        'statut' => $statut,
                    ]
                );

                $factures[] = $facture;

                // Créer la ligne de facture
                FactureLigne::updateOrCreate(
                    [
                        'facture_id' => $facture->id,
                        'designation' => "Location box {$box->numero} - {$dateEmission->format('F Y')}"
                    ],
                    [
                        'quantite' => 1,
                        'prix_unitaire' => $montantHT,
                        'taux_tva' => $tauxTVA,
                    ]
                );

                // RÈGLEMENTS pour factures payées
                if ($statut === 'payee') {
                    Reglement::create([
                        'tenant_id' => $tenant->id,
                        'client_id' => $client->id,
                        'facture_id' => $facture->id,
                        'date_reglement' => $dateEmission->copy()->addDays(rand(5, 25)),
                        'montant' => $montantTTC,
                        'mode_paiement' => ['virement', 'prelevement', 'cb', 'cheque'][rand(0, 3)],
                        'reference' => 'REG' . str_pad($facture->id, 8, '0', STR_PAD_LEFT),
                        'statut' => 'valide',
                        'created_by' => $createdBy,
                    ]);
                }
            }

            $this->command->info("✅ Client {$client->email} : {$contratActif->numero_contrat}, " . count($factures) . " factures");

            // RELANCES pour factures impayées
            $facturesImpayees = array_filter($factures, fn($f) => $f->statut === 'en_retard');
            foreach ($facturesImpayees as $factureImpayee) {
                Rappel::create([
                    'tenant_id' => $tenant->id,
                    'facture_id' => $factureImpayee->id,
                    'client_id' => $client->id,
                    'niveau' => rand(1, 2),
                    'mode_envoi' => ['email', 'courrier'][rand(0, 1)],
                    'date_rappel' => now()->subDays(rand(5, 15)),
                    'statut' => 'envoye',
                    'montant_du' => $factureImpayee->montant_ttc,
                    'notes' => 'Relance automatique pour facture impayée',
                ]);
            }

            // DOCUMENTS (2 par client)
            for ($d = 1; $d <= 2; $d++) {
                ClientDocument::create([
                    'client_id' => $client->id,
                    'nom_fichier' => "contrat_box_{$box->numero}_{$d}.pdf",
                    'nom_original' => "Contrat_Box_{$box->numero}_{$d}.pdf",
                    'type_document' => $d === 1 ? 'contrat' : 'correspondance',
                    'chemin_fichier' => "documents/clients/{$client->id}/demo_{$d}.pdf",
                    'taille' => rand(50000, 500000),
                    'mime_type' => 'application/pdf',
                    'uploaded_by' => $createdBy,
                ]);
            }

            // MANDAT SEPA ACTIF
            MandatSepa::create([
                'client_id' => $client->id,
                'tenant_id' => $tenant->id,
                'contrat_id' => $contratActif->id,
                'rum' => 'BXB' . now()->format('Ymd') . str_pad($client->id, 4, '0', STR_PAD_LEFT),
                'type_mandat' => 'core',
                'statut' => 'valide',
                'date_signature' => now()->subMonths(5),
                'iban' => 'FR76' . str_pad($client->id, 23, '0', STR_PAD_LEFT),
                'bic' => 'BNPAFRPP',
                'titulaire_compte' => $client->prenom . ' ' . $client->nom,
                'adresse_debiteur' => $client->adresse . ', ' . $client->code_postal . ' ' . $client->ville,
                'is_active' => true,
            ]);
        }

        $this->command->info('');
        $this->command->info('🎉 DONNÉES DE DÉMONSTRATION CRÉÉES AVEC SUCCÈS!');
        $this->command->info('');
        $this->command->info('📧 COMPTES CLIENTS CRÉÉS:');
        $this->command->info('   Email: client1@demo.com | Password: password');
        $this->command->info('   Email: client2@demo.com | Password: password');
        $this->command->info('   Email: client3@demo.com | Password: password');
        $this->command->info('');
        $this->command->info('🌐 URL: http://127.0.0.1:8000');
    }
}
