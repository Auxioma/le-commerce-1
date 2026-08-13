<?php
/**
 * Formulaire partagé Offre (création + édition).
 * Attend : $formAction, $submitLabel, $typeLabels, $segmentLabels, $errors, $old
 */
use App\Core\Csrf;
?>
<form method="POST" action="<?= BASE_PATH . $formAction ?>" class="space-y-5">
  <?= Csrf::field() ?>

  <div>
    <label class="block text-sm font-semibold text-ink mb-1.5">Nom de l'offre</label>
    <input type="text" name="title" required value="<?= htmlspecialchars($old['title'] ?? '') ?>" placeholder="Ex : Café offert"
           class="form-input <?= isset($errors['title']) ? 'border-brand-400' : '' ?>">
    <?php if (isset($errors['title'])): ?><p class="text-brand-500 text-xs mt-1"><?= htmlspecialchars($errors['title']) ?></p><?php endif; ?>
  </div>

  <div>
    <label class="block text-sm font-semibold text-ink mb-1.5">Description</label>
    <textarea name="description" rows="2" placeholder="Ex : Un café offert pour bien commencer la journée !"
              class="form-textarea"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
  </div>

  <div class="grid sm:grid-cols-2 gap-4">
    <div>
      <label class="block text-sm font-semibold text-ink mb-1.5">Type d'offre</label>
      <select name="type" id="offer-type" required class="form-select <?= isset($errors['type']) ? 'border-brand-400' : '' ?>">
        <option value="">Choisir un type</option>
        <?php foreach ($typeLabels as $key => $label): ?>
          <option value="<?= $key ?>" <?= ($old['type'] ?? '') === $key ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
        <?php endforeach; ?>
      </select>
      <?php if (isset($errors['type'])): ?><p class="text-brand-500 text-xs mt-1"><?= htmlspecialchars($errors['type']) ?></p><?php endif; ?>
    </div>

    <div>
      <label class="block text-sm font-semibold text-ink mb-1.5" id="value-label">Valeur estimée (€)</label>
      <input type="number" name="value" step="0.01" min="0" value="<?= htmlspecialchars($old['value'] ?? '') ?>" placeholder="Ex : 3.50"
             class="form-input <?= isset($errors['value']) ? 'border-brand-400' : '' ?>">
      <?php if (isset($errors['value'])): ?><p class="text-brand-500 text-xs mt-1"><?= htmlspecialchars($errors['value']) ?></p><?php endif; ?>
    </div>
  </div>

  <div class="grid sm:grid-cols-2 gap-4">
    <div>
      <label class="block text-sm font-semibold text-ink mb-1.5">Clients concernés</label>
      <select name="target_segment" required class="form-select">
        <?php foreach ($segmentLabels as $key => $label): ?>
          <option value="<?= $key ?>" <?= ($old['segment'] ?? 'tous') === $key ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block text-sm font-semibold text-ink mb-1.5">Valable jusqu'au</label>
      <input type="date" name="valid_until" required value="<?= htmlspecialchars($old['validUntil'] ?? '') ?>"
             class="form-input <?= isset($errors['valid_until']) ? 'border-brand-400' : '' ?>">
      <?php if (isset($errors['valid_until'])): ?><p class="text-brand-500 text-xs mt-1"><?= htmlspecialchars($errors['valid_until']) ?></p><?php endif; ?>
    </div>
  </div>

  <label class="flex items-center gap-2.5 text-sm text-gray-600 bg-gray-50 rounded-2xl px-4 py-3">
    <input type="checkbox" name="publish" value="1" <?= ($old['publish'] ?? true) ? 'checked' : '' ?> class="rounded border-gray-300 text-brand-500 focus:ring-brand-500/30">
    Publier immédiatement (sinon l'offre est enregistrée en brouillon)
  </label>

  <button type="submit" class="btn-primary w-full">
    <?= htmlspecialchars($submitLabel) ?>
  </button>
</form>

<script>
  const valueLabels = {
    'reduction_pourcentage': 'Pourcentage de réduction (%)',
    'gratuite': 'Valeur estimée offerte (€)',
    'x_plus_1': 'Valeur du produit offert (€)',
    'montant_minimum': 'Montant minimum d\'achat (€)',
    'personnalisee': 'Valeur estimée (€)',
  };
  document.getElementById('offer-type').addEventListener('change', (e) => {
    document.getElementById('value-label').textContent = valueLabels[e.target.value] || 'Valeur estimée (€)';
  });
</script>
