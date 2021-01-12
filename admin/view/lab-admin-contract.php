<?php
  function lab_admin_new_contract() {
    $active_tab = 'new';
    if (isset($_GET['tab'])) {
      $active_tab = $_GET['tab'];
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
    lab_admin_contract_new();
    } else if ($active_tab == 'list') {
    lab_admin_contract_list();
    } else {
    lab_admin_contract_new();
    }
  }

  function lab_admin_contract_new() {
?>
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
          <td scope="col" colpsan="2"><button class="page-title-action" id="lab_admin_contract_create"><?php esc_html_e('Add','lab'); ?></button></td>
        </tr>
<?php
  }
  function lab_admin_contract_list() {
?>
<table class="widefat fixed lab_keyring_table" id="lab_admin_contract_list_table">
    <tbody id="lab_admin_contract_list_table_tbody">
    </tbody>
</table>
<?php
  }
  
