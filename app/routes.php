<?php

declare(strict_types=1);

/**
 * Définition des routes
 * @var \App\Core\Router $router
 */

use App\Controllers\HomeController;
use App\Controllers\BarController;
use App\Controllers\TabacController;
use App\Controllers\PmuController;
use App\Controllers\FdjController;
use App\Controllers\PresseController;
use App\Controllers\ActualitesController;
use App\Controllers\ServicesController;
use App\Controllers\ContactController;
use App\Controllers\ReservationController;
use App\Controllers\LegalController;
use App\Controllers\Client\ClientDashboardController;
use App\Controllers\Client\WalletController;
use App\Controllers\Client\ClientPlaceholderController;
use App\Controllers\Auth\LoginController;
use App\Controllers\Auth\RegisterController;
use App\Controllers\Auth\LogoutController;
use App\Controllers\Auth\AdminLoginController;
use App\Controllers\Auth\AdminPasswordResetController;
use App\Controllers\Admin\AdminDashboardController;
use App\Controllers\Admin\AdminClientController;
use App\Controllers\Admin\AdminOfferController;
use App\Controllers\Admin\AdminOfferScanController;
use App\Controllers\Admin\AdminPollController;
use App\Controllers\Admin\AdminProximityController;
use App\Controllers\Admin\AdminReviewController;
use App\Controllers\Admin\AdminStatisticsController;
use App\Controllers\Admin\AdminBillingController;
use App\Controllers\Admin\AdminSettingsController;
use App\Controllers\Admin\AdminMaintenanceController;
use App\Controllers\Admin\AdminImageController;
use App\Controllers\Admin\AdminGoogleAnalyticsController;
use App\Controllers\Admin\AdminGoogleOAuthController;
use App\Controllers\Admin\AdminPlaceholderController;
use App\Controllers\Admin\AdminEstablishmentController;
use App\Controllers\Admin\AdminServiceController;
use App\Controllers\Admin\AdminNewsController;
use App\Controllers\Admin\AdminBarController;
use App\Controllers\Admin\AdminTabacController;
use App\Controllers\Admin\AdminPmuController;
use App\Controllers\Admin\AdminFdjController;
use App\Controllers\Admin\AdminMessageController;
use App\Controllers\Admin\AdminEmployeeController;
use App\Controllers\Admin\AdminReservationController;
use App\Controllers\Admin\AdminLotteryController;
use App\Controllers\LotteryPublicController;
use App\Controllers\Client\ClientOfferController;
use App\Controllers\Client\ClientLotteryController;
use App\Controllers\Client\PollController;
use App\Controllers\Client\ProximityController;

// --- Site public ---
$router->get('/', HomeController::class, 'index');
$router->get('/le-bar', BarController::class, 'index');
$router->get('/tabac', TabacController::class, 'index');
$router->get('/pmu', PmuController::class, 'index');
$router->get('/fdj', FdjController::class, 'index');
$router->get('/presse', PresseController::class, 'index');
$router->get('/nos-services', ServicesController::class, 'index');
$router->get('/actualites', ActualitesController::class, 'index');
$router->get('/actualites/{slug}', ActualitesController::class, 'show');
$router->get('/contact', ContactController::class, 'index');
$router->post('/contact', ContactController::class, 'send');
$router->get('/reservation', ReservationController::class, 'index');
$router->post('/reservation', ReservationController::class, 'store');
$router->get('/loterie/{token}', LotteryPublicController::class, 'show');
$router->post('/loterie/{token}', LotteryPublicController::class, 'store');
$router->get('/mentions-legales', LegalController::class, 'mentionsLegales');
$router->get('/cgu', LegalController::class, 'cgu');
$router->get('/cgv', LegalController::class, 'cgv');
$router->get('/politique-de-confidentialite', LegalController::class, 'confidentialite');

// --- Authentification client ---
$router->get('/inscription', RegisterController::class, 'index');
$router->post('/inscription', RegisterController::class, 'store');
$router->get('/connexion', LoginController::class, 'index');
$router->post('/connexion', LoginController::class, 'store');
$router->post('/deconnexion', LogoutController::class, 'destroy');

