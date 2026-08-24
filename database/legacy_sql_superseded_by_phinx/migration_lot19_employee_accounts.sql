-- =====================================================================
-- Lot 19 - Comptes de connexion pour les employés
-- Permet de lier une fiche `employees` à un compte `users` (rôle 'employe')
-- pour donner un accès de connexion au back-office (mêmes écrans que
-- l'admin pour l'instant : pas encore de permissions par onglet).
-- =====================================================================
USE le_commerce;

ALTER TABLE users
    MODIFY COLUMN role ENUM('client', 'admin', 'employe') NOT NULL DEFAULT 'client';

ALTER TABLE employees
    ADD COLUMN user_id INT UNSIGNED NULL AFTER id,
    ADD CONSTRAINT fk_employees_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;
