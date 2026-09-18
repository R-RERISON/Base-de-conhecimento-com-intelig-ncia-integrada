<?php
/**
 * Golden Candidate Seed independente da SPEC-005.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fixture temporária versionada no runtime de engenharia.
 *
 * Não lê ASI, banco legado, options legadas ou qualquer fonte externa.
 */
final class Golden_Candidate_Seed {

	public const VERSION = '1.0.0-candidate';
	public const SOURCE_SET_HASH = '6fcd334175dfb6f637f684b4df240a2b1eaf45bfeb4312728e8131c24cbec299';

	/**
	 * @return array<int,array<string,mixed>>
	 */
	public static function all(): array {
		return array(
			array(
				'id' => 'GQ-LEGACY-001',
				'legacy_id' => 1,
				'query' => 'pendrive',
				'expected_post_id' => 527,
				'max_rank' => 3,
				'legacy_severity' => 'warning',
				'expected_item_key' => '',
			),
			array(
				'id' => 'GQ-LEGACY-002',
				'legacy_id' => 2,
				'query' => 'MSTeams',
				'expected_post_id' => 579,
				'max_rank' => 3,
				'legacy_severity' => 'warning',
				'expected_item_key' => '',
			),
			array(
				'id' => 'GQ-LEGACY-003',
				'legacy_id' => 3,
				'query' => 'Windows 11',
				'expected_post_id' => 583,
				'max_rank' => 3,
				'legacy_severity' => 'warning',
				'expected_item_key' => '',
			),
			array(
				'id' => 'GQ-LEGACY-004',
				'legacy_id' => 4,
				'query' => 'Termo de assinatura',
				'expected_post_id' => 45855,
				'max_rank' => 3,
				'legacy_severity' => 'warning',
				'expected_item_key' => '',
			),
			array(
				'id' => 'GQ-LEGACY-005',
				'legacy_id' => 5,
				'query' => 'Estrutura',
				'expected_post_id' => 36620,
				'max_rank' => 3,
				'legacy_severity' => 'warning',
				'expected_item_key' => '',
			),
			array(
				'id' => 'GQ-LEGACY-006',
				'legacy_id' => 6,
				'query' => 'SCCM',
				'expected_post_id' => 412,
				'max_rank' => 3,
				'legacy_severity' => 'warning',
				'expected_item_key' => '',
			),
		);
	}
}
