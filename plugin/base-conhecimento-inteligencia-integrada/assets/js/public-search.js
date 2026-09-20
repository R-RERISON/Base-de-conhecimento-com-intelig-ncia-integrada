(function () {
  'use strict';
  function searchTarget() {
    return document.querySelector('[data-bdc-primary-search]') || document.querySelector('[data-bdc-global-search]');
  }
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
    if (!toggle) return;
    var header = toggle.closest('[data-bdc-header]');
    if (!header) return;
    var open = header.classList.toggle('is-menu-open');
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    toggle.setAttribute('aria-label', open ? 'Fechar links rápidos' : 'Abrir links rápidos');
  });
})();
