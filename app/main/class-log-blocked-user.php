<?php
/**
 * Class for logging all blocked tries.
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
if ( ! class_exists( 'CGBS_Checkout_Guard_Logger' ) ) {

    /**
     * Class for the plugin's core.
     */
    class CGBS_Checkout_Guard_Logger {

        /**
         * Constructor for class.
         */
        public function __construct() {


        }

        /**
         * Error logger function
         *
         * @param $user_id
         * @param $error_text
         * @param $product_ids
         * @param $user_details
         * @param $cart_total
         */
        public static function log_errors( $user_id, $blocked_types, $product_ids, $user_details, $order_details, $cart_total ) {
            global $wpdb;

            $blocked_types_string = '';
            if ( is_array( $blocked_types ) ) {
                $blocked_types_string = implode( ',', $blocked_types );
            }
            $table_name = $wpdb->prefix . CGBS_Checkout_Guard_DB_Management::$db_table_name;

            $data = array(
                'user_id'       => $user_id,
                'errors'        => $blocked_types_string,
                'product_ids'   => serialize( $product_ids ),
                'user_details'  => serialize( $user_details ),
                'order_details' => serialize( $order_details ),
                'cart_total'    => $cart_total,
                'date_recorded' => date( 'Y-m-d H:i:s' ),
            );

            $wpdb->insert( $table_name, $data );
        }


    }

    new CGBS_Checkout_Guard_Logger();
}