// --- Espace client (protégé, voir Middleware::requireAuth) ---
$router->get('/mon-compte', ClientDashboardController::class, 'index');
$router->get('/mon-compte/transactions', WalletController::class, 'transactions');
$router->get('/mon-compte/avantages', WalletController::class, 'rewards');
$router->get('/mon-compte/parrainage', WalletController::class, 'referral');
$router->get('/mon-compte/offres', ClientOfferController::class, 'index');
$router->get('/mon-compte/loterie', ClientLotteryController::class, 'index');
$router->post('/mon-compte/loterie/{id}/participer', ClientLotteryController::class, 'participate');
$router->get('/mon-compte/sondages', PollController::class, 'index');
$router->get('/mon-compte/sondages/{id}', PollController::class, 'show');
$router->post('/mon-compte/sondages/{id}/voter', PollController::class, 'vote');
$router->post('/mon-compte/proximite/verifier', ProximityController::class, 'check');
$router->post('/mon-compte/proximite/{id}/profiter', ProximityController::class, 'claim');

// Routes "prochainement" pour les autres sections du menu client
// IMPORTANT : doit rester déclarée en dernier pour ne pas intercepter les routes ci-dessus
$router->get('/mon-compte/{section}', ClientPlaceholderController::class, 'show');

// --- Authentification admin ---
$router->get('/admin/connexion', AdminLoginController::class, 'index');
$router->post('/admin/connexion', AdminLoginController::class, 'store');
$router->get('/admin/mot-de-passe-oublie', AdminPasswordResetController::class, 'forgot');
$router->post('/admin/mot-de-passe-oublie', AdminPasswordResetController::class, 'sendLink');
$router->get('/admin/reinitialiser-mot-de-passe/{token}', AdminPasswordResetController::class, 'reset');
$router->post('/admin/reinitialiser-mot-de-passe/{token}', AdminPasswordResetController::class, 'update');

// --- Back-office (protégé, voir Middleware::requireRole('admin')) ---
$router->get('/admin', AdminDashboardController::class, 'index');
$router->get('/admin/etablissement', AdminEstablishmentController::class, 'index');
$router->post('/admin/etablissement', AdminEstablishmentController::class, 'update');
$router->get('/admin/clients', AdminClientController::class, 'index');
$router->get('/admin/clients/export', AdminClientController::class, 'export');
$router->get('/admin/clients/{id}', AdminClientController::class, 'show');
$router->post('/admin/clients/{id}', AdminClientController::class, 'update');
$router->post('/admin/clients/{id}/wallet', AdminClientController::class, 'adjustWallet');
$router->post('/admin/clients/{id}/supprimer', AdminClientController::class, 'destroy');
$router->post('/admin/clients/{id}/message', AdminClientController::class, 'sendMessage');

// --- Le Bar (bières, planches & softs affichés sur /le-bar) ---
$router->get('/admin/bar', AdminBarController::class, 'index');
$router->get('/admin/bar/creer', AdminBarController::class, 'createDrink');
$router->post('/admin/bar', AdminBarController::class, 'storeDrink');

// Planches à partager (CRUD complet) — routes statiques déclarées avant
// /admin/bar/{id} pour ne pas être interceptées par son paramètre dynamique.
$router->get('/admin/bar/planches/creer', AdminBarController::class, 'createPlanche');
$router->post('/admin/bar/planches', AdminBarController::class, 'storePlanche');
$router->get('/admin/bar/planches/{id}/modifier', AdminBarController::class, 'editPlanche');
$router->post('/admin/bar/planches/{id}', AdminBarController::class, 'updatePlanche');
$router->post('/admin/bar/planches/{id}/statut', AdminBarController::class, 'togglePlanche');
$router->post('/admin/bar/planches/{id}/supprimer', AdminBarController::class, 'destroyPlanche');

