<?php use App\Core\Csrf; ?>

<?php
$pageTitle = 'Paramètres';
$pageSubtitle = 'Mentions légales, hébergeur et configuration Google Analytics. L\'identité, les coordonnées et les horaires du commerce se gèrent désormais depuis Mon établissement.';
$pageActions = [
    ['href' => BASE_PATH . '/admin/etablissement', 'label' => 'Mon établissement', 'class' => 'btn-primary'],
];
require __DIR__ . '/../../partials/admin-page-header.php';
?>

<div class="max-w-4xl mx-auto space-y-6">
  <div class="card card-md bg-brand-50 border-brand-100">
    <p class="text-sm text-brand-800">L'identité du commerce (nom, contact, adresse, horaires, réseaux sociaux) se modifie désormais depuis <a href="<?= BASE_PATH ?>/admin/etablissement" class="font-semibold underline">Mon établissement</a>. Cette page ne conserve que les mentions légales et la configuration technique (Google Analytics).</p>
  </div>

  <form method="POST" action="<?= BASE_PATH ?>/admin/parametres" class="space-y-6">
    <?= Csrf::field() ?>

    <div class="card card-md">
      <h2 class="font-bold text-ink mb-4">Mentions légales &amp; hébergeur</h2>
      <p class="text-sm text-gray-500 mb-4">Ces informations apparaissent sur la page « Mentions légales ». Elles peuvent être complétées plus tard.</p>
      <div class="space-y-4">
        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-ink mb-1.5">Forme juridique</label>
            <input type="text" name="legal_forme_juridique" placeholder="Ex : Entreprise individuelle, SARL..."
                   value="<?= htmlspecialchars($shop['legal']['forme_juridique']) ?>" class="form-input">
          </div>
          <div>
            <label class="block text-sm font-semibold text-ink mb-1.5">Capital social <span class="text-gray-400 font-normal">(si société)</span></label>
            <input type="text" name="legal_capital_social" placeholder="Ex : 10 000 €"
                   value="<?= htmlspecialchars($shop['legal']['capital_social']) ?>" class="form-input">
          </div>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-ink mb-1.5">SIRET</label>
            <input type="text" name="legal_siret" value="<?= htmlspecialchars($shop['legal']['siret']) ?>" class="form-input">
          </div>
          <div>
            <label class="block text-sm font-semibold text-ink mb-1.5">Directeur de la publication</label>
            <input type="text" name="legal_directeur_publication" value="<?= htmlspecialchars($shop['legal']['directeur_publication']) ?>" class="form-input">
          </div>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-ink mb-1.5">Numéro RCS</label>
            <input type="text" name="legal_rcs_numero" value="<?= htmlspecialchars($shop['legal']['rcs_numero']) ?>" class="form-input">
          </div>
          <div>
            <label class="block text-sm font-semibold text-ink mb-1.5">Ville d'immatriculation (RCS)</label>
            <input type="text" name="legal_rcs_ville" value="<?= htmlspecialchars($shop['legal']['rcs_ville']) ?>" class="form-input">
          </div>
        </div>
        <div class="border-t border-gray-100 pt-4">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Hébergeur du site</p>
          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-ink mb-1.5">Nom de l'hébergeur</label>
              <input type="text" name="legal_hebergeur_nom" value="<?= htmlspecialchars($shop['legal']['hebergeur_nom']) ?>" class="form-input">
            </div>
            <div>
              <label class="block text-sm font-semibold text-ink mb-1.5">Téléphone de l'hébergeur</label>
              <input type="text" name="legal_hebergeur_telephone" value="<?= htmlspecialchars($shop['legal']['hebergeur_telephone']) ?>" class="form-input">
            </div>
          </div>
          <div class="mt-4">
            <label class="block text-sm font-semibold text-ink mb-1.5">Adresse de l'hébergeur</label>
            <input type="text" name="legal_hebergeur_adresse" value="<?= htmlspecialchars($shop['legal']['hebergeur_adresse']) ?>" class="form-input">
          </div>
        </div>
      </div>
    </div>

    <div class="card card-md">
      <h2 class="font-bold text-ink mb-4">Google Analytics (GA4)</h2>
      <p class="text-sm text-gray-500 mb-4">
        Ces informations alimentent la page <a href="<?= BASE_PATH ?>/admin/google-analytics" class="text-brand-500 font-semibold hover:underline">Google Analytics</a> du back-office.
        Créez un <a href="https://console.cloud.google.com/iam-admin/serviceaccounts" target="_blank" rel="noopener" class="text-brand-500 hover:underline">compte de service Google Cloud</a>,
        téléchargez sa clé au format JSON, puis ajoutez son adresse e-mail (<code>...@...iam.gserviceaccount.com</code>) comme utilisateur
        « Lecteur » dans les paramètres d'administration de votre propriété GA4.
      </p>
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-semibold text-ink mb-1.5">Identifiant de propriété GA4 (Property ID)</label>
          <input type="text" name="ga4_property_id" placeholder="Ex : 123456789"
                 value="<?= htmlspecialchars(\App\Models\Settings::get('ga4_property_id', '')) ?>" class="form-input">
        </div>
        <div>
          <label class="block text-sm font-semibold text-ink mb-1.5">Clé du compte de service (contenu JSON)</label>
          <textarea name="ga4_service_account_json" rows="6" placeholder='{"type": "service_account", "private_key": "...", "client_email": "..."}'
                    class="form-input font-mono text-xs"><?= htmlspecialchars(\App\Models\Settings::get('ga4_service_account_json', '')) ?></textarea>
          <p class="text-xs text-gray-400 mt-1">Collez ici le contenu complet du fichier .json téléchargé depuis Google Cloud Console.</p>
        </div>
      </div>
    </div>

    <button type="submit" class="btn-primary w-full">
      Enregistrer les modifications
    </button>
  </form>
</div>
