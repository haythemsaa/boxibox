<?php

namespace App\Http\Controllers;

use App\Models\Box;
use App\Models\BoxFamille;
use App\Models\Client;
use App\Models\Contrat;
use App\Models\Facture;
use App\Models\Emplacement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class PublicBookingController extends Controller
{
    /**
     * Page d'accueil publique - Catalogue des boxes
     */
    public function index()
    {
        // Récupérer toutes les familles de boxes avec comptage disponibilités
        $familles = BoxFamille::withCount([
            'boxes as disponibles_count' => function($query) {
                $query->where('statut', 'libre')->where('actif', true);
            }
        ])
        ->having('disponibles_count', '>', 0)
        ->orderBy('surface')
        ->get();

        // Statistiques globales
        $stats = [
            'total_boxes' => Box::active()->count(),
            'boxes_disponibles' => Box::active()->libre()->count(),
            'taux_occupation' => Box::active()->count() > 0
                ? (Box::active()->occupe()->count() / Box::active()->count()) * 100
                : 0,
        ];

        return view('public.booking.index', compact('familles', 'stats'));
    }

    /**
     * Afficher les détails d'une famille de boxes
     */
    public function showFamille(BoxFamille $famille)
    {
        // Boxes disponibles de cette famille
        $boxesDisponibles = Box::where('box_famille_id', $famille->id)
            ->where('statut', 'libre')
            ->where('actif', true)
            ->with('emplacement')
            ->get();

        return view('public.booking.famille', compact('famille', 'boxesDisponibles'));
    }

    /**
     * Formulaire de réservation pour un box spécifique
     */
    public function bookingForm(Box $box)
    {
        // Vérifier que le box est disponible
        if ($box->statut !== 'libre' || !$box->actif) {
            return redirect()->route('public.booking.index')
                ->with('error', 'Ce box n\'est plus disponible.');
        }

        $famille = $box->famille;

        // Calcul du tarif (prix de base de la famille)
        $tarifMensuel = $famille->prix_base ?? 0;

        return view('public.booking.form', compact('box', 'famille', 'tarifMensuel'));
    }

    /**
     * Traiter la réservation
     */
    public function processBooking(Request $request, Box $box)
    {
        // Validation
        $validated = $request->validate([
            // Informations client
            'civilite' => 'required|in:M,Mme,Autre',
            'prenom' => 'required|string|max:100',
            'nom' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'telephone' => 'required|string|max:20',
            'adresse' => 'required|string|max:255',
            'code_postal' => 'required|string|max:10',
            'ville' => 'required|string|max:100',
            'pays' => 'required|string|max:100',

            // Contrat
            'date_debut' => 'required|date|after_or_equal:today',
            'duree_mois' => 'required|integer|min:1|max:24',

            // Paiement
            'mode_paiement' => 'required|in:carte,virement,sepa',

            // CGV
            'accepte_cgv' => 'required|accepted',
        ]);

        // Vérifier à nouveau la disponibilité
        if ($box->statut !== 'libre' || !$box->actif) {
            return back()->with('error', 'Ce box n\'est plus disponible.');
        }

        DB::beginTransaction();

        try {
            // 1. Créer ou récupérer le client
            $client = Client::firstOrCreate(
                ['email' => $validated['email']],
                [
                    'civilite' => $validated['civilite'],
                    'prenom' => $validated['prenom'],
                    'nom' => $validated['nom'],
                    'telephone' => $validated['telephone'],
                    'adresse' => $validated['adresse'],
                    'code_postal' => $validated['code_postal'],
                    'ville' => $validated['ville'],
                    'pays' => $validated['pays'],
                    'type_client' => 'particulier',
                    'statut' => 'actif',
                    'password' => Hash::make(uniqid()), // Mot de passe temporaire
                ]
            );

            // 2. Créer le contrat
            $dateDebut = Carbon::parse($validated['date_debut']);
            $dateFin = $dateDebut->copy()->addMonths($validated['duree_mois']);

            $contrat = Contrat::create([
                'numero_contrat' => 'CTR-' . date('Y') . '-' . str_pad(Contrat::count() + 1, 6, '0', STR_PAD_LEFT),
                'client_id' => $client->id,
                'box_id' => $box->id,
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
                'date_signature' => now(),
                'prix_mensuel' => $box->famille->prix_base ?? 0,
                'statut' => 'en_attente', // En attente de paiement
                'duree_mois' => $validated['duree_mois'],
                'mode_paiement' => $validated['mode_paiement'],
            ]);

            // 3. Réserver le box
            $box->update([
                'statut' => 'reserve',
                'date_reservation' => now(),
            ]);

            // 4. Créer la première facture
            $montantHT = $contrat->prix_mensuel;
            $tauxTVA = 20; // À adapter selon configuration
            $montantTVA = $montantHT * ($tauxTVA / 100);
            $montantTTC = $montantHT + $montantTVA;

            $facture = Facture::create([
                'numero_facture' => 'FAC-' . date('Y') . '-' . str_pad(Facture::count() + 1, 6, '0', STR_PAD_LEFT),
                'client_id' => $client->id,
                'contrat_id' => $contrat->id,
                'date_emission' => now(),
                'date_echeance' => now()->addDays(30),
                'montant_ht' => $montantHT,
                'taux_tva' => $tauxTVA,
                'montant_tva' => $montantTVA,
                'montant_ttc' => $montantTTC,
                'statut' => 'en_attente',
                'type' => 'location',
            ]);

            DB::commit();

            // Rediriger vers la page de paiement
            if ($validated['mode_paiement'] === 'carte') {
                return redirect()->route('public.booking.payment', ['contrat' => $contrat->id])
                    ->with('success', 'Votre réservation a été enregistrée. Veuillez procéder au paiement.');
            } else {
                return redirect()->route('public.booking.confirmation', ['contrat' => $contrat->id])
                    ->with('success', 'Votre réservation a été enregistrée. Vous recevrez un email de confirmation.');
            }

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la réservation : ' . $e->getMessage());
        }
    }

    /**
     * Page de paiement Stripe
     */
    public function payment(Contrat $contrat)
    {
        // Vérifier que le contrat est en attente de paiement
        if ($contrat->statut !== 'en_attente') {
            return redirect()->route('public.booking.index')
                ->with('error', 'Ce contrat n\'est plus en attente de paiement.');
        }

        $facture = $contrat->factures()->where('statut', 'en_attente')->first();

        if (!$facture) {
            return redirect()->route('public.booking.index')
                ->with('error', 'Aucune facture en attente trouvée.');
        }

        // TODO: Intégrer Stripe Payment Intent
        // Pour l'instant, page de paiement simulée

        return view('public.booking.payment', compact('contrat', 'facture'));
    }

    /**
     * Page de confirmation après paiement réussi
     */
    public function confirmation(Contrat $contrat)
    {
        return view('public.booking.confirmation', compact('contrat'));
    }

    /**
     * API - Calculer le tarif selon les paramètres
     */
    public function calculatePrice(Request $request)
    {
        $boxId = $request->input('box_id');
        $dureeMois = $request->input('duree_mois', 1);

        $box = Box::findOrFail($boxId);
        $tarifBase = $box->famille->prix_base ?? 0;

        // Calcul avec réduction selon durée
        $reduction = 0;
        if ($dureeMois >= 12) {
            $reduction = 15; // 15% de réduction pour 12 mois+
        } elseif ($dureeMois >= 6) {
            $reduction = 10; // 10% pour 6-11 mois
        } elseif ($dureeMois >= 3) {
            $reduction = 5; // 5% pour 3-5 mois
        }

        $tarifMensuel = $tarifBase * (1 - $reduction / 100);
        $montantTotal = $tarifMensuel * $dureeMois;

        return response()->json([
            'tarif_base' => $tarifBase,
            'reduction_pct' => $reduction,
            'prix_mensuel' => round($tarifMensuel, 2),
            'montant_total' => round($montantTotal, 2),
            'duree_mois' => $dureeMois,
        ]);
    }
}
