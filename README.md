# Le Commerce — Bar · Tabac · PMU · FDJ · Presse · NIRIO

Site vitrine + espace client + back-office pour un commerce de proximité
(Forges-les-Eaux). Architecture **MVC en PHP natif** (sans framework),
gabarits **Tailwind CSS + DaisyUI**, base de données **MySQL**.

> **Lots livrés :**
> - **Lot 1** : socle MVC complet + page Accueil pixel proche de la maquette.
> - **Lot 2** : authentification complète (inscription client, connexion
>   client, connexion admin, déconnexion, protection des routes par rôle,
>   CSRF, sessions).
> - **Lot 4** : espace client — tableau de bord, portefeuille, recharge
>   (avec bonus fidélité), historique des transactions paginé, avantages
>   fidélité par paliers, parrainage.
> - **Lot 5** : back-office admin — tableau de bord (KPIs portefeuilles,
>   dernières transactions, top clients) et gestion des clients inscrits
>   (recherche, filtres, pagination, export CSV, envoi de message).
> - **Lot 6** : offres & avantages — création d'offres, génération de
>   codes/QR uniques par client, envoi WhatsApp, scan et validation en
>   caisse avec logique anti-abus (verrouillage transactionnel, un code
>   ne peut être utilisé qu'une seule fois même en cas de scans
>   simultanés), espace client "Mes offres".
> - **Lot 8** : sondages & votes — création de sondages (options
>   dynamiques), vote client à choix unique garanti par contrainte
>   d'unicité en base, récompenses automatiques (points fidélité ou
>   crédit portefeuille) appliquées dans la même transaction que le vote,
>   résultats en temps réel (admin et client).
> - **Lot 7** : zonage & proximité — campagnes géolocalisées (rayon, plage
>   horaire, jours, segment cible, offre liée), détection de proximité
>   côté client via la géolocalisation du navigateur, calcul de distance
>   par formule de Haversine, notification en temps réel avec génération
>   automatique du code d'offre à l'activation.
> - **Lot 10** : avis Google (liste + ajout manuel), statistiques
>   transverses (graphiques Chart.js : activité portefeuille, moyens de
>   paiement, nouveaux clients, top clients), facturation (factures
>   imprimables générées à partir des recharges par carte, sans dépendance
>   PDF supplémentaire), paramètres généraux du commerce (table `settings`
>   modifiable depuis l'admin et répercutée automatiquement sur tout le
>   site, y compris le site public).
>
> **Il ne manque plus que le Lot 9** (intégration réelle de l'API WhatsApp
> Business — actuellement les messages sont journalisés en base de
> données uniquement, prêts à être branchés).
>
> Les autres pages du site public (Le Bar, Tabac, PMU, FDJ, Presse, Nos
> Services, Contact) sont en place en tant que pages "prochainement"
> fonctionnelles — de même que les autres sections des menus client et
> admin (Offres & Avantages, Zonage & Proximité, Sondages & Votes,
> Messages & WhatsApp, etc.), déjà navigables et protégées par
> authentification, en attendant leur développement dans les lots suivants.

## 1. Architecture

```
le-commerce/
├── app/
│   ├── Controllers/        # 1 contrôleur par page (HomeController, BarController...)
│   ├── Models/              # Active Record léger (User, Drink, Deal, GoogleReview...)
│   ├── Views/
│   │   ├── layouts/main.php # Layout HTML commun (head, header, footer inclus)
│   │   ├── partials/        # header.php, footer.php, chat-widget.php
│   │   ├── home/index.php   # Page d'accueil
│   │   ├── pages/           # Vue "placeholder" réutilisée par les pages à venir
│   │   └── errors/404.php
│   ├── Core/                # Le "mini-framework" : Router, Controller, Model, Database, App
│   └── routes.php           # Déclaration de toutes les routes
├── config/
│   ├── app.php               # Nom du site, coordonnées, horaires...
│   └── database.php          # Connexion MySQL (lit les variables d'environnement)
├── database/
│   └── schema.sql             # Schéma complet + données de démonstration
├── public/                    # Web root (à pointer dans Apache/Nginx)
│   ├── index.php              # Front controller unique
│   ├── .htaccess              # Réécriture d'URL (Apache)
│   └── assets/{css,js,img}
├── resources/css/input.css    # Source Tailwind (compilé vers public/assets/css/app.css)
├── tailwind.config.js
└── package.json
```

### Pourquoi cette architecture ?
- **Front controller unique** (`public/index.php`) : toutes les requêtes passent
  par un point d'entrée qui charge l'autoload, la config, puis délègue au `Router`.
- **Router** : mappe `méthode HTTP + URL` → `Contrôleur::action`, avec support des
  paramètres dynamiques (`/clients/{id}`) pour les futurs lots.