// Softs, cafés & boissons chaudes (CRUD complet) — mêmes précautions d'ordre.
$router->get('/admin/bar/softs/creer', AdminBarController::class, 'createSoft');
$router->post('/admin/bar/softs', AdminBarController::class, 'storeSoft');
$router->get('/admin/bar/softs/{id}/modifier', AdminBarController::class, 'editSoft');
$router->post('/admin/bar/softs/{id}', AdminBarController::class, 'updateSoft');
$router->post('/admin/bar/softs/{id}/supprimer', AdminBarController::class, 'destroySoft');

$router->get('/admin/bar/{id}/modifier', AdminBarController::class, 'editDrink');
$router->post('/admin/bar/{id}', AdminBarController::class, 'updateDrink');
$router->post('/admin/bar/{id}/supprimer', AdminBarController::class, 'destroyDrink');

// --- Tabac (catégories & services affichés sur /tabac) ---
$router->get('/admin/tabac', AdminTabacController::class, 'index');
$router->get('/admin/tabac/categories/creer', AdminTabacController::class, 'createCategory');
$router->post('/admin/tabac/categories', AdminTabacController::class, 'storeCategory');
$router->get('/admin/tabac/categories/{id}/modifier', AdminTabacController::class, 'editCategory');
$router->post('/admin/tabac/categories/{id}', AdminTabacController::class, 'updateCategory');
$router->post('/admin/tabac/categories/{id}/statut', AdminTabacController::class, 'toggleCategory');
$router->post('/admin/tabac/categories/{id}/supprimer', AdminTabacController::class, 'destroyCategory');
$router->get('/admin/tabac/services/creer', AdminTabacController::class, 'createService');
$router->post('/admin/tabac/services', AdminTabacController::class, 'storeService');
$router->get('/admin/tabac/services/{id}/modifier', AdminTabacController::class, 'editService');
$router->post('/admin/tabac/services/{id}', AdminTabacController::class, 'updateService');
$router->post('/admin/tabac/services/{id}/supprimer', AdminTabacController::class, 'destroyService');

// --- PMU (catégories de paris & services affichés sur /pmu) ---
$router->get('/admin/pmu', AdminPmuController::class, 'index');
$router->get('/admin/pmu/categories/creer', AdminPmuController::class, 'createCategory');
$router->post('/admin/pmu/categories', AdminPmuController::class, 'storeCategory');
$router->get('/admin/pmu/categories/{id}/modifier', AdminPmuController::class, 'editCategory');
$router->post('/admin/pmu/categories/{id}', AdminPmuController::class, 'updateCategory');
$router->post('/admin/pmu/categories/{id}/statut', AdminPmuController::class, 'toggleCategory');
$router->post('/admin/pmu/categories/{id}/supprimer', AdminPmuController::class, 'destroyCategory');
$router->get('/admin/pmu/services/creer', AdminPmuController::class, 'createService');
$router->post('/admin/pmu/services', AdminPmuController::class, 'storeService');
$router->get('/admin/pmu/services/{id}/modifier', AdminPmuController::class, 'editService');
$router->post('/admin/pmu/services/{id}', AdminPmuController::class, 'updateService');
$router->post('/admin/pmu/services/{id}/supprimer', AdminPmuController::class, 'destroyService');

// --- FDJ (catégories de jeux & services affichés sur /fdj) ---
$router->get('/admin/fdj', AdminFdjController::class, 'index');
$router->get('/admin/fdj/categories/creer', AdminFdjController::class, 'createCategory');
$router->post('/admin/fdj/categories', AdminFdjController::class, 'storeCategory');
$router->get('/admin/fdj/categories/{id}/modifier', AdminFdjController::class, 'editCategory');
$router->post('/admin/fdj/categories/{id}', AdminFdjController::class, 'updateCategory');
$router->post('/admin/fdj/categories/{id}/statut', AdminFdjController::class, 'toggleCategory');
$router->post('/admin/fdj/categories/{id}/supprimer', AdminFdjController::class, 'destroyCategory');
$router->get('/admin/fdj/services/creer', AdminFdjController::class, 'createService');
$router->post('/admin/fdj/services', AdminFdjController::class, 'storeService');
$router->get('/admin/fdj/services/{id}/modifier', AdminFdjController::class, 'editService');
$router->post('/admin/fdj/services/{id}', AdminFdjController::class, 'updateService');
$router->post('/admin/fdj/services/{id}/supprimer', AdminFdjController::class, 'destroyService');

