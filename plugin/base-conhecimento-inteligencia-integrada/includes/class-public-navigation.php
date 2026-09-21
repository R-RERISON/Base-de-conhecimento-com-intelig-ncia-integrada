<?php
/**
 * Public BDC navigation resolver.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Public_Navigation {

	/**
	 * @return array<int,array{label:string,url:string,icon:string}>
	 */
	public static function items(): array {
		$definitions = array(
			array( 'label' => 'Página Inicial', 'icon' => 'dashicons-admin-home', 'slugs' => array( 'home', 'pagina-inicial' ) ),
			array( 'label' => 'Consulta Avançada', 'icon' => 'dashicons-search', 'slugs' => array( 'consulta-avancada', 'consulta' ) ),
			array( 'label' => 'Telefones', 'icon' => 'dashicons-phone', 'slugs' => array( 'telefones', 'telefone' ) ),
			array( 'label' => 'Links Úteis', 'icon' => 'dashicons-admin-links', 'slugs' => array( 'links-uteis', 'links' ) ),
			array( 'label' => 'POSTI', 'icon' => 'dashicons-external', 'slugs' => array( 'posti' ) ),
		);

		$menu_map = self::menu_map();
		$items = array();
		foreach ( $definitions as $definition ) {
			$label = (string) $definition['label'];
			$url = 'Página Inicial' === $label ? home_url( '/' ) : self::menu_url( $menu_map, $label );
			if ( '' === $url ) {
				$url = self::page_url( (array) $definition['slugs'], $label );
			}
			$items[] = array(
				'label' => $label,
				'url' => $url,
				'icon' => (string) $definition['icon'],
			);
		}

		return (array) apply_filters( 'bdc_kb_public_nav_items', $items );
	}

	/** @return array<string,string> */
	private static function menu_map(): array {
		$map = array();
		$term_ids = array_values( array_unique( array_map( 'intval', array_values( (array) get_nav_menu_locations() ) ) ) );
		foreach ( (array) wp_get_nav_menus() as $menu ) {
			if ( is_object( $menu ) && isset( $menu->term_id ) ) {
				$term_ids[] = (int) $menu->term_id;
			}
		}
		$term_ids = array_values( array_unique( array_filter( $term_ids ) ) );

		foreach ( $term_ids as $term_id ) {
			foreach ( (array) wp_get_nav_menu_items( $term_id ) as $item ) {
				if ( ! is_object( $item ) || ! isset( $item->title, $item->url ) ) {
					continue;
				}
				$key = self::normalize( (string) $item->title );
				if ( '' !== $key && ! isset( $map[ $key ] ) ) {
					$map[ $key ] = (string) $item->url;
				}
			}
		}
		return $map;
	}

	/** @param array<string,string> $map */
	private static function menu_url( array $map, string $label ): string {
		$key = self::normalize( $label );
		return isset( $map[ $key ] ) ? (string) $map[ $key ] : '';
	}

	/** @param array<int,string> $slugs */
	private static function page_url( array $slugs, string $title ): string {
		foreach ( $slugs as $slug ) {
			$page = get_page_by_path( sanitize_title( $slug ), OBJECT, 'page' );
			if ( is_object( $page ) && 'publish' === (string) ( $page->post_status ?? '' ) ) {
				$url = get_permalink( (int) $page->ID );
				if ( is_string( $url ) ) {
					return $url;
				}
			}
		}

		$query = new \WP_Query(
			array(
				'post_type' => 'page',
				'post_status' => 'publish',
				'posts_per_page' => 1,
				'title' => $title,
				'fields' => 'ids',
				'no_found_rows' => true,
				'suppress_filters' => false,
			)
		);
		$id = ! empty( $query->posts ) ? (int) $query->posts[0] : 0;
		wp_reset_postdata();
		$url = $id > 0 ? get_permalink( $id ) : '';
		return is_string( $url ) ? $url : '';
	}

	private static function normalize( string $value ): string {
		$value = strtolower( remove_accents( trim( $value ) ) );
		$value = preg_replace( '/\s+/u', ' ', $value ) ?? $value;
		return $value;
	}
}