<?php

use App\Models\ContactMessage;

$unreadMessages = ContactMessage::countUnread();

$isActive = fn(string $route): bool => $currentUri === $route;
$isIn     = fn(string $route): bool => str_starts_with($currentUri, $route);

$itemBase    = 'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors';
$itemDefault = $itemBase . ' text-gray-300 hover:bg-white/5 hover:text-white';
$itemActive  = $itemBase . ' bg-[#2563eb] text-white';
$itemHome    = $itemBase . ' bg-brand-500 text-white hover:bg-brand-600';

require __DIR__ . '/admin-sidebar-icons.php';

$clientsOpen   = $isIn('/admin/clients') || $isIn('/admin/portefeuilles');
$clientsActive = $clientsOpen;
$fidelOpen     = $isIn('/admin/offres') || $isIn('/admin/zonage') || $isIn('/admin/loterie');
$fidelActive   = $fidelOpen;
?>
<div id="admin-sidebar-backdrop" class="hidden fixed inset-0 bg-black/40 z-40 lg:hidden"></div>
<aside id="admin-sidebar" class="fixed lg:sticky top-0 left-0 z-50 flex flex-col w-80 shrink-0 bg-slate-950 text-white/90 h-screen overflow-y-auto -translate-x-full lg:translate-x-0 transition-transform duration-200 shadow-2xl">
  <style>
    .sidebar-group > summary::-webkit-details-marker { display: none; }
    .sidebar-group > summary { list-style: none; }
    .sidebar-group[open] .chevron { transform: rotate(180deg); }
  </style>

  <div class="px-6 py-5 border-b border-white/10">
    <?php $sidebarLogoUrl = siteImage('logo_site', ''); ?>
    <a href="<?= BASE_PATH ?>/admin" class="block sidebar-logo">
      <?php if ($sidebarLogoUrl): ?>
        <img src="<?= htmlspecialchars($sidebarLogoUrl) ?>" alt="<?= htmlspecialchars($shop['name']) ?>" class="h-9 w-auto sidebar-logo-text">
      <?php else: ?>
        <span class="font-logo text-[28px] text-brand-500 -mb-1 sidebar-logo-text block"><?= htmlspecialchars($shop['name']) ?></span>
        <span class="text-[10px] tracking-[0.18em] text-gray-500 font-medium sidebar-logo-tagline block">BAR · TABAC · PMU · FDJ · PRESSE</span>
      <?php endif; ?>
    </a>
  </div>

  <nav class="flex-1 px-4 py-5 space-y-1">
    <a href="<?= BASE_PATH ?>/admin" class="<?= $itemHome ?>">
      <?= $icons['home'] ?>
      <span class="flex-1">Accueil</span>
    </a>

    <p class="px-3 mt-6 mb-2 text-[10px] font-bold uppercase tracking-widest text-gray-500">Gestion</p>
    <a href="<?= BASE_PATH ?>/admin/services" class="<?= $itemDefault ?>">
      <?= $icons['bar'] ?>
      <span class="flex-1">Bar</span>
    </a>
    <a href="<?= BASE_PATH ?>/admin/services" class="<?= $itemDefault ?>">
      <?= $icons['tabac'] ?>
      <span class="flex-1">Tabac</span>
    </a>
    <a href="<?= BASE_PATH ?>/admin/services" class="<?= $itemDefault ?>">
      <?= $icons['jeux'] ?>
      <span class="flex-1">Jeux & Services</span>
    </a>
    <a href="<?= BASE_PATH ?>/admin/services" class="<?= $itemDefault ?>">
      <?= $icons['pmu'] ?>
      <span class="flex-1">PMU</span>
    </a>
    <a href="<?= BASE_PATH ?>/admin/services" class="<?= $itemDefault ?>">
      <?= $icons['nirio'] ?>
      <span class="flex-1">NIRIO</span>
    </a>

    <a href="<?= BASE_PATH ?>/admin/employes" class="<?= $isActive('/admin/employes') ? $itemActive : $itemDefault ?>">
      <?= $icons['employes'] ?>
      <span class="flex-1">Employés</span>
    </a>

    <details class="sidebar-group mt-1" <?= $clientsOpen ? 'open' : '' ?>>
      <summary class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors cursor-pointer select-none <?= $clientsActive ? $itemActive : $itemDefault ?>">
        <?= $icons['users'] ?>
        <span class="flex-1">Clients & Prestataires</span>
        <?= $icons['chevron'] ?>
      </summary>
      <div class="pl-4 pr-1 mt-1 space-y-1 border-l border-white/10 ml-5">
        <a href="<?= BASE_PATH ?>/admin/clients" class="<?= $isIn('/admin/clients') ? $itemActive : $itemDefault ?>">
          <?= $icons['client-list'] ?>
          <span class="flex-1">Clients Inscrits</span>
        </a>
        <a href="<?= BASE_PATH ?>/admin/portefeuilles" class="<?= $isIn('/admin/portefeuilles') ? $itemActive : $itemDefault ?>">
          <?= $icons['wallet'] ?>
          <span class="flex-1">Portefeuille Client</span>
        </a>
      </div>
    </details>

    <a href="<?= BASE_PATH ?>/admin/reservations" class="<?= $isActive('/admin/reservations') ? $itemActive : $itemDefault ?>">
      <?= $icons['resa'] ?>
      <span class="flex-1">Réservations</span>
    </a>

    <a href="<?= BASE_PATH ?>/admin/offres" class="<?= $isActive('/admin/offres') ? $itemActive : $itemDefault ?>">
      <?= $icons['marketing'] ?>
      <span class="flex-1">Marketing</span>
    </a>

    <a href="<?= BASE_PATH ?>/admin/images" class="<?= $isActive('/admin/images') ? $itemActive : $itemDefault ?>">
      <?= $icons['content'] ?>
      <span class="flex-1">Contenu du site</span>
    </a>

    <a href="<?= BASE_PATH ?>/admin/messages" class="<?= $isActive('/admin/messages') ? $itemActive : $itemDefault ?>">
      <?= $icons['messages'] ?>
      <span class="flex-1">Messages</span>
      <?php if ($unreadMessages > 0): ?>
        <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full bg-red-500 text-white text-[10px] font-bold"><?= $unreadMessages ?></span>
      <?php endif; ?>
    </a>

    <a href="<?= BASE_PATH ?>/admin/avis-google" class="<?= $isActive('/admin/avis-google') ? $itemActive : $itemDefault ?>">
      <?= $icons['avis'] ?>
      <span class="flex-1">Avis</span>
    </a>

    <details class="sidebar-group mt-1" <?= $fidelOpen ? 'open' : '' ?>>
      <summary class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors cursor-pointer select-none <?= $fidelActive ? $itemActive : $itemDefault ?>">
        <?= $icons['fidel'] ?>
        <span class="flex-1">Fidélisations</span>
        <?= $icons['chevron'] ?>
      </summary>
      <div class="pl-4 pr-1 mt-1 space-y-1 border-l border-white/10 ml-5">
        <a href="<?= BASE_PATH ?>/admin/offres" class="<?= $isActive('/admin/offres') ? $itemActive : $itemDefault ?>">
          <?= $icons['fidel'] ?>
          <span class="flex-1">Offres & Avantages</span>
        </a>
        <a href="<?= BASE_PATH ?>/admin/offres/scanner" class="<?= $isActive('/admin/offres/scanner') ? $itemActive : $itemDefault ?>">
          <?= $icons['scanner'] ?>
          <span class="flex-1">Scanner une offre</span>
        </a>
        <a href="<?= BASE_PATH ?>/admin/zonage" class="<?= $isActive('/admin/zonage') ? $itemActive : $itemDefault ?>">
          <?= $icons['zonage'] ?>
          <span class="flex-1">Zonage & Proximité</span>
        </a>
        <a href="<?= BASE_PATH ?>/admin/loterie" class="<?= $isActive('/admin/loterie') ? $itemActive : $itemDefault ?>">
          <?= $icons['loterie'] ?>
          <span class="flex-1">Loterie</span>
        </a>
      </div>
    </details>

    <a href="<?= BASE_PATH ?>/admin/sondages" class="<?= $isActive('/admin/sondages') ? $itemActive : $itemDefault ?>">
      <?= $icons['poll'] ?>
      <span class="flex-1">Sondages & Votes</span>
    </a>

    <a href="<?= BASE_PATH ?>/admin/statistiques" class="<?= $isActive('/admin/statistiques') ? $itemActive : $itemDefault ?>">
      <?= $icons['stats'] ?>
      <span class="flex-1">Statistiques</span>
    </a>

    <a href="<?= BASE_PATH ?>/admin/google-analytics" class="<?= $isActive('/admin/google-analytics') ? $itemActive : $itemDefault ?>">
      <?= $icons['ga'] ?>
      <span class="flex-1">Google Analytics</span>
    </a>

    <a href="<?= BASE_PATH ?>/admin/parametres" class="<?= $isActive('/admin/parametres') ? $itemActive : $itemDefault ?>">
      <?= $icons['settings'] ?>
      <span class="flex-1">Paramètres</span>
    </a>

    <span class="<?= $itemDefault ?> opacity-60 cursor-not-allowed mt-1">
      <?= $icons['support'] ?>
      <span class="flex-1">Support</span>
    </span>
  </nav>

  <div class="px-4 py-5 border-t border-white/10 mt-auto">
    <div class="rounded-2xl bg-slate-900/70 overflow-hidden">
      <?php $shopPhotoUrl = siteImage('hero_accueil', BASE_PATH . '/assets/images/hero-facade.jpg'); ?>
      <img src="<?= htmlspecialchars($shopPhotoUrl) ?>" alt="<?= htmlspecialchars($shop['name']) ?>" class="w-full h-28 object-cover">
      <div class="p-4 text-sm">
        <p class="font-bold text-white"><?= htmlspecialchars($shop['name']) ?></p>
        <p class="text-[10px] tracking-wide text-gray-400 mt-0.5">BAR · TABAC · PMU · FDJ · PRESSE</p>
        <p class="text-xs text-gray-500 mt-2 leading-snug">
          <?= htmlspecialchars($shop['address']) ?><br>
          <?= htmlspecialchars($shop['zipcode'] . ' ' . $shop['city']) ?>
        </p>
        <a href="<?= BASE_PATH ?>/" target="_blank" class="mt-3 w-full inline-flex items-center justify-center gap-2 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-semibold py-2.5 transition-colors">
          Voir le site
        </a>
      </div>
    </div>

    <form method="POST" action="<?= BASE_PATH ?>/deconnexion" class="mt-3">
      <?= \App\Core\Csrf::field() ?>
      <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-400 hover:text-white hover:bg-white/5 transition-colors">
        <?= $icons['logout'] ?>
        <span>Déconnexion</span>
      </button>
    </form>
  </div>
</aside>
