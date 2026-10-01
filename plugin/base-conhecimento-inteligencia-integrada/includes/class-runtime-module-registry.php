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
 * declarada pelo bootstrap.
 */
final class Runtime_Module_Registry {

	/**
	 * Retorna a definição canônica dos módulos de produto.
	 *
	 * @return array<string,array{flag:string,files:array<int,string>,register:array<int,string>}>
	 */
	public static function definitions(): array {
		$definitions = array();

		$definitions['search'] = self::module(
			'BDC_KB_SPEC005_G530_SEARCH_ENGINE_BUILD',
			array(
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
			array(
				Search_Lifecycle::class,
				Search_Anchor_Manager::class,
			)
		);

		$definitions['public_experience_preview'] = self::module(
			'BDC_KB_PUBLIC_EXPERIENCE_PREVIEW_BUILD',
			array(
				'includes/class-public-search-facade.php',
				'includes/class-public-navigation.php',
				'includes/class-public-auth-bridge.php',
				'includes/class-public-home-read-model.php',
				'includes/class-public-article-read-model.php',
				'includes/class-public-article-content.php',
				'includes/class-public-experience.php',
			),
			array(
				Public_Search_Facade::class,
				Public_Experience::class,
			)
		);

		$definitions['word_cloud'] = self::module(
			'BDC_KB_WORD_CLOUD_BUILD',
			array(
				'includes/class-word-cloud-contract.php',
				'includes/class-word-cloud-quality.php',
				'includes/class-word-cloud-service.php',
				'includes/class-word-cloud-consultations.php',
				'includes/class-word-cloud-admin.php',
			),
			array(
				Word_Cloud_Service::class,
				Word_Cloud_Consultations::class,
				Word_Cloud_Admin::class,
			)
		);

		return $definitions;
	}

	/**
	 * Informa se um módulo está habilitado pela flag canônica.
	 *
	 * @param string $module Nome do módulo.
	 * @return bool
	 * @throws \InvalidArgumentException Quando o módulo não é conhecido.
	 */
	public static function is_enabled( string $module ): bool {
		$definition = self::definition( $module );
		$flag       = $definition['flag'];

		return defined( $flag ) && true === constant( $flag );
	}

	/**
	 * Carrega os arquivos declarados de um módulo habilitado.
	 *
	 * @param string $module Nome do módulo.
	 * @throws \RuntimeException Quando um arquivo obrigatório não existe.
	 */
	public static function load( string $module ): void {
		if ( ! self::is_enabled( $module ) ) {
			return;
		}

		$definition = self::definition( $module );
		foreach ( $definition['files'] as $relative_path ) {
			$path = BDC_KB_DIR . $relative_path;
			if ( ! is_file( $path ) ) {
				throw new \RuntimeException(
					sprintf(
						'Arquivo obrigatório do módulo ausente: %s',
						esc_html( $relative_path )
					)
				);
			}

			require_once $path;
		}
	}

	/**
	 * Registra entrypoints de um módulo já carregado.
	 *
	 * @param string $module Nome do módulo.
	 * @throws \RuntimeException Quando o entrypoint não é registrável.
	 */
	public static function register( string $module ): void {
		if ( ! self::is_enabled( $module ) ) {
			return;
		}

		$definition = self::definition( $module );
		foreach ( $definition['register'] as $class_name ) {
			if ( ! class_exists( $class_name ) || ! is_callable( array( $class_name, 'register' ) ) ) {
				throw new \RuntimeException(
					sprintf(
						'Entrypoint inválido do módulo: %s',
						esc_html( $class_name )
					)
				);
			}

			$class_name::register();
		}
	}

	/**
	 * Carrega e registra um módulo habilitado.
	 *
	 * @param string $module Nome do módulo.
	 * @throws \InvalidArgumentException Quando o módulo não é conhecido.
	 * @throws \RuntimeException Quando load/register falha.
	 */
	public static function boot( string $module ): void {
		self::load( $module );
		self::register( $module );
	}

	/**
	 * Retorna a definição de um módulo.
	 *
	 * @param string $module Nome do módulo.
	 * @return array{flag:string,files:array<int,string>,register:array<int,string>}
	 * @throws \InvalidArgumentException Quando o módulo não é conhecido.
	 */
	private static function definition( string $module ): array {
		$definitions = self::definitions();
		if ( ! isset( $definitions[ $module ] ) ) {
			throw new \InvalidArgumentException(
				sprintf(
					'Módulo de runtime desconhecido: %s',
					esc_html( $module )
				)
			);
		}

		return $definitions[ $module ];
	}

	/**
	 * Constrói uma definição de módulo sem abstração adicional.
	 *
	 * @param string            $flag     Flag canônica.
	 * @param array<int,string> $files    Arquivos do módulo.
	 * @param array<int,string> $register Entrypoints registráveis.
	 * @return array{flag:string,files:array<int,string>,register:array<int,string>}
	 */
	private static function module( string $flag, array $files, array $register ): array {
		return array(
			'flag'     => $flag,
			'files'    => $files,
			'register' => $register,
		);
	}
}
