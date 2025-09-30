<?php

namespace App\Http\Controllers;

use App\Models\Box;
use App\Models\BoxFamille;
use App\Models\Emplacement;
use App\Models\Contrat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BoxController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:view_boxes']);
        $this->middleware('permission:create_boxes')->only(['create', 'store']);
        $this->middleware('permission:edit_boxes')->only(['edit', 'update', 'reserve', 'liberer']);
        $this->middleware('permission:delete_boxes')->only(['destroy']);
        $this->middleware('permission:manage_box_plan')->only(['plan']);
    }

    public function index(Request $request)
    {
        $query = Box::with(['famille', 'emplacement', 'contratActif.client']);

        // Filtres
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('famille_id')) {
            $query->where('famille_id', $request->famille_id);
        }

        if ($request->filled('emplacement_id')) {
            $query->where('emplacement_id', $request->emplacement_id);
        }

        if ($request->filled('surface_min')) {
            $query->where('surface', '>=', $request->surface_min);
        }

        if ($request->filled('surface_max')) {
            $query->where('surface', '<=', $request->surface_max);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('numero', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $boxes = $query->orderBy('numero')->paginate(20);

        // Statistiques
        $stats = [
            'total' => Box::active()->count(),
            'libres' => Box::active()->libre()->count(),
            'occupees' => Box::active()->occupe()->count(),
            'reservees' => Box::active()->where('statut', 'reserve')->count(),
            'maintenance' => Box::active()->where('statut', 'maintenance')->count(),
            'hors_service' => Box::active()->where('statut', 'hors_service')->count(),
            'surface_totale' => Box::active()->sum('surface'),
            'ca_potentiel' => Box::active()->sum('prix_mensuel'),
            'ca_reel' => Box::active()->occupe()->sum('prix_mensuel'),
        ];

        $familles = BoxFamille::active()->get();
        $emplacements = Emplacement::active()->get();

        return view('boxes.index', compact('boxes', 'stats', 'familles', 'emplacements'));
    }

    public function plan(Request $request)
    {
        // Récupérer toutes les boxes avec leurs informations
        $boxes = Box::with(['famille', 'emplacement', 'contratActif.client'])
            ->active()
            ->get()
            ->map(function($box) {
                return [
                    'id' => $box->id,
                    'numero' => $box->numero,
                    'statut' => $box->statut,
                    'surface' => $box->surface,
                    'prix_mensuel' => $box->prix_mensuel,
                    'coordonnees_x' => $box->coordonnees_x,
                    'coordonnees_y' => $box->coordonnees_y,
                    'famille' => [
                        'id' => $box->famille->id,
                        'nom' => $box->famille->nom,
                        'couleur_plan' => $box->famille->couleur_plan,
                    ],
                    'emplacement' => [
                        'id' => $box->emplacement->id,
                        'nom' => $box->emplacement->nom,
                        'zone' => $box->emplacement->zone,
                    ],
                    'client' => $box->contratActif ? [
                        'id' => $box->contratActif->client->id,
                        'nom' => $box->contratActif->client->getFullNameAttribute(),
                    ] : null,
                ];
            });

        $emplacements = Emplacement::active()->get();
        $familles = BoxFamille::active()->get();

        // Statistiques par emplacement
        $statsParEmplacement = $emplacements->map(function($emplacement) {
            return [
                'id' => $emplacement->id,
                'nom' => $emplacement->nom,
                'total' => $emplacement->boxes()->count(),
                'libres' => $emplacement->boxes()->libre()->count(),
                'occupees' => $emplacement->boxes()->occupe()->count(),
                'taux_occupation' => $emplacement->getTauxOccupationAttribute(),
            ];
        });

        return view('boxes.plan', compact('boxes', 'emplacements', 'familles', 'statsParEmplacement'));
    }

    public function create()
    {
        $familles = BoxFamille::active()->get();
        $emplacements = Emplacement::active()->get();

        return view('boxes.create', compact('familles', 'emplacements'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'famille_id' => 'required|exists:box_familles,id',
            'emplacement_id' => 'required|exists:emplacements,id',
            'numero' => 'required|string|unique:boxes,numero',
            'surface' => 'required|numeric|min:0.1',
            'volume' => 'required|numeric|min:0.1',
            'prix_mensuel' => 'required|numeric|min:0',
            'coordonnees_x' => 'nullable|integer|min:0',
            'coordonnees_y' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
        ]);

        $validated['statut'] = 'libre';

        $box = Box::create($validated);

        return redirect()->route('boxes.show', $box)
            ->with('success', 'Box créée avec succès.');
    }

    public function show(Box $box)
    {
        $box->load(['famille', 'emplacement', 'contrats.client']);

        $historique = $box->contrats()
            ->with('client')
            ->orderBy('date_debut', 'desc')
            ->get();

        $stats = [
            'nombre_contrats' => $box->contrats()->count(),
            'duree_occupation_totale' => $this->calculateDureeOccupationTotale($box),
            'ca_genere' => $this->calculateCaGenere($box),
            'taux_occupation' => $this->calculateTauxOccupation($box),
        ];

        return view('boxes.show', compact('box', 'historique', 'stats'));
    }

    public function edit(Box $box)
    {
        $familles = BoxFamille::active()->get();
        $emplacements = Emplacement::active()->get();

        return view('boxes.edit', compact('box', 'familles', 'emplacements'));
    }

    public function update(Request $request, Box $box)
    {
        $validated = $request->validate([
            'famille_id' => 'required|exists:box_familles,id',
            'emplacement_id' => 'required|exists:emplacements,id',
            'numero' => 'required|string|unique:boxes,numero,' . $box->id,
            'surface' => 'required|numeric|min:0.1',
            'volume' => 'required|numeric|min:0.1',
            'prix_mensuel' => 'required|numeric|min:0',
            'statut' => 'required|in:libre,occupe,reserve,maintenance,hors_service',
            'coordonnees_x' => 'nullable|integer|min:0',
            'coordonnees_y' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Vérification pour éviter des changements de statut incohérents
        if ($box->statut === 'occupe' && $validated['statut'] !== 'occupe') {
            $contratActif = $box->contratActif();
            if ($contratActif->exists()) {
                return redirect()->back()
                    ->with('error', 'Impossible de changer le statut d\'une box avec un contrat actif.');
            }
        }

        $validated['is_active'] = $request->has('is_active');

        $box->update($validated);

        return redirect()->route('boxes.show', $box)
            ->with('success', 'Box modifiée avec succès.');
    }

    public function destroy(Box $box)
    {
        if ($box->contrats()->whereIn('statut', ['actif', 'en_cours'])->exists()) {
            return redirect()->route('boxes.index')
                ->with('error', 'Impossible de supprimer une box avec des contrats actifs.');
        }

        $box->delete();

        return redirect()->route('boxes.index')
            ->with('success', 'Box supprimée avec succès.');
    }

    public function reserve(Box $box)
    {
        if (!$box->isLibre()) {
            return response()->json(['error' => 'Cette box n\'est pas disponible.'], 400);
        }

        $box->update(['statut' => 'reserve']);

        return response()->json([
            'success' => true,
            'message' => 'Box réservée avec succès.',
            'statut' => 'reserve'
        ]);
    }

    public function liberer(Box $box)
    {
        if ($box->statut === 'occupe') {
            $contratActif = $box->contratActif();
            if ($contratActif->exists()) {
                return response()->json(['error' => 'Impossible de libérer une box avec un contrat actif.'], 400);
            }
        }

        $box->update(['statut' => 'libre']);

        return response()->json([
            'success' => true,
            'message' => 'Box libérée avec succès.',
            'statut' => 'libre'
        ]);
    }

    public function updateCoordinates(Request $request, Box $box)
    {
        $validated = $request->validate([
            'coordonnees_x' => 'required|integer|min:0',
            'coordonnees_y' => 'required|integer|min:0',
        ]);

        $box->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Position mise à jour avec succès.'
        ]);
    }

    public function available(Request $request)
    {
        $query = Box::libre()->active()->with(['famille', 'emplacement']);

        // Filtres pour l'API
        if ($request->filled('famille_id')) {
            $query->where('famille_id', $request->famille_id);
        }

        if ($request->filled('surface_min')) {
            $query->where('surface', '>=', $request->surface_min);
        }

        if ($request->filled('surface_max')) {
            $query->where('surface', '<=', $request->surface_max);
        }

        if ($request->filled('prix_max')) {
            $query->where('prix_mensuel', '<=', $request->prix_max);
        }

        $boxes = $query->orderBy('prix_mensuel')->get();

        return response()->json($boxes->map(function($box) {
            return [
                'id' => $box->id,
                'numero' => $box->numero,
                'surface' => $box->surface,
                'prix_mensuel' => $box->prix_mensuel,
                'famille' => $box->famille->nom,
                'emplacement' => $box->emplacement->nom,
                'description' => $box->description,
            ];
        }));
    }

    public function bulkUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            'box_ids' => 'required|array',
            'box_ids.*' => 'exists:boxes,id',
            'statut' => 'required|in:libre,maintenance,hors_service',
        ]);

        $boxes = Box::whereIn('id', $validated['box_ids'])->get();

        foreach ($boxes as $box) {
            // Vérifier qu'on ne change pas le statut d'une box occupée
            if ($box->statut === 'occupe' && $box->contratActif()->exists()) {
                continue;
            }

            $box->update(['statut' => $validated['statut']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Statuts mis à jour avec succès.'
        ]);
    }

    private function calculateDureeOccupationTotale(Box $box)
    {
        $totalJours = 0;

        foreach ($box->contrats as $contrat) {
            $dateDebut = \Carbon\Carbon::parse($contrat->date_debut);
            $dateFin = $contrat->date_fin ?
                \Carbon\Carbon::parse($contrat->date_fin) :
                ($contrat->statut === 'actif' ? now() : $dateDebut);

            $totalJours += $dateDebut->diffInDays($dateFin);
        }

        return $totalJours;
    }

    private function calculateCaGenere(Box $box)
    {
        return $box->contrats()
            ->join('factures', 'contrats.id', '=', 'factures.contrat_id')
            ->where('factures.statut', 'payee')
            ->sum('factures.montant_ttc');
    }

    private function calculateTauxOccupation(Box $box)
    {
        if ($box->created_at->isToday()) {
            return 0;
        }

        $joursDepuisCreation = $box->created_at->diffInDays(now());
        $joursOccupation = $this->calculateDureeOccupationTotale($box);

        return $joursDepuisCreation > 0 ? round(($joursOccupation / $joursDepuisCreation) * 100, 1) : 0;
    }
}