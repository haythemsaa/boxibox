<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email doit être valide.',
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        // Log de demande de réinitialisation
        \Log::info('Password reset requested', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'status' => $status
        ]);

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', 'Nous vous avons envoyé par email le lien de réinitialisation du mot de passe.')
                    : back()->withInput($request->only('email'))
                            ->withErrors(['email' => 'Nous ne trouvons pas d\'utilisateur avec cette adresse email.']);
    }
}