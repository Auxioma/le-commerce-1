<?php use App\Core\Csrf; ?>

<?php
$pageTitle = 'Modifier un service';
$pageSubtitle = 'Ajustez le nom, la description, l\'icône ou le statut de publication de ce service.';
$pageActions = [];
require __DIR__ . '/../../partials/admin-page-header.php';

$name        = $old['name'] ?? $service['name'];
$description = $old['description'] ?? ($service['description'] ?? '');
$icon        = $old['icon'] ?? $service['icon'];
$publish     = array_key_exists('publish', $old) ? $old['publish'] : ($service['status'] === 'active');
?>

<div class="max-w-2xl">
  <a href="<?= BASE_PATH ?>/admin/services" class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-500 hover:text-brand-500 mb-4">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    Retour aux services
  </a>

  <div class="card card-md sm:p-8">
    <form method="POST" action="<?= BASE_PATH ?>/admin/services/<?= $service['id'] ?>" class="space-y-5">
      <?= Csrf::field() ?>

      <div>
        <label class="block text-sm font-semibold text-ink mb-1.5">Nom du service</label>
        <input type="text" name="name" required value="<?= htmlspecialchars($name) ?>"
               class="form-input <?= isset($errors['name']) ? 'border-brand-400' : '' ?>">
        <?php if (isset($errors['name'])): ?><p class="text-brand-500 text-xs mt-1"><?= htmlspecialchars($errors['name']) ?></p><?php endif; ?>
      </div>

      <div>
        <label class="block text-sm font-semibold text-ink mb-1.5">Description</label>
        <textarea name="description" rows="2" class="form-textarea"><?= htmlspecialchars($description) ?></textarea>
      </div>

      <div>
        <label class="block text-sm font-semibold text-ink mb-1.5">Icône</label>
        <select name="icon" required class="form-select <?= isset($errors['icon']) ? 'border-brand-400' : '' ?>">
          <option value="">Choisir une icône</option>
          <?php foreach ($iconChoices as $path => $label): ?>
            <option value="<?= htmlspecialchars($path) ?>" <?= $icon === $path ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
          <?php endforeach; ?>
        </select>
        <?php if (isset($errors['icon'])): ?><p class="text-brand-500 text-xs mt-1"><?= htmlspecialchars($errors['icon']) ?></p><?php endif; ?>
      </div>

      <label class="flex items-center gap-2.5 text-sm text-gray-600 bg-gray-50 rounded-2xl px-4 py-3">
        <input type="checkbox" name="publish" value="1" <?= $publish ? 'checked' : '' ?> class="rounded border-gray-300 text-brand-500 focus:ring-brand-500/30">
        Publié sur /nos-services
      </label>

      <button type="submit" class="btn-primary w-full">
        Enregistrer les modifications
      </button>
    </form>
  </div>
</div>
