<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardAdvancedController;
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
use App\Http\Controllers\SignatureController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\ClientPortalController;
use App\Http\Controllers\Client\ClientNotificationController;
use App\Http\Controllers\Client\ClientChatController;
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
    Route::get('/dashboard/advanced', [DashboardAdvancedController::class, 'index'])
        ->name('admin.dashboard.advanced')
        ->middleware('permission:view_statistics');
    Route::get('/dashboard/advanced/export', [DashboardAdvancedController::class, 'export'])
        ->name('admin.dashboard.export')
        ->middleware('permission:view_statistics');

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
        Route::get('plan/editor', [BoxController::class, 'planEditor'])
            ->name('boxes.plan.editor')
            ->middleware('permission:manage_box_plan');
        Route::get('plan/editor-advanced', [BoxController::class, 'planEditorAdvanced'])
            ->name('boxes.plan.editor.advanced')
            ->middleware('permission:manage_box_plan');
        Route::get('plan/designer', [BoxController::class, 'floorPlanDesigner'])
            ->name('boxes.floorplan.designer')
            ->middleware('permission:manage_box_plan');
        Route::post('plan/save', [BoxController::class, 'savePlan'])
            ->name('boxes.plan.save')
            ->middleware('permission:manage_box_plan');
        Route::post('plan/floorplan-save', [BoxController::class, 'saveFloorPlan'])
            ->name('boxes.floorplan.save')
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

    // Super Admin - Gestion Multi-Tenant
    Route::prefix('superadmin')->middleware('auth')->group(function () {
        Route::get('dashboard', [SuperAdminController::class, 'dashboard'])->name('superadmin.dashboard');
        Route::get('stats', [SuperAdminController::class, 'stats'])->name('superadmin.stats');

        Route::prefix('tenants')->name('superadmin.tenants.')->group(function () {
            Route::get('/', [SuperAdminController::class, 'index'])->name('index');
            Route::get('create', [SuperAdminController::class, 'create'])->name('create');
            Route::post('/', [SuperAdminController::class, 'store'])->name('store');
            Route::get('{tenant}', [SuperAdminController::class, 'show'])->name('show');
            Route::get('{tenant}/edit', [SuperAdminController::class, 'edit'])->name('edit');
            Route::put('{tenant}', [SuperAdminController::class, 'update'])->name('update');
            Route::delete('{tenant}', [SuperAdminController::class, 'destroy'])->name('destroy');
            Route::post('{tenant}/suspend', [SuperAdminController::class, 'suspend'])->name('suspend');
            Route::post('{tenant}/activate', [SuperAdminController::class, 'activate'])->name('activate');
        });
    });

    // Client Portal - Espace Client Final
    Route::prefix('client')->middleware('auth')->group(function () {
        // Dashboard
        Route::get('dashboard', [ClientPortalController::class, 'dashboard'])->name('client.dashboard');

        // Contrats
        Route::get('contrats', [ClientPortalController::class, 'contrats'])->name('client.contrats');
        Route::get('contrats/{contrat}', [ClientPortalController::class, 'contratShow'])->name('client.contrats.show');
        Route::get('contrats/{contrat}/pdf', [ClientPortalController::class, 'contratPdf'])->name('client.contrats.pdf');

        // Mandats SEPA
        Route::get('sepa', [ClientPortalController::class, 'sepa'])->name('client.sepa');
        Route::get('sepa/create', [ClientPortalController::class, 'sepaCreate'])->name('client.sepa.create');
        Route::post('sepa', [ClientPortalController::class, 'sepaStore'])->name('client.sepa.store');
        Route::get('sepa/{mandat}/pdf', [ClientPortalController::class, 'sepaPdf'])->name('client.sepa.pdf');

        // Profil / Informations
        Route::get('profil', [ClientPortalController::class, 'profil'])->name('client.profil');
        Route::put('profil', [ClientPortalController::class, 'updateProfil'])->name('client.profil.update');

        // Factures et Avoirs
        Route::get('factures', [ClientPortalController::class, 'factures'])->name('client.factures');
        Route::get('factures/{facture}', [ClientPortalController::class, 'factureShow'])->name('client.factures.show');
        Route::get('factures/{facture}/pdf', [ClientPortalController::class, 'facturePdf'])->name('client.factures.pdf');

        // Règlements
        Route::get('reglements', [ClientPortalController::class, 'reglements'])->name('client.reglements');

        // Relances
        Route::get('relances', [ClientPortalController::class, 'relances'])->name('client.relances');

        // Fichiers / Documents
        Route::get('documents', [ClientPortalController::class, 'documents'])->name('client.documents');
        Route::post('documents/upload', [ClientPortalController::class, 'documentUpload'])->name('client.documents.upload');
        Route::get('documents/{document}/download', [ClientPortalController::class, 'documentDownload'])->name('client.documents.download');
        Route::delete('documents/{document}', [ClientPortalController::class, 'documentDelete'])->name('client.documents.delete');

        // Suivi
        Route::get('suivi', [ClientPortalController::class, 'suivi'])->name('client.suivi');

        // Plan des Boxes
        Route::get('box-plan', [ClientPortalController::class, 'boxPlan'])->name('client.boxplan');

        // Notifications
        Route::get('notifications', [ClientNotificationController::class, 'index'])->name('client.notifications');
        Route::post('notifications/{notification}/mark-read', [ClientNotificationController::class, 'markRead'])->name('client.notifications.mark-read');
        Route::post('notifications/mark-all-read', [ClientNotificationController::class, 'markAllRead'])->name('client.notifications.mark-all-read');

        // Chat
        Route::post('chat/send', [ClientChatController::class, 'send'])->name('client.chat.send');
        Route::post('chat/mark-all-read', [ClientChatController::class, 'markAllRead'])->name('client.chat.mark-all-read');
    });

    // Signatures Électroniques
    Route::prefix('signatures')->group(function () {
        // Routes admin (nécessitent auth)
        Route::get('/', [SignatureController::class, 'index'])
            ->name('signatures.index')
            ->middleware('permission:view_signatures');

        // Demande de signature pour contrat
        Route::post('contrats/{contrat}/request', [SignatureController::class, 'requestContractSignature'])
            ->name('signatures.request-contract')
            ->middleware('permission:create_signatures');

        // Demande de signature pour mandat SEPA
        Route::post('sepa/{mandat}/request', [SignatureController::class, 'requestSepaSignature'])
            ->name('signatures.request-sepa')
            ->middleware('permission:create_signatures');

        // Renvoyer un email de signature
        Route::post('{signature}/resend', [SignatureController::class, 'resend'])
            ->name('signatures.resend')
            ->middleware('permission:create_signatures');

        // Annuler une demande de signature
        Route::post('{signature}/cancel', [SignatureController::class, 'cancel'])
            ->name('signatures.cancel')
            ->middleware('permission:delete_signatures');
    });

    // API Routes pour AJAX
    Route::prefix('api')->group(function () {
        Route::get('clients/search', [ClientController::class, 'search'])->name('api.clients.search');
        Route::get('boxes/available', [BoxController::class, 'available'])->name('api.boxes.available');
        Route::get('prospects/stats', [ProspectController::class, 'stats'])->name('api.prospects.stats');
        Route::get('dashboard/charts', [DashboardController::class, 'charts'])->name('api.dashboard.charts');
    });
});

