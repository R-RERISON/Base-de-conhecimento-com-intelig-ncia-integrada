<?php
/**
 * Fundação visual compartilhada do produto BDC.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Visual_Foundation {

	private const HOOK_SUFFIX = 'toplevel_page_bdc-knowledge-summary';

	public static function register(): void {
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue' ), 20 );
	}

	public static function enqueue( string $hook_suffix ): void {
		if ( self::HOOK_SUFFIX !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style(
			'bdc-kb-visual-foundation',
			BDC_KB_URL . 'assets/css/visual-foundation.css',
			array( 'bdc-kb-history' ),
			BDC_KB_VERSION
		);
	}
}
