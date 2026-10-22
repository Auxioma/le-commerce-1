// =====================================================
// Le Commerce — JS front (vanilla, sans dépendance)
// =====================================================

document.addEventListener('DOMContentLoaded', () => {
  initMobileMenu();
  initAdminMobileSidebar();
  initAdminSidebarCollapse();
  initAdminSidebarAccordion();
  initLotteryAnnounce();
  initPollAnnounce();
  initInboxDelete();
  initWeatherWidget();
  initChatChips();
  initChatFab();
  initProximityWidget();
  initContactForm();
  initCookieConsent();
  initEventTracking();
  initScrollReveal();
  initImagePreview();
  initGalleryCarousel();
  initGalleryPage();
});

/**
 * Aperçu d'une image avant envoi : chaque `input[type=file][data-image-preview]`
 * met à jour l'`img[data-image-preview-target]` de son conteneur parent. Si la
 * sélection est annulée, on revient à l'image d'origine (ou on masque l'aperçu).
 */
function initImagePreview() {
  document.querySelectorAll('input[type="file"][data-image-preview]').forEach((input) => {
    const img = input.parentElement.querySelector('[data-image-preview-target]');
    if (!img) return;
    let objectUrl = null;

    input.addEventListener('change', () => {
      if (objectUrl) URL.revokeObjectURL(objectUrl);
      objectUrl = null;

      const file = input.files && input.files[0];
      if (file && file.type.startsWith('image/')) {
        objectUrl = URL.createObjectURL(file);
        img.src = objectUrl;
        img.classList.remove('hidden');
        return;
      }

      const original = img.dataset.originalSrc;
      if (original) {
        img.src = original;
      } else {
        img.removeAttribute('src');
        img.classList.add('hidden');
      }
    });
  });
}

/** Fait apparaître en fondu les éléments `.reveal` un à un lorsqu'ils entrent dans le viewport. */
function initScrollReveal() {
  const items = document.querySelectorAll('.reveal');
  if (!items.length) return;

  if (!('IntersectionObserver' in window)) {
    items.forEach((el) => el.classList.add('is-visible'));
    return;
  }

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });

  items.forEach((el) => observer.observe(el));
}

/**
 * Géolocalisation client : demande la position toutes les 60s et interroge
 * le serveur pour savoir si une campagne de proximité est déclenchée.
 * Ne s'active que si le widget est présent dans la page (client, opt-in activé).
 */
function initProximityWidget() {
  const banner = document.getElementById('proximity-banner');
  if (!banner || !navigator.geolocation || !window.__proximityConfig) return;

  const messageEl = document.getElementById('proximity-message');
  const form = document.getElementById('proximity-claim-form');
  const dismissBtn = document.getElementById('proximity-dismiss');
  let dismissed = false;

  dismissBtn.addEventListener('click', () => {
    dismissed = true;
    banner.classList.add('hidden');
  });

  const checkPosition = (position) => {
    if (dismissed) return;
    const { latitude, longitude } = position.coords;

    const body = new URLSearchParams();
    body.set('_csrf', window.__proximityConfig.csrfToken);
    body.set('lat', latitude);
    body.set('lng', longitude);

    fetch(window.location.origin + '/mon-compte/proximite/verifier', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: body.toString(),
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.match) {
          messageEl.textContent = data.campaign.message;
          form.action = window.location.origin + '/mon-compte/proximite/' + data.campaign.id + '/profiter';
          banner.classList.remove('hidden');
        }
      })
      .catch(() => {});
  };

  navigator.geolocation.getCurrentPosition(checkPosition, () => {}, { enableHighAccuracy: false, timeout: 8000 });
  setInterval(() => {
    navigator.geolocation.getCurrentPosition(checkPosition, () => {}, { enableHighAccuracy: false, timeout: 8000 });
  }, 60000);
}

function initAdminSidebarCollapse() {
  const btn = document.getElementById('admin-collapse-menu-btn');
  const sidebar = document.getElementById('admin-sidebar');
  if (!btn || !sidebar) return;

  const storageKey = 'le-commerce-admin-sidebar-collapsed';

  const applyState = (collapsed) => {
    if (collapsed) {
      sidebar.classList.add('sidebar-collapsed');
      sidebar.style.width = '72px';
      sidebar.style.minWidth = '72px';
      btn.setAttribute('aria-pressed', 'true');
    } else {
      sidebar.classList.remove('sidebar-collapsed');
      sidebar.style.width = '';
      sidebar.style.minWidth = '';
      btn.setAttribute('aria-pressed', 'false');
    }
  };

  const stored = localStorage.getItem(storageKey);
  applyState(stored === '1');

  btn.addEventListener('click', () => {
    const collapsed = !sidebar.classList.contains('sidebar-collapsed');
    applyState(collapsed);
    localStorage.setItem(storageKey, collapsed ? '1' : '0');
  });
}

