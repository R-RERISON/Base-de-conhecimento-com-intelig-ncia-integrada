<?php
/**
 * Orquestração mínima do runtime da Base de Conhecimento.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registra somente os hooks permanentes dos slices autorizados.
 */
final class Plugin {

	public static function register(): void {
		add_action( 'init', array( Meta_Contract::class, 'register' ) );
		add_action( 'init', array( Classification_Contract::class, 'register' ) );
		add_action( 'admin_menu', array( Admin_Page::class, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( Admin_Page::class, 'enqueue_assets' ) );
		add_action( 'admin_post_' . Admin_Page::ACTION, array( Admin_Page::class, 'handle_save' ) );
		add_action( 'admin_post_' . Classification_Admin::ACTION, array( Classification_Admin::class, 'handle_save' ) );
		add_action( 'admin_post_' . Review_Admin::ACTION, array( Review_Admin::class, 'handle_save' ) );

		if ( defined( 'BDC_KB_REVIEW_HTTP_DIAGNOSTICS_BUILD' ) && BDC_KB_REVIEW_HTTP_DIAGNOSTICS_BUILD ) {
			Review_HTTP_Cache_Coherence::register();
			Review_HTTP_Diagnostics::register();
		}
	}
}
