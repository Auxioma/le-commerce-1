# Revue du module Admin — Onglets de la sidebar

Ce document liste **chaque entrée de la sidebar admin** (`app/Views/partials/admin-sidebar.php`), ce qu'elle fait réellement (contrôleur/route/vue), ce qui manque, et mon avis. Sert de base de travail pour la revue onglet par onglet.

Légende statut : ✅ Complet · 🟡 Fonctionnel mais limité · 🔴 Lecture seule / faible valeur · ⚠️ Bug ou incohérence connue

---

## Section « Général »

### 1. Tableau de bord (`/admin`)
- **Contrôleur** : `AdminDashboardController::index`
- **Fait** : Agrège des KPIs (clients, portefeuilles, réservations, offres, loterie, sondages), graphique inscriptions 7j, répartition par outil, fil d'activité récente (6 derniers événements tous types confondus), derniers clients inscrits, dernières recharges, top offres, sondages actifs, prochaine loterie.
- **Statut** : ✅ Complet en lecture. Aucune action (pas de bouton d'action métier sur cette page, uniquement des liens vers les autres modules).
- **Manque** : pas de sélecteur de période (fixe à 7 jours / ce mois), pas de personnalisation/drag des widgets.
- **Avis** : Bonne vue d'ensemble, RAS pour un tableau de bord v1.

### 2. Mon établissement (`/admin/etablissement`) — ✅ REFONTE FAITE
- **Contrôleur** : `AdminEstablishmentController` (index/update) — nouveau contrôleur dédié, routes explicites déclarées avant le catch-all.
- **Fait (après refonte)** :
  - Formulaire d'édition complet : identité (nom, téléphone, WhatsApp, e-mail), adresse & localisation (avec carte Leaflet interactive à marqueur déplaçable pour ajuster lat/long), Street View, horaires (avec badge "Aujourd'hui" dynamique selon le jour de la semaine), réseaux sociaux.
  - Aperçu client en temps réel (carte récapitulative) + liens croisés vers Zonage/Proximité, Messages, Paramètres.
  - 4 KPIs conservés (clients, offres actives, réalisations du mois, économies).
  - Validation serveur (nom obligatoire, e-mail valide si renseigné, lat/long numériques).
- **Changements connexes** : les champs Identité/Adresse/Horaires/Réseaux sociaux ont été **retirés de Paramètres** (`AdminSettingsController` + `admin/settings/index.php`) pour éviter deux formulaires concurrents sur les mêmes données. Paramètres ne gère plus que Mentions légales & hébergeur + Google Analytics, avec un lien vers Mon établissement.
- **Manque** : pas encore de test d'adresse (géocodage automatique depuis l'adresse texte vers lat/long).
- **Avis** : Doublon avec Paramètres résolu — page désormais pleinement fonctionnelle et à sa place logique dans la sidebar.

### 3. Services du quotidien (`/admin/services`) — ✅ REFONTE FAITE (Lot 18)
- **Contrôleur** : `AdminServiceController` (index/create/store/edit/update/toggleStatus/destroy) — nouveau contrôleur dédié, routes explicites déclarées avant le catch-all.
- **Modèle** : `App\Models\Service` (table `services`, migration `database/migration_lot18_services_catalog.sql`, reprend les 8 services historiquement codés en dur avec les mêmes slugs que `config/image_slots.php` pour préserver la continuité des photos déjà uploadées).
- **Fait (après refonte)** :
  - CRUD complet : création/édition avec nom, description, choix d'icône (liste de pictos + statut publié/inactif), suppression avec confirmation.
  - Liste avec compteurs (actifs/inactifs/nouveaux ce mois-ci) et bascule rapide de statut.
  - La page publique `/nos-services` (`ServicesController`) lit désormais `Service::listActiveOrdered()` au lieu d'un tableau statique — un service désactivé en admin disparaît immédiatement du site public.
- **Manque** : les nouveaux services créés depuis l'admin n'ont pas d'emplacement dans `config/image_slots.php`, donc pas de photo dédiée uploadable via `/admin/images` sans intervention manuelle sur ce fichier (les 8 services historiques, eux, en bénéficient toujours).
- **Avis** : Onglet désormais fonctionnel et à sa place — le catalogue public est piloté depuis l'admin, ce qui n'était pas le cas avant.

