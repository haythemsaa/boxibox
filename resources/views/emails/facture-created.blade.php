<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle Facture</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0; font-size: 28px;">BOXIBOX</h1>
        <p style="color: #e7f3ff; margin: 5px 0 0 0; font-size: 14px;">Gestion de Self-Stockage</p>
    </div>

    <div style="background-color: #ffffff; padding: 30px; border: 1px solid #dee2e6; border-top: none;">
        <h2 style="color: #0d6efd; margin-top: 0;">Bonjour {{ $client->prenom }} {{ $client->nom }},</h2>

        <p>Nous vous informons qu'une nouvelle facture est disponible sur votre espace client.</p>

        <div style="background-color: #f8f9fa; padding: 20px; border-left: 4px solid #0d6efd; margin: 25px 0;">
            <h3 style="margin-top: 0; color: #0d6efd; font-size: 18px;">Détails de la facture</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #6c757d;">Numéro :</td>
                    <td style="padding: 8px 0;">{{ $facture->numero_facture }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #6c757d;">Date d'émission :</td>
                    <td style="padding: 8px 0;">{{ $facture->date_emission->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #6c757d;">Date d'échéance :</td>
                    <td style="padding: 8px 0;">{{ $facture->date_echeance->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #6c757d;">Montant TTC :</td>
                    <td style="padding: 8px 0; font-size: 20px; font-weight: bold; color: #0d6efd;">
                        {{ number_format($facture->montant_ttc, 2, ',', ' ') }} €
                    </td>
                </tr>
                @if($facture->contrat)
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #6c757d;">Contrat :</td>
                    <td style="padding: 8px 0;">{{ $facture->contrat->numero_contrat }}</td>
                </tr>
                @endif
            </table>
        </div>

        <p>La facture est jointe à cet email au format PDF. Vous pouvez également la consulter à tout moment depuis votre espace client.</p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ url('/client/factures/' . $facture->id) }}"
               style="background-color: #0d6efd; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
                Voir ma facture
            </a>
        </div>

        @if($facture->date_echeance && $facture->date_echeance->isFuture())
        <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0;">
            <strong style="color: #856404;">⚠️ Échéance de paiement</strong>
            <p style="margin: 5px 0 0 0; color: #856404;">
                Cette facture est à régler avant le <strong>{{ $facture->date_echeance->format('d/m/Y') }}</strong>.
            </p>
        </div>
        @endif

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6;">
            <h4 style="color: #6c757d; font-size: 16px;">Modes de paiement acceptés</h4>
            <ul style="color: #6c757d; padding-left: 20px;">
                <li>Prélèvement automatique SEPA</li>
                <li>Virement bancaire</li>
                <li>Carte bancaire (sur votre espace client)</li>
                <li>Chèque</li>
            </ul>
        </div>

        <p style="margin-top: 30px; color: #6c757d; font-size: 14px;">
            Pour toute question concernant cette facture, n'hésitez pas à nous contacter.
        </p>
    </div>

    <div style="background-color: #f8f9fa; padding: 20px; text-align: center; border-radius: 0 0 10px 10px; border: 1px solid #dee2e6; border-top: none;">
        <p style="margin: 0 0 10px 0; color: #6c757d; font-size: 14px;">
            <strong>BOXIBOX</strong> - Self-Stockage
        </p>
        <p style="margin: 0; color: #6c757d; font-size: 13px;">
            📧 contact@boxibox.com | ☎️ 01 23 45 67 89
        </p>
        <p style="margin: 10px 0 0 0; color: #adb5bd; font-size: 12px;">
            Cet email a été envoyé automatiquement, merci de ne pas y répondre directement.
        </p>
    </div>
</body>
</html>
