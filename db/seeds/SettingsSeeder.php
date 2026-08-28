<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Fixture générée depuis la base de données (instantané du 2026-08-28).
 * Reproduit à l'identique le contenu de la table `settings`, identifiants inclus.
 */
final class SettingsSeeder extends AbstractSeed
{
    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM `settings` LIMIT 1')) {
            return;
        }

        $this->table('settings')->insert([
            ['key' => 'ga4_property_id', 'value' => '15195475539'],
            ['key' => 'ga4_service_account_json', 'value' => ''],
            ['key' => 'hours_dim', 'value' => '6h40 - 20h00'],
            ['key' => 'hours_lun_sam', 'value' => '6h40 - 20h30'],
            ['key' => 'latitude', 'value' => '49.6136'],
            ['key' => 'legal_capital_social', 'value' => ''],
            ['key' => 'legal_directeur_publication', 'value' => ''],
            ['key' => 'legal_forme_juridique', 'value' => 'Le commerce'],
            ['key' => 'legal_hebergeur_adresse', 'value' => ''],
            ['key' => 'legal_hebergeur_nom', 'value' => ''],
            ['key' => 'legal_hebergeur_telephone', 'value' => ''],
            ['key' => 'legal_rcs_numero', 'value' => ''],
            ['key' => 'legal_rcs_ville', 'value' => ''],
            ['key' => 'legal_siret', 'value' => ''],
            ['key' => 'longitude', 'value' => '1.5399'],
            ['key' => 'maintenance_enabled', 'value' => '1'],
            ['key' => 'maintenance_message', 'value' => 'Notre site internet est maintenance.'],
            ['key' => 'maintenance_whitelist_ips', 'value' => '127.0.0.1'],
            ['key' => 'shop_address', 'value' => '3 Rue du Maréchal Leclerc'],
            ['key' => 'shop_city', 'value' => 'Forges-les-Eaux'],
            ['key' => 'shop_email', 'value' => 'lecommercetabac@gmail.com'],
            ['key' => 'shop_name', 'value' => 'Le Commerce'],
            ['key' => 'shop_phone', 'value' => '07 81 77 15 52'],
            ['key' => 'shop_zipcode', 'value' => '76440'],
            ['key' => 'social_facebook', 'value' => 'https://facebook.com'],
            ['key' => 'social_instagram', 'value' => 'https://instagram.com'],
        ])->saveData();
    }
}
