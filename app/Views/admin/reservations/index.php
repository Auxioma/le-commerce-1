<?php use App\Core\Csrf; ?>

<!-- En-tête -->
<div class="flex items-start justify-between mb-6 gap-4 flex-wrap">
  <div>
    <h1 class="font-extrabold text-ink" style="font-size:22px; letter-spacing:-0.5px;">Réservations</h1>
    <p class="text-gray-500 mt-1" style="font-size:13px;">Demandes de réservation de table reçues via le site.</p>
  </div>
  <div class="flex items-center gap-3 shrink-0">
    <div class="flex items-center gap-3 bg-white border border-gray-200 rounded-xl px-4 py-3 shadow-sm">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 7v5l3 3"/></svg>
      <div>
        <p class="text-gray-500" style="font-size:11px; font-weight:600;">En attente</p>
        <p class="font-black text-ink" style="font-size:22px; line-height:1;"><?= $pendingCount ?></p>
      </div>
    </div>
    <div class="flex items-center gap-3 bg-white border border-gray-200 rounded-xl px-4 py-3 shadow-sm">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
      <div>
        <p class="text-gray-500" style="font-size:11px; font-weight:600;">À venir (confirmées)</p>
        <p class="font-black text-ink" style="font-size:22px; line-height:1;"><?= $upcomingCount ?></p>
      </div>
    </div>
  </div>
</div>

<!-- Filtres -->
<form method="GET" action="<?= BASE_PATH ?>/admin/reservations">
  <div class="bg-white border border-gray-200 rounded-xl px-5 py-4 mb-5 shadow-sm flex flex-wrap items-center gap-3">
    <select name="status" class="border border-gray-200 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500" style="font-size:13px;">
      <option value="tous"       <?= $filters['status'] === 'tous' ? 'selected' : '' ?>>Tous les statuts</option>
      <option value="en_attente" <?= $filters['status'] === 'en_attente' ? 'selected' : '' ?>>En attente</option>
      <option value="confirmee"  <?= $filters['status'] === 'confirmee' ? 'selected' : '' ?>>Confirmée</option>
      <option value="annulee"    <?= $filters['status'] === 'annulee' ? 'selected' : '' ?>>Annulée</option>
    </select>
    <input type="date" name="date" value="<?= htmlspecialchars($filters['date']) ?>" class="border border-gray-200 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500" style="font-size:13px;">
    <button type="submit" class="inline-flex items-center gap-2 text-white font-bold rounded-lg px-5 py-2.5 transition-opacity hover:opacity-90" style="background:#c8272c; font-size:13px;">
      FILTRER
    </button>
    <a href="<?= BASE_PATH ?>/admin/reservations" class="inline-flex items-center gap-2 font-bold rounded-lg px-4 py-2.5 border border-gray-200 text-gray-500 hover:bg-gray-50 transition-colors" style="font-size:13px;">
      Réinitialiser
    </a>
  </div>
</form>

<!-- Tableau -->
<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
  <?php if (empty($reservations)): ?>
    <div class="text-center py-16 px-6">
      <p class="text-gray-500 font-medium mb-1">Aucune réservation ne correspond à ces critères.</p>
    </div>
  <?php else: ?>
    <div class="overflow-x-auto">
      <table class="min-w-full">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr>
            <th class="px-5 py-3 text-left font-bold text-gray-500 uppercase" style="font-size:11px; letter-spacing:0.5px;">Client</th>
            <th class="px-5 py-3 text-left font-bold text-gray-500 uppercase" style="font-size:11px; letter-spacing:0.5px;">Date &amp; heure</th>
            <th class="px-5 py-3 text-left font-bold text-gray-500 uppercase" style="font-size:11px; letter-spacing:0.5px;">Personnes</th>
            <th class="px-5 py-3 text-left font-bold text-gray-500 uppercase" style="font-size:11px; letter-spacing:0.5px;">Note</th>
            <th class="px-5 py-3 text-left font-bold text-gray-500 uppercase" style="font-size:11px; letter-spacing:0.5px;">Statut</th>
            <th class="px-5 py-3 text-right font-bold text-gray-500 uppercase" style="font-size:11px; letter-spacing:0.5px;">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-100">
          <?php foreach ($reservations as $r): ?>
            <tr class="hover:bg-gray-50 transition-colors">
              <td class="px-5 py-3.5 whitespace-nowrap">
                <p class="font-semibold text-ink" style="font-size:13px;"><?= htmlspecialchars($r['name']) ?></p>
                <p class="text-gray-400" style="font-size:12px;"><?= htmlspecialchars($r['phone']) ?></p>
              </td>
              <td class="px-5 py-3.5 whitespace-nowrap text-gray-600" style="font-size:13px;">
                <?= date('d/m/Y', strtotime($r['reservation_date'])) ?> à <?= substr($r['reservation_time'], 0, 5) ?>
              </td>
              <td class="px-5 py-3.5 whitespace-nowrap text-gray-600" style="font-size:13px;"><?= (int) $r['party_size'] ?></td>
              <td class="px-5 py-3.5 text-gray-500 max-w-xs truncate" style="font-size:12.5px;"><?= htmlspecialchars($r['note'] ?? '—') ?></td>
              <td class="px-5 py-3.5 whitespace-nowrap">
                <?php $statusStyles = ['en_attente' => 'bg-amber-100 text-amber-700', 'confirmee' => 'bg-emerald-100 text-emerald-700', 'annulee' => 'bg-gray-100 text-gray-500']; ?>
                <?php $statusLabels = ['en_attente' => 'En attente', 'confirmee' => 'Confirmée', 'annulee' => 'Annulée']; ?>
                <span class="px-2.5 py-1 rounded-full font-semibold <?= $statusStyles[$r['status']] ?>" style="font-size:11.5px;">
                  <?= $statusLabels[$r['status']] ?>
                </span>
              </td>
              <td class="px-5 py-3.5 whitespace-nowrap text-right">
                <div class="inline-flex items-center gap-1.5">
                  <?php if ($r['status'] !== 'confirmee'): ?>
                    <form method="POST" action="<?= BASE_PATH ?>/admin/reservations/<?= $r['id'] ?>/statut">
                      <?= Csrf::field() ?>
                      <input type="hidden" name="status" value="confirmee">
                      <button type="submit" class="font-semibold text-emerald-600 hover:text-emerald-700 px-2" style="font-size:12px;">Confirmer</button>
                    </form>
                  <?php endif; ?>
                  <?php if ($r['status'] !== 'annulee'): ?>
                    <form method="POST" action="<?= BASE_PATH ?>/admin/reservations/<?= $r['id'] ?>/statut">
                      <?= Csrf::field() ?>
                      <input type="hidden" name="status" value="annulee">
                      <button type="submit" class="font-semibold text-gray-400 hover:text-brand-500 px-2" style="font-size:12px;">Annuler</button>
                    </form>
                  <?php endif; ?>
                  <form method="POST" action="<?= BASE_PATH ?>/admin/reservations/<?= $r['id'] ?>/supprimer"
                        onsubmit="return confirm('Supprimer cette réservation ?');">
                    <?= Csrf::field() ?>
                    <button type="submit" class="font-semibold text-gray-300 hover:text-brand-500 px-2" style="font-size:12px;">Suppr.</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
