<?php
/**
 * Orquestração mínima do runtime da SPEC-001.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registra apenas os hooks necessários ao vertical slice autorizado.
 */
final class Plugin {

	public static function register(): void {
		add_action( 'init', array( Meta_Contract::class, 'register' ) );
		add_action( 'admin_menu', array( Admin_Page::class, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( Admin_Page::class, 'enqueue_assets' ) );
		add_action( 'admin_post_' . Admin_Page::ACTION, array( Admin_Page::class, 'handle_save' ) );

		if ( class_exists( Diagnostics_Runner::class ) ) {
			Diagnostics_Runner::register();
		}
	}
}
