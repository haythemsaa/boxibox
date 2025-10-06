<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContratController extends Controller
{
    /**
     * Liste des contrats du client
     */
    public function index(Request $request)
    {
        $client = $request->user();

        $contrats = $client->contrats()
            ->with(['box.famille', 'box.emplacement'])
            ->orderBy('date_debut', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $contrats->count(),
                'actifs' => $contrats->where('statut', 'actif')->count(),
                'contrats' => $contrats->map(function($contrat) {
                    return [
                        'id' => $contrat->id,
                        'numero' => $contrat->numero_contrat,
                        'date_debut' => $contrat->date_debut->format('d/m/Y'),
                        'date_fin' => $contrat->date_fin ? $contrat->date_fin->format('d/m/Y') : null,
                        'statut' => $contrat->statut,
                        'statut_label' => $this->getStatutLabel($contrat->statut),
                        'type' => $contrat->type_contrat,
                        'montant_loyer' => round($contrat->montant_loyer, 2),
                        'caution' => round($contrat->montant_caution ?? 0, 2),
                        'periodicite' => $contrat->periodicite_paiement,
                        'box' => [
                            'id' => $contrat->box->id,
                            'numero' => $contrat->box->numero,
                            'statut' => $contrat->box->statut,
                            'superficie' => $contrat->box->superficie,
                            'etage' => $contrat->box->etage,
                            'famille' => $contrat->box->famille->nom ?? null,
                            'emplacement' => $contrat->box->emplacement->nom ?? null,
                        ],
                        'duree_mois' => $this->calculerDureeMois($contrat),
                    ];
                }),
            ]
        ], 200);
    }

    /**
     * Détails d'un contrat
     */
    public function show(Request $request, $id)
    {
        $client = $request->user();
        $contrat = $client->contrats()
            ->with(['box.famille', 'box.emplacement', 'factures'])
            ->find($id);

        if (!$contrat) {
            return response()->json([
                'success' => false,
                'message' => 'Contrat non trouvé'
            ], 404);
        }

        // Calculer les statistiques
        $totalFacture = $contrat->factures->sum('montant_total');
        $totalPaye = $contrat->factures->sum('montant_paye');
        $facturesImpayees = $contrat->factures->where('statut', 'impayee')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $contrat->id,
                'numero' => $contrat->numero_contrat,
                'date_debut' => $contrat->date_debut->format('d/m/Y'),
                'date_fin' => $contrat->date_fin ? $contrat->date_fin->format('d/m/Y') : null,
                'date_signature' => $contrat->date_signature ? $contrat->date_signature->format('d/m/Y') : null,
                'statut' => $contrat->statut,
                'statut_label' => $this->getStatutLabel($contrat->statut),
                'type' => $contrat->type_contrat,
                'montant_loyer' => round($contrat->montant_loyer, 2),
                'caution' => round($contrat->montant_caution ?? 0, 2),
                'periodicite' => $contrat->periodicite_paiement,
                'jour_prelevement' => $contrat->jour_prelevement,
                'conditions_particulieres' => $contrat->conditions_particulieres,
                'box' => [
                    'id' => $contrat->box->id,
                    'numero' => $contrat->box->numero,
                    'statut' => $contrat->box->statut,
                    'superficie' => $contrat->box->superficie,
                    'longueur' => $contrat->box->longueur,
                    'largeur' => $contrat->box->largeur,
                    'hauteur' => $contrat->box->hauteur,
                    'etage' => $contrat->box->etage,
                    'description' => $contrat->box->description,
                    'equipements' => $contrat->box->equipements,
                    'famille' => [
                        'nom' => $contrat->box->famille->nom ?? null,
                        'description' => $contrat->box->famille->description ?? null,
                    ],
                    'emplacement' => [
                        'nom' => $contrat->box->emplacement->nom ?? null,
                        'adresse' => $contrat->box->emplacement->adresse ?? null,
                    ],
                ],
                'statistiques' => [
                    'duree_mois' => $this->calculerDureeMois($contrat),
                    'total_facture' => round($totalFacture, 2),
                    'total_paye' => round($totalPaye, 2),
                    'factures_count' => $contrat->factures->count(),
                    'factures_impayees' => $facturesImpayees,
                ],
                'documents' => [
                    'contrat_pdf' => route('contrats.pdf', $contrat->id),
                    'inventaire_pdf' => $contrat->inventaire_pdf_url ?? null,
                ],
            ]
        ], 200);
    }

    /**
     * Demande de résiliation
     */
    public function requestTermination(Request $request, $id)
    {
        $client = $request->user();
        $contrat = $client->contrats()->find($id);

        if (!$contrat) {
            return response()->json([
                'success' => false,
                'message' => 'Contrat non trouvé'
            ], 404);
        }

        if ($contrat->statut !== 'actif') {
            return response()->json([
                'success' => false,
                'message' => 'Seuls les contrats actifs peuvent être résiliés'
            ], 400);
        }

        $request->validate([
            'date_souhaitee' => 'required|date|after:today',
            'motif' => 'required|string|max:500',
        ]);

        // Créer une demande de résiliation (vous devriez avoir une table dédiée)
        // Pour l'instant, on envoie juste une notification

        // TODO: Créer model TerminationRequest et enregistrer

        return response()->json([
            'success' => true,
            'message' => 'Votre demande de résiliation a été envoyée. Vous serez contacté prochainement.',
            'data' => [
                'date_souhaitee' => $request->date_souhaitee,
                'motif' => $request->motif,
            ]
        ], 200);
    }

    /**
     * Demande de modification
     */
    public function requestModification(Request $request, $id)
    {
        $client = $request->user();
        $contrat = $client->contrats()->find($id);

        if (!$contrat) {
            return response()->json([
                'success' => false,
                'message' => 'Contrat non trouvé'
            ], 404);
        }

        $request->validate([
            'type_modification' => 'required|in:changement_box,modification_periodicite,autre',
            'description' => 'required|string|max:500',
        ]);

        // TODO: Créer model ModificationRequest et enregistrer

        return response()->json([
            'success' => true,
            'message' => 'Votre demande de modification a été envoyée.',
            'data' => [
                'type_modification' => $request->type_modification,
                'description' => $request->description,
            ]
        ], 200);
    }

    /**
     * Calculer la durée du contrat en mois
     */
    private function calculerDureeMois($contrat)
    {
        if (!$contrat->date_fin) {
            return null; // Contrat indéterminé
        }

        return $contrat->date_debut->diffInMonths($contrat->date_fin);
    }

    /**
     * Libellé du statut
     */
    private function getStatutLabel($statut)
    {
        $labels = [
            'brouillon' => 'Brouillon',
            'actif' => 'Actif',
            'suspendu' => 'Suspendu',
            'resilie' => 'Résilié',
            'termine' => 'Terminé',
        ];

        return $labels[$statut] ?? $statut;
    }
}
