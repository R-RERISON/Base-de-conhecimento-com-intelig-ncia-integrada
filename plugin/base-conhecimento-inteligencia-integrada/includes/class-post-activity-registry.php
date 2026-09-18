<?php
/**
 * Registry of post-scoped activities exposed inside the canonical Knowledge Workspace.
 *
 * @package BDC_Knowledge_Base
 */
namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Post_Activity_Registry {
	public const SCHEMA_VERSION = '1.0.0';
	public const DEFAULT_ACTIVITY = 'overview';

	/** @return array<string,array<string,mixed>> */
	public static function definitions(): array {
		return array(
			'overview' => array(
				'label' => 'Visão geral', 'icon' => 'grid-view', 'renderer' => 'legacy', 'mode' => 'read_only',
			),
			'content' => array(
				'label' => 'Conteúdo', 'icon' => 'text-page', 'renderer' => 'post_management', 'mode' => 'read_only',
			),
			'summary' => array(
				'label' => 'Sumário', 'icon' => 'media-text', 'renderer' => 'legacy', 'mode' => 'existing_writer',
			),
			'classification' => array(
				'label' => 'Classificação', 'icon' => 'tag', 'renderer' => 'legacy', 'mode' => 'existing_writer',
			),
			'intelligence' => array(
				'label' => 'Inteligência', 'icon' => 'lightbulb', 'renderer' => 'post_management', 'mode' => 'read_only',
			),
			'core_blocks' => array(
				'label' => 'Blocos do WordPress', 'icon' => 'block-default', 'renderer' => 'post_management', 'mode' => 'read_only',
			),
			'review' => array(
				'label' => 'Revisão e governança', 'icon' => 'yes-alt', 'renderer' => 'legacy', 'mode' => 'existing_writer',
			),
			'history' => array(
				'label' => 'Histórico', 'icon' => 'backup', 'renderer' => 'legacy', 'mode' => 'read_only',
			),
		);
	}

	public static function is_valid( string $activity ): bool {
		return isset( self::definitions()[ sanitize_key( $activity ) ] );
	}

	public static function normalize( string $activity ): string {
		$activity = sanitize_key( $activity );
		return self::is_valid( $activity ) ? $activity : self::DEFAULT_ACTIVITY;
	}

	/** @return array<string,mixed>|null */
	public static function get( string $activity ): ?array {
		$activity = self::normalize( $activity );
		$all = self::definitions();
		return $all[ $activity ] ?? null;
	}
}
