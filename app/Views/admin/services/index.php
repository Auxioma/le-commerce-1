<?php use App\Core\Csrf; ?>

<?php
$pageTitle = 'Services du quotidien';
$pageSubtitle = 'Gérez le catalogue de services affiché sur la page publique « Nos Services » (relais colis, factures, recharges...).';
$pageActions = [
    ['href' => BASE_PATH . '/admin/services/creer', 'label' => 'Ajouter un service', 'class' => 'btn-primary'],
];
require __DIR__ . '/../../partials/admin-page-header.php';
?>

<!-- KPIs -->
<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-6">
  <div class="card card-md">
    <p class="text-sm font-semibold text-gray-500 mb-2">Services actifs</p>
    <p class="font-extrabold text-3xl text-ink"><?= $activeCount ?></p>
    <p class="text-xs text-gray-400 mt-1">Visibles sur /nos-services</p>
  </div>
  <div class="card card-md">
    <p class="text-sm font-semibold text-gray-500 mb-2">Services inactifs</p>
    <p class="font-extrabold text-3xl text-ink"><?= $inactiveCount ?></p>
    <p class="text-xs text-gray-400 mt-1">Masqués côté public</p>
  </div>
  <div class="card card-md">
    <p class="text-sm font-semibold text-gray-500 mb-2">Nouveaux ce mois-ci</p>
    <p class="font-extrabold text-3xl text-ink"><?= $newThisMonth ?></p>
  </div>
</div>

<div class="card overflow-hidden">
  <?php if (empty($services)): ?>
    <div class="text-center py-16 px-6">
      <p class="text-gray-500 font-medium mb-1">Aucun service pour le moment.</p>
      <a href="<?= BASE_PATH ?>/admin/services/creer" class="text-brand-500 text-sm font-semibold hover:underline">Ajouter votre premier service</a>
    </div>
  <?php else: ?>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50">
          <tr class="text-left text-gray-500 text-xs uppercase tracking-wide">
            <th class="px-5 py-3 font-semibold">Service</th>
            <th class="px-5 py-3 font-semibold">Description</th>
            <th class="px-5 py-3 font-semibold">Statut</th>
            <th class="px-5 py-3 font-semibold text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <?php foreach ($services as $service): ?>
            <tr class="hover:bg-gray-50/50">
              <td class="px-5 py-3.5">
                <div class="flex items-center gap-3">
                  <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="<?= htmlspecialchars($service['icon']) ?>"/>
                    </svg>
                  </span>
                  <p class="font-semibold text-ink"><?= htmlspecialchars($service['name']) ?></p>
                </div>
              </td>
              <td class="px-5 py-3.5 text-gray-500 max-w-[320px] truncate"><?= htmlspecialchars($service['description'] ?? '') ?></td>
              <td class="px-5 py-3.5">
                <?php if ($service['status'] === 'active'): ?>
                  <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-600">Actif</span>
                <?php else: ?>
                  <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-100 text-gray-500">Inactif</span>
                <?php endif; ?>
              </td>
              <td class="px-5 py-3.5 text-right">
                <div class="inline-flex items-center gap-3">
                  <a href="<?= BASE_PATH ?>/admin/services/<?= $service['id'] ?>/modifier" class="text-xs font-bold text-gray-500 hover:text-brand-500">Modifier</a>
                  <form method="POST" action="<?= BASE_PATH ?>/admin/services/<?= $service['id'] ?>/statut" class="inline">
                    <?= Csrf::field() ?>
                    <button type="submit" class="text-xs font-bold text-gray-500 hover:text-brand-500">
                      <?= $service['status'] === 'active' ? 'Désactiver' : 'Activer' ?>
                    </button>
                  </form>
                  <form method="POST" action="<?= BASE_PATH ?>/admin/services/<?= $service['id'] ?>/supprimer" class="inline"
                        onsubmit="return confirm('Supprimer définitivement ce service ?');">
                    <?= Csrf::field() ?>
                    <button type="submit" class="text-xs font-bold text-brand-500 hover:text-brand-600">Supprimer</button>
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
