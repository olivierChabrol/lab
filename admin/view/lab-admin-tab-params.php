<?php
/**
 * Function for the parameter lab management
 */
function lab_admin_tab_params() {
  global $wpdb;
?>
  <div id="lab_createGroup_form">
    <h3><?php esc_html_e("Manage Parameters", "lab") ?></h3>
    <table>
      <tr>
        <!-- NEW PARAM -->
        <td>
    <h4><?php esc_html_e("New Parameters", "lab") ?></h4>
    <table>
      <tr>
        <td><label for="wp_lab_param_type"><?php esc_html_e("Param type", "lab") ?></label></td>
        <td><select id="wp_lab_param_type">
<?php
  $results = lab_admin_param_load_param_type();
  foreach ( $results as $r ) {
    echo("<option value=\"" . $r->id . "\">" . $r->value . "</option>");
  }
?>
    </select></td><td><a href="#" class="page-title-action" id="lab_tab_param_delete"><?php esc_html_e("Delete", "lab") ?></a></td>
    </tr>
    <tr>
      <td><label for="wp_lab_param_value"><?php esc_html_e("Param value", "lab") ?></label></td>
      <td><input type="text" id="wp_lab_param_value"></td><td>&nbsp;</td>
    </tr>
    <tr>
      <td><label for="wp_lab_param_color"><?php esc_html_e("Param color", "lab") ?></label></td>
      <td><input type="text" id="wp_lab_param_color"></td>
      <td>
        <div id="lab_profile_icons" class="lab_profile_edit">
					<i title="<?php esc_html_e("Affect color to param","lab") ?>" id="lab_admin_param_colorpicker" class="fas fa-fill-drip"></i>
        </div>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        See this web site to choose good colors : <a href="https://coolors.co/" target="coolors">https://coolors.co/</a>
      </td>
    </tr>
    <tr><td colspan="3">&nbsp;</td></tr>
    <tr><td colspan="3"><a href="#" class="page-title-action" id="lab_tab_param_save"><?php esc_html_e("Save param", "lab") ?></a></td></tr>
    </table>
        </td>
        <!-- EDIT PARAM -->
        <td>
          <h4><?php esc_html_e("Edit parameters", "lab") ?></h4>
        <table>
          <tr>
            <td><label for="lab_param_param_title"><?php esc_html_e("Param title", "lab") ?></label></td>
            <td colpsan="2"><input type="text" id="lab_param_value_search" placeholder="type param first letter"></td>
          </tr>
          <tr>
            <td><input type="hidden" id="wp_lab_param_id"><label for="wp_lab_param_type_edit"><?php esc_html_e("Param type", "lab") ?></label></td>
            <td colpsan="2"><select id="wp_lab_param_type_edit"></select></td>
          </tr>
          <tr>
            <td><label for="wp_lab_param_color_edit"><?php esc_html_e("Param color", "lab") ?></label></td>
            <td><input type="text" id="wp_lab_param_color_edit"></td>
            <td>
              <div id="lab_profile_icons_edit" class="lab_profile_edit">
                <i title="<?php esc_html_e("Affect color to param","lab") ?>" id="lab_admin_param_colorpicker_edit" class="fas fa-fill-drip"></i>
              </div>
            </td>
          </tr>
          <tr><td colspan="3">&nbsp;</td></tr>
          <tr>
            <td><a href="#" class="page-title-action" id="lab_tab_param_save_edit"><?php esc_html_e("Save param", "lab") ?></a></td>
            <td><a href="#" class="page-title-action" id="lab_tab_param_delete_edit"><?php esc_html_e("Delete param", "lab") ?></a></td>
          </tr>
        </table>
        </td>
      </tr>
    </table>
  </div>
  <div id="lab_admin_param_delete" class="modal">
      <p><?php esc_html_e('delete this param ?','lab'); ?></p>
      <p><span id="lab_admin_param_modal_param_name"></span></p>
      <input type="hidden" id="lab_admin_param_modal_param_id">
      <div id="lab_keyring_delete_dialog_options">
        <a href="#" rel="modal:close"><?php esc_html_e('Annuler','lab'); ?></a>
        <a href="#" rel="modal:close" id="lab_admin_param_delete_confirm" keyid=""><?php esc_html_e('Confirmer','lab'); ?></a>
      </div>
    </div>
<?php
}