// Routes publiques pour la signature (ne nécessitent pas d'authentification)
Route::prefix('sign')->group(function () {
    Route::get('{token}', [SignatureController::class, 'show'])->name('signatures.show');
    Route::post('{token}', [SignatureController::class, 'sign'])->name('signatures.sign');
    Route::post('{token}/refuse', [SignatureController::class, 'refuse'])->name('signatures.refuse');
});

// Routes publiques pour la réservation en ligne (ne nécessitent pas d'authentification)
Route::prefix('reservation')->name('public.booking.')->group(function () {
    Route::get('/', [App\Http\Controllers\PublicBookingController::class, 'index'])->name('index');
    Route::get('/famille/{famille}', [App\Http\Controllers\PublicBookingController::class, 'showFamille'])->name('famille');
    Route::get('/box/{box}', [App\Http\Controllers\PublicBookingController::class, 'bookingForm'])->name('form');
    Route::post('/box/{box}/reserver', [App\Http\Controllers\PublicBookingController::class, 'processBooking'])->name('process');
    Route::get('/paiement/{contrat}', [App\Http\Controllers\PublicBookingController::class, 'payment'])->name('payment');
    Route::get('/confirmation/{contrat}', [App\Http\Controllers\PublicBookingController::class, 'confirmation'])->name('confirmation');
    Route::post('/api/calculer-prix', [App\Http\Controllers\PublicBookingController::class, 'calculatePrice'])->name('calculate-price');
});

