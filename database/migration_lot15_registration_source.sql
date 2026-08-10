-- =====================================================================
-- Migration Lot 15 : source d'inscription (outil)
-- Ajoute la colonne permettant de savoir par quel service du commerce
-- (Bar, Tabac, Jeux & Services, PMU, NIRIO) un client s'est inscrit,
-- utilisée par le graphique "Répartition des inscriptions par outil"
-- du tableau de bord admin.
-- À exécuter après schema.sql et les migrations précédentes.
-- =====================================================================
USE le_commerce;

ALTER TABLE users
    ADD COLUMN registration_source ENUM('bar','tabac','jeux_services','pmu','nirio') NOT NULL DEFAULT 'bar'
    AFTER geolocation_opt_in;