- **Controller (classe abstraite)** : centralise le rendu de vues (`view()`),
  les réponses JSON (`json()`), les redirections et la récupération des
  paramètres `$_GET`/`$_POST`.
- **Model (classe abstraite)** : mini Active Record (`find`, `all`, `where`,
  `create`, `update`, `delete`) basé sur PDO et les requêtes préparées
  (protection injection SQL).
- **Autoload maison** (`App\Core\App::registerAutoloader`) : respecte les
  conventions PSR-4 sans dépendre de Composer, pour rester 100 % PHP natif.

## 2. Installation

### Prérequis
- PHP ≥ 8.1 avec les extensions `pdo_mysql` et `mbstring`
- MySQL ou MariaDB
- Node.js (uniquement pour compiler le CSS Tailwind — aucune dépendance
  côté PHP en production)

### Étapes

```bash
# 1. Créer la base de données et importer le schéma + données de démo
mysql -u root -p < database/schema.sql

# 2. Appliquer la migration d'authentification (Lot 2)
mysql -u root -p < database/migration_lot2_auth.sql

# 2bis. Appliquer les données de démonstration du Lot 5 (transactions portefeuille)
mysql -u root -p < database/migration_lot5_demo_data.sql

# 2ter. Appliquer les données de démonstration du Lot 6 (offres)
mysql -u root -p < database/migration_lot6_offers_demo.sql

# 2quater. Appliquer les données de démonstration du Lot 8 (sondages)
mysql -u root -p < database/migration_lot8_polls_demo.sql

# 2quinquies. Appliquer les données de démonstration du Lot 7 (zonage)
mysql -u root -p < database/migration_lot7_proximity_demo.sql

# 2sexies. Appliquer la table des paramètres du Lot 10
mysql -u root -p < database/migration_lot10_settings.sql

# 2septies. Base déjà existante uniquement : ajoute deleted_at (soft delete)
#           sur users/employees/lotteries/reservations/google_reviews
#           (inutile sur une base fraîche, schema.sql l'inclut déjà)
mysql -u root -p < database/migration_lot14_soft_deletes.sql

# 2octies. Messagerie CRM du Lot 17 (SMS, messages programmés, étiquettes
#          clients, notes internes)
mysql -u root -p < database/migration_lot17_messages_crm.sql

# 3. Configurer la connexion DB (variables d'environnement, ou éditez
#    directement config/database.php)
export DB_HOST=127.0.0.1
export DB_NAME=le_commerce
export DB_USER=root
export DB_PASS=

# 4. Installer les dépendances CSS et compiler Tailwind/DaisyUI
npm install
npm run build          # build unique (production)
# ou : npm run watch    # recompile automatiquement pendant le développement

# 5. Lancer le serveur PHP intégré (développement)
php -S localhost:8000 -t public

# -> ouvrir http://localhost:8000
```

### Comptes de démonstration

| Rôle   | Identifiant                          | Mot de passe  |
|--------|---------------------------------------|---------------|
| Admin  | `lecommercetabac@gmail.com`           | `admin123`    |
| Client | `06 12 34 56 78` (Jean Martin)        | `client123`   |

## 3bis. Authentification (Lot 2)

- **Client** : `/inscription` (création de compte + portefeuille
  automatique) et `/connexion` (numéro WhatsApp + mot de passe).
- **Admin** : `/admin/connexion` (e-mail + mot de passe), totalement
  séparée de la connexion client.
- **Protection des routes** : `App\Core\Middleware::requireAuth()` pour
  l'espace client, `Middleware::requireRole('admin')` pour le
  back-office — à appeler en première ligne de n'importe quel contrôleur
  protégé.
- **Sécurité** : mots de passe hashés (`password_hash`/`password_verify`),
  jeton CSRF sur tous les formulaires POST (`App\Core\Csrf`), régénération
  de l'identifiant de session à la connexion/déconnexion, redirection vers
  l'URL initialement demandée après connexion (`intended_url`).

### Déploiement Apache/Nginx
Pointez le **VirtualHost** vers le dossier `public/` (jamais la racine du
projet, pour ne pas exposer `app/`, `config/`, `database/`).
Le fichier `public/.htaccess` gère déjà la réécriture d'URL pour Apache.
Pour Nginx, utiliser :

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

## 3. Base de données

Le fichier `database/schema.sql` crée toutes les tables nécessaires à
l'ensemble du projet (y compris les tables prévues pour les prochains
lots : `wallets`, `wallet_transactions`, `offers`, `offer_redemptions`,
`proximity_campaigns`, `polls`, `poll_options`, `poll_votes`,
`whatsapp_messages`) ainsi que quelques lignes de démonstration.

## 3ter. Back-office Admin (Lot 5)

