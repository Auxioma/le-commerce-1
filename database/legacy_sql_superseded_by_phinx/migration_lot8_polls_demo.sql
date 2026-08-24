-- =====================================================================
-- Migration Lot 8 : données de démonstration (sondages & votes)
-- À exécuter après schema.sql, migration_lot2_auth.sql
-- =====================================================================
USE le_commerce;

INSERT INTO polls (question, description, ends_at, status, reward_type, reward_value) VALUES
('Quelle bière souhaitez-vous en pression ?', 'Choisissez votre bière préférée que vous aimeriez voir prochainement en pression.', DATE_ADD(CURDATE(), INTERVAL 15 DAY), 'actif', 'points', 10),
('Quel match voulez-vous voir ?', 'Votez pour les matchs que vous souhaitez que nous diffusions au bar.', DATE_ADD(CURDATE(), INTERVAL 10 DAY), 'actif', 'credit', 0.50),
('Quelle ambiance musicale préférez-vous ?', 'Aidez-nous à choisir l''ambiance musicale de nos prochaines soirées.', DATE_ADD(CURDATE(), INTERVAL 5 DAY), 'actif', 'tirage_sort', NULL),
('Quelle planche aimeriez-vous découvrir ?', 'Quel type de planche aimeriez-vous voir dans notre carte ?', DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'termine', 'points', 5);

SET @poll1 = (SELECT id FROM polls WHERE question LIKE 'Quelle bière%' LIMIT 1);
SET @poll2 = (SELECT id FROM polls WHERE question LIKE 'Quel match%' LIMIT 1);
SET @poll3 = (SELECT id FROM polls WHERE question LIKE 'Quelle ambiance%' LIMIT 1);
SET @poll4 = (SELECT id FROM polls WHERE question LIKE 'Quelle planche%' LIMIT 1);

INSERT INTO poll_options (poll_id, label, votes_count) VALUES
(@poll1, 'Leffe', 12),
(@poll1, 'Paix Dieu', 9),
(@poll1, 'Triple Karmeliet', 5),
(@poll1, 'Chouffe', 3),

(@poll2, 'PSG - OM', 8),
(@poll2, 'Real - Barça', 6),
(@poll2, 'Finale Champions League', 4),

(@poll3, 'Ambiance lounge', 3),
(@poll3, 'Rock/Pop', 5),
(@poll3, 'Musique française', 2),

(@poll4, 'Planche fromage', 14),
(@poll4, 'Planche charcuterie', 18),
(@poll4, 'Planche mixte', 22);

-- Quelques votes existants (pour peupler le taux de participation)
INSERT INTO poll_votes (poll_id, option_id, user_id) VALUES
(@poll1, (SELECT id FROM poll_options WHERE poll_id = @poll1 AND label = 'Leffe'), 3),
(@poll2, (SELECT id FROM poll_options WHERE poll_id = @poll2 AND label = 'PSG - OM'), 4);
