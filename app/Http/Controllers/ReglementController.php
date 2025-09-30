<?php

namespace App\Http\Controllers;

use App\Models\Reglement;
use App\Models\Facture;
use App\Models\Client;
use Illuminate\Http\Request;

class ReglementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:view_reglements']);
    }

    public function index(Request $request)
    {
        $query = Reglement::with(['facture.client'])
            ->orderBy('date_reglement', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'LIKE', "%{$search}%")
                  ->orWhereHas('facture.client', function ($clientQuery) use ($search) {
                      $clientQuery->where('nom', 'LIKE', "%{$search}%")
                                  ->orWhere('prenom', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $reglements = $query->paginate(20);

        return view('reglements.index', compact('reglements'));
    }

    public function show(Reglement $reglement)
    {
        return view('reglements.show', compact('reglement'));
    }

    public function create()
    {
        $factures = Facture::where('statut', '!=', 'payee')
            ->with('client')
            ->orderBy('date_emission', 'desc')
            ->get();

        return view('reglements.create', compact('factures'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'facture_id' => 'required|exists:factures,id',
            'date_reglement' => 'required|date',
            'montant' => 'required|numeric|min:0',
            'mode_reglement' => 'required|in:virement,cheque,especes,carte_bancaire,prelevement',
            'reference' => 'nullable|string|max:255',
            'statut' => 'required|in:en_attente,valide,rejete',
            'notes' => 'nullable|string'
        ]);

        $facture = Facture::findOrFail($validated['facture_id']);

        // Vérifier que le montant ne dépasse pas le solde dû
        $totalReglements = $facture->reglements()->where('statut', 'valide')->sum('montant');
        $solde = $facture->montant_ttc - $totalReglements;

        if ($validated['montant'] > $solde) {
            return back()->withErrors(['montant' => 'Le montant du règlement ne peut pas dépasser le solde dû (' . number_format($solde, 2) . '€)']);
        }

        $reglement = Reglement::create($validated);

        // Mettre à jour le statut de la facture si elle est entièrement payée
        if ($reglement->statut === 'valide') {
            $totalReglements = $facture->reglements()->where('statut', 'valide')->sum('montant');
            if ($totalReglements >= $facture->montant_ttc) {
                $facture->update(['statut' => 'payee']);
            }
        }

        return redirect()->route('reglements.show', $reglement)
            ->with('success', 'Règlement enregistré avec succès.');
    }

    public function edit(Reglement $reglement)
    {
        $factures = Facture::where('statut', '!=', 'payee')
            ->with('client')
            ->orderBy('date_emission', 'desc')
            ->get();

        return view('reglements.edit', compact('reglement', 'factures'));
    }

    public function update(Request $request, Reglement $reglement)
    {
        $validated = $request->validate([
            'facture_id' => 'required|exists:factures,id',
            'date_reglement' => 'required|date',
            'montant' => 'required|numeric|min:0',
            'mode_reglement' => 'required|in:virement,cheque,especes,carte_bancaire,prelevement',
            'reference' => 'nullable|string|max:255',
            'statut' => 'required|in:en_attente,valide,rejete',
            'notes' => 'nullable|string'
        ]);

        $oldStatut = $reglement->statut;
        $reglement->update($validated);

        // Recalculer le statut de la facture
        $facture = $reglement->facture;
        $totalReglements = $facture->reglements()->where('statut', 'valide')->sum('montant');

        if ($totalReglements >= $facture->montant_ttc) {
            $facture->update(['statut' => 'payee']);
        } else {
            $facture->update(['statut' => 'envoyee']);
        }

        return redirect()->route('reglements.show', $reglement)
            ->with('success', 'Règlement modifié avec succès.');
    }

    public function destroy(Reglement $reglement)
    {
        $facture = $reglement->facture;
        $reglement->delete();

        // Recalculer le statut de la facture
        $totalReglements = $facture->reglements()->where('statut', 'valide')->sum('montant');

        if ($totalReglements >= $facture->montant_ttc) {
            $facture->update(['statut' => 'payee']);
        } else {
            $facture->update(['statut' => 'envoyee']);
        }

        return redirect()->route('reglements.index')
            ->with('success', 'Règlement supprimé avec succès.');
    }
}