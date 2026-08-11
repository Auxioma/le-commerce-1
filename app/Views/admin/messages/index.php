<?php
use App\Core\Csrf;

$segmentLabels = ['fidele' => 'Fidèle', 'nouveau' => 'Nouveau', 'occasionnel' => 'Occasionnel'];
$labelColorClasses = [
    'gray'   => 'bg-gray-100 text-gray-700',
    'amber'  => 'bg-amber-100 text-amber-700',
    'blue'   => 'bg-blue-100 text-blue-700',
    'purple' => 'bg-purple-100 text-purple-700',
    'green'  => 'bg-green-100 text-green-700',
    'red'    => 'bg-red-100 text-red-700',
];
$channelMeta = [
    'email'    => ['label' => 'E-mail',    'color' => '#c8272c'],
    'sms'      => ['label' => 'SMS',       'color' => '#f97316'],
    'whatsapp' => ['label' => 'WhatsApp',  'color' => '#25D366'],
];
$quickReplies = [
    'Réponse rapide' => "Bonjour, merci pour votre message ! Je reviens vers vous très rapidement.",
    'Offre spéciale'  => "Bonjour ! Pour vous remercier de votre fidélité, voici une offre spéciale réservée à nos meilleurs clients 🎁",
    'Carte cadeau'    => "Bonjour ! Nous avons une carte cadeau qui pourrait vous intéresser, souhaitez-vous en savoir plus ?",
    'Inviter un ami'  => "Le saviez-vous ? Parrainez un ami et gagnez 10€ de crédit dès sa première recharge !",
];
?>

<!-- En-tête -->
<div class="flex items-start justify-between gap-4 mb-6 flex-wrap">
  <div>
    <h1 class="font-extrabold text-ink" style="font-size:22px; letter-spacing:-0.5px;">Messages</h1>
    <p class="text-gray-500 mt-1" style="font-size:13px;">Communiquez avec vos clients par e-mail, SMS, WhatsApp et plus encore.</p>
  </div>
  <button type="button" onclick="document.getElementById('new-message-modal').classList.remove('hidden')"
          class="inline-flex items-center gap-2 bg-brand-500 hover:bg-brand-600 text-white font-bold px-5 py-2.5 rounded-lg shadow-sm transition-colors" style="font-size:13px;">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
    Nouveau message
  </button>
</div>

<!-- Cartes KPI -->
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
  <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 flex items-center gap-3">
    <div class="w-10 h-10 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center shrink-0">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4-.8L3 20l1.3-3.9A7.96 7.96 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
    </div>
    <div>
      <p class="text-gray-500 font-semibold" style="font-size:10.5px;">TOUTES BOÎTES</p>
      <p class="font-black text-ink" style="font-size:20px; line-height:1;"><?= number_format($totalContacts + $totalWhatsapp + $totalSms) ?></p>
    </div>
  </div>
  <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 flex items-center gap-3">
    <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0" style="background:#fee2e2; color:#c8272c;">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
    </div>
    <div>
      <p class="text-gray-500 font-semibold" style="font-size:10.5px;">E-MAILS</p>
      <p class="font-black text-ink" style="font-size:20px; line-height:1;"><?= number_format($totalContacts) ?> <span class="text-brand-500 font-semibold" style="font-size:11px;">Non lus : <?= $unreadContacts ?></span></p>
    </div>
  </div>
  <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 flex items-center gap-3">
    <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0" style="background:#ffedd5; color:#f97316;">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4-.8L3 20l1.3-3.9A7.96 7.96 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
    </div>
    <div>
      <p class="text-gray-500 font-semibold" style="font-size:10.5px;">SMS</p>
      <p class="font-black text-ink" style="font-size:20px; line-height:1;"><?= number_format($totalSms) ?> <span class="font-semibold" style="font-size:11px; color:#f97316;">Non lus : <?= $smsIncoming ?></span></p>
    </div>
  </div>
  <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 flex items-center gap-3">
    <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0" style="background:#dcfce7; color:#16a34a;">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.4A10 10 0 1 0 12 2zm0 18a8 8 0 0 1-4.1-1.1l-.3-.2-2.9.8.8-2.8-.2-.3A8 8 0 1 1 12 20z"/></svg>
    </div>
    <div>
      <p class="text-gray-500 font-semibold" style="font-size:10.5px;">WHATSAPP</p>
      <p class="font-black text-ink" style="font-size:20px; line-height:1;"><?= number_format($totalWhatsapp) ?> <span class="text-green-600 font-semibold" style="font-size:11px;">Non lus : <?= $whatsappIncoming ?></span></p>
    </div>
  </div>
  <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 flex items-center gap-3">
    <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0" style="background:#f3e8ff; color:#9333ea;">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
    </div>
    <div>
      <p class="text-gray-500 font-semibold" style="font-size:10.5px;">NOTIFICATIONS</p>
      <p class="font-black text-ink" style="font-size:20px; line-height:1;"><?= number_format($notificationsCount) ?></p>
    </div>
  </div>
  <a href="<?= BASE_PATH ?>/admin/messages?tab=scheduled" class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 flex items-center gap-3 hover:border-blue-300 transition-colors">
    <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0" style="background:#dbeafe; color:#2563eb;">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
    </div>
    <div>
      <p class="text-gray-500 font-semibold" style="font-size:10.5px;">PROGRAMMÉS</p>
      <p class="font-black text-ink" style="font-size:20px; line-height:1;"><?= number_format($scheduledCount) ?> <span class="text-blue-600 font-semibold" style="font-size:11px;">À venir</span></p>
    </div>
  </a>
