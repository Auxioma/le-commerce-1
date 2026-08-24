<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class SettingsSeeder extends AbstractSeed
{
    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM settings LIMIT 1')) {
            return;
        }

        // Fusion migration_lot10_settings.sql + migration_lot16_google_analytics.sql
        $this->table('settings')->insert([
            ['key' => 'shop_name', 'value' => 'Le Commerce'],
            ['key' => 'shop_address', 'value' => '3 Rue du Maréchal Leclerc'],
            ['key' => 'shop_zipcode', 'value' => '76440'],
            ['key' => 'shop_city', 'value' => 'Forges-les-Eaux'],
            ['key' => 'shop_phone', 'value' => '07 81 77 15 52'],
            ['key' => 'shop_email', 'value' => 'lecommercetabac@gmail.com'],
            ['key' => 'hours_lun_sam', 'value' => '6h40 - 20h30'],
            ['key' => 'hours_dim', 'value' => '6h40 - 20h00'],
            ['key' => 'social_facebook', 'value' => 'https://facebook.com'],
            ['key' => 'social_instagram', 'value' => 'https://instagram.com'],
            ['key' => 'latitude', 'value' => '49.6136'],
            ['key' => 'longitude', 'value' => '1.5399'],
            ['key' => 'ga4_property_id', 'value' => ''],
            ['key' => 'ga4_service_account_json', 'value' => ''],
        ])->saveData();
    }
}
