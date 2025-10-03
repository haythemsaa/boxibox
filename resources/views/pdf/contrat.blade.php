<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Contrat {{ $contrat->numero_contrat }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #0d6efd;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #0d6efd;
            font-size: 24px;
            margin: 0;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-section h2 {
            background-color: #f8f9fa;
            padding: 8px;
            font-size: 14px;
            color: #0d6efd;
            border-left: 4px solid #0d6efd;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background-color: #f8f9fa;
            font-weight: bold;
            width: 35%;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-success { background-color: #198754; color: white; }
        .badge-warning { background-color: #ffc107; color: black; }
        .badge-danger { background-color: #dc3545; color: white; }
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
        .text-primary {
            color: #0d6efd;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>CONTRAT DE LOCATION</h1>
        <p><strong>N° {{ $contrat->numero_contrat }}</strong></p>
    </div>

    <div class="info-section">
        <h2>Informations du Client</h2>
        <table>
            <tr>
                <th>Nom Complet:</th>
                <td>{{ $client->nom }} {{ $client->prenom }}</td>
            </tr>
            <tr>
                <th>Email:</th>
                <td>{{ $client->email }}</td>
            </tr>
            <tr>
                <th>Téléphone:</th>
                <td>{{ $client->telephone ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Adresse:</th>
                <td>{{ $client->adresse }}, {{ $client->code_postal }} {{ $client->ville }}</td>
            </tr>
        </table>
    </div>

    <div class="info-section">
        <h2>Détails du Contrat</h2>
        <table>
            <tr>
                <th>Numéro de Contrat:</th>
                <td><strong>{{ $contrat->numero_contrat }}</strong></td>
            </tr>
            <tr>
                <th>Date de Début:</th>
                <td>{{ $contrat->date_debut->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <th>Date de Fin:</th>
                <td>{{ $contrat->date_fin ? $contrat->date_fin->format('d/m/Y') : 'Indéterminée' }}</td>
            </tr>
            <tr>
                <th>Durée:</th>
                <td>{{ $contrat->duree_mois ?? 'N/A' }} mois</td>
            </tr>
            <tr>
                <th>Loyer Mensuel TTC:</th>
                <td class="text-primary">{{ number_format($contrat->montant_loyer, 2) }} €</td>
            </tr>
            <tr>
                <th>Dépôt de Garantie:</th>
                <td>{{ number_format($contrat->depot_garantie ?? 0, 2) }} €</td>
            </tr>
            <tr>
                <th>Statut:</th>
                <td>
                    @if($contrat->statut == 'actif')
                        <span class="badge badge-success">ACTIF</span>
                    @elseif($contrat->statut == 'resilie')
                        <span class="badge badge-danger">RÉSILIÉ</span>
                    @else
                        <span class="badge badge-warning">{{ strtoupper($contrat->statut) }}</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="info-section">
        <h2>Informations du Box</h2>
        <table>
            <tr>
                <th>Numéro de Box:</th>
                <td><strong>{{ $contrat->box->numero }}</strong></td>
            </tr>
            <tr>
                <th>Famille:</th>
                <td>{{ $contrat->box->famille->nom ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Emplacement:</th>
                <td>{{ $contrat->box->emplacement->nom ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Surface:</th>
                <td>{{ $contrat->box->surface }} m²</td>
            </tr>
            <tr>
                <th>Volume:</th>
                <td>{{ $contrat->box->volume ?? 'N/A' }} m³</td>
            </tr>
            <tr>
                <th>Équipements:</th>
                <td>{{ $contrat->box->equipements ?? 'Aucun' }}</td>
            </tr>
        </table>
    </div>

    <div class="info-section">
        <h2>Conditions Générales</h2>
        <p style="text-align: justify; line-height: 1.6;">
            Le présent contrat de location est conclu entre le bailleur (BOXIBOX) et le locataire désigné ci-dessus.
            Le locataire s'engage à respecter le règlement intérieur et à utiliser le box conformément à sa destination.
            Le loyer est payable mensuellement à terme échu. Tout retard de paiement entraînera l'application de pénalités.
            Le dépôt de garantie sera restitué au locataire dans un délai de 30 jours suivant la fin du contrat,
            déduction faite des éventuelles sommes dues.
        </p>
    </div>

    <div class="footer">
        <p>Document généré le {{ now()->format('d/m/Y à H:i') }} | BOXIBOX - Gestion de Self-Stockage</p>
    </div>
</body>
</html>
