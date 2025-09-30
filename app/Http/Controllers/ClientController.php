<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientDocument;
use App\Models\Contrat;
use App\Models\Facture;
use App\Models\MandatSepa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:view_clients']);
        $this->middleware('permission:create_clients')->only(['create', 'store']);
        $this->middleware('permission:edit_clients')->only(['edit', 'update']);
        $this->middleware('permission:delete_clients')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Client::with(['prospect', 'contrats', 'documents'])
            ->orderBy('created_at', 'desc');

        // Filtres
        if ($request->filled('type_client')) {
            $query->where('type_client', $request->type_client);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('raison_sociale', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('telephone', 'like', "%{$search}%")
                  ->orWhere('siret', 'like', "%{$search}%");
            });
        }

        if ($request->filled('has_contrat')) {
            if ($request->has_contrat == '1') {
                $query->whereHas('contrats');
            } else {
                $query->whereDoesntHave('contrats');
            }
        }

        $clients = $query->paginate(15);

        // Statistiques
        $stats = [
            'total' => Client::count(),
            'actifs' => Client::where('is_active', true)->count(),
            'particuliers' => Client::where('type_client', 'particulier')->count(),
            'entreprises' => Client::where('type_client', 'entreprise')->count(),
            'avec_contrats' => Client::whereHas('contrats', function($q) {
                $q->where('statut', 'actif');
            })->count(),
        ];

        return view('clients.index', compact('clients', 'stats'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'type_client' => 'required|in:particulier,entreprise',
            'nom' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'telephone' => 'nullable|string|max:20',
            'telephone_urgence' => 'nullable|string|max:20',
            'adresse' => 'required|string',
            'code_postal' => 'required|string|max:10',
            'ville' => 'required|string|max:255',
            'pays' => 'string|max:255',
            'contact_urgence_nom' => 'nullable|string|max:255',
            'contact_urgence_telephone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ];

        if ($request->type_client === 'particulier') {
            $rules['prenom'] = 'required|string|max:255';
            $rules['date_naissance'] = 'nullable|date|before:today';
            $rules['piece_identite_type'] = 'nullable|in:cni,passeport,permis,autre';
            $rules['piece_identite_numero'] = 'nullable|string|max:50';
        } else {
            $rules['raison_sociale'] = 'required|string|max:255';
            $rules['siret'] = 'nullable|string|max:20|unique:clients,siret';
        }

        $validated = $request->validate($rules);
        $validated['pays'] = $validated['pays'] ?? 'France';

        $client = Client::create($validated);

        return redirect()->route('clients.show', $client)
            ->with('success', 'Client créé avec succès.');
    }

    public function show(Client $client)
    {
        $client->load([
            'prospect',
            'contrats.box.famille',
            'factures',
            'documents.uploadedBy',
            'mandatsSepa'
        ]);

        $stats = [
            'contrats_actifs' => $client->contrats()->where('statut', 'actif')->count(),
            'factures_en_attente' => $client->factures()->where('statut', 'en_attente')->count(),
            'montant_du' => $client->factures()
                ->whereIn('statut', ['en_attente', 'en_retard'])
                ->sum('montant_ttc'),
            'documents_count' => $client->documents()->count(),
        ];

        return view('clients.show', compact('client', 'stats'));
    }

    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $rules = [
            'type_client' => 'required|in:particulier,entreprise',
            'nom' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email,' . $client->id,
            'telephone' => 'nullable|string|max:20',
            'telephone_urgence' => 'nullable|string|max:20',
            'adresse' => 'required|string',
            'code_postal' => 'required|string|max:10',
            'ville' => 'required|string|max:255',
            'pays' => 'string|max:255',
            'contact_urgence_nom' => 'nullable|string|max:255',
            'contact_urgence_telephone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ];

        if ($request->type_client === 'particulier') {
            $rules['prenom'] = 'required|string|max:255';
            $rules['date_naissance'] = 'nullable|date|before:today';
            $rules['piece_identite_type'] = 'nullable|in:cni,passeport,permis,autre';
            $rules['piece_identite_numero'] = 'nullable|string|max:50';
        } else {
            $rules['raison_sociale'] = 'required|string|max:255';
            $rules['siret'] = 'nullable|string|max:20|unique:clients,siret,' . $client->id;
        }

        $validated = $request->validate($rules);
        $validated['is_active'] = $request->has('is_active');

        $client->update($validated);

        return redirect()->route('clients.show', $client)
            ->with('success', 'Client modifié avec succès.');
    }

    public function destroy(Client $client)
    {
        // Vérifier s'il y a des contrats actifs
        if ($client->contrats()->where('statut', 'actif')->exists()) {
            return redirect()->route('clients.index')
                ->with('error', 'Impossible de supprimer un client avec des contrats actifs.');
        }

        // Vérifier s'il y a des factures impayées
        if ($client->factures()->whereIn('statut', ['en_attente', 'en_retard'])->exists()) {
            return redirect()->route('clients.index')
                ->with('error', 'Impossible de supprimer un client avec des factures impayées.');
        }

        // Supprimer les documents associés
        foreach ($client->documents as $document) {
            Storage::delete($document->chemin_fichier);
        }

        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', 'Client supprimé avec succès.');
    }

    public function documents(Client $client)
    {
        $documents = $client->documents()
            ->with('uploadedBy')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('clients.documents', compact('client', 'documents'));
    }

    public function uploadDocument(Request $request, Client $client)
    {
        $request->validate([
            'document' => 'required|file|max:' . (config('boxibox.documents.max_size') / 1024),
            'type_document' => 'required|in:piece_identite,justificatif_domicile,rib,kbis,assurance,mandat_sepa,contrat,correspondance,autre',
            'description' => 'nullable|string|max:500',
        ]);

        $file = $request->file('document');
        $extension = $file->getClientOriginalExtension();

        // Vérifier l'extension
        if (!in_array(strtolower($extension), config('boxibox.documents.allowed_types'))) {
            return redirect()->back()
                ->with('error', 'Type de fichier non autorisé.');
        }

        // Générer un nom unique
        $fileName = Str::uuid() . '.' . $extension;
        $path = "documents/clients/{$client->id}";

        // Stocker le fichier
        $filePath = $file->storeAs($path, $fileName);

        // Créer l'enregistrement en base
        ClientDocument::create([
            'client_id' => $client->id,
            'type_document' => $request->type_document,
            'nom_fichier' => $fileName,
            'nom_original' => $file->getClientOriginalName(),
            'chemin_fichier' => $filePath,
            'taille' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'description' => $request->description,
            'uploaded_by' => Auth::id(),
        ]);

        return redirect()->route('clients.documents', $client)
            ->with('success', 'Document ajouté avec succès.');
    }

    public function downloadDocument(ClientDocument $document)
    {
        if (!Storage::exists($document->chemin_fichier)) {
            return redirect()->back()
                ->with('error', 'Fichier introuvable.');
        }

        return Storage::download($document->chemin_fichier, $document->nom_original);
    }

    public function deleteDocument(ClientDocument $document)
    {
        if (Storage::exists($document->chemin_fichier)) {
            Storage::delete($document->chemin_fichier);
        }

        $document->delete();

        return redirect()->back()
            ->with('success', 'Document supprimé avec succès.');
    }

    public function search(Request $request)
    {
        $query = $request->get('q');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $clients = Client::where(function($q) use ($query) {
            $q->where('nom', 'like', "%{$query}%")
              ->orWhere('prenom', 'like', "%{$query}%")
              ->orWhere('raison_sociale', 'like', "%{$query}%")
              ->orWhere('email', 'like', "%{$query}%");
        })
        ->where('is_active', true)
        ->limit(10)
        ->get()
        ->map(function($client) {
            return [
                'id' => $client->id,
                'text' => $client->getFullNameAttribute() . ' (' . $client->email . ')',
                'email' => $client->email,
                'type' => $client->type_client,
            ];
        });

        return response()->json($clients);
    }

    public function export(Request $request)
    {
        // Cette méthode sera implémentée avec les exports Excel/CSV
        return response()->json(['message' => 'Export en développement']);
    }
}