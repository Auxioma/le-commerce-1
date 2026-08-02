<?php use App\Core\Csrf; ?>

<!-- En-tête -->
<div class="mb-6">
  <h1 class="font-extrabold text-ink" style="font-size:22px; letter-spacing:-0.5px;">Messages</h1>
  <p class="text-gray-500 mt-1" style="font-size:13px;">Messages du formulaire de contact et échanges WhatsApp avec vos clients, au même endroit.</p>
</div>

<!-- Cartes stats -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
  <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 flex items-center gap-3">
    <div class="w-10 h-10 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center shrink-0">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4-.8L3 20l1.3-3.9A7.96 7.96 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
    </div>
    <div>
      <p class="text-gray-500 font-semibold" style="font-size:11px;">TOUTES LES BOÎTES</p>
      <p class="font-black text-ink" style="font-size:22px; line-height:1;"><?= number_format($totalContacts + $totalWhatsapp) ?></p>
    </div>
  </div>
  <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 flex items-center gap-3">
    <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0" style="background:#fee2e2; color:#c8272c;">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
    </div>
    <div>
      <p class="text-gray-500 font-semibold" style="font-size:11px;">E-MAILS (CONTACT)</p>
      <p class="font-black text-ink" style="font-size:22px; line-height:1;"><?= number_format($totalContacts) ?> <span class="text-brand-500 font-semibold" style="font-size:12px;"><?= $unreadContacts ?> non lus</span></p>
    </div>
  </div>
  <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 flex items-center gap-3">
    <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0" style="background:#dcfce7; color:#16a34a;">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.4A10 10 0 1 0 12 2zm0 18a8 8 0 0 1-4.1-1.1l-.3-.2-2.9.8.8-2.8-.2-.3A8 8 0 1 1 12 20z"/></svg>
    </div>
    <div>
      <p class="text-gray-500 font-semibold" style="font-size:11px;">WHATSAPP</p>
      <p class="font-black text-ink" style="font-size:22px; line-height:1;"><?= number_format($totalWhatsapp) ?> <span class="text-green-600 font-semibold" style="font-size:12px;"><?= $whatsappIncoming ?> reçus</span></p>
    </div>
  </div>
</div>

