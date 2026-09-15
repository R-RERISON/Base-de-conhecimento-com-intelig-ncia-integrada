(function () {
	'use strict';

	function moveFocus(tabs, currentIndex, direction) {
		var nextIndex = (currentIndex + direction + tabs.length) % tabs.length;
		tabs[nextIndex].focus();
	}

	document.addEventListener('keydown', function (event) {
		var current = event.target.closest ? event.target.closest('.bdc-kb-tab') : null;
		if (!current) {
			return;
		}

		var nav = current.closest('[data-bdc-workspace-tabs]');
		if (!nav) {
			return;
		}

		var tabs = Array.prototype.slice.call(nav.querySelectorAll('.bdc-kb-tab'));
		var index = tabs.indexOf(current);
		if (index < 0 || tabs.length < 2) {
			return;
		}

		if (event.key === 'ArrowRight') {
			event.preventDefault();
			moveFocus(tabs, index, 1);
		} else if (event.key === 'ArrowLeft') {
			event.preventDefault();
			moveFocus(tabs, index, -1);
		} else if (event.key === 'Home') {
			event.preventDefault();
			tabs[0].focus();
		} else if (event.key === 'End') {
			event.preventDefault();
			tabs[tabs.length - 1].focus();
		}
	});
}());
