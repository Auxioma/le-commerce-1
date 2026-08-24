-- =====================================================================
-- Migration Lot 7 : données de démonstration (zonage & proximité)
-- À exécuter après schema.sql, migration_lot6_offers_demo.sql
-- =====================================================================
USE le_commerce;

SET @offer_cafe = (SELECT id FROM offers WHERE title = 'Café offert' LIMIT 1);
SET @offer_boisson = (SELECT id FROM offers WHERE title = 'Boisson offerte' LIMIT 1);

INSERT INTO proximity_campaigns (name, radius_m, start_time, end_time, days, target_segment, offer_id, message, status, sent_count, used_count) VALUES
('Café du matin', 500, '10:00:00', '11:00:00', 'lun,mar,mer,jeu,ven', 'tous', @offer_cafe,
 '👋 Bonjour ! Vous n''êtes pas loin du Commerce. Nous vous offrons un café entre 10h00 et 11h00. Présentez ce QR code en caisse !',
 'active', 124, 32),
('Happy Hour Leffe', 1000, '17:00:00', '19:00:00', 'lun,mar,mer,jeu,ven,sam,dim', 'tous', NULL,
 '🍺 Happy Hour au Commerce ! La Leffe à 5€ jusqu''à 19h. Venez en profiter !',
 'active', 215, 48),
('Soir de match', 2000, '19:00:00', '23:00:00', 'ven,sam,dim', 'tous', @offer_boisson,
 '⚽ Soir de match au Commerce ! Une boisson offerte pour l''ambiance.',
 'active', 156, 28);
