<?php
function kpiDelta(float $value, string $suffix = ''): string
{
    if ($value == 0) return '';
    $sign = $value > 0 ? '+' : '';
    return $sign . number_format($value, 2, ',', ' ') . $suffix;
}
$segmentLabels = ['nouveau' => 'Nouveau', 'fidele' => 'Fidèle', 'occasionnel' => 'Occasionnel'];
$segmentColors = ['nouveau' => '#3b82f6', 'fidele' => '#10b981', 'occasionnel' => '#f59e0b'];
$activityIcons = [
    'client' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-500', 'path' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-4-4'],
    'wallet' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-500', 'path' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-2m-9-5h9m0 0l-3-3m3 3l-3 3'],
    'offer'  => ['bg' => 'bg-brand-50', 'text' => 'text-brand-500', 'path' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'],
];
$dayNames = [1 => 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
$monthNames = [1 => 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
$todayLabel = $dayNames[(int) date('N')] . ' ' . date('j') . ' ' . $monthNames[(int) date('n')] . ' ' . date('Y');
?>

<!-- Salutation -->
<div class="flex flex-col gap-1 mb-6 sm:flex-row sm:items-center sm:justify-between">
  <div>
    <h1 class="text-2xl font-extrabold text-ink">Bonjour <?= htmlspecialchars($currentUser['first_name'] ?? '') ?> ! 👋</h1>
    <p class="text-sm text-gray-500 mt-1">Voici un aperçu de l'activité de votre établissement.</p>
  </div>
  <p class="text-sm font-semibold text-gray-500"><?= htmlspecialchars($todayLabel) ?></p>
</div>

<!-- KPIs -->
<div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">

  <div class="card card-md">
    <span class="w-9 h-9 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center mb-3">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-4-4"/></svg>
    </span>
    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Clients inscrits</p>
    <p class="font-extrabold text-2xl text-ink mt-1"><?= number_format($totalClients) ?></p>
    <p class="text-xs font-semibold text-emerald-500 mt-1">+<?= $newClientsThisMonth ?> ce mois-ci</p>
  </div>

  <div class="card card-md">
    <span class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center mb-3">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-2m-9-5h9m0 0l-3-3m3 3l-3 3"/></svg>
    </span>
    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Portefeuilles clients</p>
    <p class="font-extrabold text-2xl text-ink mt-1"><?= number_format($totalBalance, 2, ',', ' ') ?> €</p>
    <p class="text-xs font-semibold <?= $balanceDelta >= 0 ? 'text-emerald-500' : 'text-brand-500' ?> mt-1">
      <?= $balanceDelta != 0 ? kpiDelta($balanceDelta, ' € ce mois-ci') : 'Stable ce mois-ci' ?>
    </p>
  </div>

  <div class="card card-md">
    <span class="w-9 h-9 rounded-xl bg-purple-50 text-purple-500 flex items-center justify-center mb-3">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
    </span>
    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Offres actives</p>
    <p class="font-extrabold text-2xl text-ink mt-1"><?= $offersActive ?></p>
    <p class="text-xs font-semibold text-emerald-500 mt-1">+<?= $offersCreatedThisMonth ?> créées ce mois-ci</p>
  </div>

  <div class="card card-md">
    <span class="w-9 h-9 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center mb-3">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
    </span>
    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Avis Google</p>
    <p class="font-extrabold text-2xl text-ink mt-1"><?= number_format((float) $shop['google_rating'], 1, ',', '') ?>/5</p>
    <p class="text-xs font-semibold text-gray-400 mt-1"><?= (int) $shop['google_reviews_count'] ?> avis au total</p>
  </div>

  <div class="card card-md">
    <span class="w-9 h-9 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center mb-3">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14"/></svg>
    </span>
    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Sondages en cours</p>
    <p class="font-extrabold text-2xl text-ink mt-1"><?= $pollsActive ?></p>
    <p class="text-xs font-semibold text-gray-400 mt-1"><?= $pollsParticipations ?> participations au total</p>
  </div>

  <div class="card card-md">
    <span class="w-9 h-9 rounded-xl bg-teal-50 text-teal-500 flex items-center justify-center mb-3">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
    </span>
    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Zonage & proximité</p>
    <p class="font-extrabold text-2xl text-ink mt-1"><?= $campaignsActive ?></p>
    <p class="text-xs font-semibold text-gray-400 mt-1">campagne(s) active(s)</p>
  </div>
</div>

<!-- Graphiques + activité -->
<div class="grid lg:grid-cols-3 gap-5 mb-6">
  <div class="lg:col-span-1 card card-md">
    <h2 class="font-bold text-ink mb-4" style="font-size:14px;">Évolution des inscriptions (14 derniers jours)</h2>
    <canvas id="chart-registrations" height="160"></canvas>
  </div>
  <div class="lg:col-span-1 card card-md">
    <h2 class="font-bold text-ink mb-4" style="font-size:14px;">Clients par segment</h2>
    <canvas id="chart-segments" height="160"></canvas>
  </div>
  <div class="lg:col-span-1 card card-md">
    <h2 class="font-bold text-ink mb-4" style="font-size:14px;">Activités récentes</h2>
    <?php if (empty($recentActivity)): ?>
      <p class="text-sm text-gray-400 py-8 text-center">Aucune activité récente.</p>
    <?php else: ?>
      <ul class="space-y-3">
        <?php foreach ($recentActivity as $a): $ic = $activityIcons[$a['type']]; ?>
          <li class="flex items-start gap-3">
            <span class="w-8 h-8 rounded-full <?= $ic['bg'] ?> <?= $ic['text'] ?> flex items-center justify-center shrink-0">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $ic['path'] ?>"/></svg>
            </span>
            <div class="min-w-0 flex-1">
              <p class="font-semibold text-ink truncate" style="font-size:12.5px;"><?= htmlspecialchars($a['label']) ?></p>
              <p class="text-gray-500 truncate" style="font-size:11.5px;"><?= htmlspecialchars($a['detail']) ?></p>
            </div>
            <span class="text-gray-400 shrink-0" style="font-size:10.5px;"><?= date('d/m H:i', strtotime($a['time'])) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</div>

<!-- Accès rapide -->
<div class="card card-md mb-6">
  <div class="grid grid-cols-4 sm:grid-cols-8 gap-3 text-center">
    <?php
    $quickLinks = [
      ['/admin/clients', 'Clients', 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-4-4'],
      ['/admin/portefeuilles', 'Portefeuille', 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-2m-9-5h9m0 0l-3-3m3 3l-3 3'],
      ['/admin/messages', 'Messages', 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4-.8L3 20l1.3-3.9A7.96 7.96 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
      ['/admin/offres', 'Offres', 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'],
      ['/admin/offres/scanner', 'Scanner', 'M4 4h4V2H2v6h2V4zm16 0v4h2V2h-6v2h4zM4 20h4v2H2v-6h2v4zm16 0h-4v2h6v-6h-2v4zM8 8h8v8H8V8z'],
      ['/admin/zonage', 'Zonage', 'M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z'],
      ['/admin/sondages', 'Sondages', 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14'],
      ['/admin/avis-google', 'Avis', 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'],
    ];
    foreach ($quickLinks as [$href, $label, $path]):
    ?>
      <a href="<?= BASE_PATH . $href ?>" class="flex flex-col items-center gap-2 p-3 rounded-xl hover:bg-gray-50 transition-colors group">
        <span class="w-11 h-11 rounded-xl bg-gray-50 text-gray-500 group-hover:bg-brand-50 group-hover:text-brand-500 flex items-center justify-center transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $path ?>"/></svg>
        </span>
        <span class="font-semibold text-gray-600" style="font-size:11px;"><?= $label ?></span>
      </a>
    <?php endforeach; ?>
  </div>
</div>

<!-- Transactions + top clients -->
<div class="grid lg:grid-cols-3 gap-6 mb-6">
  <div class="lg:col-span-2 card card-md">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between mb-4">
      <h2 class="font-bold text-ink">Dernières transactions portefeuille</h2>
      <a href="<?= BASE_PATH ?>/admin/portefeuilles" class="text-xs font-semibold text-brand-500 hover:underline">Voir toutes les transactions</a>
    </div>

    <?php if (empty($latestTransactions)): ?>
      <p class="text-sm text-gray-400 py-8 text-center">Aucune transaction pour le moment.</p>
    <?php else: ?>
      <div class="overflow-x-auto -mx-4 px-4">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-left text-gray-400 text-xs uppercase tracking-wide">
              <th class="px-2 py-2 font-semibold">Client</th>
              <th class="px-2 py-2 font-semibold">Type</th>
              <th class="px-2 py-2 font-semibold">Montant</th>
              <th class="px-2 py-2 font-semibold">Date</th>
              <th class="px-2 py-2 font-semibold">Statut</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <?php foreach ($latestTransactions as $tx): ?>
              <tr>
                <td class="px-2 py-3">
                  <p class="font-semibold text-ink"><?= htmlspecialchars($tx['first_name'] . ' ' . $tx['last_name']) ?></p>
                  <p class="text-xs text-gray-400"><?= htmlspecialchars($tx['phone_whatsapp']) ?></p>
                </td>
                <td class="px-2 py-3 capitalize text-gray-600"><?= htmlspecialchars($tx['type']) ?></td>
                <td class="px-2 py-3 font-semibold <?= $tx['type'] === 'debit' ? 'text-brand-500' : 'text-emerald-500' ?>">
                  <?= $tx['type'] === 'debit' ? '-' : '+' ?><?= number_format($tx['amount'], 2, ',', ' ') ?> €
                </td>
                <td class="px-2 py-3 text-gray-500"><?= date('d/m/Y à H:i', strtotime($tx['created_at'])) ?></td>
                <td class="px-2 py-3">
                  <span class="text-xs font-semibold px-2 py-1 rounded-full
                        <?= $tx['status'] === 'reussi' ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' ?>">
                    <?= ucfirst($tx['status']) ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <div class="card card-md">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-bold text-ink">Top 5 des clients (solde)</h2>
      <a href="<?= BASE_PATH ?>/admin/clients" class="text-xs font-semibold text-brand-500 hover:underline">Voir tout</a>
    </div>

    <?php if (empty($topClients)): ?>
      <p class="text-sm text-gray-400 py-8 text-center">Aucun client pour le moment.</p>
    <?php else: ?>
      <ul class="space-y-3">
        <?php foreach ($topClients as $c): ?>
          <li class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <span class="w-9 h-9 rounded-full bg-brand-50 text-brand-500 flex items-center justify-center text-xs font-bold">
                <?= htmlspecialchars(mb_substr($c['first_name'], 0, 1) . mb_substr($c['last_name'], 0, 1)) ?>
              </span>
              <div>
                <p class="font-semibold text-ink text-sm"><?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?></p>
                <p class="text-xs text-gray-400"><?= htmlspecialchars($c['phone_whatsapp']) ?></p>
              </div>
            </div>
            <p class="font-bold text-ink text-sm"><?= number_format($c['balance'], 2, ',', ' ') ?> €</p>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</div>

<!-- Offres + sondages -->
<div class="grid lg:grid-cols-2 gap-6">
  <div class="card card-md">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-bold text-ink">Offres les plus utilisées</h2>
      <a href="<?= BASE_PATH ?>/admin/offres" class="text-xs font-semibold text-brand-500 hover:underline">Voir toutes</a>
    </div>
    <?php if (empty($topOffers)): ?>
      <p class="text-sm text-gray-400 py-8 text-center">Aucune offre active.</p>
    <?php else: ?>
      <ul class="space-y-3">
        <?php $maxUsage = max(1, ...array_column($topOffers, 'usage_count')); ?>
        <?php foreach ($topOffers as $o): ?>
          <li>
            <div class="flex items-center justify-between mb-1.5">
              <p class="font-semibold text-ink text-sm"><?= htmlspecialchars($o['title']) ?></p>
              <p class="text-gray-500 text-xs"><?= (int) $o['usage_count'] ?> utilisations</p>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-1.5">
              <div class="h-1.5 rounded-full bg-brand-500" style="width:<?= min(100, round(($o['usage_count'] / $maxUsage) * 100)) ?>%;"></div>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>

  <div class="card card-md">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-bold text-ink">Sondages en cours</h2>
      <a href="<?= BASE_PATH ?>/admin/sondages" class="text-xs font-semibold text-brand-500 hover:underline">Voir tout</a>
    </div>
    <?php if (empty($activePolls)): ?>
      <p class="text-sm text-gray-400 py-8 text-center">Aucun sondage en cours.</p>
    <?php else: ?>
      <ul class="space-y-3">
        <?php foreach ($activePolls as $p): ?>
          <li class="flex items-center justify-between">
            <p class="font-semibold text-ink text-sm truncate pr-3"><?= htmlspecialchars($p['question']) ?></p>
            <a href="<?= BASE_PATH ?>/admin/sondages/<?= $p['id'] ?>/resultats" class="text-xs font-semibold text-brand-500 hover:underline shrink-0"><?= (int) $p['participations'] ?> réponses</a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
(function () {
  if (typeof Chart === 'undefined') return; // pas de connexion internet : on n'affiche pas de graphique cassé

  const brand = '#c8102e', emerald = '#10b981';
  Chart.defaults.font.family = 'Montserrat, sans-serif';
  Chart.defaults.color = '#6b7280';

  const reg = <?= json_encode($clientRegistrations) ?>;
  new Chart(document.getElementById('chart-registrations'), {
    type: 'line',
    data: {
      labels: reg.map(r => new Date(r.jour).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' })),
      datasets: [
        { label: 'Total', data: reg.map(r => r.total), borderColor: '#3b82f6', backgroundColor: '#3b82f620', tension: 0.35, fill: true },
        { label: 'Nouveaux', data: reg.map(r => r.nouveaux), borderColor: emerald, backgroundColor: emerald + '20', tension: 0.35, fill: true },
      ],
    },
    options: { plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true } } },
  });

  const segments = <?= json_encode($clientsBySegment) ?>;
  const segLabels = <?= json_encode($segmentLabels) ?>;
  const segColors = <?= json_encode($segmentColors) ?>;
  new Chart(document.getElementById('chart-segments'), {
    type: 'doughnut',
    data: {
      labels: segments.map(s => segLabels[s.segment] || s.segment),
      datasets: [{ data: segments.map(s => parseInt(s.nb, 10)), backgroundColor: segments.map(s => segColors[s.segment] || '#9ca3af') }],
    },
    options: { plugins: { legend: { position: 'bottom' } } },
  });
})();
</script>
