<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rapport d'Occupation - Boxibox</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #1cc88a;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #1cc88a;
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
            color: #1cc88a;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #1cc88a;
            margin-top: 30px;
            margin-bottom: 15px;
            border-bottom: 2px solid #1cc88a;
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
            background-color: #1cc88a;
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
        .progress-bar {
            background-color: #e9ecef;
            border-radius: 3px;
            height: 20px;
            position: relative;
        }
        .progress-fill {
            height: 100%;
            border-radius: 3px;
            display: inline-block;
            text-align: center;
            line-height: 20px;
            color: white;
            font-size: 10px;
        }
        .progress-success {
            background-color: #1cc88a;
        }
        .progress-warning {
            background-color: #f6c23e;
        }
        .progress-danger {
            background-color: #e74a3b;
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
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>📦 Rapport d'Occupation</h1>
        <div class="period">Date de référence : {{ \Carbon\Carbon::parse($date ?? now())->format('d/m/Y') }}</div>
        <div class="period">Généré le : {{ now()->format('d/m/Y à H:i') }}</div>
    </div>

    <!-- KPIs -->
    <div class="kpis">
        <div class="kpi">
            <div class="kpi-label">Taux d'Occupation</div>
            <div class="kpi-value">{{ number_format($tauxOccupation ?? 0, 1) }}%</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Boxes Occupés</div>
            <div class="kpi-value">{{ $boxesOccupes ?? 0 }}</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Boxes Libres</div>
            <div class="kpi-value" style="color: #36b9cc;">{{ $boxesLibres ?? 0 }}</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">En Maintenance</div>
            <div class="kpi-value" style="color: #f6c23e;">{{ $boxesMaintenance ?? 0 }}</div>
        </div>
    </div>

    <!-- Répartition des Statuts -->
    <h2 class="section-title">Répartition des Boxes</h2>
    <table>
        <thead>
            <tr>
                <th>Statut</th>
                <th class="text-center">Nombre</th>
                <th class="text-center">Pourcentage</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>✅ Occupés</td>
                <td class="text-center"><strong>{{ $boxesOccupes ?? 0 }}</strong></td>
                <td class="text-center">
                    {{ $totalBoxes > 0 ? number_format(($boxesOccupes / $totalBoxes) * 100, 1) : 0 }}%
                </td>
            </tr>
            <tr>
                <td>🔓 Libres</td>
                <td class="text-center"><strong>{{ $boxesLibres ?? 0 }}</strong></td>
                <td class="text-center">
                    {{ $totalBoxes > 0 ? number_format(($boxesLibres / $totalBoxes) * 100, 1) : 0 }}%
                </td>
            </tr>
            <tr>
                <td>📋 Réservés</td>
                <td class="text-center"><strong>{{ $boxesReserves ?? 0 }}</strong></td>
                <td class="text-center">
                    {{ $totalBoxes > 0 ? number_format(($boxesReserves / $totalBoxes) * 100, 1) : 0 }}%
                </td>
            </tr>
            <tr>
                <td>🔧 Maintenance</td>
                <td class="text-center"><strong>{{ $boxesMaintenance ?? 0 }}</strong></td>
                <td class="text-center">
                    {{ $totalBoxes > 0 ? number_format(($boxesMaintenance / $totalBoxes) * 100, 1) : 0 }}%
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <th>Total</th>
                <th class="text-center">{{ $totalBoxes ?? 0 }}</th>
                <th class="text-center">100%</th>
            </tr>
        </tfoot>
    </table>

    <!-- Occupation par Emplacement -->
    <h2 class="section-title">Taux d'Occupation par Emplacement</h2>
    <table>
        <thead>
            <tr>
                <th>Emplacement</th>
                <th class="text-center">Total</th>
                <th class="text-center">Occupés</th>
                <th class="text-center">Taux</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($occupationParEmplacement) && count($occupationParEmplacement) > 0)
                @foreach($occupationParEmplacement as $item)
                <tr>
                    <td><strong>{{ $item['emplacement'] }}</strong></td>
                    <td class="text-center">{{ $item['total'] }}</td>
                    <td class="text-center">{{ $item['occupes'] }}</td>
                    <td class="text-center">
                        @if($item['taux'] >= 80)
                            <span class="badge badge-success">{{ number_format($item['taux'], 1) }}%</span>
                        @elseif($item['taux'] >= 50)
                            <span class="badge badge-warning">{{ number_format($item['taux'], 1) }}%</span>
                        @else
                            <span class="badge badge-danger">{{ number_format($item['taux'], 1) }}%</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="4" class="text-center">Aucune donnée disponible</td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- Occupation par Famille -->
    <h2 class="section-title">Taux d'Occupation par Famille de Boxes</h2>
    <table>
        <thead>
            <tr>
                <th>Famille</th>
                <th class="text-center">Total</th>
                <th class="text-center">Occupés</th>
                <th class="text-center">Taux</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($occupationParFamille) && count($occupationParFamille) > 0)
                @foreach($occupationParFamille as $item)
                <tr>
                    <td><strong>{{ $item['famille'] }}</strong></td>
                    <td class="text-center">{{ $item['total'] }}</td>
                    <td class="text-center">{{ $item['occupes'] }}</td>
                    <td class="text-center">
                        @if($item['taux'] >= 80)
                            <span class="badge badge-success">{{ number_format($item['taux'], 1) }}%</span>
                        @elseif($item['taux'] >= 50)
                            <span class="badge badge-warning">{{ number_format($item['taux'], 1) }}%</span>
                        @else
                            <span class="badge badge-danger">{{ number_format($item['taux'], 1) }}%</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="4" class="text-center">Aucune donnée disponible</td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p>Boxibox - Système de Gestion de Self-Storage | Rapport généré automatiquement</p>
        <p>Page {PAGENO} sur {nb}</p>
    </div>
</body>
</html>
