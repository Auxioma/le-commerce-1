<?php
$heroEyebrow = 'RÉSERVATION';
$heroText = "Une table pour un moment convivial ? Laissez-nous vos coordonnées, nous confirmons votre réservation rapidement par téléphone.";
$heroActions = [];
$heroSlug = 'hero_bar';
require __DIR__ . '/../partials/page-hero.php';
?>

<section class="max-w-[1536px] mx-auto px-6 lg:px-10 py-12">
  <div class="max-w-xl mx-auto card card-md">
    <h2 class="font-bold text-lg text-ink mb-1">Vos informations</h2>
    <p class="text-sm text-gray-500 mb-6">Tous les champs marqués d'un * sont obligatoires.</p>

    <form method="POST" action="<?= BASE_PATH ?>/reservation" class="space-y-4">
      <?= \App\Core\Csrf::field() ?>

      <div>
        <label class="block text-sm font-semibold text-ink mb-1.5">Nom complet *</label>
        <input type="text" name="name" required value="<?= htmlspecialchars($old['name'] ?? '') ?>" placeholder="Votre nom" class="form-input">
        <?php if (!empty($errors['name'])): ?><p class="text-xs text-brand-500 mt-1"><?= htmlspecialchars($errors['name']) ?></p><?php endif; ?>
      </div>

      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-semibold text-ink mb-1.5">Téléphone *</label>
          <input type="tel" name="phone" required value="<?= htmlspecialchars($old['phone'] ?? '') ?>" placeholder="06 12 34 56 78" class="form-input">
          <?php if (!empty($errors['phone'])): ?><p class="text-xs text-brand-500 mt-1"><?= htmlspecialchars($errors['phone']) ?></p><?php endif; ?>
        </div>
        <div>
          <label class="block text-sm font-semibold text-ink mb-1.5">E-mail</label>
          <input type="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" placeholder="vous@exemple.fr" class="form-input">
          <?php if (!empty($errors['email'])): ?><p class="text-xs text-brand-500 mt-1"><?= htmlspecialchars($errors['email']) ?></p><?php endif; ?>
        </div>
      </div>

      <div class="grid sm:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-semibold text-ink mb-1.5">Date *</label>
          <input type="date" name="reservation_date" required min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($old['date'] ?? '') ?>" class="form-input">
          <?php if (!empty($errors['reservation_date'])): ?><p class="text-xs text-brand-500 mt-1"><?= htmlspecialchars($errors['reservation_date']) ?></p><?php endif; ?>
        </div>
        <div>
          <label class="block text-sm font-semibold text-ink mb-1.5">Heure *</label>
          <input type="time" name="reservation_time" required value="<?= htmlspecialchars($old['time'] ?? '') ?>" class="form-input">
          <?php if (!empty($errors['reservation_time'])): ?><p class="text-xs text-brand-500 mt-1"><?= htmlspecialchars($errors['reservation_time']) ?></p><?php endif; ?>
        </div>
        <div>
          <label class="block text-sm font-semibold text-ink mb-1.5">Personnes *</label>
          <input type="number" name="party_size" required min="1" max="30" value="<?= htmlspecialchars((string) ($old['partySize'] ?? 2)) ?>" class="form-input">
          <?php if (!empty($errors['party_size'])): ?><p class="text-xs text-brand-500 mt-1"><?= htmlspecialchars($errors['party_size']) ?></p><?php endif; ?>
        </div>
      </div>

      <div>
        <label class="block text-sm font-semibold text-ink mb-1.5">Précisions (facultatif)</label>
        <textarea name="note" rows="3" placeholder="Anniversaire, allergies, préférence de table..." class="form-textarea"><?= htmlspecialchars($old['note'] ?? '') ?></textarea>
      </div>

      <button type="submit" class="btn-primary w-full">Envoyer ma demande de réservation</button>
    </form>
  </div>
</section>
