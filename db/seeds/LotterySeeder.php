<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Fixture générée depuis la base de données (instantané du 2026-08-28).
 * Reproduit à l'identique le contenu de la table `lotteries`, identifiants inclus.
 */
final class LotterySeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['UserSeeder'];
    }

    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM `lotteries` LIMIT 1')) {
            return;
        }

        $this->table('lotteries')->insert([
            ['id' => 1, 'title' => 'Tirage de rentrée', 'description' => 'Participez pour tenter de remporter un panier gourmand.', 'prize' => 'Panier gourmand (valeur 40€)', 'ends_at' => '2026-09-02', 'status' => 'terminee', 'qr_token' => 'd6a183ecb935c3c57576', 'winner_user_id' => 8, 'drawn_at' => '2026-08-23 13:59:28', 'created_at' => '2026-08-19 15:42:57', 'deleted_at' => null],
            ['id' => 2, 'title' => 'TEST_LOTERIE_QR', 'description' => 'desc test', 'prize' => 'Un lot test', 'ends_at' => '2026-12-31', 'status' => 'active', 'qr_token' => '24b2c90f768b6770b43a', 'winner_user_id' => null, 'drawn_at' => null, 'created_at' => '2026-08-23 13:55:16', 'deleted_at' => '2026-08-23 13:55:43'],
            ['id' => 3, 'title' => 'ppp', 'description' => 'pppppp', 'prize' => 'voiture fereri', 'ends_at' => '2026-09-01', 'status' => 'terminee', 'qr_token' => '7f88cc5ecf1453ae73b2', 'winner_user_id' => 8, 'drawn_at' => '2026-08-24 10:11:17', 'created_at' => '2026-08-24 10:09:50', 'deleted_at' => null],
            ['id' => 4, 'title' => 'voiture', 'description' => 'voiture', 'prize' => 'voiture', 'ends_at' => '2026-09-06', 'status' => 'active', 'qr_token' => '2013699a8499180a2b4b', 'winner_user_id' => null, 'drawn_at' => null, 'created_at' => '2026-08-28 12:36:44', 'deleted_at' => null],
            ['id' => 5, 'title' => 'qqsqSs', 'description' => 'SSQSSQ', 'prize' => 'QsSQqQ', 'ends_at' => '2026-09-01', 'status' => 'active', 'qr_token' => 'e25601f985bbc337e4a2', 'winner_user_id' => null, 'drawn_at' => null, 'created_at' => '2026-08-28 12:50:33', 'deleted_at' => null],
        ])->saveData();
    }
}
