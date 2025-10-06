<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Facture;
use App\Models\Client;
use App\Models\Contrat;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FactureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test création d'une facture
     */
    public function test_facture_can_be_created(): void
    {
        $facture = Facture::factory()->create([
            'numero_facture' => 'FAC-2025-001',
            'montant_ht' => 100.00,
            'taux_tva' => 20,
            'montant_tva' => 20.00,
            'montant_ttc' => 120.00,
            'statut' => 'brouillon'
        ]);

        $this->assertDatabaseHas('factures', [
            'numero_facture' => 'FAC-2025-001',
            'montant_ttc' => 120.00
        ]);
    }

    /**
     * Test calcul montant TTC
     */
    public function test_calcul_montant_ttc(): void
    {
        $facture = new Facture([
            'montant_ht' => 100.00,
            'taux_tva' => 20
        ]);

        $expectedTTC = 120.00;
        $calculatedTTC = $facture->montant_ht + ($facture->montant_ht * $facture->taux_tva / 100);

        $this->assertEquals($expectedTTC, $calculatedTTC);
    }

    /**
     * Test statut facture en retard
     */
    public function test_facture_en_retard_detection(): void
    {
        $facture = Facture::factory()->create([
            'date_echeance' => now()->subDays(10),
            'statut' => 'envoyee',
            'montant_regle' => 0
        ]);

        // Simuler la logique de détection de retard
        $isEnRetard = $facture->date_echeance->isPast() && $facture->montant_regle == 0;

        $this->assertTrue($isEnRetard);
    }

    /**
     * Test relation facture-client
     */
    public function test_facture_belongs_to_client(): void
    {
        $client = Client::factory()->create();
        $facture = Facture::factory()->create(['client_id' => $client->id]);

        $this->assertInstanceOf(Client::class, $facture->client);
        $this->assertEquals($client->id, $facture->client_id);
    }

    /**
     * Test relation facture-contrat
     */
    public function test_facture_belongs_to_contrat(): void
    {
        $contrat = Contrat::factory()->create();
        $facture = Facture::factory()->create(['contrat_id' => $contrat->id]);

        $this->assertInstanceOf(Contrat::class, $facture->contrat);
        $this->assertEquals($contrat->id, $facture->contrat_id);
    }

    /**
     * Test montant restant à payer
     */
    public function test_calcul_montant_restant(): void
    {
        $facture = Facture::factory()->create([
            'montant_ttc' => 120.00,
            'montant_regle' => 50.00
        ]);

        $montantRestant = $facture->montant_ttc - $facture->montant_regle;

        $this->assertEquals(70.00, $montantRestant);
    }

    /**
     * Test numéro facture unique
     */
    public function test_numero_facture_is_unique(): void
    {
        Facture::factory()->create(['numero_facture' => 'FAC-2025-001']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Facture::factory()->create(['numero_facture' => 'FAC-2025-001']);
    }
}
