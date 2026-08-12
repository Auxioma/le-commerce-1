# Correspondance : sidebar admin actuelle ↔ maquette

Ce document fait le mapping entre les onglets de la sidebar actuelle (`app/Views/partials/admin-sidebar.php`) et ceux présents sur la maquette/image transmise, avant réécriture de la sidebar.

## Légende

- **✅ Route existante** : page déjà implémentée, onglet cliquable.
- **🚧 Pas de route dédiée** : pas de back-office correspondant aujourd'hui ; l'onglet pointera vers la page la plus proche ou sera marqué *Bientôt*.
- **📦 Groupe dépliant** : section contenant des sous-onglets.

---

## 1. Structure actuelle (sidebar `admin-sidebar.php`)

| Section actuelle | Label | Route |
|---|---|---|
| **Général** | Tableau de bord | `/admin` |
| | Mon établissement | `/admin/etablissement` |
| | Services du quotidien | `/admin/services` |
| | Photos du site | `/admin/images` |
| | Employés | `/admin/employes` |
| **Clients** | Clients inscrits | `/admin/clients` |
| | Portefeuille client | `/admin/portefeuilles` |
| | Messages | `/admin/messages` |
| | Réservations | `/admin/reservations` |
| **Fidélisation** | Offres & Avantages | `/admin/offres` |
| | Scanner une offre | `/admin/offres/scanner` |
| | Zonage & Proximité | `/admin/zonage` |
| | Sondages & Votes | `/admin/sondages` |
| | Loterie | `/admin/loterie` |
| | Avis Google | `/admin/avis-google` |
| **Pilotage** | Statistiques | `/admin/statistiques` |
| | Google Analytics | `/admin/google-analytics` |
| | Facturation | `/admin/facturation` |
| | Paramètres | `/admin/parametres` |

---

## 2. Structure maquette (image transmise)

| Groupe / Onglet maquette | Route cible | Source actuelle | Note |
|---|---|---|---|
| **Accueil** (icône maison) | `/admin` | Tableau de bord | ✅ Style rouge plein comme sur la maquette |
| **GESTION** | *section* | *nouvelle* | Regroupe les 5 univers métier |
| &nbsp;&nbsp;Bar | `/admin/services` | Services du quotidien | 🚧 Lien provisoire vers le catalogue services |
| &nbsp;&nbsp;Tabac | `/admin/services` | Services du quotidien | 🚧 Lien provisoire vers le catalogue services |
| &nbsp;&nbsp;Jeux & Services | `/admin/services` | Services du quotidien | 🚧 Lien provisoire vers le catalogue services |
| &nbsp;&nbsp;PMU | `/admin/services` | Services du quotidien | 🚧 Lien provisoire vers le catalogue services |
| &nbsp;&nbsp;NIRIO | `/admin/services` | Services du quotidien | 🚧 Lien provisoire vers le catalogue services |
| **Employés** | `/admin/employes` | Employés | ✅ |
| **Clients & Prestataires** 📦 | | | Groupe dépliant |
| &nbsp;&nbsp;Clients Inscrits | `/admin/clients` | Clients inscrits | ✅ |
| &nbsp;&nbsp;Portefeuille Client | `/admin/portefeuilles` | Portefeuille client | ✅ |
| **Réservations** | `/admin/reservations` | Réservations | ✅ |
| **Marketing** | `/admin/offres` | Offres & Avantages | 🚧 Lien provisoire vers les offres |
| **Contenu du site** | `/admin/images` | Photos du site | 🚧 Lien provisoire vers la gestion images |
| **Messages** (badge `5`) | `/admin/messages` | Messages | ✅ Affichage d'un badge de messages non lus |
| **Avis** | `/admin/avis-google` | Avis Google | ✅ Label raccourci *Avis* |
| **Fidélisations** 📦 | | | Groupe dépliant |
| &nbsp;&nbsp;Offres & Avantages | `/admin/offres` | Offres & Avantages | ✅ |
| &nbsp;&nbsp;Scanner une offre | `/admin/offres/scanner` | Scanner une offre | ✅ |
| &nbsp;&nbsp;Zonage & Proximité | `/admin/zonage` | Zonage & Proximité | ✅ |
| &nbsp;&nbsp;Loterie | `/admin/loterie` | Loterie | ✅ |
| **Sondages & Votes** | `/admin/sondages` | Sondages & Votes | ✅ Sorti du groupe Fidélisation |
| **Statistiques** | `/admin/statistiques` | Statistiques | ✅ |
| **Google Analytics** | `/admin/google-analytics` | Google Analytics | ✅ |
| **Paramètres** | `/admin/parametres` | Paramètres | ✅ |
| **Support** | — | *nouveau* | 🚧 Pas de route dédiée ; étiquette *Bientôt* |

