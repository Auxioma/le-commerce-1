<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Fixture générée depuis la base de données (instantané du 2026-08-28).
 * Reproduit à l'identique le contenu de la table `offers`, identifiants inclus.
 */
final class OfferSeeder extends AbstractSeed
{
    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM `offers` LIMIT 1')) {
            return;
        }

        $this->table('offers')->insert([
            ['id' => 1, 'title' => 'Café offert', 'description' => 'Un café offert pour bien commencer la journée !', 'type' => 'gratuite', 'value' => '1.50', 'target_segment' => 'tous', 'valid_until' => '2026-09-18', 'status' => 'active', 'created_at' => '2026-08-19 15:42:57'],
            ['id' => 2, 'title' => '2 viennoiseries + 1 offerte', 'description' => 'Pour accompagner votre café du matin.', 'type' => 'x_plus_1', 'value' => '2.00', 'target_segment' => 'tous', 'valid_until' => '2026-09-08', 'status' => 'active', 'created_at' => '2026-08-19 15:42:57'],
            ['id' => 3, 'title' => '-20% sur les sandwichs', 'description' => 'Profitez de 20% de réduction sur tous les sandwichs.', 'type' => 'reduction_pourcentage', 'value' => '20.00', 'target_segment' => 'fideles', 'valid_until' => '2026-09-18', 'status' => 'active', 'created_at' => '2026-08-19 15:42:57'],
            ['id' => 4, 'title' => 'Boisson offerte', 'description' => 'Une boisson offerte dès 10€ d\'achat.', 'type' => 'montant_minimum', 'value' => '3.00', 'target_segment' => 'tous', 'valid_until' => '2026-09-13', 'status' => 'active', 'created_at' => '2026-08-19 15:42:57'],
            ['id' => 5, 'title' => 'Happy Hour VIP', 'description' => 'Offre spéciale clients fidèles, brouillon en préparation.', 'type' => 'personnalisee', 'value' => '5.00', 'target_segment' => 'fideles', 'valid_until' => '2026-08-29', 'status' => 'brouillon', 'created_at' => '2026-08-19 15:42:57'],
            ['id' => 6, 'title' => 'dqsdsq', 'description' => 'dsqsdqsdsqdsq', 'type' => 'gratuite', 'value' => '10.00', 'target_segment' => 'fideles', 'valid_until' => '2026-08-17', 'status' => 'active', 'created_at' => '2026-08-28 21:10:39'],
        ])->saveData();
    }
}
