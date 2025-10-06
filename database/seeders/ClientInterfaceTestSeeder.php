<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ClientNotification;
use App\Models\ChatMessage;
use Carbon\Carbon;

class ClientInterfaceTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Trouver un client (utilisateur avec rôle client)
        $client = User::whereHas('roles', function($q) {
            $q->where('name', 'client');
        })->first();

        if (!$client) {
            $this->command->info('Aucun client trouvé. Créons-en un...');
            // Créer un client de test si nécessaire
            $client = User::create([
                'name' => 'Client Test',
                'email' => 'client@test.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $client->assignRole('client');
        }

        $this->command->info("Création de données de test pour le client: {$client->name} (ID: {$client->id})");

        // Créer des notifications de test
        $notifications = [
            [
                'type' => 'facture',
                'titre' => 'Nouvelle facture disponible',
                'message' => 'Votre facture de novembre 2025 est maintenant disponible. Montant: 150€',
                'lien' => '/client/factures',
                'lu' => false,
                'created_at' => Carbon::now()->subMinutes(5),
            ],
            [
                'type' => 'paiement',
                'titre' => 'Paiement reçu',
                'message' => 'Votre paiement de 150€ a bien été enregistré. Merci !',
                'lien' => '/client/reglements',
                'lu' => false,
                'created_at' => Carbon::now()->subHours(2),
            ],
            [
                'type' => 'relance',
                'titre' => 'Rappel échéance',
                'message' => 'Votre facture arrive à échéance dans 3 jours. Pensez à effectuer votre paiement.',
                'lien' => '/client/factures',
                'lu' => false,
                'created_at' => Carbon::now()->subHours(5),
            ],
            [
                'type' => 'contrat',
                'titre' => 'Renouvellement de contrat',
                'message' => 'Votre contrat arrive à échéance le mois prochain. Souhaitez-vous le renouveler ?',
                'lien' => '/client/contrats',
                'lu' => true,
                'created_at' => Carbon::now()->subDays(1),
            ],
            [
                'type' => 'document',
                'titre' => 'Nouveau document disponible',
                'message' => 'Un nouveau document a été ajouté à votre espace: Attestation d\'assurance',
                'lien' => '/client/documents',
                'lu' => true,
                'created_at' => Carbon::now()->subDays(2),
            ],
            [
                'type' => 'systeme',
                'titre' => 'Maintenance programmée',
                'message' => 'Une maintenance est prévue ce dimanche de 2h à 4h du matin.',
                'lien' => null,
                'lu' => true,
                'created_at' => Carbon::now()->subDays(3),
            ],
            [
                'type' => 'message',
                'titre' => 'Message du support',
                'message' => 'Bonjour, nous avons bien reçu votre demande et y répondrons dans les plus brefs délais.',
                'lien' => '/client/chat',
                'lu' => false,
                'created_at' => Carbon::now()->subHours(1),
            ],
        ];

        foreach ($notifications as $notification) {
            ClientNotification::create([
                'client_id' => $client->id,
                ...$notification
            ]);
        }

        $this->command->info('✓ 7 notifications créées');

        // Créer des messages de chat de test
        $chatMessages = [
            [
                'from_client' => true,
                'message' => 'Bonjour, j\'ai une question concernant ma facture du mois dernier.',
                'lu' => true,
                'created_at' => Carbon::now()->subHours(3),
            ],
            [
                'from_client' => false,
                'message' => 'Bonjour ! Je vous écoute, quelle est votre question ?',
                'lu' => true,
                'created_at' => Carbon::now()->subHours(3)->addMinutes(5),
            ],
            [
                'from_client' => true,
                'message' => 'Je vois un montant supplémentaire de 10€ que je ne comprends pas.',
                'lu' => true,
                'created_at' => Carbon::now()->subHours(3)->addMinutes(10),
            ],
            [
                'from_client' => false,
                'message' => 'Il s\'agit des frais de service supplémentaire que vous avez demandés le mois dernier.',
                'lu' => true,
                'created_at' => Carbon::now()->subHours(2)->subMinutes(50),
            ],
            [
                'from_client' => true,
                'message' => 'Ah d\'accord, je comprends maintenant. Merci !',
                'lu' => true,
                'created_at' => Carbon::now()->subHours(2)->subMinutes(45),
            ],
            [
                'from_client' => false,
                'message' => 'Je vous en prie ! N\'hésitez pas si vous avez d\'autres questions.',
                'lu' => false,
                'created_at' => Carbon::now()->subHours(2)->subMinutes(40),
            ],
        ];

        foreach ($chatMessages as $message) {
            ChatMessage::create([
                'client_id' => $client->id,
                ...$message
            ]);
        }

        $this->command->info('✓ 6 messages de chat créés');
        $this->command->info('');
        $this->command->info('====================================');
        $this->command->info('Données de test créées avec succès !');
        $this->command->info('====================================');
        $this->command->info("Email: {$client->email}");
        $this->command->info('Password: password');
        $this->command->info('');
    }
}
