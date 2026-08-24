-- =====================================================================
-- Lot 18 - Catalogue de services du quotidien piloté depuis l'admin
-- Remplace la liste statique de ServicesController par une vraie table,
-- éditable dans /admin/services et affichée sur la page publique /nos-services.
-- =====================================================================

CREATE TABLE IF NOT EXISTS services (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug        VARCHAR(60) NOT NULL UNIQUE,
    name        VARCHAR(100) NOT NULL,
    description TEXT NULL,
    icon        VARCHAR(255) NOT NULL DEFAULT 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    sort_order  INT UNSIGNED NOT NULL DEFAULT 0,
    status      ENUM('active','inactif') NOT NULL DEFAULT 'active',
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Reprise des 8 services historiquement codés en dur dans
-- App\Controllers\ServicesController, avec les mêmes slugs que ceux
-- déjà déclarés dans config/image_slots.php (continuité des photos
-- déjà uploadées via /admin/images).
-- ---------------------------------------------------------------------
INSERT INTO services (slug, name, description, icon, sort_order, status) VALUES
('services_relais_colis', 'Relais colis', 'Dépôt et retrait de vos colis en point relais, sans rendez-vous.', 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 1, 'active'),
('services_paiement_factures', 'Paiement de factures', 'Réglez vos factures d''énergie, de téléphonie et d''assurance directement en caisse.', 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v14a2 2 0 01-2 2z', 2, 'active'),
('services_amendes_timbres', 'Amendes & timbres fiscaux', 'Paiement des amendes et achat de timbres fiscaux (passeport, titre de séjour...).', 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z', 3, 'active'),
('services_paysafecard_neosurf', 'Paysafecard & Neosurf', 'Recharges de cartes prépayées pour vos achats et jeux en ligne en toute sécurité.', 'M3 10h18M7 15h1m4 0h1m-9 4h16a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 4, 'active'),
('services_retrait_depot', 'Retrait & dépôt d''espèces', 'Retirez ou déposez du liquide sur votre compte bancaire, sans distributeur.', 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-2m-9-5h9m0 0l-3-3m3 3l-3 3', 5, 'active'),
('services_blablacar_bus', 'BlaBlaCar & réservation bus', 'Réservez vos trajets BlaBlaCar Bus et vos billets de transport directement sur place.', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 6, 'active'),
('services_photocopies', 'Photocopies & impressions', 'Photocopies noir & blanc ou couleur, impressions de documents à l''unité.', 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 7, 'active'),
('services_recharge_mobile', 'Recharge mobile', 'Recharges prépayées pour tous les opérateurs de téléphonie mobile.', 'M13 10V3L4 14h7v7l9-11h-7z', 8, 'active');