<!-- Boîte de réception -->
<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden" style="height:640px;">
  <div class="grid h-full" style="grid-template-columns: 300px 1fr 280px;">

    <!-- Colonne 1 : liste -->
    <div class="border-r border-gray-100 overflow-y-auto">
      <?php if (empty($inbox)): ?>
        <div class="text-center py-16 px-4">
          <p class="text-gray-400 text-sm">Aucun message pour le moment.</p>
        </div>
      <?php endif; ?>
      <?php foreach ($inbox as $item):
        $isSelected = ($item['type'] === 'contact' && $selectedContact && (int) $selectedContact['id'] === $item['id'])
                   || ($item['type'] === 'whatsapp' && $selectedUser && (int) $selectedUser['id'] === $item['id']);
        $href = $item['type'] === 'contact'
          ? BASE_PATH . '/admin/messages?type=contact&id=' . $item['id']
          : BASE_PATH . '/admin/messages?type=whatsapp&user=' . $item['id'];
      ?>
        <a href="<?= $href ?>" class="flex items-start gap-2.5 px-4 py-3 border-b border-gray-50 transition-colors <?= $isSelected ? 'bg-brand-50' : 'hover:bg-gray-50' ?>">
          <span class="w-2 h-2 rounded-full mt-1.5 shrink-0 <?= $item['unread'] ? 'bg-brand-500' : 'bg-transparent' ?>"></span>
          <?php if ($item['type'] === 'whatsapp'): ?>
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 mt-1 shrink-0" style="color:#25D366;" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.4A10 10 0 1 0 12 2zm0 18a8 8 0 0 1-4.1-1.1l-.3-.2-2.9.8.8-2.8-.2-.3A8 8 0 1 1 12 20z"/></svg>
          <?php else: ?>
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 mt-1 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
          <?php endif; ?>
          <div class="min-w-0 flex-1">
            <div class="flex items-center justify-between gap-2">
              <p class="font-semibold text-ink truncate" style="font-size:12.5px;"><?= htmlspecialchars($item['name']) ?></p>
              <span class="text-gray-400 shrink-0" style="font-size:10.5px;"><?= date('d/m', strtotime($item['time'])) ?></span>
            </div>
            <p class="text-gray-500 truncate" style="font-size:11.5px;"><?= htmlspecialchars($item['preview']) ?></p>
          </div>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Colonne 2 : conversation / message -->
    <div class="flex flex-col min-w-0">
      <?php if ($selectedContact): ?>
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
          <div>
            <p class="font-bold text-ink" style="font-size:14px;"><?= htmlspecialchars($selectedContact['name']) ?></p>
            <p class="text-gray-400" style="font-size:12px;"><?= htmlspecialchars($selectedContact['email']) ?> · <?= date('d/m/Y H:i', strtotime($selectedContact['created_at'])) ?></p>
          </div>
          <form method="POST" action="<?= BASE_PATH ?>/admin/messages/contact/<?= $selectedContact['id'] ?>/lu">
            <?= Csrf::field() ?>
            <button type="submit" class="text-xs font-semibold text-gray-400 hover:text-brand-500 transition-colors">
              Marquer <?= $selectedContact['is_read'] ? 'non lu' : 'lu' ?>
            </button>
          </form>
        </div>
        <div class="flex-1 overflow-y-auto p-5">
          <p class="font-bold text-ink mb-3" style="font-size:14px;"><?= htmlspecialchars($selectedContact['subject'] ?: 'Sans objet') ?></p>
          <p class="text-gray-600 whitespace-pre-line leading-relaxed" style="font-size:13.5px;"><?= htmlspecialchars($selectedContact['message']) ?></p>
        </div>
        <div class="px-5 py-4 border-t border-gray-100">
          <a href="mailto:<?= htmlspecialchars($selectedContact['email']) ?>?subject=<?= rawurlencode('RE: ' . ($selectedContact['subject'] ?: 'Votre message')) ?>"
             class="inline-flex items-center gap-2 text-white font-bold px-4 py-2.5 rounded-lg transition-opacity hover:opacity-90" style="background:#c8272c; font-size:13px;">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            Répondre par e-mail
          </a>
        </div>

      <?php elseif ($selectedUser && $selectedThread !== null): ?>
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <p class="font-bold text-ink" style="font-size:14px;"><?= htmlspecialchars($selectedUser['first_name'] . ' ' . $selectedUser['last_name']) ?></p>
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" style="color:#25D366;" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.4A10 10 0 1 0 12 2zm0 18a8 8 0 0 1-4.1-1.1l-.3-.2-2.9.8.8-2.8-.2-.3A8 8 0 1 1 12 20z"/></svg>
          </div>
          <a href="<?= BASE_PATH ?>/admin/clients/<?= $selectedUser['id'] ?>" class="text-xs font-semibold text-brand-500 hover:text-brand-600">Voir la fiche</a>
        </div>
        <div class="flex-1 overflow-y-auto p-5 space-y-3" style="background:#f7f7f6;">
          <?php if (empty($selectedThread)): ?>
            <p class="text-gray-400 text-sm text-center py-8">Aucun message échangé pour l'instant.</p>
          <?php endif; ?>
          <?php foreach ($selectedThread as $msg): $out = $msg['direction'] === 'sortant'; ?>
            <div class="flex <?= $out ? 'justify-end' : 'justify-start' ?>">
              <div class="max-w-[75%] rounded-2xl px-4 py-2.5 <?= $out ? 'text-white' : 'bg-white border border-gray-100' ?>" style="<?= $out ? 'background:#25D366;' : '' ?>">
                <p class="whitespace-pre-line" style="font-size:13px;"><?= htmlspecialchars($msg['content']) ?></p>
                <p class="mt-1 <?= $out ? 'text-white/70' : 'text-gray-400' ?>" style="font-size:10.5px;"><?= date('d/m H:i', strtotime($msg['sent_at'])) ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <form method="POST" action="<?= BASE_PATH ?>/admin/messages/whatsapp/<?= $selectedUser['id'] ?>" class="px-4 py-3 border-t border-gray-100 flex items-center gap-2">
          <?= Csrf::field() ?>
          <input type="text" name="content" required placeholder="Écrire un message..."
                 class="flex-1 min-w-0 border border-gray-200 rounded-full px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-green-500/30 focus:border-green-500" style="font-size:13px;">
          <button type="submit" class="shrink-0 w-10 h-10 rounded-full flex items-center justify-center text-white transition-opacity hover:opacity-90" style="background:#25D366;">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
          </button>
        </form>

      <?php else: ?>
        <div class="flex-1 flex items-center justify-center">
          <p class="text-gray-400 text-sm">Sélectionnez une conversation.</p>
        </div>
      <?php endif; ?>
    </div>

    <!-- Colonne 3 : détails contact -->
    <div class="border-l border-gray-100 overflow-y-auto p-5">
      <?php if ($selectedUser): ?>
        <p class="text-gray-400 uppercase font-bold mb-3" style="font-size:10.5px; letter-spacing:0.5px;">Détails du contact</p>
        <p class="font-bold text-ink" style="font-size:14px;"><?= htmlspecialchars($selectedUser['first_name'] . ' ' . $selectedUser['last_name']) ?></p>
        <p class="text-gray-500 mt-1" style="font-size:12.5px;"><?= htmlspecialchars($selectedUser['phone_whatsapp']) ?></p>
        <?php if (!empty($selectedUser['email'])): ?>
          <p class="text-gray-500 break-all" style="font-size:12.5px;"><?= htmlspecialchars($selectedUser['email']) ?></p>
        <?php endif; ?>
        <p class="text-gray-400 mt-2" style="font-size:11.5px;">Inscrit le <?= date('d/m/Y', strtotime($selectedUser['created_at'])) ?></p>
        <p class="mt-2">
          <span class="px-2.5 py-1 rounded-full font-semibold bg-gray-100 text-gray-700" style="font-size:11px;">
            <?= ['fidele' => 'Fidèle', 'nouveau' => 'Nouveau', 'occasionnel' => 'Occasionnel'][$selectedUser['segment']] ?? htmlspecialchars($selectedUser['segment']) ?>
          </span>
        </p>
        <?php if ($selectedWallet): ?>
          <p class="mt-3 text-gray-500" style="font-size:12px;">Portefeuille : <span class="font-bold text-ink"><?= number_format((float) $selectedWallet['balance'], 2, ',', ' ') ?> €</span></p>
        <?php endif; ?>

        <p class="text-gray-400 uppercase font-bold mt-5 mb-2" style="font-size:10.5px; letter-spacing:0.5px;">Actions rapides</p>
        <div class="space-y-1.5">
          <a href="tel:<?= htmlspecialchars($selectedUser['phone_whatsapp']) ?>" class="flex items-center gap-2 text-gray-600 hover:text-brand-500 transition-colors" style="font-size:12.5px;">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            Appeler
          </a>
          <a href="<?= BASE_PATH ?>/admin/clients/<?= $selectedUser['id'] ?>" class="flex items-center gap-2 text-gray-600 hover:text-brand-500 transition-colors" style="font-size:12.5px;">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            Voir la fiche client
          </a>
        </div>

      <?php elseif ($selectedContact): ?>
        <p class="text-gray-400 uppercase font-bold mb-3" style="font-size:10.5px; letter-spacing:0.5px;">Détails du contact</p>
        <p class="font-bold text-ink" style="font-size:14px;"><?= htmlspecialchars($selectedContact['name']) ?></p>
        <p class="text-gray-500 break-all mt-1" style="font-size:12.5px;"><?= htmlspecialchars($selectedContact['email']) ?></p>
        <p class="text-gray-400 mt-2" style="font-size:11.5px;">Reçu le <?= date('d/m/Y à H:i', strtotime($selectedContact['created_at'])) ?></p>
        <p class="mt-3 px-2.5 py-1 inline-block rounded-full font-semibold bg-gray-100 text-gray-600" style="font-size:11px;">Formulaire de contact</p>
        <p class="text-gray-400 mt-4" style="font-size:11.5px; line-height:1.6;">Ce message provient du formulaire public, il n'est pas forcément lié à un compte client inscrit.</p>
      <?php else: ?>
        <p class="text-gray-400 text-sm">—</p>
      <?php endif; ?>
    </div>
  </div>
</div>