/** Groupes de la sidebar admin en accordéon : ouvrir un groupe referme celui déjà ouvert. */
function initAdminSidebarAccordion() {
  const groups = [...document.querySelectorAll('#admin-sidebar details.sidebar-group')];
  groups.forEach((group) => {
    // Au clic sur le titre d'un groupe fermé (donc sur le point de s'ouvrir),
    // on referme les autres tout de suite.
    group.querySelector(':scope > summary')?.addEventListener('click', () => {
      if (group.open) return;
      groups.forEach((other) => {
        if (other !== group) other.open = false;
      });
    });
  });
}

/** Sidebar admin en drawer mobile */
function initAdminMobileSidebar() {
  const btn = document.getElementById('admin-mobile-menu-btn');
  const sidebar = document.getElementById('admin-sidebar');
  const backdrop = document.getElementById('admin-sidebar-backdrop');
  if (!btn || !sidebar || !backdrop) return;

  const open = () => {
    sidebar.classList.remove('-translate-x-full');
    backdrop.classList.remove('hidden');
  };
  const close = () => {
    sidebar.classList.add('-translate-x-full');
    backdrop.classList.add('hidden');
  };

  btn.addEventListener('click', open);
  backdrop.addEventListener('click', close);
}

/** Menu mobile (burger) */
function initMobileMenu() {
  const btn = document.getElementById('mobile-menu-btn');
  const menu = document.getElementById('mobile-menu');
  if (!btn || !menu) return;

  btn.addEventListener('click', () => {
    menu.classList.toggle('hidden');
  });
}

/** Récupère la météo réelle via Open-Meteo (API publique, sans clé) */
function initWeatherWidget() {
  const widget = document.getElementById('weather-widget');
  if (!widget) return;

  const lat = widget.dataset.lat;
  const lng = widget.dataset.lng;
  const tempEl = widget.querySelector('#weather-temp');
  const descEl = widget.querySelector('#weather-desc');

  fetch(`https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lng}&current_weather=true`)
    .then((res) => res.json())
    .then((data) => {
      if (!data.current_weather) return;
      const temp = Math.round(data.current_weather.temperature);
      tempEl.textContent = `${temp}°C`;
      descEl.textContent = weatherCodeToLabel(data.current_weather.weathercode);
    })
    .catch(() => {
      descEl.textContent = 'Météo indisponible';
    });
}

function weatherCodeToLabel(code) {
  const map = {
    0: 'Ensoleillé', 1: 'Plutôt dégagé', 2: 'Partiellement nuageux', 3: 'Couvert',
    45: 'Brouillard', 48: 'Brouillard givrant',
    51: 'Bruine légère', 61: 'Pluie légère', 63: 'Pluie', 65: 'Forte pluie',
    71: 'Neige légère', 73: 'Neige', 75: 'Forte neige',
    80: 'Averses', 95: 'Orage',
  };
  return map[code] || 'Conditions variables';
}

/** Formulaire de contact : envoi en AJAX vers /contact, sans recharger la page */
function initContactForm() {
  const form = document.getElementById('contact-form');
  const feedback = document.getElementById('contact-feedback');
  const submitBtn = document.getElementById('contact-submit');
  if (!form || !feedback) return;

  const showFeedback = (message, isError) => {
    feedback.textContent = message;
    feedback.classList.remove('hidden', 'bg-emerald-50', 'text-emerald-700', 'bg-red-50', 'text-red-700');
    feedback.classList.add(isError ? 'bg-red-50' : 'bg-emerald-50', isError ? 'text-red-700' : 'text-emerald-700');
  };

  // Anti-spam : le jeton n'est renvoyé (inversé) qu'après une vraie interaction humaine.
  const jsCheck = form.querySelector('input[name="js_check"]');
  const armJsCheck = () => {
    if (jsCheck) jsCheck.value = (form.dataset.challenge || '').split('').reverse().join('');
  };
  ['keydown', 'pointerdown', 'touchstart'].forEach((evt) => form.addEventListener(evt, armJsCheck, { once: true, passive: true }));

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    submitBtn.disabled = true;
    submitBtn.textContent = 'Envoi en cours...';

    fetch(form.action, { method: 'POST', body: new FormData(form) })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          showFeedback(data.message || 'Votre message a bien été envoyé.', false);
          trackEvent('contact_form_submit', { page: location.pathname });
          form.reset();
        } else {
          showFeedback(data.error || 'Une erreur est survenue, merci de réessayer.', true);
        }
      })
      .catch(() => showFeedback('Une erreur est survenue, merci de réessayer.', true))
      .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Envoyer le message';
      });
  });
}

/** Clique sur une suggestion de l'assistant -> remplit le champ */
function initChatChips() {
  document.querySelectorAll('.chat-chip').forEach((chip) => {
    chip.addEventListener('click', () => {
      const input = document.querySelector('#assistant-form input');
      if (input) {
        input.value = chip.textContent.trim();
        input.focus();
      }
    });
  });
}

/** Bouton flottant de l'assistant (scroll vers le bloc assistant en Accueil, sinon no-op) */
function initChatFab() {
  const fab = document.getElementById('chat-fab');
  if (!fab) return;

  fab.addEventListener('click', () => {
    const target = document.getElementById('assistant-form');
    if (target) {
      target.scrollIntoView({ behavior: 'smooth', block: 'center' });
      target.querySelector('input')?.focus();
    }
  });
}

