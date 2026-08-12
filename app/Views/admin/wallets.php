<div class="space-y-6">
  <?php
  $pageTitle = 'Portefeuille client';
  $pageSubtitle = 'Suivez le flux de recharges, le solde global et les clients qui dépensent le plus.';
  $pageActions = [
    ['href' => BASE_PATH . '/admin/clients', 'label' => 'Voir les clients', 'class' => 'btn-primary'],
  ];
  require __DIR__ . '/../partials/admin-page-header.php';
  ?>

  <!-- Filtres -->
  <form method="GET" action="<?= BASE_PATH ?>/admin/portefeuilles">
    <div class="bg-white border border-gray-200 rounded-xl px-5 py-4 shadow-sm flex flex-wrap items-center gap-3">
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
      <a href="<?= BASE_PATH ?>/admin/portefeuilles" class="inline-flex items-center gap-2 font-bold rounded-lg px-4 py-2.5 border border-gray-200 text-gray-500 hover:bg-gray-50 transition-colors" style="font-size:13px;">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        Réinitialiser
      </a>
    </div>
  </form>

  <div class="grid gap-5 lg:grid-cols-4">
    <div class="card card-md">
      <p class="text-xs uppercase tracking-[0.2em] text-gray-500 font-semibold mb-3">Solde total</p>
      <p class="text-4xl font-extrabold text-ink"><?= number_format($totalBalance, 2, ',', ' ') ?> €</p>
      <p class="text-xs text-gray-400 mt-2">Solde cumulatif de tous les portefeuilles</p>
    </div>
    <div class="card card-md">
      <p class="text-xs uppercase tracking-[0.2em] text-gray-500 font-semibold mb-3">Portefeuilles</p>
      <p class="text-4xl font-extrabold text-ink"><?= number_format($walletCount) ?></p>
      <p class="text-xs text-gray-400 mt-2">Clients disposant d’un portefeuille</p>
    </div>
    <div class="card card-md">
      <p class="text-xs uppercase tracking-[0.2em] text-gray-500 font-semibold mb-3">Recharges</p>
      <p class="text-4xl font-extrabold text-ink"><?= number_format($rechargesThisMonth) ?></p>
      <p class="text-xs text-gray-400 mt-2">Transactions de recharge ce mois</p>
    </div>
    <div class="card card-md">
      <p class="text-xs uppercase tracking-[0.2em] text-gray-500 font-semibold mb-3">Top clients</p>
      <p class="text-4xl font-extrabold text-ink"><?= number_format(count($topClients)) ?></p>
      <p class="text-xs text-gray-400 mt-2">Clients les mieux crédités</p>
    </div>
  </div>

  <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
    <div class="card card-md">
      <div class="flex items-center justify-between mb-4">
        <div>
          <p class="text-sm font-semibold text-gray-500 uppercase tracking-[0.18em]">Dernières transactions</p>
          <h2 class="text-xl font-bold text-ink">Historique rapide</h2>
        </div>
        <a href="<?= BASE_PATH ?>/admin/clients" class="text-sm font-semibold text-brand-500 hover:underline">Gérer les clients</a>
      </div>

      <?php if (empty($latestTransactions)): ?>
        <p class="text-sm text-gray-400 py-8 text-center">Aucune transaction récente.</p>
      <?php else: ?>
        <div class="overflow-x-auto -mx-4 px-4">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="text-left text-gray-400 text-xs uppercase tracking-wide">
                <th class="px-2 py-2">Client</th>
                <th class="px-2 py-2">Type</th>
                <th class="px-2 py-2">Montant</th>
                <th class="px-2 py-2">Moyen</th>
                <th class="px-2 py-2">Date</th>
                <th class="px-2 py-2 text-right">Action</th>
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
                  <td class="px-2 py-3 text-gray-500"><?= htmlspecialchars(str_replace('_', ' ', $tx['payment_method'])) ?></td>
                  <td class="px-2 py-3 text-gray-500"><?= date('d/m/Y', strtotime($tx['created_at'])) ?></td>
                  <td class="px-2 py-3 text-right">
                    <a href="<?= BASE_PATH ?>/admin/clients/<?= $tx['user_id'] ?>" class="inline-flex items-center gap-1 text-xs font-semibold text-brand-500 hover:underline">
                      Voir fiche
                      <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <div class="card card-md bg-brand-50 border-brand-100 text-brand-900">
      <h2 class="font-bold text-ink mb-4">Gestion portefeuille</h2>
      <p class="text-sm leading-6">Les portefeuilles clients renforcent la fidélité et facilitent les paiements en boutique. Vérifiez régulièrement les recharges et les comptes inactifs.</p>
      <?php if (!empty($topClients)): ?>
      <div class="mt-6 space-y-3">
        <p class="font-semibold text-sm">Top clients crédités</p>
        <?php foreach ($topClients as $c): ?>
          <a href="<?= BASE_PATH ?>/admin/clients/<?= $c['user_id'] ?>" class="block rounded-xl bg-white/80 p-3 hover:bg-white transition-colors">
            <div class="flex items-center justify-between">
              <div>
                <p class="font-semibold text-sm text-ink"><?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?></p>
                <p class="text-xs text-gray-500"><?= htmlspecialchars($c['phone_whatsapp']) ?></p>
              </div>
              <p class="font-bold text-sm text-emerald-600"><?= number_format($c['balance'], 2, ',', ' ') ?> €</p>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
