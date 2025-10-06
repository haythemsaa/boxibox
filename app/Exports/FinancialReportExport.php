<?php

namespace App\Exports;

use App\Models\Facture;
use App\Models\Reglement;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class FinancialReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $dateDebut;
    protected $dateFin;

    public function __construct($dateDebut, $dateFin)
    {
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
    }

    /**
     * Récupérer les données
     */
    public function collection()
    {
        return Facture::whereBetween('date_emission', [$this->dateDebut, $this->dateFin])
            ->with(['client', 'reglements'])
            ->orderBy('date_emission', 'desc')
            ->get();
    }

    /**
     * Mapper les données pour chaque ligne
     */
    public function map($facture): array
    {
        return [
            $facture->numero_facture,
            $facture->client->nom . ' ' . $facture->client->prenom,
            $facture->client->email,
            $facture->date_emission->format('d/m/Y'),
            $facture->date_echeance ? $facture->date_echeance->format('d/m/Y') : '',
            number_format($facture->montant_total, 2) . ' €',
            number_format($facture->montant_paye, 2) . ' €',
            number_format($facture->montant_total - $facture->montant_paye, 2) . ' €',
            $this->getStatutLabel($facture->statut),
            $facture->reglements->isNotEmpty()
                ? $facture->reglements->pluck('mode_paiement')->unique()->implode(', ')
                : 'Aucun',
        ];
    }

    /**
     * En-têtes du tableau
     */
    public function headings(): array
    {
        return [
            'N° Facture',
            'Client',
            'Email',
            'Date Émission',
            'Date Échéance',
            'Montant Total',
            'Montant Payé',
            'Montant Dû',
            'Statut',
            'Mode(s) de Paiement',
        ];
    }

    /**
     * Styles du document
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style de l'en-tête
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4e73df']
                ],
            ],
        ];
    }

    /**
     * Titre de la feuille
     */
    public function title(): string
    {
        return 'Rapport Financier';
    }

    /**
     * Libellé du statut
     */
    private function getStatutLabel($statut)
    {
        $statuts = [
            'payee' => 'Payée',
            'impayee' => 'Impayée',
            'partielle' => 'Partielle',
            'annulee' => 'Annulée',
        ];

        return $statuts[$statut] ?? $statut;
    }
}
