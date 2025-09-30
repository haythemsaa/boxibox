<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Langues supportées
        $availableLocales = ['fr', 'en'];
        $defaultLocale = 'fr';

        // 1. Vérifier si l'utilisateur a explicitement choisi une langue (param GET)
        if ($request->has('lang') && in_array($request->lang, $availableLocales)) {
            $locale = $request->lang;
            Session::put('locale', $locale);

            // Si l'utilisateur est connecté, sauvegarder sa préférence
            if (auth()->check()) {
                auth()->user()->update(['locale' => $locale]);
            }
        }
        // 2. Vérifier la session
        elseif (Session::has('locale') && in_array(Session::get('locale'), $availableLocales)) {
            $locale = Session::get('locale');
        }
        // 3. Vérifier les préférences utilisateur en base
        elseif (auth()->check() && auth()->user()->locale) {
            $locale = auth()->user()->locale;
            Session::put('locale', $locale);
        }
        // 4. Détecter depuis le navigateur (Accept-Language header)
        else {
            $browserLang = substr($request->server('HTTP_ACCEPT_LANGUAGE') ?? '', 0, 2);
            $locale = in_array($browserLang, $availableLocales) ? $browserLang : $defaultLocale;
            Session::put('locale', $locale);
        }

        // Appliquer la locale
        App::setLocale($locale);

        // Définir aussi la locale pour Carbon (dates)
        if ($locale === 'fr') {
            setlocale(LC_TIME, 'fr_FR.UTF-8', 'fra');
            \Carbon\Carbon::setLocale('fr');
        } else {
            setlocale(LC_TIME, 'en_US.UTF-8', 'eng');
            \Carbon\Carbon::setLocale('en');
        }

        return $next($request);
    }
}