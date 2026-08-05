-- =====================================================================
-- Le Commerce - Schéma de base de données
-- Bar / Tabac / PMU / FDJ / Presse - Forges-les-Eaux
-- =====================================================================

CREATE DATABASE IF NOT EXISTS le_commerce CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE le_commerce;

-- ---------------------------------------------------------------------
-- Utilisateurs (clients + administrateurs)
-- ---------------------------------------------------------------------
CREATE TABLE users (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name      VARCHAR(80)  NOT NULL,
    last_name       VARCHAR(80)  NOT NULL,
    phone_whatsapp  VARCHAR(20)  NOT NULL UNIQUE,
    email           VARCHAR(150) NULL,
    password_hash   VARCHAR(255) NULL,
    role            ENUM('client','admin') NOT NULL DEFAULT 'client',
    segment         ENUM('nouveau','fidele','occasionnel') NOT NULL DEFAULT 'nouveau',
    status          ENUM('actif','inactif') NOT NULL DEFAULT 'actif',
    loyalty_points  INT UNSIGNED NOT NULL DEFAULT 0,
    referral_code   VARCHAR(20)  NULL UNIQUE,
    referred_by     INT UNSIGNED NULL,
    geolocation_opt_in TINYINT(1) NOT NULL DEFAULT 0,
    last_activity_at DATETIME NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      DATETIME NULL DEFAULT NULL,
    FOREIGN KEY (referred_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Portefeuilles clients (wallet)
-- ---------------------------------------------------------------------
CREATE TABLE wallets (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL UNIQUE,
    balance     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    qr_code     VARCHAR(64) NOT NULL UNIQUE,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Transactions du portefeuille (recharges / débits / remboursements)
-- ---------------------------------------------------------------------
CREATE TABLE wallet_transactions (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    wallet_id     INT UNSIGNED NOT NULL,
    type          ENUM('recharge','debit','remboursement') NOT NULL,
    amount        DECIMAL(10,2) NOT NULL,
    payment_method ENUM('carte_bancaire','especes','apple_pay','google_pay','portefeuille') NOT NULL,
    status        ENUM('reussi','echoue','en_attente') NOT NULL DEFAULT 'reussi',
    label         VARCHAR(120) NULL,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (wallet_id) REFERENCES wallets(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Offres & avantages
-- ---------------------------------------------------------------------
CREATE TABLE offers (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(150) NOT NULL,
    description VARCHAR(255) NULL,
    type        ENUM('gratuite','reduction_pourcentage','x_plus_1','montant_minimum','personnalisee') NOT NULL,
    value       DECIMAL(10,2) NULL COMMENT 'ex: 20 pour -20%',
    target_segment ENUM('tous','fideles','nouveaux','occasionnels') NOT NULL DEFAULT 'tous',
    valid_until DATE NOT NULL,
    status      ENUM('active','expiree','brouillon') NOT NULL DEFAULT 'active',
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Codes / QR uniques générés pour un client sur une offre donnée
CREATE TABLE offer_redemptions (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    offer_id    INT UNSIGNED NOT NULL,
    user_id     INT UNSIGNED NOT NULL,
    code        VARCHAR(30) NOT NULL UNIQUE,
    channel     ENUM('whatsapp','qr_caisse','sms','email') NOT NULL DEFAULT 'qr_caisse',
    status      ENUM('valide','utilisee','expiree') NOT NULL DEFAULT 'valide',
    used_at     DATETIME NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (offer_id) REFERENCES offers(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Campagnes de zonage & proximité (géolocalisation)
-- ---------------------------------------------------------------------
CREATE TABLE proximity_campaigns (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(120) NOT NULL,
    radius_m    INT UNSIGNED NOT NULL DEFAULT 500,
    start_time  TIME NOT NULL,
    end_time    TIME NOT NULL,
    days        VARCHAR(40) NOT NULL COMMENT 'ex: lun,mar,mer,jeu,ven',
    target_segment ENUM('tous','fideles','nouveaux','occasionnels') NOT NULL DEFAULT 'tous',
    offer_id    INT UNSIGNED NULL,
    message     VARCHAR(160) NOT NULL,
    status      ENUM('active','en_pause','terminee') NOT NULL DEFAULT 'active',
    sent_count  INT UNSIGNED NOT NULL DEFAULT 0,
    used_count  INT UNSIGNED NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (offer_id) REFERENCES offers(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Sondages & votes
-- ---------------------------------------------------------------------
CREATE TABLE polls (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question    VARCHAR(180) NOT NULL,
    description VARCHAR(255) NULL,
    image       VARCHAR(255) NULL,
    ends_at     DATE NOT NULL,
    status      ENUM('actif','programme','termine') NOT NULL DEFAULT 'actif',
    reward_type ENUM('points','credit','tirage_sort','aucune') NOT NULL DEFAULT 'points',
    reward_value DECIMAL(10,2) NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE poll_options (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    poll_id     INT UNSIGNED NOT NULL,
    label       VARCHAR(120) NOT NULL,
    votes_count INT UNSIGNED NOT NULL DEFAULT 0,
    FOREIGN KEY (poll_id) REFERENCES polls(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE poll_votes (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    poll_id     INT UNSIGNED NOT NULL,
    option_id   INT UNSIGNED NOT NULL,
    user_id     INT UNSIGNED NOT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_vote (poll_id, user_id),
    FOREIGN KEY (poll_id)   REFERENCES polls(id)        ON DELETE CASCADE,
    FOREIGN KEY (option_id) REFERENCES poll_options(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)   REFERENCES users(id)         ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Messages WhatsApp (journal d'envoi)
-- ---------------------------------------------------------------------
CREATE TABLE whatsapp_messages (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    direction   ENUM('sortant','entrant') NOT NULL DEFAULT 'sortant',
    content     TEXT NOT NULL,
    sent_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Avis Google (cache local, synchronisé depuis Google Places API)
-- ---------------------------------------------------------------------
CREATE TABLE google_reviews (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    author_name  VARCHAR(120) NOT NULL,
    rating       TINYINT UNSIGNED NOT NULL,
    comment      TEXT NULL,
    published_at DATETIME NOT NULL,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at   DATETIME NULL DEFAULT NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Bons plans (Happy Hour, promos du moment affichées en Accueil)
-- ---------------------------------------------------------------------
CREATE TABLE deals (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(120) NOT NULL,
    subtitle    VARCHAR(160) NULL,
    starts_at   TIME NULL,
    ends_at     TIME NULL,
    active      TINYINT(1) NOT NULL DEFAULT 1,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Boissons (bières à la carte affichées en Accueil / page Le Bar)
-- ---------------------------------------------------------------------
CREATE TABLE drinks (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(80) NOT NULL,
    category    ENUM('biere_blonde','biere_brune','biere_ambree','autre') NOT NULL DEFAULT 'biere_blonde',
    degree      DECIMAL(3,1) NULL,
    image       VARCHAR(255) NULL,
    price       DECIMAL(5,2) NULL,
    display_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Messages du formulaire de contact
-- ---------------------------------------------------------------------
CREATE TABLE contact_messages (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(120) NOT NULL,
    email       VARCHAR(150) NOT NULL,
    subject     VARCHAR(160) NULL,
    message     TEXT NOT NULL,
    is_read     TINYINT(1) NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Réservations de table
-- ---------------------------------------------------------------------
CREATE TABLE reservations (
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
    updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at        DATETIME NULL DEFAULT NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Loterie
-- ---------------------------------------------------------------------
CREATE TABLE lotteries (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title          VARCHAR(150) NOT NULL,
    description    VARCHAR(255) NULL,
    prize          VARCHAR(150) NOT NULL,
    ends_at        DATE NOT NULL,
    status         ENUM('brouillon','active','terminee') NOT NULL DEFAULT 'brouillon',
    winner_user_id INT UNSIGNED NULL,
    drawn_at       DATETIME NULL,
    created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at     DATETIME NULL DEFAULT NULL,
    FOREIGN KEY (winner_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE lottery_entries (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lottery_id  INT UNSIGNED NOT NULL,
    user_id     INT UNSIGNED NOT NULL,
    code        VARCHAR(30) NOT NULL UNIQUE,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_lottery_user (lottery_id, user_id),
    FOREIGN KEY (lottery_id) REFERENCES lotteries(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Employés
-- ---------------------------------------------------------------------
CREATE TABLE employees (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name  VARCHAR(80) NOT NULL,
    last_name   VARCHAR(80) NOT NULL,
    email       VARCHAR(150) NULL,
    phone       VARCHAR(20) NULL,
    role        VARCHAR(80) NOT NULL,
    status      ENUM('actif','inactif') NOT NULL DEFAULT 'actif',
    hired_at    DATE NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at  DATETIME NULL DEFAULT NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Données de démonstration
-- ---------------------------------------------------------------------
INSERT INTO users (first_name, last_name, phone_whatsapp, email, role, segment, status, loyalty_points, referral_code, geolocation_opt_in) VALUES
('Jean', 'Martin', '0612345678', 'jean.martin@example.com', 'client', 'fidele', 'actif', 120, 'JEAN2024', 1),
('Sophie', 'Petit', '0723456789', 'sophie.petit@example.com', 'client', 'nouveau', 'actif', 20, 'SOPH2024', 1),
('Lucas', 'Dubois', '0645678901', 'lucas.dubois@example.com', 'client', 'fidele', 'actif', 95, 'LUCA2024', 0),
('Claire', 'Bernard', '0789012345', 'claire.bernard@example.com', 'client', 'nouveau', 'actif', 15, 'CLAI2024', 1),
('Admin', 'Le Commerce', '0235905016', 'lecommercetabac@gmail.com', 'admin', 'fidele', 'actif', 0, NULL, 0);

INSERT INTO wallets (user_id, balance, qr_code) VALUES
(1, 58.40, 'QR-JEAN-MARTIN-001'),
(2, 43.80, 'QR-SOPHIE-PETIT-002'),
(3, 47.30, 'QR-LUCAS-DUBOIS-003'),
(4, 32.15, 'QR-CLAIRE-BERNARD-004');

INSERT INTO drinks (name, category, degree, display_order) VALUES
('Leffe Blonde', 'biere_blonde', 6.6, 1),
('Chimay Bleue', 'biere_brune', 9.0, 2),
('La Paix Dieu', 'biere_ambree', 10.0, 3),
('Rince Cochon', 'biere_ambree', 8.5, 4),
('Corbeau 9°', 'biere_brune', 9.0, 5),
('Chouffe Rouge', 'biere_ambree', 8.0, 6),
('Liefumme Pêche', 'biere_blonde', 5.2, 7),
('Liefmans Pêche', 'biere_brune', 5.0, 8),
('Grimbergen Blonde', 'biere_blonde', 6.7, 9),
('Brooklyn Lager', 'biere_ambree', 5.2, 10);

INSERT INTO deals (title, subtitle, starts_at, ends_at) VALUES
('Happy Hour', 'La pinte de Leffe à 5,00 €', '17:00:00', '19:00:00');

INSERT INTO google_reviews (author_name, rating, comment, published_at) VALUES
('Marie L.', 5, 'Excellent accueil, toujours un plaisir de venir prendre un café.', NOW()),
('Thomas B.', 5, 'Le meilleur bar-tabac du coin, service au top.', NOW());
