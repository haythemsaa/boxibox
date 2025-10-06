<?php

namespace App\Exports;

use App\Models\Box;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class OccupationReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    /**
     * Récupérer les données
     */
    public function collection()
    {
        return Box::with(['emplacement', 'famille', 'contrat.client'])
            ->orderBy('numero')
            ->get();
    }

    /**
     * Mapper les données pour chaque ligne
     */
    public function map($box): array
    {
        return [
            $box->numero,
            $box->famille->nom ?? 'N/A',
            $box->emplacement->nom ?? 'N/A',
            $box->famille->surface ?? 'N/A',
            $this->getStatutLabel($box->statut),
            $box->contrat && $box->contrat->client
                ? $box->contrat->client->nom . ' ' . $box->contrat->client->prenom
                : '-',
            $box->contrat && $box->contrat->client
                ? $box->contrat->client->email
                : '-',
            $box->famille->tarif_base ? number_format($box->famille->tarif_base, 2) . ' €' : '-',
        ];
    }

    /**
     * En-têtes du tableau
     */
    public function headings(): array
    {
        return [
            'N° Box',
            'Famille',
            'Emplacement',
            'Surface (m²)',
            'Statut',
            'Client',
            'Email Client',
            'Tarif Mensuel',
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
                    'startColor' => ['rgb' => '1cc88a']
                ],
            ],
        ];
    }

    /**
     * Titre de la feuille
     */
    public function title(): string
    {
        return 'Occupation Boxes';
    }

    /**
     * Libellé du statut
     */
    private function getStatutLabel($statut)
    {
        $statuts = [
            'libre' => 'Libre',
            'occupe' => 'Occupé',
            'reserve' => 'Réservé',
            'maintenance' => 'Maintenance',
        ];

        return $statuts[$statut] ?? $statut;
    }
}
