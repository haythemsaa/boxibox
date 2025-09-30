<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'nom' => 'Assurance vol et incendie',
                'description' => 'Assurance couvrant le vol et l\'incendie du contenu',
                'type_service' => 'assurance',
                'prix' => 5.00,
                'unite' => 'mois',
                'facturable' => true,
                'obligatoire' => false,
            ],
            [
                'nom' => 'Cadenas de sécurité',
                'description' => 'Cadenas haute sécurité fourni par le centre',
                'type_service' => 'materiel',
                'prix' => 15.00,
                'unite' => 'pièce',
                'facturable' => true,
                'obligatoire' => false,
            ],
            [
                'nom' => 'Service de manutention',
                'description' => 'Aide au transport et rangement',
                'type_service' => 'manutention',
                'prix' => 25.00,
                'unite' => 'heure',
                'facturable' => true,
                'obligatoire' => false,
            ],
            [
                'nom' => 'Kit de déménagement',
                'description' => 'Cartons, papier bulle, adhésif',
                'type_service' => 'materiel',
                'prix' => 35.00,
                'unite' => 'kit',
                'facturable' => true,
                'obligatoire' => false,
            ],
            [
                'nom' => 'Nettoyage de box',
                'description' => 'Nettoyage complet avant remise en location',
                'type_service' => 'nettoyage',
                'prix' => 50.00,
                'unite' => 'intervention',
                'facturable' => true,
                'obligatoire' => false,
            ],
            [
                'nom' => 'Accès 24h/24',
                'description' => 'Accès au box 24 heures sur 24',
                'type_service' => 'securite',
                'prix' => 10.00,
                'unite' => 'mois',
                'facturable' => true,
                'obligatoire' => false,
            ],
            [
                'nom' => 'Frais de dossier',
                'description' => 'Frais administratifs d\'ouverture de dossier',
                'type_service' => 'autre',
                'prix' => 30.00,
                'unite' => 'forfait',
                'facturable' => true,
                'obligatoire' => true,
            ],
        ];

        foreach ($services as $service) {
            Service::firstOrCreate([
                'nom' => $service['nom']
            ], $service);
        }
    }
}