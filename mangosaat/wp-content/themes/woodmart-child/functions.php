<?php
/**
 * WoodMart Child — Mango Saat
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'wp_enqueue_scripts',
	static function () {
		wp_enqueue_style(
			'woodmart-child-mango',
			get_stylesheet_uri(),
			array( 'woodmart-style' ),
			'1.0.0'
		);
	},
	30
);

add_filter(
	'woodmart_get_theme_info',
	static function ( $info ) {
		$info['is_child'] = true;
		return $info;
	}
);