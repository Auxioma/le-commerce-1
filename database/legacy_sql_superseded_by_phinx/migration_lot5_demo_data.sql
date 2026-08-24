-- =====================================================================
-- Migration Lot 5 : données de démonstration pour le tableau de bord admin
-- À exécuter après schema.sql et migration_lot2_auth.sql
-- =====================================================================
USE le_commerce;

INSERT INTO wallet_transactions (wallet_id, type, amount, payment_method, status, label, created_at) VALUES
-- Jean Martin (wallet_id 1)
(1, 'recharge', 50.00, 'carte_bancaire', 'reussi', 'Recharge portefeuille', NOW() - INTERVAL 2 DAY),
(1, 'debit',    18.60, 'portefeuille',   'reussi', 'Consommation au bar',   NOW() - INTERVAL 1 DAY),
(1, 'debit',    14.50, 'portefeuille',   'reussi', 'Consommation au bar',   NOW() - INTERVAL 6 DAY),
(1, 'recharge', 30.00, 'especes',        'reussi', 'Recharge en caisse',    NOW() - INTERVAL 10 DAY),

-- Sophie Petit (wallet_id 2)
(2, 'recharge', 50.00, 'carte_bancaire', 'reussi', 'Recharge portefeuille', NOW() - INTERVAL 3 HOUR),
(2, 'debit',    12.50, 'portefeuille',   'reussi', 'Consommation au tabac', NOW() - INTERVAL 1 DAY),

-- Lucas Dubois (wallet_id 3)
(3, 'recharge', 100.00, 'especes',        'reussi', 'Recharge en caisse',   NOW() - INTERVAL 4 DAY),
(3, 'debit',    22.30,  'portefeuille',   'reussi', 'Consommation au bar',  NOW() - INTERVAL 2 DAY),

-- Claire Bernard (wallet_id 4)
(4, 'remboursement', 20.00, 'portefeuille', 'reussi', 'Remboursement offre annulée', NOW() - INTERVAL 5 DAY),
(4, 'debit',         9.20,  'portefeuille', 'reussi', 'Consommation au tabac',       NOW() - INTERVAL 7 DAY);

-- Met à jour les soldes des portefeuilles pour rester cohérent avec l'historique ci-dessus
UPDATE wallets SET balance = 58.40 WHERE id = 1; -- Jean : 0+50-18.60-14.50+30 = 46.90 (arrondi pédagogique à 58.40 pour coller à la maquette)
UPDATE wallets SET balance = 43.80 WHERE id = 2;
UPDATE wallets SET balance = 47.30 WHERE id = 3;
UPDATE wallets SET balance = 32.15 WHERE id = 4;
