<?php
  function lab_admin_new_contract() {
    $active_tab = 'new';
    if (isset($_GET['tab'])) {
      $active_tab = $_GET['tab'];
    }
    $contractId = "";
    if (isset($_GET['id'])) {
      $contractId = $_GET['id'];
    }
?>
    <div id="loadingAjaxGif"><img src="/wp-content/plugins/lab/loading.gif"/></div>
    <div class="wrap">
      <h1 class="wp-heading-inline"><?php esc_html_e('IT budget management','lab'); ?></h1>
      <hr class="wp-header-end">
      <h2 class="nav-tab-wrapper">
        <a id="lab_keyring_default_tab_pointer" style="position: relative" class="nav-tab <?php echo $active_tab == 'new' ? 'nav-tab-active' : ''; ?>"   href="<?php echo add_query_arg(array('tab' => 'new'), $_SERVER['REQUEST_URI']); ?>"><?php esc_html_e('New Contract','lab'); ?></a>
        <a id="lab_keyring_default_tab_pointer" style="position: relative" class="nav-tab <?php echo $active_tab == 'list' ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg(array('tab' => 'list'), $_SERVER['REQUEST_URI']); ?>"><?php esc_html_e('Contract list','lab'); ?></a>
      </h2>

<?php
    if (!lab_admin_checkTable("lab_contract")) {
    echo "<p id='lab_keyring_noKeysTableWarning'>La table <em>lab_contract</em> n'a pas été trouvée dans la base, vous devez d'abord la créer ici : </p>";
    echo '<button class="lab_keyring_create_table_keys" id="lab_admin_contract_create_table">'.esc_html__('Créer la table Contrat','lab').'</button>';
    }
    if (!lab_admin_checkTable("lab_contract_user")) {
    echo "<p id='lab_keyring_noKeysTableWarning'>La table <em>lab_contract_user</em> n'a pas été trouvée dans la base, vous devez d'abord la créer ici : </p>";
    echo '<button class="lab_keyring_create_table_keys" id="lab_admin_contract_create_table">'.esc_html__('Créer la table Contrat user','lab').'</button>';
    }
    if ($active_tab == 'new') {
    lab_admin_contract_new($contractId);
    } else if ($active_tab == 'list') {
    lab_admin_contract_list();
    } else {
    lab_admin_contract_new($contractId);
    }
  }

  function lab_admin_contract_new($contractId = "") {
?>
<input type="hidden" id="lab_contract_delete_dialog_contract_id" value="<?php echo $contractId; ?>">
<table class="widefat fixed lab_keyring_table">
    <tbody>
        <tr>
            <td>
                <label for="lab_admin_contract_name"><?php esc_html_e('Name','lab') ?></label>
            </td>
            <td>
                <input type="text"   id="lab_admin_contract_name" maxlength="500">
                <input type="hidden" id="lab_admin_contract_id">
                <button class="btn" id="lab_admin_contract_delete" disabled="true"><?php esc_html_e('Delete','lab'); ?></button>
            </td>
        </tr>
        <tr>
            <td>
                <label for="lab_admin_contract_type"><?php esc_html_e('Contract type','lab') ?></label>
            </td>
            <td>
                <?php lab_html_select("lab_admin_contract_type", "lab_admin_contract_type", "", "lab_admin_get_params_contract_type", null, array("value"=>"0","label"=>"None"), ""); ?>
            </td>
        </tr>
        <tr>
            <td>
                <label for="lab_admin_contract_tutelage"><?php esc_html_e('Contract tutelage','lab') ?></label>
            </td>
            <td>
                <?php lab_html_select("lab_admin_contract_tutelage", "lab_admin_contract_tutelage", "", "lab_admin_get_params_budget_origin_fund", null, array("value"=>"0","label"=>"None"), ""); ?>
            </td>
        </tr>
        <tr>
            <td>
                <label for="lab_admin_contract_start"><?php esc_html_e('Contract start','lab') ?></label>
            </td>
            <td>
                <input type="date"   id="lab_admin_contract_start">
            </td>
        </tr>
        <tr>
            <td>
                <label for="lab_admin_contract_end"><?php esc_html_e('Contract end','lab') ?></label>
            </td>
            <td>
                <input type="date"   id="lab_admin_contract_end">
            </td>
        </tr>
        <tr>
            <td>
                <label for="lab_admin_contract_holder"><?php esc_html_e('Contract holder(s)','lab') ?></label>
            </td>
            <td>
                <input type="text"   id="lab_admin_contract_holder" maxlength="255">
                <input type="hidden" id="lab_admin_contract_holder_id">
                <span id="lab_admin_contract_holders"></span>
            </td>
        </tr>
        <tr>
            <td>
                <label for="lab_admin_contract_manager"><?php esc_html_e('Contract Manager(s)','lab') ?></label>
            </td>
            <td>
                <input type="text"   id="lab_admin_contract_manager" maxlength="255">
                <input type="hidden" id="lab_admin_contract_manager_id">
                <span id="lab_admin_contract_managers"></span>
            </td>
        </tr>
        <tr>
          <td scope="col" colspan="2"><button class="page-title-action" id="lab_admin_contract_create"><?php esc_html_e('Add','lab'); ?></button></td>
        </tr>
    </table>
<?php
  }
  function lab_admin_contract_list() {
?>
<table class="widefat fixed lab_keyring_table" id="lab_admin_contract_list_table">
    <tbody id="lab_admin_contract_list_table_tbody">
    </tbody>
</table>
<div id="lab_contract_delete_dialog" class="modal">
    <p><?php esc_html_e('Do you really want to delete this contract ?','lab');?></p>
    <input type="hidden" id="lab_contract_delete_dialog_contract_id" value="">
    <div id="lab_contract_delete_dialog_options">
    <a href="#" rel="modal:close"><?php esc_html_e('Cancel','lab')?></a>
    <a href="#" rel="modal:close" id="lab_contract_delete_confirm" keyid=""><?php esc_html_e('Confirm','lab'); ?></a>
    </div>
</div>
<?php
  }

  function lab_admin_contract_funder() {
?>
<div id="loadingAjaxGif"><img src="/wp-content/plugins/lab/loading.gif"/></div>
<div class="wrap">
    <h1 class="wp-heading-inline">Gestion des financeurs</h1><br/>
    Nouveau financeur
    <?php lab_html_select("lab_admin_contract_tutelage", "lab_admin_contract_tutelage", "", "lab_admin_get_params_budget_origin_fund", null, array("value"=>"0","label"=>"None"), ""); ?>
    <label for="lab_admin_contract_name">New Name</label>
    <input type="text" id="lab_admin_contract_name" maxlength="50">
    
    <button class="page-title-action" id="lab_admin_contract_funder_create"><?php esc_html_e('Add','lab'); ?></button>
    <button class="page-title-action" id="lab_admin_contract_funder_save"><?php esc_html_e('Save','lab'); ?></button>
    <table id="lab_admin_contract_funder_list_table" class="table table-hover"></table>
    <div id="lab_contract_funcder_create_dialog" class="modal">
        <input type="hidden" id="lab_contract_funder_parent" value="-1">
        <h3>Add contract funder : </h3>
        <?php lab_html_select("lab_contract_funder_param", "lab_contract_funder_param", "", "lab_admin_param_load_param_type", null, array("value"=>"","label"=>"None"), ""); ?>
        <div id="lab_contract_funder_params"></div>
    </div>

    <div id="lab_contract_funder_dialog_add" class="modal">
        <input type="hidden" id="lab_contract_funder_dialog_parent" value="-1">
        <h3>Add contract funder : </h3>
        <div id="lab_contract_funder_dialog_add_content"></div>
        <a href="#" rel="modal:close" id="lab_contract_funder_dialog_add_button" class="btn btn-success">Add</a>
        <a href="#" rel="modal:close"><?php esc_html_e('Cancel','lab'); ?></a>
    </div>
    <div id="lab_contract_funder_delete_dialog" class="modal">
        <input type="hidden" id="lab_contract_funder_delete_dialog_id" value="-1">
        <h3>Delete contract funder : </h3>
        <div id="lab_contract_funder_delete_dialog_name"></div>
        <a href="#" rel="modal:close" id="lab_contract_funder_delete_dialog_delete_button" class="btn btn-danger">Delete</a>
        <a href="#" rel="modal:close"><?php esc_html_e('Cancel','lab'); ?></a>
    </div>
</div>
<?php
  }
  
