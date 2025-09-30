<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Box;
use App\Models\BoxFamille;
use App\Models\Emplacement;

class BoxSeeder extends Seeder
{
    public function run(): void
    {
        $familles = BoxFamille::all();
        $emplacements = Emplacement::all();

        if ($familles->isEmpty() || $emplacements->isEmpty()) {
            $this->command->error('Veuillez d\'abord exécuter les seeders BoxFamille et Emplacement');
            return;
        }

        // Génération de boxes d'exemple
        $boxCount = 1;

        foreach ($emplacements as $emplacement) {
            foreach ($familles as $famille) {
                $nombreBoxes = match($famille->nom) {
                    'Petit' => 20,
                    'Moyen' => 15,
                    'Grand' => 10,
                    'Très Grand' => 8,
                    'Garage' => 5,
                    default => 10
                };

                for ($i = 1; $i <= $nombreBoxes; $i++) {
                    $surface = rand(
                        $famille->surface_min * 100,
                        ($famille->surface_max ?? $famille->surface_min * 2) * 100
                    ) / 100;

                    $volume = $surface * 2.5; // Hauteur moyenne de 2.5m

                    // Prix basé sur la surface avec variation
                    $prixParM2 = $famille->prix_base / ($famille->surface_min + ($famille->surface_max ?? $famille->surface_min));
                    $prix = round($surface * $prixParM2, 2);

                    Box::create([
                        'famille_id' => $famille->id,
                        'emplacement_id' => $emplacement->id,
                        'numero' => sprintf('%s%02d-%03d',
                            substr($emplacement->zone, 0, 1),
                            (int)$emplacement->niveau ?: 0,
                            $boxCount++
                        ),
                        'surface' => $surface,
                        'volume' => $volume,
                        'prix_mensuel' => $prix,
                        'statut' => 'libre',
                        'coordonnees_x' => rand(0, 100),
                        'coordonnees_y' => rand(0, 100),
                        'description' => "Box {$famille->nom} - {$surface}m²",
                        'is_active' => true,
                    ]);
                }
            }
        }

        $this->command->info('Boxes créées avec succès: ' . ($boxCount - 1) . ' boxes générées');
    }
}