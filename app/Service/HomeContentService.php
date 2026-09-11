<?php

declare(strict_types=1);

namespace App\Service;

use App\Models\Settings;

/**
 * Textes éditoriaux de la page d'accueil (hero, cartes, colonne d'infos),
 * administrables depuis /admin/page-principale et stockés dans la table
 * `settings` (préfixe home_*). Utilisé à la fois par HomeController (rendu
 * public) et AdminHomeController (formulaire d'édition), pour ne définir
 * les valeurs par défaut qu'à un seul endroit.
 */
class HomeContentService
{
    /**
     * @return array<string,string>
     */
    public static function defaults(): array
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

            'home_saucisson_title'        => 'NOTRE PLANCHE À SAUCISSON',
            'home_saucisson_description'  => 'Saucisson, cornichons, fromage et pain frais. Le plaisir de partager un bon moment !',
            'home_saucisson_button_label' => 'DÉCOUVRIR NOTRE PLANCHE',

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
            'home_assistant_greeting'    => "Bonjour ! 👋<br>Que recherchez-vous aujourd'hui ?",
            'home_assistant_placeholder' => 'Écrivez votre question...',
        ];
    }

    /**
     * @return array<string,string>
     */
    public static function get(): array
    {
        $result = [];
        foreach (self::defaults() as $key => $default) {
            $result[$key] = Settings::get($key, $default);
        }
        return $result;
    }
}
