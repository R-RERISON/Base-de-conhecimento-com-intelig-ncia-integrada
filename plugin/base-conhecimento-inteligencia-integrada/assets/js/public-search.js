(function () {
  'use strict';
  function target() {
    return document.querySelector('[data-bdc-primary-search]') || document.querySelector('[data-bdc-global-search]');
  }
  document.addEventListener('keydown', function (event) {
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
      var input = target();
      if (!input) return;
      event.preventDefault();
      input.focus();
      if (typeof input.select === 'function') input.select();
    }
  });
})();