<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', function ($request, $next) {
            if (!$request->user()->isSuperAdmin()) {
                abort(403, 'Accès réservé aux super administrateurs');
            }
            return $next($request);
        }]);
    }

    // Dashboard SuperAdmin
    public function dashboard()
    {
        $stats = [
            'total_tenants' => Tenant::count(),
            'tenants_actifs' => Tenant::active()->count(),
            'tenants_expires' => Tenant::expired()->count(),
            'tenants_suspendus' => Tenant::suspended()->count(),
            'ca_mensuel_total' => Tenant::active()->sum('prix_mensuel'),
            'total_boxes' => \App\Models\Box::count(),
            'total_users' => User::where('type_user', '!=', 'superadmin')->count(),
        ];

        $tenants = Tenant::with('users')
            ->withCount(['boxes', 'clients', 'contrats'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('superadmin.dashboard', compact('stats', 'tenants'));
    }

    // Liste des tenants
    public function index(Request $request)
    {
        $query = Tenant::withCount(['boxes', 'clients', 'users']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nom_entreprise', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('slug', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('plan')) {
            $query->where('plan', $request->plan);
        }

        if ($request->filled('statut')) {
            $query->where('statut_abonnement', $request->statut);
        }

        $tenants = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('superadmin.tenants.index', compact('tenants'));
    }

    // Afficher un tenant
    public function show(Tenant $tenant)
    {
        $tenant->load(['users', 'boxes', 'clients', 'contrats', 'factures']);

        $stats = [
            'total_boxes' => $tenant->boxes()->count(),
            'boxes_occupees' => $tenant->boxes()->where('statut', 'occupe')->count(),
            'total_clients' => $tenant->clients()->count(),
            'total_contrats' => $tenant->contrats()->count(),
            'contrats_actifs' => $tenant->contrats()->where('statut', 'actif')->count(),
            'ca_mensuel' => $tenant->contrats()->where('statut', 'actif')->sum('montant_loyer'),
            'total_factures' => $tenant->factures()->count(),
            'factures_impayees' => $tenant->factures()->where('statut', 'impaye')->count(),
        ];

        return view('superadmin.tenants.show', compact('tenant', 'stats'));
    }

    // Créer un nouveau tenant
    public function create()
    {
        return view('superadmin.tenants.create');
    }

    // Enregistrer le nouveau tenant
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom_entreprise' => 'required|string|max:255',
            'email' => 'required|email|unique:tenants,email',
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string',
            'code_postal' => 'nullable|string|max:10',
            'ville' => 'nullable|string|max:255',
            'pays' => 'nullable|string|max:255',
            'siret' => 'nullable|string|max:14',
            'plan' => 'required|in:gratuit,starter,business,enterprise',
            'prix_mensuel' => 'required|numeric|min:0',
            'max_boxes' => 'required|integer|min:1',
            'max_users' => 'required|integer|min:1',
            'date_debut_abonnement' => 'required|date',
            'date_fin_abonnement' => 'nullable|date|after:date_debut_abonnement',

            // Admin user
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8|confirmed',
        ]);

        // Create tenant
        $tenant = Tenant::create([
            'nom_entreprise' => $validated['nom_entreprise'],
            'email' => $validated['email'],
            'telephone' => $validated['telephone'] ?? null,
            'adresse' => $validated['adresse'] ?? null,
            'code_postal' => $validated['code_postal'] ?? null,
            'ville' => $validated['ville'] ?? null,
            'pays' => $validated['pays'] ?? 'France',
            'siret' => $validated['siret'] ?? null,
            'plan' => $validated['plan'],
            'prix_mensuel' => $validated['prix_mensuel'],
            'max_boxes' => $validated['max_boxes'],
            'max_users' => $validated['max_users'],
            'date_debut_abonnement' => $validated['date_debut_abonnement'],
            'date_fin_abonnement' => $validated['date_fin_abonnement'] ?? null,
            'statut_abonnement' => 'actif',
            'is_active' => true,
        ]);

        // Create admin user for this tenant
        $adminUser = User::create([
            'name' => $validated['admin_name'],
            'email' => $validated['admin_email'],
            'password' => Hash::make($validated['admin_password']),
            'tenant_id' => $tenant->id,
            'type_user' => 'admin_tenant',
            'is_active' => true,
        ]);

        return redirect()->route('superadmin.tenants.show', $tenant)
            ->with('success', 'Tenant créé avec succès.');
    }

    // Éditer un tenant
    public function edit(Tenant $tenant)
    {
        return view('superadmin.tenants.edit', compact('tenant'));
    }

    // Mettre à jour un tenant
    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'nom_entreprise' => 'required|string|max:255',
            'email' => 'required|email|unique:tenants,email,' . $tenant->id,
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string',
            'code_postal' => 'nullable|string|max:10',
            'ville' => 'nullable|string|max:255',
            'pays' => 'nullable|string|max:255',
            'siret' => 'nullable|string|max:14',
            'plan' => 'required|in:gratuit,starter,business,enterprise',
            'prix_mensuel' => 'required|numeric|min:0',
            'max_boxes' => 'required|integer|min:1',
            'max_users' => 'required|integer|min:1',
            'date_debut_abonnement' => 'required|date',
            'date_fin_abonnement' => 'nullable|date|after:date_debut_abonnement',
            'statut_abonnement' => 'required|in:actif,suspendu,expire,annule',
            'is_active' => 'required|boolean',
        ]);

        $tenant->update($validated);

        return redirect()->route('superadmin.tenants.show', $tenant)
            ->with('success', 'Tenant mis à jour avec succès.');
    }

    // Supprimer un tenant
    public function destroy(Tenant $tenant)
    {
        if ($tenant->statut_abonnement === 'actif') {
            return redirect()->route('superadmin.tenants.index')
                ->with('error', 'Impossible de supprimer un tenant actif. Veuillez d\'abord suspendre ou annuler l\'abonnement.');
        }

        $tenant->delete();

        return redirect()->route('superadmin.tenants.index')
            ->with('success', 'Tenant supprimé avec succès.');
    }

    // Suspendre un tenant
    public function suspend(Tenant $tenant)
    {
        $tenant->update([
            'statut_abonnement' => 'suspendu',
            'is_active' => false,
        ]);

        return redirect()->route('superadmin.tenants.show', $tenant)
            ->with('success', 'Tenant suspendu avec succès.');
    }

    // Activer un tenant
    public function activate(Tenant $tenant)
    {
        $tenant->update([
            'statut_abonnement' => 'actif',
            'is_active' => true,
        ]);

        return redirect()->route('superadmin.tenants.show', $tenant)
            ->with('success', 'Tenant activé avec succès.');
    }

    // Statistiques
    public function stats()
    {
        $stats = [
            'total_tenants' => Tenant::count(),
            'tenants_actifs' => Tenant::active()->count(),
            'tenants_expires' => Tenant::expired()->count(),
            'tenants_suspendus' => Tenant::suspended()->count(),
            'ca_mensuel_total' => Tenant::active()->sum('prix_mensuel'),
            'ca_annuel_estime' => Tenant::active()->sum('prix_mensuel') * 12,
            'total_boxes' => \App\Models\Box::count(),
            'total_clients' => \App\Models\Client::count(),
            'total_users' => User::where('type_user', '!=', 'superadmin')->count(),
        ];

        $tenantsByPlan = Tenant::selectRaw('plan, COUNT(*) as count')
            ->groupBy('plan')
            ->pluck('count', 'plan');

        $tenantsByStatus = Tenant::selectRaw('statut_abonnement, COUNT(*) as count')
            ->groupBy('statut_abonnement')
            ->pluck('count', 'statut_abonnement');

        $recentTenants = Tenant::orderBy('created_at', 'desc')->take(10)->get();

        return view('superadmin.stats', compact('stats', 'tenantsByPlan', 'tenantsByStatus', 'recentTenants'));
    }
}