### 4. Photos du site (`/admin/images`)
- **Contrôleur** : `AdminImageController` (index/store/destroy)
- **Fait** : Upload/remplacement/suppression d'images par emplacement (`config/image_slots.php`), validation MIME (jpg/png/webp), taille max 5 Mo, nettoyage de l'ancien fichier à chaque remplacement.
- **Statut** : ✅ Complet et robuste.
- **Manque** : Pas de recadrage/aperçu avant upload, pas d'historique des anciennes photos.
- **Avis** : Bien implémenté, rien à signaler.

### 5. Employés (`/admin/employes`)
- **Contrôleur** : `AdminEmployeeController` (index/store/update/toggleStatus/destroy)
- **Fait** : CRUD complet (ajout, modification, activation/désactivation, suppression), validation email/champs obligatoires.
- **Statut** : 🟡 CRUD fonctionnel mais c'est un **simple annuaire RH** : aucun compte de connexion (pas d'email/mot de passe lié à `users`), donc pas de gestion des droits/permissions par employé.
- **Manque** : Pas de lien avec un compte utilisateur/authentification, pas d'historique de présence/horaires.
- **Avis** : Suffisant pour un annuaire, mais s'il faut un jour donner un accès back-office à un employé (autre que l'admin), il faudra une vraie gestion de comptes.

---

## Section « Clients »

