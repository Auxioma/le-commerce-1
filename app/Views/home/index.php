<?php
$beers = $drinks ?: [];
$heroImage = siteImage('hero_accueil', BASE_PATH . '/assets/images/hero-facade.jpg');
$services = [
    ['label' => 'Payer vos factures',         'color' => '#c8272c', 'icon' => 'clipboard'],
    ['label' => 'Amendes & sanctions',         'color' => '#e08a1e', 'icon' => 'alert'],
    ['label' => 'Paysafecard / Neosurf',       'color' => '#2e6fd6', 'icon' => 'card'],
    ['label' => 'BlaBlaCar',                   'color' => '#1a1a3d', 'icon' => 'car'],
    ['label' => 'Réserver votre place de bus', 'color' => '#c8272c', 'icon' => 'bus'],
    ['label' => 'Relais colis',                'color' => '#d6a12e', 'icon' => 'package'],
    ['label' => 'Retrait d\'espèces',          'color' => '#2e6fd6', 'icon' => 'eye'],
    ['label' => 'Et bien plus encore !',       'color' => '#888888', 'icon' => 'plus'],
];

$serviceIcons = [
    'clipboard' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect><line x1="9" y1="14" x2="15" y2="14"></line><line x1="9" y1="10" x2="15" y2="10"></line></svg>',
    'alert' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>',
    'card' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2" ry="2"></rect><line x1="3" y1="10" x2="21" y2="10"></line></svg>',
    'car' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8h-1a6 6 0 0 0-6-6H7c-1.1 0-2 .9-2 2v12H3"></path><path d="M15 8h6v10c0 1.1-.9 2-2 2h-2"></path><circle cx="6" cy="20" r="2"></circle><circle cx="18" cy="20" r="2"></circle></svg>',
    'bus' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 6h8M6 9h12v8c0 1-1 2-2 2H8c-1 0-2-1-2-2V9z"></path><circle cx="9" cy="20" r="1"></circle><circle cx="15" cy="20" r="1"></circle><path d="M6 9V7a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v2"></path></svg>',
    'package' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>',
    'eye' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>',
    'plus' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>',
];
?>

<!-- =====================  HERO  ===================== -->
<section class="relative w-full overflow-hidden rounded-b-3xl" style="height:clamp(380px,65vw,520px);">
  <img src="<?= htmlspecialchars($heroImage) ?>" alt="Façade du Commerce" class="absolute inset-0 w-full h-full object-cover object-right" loading="eager" decoding="async">
  <div class="absolute inset-0" style="background:linear-gradient(90deg,rgba(255,255,255,.97) 0%,rgba(255,255,255,.93) 32%,rgba(255,255,255,.55) 52%,rgba(255,255,255,.05) 68%,rgba(255,255,255,0) 100%);"></div>
  <div class="relative h-full flex flex-col justify-center gap-3 px-6 sm:px-10 lg:px-8" style="max-width:480px;">
    <p class="font-bold uppercase" style="color:#c8272c; font-size:13px; letter-spacing:.5px;">VOTRE COMMERCE DE PROXIMITÉ À FORGES-LES-EAUX</p>
    <h1 class="font-black text-ink leading-[1.02]" style="font-size:clamp(2.8rem,5vw,3.75rem); letter-spacing:-1px;">LE COMMERCE</h1>
    <p class="font-extrabold text-ink uppercase" style="font-size:clamp(1rem,1.8vw,1.3rem);">BAR &bull; TABAC &bull; PMU &bull; FDJ &bull; PRESSE &bull; NIRIO</p>
    <p class="text-slate-500 leading-relaxed" style="font-size:15px; max-width:400px;">Un lieu convivial où se retrouver, se détendre et profiter de nombreux services au quotidien.</p>
    <div class="flex flex-wrap gap-3 mt-2">
      <a href="<?= BASE_PATH ?>/le-bar" class="inline-flex items-center gap-2 text-white font-bold px-6 py-3.5 rounded-lg transition-opacity hover:opacity-90" style="background:#c8272c; font-size:13px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M18 8h1a3 3 0 013 3v1a3 3 0 01-3 3h-1" stroke="white" stroke-width="2"/><path d="M2 8h16v6a4 4 0 01-4 4H6a4 4 0 01-4-4V8z" fill="white"/><path d="M6 2v3M10 2v3M14 2v3" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>
        DÉCOUVRIR LE BAR
      </a>
      <a href="<?= BASE_PATH ?>/contact" class="inline-flex items-center gap-2 font-bold px-6 py-3.5 rounded-lg border transition-colors text-ink hover:border-brand-500 hover:text-brand-500" style="background:#fff; border-color:#d8d8d8; font-size:13px;">
        <svg width="14" height="16" viewBox="0 0 24 24" fill="none"><path d="M12 22s7-7.5 7-13a7 7 0 10-14 0c0 5.5 7 13 7 13z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="9" r="2.5" stroke="currentColor" stroke-width="2"/></svg>
        NOUS TROUVER
      </a>
    </div>
  </div>
