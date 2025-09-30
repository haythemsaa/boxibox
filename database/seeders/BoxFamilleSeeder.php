<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BoxFamille;

class BoxFamilleSeeder extends Seeder
{
    public function run(): void
    {
        $familles = [
            [
                'nom' => 'Petit',
                'description' => 'Boxes de petite taille pour objets personnels',
                'surface_min' => 1,
                'surface_max' => 5,
                'prix_base' => 30.00,
                'couleur_plan' => '#28a745',
            ],
            [
                'nom' => 'Moyen',
                'description' => 'Boxes de taille moyenne pour mobilier',
                'surface_min' => 5,
                'surface_max' => 15,
                'prix_base' => 60.00,
                'couleur_plan' => '#ffc107',
            ],
            [
                'nom' => 'Grand',
                'description' => 'Boxes de grande taille pour gros mobilier',
                'surface_min' => 15,
                'surface_max' => 30,
                'prix_base' => 120.00,
                'couleur_plan' => '#fd7e14',
            ],
            [
                'nom' => 'Très Grand',
                'description' => 'Boxes très grandes pour stockage professionnel',
                'surface_min' => 30,
                'surface_max' => 50,
                'prix_base' => 200.00,
                'couleur_plan' => '#dc3545',
            ],
            [
                'nom' => 'Garage',
                'description' => 'Espaces pour véhicules et gros équipements',
                'surface_min' => 50,
                'surface_max' => null,
                'prix_base' => 300.00,
                'couleur_plan' => '#6f42c1',
            ],
        ];

        foreach ($familles as $famille) {
            BoxFamille::firstOrCreate(['nom' => $famille['nom']], $famille);
        }
    }
}