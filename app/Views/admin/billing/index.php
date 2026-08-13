<?php
$pageTitle = 'Facturation';
$pageSubtitle = 'Historique des paiements par carte bancaire effectués par vos clients et suivi des factures.';
$pageActions = [];
require __DIR__ . '/../../partials/admin-page-header.php';
?>

<!-- Filtres + export -->
<form method="GET" action="<?= BASE_PATH ?>/admin/facturation">
  <div class="bg-white border border-gray-200 rounded-xl px-5 py-4 shadow-sm flex flex-wrap items-center gap-3 mb-6">
    <div class="relative flex-1 min-w-[180px]">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
      <input type="text" name="q" placeholder="Rechercher un client..." value="<?= htmlspecialchars($filters['q'] ?? '') ?>"
             class="w-full border border-gray-200 rounded-lg pl-10 pr-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500" style="font-size:13px;">
    </div>
    <div class="flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2.5" style="font-size:13px;">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
      <span class="text-gray-500 shrink-0">Période :</span>
      <input type="date" name="from" value="<?= htmlspecialchars($filters['from'] ?? '') ?>" class="focus:outline-none bg-transparent" style="font-size:13px;">
      <span class="text-gray-400">—</span>
      <input type="date" name="to" value="<?= htmlspecialchars($filters['to'] ?? '') ?>" class="focus:outline-none bg-transparent" style="font-size:13px;">
    </div>
    <button type="submit" class="inline-flex items-center gap-2 text-white font-bold rounded-lg px-5 py-2.5 transition-opacity hover:opacity-90" style="background:#c8272c; font-size:13px;">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18M7 10h10M11 16h2"/></svg>
      Filtrer
    </button>
    <a href="<?= BASE_PATH ?>/admin/facturation/export?<?= http_build_query($filters) ?>"
       class="inline-flex items-center gap-2 text-white font-bold rounded-lg px-5 py-2.5 transition-opacity hover:opacity-90" style="background:#1a1a2e; font-size:13px;">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
      Exporter CSV
    </a>
    <a href="<?= BASE_PATH ?>/admin/facturation" class="inline-flex items-center gap-2 font-bold rounded-lg px-4 py-2.5 border border-gray-200 text-gray-500 hover:bg-gray-50 transition-colors" style="font-size:13px;">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
      Réinitialiser
    </a>
  </div>
</form>

<!-- KPIs -->
<div class="grid sm:grid-cols-3 gap-5 mb-6">
  <div class="card card-md">
    <p class="text-sm font-semibold text-gray-500 mb-2">Chiffre d'affaires total</p>
    <p class="font-extrabold text-3xl text-ink"><?= number_format($totalRevenue, 2, ',', ' ') ?> €</p>
  </div>
  <div class="card card-md">
    <p class="text-sm font-semibold text-gray-500 mb-2">Chiffre d'affaires ce mois</p>
    <p class="font-extrabold text-3xl text-ink"><?= number_format($totalRevenueMonth, 2, ',', ' ') ?> €</p>
  </div>
  <div class="card card-md">
    <p class="text-sm font-semibold text-gray-500 mb-2">Factures ce mois</p>
    <p class="font-extrabold text-3xl text-ink"><?= $countThisMonth ?></p>
  </div>
</div>

<div class="card card-md overflow-hidden">
  <?php if (empty($invoices)): ?>
    <div class="text-center py-16 px-6">
      <p class="text-gray-500 font-medium">Aucune facture pour le moment.</p>
      <p class="text-gray-400 text-sm mt-1">Les factures apparaissent automatiquement à chaque recharge par carte bancaire.</p>
    </div>
  <?php else: ?>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50">
          <tr class="text-left text-gray-500 text-xs uppercase tracking-wide">
            <th class="px-5 py-3 font-semibold">N° Facture</th>
            <th class="px-5 py-3 font-semibold">Client</th>
            <th class="px-5 py-3 font-semibold">Date</th>
            <th class="px-5 py-3 font-semibold">Montant</th>
            <th class="px-5 py-3 font-semibold text-right">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <?php foreach ($invoices as $inv): ?>
            <tr class="hover:bg-gray-50/50">
              <td class="px-5 py-3.5 font-mono text-gray-600">#<?= str_pad((string) $inv['id'], 6, '0', STR_PAD_LEFT) ?></td>
              <td class="px-5 py-3.5 font-semibold text-ink"><?= htmlspecialchars($inv['first_name'] . ' ' . $inv['last_name']) ?></td>
              <td class="px-5 py-3.5 text-gray-500"><?= date('d/m/Y', strtotime($inv['created_at'])) ?></td>
              <td class="px-5 py-3.5 font-semibold text-ink"><?= number_format($inv['amount'], 2, ',', ' ') ?> €</td>
              <td class="px-5 py-3.5 text-right">
                <a href="<?= BASE_PATH ?>/admin/facturation/<?= $inv['id'] ?>" class="text-xs font-bold text-brand-500 hover:underline">Voir la facture</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php if ($totalPages > 1): ?>
      <div class="flex items-center justify-center gap-1 px-5 py-4 border-t border-gray-50">
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
          <a href="<?= BASE_PATH ?>/admin/facturation?<?= http_build_query(array_merge($filters, ['page' => $p])) ?>"
             class="w-8 h-8 flex items-center justify-center rounded-lg text-sm font-semibold transition-colors <?= $p === $page ? 'bg-brand-500 text-white' : 'text-gray-500 hover:bg-gray-100' ?>">
            <?= $p ?>
          </a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>
