<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class ProximityCampaignSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['OfferSeeder'];
    }

    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM proximity_campaigns LIMIT 1')) {
            return;
        }

        $offerCafeRow = $this->fetchRow("SELECT id FROM offers WHERE title = 'Café offert' LIMIT 1");
        $offerBoissonRow = $this->fetchRow("SELECT id FROM offers WHERE title = 'Boisson offerte' LIMIT 1");
        $offerCafe = $offerCafeRow ? (int) $offerCafeRow['id'] : null;
        $offerBoisson = $offerBoissonRow ? (int) $offerBoissonRow['id'] : null;

        $this->table('proximity_campaigns')->insert([
            [
                'name' => 'Café du matin', 'radius_m' => 500, 'start_time' => '10:00:00', 'end_time' => '11:00:00',
                'days' => 'lun,mar,mer,jeu,ven', 'target_segment' => 'tous', 'offer_id' => $offerCafe,
                'message' => "👋 Bonjour ! Vous n'êtes pas loin du Commerce. Nous vous offrons un café entre 10h00 et 11h00. Présentez ce QR code en caisse !",
                'status' => 'active', 'sent_count' => 124, 'used_count' => 32,
            ],
            [
                'name' => 'Happy Hour Leffe', 'radius_m' => 1000, 'start_time' => '17:00:00', 'end_time' => '19:00:00',
                'days' => 'lun,mar,mer,jeu,ven,sam,dim', 'target_segment' => 'tous', 'offer_id' => null,
                'message' => '🍺 Happy Hour au Commerce ! La Leffe à 5€ jusqu\'à 19h. Venez en profiter !',
                'status' => 'active', 'sent_count' => 215, 'used_count' => 48,
            ],
            [
                'name' => 'Soir de match', 'radius_m' => 2000, 'start_time' => '19:00:00', 'end_time' => '23:00:00',
                'days' => 'ven,sam,dim', 'target_segment' => 'tous', 'offer_id' => $offerBoisson,
                'message' => '⚽ Soir de match au Commerce ! Une boisson offerte pour l\'ambiance.',
                'status' => 'active', 'sent_count' => 156, 'used_count' => 28,
            ],
        ])->saveData();
    }
}
