# Revue du module Admin ΓÇö Onglets de la sidebar

Ce document liste **chaque entr├⌐e de la sidebar admin** (`app/Views/partials/admin-sidebar.php`), ce qu'elle fait r├⌐ellement (contr├┤leur/route/vue), ce qui manque, et mon avis. Sert de base de travail pour la revue onglet par onglet.

L├⌐gende statut : Γ£à Complet ┬╖ ≡ƒƒí Fonctionnel mais limit├⌐ ┬╖ ≡ƒö┤ Lecture seule / faible valeur ┬╖ ΓÜá∩╕Å Bug ou incoh├⌐rence connue

---

## Section ┬½ G├⌐n├⌐ral ┬╗

### 1. Tableau de bord (`/admin`)
- **Contr├┤leur** : `AdminDashboardController::index`
- **Fait** : Agr├¿ge des KPIs (clients, portefeuilles, r├⌐servations, offres, loterie, sondages), graphique inscriptions 7j, r├⌐partition par outil, fil d'activit├⌐ r├⌐cente (6 derniers ├⌐v├⌐nements tous types confondus), derniers clients inscrits, derni├¿res recharges, top offres, sondages actifs, prochaine loterie.
- **Statut** : Γ£à Complet en lecture. Aucune action (pas de bouton d'action m├⌐tier sur cette page, uniquement des liens vers les autres modules).
- **Manque** : pas de s├⌐lecteur de p├⌐riode (fixe ├á 7 jours / ce mois), pas de personnalisation/drag des widgets.
- **Avis** : Bonne vue d'ensemble, RAS pour un tableau de bord v1.

### 2. Mon ├⌐tablissement (`/admin/etablissement`) ΓÇö Γ£à REFONTE FAITE
- **Contr├┤leur** : `AdminEstablishmentController` (index/update) ΓÇö nouveau contr├┤leur d├⌐di├⌐, routes explicites d├⌐clar├⌐es avant le catch-all.
- **Fait (apr├¿s refonte)** :
  - Formulaire d'├⌐dition complet : identit├⌐ (nom, t├⌐l├⌐phone, WhatsApp, e-mail), adresse & localisation (avec carte Leaflet interactive ├á marqueur d├⌐pla├ºable pour ajuster lat/long), Street View, horaires (avec badge "Aujourd'hui" dynamique selon le jour de la semaine), r├⌐seaux sociaux.
  - Aper├ºu client en temps r├⌐el (carte r├⌐capitulative) + liens crois├⌐s vers Zonage/Proximit├⌐, Messages, Param├¿tres.
  - 4 KPIs conserv├⌐s (clients, offres actives, r├⌐alisations du mois, ├⌐conomies).
  - Validation serveur (nom obligatoire, e-mail valide si renseign├⌐, lat/long num├⌐riques).
- **Changements connexes** : les champs Identit├⌐/Adresse/Horaires/R├⌐seaux sociaux ont ├⌐t├⌐ **retir├⌐s de Param├¿tres** (`AdminSettingsController` + `admin/settings/index.php`) pour ├⌐viter deux formulaires concurrents sur les m├¬mes donn├⌐es. Param├¿tres ne g├¿re plus que Mentions l├⌐gales & h├⌐bergeur + Google Analytics, avec un lien vers Mon ├⌐tablissement.
- **Manque** : pas encore de test d'adresse (g├⌐ocodage automatique depuis l'adresse texte vers lat/long).
- **Avis** : Doublon avec Param├¿tres r├⌐solu ΓÇö page d├⌐sormais pleinement fonctionnelle et ├á sa place logique dans la sidebar.

### 3. Services du quotidien (`/admin/services`) ΓÇö Γ£à REFONTE FAITE (Lot 18)
- **Contr├┤leur** : `AdminServiceController` (index/create/store/edit/update/toggleStatus/destroy) ΓÇö nouveau contr├┤leur d├⌐di├⌐, routes explicites d├⌐clar├⌐es avant le catch-all.
- **Mod├¿le** : `App\Models\Service` (table `services`, migration `database/migration_lot18_services_catalog.sql`, reprend les 8 services historiquement cod├⌐s en dur avec les m├¬mes slugs que `config/image_slots.php` pour pr├⌐server la continuit├⌐ des photos d├⌐j├á upload├⌐es).
- **Fait (apr├¿s refonte)** :
  - CRUD complet : cr├⌐ation/├⌐dition avec nom, description, choix d'ic├┤ne (liste de pictos + statut publi├⌐/inactif), suppression avec confirmation.
  - Liste avec compteurs (actifs/inactifs/nouveaux ce mois-ci) et bascule rapide de statut.
  - La page publique `/nos-services` (`ServicesController`) lit d├⌐sormais `Service::listActiveOrdered()` au lieu d'un tableau statique ΓÇö un service d├⌐sactiv├⌐ en admin dispara├«t imm├⌐diatement du site public.
