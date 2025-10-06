<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use App\Models\Box;
use App\Models\AccessCode;
use Laravel\Sanctum\Sanctum;

class AccessApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $client;
    protected $box;
    protected $accessCode;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer un utilisateur pour Sanctum
        $this->user = User::factory()->create([
            'tenant_id' => 1,
        ]);

        // Créer un client
        $this->client = Client::factory()->create([
            'tenant_id' => 1,
            'statut' => 'actif',
        ]);

        // Créer une box
        $this->box = Box::factory()->create([
            'tenant_id' => 1,
            'statut' => 'occupe',
        ]);

        // Créer un code d'accès
        $this->accessCode = AccessCode::create([
            'tenant_id' => 1,
            'client_id' => $this->client->id,
            'box_id' => $this->box->id,
            'type' => 'pin',
            'code_pin' => '123456',
            'statut' => 'actif',
            'date_debut_validite' => now()->subDays(1),
            'date_fin_validite' => now()->addDays(30),
        ]);
    }

    /** @test */
    public function test_api_ping_endpoint_works()
    {
        $response = $this->postJson('/api/v1/test/ping');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'API Boxibox opérationnelle',
            ]);
    }

    /** @test */
    public function test_verify_pin_requires_authentication()
    {
        $response = $this->postJson('/api/v1/access/verify-pin', [
            'pin' => '123456',
            'type_acces' => 'entree',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function test_verify_pin_with_valid_code()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/access/verify-pin', [
            'pin' => '123456',
            'box_id' => $this->box->id,
            'type_acces' => 'entree',
            'terminal_id' => 'TERM-001',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Accès autorisé',
            ])
            ->assertJsonStructure([
                'data' => [
                    'log_id',
                    'client' => ['nom', 'prenom'],
                    'box' => ['numero'],
                ],
            ]);
    }

    /** @test */
    public function test_verify_pin_with_invalid_code()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/access/verify-pin', [
            'pin' => '999999',
            'box_id' => $this->box->id,
            'type_acces' => 'entree',
            'terminal_id' => 'TERM-001',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);
    }

    /** @test */
    public function test_verify_pin_requires_valid_data()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/access/verify-pin', [
            'pin' => '12345', // Trop court
            'type_acces' => 'entree',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['pin']);
    }

    /** @test */
    public function test_verify_qr_requires_authentication()
    {
        $response = $this->postJson('/api/v1/access/verify-qr', [
            'qr_data' => 'QR-123456',
            'type_acces' => 'entree',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function test_get_logs_requires_authentication()
    {
        $response = $this->getJson('/api/v1/access/logs?terminal_id=TERM-001');

        $response->assertStatus(401);
    }

    /** @test */
    public function test_get_logs_with_terminal_id()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/access/logs?terminal_id=TERM-001&limit=10');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    /** @test */
    public function test_heartbeat_requires_authentication()
    {
        $response = $this->postJson('/api/v1/access/heartbeat', [
            'terminal_id' => 'TERM-001',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function test_heartbeat_works_with_authentication()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/access/heartbeat', [
            'terminal_id' => 'TERM-001',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Terminal connecté',
                'terminal_id' => 'TERM-001',
            ]);
    }

    /** @test */
    public function test_rate_limiting_on_verify_pin()
    {
        Sanctum::actingAs($this->user);

        // Faire 6 requêtes rapides (limite = 5)
        for ($i = 0; $i < 6; $i++) {
            $response = $this->postJson('/api/v1/access/verify-pin', [
                'pin' => '999999',
                'type_acces' => 'entree',
            ]);
        }

        // La 6ème devrait être bloquée
        $response->assertStatus(429)
            ->assertJson([
                'success' => false,
            ]);
    }

    /** @test */
    public function test_verify_pin_with_expired_code()
    {
        Sanctum::actingAs($this->user);

        // Créer un code expiré
        $expiredCode = AccessCode::create([
            'tenant_id' => 1,
            'client_id' => $this->client->id,
            'box_id' => $this->box->id,
            'type' => 'pin',
            'code_pin' => '111111',
            'statut' => 'actif',
            'date_debut_validite' => now()->subDays(10),
            'date_fin_validite' => now()->subDays(1), // Expiré
        ]);

        $response = $this->postJson('/api/v1/access/verify-pin', [
            'pin' => '111111',
            'box_id' => $this->box->id,
            'type_acces' => 'entree',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);
    }

    /** @test */
    public function test_verify_pin_with_suspended_code()
    {
        Sanctum::actingAs($this->user);

        // Créer un code suspendu
        $suspendedCode = AccessCode::create([
            'tenant_id' => 1,
            'client_id' => $this->client->id,
            'box_id' => $this->box->id,
            'type' => 'pin',
            'code_pin' => '222222',
            'statut' => 'suspendu',
            'date_debut_validite' => now()->subDays(1),
            'date_fin_validite' => now()->addDays(30),
        ]);

        $response = $this->postJson('/api/v1/access/verify-pin', [
            'pin' => '222222',
            'box_id' => $this->box->id,
            'type_acces' => 'entree',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);
    }
}
