<?php

/*
 * Plugin Name: Column Ordering For Elementor
 * Plugin URI:https://kamal.pw/elementor-column-order
 * Description: This plugin help you to ordering column from under section
 * Version: 1.0.0
 * Author: Kamal H.
 * Author URI: https://kamal.pw/
 * Text Domain: elementor-column-order
 * Domain Path: /languages/
 * License: GNU General Public License v2 or later
 */

 use Elementor\Element_Column;
 use Elementor\Controls_Manager;

final class Elementor_Column_Order {
    /**
     * Plugin Version
     *
     * @var string The plugin version.
     * @since 1.0.0
     */
    const VERSION = '1.0.0';

    /**
     * Minimum Elementor Version
     *
     * @var string Minimum Elementor version required to run the plugin.
     * @since 1.0.0
     */
    const MINIMUM_ELEMENTOR_VERSION = '2.0.0';

    /**
     * Minimum PHP Version
     *
     * @var string Minimum PHP version required to run the plugin.
     * @since 1.0.0
     */
    const MINIMUM_PHP_VERSION = '7.0';

    /**
     * Instance
     *
     * @access private
     * @static
     * @var Elementor_Test_Extension The single instance of the class.
     * @since 1.0.0
     */
    private static $_instance = null;

    /**
     * Instance
     *
     * Ensures only one instance of the class is loaded or can be loaded.
     *
     * @access public
     * @static
     * @since 1.0.0
     *
     * @return Elementor_Test_Extension An instance of the class.
     */
    public static function instance() {

        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;

    }

    /**
     * Constructor
     *
     * @access public
     * @since 1.0.0
     */
    public function __construct() {

        add_action( 'init', [$this, 'i18n'] );
        add_action( 'plugins_loaded', [$this, 'init'] );

    }

    /**
     * Load Textdomain
     *
     * Load plugin localization files.
     *
     * Fired by `init` action hook.
     *
     * @access public
     * @since 1.0.0
     */
    public function i18n() {

        load_plugin_textdomain( 'elementor-column-order' );

    }

    /**
     * Initialize the plugin
     *
     * Load the plugin only after Elementor (and other plugins) are loaded.
     * Checks for basic plugin requirements, if one check fail don't continue,
     * if all check have passed load the files required to run the plugin.
     *
     * Fired by `plugins_loaded` action hook.
     *
     * @access public
     * @since 1.0.0
     */
    public function init() {

        // Check if Elementor installed and activated
        if ( !did_action( 'elementor/loaded' ) ) {
            add_action( 'admin_notices', [$this, 'admin_notice_missing_main_plugin'] );
            return;
        }

        // Check for required Elementor version
        if ( !version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
            add_action( 'admin_notices', [$this, 'admin_notice_minimum_elementor_version'] );
            return;
        }

        // Check for required PHP version
        if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
            add_action( 'admin_notices', [$this, 'admin_notice_minimum_php_version'] );
            return;
        }

        add_action( 'elementor/element/column/layout/before_section_end', [__CLASS__, 'add_controls'] );


    }

    /**
     * Admin notice
     *
     * Warning when the site doesn't have Elementor installed or activated.
     *
     * @access public
     * @since 1.0.0
     */
    public function admin_notice_missing_main_plugin() {

        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }

        $message = sprintf(
            /* translators: 1: Plugin name 2: Elementor */
            esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'elementor-column-order' ),
            '<strong>' . esc_html__( 'Elementor Column Order', 'elementor-column-order' ) . '</strong>',
            '<strong>' . esc_html__( 'Elementor', 'elementor-column-order' ) . '</strong>'
        );

        printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

    }

    /**
     * Admin notice
     *
     * Warning when the site doesn't have a minimum required Elementor version.
     *
     * @access public
     * @since 1.0.0
     */
    public function admin_notice_minimum_elementor_version() {

        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }

        $message = sprintf(
            /* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
            esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'elementor-column-order' ),
            '<strong>' . esc_html__( 'Elementor Column Order', 'elementor-column-order' ) . '</strong>',
            '<strong>' . esc_html__( 'Elementor', 'elementor-column-order' ) . '</strong>',
            self::MINIMUM_ELEMENTOR_VERSION
        );

        printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

    }

    /**
     * Admin notice
     *
     * Warning when the site doesn't have a minimum required PHP version.
     *
     * @access public
     * @since 1.0.0
     */
    public function admin_notice_minimum_php_version() {

        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }

        $message = sprintf(
            /* translators: 1: Plugin name 2: PHP 3: Required PHP version */
            esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'elementor-column-order' ),
            '<strong>' . esc_html__( 'Elementor Column Order', 'elementor-column-order' ) . '</strong>',
            '<strong>' . esc_html__( 'PHP', 'elementor-column-order' ) . '</strong>',
            self::MINIMUM_PHP_VERSION
        );

        printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

    }

    public static function add_controls( Element_Column $element ) {
        $element->add_responsive_control(
            '_elco_column_width',
            [
                'label'       => __( 'Custom Column Width', 'elementor-column-order' ),
                'type'        => Controls_Manager::TEXT,
                'separator'   => 'before',
                'label_block' => true,
                'description' => __( 'Here you can set the column width the way you always wanted to! e.g 250px, 50%, calc(100% - 250px)', 'elementor-column-order' ),
                'selectors'   => [
                    '{{WRAPPER}}.elementor-column' => 'width: {{VALUE}};',
                ],
            ]
        );

        $element->add_responsive_control(
            '_elco_column_order',
            [
                'label'          => __( 'Column Order', 'elementor-column-order' ),
                'type'           => Controls_Manager::NUMBER,
                'style_transfer' => true,
                'selectors'      => [
                    '{{WRAPPER}}.elementor-column' => '-webkit-box-ordinal-group: calc({{VALUE}} + 1 ); -ms-flex-order:{{VALUE}}; order: {{VALUE}};',
                ],
                'description'    => sprintf(
                    __( 'Column ordering is a great addition for responsive design. You can learn more about CSS order property from %sMDN%s.', 'elementor-column-order' ),
                    '<a
href="https://developer.mozilla.org/en-US/docs/Web/CSS/CSS_Flexible_Box_Layout/Ordering_Flex_Items#The_order_property" target="_blank">',
                    '</a>'
                ),
            ]
        );
    }

}

Elementor_Column_Order::instance();