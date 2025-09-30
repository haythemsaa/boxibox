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

        $validated['numero'] = $this->generateNumeroFacture();
        $validated['montant_tva'] = $validated['montant_ht'] * ($validated['taux_tva'] / 100);
        $validated['montant_ttc'] = $validated['montant_ht'] + $validated['montant_tva'];
        $validated['statut'] = 'brouillon';

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

        return redirect()->route('factures.show', $facture)
            ->with('success', 'Facture envoyée avec succès.');
    }

    public function pdf(Facture $facture)
    {
        // TODO: Implement PDF generation
        return response()->json(['message' => 'PDF generation not implemented yet']);
    }

    public function bulkGenerate(Request $request)
    {
        $validated = $request->validate([
            'contrat_ids' => 'required|array',
            'contrat_ids.*' => 'exists:contrats,id',
            'date_emission' => 'required|date'
        ]);

        $contrats = Contrat::whereIn('id', $validated['contrat_ids'])->get();
        $factures = [];

        foreach ($contrats as $contrat) {
            $facture = Facture::create([
                'numero' => $this->generateNumeroFacture(),
                'client_id' => $contrat->client_id,
                'contrat_id' => $contrat->id,
                'date_emission' => $validated['date_emission'],
                'date_echeance' => now()->parse($validated['date_emission'])->addDays(30),
                'montant_ht' => $contrat->prix_mensuel,
                'taux_tva' => 20,
                'montant_tva' => $contrat->prix_mensuel * 0.20,
                'montant_ttc' => $contrat->prix_mensuel * 1.20,
                'statut' => 'brouillon',
                'description' => 'Facture mensuelle - Box ' . $contrat->box->numero
            ]);

            $factures[] = $facture;
        }

        return redirect()->route('factures.index')
            ->with('success', count($factures) . ' factures générées avec succès.');
    }

    private function generateNumeroFacture()
    {
        $year = now()->year;
        $lastFacture = Facture::where('numero', 'LIKE', "F{$year}%")
            ->orderBy('numero', 'desc')
            ->first();

        if ($lastFacture) {
            $lastNumber = (int) substr($lastFacture->numero, 5);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('F%d%04d', $year, $newNumber);
    }
}