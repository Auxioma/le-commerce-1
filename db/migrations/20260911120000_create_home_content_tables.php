<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Rend administrable depuis /admin/page-principale tout le contenu qui
 * était jusqu'ici codé en dur dans home/index.twig : les 6 tuiles de
 * catégories, la liste de services du quotidien affichée dans la carte
 * "Tous vos services" et les suggestions de l'assistant de la page
 * d'accueil. Les tables sont peuplées avec le contenu actuel du site pour
 * que la page reste identique tant que l'admin ne modifie rien.
 */
final class CreateHomeContentTables extends AbstractMigration
{
    public function up(): void
    {
        $commonOpts = [
            'id' => 'id',
            'signed' => false,
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ];

        // Tuiles de catégories affichées juste sous le hero de l'accueil
        // (Le Bar, Tabac, PMU, FDJ, Presse, Nos services...).
        $this->table('home_categories', $commonOpts)
            ->addColumn('name', 'string', ['limit' => 100])
            ->addColumn('description', 'string', ['limit' => 180, 'null' => true])
            ->addColumn('icon', 'text')
            ->addColumn('link', 'string', ['limit' => 255])
            ->addColumn('display_order', 'integer', ['signed' => false, 'default' => 0])
            ->addColumn('status', 'enum', ['values' => ['active', 'inactif'], 'default' => 'active'])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->create();

        // Liste de services listés dans la carte "Tous vos services du quotidien".
        $this->table('home_quick_services', $commonOpts)
            ->addColumn('label', 'string', ['limit' => 160])
            ->addColumn('icon_key', 'string', ['limit' => 40, 'default' => 'plus'])
            ->addColumn('color', 'string', ['limit' => 7, 'default' => '#c8272c'])
            ->addColumn('display_order', 'integer', ['signed' => false, 'default' => 0])
            ->addColumn('status', 'enum', ['values' => ['active', 'inactif'], 'default' => 'active'])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->create();

        // Suggestions cliquables proposées par l'assistant de l'accueil.
        $this->table('home_chips', $commonOpts)
            ->addColumn('label', 'string', ['limit' => 160])
            ->addColumn('display_order', 'integer', ['signed' => false, 'default' => 0])
            ->addColumn('status', 'enum', ['values' => ['active', 'inactif'], 'default' => 'active'])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->create();

        $now = date('Y-m-d H:i:s');

        $this->table('home_categories')->insert([
            ['name' => 'LE BAR', 'description' => 'Cafés, bières, cocktails & spiritueux', 'link' => '/le-bar', 'display_order' => 1, 'status' => 'active', 'icon' => '<path d="M5 8h11v9a3 3 0 01-3 3H8a3 3 0 01-3-3V8z"></path><path d="M16 10h1.5a2.5 2.5 0 010 5H16"></path><path d="M8 8V5a1 1 0 011-1h1"></path><line x1="8" y1="12" x2="13" y2="12"></line>', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'TABAC', 'description' => 'Cigarettes, cigares électroniques', 'link' => '/tabac', 'display_order' => 2, 'status' => 'active', 'icon' => '<rect x="3" y="9" width="18" height="12" rx="1"></rect><line x1="3" y1="13" x2="21" y2="13"></line><line x1="7" y1="9" x2="7" y2="13"></line><line x1="11" y1="9" x2="11" y2="13"></line><path d="M9 9V6a2 2 0 012-2h2a2 2 0 012 2v3"></path>', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'PMU', 'description' => 'Pariez sur vos courses préférées', 'link' => '/pmu', 'display_order' => 3, 'status' => 'active', 'icon' => '<path d="M4 20v-3.5c0-.9.5-1.7 1.3-2.1L8 13"></path><path d="M8 13V8c0-2.8 2.2-5.5 5.5-5.5 1 0 1.9.3 2.7.8"></path><path d="M16.2 3.3c1.7 1 3.3 3 3.3 5.7 0 1.4-.4 2.3-1 3.3l-1.2 2v3.2c0 1.4.4 2 1.2 2.5"></path><path d="M8 13l3 1.2"></path><path d="M13 6.5c.6-.3 1.3-.3 1.8 0"></path>', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'FDJ', 'description' => 'Jeux de la franchise des Jeux', 'link' => '/fdj', 'display_order' => 4, 'status' => 'active', 'icon' => '<path d="M12 12c0-2.5-2-4.5-4.5-4.5S3 9.5 3 12s2 4.5 4.5 4.5S12 14.5 12 12z"></path><path d="M12 12c0-2.5 2-4.5 4.5-4.5S21 9.5 21 12s-2 4.5-4.5 4.5S12 14.5 12 12z"></path><path d="M12 12c-2.5 0-4.5-2-4.5-4.5S9.5 3 12 3s4.5 2 4.5 4.5S14.5 12 12 12z"></path><path d="M12 12c-2.5 0-4.5 2-4.5 4.5S9.5 21 12 21s4.5-2 4.5-4.5S14.5 12 12 12z"></path><line x1="12" y1="13" x2="12" y2="19"></line>', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'PRESSE', 'description' => 'Journaux, magazines et livres', 'link' => '/presse', 'display_order' => 5, 'status' => 'active', 'icon' => '<path d="M4 4h16a2 2 0 012 2v12a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2z"></path><line x1="8" y1="8" x2="16" y2="8"></line><line x1="8" y1="12" x2="16" y2="12"></line><line x1="8" y1="16" x2="12" y2="16"></line>', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'NOS SERVICES', 'description' => 'Paiement de proximité, relais colis & plus', 'link' => '/nos-services', 'display_order' => 6, 'status' => 'active', 'icon' => '<polyline points="21 8 21 21 3 21 3 8"></polyline><rect x="1" y="3" width="22" height="5"></rect><path d="M10 12v8M14 12v8"></path>', 'created_at' => $now, 'updated_at' => $now],
        ])->saveData();

        $this->table('home_quick_services')->insert([
            ['label' => 'Payer vos factures', 'icon_key' => 'clipboard', 'color' => '#c8272c', 'display_order' => 1, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'Amendes & sanctions', 'icon_key' => 'alert', 'color' => '#e08a1e', 'display_order' => 2, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'Paysafecard / Neosurf', 'icon_key' => 'card', 'color' => '#2e6fd6', 'display_order' => 3, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'BlaBlaCar', 'icon_key' => 'car', 'color' => '#1a1a3d', 'display_order' => 4, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'Réserver votre place de bus', 'icon_key' => 'bus', 'color' => '#c8272c', 'display_order' => 5, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'Relais colis', 'icon_key' => 'package', 'color' => '#d6a12e', 'display_order' => 6, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['label' => "Retrait d'espèces", 'icon_key' => 'eye', 'color' => '#2e6fd6', 'display_order' => 7, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'Et bien plus encore !', 'icon_key' => 'plus', 'color' => '#888888', 'display_order' => 8, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
        ])->saveData();

        $this->table('home_chips')->insert([
            ['label' => 'Êtes-vous ouvert ?', 'display_order' => 1, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'Match ce soir ?', 'display_order' => 2, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'Bières disponibles ?', 'display_order' => 3, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'Horaires PMU ?', 'display_order' => 4, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'Réserver une table ?', 'display_order' => 5, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'Jeux FDJ ?', 'display_order' => 6, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
        ])->saveData();

        // Textes éditoriaux de l'accueil (hero, cartes, colonne d'infos),
        // stockés dans la table `settings` déjà utilisée pour tout le
        // contenu texte simple du site (mentions légales, etc.).
        $pdo = $this->getAdapter()->getConnection();
        $stmt = $pdo->prepare(
            'INSERT INTO settings (`key`, `value`) VALUES (:key, :value)
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)'
        );
        foreach ($this->defaultContent() as $key => $value) {
            $stmt->execute(['key' => $key, 'value' => $value]);
        }
    }

    public function down(): void
    {
        $pdo = $this->getAdapter()->getConnection();
        $stmt = $pdo->prepare('DELETE FROM settings WHERE `key` = :key');
        foreach (array_keys($this->defaultContent()) as $key) {
            $stmt->execute(['key' => $key]);
        }

        $this->table('home_chips')->drop()->save();
        $this->table('home_quick_services')->drop()->save();
        $this->table('home_categories')->drop()->save();
    }

    /**
     * @return array<string,string>
     */
    private function defaultContent(): array
    {
        return [
            'home_hero_badge'       => 'VOTRE COMMERCE DE PROXIMITÉ À FORGES-LES-EAUX',
            'home_hero_title'       => 'LE COMMERCE',
            'home_hero_subtitle'    => 'BAR • TABAC • PMU • FDJ • PRESSE • NIRIO',
            'home_hero_tagline'     => 'Votre lieu convivial à Forges-les-Eaux',
            'home_hero_description' => 'Un endroit chaleureux pour se retrouver, se détendre et profiter de nombreux services au quotidien.',
            'home_hero_btn1_label'  => 'DÉCOUVRIR LE BAR',
            'home_hero_btn1_link'   => '/le-bar',
            'home_hero_btn2_label'  => 'NOUS TROUVER',
            'home_hero_btn2_link'   => '/contact',

            'home_beers_title'        => 'NOS BIÈRES À DÉCOUVRIR',
            'home_beers_button_label' => 'VOIR LA CARTE COMPLÈTE DES BOISSONS',

            'home_saucisson_title'         => 'NOTRE PLANCHE À SAUCISSON',
            'home_saucisson_description'   => 'Saucisson, cornichons, fromage et pain frais. Le plaisir de partager un bon moment !',
            'home_saucisson_button_label'  => 'DÉCOUVRIR NOTRE PLANCHE',

            'home_whatsapp_title'        => "REJOIGNEZ-NOUS<br>SUR WHATSAPP !",
            'home_whatsapp_description'  => 'Recevez en exclusivité nos promotions, événements et nouveautés !',
            'home_whatsapp_button_label' => "JE M'INSCRIS",

            'home_services_card_title'        => "TOUS VOS SERVICES<br>DU QUOTIDIEN",
            'home_services_card_button_label' => 'VOIR TOUS LES SERVICES',

            'home_avis_title'        => 'AVIS GOOGLE',
            'home_avis_button_label' => 'LAISSER UN AVIS',

            'home_deals_title'        => 'LES BONS PLANS<br>DU MOMENT',
            'home_deals_empty_text'   => 'Aucune offre en cours',
            'home_deals_button_label' => 'EN PROFITER',

            'home_weather_title'       => 'MÉTÉO À',
            'home_weather_footer_text' => 'Profitez de notre terrasse !',

            'home_assistant_title'       => 'ASSISTANT LE COMMERCE',
            'home_assistant_greeting'    => 'Bonjour ! 👋<br>Que recherchez-vous aujourd\'hui ?',
            'home_assistant_placeholder' => 'Écrivez votre question...',
        ];
    }
}
