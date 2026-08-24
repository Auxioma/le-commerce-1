-- =====================================================================
-- Migration Lot 14 : suppression douce (soft delete)
-- À exécuter après schema.sql (et après les migrations lot11/12/13 qui
-- créent employees / reservations / lotteries)
-- =====================================================================
USE le_commerce;

ALTER TABLE users        ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL AFTER updated_at;
ALTER TABLE employees     ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL AFTER updated_at;
ALTER TABLE lotteries     ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL AFTER created_at;
ALTER TABLE reservations  ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL AFTER updated_at;
ALTER TABLE google_reviews ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL AFTER created_at;
