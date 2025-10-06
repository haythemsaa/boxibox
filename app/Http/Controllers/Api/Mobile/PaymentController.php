<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Facture;
use App\Models\Reglement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Créer une intention de paiement Stripe
     */
    public function createPaymentIntent(Request $request)
    {
        $client = $request->user();

        $validated = $request->validate([
            'facture_id' => 'required|exists:factures,id',
        ]);

        $facture = $client->factures()->find($validated['facture_id']);

        if (!$facture) {
            return response()->json([
                'success' => false,
                'message' => 'Facture non trouvée'
            ], 404);
        }

        if ($facture->statut === 'payee') {
            return response()->json([
                'success' => false,
                'message' => 'Cette facture est déjà payée'
            ], 400);
        }

        $montantRestant = $facture->montant_total - $facture->montant_paye;

        if ($montantRestant <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun montant à payer'
            ], 400);
        }

        try {
            // Initialiser Stripe (à configurer dans .env)
            // \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

            // Créer le Payment Intent
            // $paymentIntent = \Stripe\PaymentIntent::create([
            //     'amount' => $montantRestant * 100, // Stripe utilise les centimes
            //     'currency' => 'eur',
            //     'metadata' => [
            //         'facture_id' => $facture->id,
            //         'client_id' => $client->id,
            //     ],
            // ]);

            // Pour la démo, on retourne un fake payment intent
            $paymentIntentId = 'pi_demo_' . uniqid();

            return response()->json([
                'success' => true,
                'data' => [
                    'payment_intent_id' => $paymentIntentId,
                    'client_secret' => $paymentIntentId . '_secret_demo',
                    'amount' => round($montantRestant, 2),
                    'currency' => 'eur',
                    'facture' => [
                        'id' => $facture->id,
                        'numero' => $facture->numero_facture,
                        'montant_total' => round($facture->montant_total, 2),
                        'montant_paye' => round($facture->montant_paye, 2),
                        'montant_restant' => round($montantRestant, 2),
                    ],
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du paiement',
                'error' => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Confirmer un paiement
     */
    public function confirmPayment(Request $request)
    {
        $client = $request->user();

        $validated = $request->validate([
            'payment_intent_id' => 'required|string',
            'facture_id' => 'required|exists:factures,id',
        ]);

        $facture = $client->factures()->find($validated['facture_id']);

        if (!$facture) {
            return response()->json([
                'success' => false,
                'message' => 'Facture non trouvée'
            ], 404);
        }

        try {
            DB::beginTransaction();

            $montantRestant = $facture->montant_total - $facture->montant_paye;

            // Créer le règlement
            $reglement = Reglement::create([
                'tenant_id' => $facture->tenant_id,
                'facture_id' => $facture->id,
                'client_id' => $client->id,
                'date_reglement' => now(),
                'montant' => $montantRestant,
                'mode_paiement' => 'carte_bancaire',
                'reference' => $validated['payment_intent_id'],
                'statut' => 'valide',
                'notes' => 'Paiement en ligne via application mobile',
            ]);

            // Mettre à jour la facture
            $facture->montant_paye += $montantRestant;
            if ($facture->montant_paye >= $facture->montant_total) {
                $facture->statut = 'payee';
            } else {
                $facture->statut = 'partiellement_payee';
            }
            $facture->save();

            DB::commit();

            // Envoyer une notification de paiement reçu
            // $client->notify(new \App\Notifications\PaiementRecuNotification($reglement));

            return response()->json([
                'success' => true,
                'message' => 'Paiement effectué avec succès',
                'data' => [
                    'reglement_id' => $reglement->id,
                    'facture_id' => $facture->id,
                    'montant' => round($reglement->montant, 2),
                    'date' => $reglement->date_reglement->format('d/m/Y H:i'),
                    'reference' => $reglement->reference,
                    'facture_statut' => $facture->statut,
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la confirmation du paiement',
                'error' => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Historique des paiements
     */
    public function history(Request $request)
    {
        $client = $request->user();

        $reglements = Reglement::where('client_id', $client->id)
            ->with('facture')
            ->orderBy('date_reglement', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => [
                'payments' => $reglements->map(function($reglement) {
                    return [
                        'id' => $reglement->id,
                        'date' => $reglement->date_reglement->format('d/m/Y'),
                        'date_complete' => $reglement->date_reglement->format('d/m/Y H:i'),
                        'montant' => round($reglement->montant, 2),
                        'mode_paiement' => $reglement->mode_paiement,
                        'mode_paiement_label' => $this->getModePaiementLabel($reglement->mode_paiement),
                        'reference' => $reglement->reference,
                        'statut' => $reglement->statut,
                        'facture' => [
                            'id' => $reglement->facture->id,
                            'numero' => $reglement->facture->numero_facture,
                            'montant_total' => round($reglement->facture->montant_total, 2),
                        ],
                    ];
                }),
                'pagination' => [
                    'total' => $reglements->total(),
                    'current_page' => $reglements->currentPage(),
                    'last_page' => $reglements->lastPage(),
                    'per_page' => $reglements->perPage(),
                ],
            ]
        ], 200);
    }

    /**
     * Méthodes de paiement disponibles
     */
    public function paymentMethods(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'methods' => [
                    [
                        'id' => 'carte_bancaire',
                        'name' => 'Carte Bancaire',
                        'icon' => 'credit-card',
                        'enabled' => true,
                        'description' => 'Paiement sécurisé par carte',
                    ],
                    [
                        'id' => 'apple_pay',
                        'name' => 'Apple Pay',
                        'icon' => 'apple',
                        'enabled' => false, // À activer selon configuration
                        'description' => 'Paiement rapide avec Apple Pay',
                    ],
                    [
                        'id' => 'google_pay',
                        'name' => 'Google Pay',
                        'icon' => 'google',
                        'enabled' => false, // À activer selon configuration
                        'description' => 'Paiement rapide avec Google Pay',
                    ],
                ],
            ]
        ], 200);
    }

    /**
     * Libellé du mode de paiement
     */
    private function getModePaiementLabel($mode)
    {
        $labels = [
            'carte_bancaire' => 'Carte Bancaire',
            'virement' => 'Virement',
            'prelevement' => 'Prélèvement',
            'especes' => 'Espèces',
            'cheque' => 'Chèque',
        ];

        return $labels[$mode] ?? $mode;
    }
}
