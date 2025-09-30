<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Emplacement;

class EmplacementSeeder extends Seeder
{
    public function run(): void
    {
        $emplacements = [
            [
                'nom' => 'Bâtiment A - RDC',
                'description' => 'Rez-de-chaussée du bâtiment principal',
                'niveau' => 'RDC',
                'zone' => 'A',
            ],
            [
                'nom' => 'Bâtiment A - Étage 1',
                'description' => 'Premier étage du bâtiment principal',
                'niveau' => '1',
                'zone' => 'A',
            ],
            [
                'nom' => 'Bâtiment B - RDC',
                'description' => 'Rez-de-chaussée du bâtiment secondaire',
                'niveau' => 'RDC',
                'zone' => 'B',
            ],
            [
                'nom' => 'Bâtiment B - Étage 1',
                'description' => 'Premier étage du bâtiment secondaire',
                'niveau' => '1',
                'zone' => 'B',
            ],
            [
                'nom' => 'Extérieur - Garages',
                'description' => 'Garages extérieurs pour véhicules',
                'niveau' => 'EXT',
                'zone' => 'EXT',
            ],
        ];

        foreach ($emplacements as $emplacement) {
            Emplacement::firstOrCreate([
                'nom' => $emplacement['nom']
            ], $emplacement);
        }
    }
}