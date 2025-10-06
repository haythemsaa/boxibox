<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccessLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AccessController extends Controller
{
    /**
     * Vérifier un code PIN
     */
    public function verifyPin(Request $request)
    {
        // Rate limiting: 5 tentatives par minute
        $key = 'verify-pin:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json([
                'success' => false,
                'message' => 'Trop de tentatives. Veuillez réessayer dans ' . RateLimiter::availableIn($key) . ' secondes.',
            ], 429);
        }

        RateLimiter::hit($key, 60);

        $validated = $request->validate([
            'pin' => 'required|string|size:6',
            'box_id' => 'nullable|exists:boxes,id',
            'type_acces' => 'required|in:entree,sortie',
            'terminal_id' => 'nullable|string',
            'emplacement' => 'nullable|string',
        ]);

        $log = AccessLog::verifyAndLogPinAccess(
            $validated['pin'],
            $validated['box_id'] ?? null,
            $validated['type_acces']
        );

        // Enrichir le log avec les infos du terminal
        if (isset($validated['terminal_id'])) {
            $log->update([
                'terminal_id' => $validated['terminal_id'],
                'emplacement' => $validated['emplacement'] ?? null,
            ]);
        }

        if ($log->statut === 'autorise') {
            RateLimiter::clear($key); // Réinitialiser le compteur si succès

            return response()->json([
                'success' => true,
                'message' => 'Accès autorisé',
                'data' => [
                    'log_id' => $log->id,
                    'client' => $log->client ? [
                        'nom' => $log->client->nom,
                        'prenom' => $log->client->prenom,
                    ] : null,
                    'box' => $log->box ? [
                        'numero' => $log->box->numero,
                    ] : null,
                ],
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => $log->raison_refus,
            'data' => [
                'log_id' => $log->id,
            ],
        ], 403);
    }

    /**
     * Vérifier un QR code
     */
    public function verifyQr(Request $request)
    {
        // Rate limiting: 5 tentatives par minute
        $key = 'verify-qr:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json([
                'success' => false,
                'message' => 'Trop de tentatives. Veuillez réessayer dans ' . RateLimiter::availableIn($key) . ' secondes.',
            ], 429);
        }

        RateLimiter::hit($key, 60);

        $validated = $request->validate([
            'qr_data' => 'required|string',
            'box_id' => 'nullable|exists:boxes,id',
            'type_acces' => 'required|in:entree,sortie',
            'terminal_id' => 'nullable|string',
            'emplacement' => 'nullable|string',
        ]);

        $log = AccessLog::verifyAndLogQrAccess(
            $validated['qr_data'],
            $validated['box_id'] ?? null,
            $validated['type_acces']
        );

        // Enrichir le log avec les infos du terminal
        if (isset($validated['terminal_id'])) {
            $log->update([
                'terminal_id' => $validated['terminal_id'],
                'emplacement' => $validated['emplacement'] ?? null,
            ]);
        }

        if ($log->statut === 'autorise') {
            RateLimiter::clear($key); // Réinitialiser le compteur si succès

            return response()->json([
                'success' => true,
                'message' => 'Accès autorisé',
                'data' => [
                    'log_id' => $log->id,
                    'client' => $log->client ? [
                        'nom' => $log->client->nom,
                        'prenom' => $log->client->prenom,
                    ] : null,
                    'box' => $log->box ? [
                        'numero' => $log->box->numero,
                    ] : null,
                ],
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => $log->raison_refus,
            'data' => [
                'log_id' => $log->id,
            ],
        ], 403);
    }

    /**
     * Récupérer les logs d'un terminal
     */
    public function getLogs(Request $request)
    {
        $validated = $request->validate([
            'terminal_id' => 'required|string',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $logs = AccessLog::where('terminal_id', $validated['terminal_id'])
            ->with(['client', 'box'])
            ->orderBy('date_heure', 'desc')
            ->limit($validated['limit'] ?? 50)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'date_heure' => $log->date_heure->toIso8601String(),
                    'client' => $log->client ? [
                        'nom' => $log->client->nom,
                        'prenom' => $log->client->prenom,
                    ] : null,
                    'box' => $log->box ? [
                        'numero' => $log->box->numero,
                    ] : null,
                    'type_acces' => $log->type_acces,
                    'methode' => $log->methode,
                    'statut' => $log->statut,
                    'raison_refus' => $log->raison_refus,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $logs,
        ], 200);
    }

    /**
     * Heartbeat pour vérifier la connexion
     */
    public function heartbeat(Request $request)
    {
        $validated = $request->validate([
            'terminal_id' => 'required|string',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Terminal connecté',
            'server_time' => now()->toIso8601String(),
            'terminal_id' => $validated['terminal_id'],
        ], 200);
    }
}
