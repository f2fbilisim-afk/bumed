<?php

defined( 'ABSPATH' ) || exit;

final class Mango_Saat_Checkout {

	public static function init(): void {
		add_filter( 'woocommerce_cart_needs_payment', '__return_false' );
		add_filter( 'woocommerce_available_payment_gateways', array( __CLASS__, 'disable_gateways' ) );
		add_action( 'template_redirect', array( __CLASS__, 'redirect_checkout' ) );

		remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );
		add_action( 'woocommerce_proceed_to_checkout', array( __CLASS__, 'cart_whatsapp_button' ), 20 );

		add_action( 'woocommerce_widget_shopping_cart_buttons', array( __CLASS__, 'mini_cart_whatsapp_only' ), 1 );
		add_filter( 'woocommerce_add_to_cart_fragments', array( __CLASS__, 'fragments' ) );
	}

	public static function disable_gateways( array $gateways ): array {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return $gateways;
		}
		return array();
	}

	public static function redirect_checkout(): void {
		if ( function_exists( 'is_checkout' ) && is_checkout() && ! is_wc_endpoint_url( 'order-received' ) ) {
			wp_safe_redirect( wc_get_cart_url() );
			exit;
		}
	}

	public static function cart_whatsapp_button(): void {
		if ( WC()->cart->is_empty() ) {
			return;
		}
		$url = Mango_Saat_WhatsApp::cart_url();
		echo '<a href="' . esc_url( $url ) . '" class="checkout-button button alt mango-saat-cart-whatsapp" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Sepeti WhatsApp’tan Gönder', 'mango-saat-siparis' ) . '</a>';
	}

	public static function mini_cart_whatsapp_only(): void {
		remove_action( 'woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_proceed_to_checkout', 20 );
		if ( WC()->cart->is_empty() ) {
			return;
		}
		$url = Mango_Saat_WhatsApp::cart_url();
		echo '<a href="' . esc_url( $url ) . '" class="button mango-saat-mini-cart-whatsapp" target="_blank" rel="noopener noreferrer">' . esc_html__( 'WhatsApp’tan Gönder', 'mango-saat-siparis' ) . '</a>';
	}

	public static function fragments( array $fragments ): array {
		ob_start();
		self::mini_cart_whatsapp_only();
		$fragments['a.mango-saat-mini-cart-whatsapp'] = ob_get_clean();
		return $fragments;
	}
}