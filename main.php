<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @wordpress-plugin
 * Plugin Name:       Plugin Profiles
 * Description:       Allows you to create different profiles of different set ot plugins
 * Version:           1.0.0
 * Author:            Kristiyan Katsarov
 * Author URI:        https://katsarov.info
 * Text Domain:       pprofiler
 * License:           GPLv3
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define ( 'PPROFILES_ROOT', __DIR__ );
define ( 'PPROFILES_ROOT_FILE', __FILE__ );
define ( 'PPROFILES_TABLE', 'plugin_profiles' );

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PPROFILES_VERSION', '1.0.0' );

function pprofiler_add_admin_css($hook) {
    if( isset( $_GET['page'] ) && $_GET['page'] == 'pprofiler-options' ) {
        $plugin_url = plugin_dir_url( PPROFILES_ROOT_FILE );
        wp_enqueue_style('pprofiler-admin-css', $plugin_url . 'assets/css/admin.css', null, '1.000');
    }
}
add_action( 'admin_enqueue_scripts', 'pprofiler_add_admin_css' );

/**
 * Load inc/*.php files
 */
foreach( glob( PPROFILES_ROOT . '/inc/*.php' ) as $file ) {
    require_once( $file );
}

/**
 * Load plugin textdomain.
 */
add_action( 'init', 'pprofiles_load_textdomain' );
function pprofiles_load_textdomain() {
  load_plugin_textdomain( 'pprofiler-scanner', false, PPROFILES_ROOT . '/languages' );
}

function pprofiler_scanner_adjust_locale_path( $mofile, $domain ) {
    if ( 'pprofiler-scanner' === $domain ) {
        $locale = apply_filters( 'plugin_locale', determine_locale(), $domain );
        $mofile = PPROFILES_ROOT . '/languages/' . $locale . '.mo';
    }
    return $mofile;
}
add_filter( 'load_textdomain_mofile', 'pprofiler_scanner_adjust_locale_path', 10, 2 );

/**
 * Make a backup of the current plugin setup after this plugin is activated
 */
add_action( 'activated_plugin', function( $plugin, $network_wide ) {
    $active_plugins = get_option( 'active_plugins' );
    update_option( 'pprofiler_default_plugins', $active_plugins, false );
}, 999, 2 );
