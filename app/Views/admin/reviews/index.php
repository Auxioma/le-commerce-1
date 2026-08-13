<?php use App\Core\Csrf; ?>

<?php
$pageTitle = 'Avis clients';
$pageSubtitle = 'Suivez, ajoutez et gérez les avis de vos clients pour améliorer votre réputation en ligne.';
$pageActions = [];
require __DIR__ . '/../../partials/admin-page-header.php';
?>



<div class="grid lg:grid-cols-3 gap-6">

  <!-- Résumé -->
  <div class="card card-md text-center">
    <p class="font-extrabold text-5xl text-ink mb-1"><?= number_format($average, 1) ?></p>
    <div class="flex items-center justify-center gap-1 text-amber-400 mb-2">
      <?php for ($i = 0; $i < 5; $i++): ?>
        <svg class="w-5 h-5" fill="<?= $i < round($average) ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="1.5" viewBox="0 0 20 20"><path d="M10 15l-5.5 3 2-6.5L1 7h6.5L10 1l2.5 6H19l-5.5 4.5 2 6.5z"/></svg>
      <?php endfor; ?>
    </div>
    <p class="text-sm text-gray-400">Basé sur <?= $totalReviews ?> avis Google</p>

    <div class="mt-5 space-y-1.5 text-left">
      <?php for ($star = 5; $star >= 1; $star--): ?>
        <?php $pct = $countLoaded > 0 ? round(($distribution[$star] / $countLoaded) * 100) : 0; ?>
        <div class="flex items-center gap-2 text-xs">
          <span class="text-gray-500 w-3"><?= $star ?></span>
          <svg class="w-3 h-3 text-amber-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M10 15l-5.5 3 2-6.5L1 7h6.5L10 1l2.5 6H19l-5.5 4.5 2 6.5z"/></svg>
          <div class="flex-1 bg-gray-100 rounded-full h-1.5">
            <div class="bg-amber-400 h-1.5 rounded-full" style="width: <?= $pct ?>%"></div>
          </div>
          <span class="text-gray-400 w-6 text-right"><?= $distribution[$star] ?></span>
        </div>
      <?php endfor; ?>
    </div>
  </div>

  <!-- Ajouter un avis -->
  <div class="card card-md">
    <h2 class="section-title mb-1">Ajouter un avis</h2>
    <p class="text-sm text-gray-500 mb-4">Synchronisation automatique via l'API Google Places à venir — ajout manuel pour l'instant.</p>
    <form method="POST" action="<?= BASE_PATH ?>/admin/avis-google" class="space-y-3">
      <?= Csrf::field() ?>
      <input type="text" name="author_name" required placeholder="Nom du client"
             class="form-input">
      <select name="rating" class="form-select">
        <option value="5">5 étoiles</option>
        <option value="4">4 étoiles</option>
        <option value="3">3 étoiles</option>
        <option value="2">2 étoiles</option>
        <option value="1">1 étoile</option>
      </select>
      <textarea name="comment" rows="3" placeholder="Commentaire (facultatif)"
                class="form-textarea"></textarea>
      <button type="submit" class="btn-primary w-full">
        Ajouter
      </button>
    </form>
  </div>

  <!-- Lien Google -->
  <div class="card card-md bg-ink text-white flex flex-col justify-between">
    <div>
      <h2 class="font-bold mb-1">Votre fiche Google</h2>
      <p class="text-gray-300 text-sm">Partagez ce lien à vos clients pour qu'ils laissent un avis directement sur Google.</p>
    </div>
    <a href="https://www.google.com/maps" target="_blank" rel="noopener" class="mt-4 inline-flex items-center justify-center gap-2 bg-white text-ink font-bold text-sm px-5 py-3 rounded-lg hover:bg-gray-100 transition-colors">
      Voir la fiche Google
    </a>
  </div>
</div>

<!-- Liste des avis -->
<div class="bg-white border border-gray-100 rounded-2xl overflow-hidden mt-6">
  <div class="px-5 py-4 border-b border-gray-50">
    <h2 class="font-bold text-ink">Tous les avis</h2>
  </div>
  <?php if (empty($reviews)): ?>
    <div class="text-center py-16 px-6">
      <p class="text-gray-500 font-medium">Aucun avis pour le moment.</p>
    </div>
  <?php else: ?>
    <div class="divide-y divide-gray-50">
      <?php foreach ($reviews as $r): ?>
        <div class="px-5 py-4">
          <div class="flex items-start justify-between gap-4">
            <div class="flex items-start gap-3 min-w-0">
              <span class="w-9 h-9 rounded-full bg-brand-50 text-brand-500 flex items-center justify-center text-xs font-bold shrink-0">
                <?= htmlspecialchars(mb_substr($r['author_name'], 0, 1)) ?>
              </span>
              <div class="min-w-0">
                <div class="flex items-center gap-2">
                  <p class="font-semibold text-ink text-sm"><?= htmlspecialchars($r['author_name']) ?></p>
                  <div class="flex text-amber-400">
                    <?php for ($i = 0; $i < 5; $i++): ?>
                      <svg class="w-3 h-3" fill="<?= $i < $r['rating'] ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="1.5" viewBox="0 0 20 20"><path d="M10 15l-5.5 3 2-6.5L1 7h6.5L10 1l2.5 6H19l-5.5 4.5 2 6.5z"/></svg>
                    <?php endfor; ?>
                  </div>
                </div>
                <?php if ($r['comment']): ?><p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($r['comment']) ?></p><?php endif; ?>
                <p class="text-xs text-gray-400 mt-1"><?= date('d/m/Y', strtotime($r['published_at'])) ?></p>
              </div>
            </div>
            <div class="flex items-center gap-3 shrink-0">
              <button type="button" onclick="document.getElementById('edit-review-<?= $r['id'] ?>').classList.toggle('hidden')" class="text-xs font-semibold text-gray-400 hover:text-brand-500">Modifier</button>
              <form method="POST" action="<?= BASE_PATH ?>/admin/avis-google/<?= $r['id'] ?>/supprimer"
                    onsubmit="return confirm('Supprimer cet avis ?');">
                <?= Csrf::field() ?>
                <button type="submit" class="text-xs font-semibold text-gray-300 hover:text-brand-500">Supprimer</button>
              </form>
            </div>
          </div>

          <form id="edit-review-<?= $r['id'] ?>" method="POST" action="<?= BASE_PATH ?>/admin/avis-google/<?= $r['id'] ?>" class="hidden mt-3 pl-12 space-y-2">
            <?= Csrf::field() ?>
            <input type="text" name="author_name" required value="<?= htmlspecialchars($r['author_name']) ?>" class="form-input">
            <select name="rating" class="form-select">
              <?php for ($star = 5; $star >= 1; $star--): ?>
                <option value="<?= $star ?>" <?= (int) $r['rating'] === $star ? 'selected' : '' ?>><?= $star ?> étoile<?= $star > 1 ? 's' : '' ?></option>
              <?php endfor; ?>
            </select>
            <textarea name="comment" rows="2" class="form-textarea"><?= htmlspecialchars($r['comment'] ?? '') ?></textarea>
            <button type="submit" class="text-xs font-bold text-white bg-brand-500 hover:bg-brand-600 px-4 py-2 rounded-lg">Enregistrer</button>
          </form>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
