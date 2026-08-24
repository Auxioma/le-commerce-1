<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class OfferRedemptionSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['OfferSeeder', 'UserSeeder'];
    }

    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM offer_redemptions LIMIT 1')) {
            return;
        }

        $ago = fn(string $interval) => date('Y-m-d H:i:s', strtotime("-{$interval}"));

        $offerCafe = (int) $this->fetchRow("SELECT id FROM offers WHERE title = 'Café offert' LIMIT 1")['id'];
        $offerBoisson = (int) $this->fetchRow("SELECT id FROM offers WHERE title = 'Boisson offerte' LIMIT 1")['id'];

        // Code déjà utilisé pour Jean Martin (démo de l'historique)
        $this->table('offer_redemptions')->insert([
            'offer_id' => $offerCafe, 'user_id' => 1, 'code' => 'DEMOCAFE01',
            'channel' => 'whatsapp', 'status' => 'utilisee',
            'used_at' => $ago('3 days'), 'created_at' => $ago('5 days'),
        ])->saveData();

        // Code encore valide pour Sophie Petit (démo du scan)
        $this->table('offer_redemptions')->insert([
            'offer_id' => $offerBoisson, 'user_id' => 2, 'code' => 'DEMOBOISSON',
            'channel' => 'qr_caisse', 'status' => 'valide',
            'created_at' => $ago('1 day'),
        ])->saveData();
    }
}