</section>

<style>
@media (min-width:1024px) {
  #home-cards { grid-template-columns: 4.05fr 1.62fr 1fr 1.34fr !important; }
  #home-cards > div:first-child { grid-column: auto !important; }
  #home-info-row { grid-template-columns: 1fr 1fr 1fr 1.9fr !important; }
}
</style>

<!-- =====================  SECTION CATÉGORIES  ===================== -->
<section class="px-6 sm:px-8 lg:px-8 py-5 -mt-20 relative z-10" style="background:#ffffff;">
  <div id="home-categories" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">

    <!-- LE BAR -->
    <a href="<?= BASE_PATH ?>/le-bar" class="group flex flex-row gap-3 items-start p-4 rounded-lg hover:bg-slate-50 transition-colors">
      <svg width="28" height="28" viewBox="0 0 24 24" class="flex-shrink-0 text-brand-500 group-hover:text-brand-600 transition-colors mt-0.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M18 8h1a3 3 0 013 3v1a3 3 0 01-3 3h-1"></path>
        <path d="M2 8h16v6a4 4 0 01-4 4H6a4 4 0 01-4-4V8z"></path>
        <path d="M6 2v3M10 2v3M14 2v3"></path>
      </svg>
      <div class="flex-1 min-w-0">
        <h3 class="font-bold text-sm text-slate-900">LE BAR</h3>
        <p class="text-slate-500 text-xs mt-0.5 leading-tight">Cafés, bières, cocktails & spiritueux</p>
      </div>
    </a>

    <!-- TABAC -->
    <a href="<?= BASE_PATH ?>/tabac" class="group flex flex-row gap-3 items-start p-4 rounded-lg hover:bg-slate-50 transition-colors">
      <svg width="28" height="28" viewBox="0 0 24 24" class="flex-shrink-0 text-brand-500 group-hover:text-brand-600 transition-colors mt-0.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="7" cy="18" r="3"></circle>
        <path d="M11 14l3.5-5M14 2h-1a3 3 0 00-3 3v8"></path>
        <path d="M17 2h-1a3 3 0 00-3 3v6M20 2h-1a3 3 0 00-3 3v4"></path>
      </svg>
      <div class="flex-1 min-w-0">
        <h3 class="font-bold text-sm text-slate-900">TABAC</h3>
        <p class="text-slate-500 text-xs mt-0.5 leading-tight">Cigarettes, cigares électroniques</p>
      </div>
    </a>

    <!-- PMU -->
    <a href="<?= BASE_PATH ?>/pmu" class="group flex flex-row gap-3 items-start p-4 rounded-lg hover:bg-slate-50 transition-colors">
      <svg width="28" height="28" viewBox="0 0 24 24" class="flex-shrink-0 text-brand-500 group-hover:text-brand-600 transition-colors mt-0.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M13 2H7a2 2 0 00-2 2v16a2 2 0 002 2h10a2 2 0 002-2V9z"></path>
        <polyline points="13 2 13 9 20 9"></polyline>
      </svg>
      <div class="flex-1 min-w-0">
        <h3 class="font-bold text-sm text-slate-900">PMU</h3>
        <p class="text-slate-500 text-xs mt-0.5 leading-tight">Pariez sur vos courses préférées</p>
      </div>
    </a>

    <!-- FDJ -->
    <a href="<?= BASE_PATH ?>/fdj" class="group flex flex-row gap-3 items-start p-4 rounded-lg hover:bg-slate-50 transition-colors">
      <svg width="28" height="28" viewBox="0 0 24 24" class="flex-shrink-0 text-brand-500 group-hover:text-brand-600 transition-colors mt-0.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
        <line x1="9" y1="9" x2="15" y2="9"></line>
        <line x1="9" y1="15" x2="15" y2="15"></line>
      </svg>
      <div class="flex-1 min-w-0">
        <h3 class="font-bold text-sm text-slate-900">FDJ</h3>
        <p class="text-slate-500 text-xs mt-0.5 leading-tight">Jeux de la franchise des Jeux</p>
      </div>
    </a>

    <!-- PRESSE -->
    <a href="<?= BASE_PATH ?>/presse" class="group flex flex-row gap-3 items-start p-4 rounded-lg hover:bg-slate-50 transition-colors">
      <svg width="28" height="28" viewBox="0 0 24 24" class="flex-shrink-0 text-brand-500 group-hover:text-brand-600 transition-colors mt-0.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M4 4h16a2 2 0 012 2v12a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2z"></path>
        <line x1="8" y1="8" x2="16" y2="8"></line>
        <line x1="8" y1="12" x2="16" y2="12"></line>
        <line x1="8" y1="16" x2="12" y2="16"></line>
      </svg>
      <div class="flex-1 min-w-0">
        <h3 class="font-bold text-sm text-slate-900">PRESSE</h3>
        <p class="text-slate-500 text-xs mt-0.5 leading-tight">Journaux, magazines et livres</p>
      </div>
    </a>

    <!-- NOS SERVICES -->
    <a href="<?= BASE_PATH ?>/nos-services" class="group flex flex-row gap-3 items-start p-4 rounded-lg hover:bg-slate-50 transition-colors">
      <svg width="28" height="28" viewBox="0 0 24 24" class="flex-shrink-0 text-brand-500 group-hover:text-brand-600 transition-colors mt-0.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="21 8 21 21 3 21 3 8"></polyline>
        <rect x="1" y="3" width="22" height="5"></rect>
        <path d="M10 12v8M14 12v8"></path>
      </svg>
      <div class="flex-1 min-w-0">
        <h3 class="font-bold text-sm text-slate-900">NOS SERVICES</h3>
        <p class="text-slate-500 text-xs mt-0.5 leading-tight">Paiement de proximité, relais colis & plus</p>
      </div>
    </a>

  </div>
