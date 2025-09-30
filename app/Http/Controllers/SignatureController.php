<?php

namespace App\Http\Controllers;

use App\Models\Signature;
use App\Models\Client;
use App\Models\Contrat;
use App\Models\MandatSepa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SignatureController extends Controller
{
    /**
     * Liste des signatures
     */
    public function index(Request $request)
    {
        $query = Signature::with(['client', 'signable']);

        // Filtrage par statut
        if ($request->has('statut') && $request->statut !== 'all') {
            $query->where('statut', $request->statut);
        }

        // Filtrage par type de document
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('signable_type', $request->type);
        }

        // Recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('signataire_nom', 'like', "%{$search}%")
                  ->orWhere('signataire_email', 'like', "%{$search}%");
            });
        }

        $signatures = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('signatures.index', compact('signatures'));
    }

    /**
     * Demander une signature pour un contrat
     */
    public function requestContractSignature(Request $request, $contratId)
    {
        $contrat = Contrat::findOrFail($contratId);
        $client = $contrat->client;

        $validated = $request->validate([
            'signature_method' => 'required|in:simple,advanced,qualified',
            'expiration_days' => 'nullable|integer|min:1|max:30',
            'notes' => 'nullable|string',
        ]);

        // Créer la demande de signature
        $signature = Signature::create([
            'signable_type' => Contrat::class,
            'signable_id' => $contrat->id,
            'client_id' => $client->id,
            'signataire_nom' => $client->nom . ' ' . $client->prenom,
            'signataire_email' => $client->email,
            'signataire_telephone' => $client->telephone,
            'statut' => 'en_attente',
            'signature_method' => $validated['signature_method'],
            'date_envoi' => now(),
            'date_expiration' => isset($validated['expiration_days'])
                ? now()->addDays($validated['expiration_days'])
                : now()->addDays(7),
            'token' => Signature::generateToken(),
            'notes' => $validated['notes'] ?? null,
        ]);

        // Envoyer l'email avec le lien de signature
        $this->sendSignatureEmail($signature);

        return redirect()->back()->with('success', 'Demande de signature envoyée au client');
    }

    /**
     * Demander une signature pour un mandat SEPA
     */
    public function requestSepaSignature(Request $request, $mandatId)
    {
        $mandat = MandatSepa::findOrFail($mandatId);
        $client = $mandat->client;

        $validated = $request->validate([
            'signature_method' => 'required|in:simple,advanced,qualified',
            'expiration_days' => 'nullable|integer|min:1|max:30',
            'notes' => 'nullable|string',
        ]);

        // Créer la demande de signature
        $signature = Signature::create([
            'signable_type' => MandatSepa::class,
            'signable_id' => $mandat->id,
            'client_id' => $client->id,
            'signataire_nom' => $client->nom . ' ' . $client->prenom,
            'signataire_email' => $client->email,
            'signataire_telephone' => $client->telephone,
            'statut' => 'en_attente',
            'signature_method' => $validated['signature_method'],
            'date_envoi' => now(),
            'date_expiration' => isset($validated['expiration_days'])
                ? now()->addDays($validated['expiration_days'])
                : now()->addDays(7),
            'token' => Signature::generateToken(),
            'notes' => $validated['notes'] ?? null,
        ]);

        // Envoyer l'email avec le lien de signature
        $this->sendSignatureEmail($signature);

        return redirect()->back()->with('success', 'Demande de signature envoyée au client');
    }

    /**
     * Afficher la page de signature (pour le client)
     */
    public function show($token)
    {
        $signature = Signature::where('token', $token)->firstOrFail();

        // Vérifier si la signature est encore valide
        $signature->checkExpiration();

        if (!$signature->isPending()) {
            return view('signatures.expired', compact('signature'));
        }

        return view('signatures.sign', compact('signature'));
    }

    /**
     * Traiter la signature (soumission du client)
     */
    public function sign(Request $request, $token)
    {
        $signature = Signature::where('token', $token)->firstOrFail();

        // Vérifier si la signature est encore valide
        $signature->checkExpiration();

        if (!$signature->isPending()) {
            return redirect()->route('signatures.show', $token)
                ->with('error', 'Cette demande de signature n\'est plus valide');
        }

        $validated = $request->validate([
            'signature_data' => 'required|string',
            'accept_terms' => 'required|accepted',
        ]);

        // Enregistrer la signature
        $signature->update([
            'statut' => 'signe',
            'signature_data' => $validated['signature_data'],
            'signature_ip' => $request->ip(),
            'date_signature' => now(),
        ]);

        // Mettre à jour le statut du document signé
        $this->updateSignableStatus($signature);

        return view('signatures.success', compact('signature'));
    }

    /**
     * Refuser une signature
     */
    public function refuse(Request $request, $token)
    {
        $signature = Signature::where('token', $token)->firstOrFail();

        $signature->update([
            'statut' => 'refuse',
            'notes' => $request->input('reason', 'Refusé par le client'),
        ]);

        return view('signatures.refused', compact('signature'));
    }

    /**
     * Renvoyer un email de demande de signature
     */
    public function resend($id)
    {
        $signature = Signature::findOrFail($id);

        if ($signature->isSigned()) {
            return redirect()->back()->with('error', 'Ce document est déjà signé');
        }

        // Générer un nouveau token et prolonger l'expiration
        $signature->update([
            'token' => Signature::generateToken(),
            'date_envoi' => now(),
            'date_expiration' => now()->addDays(7),
            'statut' => 'en_attente',
        ]);

        $this->sendSignatureEmail($signature);

        return redirect()->back()->with('success', 'Email de signature renvoyé');
    }

    /**
     * Annuler une demande de signature
     */
    public function cancel($id)
    {
        $signature = Signature::findOrFail($id);

        $signature->update([
            'statut' => 'expire',
            'notes' => 'Annulé par l\'administrateur',
        ]);

        return redirect()->back()->with('success', 'Demande de signature annulée');
    }

    /**
     * Envoyer l'email avec le lien de signature
     */
    private function sendSignatureEmail(Signature $signature)
    {
        $signatureUrl = route('signatures.show', $signature->token);

        // TODO: Implémenter l'envoi d'email réel avec Mail::send()
        // Pour l'instant, on log juste l'URL
        \Log::info('Signature email would be sent', [
            'email' => $signature->signataire_email,
            'url' => $signatureUrl,
            'type' => $signature->signable_type,
            'expires' => $signature->date_expiration,
        ]);

        // Exemple de code pour envoyer l'email:
        /*
        Mail::send('emails.signature-request', [
            'signature' => $signature,
            'url' => $signatureUrl,
        ], function($message) use ($signature) {
            $message->to($signature->signataire_email, $signature->signataire_nom)
                    ->subject('Demande de signature électronique - BoxiBox');
        });
        */
    }

    /**
     * Mettre à jour le statut du document après signature
     */
    private function updateSignableStatus(Signature $signature)
    {
        $signable = $signature->signable;

        if ($signable instanceof Contrat) {
            // Si le contrat était en attente, le passer à actif
            if ($signable->statut === 'en_attente') {
                $signable->update(['statut' => 'actif']);
            }
        } elseif ($signable instanceof MandatSepa) {
            // Si le mandat était inactif, le passer à actif
            if ($signable->statut === 'inactif') {
                $signable->update([
                    'statut' => 'actif',
                    'date_signature' => now(),
                ]);
            }
        }
    }
}