-- =====================================================================
-- Migration Lot 12 : réservations de table
-- À exécuter après schema.sql
-- =====================================================================
USE le_commerce;

CREATE TABLE IF NOT EXISTS reservations (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name              VARCHAR(120) NOT NULL,
    phone             VARCHAR(20) NOT NULL,
    email             VARCHAR(150) NULL,
    party_size        TINYINT UNSIGNED NOT NULL DEFAULT 2,
    reservation_date  DATE NOT NULL,
    reservation_time  TIME NOT NULL,
    note              VARCHAR(255) NULL,
    status            ENUM('en_attente','confirmee','annulee') NOT NULL DEFAULT 'en_attente',
    created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO reservations (name, phone, email, party_size, reservation_date, reservation_time, note, status) VALUES
('Antoine Rousseau', '0678912345', 'antoine.r@example.com', 4, CURDATE() + INTERVAL 2 DAY, '19:30:00', 'Anniversaire, si possible une table près de la fenêtre', 'confirmee'),
('Léa Fontaine', '0656789012', NULL, 2, CURDATE() + INTERVAL 1 DAY, '20:00:00', NULL, 'en_attente');