---

## 3. Routes actuelles non visibles sur la maquette

| Route actuelle | Label actuel | Proposition |
|---|---|---|
| `/admin/etablissement` | Mon établissement | Non affichée dans la sidebar maquette. Page toujours accessible via URL, ou à rattacher ultérieurement sous *Paramètres* / *Contenu du site*. |
| `/admin/facturation` | Facturation | Non affichée dans la maquette. Garder accessible via URL ; possibilité d'ajouter un onglet *Facturation* sous *Pilotage* plus tard. |

> **Choix technique retenu** : pour rester fidèle à la maquette, la sidebar affichera **uniquement** les onglets de la maquette. Les routes `Mon établissement` et `Facturation` restent fonctionnelles si l'admin connaît l'URL.

---

## 4. Comportement visuel attendu

| Élément | Comportement |
|---|---|
| **Logo** | Conservé en haut de la sidebar (image `logo_site` ou fallback texte). |
| **Accueil** | Premier onglet, style plein rouge `#c8102e`, texte blanc, icône maison. |
| **GESTION** | Titre de section en petites majuscules grises. |
| **Groupes dépliants** | `Clients & Prestataires` et `Fidélisations` s'ouvrent/ferment en cliquant ; la section parente devient bleue si l'un de ses enfants est actif. |
| **Onglet actif** | Fond bleu `#2563eb`, texte blanc, arrondi `rounded-xl`. Exception : *Accueil* reste rouge. |
| **Hover** | Fond blanc très transparent (`bg-white/5`), texte blanc. |
| **Badge Messages** | Nombre de messages non lus (si le modèle le permet) ; sinon badge fixe ou absent. |
| **Footer** | Fiche établissement (image, nom, adresse, ville) + bouton *Voir le site*. |

---

## 5. Icônes à utiliser (style Lucide / ligne)

| Onglet | Icône (description) |
|---|---|
| Accueil | Maison pleine |
| Bar | Verre/cocktail |
| Tabac | Feuille de tabac / cigarette |
| Jeux & Services | Dés + puzzle |
| PMU | Cheval de courses |
| NIRIO | Logo/lettre N stylisée |
| Employés | Deux personnes |
| Clients Inscrits | Liste de contacts |
| Portefeuille Client | Portefeuille/carte |
| Réservations | Calendrier |
| Marketing | Megaphone |
| Contenu du site | Image + layout |
| Messages | Bulle de dialogue + badge |
| Avis | Étoile |
| Offres & Avantages | Ticket/cadeau |
| Scanner une offre | Scanner QR |
| Zonage & Proximité | Marqueur géo |
| Loterie | Boule de loto |
| Sondages & Votes | Barres de statistiques |
| Statistiques | Graphique en courbes |
| Google Analytics | Graphique en barres |
| Paramètres | Engrenage |
| Support | Casque d'aide |

---

## 6. Fichiers impactés

- `app/Views/partials/admin-sidebar.php` : réécriture complète de la structure et du rendu.
- `app/Views/layouts/admin.php` : pas de changement structurel, mais s'assurer que les classes du layout supportent la nouvelle largeur/scroll.
- `app/Views/partials/admin-topbar.php` : pas de changement, le bouton mobile reste identique.
- Fichiers CSS/JS éventuels : ajout d'une classe utilitaire pour l'état actif bleu si nécessaire.
