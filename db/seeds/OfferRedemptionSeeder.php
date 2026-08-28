<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Fixture générée depuis la base de données (instantané du 2026-08-28).
 * Reproduit à l'identique le contenu de la table `offer_redemptions`, identifiants inclus.
 */
final class OfferRedemptionSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['OfferSeeder', 'UserSeeder'];
    }

    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM `offer_redemptions` LIMIT 1')) {
            return;
        }

        $this->table('offer_redemptions')->insert([
            ['id' => 1, 'offer_id' => 1, 'user_id' => 1, 'code' => 'DEMOCAFE01', 'channel' => 'whatsapp', 'status' => 'utilisee', 'used_at' => '2026-08-16 13:42:57', 'created_at' => '2026-08-14 13:42:57'],
            ['id' => 2, 'offer_id' => 4, 'user_id' => 2, 'code' => 'DEMOBOISSON', 'channel' => 'qr_caisse', 'status' => 'valide', 'used_at' => null, 'created_at' => '2026-08-18 13:42:57'],
        ])->saveData();
    }
}
