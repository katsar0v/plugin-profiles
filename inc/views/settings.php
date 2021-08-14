<?php
$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'settings';
$active_plugins = get_option( 'active_plugins' );
?>
<div class="wrap plugin-profiles">
    <h1><?php _e( "Plugin Profiles", "pprofiler") ; ?></h1>
    <h2 class="nav-tab-wrapper">
        <?php pprofiles_get_profile_tab_links(); ?>
    </h2>
    <?php pprofiles_render_tabs(); ?>
</div>
