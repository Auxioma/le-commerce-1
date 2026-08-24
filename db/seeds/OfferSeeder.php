<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class OfferSeeder extends AbstractSeed
{
    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM offers LIMIT 1')) {
            return;
        }

        $in = fn(string $interval) => date('Y-m-d', strtotime("+{$interval}"));

        $this->table('offers')->insert([
            ['title' => 'Café offert', 'description' => 'Un café offert pour bien commencer la journée !', 'type' => 'gratuite', 'value' => 1.50, 'target_segment' => 'tous', 'valid_until' => $in('30 days'), 'status' => 'active'],
            ['title' => '2 viennoiseries + 1 offerte', 'description' => 'Pour accompagner votre café du matin.', 'type' => 'x_plus_1', 'value' => 2.00, 'target_segment' => 'tous', 'valid_until' => $in('20 days'), 'status' => 'active'],
            ['title' => '-20% sur les sandwichs', 'description' => 'Profitez de 20% de réduction sur tous les sandwichs.', 'type' => 'reduction_pourcentage', 'value' => 20, 'target_segment' => 'fideles', 'valid_until' => $in('30 days'), 'status' => 'active'],
            ['title' => 'Boisson offerte', 'description' => "Une boisson offerte dès 10€ d'achat.", 'type' => 'montant_minimum', 'value' => 3.00, 'target_segment' => 'tous', 'valid_until' => $in('25 days'), 'status' => 'active'],
            ['title' => 'Happy Hour VIP', 'description' => 'Offre spéciale clients fidèles, brouillon en préparation.', 'type' => 'personnalisee', 'value' => 5.00, 'target_segment' => 'fideles', 'valid_until' => $in('10 days'), 'status' => 'brouillon'],
        ])->saveData();
    }
}
