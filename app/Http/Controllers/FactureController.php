<?php

namespace App\Http\Controllers;

use App\Models\Facture;
use App\Models\Client;
use App\Models\Contrat;
use Illuminate\Http\Request;

class FactureController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:view_factures']);
    }

    public function index(Request $request)
    {
        $query = Facture::with(['client', 'contrat'])
            ->orderBy('date_emission', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numero', 'LIKE', "%{$search}%")
                  ->orWhereHas('client', function ($clientQuery) use ($search) {
                      $clientQuery->where('nom', 'LIKE', "%{$search}%")
                                  ->orWhere('prenom', 'LIKE', "%{$search}%");
                  });
            });
        }

        $factures = $query->paginate(20);

        return view('factures.index', compact('factures'));
    }

    public function show(Facture $facture)
    {
        return view('factures.show', compact('facture'));
    }

    public function create()
    {
        $clients = Client::active()->get();
        $contrats = Contrat::actif()->with('client')->get();

        return view('factures.create', compact('clients', 'contrats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'contrat_id' => 'nullable|exists:contrats,id',
            'date_emission' => 'required|date',
            'date_echeance' => 'required|date|after:date_emission',
            'montant_ht' => 'required|numeric|min:0',
            'taux_tva' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string'
        ]);

        $validated['numero_facture'] = $this->generateNumeroFacture();
        $validated['montant_tva'] = $validated['montant_ht'] * ($validated['taux_tva'] / 100);
        $validated['montant_ttc'] = $validated['montant_ht'] + $validated['montant_tva'];
        $validated['statut'] = $request->input('statut', 'brouillon');

        $facture = Facture::create($validated);

        return redirect()->route('factures.show', $facture)
            ->with('success', 'Facture créée avec succès.');
    }

    public function edit(Facture $facture)
    {
        $clients = Client::active()->get();
        $contrats = Contrat::actif()->with('client')->get();

        return view('factures.edit', compact('facture', 'clients', 'contrats'));
    }

    public function update(Request $request, Facture $facture)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'contrat_id' => 'nullable|exists:contrats,id',
            'date_emission' => 'required|date',
            'date_echeance' => 'required|date|after:date_emission',
            'montant_ht' => 'required|numeric|min:0',
            'taux_tva' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string'
        ]);

        $validated['montant_tva'] = $validated['montant_ht'] * ($validated['taux_tva'] / 100);
        $validated['montant_ttc'] = $validated['montant_ht'] + $validated['montant_tva'];

        $facture->update($validated);

        return redirect()->route('factures.show', $facture)
            ->with('success', 'Facture modifiée avec succès.');
    }

    public function destroy(Facture $facture)
    {
        if ($facture->statut !== 'brouillon') {
            return redirect()->route('factures.index')
                ->with('error', 'Seules les factures en brouillon peuvent être supprimées.');
        }

        $facture->delete();

        return redirect()->route('factures.index')
            ->with('success', 'Facture supprimée avec succès.');
    }

    public function send(Facture $facture)
    {
        $facture->update([
            'statut' => 'envoyee',
            'date_envoi' => now()
        ]);

        // Envoyer l'email au client avec la facture en pièce jointe
        try {
            \Illuminate\Support\Facades\Mail::to($facture->client->email)
                ->send(new \App\Mail\FactureCreatedMail($facture));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erreur envoi email facture: ' . $e->getMessage());
        }

        return redirect()->route('factures.show', $facture)
            ->with('success', 'Facture envoyée avec succès.');
    }

    public function pdf(Facture $facture)
    {
        $facture->load(['client', 'lignes', 'reglements']);
        $client = $facture->client;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.facture', compact('facture', 'client'));

        return $pdf->download('facture_' . $facture->numero_facture . '.pdf');
    }

    /**
     * Afficher l'interface de facturation en masse
     */
    public function bulkGenerateView()
    {
        $contrats = Contrat::with(['client', 'box'])
            ->where('statut', 'actif')
            ->whereDoesntHave('factures', function($query) {
                $query->whereMonth('date_emission', now()->month)
                      ->whereYear('date_emission', now()->year);
            })
            ->get();

        return view('factures.bulk-generate', compact('contrats'));
    }

    /**
     * Génération groupée de factures
     */
    public function bulkGenerate(Request $request)
    {
        $validated = $request->validate([
            'contrat_ids' => 'nullable|array',
            'contrat_ids.*' => 'exists:contrats,id',
            'date_emission' => 'required|date',
            'date_echeance' => 'required|date|after:date_emission',
            'auto_send' => 'boolean',
            'generate_all' => 'boolean'
        ]);

        // Si generate_all, prendre tous les contrats actifs
        if ($request->generate_all) {
            $contrats = Contrat::where('statut', 'actif')
                ->whereDoesntHave('factures', function($query) use ($validated) {
                    $query->whereDate('date_emission', $validated['date_emission']);
                })
                ->get();
        } else {
            $contrats = Contrat::whereIn('id', $validated['contrat_ids'] ?? [])->get();
        }

        if ($contrats->isEmpty()) {
            return back()->with('warning', 'Aucun contrat à facturer.');
        }

        $factures = [];
        $errors = [];

        foreach ($contrats as $contrat) {
            try {
                // Calculer le montant du loyer + services
                $montantHT = $contrat->prix_mensuel;

                // Ajouter les services additionnels
                if ($contrat->services) {
                    foreach ($contrat->services as $service) {
                        $montantHT += $service->prix;
                    }
                }

                $tauxTVA = 20; // À récupérer depuis la config
                $montantTVA = $montantHT * ($tauxTVA / 100);
                $montantTTC = $montantHT + $montantTVA;

                $facture = Facture::create([
                    'numero' => $this->generateNumeroFacture(),
                    'client_id' => $contrat->client_id,
                    'contrat_id' => $contrat->id,
                    'date_emission' => $validated['date_emission'],
                    'date_echeance' => $validated['date_echeance'],
                    'montant_ht' => $montantHT,
                    'taux_tva' => $tauxTVA,
                    'montant_tva' => $montantTVA,
                    'montant_ttc' => $montantTTC,
                    'statut' => $request->auto_send ? 'envoyee' : 'brouillon',
                    'type_facture' => 'loyer',
                    'notes' => 'Facture mensuelle - Box ' . $contrat->box->numero
                ]);

                // Créer les lignes de facture
                $facture->lignes()->create([
                    'description' => 'Location Box ' . $contrat->box->numero . ' - ' . $contrat->box->surface . 'm²',
                    'quantite' => 1,
                    'prix_unitaire' => $contrat->prix_mensuel,
                    'montant' => $contrat->prix_mensuel,
                ]);

                // Si envoi automatique
                if ($request->auto_send) {
                    $facture->update(['date_envoi' => now()]);

                    // Envoyer l'email au client
                    try {
                        \Illuminate\Support\Facades\Mail::to($facture->client->email)
                            ->send(new \App\Mail\FactureCreatedMail($facture));
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Erreur envoi email facture bulk: ' . $e->getMessage());
                    }
                }

                $factures[] = $facture;

            } catch (\Exception $e) {
                $errors[] = [
                    'contrat' => $contrat->reference,
                    'error' => $e->getMessage()
                ];
            }
        }

        if (empty($errors)) {
            $message = count($factures) . ' facture(s) générée(s) avec succès.';
            if ($request->auto_send) {
                $message .= ' Les factures ont été envoyées aux clients.';
            }
            return redirect()->route('factures.index')->with('success', $message);
        } else {
            return redirect()->route('factures.index')
                ->with('warning', count($factures) . ' facture(s) générée(s), ' . count($errors) . ' erreur(s).')
                ->with('errors', $errors);
        }
    }

    private function generateNumeroFacture()
    {
        $year = now()->year;
        $lastFacture = Facture::where('numero_facture', 'LIKE', "F{$year}%")
            ->orderBy('numero_facture', 'desc')
            ->first();

        if ($lastFacture) {
            $lastNumber = (int) substr($lastFacture->numero_facture, 5);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('F%d%04d', $year, $newNumber);
    }
}