# Revue du module Admin — Onglets de la sidebar

Ce document liste **chaque entrée de la sidebar admin** (`app/Views/partials/admin-sidebar.php`), ce qu'elle fait réellement (contrôleur/route/vue), ce qui manque, et mon avis. Sert de base de travail pour la revue onglet par onglet.

> **Refonte août 2026** : la sidebar a été redessinée d'après la maquette, le logo conservé. Ce document est désormais réorganisé pour suivre la structure actuelle de la sidebar (voir `SIDEBAR_MAQUETTE_MAPPING.md`).

Légende statut : ✅ Complet · 🟡 Fonctionnel mais limité · 🔴 Lecture seule / faible valeur · ⚠️ Bug ou incohérence connue

---

## 1. Accueil

### Tableau de bord (`/admin`)
- **Contrôleur** : `AdminDashboardController::index`
- **Fait** : Agrège des KPIs (clients, portefeuilles, réservations, offres, loterie, sondages), graphique inscriptions 7j, répartition par outil, fil d'activité récente (6 derniers événements tous types confondus), derniers clients inscrits, dernières recharges, top offres, sondages actifs, prochaine loterie.
- **Statut** : ✅ Complet en lecture. Aucune action (pas de bouton d'action métier sur cette page, uniquement des liens vers les autres modules).
- **Manque** : pas de sélecteur de période (fixe à 7 jours / ce mois), pas de personnalisation/drag des widgets.
- **Avis** : Bonne vue d'ensemble, RAS pour un tableau de bord v1.

---

## 2. Gestion

> Les 5 onglets **Bar**, **Tabac**, **Jeux & Services**, **PMU** et **NIRIO** pointent tous vers `/admin/services` en attendant une scission par catégorie.

### Bar / Tabac / Jeux & Services / PMU / NIRIO (`/admin/services`) — ✅ REFONTE FAITE (Lot 18)
- **Contrôleur** : `AdminServiceController` (index/create/store/edit/update/toggleStatus/destroy) — contrôleur dédié, routes explicites déclarées avant le catch-all.
- **Modèle** : `App\Models\Service` (table `services`, migration `database/migration_lot18_services_catalog.sql`, reprend les 8 services historiquement codés en dur avec les mêmes slugs que `config/image_slots.php` pour préserver la continuité des photos déjà uploadées).
- **Fait** :
  - CRUD complet : création/édition avec nom, description, choix d'icône (liste de pictos + statut publié/inactif), suppression avec confirmation.
  - Liste avec compteurs (actifs/inactifs/nouveaux ce mois-ci) et bascule rapide de statut.
  - La page publique `/nos-services` (`ServicesController`) lit désormais `Service::listActiveOrdered()` au lieu d'un tableau statique — un service désactivé en admin disparaît immédiatement du site public.
- **Statut** : ✅ Fonctionnel, mais **un seul et même écran pour les 5 onglets** (Bar/Tabac/Jeux & Services/PMU/NIRIO) — pas de filtrage par catégorie côté admin, la scission promise par la maquette n'existe pas encore côté données.
- **Manque** :
  - Pas de champ « catégorie » sur `Service` pour distinguer Bar/Tabac/Jeux & Services/PMU/NIRIO et faire pointer chaque onglet vers une vue filtrée.
  - Les nouveaux services créés depuis l'admin n'ont pas d'emplacement dans `config/image_slots.php`, donc pas de photo dédiée uploadable via `/admin/images` sans intervention manuelle sur ce fichier.
- **Avis** : CRUD solide, mais tant qu'il n'y a pas de notion de catégorie, les 5 entrées de sidebar sont trompeuses (elles affichent toutes le même contenu global).

---

## 3. Employés

### Employés (`/admin/employes`)
- **Contrôleur** : `AdminEmployeeController` (index/store/update/toggleStatus/destroy)
- **Fait** : CRUD complet (ajout, modification, activation/désactivation, suppression), validation email/champs obligatoires.
- **Statut** : 🟡 CRUD fonctionnel mais c'est un **simple annuaire RH** : aucun compte de connexion (pas d'email/mot de passe lié à `users`), donc pas de gestion des droits/permissions par employé.
- **Manque** : Pas de lien avec un compte utilisateur/authentification, pas d'historique de présence/horaires.
- **Avis** : Suffisant pour un annuaire, mais s'il faut un jour donner un accès back-office à un employé (autre que l'admin), il faudra une vraie gestion de comptes.

