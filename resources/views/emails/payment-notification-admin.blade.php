<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau paiement en ligne</title>
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
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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
            color: #28a745;
            margin-top: 0;
        }
        .alert-box {
            background-color: #d4edda;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .alert-box strong {
            color: #155724;
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
            font-weight: 600;
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
        .section-title {
            background-color: #f8f9fa;
            padding: 10px 15px;
            border-left: 4px solid #28a745;
            margin: 25px 0 15px 0;
            font-weight: 600;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #28a745;
            color: #ffffff;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #218838;
        }
        .email-footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-success {
            background-color: #28a745;
            color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <div class="icon">💳</div>
            <h1>Nouveau paiement en ligne</h1>
            <p style="margin: 10px 0 0 0; font-size: 16px;">Un client vient de payer en ligne via Stripe</p>
        </div>

        <!-- Body -->
        <div class="email-body">
            <div class="alert-box">
                <strong>✓ Paiement validé et traité automatiquement</strong><br>
                La facture a été marquée comme payée et le règlement a été créé.
            </div>

            <div class="section-title">
                💰 Informations du paiement
            </div>

            <table class="details-table">
                <tr>
                    <th width="40%">Montant reçu</th>
                    <td class="amount">{{ $montant }}</td>
                </tr>
                <tr>
                    <th>Date du paiement</th>
                    <td>{{ $date_paiement }}</td>
                </tr>
                <tr>
                    <th>Mode de paiement</th>
                    <td>
                        <span class="badge badge-success">STRIPE</span>
                        Carte bancaire en ligne
                    </td>
                </tr>
                <tr>
                    <th>Référence Stripe</th>
                    <td style="font-family: monospace; font-size: 11px;">{{ $reglement->reference }}</td>
                </tr>
            </table>

            <div class="section-title">
                📄 Facture associée
            </div>

            <table class="details-table">
                <tr>
                    <th width="40%">Numéro de facture</th>
                    <td><strong>{{ $facture->numero_facture }}</strong></td>
                </tr>
                <tr>
                    <th>Date d'émission</th>
                    <td>{{ $facture->date_emission->format('d/m/Y') }}</td>
                </tr>
                @if($facture->contrat)
                <tr>
                    <th>Contrat associé</th>
                    <td>{{ $facture->contrat->numero_contrat }}</td>
                </tr>
                @endif
                <tr>
                    <th>Statut</th>
                    <td><span class="badge badge-success">PAYÉE</span></td>
                </tr>
            </table>

            <div class="section-title">
                👤 Client
            </div>

            <table class="details-table">
                <tr>
                    <th width="40%">Nom</th>
                    <td><strong>{{ $client->prenom }} {{ $client->nom }}</strong></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>{{ $client->email }}</td>
                </tr>
                <tr>
                    <th>Téléphone</th>
                    <td>{{ $client->telephone ?? 'Non renseigné' }}</td>
                </tr>
            </table>

            <center>
                <a href="{{ config('app.url') }}/finance/factures/{{ $facture->id }}" class="button">
                    Voir la facture dans l'admin
                </a>
            </center>

            <p style="margin-top: 30px; font-size: 14px; color: #6c757d; background-color: #f8f9fa; padding: 15px; border-radius: 4px;">
                <strong>ℹ️ Actions automatiques effectuées :</strong><br>
                • Règlement créé avec référence Stripe<br>
                • Facture marquée comme "payée"<br>
                • Email de confirmation envoyé au client<br>
                • Montant enregistré dans le journal des paiements
            </p>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p>
                <strong>Boxibox - Notification automatique</strong><br>
                Cet email a été généré automatiquement par le webhook Stripe.
            </p>
            <p style="margin-top: 10px;">
                © {{ date('Y') }} Boxibox. Tous droits réservés.
            </p>
        </div>
    </div>
</body>
</html>
