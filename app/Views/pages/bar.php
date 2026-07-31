<?php
$heroEyebrow = 'LE BAR';
$heroText = "Un comptoir convivial pour un café rapide, une bière entre amis ou une planche à partager, du matin jusqu'à la fermeture.";
$heroActions = [
    ['href' => 'https://wa.me/' . str_replace(['+', ' '], '', $shop['whatsapp_href']), 'label' => 'Nous écrire sur WhatsApp', 'class' => 'btn-whatsapp'],
    ['href' => BASE_PATH . '/contact', 'label' => 'Réserver une table', 'class' => 'btn-outline'],
];
$heroSlug = 'hero_bar';
require __DIR__ . '/../partials/page-hero.php';

$categoryLabels = [
    'biere_blonde' => 'Bières blondes',
    'biere_brune'  => 'Bières brunes',
    'biere_ambree' => 'Bières ambrées',
    'autre'        => 'Autres boissons',
];
?>

<section class="max-w-[1536px] mx-auto px-6 lg:px-10 py-12 space-y-10">

  <!-- Bières -->
  <div class="reveal hover-lift bg-[#161513] rounded-2xl p-6 sm:p-10">
    <h2 class="text-white font-bold text-xl mb-1">NOS BIÈRES À LA PRESSION ET EN BOUTEILLE</h2>
    <div class="w-10 h-1 bg-brand-500 rounded-full mb-8"></div>

    <?php $hasAnyBeer = false; ?>
    <?php foreach ($categoryLabels as $key => $label): ?>
      <?php if (empty($categories[$key])) continue; ?>
      <?php $hasAnyBeer = true; ?>
      <div class="mb-8 last:mb-0">
        <p class="text-gray-400 text-xs font-semibold uppercase tracking-[0.2em] mb-4"><?= htmlspecialchars($label) ?></p>
        <div class="grid grid-cols-3 sm:grid-cols-5 lg:grid-cols-6 gap-5">
          <?php foreach ($categories[$key] as $beer): ?>
            <div class="text-center">
              <?= beerGlass(drinkTone($beer['category']), drinkShape($beer['category'])) ?>
              <p class="text-white text-xs font-semibold mt-2 leading-tight"><?= htmlspecialchars($beer['name']) ?></p>
              <p class="text-gray-500 text-[11px] mt-0.5">
                <?php if ($beer['degree']): ?><?= number_format((float) $beer['degree'], 1, ',', '') ?>°<?php endif; ?>
                <?php if ($beer['degree'] && $beer['price']): ?> · <?php endif; ?>
                <?php if ($beer['price']): ?><?= number_format((float) $beer['price'], 2, ',', ' ') ?> €<?php endif; ?>
              </p>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>

    <?php if (!$hasAnyBeer): ?>
      <p class="text-gray-400 text-sm">Notre carte des bières est mise à jour régulièrement — repassez bientôt !</p>
    <?php endif; ?>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Planches -->
    <div class="reveal hover-lift card card-md">
      <h2 class="font-bold text-lg text-ink mb-1">NOS PLANCHES À PARTAGER</h2>
      <div class="w-10 h-1 bg-brand-500 rounded-full mb-5"></div>
      <ul class="space-y-4">
        <?php foreach ($planches as $planche): ?>
          <?php $thumbImg = \App\Models\Settings::get('img_' . ($planche['slug'] ?? '')); ?>
          <li class="flex items-start justify-between gap-4 pb-4 border-b border-gray-50 last:border-0 last:pb-0">
            <div class="flex items-center gap-3">
              <?php if ($thumbImg): ?>
                <img src="<?= htmlspecialchars(BASE_PATH . $thumbImg) ?>" alt="<?= htmlspecialchars($planche['name']) ?>" class="w-16 h-12 object-cover rounded-md shrink-0" loading="lazy" decoding="async">
              <?php else: ?>
                <div class="w-16 h-12 rounded-md shrink-0 bg-brand-50 flex items-center justify-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-brand-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M5 6h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z"/></svg>
                </div>
              <?php endif; ?>
              <div>
                <p class="font-semibold text-ink text-sm"><?= htmlspecialchars($planche['name']) ?></p>
                <p class="text-gray-500 text-xs mt-1"><?= htmlspecialchars($planche['desc']) ?></p>
              </div>
            </div>
            <p class="font-bold text-brand-500 text-sm whitespace-nowrap"><?= number_format($planche['price'], 2, ',', ' ') ?> €</p>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <!-- Softs & chauds -->
    <div class="reveal hover-lift card card-md" style="transition-delay:80ms">
      <h2 class="font-bold text-lg text-ink mb-1">SOFTS, CAFÉS & BOISSONS CHAUDES</h2>
      <div class="w-10 h-1 bg-brand-500 rounded-full mb-5"></div>
      <ul class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <?php foreach ($softs as $soft): ?>
          <li class="flex items-center gap-2.5 text-sm text-gray-600">
            <span class="w-2 h-2 rounded-full bg-brand-500 shrink-0"></span>
            <?= htmlspecialchars($soft) ?>
          </li>
        <?php endforeach; ?>
      </ul>
      <p class="text-xs text-gray-500 mt-5">Terrasse disponible aux beaux jours — idéal pour profiter d'un moment au soleil.</p>
    </div>
  </div>

  <!-- Horaires -->
  <div class="card card-md flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h2 class="font-bold text-ink mb-1">Horaires du bar</h2>
      <p class="text-sm text-gray-500">Lundi au Samedi : <?= htmlspecialchars($shop['hours']['lun_sam']) ?> · Dimanche : <?= htmlspecialchars($shop['hours']['dim']) ?></p>
    </div>
    <a href="<?= BASE_PATH ?>/contact" class="btn-primary shrink-0">Nous contacter</a>
  </div>
</section>
