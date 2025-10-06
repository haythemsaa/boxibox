<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FactureController extends Controller
{
    /**
     * Liste des factures du client
     */
    public function index(Request $request)
    {
        $client = $request->user();

        $factures = $client->factures()
            ->orderBy('date_emission', 'desc')
            ->with('reglements')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => [
                'factures' => $factures->map(function($facture) {
                    return [
                        'id' => $facture->id,
                        'numero' => $facture->numero_facture,
                        'date_emission' => $facture->date_emission->format('d/m/Y'),
                        'date_echeance' => $facture->date_echeance ? $facture->date_echeance->format('d/m/Y') : null,
                        'montant_total' => round($facture->montant_total, 2),
                        'montant_paye' => round($facture->montant_paye, 2),
                        'montant_restant' => round($facture->montant_total - $facture->montant_paye, 2),
                        'statut' => $facture->statut,
                        'statut_label' => $this->getStatutLabel($facture->statut),
                        'jours_retard' => $facture->statut === 'impayee' && $facture->date_echeance ?
                            max(0, now()->diffInDays($facture->date_echeance, false)) : 0,
                        'pdf_url' => route('factures.pdf', $facture->id),
                    ];
                }),
                'pagination' => [
                    'total' => $factures->total(),
                    'current_page' => $factures->currentPage(),
                    'last_page' => $factures->lastPage(),
                    'per_page' => $factures->perPage(),
                ],
            ]
        ], 200);
    }

    /**
     * Détails d'une facture
     */
    public function show(Request $request, $id)
    {
        $client = $request->user();
        $facture = $client->factures()->with(['reglements', 'contrat.box'])->find($id);

        if (!$facture) {
            return response()->json([
                'success' => false,
                'message' => 'Facture non trouvée'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $facture->id,
                'numero' => $facture->numero_facture,
                'date_emission' => $facture->date_emission->format('d/m/Y'),
                'date_echeance' => $facture->date_echeance ? $facture->date_echeance->format('d/m/Y') : null,
                'montant_ht' => round($facture->montant_ht, 2),
                'montant_tva' => round($facture->montant_tva, 2),
                'montant_total' => round($facture->montant_total, 2),
                'montant_paye' => round($facture->montant_paye, 2),
                'montant_restant' => round($facture->montant_total - $facture->montant_paye, 2),
                'statut' => $facture->statut,
                'statut_label' => $this->getStatutLabel($facture->statut),
                'notes' => $facture->notes,
                'contrat' => $facture->contrat ? [
                    'numero' => $facture->contrat->numero_contrat,
                    'box_numero' => $facture->contrat->box->numero ?? null,
                ] : null,
                'reglements' => $facture->reglements->map(function($reglement) {
                    return [
                        'id' => $reglement->id,
                        'date' => $reglement->date_reglement->format('d/m/Y'),
                        'montant' => round($reglement->montant, 2),
                        'mode_paiement' => $reglement->mode_paiement,
                        'reference' => $reglement->reference,
                    ];
                }),
                'pdf_url' => route('factures.pdf', $facture->id),
            ]
        ], 200);
    }

    /**
     * Télécharger le PDF d'une facture
     */
    public function downloadPdf(Request $request, $id)
    {
        $client = $request->user();
        $facture = $client->factures()->find($id);

        if (!$facture) {
            return response()->json([
                'success' => false,
                'message' => 'Facture non trouvée'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'pdf_url' => route('factures.pdf', $facture->id),
                'download_url' => route('factures.download', $facture->id),
            ]
        ], 200);
    }

    /**
     * Libellé du statut
     */
    private function getStatutLabel($statut)
    {
        $labels = [
            'brouillon' => 'Brouillon',
            'emise' => 'Émise',
            'envoyee' => 'Envoyée',
            'partiellement_payee' => 'Partiellement payée',
            'payee' => 'Payée',
            'impayee' => 'Impayée',
            'annulee' => 'Annulée',
        ];

        return $labels[$statut] ?? $statut;
    }
}