---

## 4. Clients & Prestataires

### Clients Inscrits (`/admin/clients`)
- **Contrôleur** : `AdminClientController` (index/export/show/update/adjustWallet/destroy/sendMessage)
- **Fait** : Liste paginée + filtres (recherche, statut, segment, dates), export CSV complet (BOM UTF-8 pour Excel), fiche client détaillée (infos, portefeuille, historique paginé, parrainages), édition fiche, crédit/débit manuel du portefeuille (transaction SQL atomique + bonus fidélité +2€ sur recharge 50€), suppression douce (`deleted_at`), envoi WhatsApp simulé (écrit en base, pas d'API réelle).
- **Statut** : ✅ Très complet, le module le plus mature du back-office.
- **Manque** : L'envoi WhatsApp/SMS est simulé (persistance en base uniquement) — cohérent avec la contrainte "100% PHP natif" sans API externe pour l'instant.
- **Avis** : Excellent niveau de finition, à utiliser comme référence pour les autres modules.

### Portefeuille Client (`/admin/portefeuilles`)
- **Contrôleur** : `AdminPlaceholderController::show('portefeuilles')` (pas de contrôleur dédié)
- **Fait** : 4 KPIs (solde total, nb portefeuilles, recharges du mois, top clients) + tableau des dernières transactions avec **liens cliquables vers la fiche client** (`/admin/clients/{id}`), recherche par nom/téléphone et filtre par période (`from`/`to`). Toute action de crédit/débit reste effectuée depuis la fiche client.
- **Statut** : ✅ Lecture + navigation, cohérent avec son rôle de tableau de bord (l'action réelle reste sur la fiche client, ce qui est un choix d'architecture volontaire).
- **Manque** : rien de bloquant identifié.
- **Avis** : Contrairement à une revue précédente, ce module dispose déjà de liens cliquables et de filtres — bien intégré.

> **« Prestataires »** : cette notion n'existe pas encore dans le code (aucun modèle/table dédié). Le libellé du menu suggère une gestion à part des prestataires externes, à spécifier avec l'utilisateur si un vrai module est attendu.

---

## 5. Réservations

### Réservations (`/admin/reservations`)
- **Contrôleur** : `AdminReservationController` (index/updateStatus/destroy)
- **Fait** : Liste filtrable (statut, date), changement de statut (en attente/confirmée/annulée), suppression.
- **Statut** : 🟡 Fonctionnel mais **pas de création de réservation côté admin** (uniquement via le formulaire public `/reservation`).
- **Manque** : Pas de saisie manuelle pour un client au téléphone, pas de vue calendrier.
- **Avis** : Correct pour une gestion réactive, mais un admin ne peut pas créer de réservation "à la main" pour un client qui appelle — à considérer si c'est un besoin réel.

---

## 6. Marketing

### Marketing (`/admin/offres`)
- **Note** : ce lien ouvre la même page que **Fidélisations > Offres & Avantages**. Voir l'entrée correspondante (section 10) pour la revue détaillée — même contrôleur, même statut, même manques.

---

## 7. Contenu du site

### Photos du site (`/admin/images`)
- **Contrôleur** : `AdminImageController` (index/store/destroy)
- **Fait** : Upload/remplacement/suppression d'images par emplacement (`config/image_slots.php`), validation MIME (jpg/png/webp), taille max 5 Mo, nettoyage de l'ancien fichier à chaque remplacement.
- **Statut** : ✅ Complet et robuste.
- **Manque** : Pas de recadrage/aperçu avant upload, pas d'historique des anciennes photos.
- **Avis** : Bien implémenté, rien à signaler.

---

## 8. Messages

### Messages (`/admin/messages`)
- **Contrôleur** : `AdminMessageController` (Lot 17)
- **Fait** : Boîte de réception omnicanale (contact/SMS/WhatsApp) avec onglets (réception, envoyés, programmés, brouillons/archives/spam en placeholder), recherche + filtres canal/étiquette, conversation à 3 colonnes, réponses rapides, programmation d'envoi, étiquettes clients, notes internes, statistiques d'engagement, composeur "nouveau message". Badge du nombre de messages non lus affiché dans la sidebar via `ContactMessage::countUnread()`.
- **Statut** : 🟡 Fonctionnel après le correctif du bug de guillemets (`onclick`/`json_encode`). SMS/WhatsApp restent **simulés** (écriture en base, pas d'API Twilio/Meta réelle).
- **Manque** :
  - Les messages "programmés" ne sont **jamais envoyés automatiquement** (pas de cron/tâche planifiée) — l'action `cancelScheduled` existe mais rien ne les fait passer en "envoyé" à échéance.
  - Onglets "Brouillons", "Archives", "Spam" affichent un message "pas encore disponible" (pas de logique derrière).
  - Pas de pièce jointe / média.
- **Avis** : Prochaine étape logique : décider si les messages programmés doivent être traités par une tâche cron (hors périmètre actuel) ou rester une intention déclarative.

---

## 9. Avis

### Avis (`/admin/avis-google`) — ✅ ÉDITION AJOUTÉE
- **Contrôleur** : `AdminReviewController` (index/store/update/destroy)
- **Fait** : Liste des avis (50 derniers), répartition par note, ajout manuel d'un avis, **édition inline** (formulaire dépliable par avis, sans rechargement de page superflu) et suppression avec confirmation.
- **Statut** : 🟡 CRUD quasi complet ; reste trompeur côté nommage : **les avis sont saisis manuellement en base**, aucune intégration API Google Business Profile réelle.
- **Manque** : pas de vraie synchronisation Google.
- **Avis** : Édition ajoutée. Le renommage (« Témoignages clients ») reste à valider avec l'utilisateur pour lever toute ambiguïté.

---

## 10. Fidélisations

### Offres & Avantages (`/admin/offres`) — ✅ CRUD COMPLÉTÉ
- **Contrôleur** : `AdminOfferController` (index/create/store/edit/update/destroy/toggleStatus/generateCode/sendToClient)
- **Fait** : Liste filtrée par statut (active/brouillons/expirées/toutes), création/édition d'offre (formulaire partagé `_form.php` entre `create.php` et `edit.php`, validation extraite dans une méthode privée `validate()`), suppression avec confirmation, génération de code/QR pour un client donné, envoi WhatsApp simulé de l'offre, bascule active/brouillon.
- **Statut** : ✅ CRUD complet (update + destroy ajoutés).
- **Manque** : rien de bloquant identifié.
- **Avis** : Lacune historique comblée ; la suppression d'une offre entraîne la suppression en cascade de ses codes/redemptions (`ON DELETE CASCADE` en base), à garder en tête si l'historique doit être conservé.

### Scanner une offre (`/admin/offres/scanner`)
- **Contrôleur** : `AdminOfferScanController` (index/verify/redeem)
- **Fait** : Flux en 2 étapes (vérification du code sans le consommer, puis validation effective), gestion des cas déjà utilisé/expiré/introuvable, notification WhatsApp simulée au client après validation.
- **Statut** : ✅ Complet et bien pensé (séparation vérification/consommation).
- **Manque** : rien de bloquant identifié.
- **Avis** : Un des modules les mieux conçus, logique métier claire.

### Zonage & Proximité (`/admin/zonage`) — ✅ CRUD COMPLÉTÉ
- **Contrôleur** : `AdminProximityController` (index/store/edit/update/destroy/toggleStatus)
- **Fait** : Création/édition de campagnes géolocalisées (rayon, plage horaire, jours, segment cible, offre liée, message) via un formulaire partagé `_form.php`, suppression avec confirmation, bascule active/en pause. Carte Leaflet en JS (nécessite Internet côté navigateur).
- **Statut** : ✅ CRUD complet (update + destroy ajoutés).
- **Manque** : pas de détail d'envoi par campagne dans la vue listée (seuls les compteurs agrégés `sent_count`/`used_count` sont affichés).
- **Avis** : Cohérence rétablie avec les autres modules de Fidélisation.

### Loterie (`/admin/loterie`)
- **Contrôleur** : `AdminLotteryController` (index/create/store/toggleStatus/draw/destroy)
- **Fait** : Création, activation/pause, **tirage au sort aléatoire** avec notification WhatsApp simulée au gagnant, suppression.
- **Statut** : ✅ Le CRUD le plus complet de la section Fidélisation (a un `destroy`, contrairement aux Offres/Zonage/Sondages).
- **Manque** : pas d'édition (seulement suppression/recréation), pas d'historique des anciens gagnants visible ailleurs que dans la fiche loterie elle-même.
- **Avis** : Bon niveau, logique de tirage au sort solide.

---

## 11. Sondages & Votes

### Sondages & Votes (`/admin/sondages`) — ✅ CRUD COMPLÉTÉ
- **Contrôleur** : `AdminPollController` (index/create/store/edit/update/destroy/results/toggleStatus)
- **Fait** : Création/édition de sondage avec options multiples et récompense (formulaire partagé `_form.php`), consultation des résultats détaillés, bascule actif/terminé, suppression avec confirmation, sondage "vedette" mis en avant. Les options soumises en `options[i][label]`/`options[i][id]` : les libellés des options existantes sont mis à jour sans perdre leur `votes_count`, les nouvelles options sont ajoutées ; aucune option déjà votée n'est supprimable depuis l'édition (protection de l'intégrité des votes).
- **Statut** : ✅ CRUD complet (update + destroy ajoutés).
- **Manque** : rien de bloquant identifié.
- **Avis** : Dernier module du trio CRUD-incomplet (Offres/Zonage/Sondages) désormais harmonisé.

---

## 12. Pilotage

### Statistiques (`/admin/statistiques`)
- **Contrôleur** : `AdminStatisticsController` (index/export)
- **Fait** : Activité portefeuille, répartition moyens de paiement, nouveaux clients par mois, top clients par dépense, KPIs globaux, **filtre de période personnalisable** (`from`/`to`) et **export CSV** multi-sections.
- **Statut** : ✅ Lecture enrichie (filtre + export déjà présents).
- **Manque** : rien de bloquant identifié.
- **Avis** : Contrairement à une revue précédente, ce module dispose déjà d'un export et d'un filtre de période — bien equipé pour du reporting basique.

### Google Analytics (`/admin/google-analytics`)
- **Contrôleur** : `AdminGoogleAnalyticsController::index`
- **Fait** : KPIs internes (clients, portefeuilles, réservations, offres, loterie, sondages, messages, avis, proximité), + **intégration GA4 réelle** si `ga4_property_id`/`ga4_service_account_json` configurés dans Paramètres (sessions, utilisateurs, pages vues, taux de rebond, durée moyenne, conversions), carte Leaflet des clients à proximité.
- **Statut** : 🟡 Fonctionnel, mais dépend d'une configuration GA4 externe (compte de service Google) pour la partie analytics réelle ; sans config, `ga4Configured` est `false` et les blocs GA4 restent vides.
- **Manque** : pas de bouton "tester la connexion GA4" visible pour valider la config sans attendre le rendu de la page.
- **Avis** : Bonne architecture (graceful fallback si non configuré), mais nécessite une vraie clé de service GA4 pour être pleinement utile.

---

## 13. Paramètres

### Paramètres (`/admin/parametres`) — recadré suite à la refonte de Mon établissement
- **Contrôleur** : `AdminSettingsController` (index/update)
- **Fait** : Mentions légales (forme juridique, SIRET, RCS, directeur de publication, hébergeur), configuration Google Analytics (GA4). Les champs d'identité/adresse/horaires/réseaux sociaux ont été déplacés vers `/admin/etablissement` pour éviter deux formulaires sur les mêmes données.
- **Statut** : ✅ Fonctionnel, portée resserrée à « légal + technique ».
- **Manque** : pas de test de connexion GA4 immédiat depuis ce formulaire.
- **Avis** : Page nettement plus lisible après le recentrage ; peut encore être scindée en 2 onglets (Légal / GA4) si besoin.

---

## 14. Support

### Support
- **Contrôleur** : Aucun
- **Fait** : Placeholder dans la sidebar, affiché grisé. Pas de vue associée.
- **Statut** : ⚠️ Non implémenté.
- **Manque** : route, contrôleur, vue, fonctionnalité.
- **Avis** : Prévu pour un futur module d'assistance / FAQ / contact support.

---

## A. Annexes — modules non affichés dans la sidebar actuelle

### Mon établissement (`/admin/etablissement`) — ✅ REFONTE FAITE
- **Contrôleur** : `AdminEstablishmentController` (index/update) — contrôleur dédié, routes explicites déclarées avant le catch-all.
- **Fait** :
  - Formulaire d'édition complet : identité (nom, téléphone, WhatsApp, e-mail), adresse & localisation (avec carte Leaflet interactive à marqueur déplaçable pour ajuster lat/long), Street View, horaires (avec badge "Aujourd'hui" dynamique selon le jour de la semaine), réseaux sociaux.
  - Aperçu client en temps réel (carte récapitulative) + liens croisés vers Zonage/Proximité, Messages, Paramètres.
  - 4 KPIs conservés (clients, offres actives, réalisations du mois, économies).
  - Validation serveur (nom obligatoire, e-mail valide si renseigné, lat/long numériques).
- **Statut** : ✅ Fonctionnel et complet, mais **absent du menu de la nouvelle sidebar** — accessible uniquement par URL directe.
- **Manque** : pas encore de test d'adresse (géocodage automatique depuis l'adresse texte vers lat/long) ; pas d'entrée de menu pour y accéder depuis la nouvelle sidebar.
- **Avis** : Page solide, mais son retrait de la sidebar la rend invisible pour l'admin — à réintégrer (ex: sous Paramètres ou Contenu du site) si elle doit rester utilisée.

### Facturation (`/admin/facturation`) — ✅ FILTRES + EXPORT AJOUTÉS
- **Contrôleur** : `AdminBillingController` (index/export/show)
- **Fait** : Liste paginée des factures avec **recherche client** et **filtre de période** (`q`/`from`/`to`, méthode `Invoice::buildFilters()` centralisée pour éviter la duplication SQL entre pagination et export), **export CSV** de la liste filtrée, vue facture imprimable (HTML, impression navigateur → PDF).
- **Statut** : 🟡 Lecture enrichie (filtres + export) ; pas de création manuelle de facture — **volontairement absent** car `Invoice` n'a pas de table dédiée : une « facture » est une simple lecture de `wallet_transactions` (recharge carte bancaire réussie), donc une création manuelle romprait la source de vérité unique. **Toujours absent du menu de la nouvelle sidebar.**
- **Manque** : entrée de menu pour y accéder depuis la sidebar.
- **Avis** : Filtres et export couvrent le principal manque signalé. La génération manuelle n'a pas été ajoutée par choix d'architecture (cohérence avec le modèle `Invoice` dérivé) plutôt que par oubli — à discuter si un vrai besoin de facture indépendante d'une recharge existe.

---

## Synthèse — constats transversaux

1. ~~Pattern de CRUD incomplet récurrent (Offres, Zonage/Proximité, Sondages)~~ — **✅ résolu** : les trois modules disposent désormais d'`edit`/`update`/`destroy`, avec formulaires de création/édition partagés (`_form.php`) pour éviter toute duplication de markup, et validation extraite dans une méthode privée `validate()` par contrôleur (principe de responsabilité unique).
2. **1 page "vitrine" en lecture (+ navigation) servie par un contrôleur générique** (`AdminPlaceholderController`) : Portefeuille client. Dispose déjà de liens cliquables vers les fiches clients et de filtres — reste cohérent avec son rôle de tableau de bord, l'action réelle (crédit/débit) restant sur la fiche client. *(Services du quotidien et Mon établissement ont été sortis de ce lot et refondus en pages éditables dédiées.)*
3. **Toute communication sortante (WhatsApp/SMS/e-mail) est simulée** (écriture en base, pas d'appel API réel) — cohérent avec la contrainte actuelle "100% PHP natif", mais à garder en tête pour la mise en production réelle.
4. **"Avis" n'est pas connecté à Google** — le module dispose désormais d'une édition inline, mais le nom reste potentiellement trompeur pour l'utilisateur final (avis saisis manuellement, aucune synchronisation Google Business Profile).
5. **Aucune tâche planifiée (cron)** : les messages programmés (Lot 17) ne partent jamais tout seuls à l'heure prévue.
6. **Facturation** dispose désormais de filtres (client/période) et d'un export CSV ; la génération manuelle de facture reste absente par choix d'architecture (`Invoice` est une vue dérivée de `wallet_transactions`, pas une table dédiée).
7. **Statistiques** disposait déjà d'un export CSV et d'un filtre de période — corrigé dans cette revue (information obsolète dans une version précédente du document).

---

*Document généré pour servir de support

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
