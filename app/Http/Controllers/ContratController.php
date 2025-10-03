<?php

namespace App\Http\Controllers;

use App\Models\Contrat;
use App\Models\Client;
use App\Models\Box;
use App\Models\Service;
use App\Models\ContratService;
use App\Models\Facture;
use App\Models\FactureLigne;
use App\Models\Famille;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ContratController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:view_contrats']);
        $this->middleware('permission:create_contrats')->only(['create', 'store']);
        $this->middleware('permission:edit_contrats')->only(['edit', 'update', 'activate', 'terminate']);
        $this->middleware('permission:delete_contrats')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Contrat::with(['client', 'box.famille'])
            ->orderBy('created_at', 'desc');

        // Filtres
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('duree_type')) {
            $query->where('duree_type', $request->duree_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('numero_contrat', 'like', "%{$search}%")
                  ->orWhereHas('client', function($clientQuery) use ($search) {
                      $clientQuery->where('nom', 'like', "%{$search}%")
                                  ->orWhere('prenom', 'like', "%{$search}%")
                                  ->orWhere('raison_sociale', 'like', "%{$search}%");
                  })
                  ->orWhereHas('box', function($boxQuery) use ($search) {
                      $boxQuery->where('numero', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('date_debut')) {
            $query->where('date_debut', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->where('date_debut', '<=', $request->date_fin);
        }

        $contrats = $query->paginate(15);

        // Statistiques
        $stats = [
            'total' => Contrat::count(),
            'actifs' => Contrat::where('statut', 'actif')->count(),
            'en_cours' => Contrat::where('statut', 'en_cours')->count(),
            'termines' => Contrat::where('statut', 'termine')->count(),
            'resilies' => Contrat::where('statut', 'resilie')->count(),
            'litiges' => Contrat::where('statut', 'litige')->count(),
            'ca_mensuel' => Contrat::where('statut', 'actif')->sum('prix_mensuel'),
        ];

        // Si requête Inertia, retourner Inertia
        if ($request->header('X-Inertia')) {
            return Inertia::render('Admin/ContratsManage', [
                'contrats' => $contrats,
                'stats' => $stats,
                'queryParams' => $request->only(['search', 'statut', 'client_id', 'duree_type', 'date_debut', 'date_fin'])
            ]);
        }

        return view('contrats.index', compact('contrats', 'stats'));
    }

    public function create(Request $request)
    {
        $client = null;
        if ($request->filled('client_id')) {
            $client = Client::find($request->client_id);
        }

        $clients = Client::where('is_active', true)
            ->select('id', 'prenom', 'nom', 'email', 'telephone', 'adresse', 'type_client')
            ->orderBy('nom')
            ->get();

        $boxes = Box::with('famille', 'emplacement')
            ->select('id', 'numero', 'surface', 'volume', 'tarif_mensuel', 'statut', 'famille_id', 'emplacement_id')
            ->get();

        $familles = Famille::select('id', 'nom', 'couleur')->get();

        // Si requête Inertia, retourner Inertia
        if ($request->header('X-Inertia')) {
            return Inertia::render('Admin/ContratCreate', [
                'clients' => $clients,
                'boxes' => $boxes,
                'familles' => $familles
            ]);
        }

        $boxesLibres = Box::libre()->with('famille', 'emplacement')->get();
        $services = Service::actif()->get();

        return view('contrats.create', compact('client', 'clients', 'boxesLibres', 'services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'box_id' => 'required|exists:boxes,id',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after:date_debut',
            'duree_type' => 'required|in:determine,indetermine',
            'prix_mensuel' => 'required|numeric|min:0',
            'caution' => 'required|numeric|min:0',
            'frais_dossier' => 'numeric|min:0',
            'periodicite_facturation' => 'required|in:mensuelle,trimestrielle,semestrielle,annuelle',
            'preavis_jours' => 'required|integer|min:1|max:365',
            'renouvellement_automatique' => 'boolean',
            'assurance_incluse' => 'boolean',
            'montant_assurance' => 'numeric|min:0',
            'notes' => 'nullable|string',
            'services' => 'array',
            'services.*.service_id' => 'exists:services,id',
            'services.*.quantite' => 'numeric|min:0',
        ]);

        // Vérifier que la box est libre
        $box = Box::find($validated['box_id']);
        if (!$box->isLibre()) {
            return redirect()->back()
                ->with('error', 'Cette box n\'est plus disponible.')
                ->withInput();
        }

        DB::beginTransaction();

        try {
            // Générer le numéro de contrat
            $numeroContrat = $this->generateNumeroContrat();

            // Créer le contrat
            $contrat = Contrat::create(array_merge($validated, [
                'numero_contrat' => $numeroContrat,
                'statut' => 'en_cours',
                'renouvellement_automatique' => $request->has('renouvellement_automatique'),
                'assurance_incluse' => $request->has('assurance_incluse'),
                'frais_dossier' => $validated['frais_dossier'] ?? 0,
                'montant_assurance' => $validated['montant_assurance'] ?? 0,
            ]));

            // Ajouter les services
            if (!empty($validated['services'])) {
                foreach ($validated['services'] as $serviceData) {
                    if ($serviceData['quantite'] > 0) {
                        $service = Service::find($serviceData['service_id']);
                        ContratService::create([
                            'contrat_id' => $contrat->id,
                            'service_id' => $service->id,
                            'quantite' => $serviceData['quantite'],
                            'prix_unitaire' => $service->prix,
                            'date_debut' => $contrat->date_debut,
                        ]);
                    }
                }
            }

            // Réserver la box
            $box->update(['statut' => 'reserve']);

            DB::commit();

            return redirect()->route('contrats.show', $contrat)
                ->with('success', 'Contrat créé avec succès.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Erreur lors de la création : ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(Contrat $contrat)
    {
        $contrat->load([
            'client',
            'box.famille',
            'box.emplacement',
            'services.service',
            'factures'
        ]);

        $stats = [
            'factures_count' => $contrat->factures()->count(),
            'factures_payees' => $contrat->factures()->where('statut', 'payee')->count(),
            'montant_facture' => $contrat->factures()->sum('montant_ttc'),
            'montant_paye' => $contrat->factures()->where('statut', 'payee')->sum('montant_ttc'),
            'duree_mois' => $this->calculateDureeMois($contrat),
        ];

        return view('contrats.show', compact('contrat', 'stats'));
    }

    public function edit(Contrat $contrat)
    {
        if ($contrat->statut === 'termine' || $contrat->statut === 'resilie') {
            return redirect()->route('contrats.show', $contrat)
                ->with('error', 'Impossible de modifier un contrat terminé ou résilié.');
        }

        $contrat->load('services.service');
        $clients = Client::where('is_active', true)->get();
        $services = Service::actif()->get();

        return view('contrats.edit', compact('contrat', 'clients', 'services'));
    }

    public function update(Request $request, Contrat $contrat)
    {
        if ($contrat->statut === 'termine' || $contrat->statut === 'resilie') {
            return redirect()->route('contrats.show', $contrat)
                ->with('error', 'Impossible de modifier un contrat terminé ou résilié.');
        }

        $validated = $request->validate([
            'date_fin' => 'nullable|date|after:date_debut',
            'prix_mensuel' => 'required|numeric|min:0',
            'caution' => 'required|numeric|min:0',
            'periodicite_facturation' => 'required|in:mensuelle,trimestrielle,semestrielle,annuelle',
            'preavis_jours' => 'required|integer|min:1|max:365',
            'renouvellement_automatique' => 'boolean',
            'assurance_incluse' => 'boolean',
            'montant_assurance' => 'numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $contrat->update(array_merge($validated, [
            'renouvellement_automatique' => $request->has('renouvellement_automatique'),
            'assurance_incluse' => $request->has('assurance_incluse'),
            'montant_assurance' => $validated['montant_assurance'] ?? 0,
        ]));

        return redirect()->route('contrats.show', $contrat)
            ->with('success', 'Contrat modifié avec succès.');
    }

    public function activate(Contrat $contrat)
    {
        if ($contrat->statut !== 'en_cours') {
            return redirect()->back()
                ->with('error', 'Seuls les contrats en cours peuvent être activés.');
        }

        DB::beginTransaction();

        try {
            // Activer le contrat
            $contrat->update([
                'statut' => 'actif',
                'date_signature' => now()->toDateString(),
            ]);

            // Occuper la box
            $contrat->box->update(['statut' => 'occupe']);

            // Générer la facture de caution si nécessaire
            if ($contrat->caution > 0) {
                $this->generateFactureCaution($contrat);
            }

            // Générer la facture de frais de dossier si nécessaire
            if ($contrat->frais_dossier > 0) {
                $this->generateFactureFraisDossier($contrat);
            }

            DB::commit();

            return redirect()->route('contrats.show', $contrat)
                ->with('success', 'Contrat activé avec succès.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Erreur lors de l\'activation : ' . $e->getMessage());
        }
    }

    public function terminate(Request $request, Contrat $contrat)
    {
        $request->validate([
            'type_fin' => 'required|in:termine,resilie',
            'date_fin' => 'required|date',
            'motif' => 'nullable|string',
        ]);

        if ($contrat->statut !== 'actif') {
            return redirect()->back()
                ->with('error', 'Seuls les contrats actifs peuvent être terminés.');
        }

        DB::beginTransaction();

        try {
            // Terminer le contrat
            $contrat->update([
                'statut' => $request->type_fin,
                'date_fin' => $request->date_fin,
                'notes' => ($contrat->notes ? $contrat->notes . "\n\n" : '') .
                          "Terminé le " . now()->format('d/m/Y') .
                          ($request->motif ? " - Motif: " . $request->motif : ''),
            ]);

            // Libérer la box
            $contrat->box->update(['statut' => 'libre']);

            // Désactiver les services
            $contrat->services()->update([
                'is_active' => false,
                'date_fin' => $request->date_fin,
            ]);

            DB::commit();

            return redirect()->route('contrats.show', $contrat)
                ->with('success', 'Contrat terminé avec succès.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Erreur lors de la résiliation : ' . $e->getMessage());
        }
    }

    public function destroy(Contrat $contrat)
    {
        if ($contrat->statut === 'actif') {
            return redirect()->route('contrats.index')
                ->with('error', 'Impossible de supprimer un contrat actif.');
        }

        if ($contrat->factures()->exists()) {
            return redirect()->route('contrats.index')
                ->with('error', 'Impossible de supprimer un contrat avec des factures associées.');
        }

        // Libérer la box si elle était réservée
        if ($contrat->box->statut === 'reserve') {
            $contrat->box->update(['statut' => 'libre']);
        }

        $contrat->delete();

        return redirect()->route('contrats.index')
            ->with('success', 'Contrat supprimé avec succès.');
    }

    private function generateNumeroContrat()
    {
        $prefix = config('boxibox.contract.prefix', 'CTR');
        $length = config('boxibox.contract.number_length', 6);
        $year = date('Y');

        $lastContract = Contrat::where('numero_contrat', 'like', "{$prefix}{$year}%")
            ->orderBy('numero_contrat', 'desc')
            ->first();

        if ($lastContract) {
            $lastNumber = (int) substr($lastContract->numero_contrat, -$length);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $year . str_pad($newNumber, $length, '0', STR_PAD_LEFT);
    }

    private function generateFactureCaution(Contrat $contrat)
    {
        $numeroFacture = $this->generateNumeroFacture();

        $facture = Facture::create([
            'client_id' => $contrat->client_id,
            'contrat_id' => $contrat->id,
            'numero_facture' => $numeroFacture,
            'type_facture' => 'caution',
            'date_emission' => now()->toDateString(),
            'date_echeance' => now()->addDays(config('boxibox.invoice.due_days', 30))->toDateString(),
            'montant_ht' => $contrat->caution,
            'taux_tva' => 0,
            'montant_tva' => 0,
            'montant_ttc' => $contrat->caution,
            'statut' => 'emise',
        ]);

        FactureLigne::create([
            'facture_id' => $facture->id,
            'designation' => 'Caution - Box ' . $contrat->box->numero,
            'quantite' => 1,
            'prix_unitaire' => $contrat->caution,
            'taux_tva' => 0,
            'montant_ht' => $contrat->caution,
            'montant_tva' => 0,
            'montant_ttc' => $contrat->caution,
        ]);

        return $facture;
    }

    private function generateFactureFraisDossier(Contrat $contrat)
    {
        $numeroFacture = $this->generateNumeroFacture();
        $tauxTva = config('boxibox.invoice.tva_rate', 20.00);
        $montantHT = $contrat->frais_dossier / (1 + $tauxTva / 100);
        $montantTva = $contrat->frais_dossier - $montantHT;

        $facture = Facture::create([
            'client_id' => $contrat->client_id,
            'contrat_id' => $contrat->id,
            'numero_facture' => $numeroFacture,
            'type_facture' => 'frais_dossier',
            'date_emission' => now()->toDateString(),
            'date_echeance' => now()->addDays(config('boxibox.invoice.due_days', 30))->toDateString(),
            'montant_ht' => $montantHT,
            'taux_tva' => $tauxTva,
            'montant_tva' => $montantTva,
            'montant_ttc' => $contrat->frais_dossier,
            'statut' => 'emise',
        ]);

        FactureLigne::create([
            'facture_id' => $facture->id,
            'designation' => 'Frais de dossier - Contrat ' . $contrat->numero_contrat,
            'quantite' => 1,
            'prix_unitaire' => $montantHT,
            'taux_tva' => $tauxTva,
            'montant_ht' => $montantHT,
            'montant_tva' => $montantTva,
            'montant_ttc' => $contrat->frais_dossier,
        ]);

        return $facture;
    }

    private function generateNumeroFacture()
    {
        $prefix = config('boxibox.invoice.prefix', 'FAC');
        $length = config('boxibox.invoice.number_length', 6);
        $year = date('Y');

        $lastFacture = Facture::where('numero_facture', 'like', "{$prefix}{$year}%")
            ->orderBy('numero_facture', 'desc')
            ->first();

        if ($lastFacture) {
            $lastNumber = (int) substr($lastFacture->numero_facture, -$length);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $year . str_pad($newNumber, $length, '0', STR_PAD_LEFT);
    }

    private function calculateDureeMois(Contrat $contrat)
    {
        if (!$contrat->date_fin) {
            return null;
        }

        $dateDebut = \Carbon\Carbon::parse($contrat->date_debut);
        $dateFin = \Carbon\Carbon::parse($contrat->date_fin);

        return $dateDebut->diffInMonths($dateFin);
    }
}