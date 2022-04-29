<?php

function lab_request_view() {
    $active_tab = 'list';
    if (isset($_GET['tab'])) {
      $active_tab = $_GET['tab'];
    }
    $objId = "";
    if (isset($_GET['id'])) {
      $objId = $_GET['id'];
    }
    $view = FALSE;
    if (isset($_GET['view'])) {
      $view = $_GET['view'];
    }
?>
<div id="loadingAjaxGif"><img src="/wp-content/plugins/lab/loading.gif"/></div>
<div class="wrap">
  <h1 class="wp-heading-inline"><?php esc_html_e('Request','lab'); ?></h1>
  <hr class="wp-header-end">
  <h2 class="nav-tab-wrapper">
    <a id="lab_request_default_tab_pointer" style="position: relative" class="nav-tab <?php echo $active_tab == 'list' ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg(array('tab' => 'list'), $_SERVER['REQUEST_URI']); ?>"><?php esc_html_e('Requests list','lab'); ?></a>
    <a id="lab_request_default_tab_pointer" style="position: relative" class="nav-tab <?php echo $active_tab == 'entry' ? 'nav-tab-active' : ''; ?>"   href="<?php echo add_query_arg(array('tab' => 'entry'), $_SERVER['REQUEST_URI']); ?>"><?php esc_html_e('Request','lab'); ?></a>

<?php
  if (current_user_can("budget_info_manager") || lab_is_group_leader()) {
?>
    <a id="lab_request_default_tab_pointer" style="position: relative" class="nav-tab <?php echo $active_tab == 'admin' ? 'nav-tab-active' : ''; ?>"   href="<?php echo add_query_arg(array('tab' => 'admin'), $_SERVER['REQUEST_URI']); ?>"><?php esc_html_e('All Requests','lab'); ?></a>
<?php
  }
?>
  </h2>
<?php
    if ($active_tab == 'entry') {
        lab_request_newEdit($objId, $view);
    } 
    else if ($active_tab == 'list') 
    {
        lab_request_list();
    }
    else if ($active_tab == 'admin') 
    {
      lab_request_admin();
    }
?>
</div>
<?php
}
function lab_request_newEdit($objId, $view) {
    echo lab_request(array("id"=>$objId, "view"=>$view));
}

function lab_request_admin() {
?>
  <div id="lab_request_list_admin_filters">
<?php    
  $group = null;
  lab_html_select("lab-request-list-admin_filter_group", "lab-request-list-admin", "", "lab_admin_group_select_group", "acronym, group_name", array("value"=>0,"label"=>"None"), $group, array("id"=>"acronym", "value"=>"value"));
?>
  <select id="lab-request-list-admin_filter_status" filter="status">
    <option value="">Status</option>
    <option value="-1">Cancel</option>
    <option value="0">No taken</option>
    <option value="10">Taken</option>
    <option value="30">Close</option>
  </select>
  </div>
  <table class="widefat fixed lab_keyring_table" id="lab_request_list_admin_table">
      <tbody id="lab_request_list_table_tbody">
      </tbody>
  </table>
  <div id="lab_request_delete_dialog" class="modal">
      <p><?php esc_html_e('Do you really want to delete this request ?','lab');?></p>
      <input type="hidden" id="lab_request_delete_dialog_request_id" value="">
      <div id="lab_request_delete_dialog_options">
      <a href="#" rel="modal:close"><?php esc_html_e('Cancel','lab')?></a>
      <a href="#" rel="modal:close" id="lab_div_delete_confirm" keyid=""><?php esc_html_e('Confirm','lab'); ?></a>
      </div>
  </div>
<?php
}

function lab_request_list() {
?>  
    <table class="widefat fixed lab_keyring_table" id="lab_request_list_table">
        <tbody id="lab_request_list_table_tbody">
        </tbody>
    </table>
    <div id="lab_request_delete_dialog" class="modal">
        <p><?php esc_html_e('Do you really want to delete this request ?','lab');?></p>
        <input type="hidden" id="lab_request_delete_dialog_request_id" value="">
        <div id="lab_request_delete_dialog_options">
        <a href="#" rel="modal:close"><?php esc_html_e('Cancel','lab')?></a>
        <a href="#" rel="modal:close" id="lab_div_delete_confirm" keyid=""><?php esc_html_e('Confirm','lab'); ?></a>
        </div>
    </div>

<?php
}
?>