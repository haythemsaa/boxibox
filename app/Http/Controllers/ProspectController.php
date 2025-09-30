<?php

namespace App\Http\Controllers;

use App\Models\Prospect;
use App\Models\ProspectInteraction;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProspectController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:view_prospects']);
        $this->middleware('permission:create_prospects')->only(['create', 'store']);
        $this->middleware('permission:edit_prospects')->only(['edit', 'update']);
        $this->middleware('permission:delete_prospects')->only(['destroy']);
        $this->middleware('permission:convert_prospects')->only(['convert']);
    }

    public function index(Request $request)
    {
        $query = Prospect::with(['assignedUser', 'interactions'])
            ->orderBy('created_at', 'desc');

        // Filtres
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('origine')) {
            $query->where('origine', $request->origine);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('telephone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('converted')) {
            if ($request->converted == '1') {
                $query->whereNotNull('converted_to_client_at');
            } else {
                $query->whereNull('converted_to_client_at');
            }
        }

        $prospects = $query->paginate(15);

        // Statistiques pour le dashboard
        $stats = [
            'total' => Prospect::count(),
            'nouveaux' => Prospect::where('statut', 'nouveau')->count(),
            'contactes' => Prospect::where('statut', 'contacte')->count(),
            'interesses' => Prospect::where('statut', 'interesse')->count(),
            'convertis' => Prospect::whereNotNull('converted_to_client_at')->count(),
            'perdus' => Prospect::where('statut', 'perdu')->count(),
        ];

        $users = User::where('is_active', true)->get();

        return view('prospects.index', compact('prospects', 'stats', 'users'));
    }

    public function create()
    {
        $users = User::where('is_active', true)->get();
        return view('prospects.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'nullable|email|unique:prospects,email',
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string',
            'code_postal' => 'nullable|string|max:10',
            'ville' => 'nullable|string|max:255',
            'origine' => 'required|in:site_web,telephone,visite,recommandation,publicite,autre',
            'notes' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $validated['date_contact'] = now()->toDateString();

        $prospect = Prospect::create($validated);

        // Créer une interaction initiale
        ProspectInteraction::create([
            'prospect_id' => $prospect->id,
            'type_interaction' => $this->getInitialInteractionType($validated['origine']),
            'date_interaction' => now(),
            'contenu' => 'Premier contact - Prospect créé',
            'resultat' => 'interesse',
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('prospects.index')
            ->with('success', 'Prospect créé avec succès.');
    }

    public function show(Prospect $prospect)
    {
        $prospect->load(['assignedUser', 'interactions.createdBy']);
        $interactions = $prospect->interactions()->orderBy('date_interaction', 'desc')->get();

        return view('prospects.show', compact('prospect', 'interactions'));
    }

    public function edit(Prospect $prospect)
    {
        $users = User::where('is_active', true)->get();
        return view('prospects.edit', compact('prospect', 'users'));
    }

    public function update(Request $request, Prospect $prospect)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'nullable|email|unique:prospects,email,' . $prospect->id,
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string',
            'code_postal' => 'nullable|string|max:10',
            'ville' => 'nullable|string|max:255',
            'origine' => 'required|in:site_web,telephone,visite,recommandation,publicite,autre',
            'statut' => 'required|in:nouveau,contacte,interesse,perdu',
            'notes' => 'nullable|string',
            'date_relance' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $prospect->update($validated);

        return redirect()->route('prospects.index')
            ->with('success', 'Prospect modifié avec succès.');
    }

    public function destroy(Prospect $prospect)
    {
        if ($prospect->converted_to_client_at) {
            return redirect()->route('prospects.index')
                ->with('error', 'Impossible de supprimer un prospect déjà converti.');
        }

        $prospect->delete();

        return redirect()->route('prospects.index')
            ->with('success', 'Prospect supprimé avec succès.');
    }

    public function convert(Request $request, Prospect $prospect)
    {
        if ($prospect->converted_to_client_at) {
            return redirect()->back()
                ->with('error', 'Ce prospect a déjà été converti en client.');
        }

        DB::beginTransaction();

        try {
            // Créer le client
            $client = Client::create([
                'prospect_id' => $prospect->id,
                'type_client' => $request->input('type_client', 'particulier'),
                'nom' => $prospect->nom,
                'prenom' => $prospect->prenom,
                'email' => $prospect->email,
                'telephone' => $prospect->telephone,
                'adresse' => $prospect->adresse,
                'code_postal' => $prospect->code_postal,
                'ville' => $prospect->ville,
                'notes' => $prospect->notes,
            ]);

            // Marquer le prospect comme converti
            $prospect->update([
                'converted_to_client_at' => now(),
                'statut' => 'interesse'
            ]);

            // Créer une interaction de conversion
            ProspectInteraction::create([
                'prospect_id' => $prospect->id,
                'type_interaction' => 'autre',
                'date_interaction' => now(),
                'contenu' => 'Prospect converti en client #' . $client->id,
                'resultat' => 'interesse',
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return redirect()->route('clients.show', $client)
                ->with('success', 'Prospect converti en client avec succès.');

        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()
                ->with('error', 'Erreur lors de la conversion : ' . $e->getMessage());
        }
    }

    public function addInteraction(Request $request, Prospect $prospect)
    {
        $validated = $request->validate([
            'type_interaction' => 'required|in:appel_entrant,appel_sortant,email,visite,courrier,sms,autre',
            'date_interaction' => 'required|date',
            'duree_minutes' => 'nullable|integer|min:0',
            'contenu' => 'required|string',
            'resultat' => 'required|in:interesse,pas_interesse,demande_rappel,rdv_pris,sans_reponse,autre',
            'date_relance_prevue' => 'nullable|date|after:today',
        ]);

        $validated['prospect_id'] = $prospect->id;
        $validated['created_by'] = Auth::id();

        ProspectInteraction::create($validated);

        // Mettre à jour la date de relance du prospect si nécessaire
        if ($validated['date_relance_prevue']) {
            $prospect->update(['date_relance' => $validated['date_relance_prevue']]);
        }

        // Mettre à jour le statut du prospect selon le résultat
        $this->updateProspectStatus($prospect, $validated['resultat']);

        return redirect()->route('prospects.show', $prospect)
            ->with('success', 'Interaction ajoutée avec succès.');
    }

    public function stats()
    {
        $stats = [
            'par_origine' => Prospect::selectRaw('origine, COUNT(*) as count')
                ->groupBy('origine')
                ->get(),
            'par_statut' => Prospect::selectRaw('statut, COUNT(*) as count')
                ->groupBy('statut')
                ->get(),
            'conversion_mensuelle' => Prospect::selectRaw('
                YEAR(converted_to_client_at) as year,
                MONTH(converted_to_client_at) as month,
                COUNT(*) as conversions
            ')
                ->whereNotNull('converted_to_client_at')
                ->where('converted_to_client_at', '>=', now()->subMonths(12))
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get(),
            'taux_conversion' => $this->calculateConversionRate(),
        ];

        return response()->json($stats);
    }

    private function getInitialInteractionType($origine)
    {
        return match($origine) {
            'site_web' => 'email',
            'telephone' => 'appel_entrant',
            'visite' => 'visite',
            default => 'autre'
        };
    }

    private function updateProspectStatus(Prospect $prospect, $resultat)
    {
        $newStatus = match($resultat) {
            'interesse', 'demande_rappel', 'rdv_pris' => 'interesse',
            'pas_interesse' => 'perdu',
            'sans_reponse' => 'contacte',
            default => $prospect->statut
        };

        if ($prospect->statut !== $newStatus) {
            $prospect->update(['statut' => $newStatus]);
        }
    }

    private function calculateConversionRate()
    {
        $totalProspects = Prospect::count();
        $convertedProspects = Prospect::whereNotNull('converted_to_client_at')->count();

        return $totalProspects > 0 ? round(($convertedProspects / $totalProspects) * 100, 2) : 0;
    }
}