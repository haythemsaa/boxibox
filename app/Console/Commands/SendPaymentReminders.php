<?php

namespace App\Console\Commands;

use App\Models\Facture;
use App\Models\PaymentReminder;
use App\Models\ReminderConfig;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendPaymentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:send-payment
                            {--type=preventif : Type de rappel (preventif, relance)}
                            {--dry-run : Simule l\'envoi sans envoyer réellement}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoie des rappels automatiques de paiement aux clients';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 Mode simulation activé - Aucun email/SMS ne sera envoyé');
        }

        $this->info('📧 Démarrage de l\'envoi des rappels de paiement...');

        // Récupérer la configuration
        $config = ReminderConfig::where('type', 'payment')
            ->where('active', true)
            ->first();

        if (!$config) {
            $this->warn('⚠️  Aucune configuration de rappels trouvée. Utilisation des valeurs par défaut.');
            $config = (object) [
                'days_before' => 5,
                'send_email' => true,
                'send_sms' => false,
                'email_template' => $this->getDefaultEmailTemplate(),
                'sms_template' => $this->getDefaultSmsTemplate(),
            ];
        }

        // Calculer la date cible
        $targetDate = Carbon::now()->addDays($config->days_before);

        // Récupérer les factures à échéance
        $factures = Facture::where('statut', 'impayee')
            ->whereDate('date_echeance', $targetDate->format('Y-m-d'))
            ->whereDoesntHave('reminders', function ($query) use ($type) {
                $query->where('type', $type)
                    ->whereDate('date_envoi', Carbon::today());
            })
            ->with(['client'])
            ->get();

        if ($factures->isEmpty()) {
            $this->info('✅ Aucune facture à traiter pour cette échéance.');
            return 0;
        }

        $this->info("📊 {$factures->count()} facture(s) trouvée(s) arrivant à échéance le {$targetDate->format('d/m/Y')}");

        $sent = 0;
        $errors = 0;

        $this->output->progressStart($factures->count());

        foreach ($factures as $facture) {
            try {
                // Préparer le message
                $data = [
                    'client_nom' => $facture->client->nom,
                    'facture_numero' => $facture->numero,
                    'montant' => number_format($facture->montant_ttc, 2) . '€',
                    'date_echeance' => Carbon::parse($facture->date_echeance)->format('d/m/Y'),
                    'jours_restants' => $config->days_before,
                ];

                $emailMessage = $this->parseTemplate($config->email_template, $data);
                $smsMessage = $this->parseTemplate($config->sms_template, $data);

                if (!$dryRun) {
                    // Envoyer l'email
                    if ($config->send_email && $facture->client->email) {
                        Mail::send([], [], function ($message) use ($facture, $emailMessage, $data) {
                            $message->to($facture->client->email)
                                ->subject("Rappel : Facture #{$data['facture_numero']} à échéance")
                                ->html($emailMessage);
                        });
                    }

                    // Envoyer le SMS (si configuré)
                    if ($config->send_sms && $facture->client->telephone) {
                        // TODO: Intégration avec un service SMS (Twilio, etc.)
                        // $this->sendSms($facture->client->telephone, $smsMessage);
                    }

                    // Enregistrer dans l'historique
                    PaymentReminder::create([
                        'facture_id' => $facture->id,
                        'client_id' => $facture->client_id,
                        'type' => $type,
                        'canal' => $this->getCanal($config),
                        'date_envoi' => Carbon::now(),
                        'email_sent' => $config->send_email,
                        'sms_sent' => $config->send_sms,
                        'message' => $emailMessage,
                    ]);
                }

                $sent++;
                $this->output->progressAdvance();

            } catch (\Exception $e) {
                $errors++;
                Log::error("Erreur lors de l'envoi du rappel pour la facture #{$facture->numero}: " . $e->getMessage());
                $this->output->progressAdvance();
            }
        }

        $this->output->progressFinish();

        // Résumé
        $this->newLine();
        $this->info("✅ Rappels envoyés : $sent");
        if ($errors > 0) {
            $this->warn("⚠️  Erreurs rencontrées : $errors");
        }

        return 0;
    }

    /**
     * Parse un template avec les données
     */
    private function parseTemplate($template, $data)
    {
        foreach ($data as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }
        return $template;
    }

    /**
     * Détermine le canal utilisé
     */
    private function getCanal($config)
    {
        if ($config->send_email && $config->send_sms) {
            return 'both';
        } elseif ($config->send_email) {
            return 'email';
        } elseif ($config->send_sms) {
            return 'sms';
        }
        return 'email';
    }

    /**
     * Template email par défaut
     */
    private function getDefaultEmailTemplate()
    {
        return <<<HTML
<html>
<body style="font-family: Arial, sans-serif;">
    <h2>Bonjour {{client_nom}},</h2>

    <p>Nous vous rappelons que votre facture <strong>{{facture_numero}}</strong> d'un montant de <strong>{{montant}}</strong> arrive à échéance dans <strong>{{jours_restants}} jours</strong>, soit le <strong>{{date_echeance}}</strong>.</p>

    <p>Pour éviter tout désagrément, merci de procéder au règlement avant cette date.</p>

    <p>Si vous avez déjà effectué ce paiement, veuillez ne pas tenir compte de ce message.</p>

    <p>Cordialement,<br>L'équipe BoxiBox</p>
</body>
</html>
HTML;
    }

    /**
     * Template SMS par défaut
     */
    private function getDefaultSmsTemplate()
    {
        return 'BoxiBox : Rappel - Facture {{facture_numero}} ({{montant}}) à échéance le {{date_echeance}}. Merci de procéder au règlement.';
    }
}