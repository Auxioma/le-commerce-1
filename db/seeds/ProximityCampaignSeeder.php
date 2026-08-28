<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Fixture générée depuis la base de données (instantané du 2026-08-28).
 * Reproduit à l'identique le contenu de la table `proximity_campaigns`, identifiants inclus.
 */
final class ProximityCampaignSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['OfferSeeder'];
    }

    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM `proximity_campaigns` LIMIT 1')) {
            return;
        }

        $this->table('proximity_campaigns')->insert([
            ['id' => 1, 'name' => 'Café du matin', 'radius_m' => 500, 'start_time' => '10:00:00', 'end_time' => '11:00:00', 'days' => 'lun,mar,mer,jeu,ven', 'target_segment' => 'tous', 'offer_id' => 1, 'message' => '👋 Bonjour ! Vous n\'êtes pas loin du Commerce. Nous vous offrons un café entre 10h00 et 11h00. Présentez ce QR code en caisse !', 'status' => 'active', 'sent_count' => 124, 'used_count' => 32, 'created_at' => '2026-08-19 15:42:57'],
            ['id' => 2, 'name' => 'Happy Hour Leffe', 'radius_m' => 1000, 'start_time' => '17:00:00', 'end_time' => '19:00:00', 'days' => 'lun,mar,mer,jeu,ven,sam,dim', 'target_segment' => 'tous', 'offer_id' => null, 'message' => '🍺 Happy Hour au Commerce ! La Leffe à 5€ jusqu\'à 19h. Venez en profiter !', 'status' => 'active', 'sent_count' => 215, 'used_count' => 48, 'created_at' => '2026-08-19 15:42:57'],
            ['id' => 3, 'name' => 'Soir de match', 'radius_m' => 2000, 'start_time' => '19:00:00', 'end_time' => '23:00:00', 'days' => 'ven,sam,dim', 'target_segment' => 'tous', 'offer_id' => 4, 'message' => '⚽ Soir de match au Commerce ! Une boisson offerte pour l\'ambiance.', 'status' => 'active', 'sent_count' => 156, 'used_count' => 28, 'created_at' => '2026-08-19 15:42:57'],
            ['id' => 4, 'name' => 'toto', 'radius_m' => 3400, 'start_time' => '10:00:00', 'end_time' => '11:00:00', 'days' => 'lun,mar,mer,jeu,ven', 'target_segment' => 'fideles', 'offer_id' => 4, 'message' => 'toto va a la plage', 'status' => 'active', 'sent_count' => 0, 'used_count' => 0, 'created_at' => '2026-08-28 12:52:18'],
        ])->saveData();
    }
}
