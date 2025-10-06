<?php

namespace App\Http\Controllers;

use App\Models\Box;
use App\Models\Client;
use App\Models\Contrat;
use App\Models\Facture;
use App\Models\Reglement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardAdvancedController extends Controller
{
    public function index()
    {
        // Cache les données du dashboard pendant 5 minutes
        $cacheKey = 'dashboard_advanced_' . auth()->id();

        $dashboardData = Cache::remember($cacheKey, 300, function() {
            return [
                'stats' => $this->getKPIs(),
                'caData' => $this->getCAEvolution(),
                'topClients' => $this->getTopClients(),
                'activitesRecentes' => $this->getActivitesRecentes(),
                'alertes' => $this->getAlertes(),
                'statutBoxes' => $this->getStatutBoxes()
            ];
        });

        return view('dashboard.admin_advanced', [
            'stats' => $dashboardData['stats'],
            'ca_labels' => $dashboardData['caData']['labels'],
            'ca_data' => $dashboardData['caData']['data'],
            'top_clients_labels' => $dashboardData['topClients']['labels'],
            'top_clients_data' => $dashboardData['topClients']['data'],
            'activites_recentes' => $dashboardData['activitesRecentes'],
            'alertes' => $dashboardData['alertes'],
            'statut_boxes_data' => $dashboardData['statutBoxes']
        ]);
    }

    private function getKPIs()
    {
        $moisActuel = Carbon::now()->month;
        $anneeActuelle = Carbon::now()->year;
        $moisPrecedent = Carbon::now()->subMonth()->month;

        // CA du mois actuel
        $caMois = Reglement::whereMonth('date_reglement', $moisActuel)
            ->whereYear('date_reglement', $anneeActuelle)
            ->sum('montant');

        // CA du mois précédent
        $caMoisPrecedent = Reglement::whereMonth('date_reglement', $moisPrecedent)
            ->whereYear('date_reglement', $anneeActuelle)
            ->sum('montant');

        // Variation CA
        $variationCA = $caMoisPrecedent > 0
            ? (($caMois - $caMoisPrecedent) / $caMoisPrecedent) * 100
            : 0;

        // Taux d'occupation
        $boxesTotal = Box::active()->count();
        $boxesOccupes = Box::active()->occupe()->count();
        $tauxOccupation = $boxesTotal > 0 ? ($boxesOccupes / $boxesTotal) * 100 : 0;

        // Clients actifs
        $clientsActifs = Client::whereHas('contrats', function($q) {
            $q->where('statut', 'actif');
        })->count();

        // Nouveaux clients du mois
        $nouveauxClientsMois = Client::whereMonth('created_at', $moisActuel)
            ->whereYear('created_at', $anneeActuelle)
            ->count();

        // Impayés
        $facturesImpayees = Facture::where('statut', 'en_retard')->get();
        $montantImpayes = $facturesImpayees->sum('montant_ttc');
        $nbFacturesImpayees = $facturesImpayees->count();

        return [
            'ca_mois' => $caMois,
            'variation_ca' => $variationCA,
            'taux_occupation' => $tauxOccupation,
            'boxes_total' => $boxesTotal,
            'boxes_occupes' => $boxesOccupes,
            'clients_actifs' => $clientsActifs,
            'nouveaux_clients_mois' => $nouveauxClientsMois,
            'montant_impayes' => $montantImpayes,
            'nb_factures_impayees' => $nbFacturesImpayees
        ];
    }

    private function getCAEvolution()
    {
        $labels = [];
        $data = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $labels[] = $date->translatedFormat('M Y');

            $ca = Reglement::whereMonth('date_reglement', $date->month)
                ->whereYear('date_reglement', $date->year)
                ->sum('montant');

            $data[] = $ca;
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    private function getTopClients()
    {
        $topClients = Client::select('clients.id', 'clients.prenom', 'clients.nom')
            ->selectRaw('SUM(reglements.montant) as total_ca')
            ->join('factures', 'factures.client_id', '=', 'clients.id')
            ->join('reglements', 'reglements.facture_id', '=', 'factures.id')
            ->groupBy('clients.id', 'clients.prenom', 'clients.nom')
            ->orderByDesc('total_ca')
            ->limit(5)
            ->get();

        $labels = [];
        $data = [];

        foreach ($topClients as $client) {
            $labels[] = $client->prenom . ' ' . $client->nom;
            $data[] = $client->total_ca;
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    private function getActivitesRecentes()
    {
        $activites = [];

        // Derniers contrats signés
        $derniersContrats = Contrat::with('client')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        foreach ($derniersContrats as $contrat) {
            $activites[] = [
                'titre' => 'Nouveau contrat signé',
                'description' => $contrat->client->prenom . ' ' . $contrat->client->nom . ' - Contrat ' . $contrat->numero_contrat,
                'date' => $contrat->created_at,
                'icon' => 'fa-file-contract',
                'badge_class' => 'bg-success'
            ];
        }

        // Derniers règlements
        $derniersReglements = Reglement::with('facture.client')
            ->orderBy('date_reglement', 'desc')
            ->limit(3)
            ->get();

        foreach ($derniersReglements as $reglement) {
            $activites[] = [
                'titre' => 'Paiement reçu',
                'description' => number_format($reglement->montant, 2) . ' € - ' . $reglement->facture->client->prenom . ' ' . $reglement->facture->client->nom,
                'date' => $reglement->date_reglement,
                'icon' => 'fa-money-bill-wave',
                'badge_class' => 'bg-success'
            ];
        }

        // Dernières factures impayées
        $dernieresImpayees = Facture::with('client')
            ->where('statut', 'en_retard')
            ->orderBy('date_echeance', 'desc')
            ->limit(2)
            ->get();

        foreach ($dernieresImpayees as $facture) {
            $activites[] = [
                'titre' => 'Facture impayée',
                'description' => 'Facture ' . $facture->numero_facture . ' - ' . number_format($facture->montant_ttc, 2) . ' €',
                'date' => $facture->date_echeance,
                'icon' => 'fa-exclamation-triangle',
                'badge_class' => 'bg-danger'
            ];
        }

        // Trier par date décroissante
        usort($activites, function($a, $b) {
            return $b['date'] <=> $a['date'];
        });

        return array_slice($activites, 0, 10);
    }

    private function getAlertes()
    {
        $alertes = [];

        // Boxes en maintenance
        $boxesMaintenance = Box::where('statut', 'maintenance')->count();
        if ($boxesMaintenance > 0) {
            $alertes[] = [
                'type' => 'warning',
                'titre' => 'Boxes en maintenance',
                'message' => "$boxesMaintenance box(es) nécessitent une intervention.",
                'icon' => 'fa-tools',
                'action_url' => route('boxes.index', ['statut' => 'maintenance'])
            ];
        }

        // Factures en retard > 30 jours
        $facturesRetard30j = Facture::where('statut', 'en_retard')
            ->where('date_echeance', '<', Carbon::now()->subDays(30))
            ->count();

        if ($facturesRetard30j > 0) {
            $alertes[] = [
                'type' => 'danger',
                'titre' => 'Impayés critiques',
                'message' => "$facturesRetard30j facture(s) impayée(s) depuis plus de 30 jours.",
                'icon' => 'fa-exclamation-circle',
                'action_url' => route('factures.index', ['statut' => 'en_retard'])
            ];
        }

        // Taux d'occupation faible
        $tauxOccupation = Box::active()->occupe()->count() / max(Box::active()->count(), 1) * 100;
        if ($tauxOccupation < 70) {
            $alertes[] = [
                'type' => 'info',
                'titre' => 'Taux d\'occupation faible',
                'message' => "Taux actuel: " . number_format($tauxOccupation, 1) . "%. Pensez à lancer une campagne marketing.",
                'icon' => 'fa-chart-line',
                'action_url' => null
            ];
        }

        // Contrats arrivant à échéance dans 30 jours
        $contratsEcheance = Contrat::where('statut', 'actif')
            ->whereBetween('date_fin', [Carbon::now(), Carbon::now()->addDays(30)])
            ->count();

        if ($contratsEcheance > 0) {
            $alertes[] = [
                'type' => 'warning',
                'titre' => 'Renouvellements à prévoir',
                'message' => "$contratsEcheance contrat(s) arrivent à échéance dans les 30 prochains jours.",
                'icon' => 'fa-calendar-alt',
                'action_url' => route('contrats.index')
            ];
        }

        return $alertes;
    }

    private function getStatutBoxes()
    {
        return [
            Box::active()->occupe()->count(),
            Box::active()->libre()->count(),
            Box::active()->where('statut', 'reserve')->count(),
            Box::active()->where('statut', 'maintenance')->count()
        ];
    }

    public function export()
    {
        // TODO: Implémenter l'export Excel/PDF
        return response()->download('path/to/export.xlsx');
    }
}
