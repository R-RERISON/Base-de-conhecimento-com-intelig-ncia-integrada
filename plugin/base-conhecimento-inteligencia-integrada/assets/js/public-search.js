(function () {
  'use strict';

  var config = window.BDC_KB_PUBLIC_SEARCH || {};
  var controller = null;
  var timer = null;

  function searchTarget() {
    return document.querySelector('[data-bdc-primary-search]') || document.querySelector('[data-bdc-global-search]');
  }

  function escapeHtml(value) {
    return String(value || '').replace(/[&<>'"]/g, function (char) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' }[char];
    });
  }

  function resultMarkup(row) {
    var meta = '<span class="bdc-live-result__meta">' + escapeHtml(row.category || 'Instrução') + '</span>';
    var excerpt = row.excerpt ? '<p>' + escapeHtml(row.excerpt) + '</p>' : '';
    return '<a class="bdc-live-result" href="' + escapeHtml(row.url) + '">' +
      '<span class="bdc-live-result__rank">' + (row.rank || '') + '</span>' +
      '<span class="bdc-live-result__copy">' + meta + '<strong>' + escapeHtml(row.title) + '</strong>' + excerpt + '</span>' +
      '<span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span></a>';
  }

  function setPanel(form, payload, query) {
    var surface = form.closest('.bdc-public') || document;
    var panel = form.parentElement && form.parentElement.querySelector('[data-bdc-live-search-panel]');
    if (!panel && form.classList.contains('bdc-global-search')) {
      panel = surface.querySelector('.bdc-global-search-panel[data-bdc-live-search-panel]');
    }
    if (!panel) return;
    var target = panel.querySelector('[data-bdc-live-search-results]');
    var title = panel.querySelector('[data-bdc-live-search-title]');
    if (!target) return;
    target.setAttribute('aria-busy', 'false');
    panel.classList.remove('is-loading');

    if (!query || query.length < Number(config.minChars || 2)) {
      panel.hidden = true;
      target.innerHTML = '';
      return;
    }

    panel.hidden = false;
    if (title) {
      var count = payload && Array.isArray(payload.results) ? payload.results.length : 0;
      title.textContent = count + (count === 1 ? ' resultado' : ' resultados') + ' para “' + query + '”';
    }

    if (!payload || payload.state === 'empty' || !Array.isArray(payload.results) || !payload.results.length) {
      target.innerHTML = '<div class="bdc-search-empty">Nenhum resultado encontrado.</div>';
      return;
    }

    target.innerHTML = '<div class="bdc-live-results">' + payload.results.map(resultMarkup).join('') + '</div>';
  }

  function setLoading(form, query) {
    var surface = form.closest('.bdc-public') || document;
    var panel = form.parentElement && form.parentElement.querySelector('[data-bdc-live-search-panel]');
    if (!panel && form.classList.contains('bdc-global-search')) {
      panel = surface.querySelector('.bdc-global-search-panel[data-bdc-live-search-panel]');
    }
    if (!panel) return;
    var target = panel.querySelector('[data-bdc-live-search-results]');
    var title = panel.querySelector('[data-bdc-live-search-title]');
    panel.hidden = false;
    panel.classList.add('is-loading');
    if (target) target.setAttribute('aria-busy', 'true');
    if (title) title.textContent = 'Buscando “' + query + '”';
  }

  function liveSearch(form, input) {
    var query = input.value.trim();
    if (timer) window.clearTimeout(timer);
    if (controller) controller.abort();

    if (query.length < Number(config.minChars || 2)) {
      setPanel(form, null, query);
      return;
    }

    timer = window.setTimeout(function () {
      setLoading(form, query);
      controller = typeof AbortController !== 'undefined' ? new AbortController() : null;
      var data = new URLSearchParams();
      data.append('action', 'bdc_kb_public_search_preview');
      data.append('nonce', String(config.nonce || ''));
      data.append('query', query);

      fetch(String(config.ajaxUrl || ''), {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: data.toString(),
        signal: controller ? controller.signal : undefined
      }).then(function (response) {
        return response.json();
      }).then(function (json) {
        if (!json || !json.success) throw new Error('search_failed');
        setPanel(form, json.data || {}, query);
      }).catch(function (error) {
        if (error && error.name === 'AbortError') return;
        setPanel(form, { state: 'empty', results: [] }, query);
      });
    }, Number(config.debounceMs || 180));
  }

  document.addEventListener('input', function (event) {
    var input = event.target.closest('[data-bdc-live-search-input]');
    if (!input) return;
    var form = input.closest('[data-bdc-live-search-form]');
    if (!form) return;
    liveSearch(form, input);
  });

  document.addEventListener('keydown', function (event) {
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
      var input = searchTarget();
      if (!input) return;
      event.preventDefault();
      input.focus();
      if (typeof input.select === 'function') input.select();
    }
    if (event.key === 'Escape') {
      var header = document.querySelector('[data-bdc-header]');
      var toggle = document.querySelector('[data-bdc-header-toggle]');
      if (header) header.classList.remove('is-menu-open');
      if (toggle) {
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', 'Abrir links rápidos');
      }
    }
  });

  document.addEventListener('click', function (event) {
    var toggle = event.target.closest('[data-bdc-header-toggle]');
    if (toggle) {
      var header = toggle.closest('[data-bdc-header]');
      if (!header) return;
      var open = header.classList.toggle('is-menu-open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      toggle.setAttribute('aria-label', open ? 'Fechar links rápidos' : 'Abrir links rápidos');
      return;
    }

    var clear = event.target.closest('[data-bdc-live-search-clear]');
    if (clear) {
      var input = searchTarget();
      if (input) input.value = '';
    }
  });

  function initReaderRail() {
    var slot = document.querySelector('[data-bdc-summary-slot]');
    var rail = document.querySelector('[data-bdc-summary-rail]');
    if (!slot || !rail) return;

    var ticking = false;

    function topOffset() {
      var admin = document.getElementById('wpadminbar');
      var adminHeight = admin ? admin.getBoundingClientRect().height : 0;
      var header = document.querySelector('[data-bdc-header]');
      var headerBottom = header ? header.getBoundingClientRect().bottom : 0;
      return Math.max(adminHeight + 18, headerBottom > adminHeight ? headerBottom + 18 : adminHeight + 18);
    }

    function update() {
      ticking = false;
      if (window.innerWidth <= 1040) {
        rail.style.transform = '';
        return;
      }
      var pageY = window.scrollY || window.pageYOffset || 0;
      var slotRect = slot.getBoundingClientRect();
      var slotTop = slotRect.top + pageY;
      var max = Math.max(0, slot.offsetHeight - rail.offsetHeight);
      var desired = pageY + topOffset() - slotTop;
      var y = Math.max(0, Math.min(max, desired));
      rail.style.transform = 'translate3d(0,' + Math.round(y) + 'px,0)';
    }

    function requestUpdate() {
      if (ticking) return;
      ticking = true;
      window.requestAnimationFrame(update);
    }

    window.addEventListener('scroll', requestUpdate, { passive: true });
    window.addEventListener('resize', requestUpdate);
    requestUpdate();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initReaderRail);
  } else {
    initReaderRail();
  }
})();
