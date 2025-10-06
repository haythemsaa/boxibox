<?php

namespace App\Http\Controllers;

use App\Models\AccessCode;
use App\Models\Client;
use App\Models\Box;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccessCodeController extends Controller
{
    /**
     * Liste des codes d'accès
     */
    public function index(Request $request)
    {
        $query = AccessCode::with(['client', 'box'])
            ->orderBy('created_at', 'desc');

        // Filtres
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('code_pin', 'like', '%' . $request->search . '%')
                  ->orWhereHas('client', function ($q) use ($request) {
                      $q->where('nom', 'like', '%' . $request->search . '%')
                        ->orWhere('prenom', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $accessCodes = $query->paginate(20);
        $clients = Client::orderBy('nom')->get();

        return view('access-codes.index', compact('accessCodes', 'clients'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $clients = Client::orderBy('nom')->get();
        $boxes = Box::where('statut', 'occupe')->with('contratActif.client')->orderBy('numero')->get();

        return view('access-codes.create', compact('clients', 'boxes'));
    }

    /**
     * Enregistrer un nouveau code
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'box_id' => 'nullable|exists:boxes,id',
            'type' => 'required|in:pin,qr,badge',
            'temporaire' => 'boolean',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after:date_debut',
            'jours_autorises' => 'nullable|array',
            'heure_debut' => 'nullable|date_format:H:i',
            'heure_fin' => 'nullable|date_format:H:i',
            'max_utilisations' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Générer code PIN unique si type PIN
            if ($validated['type'] === 'pin') {
                $validated['code_pin'] = AccessCode::generateUniquePinCode();
            }

            $validated['tenant_id'] = auth()->user()->tenant_id;
            $validated['statut'] = 'actif';
            $validated['nb_utilisations'] = 0;
            $validated['temporaire'] = $request->boolean('temporaire');

            $accessCode = AccessCode::create($validated);

            // Générer QR code si type QR
            if ($validated['type'] === 'qr') {
                $accessCode->generateQRCode();
            }

            DB::commit();

            return redirect()->route('access-codes.show', $accessCode)
                ->with('success', 'Code d\'accès créé avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
    }

    /**
     * Afficher les détails d'un code
     */
    public function show(AccessCode $accessCode)
    {
        $accessCode->load(['client', 'box', 'logs' => function ($query) {
            $query->orderBy('date_heure', 'desc')->limit(20);
        }]);

        return view('access-codes.show', compact('accessCode'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit(AccessCode $accessCode)
    {
        $clients = Client::orderBy('nom')->get();
        $boxes = Box::where('statut', 'occupe')->with('client')->orderBy('numero')->get();

        return view('access-codes.edit', compact('accessCode', 'clients', 'boxes'));
    }

    /**
     * Mettre à jour un code
     */
    public function update(Request $request, AccessCode $accessCode)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'box_id' => 'nullable|exists:boxes,id',
            'temporaire' => 'boolean',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after:date_debut',
            'jours_autorises' => 'nullable|array',
            'heure_debut' => 'nullable|date_format:H:i',
            'heure_fin' => 'nullable|date_format:H:i',
            'max_utilisations' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $validated['temporaire'] = $request->boolean('temporaire');

        $accessCode->update($validated);

        return redirect()->route('access-codes.show', $accessCode)
            ->with('success', 'Code d\'accès mis à jour');
    }

    /**
     * Révoquer un code
     */
    public function revoke(Request $request, AccessCode $accessCode)
    {
        $reason = $request->input('reason', 'Révoqué par l\'administrateur');
        $accessCode->revoke($reason);

        return redirect()->route('access-codes.index')
            ->with('success', 'Code d\'accès révoqué');
    }

    /**
     * Suspendre un code
     */
    public function suspend(Request $request, AccessCode $accessCode)
    {
        $reason = $request->input('reason', 'Suspendu par l\'administrateur');
        $accessCode->suspend($reason);

        return redirect()->route('access-codes.show', $accessCode)
            ->with('success', 'Code d\'accès suspendu');
    }

    /**
     * Réactiver un code
     */
    public function reactivate(AccessCode $accessCode)
    {
        $accessCode->reactivate();

        return redirect()->route('access-codes.show', $accessCode)
            ->with('success', 'Code d\'accès réactivé');
    }

    /**
     * Télécharger le QR code
     */
    public function downloadQR(AccessCode $accessCode)
    {
        if (!$accessCode->qr_code_path) {
            return back()->with('error', 'Aucun QR code disponible');
        }

        $path = storage_path('app/public/' . $accessCode->qr_code_path);

        if (!file_exists($path)) {
            return back()->with('error', 'Fichier QR code introuvable');
        }

        return response()->download($path, 'qr_code_' . $accessCode->client->nom . '.svg');
    }
}
