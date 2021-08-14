<?php
/**
 * @return void
 */
function pprofiles_get_profile_tab_links() {
    $profiles = PluginProfiles::get_nav_profiles();
    $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'default-profile';

    ?>
        <a href="?page=<?php echo sanitize_text_field( $_GET['page'] ); ?>&tab=default-profile" class="nav-tab <?php echo $active_tab == 'default-profile' ? 'nav-tab-active' : ''; ?>">Default</a>
    <?php

    foreach( $profiles as $profile ) {
        $tab_classes = 'nav-tab';
        if( $active_tab == $profile['id'] )
            $tab_classes .= ' nav-tab-active';

        $tab_classes = apply_filters( 'pprofiler_active_tab_class', $tab_classes, $profile );
        ?>
            <a href="?page=<?php echo $_GET['page']; ?>&tab=<?php echo intval( $profile['id'] ); ?>" class="<?php echo $tab_classes; ?>"><?php echo esc_html( $profile['profile_name'] ); ?></a>
        <?php
    }

    ?>
        <a href="?page=<?php echo $_GET['page']; ?>&tab=new-profile" class="nav-tab <?php echo $active_tab == 'new-profile' ? 'nav-tab-active' : ''; ?>">Create profile +</a>
    <?php
}

/**
 * @param string $plugins (serialized)
 * @return void
 */
function pprofiles_list_plugin_rows( $active_plugins = null, $disabled = false ) {

    $active_plugins = unserialize( $active_plugins );

    $plugins = get_plugins();
    $pprofiles = plugin_basename( PPROFILES_ROOT_FILE );
    foreach( $plugins as $plugin_name => $plugin ) {
        if( $plugin_name == $pprofiles )
            continue;

        if( !$active_plugins ) {
            $checked = is_plugin_active( $plugin_name );
        } else {
            $checked = in_array( $plugin_name, $active_plugins );
        }
        ?>
         <tr <?php echo $disabled ? 'class="disabled"' : '' ; ?>>
            <th scope="row"><?php echo esc_html( $plugin['Name'] ); ?></th>
            <td>
                <div class="plugin-activate-deactivate">
                    <label for="<?php echo sanitize_title( $plugin_name . '_activate' ); ?>" class="activate">
                        <span>Activate</span><input name="new_profile[plugins][<?php echo esc_attr( $plugin_name ); ?>]" type="radio" <?php echo $checked ? 'checked="checked"' : ''; ?> id="<?php echo sanitize_title( $plugin_name . '_activate' ); ?>" value="activated">
                    </label>
                    <label for="<?php echo sanitize_title( $plugin_name . '_deactivate' ); ?>" class="deactivate">
                        <span>Deactivate</span><input name="new_profile[plugins][<?php echo esc_attr( $plugin_name ); ?>]" type="radio" <?php echo !$checked ? 'checked="checked"' : ''; ?> id="<?php echo sanitize_title( $plugin_name . '_deactivate' ); ?>" value="deactivated">
                    </label>
                </dv>
            </td>
        </tr>
        <?php
    }
}
