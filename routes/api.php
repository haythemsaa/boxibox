<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AccessController;
use App\Http\Controllers\Api\Mobile\AuthController;
use App\Http\Controllers\Api\Mobile\ClientDashboardController;
use App\Http\Controllers\Api\Mobile\FactureController;
use App\Http\Controllers\Api\Mobile\ContratController;
use App\Http\Controllers\Api\Mobile\PaymentController;
use App\Http\Controllers\Api\Mobile\ChatController;

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

/*
|--------------------------------------------------------------------------
| API Mobile Client Routes
|--------------------------------------------------------------------------
|
| Routes API pour l'application mobile des clients
|
*/

Route::prefix('mobile/v1')->group(function () {

    // Authentification (sans token)
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    });

    // Routes protégées (avec token Sanctum)
    Route::middleware('auth:sanctum')->group(function () {

        // Authentification
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/profile', [AuthController::class, 'profile']);
            Route::put('/profile', [AuthController::class, 'updateProfile']);
            Route::post('/change-password', [AuthController::class, 'changePassword']);
        });

        // Dashboard
        Route::get('/dashboard', [ClientDashboardController::class, 'index']);
        Route::get('/notifications', [ClientDashboardController::class, 'notifications']);
        Route::post('/notifications/{id}/mark-read', [ClientDashboardController::class, 'markNotificationAsRead']);

        // Factures
        Route::prefix('factures')->group(function () {
            Route::get('/', [FactureController::class, 'index']);
            Route::get('/{id}', [FactureController::class, 'show']);
            Route::get('/{id}/pdf', [FactureController::class, 'downloadPdf']);
        });

        // Contrats
        Route::prefix('contrats')->group(function () {
            Route::get('/', [ContratController::class, 'index']);
            Route::get('/{id}', [ContratController::class, 'show']);
        });

        // Paiements
        Route::prefix('payments')->group(function () {
            Route::post('/create-intent', [PaymentController::class, 'createPaymentIntent']);
            Route::post('/confirm', [PaymentController::class, 'confirmPayment']);
            Route::get('/history', [PaymentController::class, 'history']);
        });

        // Chat
        Route::prefix('chat')->group(function () {
            Route::post('/send', [ChatController::class, 'sendMessage']);
            Route::get('/messages', [ChatController::class, 'getMessages']);
            Route::post('/mark-read/{id}', [ChatController::class, 'markAsRead']);
        });
    });
});