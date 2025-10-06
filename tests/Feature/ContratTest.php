<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use App\Models\Contrat;
use App\Models\Box;
use App\Models\Emplacement;
use App\Models\FamilleBox;
use App\Models\Facture;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class ContratTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $tenantId;
    protected $user;
    protected $client;
    protected $box;
    protected $emplacement;
    protected $famille;

    protected function setUp(): void
    {
        parent::setUp();

        // Configuration de test
        $this->tenantId = 1;

        // Créer un emplacement
        $this->emplacement = Emplacement::create([
            'tenant_id' => $this->tenantId,
            'nom' => 'Bâtiment A',
            'zone' => 'Zone 1',
            'capacite_maximale' => 100,
            'statut' => 'actif',
        ]);

        // Créer une famille de box
        $this->famille = FamilleBox::create([
            'tenant_id' => $this->tenantId,
            'nom' => 'Standard',
            'description' => 'Boxes standard',
            'couleur' => '#007bff',
        ]);

        // Créer un box
        $this->box = Box::create([
            'tenant_id' => $this->tenantId,
            'numero' => 'BOX001',
            'emplacement_id' => $this->emplacement->id,
            'famille_id' => $this->famille->id,
            'surface' => 10.0,
            'volume' => 30.0,
            'prix_mensuel' => 150.00,
            'statut' => 'libre',
            'actif' => true,
        ]);

        // Créer un client
        $this->client = Client::create([
            'tenant_id' => $this->tenantId,
            'type_client' => 'particulier',
            'civilite' => 'M',
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'email' => 'jean.dupont@example.com',
            'telephone' => '0123456789',
            'adresse' => '123 Rue de Test',
            'code_postal' => '75001',
            'ville' => 'Paris',
            'pays' => 'France',
        ]);

        // Créer un utilisateur lié au client
        $this->user = User::factory()->create([
            'name' => 'Jean Dupont',
            'email' => 'jean.dupont@example.com',
            'client_id' => $this->client->id,
        ]);

        // Assigner le rôle client_final
        $this->user->assignRole('client_final');
    }

    /** @test */
    public function test_un_contrat_peut_etre_cree()
    {
        $contrat = Contrat::create([
            'tenant_id' => $this->tenantId,
            'client_id' => $this->client->id,
            'box_id' => $this->box->id,
            'numero_contrat' => 'CTR-2025-001',
            'date_debut' => now(),
            'montant_loyer' => 150.00,
            'depot_garantie' => 150.00,
            'statut' => 'actif',
        ]);

        $this->assertDatabaseHas('contrats', [
            'numero_contrat' => 'CTR-2025-001',
            'client_id' => $this->client->id,
            'box_id' => $this->box->id,
            'statut' => 'actif',
        ]);

        $this->assertEquals('CTR-2025-001', $contrat->numero_contrat);
        $this->assertEquals(150.00, $contrat->montant_loyer);
    }

    /** @test */
    public function test_un_contrat_appartient_a_un_client()
    {
        $contrat = Contrat::create([
            'tenant_id' => $this->tenantId,
            'client_id' => $this->client->id,
            'box_id' => $this->box->id,
            'numero_contrat' => 'CTR-2025-002',
            'date_debut' => now(),
            'montant_loyer' => 150.00,
            'statut' => 'actif',
        ]);

        $this->assertInstanceOf(Client::class, $contrat->client);
        $this->assertEquals($this->client->id, $contrat->client->id);
    }

    /** @test */
    public function test_un_contrat_appartient_a_un_box()
    {
        $contrat = Contrat::create([
            'tenant_id' => $this->tenantId,
            'client_id' => $this->client->id,
            'box_id' => $this->box->id,
            'numero_contrat' => 'CTR-2025-003',
            'date_debut' => now(),
            'montant_loyer' => 150.00,
            'statut' => 'actif',
        ]);

        $this->assertInstanceOf(Box::class, $contrat->box);
        $this->assertEquals($this->box->id, $contrat->box->id);
    }

    /** @test */
    public function test_un_client_peut_avoir_plusieurs_contrats()
    {
        // Créer un second box
        $box2 = Box::create([
            'tenant_id' => $this->tenantId,
            'numero' => 'BOX002',
            'emplacement_id' => $this->emplacement->id,
            'famille_id' => $this->famille->id,
            'surface' => 15.0,
            'volume' => 45.0,
            'prix_mensuel' => 200.00,
            'statut' => 'libre',
            'actif' => true,
        ]);

        Contrat::create([
            'tenant_id' => $this->tenantId,
            'client_id' => $this->client->id,
            'box_id' => $this->box->id,
            'numero_contrat' => 'CTR-2025-004',
            'date_debut' => now(),
            'montant_loyer' => 150.00,
            'statut' => 'actif',
        ]);

        Contrat::create([
            'tenant_id' => $this->tenantId,
            'client_id' => $this->client->id,
            'box_id' => $box2->id,
            'numero_contrat' => 'CTR-2025-005',
            'date_debut' => now(),
            'montant_loyer' => 200.00,
            'statut' => 'actif',
        ]);

        $this->assertEquals(2, $this->client->contrats()->count());
    }

    /** @test */
    public function test_un_contrat_peut_avoir_des_factures()
    {
        $contrat = Contrat::create([
            'tenant_id' => $this->tenantId,
            'client_id' => $this->client->id,
            'box_id' => $this->box->id,
            'numero_contrat' => 'CTR-2025-006',
            'date_debut' => now(),
            'montant_loyer' => 150.00,
            'statut' => 'actif',
        ]);

        Facture::create([
            'tenant_id' => $this->tenantId,
            'client_id' => $this->client->id,
            'contrat_id' => $contrat->id,
            'numero_facture' => 'FAC-2025-001',
            'date_emission' => now(),
            'date_echeance' => now()->addDays(30),
            'montant_ht' => 150.00,
            'taux_tva' => 20.0,
            'montant_ttc' => 180.00,
            'statut' => 'emise',
        ]);

        $this->assertEquals(1, $contrat->factures()->count());
        $this->assertInstanceOf(Facture::class, $contrat->factures->first());
    }

    /** @test */
    public function test_client_peut_voir_ses_contrats()
    {
        Contrat::create([
            'tenant_id' => $this->tenantId,
            'client_id' => $this->client->id,
            'box_id' => $this->box->id,
            'numero_contrat' => 'CTR-2025-007',
            'date_debut' => now(),
            'montant_loyer' => 150.00,
            'statut' => 'actif',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('client.contrats'));

        $response->assertStatus(200);
    }

    /** @test */
    public function test_client_peut_voir_detail_de_son_contrat()
    {
        $contrat = Contrat::create([
            'tenant_id' => $this->tenantId,
            'client_id' => $this->client->id,
            'box_id' => $this->box->id,
            'numero_contrat' => 'CTR-2025-008',
            'date_debut' => now(),
            'montant_loyer' => 150.00,
            'statut' => 'actif',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('client.contrats.show', $contrat));

        $response->assertStatus(200);
    }

    /** @test */
    public function test_client_ne_peut_pas_voir_contrat_autre_client()
    {
        // Créer un autre client
        $autreClient = Client::create([
            'tenant_id' => $this->tenantId,
            'type_client' => 'particulier',
            'civilite' => 'Mme',
            'nom' => 'Martin',
            'prenom' => 'Marie',
            'email' => 'marie.martin@example.com',
            'telephone' => '0987654321',
            'adresse' => '456 Rue Autre',
            'code_postal' => '75002',
            'ville' => 'Paris',
            'pays' => 'France',
        ]);

        // Créer un contrat pour l'autre client
        $box2 = Box::create([
            'tenant_id' => $this->tenantId,
            'numero' => 'BOX003',
            'emplacement_id' => $this->emplacement->id,
            'famille_id' => $this->famille->id,
            'surface' => 12.0,
            'volume' => 36.0,
            'prix_mensuel' => 180.00,
            'statut' => 'occupe',
            'actif' => true,
        ]);

        $contratAutreClient = Contrat::create([
            'tenant_id' => $this->tenantId,
            'client_id' => $autreClient->id,
            'box_id' => $box2->id,
            'numero_contrat' => 'CTR-2025-009',
            'date_debut' => now(),
            'montant_loyer' => 180.00,
            'statut' => 'actif',
        ]);

        // Le premier client essaie d'accéder au contrat de l'autre client
        $response = $this->actingAs($this->user)
            ->get(route('client.contrats.show', $contratAutreClient));

        $response->assertStatus(403); // Forbidden
    }

    /** @test */
    public function test_le_statut_du_box_change_quand_contrat_actif()
    {
        $this->assertEquals('libre', $this->box->statut);

        $contrat = Contrat::create([
            'tenant_id' => $this->tenantId,
            'client_id' => $this->client->id,
            'box_id' => $this->box->id,
            'numero_contrat' => 'CTR-2025-010',
            'date_debut' => now(),
            'montant_loyer' => 150.00,
            'statut' => 'actif',
        ]);

        // Simuler le changement de statut du box (normalement géré par un observer ou event)
        $this->box->update(['statut' => 'occupe']);

        $this->box->refresh();
        $this->assertEquals('occupe', $this->box->statut);
    }

    /** @test */
    public function test_numero_contrat_doit_etre_unique()
    {
        Contrat::create([
            'tenant_id' => $this->tenantId,
            'client_id' => $this->client->id,
            'box_id' => $this->box->id,
            'numero_contrat' => 'CTR-UNIQUE-001',
            'date_debut' => now(),
            'montant_loyer' => 150.00,
            'statut' => 'actif',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        // Créer un autre box pour éviter le conflit de box_id
        $box2 = Box::create([
            'tenant_id' => $this->tenantId,
            'numero' => 'BOX-UNIQUE',
            'emplacement_id' => $this->emplacement->id,
            'famille_id' => $this->famille->id,
            'surface' => 10.0,
            'volume' => 30.0,
            'prix_mensuel' => 150.00,
            'statut' => 'libre',
            'actif' => true,
        ]);

        // Tenter de créer un contrat avec le même numéro (devrait échouer)
        Contrat::create([
            'tenant_id' => $this->tenantId,
            'client_id' => $this->client->id,
            'box_id' => $box2->id,
            'numero_contrat' => 'CTR-UNIQUE-001',
            'date_debut' => now(),
            'montant_loyer' => 150.00,
            'statut' => 'actif',
        ]);
    }
}
