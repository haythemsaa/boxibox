<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Contrat;
use App\Models\Facture;
use App\Models\Box;
use App\Models\Prospect;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatisticController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_statistics');
    }

    public function index()
    {
        // Redirection vers le dashboard avancé qui contient les statistiques
        return redirect()->route('admin.dashboard.advanced');
    }

    private function calculateStatistics()
    {
        $currentDate = Carbon::now();
        $lastMonth = $currentDate->copy()->subMonth();
        $lastYear = $currentDate->copy()->subYear();

        return [
            // Statistiques générales
            'total_clients' => Client::count(),
            'total_prospects' => Prospect::count(),
            'total_contrats' => Contrat::count(),
            'total_boxes' => Box::count(),
            'boxes_occupees' => Box::where('statut', 'occupé')->count(),
            'boxes_libres' => Box::where('statut', 'libre')->count(),
            'total_users' => User::count(),

            // Évolution mensuelle
            'clients_ce_mois' => Client::whereMonth('created_at', $currentDate->month)->count(),
            'clients_mois_precedent' => Client::whereMonth('created_at', $lastMonth->month)->count(),
            'prospects_ce_mois' => Prospect::whereMonth('created_at', $currentDate->month)->count(),
            'prospects_mois_precedent' => Prospect::whereMonth('created_at', $lastMonth->month)->count(),

            // Statistiques financières
            'ca_mensuel' => Facture::where('statut', 'payée')
                ->whereMonth('created_at', $currentDate->month)
                ->sum('montant_ttc'),
            'ca_annuel' => Facture::where('statut', 'payée')
                ->whereYear('created_at', $currentDate->year)
                ->sum('montant_ttc'),
            'factures_impayees' => Facture::where('statut', 'impayée')->sum('montant_ttc'),
            'factures_en_attente' => Facture::where('statut', 'en_attente')->sum('montant_ttc'),

            // Taux de conversion
            'taux_conversion' => $this->calculateConversionRate(),

            // Taux d'occupation
            'taux_occupation' => $this->calculateOccupancyRate(),

            // Top familles de boxes
            'top_categories' => Box::select('box_familles.nom', DB::raw('count(*) as count'))
                ->join('box_familles', 'boxes.famille_id', '=', 'box_familles.id')
                ->groupBy('box_familles.id', 'box_familles.nom')
                ->orderByDesc('count')
                ->limit(5)
                ->get(),

            // Répartition par statut des contrats
            'contrats_par_statut' => Contrat::select('statut', DB::raw('count(*) as count'))
                ->groupBy('statut')
                ->get(),

            // Évolution des factures
            'evolution_factures' => $this->getInvoiceEvolution(),
        ];
    }

    private function getChartData()
    {
        return [
            'monthly_revenue' => $this->getMonthlyRevenue(),
            'client_growth' => $this->getClientGrowth(),
            'box_occupancy' => $this->getBoxOccupancyData(),
            'contract_status' => $this->getContractStatusData(),
        ];
    }

    private function calculateConversionRate()
    {
        $totalProspects = Prospect::count();
        $convertedProspects = Prospect::where('statut', 'converti')->count();

        return $totalProspects > 0 ? round(($convertedProspects / $totalProspects) * 100, 2) : 0;
    }

    private function calculateOccupancyRate()
    {
        $totalBoxes = Box::count();
        $occupiedBoxes = Box::where('statut', 'occupé')->count();

        return $totalBoxes > 0 ? round(($occupiedBoxes / $totalBoxes) * 100, 2) : 0;
    }

    private function getMonthlyRevenue()
    {
        $revenues = [];
        $labels = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $revenue = Facture::where('statut', 'payée')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('montant_ttc');

            $revenues[] = $revenue;
            $labels[] = $date->format('M Y');
        }

        return [
            'labels' => $labels,
            'data' => $revenues
        ];
    }

    private function getClientGrowth()
    {
        $growth = [];
        $labels = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $clientCount = Client::whereYear('created_at', '<=', $date->year)
                ->whereMonth('created_at', '<=', $date->month)
                ->count();

            $growth[] = $clientCount;
            $labels[] = $date->format('M Y');
        }

        return [
            'labels' => $labels,
            'data' => $growth
        ];
    }

    private function getBoxOccupancyData()
    {
        $occupancy = Box::select('statut', DB::raw('count(*) as count'))
            ->groupBy('statut')
            ->get();

        return [
            'labels' => $occupancy->pluck('statut'),
            'data' => $occupancy->pluck('count')
        ];
    }

    private function getContractStatusData()
    {
        $contracts = Contrat::select('statut', DB::raw('count(*) as count'))
            ->groupBy('statut')
            ->get();

        return [
            'labels' => $contracts->pluck('statut'),
            'data' => $contracts->pluck('count')
        ];
    }

    private function getInvoiceEvolution()
    {
        return Facture::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(montant_ttc) as total')
            )
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }
}