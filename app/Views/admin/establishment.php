<?php use App\Core\Csrf; ?>
<div class="space-y-6">
  <?php
  $pageTitle = 'Mon établissement';
  $pageSubtitle = 'Identité, coordonnées, horaires et réseaux sociaux — utilisés partout sur le site public et dans vos campagnes de proximité.';
  $pageActions = [
      ['href' => BASE_PATH . '/admin/offres', 'label' => 'Gérer les offres', 'class' => 'btn-secondary'],
      ['href' => BASE_PATH . '/admin/parametres', 'label' => 'Mentions légales & GA4', 'class' => 'btn-secondary'],
  ];
  require __DIR__ . '/../partials/admin-page-header.php';
  ?>

  <!-- KPIs -->
  <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
    <div class="card card-md">
      <p class="text-sm font-semibold text-gray-500 uppercase tracking-[0.18em] mb-3">Clients</p>
      <p class="text-4xl font-extrabold text-ink"><?= number_format($totalClients) ?></p>
      <p class="text-xs text-gray-400 mt-2">clients inscrits</p>
    </div>
    <div class="card card-md">
      <p class="text-sm font-semibold text-gray-500 uppercase tracking-[0.18em] mb-3">Offres actives</p>
      <p class="text-4xl font-extrabold text-ink"><?= number_format($activeOffers) ?></p>
      <p class="text-xs text-gray-400 mt-2">actuellement disponibles</p>
    </div>
    <div class="card card-md">
      <p class="text-sm font-semibold text-gray-500 uppercase tracking-[0.18em] mb-3">Réalisations</p>
      <p class="text-4xl font-extrabold text-ink"><?= number_format($redemptionsThisMonth) ?></p>
      <p class="text-xs text-gray-400 mt-2">offres utilisées ce mois</p>
    </div>
    <div class="card card-md">
      <p class="text-sm font-semibold text-gray-500 uppercase tracking-[0.18em] mb-3">Économies</p>
      <p class="text-4xl font-extrabold text-ink"><?= number_format($savingsEstimate, 2, ',', ' ') ?> €</p>
      <p class="text-xs text-gray-400 mt-2">valeur estimée</p>
    </div>
  </div>

  <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr] xl:grid-cols-[1.4fr_1fr]">
    <!-- Formulaire d'édition -->
    <form method="POST" action="<?= BASE_PATH ?>/admin/etablissement" class="space-y-6">
      <?= Csrf::field() ?>

      <div class="card card-md">
        <h2 class="font-bold text-ink mb-4">Identité</h2>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-semibold text-ink mb-1.5">Nom du commerce</label>
            <input type="text" name="shop_name" required value="<?= htmlspecialchars($shop['name']) ?>" class="form-input">
          </div>
          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-ink mb-1.5">Téléphone</label>
              <input type="text" name="shop_phone" value="<?= htmlspecialchars($shop['phone']) ?>" class="form-input">
            </div>
            <div>
              <label class="block text-sm font-semibold text-ink mb-1.5">E-mail</label>
              <input type="email" name="shop_email" value="<?= htmlspecialchars($shop['email']) ?>" class="form-input">
            </div>
          </div>
          <div>
            <label class="block text-sm font-semibold text-ink mb-1.5">Téléphone WhatsApp</label>
            <input type="text" name="shop_whatsapp" placeholder="Laisser vide pour utiliser le téléphone ci-dessus"
                   value="<?= htmlspecialchars($shop['whatsapp']) ?>" class="form-input">
            <p class="text-xs text-gray-400 mt-1">Utilisé pour les boutons "WhatsApp" du site. Renseignez-le uniquement s'il diffère du téléphone.</p>
          </div>
        </div>
      </div>

      <div class="card card-md">
        <h2 class="font-bold text-ink mb-4">Adresse &amp; localisation</h2>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-semibold text-ink mb-1.5">Adresse</label>
            <input type="text" name="shop_address" value="<?= htmlspecialchars($shop['address']) ?>" class="form-input">
          </div>
          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-ink mb-1.5">Code postal</label>
              <input type="text" name="shop_zipcode" value="<?= htmlspecialchars($shop['zipcode']) ?>" class="form-input">
            </div>
            <div>
              <label class="block text-sm font-semibold text-ink mb-1.5">Ville</label>
              <input type="text" name="shop_city" value="<?= htmlspecialchars($shop['city']) ?>" class="form-input">
            </div>
          </div>
          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-ink mb-1.5">Latitude <span class="text-gray-400 font-normal">(zonage &amp; proximité)</span></label>
              <input type="text" id="est-lat" name="latitude" value="<?= htmlspecialchars($shop['latitude']) ?>" class="form-input">
            </div>
            <div>
              <label class="block text-sm font-semibold text-ink mb-1.5">Longitude</label>
              <input type="text" id="est-lng" name="longitude" value="<?= htmlspecialchars($shop['longitude']) ?>" class="form-input">
            </div>
          </div>
          <div id="establishment-map" class="rounded-2xl border border-gray-100 h-56 w-full"></div>
          <p class="text-xs text-gray-400">Déplacez le repère sur la carte pour ajuster précisément les coordonnées, ou saisissez-les manuellement ci-dessus.</p>
        </div>
      </div>

      <div class="card card-md">
        <h2 class="font-bold text-ink mb-4">Visite virtuelle (Street View)</h2>
        <label class="block text-sm font-semibold text-ink mb-1.5">URL d'intégration Google Maps</label>
        <input type="url" name="streetview_embed_url" placeholder="https://www.google.com/maps/embed?pb=..."
               value="<?= htmlspecialchars($shop['streetview_embed_url'] ?? '') ?>" class="form-input">
        <p class="text-xs text-gray-400 mt-1">Depuis Google Maps : ouvrez la vue 360° de l'établissement, cliquez sur « Partager » puis « Intégrer une carte », et collez ici l'URL présente dans l'attribut src de l'iframe fournie.</p>
      </div>

      <div class="card card-md">
        <h2 class="font-bold text-ink mb-4">Horaires d'ouverture</h2>
        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-ink mb-1.5">
              Lundi au Samedi
              <?php if (!$isSunday): ?><span class="ml-1 inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-600 uppercase">Aujourd'hui</span><?php endif; ?>
            </label>
            <input type="text" name="hours_lun_sam" value="<?= htmlspecialchars($shop['hours']['lun_sam']) ?>" class="form-input">
          </div>
          <div>
            <label class="block text-sm font-semibold text-ink mb-1.5">
              Dimanche
              <?php if ($isSunday): ?><span class="ml-1 inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-600 uppercase">Aujourd'hui</span><?php endif; ?>
            </label>
            <input type="text" name="hours_dim" value="<?= htmlspecialchars($shop['hours']['dim']) ?>" class="form-input">
          </div>
        </div>
        <p class="text-xs text-gray-400 mt-3">Aujourd'hui : <span class="font-semibold text-ink capitalize"><?= htmlspecialchars($todayLabel) ?></span></p>
      </div>

      <div class="card card-md">
        <h2 class="font-bold text-ink mb-4">Réseaux sociaux</h2>
        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-ink mb-1.5">Facebook</label>
            <input type="url" name="social_facebook" value="<?= htmlspecialchars($shop['social']['facebook']) ?>" class="form-input">
          </div>
          <div>
            <label class="block text-sm font-semibold text-ink mb-1.5">Instagram</label>
            <input type="url" name="social_instagram" value="<?= htmlspecialchars($shop['social']['instagram']) ?>" class="form-input">
          </div>
        </div>
      </div>

      <button type="submit" class="btn-primary w-full">Enregistrer la fiche établissement</button>
    </form>

    <!-- Aperçu -->
    <aside class="space-y-4">
      <div class="card card-md border-brand-100 bg-brand-50 text-brand-800">
        <p class="text-xs uppercase tracking-[0.3em] font-bold text-brand-700 mb-3">Aperçu client</p>
        <div class="rounded-3xl bg-white p-5 shadow-sm">
          <p class="font-extrabold text-ink text-lg"><?= htmlspecialchars($shop['name']) ?></p>
          <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($shop['address']) ?></p>
          <p class="text-sm text-gray-500"><?= htmlspecialchars($shop['zipcode'] . ' ' . $shop['city']) ?></p>
          <div class="mt-3 pt-3 border-t border-gray-100 text-sm text-gray-600 space-y-1">
            <p><?= htmlspecialchars($shop['phone']) ?></p>
            <p class="break-all"><?= htmlspecialchars($shop['email']) ?></p>
          </div>
          <div class="mt-3 pt-3 border-t border-gray-100 text-sm text-gray-600 space-y-1">
            <p>Lun - Sam : <?= htmlspecialchars($shop['hours']['lun_sam']) ?></p>
            <p>Dimanche : <?= htmlspecialchars($shop['hours']['dim']) ?></p>
          </div>
        </div>
        <p class="text-xs text-brand-700 mt-3">Ce qui est enregistré ici s'affiche immédiatement sur toutes les pages publiques (en-tête, pied de page, contact) et dans les campagnes de proximité.</p>
      </div>

      <div class="card card-md bg-white border border-gray-100">
        <h2 class="font-bold text-ink mb-3">Pourquoi c'est important</h2>
        <ul class="space-y-3 text-sm text-gray-600">
          <li class="flex items-start gap-3">
            <span class="mt-1 inline-flex h-2.5 w-2.5 rounded-full bg-brand-500 shrink-0"></span>
            Une adresse précise (latitude/longitude) est indispensable pour les campagnes de <a href="<?= BASE_PATH ?>/admin/zonage" class="text-brand-500 hover:underline">Zonage &amp; Proximité</a>.
          </li>
          <li class="flex items-start gap-3">
            <span class="mt-1 inline-flex h-2.5 w-2.5 rounded-full bg-brand-500 shrink-0"></span>
            Le numéro WhatsApp alimente les envois automatiques d'offres et la <a href="<?= BASE_PATH ?>/admin/messages" class="text-brand-500 hover:underline">Messagerie</a>.
          </li>
          <li class="flex items-start gap-3">
            <span class="mt-1 inline-flex h-2.5 w-2.5 rounded-full bg-brand-500 shrink-0"></span>
            Les mentions légales et la configuration Google Analytics se gèrent désormais séparément dans <a href="<?= BASE_PATH ?>/admin/parametres" class="text-brand-500 hover:underline">Paramètres</a>.
          </li>
        </ul>
      </div>
    </aside>
  </div>
</div>

<!-- Leaflet (carte interactive, nécessite un accès Internet réel côté navigateur) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
  var latInput = document.getElementById('est-lat');
  var lngInput = document.getElementById('est-lng');
  var mapEl = document.getElementById('establishment-map');
  if (!latInput || !lngInput || !mapEl) return;

  if (typeof L === 'undefined') {
    mapEl.innerHTML = '<div class="flex h-full items-center justify-center text-xs text-gray-400">Carte indisponible sans connexion Internet.</div>';
    return; // pas de connexion : on n'affiche pas de carte cassée
  }

  var lat = parseFloat(latInput.value) || 48.8566;
  var lng = parseFloat(lngInput.value) || 2.3522;

  var map = L.map(mapEl).setView([lat, lng], 15);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  var marker = L.marker([lat, lng], { draggable: true }).addTo(map);
  marker.on('dragend', function (e) {
    var pos = e.target.getLatLng();
    latInput.value = pos.lat.toFixed(6);
    lngInput.value = pos.lng.toFixed(6);
  });

  setTimeout(function () { map.invalidateSize(); }, 200);
})();
</script>

