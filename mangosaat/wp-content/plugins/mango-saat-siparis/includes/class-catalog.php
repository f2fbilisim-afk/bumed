<?php

defined( 'ABSPATH' ) || exit;

final class Mango_Saat_Catalog {

	public static function init(): void {
		add_action( 'pre_get_posts', array( __CLASS__, 'hide_box_products_from_archives' ) );
		add_filter( 'woocommerce_product_is_visible', array( __CLASS__, 'hide_box_product_visibility' ), 10, 2 );
		add_filter( 'woocommerce_product_query_meta_query', array( __CLASS__, 'product_query_meta_query' ), 10, 2 );
		add_filter( 'woocommerce_shortcode_products_query', array( __CLASS__, 'shortcode_query' ), 10, 3 );

		add_filter( 'woocommerce_loop_add_to_cart_link', array( __CLASS__, 'loop_whatsapp_button' ), 99, 3 );
		add_filter( 'woodmart_swatches_loop_add_to_cart', array( __CLASS__, 'loop_whatsapp_button' ), 99, 3 );

		add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'render_box_toggle' ), 26 );
		add_action( 'woocommerce_before_add_to_cart_button', array( __CLASS__, 'single_whatsapp_wrapper_open' ), 5 );
		add_action( 'woocommerce_after_add_to_cart_button', array( __CLASS__, 'single_whatsapp_button' ), 15 );
		add_action( 'woocommerce_after_add_to_cart_button', array( __CLASS__, 'single_whatsapp_wrapper_close' ), 20 );
	}

	public static function hide_box_products_from_archives( WP_Query $query ): void {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}
		if ( ! ( $query->is_post_type_archive( 'product' ) || $query->is_tax( get_object_taxonomies( 'product' ) ) || $query->is_search() ) ) {
			return;
		}
		$meta_query   = (array) $query->get( 'meta_query' );
		$meta_query[] = array(
			'relation' => 'OR',
			array(
				'key'     => '_mango_is_box',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => '_mango_is_box',
				'value'   => 'yes',
				'compare' => '!=',
			),
		);
		$query->set( 'meta_query', $meta_query );
	}

	public static function hide_box_product_visibility( bool $visible, int $product_id ): bool {
		if ( Mango_Saat_Plugin::is_box_product( $product_id ) ) {
			return false;
		}
		return $visible;
	}

	public static function product_query_meta_query( array $meta_query, WC_Query $wc_query ): array {
		$meta_query[] = array(
			'relation' => 'OR',
			array(
				'key'     => '_mango_is_box',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => '_mango_is_box',
				'value'   => 'yes',
				'compare' => '!=',
			),
		);
		return $meta_query;
	}

	public static function shortcode_query( array $query_args, array $atts, string $type ): array {
		if ( empty( $query_args['meta_query'] ) ) {
			$query_args['meta_query'] = array();
		}
		$query_args['meta_query'][] = array(
			'relation' => 'OR',
			array(
				'key'     => '_mango_is_box',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => '_mango_is_box',
				'value'   => 'yes',
				'compare' => '!=',
			),
		);
		return $query_args;
	}

	public static function loop_whatsapp_button( $html, $product = null, $args = array() ) {
		if ( ! $product instanceof WC_Product ) {
			if ( is_numeric( $product ) ) {
				$product = wc_get_product( (int) $product );
			} elseif ( isset( $GLOBALS['product'] ) && $GLOBALS['product'] instanceof WC_Product ) {
				$product = $GLOBALS['product'];
			}
		}
		if ( ! $product || Mango_Saat_Plugin::is_box_product( $product->get_id() ) ) {
			return $html;
		}

		$url = Mango_Saat_WhatsApp::product_url( $product );
		return sprintf(
			'<a href="%s" class="button mango-saat-whatsapp-btn" target="_blank" rel="noopener noreferrer">%s</a>',
			esc_url( $url ),
			esc_html__( 'WhatsApp Sipariş', 'mango-saat-siparis' )
		);
	}

	public static function render_box_toggle(): void {
		global $product;
		if ( ! $product instanceof WC_Product || Mango_Saat_Plugin::is_box_product( $product->get_id() ) ) {
			return;
		}

		$box_id = Mango_Saat_Product_Meta::get_compatible_box_id( $product->get_id() );
		if ( ! $box_id ) {
			return;
		}

		echo Mango_Saat_Cart::get_box_offer_markup( $product->get_id(), $box_id, false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public static function single_whatsapp_wrapper_open(): void {
		echo '<div class="mango-saat-single-actions">';
	}

	public static function single_whatsapp_button(): void {
		global $product;
		if ( ! $product instanceof WC_Product ) {
			return;
		}
		$url = Mango_Saat_WhatsApp::product_url( $product, array( 'from_single' => true ) );
		printf(
			'<a href="%s" class="button alt mango-saat-whatsapp-btn mango-saat-whatsapp-single" target="_blank" rel="noopener noreferrer" data-product-id="%d">%s</a>',
			esc_url( $url ),
			(int) $product->get_id(),
			esc_html__( 'WhatsApp Sipariş', 'mango-saat-siparis' )
		);
	}

	public static function single_whatsapp_wrapper_close(): void {
		echo '</div>';
	}
}