<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AccessController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Routes API pour les terminaux d'accès et intégrations externes
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Routes API pour les terminaux d'accès (authentification par token Sanctum)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    // Vérification d'accès
    Route::post('/access/verify-pin', [AccessController::class, 'verifyPin']);
    Route::post('/access/verify-qr', [AccessController::class, 'verifyQr']);

    // Logs et monitoring
    Route::get('/access/logs', [AccessController::class, 'getLogs']);
    Route::post('/access/heartbeat', [AccessController::class, 'heartbeat']);
});

// Route de test API (sans authentification pour tester)
Route::prefix('v1/test')->group(function () {
    Route::post('/ping', function () {
        return response()->json([
            'success' => true,
            'message' => 'API Boxibox opérationnelle',
            'timestamp' => now()->toIso8601String(),
            'version' => '1.0.0',
        ]);
    });
});