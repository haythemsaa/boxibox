<?php

namespace App\Observers;

use App\Models\Facture;
use App\Services\StatisticsCache;

class FactureObserver
{
    /**
     * Handle the Facture "created" event.
     */
    public function created(Facture $facture): void
    {
        $this->invalidateCache($facture);
    }

    /**
     * Handle the Facture "updated" event.
     */
    public function updated(Facture $facture): void
    {
        $this->invalidateCache($facture);
    }

    /**
     * Handle the Facture "deleted" event.
     */
    public function deleted(Facture $facture): void
    {
        $this->invalidateCache($facture);
    }

    /**
     * Handle the Facture "restored" event.
     */
    public function restored(Facture $facture): void
    {
        $this->invalidateCache($facture);
    }

    /**
     * Handle the Facture "force deleted" event.
     */
    public function forceDeleted(Facture $facture): void
    {
        $this->invalidateCache($facture);
    }

    /**
     * Invalider le cache lié à la facture
     */
    private function invalidateCache(Facture $facture): void
    {
        // Invalider le cache du client
        if ($facture->client_id) {
            StatisticsCache::invalidateClient($facture->client_id);
        }

        // Invalider le cache admin du tenant
        if ($facture->tenant_id) {
            StatisticsCache::invalidateAdmin($facture->tenant_id);
        }
    }
}
