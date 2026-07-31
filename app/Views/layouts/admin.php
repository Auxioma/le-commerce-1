<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($title ?? $shop['name']) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&family=Pacifico&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= assetVersion('assets/css/app.css') ?>">
</head>
<body class="bg-slate-100 text-ink antialiased font-sans">

<div class="min-h-screen">
  <div class="flex min-h-screen">
    <?php require __DIR__ . '/../partials/admin-sidebar.php'; ?>

    <div class="flex-1 min-w-0 flex flex-col">
      <?php require __DIR__ . '/../partials/admin-topbar.php'; ?>
      <?php require __DIR__ . '/../partials/flash.php'; ?>

      <main class="flex-1 p-6 lg:p-8">
        <div class="mx-auto w-full max-w-[1720px]">
          <?= $content ?>
        </div>
      </main>
    </div>
  </div>
</div>

<script src="<?= BASE_PATH ?>/assets/js/app.js"></script>
</body>
</html>
