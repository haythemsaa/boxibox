<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture {{ $facture->numero_facture }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #333;
        }
        .header {
            margin-bottom: 30px;
            border-bottom: 3px solid #0d6efd;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #0d6efd;
            font-size: 26px;
            margin: 0 0 10px 0;
        }
        .header-info {
            display: table;
            width: 100%;
        }
        .header-left, .header-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .header-right {
            text-align: right;
        }
        .info-box {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #0d6efd;
        }
        .info-box h3 {
            font-size: 12px;
            color: #0d6efd;
            margin: 0 0 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.items th {
            background-color: #0d6efd;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 11px;
        }
        table.items td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        table.totals {
            width: 50%;
            margin-left: auto;
        }
        table.totals td {
            padding: 5px 10px;
        }
        table.totals tr.total {
            background-color: #0d6efd;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-success { background-color: #198754; color: white; }
        .badge-danger { background-color: #dc3545; color: white; }
        .badge-warning { background-color: #ffc107; color: black; }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .mt-30 { margin-top: 30px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>FACTURE</h1>
        <div class="header-info">
            <div class="header-left">
                <strong>BOXIBOX</strong><br>
                Self-Stockage<br>
                contact@boxibox.com
            </div>
            <div class="header-right">
                <strong>N° {{ $facture->numero_facture }}</strong><br>
                Date: {{ $facture->date_emission->format('d/m/Y') }}<br>
                Échéance: {{ $facture->date_echeance->format('d/m/Y') }}
            </div>
        </div>
    </div>

    <div class="info-box">
        <h3>FACTURÉ À</h3>
        <strong>{{ $client->nom }} {{ $client->prenom }}</strong><br>
        {{ $client->adresse }}<br>
        {{ $client->code_postal }} {{ $client->ville }}<br>
        Email: {{ $client->email }}<br>
        @if($client->telephone)
        Tél: {{ $client->telephone }}
        @endif
    </div>

    <div style="margin-bottom: 20px;">
        <strong>Statut: </strong>
        @if($facture->statut == 'payee')
            <span class="badge badge-success">PAYÉE</span>
        @elseif($facture->statut == 'en_retard')
            <span class="badge badge-danger">EN RETARD</span>
        @else
            <span class="badge badge-warning">{{ strtoupper($facture->statut) }}</span>
        @endif

        @if($facture->contrat)
        <br><strong>Contrat: </strong>{{ $facture->contrat->numero_contrat }}
        @endif
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>DÉSIGNATION</th>
                <th class="text-center" style="width: 10%;">QTÉ</th>
                <th class="text-right" style="width: 20%;">PRIX UNITAIRE HT</th>
                <th class="text-right" style="width: 20%;">TOTAL HT</th>
            </tr>
        </thead>
        <tbody>
            @if($facture->lignes && $facture->lignes->count() > 0)
                @foreach($facture->lignes as $ligne)
                <tr>
                    <td>{{ $ligne->designation }}</td>
                    <td class="text-center">{{ $ligne->quantite }}</td>
                    <td class="text-right">{{ number_format($ligne->prix_unitaire, 2) }} €</td>
                    <td class="text-right text-bold">{{ number_format($ligne->montant_ht, 2) }} €</td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td>Location Box</td>
                    <td class="text-center">1</td>
                    <td class="text-right">{{ number_format($facture->montant_ht, 2) }} €</td>
                    <td class="text-right text-bold">{{ number_format($facture->montant_ht, 2) }} €</td>
                </tr>
            @endif
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td class="text-right text-bold">Total HT:</td>
            <td class="text-right">{{ number_format($facture->montant_ht, 2) }} €</td>
        </tr>
        <tr>
            <td class="text-right text-bold">TVA ({{ $facture->taux_tva ?? 20 }}%):</td>
            <td class="text-right">{{ number_format($facture->montant_tva, 2) }} €</td>
        </tr>
        <tr class="total">
            <td class="text-right">TOTAL TTC:</td>
            <td class="text-right">{{ number_format($facture->montant_ttc, 2) }} €</td>
        </tr>
    </table>

    @if($facture->montant_regle > 0)
    <table class="totals" style="background-color: #d1ecf1; margin-top: 10px;">
        <tr>
            <td class="text-right text-bold">Montant Réglé:</td>
            <td class="text-right">{{ number_format($facture->montant_regle, 2) }} €</td>
        </tr>
        <tr style="background-color: #f8d7da;">
            <td class="text-right text-bold">Reste à Payer:</td>
            <td class="text-right text-bold">{{ number_format($facture->montant_total_ttc - $facture->montant_regle, 2) }} €</td>
        </tr>
    </table>
    @endif

    @if($facture->reglements && $facture->reglements->count() > 0)
    <div class="mt-30">
        <h3 style="font-size: 12px; color: #0d6efd; border-bottom: 2px solid #0d6efd; padding-bottom: 5px;">RÈGLEMENTS</h3>
        <table class="items">
            <thead>
                <tr>
                    <th>DATE</th>
                    <th>MODE</th>
                    <th>RÉFÉRENCE</th>
                    <th class="text-right">MONTANT</th>
                </tr>
            </thead>
            <tbody>
                @foreach($facture->reglements as $reglement)
                <tr>
                    <td>{{ $reglement->date_reglement->format('d/m/Y') }}</td>
                    <td>{{ ucfirst($reglement->mode_paiement) }}</td>
                    <td>{{ $reglement->reference ?? 'N/A' }}</td>
                    <td class="text-right text-bold">{{ number_format($reglement->montant, 2) }} €</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div style="margin-top: 30px; padding: 15px; background-color: #f8f9fa; border-left: 4px solid #6c757d; font-size: 10px;">
        <strong>Conditions de paiement:</strong><br>
        Paiement à réception de facture ou à la date d'échéance indiquée.<br>
        En cas de retard de paiement, des pénalités de 3 fois le taux d'intérêt légal seront appliquées.
    </div>

    <div class="footer">
        <p>Document généré le {{ now()->format('d/m/Y à H:i') }} | BOXIBOX - Gestion de Self-Stockage</p>
    </div>
</body>
</html>
