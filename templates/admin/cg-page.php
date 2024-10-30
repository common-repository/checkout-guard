<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Template file for WP admin settings page
 */
require_once( CGBS_PATH . 'templates/admin/header.php' );

?>
<div class="description">
    <?php
    $notice = __( 'Secure your Woo store with <strong>Checkout Guard</strong>, the ultimate protection plugin for your checkout process. With <strong>Checkout Guard</strong>, you can effortlessly enforce strict order regulations to ensure a <strong>smooth and secure shopping experience for both you and your customers</strong>.', 'checkout-guard' );

    $allowed_html = array(
        'strong' => array(),
    );
    echo wp_kses( $notice, $allowed_html );
    ?>
</div>
<hr>
<?php
if ( ! function_exists( 'woocommerce_admin_fields' ) ) {
    ?>
	<section id="cg-admin-no-wc postbox ">
		<div class="inside">
			<p><?php esc_html_e( 'Checkout Guard is a Woo extension.', 'checkout-guard' ); ?></p>
			<p><?php esc_html_e( 'Please install and enable the Woo plugin.', 'checkout-guard' ); ?></p>
		</div>
	</section>
<?php } else { ?>
	<div id="cg-admin-form-wrapper">
		<section id="cg-admin-form">

			<form method="POST">
				<input type="hidden" name="cg-admin-settings-page" value="1">
                <?php
                wp_nonce_field( 'cgbs_settings_form_nonce', 'cgbs_settings_form_nonce' );
                ?>
				<div class="postbox ">
					<div class="inside">
                        <?php woocommerce_admin_fields( self::get_settings() ); ?>
					</div>
				</div>

				<div class="inside ">
                    <?php submit_button(); ?>
				</div>
			</form>
		</section>
		<div class="cg-admin-sidebar">
			<div class="tacpp-info">

				<h3><?php esc_html_e( 'Checkout Guard', 'checkout-guard' ); ?></h3>
				<p><?php esc_html_e( 'Version', 'checkout-guard' ); ?>: <?php echo CGBS_VERSION; ?> </p>
			</div>

			<div class="tacpp-links">
				<h3><?php esc_html_e( 'Useful Links', 'checkout-guard' ); ?></h3>
				<ul>
					<li><?php esc_html_e( 'Do you like this plugin?', 'checkout-guard' ); ?>
						<a href="https://wordpress.org/support/plugin/checkout-guard/reviews/#new-post"><?php esc_html_e( 'Rate us', 'checkout-guard' ); ?></a>
					</li>
					<li><?php esc_html_e( 'Support', 'checkout-guard' ); ?>: <a href="https://wordpress.org/support/plugin/checkout-guard/"> <?php esc_html_e( 'WordPress.org', 'checkout-guard' ); ?></a>
					</li>
					<li><a href="https://wordpress.org/plugins/checkout-guard/#developers"><?php esc_html_e( 'Changelog', 'checkout-guard' ); ?></a></li>
				</ul>
			</div>
		</div>
	</div>
<?php }
?>
</div> <!-- End cg-admin section -->
