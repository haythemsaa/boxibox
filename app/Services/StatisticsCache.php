<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class StatisticsCache
{
    /**
     * Durée de cache par défaut (en minutes)
     */
    const DEFAULT_TTL = 60; // 1 heure

    /**
     * Durée de cache pour les stats dashboard (5 minutes)
     */
    const DASHBOARD_TTL = 5;

    /**
     * Durée de cache pour les stats mensuelles (30 minutes)
     */
    const MONTHLY_TTL = 30;

    /**
     * Durée de cache pour les stats annuelles (2 heures)
     */
    const YEARLY_TTL = 120;

    /**
     * Récupérer ou calculer des statistiques avec cache
     *
     * @param string $key Clé du cache
     * @param callable $callback Fonction pour calculer les stats
     * @param int|null $ttl Durée de vie en minutes
     * @return mixed
     */
    public static function remember(string $key, callable $callback, ?int $ttl = null)
    {
        $ttl = $ttl ?? self::DEFAULT_TTL;

        return Cache::remember($key, now()->addMinutes($ttl), $callback);
    }

    /**
     * Récupérer les stats du dashboard client
     *
     * @param int $clientId
     * @return array
     */
    public static function getClientDashboardStats(int $clientId): array
    {
        return self::remember(
            "client.{$clientId}.dashboard",
            function () use ($clientId) {
                $client = \App\Models\Client::find($clientId);

                return [
                    'contrats_actifs' => $client->contrats()->where('statut', 'actif')->count(),
                    'factures_impayees' => $client->factures()->where('statut', 'en_retard')->count(),
                    'montant_du' => $client->factures()->where('statut', 'en_retard')->sum('montant_ttc'),
                    'documents' => $client->documents()->count(),
                    'mandat_sepa_actif' => $client->mandatsSepa()->where('statut', 'valide')->exists(),
                ];
            },
            self::DASHBOARD_TTL
        );
    }

    /**
     * Récupérer les stats globales admin
     *
     * @param int $tenantId
     * @return array
     */
    public static function getAdminDashboardStats(int $tenantId): array
    {
        return self::remember(
            "admin.{$tenantId}.dashboard",
            function () use ($tenantId) {
                return [
                    'clients_actifs' => \App\Models\Client::where('tenant_id', $tenantId)
                        ->where('is_active', true)->count(),
                    'contrats_actifs' => \App\Models\Contrat::where('tenant_id', $tenantId)
                        ->where('statut', 'actif')->count(),
                    'factures_mois' => \App\Models\Facture::where('tenant_id', $tenantId)
                        ->whereMonth('date_emission', now()->month)
                        ->whereYear('date_emission', now()->year)
                        ->count(),
                    'ca_mois' => \App\Models\Facture::where('tenant_id', $tenantId)
                        ->whereMonth('date_emission', now()->month)
                        ->whereYear('date_emission', now()->year)
                        ->sum('montant_ttc'),
                    'impayés' => \App\Models\Facture::where('tenant_id', $tenantId)
                        ->where('statut', 'en_retard')->sum('montant_ttc'),
                    'taux_occupation' => self::calculateTauxOccupation($tenantId),
                ];
            },
            self::DASHBOARD_TTL
        );
    }

    /**
     * Récupérer les stats mensuelles
     *
     * @param int $tenantId
     * @param int $month
     * @param int $year
     * @return array
     */
    public static function getMonthlyStats(int $tenantId, int $month, int $year): array
    {
        return self::remember(
            "admin.{$tenantId}.monthly.{$year}.{$month}",
            function () use ($tenantId, $month, $year) {
                return [
                    'factures_total' => \App\Models\Facture::where('tenant_id', $tenantId)
                        ->whereMonth('date_emission', $month)
                        ->whereYear('date_emission', $year)
                        ->count(),
                    'ca_ht' => \App\Models\Facture::where('tenant_id', $tenantId)
                        ->whereMonth('date_emission', $month)
                        ->whereYear('date_emission', $year)
                        ->sum('montant_ht'),
                    'ca_ttc' => \App\Models\Facture::where('tenant_id', $tenantId)
                        ->whereMonth('date_emission', $month)
                        ->whereYear('date_emission', $year)
                        ->sum('montant_ttc'),
                    'reglements' => \App\Models\Reglement::whereHas('facture', function ($q) use ($tenantId) {
                            $q->where('tenant_id', $tenantId);
                        })
                        ->whereMonth('date_reglement', $month)
                        ->whereYear('date_reglement', $year)
                        ->sum('montant'),
                    'nouveaux_clients' => \App\Models\Client::where('tenant_id', $tenantId)
                        ->whereMonth('created_at', $month)
                        ->whereYear('created_at', $year)
                        ->count(),
                ];
            },
            self::MONTHLY_TTL
        );
    }

    /**
     * Invalider le cache d'un client
     *
     * @param int $clientId
     * @return void
     */
    public static function invalidateClient(int $clientId): void
    {
        Cache::forget("client.{$clientId}.dashboard");
        Cache::forget("client.{$clientId}.factures");
        Cache::forget("client.{$clientId}.contrats");
    }

    /**
     * Invalider le cache admin
     *
     * @param int $tenantId
     * @return void
     */
    public static function invalidateAdmin(int $tenantId): void
    {
        Cache::forget("admin.{$tenantId}.dashboard");

        // Invalider les stats mensuelles de l'année en cours
        $year = now()->year;
        for ($month = 1; $month <= 12; $month++) {
            Cache::forget("admin.{$tenantId}.monthly.{$year}.{$month}");
        }
    }

    /**
     * Invalider tout le cache
     *
     * @return void
     */
    public static function flush(): void
    {
        Cache::flush();
    }

    /**
     * Calculer le taux d'occupation
     *
     * @param int $tenantId
     * @return float
     */
    private static function calculateTauxOccupation(int $tenantId): float
    {
        $total = \App\Models\Box::where('tenant_id', $tenantId)->count();

        if ($total === 0) {
            return 0;
        }

        $occupied = \App\Models\Box::where('tenant_id', $tenantId)
            ->where('statut', 'loue')
            ->count();

        return round(($occupied / $total) * 100, 2);
    }

    /**
     * Récupérer les statistiques de facturation avec cache
     *
     * @param int $tenantId
     * @return array
     */
    public static function getFacturationStats(int $tenantId): array
    {
        return self::remember(
            "admin.{$tenantId}.facturation",
            function () use ($tenantId) {
                $factures = \App\Models\Facture::where('tenant_id', $tenantId);

                return [
                    'total' => $factures->count(),
                    'payees' => $factures->clone()->where('statut', 'payee')->count(),
                    'impayees' => $factures->clone()->where('statut', 'en_retard')->count(),
                    'brouillons' => $factures->clone()->where('statut', 'brouillon')->count(),
                    'montant_total' => $factures->sum('montant_ttc'),
                    'montant_regle' => $factures->sum('montant_regle'),
                    'montant_du' => $factures->clone()
                        ->where('statut', 'en_retard')
                        ->sum('montant_ttc'),
                ];
            },
            self::DASHBOARD_TTL
        );
    }

    /**
     * Récupérer les top clients avec cache
     *
     * @param int $tenantId
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public static function getTopClients(int $tenantId, int $limit = 10)
    {
        return self::remember(
            "admin.{$tenantId}.top_clients.{$limit}",
            function () use ($tenantId, $limit) {
                return \App\Models\Client::where('tenant_id', $tenantId)
                    ->withSum('factures', 'montant_ttc')
                    ->orderByDesc('factures_sum_montant_ttc')
                    ->limit($limit)
                    ->get();
            },
            self::MONTHLY_TTL
        );
    }

    /**
     * Récupérer l'évolution du CA sur 12 mois
     *
     * @param int $tenantId
     * @return array
     */
    public static function getCAEvolution(int $tenantId): array
    {
        return self::remember(
            "admin.{$tenantId}.ca_evolution",
            function () use ($tenantId) {
                $data = [];
                $startDate = now()->subMonths(11)->startOfMonth();

                for ($i = 0; $i < 12; $i++) {
                    $date = $startDate->copy()->addMonths($i);

                    $ca = \App\Models\Facture::where('tenant_id', $tenantId)
                        ->whereMonth('date_emission', $date->month)
                        ->whereYear('date_emission', $date->year)
                        ->sum('montant_ttc');

                    $data[] = [
                        'month' => $date->format('M Y'),
                        'ca' => $ca,
                    ];
                }

                return $data;
            },
            self::YEARLY_TTL
        );
    }
}
