<?php
$pageTitle = 'Modifier la campagne';
$pageSubtitle = 'Mettez à jour les paramètres de cette campagne de proximité.';
$pageActions = [];
require __DIR__ . '/../../partials/admin-page-header.php';
?>

<div class="max-w-2xl">
  <a href="<?= BASE_PATH ?>/admin/zonage" class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-500 hover:text-brand-500 mb-4">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    Retour aux campagnes
  </a>

  <div class="card card-md sm:p-8">
    <?php
      $formAction  = '/admin/zonage/' . $campaign['id'];
      $submitLabel = 'Enregistrer les modifications';
      require __DIR__ . '/_form.php';
    ?>
  </div>
</div>
