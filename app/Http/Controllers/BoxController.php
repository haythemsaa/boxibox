<?php

namespace App\Http\Controllers;

use App\Models\Box;
use App\Models\BoxFamille;
use App\Models\Emplacement;
use App\Models\Contrat;
use App\Models\PlanLayout;
use App\Models\FloorPlan;
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
        $this->middleware('permission:manage_box_plan')->only(['plan', 'planEditor', 'savePlan']);
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

                if ($box->contratActif && $box->contratActif->client) {
                    $data['contrat_actif'] = [
                        'id' => $box->contratActif->id,
                        'numero_contrat' => $box->contratActif->numero_contrat,
                        'date_debut' => $box->contratActif->date_debut,
                        'date_fin' => $box->contratActif->date_fin,
                        'montant_loyer' => $box->contratActif->montant_loyer,
                        'client' => [
                            'id' => $box->contratActif->client->id,
                            'nom' => $box->contratActif->client->nom,
                            'prenom' => $box->contratActif->client->prenom,
                            'email' => $box->contratActif->client->email,
                            'telephone' => $box->contratActif->client->telephone,
                        ],
                    ];
                }

                return $data;
            });

        $emplacements = Emplacement::active()->get();

        // Statistiques globales
        $stats = [
            'total' => Box::active()->count(),
            'libres' => Box::active()->libre()->count(),
            'occupes' => Box::active()->occupe()->count(),
            'reserves' => Box::active()->where('statut', 'reserve')->count(),
            'maintenance' => Box::active()->where('statut', 'maintenance')->count(),
        ];

        return \Inertia\Inertia::render('Admin/BoxPlan', [
            'boxes' => $boxes,
            'emplacements' => $emplacements,
            'stats' => $stats,
        ]);
    }

    public function planEditor(Request $request)
    {
        // Récupérer toutes les boxes avec leurs coordonnées de plan
        $boxes = Box::with(['famille', 'emplacement'])
            ->active()
            ->get()
            ->map(function($box) {
                return [
                    'id' => $box->id,
                    'numero' => $box->numero,
                    'statut' => $box->statut,
                    'surface' => $box->surface,
                    'prix_mensuel' => $box->prix_mensuel,
                    'emplacement_id' => $box->emplacement_id,
                    'plan_x' => $box->plan_x,
                    'plan_y' => $box->plan_y,
                    'plan_width' => $box->plan_width ?? 100,
                    'plan_height' => $box->plan_height ?? 80,
                    'famille' => $box->famille ? [
                        'nom' => $box->famille->nom,
                        'couleur' => $box->famille->couleur ?? '#6c757d',
                    ] : null,
                    'emplacement' => $box->emplacement ? [
                        'nom' => $box->emplacement->nom,
                    ] : null,
                ];
            });

        $emplacements = Emplacement::active()->get();

        // Charger les layouts existants pour chaque emplacement
        $planLayouts = PlanLayout::all()->keyBy('emplacement_id')
            ->map(function($layout) {
                return [
                    'background_image' => $layout->background_image,
                    'canvas_width' => $layout->canvas_width,
                    'canvas_height' => $layout->canvas_height,
                ];
            });

        return \Inertia\Inertia::render('Admin/PlanEditor', [
            'boxes' => $boxes,
            'emplacements' => $emplacements,
            'planLayouts' => $planLayouts,
        ]);
    }

    public function planEditorAdvanced(Request $request)
    {
        // Même données que planEditor mais avec la vue avancée
        $boxes = Box::with(['famille', 'emplacement'])
            ->active()
            ->get()
            ->map(function($box) {
                return [
                    'id' => $box->id,
                    'numero' => $box->numero,
                    'statut' => $box->statut,
                    'surface' => $box->surface,
                    'prix_mensuel' => $box->prix_mensuel,
                    'emplacement_id' => $box->emplacement_id,
                    'plan_x' => $box->plan_x,
                    'plan_y' => $box->plan_y,
                    'plan_width' => $box->plan_width ?? 100,
                    'plan_height' => $box->plan_height ?? 80,
                    'famille' => $box->famille ? [
                        'nom' => $box->famille->nom,
                        'couleur' => $box->famille->couleur ?? '#6c757d',
                    ] : null,
                    'emplacement' => $box->emplacement ? [
                        'nom' => $box->emplacement->nom,
                    ] : null,
                ];
            });

        $emplacements = Emplacement::active()->get();

        $planLayouts = PlanLayout::all()->keyBy('emplacement_id')
            ->map(function($layout) {
                return [
                    'background_image' => $layout->background_image,
                    'canvas_width' => $layout->canvas_width,
                    'canvas_height' => $layout->canvas_height,
                ];
            });

        return \Inertia\Inertia::render('Admin/PlanEditorAdvanced', [
            'boxes' => $boxes,
            'emplacements' => $emplacements,
            'planLayouts' => $planLayouts,
        ]);
    }

    public function savePlan(Request $request)
    {
        $validated = $request->validate([
            'emplacement_id' => 'required|exists:emplacements,id',
            'background_image' => 'nullable|string',
            'canvas_width' => 'nullable|integer|min:800|max:3000',
            'canvas_height' => 'nullable|integer|min:600|max:3000',
            'boxes' => 'required|array',
            'boxes.*.id' => 'required|exists:boxes,id',
            'boxes.*.plan_x' => 'nullable|integer',
            'boxes.*.plan_y' => 'nullable|integer',
            'boxes.*.plan_width' => 'nullable|integer|min:50|max:500',
            'boxes.*.plan_height' => 'nullable|integer|min:40|max:400',
        ]);

        // Mettre à jour ou créer le layout
        PlanLayout::updateOrCreate(
            ['emplacement_id' => $validated['emplacement_id']],
            [
                'background_image' => $validated['background_image'],
                'canvas_width' => $validated['canvas_width'] ?? 1200,
                'canvas_height' => $validated['canvas_height'] ?? 800,
            ]
        );

        // Mettre à jour les positions des boxes
        foreach ($validated['boxes'] as $boxData) {
            Box::where('id', $boxData['id'])->update([
                'plan_x' => $boxData['plan_x'],
                'plan_y' => $boxData['plan_y'],
                'plan_width' => $boxData['plan_width'],
                'plan_height' => $boxData['plan_height'],
            ]);
        }

        return redirect()->back()->with('success', 'Plan enregistré avec succès.');
    }

    public function floorPlanDesigner()
    {
        $emplacements = Emplacement::active()->get();
        $familles = BoxFamille::active()->get();

        // Charger les plans existants et les boxes
        $floorPlans = [];
        $existingBoxes = [];

        foreach ($emplacements as $emplacement) {
            $plan = FloorPlan::where('emplacement_id', $emplacement->id)->first();
            if ($plan) {
                $floorPlans[$emplacement->id] = [
                    'floor_plan' => json_decode($plan->path_data, true),
                    'canvas_width' => $plan->canvas_width,
                    'canvas_height' => $plan->canvas_height,
                    'echelle' => $plan->echelle_metres_par_pixel,
                    'metadata' => $plan->metadata,
                ];
            }

            // Charger les boxes de cet emplacement qui ont des coordonnées de plan
            $boxes = Box::where('emplacement_id', $emplacement->id)
                ->whereNotNull('plan_x')
                ->whereNotNull('plan_y')
                ->with(['famille', 'contratActif.client'])
                ->get()
                ->map(function($box) {
                    return [
                        'id' => $box->id,
                        'numero' => $box->numero,
                        'x' => $box->plan_x,
                        'y' => $box->plan_y,
                        'width' => $box->plan_width ?? 100,
                        'height' => $box->plan_height ?? 80,
                        'surface' => $box->surface,
                        'prix_mensuel' => $box->prix_mensuel,
                        'statut' => $box->statut,
                        'famille_id' => $box->famille_id,
                        'famille_nom' => $box->famille->nom ?? '',
                        'is_from_db' => true, // Marquer comme venant de la BD
                    ];
                });

            $existingBoxes[$emplacement->id] = $boxes;
        }

        return \Inertia\Inertia::render('Admin/FloorPlanDesigner', [
            'emplacements' => $emplacements,
            'familles' => $familles,
            'floorPlans' => $floorPlans,
            'existingBoxes' => $existingBoxes,
        ]);
    }

    public function saveFloorPlan(Request $request)
    {
        $validated = $request->validate([
            'emplacement_id' => 'required|exists:emplacements,id',
            'floor_plan' => 'required|array',
            'boxes' => 'nullable|array',
            'canvas_width' => 'required|integer',
            'canvas_height' => 'required|integer',
            'echelle' => 'nullable|numeric',
        ]);

        DB::beginTransaction();
        try {
            // Sauvegarder le plan de sol
            FloorPlan::updateOrCreate(
                ['emplacement_id' => $validated['emplacement_id']],
                [
                    'nom' => 'Plan ' . Emplacement::find($validated['emplacement_id'])->nom,
                    'path_data' => json_encode($validated['floor_plan']),
                    'canvas_width' => $validated['canvas_width'],
                    'canvas_height' => $validated['canvas_height'],
                    'echelle_metres_par_pixel' => $validated['echelle'] ?? 0.05,
                ]
            );

            // Gérer les boxes (si présentes)
            $boxes = $validated['boxes'] ?? [];
            foreach ($boxes as $boxData) {
                if (isset($boxData['is_from_db']) && $boxData['is_from_db']) {
                    // Mettre à jour une box existante
                    Box::where('id', $boxData['id'])->update([
                        'plan_x' => $boxData['x'],
                        'plan_y' => $boxData['y'],
                        'plan_width' => $boxData['width'],
                        'plan_height' => $boxData['height'],
                        'surface' => $boxData['surface'],
                        'prix_mensuel' => $boxData['prix_mensuel'],
                        'statut' => $boxData['statut'],
                    ]);
                } else {
                    // Créer une nouvelle box dans la BD
                    Box::create([
                        'famille_id' => $boxData['famille_id'] ?? BoxFamille::first()->id,
                        'emplacement_id' => $validated['emplacement_id'],
                        'numero' => $boxData['numero'],
                        'surface' => $boxData['surface'],
                        'volume' => $boxData['surface'] * 2.5, // Hauteur moyenne de 2.5m
                        'prix_mensuel' => $boxData['prix_mensuel'],
                        'statut' => $boxData['statut'] ?? 'libre',
                        'plan_x' => $boxData['x'],
                        'plan_y' => $boxData['y'],
                        'plan_width' => $boxData['width'],
                        'plan_height' => $boxData['height'],
                        'is_active' => true,
                    ]);
                }
            }

            DB::commit();

            // Recharger les données à jour pour Inertia
            return redirect()->route('boxes.floorplan.designer')->with('success', 'Plan de salle et boxes enregistrés avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Erreur: ' . $e->getMessage()]);
        }
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