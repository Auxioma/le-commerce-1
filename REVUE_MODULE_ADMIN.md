# Revue du module Admin — Onglets de la sidebar

Ce document liste **chaque entrée de la sidebar admin** (`app/Views/partials/admin-sidebar.php`), ce qu'elle fait réellement (contrôleur/route/vue), ce qui manque, et mon avis. Sert de base de travail pour la revue onglet par onglet.

> **Refonte août 2026** : la sidebar a été redessinée d’après la maquette, le logo conservé. Ce document est désormais réorganisé pour suivre la structure actuelle de la sidebar (voir `SIDEBAR_MAQUETTE_MAPPING.md`).

Légende statut : ✅ Complet · 🟡 Fonctionnel mais limité · 🔴 Lecture seule / faible valeur · ⚠️ Bug ou incohérence connue

---

## 1. Accueil

### Tableau de bord (/admin)
- **Note** : aucune documentation existante.

---

## 2. Gestion

> Les 5 onglets **Bar**, **Tabac**, **Jeux & Services**, **PMU** et **NIRIO** pointent tous vers `/admin/services` en attendant une scission par catégorie.

### Bar / Tabac / Jeux & Services / PMU / NIRIO (/admin/services)
- **Note** : aucune documentation existante.

---

## 3. Employés

### Inconnu (/admin/employes)
- **Note** : aucune documentation existante.

---

## 4. Clients & Prestataires

### Clients Inscrits (/admin/clients)
- **Note** : aucune documentation existante.

### Portefeuille Client (/admin/portefeuilles)
- **Note** : aucune documentation existante.

---

## 5. Réservations

### Inconnu (/admin/reservations)
- **Note** : aucune documentation existante.

---

## 6. Marketing

### Marketing (/admin/offres)
- **Note** : ce lien ouvre la même page que **Fidélisations > Offres & Avantages**. Voir l’entrée correspondante pour la revue détaillée.

---

## 7. Contenu du site

### Photos du site (/admin/images)
- **Note** : aucune documentation existante.

---

## 8. Messages

### Inconnu (/admin/messages)
- **Note** : aucune documentation existante.

---

## 9. Avis

### Avis (/admin/avis-google)
- **Note** : aucune documentation existante.

---

## 10. Fidélisations

### Offres & Avantages (/admin/offres)
- **Note** : aucune documentation existante.

### Scanner une offre (/admin/offres/scanner)
- **Note** : aucune documentation existante.

### Zonage & Proximité (/admin/zonage)
- **Note** : aucune documentation existante.

### Loterie (/admin/loterie)
- **Note** : aucune documentation existante.

---

## 11. Sondages & Votes

### Inconnu (/admin/sondages)
- **Note** : aucune documentation existante.

---

## 12. Pilotage

### Inconnu (/admin/statistiques)
- **Note** : aucune documentation existante.

### Google Analytics (/admin/google-analytics)
- **Note** : aucune documentation existante.

---

## 13. Paramètres

### Inconnu (/admin/parametres)
- **Note** : aucune documentation existante.

---

## 14. Support

### Support
- **Contrôleur** : Aucun
- **Fait** : Placeholder dans la sidebar, affiché grisé. Pas de vue associée.
- **Statut** : ⚠️ Non implémenté.
- **Manque** : route, contrôleur, vue, fonctionnalité.
- **Avis** : Prévu pour un futur module d’assistance / FAQ / contact support.

---

## A. Annexes — modules non affichés dans la sidebar actuelle

### Mon établissement (/admin/etablissement)
- **Note** : aucune documentation existante.

### Facturation (/admin/facturation)
- **Note** : aucune documentation existante.

---

## Synthèse — constats transversaux

