-- =====================================================================
-- Migration Lot 16 : page Google Analytics du back-office
-- - Table user_locations : dernière position GPS connue de chaque client
--   ayant activé la géolocalisation (mise à jour par
--   ProximityController::check()), utilisée pour la carte "Clients à
--   proximité".
-- - Paramètres GA4 (Property ID + compte de service) stockés dans la
--   table settings existante (Lot 10), pour être configurables depuis
--   /admin/parametres sans toucher au code ni au fichier .env.
-- À exécuter après schema.sql et les migrations précédentes.
-- =====================================================================
USE le_commerce;

CREATE TABLE IF NOT EXISTS user_locations (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL UNIQUE,
    latitude    DECIMAL(10,7) NOT NULL,
    longitude   DECIMAL(10,7) NOT NULL,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- La table `settings` est normalement créée par la migration Lot 10
-- (migration_lot10_settings.sql). On la crée ici aussi si besoin, pour que
-- ce script reste autonome quel que soit l'ordre d'exécution des migrations.
CREATE TABLE IF NOT EXISTS settings (
    `key`   VARCHAR(60) PRIMARY KEY,
    `value` TEXT NULL
) ENGINE=InnoDB;

INSERT INTO settings (`key`, `value`) VALUES
('ga4_property_id', ''),
('ga4_service_account_json', '')
ON DUPLICATE KEY UPDATE `key` = `key`;
