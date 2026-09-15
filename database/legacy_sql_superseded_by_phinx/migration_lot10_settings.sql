-- =====================================================================
-- Migration Lot 10 : table des paramètres modifiables (clé/valeur)
-- À exécuter après schema.sql
-- =====================================================================
USE le_commerce;

CREATE TABLE IF NOT EXISTS settings (
    `key`   VARCHAR(60) PRIMARY KEY,
    `value` TEXT NULL
) ENGINE=InnoDB;

INSERT INTO settings (`key`, `value`) VALUES
('shop_name', 'Le Commerce'),
('shop_address', '3 Rue du Maréchal Leclerc'),
('shop_zipcode', '76440'),
('shop_city', 'Forges-les-Eaux'),
('shop_phone', '02 35 90 50 16'),
('shop_email', 'lecommercetabac@gmail.com'),
('hours_lun_sam', '07h - 19h30'),
('hours_dim', '08h - 18h30'),
('social_facebook', 'https://facebook.com'),
('social_instagram', 'https://instagram.com'),
('latitude', '49.6136'),
('longitude', '1.5399')
ON DUPLICATE KEY UPDATE `key` = `key`;
