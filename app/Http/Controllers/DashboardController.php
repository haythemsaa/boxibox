<?php

namespace App\Http\Controllers;

use App\Models\Box;
use App\Models\Client;
use App\Models\Contrat;
use App\Models\Facture;
use App\Models\Reglement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:view_dashboard']);
    }

    public function index()
    {
        $stats = $this->getStatistics();
        $charts = $this->getChartData();

        return view('dashboard.index', compact('stats', 'charts'));
    }

    private function getStatistics()
    {
        // Statistiques d'occupation
        $totalBoxes = Box::active()->count();
        $boxesLibres = Box::active()->libre()->count();
        $boxesOccupes = Box::active()->occupe()->count();
        $boxesReserves = Box::active()->where('boxes.statut', 'reserve')->count();

        // Statistiques clients et contrats
        $clientsActifs = Client::active()->count();
        $contratsActifs = Contrat::actif()->count();
        $contratsLitige = Contrat::where('statut', 'litige')->count();

        // Statistiques surfaces et volumes
        $surfaceTotale = Box::active()->sum('surface');
        $surfaceOccupee = Box::active()
            ->join('contrats', 'boxes.id', '=', 'contrats.box_id')
            ->where('boxes.statut', 'occupe')
            ->where('contrats.statut', 'actif')
            ->sum('boxes.surface');

        $volumeTotal = Box::active()->sum('volume');
        $volumeOccupe = Box::active()
            ->join('contrats', 'boxes.id', '=', 'contrats.box_id')
            ->where('boxes.statut', 'occupe')
            ->where('contrats.statut', 'actif')
            ->sum('boxes.volume');

        // Pourcentages d'occupation
        $tauxOccupationNombre = $totalBoxes > 0 ? round(($boxesOccupes / $totalBoxes) * 100, 1) : 0;
        $tauxOccupationSurface = $surfaceTotale > 0 ? round(($surfaceOccupee / $surfaceTotale) * 100, 1) : 0;

        // Statistiques financières
        $chiffreAffairesMensuel = Contrat::actif()->sum('prix_mensuel');
        $montantAssurances = Contrat::actif()
            ->where('assurance_incluse', true)
            ->sum('montant_assurance');

        $chiffreAffairesMaximal = Box::active()->sum('prix_mensuel');

        // Résumé mensuel
        $debutMois = now()->startOfMonth();
        $finMois = now()->endOfMonth();

        $facturesMois = Facture::whereBetween('date_emission', [$debutMois, $finMois]);
        $chiffreAffairesMoisTTC = $facturesMois->sum('montant_ttc');
        $nombreFacturesMois = $facturesMois->count();

        $avoirsMois = Facture::whereBetween('date_emission', [$debutMois, $finMois])
            ->where('montant_ttc', '<', 0)
            ->sum('montant_ttc');

        $encaissementsMois = Reglement::whereBetween('date_reglement', [$debutMois, $finMois])
            ->where('statut', 'valide')
            ->sum('montant');

        return [
            'occupation' => [
                'total_boxes' => $totalBoxes,
                'boxes_libres' => $boxesLibres,
                'boxes_occupes' => $boxesOccupes,
                'boxes_reserves' => $boxesReserves,
                'clients_actifs' => $clientsActifs,
                'contrats_actifs' => $contratsActifs,
                'contrats_litige' => $contratsLitige,
            ],
            'surfaces' => [
                'surface_totale' => $surfaceTotale,
                'surface_occupee' => $surfaceOccupee,
                'volume_total' => $volumeTotal,
                'volume_occupe' => $volumeOccupe,
                'taux_occupation_nombre' => $tauxOccupationNombre,
                'taux_occupation_surface' => $tauxOccupationSurface,
            ],
            'financier' => [
                'ca_mensuel_ht' => $chiffreAffairesMensuel,
                'montant_assurances' => $montantAssurances,
                'ca_maximal_ht' => $chiffreAffairesMaximal,
            ],
            'mensuel' => [
                'ca_ttc' => $chiffreAffairesMoisTTC,
                'nb_factures' => $nombreFacturesMois,
                'montant_avoirs' => abs($avoirsMois),
                'encaissements' => $encaissementsMois,
            ]
        ];
    }

    private function getChartData()
    {
        // Évolution du chiffre d'affaires sur 12 mois
        $caEvolution = [];
        for ($i = 11; $i >= 0; $i--) {
            $debut = now()->subMonths($i)->startOfMonth();
            $fin = now()->subMonths($i)->endOfMonth();

            $ca = Facture::whereBetween('date_emission', [$debut, $fin])
                ->sum('montant_ttc');

            $caEvolution[] = [
                'mois' => $debut->format('M Y'),
                'ca' => $ca
            ];
        }

        // Évolution des contrats sur 12 mois
        $contratsEvolution = [];
        for ($i = 11; $i >= 0; $i--) {
            $debut = now()->subMonths($i)->startOfMonth();
            $fin = now()->subMonths($i)->endOfMonth();

            $entrees = Contrat::whereBetween('date_debut', [$debut, $fin])->count();
            $sorties = Contrat::whereBetween('updated_at', [$debut, $fin])
                ->whereIn('statut', ['termine', 'resilie'])
                ->count();

            $contratsEvolution[] = [
                'mois' => $debut->format('M Y'),
                'entrees' => $entrees,
                'sorties' => $sorties
            ];
        }

        // Répartition par surface
        $repartitionSurface = Box::active()
            ->select(DB::raw('
                CASE
                    WHEN surface < 5 THEN "Moins de 5m²"
                    WHEN surface < 10 THEN "5-10m²"
                    WHEN surface < 20 THEN "10-20m²"
                    WHEN surface < 50 THEN "20-50m²"
                    ELSE "Plus de 50m²"
                END as tranche_surface
            '), DB::raw('COUNT(*) as nombre'))
            ->groupBy('tranche_surface')
            ->get();

        return [
            'ca_evolution' => $caEvolution,
            'contrats_evolution' => $contratsEvolution,
            'repartition_surface' => $repartitionSurface
        ];
    }
}