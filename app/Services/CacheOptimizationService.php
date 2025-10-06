<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Box;
use App\Models\Client;
use App\Models\Contrat;
use App\Models\Facture;

class CacheOptimizationService
{
    /**
     * Durées de cache par défaut (en minutes)
     */
    const CACHE_SHORT = 5;      // 5 minutes - données très dynamiques
    const CACHE_MEDIUM = 30;    // 30 minutes - données moyennement dynamiques
    const CACHE_LONG = 1440;    // 24 heures - données quasi-statiques

    /**
     * Récupère les statistiques du dashboard avec cache
     */
    public function getDashboardStats(int $tenantId)
    {
        return Cache::remember("dashboard_stats_{$tenantId}", self::CACHE_MEDIUM, function () use ($tenantId) {
            return [
                'total_boxes' => Box::where('tenant_id', $tenantId)->count(),
                'boxes_occupees' => Box::where('tenant_id', $tenantId)->where('statut', 'occupe')->count(),
                'boxes_libres' => Box::where('tenant_id', $tenantId)->where('statut', 'libre')->count(),
                'total_clients' => Client::where('tenant_id', $tenantId)->where('statut', 'actif')->count(),
                'contrats_actifs' => Contrat::where('tenant_id', $tenantId)->where('statut', 'actif')->count(),
                'ca_mois' => Facture::where('tenant_id', $tenantId)
                    ->whereMonth('date_emission', now()->month)
                    ->whereYear('date_emission', now()->year)
                    ->sum('montant_total'),
                'factures_impayees' => Facture::where('tenant_id', $tenantId)
                    ->where('statut', 'impayee')
                    ->count(),
            ];
        });
    }

    /**
     * Récupère la liste des boxes avec cache
     */
    public function getBoxesList(int $tenantId, array $filters = [])
    {
        $cacheKey = "boxes_list_{$tenantId}_" . md5(json_encode($filters));

        return Cache::remember($cacheKey, self::CACHE_SHORT, function () use ($tenantId, $filters) {
            $query = Box::where('tenant_id', $tenantId)->with(['famille', 'emplacement', 'contrat_actif']);

            if (!empty($filters['statut'])) {
                $query->where('statut', $filters['statut']);
            }

            if (!empty($filters['emplacement_id'])) {
                $query->where('emplacement_id', $filters['emplacement_id']);
            }

            return $query->get();
        });
    }

    /**
     * Récupère les données client avec cache
     */
    public function getClientData(int $clientId)
    {
        return Cache::remember("client_data_{$clientId}", self::CACHE_MEDIUM, function () use ($clientId) {
            return Client::with(['contrats.box', 'factures.reglements'])
                ->findOrFail($clientId);
        });
    }

    /**
     * Invalide le cache du dashboard
     */
    public function invalidateDashboardCache(int $tenantId)
    {
        Cache::forget("dashboard_stats_{$tenantId}");
    }

    /**
     * Invalide le cache des boxes
     */
    public function invalidateBoxesCache(int $tenantId)
    {
        // Utilise un pattern pour supprimer tous les caches boxes
        $keys = Cache::get('boxes_cache_keys_' . $tenantId, []);
        foreach ($keys as $key) {
            Cache::forget($key);
        }
        Cache::forget('boxes_cache_keys_' . $tenantId);
    }

    /**
     * Invalide le cache d'un client
     */
    public function invalidateClientCache(int $clientId)
    {
        Cache::forget("client_data_{$clientId}");
    }

    /**
     * Précharge les données essentielles en cache
     */
    public function warmupCache(int $tenantId)
    {
        // Dashboard stats
        $this->getDashboardStats($tenantId);

        // Liste des boxes (sans filtre)
        $this->getBoxesList($tenantId);

        return true;
    }

    /**
     * Vide tout le cache de l'application
     */
    public function clearAllCache()
    {
        Cache::flush();
        return true;
    }

    /**
     * Récupère les métriques de performance du cache
     */
    public function getCacheMetrics()
    {
        // Si Redis est disponible
        if (config('cache.default') === 'redis') {
            try {
                $redis = Cache::getRedis();
                $info = $redis->info();

                return [
                    'driver' => 'redis',
                    'memory_used' => $info['used_memory_human'] ?? 'N/A',
                    'keys_count' => $redis->dbSize(),
                    'hits' => $info['keyspace_hits'] ?? 0,
                    'misses' => $info['keyspace_misses'] ?? 0,
                    'hit_rate' => $this->calculateHitRate($info),
                ];
            } catch (\Exception $e) {
                return ['error' => 'Redis non disponible'];
            }
        }

        return [
            'driver' => config('cache.default'),
            'message' => 'Métriques disponibles uniquement avec Redis'
        ];
    }

    /**
     * Calcule le taux de réussite du cache
     */
    private function calculateHitRate(array $info): string
    {
        $hits = $info['keyspace_hits'] ?? 0;
        $misses = $info['keyspace_misses'] ?? 0;
        $total = $hits + $misses;

        if ($total === 0) {
            return '0%';
        }

        return round(($hits / $total) * 100, 2) . '%';
    }
}