</section>

<!-- =====================  SECTION CARDS  ===================== -->
<section class="px-6 sm:px-8 lg:px-8 py-5" style="background:#f4f3f1;">
  <div id="home-cards" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

    <!-- Nos bières -->
    <div class="reveal bg-white rounded-xl p-5 flex flex-col shadow-sm sm:col-span-2 lg:col-span-auto">
      <h2 class="font-extrabold text-ink mb-3" style="font-size:17px; letter-spacing:.3px;">NOS BIÈRES À DÉCOUVRIR</h2>
      <div class="rounded-lg overflow-hidden mb-3 flex-1">
        <?php $beersImg = siteImage('hero_bar', BASE_PATH . '/assets/images/beers-strip.jpg'); ?>
        <img src="<?= htmlspecialchars($beersImg) ?>" alt="Bières à la carte" class="w-full h-full object-cover" loading="lazy" decoding="async">
      </div>
      <a href="<?= BASE_PATH ?>/le-bar" class="flex items-center justify-center gap-2 text-white font-bold rounded-lg py-3 transition-opacity hover:opacity-90" style="background:#c8272c; font-size:13px;">
        VOIR LA CARTE COMPLÈTE DES BOISSONS
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M9 6l6 6-6 6" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </a>
    </div>

    <!-- Planche à saucisson -->
    <div class="reveal bg-white rounded-xl p-5 flex flex-col shadow-sm" style="transition-delay:80ms;">
      <h2 class="font-extrabold text-ink mb-1" style="font-size:16px;">NOTRE PLANCHE À SAUCISSON</h2>
      <div class="w-9 h-[3px] rounded-full mb-3" style="background:#c8272c;"></div>
      <?php $saucissonImg = siteImage('bar_planche_saucisson', BASE_PATH . '/assets/images/charcuterie.jpg'); ?>
      <img src="<?= htmlspecialchars($saucissonImg) ?>" alt="Planche à saucisson" class="w-full object-cover rounded-lg mb-3" style="height:150px;" loading="lazy" decoding="async">
      <p class="text-slate-500 leading-relaxed flex-1 mb-3" style="font-size:13px;">Saucisson, cornichons, fromage et pain frais. Le plaisir de partager un bon moment !</p>
      <a href="<?= BASE_PATH ?>/le-bar" class="flex items-center justify-center gap-2 text-white font-bold rounded-lg py-3 transition-opacity hover:opacity-90" style="background:#c8272c; font-size:12.5px;">
        DÉCOUVRIR NOTRE PLANCHE
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M9 6l6 6-6 6" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </a>
    </div>

    <!-- WhatsApp -->
    <div class="reveal bg-white rounded-xl p-5 flex flex-col items-center shadow-sm" style="transition-delay:160ms;">
      <h2 class="font-extrabold text-ink leading-snug self-start mb-2" style="font-size:15px;">REJOIGNEZ-NOUS<br>SUR <span style="color:#25D366;">WHATSAPP</span> !</h2>
      <p class="text-slate-500 self-start mb-3" style="font-size:12.5px; line-height:1.6;">Recevez en exclusivité nos promotions, événements et nouveautés !</p>
      <div class="flex-1 flex items-center justify-center py-2">
        <img src="<?= BASE_PATH ?>/assets/images/qrcode.jpg" alt="QR Code WhatsApp" class="rounded-lg" style="width:120px; height:120px; object-fit:cover;" loading="lazy" decoding="async">
      </div>
      <a href="https://wa.me/<?= htmlspecialchars(str_replace(['+', ' '], '', $shop['whatsapp_href'])) ?>"
         target="_blank" rel="noopener"
         class="flex items-center justify-center gap-2 text-white font-bold rounded-lg py-3 w-full mt-3 transition-opacity hover:opacity-90" style="background:#1fa855; font-size:13px;">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.4A10 10 0 1 0 12 2zm0 18a8 8 0 0 1-4.1-1.1l-.3-.2-2.9.8.8-2.8-.2-.3A8 8 0 1 1 12 20z"/></svg>
        JE M'INSCRIS
      </a>
    </div>


    <!-- Services -->
    <div class="reveal bg-white rounded-xl p-5 flex flex-col shadow-sm" style="transition-delay:240ms;">
      <h2 class="font-extrabold text-ink mb-3" style="font-size:15.5px; line-height:1.3;">TOUS VOS SERVICES<br>DU QUOTIDIEN</h2>
      <div class="flex flex-col gap-2.5 flex-1">
        <?php foreach ($services as $svc): ?>
          <div class="flex items-center gap-2.5" style="font-size:12.5px; font-weight:600; color:#2a2a2a;">
            <span class="flex-shrink-0" style="color:<?= htmlspecialchars($svc['color']) ?>;">
              <?= $serviceIcons[$svc['icon']] ?? '' ?>
            </span>
            <?= htmlspecialchars($svc['label']) ?>
          </div>
        <?php endforeach; ?>
      </div>
      <a href="<?= BASE_PATH ?>/nos-services" class="flex items-center justify-center gap-2 font-bold rounded-full py-3 mt-4 transition-colors hover:bg-red-50" style="border:2px solid #c8272c; color:#c8272c; font-size:12.5px;">
        VOIR TOUS LES SERVICES
      </a>
    </div>

  </div>
