<?php

declare(strict_types=1);

/**
 * Registre des emplacements d'images pilotables depuis /admin/images.
 * Chaque clé (slug) est utilisée telle quelle par le front-office
 * (voir 'slug' dans les contrôleurs et $heroSlug dans les pages) et par
 * l'écran admin pour lister les emplacements — ne pas les désynchroniser :
 * ajouter/renommer un slug ici implique de faire le même changement côté
 * contrôleur/vue qui l'utilise, et inversement.
 *
 * 'default' est la photo affichée en miniature côté admin tant qu'aucune
 * photo n'a été envoyée : elle doit reproduire exactement la valeur par
 * défaut passée à site_image() à l'endroit où le slug est utilisé (sinon
 * l'aperçu admin mentirait sur ce qui s'affiche réellement sur le site).
 * `null` quand l'emplacement n'a pas de photo de repli (icône ou logo).
 */

return [
    'Bannières' => [
        'hero_accueil'  => ['label' => "Page d'accueil", 'default' => '/assets/images/hero-facade.jpg'],
        'hero_bar'      => ['label' => 'Page Le Bar', 'default' => 'https://images.unsplash.com/photo-1528605248644-14dd04022da1?q=80&auto=format&fit=crop&w=1600'],
        'hero_tabac'    => ['label' => 'Page Tabac', 'default' => 'https://images.unsplash.com/photo-1528605248644-14dd04022da1?q=80&auto=format&fit=crop&w=1600'],
        'hero_pmu'      => ['label' => 'Page PMU', 'default' => 'https://images.unsplash.com/photo-1528605248644-14dd04022da1?q=80&auto=format&fit=crop&w=1600'],
        'hero_fdj'      => ['label' => 'Page FDJ', 'default' => 'https://images.unsplash.com/photo-1528605248644-14dd04022da1?q=80&auto=format&fit=crop&w=1600'],
        'hero_presse'   => ['label' => 'Page Presse', 'default' => 'https://images.unsplash.com/photo-1528605248644-14dd04022da1?q=80&auto=format&fit=crop&w=1600'],
        'hero_services'   => ['label' => 'Page Nos Services', 'default' => 'https://images.unsplash.com/photo-1528605248644-14dd04022da1?q=80&auto=format&fit=crop&w=1600'],
        'hero_actualites' => ['label' => 'Page Actualités', 'default' => 'https://images.unsplash.com/photo-1528605248644-14dd04022da1?q=80&auto=format&fit=crop&w=1600'],
        'hero_contact'    => ['label' => 'Page Contact', 'default' => 'https://images.unsplash.com/photo-1528605248644-14dd04022da1?q=80&auto=format&fit=crop&w=1600'],
    ],
    'Accueil — visuels' => [
        'accueil_nos_bieres'      => ['label' => 'Nos bières à découvrir', 'default' => '/assets/images/beers-strip.jpg'],
        'accueil_qrcode_whatsapp' => ['label' => 'QR code WhatsApp', 'default' => '/assets/images/qrcode.jpg'],
        'accueil_bons_plans'      => ['label' => 'Bons plans du moment', 'default' => '/assets/images/happy-hour.jpg'],
    ],
    'Identité' => [
        'logo_site' => ['label' => 'Logo du commerce', 'default' => null],
    ],
    'Bar — Planches' => [
        'bar_planche_saucisson' => ['label' => 'Planche à saucisson', 'default' => '/assets/images/charcuterie.jpg'],
        'bar_planche_mixte'     => ['label' => 'Planche mixte', 'default' => null],
        'bar_planche_fromage'   => ['label' => 'Planche fromage', 'default' => null],
    ],
    'Presse' => [
        'presse_quotidiens_nationaux' => ['label' => 'Quotidiens nationaux', 'default' => null],
        'presse_regionale'            => ['label' => 'Presse régionale', 'default' => null],
        'presse_magazines'            => ['label' => 'Magazines & spécialisée', 'default' => null],
        'presse_jeunesse'             => ['label' => 'Presse jeunesse', 'default' => null],
    ],
    'Services' => [
        'services_relais_colis'        => ['label' => 'Relais colis', 'default' => null],
        'services_paiement_factures'   => ['label' => 'Paiement de factures', 'default' => null],
        'services_amendes_timbres'     => ['label' => 'Amendes & timbres fiscaux', 'default' => null],
        'services_paysafecard_neosurf' => ['label' => 'Paysafecard & Neosurf', 'default' => null],
        'services_retrait_depot'       => ['label' => "Retrait & dépôt d'espèces", 'default' => null],
        'services_blablacar_bus'       => ['label' => 'BlaBlaCar & réservation bus', 'default' => null],
        'services_photocopies'         => ['label' => 'Photocopies & impressions', 'default' => null],
        'services_recharge_mobile'     => ['label' => 'Recharge mobile', 'default' => null],
    ],
];
