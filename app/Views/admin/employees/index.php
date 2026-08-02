<?php use App\Core\Csrf; ?>

<!-- En-tête -->
<div class="flex items-start justify-between mb-6 gap-4 flex-wrap">
  <div>
    <h1 class="font-extrabold text-ink" style="font-size:22px; letter-spacing:-0.5px;">Employés</h1>
    <p class="text-gray-500 mt-1" style="font-size:13px;">Gérez la liste de votre équipe.</p>
  </div>
  <div class="flex items-center gap-3 shrink-0">
    <div class="flex items-center gap-3 bg-white border border-gray-200 rounded-xl px-4 py-3 shadow-sm">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-4-4"/></svg>
      <div>
        <p class="text-gray-500" style="font-size:11px; font-weight:600;">Équipe active</p>
        <p class="font-black text-ink" style="font-size:22px; line-height:1;"><?= $activeCount ?> <span class="text-gray-400 font-semibold" style="font-size:12px;">/ <?= $totalCount ?> au total</span></p>
      </div>
    </div>
    <button type="button" onclick="document.getElementById('employee-add-modal').classList.remove('hidden')"
            class="inline-flex items-center gap-2 text-white font-bold rounded-xl px-5 py-3 transition-opacity hover:opacity-90" style="background:#c8272c; font-size:13px;">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
      AJOUTER UN EMPLOYÉ
    </button>
  </div>
</div>

<!-- Tableau -->
<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
  <?php if (empty($employees)): ?>
    <div class="text-center py-16 px-6">
      <p class="text-gray-500 font-medium mb-1">Aucun employé pour le moment.</p>
      <p class="text-gray-400 text-sm">Ajoutez le premier membre de votre équipe.</p>
    </div>
  <?php else: ?>
    <div class="overflow-x-auto">
      <table class="min-w-full">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr>
            <th class="px-5 py-3 text-left font-bold text-gray-500 uppercase" style="font-size:11px; letter-spacing:0.5px;">Employé</th>
            <th class="px-5 py-3 text-left font-bold text-gray-500 uppercase" style="font-size:11px; letter-spacing:0.5px;">Poste</th>
            <th class="px-5 py-3 text-left font-bold text-gray-500 uppercase" style="font-size:11px; letter-spacing:0.5px;">Contact</th>
            <th class="px-5 py-3 text-left font-bold text-gray-500 uppercase" style="font-size:11px; letter-spacing:0.5px;">Embauché le</th>
            <th class="px-5 py-3 text-left font-bold text-gray-500 uppercase" style="font-size:11px; letter-spacing:0.5px;">Statut</th>
            <th class="px-5 py-3 text-right font-bold text-gray-500 uppercase" style="font-size:11px; letter-spacing:0.5px;">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-100">
          <?php foreach ($employees as $e):
            $avatarColors = ['bg-blue-500', 'bg-purple-500', 'bg-green-500', 'bg-orange-500', 'bg-pink-500', 'bg-teal-500'];
            $colorIdx = crc32($e['first_name']) % count($avatarColors);
            $initials = mb_strtoupper(mb_substr($e['first_name'], 0, 1) . mb_substr($e['last_name'], 0, 1));
          ?>
            <tr class="hover:bg-gray-50 transition-colors">
              <td class="px-5 py-3.5 whitespace-nowrap">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-full <?= $avatarColors[$colorIdx] ?> flex items-center justify-center shrink-0">
                    <span class="text-white font-bold" style="font-size:12px;"><?= htmlspecialchars($initials) ?></span>
                  </div>
                  <span class="font-semibold text-ink" style="font-size:13px;"><?= htmlspecialchars($e['first_name'] . ' ' . $e['last_name']) ?></span>
                </div>
              </td>
              <td class="px-5 py-3.5 whitespace-nowrap text-gray-600" style="font-size:13px;"><?= htmlspecialchars($e['role']) ?></td>
              <td class="px-5 py-3.5 whitespace-nowrap text-gray-500" style="font-size:12.5px;">
                <?php if ($e['phone']): ?><p><?= htmlspecialchars($e['phone']) ?></p><?php endif; ?>
                <?php if ($e['email']): ?><p class="text-gray-400"><?= htmlspecialchars($e['email']) ?></p><?php endif; ?>
                <?php if (!$e['phone'] && !$e['email']): ?>—<?php endif; ?>
              </td>
              <td class="px-5 py-3.5 whitespace-nowrap text-gray-500" style="font-size:13px;">
                <?= $e['hired_at'] ? date('d/m/Y', strtotime($e['hired_at'])) : '—' ?>
              </td>
              <td class="px-5 py-3.5 whitespace-nowrap">
                <form method="POST" action="<?= BASE_PATH ?>/admin/employes/<?= $e['id'] ?>/statut">
                  <?= Csrf::field() ?>
                  <button type="submit" class="px-2.5 py-1 rounded-full font-semibold transition-colors <?= $e['status'] === 'actif' ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' ?>" style="font-size:11.5px;">
                    <?= $e['status'] === 'actif' ? 'Actif' : 'Inactif' ?>
                  </button>
                </form>
              </td>
              <td class="px-5 py-3.5 whitespace-nowrap text-right">
                <div class="inline-flex items-center gap-2">
                  <button type="button" onclick="document.getElementById('employee-edit-modal-<?= $e['id'] ?>').classList.remove('hidden')"
                          class="inline-flex items-center gap-1.5 font-semibold px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors" style="font-size:12px;">
                    Modifier
                  </button>
                  <form method="POST" action="<?= BASE_PATH ?>/admin/employes/<?= $e['id'] ?>/supprimer"
                        onsubmit="return confirm('Supprimer <?= htmlspecialchars(addslashes($e['first_name'] . ' ' . $e['last_name'])) ?> ?');">
                    <?= Csrf::field() ?>
                    <button type="submit" class="font-semibold text-gray-400 hover:text-brand-500 transition-colors px-2" style="font-size:12px;">Suppr.</button>
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