### 6. Clients inscrits (`/admin/clients`)
- **Contrôleur** : `AdminClientController` (index/export/show/update/adjustWallet/destroy/sendMessage)
- **Fait** : Liste paginée + filtres (recherche, statut, segment, dates), export CSV complet (BOM UTF-8 pour Excel), fiche client détaillée (infos, portefeuille, historique paginé, parrainages), édition fiche, crédit/débit manuel du portefeuille (transaction SQL atomique + bonus fidélité +2€ sur recharge 50€), suppression douce (`deleted_at`), envoi WhatsApp simulé (écrit en base, pas d'API réelle).
- **Statut** : ✅ Très complet, le module le plus mature du back-office.
- **Manque** : L'envoi WhatsApp/SMS est simulé (persistance en base uniquement) — cohérent avec la contrainte "100% PHP natif" sans API externe pour l'instant.
- **Avis** : Excellent niveau de finition, à utiliser comme référence pour les autres modules.

### 7. Portefeuille client (`/admin/portefeuilles`)
- **Contrôleur** : `AdminPlaceholderController::show('portefeuilles')` (pas de contrôleur dédié)
- **Fait** : 4 KPIs (solde total, nb portefeuilles, recharges du mois, top clients) + tableau des dernières transactions. 100% lecture.
- **Statut** : 🔴 Lecture seule — toutes les vraies actions (créditer/débiter) se font en fait depuis la fiche client (`/admin/clients/{id}`).
- **Manque** : Pas de lien direct depuis une ligne de transaction vers la fiche client concernée (pas de `<a>` cliquable), pas de filtre par période/client.
- **Avis** : Fonctionne comme un mini tableau de bord "Portefeuilles" — cohérent avec Mon établissement/Services (tous 3 servis par le même `AdminPlaceholderController`). Gagnerait à avoir des liens cliquables vers les fiches clients.

### 8. Messages (`/admin/messages`)
- **Contrôleur** : `AdminMessageController` (Lot 17, tout juste livré)
- **Fait** : Boîte de réception omnicanale (contact/SMS/WhatsApp) avec onglets (réception, envoyés, programmés, brouillons/archives/spam en placeholder), recherche + filtres canal/étiquette, conversation à 3 colonnes, réponses rapides, programmation d'envoi, étiquettes clients, notes internes, statistiques d'engagement, composeur "nouveau message".
- **Statut** : 🟡 Fonctionnel après le correctif du bug de guillemets (`onclick`/`json_encode`). SMS/WhatsApp restent **simulés** (écriture en base, pas d'API Twilio/Meta réelle).
- **Manque** :
  - Les messages "programmés" ne sont **jamais envoyés automatiquement** (pas de cron/tâche planifiée) — l'action `cancelScheduled` existe mais rien ne les fait passer en "envoyé" à échéance.
  - Onglets "Brouillons", "Archives", "Spam" affichent un message "pas encore disponible" (pas de logique derrière).
  - Pas de pièce jointe / média.
- **Avis** : Vient d'être posé, correctif de bug fait. Prochaine étape logique : décider si les messages programmés doivent être traités par une tâche cron (hors périmètre actuel) ou rester une intention déclarative.

### 9. Réservations (`/admin/reservations`)
- **Contrôleur** : `AdminReservationController` (index/updateStatus/destroy)
- **Fait** : Liste filtrable (statut, date), changement de statut (en attente/confirmée/annulée), suppression.
- **Statut** : 🟡 Fonctionnel mais **pas de création de réservation côté admin** (uniquement via le formulaire public `/reservation`).
- **Manque** : Pas de saisie manuelle pour un client au téléphone, pas de vue calendrier.
- **Avis** : Correct pour une gestion réactive, mais un admin ne peut pas créer de réservation "à la main" pour un client qui appelle — à considérer si c'est un besoin réel.

---

## Section « Fidélisation »

### 10. Offres & Avantages (`/admin/offres`)
- **Contrôleur** : `AdminOfferController` (index/create/store/toggleStatus/generateCode/sendToClient)
- **Fait** : Liste filtrée par statut (active/brouillons/expirées/toutes), création d'offre, génération de code/QR pour un client donné, envoi WhatsApp simulé de l'offre, bascule active/brouillon.
- **Statut** : ⚠️ **Pas de route/action `update` ni `destroy`** — une offre créée ne peut ni être modifiée, ni supprimée, seulement activée/mise en brouillon.
- **Manque** : édition et suppression d'offre.
- **Avis** : Lacune fonctionnelle réelle à combler si les offres sont amenées à évoluer après création (erreur de saisie, changement de valeur, etc.).

### 11. Scanner une offre (`/admin/offres/scanner`)
- **Contrôleur** : `AdminOfferScanController` (index/verify/redeem)
- **Fait** : Flux en 2 étapes (vérification du code sans le consommer, puis validation effective), gestion des cas déjà utilisé/expiré/introuvable, notification WhatsApp simulée au client après validation.
- **Statut** : ✅ Complet et bien pensé (séparation vérification/consommation).
- **Manque** : rien de bloquant identifié.
- **Avis** : Un des modules les mieux conçus, logique métier claire.

### 12. Zonage & Proximité (`/admin/zonage`)
- **Contrôleur** : `AdminProximityController` (index/store/toggleStatus)
- **Fait** : Création de campagnes géolocalisées (rayon, plage horaire, jours, segment cible, offre liée, message), bascule active/en pause. Carte Leaflet en JS (nécessite Internet côté navigateur).
- **Statut** : ⚠️ **Pas d'édition ni de suppression** de campagne, seulement création + toggle.
- **Manque** : update/destroy, pas d'historique d'envoi (`totalSent`/`totalUsed` existent au niveau modèle mais pas de détail par campagne dans la vue listée ici).
- **Avis** : Même lacune que les Offres — cohérence à améliorer sur l'ensemble du back-office (CRUD incomplet récurrent).

### 13. Sondages & Votes (`/admin/sondages`)
- **Contrôleur** : `AdminPollController` (index/create/store/results/toggleStatus)
- **Fait** : Création de sondage avec options multiples et récompense, consultation des résultats détaillés, bascule actif/terminé, sondage "vedette" mis en avant.
- **Statut** : ⚠️ **Pas d'édition/suppression** d'un sondage ni de ses options une fois créé.
- **Manque** : update/destroy.
- **Avis** : Encore la même lacune de CRUD partiel — pattern récurrent à traiter transversalement (Offres, Zonage, Sondages).

### 14. Loterie (`/admin/loterie`)
- **Contrôleur** : `AdminLotteryController` (index/create/store/toggleStatus/draw/destroy)
- **Fait** : Création, activation/pause, **tirage au sort aléatoire** avec notification WhatsApp simulée au gagnant, suppression.
- **Statut** : ✅ Le CRUD le plus complet de la section Fidélisation (a un `destroy`, contrairement aux Offres/Zonage/Sondages).
- **Manque** : pas d'édition (seulement suppression/recréation), pas d'historique des anciens gagnants visible ailleurs que dans la fiche loterie elle-même.
- **Avis** : Bon niveau, logique de tirage au sort solide.

### 15. Avis Google (`/admin/avis-google`)
- **Contrôleur** : `AdminReviewController` (index/store/destroy)
- **Fait** : Liste des avis (50 derniers), répartition par note, ajout manuel d'un avis, suppression.
- **Statut** : ⚠️ Trompeur : le nom "Avis Google" laisse penser à une synchronisation avec Google Business Profile, mais **les avis sont saisis manuellement en base**, aucune intégration API réelle.
- **Manque** : pas d'édition d'un avis existant, pas de vraie synchronisation Google.
- **Avis** : À clarifier avec l'utilisateur — soit renommer (« Témoignages clients ») pour éviter la confusion, soit prévoir une vraie intégration Google Business Profile plus tard.

---

## Section « Pilotage »

### 16. Statistiques (`/admin/statistiques`)
- **Contrôleur** : `AdminStatisticsController::index`
- **Fait** : Activité portefeuille (14j), répartition moyens de paiement, nouveaux clients par mois (6 mois), top clients par dépense, quelques KPIs globaux.
- **Statut** : 🔴 Lecture seule, aucune action.
- **Manque** : pas d'export, pas de filtre de période personnalisé.
- **Avis** : Correct comme page de reporting basique.

### 17. Google Analytics (`/admin/google-analytics`)
- **Contrôleur** : `AdminGoogleAnalyticsController::index`
- **Fait** : KPIs internes (clients, portefeuilles, réservations, offres, loterie, sondages, messages, avis, proximité), + **intégration GA4 réelle** si `ga4_property_id`/`ga4_service_account_json` configurés dans Paramètres (sessions, utilisateurs, pages vues, taux de rebond, durée moyenne, conversions), carte Leaflet des clients à proximité.
- **Statut** : 🟡 Fonctionnel, mais dépend d'une configuration GA4 externe (compte de service Google) pour la partie analytics réelle ; sans config, `ga4Configured` est `false` et les blocs GA4 restent vides.
- **Manque** : pas de bouton "tester la connexion GA4" visible pour valider la config sans attendre le rendu de la page.
- **Avis** : Bonne architecture (graceful fallback si non configuré), mais nécessite une vraie clé de service GA4 pour être pleinement utile — à vérifier si l'utilisateur en dispose.

### 18. Facturation (`/admin/facturation`)
- **Contrôleur** : `AdminBillingController` (index/show)
- **Fait** : Liste paginée des factures (revenus totaux, revenus du mois, nombre de factures du mois), vue facture imprimable (HTML, impression navigateur → PDF).
- **Statut** : 🔴 100% lecture — pas de création manuelle de facture, pas de filtre par statut/client/date, pas d'export CSV/PDF natif (uniquement impression navigateur).
- **Manque** : filtres, génération manuelle, export groupé.
- **Avis** : Suffisant si les factures sont générées automatiquement ailleurs (ex: à la recharge portefeuille) ; sinon manque clairement d'outils de gestion actifs.

### 19. Paramètres (`/admin/parametres`) — recadré suite à la refonte de Mon établissement
- **Contrôleur** : `AdminSettingsController` (index/update)
- **Fait** : Mentions légales (forme juridique, SIRET, RCS, directeur de publication, hébergeur), configuration Google Analytics (GA4). Les champs d'identité/adresse/horaires/réseaux sociaux ont été déplacés vers `/admin/etablissement` pour éviter deux formulaires sur les mêmes données.
- **Statut** : ✅ Fonctionnel, portée resserrée à « légal + technique ».
- **Manque** : pas de test de connexion GA4 immédiat depuis ce formulaire.
- **Avis** : Page nettement plus lisible après le recentrage ; peut encore être scindée en 2 onglets (Légal / GA4) si besoin.

---

## Synthèse — constats transversaux

1. **Pattern de CRUD incomplet récurrent** : Offres, Zonage/Proximité et Sondages n'ont **ni update ni destroy** — seulement création + bascule de statut. Loterie est la seule exception avec un `destroy`. À harmoniser si l'utilisateur veut pouvoir corriger une erreur de saisie sans devoir désactiver et recréer.
2. **2 pages "vitrines" en lecture seule servies par le même contrôleur générique** (`AdminPlaceholderController`) : Services du quotidien, Portefeuille client. Elles dupliquent des données déjà visibles ailleurs (Offres, Clients) sans action propre — à fusionner ou enrichir. *(Mon établissement a été sorti de ce lot et refondu en page éditable dédiée.)*
3. **Toute communication sortante (WhatsApp/SMS/e-mail) est simulée** (écriture en base, pas d'appel API réel) — cohérent avec la contrainte actuelle "100% PHP natif", mais à garder en tête pour la mise en production réelle.
4. **"Avis Google" n'est pas connecté à Google** — nom potentiellement trompeur pour l'utilisateur final.
5. **Aucune tâche planifiée (cron)** : les messages programmés (Lot 17) ne partent jamais tout seuls à l'heure prévue.

---

*Document généré pour servir de support à la revue onglet par onglet demandée par l'utilisateur. Prochaine étape : passer en revue chaque module un par un pour prioriser les corrections/évolutions.*
