<?php use App\Core\Csrf; ?>

<div class="w-full max-w-md bg-white border border-gray-100 rounded-2xl shadow-sm p-8">
  <h1 class="font-extrabold text-2xl text-ink mb-1">Créer mon compte</h1>
  <p class="text-gray-500 text-sm mb-6">Rejoignez le programme fidélité et profitez de votre portefeuille en ligne.</p>

  <form method="POST" action="<?= BASE_PATH ?>/inscription" class="space-y-4">
    <?= Csrf::field() ?>

    <div class="grid grid-cols-2 gap-3">
      <div>
        <label class="block text-sm font-semibold text-ink mb-1.5">Prénom</label>
        <input type="text" name="first_name" required value="<?= htmlspecialchars($old['firstName'] ?? '') ?>"
               class="w-full border <?= isset($errors['first_name']) ? 'border-brand-400' : 'border-gray-200' ?> rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500">
        <?php if (isset($errors['first_name'])): ?><p class="text-brand-500 text-xs mt-1"><?= htmlspecialchars($errors['first_name']) ?></p><?php endif; ?>
      </div>
      <div>
        <label class="block text-sm font-semibold text-ink mb-1.5">Nom</label>
        <input type="text" name="last_name" required value="<?= htmlspecialchars($old['lastName'] ?? '') ?>"
               class="w-full border <?= isset($errors['last_name']) ? 'border-brand-400' : 'border-gray-200' ?> rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500">
        <?php if (isset($errors['last_name'])): ?><p class="text-brand-500 text-xs mt-1"><?= htmlspecialchars($errors['last_name']) ?></p><?php endif; ?>
      </div>
    </div>

    <div>
      <label class="block text-sm font-semibold text-ink mb-1.5">Numéro WhatsApp</label>
      <input type="tel" name="phone_whatsapp" required placeholder="06 12 34 56 78" value="<?= htmlspecialchars($old['phone'] ?? '') ?>"
             class="w-full border <?= isset($errors['phone_whatsapp']) ? 'border-brand-400' : 'border-gray-200' ?> rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500">
      <?php if (isset($errors['phone_whatsapp'])): ?><p class="text-brand-500 text-xs mt-1"><?= htmlspecialchars($errors['phone_whatsapp']) ?></p><?php endif; ?>
    </div>

    <div>
      <label class="block text-sm font-semibold text-ink mb-1.5">E-mail <span class="text-gray-400 font-normal">(facultatif)</span></label>
      <input type="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>"
             class="w-full border <?= isset($errors['email']) ? 'border-brand-400' : 'border-gray-200' ?> rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500">
      <?php if (isset($errors['email'])): ?><p class="text-brand-500 text-xs mt-1"><?= htmlspecialchars($errors['email']) ?></p><?php endif; ?>
    </div>

    <div>
      <label class="block text-sm font-semibold text-ink mb-1.5">Mot de passe</label>
      <input type="password" name="password" required minlength="6" placeholder="6 caractères minimum"
             class="w-full border <?= isset($errors['password']) ? 'border-brand-400' : 'border-gray-200' ?> rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500">
      <?php if (isset($errors['password'])): ?><p class="text-brand-500 text-xs mt-1"><?= htmlspecialchars($errors['password']) ?></p><?php endif; ?>
    </div>

    <div>
      <label class="block text-sm font-semibold text-ink mb-1.5">Comment nous avez-vous connu ?</label>
      <select name="registration_source" class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500">
        <?php $selectedSource = $old['source'] ?? 'bar'; ?>
        <?php foreach (\App\Models\User::SOURCE_LABELS as $value => $label): ?>
          <option value="<?= htmlspecialchars($value) ?>" <?= $selectedSource === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <label class="flex items-start gap-2.5 text-sm text-gray-600">
      <input type="checkbox" name="geolocation_opt_in" value="1" class="mt-0.5 rounded border-gray-300 text-brand-500 focus:ring-brand-500/30">
      Je souhaite recevoir des offres exclusives lorsque je suis à proximité du Commerce.
    </label>

    <button type="submit" class="w-full bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm px-6 py-3.5 rounded-lg transition-colors">
      Créer mon compte
    </button>
  </form>

  <p class="text-center text-sm text-gray-500 mt-6">
    Déjà inscrit ?
    <a href="<?= BASE_PATH ?>/connexion" class="text-brand-500 font-semibold hover:underline">Se connecter</a>
  </p>
</div>
