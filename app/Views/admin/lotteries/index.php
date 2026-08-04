<?php use App\Core\Csrf; ?>

<?php
$pageTitle = 'Loterie';
$pageSubtitle = 'Créez des tirages au sort, suivez les participations et désignez un gagnant en un clic.';
$pageActions = [
    ['href' => BASE_PATH . '/admin/loterie/creer', 'label' => 'Créer une loterie', 'class' => 'btn-primary'],
];
require __DIR__ . '/../../partials/admin-page-header.php';
?>

<!-- KPIs -->
<div class="grid sm:grid-cols-3 gap-5 mb-6">
  <div class="card card-md">
    <p class="text-sm font-semibold text-gray-500 mb-2">Loteries actives</p>
    <p class="font-extrabold text-3xl text-ink"><?= $lotteriesActive ?></p>
  </div>
  <div class="card card-md">
    <p class="text-sm font-semibold text-gray-500 mb-2">Brouillons</p>
    <p class="font-extrabold text-3xl text-ink"><?= $lotteriesDraft ?></p>
  </div>
  <div class="card card-md">
    <p class="text-sm font-semibold text-gray-500 mb-2">Terminées</p>
    <p class="font-extrabold text-3xl text-ink"><?= $lotteriesDone ?></p>
  </div>
</div>

<div class="card overflow-hidden">
  <div class="flex items-center gap-1 px-5 pt-4 border-b border-gray-50 overflow-x-auto">
    <?php $tabs = ['actives' => 'Actives', 'brouillons' => 'Brouillons', 'terminees' => 'Terminées', 'toutes' => 'Toutes']; ?>
    <?php foreach ($tabs as $key => $label): ?>
      <a href="<?= BASE_PATH ?>/admin/loterie?statut=<?= $key ?>"
         class="px-4 py-3 text-sm font-semibold border-b-2 -mb-px whitespace-nowrap transition-colors
                <?= $activeTab === $key ? 'border-brand-500 text-brand-500' : 'border-transparent text-gray-400 hover:text-ink' ?>">
        <?= htmlspecialchars($label) ?>
      </a>
    <?php endforeach; ?>
  </div>

  <?php if (empty($lotteries)): ?>
    <div class="text-center py-16 px-6">
      <p class="text-gray-500 font-medium mb-1">Aucune loterie dans cette catégorie.</p>
      <a href="<?= BASE_PATH ?>/admin/loterie/creer" class="text-brand-500 text-sm font-semibold hover:underline">Créer votre première loterie</a>
    </div>
  <?php else: ?>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50">
          <tr class="text-left text-gray-500 text-xs uppercase tracking-wide">
            <th class="px-5 py-3 font-semibold">Loterie</th>
            <th class="px-5 py-3 font-semibold">Lot à gagner</th>
            <th class="px-5 py-3 font-semibold">Participants</th>
            <th class="px-5 py-3 font-semibold">Fin</th>
            <th class="px-5 py-3 font-semibold">Statut / Gagnant</th>
            <th class="px-5 py-3 font-semibold text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <?php foreach ($lotteries as $l): ?>
            <tr class="hover:bg-gray-50/50">
              <td class="px-5 py-3.5">
                <p class="font-semibold text-ink"><?= htmlspecialchars($l['title']) ?></p>
                <?php if ($l['description']): ?>
                  <p class="text-xs text-gray-400 max-w-[220px] truncate"><?= htmlspecialchars($l['description']) ?></p>
                <?php endif; ?>
              </td>
              <td class="px-5 py-3.5 text-gray-600"><?= htmlspecialchars($l['prize']) ?></td>
              <td class="px-5 py-3.5 font-semibold text-ink"><?= (int) $l['entries_count'] ?></td>
              <td class="px-5 py-3.5 text-gray-500"><?= date('d/m/Y', strtotime($l['ends_at'])) ?></td>
              <td class="px-5 py-3.5">
                <?php if ($l['status'] === 'terminee'): ?>
                  <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-100 text-gray-600">Terminée</span>
                  <?php if ($l['winner_first_name']): ?>
                    <p class="text-xs text-gray-500 mt-1">🏆 <?= htmlspecialchars($l['winner_first_name'] . ' ' . $l['winner_last_name']) ?></p>
                  <?php endif; ?>
                <?php elseif ($l['status'] === 'active'): ?>
                  <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-600">Active</span>
                <?php else: ?>
                  <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-amber-50 text-amber-600">Brouillon</span>
                <?php endif; ?>
              </td>
              <td class="px-5 py-3.5 text-right">
                <div class="inline-flex items-center gap-2">
                  <?php if ($l['status'] !== 'terminee'): ?>
                    <form method="POST" action="<?= BASE_PATH ?>/admin/loterie/<?= $l['id'] ?>/statut" class="inline">
                      <?= Csrf::field() ?>
                      <button type="submit" class="text-xs font-bold text-gray-500 hover:text-brand-500">
                        <?= $l['status'] === 'active' ? 'Passer en brouillon' : 'Activer' ?>
                      </button>
                    </form>
                    <form method="POST" action="<?= BASE_PATH ?>/admin/loterie/<?= $l['id'] ?>/tirage" class="inline"
                          onsubmit="return confirm('Tirer un gagnant au sort parmi les <?= (int) $l['entries_count'] ?> participants ? Cette action est définitive.');">
                      <?= Csrf::field() ?>
                      <button type="submit" class="text-xs font-bold text-white bg-ink hover:bg-black px-3 py-1.5 rounded-lg">
                        🎲 Tirer au sort
                      </button>
                    </form>
                  <?php endif; ?>
                  <form method="POST" action="<?= BASE_PATH ?>/admin/loterie/<?= $l['id'] ?>/supprimer" class="inline"
                        onsubmit="return confirm('Supprimer cette loterie ?');">
                    <?= Csrf::field() ?>
                    <button type="submit" class="text-xs font-semibold text-gray-300 hover:text-brand-500">Suppr.</button>
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
