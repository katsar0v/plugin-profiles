<?php
$current_profile = PluginProfiles::get_current_profile();
if( isset( $_GET['on'] ) && $_GET['on'] == '1' ) {
    PluginProfiles::activate_current_profile();
    ?>
    <script>
        setTimeout( () => {
            window.location.href = '/wp-admin/options-general.php?page=<?php echo sanitize_key( $_GET['page'] ); ?>&tab=<?php echo sanitize_key( $_GET['tab'] ); ?>';
        }, 2000 );
    </script>
    <?php
    add_filter( 'pprofiler-submit-message', function() { return 'Profile activated! The page will refresh in a second.'; });
}
if( isset( $_GET['default'] ) && $_GET['default'] == '1' && !isset( $_GET['on'] ) ) {
    PluginProfiles::update_default_profile( sanitize_key( $_GET['tab'] ) );
}
?>
<form action="/wp-admin/options-general.php?page=<?php echo sanitize_key( $_GET['page'] ); ?>&tab=<?php echo sanitize_key( $_GET['tab'] ); ?>" method="POST">
    <input type="hidden" name="action" value="update_profile">
    <table class="form-table">
        <tr>
            <th scope="row"><?php _e( 'Profile name', 'pprofiler' ); ?></th>
            <td>
                <input name="new_profile[name]" type="text" value="<?php echo esc_attr( $current_profile['profile_name'] ); ?>" class="regular-text">
            </td>
        </tr>
        <?php pprofiles_list_plugin_rows( $current_profile['plugins'] ); ?>
    </table>

    <?php submit_button( 'Update profile' ); ?>

    <p>
        <a href="?page=<?php echo sanitize_key( $_GET['page'] ); ?>&tab=<?php echo sanitize_key( $_GET['tab'] ); ?>&on=1" class="button">Activate profile</a>
        <a href="?page=<?php echo sanitize_key( $_GET['page'] ); ?>&tab=<?php echo sanitize_key( $_GET['tab'] ); ?>&default=1" class="button button-secondary">Make default profile</a>
    </p>

    <p>
        <a style="color:red" href="?page=<?php echo sanitize_key( $_GET['page'] ); ?>&tab=<?php echo sanitize_key( $_GET['tab'] ); ?>&delete=1">Delete profile</a>
        <?php
            if( isset( $_GET['delete'] ) && $_GET['delete'] == '1' && isset( $_GET['tab'] ) && is_numeric( $_GET['tab']) ) {
                PluginProfiles::delete_profile( (int) $_GET['tab'] );
            }
        ?>
    </p>

    <?php

    echo apply_filters( 'pprofiler-submit-message', null ); ?>
</form>
