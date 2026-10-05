// =====================================================
// Le Commerce — JS front (vanilla, sans dépendance)
// =====================================================

document.addEventListener('DOMContentLoaded', () => {
  initMobileMenu();
  initAdminMobileSidebar();
  initAdminSidebarCollapse();
  initWeatherWidget();
  initChatChips();
  initChatFab();
  initProximityWidget();
  initContactForm();
  initCookieConsent();
  initEventTracking();
  initScrollReveal();
  initImagePreview();
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
