<?php

defined( 'ABSPATH' ) || exit;

final class Mango_Saat_Settings {

	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
	}

	public static function register_menu(): void {
		add_submenu_page(
			'woocommerce',
			'Mango Saat Sipariş',
			'Mango Saat Sipariş',
			'manage_woocommerce',
			'mango-saat-siparis',
			array( __CLASS__, 'render_page' )
		);
	}

	public static function register_settings(): void {
		register_setting(
			'mango_saat_siparis',
			'mango_saat_whatsapp_display',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '+90 541 463 16 77',
			)
		);
		register_setting(
			'mango_saat_siparis',
			'mango_saat_whatsapp_e164',
			array(
				'type'              => 'string',
				'sanitize_callback' => array( __CLASS__, 'sanitize_e164' ),
				'default'           => '905414631677',
			)
		);
	}

	public static function sanitize_e164( string $value ): string {
		$digits = preg_replace( '/\D+/', '', $value );
		return $digits ?: '905414631677';
	}

	public static function get_whatsapp_e164(): string {
		$stored = get_option( 'mango_saat_whatsapp_e164', '905414631677' );
		return preg_replace( '/\D+/', '', (string) $stored ) ?: '905414631677';
	}

	public static function render_page(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		if ( isset( $_POST['mango_saat_run_seeder'] ) && check_admin_referer( 'mango_saat_seeder' ) ) {
			$report = Mango_Saat_Seeder::run();
			echo '<div class="notice notice-success"><p>Kategori ve marka senkronu tamamlandı.</p></div>';
			echo '<pre style="background:#f6f6f6;padding:12px;max-width:800px;overflow:auto;">' . esc_html( wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) ) . '</pre>';
		}
		?>
		<div class="wrap">
			<h1>Mango Saat Sipariş</h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'mango_saat_siparis' ); ?>
				<table class="form-table">
					<tr>
						<th scope="row"><label for="mango_saat_whatsapp_display">WhatsApp numarası (görünen)</label></th>
						<td><input name="mango_saat_whatsapp_display" id="mango_saat_whatsapp_display" type="text" class="regular-text" value="<?php echo esc_attr( get_option( 'mango_saat_whatsapp_display', '+90 541 463 16 77' ) ); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="mango_saat_whatsapp_e164">WhatsApp numarası (bağlantı için, örn. 905414631677)</label></th>
						<td><input name="mango_saat_whatsapp_e164" id="mango_saat_whatsapp_e164" type="text" class="regular-text" value="<?php echo esc_attr( get_option( 'mango_saat_whatsapp_e164', '905414631677' ) ); ?>" /></td>
					</tr>
				</table>
				<?php submit_button( 'Kaydet' ); ?>
			</form>

			<hr />
			<h2>Katalog kayıtları</h2>
			<p>Şartnamedeki ana kategoriler ve 32 markayı mevcut kayıtları silmeden oluşturur veya günceller.</p>
			<form method="post">
				<?php wp_nonce_field( 'mango_saat_seeder' ); ?>
				<input type="hidden" name="mango_saat_run_seeder" value="1" />
				<?php submit_button( 'Kategori ve markaları senkronize et', 'secondary' ); ?>
			</form>
		</div>
		<?php
	}
}