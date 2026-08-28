<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Fixture générée depuis la base de données (instantané du 2026-08-28).
 * Reproduit à l'identique le contenu de la table `fdj_categories`, identifiants inclus.
 */
final class FdjCategorySeeder extends AbstractSeed
{
    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM `fdj_categories` LIMIT 1')) {
            return;
        }

        $this->table('fdj_categories')->insert([
            ['id' => 1, 'name' => 'Loto & Euromillions', 'description' => 'Tentez le jackpot avec les grands tirages nationaux et européens, plusieurs fois par semaine.', 'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z', 'image' => null, 'display_order' => 1, 'status' => 'active', 'created_at' => '2026-08-23 13:37:09', 'updated_at' => '2026-08-23 13:37:09'],
            ['id' => 2, 'name' => 'Illiko (jeux à gratter)', 'description' => 'Un large choix de tickets à gratter, du plus accessible aux plus gros gains instantanés.', 'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z', 'image' => null, 'display_order' => 2, 'status' => 'active', 'created_at' => '2026-08-23 13:37:09', 'updated_at' => '2026-08-23 13:37:09'],
            ['id' => 3, 'name' => 'Amigo & Keno', 'description' => 'Des tirages toutes les 5 minutes pour jouer et connaître le résultat presque immédiatement.', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'image' => null, 'display_order' => 3, 'status' => 'active', 'created_at' => '2026-08-23 13:37:09', 'updated_at' => '2026-08-23 13:37:09'],
            ['id' => 4, 'name' => 'Rapido & jeux express', 'description' => 'Des jeux rapides et accessibles à tout moment de la journée, pour un plaisir immédiat.', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10', 'image' => null, 'display_order' => 4, 'status' => 'active', 'created_at' => '2026-08-23 13:37:09', 'updated_at' => '2026-08-23 13:37:09'],
        ])->saveData();
    }
}
