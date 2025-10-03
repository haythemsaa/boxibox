<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Les informations de connexion ne correspondent pas à nos enregistrements.',
            ]);
        }

        $request->session()->regenerate();

        // Mettre à jour la date de dernière connexion
        Auth::user()->update(['last_login_at' => now()]);

        // Log de connexion
        \Log::info('User logged in', [
            'user_id' => Auth::id(),
            'email' => Auth::user()->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        // Redirection selon le type d'utilisateur
        $user = Auth::user();

        \Log::info('User login redirect', [
            'user_id' => $user->id,
            'type_user' => $user->type_user,
            'isSuperAdmin' => $user->isSuperAdmin(),
            'isClientFinal' => $user->isClientFinal(),
        ]);

        // Déterminer la route de redirection selon le type d'utilisateur
        if ($user->isSuperAdmin()) {
            $redirectRoute = 'superadmin.dashboard';
        } elseif ($user->isClientFinal()) {
            $redirectRoute = 'client.dashboard';
        } else {
            $redirectRoute = 'dashboard';
        }

        // Redirection - utiliser intended() seulement si l'URL précédente n'était pas /dashboard
        $intended = session('url.intended');
        if ($intended && !str_contains($intended, '/dashboard')) {
            return redirect()->intended(route($redirectRoute));
        }

        return redirect()->route($redirectRoute);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Log de déconnexion
        \Log::info('User logged out', [
            'user_id' => Auth::id(),
            'email' => Auth::user()->email
        ]);

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}