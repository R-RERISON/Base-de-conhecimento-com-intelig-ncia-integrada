<?php
/**
 * P-580A / UX-004 / UX-005 public experience inventory runner.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Public_Experience_Inventory_Runner_P580 {

	public const ACTION = 'bdc_kb_p580_public_experience_inventory';
	public const PAGE_SLUG = 'bdc-kb-p580-public-inventory';

	/** @var array<int,string> */
	private const HOME_SHORTCODES = array(
		'bc_home_config',
		'bc_ultimas',
		'bc_populares',
		'asi_search_form',
		'bdc_word_cloud',
		'bdc_entra_login',
	);

	/** @var array<int,string> */
	private const PUBLIC_HOOKS = array(
		'the_content',
		'wp_footer',
		'template_include',
		'single_template',
		'template_redirect',
		'wp_ajax_bdc_home_filter_v270',
		'wp_ajax_nopriv_bdc_home_filter_v270',
	);

	/** @var array<string,string> */
	private const GRE_META = array(
		'objective'        => '_bdc_es_objective',
		'responsible_team' => '_bdc_es_responsible_team',
		'catalog_item'     => '_bdc_es_catalog_item',
		'affected_service' => '_bdc_es_affected_service',
		'systems_involved' => '_bdc_es_systems_involved',
		'target_audience'  => '_bdc_es_target_audience',
		'escalation'       => '_bdc_es_escalation',
		'important'        => '_bdc_es_important',
	);

	private const NONCE_ACTION = 'bdc_kb_p580_public_experience_inventory';
	private const NONCE_FIELD  = 'bdc_kb_p580_public_experience_inventory_nonce';
	private const SAMPLE_LIMIT = 12;
	private const SOURCE_SAMPLE_LIMIT = 8;
	private const CORPUS_LIMIT = 1000;

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_page' ), 49 );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_run' ) );
	}

	public static function register_page(): void {
		add_submenu_page(
			Admin_Page::PAGE_SLUG,
			'Inventário Público P-580',
			'Inventário Público',
			'manage_options',
			self::PAGE_SLUG,
			array( self::class, 'render_page' )
		);
	}

	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permissão insuficiente.', 'bdc-knowledge-base' ), '', array( 'response' => 403 ) );
		}

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'P-580 — Inventário da Experiência Pública', 'bdc-knowledge-base' ) . '</h1>';
		echo '<p>' . esc_html__( 'Diagnóstico somente leitura para Home, Article Reader, shortcodes, hooks, tema/CSS, Dicas úteis e cobertura GRE. Nenhum conteúdo ou valor de metadado é exportado.', 'bdc-knowledge-base' ) . '</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		submit_button( __( 'Executar inventário e baixar JSON', 'bdc-knowledge-base' ), 'primary' );
		echo '</form>';
		echo '</div>';
	}

	public static function handle_run(): void {
		if ( 'POST' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
			wp_die( esc_html__( 'Método HTTP não permitido.', 'bdc-knowledge-base' ), '', array( 'response' => 405 ) );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permissão insuficiente.', 'bdc-knowledge-base' ), '', array( 'response' => 403 ) );
		}

		$nonce = isset( $_POST[ self::NONCE_FIELD ] ) && is_scalar( $_POST[ self::NONCE_FIELD ] )
			? wp_unslash( (string) $_POST[ self::NONCE_FIELD ] )
			: '';
		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			wp_die( esc_html__( 'Nonce inválido ou expirado.', 'bdc-knowledge-base' ), '', array( 'response' => 403 ) );
		}

		$report = self::run();
		$json = wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		if ( ! is_string( $json ) ) {
			wp_die( esc_html__( 'Falha ao serializar inventário.', 'bdc-knowledge-base' ), '', array( 'response' => 500 ) );
		}

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="bdc-kb-p580-public-experience-inventory-' . gmdate( 'Ymd-His' ) . '.json"' );
		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON download.
		exit;
	}

	/** @return array<string,mixed> */
	public static function run(): array {
		$started = microtime( true );
		$errors = array();
		$throwables = array();

		try {
			$theme = self::theme_inventory();
		} catch ( \Throwable $error ) {
			$theme = array();
			$throwables[] = self::throwable_row( 'theme_inventory', $error );
		}

		try {
			$shortcodes = self::shortcode_registry();
		} catch ( \Throwable $error ) {
			$shortcodes = array();
			$throwables[] = self::throwable_row( 'shortcode_registry', $error );
		}

		try {
			$hooks = self::hook_registry();
		} catch ( \Throwable $error ) {
			$hooks = array();
			$throwables[] = self::throwable_row( 'hook_registry', $error );
		}

		try {
			$surfaces = self::surface_inventory();
		} catch ( \Throwable $error ) {
			$surfaces = array();
			$throwables[] = self::throwable_row( 'surface_inventory', $error );
		}

		try {
			$corpus = self::corpus_inventory();
		} catch ( \Throwable $error ) {
			$corpus = array();
			$throwables[] = self::throwable_row( 'corpus_inventory', $error );
		}

		try {
			$plugin_inventory = self::plugin_inventory();
		} catch ( \Throwable $error ) {
			$plugin_inventory = array();
			$throwables[] = self::throwable_row( 'plugin_inventory', $error );
		}

		try {
			$snippet_inventory = self::code_snippets_inventory();
		} catch ( \Throwable $error ) {
			$snippet_inventory = array();
			$throwables[] = self::throwable_row( 'code_snippets_inventory', $error );
		}

		try {
			$helpful_tips_profile = self::helpful_tips_profile();
		} catch ( \Throwable $error ) {
			$helpful_tips_profile = array();
			$throwables[] = self::throwable_row( 'helpful_tips_profile', $error );
		}

		return array(
			'schema_version' => '1.1.0',
			'mode' => 'p580_public_experience_inventory',
			'generated_at' => gmdate( 'c' ),
			'environment' => array(
				'wordpress' => get_bloginfo( 'version' ),
				'php' => PHP_VERSION,
				'plugin' => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : '',
				'multisite' => is_multisite(),
				'db_server' => self::db_version(),
			),
			'front_page_settings' => self::front_page_settings(),
			'theme' => $theme,
			'plugin_inventory' => $plugin_inventory,
			'code_snippets_inventory' => $snippet_inventory,
			'helpful_tips_profile' => $helpful_tips_profile,
			'target_shortcodes' => $shortcodes,
			'public_hooks' => $hooks,
			'public_surfaces' => $surfaces,
			'corpus' => $corpus,
			'safety' => array(
				'read_only' => true,
				'writes_post_content' => false,
				'writes_post_meta' => false,
				'writes_elementor_data' => false,
				'executes_shortcodes' => false,
				'applies_the_content_filter' => false,
				'exports_editorial_content' => false,
				'exports_gre_values' => false,
				'exports_custom_css' => false,
				'calls_external_network' => false,
			),
			'errors' => $errors,
			'throwables' => $throwables,
			'runner' => array(
				'total_runtime_ms' => round( ( microtime( true ) - $started ) * 1000, 4 ),
				'peak_memory_bytes' => memory_get_peak_usage( true ),
			),
			'next_action' => 'Use this artifact to close H-001/A-001 ownership and corpus discovery. Do not decommission ASI/GRE from this report alone.',
		);
	}

	/** @return array<string,mixed> */
	private static function plugin_inventory(): array {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugins = function_exists( 'get_plugins' ) ? get_plugins() : array();
		$targets = array(
			'code_snippets' => array( 'code snippets' ),
			'gre' => array( 'gerenciador de resumo executivo', 'resumo executivo' ),
			'gac' => array( 'gac', 'acompanhamento' ),
			'wp_unified_indexer' => array( 'unified indexer', 'wp unified indexer' ),
			'entra_gateway' => array( 'entra', 'authentication gateway' ),
		);
		$out = array();

		foreach ( $targets as $key => $needles ) {
			$out[ $key ] = array();
			foreach ( (array) $plugins as $basename => $headers ) {
				$name = strtolower( (string) ( $headers['Name'] ?? '' ) );
				$text_domain = strtolower( (string) ( $headers['TextDomain'] ?? '' ) );
				$haystack = $name . ' ' . $text_domain . ' ' . strtolower( (string) $basename );
				$matched = false;
				foreach ( $needles as $needle ) {
					if ( str_contains( $haystack, $needle ) ) {
						$matched = true;
						break;
					}
				}
				if ( ! $matched ) {
					continue;
				}

				$root = trailingslashit( WP_PLUGIN_DIR ) . dirname( (string) $basename );
				$files = array();
				if ( 'gre' === $key && is_dir( $root . '/includes' ) ) {
					$patterns = array(
						$root . '/includes/*helpful*tips*.php',
						$root . '/includes/class-frontend-renderer.php',
					);
					foreach ( $patterns as $pattern ) {
						foreach ( (array) glob( $pattern ) as $file ) {
							if ( ! is_file( $file ) ) { continue; }
							$relative = ltrim( str_replace( wp_normalize_path( $root ), '', wp_normalize_path( $file ) ), '/' );
							$files[ $relative ] = array(
								'bytes' => (int) filesize( $file ),
								'sha256' => hash_file( 'sha256', $file ),
							);
						}
					}
					ksort( $files, SORT_STRING );
				}

				$out[ $key ][] = array(
					'basename' => (string) $basename,
					'name' => (string) ( $headers['Name'] ?? '' ),
					'version' => (string) ( $headers['Version'] ?? '' ),
					'text_domain' => (string) ( $headers['TextDomain'] ?? '' ),
					'active' => is_plugin_active( (string) $basename ),
					'files_of_interest' => $files,
				);
			}
		}
		return $out;
	}

	/** @return array<string,mixed> */
	private static function code_snippets_inventory(): array {
		global $wpdb;
		$table = $wpdb->prefix . 'snippets';
		$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table;
		if ( ! $table_exists ) {
			return array( 'table_present' => false, 'matches' => array(), 'code_exported' => false );
		}
		$columns_raw = $wpdb->get_results( 'SHOW COLUMNS FROM ' . esc_sql( $table ), ARRAY_A );
		$columns = array();
		foreach ( (array) $columns_raw as $column ) {
			$field = (string) ( $column['Field'] ?? '' );
			if ( '' !== $field ) { $columns[] = $field; }
		}
		$select = array_values( array_intersect( array( 'id', 'name', 'scope', 'priority', 'active', 'code' ), $columns ) );
		if ( ! in_array( 'code', $select, true ) ) {
			return array( 'table_present' => true, 'columns' => $columns, 'matches' => array(), 'code_exported' => false, 'warning' => 'CODE_COLUMN_NOT_AVAILABLE' );
		}
		$sql = 'SELECT ' . implode( ', ', array_map( static fn ( string $field ): string => esc_sql( $field ), $select ) ) . ' FROM ' . esc_sql( $table );
		$rows = $wpdb->get_results( $sql, ARRAY_A );
		$symbols = array( 'bdc_home_v270_config_shortcode', 'bdc_home_v270_ultimas_shortcode', 'bdc_home_v270_populares_shortcode', 'bdc_home_v270_ajax_filter' );
		$matches = array();
		foreach ( (array) $rows as $row ) {
			$code = isset( $row['code'] ) && is_string( $row['code'] ) ? $row['code'] : '';
			$found = array();
			foreach ( $symbols as $symbol ) { if ( str_contains( $code, $symbol ) ) { $found[] = $symbol; } }
			if ( empty( $found ) ) { continue; }
			$matches[] = array(
				'id' => isset( $row['id'] ) ? (int) $row['id'] : 0,
				'name' => isset( $row['name'] ) ? (string) $row['name'] : '',
				'scope' => isset( $row['scope'] ) ? (string) $row['scope'] : '',
				'priority' => isset( $row['priority'] ) ? (int) $row['priority'] : null,
				'active' => isset( $row['active'] ) ? (bool) $row['active'] : null,
				'code_bytes' => strlen( $code ), 'code_sha256' => hash( 'sha256', $code ),
				'matched_symbols' => $found, 'behavioral_signals' => self::snippet_behavioral_signals( $code ),
			);
		}
		return array( 'table_present' => true, 'columns' => $columns, 'matches' => $matches, 'code_exported' => false );
	}

	/** @return array<string,bool> */
	private static function snippet_behavioral_signals( string $code ): array {
		$lower = strtolower( $code );
		return array(
			'uses_wp_query' => str_contains( $code, 'WP_Query' ), 'uses_get_posts' => str_contains( $code, 'get_posts(' ),
			'uses_get_terms' => str_contains( $code, 'get_terms(' ) || str_contains( $code, 'get_categories(' ),
			'uses_post_meta' => str_contains( $code, 'get_post_meta(' ), 'uses_comment_count' => str_contains( $lower, 'comment_count' ),
			'uses_orderby_date' => str_contains( $lower, "'orderby' => 'date'" ) || str_contains( $lower, 'orderby=date' ),
			'uses_orderby_modified' => str_contains( $lower, 'modified' ), 'uses_orderby_meta' => str_contains( $lower, 'meta_value' ) || str_contains( $lower, 'meta_key' ),
			'uses_tax_query' => str_contains( $lower, 'tax_query' ), 'uses_category' => str_contains( $lower, 'category' ),
			'uses_nonce' => str_contains( $code, 'check_ajax_referer(' ) || str_contains( $code, 'wp_verify_nonce(' ),
			'uses_capability_check' => str_contains( $code, 'current_user_can(' ), 'uses_json_response' => str_contains( $code, 'wp_send_json_' ),
		);
	}

	/** @return array<string,mixed> */
	private static function helpful_tips_profile(): array {
		$key = '_bdc_es_helpful_tips';
		$ids = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => $key, 'meta_compare' => 'EXISTS', 'orderby' => 'ID', 'order' => 'ASC', 'no_found_rows' => true, 'suppress_filters' => false ) );
		$rows = array(); $union_item_keys = array(); $item_key_types = array(); $item_counts = array();
		foreach ( (array) $ids as $raw_id ) {
			$post_id = absint( $raw_id ); $value = get_post_meta( $post_id, $key, true ); $shape = self::value_shape( $value );
			$item_counts[] = (int) ( $shape['item_count'] ?? 0 );
			foreach ( (array) ( $shape['item_keys'] ?? array() ) as $item_key ) { $union_item_keys[ (string) $item_key ] = true; }
			foreach ( (array) ( $shape['item_key_types'] ?? array() ) as $item_key => $types ) {
				if ( ! isset( $item_key_types[ $item_key ] ) ) { $item_key_types[ $item_key ] = array(); }
				foreach ( (array) $types as $type ) { $item_key_types[ $item_key ][ (string) $type ] = true; }
			}
			$rows[] = array( 'post_id' => $post_id, 'value_type' => gettype( $value ), 'json_bytes' => self::json_bytes( $value ), 'shape' => $shape );
		}
		$types_out = array();
		foreach ( $item_key_types as $item_key => $types ) { $types_out[ $item_key ] = array_keys( $types ); sort( $types_out[ $item_key ], SORT_STRING ); }
		ksort( $types_out, SORT_STRING );
		return array( 'meta_key' => $key, 'post_count' => count( $rows ), 'posts' => $rows, 'union_item_keys' => array_keys( $union_item_keys ), 'item_key_types' => $types_out, 'item_count_min' => empty( $item_counts ) ? 0 : min( $item_counts ), 'item_count_max' => empty( $item_counts ) ? 0 : max( $item_counts ), 'values_exported' => false );
	}

	/** @return array<string,mixed> */
	private static function value_shape( mixed $value ): array {
		if ( is_array( $value ) ) {
			$is_list = array_is_list( $value ); $item_keys = array(); $item_key_types = array();
			if ( $is_list ) {
				foreach ( $value as $item ) {
					if ( ! is_array( $item ) ) { continue; }
					foreach ( $item as $key => $item_value ) {
						$item_keys[ (string) $key ] = true;
						if ( ! isset( $item_key_types[ (string) $key ] ) ) { $item_key_types[ (string) $key ] = array(); }
						$item_key_types[ (string) $key ][ gettype( $item_value ) ] = true;
					}
				}
			}
			$types = array(); foreach ( $item_key_types as $key => $set ) { $types[ $key ] = array_keys( $set ); }
			return array( 'kind' => $is_list ? 'list' : 'associative_array', 'item_count' => count( $value ), 'item_keys' => array_keys( $item_keys ), 'item_key_types' => $types );
		}
		if ( is_string( $value ) ) {
			$decoded = json_decode( $value, true ); $json_ok = JSON_ERROR_NONE === json_last_error();
			return array( 'kind' => 'string', 'item_count' => 0, 'json_decodable' => $json_ok, 'json_decoded_type' => $json_ok ? gettype( $decoded ) : '', 'item_keys' => array(), 'item_key_types' => array() );
		}
		return array( 'kind' => gettype( $value ), 'item_count' => 0, 'item_keys' => array(), 'item_key_types' => array() );
	}

	private static function json_bytes( mixed $value ): int {
		$json = wp_json_encode( $value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		return is_string( $json ) ? strlen( $json ) : 0;
	}

	/** @return array<string,mixed> */
	private static function front_page_settings(): array {
		$front_id = absint( get_option( 'page_on_front', 0 ) );
		$posts_id = absint( get_option( 'page_for_posts', 0 ) );

		return array(
			'show_on_front' => (string) get_option( 'show_on_front', 'posts' ),
			'page_on_front' => self::post_identity( $front_id ),
			'page_for_posts' => self::post_identity( $posts_id ),
		);
	}

	/** @return array<string,mixed> */
	private static function theme_inventory(): array {
		$stylesheet = (string) get_stylesheet();
		$template = (string) get_template();
		$theme = wp_get_theme( $stylesheet );
		$parent = $theme->parent();
		$css = function_exists( 'wp_get_custom_css' ) ? (string) wp_get_custom_css( $stylesheet ) : '';

		return array(
			'stylesheet' => $stylesheet,
			'template' => $template,
			'is_child_theme' => $stylesheet !== $template,
			'theme_name' => (string) $theme->get( 'Name' ),
			'theme_version' => (string) $theme->get( 'Version' ),
			'parent_name' => $parent instanceof \WP_Theme ? (string) $parent->get( 'Name' ) : '',
			'parent_version' => $parent instanceof \WP_Theme ? (string) $parent->get( 'Version' ) : '',
			'custom_css' => array(
				'present' => '' !== trim( $css ),
				'bytes' => strlen( $css ),
				'sha256' => hash( 'sha256', $css ),
				'content_exported' => false,
			),
		);
	}

	/** @return array<string,array<string,mixed>> */
	private static function shortcode_registry(): array {
		global $shortcode_tags;
		$out = array();

		foreach ( self::HOME_SHORTCODES as $tag ) {
			$registered = is_array( $shortcode_tags ) && isset( $shortcode_tags[ $tag ] );
			$out[ $tag ] = array(
				'registered' => $registered,
				'callback' => $registered ? self::describe_callable( $shortcode_tags[ $tag ] ) : null,
			);
		}
		return $out;
	}

	/** @return array<string,array<int,array<string,mixed>>> */
	private static function hook_registry(): array {
		global $wp_filter;
		$out = array();

		foreach ( self::PUBLIC_HOOKS as $hook_name ) {
			$rows = array();
			$hook = is_array( $wp_filter ) ? ( $wp_filter[ $hook_name ] ?? null ) : null;
			if ( $hook instanceof \WP_Hook && is_array( $hook->callbacks ) ) {
				foreach ( $hook->callbacks as $priority => $callbacks ) {
					foreach ( (array) $callbacks as $callback ) {
						$function = is_array( $callback ) ? ( $callback['function'] ?? null ) : null;
						if ( null === $function ) {
							continue;
						}
						$row = self::describe_callable( $function );
						$row['priority'] = (int) $priority;
						$row['accepted_args'] = is_array( $callback ) ? (int) ( $callback['accepted_args'] ?? 0 ) : 0;
						$rows[] = $row;
					}
				}
			}
			$out[ $hook_name ] = $rows;
		}
		return $out;
	}

	/** @return array<string,mixed> */
	private static function surface_inventory(): array {
		$statuses = array( 'publish', 'private', 'draft', 'pending', 'future' );
		$ids = get_posts(
			array(
				'post_type' => array( 'post', 'page' ),
				'post_status' => $statuses,
				'posts_per_page' => -1,
				'fields' => 'ids',
				'orderby' => 'ID',
				'order' => 'ASC',
				'no_found_rows' => true,
				'suppress_filters' => false,
			)
		);

		$candidates = array();
		foreach ( (array) $ids as $raw_id ) {
			$post_id = absint( $raw_id );
			$post = get_post( $post_id );
			if ( ! is_object( $post ) ) {
				continue;
			}
			$content = (string) ( $post->post_content ?? '' );
			$found = array();
			foreach ( self::HOME_SHORTCODES as $tag ) {
				$count = preg_match_all( '/\[\/?\s*' . preg_quote( $tag, '/' ) . '\b/i', $content );
				if ( false !== $count && $count > 0 ) {
					$found[ $tag ] = (int) $count;
				}
			}
			if ( empty( $found ) ) {
				continue;
			}
			$candidates[] = array(
				'post_id' => $post_id,
				'post_type' => (string) ( $post->post_type ?? '' ),
				'post_status' => (string) ( $post->post_status ?? '' ),
				'title' => (string) ( $post->post_title ?? '' ),
				'template_slug' => (string) get_page_template_slug( $post_id ),
				'source_kind' => self::surface_source_kind( $post_id, (string) ( $post->post_type ?? '' ), $content ),
				'shortcodes' => $found,
			);
		}

		return array(
			'candidates_count' => count( $candidates ),
			'candidates' => array_slice( $candidates, 0, 20 ),
			'truncated' => count( $candidates ) > 20,
		);
	}

	/** @return array<string,mixed> */
	private static function corpus_inventory(): array {
		$ids = get_posts(
			array(
				'post_type' => 'post',
				'post_status' => 'publish',
				'posts_per_page' => self::CORPUS_LIMIT,
				'fields' => 'ids',
				'orderby' => 'ID',
				'order' => 'ASC',
				'no_found_rows' => true,
				'suppress_filters' => false,
			)
		);

		$found_total = 0;
		$counts = array(
			'gutenberg' => 0,
			'legacy_html' => 0,
			'plain_text' => 0,
			'elementor' => 0,
			'mixed' => 0,
			'empty' => 0,
			'error' => 0,
		);
		$samples = array();
		$warnings = array();
		$tips = array(
			'meta_keys' => array(),
			'post_content' => array( 'count' => 0, 'sample_post_ids' => array() ),
			'elementor_data' => array( 'count' => 0, 'sample_post_ids' => array() ),
		);
		$gre = array();
		foreach ( self::GRE_META as $field => $meta_key ) {
			$gre[ $field ] = array(
				'meta_key' => $meta_key,
				'non_empty_posts' => 0,
				'sample_post_ids' => array(),
			);
		}

		foreach ( (array) $ids as $raw_id ) {
			$post_id = absint( $raw_id );
			if ( $post_id <= 0 ) {
				continue;
			}
			++$found_total;

			$extraction = Content_Extractor::extract( $post_id );
			if ( $extraction instanceof \WP_Error ) {
				++$counts['error'];
				self::append_sample( $samples, 'error', $post_id, self::SOURCE_SAMPLE_LIMIT );
			} else {
				$kind = (string) ( $extraction['source_kind'] ?? 'error' );
				if ( ! isset( $counts[ $kind ] ) ) {
					$kind = 'error';
				}
				++$counts[ $kind ];
				self::append_sample( $samples, $kind, $post_id, self::SOURCE_SAMPLE_LIMIT );
				foreach ( (array) ( $extraction['warnings'] ?? array() ) as $warning ) {
					$key = self::warning_family( (string) $warning );
					$warnings[ $key ] = (int) ( $warnings[ $key ] ?? 0 ) + 1;
				}
			}

			$post = get_post( $post_id );
			$content = is_object( $post ) ? (string) ( $post->post_content ?? '' ) : '';
			if ( self::contains_tips_marker( $content ) ) {
				++$tips['post_content']['count'];
				self::append_flat_sample( $tips['post_content']['sample_post_ids'], $post_id, self::SAMPLE_LIMIT );
			}

			$all_meta = get_post_meta( $post_id );
			foreach ( array_keys( (array) $all_meta ) as $meta_key ) {
				$meta_key = (string) $meta_key;
				if ( preg_match( '/dica|tip|util|useful/i', $meta_key ) ) {
					if ( ! isset( $tips['meta_keys'][ $meta_key ] ) ) {
						$tips['meta_keys'][ $meta_key ] = array( 'posts' => 0, 'sample_post_ids' => array() );
					}
					++$tips['meta_keys'][ $meta_key ]['posts'];
					self::append_flat_sample( $tips['meta_keys'][ $meta_key ]['sample_post_ids'], $post_id, self::SAMPLE_LIMIT );
				}
			}

			$elementor_raw = $all_meta['_elementor_data'][0] ?? '';
			if ( is_string( $elementor_raw ) && self::contains_tips_marker( $elementor_raw ) ) {
				++$tips['elementor_data']['count'];
				self::append_flat_sample( $tips['elementor_data']['sample_post_ids'], $post_id, self::SAMPLE_LIMIT );
			}

			foreach ( self::GRE_META as $field => $meta_key ) {
				$value = $all_meta[ $meta_key ][0] ?? '';
				if ( is_scalar( $value ) && '' !== trim( (string) $value ) ) {
					++$gre[ $field ]['non_empty_posts'];
					self::append_flat_sample( $gre[ $field ]['sample_post_ids'], $post_id, self::SOURCE_SAMPLE_LIMIT );
				}
			}
		}

		ksort( $warnings, SORT_STRING );
		ksort( $tips['meta_keys'], SORT_STRING );

		return array(
			'published_posts_scanned' => $found_total,
			'limit' => self::CORPUS_LIMIT,
			'possibly_truncated' => count( (array) $ids ) >= self::CORPUS_LIMIT,
			'source_kind_counts' => $counts,
			'source_kind_sample_post_ids' => $samples,
			'warning_families' => $warnings,
			'structured_tips_discovery' => $tips,
			'gre_meta_coverage' => $gre,
		);
	}

	private static function contains_tips_marker( string $value ): bool {
		if ( '' === $value ) {
			return false;
		}
		$normalized = remove_accents( strtolower( $value ) );
		return str_contains( $normalized, 'dicas uteis' )
			|| str_contains( $normalized, 'bdc-tips' )
			|| str_contains( $normalized, 'bdc_tip' )
			|| str_contains( $normalized, 'bdc-tip' );
	}

	private static function warning_family( string $warning ): string {
		if ( str_contains( $warning, ':' ) ) {
			$parts = explode( ':', $warning, 2 );
			return (string) $parts[0];
		}
		return $warning;
	}

	/** @param array<string,array<int,int>> $samples */
	private static function append_sample( array &$samples, string $key, int $post_id, int $limit ): void {
		if ( ! isset( $samples[ $key ] ) ) {
			$samples[ $key ] = array();
		}
		self::append_flat_sample( $samples[ $key ], $post_id, $limit );
	}

	/** @param array<int,int> $samples */
	private static function append_flat_sample( array &$samples, int $post_id, int $limit ): void {
		if ( count( $samples ) >= $limit || in_array( $post_id, $samples, true ) ) {
			return;
		}
		$samples[] = $post_id;
	}

	/** @return array<string,mixed> */
	private static function post_identity( int $post_id ): array {
		if ( $post_id <= 0 ) {
			return array( 'post_id' => 0, 'exists' => false );
		}
		$post = get_post( $post_id );
		if ( ! is_object( $post ) ) {
			return array( 'post_id' => $post_id, 'exists' => false );
		}
		return array(
			'post_id' => $post_id,
			'exists' => true,
			'post_type' => (string) ( $post->post_type ?? '' ),
			'post_status' => (string) ( $post->post_status ?? '' ),
			'title' => (string) ( $post->post_title ?? '' ),
			'template_slug' => (string) get_page_template_slug( $post_id ),
		);
	}

	private static function surface_source_kind( int $post_id, string $post_type, string $content ): string {
		if ( 'post' === $post_type ) {
			$extraction = Content_Extractor::extract( $post_id );
			if ( ! ( $extraction instanceof \WP_Error ) ) {
				return (string) ( $extraction['source_kind'] ?? 'unknown' );
			}
		}
		$elementor = get_post_meta( $post_id, '_elementor_data', true );
		$has_elementor = ( is_string( $elementor ) && '' !== trim( $elementor ) ) || ( is_array( $elementor ) && ! empty( $elementor ) );
		$has_blocks = function_exists( 'has_blocks' ) ? has_blocks( $content ) : str_contains( $content, '<!-- wp:' );
		if ( $has_elementor && $has_blocks ) {
			return 'mixed';
		}
		if ( $has_elementor ) {
			return 'elementor';
		}
		if ( $has_blocks ) {
			return 'gutenberg';
		}
		if ( 1 === preg_match( '/<\s*[a-z][^>]*>/i', $content ) ) {
			return 'legacy_html';
		}
		return '' === trim( wp_strip_all_tags( $content ) ) ? 'empty' : 'plain_text';
	}

	/** @return array<string,mixed> */
	private static function describe_callable( mixed $callback ): array {
		$type = 'unknown';
		$name = '';
		$file = '';

		try {
			if ( $callback instanceof \Closure ) {
				$type = 'closure';
				$name = 'Closure';
				$reflection = new \ReflectionFunction( $callback );
				$resolved = $reflection->getFileName();
				$file = is_string( $resolved ) ? $resolved : '';
			} elseif ( is_string( $callback ) && function_exists( $callback ) ) {
				$type = 'function';
				$name = $callback;
				$reflection = new \ReflectionFunction( $callback );
				$resolved = $reflection->getFileName();
				$file = is_string( $resolved ) ? $resolved : '';
			} elseif ( is_array( $callback ) && 2 === count( $callback ) ) {
				$owner = $callback[0];
				$method = is_string( $callback[1] ) ? $callback[1] : '';
				$class = is_object( $owner ) ? get_class( $owner ) : ( is_string( $owner ) ? $owner : '' );
				$type = 'method';
				$name = $class . '::' . $method;
				if ( '' !== $class && '' !== $method && method_exists( $class, $method ) ) {
					$reflection = new \ReflectionMethod( $class, $method );
					$resolved = $reflection->getFileName();
					$file = is_string( $resolved ) ? $resolved : '';
				}
			} elseif ( is_object( $callback ) && method_exists( $callback, '__invoke' ) ) {
				$type = 'invokable';
				$name = get_class( $callback ) . '::__invoke';
				$reflection = new \ReflectionMethod( $callback, '__invoke' );
				$resolved = $reflection->getFileName();
				$file = is_string( $resolved ) ? $resolved : '';
			} elseif ( is_string( $callback ) ) {
				$type = 'string';
				$name = $callback;
			}
		} catch ( \Throwable $error ) {
			$type = 'reflection_error';
			$name = is_string( $callback ) ? $callback : $type;
		}

		$source = self::describe_source_file( $file );
		return array(
			'type' => $type,
			'callable' => $name,
			'source_scope' => $source['source_scope'],
			'source_path' => $source['source_path'],
		);
	}

	/** @return array{source_scope:string,source_path:string} */
	private static function describe_source_file( string $file ): array {
		if ( '' === $file ) {
			return array( 'source_scope' => 'internal_or_unknown', 'source_path' => '' );
		}

		$normalized = wp_normalize_path( $file );
		$roots = array(
			'bdc_plugin' => wp_normalize_path( BDC_KB_DIR ),
			'mu_plugin' => defined( 'WPMU_PLUGIN_DIR' ) ? trailingslashit( wp_normalize_path( WPMU_PLUGIN_DIR ) ) : '',
			'plugin' => defined( 'WP_PLUGIN_DIR' ) ? trailingslashit( wp_normalize_path( WP_PLUGIN_DIR ) ) : '',
			'theme' => trailingslashit( wp_normalize_path( get_theme_root() ) ),
			'wordpress_core' => trailingslashit( wp_normalize_path( ABSPATH ) ),
		);

		foreach ( $roots as $scope => $root ) {
			if ( '' === $root || ! str_starts_with( $normalized, $root ) ) {
				continue;
			}
			return array(
				'source_scope' => $scope,
				'source_path' => ltrim( substr( $normalized, strlen( $root ) ), '/' ),
			);
		}

		return array(
			'source_scope' => 'external_or_unknown',
			'source_path' => basename( $normalized ),
		);
	}

	/** @return array<string,string> */
	private static function throwable_row( string $phase, \Throwable $error ): array {
		return array(
			'phase' => $phase,
			'class' => get_class( $error ),
			'code' => (string) $error->getCode(),
			'message' => $error->getMessage(),
		);
	}

	private static function db_version(): string {
		global $wpdb;
		return method_exists( $wpdb, 'db_version' ) ? (string) $wpdb->db_version() : '';
	}
}