/**
 * Bandeau cookies. Deux modes selon si Google Analytics est configuré :
 * - Analytics désactivé : simple mention à acquitter (aucun cookie de mesure d'audience).
 * - Analytics activé : vrai choix Accepter/Refuser, gtag.js n'est chargé qu'après acceptation.
 */
function initCookieConsent() {
  const banner = document.getElementById('cookie-consent-banner');
  if (!banner) return;

  // Le bouton flottant de l'assistant (fixed bottom-6) chevauche sinon la
  // bannière : on le remonte tant qu'elle est affichée.
  const fab = document.getElementById('chat-fab');
  const dismiss = () => {
    banner.remove();
    fab?.style.removeProperty('bottom');
  };
  if (fab) {
    fab.style.bottom = (banner.getBoundingClientRect().height + 24) + 'px';
  }

  const gaEnabled = banner.dataset.gaEnabled === '1';
  const gaId = banner.dataset.gaId;

  if (!gaEnabled) {
    const STORAGE_KEY = 'lc_cookie_banner_dismissed';
    if (localStorage.getItem(STORAGE_KEY) === '1') {
      dismiss();
      return;
    }
    document.getElementById('cookie-consent-dismiss')?.addEventListener('click', () => {
      localStorage.setItem(STORAGE_KEY, '1');
      dismiss();
    });
    return;
  }

  const CONSENT_KEY = 'lc_cookie_consent';
  const consent = localStorage.getItem(CONSENT_KEY);

  if (consent === 'accepted') {
    loadGoogleAnalytics(gaId);
    dismiss();
    return;
  }
  if (consent === 'declined') {
    dismiss();
    return;
  }

  document.getElementById('cookie-consent-accept')?.addEventListener('click', () => {
    localStorage.setItem(CONSENT_KEY, 'accepted');
    loadGoogleAnalytics(gaId);
    dismiss();
  });
  document.getElementById('cookie-consent-decline')?.addEventListener('click', () => {
    localStorage.setItem(CONSENT_KEY, 'declined');
    dismiss();
  });
}

/** Injecte gtag.js et l'active — appelé uniquement après consentement explicite (jamais au chargement initial). */
function loadGoogleAnalytics(measurementId) {
  if (!measurementId || window.dataLayer) return;

  window.dataLayer = window.dataLayer || [];
  window.gtag = function () { window.dataLayer.push(arguments); };
  window.gtag('js', new Date());
  window.gtag('config', measurementId);

  const script = document.createElement('script');
  script.async = true;
  script.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(measurementId);
  document.head.appendChild(script);
}

/** Envoie un événement gtag si Analytics est chargé et consenti ; ne fait rien sinon. */
function trackEvent(name, params = {}) {
  if (typeof window.gtag === 'function') {
    window.gtag('event', name, params);
  }
}

/** Suivi des interactions clés : clic WhatsApp, clic téléphone (le contact form est suivi dans initContactForm). */
function initEventTracking() {
  document.querySelectorAll('a[href^="https://wa.me/"]').forEach((link) => {
    link.addEventListener('click', () => trackEvent('click_whatsapp', { page: location.pathname }));
  });
  document.querySelectorAll('a[href^="tel:"]').forEach((link) => {
    link.addEventListener('click', () => trackEvent('click_phone', { page: location.pathname }));
  });
}

/**
 * Filtre une galerie : active le bouton `[data-filter]` choisi et masque les
 * `[data-category]` qui n'appartiennent pas à la catégorie ("all" = tout).
 * Renvoie le nombre de photos restées visibles.
 */
function applyGalleryFilter(root, filter) {
  root.querySelectorAll('[data-filter]').forEach((btn) => {
    const active = btn.dataset.filter === filter;
    btn.classList.toggle('is-active', active);
    btn.setAttribute('aria-selected', active ? 'true' : 'false');
  });
  let visible = 0;
  root.querySelectorAll('[data-category]').forEach((item) => {
    item.hidden = filter !== 'all' && item.dataset.category !== filter;
    if (!item.hidden) visible++;
  });
  const empty = root.querySelector('[data-gallery-empty]');
  if (empty) empty.hidden = visible > 0;
  return visible;
}

