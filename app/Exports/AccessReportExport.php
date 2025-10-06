<?php

namespace App\Exports;

use App\Models\AccessLog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AccessReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
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
        return AccessLog::whereBetween('date_heure', [$this->dateDebut, $this->dateFin])
            ->with(['client', 'box', 'accessCode'])
            ->orderBy('date_heure', 'desc')
            ->get();
    }

    /**
     * Mapper les données pour chaque ligne
     */
    public function map($log): array
    {
        return [
            $log->id,
            $log->date_heure->format('d/m/Y H:i:s'),
            $log->client ? ($log->client->nom . ' ' . $log->client->prenom) : 'Inconnu',
            $log->client ? $log->client->email : '',
            $log->box ? $log->box->numero : 'N/A',
            $this->getTypeAccesLabel($log->type_acces),
            $this->getMethodeLabel($log->methode),
            $this->getStatutLabel($log->statut),
            $log->code_utilise ?? '',
            $log->raison_refus ?? '',
            $log->terminal_id ?? '',
            $log->emplacement ?? '',
            $log->ip_address ?? '',
        ];
    }

    /**
     * En-têtes du tableau
     */
    public function headings(): array
    {
        return [
            'ID',
            'Date & Heure',
            'Client',
            'Email',
            'Box',
            'Type d\'Accès',
            'Méthode',
            'Statut',
            'Code Utilisé',
            'Raison Refus',
            'Terminal',
            'Emplacement',
            'Adresse IP',
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
                    'startColor' => ['rgb' => 'f6c23e']
                ],
            ],
        ];
    }

    /**
     * Titre de la feuille
     */
    public function title(): string
    {
        return 'Logs d\'Accès';
    }

    /**
     * Libellés
     */
    private function getTypeAccesLabel($type)
    {
        return $type === 'entree' ? 'Entrée' : 'Sortie';
    }

    private function getMethodeLabel($methode)
    {
        $methodes = [
            'pin' => 'Code PIN',
            'qr' => 'QR Code',
            'badge' => 'Badge',
            'manuel' => 'Manuel',
            'maintenance' => 'Maintenance',
        ];

        return $methodes[$methode] ?? $methode;
    }

    private function getStatutLabel($statut)
    {
        $statuts = [
            'autorise' => 'Autorisé',
            'refuse' => 'Refusé',
            'erreur' => 'Erreur',
        ];

        return $statuts[$statut] ?? $statut;
    }
}
