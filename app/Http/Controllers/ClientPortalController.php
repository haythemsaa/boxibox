<?php

namespace App\Http\Controllers;

use App\Models\Contrat;
use App\Models\Facture;
use App\Models\Reglement;
use App\Models\Document;
use App\Models\MandatSepa;
use App\Models\Client;
use App\Models\Rappel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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

        $stats = [
            'contrats_actifs' => $client->contrats()->where('statut', 'actif')->count(),
            'factures_impayees' => $client->factures()->where('statut', 'impaye')->count(),
            'montant_du' => $client->factures()->where('statut', 'impaye')->sum('montant_total_ttc'),
            'documents' => $client->documents()->count(),
            'mandat_sepa_actif' => $client->mandatsSepa()->where('statut', 'actif')->exists(),
        ];

        $contratsActifs = $client->contrats()
            ->where('statut', 'actif')
            ->with(['box.famille', 'box.emplacement'])
            ->take(5)
            ->get();

        $dernieresFactures = $client->factures()
            ->orderBy('date_emission', 'desc')
            ->take(5)
            ->get();

        return view('client.dashboard', compact('client', 'stats', 'contratsActifs', 'dernieresFactures'));
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

        return view('client.contrats.index', compact('client', 'contrats'));
    }

    public function contratShow(Contrat $contrat)
    {
        $client = $this->getClient();
        if ($contrat->client_id !== $client->id) abort(403);

        $contrat->load(['box.famille', 'box.emplacement', 'factures.reglements']);

        return view('client.contrats.show', compact('client', 'contrat'));
    }

    public function contratPdf(Contrat $contrat)
    {
        $client = $this->getClient();
        if ($contrat->client_id !== $client->id) abort(403);

        // TODO: Générer PDF du contrat
        return response()->json(['message' => 'Génération PDF non implémentée']);
    }

    // 3. MANDATS SEPA - Gestion et signature électronique
    public function sepa()
    {
        $client = $this->getClient();
        if (!$client) abort(404);

        $mandats = $client->mandatsSepa()->orderBy('created_at', 'desc')->get();
        $mandatActif = $mandats->where('statut', 'actif')->first();

        return view('client.sepa.index', compact('client', 'mandats', 'mandatActif'));
    }

    public function sepaCreate()
    {
        $client = $this->getClient();
        if (!$client) abort(404);

        // Vérifier si un mandat actif existe déjà
        if ($client->mandatsSepa()->where('statut', 'actif')->exists()) {
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

        return view('client.profil', compact('client', 'user'));
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

        $stats = [
            'total' => $client->factures()->count(),
            'payees' => $client->factures()->where('statut', 'paye')->count(),
            'impayees' => $client->factures()->where('statut', 'impaye')->count(),
            'montant_total' => $client->factures()->sum('montant_total_ttc'),
            'montant_du' => $client->factures()->where('statut', 'impaye')->sum('montant_total_ttc'),
        ];

        return view('client.factures.index', compact('client', 'factures', 'stats'));
    }

    public function factureShow(Facture $facture)
    {
        $client = $this->getClient();
        if ($facture->client_id !== $client->id) abort(403);

        $facture->load(['lignes', 'reglements', 'contrat']);

        return view('client.factures.show', compact('client', 'facture'));
    }

    public function facturePdf(Facture $facture)
    {
        $client = $this->getClient();
        if ($facture->client_id !== $client->id) abort(403);

        // TODO: Générer PDF
        return response()->json(['message' => 'PDF non implémenté']);
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

        $stats = [
            'total_reglements' => $reglements->total(),
            'montant_total' => Reglement::whereHas('facture', function ($q) use ($client) {
                $q->where('client_id', $client->id);
            })->sum('montant'),
        ];

        return view('client.reglements.index', compact('client', 'reglements', 'stats'));
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

        return view('client.relances.index', compact('client', 'relances'));
    }

    // 8. FICHIERS - Upload et téléchargement de documents
    public function documents()
    {
        $client = $this->getClient();
        if (!$client) abort(404);

        $documents = $client->documents()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('client.documents.index', compact('client', 'documents'));
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
        $path = $file->storeAs('documents/clients/' . $client->id, $filename);

        Document::create([
            'client_id' => $client->id,
            'tenant_id' => $client->tenant_id,
            'nom' => $file->getClientOriginalName(),
            'type_document' => 'client_upload',
            'chemin' => $path,
            'taille' => $file->getSize(),
            'uploaded_by' => Auth::id(),
        ]);

        return redirect()->route('client.documents')
            ->with('success', 'Document téléchargé avec succès.');
    }

    public function documentDownload(Document $document)
    {
        $client = $this->getClient();
        if ($document->client_id !== $client->id) abort(403);

        if (!Storage::exists($document->chemin)) {
            abort(404, 'Fichier introuvable');
        }

        return Storage::download($document->chemin, $document->nom);
    }

    public function documentDelete(Document $document)
    {
        $client = $this->getClient();
        if ($document->client_id !== $client->id) abort(403);

        // Vérifier que c'est le client qui l'a uploadé
        if ($document->uploaded_by !== Auth::id()) {
            return redirect()->route('client.documents')
                ->with('error', 'Vous ne pouvez supprimer que vos propres documents.');
        }

        Storage::delete($document->chemin);
        $document->delete();

        return redirect()->route('client.documents')
            ->with('success', 'Document supprimé avec succès.');
    }

    // 9. SUIVI - Chronologie des événements
    public function suivi()
    {
        $client = $this->getClient();
        if (!$client) abort(404);

        // Récupérer tous les contrats avec leurs événements
        $contrats = $client->contrats()
            ->with(['box.famille'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('client.suivi.index', compact('client', 'contrats'));
    }
}