/** Carrousel « Le Commerce en images » de l'accueil : flèches, pastilles et filtres. */
function initGalleryCarousel() {
  document.querySelectorAll('[data-gallery]').forEach((root) => {
    const track = root.querySelector('[data-gallery-track]');
    if (!track) return;
    const prev = root.querySelector('[data-gallery-prev]');
    const next = root.querySelector('[data-gallery-next]');
    const dots = root.querySelector('[data-gallery-dots]');
    const gap = () => parseFloat(getComputedStyle(track).columnGap) || 0;
    const pageWidth = () => track.clientWidth + gap();
    const pageCount = () => Math.max(1, Math.ceil((track.scrollWidth + gap()) / pageWidth() - 0.01));
    const goTo = (page) => track.scrollTo({ left: page * pageWidth() });

    const update = () => {
      const maxScroll = track.scrollWidth - track.clientWidth;
      const current = track.scrollLeft >= maxScroll - 1 ? pageCount() - 1 : Math.round(track.scrollLeft / pageWidth());
      if (prev) prev.disabled = track.scrollLeft <= 1;
      if (next) next.disabled = track.scrollLeft >= maxScroll - 1;
      dots?.querySelectorAll('.gallery-dot').forEach((dot, i) => dot.classList.toggle('is-active', i === current));
    };

    const buildDots = () => {
      if (!dots) return;
      const count = pageCount();
      dots.innerHTML = '';
      if (count > 1) {
        for (let i = 0; i < count; i++) {
          const dot = document.createElement('button');
          dot.type = 'button';
          dot.className = 'gallery-dot';
          dot.setAttribute('aria-label', `Page ${i + 1}`);
          dot.addEventListener('click', () => goTo(i));
          dots.appendChild(dot);
        }
      }
      update();
    };

    prev?.addEventListener('click', () => track.scrollBy({ left: -pageWidth() }));
    next?.addEventListener('click', () => track.scrollBy({ left: pageWidth() }));

    let ticking = false;
    track.addEventListener('scroll', () => {
      if (ticking) return;
      ticking = true;
      requestAnimationFrame(() => { update(); ticking = false; });
    }, { passive: true });

    root.querySelectorAll('[data-filter]').forEach((btn) => {
      btn.addEventListener('click', () => {
        applyGalleryFilter(root, btn.dataset.filter);
        track.scrollTo({ left: 0, behavior: 'instant' });
        buildDots();
      });
    });

    window.addEventListener('resize', buildDots);
    buildDots();
  });
}

/**
 * Page /galerie, dynamique (pas de rechargement) :
 * - un clic sur une catégorie n'affiche que les images de cette catégorie (#categorie=slug) ;
 * - #album=slug (lien depuis l'accueil) fait défiler jusqu'à la galerie concernée ;
 * - un clic sur une photo l'ouvre en plein écran (flèches clavier, Échap).
 */
function initGalleryPage() {
  const root = document.querySelector('[data-gallery-page]');
  if (!root) return;

  const setFilter = (filter) => {
    if (!root.querySelector(`[data-filter="${CSS.escape(filter)}"]`)) filter = 'all';
    applyGalleryFilter(root, filter);
  };

  const route = () => {
    const match = location.hash.slice(1).match(/^(categorie|album)=(.+)$/);
    const value = match ? decodeURIComponent(match[2]) : null;
    if (match && match[1] === 'album') {
      setFilter('all');
      document.getElementById(`album-${value}`)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
      setFilter(match ? value : 'all');
    }
  };

  root.querySelectorAll('[data-filter]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const filter = btn.dataset.filter;
      history.replaceState(null, '', filter === 'all' ? location.pathname + location.search : `#categorie=${filter}`);
      setFilter(filter);
    });
  });
  window.addEventListener('hashchange', route);
  route();

  const dialog = document.getElementById('gallery-lightbox');
  if (!dialog || typeof dialog.showModal !== 'function') return;
  const img = dialog.querySelector('[data-lightbox-img]');
  const caption = dialog.querySelector('[data-lightbox-caption]');
  let index = 0;
  // Photos actuellement visibles (celles de la catégorie choisie), dans l'ordre de la page.
  const photos = () => [...root.querySelectorAll('[data-photo]')].filter((p) => !p.closest('[hidden]'));

  const show = (i) => {
    const items = photos();
    if (!items.length) return;
    index = (i + items.length) % items.length;
    const item = items[index];
    img.src = item.dataset.full;
    img.alt = item.dataset.title;
    caption.textContent = `${item.dataset.title} — ${item.dataset.date}`;
  };

  root.querySelectorAll('[data-photo]').forEach((item) => {
    item.addEventListener('click', (e) => {
      e.preventDefault();
      show(photos().indexOf(item));
      dialog.showModal();
    });
  });
  dialog.querySelector('[data-lightbox-prev]')?.addEventListener('click', () => show(index - 1));
  dialog.querySelector('[data-lightbox-next]')?.addEventListener('click', () => show(index + 1));
  dialog.querySelector('[data-lightbox-close]')?.addEventListener('click', () => dialog.close());
  dialog.addEventListener('click', (e) => { if (e.target === dialog) dialog.close(); });
  dialog.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowLeft') show(index - 1);
    if (e.key === 'ArrowRight') show(index + 1);
  });
}

/**
 * Messagerie admin : suppression sans rechargement d'une ou plusieurs
 * conversations (cases à cocher de la liste) ou d'un seul message d'un fil.
 * Si la conversation affichée est supprimée, on revient à la boîte de réception.
 */
