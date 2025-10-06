<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Client;
use App\Models\Facture;
use App\Models\Contrat;
use App\Models\MandatSepa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientPortalTest extends TestCase
{
    use RefreshDatabase;

    private User $clientUser;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer un client test avec user
        $this->client = Client::factory()->create();
        $this->clientUser = User::factory()->create([
            'client_id' => $this->client->id,
            'email' => $this->client->email
        ]);
        $this->clientUser->assignRole('Client');
    }

    /**
     * Test accès au dashboard client
     */
    public function test_client_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->clientUser)
            ->get(route('client.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('client.dashboard');
        $response->assertViewHas('client');
    }

    /**
     * Test redirection si non authentifié
     */
    public function test_guest_cannot_access_client_portal(): void
    {
        $response = $this->get(route('client.dashboard'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test accès liste factures
     */
    public function test_client_can_view_factures(): void
    {
        Facture::factory()->count(3)->create(['client_id' => $this->client->id]);

        $response = $this->actingAs($this->clientUser)
            ->get(route('client.factures'));

        $response->assertStatus(200);
        $response->assertViewIs('client.factures.index');
        $response->assertViewHas('factures');
    }

    /**
     * Test téléchargement PDF facture
     */
    public function test_client_can_download_facture_pdf(): void
    {
        $facture = Facture::factory()->create(['client_id' => $this->client->id]);

        $response = $this->actingAs($this->clientUser)
            ->get(route('client.factures.pdf', $facture));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     * Test client ne peut pas accéder aux factures d'autres clients
     */
    public function test_client_cannot_access_other_client_factures(): void
    {
        $otherClient = Client::factory()->create();
        $otherFacture = Facture::factory()->create(['client_id' => $otherClient->id]);

        $response = $this->actingAs($this->clientUser)
            ->get(route('client.factures.show', $otherFacture));

        $response->assertStatus(403);
    }

    /**
     * Test accès liste contrats
     */
    public function test_client_can_view_contrats(): void
    {
        Contrat::factory()->count(2)->create(['client_id' => $this->client->id]);

        $response = $this->actingAs($this->clientUser)
            ->get(route('client.contrats'));

        $response->assertStatus(200);
        $response->assertViewIs('client.contrats.index');
        $response->assertViewHas('contrats');
    }

    /**
     * Test téléchargement PDF mandat SEPA
     */
    public function test_client_can_download_mandat_sepa_pdf(): void
    {
        $mandat = MandatSepa::factory()->create(['client_id' => $this->client->id]);

        $response = $this->actingAs($this->clientUser)
            ->get(route('client.sepa.pdf', $mandat));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     * Test mise à jour profil client
     */
    public function test_client_can_update_profile(): void
    {
        $response = $this->actingAs($this->clientUser)
            ->put(route('client.profil.update'), [
                'email' => 'newemail@test.com',
                'telephone' => '0123456789',
                'adresse' => '123 Rue Test',
                'code_postal' => '75001',
                'ville' => 'Paris',
                'pays' => 'France'
            ]);

        $response->assertRedirect(route('client.profil'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('clients', [
            'id' => $this->client->id,
            'email' => 'newemail@test.com'
        ]);
    }

    /**
     * Test affichage page documents
     */
    public function test_client_can_view_documents_page(): void
    {
        $response = $this->actingAs($this->clientUser)
            ->get(route('client.documents'));

        $response->assertStatus(200);
        $response->assertViewIs('client.documents.index');
    }

    /**
     * Test création mandat SEPA
     */
    public function test_client_can_create_sepa_mandat(): void
    {
        $response = $this->actingAs($this->clientUser)
            ->post(route('client.sepa.store'), [
                'titulaire' => 'John Doe',
                'iban' => 'FR7630006000011234567890189',
                'bic' => 'BNPAFRPP',
                'consentement' => true
            ]);

        $response->assertRedirect(route('client.sepa'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('mandats_sepa', [
            'client_id' => $this->client->id,
            'titulaire' => 'John Doe'
        ]);
    }

    /**
     * Test validation IBAN incorrect
     */
    public function test_sepa_mandat_requires_valid_iban(): void
    {
        $response = $this->actingAs($this->clientUser)
            ->post(route('client.sepa.store'), [
                'titulaire' => 'John Doe',
                'iban' => 'INVALID',
                'bic' => 'BNPAFRPP',
                'consentement' => true
            ]);

        $response->assertSessionHasErrors('iban');
    }
}
