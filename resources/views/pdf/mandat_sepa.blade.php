<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mandat SEPA - {{ $mandat->rum }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #0d6efd;
        }
        .header h1 {
            color: #0d6efd;
            font-size: 24px;
            margin: 0 0 5px 0;
        }
        .header h2 {
            font-size: 16px;
            color: #6c757d;
            margin: 0;
            font-weight: normal;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            background-color: #0d6efd;
            color: white;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        .info-row {
            display: table-row;
        }
        .info-label {
            display: table-cell;
            width: 35%;
            padding: 6px 10px;
            background-color: #f8f9fa;
            font-weight: bold;
            border-bottom: 1px solid #dee2e6;
        }
        .info-value {
            display: table-cell;
            width: 65%;
            padding: 6px 10px;
            border-bottom: 1px solid #dee2e6;
        }
        .info-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 12px;
            margin: 20px 0;
            font-size: 9px;
        }
        .legal-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 15px;
            margin: 20px 0;
            font-size: 9px;
            line-height: 1.4;
        }
        .legal-box h4 {
            font-size: 11px;
            margin: 0 0 10px 0;
            color: #0d6efd;
        }
        .signature-block {
            margin-top: 40px;
            page-break-inside: avoid;
        }
        .signature-box {
            display: table;
            width: 100%;
            margin-top: 30px;
        }
        .signature-left, .signature-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 10px;
        }
        .signature-line {
            border-top: 2px solid #333;
            margin-top: 60px;
            padding-top: 5px;
            text-align: center;
            font-size: 9px;
            font-style: italic;
        }
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-success { background-color: #198754; color: white; }
        .badge-warning { background-color: #ffc107; color: black; }
        .badge-info { background-color: #0dcaf0; color: black; }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .mb-10 { margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>MANDAT DE PRÉLÈVEMENT SEPA</h1>
        <h2>Récurrent / Recurrent</h2>
    </div>

    <div class="section">
        <div class="section-title">RÉFÉRENCE DU MANDAT</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Référence Unique du Mandat (RUM)</div>
                <div class="info-value text-bold">{{ $mandat->rum }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Type de paiement</div>
                <div class="info-value">{{ ucfirst($mandat->type_paiement ?? 'Récurrent') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Date de signature</div>
                <div class="info-value">{{ $mandat->date_signature ? $mandat->date_signature->format('d/m/Y') : now()->format('d/m/Y') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Statut</div>
                <div class="info-value">
                    @if($mandat->statut == 'valide' || $mandat->statut == 'actif')
                        <span class="badge badge-success">ACTIF</span>
                    @elseif($mandat->statut == 'en_attente')
                        <span class="badge badge-warning">EN ATTENTE</span>
                    @else
                        <span class="badge badge-info">{{ strtoupper($mandat->statut) }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">CRÉANCIER / CREDITOR</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Nom du créancier</div>
                <div class="info-value text-bold">BOXIBOX</div>
            </div>
            <div class="info-row">
                <div class="info-label">Identifiant Créancier SEPA (ICS)</div>
                <div class="info-value">FR12ZZZ123456</div>
            </div>
            <div class="info-row">
                <div class="info-label">Adresse</div>
                <div class="info-value">
                    123 Rue du Self-Stockage<br>
                    75001 Paris, France
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">DÉBITEUR / DEBTOR</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Nom et Prénom</div>
                <div class="info-value text-bold">{{ $client->nom }} {{ $client->prenom }}</div>
            </div>
            @if($client->raison_sociale)
            <div class="info-row">
                <div class="info-label">Raison sociale</div>
                <div class="info-value">{{ $client->raison_sociale }}</div>
            </div>
            @endif
            <div class="info-row">
                <div class="info-label">Adresse complète</div>
                <div class="info-value">
                    {{ $client->adresse ?? 'Non renseignée' }}<br>
                    {{ $client->code_postal }} {{ $client->ville }}<br>
                    {{ $client->pays ?? 'France' }}
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Email</div>
                <div class="info-value">{{ $client->email }}</div>
            </div>
            @if($client->telephone)
            <div class="info-row">
                <div class="info-label">Téléphone</div>
                <div class="info-value">{{ $client->telephone }}</div>
            </div>
            @endif
        </div>
    </div>

    <div class="section">
        <div class="section-title">COMPTE À DÉBITER / ACCOUNT TO BE DEBITED</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Titulaire du compte</div>
                <div class="info-value text-bold">{{ $mandat->titulaire_compte ?? $mandat->titulaire }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">IBAN</div>
                <div class="info-value text-bold" style="letter-spacing: 1px;">
                    {{ chunk_split($mandat->iban, 4, ' ') }}
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">BIC / SWIFT</div>
                <div class="info-value text-bold">{{ $mandat->bic }}</div>
            </div>
        </div>
    </div>

    <div class="info-box">
        <strong>⚠️ INFORMATION IMPORTANTE</strong><br>
        En signant ce formulaire de mandat, vous autorisez BOXIBOX à envoyer des instructions à votre banque pour débiter votre compte,
        et votre banque à débiter votre compte conformément aux instructions de BOXIBOX.
    </div>

    <div class="legal-box">
        <h4>VOS DROITS CONCERNANT LE PRÉSENT MANDAT</h4>
        <p style="margin: 0 0 8px 0;">
            <strong>Vous bénéficiez du droit d'être remboursé par votre banque selon les conditions décrites dans la convention que vous avez passée avec elle.</strong>
        </p>
        <p style="margin: 0 0 8px 0;">
            Une demande de remboursement doit être présentée dans les 8 semaines suivant la date de débit de votre compte
            pour un prélèvement autorisé.
        </p>
        <p style="margin: 0;">
            <em>Vos droits concernant le présent mandat sont expliqués dans un document que vous pouvez obtenir auprès de votre banque.</em>
        </p>
    </div>

    <div class="legal-box">
        <h4>DURÉE DE VALIDITÉ ET RÉSILIATION</h4>
        <p style="margin: 0 0 8px 0;">
            Ce mandat reste valable tant que votre contrat de location avec BOXIBOX est actif, sauf révocation de votre part.
        </p>
        <p style="margin: 0;">
            Pour révoquer ce mandat, vous devez en informer BOXIBOX par écrit (email ou courrier recommandé)
            au moins 3 jours ouvrés avant la date de prélèvement prévue.
        </p>
    </div>

    <div class="signature-block">
        <div class="section-title">SIGNATURE ET ACCEPTATION</div>

        <p class="mb-10">
            <strong>Je soussigné(e) {{ $client->nom }} {{ $client->prenom }},</strong> autorise BOXIBOX à prélever sur mon compte
            les montants dus au titre de mon/mes contrat(s) de location, conformément aux conditions générales acceptées.
        </p>

        <div class="signature-box">
            <div class="signature-left">
                <div style="text-align: center; margin-bottom: 80px;">
                    <strong>Pour le Créancier BOXIBOX</strong>
                </div>
                <div class="signature-line">
                    Signature autorisée
                </div>
            </div>
            <div class="signature-right">
                <div style="text-align: center; margin-bottom: 10px;">
                    <strong>Pour le Débiteur</strong>
                </div>
                <div style="text-align: center; margin-bottom: 5px;">
                    Lieu : ____________________
                </div>
                <div style="text-align: center; margin-bottom: 50px;">
                    Date : {{ $mandat->date_signature ? $mandat->date_signature->format('d/m/Y') : '___/___/_____' }}
                </div>
                <div class="signature-line">
                    Signature du titulaire du compte
                </div>
            </div>
        </div>
    </div>

    <div style="margin-top: 30px; padding: 10px; background-color: #e7f3ff; border-left: 4px solid #0d6efd; font-size: 9px;">
        <strong>📧 Contact :</strong> Pour toute question relative à ce mandat SEPA, contactez-nous :<br>
        Email : comptabilite@boxibox.com | Tél : 01 23 45 67 89
    </div>

    <div class="footer">
        <p>Document généré le {{ now()->format('d/m/Y à H:i') }} | BOXIBOX - Mandat SEPA {{ $mandat->rum }}</p>
        <p>Ce document constitue un engagement juridique conforme à la réglementation européenne SEPA.</p>
    </div>
</body>
</html>
