<?php
/**
 * Class for custom work.
 *
 * @package Checkout_Guard
 */

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// If class is exist, then don't execute this.
if ( ! class_exists( 'CGBS_Checkout_Guard' ) ) {

    /**
     * Class for the plugin's core.
     */
    class CGBS_Checkout_Guard {

        /**
         * Constructor for class.
         */
        public function __construct() {

            // Enqueue front-end scripts
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_style_scripts' ), 100 );


            // Enqueue Back end scripts
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_style_scripts' ), 100 );

        }


        /**
         * Enqueue style/script.
         *
         * @return void
         */
        public function enqueue_style_scripts() {

            // Custom plugin script.
            wp_enqueue_style(
                'checkout-guard-core-style',
                CGBS_URL . 'assets/css/checkout-guard.css',
                '',
                CGBS_VERSION
            );

            // Register plugin's JS script
            wp_register_script(
                'checkout-guard-custom-script',
                CGBS_URL . 'assets/js/checkout-guard.js',
                array(
                    'jquery',
                ),
                CGBS_VERSION,
                true
            );


            // Provide a global object to our JS file containing the AJAX url and security nonce
            wp_localize_script( 'checkout-guard-custom-script', 'ajaxObject',
                array(
                    'ajax_url'   => admin_url( 'admin-ajax.php' ),
                    'ajax_nonce' => wp_create_nonce( 'ajax_nonce' ),
                    //'plugin_url'	=> plugins_url('/', __FILE__),
                )
            );
            wp_enqueue_script( 'checkout-guard-custom-script' );

        }

        /**
         * Enqueue Admin style/script.
         *
         * @return void
         */
        public function admin_enqueue_style_scripts() {

            // Bail out if on another page.
            if ( ! is_admin() || ! isset( $_GET['page'] ) || $_GET['page'] != 'checkout-guard' ) {
                return;
            }

            // Custom plugin script.
            wp_enqueue_style(
                'checkout-guard-admin-style',
                CGBS_URL . 'assets/css/checkout-guard-admin.css',
                '',
                CGBS_VERSION
            );

            // Register plugin's JS script
            wp_register_script(
                'checkout-guard-admin-script',
                CGBS_URL . 'assets/js/checkout-guard-admin.js',
                array(
                    'jquery',
                ),
                CGBS_VERSION,
                true
            );
            wp_enqueue_script( 'checkout-guard-admin-script' );

        }

        /**
         * Get a parameter's value from the current URL
         *
         * @param $param
         *
         * @return string $param_value
         */
        public static function get_parameter_from_url( $param, $url = false ) {
            // Get the request URI
            if ( $url ) {
                $request_uri = sanitize_url( $url );
            } else {
                $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_url( $_SERVER['REQUEST_URI'] ) : '';
            }

            // Parse the query string
            $query_string = parse_url( $request_uri, PHP_URL_QUERY );

            // Parse the query parameters
            parse_str( $query_string, $query_params );

            // Get the dynamic parameter value
            $param_value = isset( $query_params[ $param ] ) ? $query_params[ $param ] : '';

            // Return the parameter's value
            return apply_filters( 'cgbs_get_parameter_from_url', $param_value, $param );
        }
    }

    new CGBS_Checkout_Guard();
}
