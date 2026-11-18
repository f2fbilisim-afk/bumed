<?php

defined( 'ABSPATH' ) || exit;

final class Mango_Saat_Plugin {

	private static ?self $instance = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public static function activate(): void {
		if ( ! get_option( 'mango_saat_whatsapp_display' ) ) {
			update_option( 'mango_saat_whatsapp_display', '+90 541 463 16 77' );
		}
		if ( ! get_option( 'mango_saat_whatsapp_e164' ) ) {
			update_option( 'mango_saat_whatsapp_e164', '905414631677' );
		}
		flush_rewrite_rules();
	}

	public function init(): void {
		$this->load_includes();
		Mango_Saat_Settings::init();
		Mango_Saat_Product_Meta::init();
		Mango_Saat_Catalog::init();
		Mango_Saat_Checkout::init();
		Mango_Saat_Cart::init();
		Mango_Saat_WhatsApp::init();
		Mango_Saat_Seeder::init();

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	private function load_includes(): void {
		require_once MANGO_SAAT_PLUGIN_DIR . 'includes/class-settings.php';
		require_once MANGO_SAAT_PLUGIN_DIR . 'includes/class-product-meta.php';
		require_once MANGO_SAAT_PLUGIN_DIR . 'includes/class-catalog.php';
		require_once MANGO_SAAT_PLUGIN_DIR . 'includes/class-checkout.php';
		require_once MANGO_SAAT_PLUGIN_DIR . 'includes/class-cart.php';
		require_once MANGO_SAAT_PLUGIN_DIR . 'includes/class-whatsapp.php';
		require_once MANGO_SAAT_PLUGIN_DIR . 'includes/class-seeder.php';
	}

	public function enqueue_assets(): void {
		if ( ! is_woocommerce() && ! is_cart() && ! is_shop() && ! is_front_page() ) {
			return;
		}

		wp_enqueue_style(
			'mango-saat-frontend',
			MANGO_SAAT_PLUGIN_URL . 'assets/css/frontend.css',
			array(),
			MANGO_SAAT_VERSION
		);

		wp_enqueue_script(
			'mango-saat-frontend',
			MANGO_SAAT_PLUGIN_URL . 'assets/js/frontend.js',
			array( 'jquery', 'wc-add-to-cart-variation' ),
			MANGO_SAAT_VERSION,
			true
		);

		wp_localize_script(
			'mango-saat-frontend',
			'mangoSaat',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'mango_saat_cart' ),
				'i18n'    => array(
					'selectVariation' => __( 'Lütfen ürün seçeneklerini belirleyin.', 'mango-saat-siparis' ),
					'boxOutOfStock'   => __( 'Kutu stokta yok; kutusuz devam edebilirsiniz.', 'mango-saat-siparis' ),
				),
			)
		);
	}

	public static function is_box_product( int $product_id ): bool {
		return 'yes' === get_post_meta( $product_id, '_mango_is_box', true );
	}
}