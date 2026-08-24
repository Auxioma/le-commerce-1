-- =====================================================================
-- Migration Lot 6 : données de démonstration (offres & avantages)
-- À exécuter après schema.sql, migration_lot2_auth.sql, migration_lot5_demo_data.sql
-- =====================================================================
USE le_commerce;

INSERT INTO offers (title, description, type, value, target_segment, valid_until, status) VALUES
('Café offert', 'Un café offert pour bien commencer la journée !', 'gratuite', 1.50, 'tous', DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'active'),
('2 viennoiseries + 1 offerte', 'Pour accompagner votre café du matin.', 'x_plus_1', 2.00, 'tous', DATE_ADD(CURDATE(), INTERVAL 20 DAY), 'active'),
('-20% sur les sandwichs', 'Profitez de 20% de réduction sur tous les sandwichs.', 'reduction_pourcentage', 20, 'fideles', DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'active'),
('Boisson offerte', 'Une boisson offerte dès 10€ d''achat.', 'montant_minimum', 3.00, 'tous', DATE_ADD(CURDATE(), INTERVAL 25 DAY), 'active'),
('Happy Hour VIP', 'Offre spéciale clients fidèles, brouillon en préparation.', 'personnalisee', 5.00, 'fideles', DATE_ADD(CURDATE(), INTERVAL 10 DAY), 'brouillon');

-- Un code déjà généré et utilisé pour Jean Martin (démo de l'historique)
INSERT INTO offer_redemptions (offer_id, user_id, code, channel, status, used_at, created_at) VALUES
(1, 1, 'DEMOCAFE01', 'whatsapp', 'utilisee', NOW() - INTERVAL 3 DAY, NOW() - INTERVAL 5 DAY);

-- Un code encore valide pour Sophie Petit (démo du scan)
INSERT INTO offer_redemptions (offer_id, user_id, code, channel, status, created_at) VALUES
(4, 2, 'DEMOBOISSON', 'qr_caisse', 'valide', NOW() - INTERVAL 1 DAY);
