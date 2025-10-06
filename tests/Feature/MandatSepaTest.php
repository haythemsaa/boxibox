<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use App\Models\MandatSepa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class MandatSepaTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $tenantId;
    protected $user;
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantId = 1;

        // Créer un client
        $this->client = Client::create([
            'tenant_id' => $this->tenantId,
            'type_client' => 'particulier',
            'civilite' => 'M',
            'nom' => 'Durand',
            'prenom' => 'Pierre',
            'email' => 'pierre.durand@example.com',
            'telephone' => '0123456789',
            'adresse' => '123 Rue SEPA',
            'code_postal' => '75001',
            'ville' => 'Paris',
            'pays' => 'France',
        ]);

        // Créer un utilisateur
        $this->user = User::factory()->create([
            'name' => 'Pierre Durand',
            'email' => 'pierre.durand@example.com',
            'client_id' => $this->client->id,
        ]);

        $this->user->assignRole('client_final');
    }

    /** @test */
    public function test_un_mandat_sepa_peut_etre_cree()
    {
        $mandat = MandatSepa::create([
            'tenant_id' => $this->tenantId,
            'client_id' => $this->client->id,
            'rum' => 'BXB20250101001',
            'titulaire' => 'Pierre Durand',
            'iban' => 'FR7612345678901234567890123',
            'bic' => 'BNPAFRPPXXX',
            'date_signature' => now(),
            'type_paiement' => 'recurrent',
            'statut' => 'en_attente',
        ]);

        $this->assertDatabaseHas('mandats_sepa', [
            'rum' => 'BXB20250101001',
            'client_id' => $this->client->id,
            'statut' => 'en_attente',
        ]);

        $this->assertEquals('BXB20250101001', $mandat->rum);
        $this->assertEquals('Pierre Durand', $mandat->titulaire);
    }

    /** @test */
    public function test_un_mandat_appartient_a_un_client()
    {
        $mandat = MandatSepa::create([
            'tenant_id' => $this->tenantId,
            'client_id' => $this->client->id,
            'rum' => 'BXB20250102001',
            'titulaire' => 'Pierre Durand',
            'iban' => 'FR7612345678901234567890123',
            'bic' => 'BNPAFRPPXXX',
            'date_signature' => now(),
            'type_paiement' => 'recurrent',
            'statut' => 'valide',
        ]);

        $this->assertInstanceOf(Client::class, $mandat->client);
        $this->assertEquals($this->client->id, $mandat->client->id);
    }

    /** @test */
    public function test_rum_doit_etre_unique()
    {
        MandatSepa::create([
            'tenant_id' => $this->tenantId,
            'client_id' => $this->client->id,
            'rum' => 'RUM-UNIQUE-001',
            'titulaire' => 'Pierre Durand',
            'iban' => 'FR7612345678901234567890123',
            'bic' => 'BNPAFRPPXXX',
            'date_signature' => now(),
            'type_paiement' => 'recurrent',
            'statut' => 'valide',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        // Tenter de créer un autre mandat avec le même RUM
        MandatSepa::create([
            'tenant_id' => $this->tenantId,
            'client_id' => $this->client->id,
            'rum' => 'RUM-UNIQUE-001',
            'titulaire' => 'Pierre Durand',
            'iban' => 'FR7612345678901234567890999',
            'bic' => 'BNPAFRPPXXX',
            'date_signature' => now(),
            'type_paiement' => 'recurrent',
            'statut' => 'valide',
        ]);
    }

    /** @test */
    public function test_un_client_peut_avoir_plusieurs_mandats()
    {
        MandatSepa::create([
            'tenant_id' => $this->tenantId,
            'client_id' => $this->client->id,
            'rum' => 'BXB20250103001',
            'titulaire' => 'Pierre Durand',
            'iban' => 'FR7612345678901234567890111',
            'bic' => 'BNPAFRPPXXX',
            'date_signature' => now()->subMonths(2),
            'type_paiement' => 'recurrent',
            'statut' => 'annule',
        ]);

        MandatSepa::create([
            'tenant_id' => $this->tenantId,
            'client_id' => $this->client->id,
            'rum' => 'BXB20250104001',
            'titulaire' => 'Pierre Durand',
            'iban' => 'FR7612345678901234567890222',
            'bic' => 'BNPAFRPPXXX',
            'date_signature' => now(),
            'type_paiement' => 'recurrent',
            'statut' => 'valide',
        ]);

        $this->assertEquals(2, $this->client->mandatsSepa()->count());
    }

    /** @test */
    public function test_seul_un_mandat_actif_par_client()
    {
        // Premier mandat valide
        $mandat1 = MandatSepa::create([
            'tenant_id' => $this->tenantId,
            'client_id' => $this->client->id,
            'rum' => 'BXB20250105001',
            'titulaire' => 'Pierre Durand',
            'iban' => 'FR7612345678901234567890333',
            'bic' => 'BNPAFRPPXXX',
            'date_signature' => now(),
            'type_paiement' => 'recurrent',
            'statut' => 'valide',
        ]);

        $mandatsValides = $this->client->mandatsSepa()->where('statut', 'valide')->count();
        $this->assertEquals(1, $mandatsValides);

        // Si on veut créer un second mandat valide, il faut d'abord invalider le premier
        $mandat1->update(['statut' => 'annule']);

        MandatSepa::create([
            'tenant_id' => $this->tenantId,
            'client_id' => $this->client->id,
            'rum' => 'BXB20250106001',
            'titulaire' => 'Pierre Durand',
            'iban' => 'FR7612345678901234567890444',
            'bic' => 'BNPAFRPPXXX',
            'date_signature' => now(),
            'type_paiement' => 'recurrent',
            'statut' => 'valide',
        ]);

        $mandatsValides = $this->client->mandatsSepa()->where('statut', 'valide')->count();
        $this->assertEquals(1, $mandatsValides);
    }

    /** @test */
    public function test_client_peut_creer_un_mandat()
    {
        $response = $this->actingAs($this->user)
            ->post(route('client.sepa.store'), [
                'titulaire' => 'Pierre Durand',
                'iban' => 'FR7612345678901234567890555',
                'bic' => 'BNPAFRPPXXX',
                'consentement' => true,
            ]);

        $this->assertDatabaseHas('mandats_sepa', [
            'client_id' => $this->client->id,
            'titulaire' => 'Pierre Durand',
            'iban' => 'FR7612345678901234567890555',
        ]);

        $response->assertRedirect(route('client.sepa'));
    }

    /** @test */
    public function test_iban_doit_avoir_27_caracteres()
    {
        $response = $this->actingAs($this->user)
            ->post(route('client.sepa.store'), [
                'titulaire' => 'Pierre Durand',
                'iban' => 'FR76123', // IBAN trop court
                'bic' => 'BNPAFRPPXXX',
                'consentement' => true,
            ]);

        $response->assertSessionHasErrors('iban');
    }

    /** @test */
    public function test_bic_doit_etre_valide()
    {
        $response = $this->actingAs($this->user)
            ->post(route('client.sepa.store'), [
                'titulaire' => 'Pierre Durand',
                'iban' => 'FR7612345678901234567890666',
                'bic' => 'INVALID', // BIC trop court
                'consentement' => true,
            ]);

        $response->assertSessionHasErrors('bic');
    }

    /** @test */
    public function test_consentement_est_requis()
    {
        $response = $this->actingAs($this->user)
            ->post(route('client.sepa.store'), [
                'titulaire' => 'Pierre Durand',
                'iban' => 'FR7612345678901234567890777',
                'bic' => 'BNPAFRPPXXX',
                'consentement' => false, // Pas de consentement
            ]);

        $response->assertSessionHasErrors('consentement');
    }

    /** @test */
    public function test_client_peut_telecharger_pdf_mandat()
    {
        $mandat = MandatSepa::create([
            'tenant_id' => $this->tenantId,
            'client_id' => $this->client->id,
            'rum' => 'BXB20250107001',
            'titulaire' => 'Pierre Durand',
            'iban' => 'FR7612345678901234567890888',
            'bic' => 'BNPAFRPPXXX',
            'date_signature' => now(),
            'type_paiement' => 'recurrent',
            'statut' => 'valide',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('client.sepa.pdf', $mandat));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /** @test */
    public function test_client_ne_peut_pas_telecharger_mandat_autre_client()
    {
        // Créer un autre client
        $autreClient = Client::create([
            'tenant_id' => $this->tenantId,
            'type_client' => 'particulier',
            'civilite' => 'Mme',
            'nom' => 'Autre',
            'prenom' => 'Client',
            'email' => 'autre@example.com',
            'telephone' => '0987654321',
            'adresse' => '789 Rue Autre',
            'code_postal' => '75002',
            'ville' => 'Paris',
            'pays' => 'France',
        ]);

        $mandatAutreClient = MandatSepa::create([
            'tenant_id' => $this->tenantId,
            'client_id' => $autreClient->id,
            'rum' => 'BXB20250108001',
            'titulaire' => 'Client Autre',
            'iban' => 'FR7612345678901234567890999',
            'bic' => 'BNPAFRPPXXX',
            'date_signature' => now(),
            'type_paiement' => 'recurrent',
            'statut' => 'valide',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('client.sepa.pdf', $mandatAutreClient));

        $response->assertStatus(403);
    }
}
