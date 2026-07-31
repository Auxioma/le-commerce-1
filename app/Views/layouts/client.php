<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($title ?? $shop['name']) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&family=Pacifico&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= assetVersion('assets/css/app.css') ?>">
<style>
  html, body { scrollbar-width: none; }
  ::-webkit-scrollbar { display: none; }
  @media (max-width: 1024px) {
    body { background: white; }
  }
</style>
</head>
<body class="bg-gray-50 text-ink antialiased font-sans overflow-x-hidden">

<?php require __DIR__ . '/../partials/header.php'; ?>
<?php require __DIR__ . '/../partials/flash.php'; ?>

<div class="flex flex-col lg:flex-row min-h-[calc(100vh-80px)]">
  <!-- Sidebar (partials/client-sidebar.php fournit déjà son propre <aside>) -->
  <div class="hidden lg:flex flex-col w-64 border-r border-gray-100 bg-white sticky top-[80px] h-[calc(100vh-80px)]">
    <?php require __DIR__ . '/../partials/client-sidebar.php'; ?>
  </div>

  <!-- Main content -->
  <main class="flex-1 px-6 lg:px-10 py-8 lg:py-10 bg-gray-50">
    <div class="max-w-6xl mx-auto">
      <?= $content ?>
    </div>
  </main>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
<?php require __DIR__ . '/../partials/chat-widget.php'; ?>
<?php require __DIR__ . '/../partials/cookie-consent.php'; ?>
<?php require __DIR__ . '/../partials/proximity-widget.php'; ?>

<script src="<?= BASE_PATH ?>/assets/js/app.js"></script>
</body>
</html>
