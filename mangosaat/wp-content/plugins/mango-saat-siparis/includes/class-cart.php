<?php

defined( 'ABSPATH' ) || exit;

final class Mango_Saat_Cart {

	public static function init(): void {
		add_action( 'woocommerce_after_cart_item_name', array( __CLASS__, 'render_cart_item_box_offer' ), 15, 2 );

		add_filter( 'woocommerce_add_cart_item_data', array( __CLASS__, 'add_cart_item_data' ), 10, 3 );
		add_filter( 'woocommerce_add_cart_item', array( __CLASS__, 'hydrate_cart_item' ), 10, 2 );
		add_action( 'woocommerce_before_calculate_totals', array( __CLASS__, 'apply_box_pricing' ), 20, 1 );
		add_filter( 'woocommerce_cart_item_name', array( __CLASS__, 'cart_item_name' ), 10, 3 );
		add_filter( 'woocommerce_get_item_data', array( __CLASS__, 'display_item_data' ), 10, 2 );
		add_action( 'woocommerce_check_cart_items', array( __CLASS__, 'validate_cart_items' ) );
		add_filter( 'woocommerce_cart_id', array( __CLASS__, 'distinct_cart_ids' ), 10, 5 );

		add_action( 'wp_ajax_mango_saat_clear_cart_after_whatsapp', array( __CLASS__, 'ajax_clear_cart' ) );
		add_action( 'wp_ajax_nopriv_mango_saat_clear_cart_after_whatsapp', array( __CLASS__, 'ajax_clear_cart' ) );
		add_action( 'wp_ajax_mango_saat_toggle_cart_box', array( __CLASS__, 'ajax_toggle_cart_box' ) );
		add_action( 'wp_ajax_nopriv_mango_saat_toggle_cart_box', array( __CLASS__, 'ajax_toggle_cart_box' ) );
	}

	/**
	 * @return array<string, mixed>
	 */
	public static function build_box_cart_item_data( int $product_id, int $variation_id, bool $with_box, bool $show_notices = true ): array {
		$main_id = $variation_id ?: $product_id;

		if ( ! $with_box ) {
			return array(
				'mango_with_box' => 0,
			);
		}

		$box_id = Mango_Saat_Product_Meta::get_compatible_box_id( $main_id );
		if ( ! $box_id ) {
			return array(
				'mango_with_box' => 0,
			);
		}

		$box = wc_get_product( $box_id );
		if ( ! $box || ! $box->is_in_stock() ) {
			if ( $show_notices ) {
				wc_add_notice( __( 'Seçilen kutu stokta yok.', 'mango-saat-siparis' ), 'notice' );
			}
			return array(
				'mango_with_box' => 0,
			);
		}

		$data = array(
			'mango_with_box'  => 1,
			'mango_box_id'    => $box_id,
			'mango_box_price' => Mango_Saat_Product_Meta::get_box_price_for_product( $main_id, $box_id ),
			'mango_box_name'  => $box->get_name(),
		);
		$data['unique_key'] = md5( wp_json_encode( $data ) . microtime() );

		return $data;
	}