<!-- Modale : ajouter -->
<div id="employee-add-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
  <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl">
    <h3 class="font-extrabold text-ink mb-4" style="font-size:16px;">Ajouter un employé</h3>
    <form method="POST" action="<?= BASE_PATH ?>/admin/employes" class="space-y-3">
      <?= Csrf::field() ?>
      <div class="grid grid-cols-2 gap-3">
        <input type="text" name="first_name" required placeholder="Prénom" class="form-input">
        <input type="text" name="last_name" required placeholder="Nom" class="form-input">
      </div>
      <input type="text" name="role" required placeholder="Poste (ex: Serveur, Caissier...)" class="form-input">
      <div class="grid grid-cols-2 gap-3">
        <input type="text" name="phone" placeholder="Téléphone" class="form-input">
        <input type="email" name="email" placeholder="E-mail" class="form-input">
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-semibold text-gray-500 mb-1">Date d'embauche</label>
          <input type="date" name="hired_at" class="form-input">
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-500 mb-1">Statut</label>
          <select name="status" class="form-select">
            <option value="actif">Actif</option>
            <option value="inactif">Inactif</option>
          </select>
        </div>
      </div>
      <div class="flex gap-2 justify-end pt-2">
        <button type="button" onclick="document.getElementById('employee-add-modal').classList.add('hidden')"
                class="px-4 py-2.5 font-semibold text-gray-500 hover:text-ink transition-colors" style="font-size:13px;">Annuler</button>
        <button type="submit" class="btn-primary">Ajouter</button>
      </div>
    </form>
  </div>
</div>

<!-- Modales : modifier -->
<?php foreach ($employees as $e): ?>
  <div id="employee-edit-modal-<?= $e['id'] ?>" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl">
      <h3 class="font-extrabold text-ink mb-4" style="font-size:16px;">Modifier <?= htmlspecialchars($e['first_name']) ?></h3>
      <form method="POST" action="<?= BASE_PATH ?>/admin/employes/<?= $e['id'] ?>" class="space-y-3">
        <?= Csrf::field() ?>
        <div class="grid grid-cols-2 gap-3">
          <input type="text" name="first_name" required value="<?= htmlspecialchars($e['first_name']) ?>" class="form-input">
          <input type="text" name="last_name" required value="<?= htmlspecialchars($e['last_name']) ?>" class="form-input">
        </div>
        <input type="text" name="role" required value="<?= htmlspecialchars($e['role']) ?>" class="form-input">
        <div class="grid grid-cols-2 gap-3">
          <input type="text" name="phone" value="<?= htmlspecialchars($e['phone'] ?? '') ?>" placeholder="Téléphone" class="form-input">
          <input type="email" name="email" value="<?= htmlspecialchars($e['email'] ?? '') ?>" placeholder="E-mail" class="form-input">
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Date d'embauche</label>
            <input type="date" name="hired_at" value="<?= htmlspecialchars($e['hired_at'] ?? '') ?>" class="form-input">
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Statut</label>
            <select name="status" class="form-select">
              <option value="actif" <?= $e['status'] === 'actif' ? 'selected' : '' ?>>Actif</option>
              <option value="inactif" <?= $e['status'] === 'inactif' ? 'selected' : '' ?>>Inactif</option>
            </select>
          </div>
        </div>
        <div class="flex gap-2 justify-end pt-2">
          <button type="button" onclick="document.getElementById('employee-edit-modal-<?= $e['id'] ?>').classList.add('hidden')"
                  class="px-4 py-2.5 font-semibold text-gray-500 hover:text-ink transition-colors" style="font-size:13px;">Annuler</button>
          <button type="submit" class="btn-primary">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
<?php endforeach; ?>
