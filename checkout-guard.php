<?php
/**
 * Checkout Guard: Block Spam Woo Orders
 *
 * Defend your e-shop against spam Woo orders, boost checkout security, and streamline your e-commerce experience. Say goodbye to fraudulent and unwanted orders, and focus on what truly matters – growing your business.
 *
 * @link              https://gianniskipouros.com
 * @since             1.0.0
 * @package           checkout-guard
 *
 * @wordpress-plugin
 * Plugin Name:       Checkout Guard: Block Spam Woo Orders
 * Description:       Enhance Woo checkout security. Block spam orders and protect your revenue and customer's trust.
 * Version:           1.0.1
 * Author:            Giannis Kipouros
 * Author URI:        https://gianniskipouros.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       checkout-guard
 * Domain Path:       /languages
 */


if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Main file, contains the plugin metadata and activation processes
 *
 * @package    checkout-guard
 */
if ( ! defined( 'CGBS_VERSION' ) ) {
    /**
     * The version of the plugin.
     */
    define( 'CGBS_VERSION', '1.0.1' );
}
if ( ! defined( 'CGBS_FILE' ) ) {
    /**
     * The url to the plugin directory.
     */
    define( 'CGBS_FILE', __FILE__ );
}
if ( ! defined( 'CGBS_PATH' ) ) {
    /**
     *  The server file system path to the plugin directory.
     */
    define( 'CGBS_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'CGBS_URL' ) ) {
    /**
     * The url to the plugin directory.
     */
    define( 'CGBS_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'CGBS_BASE_NAME' ) ) {
    /**
     * The url to the plugin directory.
     */
    define( 'CGBS_BASE_NAME', plugin_basename( __FILE__ ) );
}

/**
 * Include files.
 */
function cgbs_include_plugin_files() {

    // Load plugin files only if the current user is an admin
    if ( ! current_user_can( 'activate_plugins' ) ) {
        return;
    }

    // Include Class files
    $files = array(
        'app/main/class-main',
        'app/main/class-admin-settings',
       // 'app/main/class-log-blocked-user',
        'app/main/class-woo-checkout',
    );

    // Include Includes files
    $includes = array();

    // Merge the two arrays
    $files = array_merge( $files, $includes );

    foreach ( $files as $file ) {
        // Include functions file.
        require CGBS_PATH . $file . '.php';
    }
}

add_action( 'plugins_loaded', 'cgbs_include_plugin_files' );


/**
 * Load plugin's textdomain.
 */
function cgbs_language_textdomain_init() {
    // Localization
    load_plugin_textdomain( 'checkout-guard', false, dirname( plugin_basename( __FILE__ ) ) . "/languages" );
}

// Add actions
add_action( 'init', 'cgbs_language_textdomain_init' );


// Run on plugin activation
register_activation_hook( __FILE__, 'cgbs_activate_plugin' );
function cgbs_activate_plugin() {
    if ( ! current_user_can( 'activate_plugins' ) ) {
        return;
    }

    // Set this option to flag that the plugin has just been activated
    //  it is impossible to use add_action() or add_filter() type calls here.
    add_option( 'cgbs_activated', 'checkout-guard' );
}

function cgbs_load_plugin() {
    if ( is_admin() && get_option( 'cgbs_activated' ) == 'checkout-guard' ) {

        // Remove the newly enabled plugin flag
        delete_option( 'cgbs_activated' );

        // Redirect to the settings page after the plugin is activated
        wp_redirect( admin_url( 'admin.php?page=checkout-guard' ) );
        exit;
    }
}
add_action( 'admin_init', 'cgbs_load_plugin' );

// INCLUDES - Need to run First
include( CGBS_PATH . 'app/main/class-db-management.php' );

/**
 * WC Checkout blocks incompatibility
 */
add_action( 'before_woocommerce_init', function () {
    if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
        // Checkout Blocks compatibility flag
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, false );
        // HPOS compatibility flag
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
} );