// Routes pour les notifications (nécessitent authentification)
Route::middleware(['auth'])->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [App\Http\Controllers\NotificationController::class, 'index'])->name('index');
    Route::get('/unread', [App\Http\Controllers\NotificationController::class, 'getUnread'])->name('getUnread');
    Route::post('/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('markAsRead');
    Route::post('/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('markAllAsRead');
    Route::delete('/{id}', [App\Http\Controllers\NotificationController::class, 'destroy'])->name('destroy');
    Route::get('/settings', [App\Http\Controllers\NotificationController::class, 'settings'])->name('settings');
    Route::put('/settings', [App\Http\Controllers\NotificationController::class, 'updateSettings'])->name('updateSettings');
});

// Routes pour les rapports (nécessitent authentification et permission)
Route::middleware(['auth'])->prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [App\Http\Controllers\ReportController::class, 'index'])->name('index')->middleware('permission:view_statistics');
    Route::get('/financial', [App\Http\Controllers\ReportController::class, 'financial'])->name('financial')->middleware('permission:view_statistics');
    Route::get('/occupation', [App\Http\Controllers\ReportController::class, 'occupation'])->name('occupation')->middleware('permission:view_statistics');
    Route::get('/clients', [App\Http\Controllers\ReportController::class, 'clients'])->name('clients')->middleware('permission:view_statistics');
    Route::get('/access', [App\Http\Controllers\ReportController::class, 'access'])->name('access')->middleware('permission:view_statistics');
    Route::get('/export-pdf', [App\Http\Controllers\ReportController::class, 'exportPDF'])->name('exportPDF')->middleware('permission:view_statistics');
    Route::get('/export-excel', [App\Http\Controllers\ReportController::class, 'exportExcel'])->name('exportExcel')->middleware('permission:view_statistics');
});

// Routes pour la gestion des codes d'accès
Route::middleware(['auth'])->prefix('access-codes')->name('access-codes.')->group(function () {
    Route::get('/', [App\Http\Controllers\AccessCodeController::class, 'index'])->name('index')->middleware('permission:view_boxes');
    Route::get('/create', [App\Http\Controllers\AccessCodeController::class, 'create'])->name('create')->middleware('permission:create_boxes');
    Route::post('/', [App\Http\Controllers\AccessCodeController::class, 'store'])->name('store')->middleware('permission:create_boxes');
    Route::get('/{accessCode}', [App\Http\Controllers\AccessCodeController::class, 'show'])->name('show')->middleware('permission:view_boxes');
    Route::get('/{accessCode}/edit', [App\Http\Controllers\AccessCodeController::class, 'edit'])->name('edit')->middleware('permission:edit_boxes');
    Route::put('/{accessCode}', [App\Http\Controllers\AccessCodeController::class, 'update'])->name('update')->middleware('permission:edit_boxes');
    Route::post('/{accessCode}/revoke', [App\Http\Controllers\AccessCodeController::class, 'revoke'])->name('revoke')->middleware('permission:delete_boxes');
    Route::post('/{accessCode}/suspend', [App\Http\Controllers\AccessCodeController::class, 'suspend'])->name('suspend')->middleware('permission:edit_boxes');
    Route::post('/{accessCode}/reactivate', [App\Http\Controllers\AccessCodeController::class, 'reactivate'])->name('reactivate')->middleware('permission:edit_boxes');
    Route::get('/{accessCode}/download-qr', [App\Http\Controllers\AccessCodeController::class, 'downloadQR'])->name('download-qr')->middleware('permission:view_boxes');
});

require __DIR__.'/auth.php';
// Route de test Vue.js (TEMPORAIRE - à supprimer en production)
Route::get('/test-vue', function () {
    return \Inertia\Inertia::render('Client/Dashboard', [
        'stats' => [
            'contrats_actifs' => 3,
            'factures_impayees' => 2,
            'montant_du' => 1500.00,
            'documents' => 5,
            'mandat_sepa_actif' => true
        ],
    ]);
});
