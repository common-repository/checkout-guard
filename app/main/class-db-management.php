<?php
/**
 * Class for managing the DB.
 *
 * @package CGBS_Checkout_Guard_DB_Management
 */

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// If class exists, then don't execute this.
if ( ! class_exists( 'CGBS_Checkout_Guard_DB_Management' ) ) {

    /**
     * Class for the plugin's core.
     */
    class CGBS_Checkout_Guard_DB_Management {

        static $db_table_name;

        /**
         * Constructor for class.
         */
        public function __construct() {

            /**
             * Set up the log table name
             */
            self::$db_table_name = apply_filters(
                'cg_db_management_table_name',
                'checkout_guard_log'
            );

            // Register Activation hooks
            register_activation_hook( CGBS_FILE, array( __class__, 'create_log_table' ) );
            register_uninstall_hook( CGBS_FILE, array( __class__, 'delete_log_table' ) );
        }


        /**
         * Create the custom table for logging
         * Since 1.0.0
         */
        static function create_log_table() {
            global $wpdb;

            $table_name = $wpdb->prefix . self::$db_table_name;

            // Check if table exists.
            $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );

            if ( $wpdb->get_var( $query ) === $table_name ) {
                return true;
            }

            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE `{$table_name}`  (
                         `id` bigint NOT NULL AUTO_INCREMENT,
                          `user_id` bigint NULL,
                          `errors` text NULL,
                          `product_ids` text NULL,
                          `user_details` text null,
                          `order_details` text null,
                          `cart_total` float null,                          
                          `date_recorded` datetime NULL,
                          PRIMARY KEY (`id`) USING BTREE
                        ) ENGINE = InnoDB  {$charset_collate} ROW_FORMAT = COMPACT;
                        ";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            $result = dbDelta( $sql );
        }


        // Delete DB tables on Plugin removal
        static function delete_log_table() {
            global $wpdb;

            $tables = array(
                $wpdb->prefix . self::$db_table_name,
            );

            foreach ( $tables as $table_name ) {
                $sql = "DROP TABLE IF EXISTS $table_name";
                $wpdb->query( $sql );
            }
        }
    }

    new CGBS_Checkout_Guard_DB_Management();
}
