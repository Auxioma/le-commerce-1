<?php use App\Core\Csrf; ?>

<?php
$pageTitle = 'Sondages';
$pageSubtitle = 'Collectez l’avis de vos clients grâce à des sondages simples et engageants.';
$pageActions = [
    ['href' => BASE_PATH . '/admin/sondages/creer', 'label' => 'Créer un sondage', 'class' => 'btn-primary'],
];
require __DIR__ . '/../../partials/admin-page-header.php';
?>

<!-- KPIs -->
<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
  <div class="card card-md">
    <p class="text-sm font-semibold text-gray-500 mb-2">Sondages actifs</p>
    <p class="font-extrabold text-3xl text-ink"><?= $pollsActive ?></p>
    <p class="text-xs font-semibold text-emerald-500 mt-1">+<?= $pollsDelta ?> ce mois-ci</p>
  </div>
  <div class="card card-md">
    <p class="text-sm font-semibold text-gray-500 mb-2">Total participations</p>
    <p class="font-extrabold text-3xl text-ink"><?= $totalParticipations ?></p>
  </div>
  <div class="card card-md">
    <p class="text-sm font-semibold text-gray-500 mb-2">Taux de participation moyen</p>
    <p class="font-extrabold text-3xl text-ink"><?= $participationRate ?>%</p>
  </div>
  <div class="card card-md">
    <p class="text-sm font-semibold text-gray-500 mb-2">Récompenses offertes</p>
    <p class="font-extrabold text-3xl text-ink"><?= number_format($rewardsGiven, 2, ',', ' ') ?> €</p>
  </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">

  <!-- Liste des sondages -->
  <div class="lg:col-span-2 card overflow-hidden">
    <div class="flex items-center gap-1 px-5 pt-4 border-b border-gray-50 overflow-x-auto">
      <?php $tabs = ['actifs' => 'Sondages actifs', 'programmes' => 'Sondages programmés', 'termines' => 'Sondages terminés', 'tous' => 'Tous les sondages']; ?>
      <?php foreach ($tabs as $key => $label): ?>
        <a href="<?= BASE_PATH ?>/admin/sondages?statut=<?= $key ?>"
           class="px-4 py-3 text-sm font-semibold border-b-2 -mb-px whitespace-nowrap transition-colors
                  <?= $activeTab === $key ? 'border-brand-500 text-brand-500' : 'border-transparent text-gray-400 hover:text-ink' ?>">
          <?= htmlspecialchars($label) ?>
        </a>
      <?php endforeach; ?>
    </div>

    <?php if (empty($polls)): ?>
      <div class="text-center py-16 px-6">
        <p class="text-gray-500 font-medium mb-1">Aucun sondage dans cette catégorie.</p>
        <a href="<?= BASE_PATH ?>/admin/sondages/creer" class="text-brand-500 text-sm font-semibold hover:underline">Créer votre premier sondage</a>
      </div>
    <?php else: ?>
      <div class="divide-y divide-gray-50">
        <?php foreach ($polls as $poll): ?>
          <div class="flex items-center justify-between gap-4 px-5 py-4 hover:bg-gray-50/50">
            <div class="min-w-0">
              <a href="<?= BASE_PATH ?>/admin/sondages/<?= $poll['id'] ?>/resultats" class="font-semibold text-ink hover:text-brand-500 transition-colors">
                <?= htmlspecialchars($poll['question']) ?>
              </a>
              <p class="text-xs text-gray-400 mt-0.5">
                <?= $poll['participations'] ?> participation<?= $poll['participations'] > 1 ? 's' : '' ?> ·
                Fin le <?= date('d/m/Y', strtotime($poll['ends_at'])) ?>
              </p>
            </div>
            <div class="flex items-center gap-3 shrink-0">
              <?php
                $statusStyles = ['actif' => 'bg-emerald-50 text-emerald-600', 'programme' => 'bg-blue-50 text-blue-600', 'termine' => 'bg-gray-100 text-gray-500'];
                $statusLabels = ['actif' => 'Actif', 'programme' => 'Programmé', 'termine' => 'Terminé'];
              ?>
              <span class="text-xs font-semibold px-2.5 py-1 rounded-full <?= $statusStyles[$poll['status']] ?? '' ?>"><?= $statusLabels[$poll['status']] ?? $poll['status'] ?></span>
              <form method="POST" action="<?= BASE_PATH ?>/admin/sondages/<?= $poll['id'] ?>/statut">
                <?= Csrf::field() ?>
                <button type="submit" class="text-xs font-bold text-gray-400 hover:text-brand-500">
                  <?= $poll['status'] === 'actif' ? 'Terminer' : 'Activer' ?>
                </button>
              </form>
              <a href="<?= BASE_PATH ?>/admin/sondages/<?= $poll['id'] ?>/modifier" class="text-xs font-bold text-gray-400 hover:text-brand-500">Modifier</a>
              <form method="POST" action="<?= BASE_PATH ?>/admin/sondages/<?= $poll['id'] ?>/supprimer"
                    onsubmit="return confirm('Supprimer définitivement ce sondage ?');">
                <?= Csrf::field() ?>
                <button type="submit" class="text-xs font-bold text-gray-300 hover:text-brand-500">Suppr.</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Panneaux latéraux -->
  <div class="flex flex-col gap-6">

    <!-- Sondage à la une -->
    <div class="card card-md">
      <h2 class="font-bold text-ink mb-4">Sondage à la une</h2>
      <?php if (!$featured): ?>
        <p class="text-sm text-gray-400">Aucun sondage actif pour le moment.</p>
      <?php else: ?>
        <p class="font-semibold text-ink text-sm mb-1"><?= htmlspecialchars($featured['question']) ?></p>
        <?php if ($featured['description']): ?><p class="text-xs text-gray-400 mb-4"><?= htmlspecialchars($featured['description']) ?></p><?php endif; ?>

        <div class="space-y-2.5 mb-4">
          <?php foreach ($featuredOptions as $opt): ?>
            <?php $pct = $featuredTotal > 0 ? round(($opt['votes_count'] / $featuredTotal) * 100) : 0; ?>
            <div>
              <div class="flex justify-between text-xs mb-1">
                <span class="text-gray-600 font-medium"><?= htmlspecialchars($opt['label']) ?></span>
                <span class="text-gray-400"><?= $pct ?>%</span>
              </div>
              <div class="w-full bg-gray-100 rounded-full h-2">
                <div class="bg-brand-500 h-2 rounded-full" style="width: <?= $pct ?>%"></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <p class="text-xs text-gray-400 mb-3"><?= $featuredTotal ?> participations</p>
        <a href="<?= BASE_PATH ?>/admin/sondages/<?= $featured['id'] ?>/resultats" class="block text-center bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm px-5 py-3 rounded-lg transition-colors">
          Voir les résultats détaillés
        </a>
      <?php endif; ?>
    </div>

    <!-- Types de récompense -->
    <div class="card card-md">
      <h2 class="section-title mb-1">Récompense pour participation</h2>
      <p class="text-xs text-gray-400 mb-4">Remerciez vos clients pour leur avis !</p>
      <div class="grid grid-cols-1 gap-2">
        <?php foreach ($rewardLabels as $key => $label): ?>
          <div class="flex items-center gap-3 bg-gray-50 rounded-lg px-4 py-3">
            <span class="w-8 h-8 rounded-full bg-white text-brand-500 flex items-center justify-center shrink-0">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </span>
            <span class="text-sm font-medium text-ink"><?= htmlspecialchars($label) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
