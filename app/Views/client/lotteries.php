<?php use App\Core\Csrf; ?>

<div class="mb-8">
  <h1 class="font-extrabold text-3xl text-ink mb-2">Loterie</h1>
  <p class="text-gray-500">Participez aux tirages en cours et tentez de remporter un lot !</p>
</div>

<?php if (empty($lotteries)): ?>
  <div class="bg-white border border-gray-100 rounded-2xl text-center py-20 px-6">
    <span class="w-16 h-16 rounded-full bg-gray-100 text-gray-300 flex items-center justify-center mx-auto mb-4">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
    </span>
    <p class="text-gray-600 font-semibold text-lg mb-1">Aucune loterie en cours</p>
    <p class="text-gray-400 text-sm">Revenez bientôt pour tenter votre chance !</p>
  </div>
<?php else: ?>
  <div class="grid sm:grid-cols-2 gap-6">
    <?php foreach ($lotteries as $lottery): $entry = $entries[$lottery['id']] ?? null; ?>
      <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden hover-lift transition-all">
        <div class="p-6 bg-gradient-to-r from-brand-50 to-transparent border-b border-gray-100">
          <h3 class="font-bold text-ink text-lg mb-1"><?= htmlspecialchars($lottery['title']) ?></h3>
          <?php if ($lottery['description']): ?>
            <p class="text-sm text-gray-600 mb-2"><?= htmlspecialchars($lottery['description']) ?></p>
          <?php endif; ?>
          <p class="text-sm font-bold text-brand-500">🎁 <?= htmlspecialchars($lottery['prize']) ?></p>
          <p class="text-xs text-gray-400 mt-1">Fin des inscriptions le <?= date('d/m/Y', strtotime($lottery['ends_at'])) ?></p>
        </div>

        <div class="p-6">
          <?php if ($entry): ?>
            <div class="bg-slate-900 rounded-xl p-5 mb-4 flex items-center gap-4">
              <svg viewBox="0 0 100 100" class="w-20 h-20 shrink-0 bg-white rounded-lg p-1">
                <rect width="100" height="100" fill="#fff"/>
                <?php
                  mt_srand(crc32($entry['code']));
                  for ($y = 0; $y < 12; $y++) {
                      for ($x = 0; $x < 12; $x++) {
                          if (mt_rand(0, 100) > 52) {
                              echo '<rect x="' . (6 + $x * 7) . '" y="' . (6 + $y * 7) . '" width="7" height="7" fill="#111"/>';
                          }
                      }
                  }
                ?>
              </svg>
              <div>
                <p class="text-gray-400 text-xs font-semibold uppercase tracking-wider">Votre ticket</p>
                <p class="font-mono font-extrabold text-white text-lg mt-1"><?= htmlspecialchars($entry['code']) ?></p>
                <p class="text-gray-400 text-xs mt-2">Vous êtes inscrit ✓</p>
              </div>
            </div>
          <?php else: ?>
            <form method="POST" action="<?= BASE_PATH ?>/mon-compte/loterie/<?= $lottery['id'] ?>/participer">
              <?= Csrf::field() ?>
              <button type="submit" class="w-full bg-brand-500 hover:bg-brand-600 text-white font-bold py-3 rounded-lg transition-colors text-sm">
                Générer mon ticket de participation
              </button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php
  $wonEntries = array_filter($pastEntries, fn($e) => $e['lottery_status'] === 'terminee' && (int) $e['winner_user_id'] === (int) $user['id']);
  $endedEntries = array_filter($pastEntries, fn($e) => $e['lottery_status'] === 'terminee');
?>
<?php if (!empty($endedEntries)): ?>
  <div class="mt-10">
    <h2 class="font-bold text-xl text-ink mb-4">Tirages terminés</h2>
    <div class="bg-white border border-gray-100 rounded-2xl divide-y divide-gray-50">
      <?php foreach ($endedEntries as $e): $won = (int) $e['winner_user_id'] === (int) $user['id']; ?>
        <div class="flex items-center justify-between px-5 py-4">
          <div>
            <p class="font-semibold text-ink text-sm"><?= htmlspecialchars($e['lottery_title']) ?></p>
            <p class="text-xs text-gray-400"><?= htmlspecialchars($e['prize']) ?></p>
          </div>
          <span class="text-xs font-bold px-3 py-1.5 rounded-full <?= $won ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-500' ?>">
            <?= $won ? '🏆 Gagné !' : 'Non gagnant' ?>
          </span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>
