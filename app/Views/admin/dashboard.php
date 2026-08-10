<?php
$dayNames = [1 => 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
$monthNames = [1 => 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
$todayLabel = $dayNames[(int) date('N')] . ' ' . date('j') . ' ' . $monthNames[(int) date('n')] . ' ' . date('Y');

$sourceColors = ['bar' => '#3b82f6', 'tabac' => '#ef4444', 'jeux_services' => '#10b981', 'pmu' => '#f59e0b', 'nirio' => '#a855f7'];

$activityIcons = [
    'client'      => ['bg' => 'bg-blue-100', 'text' => 'text-blue-500', 'icon' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-4-4'],
    'wallet'      => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-500', 'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-2m-9-5h9m0 0l-3-3m3 3l-3 3'],
    'reservation' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-500', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
    'offer'       => ['bg' => 'bg-orange-100', 'text' => 'text-orange-500', 'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'],
    'lottery'     => ['bg' => 'bg-pink-100', 'text' => 'text-pink-500', 'icon' => 'M12 8c-1.5-3-6-3-6 0s4.5 3 6 6c1.5-3 6-3 6-6s-4.5-3-6 0z'],
];

function timeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'Il y a ' . $diff . ' s';
    if ($diff < 3600) return 'Il y a ' . floor($diff / 60) . ' min';
    if ($diff < 86400) return 'Il y a ' . floor($diff / 3600) . ' h';
    return 'Il y a ' . floor($diff / 86400) . ' j';
}
?>

<!-- Salutation -->
<div class="flex flex-col gap-1 mb-6 sm:flex-row sm:items-center sm:justify-between">
  <div>
    <h1 class="text-2xl font-extrabold text-ink">Bonjour <?= htmlspecialchars($currentUser['first_name'] ?? '') ?> !</h1>
    <p class="text-sm text-gray-500 mt-1">Bienvenue dans votre tableau de bord administrateur.</p>
  </div>
  <span class="inline-flex items-center gap-2 text-sm font-semibold text-gray-600 bg-white border border-gray-200 rounded-full px-4 py-2 shadow-sm shrink-0">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
    <?= htmlspecialchars(ucfirst($todayLabel)) ?>
  </span>
</div>

<!-- KPIs -->
<div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">

  <!-- Clients inscrits -->
  <div class="card card-md">
    <span class="w-10 h-10 rounded-xl bg-blue-100 text-blue-500 flex items-center justify-center mb-3">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-4-4"/></svg>
    </span>
    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wide">Clients inscrits</p>
    <p class="font-extrabold text-2xl text-ink mt-1"><?= number_format($totalClients, 0, ',', ' ') ?></p>
    <p class="text-xs text-emerald-500 font-semibold mt-1"><?= $newClientsToday > 0 ? '+' . $newClientsToday . " aujourd'hui" : "Stable aujourd'hui" ?></p>
    <a href="<?= BASE_PATH ?>/admin/clients" class="text-xs text-brand-500 font-semibold mt-2 inline-block hover:underline">Voir les détails</a>
  </div>

  <!-- Portefeuilles actifs -->
  <div class="card card-md">
    <span class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-500 flex items-center justify-center mb-3">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-9 4h16a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
    </span>
    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wide">Portefeuilles actifs</p>
    <p class="font-extrabold text-2xl text-ink mt-1"><?= number_format($walletsActive, 0, ',', ' ') ?></p>
    <p class="text-xs text-emerald-500 font-semibold mt-1"><?= $walletsNewToday > 0 ? '+' . $walletsNewToday . " aujourd'hui" : "Stable" ?></p>
    <a href="<?= BASE_PATH ?>/admin/portefeuilles" class="text-xs text-brand-500 font-semibold mt-2 inline-block hover:underline">Voir les détails</a>
  </div>

  <!-- Réservations -->
  <div class="card card-md">
    <span class="w-10 h-10 rounded-xl bg-purple-100 text-purple-500 flex items-center justify-center mb-3">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
    </span>
    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wide">Réservations</p>
    <p class="font-extrabold text-2xl text-ink mt-1"><?= $reservationsCount ?></p>
    <p class="text-xs text-emerald-500 font-semibold mt-1"><?= $reservationsToday > 0 ? '+' . $reservationsToday . " aujourd'hui" : "Aucune aujourd'hui" ?></p>
    <a href="<?= BASE_PATH ?>/admin/reservations" class="text-xs text-brand-500 font-semibold mt-2 inline-block hover:underline">Voir les détails</a>
  </div>

  <!-- Offres créées -->
  <div class="card card-md">
    <span class="w-10 h-10 rounded-xl bg-orange-100 text-orange-500 flex items-center justify-center mb-3">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
    </span>
    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wide">Offres créées</p>
    <p class="font-extrabold text-2xl text-ink mt-1"><?= $offersActive ?></p>
    <p class="text-xs text-gray-400 font-semibold mt-1">Actives actuellement</p>
    <a href="<?= BASE_PATH ?>/admin/offres" class="text-xs text-brand-500 font-semibold mt-2 inline-block hover:underline">Voir les offres</a>
  </div>

  <!-- Participations loterie -->
  <div class="card card-md">
    <span class="w-10 h-10 rounded-xl bg-pink-100 text-pink-500 flex items-center justify-center mb-3">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.5-3-6-3-6 0s4.5 3 6 6c1.5-3 6-3 6-6s-4.5-3-6 0z"/></svg>
    </span>
    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wide">Participations loterie</p>
    <p class="font-extrabold text-2xl text-ink mt-1"><?= number_format($lotteryEntries, 0, ',', ' ') ?></p>
    <p class="text-xs text-emerald-500 font-semibold mt-1"><?= $lotteryEntriesToday > 0 ? '+' . $lotteryEntriesToday . " aujourd'hui" : "Stable" ?></p>
    <a href="<?= BASE_PATH ?>/admin/loterie" class="text-xs text-brand-500 font-semibold mt-2 inline-block hover:underline">Voir les participations</a>
  </div>

  <!-- Sondages & Votes -->
  <div class="card card-md">
    <span class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-500 flex items-center justify-center mb-3">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14"/></svg>
    </span>
    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wide">Sondages & Votes</p>
    <p class="font-extrabold text-2xl text-ink mt-1"><?= $pollsActive ?></p>
    <p class="text-xs text-gray-400 font-semibold mt-1">En cours</p>
    <a href="<?= BASE_PATH ?>/admin/sondages" class="text-xs text-brand-500 font-semibold mt-2 inline-block hover:underline">Voir les sondages</a>
  </div>
</div>

<!-- Graphiques + Répartition + Activité -->
<div class="grid lg:grid-cols-3 gap-5 mb-6">
  <!-- Graphique inscriptions -->
  <div class="card card-md">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-bold text-ink text-sm">Évolution des inscriptions clients</h2>
      <span class="text-xs text-gray-400 font-medium">7 derniers jours</span>
    </div>
    <div class="flex items-center gap-4 mb-3">
      <span class="flex items-center gap-1.5 text-xs text-gray-500"><span class="w-3 h-0.5 rounded-full bg-blue-500 inline-block"></span> Total</span>
      <span class="flex items-center gap-1.5 text-xs text-gray-500"><span class="w-3 h-0.5 rounded-full bg-emerald-500 inline-block"></span> Nouveaux</span>
    </div>
    <canvas id="chart-registrations" height="180"></canvas>
  </div>

  <!-- Répartition par outil (Doughnut) -->
  <div class="card card-md">
    <h2 class="font-bold text-ink text-sm mb-4">Répartition des inscriptions par outil</h2>
    <div class="flex items-center gap-6">
      <div class="w-40 h-40 shrink-0">
        <canvas id="chart-segments" height="160" width="160"></canvas>
      </div>
      <div class="space-y-2 text-sm">
        <?php
        $totalSources = array_sum(array_column($registrationsBySource, 'nb'));
        foreach ($registrationsBySource as $src):
          $pct = $totalSources > 0 ? round(($src['nb'] / $totalSources) * 100, 1) : 0;
          $color = $sourceColors[$src['registration_source']] ?? '#6b7280';
          $label = \App\Models\User::SOURCE_LABELS[$src['registration_source']] ?? ucfirst($src['registration_source']);
        ?>
          <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full shrink-0" style="background:<?= $color ?>;"></span>
            <span class="text-gray-700 font-medium"><?= $label ?></span>
            <span class="text-gray-400 ml-auto"><?= (int) $src['nb'] ?> (<?= $pct ?>%)</span>
          </div>
        <?php endforeach; ?>
        <?php if (empty($registrationsBySource)): ?>
          <p class="text-gray-400 text-xs py-4 text-center">Aucune inscription pour le moment.</p>
        <?php endif; ?>
        <div class="flex items-center gap-2 pt-2 border-t border-gray-100 font-bold">
          <span class="text-ink">Total</span>
          <span class="text-ink ml-auto"><?= number_format($totalSources, 0, ',', ' ') ?></span>
        </div>
      </div>
    </div>
  </div>

  <!-- Activités récentes -->
  <div class="card card-md">
    <h2 class="font-bold text-ink text-sm mb-4">Activités récentes</h2>
    <?php if (empty($recentActivity)): ?>
      <p class="text-sm text-gray-400 py-8 text-center">Aucune activité récente.</p>
    <?php else: ?>
      <ul class="space-y-3">
        <?php foreach ($recentActivity as $a): $ic = $activityIcons[$a['type']] ?? $activityIcons['client']; ?>
          <li class="flex items-start gap-3">
            <span class="w-9 h-9 rounded-full <?= $ic['bg'] ?> <?= $ic['text'] ?> flex items-center justify-center shrink-0 mt-0.5">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $ic['icon'] ?>"/></svg>
            </span>
            <div class="min-w-0 flex-1">
              <p class="font-semibold text-ink text-xs"><?= htmlspecialchars($a['label']) ?></p>
              <p class="text-gray-500 text-xs truncate"><?= htmlspecialchars($a['detail']) ?></p>
            </div>
            <span class="text-gray-400 text-[10px] shrink-0 whitespace-nowrap"><?= timeAgo($a['time']) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
      <a href="#" class="text-xs text-brand-500 font-semibold mt-4 inline-block hover:underline">Voir toutes les activités</a>
    <?php endif; ?>
  </div>
</div>

<!-- Accès rapide -->
<div class="card card-md mb-6">
  <div class="grid grid-cols-5 sm:grid-cols-10 gap-3 text-center">
    <?php
    $quickLinks = [
      ['/admin/employes', 'Employés', '#3b82f6', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
      ['/admin/clients', 'Clients inscrits', '#10b981', 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-4-4'],
      ['/admin/portefeuilles', 'Portefeuille client', '#f59e0b', 'M3 10h18M7 15h1m4 0h1m-9 4h16a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
      ['/admin/reservations', 'Réservations', '#8b5cf6', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
      ['/admin/messages', 'Messages', '#ec4899', 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4-.8L3 20l1.3-3.9A7.96 7.96 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
      ['/admin/offres', 'Offres & Avantages', '#ef4444', 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'],
      ['/admin/offres/scanner', 'Scanner une offre', '#f97316', 'M4 4h4V2H2v6h2V4zm16 0v4h2V2h-6v2h4zM4 20h4v2H2v-6h2v4zm16 0h-4v2h6v-6h-2v4zM8 8h8v8H8V8z'],
      ['/admin/zonage', 'Zonage & Proximité', '#14b8a6', 'M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z'],
      ['/admin/loterie', 'Loterie', '#a855f7', 'M12 8c-1.5-3-6-3-6 0s4.5 3 6 6c1.5-3 6-3 6-6s-4.5-3-6 0z'],
      ['/admin/sondages', 'Sondages & Votes', '#6366f1', 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14'],
    ];
    foreach ($quickLinks as [$href, $label, $color, $path]):
    ?>
      <a href="<?= BASE_PATH . $href ?>" class="flex flex-col items-center gap-2 p-2 rounded-xl hover:bg-gray-50 transition-colors group">
        <span class="w-12 h-12 rounded-full flex items-center justify-center transition-colors" style="background:<?= $color ?>15; color:<?= $color ?>;">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $path ?>"/></svg>
        </span>
        <span class="font-semibold text-gray-600 text-[10px] leading-tight text-center"><?= $label ?></span>
      </a>
    <?php endforeach; ?>
  </div>
</div>

<!-- Section basse : 4 colonnes -->
<div class="grid lg:grid-cols-4 gap-5">

  <!-- Inscriptions récentes -->
  <div class="card card-md">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-bold text-ink text-xs uppercase tracking-wide">Inscriptions récentes</h2>
      <a href="<?= BASE_PATH ?>/admin/clients" class="text-xs text-brand-500 font-semibold hover:underline">Voir tout</a>
    </div>
    <?php if (empty($latestClients)): ?>
      <p class="text-sm text-gray-400 py-6 text-center">Aucun client inscrit.</p>
    <?php else: ?>
      <ul class="space-y-3">
        <?php foreach ($latestClients as $c): ?>
          <li class="flex items-center gap-3">
            <span class="w-9 h-9 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold shrink-0">
              <?= htmlspecialchars(mb_substr($c['first_name'], 0, 1) . mb_substr($c['last_name'], 0, 1)) ?>
            </span>
            <div class="min-w-0 flex-1">
              <p class="font-semibold text-ink text-xs truncate"><?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?></p>
              <p class="text-gray-400 text-[10px] truncate"><?= htmlspecialchars($c['email'] ?? '—') ?></p>
            </div>
            <div class="text-right shrink-0">
              <p class="text-gray-400 text-[10px]"><?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></p>
              <span class="inline-block mt-0.5 text-[9px] font-bold px-1.5 py-0.5 rounded-full bg-blue-100 text-blue-600"><?= ucfirst($c['segment'] ?? 'Nouveau') ?></span>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>

  <!-- Portefeuilles clients -->
  <div class="card card-md">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-bold text-ink text-xs uppercase tracking-wide">Portefeuilles clients</h2>
      <a href="<?= BASE_PATH ?>/admin/portefeuilles" class="text-xs text-brand-500 font-semibold hover:underline">Voir tout</a>
    </div>
    <div class="flex items-center gap-4 mb-4">
      <div>
        <p class="text-[10px] font-semibold text-gray-400 uppercase">Solde total des portefeuilles</p>
        <p class="font-extrabold text-xl text-ink"><?= number_format($totalBalance, 2, ',', ' ') ?> &euro;</p>
      </div>
      <div class="ml-auto text-center">
        <p class="text-[10px] font-semibold text-gray-400 uppercase">Rechargements ce mois</p>
        <p class="font-extrabold text-xl text-ink"><?= $rechargesThisMonth ?></p>
      </div>
    </div>
    <p class="font-semibold text-xs text-gray-500 mb-2">Derniers rechargements</p>
    <?php if (empty($latestRecharges)): ?>
      <p class="text-sm text-gray-400 py-4 text-center">Aucune recharge.</p>
    <?php else: ?>
      <ul class="space-y-2">
        <?php foreach (array_filter($latestRecharges, fn($t) => $t['type'] === 'recharge') as $t): ?>
          <li class="flex items-center justify-between text-xs">
            <span class="text-ink font-medium"><?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?></span>
            <span class="text-gray-500"><?= number_format($t['amount'], 2, ',', ' ') ?> &euro;</span>
            <span class="text-gray-400 text-[10px]"><?= date('d/m/Y H:i', strtotime($t['created_at'])) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>

  <!-- Offres les plus utilisées -->
  <div class="card card-md">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-bold text-ink text-xs uppercase tracking-wide">Offres les plus utilisées</h2>
      <a href="<?= BASE_PATH ?>/admin/offres" class="text-xs text-brand-500 font-semibold hover:underline">Voir tout</a>
    </div>
    <?php if (empty($topOffers)): ?>
      <p class="text-sm text-gray-400 py-6 text-center">Aucune offre active.</p>
    <?php else: ?>
      <?php $maxUsage = max(1, ...array_column($topOffers, 'usage_count')); ?>
      <ul class="space-y-3">
        <?php $offerColors = ['#10b981', '#f59e0b', '#3b82f6', '#ef4444', '#8b5cf6']; $ci = 0; ?>
        <?php foreach ($topOffers as $o): ?>
          <li>
            <div class="flex items-center justify-between mb-1">
              <p class="font-medium text-ink text-xs truncate"><?= htmlspecialchars($o['title']) ?></p>
              <p class="text-gray-400 text-[10px] shrink-0 ml-2"><?= (int) $o['usage_count'] ?> utilisations</p>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2">
              <div class="h-2 rounded-full" style="width:<?= min(100, round(($o['usage_count'] / $maxUsage) * 100)) ?>%; background:<?= $offerColors[$ci % 5] ?>;"></div>
            </div>
          </li>
        <?php $ci++; endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>

  <!-- Sondages en cours + Loterie -->
  <div class="card card-md flex flex-col">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-bold text-ink text-xs uppercase tracking-wide">Sondages en cours</h2>
      <a href="<?= BASE_PATH ?>/admin/sondages" class="text-xs text-brand-500 font-semibold hover:underline">Voir tout</a>
    </div>
    <?php if (empty($activePolls)): ?>
      <p class="text-sm text-gray-400 py-4 text-center">Aucun sondage en cours.</p>
    <?php else: ?>
      <ul class="space-y-2.5">
        <?php foreach ($activePolls as $p): ?>
          <li class="flex items-center justify-between">
            <div class="min-w-0 flex-1">
              <p class="font-medium text-ink text-xs truncate"><?= htmlspecialchars($p['question']) ?></p>
              <p class="text-gray-400 text-[10px]"><?= (int) $p['participations'] ?> réponses</p>
            </div>
            <a href="<?= BASE_PATH ?>/admin/sondages/<?= $p['id'] ?>/resultats" class="text-[10px] text-brand-500 font-semibold hover:underline shrink-0 ml-2">Participer</a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <!-- Prochaine loterie (compte à rebours) -->
    <?php if ($nextLottery): ?>
      <div class="mt-auto pt-4">
        <div class="rounded-2xl bg-pink-50 p-4">
          <p class="text-[11px] font-extrabold text-pink-600 uppercase tracking-wide mb-3">Prochaine loterie</p>
          <div class="grid grid-cols-4 gap-2" id="lottery-countdown" data-ends="<?= htmlspecialchars($nextLottery['ends_at']) ?>">
            <div class="text-center bg-white rounded-lg py-1.5 shadow-sm">
              <p class="font-extrabold text-lg leading-none text-pink-600 countdown-days">--</p>
              <p class="text-[8px] uppercase text-gray-500 mt-0.5">Jours</p>
            </div>
            <div class="text-center bg-white rounded-lg py-1.5 shadow-sm">
              <p class="font-extrabold text-lg leading-none text-pink-600 countdown-hours">--</p>
              <p class="text-[8px] uppercase text-gray-500 mt-0.5">Heures</p>
            </div>
            <div class="text-center bg-white rounded-lg py-1.5 shadow-sm">
              <p class="font-extrabold text-lg leading-none text-pink-600 countdown-minutes">--</p>
              <p class="text-[8px] uppercase text-gray-500 mt-0.5">Minutes</p>
            </div>
            <div class="text-center bg-white rounded-lg py-1.5 shadow-sm">
              <p class="font-extrabold text-lg leading-none text-pink-600 countdown-seconds">--</p>
              <p class="text-[8px] uppercase text-gray-500 mt-0.5">Secondes</p>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
(function () {
  if (typeof Chart === 'undefined') return;

  const brand = '#c8102e', blue = '#3b82f6', emerald = '#10b981';
  Chart.defaults.font.family = 'Montserrat, sans-serif';
  Chart.defaults.font.size = 11;
  Chart.defaults.color = '#6b7280';

  // --- Graphique inscriptions ---
  const reg = <?= json_encode($clientRegistrations) ?>;
  new Chart(document.getElementById('chart-registrations'), {
    type: 'line',
    data: {
      labels: reg.map(r => { const d = new Date(r.jour); return d.toLocaleDateString('fr-FR', {day:'2-digit', month:'short'}); }),
      datasets: [
        { label: 'Total', data: reg.map(r => r.total), borderColor: blue, backgroundColor: blue + '20', fill: true, tension: 0.35, borderWidth: 2, pointRadius: 3, pointBackgroundColor: blue },
        { label: 'Nouveaux', data: reg.map(r => r.nouveaux), borderColor: emerald, backgroundColor: emerald + '20', fill: true, tension: 0.35, borderWidth: 2, pointRadius: 3, pointBackgroundColor: emerald }
      ]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: '#f3f4f6' } }, x: { grid: { display: false } } } }
  });

  // --- Doughnut répartition par outil ---
  const sources = <?= json_encode($registrationsBySource) ?>;
  const sourceLabels = <?= json_encode(\App\Models\User::SOURCE_LABELS) ?>;
  const sourceColors = { bar: '#3b82f6', tabac: '#ef4444', jeux_services: '#10b981', pmu: '#f59e0b', nirio: '#a855f7' };
  const labels = sources.map(s => sourceLabels[s.registration_source] || s.registration_source);
  const data = sources.map(s => parseInt(s.nb));
  const colors = sources.map(s => sourceColors[s.registration_source] || '#6b7280');

  if (data.length) {
    new Chart(document.getElementById('chart-segments'), {
      type: 'doughnut',
      data: { labels: labels, datasets: [{ data: data, backgroundColor: colors, borderWidth: 0 }] },
      options: { responsive: true, cutout: '65%', plugins: { legend: { display: false } } }
    });
  }

  // --- Countdown loterie ---
  const el = document.getElementById('lottery-countdown');
  if (el) {
    const endsAt = new Date(el.dataset.ends + 'T23:59:59').getTime();
    function updateCountdown() {
      const now = Date.now();
      const diff = Math.max(0, endsAt - now);
      const d = Math.floor(diff / 86400000);
      const h = Math.floor((diff % 86400000) / 3600000);
      const m = Math.floor((diff % 3600000) / 60000);
      const s = Math.floor((diff % 60000) / 1000);
      el.querySelector('.countdown-days').textContent = String(d).padStart(2, '0');
      el.querySelector('.countdown-hours').textContent = String(h).padStart(2, '0');
      el.querySelector('.countdown-minutes').textContent = String(m).padStart(2, '0');
      el.querySelector('.countdown-seconds').textContent = String(s).padStart(2, '0');
    }
    updateCountdown();
    setInterval(updateCountdown, 1000);
  }
})();
</script>
