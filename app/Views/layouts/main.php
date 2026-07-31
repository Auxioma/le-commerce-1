<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($title ?? $shop['name']) ?></title>
<meta name="description" content="<?= htmlspecialchars($shop['name']) ?> — Bar, Tabac, PMU, FDJ, Presse à <?= htmlspecialchars($shop['city']) ?>. Votre commerce de proximité.">

<!-- Polices -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&family=Pacifico&display=swap" rel="stylesheet">

<!-- CSS compilé (Tailwind + DaisyUI, build local via `npm run build`) -->
<link rel="stylesheet" href="<?= assetVersion('assets/css/app.css') ?>">
<style>
  html, body { scrollbar-width: none !important; }
  ::-webkit-scrollbar { width: 0px !important; height: 0px !important; display: none !important; }
  ::-webkit-scrollbar-track { background: transparent !important; }
  ::-webkit-scrollbar-thumb { background: transparent !important; }
  ::-webkit-scrollbar-corner { background: transparent !important; }
</style>
</head>
<body class="bg-white text-ink antialiased font-sans overflow-x-hidden">

<?php require __DIR__ . '/../partials/header.php'; ?>
<?php require __DIR__ . '/../partials/flash.php'; ?>

<main>
<?= $content ?>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
<?php require __DIR__ . '/../partials/chat-widget.php'; ?>
<?php require __DIR__ . '/../partials/cookie-consent.php'; ?>

<script src="<?= BASE_PATH ?>/assets/js/app.js"></script>
</body>
</html>
