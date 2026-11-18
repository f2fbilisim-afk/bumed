<?php

defined( 'ABSPATH' ) || exit;

final class Mango_Saat_Cart {

	public static function init(): void {
		add_filter( 'woocommerce_add_cart_item_data', array( __CLASS__, 'add_cart_item_data' ), 10, 3 );
		add_filter( 'woocommerce_add_cart_item', array( __CLASS__, 'hydrate_cart_item' ), 10, 2 );
		add_action( 'woocommerce_before_calculate_totals', array( __CLASS__, 'apply_box_pricing' ), 20, 1 );
		add_filter( 'woocommerce_cart_item_name', array( __CLASS__, 'cart_item_name' ), 10, 3 );
		add_filter( 'woocommerce_get_item_data', array( __CLASS__, 'display_item_data' ), 10, 2 );
		add_action( 'woocommerce_check_cart_items', array( __CLASS__, 'validate_cart_items' ) );
		add_filter( 'woocommerce_cart_id', array( __CLASS__, 'distinct_cart_ids' ), 10, 5 );

		add_action( 'wp_ajax_mango_saat_clear_cart_after_whatsapp', array( __CLASS__, 'ajax_clear_cart' ) );
		add_action( 'wp_ajax_nopriv_mango_saat_clear_cart_after_whatsapp', array( __CLASS__, 'ajax_clear_cart' ) );
	}

	public static function add_cart_item_data( array $cart_item_data, int $product_id, int $variation_id ): array {
		if ( Mango_Saat_Plugin::is_box_product( $product_id ) ) {
			wc_add_notice( __( 'Kutu ürünleri yalnızca ana ürünle sepete eklenir.', 'mango-saat-siparis' ), 'error' );
			return $cart_item_data;
		}

		$with_box = ! empty( $_REQUEST['mango_with_box'] );
		if ( ! $with_box ) {
			$cart_item_data['mango_with_box'] = 0;
			return $cart_item_data;
		}

		$box_id = Mango_Saat_Product_Meta::get_compatible_box_id( $variation_id ?: $product_id );
		if ( ! $box_id ) {
			return $cart_item_data;
		}

		$box = wc_get_product( $box_id );
		if ( ! $box || ! $box->is_in_stock() ) {
			wc_add_notice( __( 'Seçilen kutu stokta yok.', 'mango-saat-siparis' ), 'notice' );
			return $cart_item_data;
		}

		$cart_item_data['mango_with_box']    = 1;
		$cart_item_data['mango_box_id']      = $box_id;
		$cart_item_data['mango_box_price']   = Mango_Saat_Product_Meta::get_box_price_for_product( $variation_id ?: $product_id, $box_id );
		$cart_item_data['mango_box_name']    = $box->get_name();
		$cart_item_data['unique_key']        = md5( wp_json_encode( $cart_item_data ) . microtime() );

		return $cart_item_data;
	}

	public static function hydrate_cart_item( array $cart_item, string $cart_item_key ): array {
		return $cart_item;
	}

	public static function apply_box_pricing( WC_Cart $cart ): void {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return;
		}
		foreach ( $cart->get_cart() as $item ) {
			if ( empty( $item['mango_with_box'] ) || empty( $item['data'] ) ) {
				continue;
			}
			$product   = $item['data'];
			$base      = (float) $product->get_regular_price();
			if ( '' === $product->get_regular_price() ) {
				$base = (float) $product->get_price();
			}
			$box_price = (float) ( $item['mango_box_price'] ?? 0 );
			$product->set_price( $base + $box_price );
		}
	}

	public static function cart_item_name( string $name, array $cart_item, string $cart_item_key ): string {
		if ( ! empty( $cart_item['mango_with_box'] ) && ! empty( $cart_item['mango_box_name'] ) ) {
			$name .= '<br/><small class="mango-saat-cart-box-label">' . esc_html__( 'Kutu:', 'mango-saat-siparis' ) . ' ' . esc_html( $cart_item['mango_box_name'] ) . '</small>';
		}
		return $name;
	}

	public static function display_item_data( array $item_data, array $cart_item ): array {
		if ( ! empty( $cart_item['mango_with_box'] ) ) {
			$item_data[] = array(
				'key'   => __( 'Kutu', 'mango-saat-siparis' ),
				'value' => esc_html( $cart_item['mango_box_name'] ?? '' ) . ' (+' . wp_strip_all_tags( wc_price( $cart_item['mango_box_price'] ?? 0 ) ) . ')',
			);
		}
		return $item_data;
	}

	public static function validate_cart_items(): void {
		foreach ( WC()->cart->get_cart() as $key => $item ) {
			if ( empty( $item['mango_with_box'] ) ) {
				continue;
			}
			$box_id = (int) ( $item['mango_box_id'] ?? 0 );
			$box    = $box_id ? wc_get_product( $box_id ) : null;
			if ( ! $box || ! $box->is_in_stock() ) {
				WC()->cart->set_quantity( $key, 0, false );
				wc_add_notice( __( 'Sepetinizdeki bir kutu stok dışı kaldı; ilgili satır kaldırıldı.', 'mango-saat-siparis' ), 'notice' );
			}
		}
	}

	public static function distinct_cart_ids( string $cart_id, int $product_id, int $variation_id, array $variation, array $cart_item_data ): string {
		if ( isset( $cart_item_data['unique_key'] ) ) {
			$cart_id .= '|' . $cart_item_data['unique_key'];
		}
		return $cart_id;
	}

	public static function ajax_clear_cart(): void {
		check_ajax_referer( 'mango_saat_cart', 'nonce' );
		if ( WC()->cart ) {
			WC()->cart->empty_cart();
		}
		wp_send_json_success();
	}
}