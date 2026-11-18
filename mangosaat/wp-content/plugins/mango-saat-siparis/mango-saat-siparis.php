<?php
/**
 * Plugin Name: Mango Saat Sipariş
 * Description: WhatsApp sipariş akışı, kutu ürünleri ve WooCommerce ödeme devre dışı — Mangosaat.com.tr
 * Version: 1.0.1
 * Author: Mango Saat
 * Requires Plugins: woocommerce
 * Text Domain: mango-saat-siparis
 */

defined( 'ABSPATH' ) || exit;

define( 'MANGO_SAAT_VERSION', '1.0.1' );
define( 'MANGO_SAAT_CATALOG_VERSION', '2026.01' );
define( 'MANGO_SAAT_PLUGIN_FILE', __FILE__ );
define( 'MANGO_SAAT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MANGO_SAAT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once MANGO_SAAT_PLUGIN_DIR . 'includes/class-mango-saat.php';

function mango_saat_siparis(): Mango_Saat_Plugin {
	return Mango_Saat_Plugin::instance();
}

add_action( 'plugins_loaded', static function () {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action(
			'admin_notices',
			static function () {
				echo '<div class="notice notice-error"><p>Mango Saat Sipariş için WooCommerce gerekir.</p></div>';
			}
		);
		return;
	}
	mango_saat_siparis()->init();
}, 20 );

register_activation_hook( __FILE__, array( 'Mango_Saat_Plugin', 'activate' ) );