</section>

<!-- =====================  INFO ROW (4 colonnes)  ===================== -->
<section class="px-6 sm:px-8 lg:px-8 py-5 pb-10" style="background:#ffffff;">
  <div id="home-info-row" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

    <!-- Avis Google -->
    <div class="reveal rounded-xl p-5 flex flex-col shadow-sm" style="background:#f7f7f6; transition-delay:0ms;">
      <h3 class="font-extrabold text-ink mb-3" style="font-size:14px;">AVIS GOOGLE</h3>
      <div class="flex items-center gap-3 mb-1.5">
        <svg width="30" height="30" viewBox="0 0 24 24"><path fill="#4285F4" d="M23.5 12.3c0-.9-.1-1.5-.2-2.2H12v4h6.5c-.1 1.1-.9 2.7-2.5 3.8l3.9 3c2.3-2.1 3.6-5.3 3.6-8.6z"/><path fill="#34A853" d="M12 24c3.2 0 6-1.1 7.9-2.9l-3.9-3c-1 .7-2.4 1.2-4 1.2-3.1 0-5.7-2.1-6.6-4.9l-4 3.1C3.4 21.4 7.4 24 12 24z"/><path fill="#FBBC05" d="M5.4 14.4c-.2-.7-.4-1.4-.4-2.4s.1-1.6.4-2.4l-4-3.1C.5 8.1 0 10 0 12s.5 3.9 1.4 5.5l4-3.1z"/><path fill="#EA4335" d="M12 4.7c1.8 0 3.4.6 4.6 1.8l3.4-3.4C18 1.2 15.2 0 12 0 7.4 0 3.4 2.6 1.4 6.5l4 3.1C6.3 6.8 8.9 4.7 12 4.7z"/></svg>
        <span class="font-black text-ink" style="font-size:26px;"><?= number_format((float)$shop['google_rating'], 1, ',', '') ?>/5</span>
      </div>
      <div class="text-amber-400 mb-2" style="font-size:18px; letter-spacing:2px;">★★★★★</div>
      <p class="text-slate-500 flex-1 mb-4" style="font-size:12.5px;">Basé sur plus de <strong><?= (int)$shop['google_reviews_count'] ?> avis</strong></p>
      <a href="https://www.google.com/maps" target="_blank" rel="noopener" class="block text-center font-bold border rounded-lg py-2.5 transition-colors hover:bg-gray-50" style="font-size:12.5px; color:#c8272c; border-color:#d8d8d8;">LAISSER UN AVIS</a>
    </div>

    <!-- Bons plans -->
    <div class="reveal rounded-xl p-5 flex flex-col shadow-sm" style="background:#f7f7f6; transition-delay:80ms;">
      <h3 class="font-extrabold text-ink mb-3 leading-snug" style="font-size:14px;">LES BONS PLANS<br>DU MOMENT</h3>
      <?php if ($deal): ?>
        <p class="font-extrabold" style="font-size:13px; color:#c8272c;"><?= htmlspecialchars(mb_strtoupper($deal['title'])) ?></p>
        <p class="text-slate-500 mb-2" style="font-size:12px;"><?= substr($deal['starts_at'], 0, 5) ?>H - <?= substr($deal['ends_at'], 0, 5) ?>H</p>
        <img src="<?= BASE_PATH ?>/assets/images/happy-hour.jpg" alt="Happy Hour" class="w-full rounded-lg object-cover mb-2" style="height:70px;" loading="lazy" decoding="async">
        <p class="font-extrabold flex-1" style="font-size:12.5px; color:#c8272c;"><?= htmlspecialchars(mb_strtoupper($deal['subtitle'])) ?></p>
      <?php else: ?>
        <p class="text-slate-400 flex-1 text-sm">Aucune offre en cours</p>
      <?php endif; ?>
      <a href="<?= BASE_PATH ?>/le-bar" class="block text-center text-white font-bold rounded-lg py-2.5 mt-3 transition-opacity hover:opacity-90" style="font-size:12.5px; background:#c8272c;">EN PROFITER</a>
    </div>

    <!-- Météo -->
    <div class="reveal rounded-xl p-5 flex flex-col shadow-sm" id="weather-widget" data-lat="<?= (float)$shop['latitude'] ?>" data-lng="<?= (float)$shop['longitude'] ?>" style="background:#f7f7f6; transition-delay:160ms;">
      <h3 class="font-extrabold text-ink mb-3 leading-snug" style="font-size:14px;">MÉTÉO À<br><?= htmlspecialchars(mb_strtoupper($shop['city'])) ?></h3>
      <div class="flex items-center gap-4 mb-2">
        <svg width="46" height="40" viewBox="0 0 46 40">
          <circle cx="15" cy="14" r="9" fill="#f5b13d"/>
          <ellipse cx="27" cy="26" rx="16" ry="11" fill="#bcd3e6"/>
        </svg>
        <p class="font-black text-ink" id="weather-temp" style="font-size:30px;">--°C</p>
      </div>
      <p class="font-bold text-ink mb-3" id="weather-desc" style="font-size:13px;">Chargement…</p>
      <p class="text-slate-500 mt-auto" style="font-size:12.5px; line-height:1.6;">Profitez de notre terrasse !</p>
    </div>

    <!-- Assistant -->
    <div class="reveal rounded-xl p-5 flex flex-col shadow-sm" style="background:#f7f7f6; transition-delay:240ms;">
      <div class="flex items-center gap-2 mb-3">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M4 4h16v11H8l-4 4V4z" fill="#c8272c"/></svg>
        <h3 class="font-extrabold text-ink" style="font-size:14px;">ASSISTANT LE COMMERCE</h3>
      </div>
      <div class="bg-white rounded-xl p-3 mb-3 self-start" style="font-size:13px; color:#2a2a2a; line-height:1.6; max-width:80%;">Bonjour ! 👋<br>Que recherchez-vous aujourd'hui ?</div>
      <div class="flex flex-wrap gap-2 mb-3">
        <?php foreach (['Êtes-vous ouvert ?', 'Match ce soir ?', 'Bières disponibles ?', 'Horaires PMU ?', 'Réserver une table ?', 'Jeux FDJ ?'] as $chip): ?>
          <button type="button" class="chat-chip bg-white border border-gray-200 rounded-full transition-colors hover:border-brand-500 hover:text-brand-500" style="font-size:11.5px; font-weight:600; padding:8px 14px;">
            <?= htmlspecialchars($chip) ?>
          </button>
        <?php endforeach; ?>
      </div>
      <form id="assistant-form" class="flex items-center bg-white border border-gray-200 rounded-full mt-auto" style="padding:10px 14px; gap:10px;">
        <input type="text" placeholder="Écrivez votre question..." class="flex-1 min-w-0 focus:outline-none text-ink placeholder-gray-400" style="font-size:12.5px; background:transparent; border:none;">
        <button type="submit" class="shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-white transition-opacity hover:opacity-90" style="background:#c8272c;">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M2 21l20-9L2 3v7l14 2-14 2v7z" fill="white"/></svg>
        </button>
      </form>
    </div>

  </div>
</section>
