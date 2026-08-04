<?php use App\Core\Csrf; ?>

<?php
$pageTitle = 'Créer une loterie';
$pageSubtitle = 'Configurez un nouveau tirage au sort pour vos clients.';
$pageActions = [];
require __DIR__ . '/../../partials/admin-page-header.php';
?>

<div class="max-w-2xl">
  <a href="<?= BASE_PATH ?>/admin/loterie" class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-500 hover:text-brand-500 mb-4">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    Retour à la loterie
  </a>

  <div class="card card-md sm:p-8">
    <form method="POST" action="<?= BASE_PATH ?>/admin/loterie" class="space-y-5">
      <?= Csrf::field() ?>

      <div>
        <label class="block text-sm font-semibold text-ink mb-1.5">Titre de la loterie</label>
        <input type="text" name="title" required value="<?= htmlspecialchars($old['title'] ?? '') ?>" placeholder="Ex : Tirage de rentrée"
               class="form-input <?= isset($errors['title']) ? 'border-brand-400' : '' ?>">
        <?php if (isset($errors['title'])): ?><p class="text-brand-500 text-xs mt-1"><?= htmlspecialchars($errors['title']) ?></p><?php endif; ?>
      </div>

      <div>
        <label class="block text-sm font-semibold text-ink mb-1.5">Description</label>
        <textarea name="description" rows="2" placeholder="Ex : Participez pour tenter de remporter..."
                  class="form-textarea"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
      </div>

      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-semibold text-ink mb-1.5">Lot à gagner</label>
          <input type="text" name="prize" required value="<?= htmlspecialchars($old['prize'] ?? '') ?>" placeholder="Ex : Panier gourmand (valeur 40€)"
                 class="form-input <?= isset($errors['prize']) ? 'border-brand-400' : '' ?>">
          <?php if (isset($errors['prize'])): ?><p class="text-brand-500 text-xs mt-1"><?= htmlspecialchars($errors['prize']) ?></p><?php endif; ?>
        </div>
        <div>
          <label class="block text-sm font-semibold text-ink mb-1.5">Date de fin des inscriptions</label>
          <input type="date" name="ends_at" required value="<?= htmlspecialchars($old['endsAt'] ?? '') ?>" min="<?= date('Y-m-d') ?>"
                 class="form-input <?= isset($errors['ends_at']) ? 'border-brand-400' : '' ?>">
          <?php if (isset($errors['ends_at'])): ?><p class="text-brand-500 text-xs mt-1"><?= htmlspecialchars($errors['ends_at']) ?></p><?php endif; ?>
        </div>
      </div>

      <label class="flex items-center gap-2.5 text-sm text-gray-600 bg-gray-50 rounded-2xl px-4 py-3">
        <input type="checkbox" name="publish" value="1" checked class="rounded border-gray-300 text-brand-500 focus:ring-brand-500/30">
        Publier immédiatement (sinon la loterie est enregistrée en brouillon)
      </label>

      <button type="submit" class="btn-primary w-full">
        Créer la loterie
      </button>
    </form>
  </div>
</div>
