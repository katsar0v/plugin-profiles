<?php
$current_profile = PluginProfiles::get_default_profile();
?>
<form action="#" method="POST">
    <input type="hidden" name="action" value="update_profile">
    <table class="form-table">
        <?php pprofiles_list_plugin_rows( $current_profile['plugins'], true ); ?>
    </table>
</form>