- **Manque** : les nouveaux services cr├⌐├⌐s depuis l'admin n'ont pas d'emplacement dans `config/image_slots.php`, donc pas de photo d├⌐di├⌐e uploadable via `/admin/images` sans intervention manuelle sur ce fichier (les 8 services historiques, eux, en b├⌐n├⌐ficient toujours).
- **Avis** : Onglet d├⌐sormais fonctionnel et ├á sa place ΓÇö le catalogue public est pilot├⌐ depuis l'admin, ce qui n'├⌐tait pas le cas avant.

### 4. Photos du site (`/admin/images`)
- **Contr├┤leur** : `AdminImageController` (index/store/destroy)
- **Fait** : Upload/remplacement/suppression d'images par emplacement (`config/image_slots.php`), validation MIME (jpg/png/webp), taille max 5 Mo, nettoyage de l'ancien fichier ├á chaque remplacement.
- **Statut** : Γ£à Complet et robuste.
- **Manque** : Pas de recadrage/aper├ºu avant upload, pas d'historique des anciennes photos.
- **Avis** : Bien impl├⌐ment├⌐, rien ├á signaler.

### 5. Employ├⌐s (`/admin/employes`)
- **Contr├┤leur** : `AdminEmployeeController` (index/store/update/toggleStatus/destroy)
- **Fait** : CRUD complet (ajout, modification, activation/d├⌐sactivation, suppression), validation email/champs obligatoires.
- **Statut** : ≡ƒƒí CRUD fonctionnel mais c'est un **simple annuaire RH** : aucun compte de connexion (pas d'email/mot de passe li├⌐ ├á `users`), donc pas de gestion des droits/permissions par employ├⌐.
- **Manque** : Pas de lien avec un compte utilisateur/authentification, pas d'historique de pr├⌐sence/horaires.
- **Avis** : Suffisant pour un annuaire, mais s'il faut un jour donner un acc├¿s back-office ├á un employ├⌐ (autre que l'admin), il faudra une vraie gestion de comptes.

---

## Section ┬½ Clients ┬╗

