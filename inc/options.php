<?php
/**
 * Renders tab content
 */
function pprofiles_render_tabs() {
    $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'default-profile';
    $file = PPROFILES_ROOT . '/inc/views/tab-' . $active_tab . '.php';

    if( is_numeric( $active_tab ) ) {
        $file = PPROFILES_ROOT . '/inc/views/tab-profile.php';
    }

    if( file_exists( $file ) ) {
        require_once( $file );
    } else {
        wp_die( 'Tab not found');
    }
}

/**
 * Register options menu
 */
add_action('admin_menu', 'register_options_menu');
function register_options_menu() {
    add_options_page(
        __( 'Options', 'pprofiler' ),
        __( 'Plugin Profiles', 'pprofiler' ),
        'manage_options',
        'pprofiler-options',
        function () {
            if( !current_user_can('manage_options') )
                wp_die();

            require_once( PPROFILES_ROOT . '/inc/views/settings.php' );
        }
    );
}

/**
 * Add settings link on plugins page
 */
add_filter( 'plugin_action_links_' . plugin_basename( PPROFILES_ROOT_FILE ), 'pprofiles_add_settings_link');
function pprofiles_add_settings_link( $links ) {
	array_unshift( $links, '<a href="' .
		admin_url( 'options-general.php?page=pprofiler-options' ) .
		'">' . __('Settings') . '</a>' );
	return $links;
}

add_action( 'admin_init', function() {
    if( !current_user_can('manage_options') )
        return;

    $action = isset( $_POST['action'] ) ? sanitize_text_field( $_POST['action'] ) : null;

    if( !isset( $_GET['page'] ) || $_GET['page'] != 'pprofiler-options' )
        return;

    if( !$action )
        return;

    switch($action) {
        case 'settings':
            // empty for now
            break;
        case 'update_profile':
            global $wpdb;
            if( !is_array( $_POST['new_profile'] ) )
                wp_die();

            $name = $_POST['new_profile']['name'];
            $plugins = $_POST['new_profile']['plugins'];

            $res = update_plugins_profile( $_GET['tab'], $name, $plugins );
            if( $res > 0 ) {
                $message = 'Profile updated';
            } else if ( $res == 0 ) {
                $message = 'No changes have been done.';
            } else {
                $message = 'There was an error updating the profile.';
            }

            add_filter( 'pprofiler-submit-message', function() use($message) { return $message; } );
            break;
        case 'new_profile':
            if( !is_array( $_POST['new_profile'] ) )
                wp_die();

            $name = $_POST['new_profile']['name'];
            $plugins = $_POST['new_profile']['plugins'];

            $res = insert_plugins_profile( $name, $plugins );
            $message = $res ? 'Profile created' : 'There was an error creating the profile';

            add_filter( 'pprofiler-submit-message', function() use($message) { return $message; } );
            break;
        default:
            wp_die( 'unsupported action' );
    }
} );

/**
 * Overwrite classes for active profile
 */
add_filter( 'pprofiler_active_tab_class', function( $classes, $profile ) {
    $active_plugins = [];
    $all_plugins = array_keys( get_plugins() );

    foreach( $all_plugins as $plugin ) {
        if( is_plugin_active( $plugin ) )
            $active_plugins[] = $plugin;
    }

    $current_profile_plugins = unserialize ( $profile['plugins'] );

    $res = empty( array_diff( $current_profile_plugins, $active_plugins ) ) && count( $current_profile_plugins ) == count( $active_plugins );

    if( $res )
        $classes .= ' active-profile';

    return $classes;
}, 10, 2 );
