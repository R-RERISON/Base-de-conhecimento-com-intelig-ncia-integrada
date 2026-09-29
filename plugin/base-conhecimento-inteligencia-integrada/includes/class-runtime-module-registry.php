<?php
/**
 * Registry explícito dos módulos opcionais de produto.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Descreve e carrega módulos opcionais sem discovery, reflexão ou estado externo.
 *
 * O registry não contém regra de domínio. Ele apenas materializa a composição
 * declarada pelo bootstrap e será conectado ao composition root em P640-03.
 */
final class Runtime_Module_Registry {

	/**
	 * @return array<string,array{flag:string,files:array<int,string>,register:array<int,string>}>
	 */
	public static function definitions(): array {
		return array(
			'search' => array(
				'flag' => 'BDC_KB_SPEC005_G530_SEARCH_ENGINE_BUILD',
				'files' => array(
					'includes/class-search-query-normalizer.php',
					'includes/class-r260-contextual-anchor-resolver.php',
					'includes/class-r260-structural-projector.php',
					'includes/class-search-section-projector.php',
					'includes/class-search-document-builder.php',
					'includes/class-search-projection-repository.php',
					'includes/class-search-rebuild-service.php',
					'includes/class-search-lifecycle.php',
					'includes/class-lexical-ranker.php',
					'includes/class-search-section-ranker.php',
					'includes/class-search-section-service.php',
					'includes/class-search-anchor-manager.php',
					'includes/class-search-service.php',
				),
				'register' => array(
					Search_Lifecycle::class,
					Search_Anchor_Manager::class,
				),
			),
			'public_experience_preview' => array(
				'flag' => 'BDC_KB_PUBLIC_EXPERIENCE_PREVIEW_BUILD',
				'files' => array(
					'includes/class-public-search-facade.php',
					'includes/class-public-navigation.php',
					'includes/class-public-auth-bridge.php',
					'includes/class-public-home-read-model.php',
					'includes/class-public-article-read-model.php',
					'includes/class-public-article-content.php',
					'includes/class-public-experience.php',
				),
				'register' => array(
					Public_Search_Facade::class,
					Public_Experience::class,
				),
			),
			'word_cloud' => array(
				'flag' => 'BDC_KB_WORD_CLOUD_BUILD',
				'files' => array(
					'includes/class-word-cloud-contract.php',
					'includes/class-word-cloud-quality.php',
					'includes/class-word-cloud-service.php',
					'includes/class-word-cloud-consultations.php',
					'includes/class-word-cloud-admin.php',
				),
				'register' => array(
					Word_Cloud_Service::class,
					Word_Cloud_Consultations::class,
					Word_Cloud_Admin::class,
				),
			),
		);
	}

	public static function is_enabled( string $module ): bool {
		$definition = self::definition( $module );
		$flag = $definition['flag'];

		return defined( $flag ) && true === constant( $flag );
	}

	/**
	 * Carrega os arquivos declarados de um módulo habilitado.
	 */
	public static function load( string $module ): void {
		if ( ! self::is_enabled( $module ) ) {
			return;
		}

		$definition = self::definition( $module );
		foreach ( $definition['files'] as $relative_path ) {
			$path = BDC_KB_DIR . $relative_path;
			if ( ! is_file( $path ) ) {
				throw new \RuntimeException( 'Arquivo obrigatório do módulo ausente: ' . $relative_path );
			}

			require_once $path;
		}
	}

	/**
	 * Registra entrypoints de um módulo já carregado.
	 */
	public static function register( string $module ): void {
		if ( ! self::is_enabled( $module ) ) {
			return;
		}

		$definition = self::definition( $module );
		foreach ( $definition['register'] as $class_name ) {
			if ( ! class_exists( $class_name ) || ! is_callable( array( $class_name, 'register' ) ) ) {
				throw new \RuntimeException( 'Entrypoint inválido do módulo: ' . $class_name );
			}

			$class_name::register();
		}
	}

	/**
	 * Carrega e registra um módulo habilitado.
	 */
	public static function boot( string $module ): void {
		self::load( $module );
		self::register( $module );
	}

	/**
	 * @return array{flag:string,files:array<int,string>,register:array<int,string>}
	 */
	private static function definition( string $module ): array {
		$definitions = self::definitions();
		if ( ! isset( $definitions[ $module ] ) ) {
			throw new \InvalidArgumentException( 'Módulo de runtime desconhecido: ' . $module );
		}

		return $definitions[ $module ];
	}
}
