<?php
/**
 * Configuration générale de l'application
 */

return [
    'name'        => 'Le Commerce',
    'baseline'    => 'BAR · TABAC · PMU · FDJ · PRESSE · NIRIO',
    'url'         => getenv('APP_URL') ?: 'http://localhost:8000',
    'env'         => getenv('APP_ENV') ?: 'development',
    'debug'       => filter_var(getenv('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN),
    'timezone'    => 'Europe/Paris',
    'locale'      => 'fr',
    // Identifiant de mesure Google Analytics 4 (ex. "G-XXXXXXXXXX").
    // Le script n'est chargé côté navigateur qu'après consentement de
    // l'utilisateur via la bannière cookies (voir partials/cookie-consent.php).
    'google_analytics_id' => getenv('GOOGLE_ANALYTICS_ID') ?: '',

    // Informations de l'établissement (affichées dans header/footer)
    'shop' => [
        'name'      => 'Le Commerce',
        'address'   => '3 Rue du Maréchal Leclerc',
        'zipcode'   => '76440',
        'city'      => 'Forges-les-Eaux',
        'phone'     => '07 81 77 15 52',
        'phone_href'=> '+33781771552',
        // Numéro WhatsApp, séparé du téléphone (ex. si une ligne dédiée est
        // utilisée pour les messages). Vide par défaut : les liens WhatsApp
        // utilisent alors le téléphone ci-dessus.
        'whatsapp'      => '',
        'whatsapp_href' => '+33781771552',
        'email'     => 'lecommercetabac@gmail.com',
        'hours' => [
            'lun_sam' => '6h40 - 20h30',
            'dim'     => '6h40 - 20h00',
        ],
        'social' => [
            'facebook'  => 'https://facebook.com',
            'instagram' => 'https://instagram.com',
        ],
        'google_rating' => 4.8,
        'google_reviews_count' => 230,
        // Coordonnées GPS du commerce, utilisées pour le calcul de distance
        // dans le module Zonage & Proximité (Lot 7).
        'latitude'  => 49.6136,
        'longitude' => 1.5399,

        // Informations légales (mentions légales / CGU / CGV), éditables
        // depuis /admin/parametres. Vides par défaut : à compléter par le
        // commerçant, affichées en « — » tant qu'elles ne le sont pas.
        'legal' => [
            'forme_juridique'       => '',
            'capital_social'        => '',
            'siret'                 => '',
            'rcs_numero'            => '',
            'rcs_ville'             => '',
            'directeur_publication' => '',
            'hebergeur_nom'         => '',
            'hebergeur_adresse'     => '',
            'hebergeur_telephone'   => '',
        ],
    ],
];
