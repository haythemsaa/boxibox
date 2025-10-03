<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rappel de Paiement</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    @php
        $bgColor = match($rappel->niveau) {
            1 => '#0d6efd',
            2 => '#fd7e14',
            3 => '#dc3545',
            default => '#0d6efd'
        };
        $urgence = match($rappel->niveau) {
            1 => 'Rappel amical',
            2 => 'Relance importante',
            3 => 'MISE EN DEMEURE',
            default => 'Rappel'
        };
    @endphp

    <div style="background: {{ $bgColor }}; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0; font-size: 28px;">BOXIBOX</h1>
        <p style="color: white; margin: 5px 0 0 0; font-size: 16px; font-weight: bold;">{{ $urgence }}</p>
    </div>

    <div style="background-color: #ffffff; padding: 30px; border: 1px solid #dee2e6; border-top: none;">
        <h2 style="color: {{ $bgColor }}; margin-top: 0;">
            {{ $rappel->niveau >= 3 ? 'Madame, Monsieur,' : 'Bonjour ' . $client->prenom . ' ' . $client->nom . ',' }}
        </h2>

        @if($rappel->niveau == 1)
            <p>Nous vous informons qu'une facture est en attente de règlement sur votre compte.</p>
            <p>Il s'agit peut-être d'un simple oubli de votre part, c'est pourquoi nous nous permettons de vous le rappeler.</p>
        @elseif($rappel->niveau == 2)
            <p><strong>Malgré notre premier rappel, nous constatons que la facture ci-dessous reste impayée.</strong></p>
            <p>Nous vous remercions de bien vouloir régulariser votre situation dans les plus brefs délais.</p>
        @else
            <p><strong style="color: #dc3545;">MISE EN DEMEURE DE PAYER</strong></p>
            <p>Malgré nos précédents rappels, la facture mentionnée ci-dessous demeure impayée.</p>
            <p><strong>Nous vous mettons en demeure de procéder au règlement sous 8 jours à compter de la réception de ce courrier.</strong></p>
            <p>À défaut, nous serons contraints d'engager une procédure de recouvrement contentieux.</p>
        @endif

        <div style="background-color: {{ $rappel->niveau >= 3 ? '#f8d7da' : '#f8f9fa' }}; padding: 20px; border-left: 4px solid {{ $bgColor }}; margin: 25px 0;">
            <h3 style="margin-top: 0; color: {{ $bgColor }}; font-size: 18px;">Détails de la facture impayée</h3>
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
                    <td style="padding: 8px 0; color: #dc3545; font-weight: bold;">
                        {{ $facture->date_echeance->format('d/m/Y') }}
                        ({{ $facture->date_echeance->diffInDays(now()) }} jours de retard)
                    </td>
                </tr>
                <tr style="background-color: {{ $bgColor }}20;">
                    <td style="padding: 12px 8px; font-weight: bold; color: #000;">Montant à régler :</td>
                    <td style="padding: 12px 8px; font-size: 24px; font-weight: bold; color: {{ $bgColor }};">
                        {{ number_format($rappel->montant, 2, ',', ' ') }} €
                    </td>
                </tr>
            </table>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ url('/client/factures/' . $facture->id) }}"
               style="background-color: {{ $bgColor }}; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
                Régler maintenant
            </a>
        </div>

        @if($rappel->niveau >= 3)
        <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0;">
            <strong style="color: #856404;">⚠️ Conséquences en cas de non-paiement</strong>
            <ul style="color: #856404; margin: 10px 0 0 0; padding-left: 20px;">
                <li>Procédure contentieuse engagée</li>
                <li>Frais de recouvrement à votre charge</li>
                <li>Pénalités de retard (3x le taux légal)</li>
                <li>Suspension du contrat de location</li>
            </ul>
        </div>
        @endif

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6;">
            <h4 style="color: #6c757d; font-size: 16px;">Modes de règlement</h4>
            <ul style="color: #6c757d; padding-left: 20px;">
                <li><strong>Virement bancaire</strong> - RIB disponible sur votre espace client</li>
                <li><strong>Carte bancaire</strong> - Paiement en ligne sécurisé</li>
                <li><strong>Prélèvement SEPA</strong> - Configurez un mandat pour éviter les oublis</li>
            </ul>
        </div>

        @if($rappel->niveau < 3)
        <p style="margin-top: 30px; color: #6c757d; font-size: 14px;">
            Si vous rencontrez des difficultés de paiement, n'hésitez pas à nous contacter pour trouver une solution ensemble.
        </p>
        @else
        <p style="margin-top: 30px; color: #6c757d; font-size: 14px;">
            Si vous avez déjà effectué ce règlement, merci de nous transmettre votre justificatif de paiement.
        </p>
        @endif
    </div>

    <div style="background-color: #f8f9fa; padding: 20px; text-align: center; border-radius: 0 0 10px 10px; border: 1px solid #dee2e6; border-top: none;">
        <p style="margin: 0 0 10px 0; color: #6c757d; font-size: 14px;">
            <strong>BOXIBOX</strong> - Self-Stockage
        </p>
        <p style="margin: 0; color: #6c757d; font-size: 13px;">
            📧 comptabilite@boxibox.com | ☎️ 01 23 45 67 89
        </p>
        @if($rappel->niveau >= 3)
        <p style="margin: 10px 0 0 0; color: #dc3545; font-size: 11px; font-weight: bold;">
            Mise en demeure envoyée le {{ $rappel->date_envoi->format('d/m/Y') }}
        </p>
        @endif
    </div>
</body>
</html>
