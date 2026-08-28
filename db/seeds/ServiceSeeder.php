<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Fixture générée depuis la base de données (instantané du 2026-08-28).
 * Reproduit à l'identique le contenu de la table `services`, identifiants inclus.
 */
final class ServiceSeeder extends AbstractSeed
{
    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM `services` LIMIT 1')) {
            return;
        }

        $this->table('services')->insert([
            ['id' => 1, 'slug' => 'services_relais_colis', 'name' => 'Relais colis', 'description' => 'Dépôt et retrait de vos colis en point relais, sans rendez-vous.', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'sort_order' => 1, 'status' => 'active', 'created_at' => '2026-08-19 15:42:57', 'updated_at' => '2026-08-19 15:42:57'],
            ['id' => 2, 'slug' => 'services_paiement_factures', 'name' => 'Paiement de factures', 'description' => 'Réglez vos factures d\'énergie, de téléphonie et d\'assurance directement en caisse.', 'icon' => 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v14a2 2 0 01-2 2z', 'sort_order' => 2, 'status' => 'active', 'created_at' => '2026-08-19 15:42:57', 'updated_at' => '2026-08-19 15:42:57'],
            ['id' => 3, 'slug' => 'services_amendes_timbres', 'name' => 'Amendes & timbres fiscaux', 'description' => 'Paiement des amendes et achat de timbres fiscaux (passeport, titre de séjour...).', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z', 'sort_order' => 3, 'status' => 'active', 'created_at' => '2026-08-19 15:42:57', 'updated_at' => '2026-08-19 15:42:57'],
            ['id' => 4, 'slug' => 'services_paysafecard_neosurf', 'name' => 'Paysafecard & Neosurf', 'description' => 'Recharges de cartes prépayées pour vos achats et jeux en ligne en toute sécurité.', 'icon' => 'M3 10h18M7 15h1m4 0h1m-9 4h16a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'sort_order' => 4, 'status' => 'active', 'created_at' => '2026-08-19 15:42:57', 'updated_at' => '2026-08-19 15:42:57'],
            ['id' => 5, 'slug' => 'services_retrait_depot', 'name' => 'Retrait & dépôt d\'espèces', 'description' => 'Retirez ou déposez du liquide sur votre compte bancaire, sans distributeur.', 'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-2m-9-5h9m0 0l-3-3m3 3l-3 3', 'sort_order' => 5, 'status' => 'active', 'created_at' => '2026-08-19 15:42:57', 'updated_at' => '2026-08-19 15:42:57'],
            ['id' => 6, 'slug' => 'services_blablacar_bus', 'name' => 'BlaBlaCar & réservation bus', 'description' => 'Réservez vos trajets BlaBlaCar Bus et vos billets de transport directement sur place.', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'sort_order' => 6, 'status' => 'active', 'created_at' => '2026-08-19 15:42:57', 'updated_at' => '2026-08-19 15:42:57'],
            ['id' => 7, 'slug' => 'services_photocopies', 'name' => 'Photocopies & impressions', 'description' => 'Photocopies noir & blanc ou couleur, impressions de documents à l\'unité.', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'sort_order' => 7, 'status' => 'active', 'created_at' => '2026-08-19 15:42:57', 'updated_at' => '2026-08-19 15:42:57'],
            ['id' => 8, 'slug' => 'services_recharge_mobile', 'name' => 'Recharge mobile', 'description' => 'Recharges prépayées pour tous les opérateurs de téléphonie mobile.', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'sort_order' => 8, 'status' => 'active', 'created_at' => '2026-08-19 15:42:57', 'updated_at' => '2026-08-19 15:42:57'],
        ])->saveData();
    }
}