</div>

<!-- Onglets -->
<?php $tabs = ['inbox' => 'Boîte de réception', 'sent' => 'Envoyés', 'drafts' => 'Brouillons', 'archive' => 'Archives', 'spam' => 'Spam']; ?>
<div class="flex items-center gap-1 border-b border-gray-200 mb-4 overflow-x-auto">
  <?php foreach ($tabs as $key => $tabLabel): ?>
    <a href="<?= BASE_PATH ?>/admin/messages?tab=<?= $key ?>"
       class="px-4 py-2.5 font-semibold border-b-2 whitespace-nowrap transition-colors <?= $tab === $key ? 'border-brand-500 text-brand-500' : 'border-transparent text-gray-500 hover:text-ink' ?>"
       style="font-size:13px;">
      <?= htmlspecialchars($tabLabel) ?>
    </a>
  <?php endforeach; ?>
</div>

<?php if ($tab === 'inbox'): ?>

  <!-- Recherche + filtres -->
  <form method="GET" action="<?= BASE_PATH ?>/admin/messages" class="flex items-center gap-3 mb-4 flex-wrap">
    <input type="hidden" name="tab" value="inbox">
    <div class="relative flex-1 min-w-[220px]">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Rechercher un message..."
             class="w-full border border-gray-200 rounded-lg pl-9 pr-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500" style="font-size:13px;">
    </div>
    <select name="channel" onchange="this.form.submit()" class="border border-gray-200 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500" style="font-size:13px;">
      <option value="tous" <?= $channel === 'tous' ? 'selected' : '' ?>>Tous les canaux</option>
      <option value="email" <?= $channel === 'email' ? 'selected' : '' ?>>E-mail</option>
      <option value="sms" <?= $channel === 'sms' ? 'selected' : '' ?>>SMS</option>
      <option value="whatsapp" <?= $channel === 'whatsapp' ? 'selected' : '' ?>>WhatsApp</option>
    </select>
    <select name="label" onchange="this.form.submit()" class="border border-gray-200 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500" style="font-size:13px;">
      <option value="tous" <?= $label === 'tous' ? 'selected' : '' ?>>Toutes les étiquettes</option>
      <?php foreach ($allLabels as $lbl): ?>
        <option value="<?= htmlspecialchars($lbl) ?>" <?= $label === $lbl ? 'selected' : '' ?>><?= htmlspecialchars($lbl) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="text-gray-500 hover:text-brand-500 font-semibold transition-colors" style="font-size:12.5px;">Filtrer</button>
  </form>

  <!-- Boîte de réception -->
  <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden" style="height:640px;">
    <div class="grid h-full" style="grid-template-columns: 300px 1fr 300px;">

      <!-- Colonne 1 : liste -->
      <div class="border-r border-gray-100 overflow-y-auto">
        <?php if (empty($inbox)): ?>
          <div class="text-center py-16 px-4">
            <p class="text-gray-400 text-sm">Aucun message ne correspond à ces filtres.</p>
          </div>
        <?php endif; ?>
        <?php foreach ($inbox as $item):
          $isSelected = ($item['type'] === 'contact' && $selectedContact && (int) $selectedContact['id'] === $item['id'])
                     || ($item['type'] !== 'contact' && $selectedUser && $selectedChannel === $item['type'] && (int) $selectedUser['id'] === $item['id']);
          $href = $item['type'] === 'contact'
            ? BASE_PATH . '/admin/messages?type=contact&id=' . $item['id']
            : BASE_PATH . '/admin/messages?type=' . $item['type'] . '&user=' . $item['id'];
        ?>
          <a href="<?= $href ?>" class="flex items-start gap-2.5 px-4 py-3 border-b border-gray-50 transition-colors <?= $isSelected ? 'bg-brand-50' : 'hover:bg-gray-50' ?>">
            <span class="w-2 h-2 rounded-full mt-1.5 shrink-0 <?= $item['unread'] ? 'bg-brand-500' : 'bg-transparent' ?>"></span>
            <?php if ($item['type'] === 'whatsapp'): ?>
              <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 mt-1 shrink-0" style="color:#25D366;" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.4A10 10 0 1 0 12 2zm0 18a8 8 0 0 1-4.1-1.1l-.3-.2-2.9.8.8-2.8-.2-.3A8 8 0 1 1 12 20z"/></svg>
            <?php elseif ($item['type'] === 'sms'): ?>
              <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 mt-1 shrink-0" style="color:#f97316;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4-.8L3 20l1.3-3.9A7.96 7.96 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            <?php else: ?>
              <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 mt-1 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            <?php endif; ?>
            <div class="min-w-0 flex-1">
              <div class="flex items-center justify-between gap-2">
                <p class="font-semibold text-ink truncate" style="font-size:12.5px;"><?= htmlspecialchars($item['name']) ?></p>
                <span class="text-gray-400 shrink-0" style="font-size:10.5px;"><?= date('d/m', strtotime($item['time'])) ?></span>
              </div>
              <p class="text-gray-500 truncate" style="font-size:11.5px;"><?= htmlspecialchars($item['preview']) ?></p>
              <?php if (!empty($item['labels'])): ?>
                <div class="flex items-center gap-1 mt-1 flex-wrap">
                  <?php foreach ($item['labels'] as $lbl): ?>
                    <span class="px-1.5 py-0.5 rounded-full font-semibold <?= $labelColorClasses[$lbl['color']] ?? $labelColorClasses['gray'] ?>" style="font-size:9.5px;"><?= htmlspecialchars($lbl['label']) ?></span>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          </a>
        <?php endforeach; ?>
      </div>

      <!-- Colonne 2 : conversation / message -->
      <div class="flex flex-col min-w-0">
        <?php if ($selectedContact): ?>
          <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
              <p class="font-bold text-ink" style="font-size:14px;"><?= htmlspecialchars($selectedContact['name']) ?></p>
              <span class="px-2 py-0.5 rounded-full font-semibold" style="font-size:10px; background:#fee2e2; color:#c8272c;">E-mail</span>
            </div>
            <form method="POST" action="<?= BASE_PATH ?>/admin/messages/contact/<?= $selectedContact['id'] ?>/lu">
              <?= Csrf::field() ?>
              <button type="submit" class="text-xs font-semibold text-gray-400 hover:text-brand-500 transition-colors">
                Marquer <?= $selectedContact['is_read'] ? 'non lu' : 'lu' ?>
              </button>
            </form>
          </div>
          <div class="flex-1 overflow-y-auto p-5">
            <p class="text-gray-400" style="font-size:12px;"><?= htmlspecialchars($selectedContact['email']) ?> · <?= date('d/m/Y H:i', strtotime($selectedContact['created_at'])) ?></p>
            <p class="font-bold text-ink mt-3 mb-3" style="font-size:14px;"><?= htmlspecialchars($selectedContact['subject'] ?: 'Sans objet') ?></p>
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
          <?php
            $isWhats = $selectedChannel === 'whatsapp';
            $accent  = $isWhats ? '#25D366' : '#f97316';
            $sendUrl = BASE_PATH . '/admin/messages/' . $selectedChannel . '/' . $selectedUser['id'];
          ?>
          <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2 flex-wrap">
              <p class="font-bold text-ink" style="font-size:14px;"><?= htmlspecialchars($selectedUser['first_name'] . ' ' . $selectedUser['last_name']) ?></p>
              <span class="px-2 py-0.5 rounded-full font-semibold text-white" style="font-size:10px; background:<?= $accent ?>;"><?= $isWhats ? 'WhatsApp' : 'SMS' ?></span>
              <?php foreach ($clientLabels as $lbl): ?>
                <span class="px-2 py-0.5 rounded-full font-semibold <?= $labelColorClasses[$lbl['color']] ?? $labelColorClasses['gray'] ?>" style="font-size:10px;"><?= htmlspecialchars($lbl['label']) ?></span>
              <?php endforeach; ?>
            </div>
            <a href="<?= BASE_PATH ?>/admin/clients/<?= $selectedUser['id'] ?>" class="text-xs font-semibold text-brand-500 hover:text-brand-600 shrink-0">Voir la fiche</a>
          </div>
          <div class="flex-1 overflow-y-auto p-5 space-y-3" style="background:#f7f7f6;">
            <?php if (empty($selectedThread)): ?>
              <p class="text-gray-400 text-sm text-center py-8">Aucun message échangé pour l'instant.</p>
            <?php endif; ?>
            <?php foreach ($selectedThread as $msg): $out = $msg['direction'] === 'sortant'; ?>
              <div class="flex <?= $out ? 'justify-end' : 'justify-start' ?>">
                <div class="max-w-[75%] rounded-2xl px-4 py-2.5 <?= $out ? 'text-white' : 'bg-white border border-gray-100' ?>" style="<?= $out ? 'background:' . $accent . ';' : '' ?>">
                  <p class="whitespace-pre-line" style="font-size:13px;"><?= htmlspecialchars($msg['content']) ?></p>
                  <p class="mt-1 <?= $out ? 'text-white/70' : 'text-gray-400' ?>" style="font-size:10.5px;"><?= date('d/m H:i', strtotime($msg['sent_at'])) ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- Réponses rapides -->
          <div class="px-4 pt-3 flex items-center gap-2 flex-wrap border-t border-gray-100">
            <?php foreach ($quickReplies as $qLabel => $qText): ?>
              <button type="button" onclick='document.getElementById("composer-<?= $selectedChannel ?>-<?= $selectedUser['id'] ?>").value = <?= json_encode($qText, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS) ?>; document.getElementById("composer-<?= $selectedChannel ?>-<?= $selectedUser['id'] ?>").focus();'
                      class="px-3 py-1.5 rounded-full border border-gray-200 text-gray-600 font-semibold hover:border-brand-300 hover:text-brand-500 transition-colors" style="font-size:11.5px;">
                <?= htmlspecialchars($qLabel) ?>
              </button>
            <?php endforeach; ?>
          </div>

          <form method="POST" action="<?= $sendUrl ?>" class="px-4 py-3 flex items-center gap-2">
            <?= Csrf::field() ?>
            <input type="hidden" name="back_channel" value="<?= $selectedChannel ?>">
            <input type="text" id="composer-<?= $selectedChannel ?>-<?= $selectedUser['id'] ?>" name="content" required placeholder="Écrire un message..."
                   class="flex-1 min-w-0 border border-gray-200 rounded-full px-4 py-2.5 focus:outline-none focus:ring-2" style="font-size:13px;">
            <label class="flex items-center gap-1.5 text-gray-500 shrink-0" style="font-size:11px;" title="Programmer l'envoi">
              <input type="datetime-local" name="scheduled_at" class="border border-gray-200 rounded-lg px-2 py-2 hidden sm:block" style="font-size:11px;">
            </label>
            <button type="submit" class="shrink-0 w-10 h-10 rounded-full flex items-center justify-center text-white transition-opacity hover:opacity-90" style="background:<?= $accent ?>;">
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
              <?= $segmentLabels[$selectedUser['segment']] ?? htmlspecialchars($selectedUser['segment']) ?>
            </span>
          </p>
          <?php if ($selectedWallet): ?>
            <p class="mt-3 text-gray-500" style="font-size:12px;">Portefeuille : <span class="font-bold text-ink"><?= number_format((float) $selectedWallet['balance'], 2, ',', ' ') ?> €</span></p>
          <?php endif; ?>

          <!-- Actions rapides -->
          <p class="text-gray-400 uppercase font-bold mt-5 mb-2" style="font-size:10.5px; letter-spacing:0.5px;">Actions rapides</p>
          <div class="space-y-1.5">
            <a href="mailto:<?= htmlspecialchars($selectedUser['email'] ?? '') ?>" class="flex items-center gap-2 text-gray-600 hover:text-brand-500 transition-colors" style="font-size:12.5px;">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
              Envoyer un e-mail
            </a>
            <a href="<?= BASE_PATH ?>/admin/messages?type=sms&user=<?= $selectedUser['id'] ?>" class="flex items-center gap-2 text-gray-600 hover:text-brand-500 transition-colors" style="font-size:12.5px;">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4-.8L3 20l1.3-3.9A7.96 7.96 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
              Envoyer un SMS
            </a>
            <a href="<?= BASE_PATH ?>/admin/messages?type=whatsapp&user=<?= $selectedUser['id'] ?>" class="flex items-center gap-2 text-gray-600 hover:text-brand-500 transition-colors" style="font-size:12.5px;">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.4A10 10 0 1 0 12 2zm0 18a8 8 0 0 1-4.1-1.1l-.3-.2-2.9.8.8-2.8-.2-.3A8 8 0 1 1 12 20z"/></svg>
              Envoyer un WhatsApp
            </a>
            <a href="<?= BASE_PATH ?>/admin/offres/creer" class="flex items-center gap-2 text-gray-600 hover:text-brand-500 transition-colors" style="font-size:12.5px;">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
              Créer une offre personnalisée
            </a>
            <button type="button" onclick="document.getElementById('note-form-<?= $selectedUser['id'] ?>').classList.toggle('hidden')" class="flex items-center gap-2 text-gray-600 hover:text-brand-500 transition-colors" style="font-size:12.5px;">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
              Ajouter une note
            </button>
          </div>

          <form id="note-form-<?= $selectedUser['id'] ?>" method="POST" action="<?= BASE_PATH ?>/admin/messages/clients/<?= $selectedUser['id'] ?>/notes" class="hidden mt-2 space-y-2">
            <?= Csrf::field() ?>
            <input type="hidden" name="back_channel" value="<?= $selectedChannel ?>">
            <textarea name="content" rows="2" placeholder="Écrire une note interne..." class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500" style="font-size:12.5px;"></textarea>
            <button type="submit" class="w-full text-white font-bold py-2 rounded-lg" style="background:#c8272c; font-size:12px;">Enregistrer la note</button>
          </form>

          <?php if (!empty($clientNotes)): ?>
            <div class="mt-3 space-y-2">
              <?php foreach (array_slice($clientNotes, 0, 3) as $note): ?>
                <div class="bg-amber-50 border border-amber-100 rounded-lg px-3 py-2">
                  <p class="text-gray-700" style="font-size:12px;"><?= htmlspecialchars($note['content']) ?></p>
                  <p class="text-amber-600 mt-1" style="font-size:10px;"><?= date('d/m/Y H:i', strtotime($note['created_at'])) ?></p>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <!-- Statistiques d'engagement -->
          <?php if ($engagement): ?>
            <p class="text-gray-400 uppercase font-bold mt-5 mb-2" style="font-size:10.5px; letter-spacing:0.5px;">Statistiques d'engagement</p>
            <div class="grid grid-cols-2 gap-2">
              <div class="bg-gray-50 rounded-lg px-3 py-2">
                <p class="text-gray-400" style="font-size:10px;">Messages échangés</p>
                <p class="font-bold text-ink" style="font-size:14px;"><?= $engagement['total'] ?></p>
              </div>
              <div class="bg-gray-50 rounded-lg px-3 py-2">
                <p class="text-gray-400" style="font-size:10px;">Taux de réponse</p>
                <p class="font-bold text-ink" style="font-size:14px;"><?= $engagement['responseRate'] ?>%</p>
              </div>
              <div class="bg-gray-50 rounded-lg px-3 py-2">
                <p class="text-gray-400" style="font-size:10px;">Dernier contact</p>
                <p class="font-bold text-ink" style="font-size:12px;"><?= $engagement['lastContact'] ? date('d/m/Y', strtotime($engagement['lastContact'])) : '—' ?></p>
              </div>
              <div class="bg-gray-50 rounded-lg px-3 py-2">
                <p class="text-gray-400" style="font-size:10px;">Canal préféré</p>
                <p class="font-bold text-ink" style="font-size:12px;"><?= $engagement['preferredChannel'] ? ucfirst($engagement['preferredChannel']) : '—' ?></p>
              </div>
            </div>
          <?php endif; ?>

          <!-- Étiquettes -->
          <div class="flex items-center justify-between mt-5 mb-2">
            <p class="text-gray-400 uppercase font-bold" style="font-size:10.5px; letter-spacing:0.5px;">Étiquettes</p>
            <button type="button" onclick="document.getElementById('label-form-<?= $selectedUser['id'] ?>').classList.toggle('hidden')" class="text-brand-500 font-semibold hover:text-brand-600" style="font-size:11px;">Gérer</button>
          </div>
          <div class="flex items-center gap-1.5 flex-wrap">
            <?php foreach ($clientLabels as $lbl): ?>
              <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full font-semibold <?= $labelColorClasses[$lbl['color']] ?? $labelColorClasses['gray'] ?>" style="font-size:11px;">
                <?= htmlspecialchars($lbl['label']) ?>
                <form method="POST" action="<?= BASE_PATH ?>/admin/messages/etiquettes/<?= $lbl['id'] ?>/supprimer" class="inline">
                  <?= Csrf::field() ?>
                  <input type="hidden" name="user_id" value="<?= $selectedUser['id'] ?>">
                  <input type="hidden" name="back_channel" value="<?= $selectedChannel ?>">
                  <button type="submit" class="opacity-60 hover:opacity-100">&times;</button>
                </form>
              </span>
            <?php endforeach; ?>
            <?php if (empty($clientLabels)): ?>
              <p class="text-gray-400" style="font-size:11.5px;">Aucune étiquette.</p>
            <?php endif; ?>
          </div>
          <form id="label-form-<?= $selectedUser['id'] ?>" method="POST" action="<?= BASE_PATH ?>/admin/messages/clients/<?= $selectedUser['id'] ?>/etiquettes" class="hidden mt-2 flex items-center gap-2">
            <?= Csrf::field() ?>
            <input type="hidden" name="back_channel" value="<?= $selectedChannel ?>">
            <input type="text" name="label" required placeholder="Nouvelle étiquette" class="flex-1 min-w-0 border border-gray-200 rounded-lg px-2.5 py-1.5" style="font-size:11.5px;">
            <select name="color" class="border border-gray-200 rounded-lg px-2 py-1.5" style="font-size:11.5px;">
              <?php foreach (\App\Models\ClientLabel::COLORS as $c): ?>
                <option value="<?= $c ?>"><?= ucfirst($c) ?></option>
              <?php endforeach; ?>
            </select>
            <button type="submit" class="text-white font-bold px-3 py-1.5 rounded-lg" style="background:#c8272c; font-size:11px;">+</button>
          </form>

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

