<?php

namespace App\Http\Controllers;

use App\Models\Box;
use App\Models\Client;
use App\Models\Contrat;
use App\Models\Facture;
use App\Models\Reglement;
use App\Models\AccessLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Page principale des rapports
     */
    public function index()
    {
        return view('reports.index');
    }

    /**
     * Rapport financier
     */
    public function financial(Request $request)
    {
        $dateDebut = $request->input('date_debut', now()->startOfMonth()->format('Y-m-d'));
        $dateFin = $request->input('date_fin', now()->endOfMonth()->format('Y-m-d'));

        // Chiffre d'affaires
        $ca = Reglement::whereBetween('date_reglement', [$dateDebut, $dateFin])
            ->sum('montant');

        $caParMode = Reglement::whereBetween('date_reglement', [$dateDebut, $dateFin])
            ->select('mode_paiement', DB::raw('SUM(montant) as total'))
            ->groupBy('mode_paiement')
            ->get();

        // Factures
        $facturesEmises = Facture::whereBetween('date_emission', [$dateDebut, $dateFin])->count();
        $facturesPayees = Facture::whereBetween('date_emission', [$dateDebut, $dateFin])
            ->where('statut', 'payee')->count();
        $facturesImpayees = Facture::whereBetween('date_emission', [$dateDebut, $dateFin])
            ->where('statut', 'impayee')->count();

        // Montants
        $montantTotal = Facture::whereBetween('date_emission', [$dateDebut, $dateFin])
            ->sum('montant_total');
        $montantPaye = Facture::whereBetween('date_emission', [$dateDebut, $dateFin])
            ->sum('montant_paye');
        $montantImpaye = $montantTotal - $montantPaye;

        // Évolution mensuelle
        $evolutionCA = Reglement::whereBetween('date_reglement', [$dateDebut, $dateFin])
            ->selectRaw('DATE_FORMAT(date_reglement, "%Y-%m") as mois, SUM(montant) as total')
            ->groupBy('mois')
            ->orderBy('mois')
            ->get();

        return view('reports.financial', compact(
            'dateDebut',
            'dateFin',
            'ca',
            'caParMode',
            'facturesEmises',
            'facturesPayees',
            'facturesImpayees',
            'montantTotal',
            'montantPaye',
            'montantImpaye',
            'evolutionCA'
        ));
    }

    /**
     * Rapport d'occupation
     */
    public function occupation(Request $request)
    {
        $date = $request->input('date', now()->format('Y-m-d'));

        // Stats globales
        $totalBoxes = Box::count();
        $boxesOccupes = Box::where('statut', 'occupe')->count();
        $boxesLibres = Box::where('statut', 'libre')->count();
        $boxesReserves = Box::where('statut', 'reserve')->count();
        $boxesMaintenance = Box::where('statut', 'maintenance')->count();

        $tauxOccupation = $totalBoxes > 0 ? ($boxesOccupes / $totalBoxes) * 100 : 0;

        // Par emplacement
        $occupationParEmplacement = Box::select('emplacement_id')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN statut = "occupe" THEN 1 ELSE 0 END) as occupes')
            ->with('emplacement')
            ->groupBy('emplacement_id')
            ->get()
            ->map(function ($item) {
                return [
                    'emplacement' => $item->emplacement->nom ?? 'N/A',
                    'total' => $item->total,
                    'occupes' => $item->occupes,
                    'taux' => $item->total > 0 ? ($item->occupes / $item->total) * 100 : 0,
                ];
            });

        // Par famille
        $occupationParFamille = Box::select('box_famille_id')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN statut = "occupe" THEN 1 ELSE 0 END) as occupes')
            ->with('famille')
            ->groupBy('box_famille_id')
            ->get()
            ->map(function ($item) {
                return [
                    'famille' => $item->famille->nom ?? 'N/A',
                    'total' => $item->total,
                    'occupes' => $item->occupes,
                    'taux' => $item->total > 0 ? ($item->occupes / $item->total) * 100 : 0,
                ];
            });

        // Évolution de l'occupation (6 derniers mois)
        $evolutionOccupation = collect();
        for ($i = 5; $i >= 0; $i--) {
            $mois = now()->subMonths($i)->format('Y-m');
            $totalMois = Box::count();
            $occupesMois = Contrat::where('statut', 'actif')
                ->whereYear('date_debut', '=', now()->subMonths($i)->year)
                ->whereMonth('date_debut', '=', now()->subMonths($i)->month)
                ->count();

            $evolutionOccupation->push([
                'mois' => $mois,
                'taux' => $totalMois > 0 ? ($occupesMois / $totalMois) * 100 : 0,
            ]);
        }

        return view('reports.occupation', compact(
            'date',
            'totalBoxes',
            'boxesOccupes',
            'boxesLibres',
            'boxesReserves',
            'boxesMaintenance',
            'tauxOccupation',
            'occupationParEmplacement',
            'occupationParFamille',
            'evolutionOccupation'
        ));
    }

    /**
     * Rapport clients
     */
    public function clients(Request $request)
    {
        // Nouveaux clients par mois
        $nouveauxClients = Client::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as mois, COUNT(*) as total')
            ->groupBy('mois')
            ->orderBy('mois', 'desc')
            ->limit(12)
            ->get();

        // Top 10 clients par CA
        $topClients = Client::select('clients.*')
            ->selectRaw('SUM(reglements.montant) as total_ca')
            ->join('factures', 'factures.client_id', '=', 'clients.id')
            ->join('reglements', 'reglements.facture_id', '=', 'factures.id')
            ->groupBy('clients.id')
            ->orderByDesc('total_ca')
            ->limit(10)
            ->get();

        // Clients avec retards de paiement
        $clientsRetard = Client::select('clients.*')
            ->selectRaw('COUNT(factures.id) as nb_factures_impayees')
            ->selectRaw('SUM(factures.montant_total - factures.montant_paye) as montant_du')
            ->join('factures', 'factures.client_id', '=', 'clients.id')
            ->where('factures.statut', 'impayee')
            ->where('factures.date_echeance', '<', now())
            ->groupBy('clients.id')
            ->orderByDesc('montant_du')
            ->get();

        // Statistiques générales
        $stats = [
            'total_clients' => Client::count(),
            'clients_actifs' => Client::whereHas('contrats', function ($q) {
                $q->where('statut', 'actif');
            })->count(),
            'nouveaux_ce_mois' => Client::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)->count(),
        ];

        return view('reports.clients', compact(
            'nouveauxClients',
            'topClients',
            'clientsRetard',
            'stats'
        ));
    }

    /**
     * Rapport d'accès et sécurité
     */
    public function access(Request $request)
    {
        $dateDebut = $request->input('date_debut', now()->startOfMonth()->format('Y-m-d'));
        $dateFin = $request->input('date_fin', now()->endOfMonth()->format('Y-m-d'));

        // Stats globales
        $totalAccess = AccessLog::whereBetween('date_heure', [$dateDebut, $dateFin])->count();
        $accessAutorises = AccessLog::whereBetween('date_heure', [$dateDebut, $dateFin])
            ->where('statut', 'autorise')->count();
        $accessRefuses = AccessLog::whereBetween('date_heure', [$dateDebut, $dateFin])
            ->where('statut', 'refuse')->count();

        // Par méthode
        $accessParMethode = AccessLog::whereBetween('date_heure', [$dateDebut, $dateFin])
            ->select('methode')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN statut = "autorise" THEN 1 ELSE 0 END) as autorises')
            ->selectRaw('SUM(CASE WHEN statut = "refuse" THEN 1 ELSE 0 END) as refuses')
            ->groupBy('methode')
            ->get();

        // Accès refusés récents
        $accessRefusesRecents = AccessLog::where('statut', 'refuse')
            ->whereBetween('date_heure', [$dateDebut, $dateFin])
            ->with(['client', 'box'])
            ->orderBy('date_heure', 'desc')
            ->limit(20)
            ->get();

        // Top 10 clients avec le plus d'accès
        $topClientsAccess = AccessLog::whereBetween('date_heure', [$dateDebut, $dateFin])
            ->select('client_id')
            ->selectRaw('COUNT(*) as total_acces')
            ->with('client')
            ->groupBy('client_id')
            ->orderByDesc('total_acces')
            ->limit(10)
            ->get();

        // Évolution des accès par jour
        $evolutionAccess = AccessLog::whereBetween('date_heure', [$dateDebut, $dateFin])
            ->selectRaw('DATE(date_heure) as jour')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN statut = "autorise" THEN 1 ELSE 0 END) as autorises')
            ->selectRaw('SUM(CASE WHEN statut = "refuse" THEN 1 ELSE 0 END) as refuses')
            ->groupBy('jour')
            ->orderBy('jour')
            ->get();

        return view('reports.access', compact(
            'dateDebut',
            'dateFin',
            'totalAccess',
            'accessAutorises',
            'accessRefuses',
            'accessParMethode',
            'accessRefusesRecents',
            'topClientsAccess',
            'evolutionAccess'
        ));
    }

    /**
     * Exporter un rapport en PDF
     */
    public function exportPDF(Request $request)
    {
        $type = $request->input('type'); // financial, occupation, clients, access
        $dateDebut = $request->input('date_debut');
        $dateFin = $request->input('date_fin');

        $data = [];

        switch ($type) {
            case 'financial':
                $data = $this->getFinancialData($dateDebut, $dateFin);
                break;
            case 'occupation':
                $data = $this->getOccupationData();
                break;
            case 'clients':
                $data = $this->getClientsData();
                break;
            case 'access':
                $data = $this->getAccessData($dateDebut, $dateFin);
                break;
        }

        $pdf = Pdf::loadView("reports.pdf.{$type}", $data);

        return $pdf->download("rapport_{$type}_" . now()->format('Y-m-d') . ".pdf");
    }

    /**
     * Exporter un rapport en Excel
     */
    public function exportExcel(Request $request)
    {
        $type = $request->input('type');
        $dateDebut = $request->input('date_debut', now()->startOfMonth()->format('Y-m-d'));
        $dateFin = $request->input('date_fin', now()->endOfMonth()->format('Y-m-d'));

        $filename = "rapport_{$type}_" . now()->format('Y-m-d') . ".xlsx";

        switch ($type) {
            case 'financial':
                return \Maatwebsite\Excel\Facades\Excel::download(
                    new \App\Exports\FinancialReportExport($dateDebut, $dateFin),
                    $filename
                );

            case 'occupation':
                return \Maatwebsite\Excel\Facades\Excel::download(
                    new \App\Exports\OccupationReportExport(),
                    $filename
                );

            case 'clients':
                return \Maatwebsite\Excel\Facades\Excel::download(
                    new \App\Exports\ClientsReportExport(),
                    $filename
                );

            case 'access':
                return \Maatwebsite\Excel\Facades\Excel::download(
                    new \App\Exports\AccessReportExport($dateDebut, $dateFin),
                    $filename
                );

            default:
                return back()->with('error', 'Type de rapport non reconnu');
        }
    }

    // Méthodes privées pour récupérer les données

    private function getFinancialData($dateDebut, $dateFin)
    {
        $ca = Reglement::whereBetween('date_reglement', [$dateDebut, $dateFin])->sum('montant');

        $caParMode = Reglement::whereBetween('date_reglement', [$dateDebut, $dateFin])
            ->select('mode_paiement', DB::raw('SUM(montant) as total'))
            ->groupBy('mode_paiement')
            ->get();

        $facturesEmises = Facture::whereBetween('date_emission', [$dateDebut, $dateFin])->count();
        $facturesPayees = Facture::whereBetween('date_emission', [$dateDebut, $dateFin])
            ->where('statut', 'payee')->count();
        $facturesImpayees = Facture::whereBetween('date_emission', [$dateDebut, $dateFin])
            ->where('statut', 'impayee')->count();

        $montantTotal = Facture::whereBetween('date_emission', [$dateDebut, $dateFin])
            ->sum('montant_total');
        $montantPaye = Facture::whereBetween('date_emission', [$dateDebut, $dateFin])
            ->sum('montant_paye');
        $montantImpaye = $montantTotal - $montantPaye;

        $evolutionCA = Reglement::whereBetween('date_reglement', [$dateDebut, $dateFin])
            ->selectRaw('DATE_FORMAT(date_reglement, "%Y-%m") as mois, SUM(montant) as total')
            ->groupBy('mois')
            ->orderBy('mois')
            ->get();

        return [
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
            'ca' => $ca,
            'caParMode' => $caParMode,
            'facturesEmises' => $facturesEmises,
            'facturesPayees' => $facturesPayees,
            'facturesImpayees' => $facturesImpayees,
            'montantTotal' => $montantTotal,
            'montantPaye' => $montantPaye,
            'montantImpaye' => $montantImpaye,
            'evolutionCA' => $evolutionCA,
        ];
    }

    private function getOccupationData()
    {
        $totalBoxes = Box::count();
        $boxesOccupes = Box::where('statut', 'occupe')->count();
        $boxesLibres = Box::where('statut', 'libre')->count();
        $boxesReserves = Box::where('statut', 'reserve')->count();
        $boxesMaintenance = Box::where('statut', 'maintenance')->count();
        $tauxOccupation = $totalBoxes > 0 ? ($boxesOccupes / $totalBoxes) * 100 : 0;

        $occupationParEmplacement = Box::select('emplacement_id')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN statut = "occupe" THEN 1 ELSE 0 END) as occupes')
            ->with('emplacement')
            ->groupBy('emplacement_id')
            ->get()
            ->map(function ($item) {
                return [
                    'emplacement' => $item->emplacement->nom ?? 'N/A',
                    'total' => $item->total,
                    'occupes' => $item->occupes,
                    'taux' => $item->total > 0 ? ($item->occupes / $item->total) * 100 : 0,
                ];
            });

        $occupationParFamille = Box::select('box_famille_id')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN statut = "occupe" THEN 1 ELSE 0 END) as occupes')
            ->with('famille')
            ->groupBy('box_famille_id')
            ->get()
            ->map(function ($item) {
                return [
                    'famille' => $item->famille->nom ?? 'N/A',
                    'total' => $item->total,
                    'occupes' => $item->occupes,
                    'taux' => $item->total > 0 ? ($item->occupes / $item->total) * 100 : 0,
                ];
            });

        return [
            'date' => now()->format('Y-m-d'),
            'totalBoxes' => $totalBoxes,
            'boxesOccupes' => $boxesOccupes,
            'boxesLibres' => $boxesLibres,
            'boxesReserves' => $boxesReserves,
            'boxesMaintenance' => $boxesMaintenance,
            'tauxOccupation' => $tauxOccupation,
            'occupationParEmplacement' => $occupationParEmplacement,
            'occupationParFamille' => $occupationParFamille,
        ];
    }

    private function getClientsData()
    {
        return [
            'clients' => Client::with('contrats')->get(),
        ];
    }

    private function getAccessData($dateDebut, $dateFin)
    {
        return [
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
            'logs' => AccessLog::whereBetween('date_heure', [$dateDebut, $dateFin])->get(),
        ];
    }
}
