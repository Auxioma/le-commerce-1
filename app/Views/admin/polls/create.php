<?php
$pageTitle = 'Créer un sondage';
$pageSubtitle = 'Lancez un sondage pour mieux connaître vos clients et collecter des retours utiles.';
$pageActions = [];
require __DIR__ . '/../../partials/admin-page-header.php';
?>

<div class="max-w-2xl">
  <a href="<?= BASE_PATH ?>/admin/sondages" class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-500 hover:text-brand-500 mb-4">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    Retour aux sondages
  </a>

  <div class="card card-md sm:p-8">
    <p class="text-gray-500 text-sm mb-6">Choisissez un thème, ajoutez vos options et paramétrez la durée.</p>

    <?php
      $formAction  = '/admin/sondages';
      $submitLabel = 'Créer le sondage';
      $optionRows  = $old['options'] ?? [];
      require __DIR__ . '/_form.php';
    ?>
  </div>
</div>

