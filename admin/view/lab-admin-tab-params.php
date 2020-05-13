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
    <div class="form-row">
      <div class="col">
        <label for="wp_lab_param_type"><?php esc_html_e("Param type", "lab") ?></label>
    <select id="wp_lab_param_type">
<?php
  $results = lab_admin_param_load_param_type();
  foreach ( $results as $r ) {
    echo("<option value=\"" . $r->id . "\">" . $r->value . "</option>");
  }
?>
    </select><a href="#" class="page-title-action" id="lab_tab_param_delete"><?php esc_html_e("Delete", "lab") ?></a>
      </div>
    </div>
    <label for="wp_lab_param_value"><?php esc_html_e("Param value", "lab") ?></label>
    <input type="text" id="wp_lab_param_value">
    <label for="wp_lab_param_color"><?php esc_html_e("Param color", "lab") ?></label>
    <input type="text" id="wp_lab_param_color">
    <i title="<?php esc_html_e("Modifier la couleur du parametre","lab") ?>" style="display:none" id="lab_admin_param_colorpicker" class="fas fa-fill-drip lab_profile_edit"></i>
    <a href="#" class="page-title-action" id="lab_tab_param_save"><?php esc_html_e("Save param", "lab") ?></a>
        </td>
        <!-- EDIT PARAM -->
        <td>
          <h4><?php esc_html_e("Edit parameters", "lab") ?></h4>
          <label for="lab_param_param_title"><?php esc_html_e("Param title", "lab") ?></label>
          <input type="text" id="lab_param_value_search" placeholder="type param first letter">
          <input type="hidden" id="wp_lab_param_id">
          <label for="wp_lab_param_type_edit"><?php esc_html_e("Param type", "lab") ?></label>
          <select id="wp_lab_param_type_edit"></select>
          <a href="#" class="page-title-action" id="lab_tab_param_save_edit"><?php esc_html_e("Save param", "lab") ?></a>
          <a href="#" class="page-title-action" id="lab_tab_param_delete_edit"><?php esc_html_e("Delete param", "lab") ?></a>
        </td>
      </tr>
    </table>
  </div>
<?php
}