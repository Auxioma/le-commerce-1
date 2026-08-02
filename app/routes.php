<?php

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
use App\Controllers\LegalController;
use App\Controllers\Client\ClientDashboardController;
use App\Controllers\Client\WalletController;
use App\Controllers\Client\ClientPlaceholderController;
use App\Controllers\Auth\LoginController;
use App\Controllers\Auth\RegisterController;
use App\Controllers\Auth\LogoutController;
use App\Controllers\Auth\AdminLoginController;
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
use App\Controllers\Admin\AdminImageController;
use App\Controllers\Admin\AdminPlaceholderController;
use App\Controllers\Admin\AdminMessageController;
use App\Controllers\Admin\AdminEmployeeController;
use App\Controllers\Client\ClientOfferController;
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
$router->get('/contact', ContactController::class, 'index');
$router->post('/contact', ContactController::class, 'send');
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
$router->post('/mon-compte/recharger', WalletController::class, 'recharge');
$router->get('/mon-compte/transactions', WalletController::class, 'transactions');
$router->get('/mon-compte/avantages', WalletController::class, 'rewards');
$router->get('/mon-compte/parrainage', WalletController::class, 'referral');
$router->get('/mon-compte/offres', ClientOfferController::class, 'index');
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

// --- Back-office (protégé, voir Middleware::requireRole('admin')) ---
$router->get('/admin', AdminDashboardController::class, 'index');
$router->get('/admin/clients', AdminClientController::class, 'index');
$router->get('/admin/clients/export', AdminClientController::class, 'export');
$router->get('/admin/clients/{id}', AdminClientController::class, 'show');
$router->post('/admin/clients/{id}', AdminClientController::class, 'update');
$router->post('/admin/clients/{id}/wallet', AdminClientController::class, 'adjustWallet');
$router->post('/admin/clients/{id}/supprimer', AdminClientController::class, 'destroy');
$router->post('/admin/clients/{id}/message', AdminClientController::class, 'sendMessage');

// --- Offres & Avantages (Lot 6) ---
$router->get('/admin/offres', AdminOfferController::class, 'index');
$router->get('/admin/offres/creer', AdminOfferController::class, 'create');
$router->post('/admin/offres', AdminOfferController::class, 'store');
$router->post('/admin/offres/generer', AdminOfferController::class, 'generateCode');
$router->post('/admin/offres/envoyer', AdminOfferController::class, 'sendToClient');
$router->post('/admin/offres/{id}/statut', AdminOfferController::class, 'toggleStatus');
$router->get('/admin/offres/scanner', AdminOfferScanController::class, 'index');
$router->post('/admin/offres/scanner/verifier', AdminOfferScanController::class, 'verify');
$router->post('/admin/offres/scanner/valider', AdminOfferScanController::class, 'redeem');

// --- Sondages & Votes (Lot 8) ---
$router->get('/admin/sondages', AdminPollController::class, 'index');
$router->get('/admin/sondages/creer', AdminPollController::class, 'create');
$router->post('/admin/sondages', AdminPollController::class, 'store');
$router->get('/admin/sondages/{id}/resultats', AdminPollController::class, 'results');
$router->post('/admin/sondages/{id}/statut', AdminPollController::class, 'toggleStatus');

// --- Zonage & Proximité (Lot 7) ---
$router->get('/admin/zonage', AdminProximityController::class, 'index');
$router->post('/admin/zonage', AdminProximityController::class, 'store');
$router->post('/admin/zonage/{id}/statut', AdminProximityController::class, 'toggleStatus');

// --- Avis Google, Statistiques, Facturation, Paramètres (Lot 10) ---
$router->get('/admin/avis-google', AdminReviewController::class, 'index');
$router->post('/admin/avis-google', AdminReviewController::class, 'store');
$router->post('/admin/avis-google/{id}/supprimer', AdminReviewController::class, 'destroy');

$router->get('/admin/statistiques', AdminStatisticsController::class, 'index');

$router->get('/admin/facturation', AdminBillingController::class, 'index');
$router->get('/admin/facturation/{id}', AdminBillingController::class, 'show');

$router->get('/admin/parametres', AdminSettingsController::class, 'index');
$router->post('/admin/parametres', AdminSettingsController::class, 'update');

$router->get('/admin/images', AdminImageController::class, 'index');
$router->post('/admin/images', AdminImageController::class, 'store');
$router->post('/admin/images/{slug}/supprimer', AdminImageController::class, 'destroy');

// --- Messages (boîte de réception : contact + WhatsApp) ---
$router->get('/admin/messages', AdminMessageController::class, 'index');
$router->post('/admin/messages/whatsapp/{userId}', AdminMessageController::class, 'sendWhatsapp');
$router->post('/admin/messages/contact/{id}/lu', AdminMessageController::class, 'toggleContactRead');

// --- Employés ---
$router->get('/admin/employes', AdminEmployeeController::class, 'index');
$router->post('/admin/employes', AdminEmployeeController::class, 'store');
$router->post('/admin/employes/{id}', AdminEmployeeController::class, 'update');
$router->post('/admin/employes/{id}/statut', AdminEmployeeController::class, 'toggleStatus');
$router->post('/admin/employes/{id}/supprimer', AdminEmployeeController::class, 'destroy');

// Routes "prochainement" pour les autres sections du menu admin (Lots 6 à 10)
// IMPORTANT : doit rester déclarée en dernier pour ne pas intercepter les routes ci-dessus
$router->get('/admin/{section}', AdminPlaceholderController::class, 'show');
