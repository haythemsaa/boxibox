<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de paiement</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 28px;
        }
        .email-header .icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .email-body {
            padding: 40px 30px;
        }
        .email-body h2 {
            color: #667eea;
            margin-top: 0;
        }
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info-box strong {
            color: #28a745;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .details-table th {
            text-align: left;
            padding: 10px;
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }
        .details-table td {
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        .details-table tr:last-child td {
            border-bottom: none;
        }
        .amount {
            font-size: 24px;
            font-weight: bold;
            color: #28a745;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #667eea;
            color: #ffffff;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #5568d3;
        }
        .email-footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
        }
        .divider {
            height: 1px;
            background-color: #dee2e6;
            margin: 30px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <div class="icon">✅</div>
            <h1>Paiement confirmé</h1>
            <p style="margin: 10px 0 0 0; font-size: 16px;">Votre paiement a été traité avec succès</p>
        </div>

        <!-- Body -->
        <div class="email-body">
            <h2>Bonjour {{ $client->prenom }} {{ $client->nom }},</h2>

            <p>
                Nous vous confirmons la bonne réception de votre paiement en ligne pour la facture
                <strong>{{ $facture->numero_facture }}</strong>.
            </p>

            <div class="info-box">
                <strong>✓ Votre facture est maintenant soldée</strong><br>
                Vous n'avez plus rien à payer pour cette facture.
            </div>

            <h3>Détails du paiement</h3>
            <table class="details-table">
                <tr>
                    <th width="40%">Numéro de facture</th>
                    <td><strong>{{ $facture->numero_facture }}</strong></td>
                </tr>
                @if($facture->contrat)
                <tr>
                    <th>Contrat</th>
                    <td>{{ $facture->contrat->numero_contrat }}</td>
                </tr>
                @endif
                <tr>
                    <th>Date du paiement</th>
                    <td>{{ $date_paiement }}</td>
                </tr>
                <tr>
                    <th>Mode de paiement</th>
                    <td>Carte bancaire (Stripe)</td>
                </tr>
                <tr>
                    <th>Référence de transaction</th>
                    <td style="font-family: monospace; font-size: 11px;">{{ $reglement->reference }}</td>
                </tr>
                <tr>
                    <th>Montant payé</th>
                    <td class="amount">{{ $montant }}</td>
                </tr>
            </table>

            <div class="divider"></div>

            <h3>Que faire ensuite ?</h3>
            <p>
                • Vous pouvez télécharger votre facture depuis votre espace client<br>
                • Un reçu de paiement est disponible dans la section "Règlements"<br>
                • Conservez ce email comme preuve de paiement
            </p>

            <center>
                <a href="{{ config('app.url') }}/client/factures/{{ $facture->id }}" class="button">
                    Voir ma facture
                </a>
            </center>

            <div class="divider"></div>

            <p style="font-size: 14px; color: #6c757d;">
                <strong>Besoin d'aide ?</strong><br>
                N'hésitez pas à nous contacter si vous avez des questions concernant ce paiement.
            </p>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p>
                <strong>Boxibox</strong><br>
                Cet email a été envoyé automatiquement, merci de ne pas y répondre directement.
            </p>
            <p style="margin-top: 10px;">
                © {{ date('Y') }} Boxibox. Tous droits réservés.
            </p>
        </div>
    </div>
</body>
</html>
