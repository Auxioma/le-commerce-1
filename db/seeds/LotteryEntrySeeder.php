<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Fixture générée depuis la base de données (instantané du 2026-08-28).
 * Reproduit à l'identique le contenu de la table `lottery_entries`, identifiants inclus.
 */
final class LotteryEntrySeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['LotterySeeder', 'UserSeeder'];
    }

    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM `lottery_entries` LIMIT 1')) {
            return;
        }

        $this->table('lottery_entries')->insert([
            ['id' => 2, 'lottery_id' => 1, 'user_id' => 8, 'code' => 'LOT-4DBDCADB', 'created_at' => '2026-08-23 13:58:23'],
            ['id' => 3, 'lottery_id' => 3, 'user_id' => 8, 'code' => 'LOT-2C19A9A5', 'created_at' => '2026-08-24 10:10:39'],
            ['id' => 4, 'lottery_id' => 4, 'user_id' => 13, 'code' => 'LOT-0EA51DEB', 'created_at' => '2026-08-28 12:37:58'],
            ['id' => 5, 'lottery_id' => 4, 'user_id' => 14, 'code' => 'LOT-A3F24F18', 'created_at' => '2026-08-28 12:41:07'],
        ])->saveData();
    }
}
