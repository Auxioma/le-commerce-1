-- =====================================================================
-- Migration Lot 13 : loterie (participation par QR, tirage au sort admin)
-- À exécuter après schema.sql
-- =====================================================================
USE le_commerce;

CREATE TABLE IF NOT EXISTS lotteries (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title          VARCHAR(150) NOT NULL,
    description    VARCHAR(255) NULL,
    prize          VARCHAR(150) NOT NULL,
    ends_at        DATE NOT NULL,
    status         ENUM('brouillon','active','terminee') NOT NULL DEFAULT 'brouillon',
    winner_user_id INT UNSIGNED NULL,
    drawn_at       DATETIME NULL,
    created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (winner_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS lottery_entries (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lottery_id  INT UNSIGNED NOT NULL,
    user_id     INT UNSIGNED NOT NULL,
    code        VARCHAR(30) NOT NULL UNIQUE,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_lottery_user (lottery_id, user_id),
    FOREIGN KEY (lottery_id) REFERENCES lotteries(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO lotteries (title, description, prize, ends_at, status) VALUES
('Tirage de rentrée', 'Participez pour tenter de remporter un panier gourmand.', 'Panier gourmand (valeur 40€)', CURDATE() + INTERVAL 14 DAY, 'active');