<?php elseif ($tab === 'sent'): ?>

  <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
    <?php if (empty($sentMessages)): ?>
      <div class="text-center py-16 px-4"><p class="text-gray-400 text-sm">Aucun message envoyé pour le moment.</p></div>
    <?php else: ?>
      <table class="min-w-full">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr>
            <th class="px-5 py-2.5 text-left font-bold text-gray-500 uppercase" style="font-size:10.5px;">Destinataire</th>
            <th class="px-5 py-2.5 text-left font-bold text-gray-500 uppercase" style="font-size:10.5px;">Canal</th>
            <th class="px-5 py-2.5 text-left font-bold text-gray-500 uppercase" style="font-size:10.5px;">Message</th>
            <th class="px-5 py-2.5 text-right font-bold text-gray-500 uppercase" style="font-size:10.5px;">Envoyé le</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <?php foreach ($sentMessages as $m): $meta = $channelMeta[$m['channel']]; ?>
            <tr class="hover:bg-gray-50 transition-colors">
              <td class="px-5 py-3">
                <a href="<?= BASE_PATH ?>/admin/messages?type=<?= $m['channel'] ?>&user=<?= $m['userId'] ?>" class="font-semibold text-ink hover:text-brand-500" style="font-size:13px;"><?= htmlspecialchars($m['name']) ?></a>
              </td>
              <td class="px-5 py-3"><span class="px-2 py-0.5 rounded-full font-semibold text-white" style="font-size:10.5px; background:<?= $meta['color'] ?>;"><?= $meta['label'] ?></span></td>
              <td class="px-5 py-3 text-gray-600 truncate" style="font-size:12.5px; max-width:420px;"><?= htmlspecialchars($m['content']) ?></td>
              <td class="px-5 py-3 text-right text-gray-500" style="font-size:12px;"><?= date('d/m/Y H:i', strtotime($m['time'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

<?php elseif ($tab === 'scheduled'): ?>

  <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
    <?php if (empty($scheduledMessages)): ?>
      <div class="text-center py-16 px-4"><p class="text-gray-400 text-sm">Aucun message programmé.</p></div>
    <?php else: ?>
      <table class="min-w-full">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr>
            <th class="px-5 py-2.5 text-left font-bold text-gray-500 uppercase" style="font-size:10.5px;">Destinataire</th>
            <th class="px-5 py-2.5 text-left font-bold text-gray-500 uppercase" style="font-size:10.5px;">Canal</th>
            <th class="px-5 py-2.5 text-left font-bold text-gray-500 uppercase" style="font-size:10.5px;">Message</th>
            <th class="px-5 py-2.5 text-left font-bold text-gray-500 uppercase" style="font-size:10.5px;">Programmé pour</th>
            <th class="px-5 py-2.5"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <?php foreach ($scheduledMessages as $m): $meta = $channelMeta[$m['channel']]; ?>
            <tr class="hover:bg-gray-50 transition-colors">
              <td class="px-5 py-3 font-semibold text-ink" style="font-size:13px;"><?= htmlspecialchars($m['first_name'] . ' ' . $m['last_name']) ?></td>
              <td class="px-5 py-3"><span class="px-2 py-0.5 rounded-full font-semibold text-white" style="font-size:10.5px; background:<?= $meta['color'] ?>;"><?= $meta['label'] ?></span></td>
              <td class="px-5 py-3 text-gray-600 truncate" style="font-size:12.5px; max-width:380px;"><?= htmlspecialchars($m['content']) ?></td>
              <td class="px-5 py-3 text-gray-500" style="font-size:12px;"><?= date('d/m/Y H:i', strtotime($m['scheduled_at'])) ?></td>
              <td class="px-5 py-3 text-right">
                <form method="POST" action="<?= BASE_PATH ?>/admin/messages/programmes/<?= $m['id'] ?>/annuler">
                  <?= Csrf::field() ?>
                  <button type="submit" class="text-xs font-semibold text-red-500 hover:text-red-700">Annuler</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

<?php else: ?>

  <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-16 text-center">
    <p class="text-gray-400 text-sm">Cette section n'est pas encore disponible dans cette version.</p>
  </div>

<?php endif; ?>

<!-- Modale "Nouveau message" -->
<div id="new-message-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
  <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-bold text-ink" style="font-size:16px;">Nouveau message</h2>
      <button type="button" onclick="document.getElementById('new-message-modal').classList.add('hidden')" class="text-gray-400 hover:text-ink">&times;</button>
    </div>
    <form method="POST" action="<?= BASE_PATH ?>/admin/messages/nouveau" class="space-y-3">
      <?= Csrf::field() ?>
      <div>
        <label class="block text-gray-500 font-semibold mb-1" style="font-size:12px;">Client</label>
        <select name="user_id" required class="w-full border border-gray-200 rounded-lg px-3 py-2.5" style="font-size:13px;">
          <option value="">Choisir un client...</option>
          <?php foreach ($allClients as $c): ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name'] . ' — ' . $c['phone_whatsapp']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block text-gray-500 font-semibold mb-1" style="font-size:12px;">Canal</label>
        <select name="channel" class="w-full border border-gray-200 rounded-lg px-3 py-2.5" style="font-size:13px;">
          <option value="whatsapp">WhatsApp</option>
          <option value="sms">SMS</option>
        </select>
      </div>
      <div>
        <label class="block text-gray-500 font-semibold mb-1" style="font-size:12px;">Message</label>
        <textarea name="content" rows="3" required placeholder="Votre message..." class="w-full border border-gray-200 rounded-lg px-3 py-2.5" style="font-size:13px;"></textarea>
      </div>
      <button type="submit" class="w-full text-white font-bold py-2.5 rounded-lg" style="background:#c8272c; font-size:13px;">Envoyer</button>
    </form>
  </div>
</div>
