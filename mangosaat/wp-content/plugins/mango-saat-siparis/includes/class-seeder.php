<?php

defined( 'ABSPATH' ) || exit;

final class Mango_Saat_Seeder {

	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'maybe_seed_catalog' ), 99 );
	}

	public static function maybe_seed_catalog(): void {
		if ( is_admin() && wp_doing_ajax() ) {
			return;
		}
		$stored = get_option( 'mango_saat_catalog_version', '' );
		if ( $stored === MANGO_SAAT_CATALOG_VERSION ) {
			return;
		}
		if ( ! taxonomy_exists( 'product_cat' ) || ! taxonomy_exists( 'product_brand' ) ) {
			return;
		}
		self::run();
		update_option( 'mango_saat_catalog_version', MANGO_SAAT_CATALOG_VERSION );
	}

	/**
	 * @return array<string, mixed>
	 */
	public static function run(): array {
		$report = array(
			'categories' => array(),
			'brands'     => array(),
		);

		$tree = array(
			'Saat'      => array( 'Erkek', 'Kadın' ),
			'Parfüm'    => array( 'Erkek', 'Kadın' ),
			'Gözlük'    => array( 'Erkek', 'Kadın' ),
			'Çakmak'    => array(),
			'Uygun Seri' => array(),
		);

		foreach ( $tree as $parent => $children ) {
			$parent_slug = self::slug_for( $parent );
			$parent_id   = self::upsert_term( 'product_cat', $parent, $parent_slug, 0 );
			$report['categories'][ $parent ] = $parent_id;

			foreach ( $children as $child ) {
				$child_slug = self::slug_for( $parent . '-' . $child );
				$child_id   = self::upsert_term( 'product_cat', $child, $child_slug, $parent_id );
				$report['categories'][ "$parent > $child" ] = $child_id;
			}
		}

		$brands = array(
			'Audemars Piguet', 'Armani', 'Breitling', 'Burberry', 'BVLGARI', 'Cartier',
			'Franck Muller', 'Gucci', 'Guess', 'Hublot', 'Invicta', 'IWC Schaffhausen',
			'Jacob & Co.', 'Longines', 'Michael Kors', 'Montblanc', 'Omega', 'Oris',
			'Panerai', 'Patek Philippe', 'Rado', 'Richard Mille', 'Rolex', 'Roger Dubuis',
			'Seiko', 'SEVENFRIDAY', 'TAG Heuer', 'Tissot', 'Tudor', 'Ulysse Nardin',
			'Vacheron Constantin', 'Versace',
		);

		foreach ( $brands as $brand ) {
			$slug = sanitize_title( $brand );
			$report['brands'][ $brand ] = self::upsert_term( 'product_brand', $brand, $slug, 0 );
		}

		return $report;
	}

	private static function slug_for( string $label ): string {
		$map = array(
			'Saat' => 'saat',
			'Parfüm' => 'parfum',
			'Gözlük' => 'gozluk',
			'Çakmak' => 'cakmak',
			'Uygun Seri' => 'uygun-seri',
			'Erkek' => 'erkek',
			'Kadın' => 'kadin',
		);
		if ( isset( $map[ $label ] ) ) {
			return $map[ $label ];
		}
		if ( str_contains( $label, '-' ) ) {
			$parts = explode( '-', $label, 2 );
			return $map[ $parts[0] ] ?? sanitize_title( $parts[0] ) ) . '-' . ( $map[ $parts[1] ] ?? sanitize_title( $parts[1] ) );
		}
		return sanitize_title( $label );
	}

	private static function upsert_term( string $taxonomy, string $name, string $slug, int $parent ): int {
		$existing = term_exists( $slug, $taxonomy );
		if ( $existing ) {
			$term_id = is_array( $existing ) ? (int) $existing['term_id'] : (int) $existing;
			wp_update_term(
				$term_id,
				$taxonomy,
				array(
					'name'   => $name,
					'slug'   => $slug,
					'parent' => $parent,
				)
			);
			return $term_id;
		}

		$result = wp_insert_term(
			$name,
			$taxonomy,
			array(
				'slug'   => $slug,
				'parent' => $parent,
			)
		);
		if ( is_wp_error( $result ) ) {
			return 0;
		}
		return (int) $result['term_id'];
	}
}