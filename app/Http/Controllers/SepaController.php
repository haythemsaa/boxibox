<?php

namespace App\Http\Controllers;

use App\Models\SepaManadat;
use App\Models\Client;
use Illuminate\Http\Request;

class SepaController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:view_sepa']);
    }

    public function index(Request $request)
    {
        $query = SepaManadat::with('client')
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('rum', 'LIKE', "%{$search}%")
                  ->orWhere('iban', 'LIKE', "%{$search}%")
                  ->orWhereHas('client', function ($clientQuery) use ($search) {
                      $clientQuery->where('nom', 'LIKE', "%{$search}%")
                                  ->orWhere('prenom', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $mandats = $query->paginate(20);

        return view('sepa.index', compact('mandats'));
    }

    public function show(SepaManadat $mandat)
    {
        return view('sepa.show', compact('mandat'));
    }

    public function create()
    {
        $clients = Client::active()->get();

        return view('sepa.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'titulaire' => 'required|string|max:255',
            'iban' => 'required|string|size:27', // IBAN français = 27 caractères
            'bic' => 'required|string|min:8|max:11',
            'date_signature' => 'required|date',
            'type_paiement' => 'required|in:recurrent,ponctuel',
            'statut' => 'required|in:actif,suspendu,annule'
        ]);

        // Générer le RUM (Référence Unique du Mandat)
        $validated['rum'] = $this->generateRUM();
        $validated['date_creation'] = now();

        $mandat = SepaManadat::create($validated);

        return redirect()->route('sepa.show', $mandat)
            ->with('success', 'Mandat SEPA créé avec succès.');
    }

    public function edit(SepaManadat $mandat)
    {
        $clients = Client::active()->get();

        return view('sepa.edit', compact('mandat', 'clients'));
    }

    public function update(Request $request, SepaManadat $mandat)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'titulaire' => 'required|string|max:255',
            'iban' => 'required|string|size:27',
            'bic' => 'required|string|min:8|max:11',
            'date_signature' => 'required|date',
            'type_paiement' => 'required|in:recurrent,ponctuel',
            'statut' => 'required|in:actif,suspendu,annule'
        ]);

        $mandat->update($validated);

        return redirect()->route('sepa.show', $mandat)
            ->with('success', 'Mandat SEPA modifié avec succès.');
    }

    public function destroy(SepaManadat $mandat)
    {
        if ($mandat->statut === 'actif') {
            return redirect()->route('sepa.index')
                ->with('error', 'Un mandat actif ne peut pas être supprimé. Veuillez d\'abord l\'annuler.');
        }

        $mandat->delete();

        return redirect()->route('sepa.index')
            ->with('success', 'Mandat SEPA supprimé avec succès.');
    }

    public function activate(SepaManadat $mandat)
    {
        if (!$mandat->canBeActivated()) {
            return redirect()->route('sepa.show', $mandat)
                ->with('error', 'Ce mandat ne peut pas être activé.');
        }

        $mandat->update([
            'statut' => 'actif',
            'date_activation' => now()
        ]);

        return redirect()->route('sepa.show', $mandat)
            ->with('success', 'Mandat SEPA activé avec succès.');
    }

    public function export($type)
    {
        switch ($type) {
            case 'pain008':
                return $this->exportPain008();
            case 'pain001':
                return $this->exportPain001();
            default:
                return redirect()->route('sepa.index')
                    ->with('error', 'Type d\'export non supporté.');
        }
    }

    public function importReturns(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xml'
        ]);

        // TODO: Implement SEPA returns import logic
        return redirect()->route('sepa.index')
            ->with('success', 'Fichier de retours SEPA traité avec succès.');
    }

    private function generateRUM()
    {
        $prefix = config('app.sepa_creditor_id', 'FR');
        $date = now()->format('Ymd');
        $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

        return $prefix . $date . $random;
    }

    private function exportPain008()
    {
        // TODO: Implement PAIN.008 (Direct Debit) export
        return response()->json(['message' => 'Export PAIN.008 non implémenté']);
    }

    private function exportPain001()
    {
        // TODO: Implement PAIN.001 (Credit Transfer) export
        return response()->json(['message' => 'Export PAIN.001 non implémenté']);
    }
}