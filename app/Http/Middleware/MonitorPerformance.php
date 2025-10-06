<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class MonitorPerformance
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Temps de début
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        // Exécuter la requête
        $response = $next($request);

        // Calculer les métriques
        $executionTime = (microtime(true) - $startTime) * 1000; // en ms
        $memoryUsed = (memory_get_usage() - $startMemory) / 1024 / 1024; // en MB

        // Logger si la requête est lente (> 1 seconde)
        if ($executionTime > 1000) {
            Log::warning('Requête lente détectée', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'execution_time' => round($executionTime, 2) . ' ms',
                'memory_used' => round($memoryUsed, 2) . ' MB',
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
            ]);
        }

        // Ajouter des headers de debug en environnement local
        if (app()->environment('local')) {
            $response->headers->set('X-Execution-Time', round($executionTime, 2) . 'ms');
            $response->headers->set('X-Memory-Used', round($memoryUsed, 2) . 'MB');
            $response->headers->set('X-Query-Count', \DB::getQueryLog() ? count(\DB::getQueryLog()) : 0);
        }

        return $response;
    }
}
