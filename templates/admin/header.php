<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * The template for the header of the admin page.
 *
 */
?>
<div class="wrap nosubsub cg-admin  postbox-container">
	<h1 class="screen-heading cg-settings-screen">
		<img class="shield" src="<?php echo CGBS_URL . 'assets/images/icons/logo-header.png'; ?>">
		<span><?php esc_html_e( 'Checkout Guard', 'checkout-guard' ); ?>:</span>
        <?php esc_html_e( 'Block Spam Woo Orders', 'checkout-guard' ); ?>
	</h1>

