<?php use App\Core\Csrf; ?>

<?php
$pageTitle = 'Paramètres du commerce';
$pageSubtitle = 'Modifiez les informations de votre établissement et les coordonnées utilisées partout sur le site.';
$pageActions = [];
require __DIR__ . '/../../partials/admin-page-header.php';
?>



<div class="max-w-4xl mx-auto space-y-6">
  <div class="card card-md">
    <p class="text-sm text-gray-500">Ces informations sont utilisées sur tout le site (en-tête, pied de page, contact).</p>
  </div>

  <form method="POST" action="<?= BASE_PATH ?>/admin/parametres" class="space-y-6">
    <?= Csrf::field() ?>

    <div class="card card-md">
      <h2 class="font-bold text-ink mb-4">Identité</h2>
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-semibold text-ink mb-1.5">Nom du commerce</label>
          <input type="text" name="shop_name" required value="<?= htmlspecialchars($shop['name']) ?>"
                 class="form-input">
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-ink mb-1.5">Téléphone</label>
            <input type="text" name="shop_phone" value="<?= htmlspecialchars($shop['phone']) ?>"
                   class="form-input">
          </div>
          <div>
            <label class="block text-sm font-semibold text-ink mb-1.5">E-mail</label>
            <input type="email" name="shop_email" required value="<?= htmlspecialchars($shop['email']) ?>"
                   class="form-input">
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
          <input type="text" name="shop_address" value="<?= htmlspecialchars($shop['address']) ?>"
                 class="form-input">
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-ink mb-1.5">Code postal</label>
            <input type="text" name="shop_zipcode" value="<?= htmlspecialchars($shop['zipcode']) ?>"
                   class="form-input">
          </div>
          <div>
            <label class="block text-sm font-semibold text-ink mb-1.5">Ville</label>
            <input type="text" name="shop_city" value="<?= htmlspecialchars($shop['city']) ?>"
                   class="form-input">
          </div>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-ink mb-1.5">Latitude <span class="text-gray-400 font-normal">(zonage & proximité)</span></label>
            <input type="text" name="latitude" value="<?= htmlspecialchars($shop['latitude']) ?>"
                   class="form-input">
          </div>
          <div>
            <label class="block text-sm font-semibold text-ink mb-1.5">Longitude</label>
            <input type="text" name="longitude" value="<?= htmlspecialchars($shop['longitude']) ?>"
                   class="form-input">
          </div>
        </div>
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
          <label class="block text-sm font-semibold text-ink mb-1.5">Lundi au Samedi</label>
          <input type="text" name="hours_lun_sam" value="<?= htmlspecialchars($shop['hours']['lun_sam']) ?>"
                 class="form-input">
        </div>
        <div>
          <label class="block text-sm font-semibold text-ink mb-1.5">Dimanche</label>
          <input type="text" name="hours_dim" value="<?= htmlspecialchars($shop['hours']['dim']) ?>"
                 class="form-input">
        </div>
      </div>
    </div>

    <div class="card card-md">
      <h2 class="font-bold text-ink mb-4">Réseaux sociaux</h2>
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-semibold text-ink mb-1.5">Facebook</label>
          <input type="url" name="social_facebook" value="<?= htmlspecialchars($shop['social']['facebook']) ?>"
                 class="form-input">
        </div>
        <div>
          <label class="block text-sm font-semibold text-ink mb-1.5">Instagram</label>
          <input type="url" name="social_instagram" value="<?= htmlspecialchars($shop['social']['instagram']) ?>"
                 class="form-input">
        </div>
      </div>
    </div>

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

    <button type="submit" class="btn-primary w-full">
      Enregistrer les modifications
    </button>
  </form>
</div>
