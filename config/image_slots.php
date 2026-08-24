<?php

declare(strict_types=1);

/**
 * Registre des emplacements d'images pilotables depuis /admin/images.
 * Chaque clé (slug) est utilisée telle quelle par le front-office
 * (voir 'slug' dans les contrôleurs et $heroSlug dans les pages) et par
 * l'écran admin pour lister les emplacements — ne pas les désynchroniser :
 * ajouter/renommer un slug ici implique de faire le même changement côté
 * contrôleur/vue qui l'utilise, et inversement.
 */

return [
    'Bannières' => [
        'hero_accueil'  => "Page d'accueil",
        'hero_bar'      => 'Page Le Bar',
        'hero_tabac'    => 'Page Tabac',
        'hero_pmu'      => 'Page PMU',
        'hero_fdj'      => 'Page FDJ',
        'hero_presse'   => 'Page Presse',
        'hero_services' => 'Page Nos Services',
        'hero_contact'  => 'Page Contact',
    ],
    'Identité' => [
        'logo_site' => 'Logo du commerce',
    ],
    'Bar — Planches' => [
        'bar_planche_saucisson' => 'Planche à saucisson',
        'bar_planche_mixte'     => 'Planche mixte',
        'bar_planche_fromage'   => 'Planche fromage',
    ],
    'Presse' => [
        'presse_quotidiens_nationaux' => 'Quotidiens nationaux',
        'presse_regionale'            => 'Presse régionale',
        'presse_magazines'            => 'Magazines & spécialisée',
        'presse_jeunesse'             => 'Presse jeunesse',
    ],
    'Services' => [
        'services_relais_colis'        => 'Relais colis',
        'services_paiement_factures'   => 'Paiement de factures',
        'services_amendes_timbres'     => 'Amendes & timbres fiscaux',
        'services_paysafecard_neosurf' => 'Paysafecard & Neosurf',
        'services_retrait_depot'       => "Retrait & dépôt d'espèces",
        'services_blablacar_bus'       => 'BlaBlaCar & réservation bus',
        'services_photocopies'         => 'Photocopies & impressions',
        'services_recharge_mobile'     => 'Recharge mobile',
    ],
];