function initInboxDelete() {
  const root = document.querySelector('[data-inbox]');
  if (!root) return;

  const checkAll = root.querySelector('[data-inbox-check-all]');
  const deleteBtn = root.querySelector('[data-inbox-delete]');
  const count = root.querySelector('[data-inbox-count]');
  const checks = () => [...root.querySelectorAll('[data-inbox-check]')];

  const refresh = () => {
    const all = checks();
    const checked = all.filter((c) => c.checked);
    if (deleteBtn) deleteBtn.disabled = checked.length === 0;
    if (count) count.textContent = checked.length ? `(${checked.length})` : '';
    if (checkAll) {
      checkAll.checked = all.length > 0 && checked.length === all.length;
      checkAll.indeterminate = checked.length > 0 && checked.length < all.length;
    }
    root.querySelector('[data-inbox-empty]').hidden = all.length > 0;
  };

  const remove = async (items, confirmText) => {
    if (items.length === 0 || !window.confirm(confirmText)) return false;
    const body = new FormData();
    body.append('_csrf', root.dataset.csrf);
    items.forEach((item) => body.append('items[]', item));
    try {
      const res = await fetch(root.dataset.deleteUrl, { method: 'POST', body, headers: { Accept: 'application/json' } });
      if (!res.ok) throw new Error(res.status);
      await res.json();
    } catch (e) {
      window.alert('La suppression a échoué. Merci de recharger la page et réessayer.');
      return false;
    }
    return true;
  };

  checkAll?.addEventListener('change', () => {
    checks().forEach((c) => { c.checked = checkAll.checked; });
    refresh();
  });
  root.addEventListener('change', (e) => {
    if (e.target.matches('[data-inbox-check]')) refresh();
  });

  deleteBtn?.addEventListener('click', async () => {
    const items = checks().filter((c) => c.checked).map((c) => c.value);
    const label = items.length > 1 ? `ces ${items.length} conversations` : 'cette conversation';
    if (!(await remove(items, `Supprimer définitivement ${label} ? Cette action est irréversible.`))) return;

    let currentRemoved = false;
    items.forEach((item) => {
      const row = root.querySelector(`[data-inbox-item="${item}"]`);
      if (row?.hasAttribute('data-inbox-selected')) currentRemoved = true;
      row?.remove();
    });
    if (currentRemoved) {
      window.location.href = root.dataset.resetUrl;
      return;
    }
    refresh();
  });

  // Un seul message (fil WhatsApp/SMS) ou le message de contact affiché
  document.querySelectorAll('[data-message-delete]').forEach((btn) => {
    btn.addEventListener('click', async () => {
      const item = btn.dataset.messageDelete;
      if (!(await remove([item], 'Supprimer définitivement ce message ?'))) return;

      if (item.startsWith('contact:')) {
        window.location.href = root.dataset.resetUrl;
        return;
      }
      const thread = btn.closest('[data-thread-message]')?.parentElement;
      btn.closest('[data-thread-message]')?.remove();
      if (thread && !thread.querySelector('[data-thread-message]')) {
        // Fil vidé : la conversation disparaît de la liste
        window.location.href = root.dataset.resetUrl;
      }
    });
  });
}

/**
 * Création d'une loterie / diffusion d'une offre : choix du mode de diffusion
 * (SMS / e-mail) et des segments ciblés, avec estimation en direct du nombre
 * de destinataires.
 */
function initLotteryAnnounce() {
  const root = document.querySelector('[data-announce]');
  const dataEl = root?.querySelector('[data-announce-audience]');
  if (!root || !dataEl) return;

  const audience = JSON.parse(dataEl.textContent || '[]');
  const publish = document.querySelector('[data-announce-publish]');
  const all = root.querySelector('[data-announce-all]');
  const segments = [...root.querySelectorAll('[data-announce-segment]')];
  const channels = [...root.querySelectorAll('[data-announce-channel]')];
  const summary = root.querySelector('[data-announce-summary]');
  const submit = document.querySelector('[data-announce-submit]');
  const plural = (n, word) => `${n} ${word}${n > 1 ? 's' : ''}`;
  const emailChannel = channels.find((c) => c.value === 'email');
  const picker = initEmailRecipientPicker(root, audience, () => update());

  const update = () => {
    const draft = publish && !publish.checked;
    const draftNote = root.querySelector('[data-announce-draft-note]');
    if (draftNote) draftNote.hidden = !draft;
    root.querySelectorAll('[data-announce-options]').forEach((el) => { el.hidden = draft; });
    if (picker) picker.el.hidden = !emailChannel?.checked;

    const activeChannels = channels.filter((c) => c.checked).map((c) => c.value);
    const selected = new Set(segments.filter((s) => s.checked).map((s) => s.value));
    const targeted = all.checked ? audience : audience.filter((c) => c.s.some((s) => selected.has(s)));
    const pickedCount = activeChannels.includes('email') && picker ? picker.picked.size : 0;
    // Les segments ne servent qu'au SMS et à l'e-mail sans sélection manuelle.
    const needsSegments = activeChannels.includes('sms') || (activeChannels.includes('email') && pickedCount === 0);

    let text;
    let sending = false;
    if (activeChannels.length === 0) {
      text = root.dataset.announceEmpty || 'Aucun mode de diffusion coché : la loterie sera publiée sans annonce.';
    } else if (needsSegments && !all.checked && selected.size === 0) {
      text = 'Cochez « Tous les clients actifs » ou au moins un segment.';
    } else {
      const parts = [];
      let total = 0;
      if (activeChannels.includes('sms')) {
        const n = targeted.filter((c) => c.sms).length;
        total += n;
        parts.push(`SMS : ${n}`);
      }
      if (activeChannels.includes('email')) {
        const n = pickedCount > 0 ? pickedCount : targeted.filter((c) => c.email).length;
        total += n;
        parts.push(`e-mail : ${n}${pickedCount > 0 ? ' (sélection manuelle)' : ''}`);
      }
      text = needsSegments
        ? `${plural(targeted.length, 'client')} ciblé${targeted.length > 1 ? 's' : ''} → ${parts.join(' · ')}`
          + ' (sans numéro ou sans e-mail valide, ou désinscrits des e-mails, certains clients ne reçoivent pas tous les canaux).'
        : `Annonce → ${parts.join(' · ')}`;
      sending = total > 0;
    }
    summary.textContent = text;
    if (submit) submit.textContent = !draft && sending ? submit.dataset.labelSend : submit.dataset.labelIdle;
  };

  all.addEventListener('change', () => {
    if (all.checked) segments.forEach((s) => { s.checked = false; });
    update();
  });
  segments.forEach((s) => s.addEventListener('change', () => {
    if (s.checked) all.checked = false;
    update();
  }));
  channels.forEach((c) => c.addEventListener('change', update));
  publish?.addEventListener('change', update);
  update();
}

