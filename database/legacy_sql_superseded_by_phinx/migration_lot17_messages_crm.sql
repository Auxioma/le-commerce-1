-- =====================================================================
-- Lot 17 - CRM Messagerie : canal SMS, messages programmés,
-- étiquettes clients et notes internes (boîte de réception unifiée)
-- =====================================================================

-- ---------------------------------------------------------------------
-- Messages SMS (miroir de whatsapp_messages, canal SMS)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS sms_messages (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    direction   ENUM('sortant','entrant') NOT NULL DEFAULT 'sortant',
    content     TEXT NOT NULL,
    sent_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Messages programmés (email / sms / whatsapp), envoyés plus tard
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS scheduled_messages (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id       INT UNSIGNED NOT NULL,
    channel       ENUM('whatsapp','sms','email') NOT NULL DEFAULT 'whatsapp',
    content       TEXT NOT NULL,
    scheduled_at  DATETIME NOT NULL,
    status        ENUM('programme','envoye','annule') NOT NULL DEFAULT 'programme',
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Étiquettes libres posées sur un client (ex: "Client fidèle",
-- "Aime la bière", "Participe à la loterie"), visibles dans la
-- messagerie et sur la fiche client.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS client_labels (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    label       VARCHAR(40) NOT NULL,
    color       VARCHAR(20) NOT NULL DEFAULT 'gray',
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_client_label (user_id, label),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Notes internes admin sur un client (visibles depuis la messagerie)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS client_notes (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    content     TEXT NOT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Données de démonstration
-- ---------------------------------------------------------------------
INSERT INTO sms_messages (user_id, direction, content, sent_at) VALUES
(1, 'sortant', 'Bonjour Jean, votre solde portefeuille est de 58,40€. Merci de votre fidélité !', NOW() - INTERVAL 2 DAY),
(2, 'entrant', 'Bonjour, avez-vous encore des tickets pour le tirage FDJ de ce soir ?', NOW() - INTERVAL 1 DAY),
(2, 'sortant', 'Bonjour Sophie, oui il reste des tickets, à très vite !', NOW() - INTERVAL 1 DAY);

INSERT INTO client_labels (user_id, label, color) VALUES
(1, 'Client fidèle', 'amber'),
(1, 'Aime la bière', 'blue'),
(3, 'Client fidèle', 'amber'),
(4, 'Participe à la loterie', 'purple');

INSERT INTO scheduled_messages (user_id, channel, content, scheduled_at, status) VALUES
(1, 'whatsapp', 'Rappel : votre offre Happy Hour expire ce soir à 20h !', NOW() + INTERVAL 1 DAY, 'programme'),
(2, 'sms', 'N''oubliez pas de valider votre inscription à la loterie avant vendredi.', NOW() + INTERVAL 2 DAY, 'programme');
