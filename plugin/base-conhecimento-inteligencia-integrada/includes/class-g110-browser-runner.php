<?php
namespace BDC\KnowledgeBase;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class G110_Browser_Runner {
	public static function register(): void {
		add_action( 'admin_notices', array( self::class, 'render_notice' ) );
	}
	public static function render_notice(): void {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( (string) $_GET['page'] ) ) : '';
		if ( Admin_Page::PAGE_SLUG !== $page ) { return; }
		echo '<div class="notice notice-info"><p>G-110 Browser Acceptance disponível neste build de homologação.</p></div>';
	}
}
