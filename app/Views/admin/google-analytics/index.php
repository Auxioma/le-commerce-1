<?php
$pageTitle = 'Google Analytics';
$pageSubtitle = "Vue d'ensemble de l'activité de votre plateforme (clients, portefeuilles, offres, trafic du site).";
$pageActions = [];
require __DIR__ . '/../../partials/admin-page-header.php';

$sourceColors = ['bar' => '#3b82f6', 'tabac' => '#ef4444', 'jeux_services' => '#10b981', 'pmu' => '#f59e0b', 'nirio' => '#a855f7'];

function gaTimeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'Il y a ' . $diff . ' s';
    if ($diff < 3600) return 'Il y a ' . floor($diff / 60) . ' min';
    if ($diff < 86400) return 'Il y a ' . floor($diff / 3600) . ' h';
    return 'Il y a ' . floor($diff / 86400) . ' j';
}
?>

<div class="grid lg:grid-cols-4 gap-6 mt-6">
  <div class="lg:col-span-3 space-y-6">

    <!-- KPIs -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
      <?php
      $kpis = [
        ['label' => 'Clients inscrits', 'value' => number_format($totalClients, 0, ',', ' '), 'delta' => $newClientsToday > 0 ? "+{$newClientsToday} aujourd'hui" : "Stable aujourd'hui", 'color' => 'blue', 'icon' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-4-4', 'href' => '/admin/clients'],
        ['label' => 'Portefeuilles actifs', 'value' => number_format($walletsActive, 0, ',', ' '), 'delta' => $walletsNewToday > 0 ? "+{$walletsNewToday} aujourd'hui" : 'Stable', 'color' => 'emerald', 'icon' => 'M3 10h18M7 15h1m4 0h1m-9 4h16a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'href' => '/admin/portefeuilles'],
        ['label' => 'Réservations', 'value' => $reservationsCount, 'delta' => $reservationsToday > 0 ? "+{$reservationsToday} aujourd'hui" : "Aucune aujourd'hui", 'color' => 'purple', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'href' => '/admin/reservations'],
        ['label' => 'Offres créées', 'value' => $offersActive, 'delta' => 'Actives actuellement', 'color' => 'orange', 'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z', 'href' => '/admin/offres'],
        ['label' => 'Participations loterie', 'value' => number_format($lotteryEntries, 0, ',', ' '), 'delta' => $lotteryEntriesToday > 0 ? "+{$lotteryEntriesToday} aujourd'hui" : 'Stable', 'color' => 'pink', 'icon' => 'M12 8c-1.5-3-6-3-6 0s4.5 3 6 6c1.5-3 6-3 6-6s-4.5-3-6 0z', 'href' => '/admin/loterie'],
        ['label' => 'Sondages en cours', 'value' => $pollsActive, 'delta' => 'En cours', 'color' => 'indigo', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14', 'href' => '/admin/sondages'],
        ['label' => 'Messages envoyés', 'value' => $messagesToday, 'delta' => "aujourd'hui", 'color' => 'orange', 'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4-.8L3 20l1.3-3.9A7.96 7.96 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z', 'href' => '/admin/messages'],
        ['label' => 'WhatsApp envoyés', 'value' => $whatsappToday, 'delta' => "aujourd'hui", 'color' => 'emerald', 'icon' => 'M3 21l1.65-4.95A9 9 0 1112 21a9 9 0 01-8.35-4.95z', 'href' => '/admin/messages'],
        ['label' => 'Clients à proximité', 'value' => $nearbyClients, 'delta' => 'actifs', 'color' => 'purple', 'icon' => 'M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z', 'href' => '/admin/zonage'],
        ['label' => 'Avis reçus', 'value' => $reviewsThisMonth, 'delta' => 'ce mois-ci', 'color' => 'blue', 'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z', 'href' => '/admin/avis-google'],
        ['label' => 'Nouveaux clients', 'value' => $newClientsThisWeek, 'delta' => 'cette semaine', 'color' => 'pink', 'icon' => 'M8 9a3 3 0 100-6 3 3 0 000 6zm-7 8a7 7 0 0114 0H1z', 'href' => '/admin/clients'],
      ];
      $colorMap = [
        'blue'    => ['bg' => 'bg-blue-100', 'text' => 'text-blue-500'],
        'emerald' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-500'],
        'purple'  => ['bg' => 'bg-purple-100', 'text' => 'text-purple-500'],
        'orange'  => ['bg' => 'bg-orange-100', 'text' => 'text-orange-500'],
        'pink'    => ['bg' => 'bg-pink-100', 'text' => 'text-pink-500'],
        'indigo'  => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-500'],
      ];
      foreach ($kpis as $k): $c = $colorMap[$k['color']];
      ?>
        <div class="card card-md">
          <span class="w-10 h-10 rounded-xl <?= $c['bg'] ?> <?= $c['text'] ?> flex items-center justify-center mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $k['icon'] ?>"/></svg>
          </span>
          <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wide"><?= htmlspecialchars($k['label']) ?></p>
          <p class="font-extrabold text-2xl text-ink mt-1"><?= $k['value'] ?></p>
          <p class="text-xs text-emerald-500 font-semibold mt-1"><?= htmlspecialchars($k['delta']) ?></p>
          <a href="<?= BASE_PATH . $k['href'] ?>" class="text-xs text-brand-500 font-semibold mt-2 inline-block hover:underline">Voir les détails</a>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Graphique + Répartition + Carte proximité -->
    <div class="grid lg:grid-cols-3 gap-5">
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

      <div class="card card-md">
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-bold text-ink text-sm">Clients à proximité</h2>
        </div>
        <div id="proximity-map" style="height:220px; border-radius:12px;"></div>
        <p class="text-xs text-gray-400 mt-3"><?= count($nearbyLocations) ?> client(s) géolocalisé(s) dans la dernière heure.</p>
      </div>
    </div>

    <!-- 4 blocs bas -->
    <div class="grid lg:grid-cols-4 gap-5">

      <!-- Offres les plus utilisées -->
      <div class="card card-md">
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-bold text-ink text-xs uppercase tracking-wide">Offres les plus utilisées</h2>
          <a href="<?= BASE_PATH ?>/admin/offres" class="text-xs text-brand-500 font-semibold hover:underline">Voir toutes</a>
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
                  <p class="text-gray-400 text-[10px] shrink-0 ml-2"><?= (int) $o['usage_count'] ?> util.</p>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                  <div class="h-2 rounded-full" style="width:<?= min(100, round(($o['usage_count'] / $maxUsage) * 100)) ?>%; background:<?= $offerColors[$ci % 5] ?>;"></div>
                </div>
              </li>
            <?php $ci++; endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>

      <!-- Rechargements portefeuilles -->
      <div class="card card-md">
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-bold text-ink text-xs uppercase tracking-wide">Rechargements portefeuilles</h2>
          <a href="<?= BASE_PATH ?>/admin/portefeuilles" class="text-xs text-brand-500 font-semibold hover:underline">Voir tout</a>
        </div>
        <p class="text-[10px] font-semibold text-gray-400 uppercase">Total rechargé ce mois</p>
        <p class="font-extrabold text-xl text-ink mb-3"><?= number_format($totalBalance, 2, ',', ' ') ?> &euro; <span class="text-xs text-emerald-500 font-semibold"><?= $rechargesThisMonth ?> recharges</span></p>
        <?php if (empty($latestRecharges)): ?>
          <p class="text-sm text-gray-400 py-4 text-center">Aucune recharge.</p>
        <?php else: ?>
          <ul class="space-y-2">
            <?php foreach (array_filter($latestRecharges, fn($t) => $t['type'] === 'recharge') as $t): ?>
              <li class="flex items-center justify-between text-xs">
                <span class="text-ink font-medium truncate"><?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?></span>
                <span class="text-gray-500 shrink-0 ml-2"><?= number_format($t['amount'], 2, ',', ' ') ?> &euro;</span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>

      <!-- Utilisation des QR Codes -->
      <div class="card card-md">
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-bold text-ink text-xs uppercase tracking-wide">Utilisation des QR Codes</h2>
        </div>
        <?php $maxQr = max(1, ...array_column($qrUsage, 'count')); $qrColors = ['#3b82f6', '#10b981', '#a855f7', '#f59e0b']; ?>
        <ul class="space-y-3">
          <?php foreach ($qrUsage as $i => $qr): $pct = min(100, round(($qr['count'] / $maxQr) * 100)); ?>
            <li>
              <div class="flex items-center justify-between mb-1">
                <p class="font-medium text-ink text-xs truncate"><?= htmlspecialchars($qr['label']) ?></p>
                <p class="text-gray-400 text-[10px] shrink-0 ml-2"><?= (int) $qr['count'] ?> (<?= $pct ?>%)</p>
              </div>
              <div class="w-full bg-gray-100 rounded-full h-2">
                <div class="h-2 rounded-full" style="width:<?= $pct ?>%; background:<?= $qrColors[$i % 4] ?>;"></div>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- Google Analytics -->
      <div class="card card-md">
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-bold text-ink text-xs uppercase tracking-wide">Google Analytics</h2>
        </div>
        <?php if (!$ga4Configured): ?>
          <p class="text-sm text-gray-400 py-2">Configuration requise.</p>
          <a href="<?= BASE_PATH ?>/admin/parametres" class="text-xs text-brand-500 font-semibold hover:underline">Configurer Google Analytics</a>
        <?php elseif ($ga4 === null): ?>
          <p class="text-sm text-gray-400 py-2">Impossible de récupérer les données GA4 pour le moment (vérifiez la configuration).</p>
        <?php else: ?>
          <div class="grid grid-cols-3 gap-2 mb-3 text-center">
            <div>
              <p class="font-extrabold text-lg text-ink"><?= number_format($ga4['users'], 0, ',', ' ') ?></p>
              <p class="text-[9px] text-gray-400 uppercase">Utilisateurs</p>
            </div>
            <div>
              <p class="font-extrabold text-lg text-ink"><?= number_format($ga4['sessions'], 0, ',', ' ') ?></p>
              <p class="text-[9px] text-gray-400 uppercase">Sessions</p>
            </div>
            <div>
              <p class="font-extrabold text-lg text-ink"><?= number_format($ga4['pageViews'], 0, ',', ' ') ?></p>
              <p class="text-[9px] text-gray-400 uppercase">Pages vues</p>
            </div>
          </div>
          <div class="grid grid-cols-3 gap-2 mb-3 text-center">
            <div>
              <p class="font-bold text-sm text-ink"><?= $ga4['bounceRate'] ?>%</p>
              <p class="text-[9px] text-gray-400 uppercase">Rebond</p>
            </div>
            <div>
              <p class="font-bold text-sm text-ink"><?= gmdate('i\ms', $ga4['avgDuration']) ?></p>
              <p class="text-[9px] text-gray-400 uppercase">Durée moy.</p>
            </div>
            <div>
              <p class="font-bold text-sm text-ink"><?= $ga4['conversions'] ?></p>
              <p class="text-[9px] text-gray-400 uppercase">Conversions</p>
            </div>
          </div>
          <canvas id="chart-ga4" height="80"></canvas>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Colonne droite : actions rapides -->
  <div class="lg:col-span-1 space-y-5">
    <div class="card card-md">
      <h2 class="font-bold text-ink text-xs uppercase tracking-wide mb-3">Actions rapides</h2>
      <ul class="space-y-1">
        <?php
        $quickActions = [
          ['label' => 'Ajouter une offre', 'href' => '/admin/offres/creer'],
          ['label' => 'Envoyer un SMS', 'href' => '/admin/messages'],
          ['label' => 'Envoyer un e-mail', 'href' => '/admin/messages'],
          ['label' => 'Envoyer un WhatsApp', 'href' => '/admin/messages'],
          ['label' => 'Lancer une loterie', 'href' => '/admin/loterie'],
          ['label' => 'Créer un sondage', 'href' => '/admin/sondages/creer'],
          ['label' => 'Ajouter une réservation', 'href' => '/admin/reservations'],
          ['label' => 'Envoyer à proximité', 'href' => '/admin/zonage'],
        ];
        foreach ($quickActions as $a):
        ?>
          <li>
            <a href="<?= BASE_PATH . $a['href'] ?>" class="flex items-center justify-between px-3 py-2 rounded-xl text-sm text-gray-600 hover:bg-gray-50 hover:text-brand-500 transition-colors">
              <?= htmlspecialchars($a['label']) ?>
              <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="card card-md bg-emerald-50/60 border-emerald-100">
      <h2 class="font-bold text-emerald-700 text-xs uppercase tracking-wide mb-3">Communiquer sur WhatsApp</h2>
      <ul class="space-y-1">
        <?php
        $whatsappActions = [
          ['label' => 'Nouveau message', 'href' => '/admin/messages'],
          ['label' => 'Listes de diffusion', 'href' => '/admin/messages'],
          ['label' => 'Messages programmés', 'href' => '/admin/messages'],
          ['label' => 'Statistiques WhatsApp', 'href' => '/admin/messages'],
        ];
        foreach ($whatsappActions as $a):
        ?>
          <li>
            <a href="<?= BASE_PATH . $a['href'] ?>" class="flex items-center justify-between px-3 py-2 rounded-xl text-sm text-emerald-700 hover:bg-emerald-100/70 transition-colors">
              <?= htmlspecialchars($a['label']) ?>
              <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
      <a href="<?= BASE_PATH ?>/admin/messages" class="btn-primary w-full mt-3 justify-center">Accéder au WhatsApp</a>
    </div>
  </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
(function () {
  if (typeof Chart === 'undefined') return;

  const blue = '#3b82f6', emerald = '#10b981';
  Chart.defaults.font.family = 'Montserrat, sans-serif';
  Chart.defaults.font.size = 11;
  Chart.defaults.color = '#6b7280';

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

  <?php if (!empty($ga4['series'])): ?>
  const ga4Series = <?= json_encode($ga4['series']) ?>;
  new Chart(document.getElementById('chart-ga4'), {
    type: 'line',
    data: {
      labels: ga4Series.map(s => s.date.slice(4,6) + '/' + s.date.slice(6,8)),
      datasets: [{ data: ga4Series.map(s => s.sessions), borderColor: '#c8102e', backgroundColor: '#c8102e20', fill: true, tension: 0.35, borderWidth: 2, pointRadius: 0 }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { display: false }, x: { display: false } } }
  });
  <?php endif; ?>
})();

// --- Carte "Clients à proximité" (Leaflet + OpenStreetMap) ---
(function () {
  if (typeof L === 'undefined') return;
  const shopLat = <?= (float) $shop['latitude'] ?>;
  const shopLng = <?= (float) $shop['longitude'] ?>;
  const map = L.map('proximity-map', { zoomControl: false, attributionControl: false }).setView([shopLat, shopLng], 14);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

  L.circleMarker([shopLat, shopLng], { radius: 9, color: '#c8102e', fillColor: '#c8102e', fillOpacity: 1 })
    .addTo(map).bindPopup('<?= htmlspecialchars($shop['name']) ?>');

  const clients = <?= json_encode($nearbyLocations) ?>;
  clients.forEach(function (c) {
    L.circleMarker([parseFloat(c.latitude), parseFloat(c.longitude)], { radius: 7, color: '#3b82f6', fillColor: '#3b82f6', fillOpacity: 0.85 })
      .addTo(map)
      .bindPopup((c.first_name + ' ' + c.last_name).trim());
  });
})();
</script>
