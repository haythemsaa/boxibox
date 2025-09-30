<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage_settings');
    }

    public function index()
    {
        $settings = $this->loadSettings();

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            // Paramètres généraux
            'app_name' => 'required|string|max:255',
            'app_description' => 'nullable|string|max:500',
            'contact_email' => 'required|email',
            'contact_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',

            // Paramètres SEPA
            'sepa_creditor_id' => 'required|string|max:35',
            'sepa_creditor_name' => 'required|string|max:70',
            'sepa_bic' => 'required|string|max:11',
            'sepa_iban' => 'required|string|max:34',

            // Paramètres factures
            'invoice_prefix' => 'required|string|max:10',
            'invoice_next_number' => 'required|integer|min:1',
            'vat_rate' => 'required|numeric|min:0|max:100',
            'late_fee_rate' => 'required|numeric|min:0|max:100',
            'payment_terms' => 'required|integer|min:1|max:365',

            // Paramètres notifications
            'enable_email_notifications' => 'boolean',
            'enable_sms_notifications' => 'boolean',
            'reminder_days_before' => 'required|array',
            'reminder_days_before.*' => 'integer|min:1',

            // Paramètres système
            'maintenance_mode' => 'boolean',
            'backup_retention_days' => 'required|integer|min:1',
            'log_retention_days' => 'required|integer|min:1',
        ], [
            'app_name.required' => 'Le nom de l\'application est obligatoire.',
            'contact_email.required' => 'L\'email de contact est obligatoire.',
            'contact_email.email' => 'L\'email de contact doit être valide.',
            'sepa_creditor_id.required' => 'L\'identifiant créancier SEPA est obligatoire.',
            'sepa_creditor_name.required' => 'Le nom du créancier SEPA est obligatoire.',
            'sepa_bic.required' => 'Le BIC est obligatoire.',
            'sepa_iban.required' => 'L\'IBAN est obligatoire.',
            'invoice_prefix.required' => 'Le préfixe des factures est obligatoire.',
            'invoice_next_number.required' => 'Le numéro de la prochaine facture est obligatoire.',
            'vat_rate.required' => 'Le taux de TVA est obligatoire.',
            'late_fee_rate.required' => 'Le taux des pénalités de retard est obligatoire.',
            'payment_terms.required' => 'Les conditions de paiement sont obligatoires.',
            'backup_retention_days.required' => 'La durée de rétention des sauvegardes est obligatoire.',
            'log_retention_days.required' => 'La durée de rétention des logs est obligatoire.',
        ]);

        $this->saveSettings($validated);

        // Clear cache
        Cache::forget('app_settings');

        return redirect()->route('admin.settings')
            ->with('success', 'Paramètres mis à jour avec succès.');
    }

    private function loadSettings()
    {
        return Cache::remember('app_settings', 3600, function () {
            $settingsPath = storage_path('app/settings.json');

            if (Storage::disk('local')->exists('settings.json')) {
                $settings = json_decode(Storage::disk('local')->get('settings.json'), true);
            } else {
                $settings = $this->getDefaultSettings();
                $this->saveSettings($settings);
            }

            return $settings;
        });
    }

    private function saveSettings(array $settings)
    {
        Storage::disk('local')->put('settings.json', json_encode($settings, JSON_PRETTY_PRINT));
    }

    private function getDefaultSettings()
    {
        return [
            // Paramètres généraux
            'app_name' => 'Boxibox',
            'app_description' => 'Système de gestion de self-stockage',
            'contact_email' => 'contact@boxibox.com',
            'contact_phone' => null,
            'address' => null,

            // Paramètres SEPA
            'sepa_creditor_id' => 'FR12ZZZ123456',
            'sepa_creditor_name' => 'Boxibox SARL',
            'sepa_bic' => 'BNPAFRPPXXX',
            'sepa_iban' => 'FR1420041010050500013M02606',

            // Paramètres factures
            'invoice_prefix' => 'BOX',
            'invoice_next_number' => 1,
            'vat_rate' => 20.0,
            'late_fee_rate' => 1.5,
            'payment_terms' => 30,

            // Paramètres notifications
            'enable_email_notifications' => true,
            'enable_sms_notifications' => false,
            'reminder_days_before' => [7, 3, 1],

            // Paramètres système
            'maintenance_mode' => false,
            'backup_retention_days' => 30,
            'log_retention_days' => 90,
        ];
    }

    public function getSetting($key, $default = null)
    {
        $settings = $this->loadSettings();
        return $settings[$key] ?? $default;
    }

    public function setSetting($key, $value)
    {
        $settings = $this->loadSettings();
        $settings[$key] = $value;
        $this->saveSettings($settings);
        Cache::forget('app_settings');
    }
}