// --- Services du quotidien (catalogue affiché sur /nos-services) ---
$router->get('/admin/services', AdminServiceController::class, 'index');
$router->get('/admin/services/creer', AdminServiceController::class, 'create');
$router->post('/admin/services', AdminServiceController::class, 'store');
$router->get('/admin/services/{id}/modifier', AdminServiceController::class, 'edit');
$router->post('/admin/services/{id}', AdminServiceController::class, 'update');
$router->post('/admin/services/{id}/statut', AdminServiceController::class, 'toggleStatus');
$router->post('/admin/services/{id}/supprimer', AdminServiceController::class, 'destroy');

// --- Actualités (billets affichés sur /actualites) ---
$router->get('/admin/actualites', AdminNewsController::class, 'index');
$router->get('/admin/actualites/creer', AdminNewsController::class, 'create');
$router->post('/admin/actualites', AdminNewsController::class, 'store');
$router->get('/admin/actualites/{id}/modifier', AdminNewsController::class, 'edit');
$router->post('/admin/actualites/{id}', AdminNewsController::class, 'update');
$router->post('/admin/actualites/{id}/statut', AdminNewsController::class, 'toggleStatus');
$router->post('/admin/actualites/{id}/supprimer', AdminNewsController::class, 'destroy');

// --- Offres & Avantages (Lot 6) ---
$router->get('/admin/offres', AdminOfferController::class, 'index');
$router->get('/admin/offres/creer', AdminOfferController::class, 'create');
$router->post('/admin/offres', AdminOfferController::class, 'store');
$router->post('/admin/offres/generer', AdminOfferController::class, 'generateCode');
$router->post('/admin/offres/envoyer', AdminOfferController::class, 'sendToClient');
$router->post('/admin/offres/{id}/statut', AdminOfferController::class, 'toggleStatus');
$router->get('/admin/offres/{id}/modifier', AdminOfferController::class, 'edit');
$router->post('/admin/offres/{id}', AdminOfferController::class, 'update');
$router->post('/admin/offres/{id}/supprimer', AdminOfferController::class, 'destroy');
$router->get('/admin/offres/scanner', AdminOfferScanController::class, 'index');
$router->post('/admin/offres/scanner/verifier', AdminOfferScanController::class, 'verify');
$router->post('/admin/offres/scanner/valider', AdminOfferScanController::class, 'redeem');

// --- Sondages & Votes (Lot 8) ---
$router->get('/admin/sondages', AdminPollController::class, 'index');
$router->get('/admin/sondages/creer', AdminPollController::class, 'create');
$router->post('/admin/sondages', AdminPollController::class, 'store');
$router->get('/admin/sondages/{id}/resultats', AdminPollController::class, 'results');
$router->post('/admin/sondages/{id}/statut', AdminPollController::class, 'toggleStatus');
$router->get('/admin/sondages/{id}/modifier', AdminPollController::class, 'edit');
$router->post('/admin/sondages/{id}', AdminPollController::class, 'update');
$router->post('/admin/sondages/{id}/supprimer', AdminPollController::class, 'destroy');

// --- Zonage & Proximité (Lot 7) ---
$router->get('/admin/zonage', AdminProximityController::class, 'index');
$router->post('/admin/zonage', AdminProximityController::class, 'store');
$router->post('/admin/zonage/{id}/statut', AdminProximityController::class, 'toggleStatus');
$router->get('/admin/zonage/{id}/modifier', AdminProximityController::class, 'edit');
$router->post('/admin/zonage/{id}', AdminProximityController::class, 'update');
$router->post('/admin/zonage/{id}/supprimer', AdminProximityController::class, 'destroy');

// --- Avis Google, Statistiques, Facturation, Paramètres (Lot 10) ---
$router->get('/admin/avis-google', AdminReviewController::class, 'index');

$router->get('/admin/statistiques', AdminStatisticsController::class, 'index');
$router->get('/admin/statistiques/export', AdminStatisticsController::class, 'export');
$router->get('/admin/google-analytics', AdminGoogleAnalyticsController::class, 'index');

