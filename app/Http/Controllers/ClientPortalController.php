<?php

namespace App\Http\Controllers;

use App\Models\Contrat;
use App\Models\Facture;
use App\Models\Reglement;
use App\Models\Document;
use App\Models\MandatSepa;
use App\Models\Client;
use App\Models\Rappel;
use App\Models\ClientDocument;
use App\Models\ClientNotification;
use App\Models\ChatMessage;
use App\Services\StatisticsCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ClientPortalController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', function ($request, $next) {
            if (!$request->user()->isClientFinal()) {
                abort(403, 'Accès réservé aux clients');
            }
            return $next($request);
        }]);
    }

    private function getClient()
    {
        $user = Auth::user();
        return Client::find($user->client_id);
    }

    // 1. DASHBOARD
    public function dashboard()
    {
        $client = $this->getClient();
        if (!$client) abort(404, 'Client non trouvé');

        // Utiliser le cache pour les statistiques (TTL: 5 minutes)
        $stats = StatisticsCache::getClientDashboardStats($client->id);

        // Ces données changent plus fréquemment, pas de cache
        $contratsActifs = $client->contrats()
            ->where('statut', 'actif')
            ->with(['box.famille', 'box.emplacement'])
            ->take(5)
            ->get();

        $dernieresFactures = $client->factures()
            ->orderBy('date_emission', 'desc')
            ->take(5)
            ->get();

        // Notifications (les 10 dernières pour le badge)
        $notifications = ClientNotification::where('client_id', $client->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Messages du chat
        $chatMessages = ChatMessage::where('client_id', $client->id)
            ->orderBy('created_at', 'asc')
            ->get();

        // Activités récentes (simulées pour l'instant - à implémenter plus tard)
        $recentActivities = [];

        return Inertia::render('Client/Dashboard', [
            'stats' => $stats,
            'contratsActifs' => $contratsActifs,
            'dernieresFactures' => $dernieresFactures,
            'notifications' => $notifications,
            'chatMessages' => $chatMessages,
            'recentActivities' => $recentActivities,
        ]);
    }

    // 2. CONTRATS - Liste avec filtres et colonnes avancées
    public function contrats(Request $request)
    {
        $client = $this->getClient();
        if (!$client) abort(404, 'Client non trouvé');

        $query = $client->contrats()->with(['box.famille', 'box.emplacement']);

        // Filtres
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('numero_contrat', 'LIKE', "%{$search}%")
                  ->orWhereHas('box', function($boxQuery) use ($search) {
                      $boxQuery->where('numero', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        // Tri
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $contrats = $query->paginate(15);

        return Inertia::render('Client/Contrats', [
            'contrats' => $contrats,
            'queryParams' => $request->only(['search', 'statut', 'sort_by', 'sort_order'])
        ]);
    }

    public function contratShow(Contrat $contrat)
    {
        $client = $this->getClient();
        if ($contrat->client_id !== $client->id) abort(403);

        $contrat->load(['box.famille', 'box.emplacement', 'factures.reglements']);

        return Inertia::render('Client/ContratShow', [
            'contrat' => $contrat
        ]);
    }

    public function contratPdf(Contrat $contrat)
    {
        $client = $this->getClient();
        if ($contrat->client_id !== $client->id) abort(403);

        $contrat->load(['box.famille', 'box.emplacement', 'client']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.contrat', compact('contrat', 'client'));

        return $pdf->download('contrat_' . $contrat->numero_contrat . '.pdf');
    }

    // 3. MANDATS SEPA - Gestion et signature électronique
    public function sepa()
    {
        $client = $this->getClient();
        if (!$client) abort(404);

        $mandats = $client->mandatsSepa()->orderBy('created_at', 'desc')->get();
        $mandatActif = $mandats->where('statut', 'valide')->first();

        return Inertia::render('Client/Sepa', [
            'mandats' => $mandats,
            'mandatActif' => $mandatActif
        ]);
    }

    public function sepaCreate()
    {
        $client = $this->getClient();
        if (!$client) abort(404);

        // Vérifier si un mandat actif existe déjà
        if ($client->mandatsSepa()->where('statut', 'valide')->exists()) {
            return redirect()->route('client.sepa')
                ->with('error', 'Vous avez déjà un mandat SEPA actif.');
        }

        return view('client.sepa.create', compact('client'));
    }

    public function sepaStore(Request $request)
    {
        $client = $this->getClient();
        if (!$client) abort(404);

        $validated = $request->validate([
            'titulaire' => 'required|string|max:255',
            'iban' => 'required|string|size:27',
            'bic' => 'required|string|min:8|max:11',
            'consentement' => 'required|accepted',
        ]);

        $rum = $this->generateRUM();

        $mandat = MandatSepa::create([
            'client_id' => $client->id,
            'tenant_id' => $client->tenant_id,
            'rum' => $rum,
            'titulaire' => $validated['titulaire'],
            'iban' => $validated['iban'],
            'bic' => $validated['bic'],
            'date_signature' => now(),
            'type_paiement' => 'recurrent',
            'statut' => 'en_attente',
        ]);

        return redirect()->route('client.sepa')
            ->with('success', 'Mandat SEPA créé avec succès.');
    }

    public function sepaPdf(MandatSepa $mandat)
    {
        $client = $this->getClient();
        if ($mandat->client_id !== $client->id) abort(403);

        $mandat->load('client');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.mandat_sepa', compact('mandat', 'client'));

        return $pdf->download('mandat_sepa_' . $mandat->rum . '.pdf');
    }

    private function generateRUM()
    {
        $prefix = 'BXB';
        $date = now()->format('Ymd');
        $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        return $prefix . $date . $random;
    }

    // 4. PROFIL / INFORMATIONS - Édition des coordonnées
    public function profil()
    {
        $client = $this->getClient();
        if (!$client) abort(404);
        $user = Auth::user();

        return Inertia::render('Client/Profil', [
            'client' => $client,
            'user' => $user
        ]);
    }

    public function updateProfil(Request $request)
    {
        $client = $this->getClient();
        if (!$client) abort(404);

        $validated = $request->validate([
            'email' => 'required|email|unique:clients,email,' . $client->id,
            'telephone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'adresse' => 'nullable|string',
            'code_postal' => 'nullable|string|max:10',
            'ville' => 'nullable|string|max:255',
            'pays' => 'nullable|string|max:255',
        ]);

        $client->update($validated);

        return redirect()->route('client.profil')
            ->with('success', 'Profil mis à jour avec succès.');
    }

    // 5. FACTURES ET AVOIRS - Historique avec filtres
    public function factures(Request $request)
    {
        $client = $this->getClient();
        if (!$client) abort(404);

        $query = $client->factures()->with('contrat');

        // Filtres
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('date_debut') && $request->filled('date_fin')) {
            $query->whereBetween('date_emission', [$request->date_debut, $request->date_fin]);
        }

        if ($request->filled('annee')) {
            $query->whereYear('date_emission', $request->annee);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('numero_facture', 'LIKE', "%{$search}%");
        }

        $factures = $query->orderBy('date_emission', 'desc')->paginate(20);

        // Calculs stats complètes
        $reglementsMois = Reglement::whereHas('facture', function ($q) use ($client) {
            $q->where('client_id', $client->id);
        })->whereMonth('date_reglement', now()->month)
          ->whereYear('date_reglement', now()->year)
          ->sum('montant');

        $totalReglements = Reglement::whereHas('facture', function ($q) use ($client) {
            $q->where('client_id', $client->id);
        })->count();

        $montantMoyen = $totalReglements > 0
            ? Reglement::whereHas('facture', function ($q) use ($client) {
                $q->where('client_id', $client->id);
              })->sum('montant') / $totalReglements
            : 0;

        $stats = [
            'total' => $client->factures()->count(),
            'payees' => $client->factures()->where('statut', 'payee')->count(),
            'impayees' => $client->factures()->where('statut', 'en_retard')->count(),
            'montant_total' => $client->factures()->sum('montant_ttc'),
            'montant_du' => $client->factures()->where('statut', 'en_retard')->sum('montant_ttc'),
            'reglements_mois' => $reglementsMois,
            'montant_moyen' => $montantMoyen,
        ];

        // Si c'est une requête Inertia, retourner Inertia
        if ($request->header('X-Inertia')) {
            return Inertia::render('Client/Factures', [
                'factures' => $factures->items(),
                'stats' => $stats,
            ]);
        }

        return view('client.factures.index', compact('client', 'factures', 'stats'));
    }

    public function factureShow(Facture $facture)
    {
        $client = $this->getClient();
        if ($facture->client_id !== $client->id) abort(403);

        $facture->load(['lignes', 'reglements', 'contrat']);

        return Inertia::render('Client/FactureShow', [
            'facture' => $facture
        ]);
    }

    public function facturePdf(Facture $facture)
    {
        $client = $this->getClient();
        if ($facture->client_id !== $client->id) abort(403);

        $facture->load(['lignes', 'reglements', 'contrat', 'client']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.facture', compact('facture', 'client'));

        return $pdf->download('facture_' . $facture->numero_facture . '.pdf');
    }

    // 6. RÈGLEMENTS - Historique des paiements
    public function reglements(Request $request)
    {
        $client = $this->getClient();
        if (!$client) abort(404);

        $query = Reglement::whereHas('facture', function ($q) use ($client) {
            $q->where('client_id', $client->id);
        })->with(['facture']);

        // Filtres
        if ($request->filled('date_debut') && $request->filled('date_fin')) {
            $query->whereBetween('date_reglement', [$request->date_debut, $request->date_fin]);
        }

        if ($request->filled('mode_paiement')) {
            $query->where('mode_paiement', $request->mode_paiement);
        }

        $reglements = $query->orderBy('date_reglement', 'desc')->paginate(20);

        // Calculs stats complètes
        $reglementsAll = Reglement::whereHas('facture', function ($q) use ($client) {
            $q->where('client_id', $client->id);
        });

        $reglementsMois = Reglement::whereHas('facture', function ($q) use ($client) {
            $q->where('client_id', $client->id);
        })->whereMonth('date_reglement', now()->month)
          ->whereYear('date_reglement', now()->year)
          ->sum('montant');

        $totalReglements = $reglementsAll->count();
        $montantMoyen = $totalReglements > 0 ? $reglementsAll->sum('montant') / $totalReglements : 0;

        $stats = [
            'total_reglements' => $totalReglements,
            'montant_total' => $reglementsAll->sum('montant'),
            'reglements_mois' => $reglementsMois,
            'montant_moyen' => $montantMoyen,
        ];

        return Inertia::render('Client/Reglements', [
            'reglements' => $reglements,
            'stats' => $stats,
            'queryParams' => $request->only(['mode_paiement', 'date_debut', 'date_fin'])
        ]);
    }

    // 7. RELANCES - Historique des rappels
    public function relances()
    {
        $client = $this->getClient();
        if (!$client) abort(404);

        $relances = Rappel::where('client_id', $client->id)
            ->with('facture')
            ->orderBy('date_rappel', 'desc')
            ->paginate(15);

        return Inertia::render('Client/Relances', [
            'relances' => $relances
        ]);
    }

    // 8. FICHIERS - Upload et téléchargement de documents
    public function documents()
    {
        $client = $this->getClient();
        if (!$client) abort(404);

        $documents = $client->documents()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Client/Documents', [
            'documents' => $documents
        ]);
    }

    public function documentUpload(Request $request)
    {
        $client = $this->getClient();
        if (!$client) abort(404);

        $request->validate([
            'file' => 'required|file|mimes:pdf|max:20480', // 20MB max
        ]);

        $file = $request->file('file');
        $filename = time() . '_' . $file->getClientOriginalName();

        // S'assurer que le répertoire existe
        Storage::makeDirectory('documents/clients/' . $client->id);

        $path = $file->storeAs('documents/clients/' . $client->id, $filename);

        ClientDocument::create([
            'client_id' => $client->id,
            'nom_fichier' => $filename,
            'nom_original' => $file->getClientOriginalName(),
            'type_document' => 'autre',
            'chemin_fichier' => $path,
            'taille' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'uploaded_by' => Auth::id(),
        ]);

        return redirect()->route('client.documents')
            ->with('success', 'Document téléchargé avec succès.');
    }

    public function documentDownload(ClientDocument $document)
    {
        $client = $this->getClient();
        if ($document->client_id !== $client->id) abort(403);

        if (!Storage::exists($document->chemin_fichier)) {
            abort(404, 'Fichier introuvable');
        }

        return Storage::download($document->chemin_fichier, $document->nom_original);
    }

    public function documentDelete(ClientDocument $document)
    {
        $client = $this->getClient();
        if ($document->client_id !== $client->id) abort(403);

        // Vérifier que c'est le client qui l'a uploadé
        if ($document->uploaded_by !== Auth::id()) {
            return redirect()->route('client.documents')
                ->with('error', 'Vous ne pouvez supprimer que vos propres documents.');
        }

        Storage::delete($document->chemin_fichier);
        $document->delete();

        return redirect()->route('client.documents')
            ->with('success', 'Document supprimé avec succès.');
    }

    // 9. SUIVI - Chronologie des événements
    public function suivi(Request $request)
    {
        $client = $this->getClient();
        if (!$client) abort(404);

        // Agrégation de tous les événements
        $evenements = collect();

        // 1. Événements CONTRATS
        $contrats = $client->contrats()->with('box')->get();
        foreach ($contrats as $contrat) {
            $evenements->push([
                'type' => 'contrat',
                'titre' => 'Contrat #' . $contrat->numero_contrat,
                'description' => 'Box ' . $contrat->box->numero . ' - ' . ucfirst($contrat->statut),
                'date' => $contrat->created_at,
                'icon' => 'fa-file-contract',
                'badge_class' => $this->getContratBadgeClass($contrat->statut),
                'details' => [
                    'Statut' => ucfirst($contrat->statut),
                    'Box' => $contrat->box->numero,
                    'Début' => $contrat->date_debut?->format('d/m/Y'),
                ],
                'actions' => [
                    ['label' => 'Voir détails', 'route' => route('client.contrats.show', $contrat)]
                ]
            ]);
        }

        // 2. Événements FACTURES
        $factures = $client->factures()->get();
        foreach ($factures as $facture) {
            $evenements->push([
                'type' => 'facture',
                'titre' => 'Facture ' . $facture->numero_facture,
                'description' => number_format($facture->montant_ttc, 2) . ' € - ' . ucfirst($facture->statut),
                'date' => $facture->date_emission,
                'icon' => 'fa-file-invoice-dollar',
                'badge_class' => $this->getFactureBadgeClass($facture->statut),
                'details' => [
                    'Montant' => number_format($facture->montant_ttc, 2) . ' €',
                    'Statut' => ucfirst($facture->statut),
                    'Échéance' => $facture->date_echeance?->format('d/m/Y'),
                ],
                'actions' => [
                    ['label' => 'Voir facture', 'route' => route('client.factures.show', $facture)]
                ]
            ]);
        }

        // 3. Événements RÈGLEMENTS
        $reglements = Reglement::whereHas('facture', function ($q) use ($client) {
            $q->where('client_id', $client->id);
        })->with('facture')->get();

        foreach ($reglements as $reglement) {
            $evenements->push([
                'type' => 'reglement',
                'titre' => 'Règlement ' . number_format($reglement->montant, 2) . ' €',
                'description' => 'Paiement par ' . $reglement->mode_paiement . ' - Facture ' . $reglement->facture->numero_facture,
                'date' => $reglement->date_reglement,
                'icon' => 'fa-money-bill-wave',
                'badge_class' => 'bg-success',
                'details' => [
                    'Montant' => number_format($reglement->montant, 2) . ' €',
                    'Mode' => ucfirst($reglement->mode_paiement),
                    'Référence' => $reglement->reference,
                ],
                'actions' => []
            ]);
        }

        // 4. Événements RELANCES
        $relances = Rappel::where('client_id', $client->id)->with('facture')->get();
        foreach ($relances as $relance) {
            $evenements->push([
                'type' => 'relance',
                'titre' => 'Relance niveau ' . $relance->niveau,
                'description' => 'Facture ' . $relance->facture->numero_facture . ' - ' . number_format($relance->montant_du, 2) . ' €',
                'date' => $relance->date_rappel,
                'icon' => 'fa-bell',
                'badge_class' => $this->getRelanceBadgeClass($relance->niveau),
                'details' => [
                    'Niveau' => $relance->niveau,
                    'Montant dû' => number_format($relance->montant_du, 2) . ' €',
                    'Mode envoi' => ucfirst($relance->mode_envoi),
                ],
                'actions' => []
            ]);
        }

        // 5. Événements DOCUMENTS
        $documents = $client->documents()->get();
        foreach ($documents as $document) {
            $evenements->push([
                'type' => 'document',
                'titre' => 'Document: ' . $document->nom,
                'description' => 'Ajouté par ' . ($document->uploaded_by === Auth::id() ? 'vous' : 'BOXIBOX'),
                'date' => $document->created_at,
                'icon' => 'fa-file-pdf',
                'badge_class' => 'bg-secondary',
                'details' => [
                    'Taille' => round($document->taille / 1024, 2) . ' KB',
                    'Type' => $document->type_document,
                ],
                'actions' => [
                    ['label' => 'Télécharger', 'route' => route('client.documents.download', $document)]
                ]
            ]);
        }

        // 6. Événements SEPA
        $mandats = $client->mandatsSepa()->get();
        foreach ($mandats as $mandat) {
            $evenements->push([
                'type' => 'sepa',
                'titre' => 'Mandat SEPA ' . $mandat->rum,
                'description' => 'IBAN ' . substr($mandat->iban, -4) . ' - ' . ucfirst($mandat->statut),
                'date' => $mandat->date_signature,
                'icon' => 'fa-university',
                'badge_class' => $mandat->statut === 'valide' ? 'bg-success' : 'bg-warning',
                'details' => [
                    'RUM' => $mandat->rum,
                    'Titulaire' => $mandat->titulaire,
                    'Statut' => ucfirst($mandat->statut),
                ],
                'actions' => []
            ]);
        }

        // Tri par date décroissante
        $evenements = $evenements->sortByDesc('date');

        // Filtres
        if ($request->filled('type')) {
            $evenements = $evenements->where('type', $request->type);
        }

        if ($request->filled('date_debut') && $request->filled('date_fin')) {
            $evenements = $evenements->whereBetween('date', [
                \Carbon\Carbon::parse($request->date_debut),
                \Carbon\Carbon::parse($request->date_fin)
            ]);
        }

        // Pagination manuelle
        $perPage = 15;
        $currentPage = request()->get('page', 1);
        $evenementsPaginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $evenements->forPage($currentPage, $perPage),
            $evenements->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return Inertia::render('Client/Suivi', [
            'evenements' => $evenementsPaginated,
            'queryParams' => $request->only(['type', 'date_debut', 'date_fin'])
        ]);
    }

    private function getContratBadgeClass($statut)
    {
        return match($statut) {
            'actif' => 'bg-success',
            'en_cours' => 'bg-primary',
            'resilie' => 'bg-danger',
            'termine' => 'bg-secondary',
            default => 'bg-info',
        };
    }

    private function getFactureBadgeClass($statut)
    {
        return match($statut) {
            'payee' => 'bg-success',
            'en_retard' => 'bg-danger',
            'envoyee' => 'bg-warning',
            'annulee' => 'bg-secondary',
            'brouillon' => 'bg-secondary',
            'emise' => 'bg-info',
            default => 'bg-info',
        };
    }

    private function getRelanceBadgeClass($niveau)
    {
        return match($niveau) {
            1 => 'bg-info',
            2 => 'bg-warning',
            3 => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    // 10. PLAN DES BOXES - Visualisation interactive
    public function boxPlan()
    {
        $client = $this->getClient();
        if (!$client) abort(404);

        // Récupérer toutes les boxes avec leurs informations
        $boxes = \App\Models\Box::with(['famille', 'emplacement', 'contratActif.client'])
            ->active()
            ->get()
            ->map(function($box) use ($client) {
                $data = [
                    'id' => $box->id,
                    'numero' => $box->numero,
                    'statut' => $box->statut,
                    'surface' => $box->surface,
                    'volume' => $box->volume,
                    'prix_mensuel' => $box->prix_mensuel,
                    'coordonnees_x' => $box->coordonnees_x,
                    'coordonnees_y' => $box->coordonnees_y,
                    'emplacement_id' => $box->emplacement_id,
                ];

                if ($box->famille) {
                    $data['famille'] = [
                        'id' => $box->famille->id,
                        'nom' => $box->famille->nom,
                        'couleur' => $box->famille->couleur ?? '#6c757d',
                    ];
                }

                if ($box->emplacement) {
                    $data['emplacement'] = [
                        'id' => $box->emplacement->id,
                        'nom' => $box->emplacement->nom,
                        'zone' => $box->emplacement->zone ?? null,
                    ];
                }

                // Si c'est un box du client, montrer les infos du contrat
                if ($box->contratActif && $box->contratActif->client_id === $client->id) {
                    $data['contrat_actif'] = [
                        'id' => $box->contratActif->id,
                        'numero_contrat' => $box->contratActif->numero_contrat,
                        'date_debut' => $box->contratActif->date_debut,
                        'date_fin' => $box->contratActif->date_fin,
                        'montant_loyer' => $box->contratActif->montant_loyer,
                        'client' => [
                            'id' => $client->id,
                            'nom' => $client->nom,
                            'prenom' => $client->prenom,
                        ],
                    ];
                }

                return $data;
            });

        $emplacements = \App\Models\Emplacement::active()->get();

        // Statistiques globales
        $stats = [
            'total' => \App\Models\Box::active()->count(),
            'libres' => \App\Models\Box::active()->libre()->count(),
            'occupes' => \App\Models\Box::active()->occupe()->count(),
            'reserves' => \App\Models\Box::active()->where('statut', 'reserve')->count(),
            'maintenance' => \App\Models\Box::active()->where('statut', 'maintenance')->count(),
        ];

        // Contrats actifs du client
        $contratsActifs = $client->contrats()
            ->with(['box.famille', 'box.emplacement'])
            ->where('statut', 'actif')
            ->get();

        return Inertia::render('Client/BoxPlan', [
            'boxes' => $boxes,
            'emplacements' => $emplacements,
            'stats' => $stats,
            'contratsActifs' => $contratsActifs,
        ]);
    }
}