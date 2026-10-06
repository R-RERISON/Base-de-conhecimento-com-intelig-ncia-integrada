(function () {
  'use strict';

  var config = window.BDC_KB_PUBLIC_SEARCH || {};
  var searchStates = new WeakMap();
  var consultationEvents = new WeakSet();

  function searchTarget() {
    return document.querySelector('[data-bdc-primary-search]') || document.querySelector('[data-bdc-global-search]');
  }

  function panelForForm(form) {
    var surface = form.closest('.bdc-public') || document;
    var panel = form.parentElement && form.parentElement.querySelector('[data-bdc-live-search-panel]');
    if (!panel && form.classList.contains('bdc-global-search')) {
      panel = surface.querySelector('.bdc-global-search-panel[data-bdc-live-search-panel]');
    }
    return panel;
  }

  function stateForForm(form) {
    var state = searchStates.get(form);
    if (!state) {
      state = { timer: null, controller: null, requestId: 0 };
      searchStates.set(form, state);
    }
    return state;
  }

  function cancelPending(form) {
    if (!form) return null;
    var state = stateForForm(form);
    if (state.timer) {
      window.clearTimeout(state.timer);
      state.timer = null;
    }
    if (state.controller) {
      state.controller.abort();
      state.controller = null;
    }
    state.requestId += 1;
    return state;
  }

  function escapeHtml(value) {
    return String(value || '').replace(/[&<>'"]/g, function (char) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' }[char];
    });
  }

  function resultMarkup(row) {
    var meta = '<span class="bdc-live-result__meta">' + escapeHtml(row.category || 'Instrução') + '</span>';
    var excerpt = row.excerpt ? '<p>' + escapeHtml(row.excerpt) + '</p>' : '';
    return '<a class="bdc-live-result" data-bdc-consult-term="' + escapeHtml(row.title || '') + '" data-bdc-consult-source="result_click" href="' + escapeHtml(row.url) + '">' +
      '<span class="bdc-live-result__rank">' + (row.rank || '') + '</span>' +
      '<span class="bdc-live-result__copy">' + meta + '<strong>' + escapeHtml(row.title) + '</strong>' + excerpt + '</span>' +
      '<span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span></a>';
  }

  function setPanel(form, payload, query) {
    var panel = panelForForm(form);
    if (!panel) return;
    var target = panel.querySelector('[data-bdc-live-search-results]');
    var title = panel.querySelector('[data-bdc-live-search-title]');
    if (!target) return;

    target.setAttribute('aria-live', 'polite');
    target.setAttribute('aria-busy', 'false');
    panel.classList.remove('is-loading', 'is-error');

    if (!query || query.length < Number(config.minChars || 2)) {
      panel.hidden = true;
      panel.setAttribute('data-bdc-live-search-state', 'idle');
      target.innerHTML = '';
      if (title) title.textContent = 'Resultados';
      return;
    }

    var state = payload && payload.state ? String(payload.state) : 'empty';
    panel.hidden = false;
    panel.setAttribute('data-bdc-live-search-state', state);

    if (state === 'request_error' || state === 'technical_error' || state === 'rate_limited' || state === 'invalid_query') {
      var message = payload && payload.message
        ? String(payload.message)
        : 'Não foi possível concluir a pesquisa agora.';
      panel.classList.add('is-error');
      if (title) title.textContent = 'Pesquisa indisponível';
      target.innerHTML = '<div class="bdc-search-error" role="status"><strong>' + escapeHtml(message) + '</strong><span>Tente novamente ou pressione Enter para usar a pesquisa completa.</span></div>';
      return;
    }

    var count = payload && Array.isArray(payload.results) ? payload.results.length : 0;
    if (title) {
      title.textContent = count + (count === 1 ? ' resultado' : ' resultados') + ' para “' + query + '”';
    }

    if (!payload || state === 'empty' || state === 'zero_results' || !Array.isArray(payload.results) || !payload.results.length) {
      target.innerHTML = '<div class="bdc-search-empty">Nenhum resultado encontrado.</div>';
      return;
    }

    target.innerHTML = '<div class="bdc-live-results">' + payload.results.map(resultMarkup).join('') + '</div>';
  }

  function setLoading(form, query) {
    var panel = panelForForm(form);
    if (!panel) return;
    var target = panel.querySelector('[data-bdc-live-search-results]');
    var title = panel.querySelector('[data-bdc-live-search-title]');
    panel.hidden = false;
    panel.classList.remove('is-error');
    panel.classList.add('is-loading');
    panel.setAttribute('data-bdc-live-search-state', 'loading');
    if (target) {
      target.setAttribute('aria-live', 'polite');
      target.setAttribute('aria-busy', 'true');
    }
    if (title) title.textContent = 'Buscando “' + query + '”';
  }

  function liveSearch(form, input) {
    var query = input.value.trim();
    var state = cancelPending(form);
    if (!state) return;
    var requestId = state.requestId;

    if (query.length < Number(config.minChars || 2)) {
      setPanel(form, null, query);
      return;
    }

    state.timer = window.setTimeout(function () {
      state.timer = null;
      if (requestId !== state.requestId || input.value.trim() !== query) return;

      setLoading(form, query);
      state.controller = typeof AbortController !== 'undefined' ? new AbortController() : null;
      var data = new URLSearchParams();
      data.append('action', String(config.action || 'bdc_kb_public_search'));
      data.append('nonce', String(config.nonce || ''));
      data.append('query', query);
      if (config.previewMode) data.append('preview', '1');
      if (config.isolationMode) data.append('isolation', '1');

      fetch(String(config.ajaxUrl || ''), {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: data.toString(),
        signal: state.controller ? state.controller.signal : undefined
      }).then(function (response) {
        return response.json().then(function (json) {
          return { ok: response.ok, json: json };
        });
      }).then(function (result) {
        if (requestId !== state.requestId || input.value.trim() !== query) return;
        if (!result.ok || !result.json || !result.json.success) {
          var failure = new Error('search_failed');
          failure.payload = result.json && result.json.data ? result.json.data : {};
          throw failure;
        }
        state.controller = null;
        setPanel(form, result.json.data || {}, query);
      }).catch(function (error) {
        if (error && error.name === 'AbortError') return;
        if (requestId !== state.requestId || input.value.trim() !== query) return;
        state.controller = null;
        var payload = error && error.payload ? error.payload : {};
        setPanel(form, {
          state: payload.state || 'request_error',
          message: payload.message || 'Não foi possível concluir a pesquisa agora.'
        }, query);
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
      var active = document.activeElement;
      var liveInput = active && typeof active.closest === 'function' ? active.closest('[data-bdc-live-search-input]') : null;
      if (liveInput) {
        var liveForm = liveInput.closest('[data-bdc-live-search-form]');
        if (liveForm) {
          cancelPending(liveForm);
          setPanel(liveForm, null, '');
        }
      }

      var header = document.querySelector('[data-bdc-header]');
      var toggle = document.querySelector('[data-bdc-header-toggle]');
      if (header) header.classList.remove('is-menu-open');
      if (toggle) {
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', 'Abrir links rápidos');
      }
    }
  });

  function consultationEventId() {
    if (window.crypto && typeof window.crypto.randomUUID === 'function') {
      return window.crypto.randomUUID();
    }
    return 'bdc-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 14);
  }

  function recordConsultation(element, term, source) {
    if (!element || !term || !config.consultAction || !config.consultNonce || !config.ajaxUrl) return;
    if (consultationEvents.has(element)) return;
    consultationEvents.add(element);

    var eventId = element.getAttribute('data-bdc-consult-event-id') || consultationEventId();
    element.setAttribute('data-bdc-consult-event-id', eventId);

    var data = new URLSearchParams();
    data.append('action', String(config.consultAction));
    data.append('nonce', String(config.consultNonce));
    data.append('term', String(term));
    data.append('source', String(source || 'result_click'));
    data.append('event_id', eventId);
    fetch(String(config.ajaxUrl), {
      method: 'POST',
      credentials: 'same-origin',
      keepalive: true,
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: data.toString()
    }).catch(function () {
      consultationEvents.delete(element);
    });
  }

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

    var consult = event.target.closest('[data-bdc-consult-term]');
    if (consult) {
      recordConsultation(consult, consult.getAttribute('data-bdc-consult-term') || '', consult.getAttribute('data-bdc-consult-source') || 'result_click');
    }

    var clear = event.target.closest('[data-bdc-live-search-clear]');
    if (clear) {
      var input = searchTarget();
      if (input) {
        var form = input.closest('[data-bdc-live-search-form]');
        if (form) {
          cancelPending(form);
          input.value = '';
          setPanel(form, null, '');
        } else {
          input.value = '';
        }
      }
    }
  });


})();