### 6. Clients inscrits (`/admin/clients`)
- **Contr├┤leur** : `AdminClientController` (index/export/show/update/adjustWallet/destroy/sendMessage)
- **Fait** : Liste pagin├⌐e + filtres (recherche, statut, segment, dates), export CSV complet (BOM UTF-8 pour Excel), fiche client d├⌐taill├⌐e (infos, portefeuille, historique pagin├⌐, parrainages), ├⌐dition fiche, cr├⌐dit/d├⌐bit manuel du portefeuille (transaction SQL atomique + bonus fid├⌐lit├⌐ +2Γé¼ sur recharge 50Γé¼), suppression douce (`deleted_at`), envoi WhatsApp simul├⌐ (├⌐crit en base, pas d'API r├⌐elle).
- **Statut** : Γ£à Tr├¿s complet, le module le plus mature du back-office.
- **Manque** : L'envoi WhatsApp/SMS est simul├⌐ (persistance en base uniquement) ΓÇö coh├⌐rent avec la contrainte "100% PHP natif" sans API externe pour l'instant.
- **Avis** : Excellent niveau de finition, ├á utiliser comme r├⌐f├⌐rence pour les autres modules.

### 7. Portefeuille client (`/admin/portefeuilles`)
- **Contr├┤leur** : `AdminPlaceholderController::show('portefeuilles')` (pas de contr├┤leur d├⌐di├⌐)
- **Fait** : 4 KPIs (solde total, nb portefeuilles, recharges du mois, top clients) + tableau des derni├¿res transactions. 100% lecture.
- **Statut** : ≡ƒö┤ Lecture seule ΓÇö toutes les vraies actions (cr├⌐diter/d├⌐biter) se font en fait depuis la fiche client (`/admin/clients/{id}`).
- **Manque** : Pas de lien direct depuis une ligne de transaction vers la fiche client concern├⌐e (pas de `<a>` cliquable), pas de filtre par p├⌐riode/client.
- **Avis** : Fonctionne comme un mini tableau de bord "Portefeuilles" ΓÇö coh├⌐rent avec Mon ├⌐tablissement/Services (tous 3 servis par le m├¬me `AdminPlaceholderController`). Gagnerait ├á avoir des liens cliquables vers les fiches clients.

### 8. Messages (`/admin/messages`)
- **Contr├┤leur** : `AdminMessageController` (Lot 17, tout juste livr├⌐)
- **Fait** : Bo├«te de r├⌐ception omnicanale (contact/SMS/WhatsApp) avec onglets (r├⌐ception, envoy├⌐s, programm├⌐s, brouillons/archives/spam en placeholder), recherche + filtres canal/├⌐tiquette, conversation ├á 3 colonnes, r├⌐ponses rapides, programmation d'envoi, ├⌐tiquettes clients, notes internes, statistiques d'engagement, composeur "nouveau message".
- **Statut** : ≡ƒƒí Fonctionnel apr├¿s le correctif du bug de guillemets (`onclick`/`json_encode`). SMS/WhatsApp restent **simul├⌐s** (├⌐criture en base, pas d'API Twilio/Meta r├⌐elle).
- **Manque** :
  - Les messages "programm├⌐s" ne sont **jamais envoy├⌐s automatiquement** (pas de cron/t├óche planifi├⌐e) ΓÇö l'action `cancelScheduled` existe mais rien ne les fait passer en "envoy├⌐" ├á ├⌐ch├⌐ance.
  - Onglets "Brouillons", "Archives", "Spam" affichent un message "pas encore disponible" (pas de logique derri├¿re).
  - Pas de pi├¿ce jointe / m├⌐dia.
- **Avis** : Vient d'├¬tre pos├⌐, correctif de bug fait. Prochaine ├⌐tape logique : d├⌐cider si les messages programm├⌐s doivent ├¬tre trait├⌐s par une t├óche cron (hors p├⌐rim├¿tre actuel) ou rester une intention d├⌐clarative.

### 9. R├⌐servations (`/admin/reservations`)
- **Contr├┤leur** : `AdminReservationController` (index/updateStatus/destroy)
- **Fait** : Liste filtrable (statut, date), changement de statut (en attente/confirm├⌐e/annul├⌐e), suppression.
- **Statut** : ≡ƒƒí Fonctionnel mais **pas de cr├⌐ation de r├⌐servation c├┤t├⌐ admin** (uniquement via le formulaire public `/reservation`).
- **Manque** : Pas de saisie manuelle pour un client au t├⌐l├⌐phone, pas de vue calendrier.
- **Avis** : Correct pour une gestion r├⌐active, mais un admin ne peut pas cr├⌐er de r├⌐servation "├á la main" pour un client qui appelle ΓÇö ├á consid├⌐rer si c'est un besoin r├⌐el.

---

## Section ┬½ Fid├⌐lisation ┬╗

### 10. Offres & Avantages (`/admin/offres`)
- **Contr├┤leur** : `AdminOfferController` (index/create/store/toggleStatus/generateCode/sendToClient)
- **Fait** : Liste filtr├⌐e par statut (active/brouillons/expir├⌐es/toutes), cr├⌐ation d'offre, g├⌐n├⌐ration de code/QR pour un client donn├⌐, envoi WhatsApp simul├⌐ de l'offre, bascule active/brouillon.
- **Statut** : ΓÜá∩╕Å **Pas de route/action `update` ni `destroy`** ΓÇö une offre cr├⌐├⌐e ne peut ni ├¬tre modifi├⌐e, ni supprim├⌐e, seulement activ├⌐e/mise en brouillon.
- **Manque** : ├⌐dition et suppression d'offre.
- **Avis** : Lacune fonctionnelle r├⌐elle ├á combler si les offres sont amen├⌐es ├á ├⌐voluer apr├¿s cr├⌐ation (erreur de saisie, changement de valeur, etc.).

### 11. Scanner une offre (`/admin/offres/scanner`)
- **Contr├┤leur** : `AdminOfferScanController` (index/verify/redeem)
- **Fait** : Flux en 2 ├⌐tapes (v├⌐rification du code sans le consommer, puis validation effective), gestion des cas d├⌐j├á utilis├⌐/expir├⌐/introuvable, notification WhatsApp simul├⌐e au client apr├¿s validation.
- **Statut** : Γ£à Complet et bien pens├⌐ (s├⌐paration v├⌐rification/consommation).
- **Manque** : rien de bloquant identifi├⌐.
- **Avis** : Un des modules les mieux con├ºus, logique m├⌐tier claire.

### 12. Zonage & Proximit├⌐ (`/admin/zonage`)
- **Contr├┤leur** : `AdminProximityController` (index/store/toggleStatus)
- **Fait** : Cr├⌐ation de campagnes g├⌐olocalis├⌐es (rayon, plage horaire, jours, segment cible, offre li├⌐e, message), bascule active/en pause. Carte Leaflet en JS (n├⌐cessite Internet c├┤t├⌐ navigateur).
- **Statut** : ΓÜá∩╕Å **Pas d'├⌐dition ni de suppression** de campagne, seulement cr├⌐ation + toggle.
- **Manque** : update/destroy, pas d'historique d'envoi (`totalSent`/`totalUsed` existent au niveau mod├¿le mais pas de d├⌐tail par campagne dans la vue list├⌐e ici).
- **Avis** : M├¬me lacune que les Offres ΓÇö coh├⌐rence ├á am├⌐liorer sur l'ensemble du back-office (CRUD incomplet r├⌐current).

### 13. Sondages & Votes (`/admin/sondages`)
- **Contr├┤leur** : `AdminPollController` (index/create/store/results/toggleStatus)
- **Fait** : Cr├⌐ation de sondage avec options multiples et r├⌐compense, consultation des r├⌐sultats d├⌐taill├⌐s, bascule actif/termin├⌐, sondage "vedette" mis en avant.
- **Statut** : ΓÜá∩╕Å **Pas d'├⌐dition/suppression** d'un sondage ni de ses options une fois cr├⌐├⌐.
- **Manque** : update/destroy.
- **Avis** : Encore la m├¬me lacune de CRUD partiel ΓÇö pattern r├⌐current ├á traiter transversalement (Offres, Zonage, Sondages).

### 14. Loterie (`/admin/loterie`)
- **Contr├┤leur** : `AdminLotteryController` (index/create/store/toggleStatus/draw/destroy)
- **Fait** : Cr├⌐ation, activation/pause, **tirage au sort al├⌐atoire** avec notification WhatsApp simul├⌐e au gagnant, suppression.
- **Statut** : Γ£à Le CRUD le plus complet de la section Fid├⌐lisation (a un `destroy`, contrairement aux Offres/Zonage/Sondages).
- **Manque** : pas d'├⌐dition (seulement suppression/recr├⌐ation), pas d'historique des anciens gagnants visible ailleurs que dans la fiche loterie elle-m├¬me.
- **Avis** : Bon niveau, logique de tirage au sort solide.

### 15. Avis Google (`/admin/avis-google`)
- **Contr├┤leur** : `AdminReviewController` (index/store/destroy)
- **Fait** : Liste des avis (50 derniers), r├⌐partition par note, ajout manuel d'un avis, suppression.
- **Statut** : ΓÜá∩╕Å Trompeur : le nom "Avis Google" laisse penser ├á une synchronisation avec Google Business Profile, mais **les avis sont saisis manuellement en base**, aucune int├⌐gration API r├⌐elle.
- **Manque** : pas d'├⌐dition d'un avis existant, pas de vraie synchronisation Google.
- **Avis** : ├Ç clarifier avec l'utilisateur ΓÇö soit renommer (┬½ T├⌐moignages clients ┬╗) pour ├⌐viter la confusion, soit pr├⌐voir une vraie int├⌐gration Google Business Profile plus tard.

---

## Section ┬½ Pilotage ┬╗

### 16. Statistiques (`/admin/statistiques`)
- **Contr├┤leur** : `AdminStatisticsController::index`
- **Fait** : Activit├⌐ portefeuille (14j), r├⌐partition moyens de paiement, nouveaux clients par mois (6 mois), top clients par d├⌐pense, quelques KPIs globaux.
- **Statut** : ≡ƒö┤ Lecture seule, aucune action.
- **Manque** : pas d'export, pas de filtre de p├⌐riode personnalis├⌐.
- **Avis** : Correct comme page de reporting basique.

### 17. Google Analytics (`/admin/google-analytics`)
- **Contr├┤leur** : `AdminGoogleAnalyticsController::index`
- **Fait** : KPIs internes (clients, portefeuilles, r├⌐servations, offres, loterie, sondages, messages, avis, proximit├⌐), + **int├⌐gration GA4 r├⌐elle** si `ga4_property_id`/`ga4_service_account_json` configur├⌐s dans Param├¿tres (sessions, utilisateurs, pages vues, taux de rebond, dur├⌐e moyenne, conversions), carte Leaflet des clients ├á proximit├⌐.
- **Statut** : ≡ƒƒí Fonctionnel, mais d├⌐pend d'une configuration GA4 externe (compte de service Google) pour la partie analytics r├⌐elle ; sans config, `ga4Configured` est `false` et les blocs GA4 restent vides.
- **Manque** : pas de bouton "tester la connexion GA4" visible pour valider la config sans attendre le rendu de la page.
- **Avis** : Bonne architecture (graceful fallback si non configur├⌐), mais n├⌐cessite une vraie cl├⌐ de service GA4 pour ├¬tre pleinement utile ΓÇö ├á v├⌐rifier si l'utilisateur en dispose.

### 18. Facturation (`/admin/facturation`)
- **Contr├┤leur** : `AdminBillingController` (index/show)
- **Fait** : Liste pagin├⌐e des factures (revenus totaux, revenus du mois, nombre de factures du mois), vue facture imprimable (HTML, impression navigateur ΓåÆ PDF).
- **Statut** : ≡ƒö┤ 100% lecture ΓÇö pas de cr├⌐ation manuelle de facture, pas de filtre par statut/client/date, pas d'export CSV/PDF natif (uniquement impression navigateur).
- **Manque** : filtres, g├⌐n├⌐ration manuelle, export group├⌐.
- **Avis** : Suffisant si les factures sont g├⌐n├⌐r├⌐es automatiquement ailleurs (ex: ├á la recharge portefeuille) ; sinon manque clairement d'outils de gestion actifs.

### 19. Param├¿tres (`/admin/parametres`) ΓÇö recadr├⌐ suite ├á la refonte de Mon ├⌐tablissement
- **Contr├┤leur** : `AdminSettingsController` (index/update)
- **Fait** : Mentions l├⌐gales (forme juridique, SIRET, RCS, directeur de publication, h├⌐bergeur), configuration Google Analytics (GA4). Les champs d'identit├⌐/adresse/horaires/r├⌐seaux sociaux ont ├⌐t├⌐ d├⌐plac├⌐s vers `/admin/etablissement` pour ├⌐viter deux formulaires sur les m├¬mes donn├⌐es.
- **Statut** : Γ£à Fonctionnel, port├⌐e resserr├⌐e ├á ┬½ l├⌐gal + technique ┬╗.
- **Manque** : pas de test de connexion GA4 imm├⌐diat depuis ce formulaire.
- **Avis** : Page nettement plus lisible apr├¿s le recentrage ; peut encore ├¬tre scind├⌐e en 2 onglets (L├⌐gal / GA4) si besoin.

---

## Synth├¿se ΓÇö constats transversaux

1. **Pattern de CRUD incomplet r├⌐current** : Offres, Zonage/Proximit├⌐ et Sondages n'ont **ni update ni destroy** ΓÇö seulement cr├⌐ation + bascule de statut. Loterie est la seule exception avec un `destroy`. ├Ç harmoniser si l'utilisateur veut pouvoir corriger une erreur de saisie sans devoir d├⌐sactiver et recr├⌐er.
2. **2 pages "vitrines" en lecture seule servies par le m├¬me contr├┤leur g├⌐n├⌐rique** (`AdminPlaceholderController`) : Services du quotidien, Portefeuille client. Elles dupliquent des donn├⌐es d├⌐j├á visibles ailleurs (Offres, Clients) sans action propre ΓÇö ├á fusionner ou enrichir. *(Mon ├⌐tablissement a ├⌐t├⌐ sorti de ce lot et refondu en page ├⌐ditable d├⌐di├⌐e.)*
3. **Toute communication sortante (WhatsApp/SMS/e-mail) est simul├⌐e** (├⌐criture en base, pas d'appel API r├⌐el) ΓÇö coh├⌐rent avec la contrainte actuelle "100% PHP natif", mais ├á garder en t├¬te pour la mise en production r├⌐elle.
4. **"Avis Google" n'est pas connect├⌐ ├á Google** ΓÇö nom potentiellement trompeur pour l'utilisateur final.
5. **Aucune t├óche planifi├⌐e (cron)** : les messages programm├⌐s (Lot 17) ne partent jamais tout seuls ├á l'heure pr├⌐vue.

---

*Document g├⌐n├⌐r├⌐ pour servir de support ├á la revue onglet par onglet demand├⌐e par l'utilisateur. Prochaine ├⌐tape : passer en revue chaque module un par un pour prioriser les corrections/├⌐volutions.*
