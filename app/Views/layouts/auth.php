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
<body class="bg-gray-50 text-ink antialiased font-sans min-h-screen flex flex-col">

  <header class="py-6">
    <a href="<?= BASE_PATH ?>/" class="flex flex-col items-center leading-none">
      <span class="font-logo text-[32px] font-bold text-brand-500 -mb-1"><?= htmlspecialchars($shop['name']) ?></span>
      <span class="text-[11px] tracking-[0.15em] text-gray-500 font-medium">BAR · TABAC · PMU · FDJ · PRESSE · NIRIO</span>
    </a>
  </header>

  <?php require __DIR__ . '/../partials/flash.php'; ?>

  <main class="flex-1 flex items-center justify-center px-4 pb-16">
    <?= $content ?>
  </main>

  <footer class="text-center text-xs text-gray-400 pb-6">
    &copy; <?= date('Y') ?> <?= htmlspecialchars($shop['name']) ?> — <?= htmlspecialchars($shop['address'] . ', ' . $shop['zipcode'] . ' ' . $shop['city']) ?>
  </footer>

</body>
</html>