/**
 * Moteur de recherche des destinataires e-mail d'une loterie : filtre les
 * clients joignables par nom ou adresse, sélection multiple sous forme de
 * pastilles, et un champ caché email_recipients[] par client choisi.
 */
function initEmailRecipientPicker(root, audience, onChange) {
  const el = root.querySelector('[data-email-picker]');
  if (!el) return null;

  const search = el.querySelector('[data-email-picker-search]');
  const results = el.querySelector('[data-email-picker-results]');
  const chips = el.querySelector('[data-email-picker-chips]');
  const inputs = el.querySelector('[data-email-picker-inputs]');
  const clients = audience.filter((c) => c.m);
  const byId = new Map(clients.map((c) => [c.id, c]));
  const picked = new Set([...inputs.querySelectorAll('input')].map((i) => Number(i.value)).filter((id) => byId.has(id)));
  const normalize = (str) => str.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
  let matches = [];

  const render = (notify = true) => {
    inputs.innerHTML = '';
    chips.innerHTML = '';
    picked.forEach((id) => {
      const client = byId.get(id);
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'email_recipients[]';
      input.value = String(id);
      inputs.appendChild(input);

      const chip = document.createElement('button');
      chip.type = 'button';
      chip.className = 'inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-brand-50 text-ink text-xs font-semibold hover:bg-brand-100';
      chip.title = client.m;
      chip.textContent = `${client.n || client.m} ×`;
      chip.addEventListener('click', () => { picked.delete(id); render(); });
      chips.appendChild(chip);
    });
    if (picked.size > 1) {
      const clear = document.createElement('button');
      clear.type = 'button';
      clear.className = 'text-xs font-semibold text-gray-500 hover:text-brand-500 px-1';
      clear.textContent = 'Tout retirer';
      clear.addEventListener('click', () => { picked.clear(); render(); });
      chips.appendChild(clear);
    }
    refresh();
    if (notify) onChange();
  };

  const refresh = () => {
    if (results.hidden) return;
    const q = normalize(search.value.trim());
    matches = clients.filter((c) => !q || normalize(`${c.n} ${c.m}`).includes(q));
    results.innerHTML = '';
    if (matches.length === 0) {
      results.innerHTML = '<li class="px-4 py-3 text-sm text-gray-400">Aucun client trouvé.</li>';
      return;
    }
    const unpicked = matches.filter((c) => !picked.has(c.id));
    if (q && unpicked.length > 1) {
      const li = document.createElement('li');
      li.className = 'px-4 py-2 text-xs font-semibold text-brand-500 cursor-pointer hover:bg-gray-50 border-b border-gray-100';
      li.textContent = `Sélectionner les ${unpicked.length} résultats`;
      li.addEventListener('mousedown', (e) => {
        e.preventDefault();
        unpicked.forEach((c) => picked.add(c.id));
        render();
      });
      results.appendChild(li);
    }
    matches.slice(0, 50).forEach((c) => {
      const li = document.createElement('li');
      li.className = 'flex items-center gap-2.5 px-4 py-2 text-sm cursor-pointer hover:bg-gray-50';
      const box = document.createElement('input');
      box.type = 'checkbox';
      box.tabIndex = -1;
      box.checked = picked.has(c.id);
      box.className = 'rounded border-gray-300 text-brand-500 pointer-events-none';
      const label = document.createElement('span');
      label.className = 'min-w-0 truncate';
      label.innerHTML = '<span class="font-semibold text-ink"></span> <span class="text-gray-500"></span>';
      label.children[0].textContent = c.n;
      label.children[1].textContent = c.m;
      li.append(box, label);
      // mousedown + preventDefault : garde le focus dans la recherche pour enchaîner les sélections.
      li.addEventListener('mousedown', (e) => {
        e.preventDefault();
        if (picked.has(c.id)) picked.delete(c.id); else picked.add(c.id);
        render();
      });
      results.appendChild(li);
    });
    if (matches.length > 50) {
      results.insertAdjacentHTML('beforeend', `<li class="px-4 py-2 text-xs text-gray-400">… ${matches.length - 50} autres, affinez la recherche.</li>`);
    }
  };

  const open = () => { results.hidden = false; refresh(); };
  search.addEventListener('focus', open);
  search.addEventListener('input', open);
  search.addEventListener('blur', () => { results.hidden = true; });
  search.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      e.preventDefault(); // ne soumet pas le formulaire
      const first = matches.find((c) => !picked.has(c.id));
      if (first) { picked.add(first.id); render(); }
    } else if (e.key === 'Escape') {
      results.hidden = true;
    }
  });

  render(false); // initial : update() est appelé ensuite par initLotteryAnnounce
  return { el, picked };
}

