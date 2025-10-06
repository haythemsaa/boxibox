<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rapport Financier - Boxibox</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #4e73df;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #4e73df;
            margin: 0;
            font-size: 24px;
        }
        .period {
            color: #666;
            margin-top: 5px;
        }
        .kpis {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .kpi {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 15px;
            background: #f8f9fc;
            border-right: 2px solid white;
        }
        .kpi:last-child {
            border-right: none;
        }
        .kpi-label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .kpi-value {
            font-size: 20px;
            font-weight: bold;
            color: #4e73df;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #4e73df;
            margin-top: 30px;
            margin-bottom: 15px;
            border-bottom: 2px solid #4e73df;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #4e73df;
            color: white;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #1cc88a;
            color: white;
        }
        .badge-warning {
            background-color: #f6c23e;
            color: white;
        }
        .badge-danger {
            background-color: #e74a3b;
            color: white;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>📊 Rapport Financier</h1>
        <div class="period">Période : {{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}</div>
        <div class="period">Généré le : {{ now()->format('d/m/Y à H:i') }}</div>
    </div>

    <!-- KPIs -->
    <div class="kpis">
        <div class="kpi">
            <div class="kpi-label">Chiffre d'Affaires</div>
            <div class="kpi-value">{{ number_format($ca, 2) }} €</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Factures Émises</div>
            <div class="kpi-value">{{ $facturesEmises ?? 0 }}</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Montant Impayé</div>
            <div class="kpi-value" style="color: #e74a3b;">{{ number_format($montantImpaye ?? 0, 2) }} €</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Taux de Paiement</div>
            <div class="kpi-value" style="color: #1cc88a;">
                {{ $montantTotal > 0 ? number_format(($montantPaye / $montantTotal) * 100, 1) : 0 }}%
            </div>
        </div>
    </div>

    <!-- CA par Mode de Paiement -->
    <h2 class="section-title">Chiffre d'Affaires par Mode de Paiement</h2>
    <table>
        <thead>
            <tr>
                <th>Mode de Paiement</th>
                <th class="text-right">Montant</th>
                <th class="text-center">Pourcentage</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($caParMode) && $caParMode->count() > 0)
                @foreach($caParMode as $mode)
                <tr>
                    <td>{{ ucfirst($mode->mode_paiement) }}</td>
                    <td class="text-right"><strong>{{ number_format($mode->total, 2) }} €</strong></td>
                    <td class="text-center">
                        {{ $ca > 0 ? number_format(($mode->total / $ca) * 100, 1) : 0 }}%
                    </td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="3" class="text-center">Aucune donnée disponible</td>
                </tr>
            @endif
        </tbody>
        <tfoot>
            <tr>
                <th>Total</th>
                <th class="text-right">{{ number_format($ca, 2) }} €</th>
                <th class="text-center">100%</th>
            </tr>
        </tfoot>
    </table>

    <!-- Page Break -->
    <div class="page-break"></div>

    <!-- Évolution Mensuelle -->
    <h2 class="section-title">Évolution Mensuelle du Chiffre d'Affaires</h2>
    <table>
        <thead>
            <tr>
                <th>Mois</th>
                <th class="text-right">Chiffre d'Affaires</th>
                <th class="text-center">Évolution</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($evolutionCA) && $evolutionCA->count() > 0)
                @php $previousAmount = 0; @endphp
                @foreach($evolutionCA as $item)
                <tr>
                    <td>{{ $item->mois }}</td>
                    <td class="text-right"><strong>{{ number_format($item->total, 2) }} €</strong></td>
                    <td class="text-center">
                        @if($previousAmount > 0)
                            @php
                                $variation = (($item->total - $previousAmount) / $previousAmount) * 100;
                            @endphp
                            @if($variation > 0)
                                <span class="badge badge-success">+{{ number_format($variation, 1) }}%</span>
                            @elseif($variation < 0)
                                <span class="badge badge-danger">{{ number_format($variation, 1) }}%</span>
                            @else
                                <span class="badge">0%</span>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @php $previousAmount = $item->total; @endphp
                @endforeach
            @else
                <tr>
                    <td colspan="3" class="text-center">Aucune donnée disponible</td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- Statut des Factures -->
    <h2 class="section-title">Statut des Factures</h2>
    <table>
        <thead>
            <tr>
                <th>Statut</th>
                <th class="text-center">Nombre</th>
                <th class="text-right">Montant Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>✅ Factures Payées</td>
                <td class="text-center"><strong>{{ $facturesPayees ?? 0 }}</strong></td>
                <td class="text-right"><strong>{{ number_format($montantPaye ?? 0, 2) }} €</strong></td>
            </tr>
            <tr>
                <td>⚠️ Factures Impayées</td>
                <td class="text-center"><strong>{{ $facturesImpayees ?? 0 }}</strong></td>
                <td class="text-right" style="color: #e74a3b;"><strong>{{ number_format($montantImpaye ?? 0, 2) }} €</strong></td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <th>Total</th>
                <th class="text-center">{{ $facturesEmises ?? 0 }}</th>
                <th class="text-right">{{ number_format($montantTotal ?? 0, 2) }} €</th>
            </tr>
        </tfoot>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p>Boxibox - Système de Gestion de Self-Storage | Rapport généré automatiquement</p>
        <p>Page {PAGENO} sur {nb}</p>
    </div>
</body>
</html>
