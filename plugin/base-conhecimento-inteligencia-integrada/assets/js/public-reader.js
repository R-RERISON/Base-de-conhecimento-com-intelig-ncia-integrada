(function () {
  'use strict';

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
      var viewportGap = 18;

      if (headerBottom > adminHeight) {
        return headerBottom + viewportGap;
      }

      return adminHeight + viewportGap;
    }

    function resetForFlow() {
      rail.style.transform = '';
      rail.removeAttribute('data-bdc-rail-following');
    }

    function update() {
      ticking = false;

      if (window.innerWidth <= 1040) {
        resetForFlow();
        return;
      }

      var pageY = window.scrollY || window.pageYOffset || 0;
      var slotRect = slot.getBoundingClientRect();
      var slotTop = slotRect.top + pageY;
      var slotHeight = slot.offsetHeight;
      var railHeight = rail.offsetHeight;
      var max = Math.max(0, slotHeight - railHeight);
      var desired = pageY + topOffset() - slotTop;
      var y = Math.max(0, Math.min(max, desired));

      rail.style.transform = 'translate3d(0,' + Math.round(y) + 'px,0)';
      rail.setAttribute('data-bdc-rail-following', y > 0 ? 'true' : 'false');
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
    document.addEventListener('DOMContentLoaded', initReaderRail);
  } else {
    initReaderRail();
  }
})();