	public static function render_cart_item_box_offer( array $cart_item, string $cart_item_key ): void {
		if ( empty( $cart_item['data'] ) || ! $cart_item['data'] instanceof WC_Product ) {
			return;
		}

		$product = $cart_item['data'];
		if ( Mango_Saat_Plugin::is_box_product( $product->get_id() ) ) {
			return;
		}

		$main_id = ! empty( $cart_item['variation_id'] ) ? (int) $cart_item['variation_id'] : (int) $cart_item['product_id'];
		$box_id  = Mango_Saat_Product_Meta::get_compatible_box_id( $main_id );
		if ( ! $box_id ) {
			return;
		}

		$checked = ! empty( $cart_item['mango_with_box'] );
		echo self::get_box_offer_markup( $main_id, $box_id, $checked, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public static function get_box_offer_markup( int $main_product_id, int $box_id, bool $checked, string $cart_item_key = '' ): string {
		$box = wc_get_product( $box_id );
		if ( ! $box ) {
			return '';
		}

		$price    = Mango_Saat_Product_Meta::get_box_price_for_product( $main_product_id, $box_id );
		$in_stock = $box->is_in_stock();
		$image    = $box->get_image( 'thumbnail', array( 'class' => 'mango-saat-box-thumb' ) );
		$is_cart  = '' !== $cart_item_key;

		ob_start();
		?>
		<div class="mango-saat-box-offer <?php echo $is_cart ? 'mango-saat-box-offer--cart' : ''; ?>" data-box-id="<?php echo esc_attr( $box_id ); ?>" data-box-price="<?php echo esc_attr( $price ); ?>" data-in-stock="<?php echo $in_stock ? '1' : '0'; ?>">
			<label class="mango-saat-box-label">
				<input type="checkbox"
					class="mango-saat-box-checkbox <?php echo $is_cart ? 'mango-saat-cart-box-checkbox' : ''; ?>"
					name="<?php echo $is_cart ? '' : 'mango_with_box'; ?>"
					value="1"
					<?php checked( $checked ); ?>
					<?php echo $is_cart ? 'data-cart-key="' . esc_attr( $cart_item_key ) . '"' : ''; ?>
					<?php disabled( ! $in_stock && ! $checked ); ?> />
				<span class="mango-saat-box-content">
					<?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span class="mango-saat-box-text">
						<strong><?php esc_html_e( 'Kutuyla al', 'mango-saat-siparis' ); ?></strong>
						<span class="mango-saat-box-name"><?php echo esc_html( $box->get_name() ); ?></span>
						<span class="mango-saat-box-price">+<?php echo wp_kses_post( wc_price( $price ) ); ?></span>
					</span>
				</span>
			</label>
			<?php if ( ! $in_stock ) : ?>
				<p class="mango-saat-box-stock-notice"><?php esc_html_e( 'Kutu şu an stokta yok; kutusuz sipariş verebilirsiniz.', 'mango-saat-siparis' ); ?></p>
			<?php endif; ?>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	public static function add_cart_item_data( array $cart_item_data, int $product_id, int $variation_id ): array {
		if ( Mango_Saat_Plugin::is_box_product( $product_id ) ) {
			wc_add_notice( __( 'Kutu ürünleri yalnızca ana ürünle sepete eklenir.', 'mango-saat-siparis' ), 'error' );
			return $cart_item_data;
		}

		if ( ! empty( $cart_item_data['mango_with_box'] ) && ! empty( $cart_item_data['mango_box_id'] ) ) {
			return $cart_item_data;
		}

		$with_box = ! empty( $_REQUEST['mango_with_box'] ) || ! empty( $cart_item_data['mango_with_box'] );
		$box_data = self::build_box_cart_item_data( $product_id, $variation_id, $with_box );
		return array_merge( $cart_item_data, $box_data );
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

	public static function ajax_toggle_cart_box(): void {
		check_ajax_referer( 'mango_saat_cart', 'nonce' );

		$cart_key = isset( $_POST['cart_key'] ) ? wc_clean( wp_unslash( $_POST['cart_key'] ) ) : '';
		$with_box = ! empty( $_POST['with_box'] );

		if ( ! $cart_key || ! WC()->cart ) {
			wp_send_json_error( array( 'message' => __( 'Sepet satırı bulunamadı.', 'mango-saat-siparis' ) ) );
		}

		$item = WC()->cart->get_cart_item( $cart_key );
		if ( ! $item ) {
			wp_send_json_error( array( 'message' => __( 'Sepet satırı bulunamadı.', 'mango-saat-siparis' ) ) );
		}

		$product_id   = (int) $item['product_id'];
		$variation_id = (int) ( $item['variation_id'] ?? 0 );
		$qty          = (int) $item['quantity'];
		$variation    = isset( $item['variation'] ) ? (array) $item['variation'] : array();

		$box_data = self::build_box_cart_item_data( $product_id, $variation_id, $with_box, false );
		if ( $with_box && empty( $box_data['mango_with_box'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Kutu şu an eklenemiyor.', 'mango-saat-siparis' ) ) );
		}

		WC()->cart->remove_cart_item( $cart_key );
		WC()->cart->add_to_cart( $product_id, $qty, $variation_id, $variation, $box_data );

		wp_send_json_success();
	}
}