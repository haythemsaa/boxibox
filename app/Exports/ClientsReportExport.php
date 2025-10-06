<?php

namespace App\Exports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ClientsReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    /**
     * Récupérer les données
     */
    public function collection()
    {
        return Client::withCount('contrats')
            ->with(['contrats' => function ($query) {
                $query->where('statut', 'actif');
            }])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Mapper les données pour chaque ligne
     */
    public function map($client): array
    {
        $contratsActifs = $client->contrats->count();
        $caTotal = $client->factures()
            ->join('reglements', 'reglements.facture_id', '=', 'factures.id')
            ->sum('reglements.montant');

        $factures Impayees = $client->factures()
            ->where('statut', 'impayee')
            ->count();

        return [
            $client->id,
            $client->nom,
            $client->prenom,
            $client->email,
            $client->telephone,
            $client->adresse,
            $client->ville,
            $client->code_postal,
            $contratsActifs,
            $client->contrats_count,
            number_format($caTotal, 2) . ' €',
            $facturesImpayees,
            $client->created_at->format('d/m/Y'),
        ];
    }

    /**
     * En-têtes du tableau
     */
    public function headings(): array
    {
        return [
            'ID',
            'Nom',
            'Prénom',
            'Email',
            'Téléphone',
            'Adresse',
            'Ville',
            'Code Postal',
            'Contrats Actifs',
            'Total Contrats',
            'CA Total',
            'Factures Impayées',
            'Date Création',
        ];
    }

    /**
     * Styles du document
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '36b9cc']
                ],
            ],
        ];
    }

    /**
     * Titre de la feuille
     */
    public function title(): string
    {
        return 'Base Clients';
    }
}
