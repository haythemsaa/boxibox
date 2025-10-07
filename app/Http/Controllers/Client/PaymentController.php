<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Facture;
use App\Models\Reglement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Afficher la page de paiement pour une facture
     */
    public function show(Facture $facture)
    {
        // Vérifier que la facture appartient au client connecté
        if ($facture->client_id !== Auth::id()) {
            abort(403, 'Accès non autorisé');
        }

        // Vérifier que la facture n'est pas déjà payée
        if ($facture->statut === 'payee') {
            return redirect()->route('client.factures.show', $facture)
                ->with('error', 'Cette facture est déjà payée');
        }

        return Inertia::render('Client/Payment', [
            'facture' => $facture->load('contrat'),
            'stripePublicKey' => config('services.stripe.key'),
        ]);
    }

    /**
     * Créer une session Stripe Checkout
     */
    public function createCheckoutSession(Request $request, Facture $facture)
    {
        // Vérifier que la facture appartient au client connecté
        if ($facture->client_id !== Auth::id()) {
            abort(403, 'Accès non autorisé');
        }

        // Vérifier que la facture n'est pas déjà payée
        if ($facture->statut === 'payee') {
            return response()->json([
                'error' => 'Cette facture est déjà payée'
            ], 400);
        }

        try {
            $client = Auth::user();

            // Créer la session Stripe Checkout
            $session = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => 'Facture ' . $facture->numero_facture,
                            'description' => 'Paiement facture Boxibox',
                        ],
                        'unit_amount' => (int)($facture->montant_ttc * 100), // En centimes
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('client.payment.success', ['facture' => $facture->id]) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('client.payment.cancel', ['facture' => $facture->id]),
                'customer_email' => $client->email,
                'metadata' => [
                    'facture_id' => $facture->id,
                    'client_id' => $client->id,
                    'tenant_id' => $client->tenant_id ?? null,
                ],
            ]);

            return response()->json([
                'sessionId' => $session->id,
                'url' => $session->url,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la création de la session de paiement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Page de succès après paiement
     */
    public function success(Request $request, Facture $facture)
    {
        $sessionId = $request->get('session_id');

        try {
            // Récupérer les détails de la session
            $session = StripeSession::retrieve($sessionId);

            // Vérifier que la session concerne bien cette facture
            if ($session->metadata->facture_id != $facture->id) {
                abort(403, 'Session de paiement invalide');
            }

            return Inertia::render('Client/PaymentSuccess', [
                'facture' => $facture->fresh()->load('contrat'),
                'session' => [
                    'id' => $session->id,
                    'amount' => $session->amount_total / 100,
                    'currency' => strtoupper($session->currency),
                    'status' => $session->payment_status,
                ],
            ]);
        } catch (\Exception $e) {
            return redirect()->route('client.factures.show', $facture)
                ->with('error', 'Impossible de récupérer les informations de paiement');
        }
    }

    /**
     * Page d'annulation de paiement
     */
    public function cancel(Facture $facture)
    {
        return Inertia::render('Client/PaymentCancel', [
            'facture' => $facture->load('contrat'),
        ]);
    }

    /**
     * Webhook Stripe pour confirmer les paiements
     */
    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (\UnexpectedValueException $e) {
            // Payload invalide
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (SignatureVerificationException $e) {
            // Signature invalide
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Gérer les événements Stripe
        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;
                $this->handleSuccessfulPayment($session);
                break;

            case 'payment_intent.payment_failed':
                $paymentIntent = $event->data->object;
                $this->handleFailedPayment($paymentIntent);
                break;

            default:
                \Log::info('Stripe webhook event reçu: ' . $event->type);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Traiter un paiement réussi
     */
    protected function handleSuccessfulPayment($session)
    {
        $factureId = $session->metadata->facture_id;
        $facture = Facture::find($factureId);

        if (!$facture) {
            \Log::error('Facture non trouvée pour le paiement Stripe: ' . $factureId);
            return;
        }

        // Vérifier si le paiement n'a pas déjà été traité
        if ($facture->statut === 'payee') {
            \Log::info('Facture déjà payée: ' . $factureId);
            return;
        }

        // Créer le règlement
        $reglement = Reglement::create([
            'facture_id' => $facture->id,
            'client_id' => $facture->client_id,
            'montant' => $facture->montant_ttc,
            'mode_paiement' => 'stripe',
            'date_reglement' => now(),
            'reference' => $session->payment_intent ?? $session->id,
            'statut' => 'valide',
            'notes' => 'Paiement en ligne via Stripe',
            'created_by' => $facture->client_id,
        ]);

        // Mettre à jour la facture
        $facture->update([
            'statut' => 'payee',
            'date_paiement' => now(),
            'mode_paiement' => 'stripe',
            'reference_paiement' => $session->payment_intent ?? $session->id,
        ]);

        \Log::info('Paiement Stripe traité avec succès', [
            'facture_id' => $facture->id,
            'reglement_id' => $reglement->id,
            'montant' => $facture->montant_ttc,
        ]);

        // TODO: Envoyer une notification au client
        // TODO: Envoyer une notification à l'admin
    }

    /**
     * Traiter un paiement échoué
     */
    protected function handleFailedPayment($paymentIntent)
    {
        \Log::warning('Paiement Stripe échoué', [
            'payment_intent_id' => $paymentIntent->id,
            'error' => $paymentIntent->last_payment_error->message ?? 'Erreur inconnue',
        ]);

        // TODO: Notifier le client de l'échec
    }
}