/**
 * Création d'un sondage : mode de diffusion (SMS / e-mail) et choix des
 * destinataires client par client dans un tableau filtrable et triable, avec
 * un champ caché recipients[] par client coché et le décompte par canal.
 */
function initPollAnnounce() {
  const root = document.querySelector('[data-poll-announce]');
  const dataEl = root?.querySelector('[data-recipient-data]');
  if (!root || !dataEl) return;

  const { clients, groups } = JSON.parse(dataEl.textContent || '{}');
  const publish = document.querySelector('[data-announce-publish]');
  const channels = [...root.querySelectorAll('[data-announce-channel]')];
  const summary = root.querySelector('[data-announce-summary]');
  const submit = document.querySelector('[data-announce-submit]');
  const tbody = root.querySelector('[data-recipient-rows]');
  const inputs = root.querySelector('[data-recipient-inputs]');
  const allBox = root.querySelector('[data-recipient-all]');
  const count = root.querySelector('[data-recipient-count]');
  const filters = [...root.querySelectorAll('[data-filter]')];
  const sortHeaders = [...root.querySelectorAll('[data-sort]')];
  const byId = new Map(clients.map((c) => [c.id, c]));
  const picked = new Set([...inputs.querySelectorAll('input')].map((i) => Number(i.value)).filter((id) => byId.has(id)));
  const normalize = (str) => String(str ?? '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
  const plural = (n, word) => `${n} ${word}${n > 1 ? 's' : ''}`;
  const frDate = (iso) => (iso ? iso.slice(0, 10).split('-').reverse().join('/') : '—');
  const money = (n) => (n > 0 ? `${n.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} €` : '—');
  let sort = { key: 'created', dir: -1 };
  const arrows = new Map(sortHeaders.map((th) => {
    const arrow = document.createElement('span');
    arrow.className = 'ml-1 text-brand-500';
    return [th, th.appendChild(arrow)];
  }));
  let visible = [];

  const matches = (c) => filters.every((f) => {
    const v = f.value.trim();
    if (v === '') return true;
    switch (f.dataset.filter) {
      case 'q': return normalize(`${c.id} ${c.first} ${c.last} ${c.email} ${c.phone}`).includes(normalize(v));
      case 'group': return c.group === v;
      case 'from': return c.created.slice(0, 10) >= v;
      case 'to': return c.created.slice(0, 10) <= v;
      default: return true;
    }
  });

  const renderRows = () => {
    visible = clients.filter(matches).sort((a, b) => {
      const x = a[sort.key] ?? '';
      const y = b[sort.key] ?? '';
      const cmp = typeof x === 'number' && typeof y === 'number' ? x - y : normalize(x).localeCompare(normalize(y), 'fr');
      return cmp * sort.dir;
    });
    arrows.forEach((arrow, th) => {
      arrow.textContent = th.dataset.sort === sort.key ? (sort.dir > 0 ? '↑' : '↓') : '';
    });

    tbody.innerHTML = '';
    if (visible.length === 0) {
      tbody.innerHTML = '<tr><td colspan="10" class="px-4 py-10 text-center text-gray-400">Aucun client ne correspond à ces filtres.</td></tr>';
    }
    visible.forEach((c) => {
      const tr = document.createElement('tr');
      tr.className = `cursor-pointer transition-colors ${picked.has(c.id) ? 'bg-brand-50/60 hover:bg-brand-50' : 'hover:bg-gray-50'}`;
      const group = groups[c.group];
      tr.innerHTML = `
        <td class="pl-4 pr-2 py-3"><input type="checkbox" tabindex="-1" class="rounded border-gray-300 text-brand-500 pointer-events-none" ${picked.has(c.id) ? 'checked' : ''}></td>
        <td class="px-3 py-3 text-gray-400 tabular-nums">${c.id}</td>
        <td class="px-3 py-3 font-medium text-ink" data-col="first"></td>
        <td class="px-3 py-3 font-medium text-ink" data-col="last"></td>
        <td class="px-3 py-3 ${c.mail ? 'text-gray-600' : 'text-gray-300'}" data-col="email" ${c.mail ? '' : 'title="Ne recevra pas l\'e-mail (adresse invalide ou désinscrit)"'}></td>
        <td class="px-3 py-3 whitespace-nowrap tabular-nums ${c.sms ? 'text-gray-600' : 'text-gray-300'}" data-col="phone"></td>
        <td class="px-3 py-3">${group ? `<span class="px-2 py-0.5 rounded-full text-xs font-semibold whitespace-nowrap ${group.color}"></span>` : '<span class="text-gray-300">—</span>'}</td>
        <td class="px-3 py-3 text-right whitespace-nowrap tabular-nums text-gray-600">${money(c.sales)}</td>
        <td class="px-3 py-3 whitespace-nowrap tabular-nums text-gray-600">${frDate(c.created)}</td>
        <td class="px-3 py-3 pr-4 whitespace-nowrap tabular-nums text-gray-600">${frDate(c.visit)}</td>`;
      // Données saisies par les clients : insérées en texte, jamais en HTML.
      ['first', 'last', 'email', 'phone'].forEach((key) => { tr.querySelector(`[data-col="${key}"]`).textContent = c[key] || '—'; });
      if (group) tr.querySelector('span.rounded-full').textContent = group.label;
      tr.addEventListener('click', () => {
        if (picked.has(c.id)) picked.delete(c.id); else picked.add(c.id);
        renderRows();
        update();
      });
      tbody.appendChild(tr);
    });

    const pickedVisible = visible.filter((c) => picked.has(c.id)).length;
    allBox.checked = visible.length > 0 && pickedVisible === visible.length;
    allBox.indeterminate = pickedVisible > 0 && pickedVisible < visible.length;
    count.innerHTML = `<span class="font-semibold text-ink">${plural(picked.size, 'client')} sélectionné${picked.size > 1 ? 's' : ''}</span> · ${visible.length} affiché${visible.length > 1 ? 's' : ''} sur ${clients.length}`;

    inputs.innerHTML = '';
    picked.forEach((id) => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'recipients[]';
      input.value = String(id);
      inputs.appendChild(input);
    });
  };

  const update = () => {
    const draft = publish && !publish.checked;
    const draftNote = root.querySelector('[data-announce-draft-note]');
    if (draftNote) draftNote.hidden = !draft;
    root.querySelectorAll('[data-announce-options]').forEach((el) => { el.hidden = draft; });

    const activeChannels = channels.filter((c) => c.checked).map((c) => c.value);
    const selected = clients.filter((c) => picked.has(c.id));
    let text;
    let sending = false;
    if (activeChannels.length === 0) {
      text = root.dataset.announceEmpty;
    } else if (selected.length === 0) {
      text = 'Cochez au moins un client dans le tableau.';
    } else {
      const parts = [];
      let total = 0;
      if (activeChannels.includes('sms')) {
        const n = selected.filter((c) => c.sms).length;
        total += n;
        parts.push(`SMS : ${n}`);
      }
      if (activeChannels.includes('email')) {
        const n = selected.filter((c) => c.mail).length;
        total += n;
        parts.push(`e-mail : ${n}`);
      }
      text = `${plural(selected.length, 'client')} choisi${selected.length > 1 ? 's' : ''} → ${parts.join(' · ')}`
        + (total < selected.length * activeChannels.length ? ' (sans numéro ou sans e-mail valide, ou désinscrits des e-mails, certains clients ne reçoivent pas tous les canaux).' : '');
      sending = total > 0;
    }
    summary.textContent = text;
    if (submit) submit.textContent = !draft && sending ? submit.dataset.labelSend : submit.dataset.labelIdle;
  };

  allBox.addEventListener('change', () => {
    visible.forEach((c) => { if (allBox.checked) picked.add(c.id); else picked.delete(c.id); });
    renderRows();
    update();
  });
  root.querySelector('[data-recipient-clear]').addEventListener('click', () => {
    picked.clear();
    renderRows();
    update();
  });
  filters.forEach((f) => f.addEventListener(f.tagName === 'SELECT' || f.type === 'date' ? 'change' : 'input', renderRows));
  filters.forEach((f) => f.addEventListener('keydown', (e) => { if (e.key === 'Enter') e.preventDefault(); }));
  root.querySelector('[data-filter-reset]').addEventListener('click', () => {
    filters.forEach((f) => { f.value = ''; });
    renderRows();
  });
  sortHeaders.forEach((th) => th.addEventListener('click', () => {
    sort = { key: th.dataset.sort, dir: sort.key === th.dataset.sort ? -sort.dir : 1 };
    renderRows();
  }));
  channels.forEach((c) => c.addEventListener('change', update));
  publish?.addEventListener('change', update);
  renderRows();
  update();
}
