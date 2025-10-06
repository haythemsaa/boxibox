<?php

namespace Tests\Feature\Api;

use App\Models\Client;
use App\Models\Contrat;
use App\Models\Box;
use App\Models\Facture;
use App\Models\Reglement;
use App\Models\ChatMessage;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MobileApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $tenant;
    protected $client;
    protected $box;
    protected $contrat;
    protected $facture;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer un tenant de test
        $this->tenant = Tenant::factory()->create();

        // Créer un client de test
        $this->client = Client::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'statut' => 'actif',
        ]);

        // Créer un box
        $this->box = Box::factory()->create([
            'tenant_id' => $this->tenant->id,
            'statut' => 'occupe',
        ]);

        // Créer un contrat
        $this->contrat = Contrat::factory()->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $this->client->id,
            'box_id' => $this->box->id,
            'statut' => 'actif',
        ]);

        // Créer une facture
        $this->facture = Facture::factory()->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $this->client->id,
            'contrat_id' => $this->contrat->id,
            'statut' => 'impayee',
            'montant_total' => 100.00,
            'montant_paye' => 0.00,
        ]);
    }

    /** @test */
    public function test_client_can_login_with_valid_credentials()
    {
        $response = $this->postJson('/api/mobile/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'device_name' => 'iPhone 13',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'client' => ['id', 'nom', 'prenom', 'email', 'telephone'],
                    'token'
                ]
            ]);
    }

    /** @test */
    public function test_client_cannot_login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/mobile/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
            'device_name' => 'iPhone 13',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Les identifiants sont incorrects.'
            ]);
    }

    /** @test */
    public function test_inactive_client_cannot_login()
    {
        $this->client->update(['statut' => 'inactif']);

        $response = $this->postJson('/api/mobile/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'device_name' => 'iPhone 13',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Votre compte n\'est pas actif.'
            ]);
    }

    /** @test */
    public function test_client_can_get_dashboard_data()
    {
        Sanctum::actingAs($this->client);

        $response = $this->getJson('/api/mobile/v1/dashboard');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => [
                    'statistiques' => [
                        'contrats_actifs',
                        'factures_impayees',
                        'montant_impaye',
                        'codes_acces_actifs'
                    ],
                    'contrats',
                    'factures_impayees',
                    'codes_acces'
                ]
            ]);

        $this->assertEquals(1, $response->json('data.statistiques.contrats_actifs'));
        $this->assertEquals(1, $response->json('data.statistiques.factures_impayees'));
        $this->assertEquals(100.00, $response->json('data.statistiques.montant_impaye'));
    }

    /** @test */
    public function test_client_can_get_factures_list()
    {
        Sanctum::actingAs($this->client);

        $response = $this->getJson('/api/mobile/v1/factures');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => [
                    'factures',
                    'pagination' => ['total', 'current_page', 'last_page']
                ]
            ]);

        $this->assertCount(1, $response->json('data.factures'));
    }

    /** @test */
    public function test_client_can_get_facture_details()
    {
        Sanctum::actingAs($this->client);

        $response = $this->getJson("/api/mobile/v1/factures/{$this->facture->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'numero',
                    'montant_total',
                    'montant_paye',
                    'montant_restant',
                    'statut',
                    'lignes',
                    'reglements'
                ]
            ]);

        $this->assertEquals($this->facture->id, $response->json('data.id'));
        $this->assertEquals(100.00, $response->json('data.montant_total'));
    }

    /** @test */
    public function test_client_cannot_access_other_client_facture()
    {
        Sanctum::actingAs($this->client);

        // Créer une facture pour un autre client
        $otherClient = Client::factory()->create(['tenant_id' => $this->tenant->id]);
        $otherFacture = Facture::factory()->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $otherClient->id,
        ]);

        $response = $this->getJson("/api/mobile/v1/factures/{$otherFacture->id}");

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Facture non trouvée'
            ]);
    }

    /** @test */
    public function test_client_can_get_contrats_list()
    {
        Sanctum::actingAs($this->client);

        $response = $this->getJson('/api/mobile/v1/contrats');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => [
                    'total',
                    'actifs',
                    'contrats'
                ]
            ]);

        $this->assertEquals(1, $response->json('data.total'));
        $this->assertEquals(1, $response->json('data.actifs'));
    }

    /** @test */
    public function test_client_can_get_contrat_details()
    {
        Sanctum::actingAs($this->client);

        $response = $this->getJson("/api/mobile/v1/contrats/{$this->contrat->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'numero',
                    'statut',
                    'montant_loyer',
                    'box' => ['id', 'numero', 'superficie'],
                    'statistiques',
                    'documents'
                ]
            ]);

        $this->assertEquals($this->contrat->id, $response->json('data.id'));
    }

    /** @test */
    public function test_client_can_create_payment_intent()
    {
        Sanctum::actingAs($this->client);

        $response = $this->postJson('/api/mobile/v1/payments/create-intent', [
            'facture_id' => $this->facture->id,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => [
                    'payment_intent_id',
                    'client_secret',
                    'amount',
                    'currency',
                    'facture'
                ]
            ]);

        $this->assertEquals(100.00, $response->json('data.amount'));
        $this->assertEquals('eur', $response->json('data.currency'));
    }

    /** @test */
    public function test_client_cannot_create_payment_intent_for_paid_facture()
    {
        Sanctum::actingAs($this->client);

        $this->facture->update(['statut' => 'payee']);

        $response = $this->postJson('/api/mobile/v1/payments/create-intent', [
            'facture_id' => $this->facture->id,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Cette facture est déjà payée'
            ]);
    }

    /** @test */
    public function test_client_can_confirm_payment()
    {
        Sanctum::actingAs($this->client);

        $response = $this->postJson('/api/mobile/v1/payments/confirm', [
            'payment_intent_id' => 'pi_test_123456',
            'facture_id' => $this->facture->id,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => [
                    'reglement_id',
                    'facture_id',
                    'montant',
                    'date',
                    'reference',
                    'facture_statut'
                ]
            ]);

        // Vérifier que le règlement a été créé
        $this->assertDatabaseHas('reglements', [
            'facture_id' => $this->facture->id,
            'montant' => 100.00,
            'reference' => 'pi_test_123456',
        ]);

        // Vérifier que la facture a été mise à jour
        $this->facture->refresh();
        $this->assertEquals(100.00, $this->facture->montant_paye);
        $this->assertEquals('payee', $this->facture->statut);
    }

    /** @test */
    public function test_client_can_get_payment_history()
    {
        Sanctum::actingAs($this->client);

        // Créer un règlement
        Reglement::factory()->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $this->client->id,
            'facture_id' => $this->facture->id,
            'montant' => 50.00,
        ]);

        $response = $this->getJson('/api/mobile/v1/payments/history');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => [
                    'payments',
                    'pagination'
                ]
            ]);

        $this->assertCount(1, $response->json('data.payments'));
    }

    /** @test */
    public function test_client_can_send_chat_message()
    {
        Sanctum::actingAs($this->client);

        $response = $this->postJson('/api/mobile/v1/chat/send', [
            'message' => 'Bonjour, j\'ai une question sur ma facture',
        ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'message',
                    'sent_by',
                    'created_at'
                ]
            ]);

        $this->assertDatabaseHas('chat_messages', [
            'client_id' => $this->client->id,
            'message' => 'Bonjour, j\'ai une question sur ma facture',
            'sent_by' => 'client',
        ]);
    }

    /** @test */
    public function test_client_can_send_chat_message_with_attachment()
    {
        Sanctum::actingAs($this->client);
        Storage::fake('public');

        $file = UploadedFile::fake()->image('photo.jpg', 1000, 1000)->size(2048);

        $response = $this->postJson('/api/mobile/v1/chat/send', [
            'message' => 'Voici une photo',
            'attachment' => $file,
        ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => [
                    'attachment_url'
                ]
            ]);

        // Vérifier que le fichier a été stocké
        $chatMessage = ChatMessage::latest()->first();
        Storage::disk('public')->assertExists($chatMessage->attachment_path);
    }

    /** @test */
    public function test_client_can_get_chat_messages()
    {
        Sanctum::actingAs($this->client);

        // Créer quelques messages
        ChatMessage::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $this->client->id,
            'sent_by' => 'client',
        ]);

        ChatMessage::factory()->count(2)->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $this->client->id,
            'sent_by' => 'admin',
            'read_at' => null,
        ]);

        $response = $this->getJson('/api/mobile/v1/chat/messages');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => [
                    'messages',
                    'pagination',
                    'unread_count'
                ]
            ]);

        $this->assertCount(5, $response->json('data.messages'));

        // Vérifier que les messages admin ont été marqués comme lus
        $this->assertEquals(0, ChatMessage::where('client_id', $this->client->id)
            ->where('sent_by', 'admin')
            ->whereNull('read_at')
            ->count());
    }

    /** @test */
    public function test_client_can_mark_message_as_read()
    {
        Sanctum::actingAs($this->client);

        $message = ChatMessage::factory()->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $this->client->id,
            'sent_by' => 'admin',
            'read_at' => null,
        ]);

        $response = $this->postJson("/api/mobile/v1/chat/mark-read/{$message->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Message marqué comme lu'
            ]);

        $message->refresh();
        $this->assertNotNull($message->read_at);
    }

    /** @test */
    public function test_client_can_delete_recent_message()
    {
        Sanctum::actingAs($this->client);

        $message = ChatMessage::factory()->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $this->client->id,
            'sent_by' => 'client',
            'created_at' => now()->subMinutes(2),
        ]);

        $response = $this->deleteJson("/api/mobile/v1/chat/messages/{$message->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Message supprimé'
            ]);

        $this->assertSoftDeleted('chat_messages', ['id' => $message->id]);
    }

    /** @test */
    public function test_client_cannot_delete_old_message()
    {
        Sanctum::actingAs($this->client);

        $message = ChatMessage::factory()->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $this->client->id,
            'sent_by' => 'client',
            'created_at' => now()->subMinutes(10),
        ]);

        $response = $this->deleteJson("/api/mobile/v1/chat/messages/{$message->id}");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Vous ne pouvez supprimer que les messages de moins de 5 minutes'
            ]);

        $this->assertDatabaseHas('chat_messages', ['id' => $message->id]);
    }

    /** @test */
    public function test_client_can_logout()
    {
        Sanctum::actingAs($this->client);

        $response = $this->postJson('/api/mobile/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Déconnexion réussie'
            ]);

        // Vérifier que le token a été révoqué
        $this->assertCount(0, $this->client->tokens);
    }

    /** @test */
    public function test_client_can_update_profile()
    {
        Sanctum::actingAs($this->client);

        $response = $this->putJson('/api/mobile/v1/auth/profile', [
            'telephone' => '+33612345678',
            'adresse' => '123 Rue de Test',
            'code_postal' => '75001',
            'ville' => 'Paris',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->client->refresh();
        $this->assertEquals('+33612345678', $this->client->telephone);
        $this->assertEquals('75001', $this->client->code_postal);
    }

    /** @test */
    public function test_client_can_change_password()
    {
        Sanctum::actingAs($this->client);

        $response = $this->postJson('/api/mobile/v1/auth/change-password', [
            'current_password' => 'password123',
            'new_password' => 'newpassword456',
            'new_password_confirmation' => 'newpassword456',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Mot de passe modifié avec succès'
            ]);

        // Vérifier que le nouveau mot de passe fonctionne
        $this->client->refresh();
        $this->assertTrue(Hash::check('newpassword456', $this->client->password));
    }

    /** @test */
    public function test_unauthenticated_client_cannot_access_protected_routes()
    {
        $response = $this->getJson('/api/mobile/v1/dashboard');
        $response->assertStatus(401);

        $response = $this->getJson('/api/mobile/v1/factures');
        $response->assertStatus(401);

        $response = $this->getJson('/api/mobile/v1/contrats');
        $response->assertStatus(401);
    }
}
