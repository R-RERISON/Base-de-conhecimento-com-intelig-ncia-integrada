<?php
/**
 * Coerência de cache do runner HTTP temporário do Gate G-070.
 *
 * O runner executa o writer em outro request PHP via loopback. Após o request
 * remoto, o processo pai pode manter o last_changed de comments anterior e
 * reutilizar resultados de WP_Comment_Query já cacheados. Esta classe avança
 * apenas o marcador de cache no processo do runner para que a verificação
 * pós-HTTP releia a Comments API do estado persistido pelo request filho.
 *
 * Deve ser removida junto com o runner antes do RC.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Review_HTTP_Cache_Coherence {

	public static function register(): void {
		add_action( 'http_api_debug', array( self::class, 'refresh_after_review_loopback' ), 999, 5 );
	}

	/**
	 * Invalida somente o namespace de query de comentários depois de um
	 * wp_remote_* direcionado ao writer Review do próprio runner.
	 *
	 * @param mixed  $response    Resposta HTTP ou WP_Error.
	 * @param string $context     Contexto do transporte HTTP.
	 * @param string $class       Classe de transporte.
	 * @param array  $parsed_args Argumentos normalizados da HTTP API.
	 * @param string $url         URL chamada.
	 */
	public static function refresh_after_review_loopback( $response, string $context, string $class, array $parsed_args, string $url ): void {
		unset( $response, $class );

		if ( 'response' !== $context || ! self::is_review_writer_request( $parsed_args, $url ) ) {
			return;
		}

		if ( function_exists( 'wp_cache_set_comments_last_changed' ) ) {
			wp_cache_set_comments_last_changed();
		}
	}

	/**
	 * @param array<string,mixed> $parsed_args Argumentos da HTTP API.
	 */
	private static function is_review_writer_request( array $parsed_args, string $url ): bool {
		$admin_post = admin_url( 'admin-post.php' );
		if ( 0 !== strpos( $url, $admin_post ) ) {
			return false;
		}

		$body = $parsed_args['body'] ?? array();
		if ( is_array( $body ) ) {
			return Review_Admin::ACTION === (string) ( $body['action'] ?? '' );
		}

		if ( is_string( $body ) && '' !== $body ) {
			$params = array();
			parse_str( $body, $params );
			return Review_Admin::ACTION === (string) ( $params['action'] ?? '' );
		}

		return false;
	}
}
