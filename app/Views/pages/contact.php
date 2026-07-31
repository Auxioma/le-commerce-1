<?php
$heroEyebrow = 'CONTACT';
$heroText = "Une question, une réservation, une remarque ? Écrivez-nous, notre équipe vous répond rapidement.";
$heroActions = [];
$heroSlug = 'hero_contact';
require __DIR__ . '/../partials/page-hero.php';

$mapQuery = urlencode($shop['address'] . ', ' . $shop['zipcode'] . ' ' . $shop['city']);
?>

<section class="max-w-[1536px] mx-auto px-6 lg:px-10 py-12">
  <div class="grid lg:grid-cols-[1.2fr_0.8fr] gap-6">

    <!-- Formulaire -->
    <div class="card card-md">
      <h2 class="font-bold text-lg text-ink mb-1">Envoyez-nous un message</h2>
      <p class="text-sm text-gray-500 mb-6">Nous vous répondons généralement sous 24 à 48h.</p>

      <div id="contact-feedback" class="hidden mb-5 rounded-2xl px-4 py-3 text-sm font-semibold"></div>

      <form id="contact-form" method="POST" action="<?= BASE_PATH ?>/contact" class="space-y-4">
        <?= \App\Core\Csrf::field() ?>

        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-ink mb-1.5">Nom complet</label>
            <input type="text" name="name" required placeholder="Votre nom" class="form-input">
          </div>
          <div>
            <label class="block text-sm font-semibold text-ink mb-1.5">E-mail</label>
            <input type="email" name="email" required placeholder="vous@exemple.fr" class="form-input">
          </div>
        </div>

        <div>
          <label class="block text-sm font-semibold text-ink mb-1.5">Sujet</label>
          <select name="subject" class="form-select">
            <option value="Renseignement général">Renseignement général</option>
            <option value="Réservation">Réservation</option>
            <option value="Suggestion">Suggestion</option>
            <option value="Autre">Autre</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-semibold text-ink mb-1.5">Message</label>
          <textarea name="message" rows="5" required placeholder="Votre message..." class="form-textarea"></textarea>
        </div>

        <button type="submit" class="btn-primary w-full" id="contact-submit">
          Envoyer le message
        </button>
      </form>
    </div>

    <!-- Coordonnées -->
    <div class="space-y-6">
      <div class="card card-md">
        <h2 class="font-bold text-ink mb-4">Nos coordonnées</h2>
        <div class="space-y-4 text-sm">
          <div class="flex gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-brand-500 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C7.6 2 4 5.6 4 10c0 6 8 12 8 12s8-6 8-12c0-4.4-3.6-8-8-8zm0 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/></svg>
            <div>
              <p class="text-ink font-medium"><?= htmlspecialchars($shop['address']) ?></p>
              <p class="text-gray-500"><?= htmlspecialchars($shop['zipcode'] . ' ' . $shop['city']) ?></p>
            </div>
          </div>
          <div class="flex gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-brand-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            <a href="tel:<?= htmlspecialchars($shop['phone_href']) ?>" class="text-ink font-medium hover:text-brand-500"><?= htmlspecialchars($shop['phone']) ?></a>
          </div>
          <div class="flex gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-brand-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            <a href="mailto:<?= htmlspecialchars($shop['email']) ?>" class="text-ink font-medium hover:text-brand-500 break-all"><?= htmlspecialchars($shop['email']) ?></a>
          </div>
          <div class="flex gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-brand-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 7v5l3 3"/></svg>
            <div class="text-gray-600">
              <p>Lundi au Samedi : <?= htmlspecialchars($shop['hours']['lun_sam']) ?></p>
              <p>Dimanche : <?= htmlspecialchars($shop['hours']['dim']) ?></p>
            </div>
          </div>
        </div>
      </div>

      <div class="rounded-2xl overflow-hidden border border-gray-100 shadow-sm h-64">
        <iframe
          src="https://www.google.com/maps?q=<?= $mapQuery ?>&output=embed"
          class="w-full h-full border-0"
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade"
          title="Localisation de <?= htmlspecialchars($shop['name']) ?>">
        </iframe>
      </div>
    </div>
  </div>

  <div class="mt-6">
    <?php require __DIR__ . '/../partials/street-view-section.php'; ?>
  </div>
</section>
