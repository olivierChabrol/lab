<?php

  function lab_admin_budget_mission_url($elements = null) {
    $str = "";
    if ($elements != null) {  
      foreach($elements as $k=>$v) {
        $str .= "&$k=$v";
      }
    }
    return get_admin_url()."admin.php?page=lab_admin_mission_manager".$str;
  }

  function lab_admin_mission_manager() {
    $active_tab = 'historic';
    if (isset($_GET['tab'])) {
      $active_tab = $_GET['tab'];
    }
    $id = "";
    if (isset($_GET['id'])) {
      $id = $_GET['id'];
    }
    $token = "";
    if (isset($_GET['token'])) {
      $token = $_GET['token'];
    }

    global $wp;
    $current_url = home_url( add_query_arg( array(), $wp->request ) );
?>
<input type="hidden" id="lab_mission_user_id" value="<?php echo get_current_user_id(); ?>">
<div id="loadingAjaxGif"><img src="/wp-content/plugins/lab/loading.gif"/></div>
<div class="wrap">
  <h1 class="wp-heading-inline"><?php esc_html_e('Mission management','lab'); ?></h1>
  <hr class="wp-header-end">
  <h2 class="nav-tab-wrapper">
    <a id="lab_keyring_default_tab_pointer" style="position: relative" class="nav-tab <?php echo $active_tab == 'historic' ? 'nav-tab-active' : ''; ?>" href="<?php echo lab_admin_budget_mission_url(array('tab' => 'historic','year'=>date("Y"))); ?>"><?php esc_html_e('Historic','lab'); ?></a>
    <a id="lab_keyring_default_tab_pointer" style="position: relative" class="nav-tab <?php echo $active_tab == 'entry' ? 'nav-tab-active' : ''; ?>"   href="<?php echo lab_admin_budget_mission_url(array('tab' => 'entry')); ?>"><?php esc_html_e('Mission','lab'); ?></a>
  </h2>

<?php
      if (!lab_admin_checkTable("lab_budget_info")) {
        echo "<p id='lab_keyring_noKeysTableWarning'>".esc_html__("The table <em>wp_lab_budget_info</em> hasn't been found in database, you must first create it here : ", "lab")."</p>";
        echo '<button class="lab_keyring_create_table_keys" id="lab_budget_info_create_table">'.esc_html__('Create table Budget info','lab').'</button>';
      }
      if ($id != "")
      {
        echo '<input type="hidden" id="lab_mission_id" value="'.$id.'">';
      }
      
      if ($active_tab == 'entry') {
        lab_mission_tab_mission($token);
      } 
      else //if ($active_tab == 'historic') {
      {
        lab_mission_tab_historic();
      //} else {
      //  lab_budget_info_tab_new_order();
      }
  }


  function lab_mission_tab_mission($token) {
    $args = array();
    $args["hostpage"] = "1";
    $args["token"]    = $token;
    echo lab_mission($args);
  }

  function lab_mission_tab_historic()
  {
    $user = wp_get_current_user();
    $roles = $user->roles;
    $isAdministrator = False;
    $isManager       = False;
    $isGroupLeader   = False;
    $groupLeaderIds  = array();
    $groupManagerIds = array();
    foreach($roles as $role) {
      if ($role == "administrator")
      {
        $isAdministrator = True;
      }
    }
    if (!$isAdministrator) {
      $groupManagerIds = lab_admin_group_get_groups_of_manager(get_current_user_id());
      $groupLeaderIds  = lab_admin_group_get_groups_of_leader(get_current_user_id());
      $isManager = count($groupManagerIds) > 0;
      $isGroupLeader = count($groupLeaderIds) > 0;
      echo ("isManager :" . $isManager . "<br>");
      echo ("isGroupLeader :" . $isGroupLeader . "<br>");
    }
    ?>
    <div class="wrap">
    <?php
      if($isGroupLeader) {
        echo '<input type="hidden" id="lab_mission_group_leader" value="'.urlencode(json_encode ($groupLeaderIds)).'">';
      }
      if($isManager) {
        echo '<input type="hidden" id="lab_mission_group_manager" value="'.urlencode(json_encode ($groupManagerIds)).'">';
      }
    ?>
    <h1 class="wp-heading-inline"><?php esc_html_e('Missions','lab'); ?></h1>
    <h2 class='screen-reader-text'>Filtrer la liste des commandes</h2>
    <p class="search-box">
    <div class="tablenav top">
    <div class="alignleft actions bulkactions">
    <select id="lab_mission_filter_year"></select>
    <select id="lab_mission_filter_status">
      <option value=""><?php esc_html_e('All','lab'); ?></option>
      <option value="n"><?php esc_html_e('New','lab'); ?></option>
      <option value="c"><?php esc_html_e('Complete','lab'); ?></option>
      <option value="ca"><?php esc_html_e('Cancelled','lab'); ?></option>
      <option value="vgl"><?php esc_html_e('Validated','lab'); ?></option>
      <option value="rgl"><?php esc_html_e('Refused','lab'); ?></option>
      <option value="wgm"><?php esc_html_e('Waiting','lab'); ?></option>
    </select>
  
<?php lab_html_select("lab_mission_filter_site", "lab_budget_info_filter_site", "", "lab_admin_get_params_userLocation", null, array("value"=>"","label"=>"".esc_html('Site','lab')), ""); ?>
<?php lab_html_select("lab_mission_filter_budget_manager", "lab_budget_info_filter_budget_manager", "", "lab_admin_budget_managers_list", null, array("value"=>"","label"=>"".esc_html('Budget manager','lab')), ""); ?>
<label class="screen-reader-text" for="post-search-input"><?php esc_html_e('Search command Number','lab'); ?>:</label>
  <input type="text" id="lab_budget_info_filter_order_number" placeholder="<?php esc_html_e('Command Number','lab'); ?>"></input>
  </div></div>
  <br class="clear">
    </p>
    <table class="widefat fixed lab_keyring_table" id="lab_admin_mission_list_table">
      <thead>
        <th>id</th>
        <th><?php esc_html_e('Status','lab'); ?></th>
        <th><?php esc_html_e('Request date','lab'); ?></th>
        <th><?php esc_html_e('User','lab'); ?></th>
        <th><?php esc_html_e('Site','lab'); ?></th>
        <th><?php esc_html_e('Group','lab'); ?></th>
        <th><?php esc_html_e('Budget manager','lab'); ?></th>
        <th><?php esc_html_e('Mission Type','lab'); ?></th>
        <th><?php esc_html_e('Action','lab'); ?></th>
      </thead>
        <tbody id="lab_admin_mission_list_table_tbody">
        </tbody>
    </table>
    </div> 
    <div id="lab_mission_delete_dialog" class="modal">
      <p><?php esc_html_e('Do you really want to delete this mission ?','lab');?></p>
      <input type="hidden" id="lab_mission_delete_dialog_mission_id" value="">
      <div id="lab_mission_delete_dialog_options">
        <a href="#" rel="modal:close"><?php esc_html_e('Cancel','lab')?></a>
        <a href="#" rel="modal:close" id="lab_mission_delete_confirm" keyid=""><?php esc_html_e('Confirm','lab'); ?></a>
      </div>
    </div>
      <?php
  }