// --- Connexion OAuth Google Analytics (compte de service indisponible) ---
$router->get('/admin/google-analytics/connecter', AdminGoogleOAuthController::class, 'connect');
$router->post('/admin/google-analytics/deconnecter', AdminGoogleOAuthController::class, 'disconnect');
$router->get('/callback/google/analytics', AdminGoogleOAuthController::class, 'callback');

$router->get('/admin/facturation', AdminBillingController::class, 'index');
$router->get('/admin/facturation/export', AdminBillingController::class, 'export');
$router->get('/admin/facturation/{id}', AdminBillingController::class, 'show');

$router->get('/admin/parametres', AdminSettingsController::class, 'index');
$router->post('/admin/parametres', AdminSettingsController::class, 'update');
$router->get('/admin/maintenance', AdminMaintenanceController::class, 'index');
$router->post('/admin/maintenance', AdminMaintenanceController::class, 'update');

$router->get('/admin/images', AdminImageController::class, 'index');
$router->post('/admin/images', AdminImageController::class, 'store');
$router->post('/admin/images/{slug}/supprimer', AdminImageController::class, 'destroy');

// --- Messages (boîte de réception omnicanale : contact, SMS, WhatsApp) ---
$router->get('/admin/messages', AdminMessageController::class, 'index');
$router->post('/admin/messages/nouveau', AdminMessageController::class, 'newConversation');
$router->post('/admin/messages/whatsapp/{userId}', AdminMessageController::class, 'sendWhatsapp');
$router->post('/admin/messages/sms/{userId}', AdminMessageController::class, 'sendSms');
$router->post('/admin/messages/contact/{id}/lu', AdminMessageController::class, 'toggleContactRead');
$router->post('/admin/messages/programmes/{id}/annuler', AdminMessageController::class, 'cancelScheduled');
$router->post('/admin/messages/clients/{userId}/etiquettes', AdminMessageController::class, 'addLabel');
$router->post('/admin/messages/etiquettes/{id}/supprimer', AdminMessageController::class, 'removeLabel');
$router->post('/admin/messages/clients/{userId}/notes', AdminMessageController::class, 'addNote');

// --- Employés ---
$router->get('/admin/employes', AdminEmployeeController::class, 'index');
$router->post('/admin/employes', AdminEmployeeController::class, 'store');
$router->post('/admin/employes/{id}', AdminEmployeeController::class, 'update');
$router->post('/admin/employes/{id}/statut', AdminEmployeeController::class, 'toggleStatus');
$router->post('/admin/employes/{id}/supprimer', AdminEmployeeController::class, 'destroy');

// --- Réservations ---
$router->get('/admin/reservations', AdminReservationController::class, 'index');
$router->post('/admin/reservations', AdminReservationController::class, 'store');
$router->post('/admin/reservations/{id}/statut', AdminReservationController::class, 'updateStatus');
$router->post('/admin/reservations/{id}/supprimer', AdminReservationController::class, 'destroy');
$router->post('/admin/reservations/{id}', AdminReservationController::class, 'update');

// --- Loterie ---
$router->get('/admin/loterie', AdminLotteryController::class, 'index');
$router->get('/admin/loterie/creer', AdminLotteryController::class, 'create');
$router->post('/admin/loterie', AdminLotteryController::class, 'store');
$router->post('/admin/loterie/{id}/statut', AdminLotteryController::class, 'toggleStatus');
$router->post('/admin/loterie/{id}/tirage', AdminLotteryController::class, 'draw');
$router->post('/admin/loterie/{id}/supprimer', AdminLotteryController::class, 'destroy');
$router->get('/admin/loterie/{id}/qrcode', AdminLotteryController::class, 'qrcode');
$router->get('/admin/loterie/{id}/qrcode.png', AdminLotteryController::class, 'qrcodeImage');

// Routes "prochainement" pour les autres sections du menu admin (Lots 6 à 10)
// IMPORTANT : doit rester déclarée en dernier pour ne pas intercepter les routes ci-dessus
$router->get('/admin/{section}', AdminPlaceholderController::class, 'show');
