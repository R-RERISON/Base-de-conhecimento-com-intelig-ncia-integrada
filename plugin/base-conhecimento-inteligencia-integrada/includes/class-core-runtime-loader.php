<?php
/**
 * Loader explícito do runtime permanente do produto.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Carrega somente classes permanentes do core BDC.
 */
final class Core_Runtime_Loader {

	/**
	 * Lista os arquivos permanentes do core.
	 *
	 * @return array<int,string>
	 */
	private static function files(): array {
		return array(
			'includes/class-meta-contract.php',
			'includes/class-summary-store.php',
			'includes/class-knowledge-facts-contract.php',
			'includes/class-knowledge-facts-store.php',
			'includes/class-helpful-tips-store.php',
			'includes/class-knowledge-details-admin.php',
			'includes/class-coverage-read-model.php',
			'includes/class-classification-contract.php',
			'includes/class-classification-store.php',
			'includes/class-classification-admin.php',
			'includes/class-review-contract.php',
			'includes/class-review-store.php',
			'includes/class-review-admin.php',
			'includes/class-content-normalizer.php',
			'includes/class-shortcode-inspector.php',
			'includes/class-hierarchy-relationships.php',
			'includes/class-numbered-hierarchy-resolver.php',
			'includes/class-legacy-html-adapter.php',
			'includes/class-semantic-dom-expectation.php',
			'includes/class-content-source.php',
			'includes/class-elementor-adapter.php',
			'includes/class-gutenberg-adapter.php',
			'includes/class-content-extractor.php',
			'includes/class-canonical-json.php',
			'includes/class-semantic-structure.php',
			'includes/class-knowledge-document.php',
			'includes/class-block-projection-plan.php',
			'includes/class-migration-fidelity-source.php',
			'includes/class-core-block-lossless-serializer.php',
			'includes/class-block-migration-stale-source-guard.php',
			'includes/class-core-block-editorial-parity.php',
			'includes/class-block-migration-journal.php',
			'includes/class-block-migration-journal-store.php',
			'includes/class-block-migration-dry-run.php',
			'includes/class-block-migration-batch-plan.php',
			'includes/class-block-migration-lock.php',
			'includes/class-post-activity-registry.php',
			'includes/class-post-management-context.php',
			'includes/class-post-management-activities.php',
			'includes/class-post-core-blocks-activity.php',
			'includes/class-admin-page.php',
			'includes/class-visual-foundation.php',
		);
	}

	/**
	 * Carrega o runtime permanente.
	 *
	 * @throws \RuntimeException Quando um arquivo obrigatório não existe.
	 */
	public static function load(): void {
		foreach ( self::files() as $relative_path ) {
			$path = BDC_KB_DIR . $relative_path;
			if ( ! is_file( $path ) ) {
				throw new \RuntimeException(
					sprintf(
						'Arquivo obrigatório do core ausente: %s',
						esc_html( $relative_path )
					)
				);
			}

			require_once $path;
		}
	}
}
