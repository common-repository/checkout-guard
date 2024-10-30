<?php
/**
 * Class for the WooCommerce checkout functions.
 *
 * @package CGBS_Checkout_Guard_Checkout
 */

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// If class is exist, then don't execute this.
if ( ! class_exists( 'CGBS_Checkout_Guard_Checkout' ) ) {

    /**
     * Class for the plugin's core.
     */
    class CGBS_Checkout_Guard_Checkout {

        /**
         * Constructor for class.
         */
        public function __construct() {

            $this->init();
        }

        public function init() {
            add_action( 'woocommerce_after_checkout_validation',
                array( __class__, 'check_checkout_regulations' ), 10, 2 );
        }

        /**
         * Check regulations against the user details on the checkout page
         */
        public static function check_checkout_regulations( $fields, $errors ) {

            // Bailout if there is a problem with the settings class
            if ( ! class_exists( 'CGBS_Checkout_Guard_Admin_Settings' ) ) {
                return;
            }

            // Bail out if the WooCommerce class is not loaded
            if ( ! class_exists( 'WooCommerce' ) ) {
                return;
            }

            // Get user settings
            $settings = get_option(
                CGBS_Checkout_Guard_Admin_Settings::$cgbs_option_name );

            // Bail out if there are no settings
            if ( empty( $settings ) ) {
                return;
            }

            // Should the errors be logged (flag)?
            $log_errors = false;
            if ( isset( $settings['block_logger']['enabled'] ) &&
                 $settings['block_logger']['enabled'] == 1 &&
                 class_exists( 'CGBS_Checkout_Guard_Logger' )
            ) {
                $log_errors = true;
            }

            /**
             * Cart details. Used for logging and max/min total cost
             */
            // Get the cart object
            $cart = WC()->cart;

            // Get the cart total (including shipping)
            $cart_total = $cart->total;

            // Get product IDs
            $product_ids = self::get_cart_product_ids();

            // Get user details
            $user_details = self::get_cart_billing_details();

            $blocked_types = array();

            /**
             * Check if the user is blocked by email
             */
            if ( isset( $settings['block_by_email']['enabled'] ) &&
                 $settings['block_by_email']['enabled'] == 1 ) {

                // Fetch the blocked email list
                if ( isset( $settings['block_by_email']['list'] ) &&
                     is_array( $settings['block_by_email']['list'] ) ) {

                    $blocked_emails = $settings['block_by_email']['list'];
                    $user_email     = mb_strtolower( $fields['billing_email'] );

                    $notice = $settings['block_by_email']['text'];
                    if ( empty( $notice ) ) {
                        $notice =
                            CGBS_Checkout_Guard_Admin_Settings::CGBS_BLOCKED_BY_EMAIL_TEXT;
                    }

                    // Loop through each blocked email
                    foreach ( $blocked_emails as $blocked_email ) {
                        if ( stristr( $user_email, $blocked_email ) ) {
                            $errors->add( 'block_by_email', $notice );
                            // Log error
                            if ( $log_errors ) {
                                $blocked_types[] = 'email';
                            }
                            break;
                        }
                    }
                }
            }

            /**
             * Check if the user is blocked by Country
             */
            if ( isset( $settings['block_by_country']['enabled'] ) &&
                 $settings['block_by_country']['enabled'] == 1 ) {

                // Fetch the blocked Country list
                if ( isset( $settings['block_by_country']['list'] ) &&
                     is_array( $settings['block_by_country']['list'] ) ) {

                    $blocked_countries = $settings['block_by_country']['list'];
                    $user_country      = mb_strtolower( $fields['billing_country'] );

                    $notice = $settings['block_by_country']['text'];
                    if ( empty( $notice ) ) {
                        $notice =
                            CGBS_Checkout_Guard_Admin_Settings::CGBS_BLOCKED_BY_COUNTRY_TEXT;
                    }

                    // Loop through each blocked Country
                    foreach ( $blocked_countries as $blocked_country ) {
                        if ( stristr( $user_country, $blocked_country ) ) {
                            $errors->add( 'block_by_country', $notice );
                            // Log error
                            if ( $log_errors ) {
                                $blocked_types[] = 'country';
                            }
                            break;
                        }
                    }
                }
            }

            /**
             * Check if the user is blocked by IP Address
             */
            if ( isset( $settings['block_by_ip']['enabled'] ) &&
                 $settings['block_by_ip']['enabled'] == 1 ) {

                // Fetch the blocked IP Address list
                if ( isset( $settings['block_by_ip']['list'] ) &&
                     is_array( $settings['block_by_ip']['list'] ) ) {

                    $blocked_ips = $settings['block_by_ip']['list'];

                    // Clean up IPs (in case something is wrong with them)
                    $blocked_ips =
                        CGBS_Checkout_Guard_Admin_Settings::sanitize_ip_list_field(
                            $blocked_ips );

                    // Get user IPs
                    $user_ips = array(
                        'remote_addr'    =>
                            sanitize_text_field( $_SERVER['REMOTE_ADDR'] ),
                        'forwarded_addr' =>
                            sanitize_text_field( $_SERVER['HTTP_X_FORWARDED_FOR'] ),
                    );

                    // Apply filter
                    $user_ips = apply_filters( 'cgbs_get_user_ips', $user_ips );

                    $notice = $settings['block_by_ip']['text'];
                    if ( empty( $notice ) ) {
                        $notice =
                            GBS_Checkout_Guard_Admin_Settings::CGBS_BLOCKED_BY_COUNTRY_TEXT;
                    }

                    // Loop through each blocked IP and check against
                    // the ser remote and forwarded address
                    foreach ( $blocked_ips as $blocked_ip ) {
                        if ( in_array( $blocked_ip, $user_ips ) ) {
                            $errors->add( 'block_by_ip', $notice );

                            // Log error
                            if ( $log_errors ) {
                                $blocked_types[] = 'ip';
                            }
                            break;
                        }
                    }
                }
            }

            /**
             * Check if the checkout is blocked by the price range
             */
            if ( isset( $settings['block_by_total_cost']['enabled'] ) &&
                 $settings['block_by_total_cost']['enabled'] == 1 ) {

                // Bail out if the WooCommerce class is not loaded
                if ( ! class_exists( 'WooCommerce' ) ) {
                    return;
                }

                // Fetch the min and max order total list
                if ( isset( $settings['block_by_total_cost']['total_cost_min'] ) ||
                     isset( $settings['block_by_total_cost']['total_cost_max'] ) ) {

                    // Get Minimum Total Cost
                    $cost_min = 0;
                    if ( isset( $settings['block_by_total_cost']['total_cost_min'] ) ) {
                        $cost_min = absint(
                            $settings['block_by_total_cost']['total_cost_min'] );
                    }
                    // Apply filter
                    $cost_min = apply_filters( 'cgbs_block_by_total_cost_min', $cost_min );

                    // Get Maximum Total Cost
                    $cost_max = 0;
                    if ( isset( $settings['block_by_total_cost']['total_cost_max'] ) ) {
                        $cost_max = absint(
                            $settings['block_by_total_cost']['total_cost_max'] );
                    }
                    // Apply filter
                    $cost_max = apply_filters( 'cgbs_block_by_total_cost_max', $cost_max );

                    // Get error notice
                    $notice = $settings['block_by_total_cost']['text'];
                    if ( empty( $notice ) ) {
                        $notice =
                            CGBS_Checkout_Guard_Admin_Settings::CGBS_BLOCKED_BY_TOTAL_COST_TEXT;
                    }

                    // Get the cart object
                    $cart = WC()->cart;

                    // Get the cart total (including shipping)
                    $cart_total = $cart->total;
                    // Get currency
                    $currency = get_woocommerce_currency();

                    // Apply filters
                    $cart_total = apply_filters(
                        'cgbs_block_by_total_cost_cart_total',
                        $cart_total,
                        $currency,
                        $cost_max,
                        $cost_min
                    );

                    // Check if the cart total is more than the total cost allowed
                    // 0 disables the check
                    if ( $cost_max != 0 && $cart_total > $cost_max ) {
                        // Add an extra notice to the error notice that displays the minimum
                        // cost amount
                        $maximum_extra_notice = apply_filters(
                            'cgbs_maximum_extra_notice',
                            sprintf(
                                __( 'The maximum allowed total cost is %s.',
                                    'checkout-guard' ),
                                wc_price( $cost_max )
                            ),
                            $cost_max,
                            $cart_total
                        );

                        // Add the extra notice to the error notice
                        $notice .= " " . $maximum_extra_notice;
                        $errors->add( 'block_by_total_cost_max', $notice );

                        // Log error
                        if ( $log_errors ) {
                            $blocked_types[] = 'total_cost_max';
                        }
                    }
                    // Else check if the cart total is less than the minimum total cost
                    // 0 disables the check
                    else if ( $cost_min != 0 && $cart_total < $cost_min ) {

                        // Add an extra notice to the error notice that displays the minimum
                        // cost amount
                        $minimum_extra_notice = apply_filters(
                            'cgbs_minimum_extra_notice',
                            sprintf(
                                __( 'The minimum allowed total cost is %s.',
                                    'checkout-guard' ),
                                wc_price( $cost_min )
                            ),
                            $cost_min,
                            $cart_total
                        );

                        // Add the extra notice to the error notice
                        $notice .= " " . $minimum_extra_notice;
                        $errors->add( 'block_by_total_cost_min', $notice );

                        // Log error
                        if ( $log_errors ) {
                            $blocked_types[] = 'total_cost_min';
                        }
                    }
                }
            }

            // Log error
            if ( $log_errors && is_array( $blocked_types ) && count( $blocked_types ) > 0 ) {
                CGBS_Checkout_Guard_Logger::log_errors(
                    get_current_user_id(),
                    $blocked_types,
                    $product_ids,
                    $user_details,
                    $fields,
                    $cart_total
                );
            }
        }

        /**
         * Get the cart's product IDs
         *
         * @return mixed
         */
        public static function get_cart_product_ids() {
            $product_ids = array();
            $cart        = null;
            $cart_items  = array();

            // Check if WooCommerce is active
            if ( class_exists( 'WooCommerce' ) ) {
                // Get the WooCommerce cart instance
                $cart = WC()->cart;

                // Get all cart items
                $cart_items = $cart->get_cart();

                // Loop through the cart items
                foreach ( $cart_items as $cart_item_key => $cart_item ) {
                    // Get product data for each item
                    $product_id    = $cart_item['product_id'];
                    $product_ids[] = $product_id;
                }
            }

            return apply_filters( 'gcbs_get_cart_product_ids', $product_ids, $cart, $cart_items );

        }

        /**
         * Get the cart's billing details
         *
         * @return mixed
         */
        public static function get_cart_billing_details() {
            $billing_details = array();
            $cart            = null;

            // Check if WooCommerce is active
            if ( class_exists( 'WooCommerce' ) ) {
                // Get the WooCommerce cart instance
                $cart = WC()->cart;

                if ( ! $cart->is_empty() ) {
                    $billing_details = $cart->get_customer()->get_billing();
                }
            }

            return apply_filters(
                'gcbs_get_cart_billing_details',
                $billing_details,
                $cart
            );
        }
    }

    new CGBS_Checkout_Guard_Checkout();
}
