<?php

defined( 'ABSPATH' ) || exit;

final class Mango_Saat_WhatsApp {

	public static function init(): void {
		add_action( 'wp_footer', array( __CLASS__, 'cart_clear_script' ) );
	}

	public static function wa_link( string $message ): string {
		$phone = Mango_Saat_Settings::get_whatsapp_e164();
		return 'https://wa.me/' . rawurlencode( $phone ) . '?text=' . rawurlencode( $message );
	}

	public static function product_url( WC_Product $product, array $context = array() ): string {
		$parent_id = $product->get_parent_id() ?: $product->get_id();
		$name      = $product->get_name();
		$price_html = wp_strip_all_tags( $product->get_price_html() );
		$link      = get_permalink( $product->get_id() );

		if ( $product->is_type( 'variable' ) && empty( $context['variation_resolved'] ) ) {
			$message = sprintf(
				"Merhaba, \"%s\" hakkında sipariş vermek istiyorum.\nSayfa: %s",
				$name,
				$link
			);
			return self::wa_link( $message );
		}

		if ( $product->is_type( 'variation' ) ) {
			$attrs = wc_get_formatted_variation( $product, true, false, true );
			$name .= $attrs ? ' — ' . wp_strip_all_tags( $attrs ) : '';
		}

		$message = sprintf(
			"Merhaba, sipariş vermek istiyorum.\nÜrün: %s\nFiyat: %s\nLink: %s",
			$name,
			$price_html,
			$link
		);

		return self::wa_link( $message );
	}

	public static function cart_url(): string {
		$lines = array( 'Merhaba, sepetimdeki ürünler için sipariş vermek istiyorum.', '' );
		$total = 0.0;

		foreach ( WC()->cart->get_cart() as $item ) {
			/** @var WC_Product $product */
			$product = $item['data'];
			$qty     = (int) $item['quantity'];
			$line_total = (float) $product->get_price() * $qty;
			$total += $line_total;

			$line = sprintf( '• %s x%d — %s', $product->get_name(), $qty, wp_strip_all_tags( wc_price( $product->get_price() ) ) );
			$line .= "\n  " . get_permalink( $product->get_id() );

			if ( ! empty( $item['mango_with_box'] ) ) {
				$box_name  = $item['mango_box_name'] ?? '';
				$box_price = (float) ( $item['mango_box_price'] ?? 0 );
				$line .= sprintf( "\n  Kutu: %s (+%s)", $box_name, wp_strip_all_tags( wc_price( $box_price ) ) );
			}

			$lines[] = $line;
		}

		$lines[] = '';
		$lines[] = 'Ürün toplamı: ' . wp_strip_all_tags( wc_price( $total ) );

		return self::wa_link( implode( "\n", $lines ) );
	}

	public static function cart_clear_script(): void {
		if ( ! is_cart() && ! is_shop() && ! is_product() ) {
			return;
		}
		?>
		<script>
		document.addEventListener('click', function (e) {
			var target = e.target.closest('a.mango-saat-cart-whatsapp, a.mango-saat-mini-cart-whatsapp, a.mango-saat-whatsapp-btn.mango-saat-whatsapp-single');
			if (!target) return;
			if (target.classList.contains('mango-saat-cart-whatsapp') || target.classList.contains('mango-saat-mini-cart-whatsapp')) {
				var body = new URLSearchParams();
				body.append('action', 'mango_saat_clear_cart_after_whatsapp');
				body.append('nonce', '<?php echo esc_js( wp_create_nonce( 'mango_saat_cart' ) ); ?>');
				navigator.sendBeacon && navigator.sendBeacon('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', body);
			}
		});
		</script>
		<?php
	}
}