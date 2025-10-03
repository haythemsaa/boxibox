<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Box;
use App\Models\Emplacement;

class BoxCoordinatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer tous les emplacements
        $emplacements = Emplacement::all();

        if ($emplacements->isEmpty()) {
            $this->command->warn('Aucun emplacement trouvé. Créons des emplacements par défaut...');

            // Créer des emplacements par défaut
            $emplacements = collect([
                Emplacement::create([
                    'nom' => 'Bâtiment A - RDC',
                    'description' => 'Rez-de-chaussée du bâtiment A',
                    'niveau' => 0,
                    'zone' => 'A',
                    'is_active' => true,
                ]),
                Emplacement::create([
                    'nom' => 'Bâtiment A - 1er étage',
                    'description' => 'Premier étage du bâtiment A',
                    'niveau' => 1,
                    'zone' => 'A',
                    'is_active' => true,
                ]),
                Emplacement::create([
                    'nom' => 'Bâtiment B - RDC',
                    'description' => 'Rez-de-chaussée du bâtiment B',
                    'niveau' => 0,
                    'zone' => 'B',
                    'is_active' => true,
                ]),
            ]);
        }

        // Pour chaque emplacement, attribuer des coordonnées aux boxes
        $gridWidth = 10; // 10 colonnes
        $gridHeight = 8; // 8 lignes

        foreach ($emplacements as $index => $emplacement) {
            $this->command->info("Traitement de l'emplacement: {$emplacement->nom}");

            // Récupérer les boxes de cet emplacement
            $boxes = Box::where('emplacement_id', $emplacement->id)->get();

            if ($boxes->isEmpty()) {
                $this->command->warn("  Aucun box dans cet emplacement.");
                continue;
            }

            $this->command->info("  {$boxes->count()} boxes à positionner");

            // Distribuer les boxes sur une grille
            $x = 0;
            $y = 0;

            foreach ($boxes as $box) {
                // Mettre à jour les coordonnées
                $box->update([
                    'coordonnees_x' => $x,
                    'coordonnees_y' => $y,
                ]);

                $this->command->info("  - Box {$box->numero}: ({$x}, {$y})");

                // Passer à la position suivante
                $x++;
                if ($x >= $gridWidth) {
                    $x = 0;
                    $y++;
                }

                // Si on dépasse la hauteur de grille, recommencer
                if ($y >= $gridHeight) {
                    $y = 0;
                }
            }
        }

        // Si des boxes n'ont pas d'emplacement, les assigner au premier
        $boxesSansEmplacement = Box::whereNull('emplacement_id')->get();

        if ($boxesSansEmplacement->count() > 0 && $emplacements->count() > 0) {
            $this->command->info("\nAssignation des boxes sans emplacement...");
            $premierEmplacement = $emplacements->first();

            $x = 0;
            $y = 0;

            foreach ($boxesSansEmplacement as $box) {
                $box->update([
                    'emplacement_id' => $premierEmplacement->id,
                    'coordonnees_x' => $x,
                    'coordonnees_y' => $y,
                ]);

                $this->command->info("  - Box {$box->numero} assigné à {$premierEmplacement->nom}: ({$x}, {$y})");

                $x++;
                if ($x >= $gridWidth) {
                    $x = 0;
                    $y++;
                }
            }
        }

        // Statistiques finales
        $totalBoxes = Box::count();
        $boxesAvecCoordonnees = Box::whereNotNull('coordonnees_x')
                                   ->whereNotNull('coordonnees_y')
                                   ->count();

        $this->command->info("\n✅ Seeder terminé!");
        $this->command->info("📦 Total boxes: {$totalBoxes}");
        $this->command->info("📍 Boxes avec coordonnées: {$boxesAvecCoordonnees}");
        $this->command->info("🏢 Emplacements: {$emplacements->count()}");
    }
}
