<?php
/**
 * Hardening temporário do harness G-110.
 *
 * Evita colisão DOM entre submit_button() e HTMLFormElement::submit()
 * somente nos iframes criados pelo Browser Acceptance.
 * Remover no G-130.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Workspace_Browser_Submit_Shim {

	public static function register(): void {
		add_action( 'admin_enqueue_scripts', array( self::class, 'inject' ), 100 );
	}

	public static function inject( string $hook_suffix ): void {
		if ( 'toplevel_page_' . Admin_Page::PAGE_SLUG !== $hook_suffix ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( ! wp_script_is( 'bdc-kb-browser-acceptance', 'enqueued' ) ) {
			return;
		}

		$script = <<<'JS'
(function () {
	'use strict';
	var nativeCreateElement = document.createElement.bind(document);
	document.createElement = function (name, options) {
		var element = nativeCreateElement(name, options);
		if (String(name).toLowerCase() === 'iframe') {
			element.addEventListener('load', function () {
				try {
					var doc = element.contentDocument;
					if (!doc) { return; }
					Array.prototype.forEach.call(doc.querySelectorAll('form [name="submit"]'), function (control) {
						control.setAttribute('data-bdc-g110-original-name', 'submit');
						control.setAttribute('name', 'bdc_g110_submit');
					});
				} catch (error) {
					/* same-origin harness; ignore defensive access errors */
				}
			});
		}
		return element;
	};
}());
JS;

		wp_add_inline_script( 'bdc-kb-browser-acceptance', $script, 'before' );
	}
}