- **Layout dédié** (`app/Views/layouts/admin.php`) : sidebar sombre
  (`partials/admin-sidebar.php`) organisée en 4 sections (Général, Clients,
  Fidélisation, Pilotage), topbar (`partials/admin-topbar.php`), drawer
  mobile.
- **Tableau de bord** (`/admin`) : solde total des portefeuilles + variation
  du mois, nombre de clients avec portefeuille, recharges du mois vs mois
  dernier, dépenses du mois vs mois dernier, 5 dernières transactions,
  top 5 clients par solde. Tous les chiffres sont calculés en direct à
  partir de la base (aucune donnée figée).
- **Clients inscrits** (`/admin/clients`) : recherche (nom/téléphone),
  filtres (statut, segment, plage de dates d'inscription), pagination,
  export CSV (`/admin/clients/export`, compatible Excel avec BOM UTF-8),
  envoi de message (modale, enregistré dans `whatsapp_messages` —
  l'envoi réel via l'API WhatsApp Business sera branché au Lot 9).
- **Sections à venir** : chaque lien du menu non encore développé
  (Portefeuille client, Offres, Zonage, Sondages...) est déjà navigable
  et protégé par connexion admin, avec un badge "Bientôt" dans le menu.

## 3quater. Espace Client (Lot 4)

- **Layout dédié** (`app/Views/layouts/client.php`) : header public réutilisé
  + sidebar compte claire (`partials/client-sidebar.php`), différente de la
  sidebar sombre admin.
- **Tableau de bord** (`/mon-compte`) : solde du portefeuille, dernière
  transaction, total dépensé ce mois, points fidélité, QR code personnel,
  bloc de recharge, parrainage, étapes "Comment ça marche".
- **Recharge** (`POST /mon-compte/recharger`) : montants prédéfinis
  (10/20/50/100/150 €) ou montant libre (5 à 500 €), **bonus fidélité de
  +2 € automatique pour une recharge de 50 €**, opération atomique en base
  (`Wallet::adjustBalance` + insertion `wallet_transactions` dans une
  transaction SQL avec rollback en cas d'erreur). *Le paiement est simulé
  pour ce lot ; l'intégration Stripe réelle nécessitera vos clés API.*
- **Transactions** (`/mon-compte/transactions`) : historique complet paginé.
- **Avantages** (`/mon-compte/avantages`) : points de fidélité et paliers
  de récompenses (paramétrage fin réservé au Lot 6 — Offres & Avantages).
- **Parrainage** (`/mon-compte/parrainage`) : code personnel, lien à copier,
  nombre de filleuls.
- **Sections à venir** (Mes informations, Notifications, Aide & support) :
  déjà navigables avec badge "Bientôt", protégées par connexion.

## 3quinquies. Offres & Avantages (Lot 6)

- **Admin** (`/admin/offres`) : stats (offres actives, utilisations du mois,
  clients touchés, économies offertes), onglets par statut (actives /
  brouillons / expirées / toutes), création d'offre (`/admin/offres/creer`),
  génération de code pour un client, envoi direct par WhatsApp.
- **Scan & validation** (`/admin/offres/scanner`) : saisie du code en deux
  temps — **vérification** (affiche l'offre et le client sans consommer le
  code) puis **validation** (consomme le code). Le client est notifié par
  WhatsApp une fois l'offre validée.
- **Anti-abus, le cœur du module** (`OfferRedemption::redeem()`) :
  verrouillage `SELECT ... FOR UPDATE` dans une transaction SQL, puis
  `UPDATE ... WHERE status = 'valide'` — si deux scans arrivent en même
  temps sur le même code, un seul peut réussir, l'autre reçoit
  automatiquement "Offre déjà utilisée". **Testé et vérifié** : un rejeu
  du même code après validation est bien rejeté, l'état en base ne
  change pas.
- **Client** (`/mon-compte/offres`) : liste des offres reçues avec QR code
  et statut (valide / utilisée / expirée).
- Le champ `value` des offres sert d'estimation en euros des économies
  générées (sauf pour les réductions en %, non additionnables en euros).

## 3sexies. Sondages & Votes (Lot 8)

- **Admin** (`/admin/sondages`) : stats (sondages actifs, participations
  totales, taux de participation moyen, récompenses offertes), onglets par
  statut, création (`/admin/sondages/creer`, options de réponse
  dynamiques en JS), résultats détaillés avec barres de progression,
  panneau "Sondage à la une".
- **Client** (`/mon-compte/sondages`) : liste des sondages disponibles
  (badge "Nouveau"/"Répondu"), page de vote à choix unique, affichage des
  résultats une fois voté (avec mise en évidence du choix personnel).
- **Vote unique garanti par la base de données** : contrainte
  `UNIQUE(poll_id, user_id)` sur `poll_votes` — une tentative de double
  vote lève une erreur SQL 23000, interceptée et transformée en message
  "Vous avez déjà participé à ce sondage", sans jamais recompter le vote.
  **Testé et vérifié** : après un premier vote, une seconde tentative
  n'incrémente pas le compteur (vérifié en base).
- **Récompenses automatiques**, appliquées dans la même transaction que
  le vote (`PollVote::castVote()`) : points fidélité crédités sur
  `users.loyalty_points`, ou crédit ajouté au portefeuille avec une ligne
  `wallet_transactions` correspondante. **Testé et vérifié** : +10 points
  et +0,50 € crédités exactement comme configuré sur le sondage.

## 3septies. Zonage & Proximité (Lot 7)

- **Admin** (`/admin/zonage`) : création de campagne (rayon en mètres via
  slider, plage horaire, jours de diffusion, segment cible, offre liée
  facultative, message), carte interactive (Leaflet + OpenStreetMap,
  nécessite un accès Internet côté navigateur du poste admin), liste des
  campagnes avec compteurs envoyées/utilisées et taux de conversion.
- **Détection côté client** : le navigateur envoie sa position GPS
  (`navigator.geolocation`) toutes les 60 secondes à
  `POST /mon-compte/proximite/verifier`, uniquement si le client a activé
  `geolocation_opt_in` à l'inscription. Le serveur calcule la distance
  réelle par la **formule de Haversine** (`ProximityCampaign::distanceMeters()`)
  et vérifie que le jour, l'heure et le segment du client correspondent à
  une campagne active. **Testé et vérifié avec de vraies coordonnées GPS** :
  position sur le commerce → correspondance immédiate (distance 0 m) ;
  position à Paris (~103 km) → distance calculée à 102 778 m, hors zone,
  aucune correspondance.
- **Activation** (`POST /mon-compte/proximite/{id}/profiter`) : incrémente
  le compteur d'utilisation de la campagne et, si une offre est liée,
  génère automatiquement un code de réduction réutilisable en caisse (même
  mécanisme que le Lot 6).
- **Respect de la vie privée** : aucune position GPS n'est stockée en
  base — seule la distance instantanée est calculée puis oubliée ; le
  client peut désactiver l'option à tout moment (paramètre à venir dans
  "Mes informations", Lot 10).

## 3octies. Avis Google, Statistiques, Facturation, Paramètres (Lot 10)

- **Avis Google** (`/admin/avis-google`) : note moyenne, répartition par
  étoile, ajout manuel d'avis (la synchronisation automatique via l'API
  Google Places nécessitera une clé API à fournir), suppression.
