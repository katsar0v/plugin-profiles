<form action="#" method="POST">
    <input type="hidden" name="action" value="new_profile">
    <table class="form-table">
        <tr>
            <th scope="row"><?php _e( 'Profile name', 'pprofiler' ); ?></th>
            <td>
                <input name="new_profile[name]" type="text" class="regular-text">
            </td>
        </tr>
        <?php pprofiles_list_plugin_rows(); ?>
    </table>
    <?php submit_button( 'Create profile' ); ?>
    <?php echo apply_filters( 'pprofiler-submit-message', null ); ?>
</form>
