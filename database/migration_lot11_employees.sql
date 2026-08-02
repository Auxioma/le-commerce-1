-- =====================================================================
-- Migration Lot 11 : gestion des employés
-- À exécuter après schema.sql
-- =====================================================================
USE le_commerce;

CREATE TABLE IF NOT EXISTS employees (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name  VARCHAR(80) NOT NULL,
    last_name   VARCHAR(80) NOT NULL,
    email       VARCHAR(150) NULL,
    phone       VARCHAR(20) NULL,
    role        VARCHAR(80) NOT NULL,
    status      ENUM('actif','inactif') NOT NULL DEFAULT 'actif',
    hired_at    DATE NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO employees (first_name, last_name, email, phone, role, status, hired_at) VALUES
('Marie', 'Lefevre', 'marie.lefevre@lecommerce.fr', '0612345601', 'Responsable de salle', 'actif', '2023-03-01'),
('Karim', 'Benali', 'karim.benali@lecommerce.fr', '0612345602', 'Caissier', 'actif', '2024-01-15'),
('Julie', 'Moreau', NULL, '0612345603', 'Serveuse', 'inactif', '2022-09-10');