- **Statistiques** (`/admin/statistiques`) : graphiques Chart.js (CDN)
  construits à partir de vraies requêtes agrégées sur les données
  existantes — aucune table dédiée : activité portefeuille (14 derniers
  jours), répartition des moyens de paiement, nouveaux clients par mois,
  top 5 clients par dépense.
- **Facturation** (`/admin/facturation`) : chaque recharge par carte
  bancaire génère automatiquement une "facture" (dérivée de
  `wallet_transactions`, pas de table dupliquée). Facture imprimable au
  format A4 via `window.print()` du navigateur (Enregistrer en PDF),
  volontairement sans dépendance Composer/PDF supplémentaire.
- **Paramètres** (`/admin/parametres`) : les informations du commerce
  (nom, adresse, téléphone, horaires, réseaux sociaux, coordonnées GPS)
  sont désormais éditables sans toucher au code, via une table `settings`
  clé/valeur. `Controller::buildShopData()` fusionne ces valeurs par-dessus
  `config/app.php` à chaque requête. **Testé et vérifié** : une
  modification du nom du commerce depuis `/admin/parametres` est
  immédiatement répercutée sur la page d'accueil publique.

## 4. Dernier lot restant

**Messages & WhatsApp** (Lot 9) : véritable intégration API WhatsApp
Business (Meta Cloud API). Actuellement, tous les messages générés par
l'application (offres, confirmations, campagnes de proximité) sont déjà
journalisés dans la table `whatsapp_messages` — il ne reste qu'à brancher
l'appel HTTP réel vers l'API Meta, ce qui nécessitera un compte
WhatsApp Business et un token d'accès à fournir.

## 5. Notes techniques

- Les images de démonstration (façade, planche à saucisson) pointent vers
  Unsplash à titre d'exemple — à remplacer par vos propres photos dans
  `public/assets/img/`.
- Le QR code du bloc WhatsApp est généré proceduralement en SVG à titre de
  démonstration visuelle. En production, remplacez-le par une vraie
  librairie de génération QR (ex. `endroid/qr-code` via Composer, ou une
  API externe).
- Le widget météo interroge l'API publique et gratuite
  [Open-Meteo](https://open-meteo.com/) (aucune clé API requise).
