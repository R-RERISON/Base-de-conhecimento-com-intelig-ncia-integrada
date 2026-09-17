<?php
/**
 * Gateway fail-closed para qualquer futura mutação Elementor — SPEC-004 / G-245 / T082.
 *
 * T082 não implementa writer. Ele apenas centraliza a avaliação de compatibilidade,
 * capability, feature flag e autorização de fase para impedir escrita acidental.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Elementor_Gateway {

	public const CONTRACT_VERSION              = '1.0.0';
	public const MATRIX_VERSION                = 'g245-compatibility-matrix-v1';
	public const HOMOLOGATED_ELEMENTOR_VERSION = '4.1.0';
	public const WRITER_FEATURE_FLAG           = 'BDC_KB_ELEMENTOR_WRITER_ENABLED';
	public const REQUIRED_CAPABILITY           = 'manage_options';
	public const WRITER_ACTION                 = 'bdc_kb_elementor_write';
	public const NONCE_FIELD                   = '_bdc_kb_elementor_nonce';
	public const NONCE_ACTION_PREFIX           = 'bdc_kb_elementor_write_';
	public const SOURCE_HASH_FIELD             = 'source_hash_before';

	/**
	 * Hard gate da fase T082.
	 *
	 * Mesmo que versão, capability e feature flag estejam válidas, o writer permanece
	 * proibido até uma mudança de código posterior, coberta pelos gates T083–T089.
	 */
	private const PHASE_WRITE_AUTHORIZED = false;

	/** @return array<string,mixed> */
	public static function inspect_runtime(): array {
		$elementor_version = defined( 'ELEMENTOR_VERSION' ) ? trim( (string) ELEMENTOR_VERSION ) : '';
		$feature_requested = defined( self::WRITER_FEATURE_FLAG ) && true === constant( self::WRITER_FEATURE_FLAG );
		$capability_granted = function_exists( 'current_user_can' ) && current_user_can( self::REQUIRED_CAPABILITY );

		return self::assess( $elementor_version, $feature_requested, $capability_granted );
	}

	/**
	 * Avaliador puro e determinístico do gateway.
	 *
	 * @return array<string,mixed>
	 */
	public static function assess( string $elementor_version, bool $feature_requested, bool $capability_granted ): array {
		$version = trim( $elementor_version );
		$status  = 'compatible_read_only';
		$reasons = array();

		if ( '' === $version ) {
			$status    = 'blocking';
			$reasons[] = 'ELEMENTOR_MISSING';
		} elseif ( self::HOMOLOGATED_ELEMENTOR_VERSION !== $version ) {
			$status    = 'review_required';
			$reasons[] = 'ELEMENTOR_VERSION_NOT_HOMOLOGATED:' . $version;
		}

		$writer_denials = array();
		if ( 'compatible_read_only' !== $status ) {
			$writer_denials[] = 'VERSION_GATE_NOT_COMPATIBLE';
		}
		if ( ! $feature_requested ) {
			$writer_denials[] = 'FEATURE_FLAG_DISABLED';
		}
		if ( ! $capability_granted ) {
			$writer_denials[] = 'CAPABILITY_REQUIRED:' . self::REQUIRED_CAPABILITY;
		}
		if ( ! self::PHASE_WRITE_AUTHORIZED ) {
			$writer_denials[] = 'PHASE_T082_READ_ONLY';
		}

		return array(
			'contract_version'              => self::CONTRACT_VERSION,
			'matrix_version'                => self::MATRIX_VERSION,
			'elementor_version'             => '' !== $version ? $version : null,
			'homologated_elementor_version' => self::HOMOLOGATED_ELEMENTOR_VERSION,
			'compatibility_status'          => $status,
			'compatibility_reasons'         => $reasons,
			'writer_feature_flag'           => self::WRITER_FEATURE_FLAG,
			'writer_feature_requested'      => $feature_requested,
			'required_capability'           => self::REQUIRED_CAPABILITY,
			'writer_action'                 => self::WRITER_ACTION,
			'nonce_field'                   => self::NONCE_FIELD,
			'nonce_action_prefix'           => self::NONCE_ACTION_PREFIX,
			'nonce_validation_required'     => true,
			'required_projection_fields'    => array( 'post_id', self::SOURCE_HASH_FIELD, 'projection_hash' ),
			'capability_granted'            => $capability_granted,
			'phase_write_authorized'        => self::PHASE_WRITE_AUTHORIZED,
			'writer_allowed'                => false,
			'migration_execution_allowed'   => false,
			'writer_denials'                => array_values( array_unique( $writer_denials ) ),
			'safety'                        => array(
				'persists_state'         => false,
				'writes_post_content'    => false,
				'writes_elementor_data'  => false,
				'calls_external_network' => false,
				'executes_shortcodes'    => false,
			),
		);
	}

	/**
	 * Guard explícito para qualquer futuro chamador de writer.
	 *
	 * @param array<string,mixed>|null $assessment Avaliação injetável para testes.
	 * @return bool|\WP_Error
	 */
	public static function assert_writer_allowed( ?array $assessment = null ): bool|\WP_Error {
		$state = is_array( $assessment ) ? $assessment : self::inspect_runtime();

		if ( true === ( $state['writer_allowed'] ?? false ) ) {
			return true;
		}

		return new \WP_Error(
			'bdc_kb_elementor_writer_disabled',
			'Writer Elementor bloqueado pelo Gateway G-245/T082.',
			array(
				'status'  => 409,
				'gateway' => $state,
			)
		);
	}
}
