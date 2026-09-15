<?php
/**
 * Contrato canônico de Classificação de Conhecimento.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define as quatro taxonomias canônicas autorizadas pela SPEC-002.
 */
final class Classification_Contract {

	public const POST_TYPE = 'post';
	public const MAX_TERMS_PER_FIELD = 50;

	/**
	 * @return array<string,array{taxonomy:string,label:string,singular:string,multiple:bool,legacy_keys:array<int,string>}>
	 */
	public static function fields(): array {
		return array(
			'audience' => array(
				'taxonomy'    => 'bdc_kb_audience',
				'label'       => 'Audiência',
				'singular'    => 'Audiência',
				'multiple'    => true,
				'legacy_keys' => array( '_bdc_es_target_audience', '_kb2ops_target_audience' ),
			),
			'responsible_team' => array(
				'taxonomy'    => 'bdc_kb_responsible_team',
				'label'       => 'Equipes responsáveis',
				'singular'    => 'Equipe responsável',
				'multiple'    => true,
				'legacy_keys' => array( '_bdc_es_responsible_team' ),
			),
			'knowledge_type' => array(
				'taxonomy'    => 'bdc_kb_knowledge_type',
				'label'       => 'Tipos de conhecimento',
				'singular'    => 'Tipo de conhecimento',
				'multiple'    => false,
				'legacy_keys' => array( '_kb2ops_knowledge_type' ),
			),
			'catalog_item' => array(
				'taxonomy'    => 'bdc_kb_catalog_item',
				'label'       => 'Itens de catálogo',
				'singular'    => 'Item de catálogo',
				'multiple'    => true,
				'legacy_keys' => array( '_bdc_es_catalog_item' ),
			),
		);
	}

	public static function register(): void {
		foreach ( self::fields() as $definition ) {
			register_taxonomy(
				$definition['taxonomy'],
				array( self::POST_TYPE ),
				array(
					'labels' => array(
						'name'          => $definition['label'],
						'singular_name' => $definition['singular'],
						'menu_name'     => $definition['label'],
						'search_items'  => 'Buscar ' . $definition['label'],
						'all_items'     => 'Todos: ' . $definition['label'],
						'edit_item'     => 'Editar ' . $definition['singular'],
						'update_item'   => 'Atualizar ' . $definition['singular'],
						'add_new_item'  => 'Adicionar ' . $definition['singular'],
						'new_item_name' => 'Novo nome — ' . $definition['singular'],
					),
					'public'              => false,
					'publicly_queryable'  => false,
					'show_ui'             => true,
					'show_in_rest'        => false,
					'show_in_nav_menus'   => false,
					'show_admin_column'   => false,
					'show_in_quick_edit'  => false,
					'meta_box_cb'         => false,
					'hierarchical'        => false,
					'rewrite'             => false,
					'query_var'           => false,
					'capabilities'        => array(
						'manage_terms' => 'manage_categories',
						'edit_terms'   => 'manage_categories',
						'delete_terms' => 'manage_categories',
						'assign_terms' => 'edit_posts',
					),
				)
			);
		}
	}
}
