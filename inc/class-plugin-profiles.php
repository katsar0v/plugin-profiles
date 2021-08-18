<?php
/**
 *  PluginProfiles is the main class taking care of the functionality needed to create/delete and adjust sets of plugins.
 *  @author Kristiyan Katsarov <katsarov.info>
 */
class PluginProfiles {

    private static $profiles = null;

    private const DEFAULT_PROFILE_NAME = 'pprofile_default_profile';

    /**
     * Validates and sanitizes an array of plugins recursively
     * @param Array $array
     * @return Array
     */
    static function validate_and_sanitize_plugins_array( $array ) {
        $tmp = [];
        foreach( $array as $key => $value ) {
            if( is_string( $value ) ) {
                $tmp[ sanitize_text_field( $key ) ] = sanitize_text_field( $value );
            } else if( is_array( $value ) ) {
                $tmp[ sanitize_text_field( $key ) ] = PluginProfiles::validate_and_sanitize_plugins_array( $value );
            }
        }
        return $tmp;
    }

    /**
     * Checks whether the default plugin profile is active
     * @return bool
     */
    static function is_default_active() {
        $active_plugins = get_option( 'active_plugins' );
        $default_plugins = get_option( 'pprofiler_default_plugins' );
        return $active_plugins == $default_plugins;
    }

    /**
     * @return array
     */
    static function get_profiles() {
        if( !self::$profiles ) {
            global $wpdb;
            $sql = "SELECT * FROM " . $wpdb->prefix . PPROFILES_TABLE;
            $res = $wpdb->get_results( $sql, ARRAY_A );
            self::$profiles = $res;

            usort( self::$profiles, function( $a, $b ) {
                return $a['id'] > $b['id'];
            } );

            return $res;
        }

        usort( self::$profiles, function( $a, $b ) {
            return $a['id'] > $b['id'];
        } );

        return self::$profiles;
    }

    /**
     * @return array
     */
    static function get_nav_profiles() {
        $all_profiles = self::get_profiles();

        foreach( self::$profiles as $name => $profile ) {
            if( $profile['profile_name'] == self::DEFAULT_PROFILE_NAME )
                unset( $all_profiles[$name] );
        }

        return $all_profiles;
    }

    /**
     * @return array
     */
    static function get_current_profile( ) {
        $profiles = self::get_profiles();
        if( isset( $_GET['tab'] ) && is_numeric( $_GET['tab'] ) ) {
            foreach( $profiles as $profile ) {
                if( $profile['id'] == $_GET['tab'] )
                    return $profile;
            }
        }
    }

    /**
     * @return array
     */
    static function get_default_profile() {
        $profiles = self::$profiles;
        foreach( $profiles as $profile ) {
            if( $profile['profile_name'] == self::DEFAULT_PROFILE_NAME )
                return $profile;
        }
    }

    /**
     * @param int id
     * @return void
     */
    static function update_default_profile( $id ) {
        $profiles = self::get_profiles();

        $default_profile_id = null;
        $default_profile_exists = false;
        $new_default_profile = [];
        foreach( $profiles as $profile ) {

            if( $profile['profile_name'] == self::DEFAULT_PROFILE_NAME ) {
                $default_profile_exists = true;
                $default_profile_id = $profile['id'];
            }

            if( $profile['id'] == $_GET['tab'] ) {
                $new_profile = unserialize( $profile['plugins'] );
                foreach( $new_profile as $active_plugin ) {
                    $new_default_profile[$active_plugin] = 'activated';
                }
            }
        }

        if( !$default_profile_exists ) {
            $res = insert_plugins_profile( self::DEFAULT_PROFILE_NAME, $new_default_profile );
        } else {
            $res = update_plugins_profile( $default_profile_id, self::DEFAULT_PROFILE_NAME, $new_default_profile );
        }
        $msg = 'There was an error';
        if( $res == 0 || $res ) {
            $msg = 'Default profile is updated';
        }
        add_filter( 'pprofiler-submit-message', function() use($msg) { return $msg; });
    }

    static function activate_current_profile() {
        if( !isset( $_GET['page'] ) || $_GET['page'] != 'pprofiler-options' )
            return;

        if( !isset( $_GET['tab'] ) ||! is_numeric( $_GET['tab'] ) )
            return;

        if( !isset( $_GET['on'] ) || $_GET['on'] != '1' )
            return;

        $current_profile = PluginProfiles::get_current_profile();
        $to_be_activated = unserialize( $current_profile['plugins'] );
        $all_plugins = array_keys( get_plugins() );
        foreach( $all_plugins as $plugin ) {
            $check = in_array( $plugin, $to_be_activated );
            if( $check ) {
                activate_plugin( $plugin );
            } else {
                deactivate_plugins( $plugin );
            }
        }
    }

    /**
     * @param int $id
     */
    static function delete_profile( int $id ) {
        ?>
        <script>
            setTimeout( () => {
                window.location.href = '/wp-admin/options-general.php?page=<?php echo esc_attr( $_GET['page'] ); ?>';
            }, 1500 );
        </script>
        <?php
        delete_plugins_profile( $id );
        add_filter( 'pprofiler-submit-message', function() { return 'Profile deleted! You will be redirected in a second.'; });
    }
}
