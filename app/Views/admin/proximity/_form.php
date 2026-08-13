<?php
/**
 * Formulaire partagé Campagne de proximité (création + édition).
 * Attend : $formAction, $submitLabel, $segmentLabels, $activeOffers, $campaign (optionnel), $errors
 */
use App\Core\Csrf;

$c = $campaign ?? [];
$dayKeys = ['lun', 'mar', 'mer', 'jeu', 'ven', 'sam', 'dim'];
$selectedDays = isset($c['days']) ? explode(',', $c['days']) : ['lun', 'mar', 'mer', 'jeu', 'ven'];
?>
<form method="POST" action="<?= BASE_PATH . $formAction ?>" class="space-y-4">
  <?= Csrf::field() ?>

  <div>
    <label class="block text-sm font-semibold text-ink mb-1.5">Nom de la campagne</label>
    <input type="text" name="name" required value="<?= htmlspecialchars($c['name'] ?? '') ?>" placeholder="Ex : Café du matin"
           class="form-input">
  </div>

  <div>
    <label class="block text-sm font-semibold text-ink mb-1.5">
      Zone de diffusion : <span id="radius-value" class="text-brand-500"><?= (int) ($c['radius_m'] ?? 500) ?> mètres</span>
    </label>
    <input type="range" name="radius_m" min="100" max="5000" step="100" value="<?= (int) ($c['radius_m'] ?? 500) ?>" id="radius-slider"
           class="w-full accent-brand-500">
    <div class="flex justify-between text-[10px] text-gray-400 mt-1">
      <span>100m</span><span>1km</span><span>2km</span><span>5km</span>
    </div>
  </div>

  <div class="grid grid-cols-2 gap-4">
    <div>
      <label class="block text-sm font-semibold text-ink mb-1.5">Heure de début</label>
      <input type="time" name="start_time" required value="<?= htmlspecialchars(substr($c['start_time'] ?? '10:00', 0, 5)) ?>"
             class="form-input">
    </div>
    <div>
      <label class="block text-sm font-semibold text-ink mb-1.5">Heure de fin</label>
      <input type="time" name="end_time" required value="<?= htmlspecialchars(substr($c['end_time'] ?? '11:00', 0, 5)) ?>"
             class="form-input">
    </div>
  </div>

  <div>
    <label class="block text-sm font-semibold text-ink mb-2">Jours de diffusion</label>
    <div class="flex flex-wrap gap-2">
      <?php foreach (['lun' => 'Lun', 'mar' => 'Mar', 'mer' => 'Mer', 'jeu' => 'Jeu', 'ven' => 'Ven', 'sam' => 'Sam', 'dim' => 'Dim'] as $key => $label): ?>
        <label class="cursor-pointer">
          <input type="checkbox" name="days[]" value="<?= $key ?>" class="peer sr-only" <?= in_array($key, $selectedDays, true) ? 'checked' : '' ?>>
          <span class="inline-block px-3 py-2 rounded-lg text-xs font-bold border-2 border-gray-200 peer-checked:border-brand-500 peer-checked:bg-brand-50 peer-checked:text-brand-600 transition-colors">
            <?= $label ?>
          </span>
        </label>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="grid grid-cols-2 gap-4">
    <div>
      <label class="block text-sm font-semibold text-ink mb-1.5">Cible</label>
      <select name="target_segment" class="form-select">
        <?php foreach ($segmentLabels as $key => $label): ?>
          <option value="<?= $key ?>" <?= ($c['target_segment'] ?? 'tous') === $key ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block text-sm font-semibold text-ink mb-1.5">Offre liée <span class="text-gray-400 font-normal">(facultatif)</span></label>
      <select name="offer_id" class="form-select">
        <option value="">Aucune offre</option>
        <?php foreach ($activeOffers as $o): ?>
          <option value="<?= $o['id'] ?>" <?= (int) ($c['offer_id'] ?? 0) === (int) $o['id'] ? 'selected' : '' ?>><?= htmlspecialchars($o['title']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div>
    <label class="block text-sm font-semibold text-ink mb-1.5">Message</label>
    <textarea name="message" rows="3" maxlength="160" required placeholder="👋 Bonjour {prenom} ! Vous n'êtes pas loin du Commerce..."
              class="form-textarea"><?= htmlspecialchars($c['message'] ?? '') ?></textarea>
  </div>

  <label class="flex items-center gap-2.5 text-sm text-gray-600 bg-gray-50 rounded-2xl px-4 py-3">
    <input type="checkbox" name="publish" value="1" <?= (($c['status'] ?? 'active') === 'active') ? 'checked' : '' ?> class="rounded border-gray-300 text-brand-500 focus:ring-brand-500/30">
    Activer immédiatement la campagne
  </label>

  <button type="submit" class="btn-primary w-full">
    <?= htmlspecialchars($submitLabel) ?>
  </button>
</form>
