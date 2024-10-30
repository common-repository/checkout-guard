<?php
/**
 * Class for Woo Admin Settings.
 *
 * @package CGBS_Checkout_Guard_Admin_Settings
 */

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// If class is exist, then don't execute this.
if ( ! class_exists( 'CGBS_Checkout_Guard_Admin_Settings' ) ) {

    /**
     * Class for the plugin's core.
     */
    class CGBS_Checkout_Guard_Admin_Settings {

        const CGBS_BLOCKED_BY_EMAIL_TEXT = 'Sorry! Your email or your email service provider has been blocked. Please contact us if you think this is an error.';
        const CGBS_BLOCKED_BY_COUNTRY_TEXT = 'Sorry! We do not deliver to your country. Please contact us if you think this is an error.';
        const CGBS_BLOCKED_BY_IP_TEXT = 'Sorry! Your IP has been blocked. Please contact us if you think this is an error.';
        const CGBS_BLOCKED_BY_TOTAL_COST_TEXT = 'Sorry! It seems that the order cost is not within the expected range.';

        protected static $instance = null;

        static $cgbs_option_name;

        public static function get_instance() {
            null === self::$instance and self::$instance = new self;

            return self::$instance;
        }


        /**
         * Constructor for class.
         */
        public function __construct() {
            self::$cgbs_option_name = 'cgbs_admin_settings';

            $this->init();
        }

        public function init() {
            if ( is_admin() ) {
                add_action( 'admin_menu',
                    array( __CLASS__, 'add_settings_menu_entry' ), 100 );
                add_action( 'admin_init', array( $this, 'update_settings' ) );

                add_filter( 'woocommerce_admin_field_full_info',
                    array( $this, 'show_setting_info' ) );

                add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
            }
        }

        public function admin_enqueue_scripts() {

            /**
             * Enqueue the tooltip in the admin settings page
             */

            if ( isset( $_GET['page'] ) && $_GET['page'] == 'checkout-guard' ) {
                wp_enqueue_script(
                    'js-popper',
                    CGBS_URL . 'assets/js/popper.min.js',
                    array(
                        'jquery',
                    ),
                    CGBS_VERSION,
                    true
                );

                wp_enqueue_script(
                    'js-tooltip',
                    CGBS_URL . 'assets/js/tippy-bundle.umd.min.js',
                    array( 'jquery', 'js-popper' ),
                    CGBS_VERSION,
                    true
                );
                wp_enqueue_style( 'woocommerce_admin_styles' );
            }
        }

        public static function add_settings_menu_entry() {

            add_menu_page(
                __( 'Checkout Guard', 'checkout-guard' ),
                __( 'Checkout Guard', 'checkout-guard' ),
                'manage_woocommerce', // Required user capability
                'checkout-guard',
                array( __CLASS__, 'cgbs_submenu_page_callback' ),
                CGBS_URL . 'assets/images/icons/logo-sm.png'
            );
        }

        public static function cgbs_submenu_page_callback() {
            $template = apply_filters(
                'cgbs_submenu_page_callback',
                CGBS_PATH . 'templates/admin/cg-page.php'
            );

            include( $template );
        }


        private static function get_settings() {

            $settings     = get_option( self::$cgbs_option_name );
            $hidden_class = array();

            // Set checkbox value to yes in order to be checked
            $block_by_email = '';
            if ( isset( $settings['block_by_email']['enabled'] ) && $settings['block_by_email']['enabled'] === 1 ) {
                $block_by_email = 'yes';
            } else {
                $hidden_class['block_by_email'] = 'hidden';
            }

            // Blocked email list (single emails and domains)
            $block_by_email_list = '';
            if ( isset( $settings['block_by_email']['list'] ) ) {
                $block_by_email_list = $settings['block_by_email']['list'];
                if ( is_array( $block_by_email_list ) ) {
                    $block_by_email_list =
                        implode( PHP_EOL,
                            $settings['block_by_email']['list'] );
                }
            }

            // Block by email error text
            $block_by_email_text = '';
            if ( isset( $settings['block_by_email']['text'] ) ) {
                $block_by_email_text = esc_html(
                    $settings['block_by_email']['text'] );
            }
            if ( empty( $block_by_email_text ) ) {
                $block_by_email_text =
					CGBS_Checkout_Guard_Admin_Settings::CGBS_BLOCKED_BY_EMAIL_TEXT;
            }

            // Block by Country fetch settings
            $block_by_country = '';
            if ( isset( $settings['block_by_country']['enabled'] ) && $settings['block_by_country']['enabled'] === 1 ) {
                $block_by_country = 'yes';
            } else {
                $hidden_class['block_by_country'] = 'hidden';
            }

            $block_by_country_list = array();
            if ( isset( $settings['block_by_country']['list'] ) ) {
                $block_by_country_list =
                    (array) $settings['block_by_country']['list'];
            }
            $block_by_country_text = '';
            if ( isset( $settings['block_by_country']['text'] ) ) {
                $block_by_country_text =
                    esc_html( $settings['block_by_country']['text'] );
            }
            if ( empty( $block_by_country_text ) ) {
                $block_by_country_text =
					CGBS_Checkout_Guard_Admin_Settings::CGBS_BLOCKED_BY_COUNTRY_TEXT;
            }

            // Block by IP fetch settings
            $block_by_ip = '';
            if ( isset( $settings['block_by_ip']['enabled'] ) && $settings['block_by_ip']['enabled'] === 1 ) {
                $block_by_ip = 'yes';
            } else {
                $hidden_class['block_by_ip'] = 'hidden';
            }
            $block_by_ip_list = '';
            if ( isset( $settings['block_by_ip']['list'] ) ) {
                $block_by_ip_list = $settings['block_by_ip']['list'];
                if ( is_array( $block_by_ip_list ) ) {
                    $block_by_ip_list =
                        implode( PHP_EOL, $block_by_ip_list );
                }

            }
            $block_by_ip_text = '';
            if ( isset( $settings['block_by_ip']['text'] ) ) {
                $block_by_ip_text =
                    esc_html( $settings['block_by_ip']['text'] );
            }
            if ( empty( $block_by_ip_text ) ) {
                $block_by_ip_text =
					CGBS_Checkout_Guard_Admin_Settings::CGBS_BLOCKED_BY_IP_TEXT;
            }

            // Block by total cost fetch settings
            $block_by_total_cost = '';
            if ( isset( $settings['block_by_total_cost']['enabled'] ) && $settings['block_by_total_cost']['enabled'] === 1 ) {
                $block_by_total_cost = 'yes';
            } else {
                $hidden_class['block_by_total_cost'] = 'hidden';
            }

            $block_by_total_cost_min = 0;
            if ( isset( $settings['block_by_total_cost']['total_cost_min'] ) ) {
                $block_by_total_cost_min = (int)
                $settings['block_by_total_cost']['total_cost_min'];
            }
            $block_by_total_cost_max = 0;
            if ( isset( $settings['block_by_total_cost']['total_cost_max'] ) ) {
                $block_by_total_cost_max = (int)
                $settings['block_by_total_cost']['total_cost_max'];
            }
            $block_by_total_cost_text = '';
            if ( isset( $settings['block_by_total_cost']['text'] ) ) {
                $block_by_total_cost_text =
                    esc_html( $settings['block_by_total_cost']['text'] );
            }
            if ( empty( $block_by_total_cost_text ) ) {
                $block_by_total_cost_text =
					CGBS_Checkout_Guard_Admin_Settings::CGBS_BLOCKED_BY_TOTAL_COST_TEXT;
            }

            // The tooltips texts
            $tooltips = array(
                'block_by_email' => __(
                    'Restrict checkout for specific emails or email domains.',
                    'checkout-guard'
                ),

                'block_by_country'    =>
                    __( 'Restrict checkout for specific countries.', 'checkout-guard' ),
                'block_by_ip'         =>
                    __( 'Restrict checkout for specific IP addresses.',
                        'checkout-guard'
                    ),
                'block_by_total_cost' =>
                    __( 'Specify the acceptable range for the total cost of orders at checkout. You can either enforce a minimum or maximum amount as needed.',
                        'checkout-guard'
                    ),

            );
            $settings = array(
                'section_title'       => array(
                    'name' => __( 'Checkout Restrictions',
                        'checkout-guard' ),
                    'type' => 'title',
                    'desc' => '',
                    'id'   => 'cgbs_settings_section_title'
                ),
                // Block by Email
                'block_by_email'      => array(
                    'name'     => __( "Email", 'checkout-guard' ),
                    'desc'     => '<span class="woocommerce-help-tip cg-setting-info"></span>',
                    'desc_tip' => $tooltips['block_by_email'],
                    'type'     => 'checkbox',
                    'id'       => 'cgbs_block_by_email',
                    'value'    => $block_by_email,
                    'class'    => "form-ui-toggle",
                ),
                'block_by_email_list' => array(
                    'desc'      => __( "Enter one email address or domain per line to block them. (e.g. myname@domain.com or just domain.com)", 'checkout-guard' ),
                    'type'      => 'textarea',
                    'id'        => 'cgbs_block_by_email_list',
                    'value'     => $block_by_email_list,
                    'class'     => "large-text",
                    'row_class' => 'sub-option block_by_email ' . $hidden_class['block_by_email'],
                ),
                'block_by_email_text' => array(
                    'desc'      => __( "Add your \"custom blocked by email\" message.", 'checkout-guard' ),
                    'type'      => 'textarea',
                    'id'        => 'cgbs_block_by_email_text',
                    'value'     => $block_by_email_text,
                    'class'     => "large-text message",
                    'row_class' => 'sub-option block_by_email ' . $hidden_class['block_by_email'],
                ),
                // Block by Country
                'block_by_country'    => array(
                    'name'     => __( "Country", 'checkout-guard' ),
                    'desc'     => '<span class="woocommerce-help-tip cg-setting-info"></span>',
                    'desc_tip' => $tooltips['block_by_country'],
                    'type'     => 'checkbox',
                    'id'       => 'cgbs_block_by_country',
                    'value'    => $block_by_country,
                    'class'    => "form-ui-toggle",
                ),

                'block_by_country_list' => array(
                    'desc'      => __( "Choose the countries to restrict (Press CTRL or ⌘ and click to select multiple)", 'checkout-guard' ),
                    'type'      => 'multi_select_countries',
                    'id'        => 'cgbs_block_by_country_list',
                    'value'     => $block_by_country_list,
                    'class'     => "large-text",
                    'row_class' => 'sub-option block_by_country ' . $hidden_class['block_by_country'],
                ),
                'block_by_country_text' => array(
                    'desc'      => __( "Add your custom blocked by country message:", 'checkout-guard' ),
                    'type'      => 'textarea',
                    'id'        => 'cgbs_block_by_country_text',
                    'value'     => $block_by_country_text,
                    'class'     => "large-text message",
                    'row_class' => 'sub-option block_by_country ' . $hidden_class['block_by_country'],
                ),
                // Block by IP
                'block_by_ip'           => array(
                    'name'     => __( "IP Address", 'checkout-guard' ),
                    'desc'     => '<span class="woocommerce-help-tip cg-setting-info"></span>',
                    'desc_tip' => $tooltips['block_by_ip'],
                    'type'     => 'checkbox',
                    'id'       => 'cgbs_block_by_ip',
                    'value'    => $block_by_ip,
                    'class'    => "form-ui-toggle",
                ),

                'block_by_ip_list'    => array(
                    'desc'      => __( "Enter one IP address per line to block them (Supports IPv4 &amp; IPv6):", 'checkout-guard' ),
                    'type'      => 'textarea',
                    'id'        => 'cgbs_block_by_ip_list',
                    'value'     => $block_by_ip_list,
                    'class'     => "large-text",
                    'row_class' => 'sub-option block_by_ip ' .
                                   $hidden_class['block_by_ip'],
                ),
                'block_by_ip_text'    => array(
                    'desc'      => __( "Add your custom blocked by IP address message:", 'checkout-guard' ),
                    'type'      => 'textarea',
                    'id'        => 'cgbs_block_by_ip_text',
                    'value'     => $block_by_ip_text,
                    'class'     => "large-text message",
                    'row_class' => 'sub-option block_by_ip ' .
                                   $hidden_class['block_by_ip'],
                ),
                // Block by min / max checkout total cost
                'block_by_total_cost' => array(
                    'name'     => __( "Order Total Cost ", 'checkout-guard' ),
                    'desc'     => '<span class="woocommerce-help-tip cg-setting-info"></span>',
                    'desc_tip' => $tooltips['block_by_total_cost'],
                    'type'     => 'checkbox',
                    'id'       => 'cgbs_block_by_total_cost',
                    'value'    => $block_by_total_cost,
                    'class'    => "form-ui-toggle",
                ),

                'block_by_total_cost_min'  => array(
                    'desc'              => __( "Add the minimum order cost (0 to disable)", 'checkout-guard' ),
                    'type'              => 'number',
                    'id'                => 'cgbs_block_by_total_cost_min',
                    'value'             => $block_by_total_cost_min,
                    'class'             => "medium-text",
                    'row_class'         => 'sub-option block_by_total_cost ' .
                                           $hidden_class['block_by_total_cost'],
                    'custom_attributes' => array(
                        /**
                         * Use filter cgbs_block_by_total_cost_min_step to
                         * customize the minimum filter step
                         */
                        'step' => apply_filters(
                            'cgbs_block_by_total_cost_min_step', '1' ),
                        'min'  => apply_filters(
                            'cgbs_block_by_total_cost_min_min', '0' ),
                    ),
                ),
                'block_by_total_cost_max'  => array(
                    'desc'              => __( "Add the maximum order cost (0 to disable)", 'checkout-guard' ),
                    'type'              => 'number',
                    'id'                => 'cgbs_block_by_total_cost_max',
                    'value'             => $block_by_total_cost_max,
                    'class'             => "medium-text",
                    'row_class'         => 'sub-option block_by_total_cost ' .
                                           $hidden_class['block_by_total_cost'],
                    'custom_attributes' => array(
                        /**
                         * Use filter cgbs_block_by_total_cost_max_step to
                         * customize the minimum filter step
                         */
                        'step' => apply_filters(
                            'cgbs_block_by_total_cost_max_step', '1' ),
                        'min'  => apply_filters(
                            'cgbs_block_by_total_cost_max_step', '0' ),
                    ),
                ),
                'block_by_total_cost_text' => array(
                    'desc'      => __( "Add your custom blocked by Total Cost message:", 'checkout-guard' ),
                    'type'      => 'textarea',
                    'id'        => 'cgbs_block_by_total_cost_text',
                    'value'     => $block_by_total_cost_text,
                    'class'     => "large-text message",
                    'row_class' => 'sub-option block_by_total_cost ' .
                                   $hidden_class['block_by_total_cost'],
                ),
            );

            $settings['section_end'] = array(
                'type' => 'sectionend',
                'id'   => 'cgbs_settings_section_end'
            );

            return apply_filters( 'cgbs_admin_settings_form', $settings );
        }
        /**
         * Utility settings
         */
        private static function get_util_settings() {

            $settings     = get_option( self::$cgbs_option_name );
            $hidden_class = array();

            if ( isset( $settings['block_logger']['enabled'] ) && $settings['block_logger']['enabled'] === 1 ) {
                $block_logger = 'yes';
            } else {
                $hidden_class['block_logger'] = 'hidden';
            }

            $block_logger_history = 0;
            if ( isset( $settings['block_logger']['block_logger_history'] ) ) {
                $block_logger_history = (int)
                $settings['block_logger']['block_logger_history'];
            }

            // Tooltips
            $tooltips = array(
                'block_logger' => __('Log blocked user checkout attempts.', 'checkout-guard'),
            );

            $form_settings = array(
                'section_title'       => array(
                    'name' => __( 'Settings',
                        'checkout-guard' ),
                    'type' => 'title',
                    'desc' => '',
                    'id'   => 'cgbs_settings_section_title'
                )
            );

            // Add logger settings (Premium)
            $form_settings['block_logger']         = array(
                'name'     => __( "Log Blocked Checkouts", 'checkout-guard' ),
                'desc'     => '<span class="woocommerce-help-tip cg-setting-info"></span>',
                'desc_tip' => $tooltips['block_logger'],
                'type'     => 'checkbox',
                'id'       => 'cgbs_block_logger',
                'value'    => $block_logger,
                'class'    => "form-ui-toggle",
                'row_class' => 'main-switch'
            );
            $form_settings['block_logger_history'] = array(
                'desc'              => __( "Select the number of days to keep log history (0 for unlimited).", 'checkout-guard' ),
                'type'              => 'number',
                'suffix'            => __( 'Days', 'checkout-guard' ),
                'id'                => 'cgbs_block_logger_history',
                'value'             => $block_logger_history,
                'class'             => "small-text",
                'row_class'         => 'sub-option block_logger ' .
                                       $hidden_class['block_logger'],
                'custom_attributes' => array(
                    /**
                     * Use filter gcbs_block_logger_history to
                     * customize the minimum filter step
                     */
                    'step' => apply_filters(
                        'gcbs_block_logger_history', '1' ),
                    'min'  => apply_filters(
                        'gcbs_block_logger_history', '0' ),
                ),
            );

            $form_settings['section_end'] = array(
                'type' => 'sectionend',
                'id'   => 'cgbs_settings_section_end'
            );

            return apply_filters( 'cgbs_admin_util_settings_form', $form_settings );
        }

        /**
         * Store Checkout Guard settings to DB
         */
        public function update_settings() {
            $current_page = CGBS_Checkout_Guard::get_parameter_from_url( 'page' );

            /**
             * Get nonce
             */
            $nonce = "";
            if ( isset( $_POST['cgbs_settings_form_nonce'] ) ) {
                $nonce = sanitize_text_field( $_POST['cgbs_settings_form_nonce'] );
            }

            /**
             * Verify nonce
             */
            if ( ! wp_verify_nonce( $nonce, 'cgbs_settings_form_nonce' ) ) {
                return;
            }
            // Check if correct form has been submitted
            if ( $current_page !== 'checkout-guard' ||
                 ! isset( $_POST['cg-admin-settings-page'] ) ||
                 absint( $_POST['cg-admin-settings-page'] ) !== 1 ) {
                return;
            }

            $settings = get_option( self::$cgbs_option_name );

            // Update email list
            $email_list = '';
            if ( isset( $_POST['cgbs_block_by_email_list'] ) ) {
                $email_list = $this->sanitize_email_list_field(
                    sanitize_textarea_field( $_POST['cgbs_block_by_email_list'] )
                );
            }
            $email_text = '';
            if ( isset( $_POST['cgbs_block_by_email_text'] ) ) {
                $email_text = sanitize_textarea_field( $_POST['cgbs_block_by_email_text'] );
            }
            $settings['block_by_email'] = array(
                'enabled' => ( isset( $_POST['cgbs_block_by_email'] ) ? 1 : 0 ),
                'list'    => $email_list,
                'text'    => $email_text,
            );

            // Update Country List
            $country_list = array();
            if ( isset( $_POST['cgbs_block_by_country_list'] ) &&
                 is_array( $_POST['cgbs_block_by_country_list'] ) ) {
                $country_list = ( $this->sanitize_country_list(
                    array_map( 'sanitize_text_field',
                        $_POST['cgbs_block_by_country_list'] ) )
                );
            }
            $country_text = '';
            if ( isset( $_POST['cgbs_block_by_country_text'] ) ) {
                $country_text = sanitize_textarea_field( $_POST['cgbs_block_by_country_text'] );
            }

            $settings['block_by_country'] = array(
                'enabled' => ( isset( $_POST['cgbs_block_by_country'] ) ? 1 : 0 ),
                'list'    => $country_list,
                'text'    => $country_text,
            );

            // Save block by IP selections
            $ip_list = '';
            if ( isset( $_POST['cgbs_block_by_ip_list'] ) ) {
                $ip_list =
                    self::sanitize_ip_list_field(
                        sanitize_textarea_field(
                            $_POST['cgbs_block_by_ip_list'] )
                    );
            }
            $ip_text = '';
            if ( isset( $_POST['cgbs_block_by_ip_text'] ) ) {
                $ip_text =
                    sanitize_textarea_field( $_POST['cgbs_block_by_ip_text'] );
            }
            $settings['block_by_ip'] = array(
                'enabled' => ( isset( $_POST['cgbs_block_by_ip'] ) ? 1 : 0 ),
                'list'    => $ip_list,
                'text'    => $ip_text,
            );


            // Save block by Total Cost selections
            $total_cost_min = 0;
            if ( isset( $_POST['cgbs_block_by_total_cost_min'] ) ) {
                $total_cost_min = absint( $_POST['cgbs_block_by_total_cost_min'] );
            }
            $total_cost_max = 0;
            if ( isset( $_POST['cgbs_block_by_total_cost_max'] ) ) {
                $total_cost_max = absint( $_POST['cgbs_block_by_total_cost_max'] );

                // Set the max value to the minimum value if it is less than that
                if ( $total_cost_max != 0 && $total_cost_min > $total_cost_max ) {
                    $total_cost_max = $total_cost_min;
                }
            }
            $total_cost_text = '';
            if ( isset( $_POST['cgbs_block_by_total_cost_text'] ) ) {
                $total_cost_text =
                    sanitize_textarea_field(
                        $_POST['cgbs_block_by_total_cost_text'] );
            }
            $settings['block_by_total_cost'] = array(
                'enabled'        => (
                isset( $_POST['cgbs_block_by_total_cost'] ) ? 1 : 0 ),
                'total_cost_min' => $total_cost_min,
                'total_cost_max' => $total_cost_max,
                'text'           => $total_cost_text,
            );

            // Get log history values
            $log_days = 0;
            if ( isset( $_POST['cgbs_block_logger_history'] ) ) {
                $log_days = absint( $_POST['cgbs_block_logger_history'] );
            }
            $settings['block_logger'] = array(
                'enabled'        => (
                isset( $_POST['cgbs_block_logger'] ) ? 1 : 0 ),
                'block_logger_history' => $log_days,
            );

            update_option( self::$cgbs_option_name, $settings );

        }

        public function show_setting_info( $settings ) {
            ?>
			<tr>
				<th scope="row" class="" colspan="2">
                    <?php echo wp_kses_post( wpautop( wptexturize( $settings['content'] ) ) ); ?>
				</td></tr>
            <?php
        }

        /**
         * Utility function to sanitize an array
         *
         * @param array $array
         */
        private function sanitize_country_list( $array = array() ) {
            if ( class_exists( 'WC_Countries' ) ) {
                $countries_obj = new WC_Countries();
                $countries     = $countries_obj->__get( 'countries' );

                // Clear all items that are not included in WP countries list
                $clear_non_countries = array_filter( $array, function ( $value ) use ( $countries ) {
                    return isset( $countries[ $value ] );
                } );

                $keys = array_keys( $clear_non_countries );
                $keys = array_map( 'sanitize_key', $keys );

                $values = array_values( $clear_non_countries );
                $values = array_map( 'sanitize_text_field', $values );

                $sanitized_array = array_combine( $keys, $values );
            }

            return apply_filters( 'cgbs_sanitize_country_list', $sanitized_array, $array );
        }

        /**
         * Utility function to sanitize an email list (one per line)
         *
         * @param array $email_list
         */
        private function sanitize_email_list_field( $email_list ) {

            $email_data = explode( PHP_EOL, $email_list );

            // Clean up empty lines
            $cleaned_email_data = array_filter( $email_data );

            // Clean up duplicates
            $cleaned_email_data = array_unique( $cleaned_email_data );

            foreach ( $cleaned_email_data as $key => $email_item ) {
                $is_email  = boolval( filter_var( $email_item, FILTER_VALIDATE_EMAIL ) );
                $is_domain = $this->validate_domain_name( $email_item );

                if ( ! $is_email && ! $is_domain ) {
                    unset( $cleaned_email_data[ $key ] );
                }
            }

            return apply_filters( 'cgbs_sanitize_email_list_field',
                $cleaned_email_data,
                $email_data
            );
        }

        /**
         * Check if a string is a domain name
         *
         * @param $domain
         *
         * @return bool
         */
        private function validate_domain_name( $domain ) {
            // Define a regular expression pattern for a valid domain name
            $pattern = "/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/";

            // Use preg_match to check if the domain matches the pattern
            if ( preg_match( $pattern, $domain ) ) {
                return true;
            } else {
                return false;
            }
        }

        /**
         * Clean up an IP list
         */
        public static function sanitize_ip_list_field( $ip_list ) {
            if ( is_array( $ip_list ) ) {
                $ip_data = $ip_list;
            } else {
                $ip_data = explode( PHP_EOL, $ip_list );
            }

            // Clean up empty lines
            $cleaned_ip_data = array_filter( $ip_data );

            // Clean up duplicates
            $cleaned_ip_data = array_unique( $cleaned_ip_data );

            foreach ( $cleaned_ip_data as $key => $ip ) {

                // Clean up empty spaces
                if ( $ip != trim( $ip ) ) {
                    $ip                      = trim( $ip );
                    $cleaned_ip_data[ $key ] = $ip;
                }

                // Check if the IP address string is a valid IP
                $is_ip = boolval( filter_var( $ip, FILTER_VALIDATE_IP ) );

                if ( ! $is_ip ) {
                    unset( $cleaned_ip_data[ $key ] );
                }
            }

            return apply_filters( 'cgbs_sanitize_ip_list_field',
                $cleaned_ip_data,
                $ip_data
            );
        }

    }

    new CGBS_Checkout_Guard_Admin_Settings();
}
