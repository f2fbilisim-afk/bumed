<?php

defined( 'ABSPATH' ) || exit;

final class Mango_Saat_Product_Meta {

	public static function init(): void {
		add_action( 'woocommerce_product_options_general_product_data', array( __CLASS__, 'box_product_field' ) );
		add_action( 'woocommerce_process_product_meta', array( __CLASS__, 'save_box_product_field' ) );
		add_action( 'woocommerce_product_after_variable_attributes', array( __CLASS__, 'variation_placeholder' ), 10, 3 );

		add_action( 'add_meta_boxes', array( __CLASS__, 'register_meta_boxes' ) );
		add_action( 'save_post_product', array( __CLASS__, 'save_main_product_meta' ), 10, 2 );

		add_action( 'wp_ajax_mango_saat_search_boxes', array( __CLASS__, 'ajax_search_boxes' ) );
		add_action( 'wp_ajax_mango_saat_search_products', array( __CLASS__, 'ajax_search_products' ) );

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_scripts' ) );
	}

	public static function admin_scripts( string $hook ): void {
		global $post;
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || ! $post || 'product' !== $post->post_type ) {
			return;
		}
		wp_enqueue_script(
			'mango-saat-admin',
			MANGO_SAAT_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery', 'selectWoo' ),
			MANGO_SAAT_VERSION,
			true
		);
		wp_localize_script(
			'mango-saat-admin',
			'mangoSaatAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'mango_saat_admin' ),
			)
		);
	}

	public static function box_product_field(): void {
		global $post;
		$is_box = get_post_meta( $post->ID, '_mango_is_box', true );
		woocommerce_wp_checkbox(
			array(
				'id'          => '_mango_is_box',
				'label'       => 'Kutu ürünü',
				'description' => 'İşaretlenirse katalogda, aramada ve marka arşivlerinde gizlenir; yalnızca ilişkili ana ürünlere eklenebilir.',
				'value'       => 'yes' === $is_box ? 'yes' : 'no',
			)
		);
	}

	public static function save_box_product_field( int $product_id ): void {
		$is_box = isset( $_POST['_mango_is_box'] ) ? 'yes' : 'no';
		update_post_meta( $product_id, '_mango_is_box', $is_box );
	}

	public static function variation_placeholder(): void {
		// Varyasyon düzeyinde kutu bağlantısı gerekmez.
	}

	public static function register_meta_boxes(): void {
		add_meta_box(
			'mango_saat_box_link',
			'Uyumlu kutu',
			array( __CLASS__, 'render_box_meta_box' ),
			'product',
			'side',
			'default'
		);
	}

	public static function render_box_meta_box( WP_Post $post ): void {
		if ( Mango_Saat_Plugin::is_box_product( (int) $post->ID ) ) {
			echo '<p>Bu ürün bir kutu ürünüdür. Ana ürünlerde ilişki kurulur.</p>';
			return;
		}

		$box_id   = (int) get_post_meta( $post->ID, '_mango_compatible_box_id', true );
		$box_price = get_post_meta( $post->ID, '_mango_box_custom_price', true );
		wp_nonce_field( 'mango_saat_box_meta', 'mango_saat_box_meta_nonce' );

		$selected = '';
		if ( $box_id ) {
			$product = wc_get_product( $box_id );
			if ( $product ) {
				$selected = sprintf( '<option value="%d" selected>%s</option>', $box_id, esc_html( $product->get_name() ) );
			}
		}
		?>
		<p>
			<label for="mango_compatible_box_id"><strong>Uyumlu kutu ürünü</strong></label>
			<select class="mango-saat-box-search" name="mango_compatible_box_id" id="mango_compatible_box_id" style="width:100%;">
				<?php echo $selected; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</select>
		</p>
		<p>
			<label for="mango_box_custom_price"><strong>Bu ürüne özel kutu fiyatı (₺)</strong></label>
			<input type="text" class="short" name="mango_box_custom_price" id="mango_box_custom_price" value="<?php echo esc_attr( $box_price ); ?>" placeholder="Boş = kutu ürün fiyatı" />
		</p>
		<?php
	}

	public static function save_main_product_meta( int $post_id, WP_Post $post ): void {
		if ( 'product' !== $post->post_type || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( ! isset( $_POST['mango_saat_box_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mango_saat_box_meta_nonce'] ) ), 'mango_saat_box_meta' ) ) {
			return;
		}
		if ( Mango_Saat_Plugin::is_box_product( $post_id ) ) {
			return;
		}

		$box_id = isset( $_POST['mango_compatible_box_id'] ) ? absint( $_POST['mango_compatible_box_id'] ) : 0;
		if ( $box_id && ! Mango_Saat_Plugin::is_box_product( $box_id ) ) {
			$box_id = 0;
		}
		update_post_meta( $post_id, '_mango_compatible_box_id', $box_id );

		$raw_price = isset( $_POST['mango_box_custom_price'] ) ? wc_clean( wp_unslash( $_POST['mango_box_custom_price'] ) ) : '';
		if ( '' === $raw_price ) {
			delete_post_meta( $post_id, '_mango_box_custom_price' );
		} else {
			update_post_meta( $post_id, '_mango_box_custom_price', wc_format_decimal( $raw_price ) );
		}
	}

	public static function get_compatible_box_id( int $product_id ): int {
		$id = (int) get_post_meta( $product_id, '_mango_compatible_box_id', true );
		if ( ! $id || ! Mango_Saat_Plugin::is_box_product( $id ) ) {
			return 0;
		}
		return $id;
	}

	public static function get_box_price_for_product( int $product_id, int $box_id ): float {
		$custom = get_post_meta( $product_id, '_mango_box_custom_price', true );
		if ( '' !== $custom && is_numeric( $custom ) ) {
			return (float) wc_format_decimal( $custom );
		}
		$box = wc_get_product( $box_id );
		return $box ? (float) $box->get_price() : 0.0;
	}

	public static function ajax_search_boxes(): void {
		check_ajax_referer( 'mango_saat_admin', 'nonce' );
		if ( ! current_user_can( 'edit_products' ) ) {
			wp_send_json_error();
		}
		self::send_product_search_results( true );
	}

	public static function ajax_search_products(): void {
		check_ajax_referer( 'mango_saat_admin', 'nonce' );
		if ( ! current_user_can( 'edit_products' ) ) {
			wp_send_json_error();
		}
		self::send_product_search_results( false );
	}

	private static function send_product_search_results( bool $boxes_only ): void {
		$term = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '';
		$meta_query = $boxes_only
			? array(
				array(
					'key'     => '_mango_is_box',
					'value'   => 'yes',
					'compare' => '=',
				),
			)
			: array(
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

		$args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 20,
			's'              => $term,
			'meta_query'     => $meta_query,
		);
		$query = new WP_Query( $args );
		$results = array();
		foreach ( $query->posts as $post ) {
			$results[] = array(
				'id'   => $post->ID,
				'text' => $post->post_title,
			);
		}
		wp_send_json( array( 'results' => $results ) );
	}
}