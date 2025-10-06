<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Box;
use App\Models\Emplacement;
use App\Models\FamilleBox;
use App\Models\Contrat;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class BoxTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $tenantId;
    protected $emplacement;
    protected $famille;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantId = 1;

        // Créer un emplacement
        $this->emplacement = Emplacement::create([
            'tenant_id' => $this->tenantId,
            'nom' => 'Bâtiment Test',
            'zone' => 'Zone A',
            'capacite_maximale' => 50,
            'statut' => 'actif',
        ]);

        // Créer une famille
        $this->famille = FamilleBox::create([
            'tenant_id' => $this->tenantId,
            'nom' => 'Premium',
            'description' => 'Boxes premium avec climatisation',
            'couleur' => '#ff6b6b',
        ]);
    }

    /** @test */
    public function test_un_box_peut_etre_cree()
    {
        $box = Box::create([
            'tenant_id' => $this->tenantId,
            'numero' => 'TEST-001',
            'emplacement_id' => $this->emplacement->id,
            'famille_id' => $this->famille->id,
            'surface' => 20.0,
            'volume' => 60.0,
            'prix_mensuel' => 250.00,
            'statut' => 'libre',
            'actif' => true,
        ]);

        $this->assertDatabaseHas('boxes', [
            'numero' => 'TEST-001',
            'surface' => 20.0,
            'statut' => 'libre',
        ]);

        $this->assertEquals('TEST-001', $box->numero);
        $this->assertEquals(250.00, $box->prix_mensuel);
    }

    /** @test */
    public function test_un_box_appartient_a_un_emplacement()
    {
        $box = Box::create([
            'tenant_id' => $this->tenantId,
            'numero' => 'TEST-002',
            'emplacement_id' => $this->emplacement->id,
            'famille_id' => $this->famille->id,
            'surface' => 15.0,
            'volume' => 45.0,
            'prix_mensuel' => 200.00,
            'statut' => 'libre',
            'actif' => true,
        ]);

        $this->assertInstanceOf(Emplacement::class, $box->emplacement);
        $this->assertEquals($this->emplacement->id, $box->emplacement->id);
        $this->assertEquals('Bâtiment Test', $box->emplacement->nom);
    }

    /** @test */
    public function test_un_box_appartient_a_une_famille()
    {
        $box = Box::create([
            'tenant_id' => $this->tenantId,
            'numero' => 'TEST-003',
            'emplacement_id' => $this->emplacement->id,
            'famille_id' => $this->famille->id,
            'surface' => 10.0,
            'volume' => 30.0,
            'prix_mensuel' => 150.00,
            'statut' => 'libre',
            'actif' => true,
        ]);

        $this->assertInstanceOf(FamilleBox::class, $box->famille);
        $this->assertEquals($this->famille->id, $box->famille->id);
        $this->assertEquals('Premium', $box->famille->nom);
    }

    /** @test */
    public function test_scope_libre_retourne_boxes_libres()
    {
        Box::create([
            'tenant_id' => $this->tenantId,
            'numero' => 'LIBRE-001',
            'emplacement_id' => $this->emplacement->id,
            'famille_id' => $this->famille->id,
            'surface' => 10.0,
            'volume' => 30.0,
            'prix_mensuel' => 150.00,
            'statut' => 'libre',
            'actif' => true,
        ]);

        Box::create([
            'tenant_id' => $this->tenantId,
            'numero' => 'OCCUPE-001',
            'emplacement_id' => $this->emplacement->id,
            'famille_id' => $this->famille->id,
            'surface' => 10.0,
            'volume' => 30.0,
            'prix_mensuel' => 150.00,
            'statut' => 'occupe',
            'actif' => true,
        ]);

        $boxesLibres = Box::libre()->get();

        $this->assertEquals(1, $boxesLibres->count());
        $this->assertEquals('libre', $boxesLibres->first()->statut);
    }

    /** @test */
    public function test_scope_occupe_retourne_boxes_occupes()
    {
        Box::create([
            'tenant_id' => $this->tenantId,
            'numero' => 'LIBRE-002',
            'emplacement_id' => $this->emplacement->id,
            'famille_id' => $this->famille->id,
            'surface' => 10.0,
            'volume' => 30.0,
            'prix_mensuel' => 150.00,
            'statut' => 'libre',
            'actif' => true,
        ]);

        Box::create([
            'tenant_id' => $this->tenantId,
            'numero' => 'OCCUPE-002',
            'emplacement_id' => $this->emplacement->id,
            'famille_id' => $this->famille->id,
            'surface' => 10.0,
            'volume' => 30.0,
            'prix_mensuel' => 150.00,
            'statut' => 'occupe',
            'actif' => true,
        ]);

        $boxesOccupes = Box::occupe()->get();

        $this->assertEquals(1, $boxesOccupes->count());
        $this->assertEquals('occupe', $boxesOccupes->first()->statut);
    }

    /** @test */
    public function test_scope_active_retourne_boxes_actifs()
    {
        Box::create([
            'tenant_id' => $this->tenantId,
            'numero' => 'ACTIF-001',
            'emplacement_id' => $this->emplacement->id,
            'famille_id' => $this->famille->id,
            'surface' => 10.0,
            'volume' => 30.0,
            'prix_mensuel' => 150.00,
            'statut' => 'libre',
            'actif' => true,
        ]);

        Box::create([
            'tenant_id' => $this->tenantId,
            'numero' => 'INACTIF-001',
            'emplacement_id' => $this->emplacement->id,
            'famille_id' => $this->famille->id,
            'surface' => 10.0,
            'volume' => 30.0,
            'prix_mensuel' => 150.00,
            'statut' => 'libre',
            'actif' => false,
        ]);

        $boxesActifs = Box::active()->get();

        $this->assertEquals(1, $boxesActifs->count());
        $this->assertTrue($boxesActifs->first()->actif);
    }

    /** @test */
    public function test_un_box_peut_avoir_un_contrat_actif()
    {
        $box = Box::create([
            'tenant_id' => $this->tenantId,
            'numero' => 'CONTRAT-001',
            'emplacement_id' => $this->emplacement->id,
            'famille_id' => $this->famille->id,
            'surface' => 10.0,
            'volume' => 30.0,
            'prix_mensuel' => 150.00,
            'statut' => 'occupe',
            'actif' => true,
        ]);

        $client = Client::create([
            'tenant_id' => $this->tenantId,
            'type_client' => 'particulier',
            'civilite' => 'M',
            'nom' => 'Test',
            'prenom' => 'Client',
            'email' => 'test@example.com',
            'telephone' => '0123456789',
            'adresse' => '123 Rue Test',
            'code_postal' => '75001',
            'ville' => 'Paris',
            'pays' => 'France',
        ]);

        Contrat::create([
            'tenant_id' => $this->tenantId,
            'client_id' => $client->id,
            'box_id' => $box->id,
            'numero_contrat' => 'CTR-TEST-001',
            'date_debut' => now(),
            'montant_loyer' => 150.00,
            'statut' => 'actif',
        ]);

        $this->assertInstanceOf(Contrat::class, $box->contratActif);
        $this->assertEquals('actif', $box->contratActif->statut);
    }

    /** @test */
    public function test_numero_box_doit_etre_unique()
    {
        Box::create([
            'tenant_id' => $this->tenantId,
            'numero' => 'UNIQUE-001',
            'emplacement_id' => $this->emplacement->id,
            'famille_id' => $this->famille->id,
            'surface' => 10.0,
            'volume' => 30.0,
            'prix_mensuel' => 150.00,
            'statut' => 'libre',
            'actif' => true,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        // Tenter de créer un box avec le même numéro (devrait échouer)
        Box::create([
            'tenant_id' => $this->tenantId,
            'numero' => 'UNIQUE-001',
            'emplacement_id' => $this->emplacement->id,
            'famille_id' => $this->famille->id,
            'surface' => 12.0,
            'volume' => 36.0,
            'prix_mensuel' => 180.00,
            'statut' => 'libre',
            'actif' => true,
        ]);
    }

    /** @test */
    public function test_calcul_taux_occupation()
    {
        // Créer 10 boxes
        for ($i = 1; $i <= 10; $i++) {
            Box::create([
                'tenant_id' => $this->tenantId,
                'numero' => "BOX-{$i}",
                'emplacement_id' => $this->emplacement->id,
                'famille_id' => $this->famille->id,
                'surface' => 10.0,
                'volume' => 30.0,
                'prix_mensuel' => 150.00,
                'statut' => $i <= 7 ? 'occupe' : 'libre', // 7 occupés, 3 libres
                'actif' => true,
            ]);
        }

        $total = Box::active()->count();
        $occupes = Box::occupe()->count();
        $tauxOccupation = ($occupes / $total) * 100;

        $this->assertEquals(10, $total);
        $this->assertEquals(7, $occupes);
        $this->assertEquals(70, $tauxOccupation);
    }
}
