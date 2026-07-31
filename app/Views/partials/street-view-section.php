<?php
/**
 * Section "Visite virtuelle 360°" — iframe Google Maps Street View, chargée
 * uniquement au clic (poids/LCP) plutôt qu'au chargement de la page.
 * URL configurable depuis /admin/parametres (clé settings `streetview_embed_url`) ;
 * la valeur ci-dessous n'est qu'un repli si l'admin n'a rien renseigné.
 */
$streetViewDefaultUrl = 'https://www.google.com/maps/embed?pb=!4v1785517545160!6m8!1m7!1sE9fm2W1Q67_44kxKC4pBzg!2m2!1d49.61269599350963!2d1.546989958750863!3f13.197598577345218!4f-5.274069045769821!5f0.7820865974627469';
$streetViewUrl = \App\Models\Settings::get('streetview_embed_url', $streetViewDefaultUrl);
$streetViewCover = siteImage('hero_accueil', BASE_PATH . '/assets/images/hero-facade.jpg');
$streetViewDirections = 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode($shop['address'] . ', ' . $shop['zipcode'] . ' ' . $shop['city']);
?>
<div class="reveal hover-lift card card-md">
  <h2 class="font-bold text-lg text-ink mb-1">VISITEZ-NOUS EN VUE 360°</h2>
  <div class="w-10 h-1 bg-brand-500 rounded-full mb-5"></div>
  <p class="text-sm text-gray-500 mb-5">Baladez-vous virtuellement dans notre établissement avant votre venue, comme si vous y étiez déjà.</p>

  <div
    class="street-view-frame relative w-full rounded-2xl overflow-hidden border border-gray-100 shadow-sm bg-gray-100 bg-cover bg-center"
    style="aspect-ratio:16/9; background-image:url('<?= htmlspecialchars($streetViewCover) ?>');"
    data-streetview-src="<?= htmlspecialchars($streetViewUrl) ?>"
  >
    <div class="absolute inset-0 bg-black/25"></div>
    <button type="button" class="street-view-trigger absolute inset-0 w-full h-full flex flex-col items-center justify-center gap-3 group">
      <span class="w-16 h-16 rounded-full bg-white shadow-lg flex items-center justify-center transition-transform group-hover:scale-105">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-brand-500 ml-0.5" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
      </span>
      <span class="text-sm font-semibold text-white bg-black/40 px-4 py-1.5 rounded-full backdrop-blur-sm">Charger la vue 360°</span>
    </button>
  </div>

  <div class="mt-5 flex flex-wrap gap-3">
    <a href="<?= htmlspecialchars($streetViewDirections) ?>" target="_blank" rel="noopener" class="btn-outline">Itinéraire vers le commerce</a>
  </div>
</div>

<script>
(function () {
  document.querySelectorAll('.street-view-frame').forEach(function (frame) {
    var trigger = frame.querySelector('.street-view-trigger');
    if (!trigger) return;
    trigger.addEventListener('click', function () {
      var iframe = document.createElement('iframe');
      iframe.src = frame.getAttribute('data-streetview-src');
      iframe.className = 'absolute inset-0 w-full h-full border-0';
      iframe.loading = 'lazy';
      iframe.allowFullscreen = true;
      iframe.referrerPolicy = 'strict-origin-when-cross-origin';
      iframe.title = 'Vue 360° de l\'établissement';
      frame.innerHTML = '';
      frame.appendChild(iframe);
    }, { once: true });
  });
})();
</script>
