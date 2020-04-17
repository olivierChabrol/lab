<?php
/**
 * Function for the parameter lab management
 */
function lab_admin_tab_params() {
  global $wpdb;
?>
  <div id="lab_createGroup_form">
    <h3>Manage Parameters :</h3>
    <table>
      <tr>
        <!-- NEW PARAM -->
        <td>
    <h4>New Parameters</h4>
    <label for="wp_lab_param_type">Type param</label>
    <select id="wp_lab_param_type">
<?php
  $results = lab_admin_param_load_param_type();
  foreach ( $results as $r ) {
    echo("<option value=\"" . $r->id . "\">" . $r->value . "</option>");
  }
?>
    </select><a href="#" class="page-title-action" id="lab_tab_param_delete">delete</a>
    <br>
    <label for="wp_lab_param_value">Param value</label>
    <input type="text" id="wp_lab_param_value">
    <a href="#" class="page-title-action" id="lab_tab_param_save">Save param</a>
        </td>
        <!-- EDIT PARAM -->
        <td>
          <h4>Edit parameters</h4>
          <label for="lab_param_param_title">Param title</label>
          <input type="text" id="lab_param_value_search" placeholder="type param first letter">
          <input type="hidden" id="wp_lab_param_id">
          <label for="wp_lab_param_type_edit">Param type</label>
          <select id="wp_lab_param_type_edit"></select>
          <a href="#" class="page-title-action" id="lab_tab_param_save_edit">Save param</a>
          <a href="#" class="page-title-action" id="lab_tab_param_delete_edit">Delete param</a>

        </td>
      </tr>
    </table>
  </div>
<?php
}