<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClientDashboardController extends Controller
{
    /**
     * Dashboard principal du client
     */
    public function index(Request $request)
    {
        $client = $request->user();

        // Récupérer les contrats actifs
        $contratsActifs = $client->contrats()
            ->where('statut', 'actif')
            ->with(['box.famille', 'box.emplacement'])
            ->get();

        // Récupérer les factures impayées
        $facturesImpayees = $client->factures()
            ->where('statut', 'impayee')
            ->orderBy('date_emission', 'desc')
            ->get();

        // Calculer le montant total des impayés
        $montantImpaye = $facturesImpayees->sum('montant_total');

        // Récupérer les dernières factures
        $dernieresFactures = $client->factures()
            ->orderBy('date_emission', 'desc')
            ->limit(5)
            ->get();

        // Récupérer les codes d'accès actifs
        $codesAcces = $client->accessCodes()
            ->where('statut', 'actif')
            ->where(function($query) {
                $query->whereNull('date_fin_validite')
                      ->orWhere('date_fin_validite', '>', now());
            })
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'client' => [
                    'nom_complet' => $client->nom . ' ' . $client->prenom,
                    'email' => $client->email,
                    'telephone' => $client->telephone,
                    'statut' => $client->statut,
                ],
                'statistiques' => [
                    'contrats_actifs' => $contratsActifs->count(),
                    'factures_impayees' => $facturesImpayees->count(),
                    'montant_impaye' => round($montantImpaye, 2),
                    'codes_acces_actifs' => $codesAcces->count(),
                ],
                'contrats' => $contratsActifs->map(function($contrat) {
                    return [
                        'id' => $contrat->id,
                        'numero' => $contrat->numero_contrat,
                        'date_debut' => $contrat->date_debut->format('d/m/Y'),
                        'date_fin' => $contrat->date_fin ? $contrat->date_fin->format('d/m/Y') : null,
                        'statut' => $contrat->statut,
                        'montant_mensuel' => round($contrat->montant_loyer, 2),
                        'box' => [
                            'numero' => $contrat->box->numero,
                            'statut' => $contrat->box->statut,
                            'superficie' => $contrat->box->superficie,
                            'famille' => $contrat->box->famille->nom ?? null,
                            'emplacement' => $contrat->box->emplacement->nom ?? null,
                        ],
                    ];
                }),
                'factures_impayees' => $facturesImpayees->map(function($facture) {
                    return [
                        'id' => $facture->id,
                        'numero' => $facture->numero_facture,
                        'date_emission' => $facture->date_emission->format('d/m/Y'),
                        'date_echeance' => $facture->date_echeance ? $facture->date_echeance->format('d/m/Y') : null,
                        'montant_total' => round($facture->montant_total, 2),
                        'statut' => $facture->statut,
                        'jours_retard' => $facture->date_echeance ? now()->diffInDays($facture->date_echeance, false) : 0,
                    ];
                }),
                'codes_acces' => $codesAcces->map(function($code) {
                    return [
                        'id' => $code->id,
                        'type' => $code->type,
                        'code_pin' => $code->type === 'pin' ? $code->code_pin : null,
                        'qr_code_url' => $code->type === 'qr' ? $code->qr_code_url : null,
                        'box_numero' => $code->box->numero ?? 'Tous les box',
                        'date_fin_validite' => $code->date_fin_validite ? $code->date_fin_validite->format('d/m/Y') : 'Illimité',
                        'statut' => $code->statut,
                    ];
                }),
            ]
        ], 200);
    }

    /**
     * Notifications du client
     */
    public function notifications(Request $request)
    {
        $client = $request->user();

        $notifications = $client->notifications()
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $notifications->count(),
                'non_lues' => $notifications->where('read_at', null)->count(),
                'notifications' => $notifications->map(function($notif) {
                    return [
                        'id' => $notif->id,
                        'type' => $notif->type,
                        'titre' => $notif->data['titre'] ?? 'Notification',
                        'message' => $notif->data['message'] ?? '',
                        'lu' => $notif->read_at !== null,
                        'date' => $notif->created_at->format('d/m/Y H:i'),
                    ];
                }),
            ]
        ], 200);
    }

    /**
     * Marquer une notification comme lue
     */
    public function markNotificationAsRead(Request $request, $id)
    {
        $client = $request->user();
        $notification = $client->notifications()->find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification non trouvée'
            ], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marquée comme lue'
        ], 200);
    }
}
