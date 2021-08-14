<?php
/**
 * Creates the main database table after the plugin has been activated.
 */
function pprofiles_create_plugin_database_table() {
    global $wpdb;

    $wp_track_table = $wpdb->prefix . PPROFILES_TABLE;
    $charset_collate = $wpdb->get_charset_collate();

    if($wpdb->get_var( "SHOW TABLES LIKE '$wp_track_table'" ) != $wp_track_table)
    {

        $sql = "CREATE TABLE `$wp_track_table` ( ";
        $sql .= "  `id`             int(11)      NOT NULL auto_increment, ";
        $sql .= "  `profile_name`   varchar(255) NOT NULL, ";
        $sql .= "  `plugins`        varchar(255) NOT NULL, ";
        $sql .= "  PRIMARY KEY(`id`), UNIQUE(`profile_name`)";
        $sql .= ") $charset_collate;";
        require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
        dbDelta($sql);
    }
}
register_activation_hook( PPROFILES_ROOT_FILE, 'pprofiles_create_plugin_database_table' );

/**
 * Deletes the tables upon plugin deletion
 */
function pprofiles_delete_plugin_database_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . PPROFILES_TABLE;
    $sql = "DROP TABLE IF EXISTS $table_name";
    $wpdb->query( $sql );
}
register_uninstall_hook(__FILE__, 'pprofiles_delete_plugin_database_table');

/**
 * @param string $profile_name
 * @param int $plugins
 */
function insert_plugins_profile( string $profile_name, array $plugins ) {
    global $wpdb;

    if( empty( $profile_name ) || empty( $plugins ) )
        return false;

    $active_plugins = [];

    foreach( $plugins as $plugin => $active ) {
        if( $active == "activated" )
            $active_plugins[] = $plugin;
    }

    // Current plugin is always active
    $active_plugins[] = plugin_basename( PPROFILES_ROOT_FILE );

    return $wpdb->insert( $wpdb->prefix . PPROFILES_TABLE, [
        'profile_name' => sanitize_text_field( $profile_name ),
        'plugins'    => serialize( $active_plugins )
    ] );
}

/**
 * @param $id
 * @param $name
 * @param $plugins e.g. [ 'plugin-path' => 'activated' ]
 */
function update_plugins_profile( $id, $name, $plugins ) {
    global $wpdb;

    $active_plugins = [];
    $current_is_active = false;

    foreach( $plugins as $plugin => $active ) {
        if( $active == "activated" )
            $active_plugins[] = sanitize_text_field( $plugin );

        if( $plugin == plugin_basename( PPROFILES_ROOT_FILE ) )
            $current_is_active = true;
    }

    // Current plugin is always active
    if( !$current_is_active )
        $active_plugins[] = plugin_basename( PPROFILES_ROOT_FILE );

    $data = [
        'profile_name' => sanitize_text_field( $name ),
        'plugins'      => serialize( $active_plugins )
    ];

    $where = [
        'id'           => (int) $id
    ];

    return $wpdb->update( $wpdb->prefix . PPROFILES_TABLE, $data, $where );
}

/**
 * @param int $id
 */
function delete_plugins_profile( $id ) {
    global $wpdb;
    $where = [
        'id' => (int) $id
    ];
    return $wpdb->delete( $wpdb->prefix . PPROFILES_TABLE, $where );
}