1. **Pattern de CRUD incomplet récurrent** : Offres, Zonage/Proximité et Sondages n'ont **ni update ni destroy** — seulement création + bascule de statut. Loterie est la seule exception avec un `destroy`. À harmoniser si l'utilisateur veut pouvoir corriger une erreur de saisie sans devoir désactiver et recréer.
2. **2 pages "vitrines" en lecture seule servies par le même contrôleur générique** (`AdminPlaceholderController`) : Services du quotidien, Portefeuille client. Elles dupliquent des données déjà visibles ailleurs (Offres, Clients) sans action propre — à fusionner ou enrichir. *(Mon établissement a été sorti de ce lot et refondu en page éditable dédiée.)*
3. **Toute communication sortante (WhatsApp/SMS/e-mail) est simulée** (écriture en base, pas d'appel API réel) — cohérent avec la contrainte actuelle "100% PHP natif", mais à garder en tête pour la mise en production réelle.
4. **"Avis Google" n'est pas connecté à Google** — nom potentiellement trompeur pour l'utilisateur final.
5. **Aucune tâche planifiée (cron)** : les messages programmés (Lot 17) ne partent jamais tout seuls à l'heure prévue.

---

*Document généré pour servir de support à la revue onglet par onglet demandée par l'utilisateur. Prochaine étape : passer en revue chaque module un par un pour prioriser les corrections/évolutions.*

---

## Refonte de la sidebar admin — maquette

### Vue d'ensemble
La sidebar a été refondue dans `app/Views/partials/admin-sidebar.php` avec un regroupement par groupes fonctionnels plus proches de la maquette. Le logo du commerce est conservé via `siteImage('logo_site')`.

### Fichiers concernés
- Partial : `app/Views/partials/admin-sidebar.php`
- Icônes SVG : `app/Views/partials/admin-sidebar-icons.php`
- Mapping actuel ↔ maquette : `SIDEBAR_MAQUETTE_MAPPING.md`
- JS mobile existant : `public/assets/js/app.js` (`initAdminMobileSidebar`)

### Nouvelle structure du menu
- **Accueil** (`/admin`) — bouton rouge actif permanent, couleur brand.
- **Gestion** :
  - Bar (`/admin/services`)
  - Tabac (`/admin/services`)
  - Jeux & Services (`/admin/services`)
  - PMU (`/admin/services`)
  - NIRIO (`/admin/services`)
- **Employés** (`/admin/employes`)
- **Clients & Prestataires** (groupe déroulant) :
  - Clients Inscrits (`/admin/clients`)
  - Portefeuille Client (`/admin/portefeuilles`)
- **Réservations** (`/admin/reservations`)
- **Marketing** (`/admin/offres`)
- **Contenu du site** (`/admin/images`)
- **Messages** (`/admin/messages`) — badge du nombre de messages non lus via `ContactMessage::countUnread()`.
- **Avis** (`/admin/avis-google`)
- **Fidélisations** (groupe déroulant) :
  - Offres & Avantages (`/admin/offres`)
  - Scanner une offre (`/admin/offres/scanner`)
  - Zonage & Proximité (`/admin/zonage`)
  - Loterie (`/admin/loterie`)
- **Sondages & Votes** (`/admin/sondages`)
- **Statistiques** (`/admin/statistiques`)
- **Google Analytics** (`/admin/google-analytics`)
- **Paramètres** (`/admin/parametres`)
- **Support** — placeholder (non implémenté, affiché grisé)

### États visuels
- Élément actif : fond bleu (`#2563eb`) + texte blanc.
- Hover : fond blanc à 5 %, texte blanc.
- Groupes déroulants : `<details>`, chevron orienté vers le bas ouvert.
- Mobile : drawer coulissant avec `admin-sidebar-backdrop` conservé.

### Constats liés à la sidebar
1. Les 5 items « Gestion » (Bar/Tabac/Jeux & Services/PMU/NIRIO) pointent tous vers `/admin/services` en attendant une éventuelle scission par catégorie dans `Service` ou des contrôleurs dédiés.
2. Le menu « Marketing » pointe sur `/admin/offres` : le libellé a changé, mais la fonction reste la gestion des offres.
3. « Avis » reste `/admin/avis-google` ; le nom affiché est désormais plus neutre que « Avis Google ».
4. « Support » est un placeholder sans route.
5. Le drawer mobile et le toggle `admin-mobile-menu-btn` n'ont pas changé, donc compatibles.
