// =====================================================
// Le Commerce — JS front (vanilla, sans dépendance)
// =====================================================

document.addEventListener('DOMContentLoaded', () => {
  initMobileMenu();
  initAdminMobileSidebar();
  initAdminSidebarCollapse();
  initAdminSidebarAccordion();
  initLotteryAnnounce();
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
 * Création d'une loterie : choix du mode de diffusion (SMS / e-mail) et des
 * segments ciblés, avec estimation en direct du nombre de destinataires.
 */
function initLotteryAnnounce() {
  const root = document.querySelector('[data-announce]');
  const dataEl = document.getElementById('lottery-audience');
  if (!root || !dataEl) return;

  const audience = JSON.parse(dataEl.textContent || '[]');
  const publish = document.querySelector('[data-announce-publish]');
  const all = root.querySelector('[data-announce-all]');
  const segments = [...root.querySelectorAll('[data-announce-segment]')];
  const channels = [...root.querySelectorAll('[data-announce-channel]')];
  const summary = root.querySelector('[data-announce-summary]');
  const submit = document.querySelector('[data-announce-submit]');
  const plural = (n, word) => `${n} ${word}${n > 1 ? 's' : ''}`;

  const update = () => {
    const draft = publish && !publish.checked;
    root.querySelector('[data-announce-draft-note]').hidden = !draft;
    root.querySelectorAll('[data-announce-options]').forEach((el) => { el.hidden = draft; });

    const activeChannels = channels.filter((c) => c.checked).map((c) => c.value);
    const selected = new Set(segments.filter((s) => s.checked).map((s) => s.value));
    const targeted = all.checked ? audience : audience.filter((c) => c.s.some((s) => selected.has(s)));

    let text;
    let sending = false;
    if (activeChannels.length === 0) {
      text = 'Aucun mode de diffusion coché : la loterie sera publiée sans annonce.';
    } else if (!all.checked && selected.size === 0) {
      text = 'Cochez « Tous les clients actifs » ou au moins un segment.';
    } else {
      const parts = [];
      if (activeChannels.includes('sms')) parts.push(`SMS : ${targeted.filter((c) => c.sms).length}`);
      if (activeChannels.includes('email')) parts.push(`e-mail : ${targeted.filter((c) => c.email).length}`);
      text = `${plural(targeted.length, 'client')} ciblé${targeted.length > 1 ? 's' : ''} → ${parts.join(' · ')}`
        + ' (sans numéro ou sans e-mail valide, ou désinscrits des e-mails, certains clients ne reçoivent pas tous les canaux).';
      sending = targeted.length > 0;
    }
    summary.textContent = text;
    if (submit) submit.textContent = !draft && sending ? 'Créer la loterie et envoyer l\'annonce' : 'Créer la loterie';
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
