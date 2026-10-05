(function () {
  'use strict';

  function tagIntegratedActions() {
    var documentBody = document.querySelector('.bdc-reader-document');
    if (!documentBody || !documentBody.firstElementChild) return;

    var first = documentBody.firstElementChild;
    var text = (first.textContent || '').replace(/\s+/g, ' ').trim();
    if (text.indexOf('Ações do conteúdo') !== -1 && text.indexOf('Registrar leitura') !== -1) {
      first.classList.add('bdc-reader-integrated-actions');
    }
  }

  function initReaderRail() {
    var slot = document.querySelector('[data-bdc-summary-slot]');
    var rail = document.querySelector('[data-bdc-summary-rail]');
    if (!slot || !rail) return;

    var ticking = false;
    var currentState = '';
    var fixedLeft = null;
    var fixedWidth = null;

    function topOffset() {
      var admin = document.getElementById('wpadminbar');
      var adminHeight = admin ? admin.getBoundingClientRect().height : 0;
      var header = document.querySelector('[data-bdc-header]');
      var headerBottom = header ? header.getBoundingClientRect().bottom : adminHeight;
      return Math.max(adminHeight, headerBottom) + 18;
    }

    function clearInlineGeometry() {
      rail.style.top = '';
      rail.style.left = '';
      rail.style.width = '';
      rail.style.bottom = '';
    }

    function setState(nextState, geometry) {
      if (currentState !== nextState) {
        rail.setAttribute('data-bdc-rail-state', nextState);
        currentState = nextState;
      }

      if (nextState === 'fixed' && geometry) {
        var left = Math.round(geometry.left * 100) / 100;
        var width = Math.round(geometry.width * 100) / 100;

        if (fixedLeft !== left) {
          rail.style.left = left + 'px';
          fixedLeft = left;
        }
        if (fixedWidth !== width) {
          rail.style.width = width + 'px';
          fixedWidth = width;
        }

        rail.style.top = Math.round(geometry.top) + 'px';
        rail.style.bottom = '';
        return;
      }

      fixedLeft = null;
      fixedWidth = null;
      clearInlineGeometry();
    }

    function resetForFlow() {
      setState('flow');
    }

    function update() {
      ticking = false;

      if (window.innerWidth <= 1040) {
        resetForFlow();
        return;
      }

      var offset = topOffset();
      var slotRect = slot.getBoundingClientRect();
      var railHeight = rail.offsetHeight;

      if (slotRect.top >= offset) {
        setState('flow');
        return;
      }

      if (slotRect.bottom - railHeight <= offset) {
        setState('bottom');
        return;
      }

      setState('fixed', {
        top: offset,
        left: slotRect.left,
        width: slotRect.width
      });
    }

    function requestUpdate() {
      if (ticking) return;
      ticking = true;
      window.requestAnimationFrame(update);
    }

    window.addEventListener('scroll', requestUpdate, { passive: true });
    window.addEventListener('resize', requestUpdate);

    if (typeof ResizeObserver !== 'undefined') {
      var observer = new ResizeObserver(requestUpdate);
      observer.observe(slot);
      observer.observe(rail);
    }

    requestUpdate();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      tagIntegratedActions();
      initReaderRail();
    });
  } else {
    tagIntegratedActions();
    initReaderRail();
  }
})();
