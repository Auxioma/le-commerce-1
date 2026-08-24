-- =====================================================================
-- Migration Lot 2 : Authentification
-- À exécuter après database/schema.sql
-- =====================================================================
USE le_commerce;

-- Journal des connexions (audit léger, utile en admin plus tard)
CREATE TABLE IF NOT EXISTS login_logs (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    ip_address  VARCHAR(45) NULL,
    user_agent  VARCHAR(255) NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Mot de passe de démonstration pour le compte admin existant : "admin123"
UPDATE users
SET password_hash = '$2y$10$G/o10NdBzAm8fo3BmVaij.57vmcknkyD/r4fY9eUs9OOZkMGk6p1e'
WHERE role = 'admin' AND password_hash IS NULL;

-- Mot de passe de démonstration pour les clients de démo : "client123"
UPDATE users
SET password_hash = '$2y$10$V8.3Qjjplb4ptylpo1E.v.fqlNA1pGuYT8XOBcsc9qgdt54z.rBeW'
WHERE role = 'client' AND password_hash IS NULL;
