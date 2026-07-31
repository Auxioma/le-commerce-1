<?php
$heroEyebrow = $heroEyebrow ?? null;
$heroImage = siteImage($heroSlug ?? '', 'https://images.unsplash.com/photo-1528605248644-14dd04022da1?q=80&auto=format&fit=crop&w=1600');
$heroActions = $heroActions ?? [];
// Les URL Unsplash ont déjà une query string (?q=...) : on y ajoute w=800/1200
// avec "&". Les images uploadées localement (/uploads/...) n'en ont pas : il
// faut démarrer la query string avec "?", sinon l'URL générée est invalide.
$heroImageSep = str_contains($heroImage, '?') ? '&' : '?';
?>
<section class="max-w-[1536px] mx-auto px-6 lg:px-10 py-12">
  <div class="page-hero overflow-hidden rounded-[32px] shadow-[0_40px_120px_-60px_rgba(15,23,42,0.25)]">
    <div class="relative overflow-hidden px-6 py-14 sm:px-10 lg:px-16 xl:px-20">
      <div class="absolute inset-0 z-0">
        <picture>
          <source type="image/webp" srcset="<?= htmlspecialchars(str_replace('auto=format','fm=webp', $heroImage) . $heroImageSep) ?>w=800 800w, <?= htmlspecialchars(str_replace('auto=format','fm=webp', $heroImage) . $heroImageSep) ?>w=1200 1200w" sizes="100vw">
          <img src="<?= htmlspecialchars($heroImage) ?>" srcset="<?= htmlspecialchars($heroImage . $heroImageSep) ?>w=800 800w, <?= htmlspecialchars($heroImage . $heroImageSep) ?>w=1200 1200w" sizes="100vw" alt="" class="hero-kenburns w-full h-full object-cover object-right" loading="lazy" decoding="async">
        </picture>
      </div>
      <div class="absolute inset-0 z-[5] pointer-events-none" style="background:linear-gradient(90deg,rgba(255,255,255,.98) 0%,rgba(255,255,255,.92) 30%,rgba(255,255,255,.55) 46%,rgba(255,255,255,0) 66%);"></div>
      <div class="reveal relative z-10 max-w-3xl space-y-6">
        <?php if ($heroEyebrow): ?>
          <p class="eyebrow"><?= htmlspecialchars($heroEyebrow) ?></p>
        <?php endif; ?>
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-[1.04]">
          <?= htmlspecialchars($heading) ?>
        </h1>
        <p class="max-w-2xl text-sm sm:text-base leading-8">
          <?= htmlspecialchars($heroText) ?>
        </p>

        <?php if (!empty($heroActions)): ?>
          <div class="flex flex-wrap gap-3 pt-2">
            <?php foreach ($heroActions as $action): ?>
              <a href="<?= htmlspecialchars($action['href']) ?>" class="<?= htmlspecialchars($action['class'] ?? 'btn-primary') ?>">
                <?= htmlspecialchars($action['label']) ?>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
