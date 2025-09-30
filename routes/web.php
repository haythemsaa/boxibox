<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProspectController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ContratController;
use App\Http\Controllers\FactureController;
use App\Http\Controllers\ReglementController;
use App\Http\Controllers\BoxController;
use App\Http\Controllers\SepaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StatisticController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::put('/password', [App\Http\Controllers\Auth\PasswordController::class, 'update'])->name('password.update');

    // Gestion Commerciale
    Route::prefix('commercial')->group(function () {
        // Prospects
        Route::resource('prospects', ProspectController::class)
            ->middleware('permission:view_prospects');
        Route::post('prospects/{prospect}/convert', [ProspectController::class, 'convert'])
            ->name('prospects.convert')
            ->middleware('permission:convert_prospects');

        // Clients
        Route::resource('clients', ClientController::class)
            ->middleware('permission:view_clients');
        Route::get('clients/{client}/documents', [ClientController::class, 'documents'])
            ->name('clients.documents')
            ->middleware('permission:view_clients');

        // Contrats
        Route::resource('contrats', ContratController::class)
            ->middleware('permission:view_contrats');
        Route::post('contrats/{contrat}/activate', [ContratController::class, 'activate'])
            ->name('contrats.activate')
            ->middleware('permission:edit_contrats');
        Route::post('contrats/{contrat}/terminate', [ContratController::class, 'terminate'])
            ->name('contrats.terminate')
            ->middleware('permission:edit_contrats');
    });

    // Gestion Financière
    Route::prefix('finance')->group(function () {
        // Factures
        Route::resource('factures', FactureController::class)
            ->middleware('permission:view_factures');
        Route::post('factures/{facture}/send', [FactureController::class, 'send'])
            ->name('factures.send')
            ->middleware('permission:send_factures');
        Route::get('factures/{facture}/pdf', [FactureController::class, 'pdf'])
            ->name('factures.pdf')
            ->middleware('permission:view_factures');
        Route::post('factures/bulk-generate', [FactureController::class, 'bulkGenerate'])
            ->name('factures.bulk-generate')
            ->middleware('permission:create_factures');

        // Règlements
        Route::resource('reglements', ReglementController::class)
            ->middleware('permission:view_reglements');

        // SEPA
        Route::prefix('sepa')->middleware('permission:view_sepa')->group(function () {
            Route::get('/', [SepaController::class, 'index'])->name('sepa.index');
            Route::resource('mandats', SepaController::class, ['except' => ['index']]);
            Route::post('mandats/{mandat}/activate', [SepaController::class, 'activate'])
                ->name('sepa.mandats.activate')
                ->middleware('permission:create_sepa_mandats');
            Route::get('export/{type}', [SepaController::class, 'export'])
                ->name('sepa.export')
                ->middleware('permission:generate_sepa_files');
            Route::post('import-returns', [SepaController::class, 'importReturns'])
                ->name('sepa.import-returns')
                ->middleware('permission:process_sepa_returns');
        });
    });

    // Gestion Technique
    Route::prefix('technique')->group(function () {
        // Boxes
        Route::resource('boxes', BoxController::class)
            ->middleware('permission:view_boxes');
        Route::get('plan', [BoxController::class, 'plan'])
            ->name('boxes.plan')
            ->middleware('permission:manage_box_plan');
        Route::post('boxes/{box}/reserve', [BoxController::class, 'reserve'])
            ->name('boxes.reserve')
            ->middleware('permission:edit_boxes');
        Route::post('boxes/{box}/liberer', [BoxController::class, 'liberer'])
            ->name('boxes.liberer')
            ->middleware('permission:edit_boxes');
    });

    // Administration
    Route::prefix('admin')->middleware('permission:view_users')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('admin.users.index');
        Route::resource('users', UserController::class, ['except' => ['index']]);
        Route::get('statistics', [StatisticController::class, 'index'])
            ->name('admin.statistics')
            ->middleware('permission:view_statistics');
        Route::get('settings', [SettingController::class, 'index'])
            ->name('admin.settings')
            ->middleware('permission:manage_settings');
        Route::post('settings', [SettingController::class, 'update'])
            ->name('admin.settings.update')
            ->middleware('permission:manage_settings');
    });

    // API Routes pour AJAX
    Route::prefix('api')->group(function () {
        Route::get('clients/search', [ClientController::class, 'search'])->name('api.clients.search');
        Route::get('boxes/available', [BoxController::class, 'available'])->name('api.boxes.available');
        Route::get('prospects/stats', [ProspectController::class, 'stats'])->name('api.prospects.stats');
        Route::get('dashboard/charts', [DashboardController::class, 'charts'])->name('api.dashboard.charts');
    });
});

require __DIR__.'/auth.php';