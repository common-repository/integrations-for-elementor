<?php
/**
 * Plugin Name: Elementor Integrations
 * Plugin URI: https://github.com/obiPlabon
 * Description: Custom actions for Elementor form widget.
 * Version: 0.0.8
 * Author: obiPlabon
 * Author URI: https://fb.me/obiPlabon
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: elementor-integrations
 * Domain Path: /languages/
 *
 * @package Elementor_Integrations
 */

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Copyright 2020 obiPlabon <obiplabon@gmail.com>
*/

namespace obiPlabon;

defined( 'ABSPATH' ) || exit;

/**
 * Class Elementor_Integrations
 *
 * @package obiPlabon
 */
final class Elementor_Integrations {

    /**
     * Plugin version
     */
    const VERSION = '0.0.8';

    /**
     * Required minimum php version
     */
    const REQUIRED_PHP_VERSION = '5.4';

    /**
     * Plugin slug
     */
    const SLUG = 'elementor-integrations';

    /**
     * Initialize the plugin function here
     *
     * @return void
     */
    public static function init() {
        // Check for required PHP version
        if ( version_compare( PHP_VERSION, self::REQUIRED_PHP_VERSION, '<' ) ) {
            add_action( 'admin_notices', [ __CLASS__, 'show_required_php_version_missing_notice' ] );
            return;
        }

        add_action( 'plugins_loaded', [ __CLASS__, 'on_plugins_loaded' ], 15 );
    }

    public static function on_plugins_loaded() {
        // Check if Elementor installed and activated
        if ( ! did_action( 'elementor/loaded' ) ) {
            add_action( 'admin_notices', [ __CLASS__, 'show_elementor_missing_notice' ] );
            return;
        }

        // Check if Elementor Pro installed and activated
        if ( ! defined( 'ELEMENTOR_PRO_VERSION' ) ) {
            add_action( 'admin_notices', [ __CLASS__, 'show_elementor_pro_missing_notice' ] );
            return;
        }

        add_action( 'elementor_pro/init', [ __CLASS__, 'register_form_actions' ] );
    }

    /**
     * Get forms moduel
     *
     * @return \ElementorPro\Modules\Forms;
     */
    protected static function get_forms_module() {
        return \ElementorPro\Plugin::instance()->modules_manager->get_modules( 'forms' );
    }

    /**
     * Register forms integrations
     *
     * @return void
     */
    public static function register_form_actions() {
        include_once __DIR__ . '/integrations/newsletter.php';

        $newsletter = new Integrations\Newsletter();
    
        // Register the action with form widget
        self::get_forms_module()->add_form_action( $newsletter->get_name(), $newsletter );
    }

    /**
     * Show required minimum php version missing notice to admin
     *
     * @return void
     */
    public static function show_required_php_version_missing_notice() {
        if ( ! self::user_can_see_notice() ) {
            return;
        }

        $notice = sprintf(
            /* translators: 1: Plugin name 2: PHP 3: Required PHP version */
            esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'elementor-integrations' ),
            '<strong>' . esc_html__( 'Elementor Integrations', 'elementor-integrations' ) . '</strong>',
            '<strong>' . esc_html__( 'PHP', 'elementor-integrations' ) . '</strong>',
            self::REQUIRED_PHP_VERSION
        );

        printf( '<div class="notice notice-warning is-dismissible"><p style="padding: 13px 0">%1$s</p></div>', $notice );
    }

    /**
     * Show Elementor missing notice to admin
     *
     * @return void
     */
    public static function show_elementor_missing_notice() {
        if ( ! self::user_can_see_notice() ) {
            return;
        }

        $notice = sprintf(
            /* translators: 1: Plugin name 2: Elementor 3: Elementor installation link */
            __( '%1$s requires %2$s to be installed and activated to function properly. %3$s', 'elementor-integrations' ),
            '<strong>' . __( 'Elementor Integrations', 'elementor-integrations' ) . '</strong>',
            '<strong>' . __( 'Elementor', 'elementor-integrations' ) . '</strong>',
            '<a href="' . esc_url( admin_url( 'plugin-install.php?s=Elementor&tab=search&type=term' ) ) . '">' . __( 'Please click on this link and install Elementor', 'elementor-integrations' ) . '</a>'
        );

        printf( '<div class="notice notice-warning is-dismissible"><p style="padding: 13px 0">%1$s</p></div>', $notice );
    }

    /**
     * Show Elementor Pro missing notice to admin
     *
     * @return void
     */
    public static function show_elementor_pro_missing_notice() {
        if ( ! self::user_can_see_notice() ) {
            return;
        }

        $notice = sprintf(
            /* translators: 1: Plugin name 2: Elementor Pro */
            __( '%1$s requires %2$s to be installed and activated to function properly.', 'elementor-integrations' ),
            '<strong>' . __( 'Elementor Integrations', 'elementor-integrations' ) . '</strong>',
            '<strong>' . __( 'Elementor Pro', 'elementor-integrations' ) . '</strong>'
        );

        printf( '<div class="notice notice-warning is-dismissible"><p style="padding: 13px 0">%1$s</p></div>', $notice );
    }

    /**
     * Check if current user has the capability to install or activate plugins
     *
     * @return bool
     */
    private static function user_can_see_notice() {
        return current_user_can( 'install_plugins' ) || current_user_can( 'activate_plugins' );
    }

}

Elementor_Integrations::init();
