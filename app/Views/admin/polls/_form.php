<?php
/**
 * Formulaire partagé Sondage (création + édition).
 * Attend : $formAction, $submitLabel, $rewardLabels, $errors, $old, $optionRows
 * $optionRows : liste de ['id' => int|null, 'label' => string, 'votes_count' => int|null]
 */
use App\Core\Csrf;
?>
<?php if (isset($errors['options'])): ?>
  <div class="flex items-center gap-2 bg-brand-50 text-brand-700 border border-brand-100 rounded-lg px-4 py-3 text-sm mb-5">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 8v5M12 16h.01"/></svg>
    <?= htmlspecialchars($errors['options']) ?>
  </div>
<?php endif; ?>

<form method="POST" action="<?= BASE_PATH . $formAction ?>" class="space-y-5">
  <?= Csrf::field() ?>

  <div>
    <label class="block text-sm font-semibold text-ink mb-1.5">Question</label>
    <input type="text" name="question" required value="<?= htmlspecialchars($old['question'] ?? '') ?>" placeholder="Ex : Quelle bière souhaitez-vous en pression ?"
           class="form-input <?= isset($errors['question']) ? 'border-brand-400' : '' ?>">
    <?php if (isset($errors['question'])): ?><p class="text-brand-500 text-xs mt-1"><?= htmlspecialchars($errors['question']) ?></p><?php endif; ?>
  </div>

  <div>
    <label class="block text-sm font-semibold text-ink mb-1.5">Description <span class="text-gray-400 font-normal">(facultatif)</span></label>
    <textarea name="description" rows="2" placeholder="Ex : Choisissez votre bière préférée !"
              class="form-textarea"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
  </div>

  <div>
    <label class="block text-sm font-semibold text-ink mb-1.5">Options de réponse</label>
    <div id="options-list" class="space-y-2">
      <?php $rows = !empty($optionRows) ? $optionRows : [['id' => null, 'label' => '', 'votes_count' => null], ['id' => null, 'label' => '', 'votes_count' => null]]; ?>
      <?php foreach ($rows as $i => $opt): ?>
        <div class="flex items-center gap-2 option-row">
          <input type="hidden" name="options[<?= $i ?>][id]" value="<?= (int) ($opt['id'] ?? 0) ?>">
          <input type="text" name="options[<?= $i ?>][label]" value="<?= htmlspecialchars($opt['label'] ?? '') ?>" required placeholder="Option <?= $i + 1 ?>"
                 class="form-input">
          <?php if (isset($opt['votes_count']) && $opt['votes_count'] !== null): ?>
            <span class="text-xs text-gray-400 shrink-0 whitespace-nowrap"><?= (int) $opt['votes_count'] ?> vote<?= (int) $opt['votes_count'] > 1 ? 's' : '' ?></span>
          <?php endif; ?>
          <button type="button" onclick="this.closest('.option-row').remove()" class="text-gray-300 hover:text-brand-500 p-2" aria-label="Retirer">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>
      <?php endforeach; ?>
    </div>
    <button type="button" id="add-option" class="mt-2 text-sm font-semibold text-brand-500 hover:underline inline-flex items-center gap-1">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
      Ajouter une option
    </button>
    <?php if (!empty($optionRows)): ?>
      <p class="text-xs text-gray-400 mt-2">Les options ayant déjà reçu des votes ne peuvent pas être supprimées ici pour préserver l'historique ; vous pouvez uniquement renommer leur libellé ou en ajouter de nouvelles.</p>
    <?php endif; ?>
  </div>

  <div class="grid sm:grid-cols-2 gap-4">
    <div>
      <label class="block text-sm font-semibold text-ink mb-1.5">Fin du sondage</label>
      <input type="date" name="ends_at" required value="<?= htmlspecialchars($old['endsAt'] ?? '') ?>"
             class="form-input <?= isset($errors['ends_at']) ? 'border-brand-400' : '' ?>">
      <?php if (isset($errors['ends_at'])): ?><p class="text-brand-500 text-xs mt-1"><?= htmlspecialchars($errors['ends_at']) ?></p><?php endif; ?>
    </div>
    <div>
      <label class="block text-sm font-semibold text-ink mb-1.5">Récompense</label>
      <select name="reward_type" id="reward-type" required class="form-select">
        <?php foreach ($rewardLabels as $key => $label): ?>
          <option value="<?= $key ?>" <?= ($old['rewardType'] ?? 'points') === $key ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div id="reward-value-wrapper">
    <label class="block text-sm font-semibold text-ink mb-1.5" id="reward-value-label">Nombre de points</label>
    <input type="number" name="reward_value" step="0.01" min="0" value="<?= htmlspecialchars($old['rewardValue'] ?? '10') ?>"
           class="form-input <?= isset($errors['reward_value']) ? 'border-brand-400' : '' ?>">
    <?php if (isset($errors['reward_value'])): ?><p class="text-brand-500 text-xs mt-1"><?= htmlspecialchars($errors['reward_value']) ?></p><?php endif; ?>
  </div>

  <label class="flex items-center gap-2.5 text-sm text-gray-600 bg-gray-50 rounded-lg px-4 py-3">
    <input type="checkbox" name="publish" value="1" <?= ($old['publish'] ?? true) ? 'checked' : '' ?> class="rounded border-gray-300 text-brand-500 focus:ring-brand-500/30">
    Publier immédiatement (sinon le sondage est enregistré comme programmé)
  </label>

  <button type="submit" class="w-full bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm px-6 py-3.5 rounded-lg transition-colors">
    <?= htmlspecialchars($submitLabel) ?>
  </button>
</form>

<script>
  let optionIndex = <?= count($rows) ?>;
  document.getElementById('add-option').addEventListener('click', () => {
    const list = document.getElementById('options-list');
    const row = document.createElement('div');
    row.className = 'flex items-center gap-2 option-row';
    row.innerHTML = `
      <input type="hidden" name="options[${optionIndex}][id]" value="0">
      <input type="text" name="options[${optionIndex}][label]" placeholder="Nouvelle option" required
             class="flex-1 border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30">
      <button type="button" onclick="this.closest('.option-row').remove()" class="text-gray-300 hover:text-brand-500 p-2" aria-label="Retirer">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>`;
    list.appendChild(row);
    optionIndex++;
  });

  const rewardLabels = {
    'points': 'Nombre de points',
    'credit': 'Montant du crédit (€)',
    'tirage_sort': null,
    'aucune': null,
  };
  function updateRewardValueField() {
    const type = document.getElementById('reward-type').value;
    const wrapper = document.getElementById('reward-value-wrapper');
    const label = rewardLabels[type];
    if (!label) {
      wrapper.classList.add('hidden');
    } else {
      wrapper.classList.remove('hidden');
      document.getElementById('reward-value-label').textContent = label;
    }
  }
  document.getElementById('reward-type').addEventListener('change', updateRewardValueField);
  updateRewardValueField();
</script>
