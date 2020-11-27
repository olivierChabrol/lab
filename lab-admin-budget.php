<?php
  function lab_admin_budget_info() {
    $active_tab = 'entry';
    if (isset($_GET['tab'])) {
      $active_tab = $_GET['tab'];
    }
    $id = "";
    if (isset($_GET['id'])) {
      $id = $_GET['id'];
    }
?>
<div id="loadingAjaxGif"><img src="/wp-content/plugins/lab/loading.gif"/></div>
<div class="wrap">
  <h1 class="wp-heading-inline"><?php esc_html_e('IT budget management','lab'); ?></h1>
  <hr class="wp-header-end">
  <h2 class="nav-tab-wrapper">
    <a id="lab_keyring_default_tab_pointer" style="position: relative" class="nav-tab <?php echo $active_tab == 'entry' ? 'nav-tab-active' : ''; ?>"   href="<?php echo add_query_arg(array('tab' => 'entry')  , $_SERVER['REQUEST_URI']); ?>"><?php esc_html_e('New order','lab'); ?></a>
    <a id="lab_keyring_default_tab_pointer" style="position: relative" class="nav-tab <?php echo $active_tab == 'historic' ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg(array('tab' => 'historic'), $_SERVER['REQUEST_URI']); ?>"><?php esc_html_e('Historic','lab'); ?></a>
  </h2>

<?php
      if (!lab_admin_checkTable("lab_budget_info")) {
        echo "<p id='lab_keyring_noKeysTableWarning'>La table <em>wp_lab_budget_info</em> n'a pas été trouvée dans la base, vous devez d'abord la créer ici : </p>";
        echo '<button class="lab_keyring_create_table_keys" id="lab_budget_info_create_table">'.esc_html__('Créer la table Budget info','lab').'</button>';
      }
      if ($id != "")
      {
        echo '<input type="hidden" id="lab_budget_info_id" value="'.$id.'">';
      }
      
      if ($active_tab == 'entry') {
        lab_budget_info_tab_new_order();
      } else if ($active_tab == 'historic') {
        lab_budget_info_tab_historic();
      } else {
        lab_budget_info_tab_new_order();
      }
  }

  function lab_budget_info_tab_new_order()
  {
?>

<table class="widefat fixed lab_keyring_table">
    <tbody>
        <tr>
            <td>
 <label for="lab_budget_info_expenditure_type"><?php esc_html_e('Expenditure type','lab') ?></label>
            </td>
            <td>
<?php lab_html_select("lab_budget_info_expenditure_type", "lab_budget_info_expenditure_type", "", lab_admin_get_params_budgetInfoType, null, array("value"=>"","label"=>"None"), ""); ?>
            </td>
        </tr>
        <tr>
            <td>
 <label for="lab_budget_info_title"><?php esc_html_e('Title','lab') ?></label>
            </td>
            <td>
 <input type="text" id="lab_budget_info_title" maxlength="255">
            </td>
        </tr>
        <tr>
            <td>
 <label for="lab_budget_info_request_date"><?php esc_html_e('Date of request','lab') ?></label>
            </td>
            <td>
 <input type="date" id="lab_budget_info_request_date">
            </td>
        </tr>
        <tr>
            <td>
 <label for="lab_budget_info_user"><?php esc_html_e('User','lab') ?></label>
            </td>
            <td>
            <input type="text" id="lab_budget_info_user" maxlength="255">
            <input type="hidden" id="lab_budget_info_user_id">
            <span id="lab_budget_info_group_name"></span>
            <input type="hidden" id="lab_budget_info_user_group_id">
            </td>
        </tr>
        <tr>
            <td>
 <label for="lab_budget_info_site_id"><?php esc_html_e('Site','lab') ?></label>
            </td>
            <td>
            <?php lab_html_select("lab_budget_info_site_id", "lab_budget_info_site_id", "", lab_admin_get_params_userLocation, null, array("value"=>"0","label"=>"None"), ""); ?>
            </td>
        </tr>
        <tr>
            <td>
              <label for="lab_budget_info_budget_manager_id"><?php esc_html_e('Budget manager','lab') ?></label>
            </td>
            <td>
            <?php lab_html_select("lab_budget_info_budget_manager_id", "lab_budget_info_budget_manager_id", "", lab_admin_budget_managers_list, null, null, ""); ?>
              <span id="lab_budget_info_managers"></span>
            </td>
        </tr>
        <tr>
            <td>
 <label for="lab_budget_info_fund_origin"><?php esc_html_e('Funding','lab') ?></label>
            </td>
            <td>
            <?php lab_html_select("lab_budget_info_fund_origin", "lab_budget_info_fund_origin", "", lab_admin_budget_funds, null, array("value"=>"0","label"=>"None"), ""); ?>
            </td>
        </tr>
        <tr>
            <td>
 <label for="lab_budget_info_amount"><?php esc_html_e('Amount HT','lab') ?></label>
            </td>
            <td>
            <input type="text" id="lab_budget_info_amount" maxlength="255">
            </td>
        </tr>
        <tr>
            <td>
 <label for="lab_budget_info_order_number"><?php esc_html_e('Order\'s Number','lab') ?></label>
            </td>
            <td>
 <input type="text" id="lab_budget_info_order_number" maxlength="255">
            </td>
        </tr>
        <tr>
            <td>
 <label for="lab_budget_info_order_date"><?php esc_html_e('Order\'s date','lab') ?></label>
            </td>
            <td>
 <input type="date" id="lab_budget_info_order_date">
            </td>
        </tr>
        <tr>
            <td>
 <label for="lab_budget_info_delivery_date"><?php esc_html_e('Date of delivery','lab') ?></label>
            </td>
            <td>
 <input type="date" id="lab_budget_info_delivery_date">
            </td>
        </tr>
        <tr>
            <td>
 <label for="lab_budget_info_payment_date"><?php esc_html_e('Date of payment','lab') ?></label>
            </td>
            <td>
 <input type="date" id="lab_budget_info_payment_date">
            </td>
        </tr>
        <tr>
          <td scope="col" colspan="2"><button class="page-title-action" id="lab_budget_info_entry_create"><?php esc_html_e('Add','lab'); ?></button></td>
        </tr>
    </tbody>
</table>
<?php
  }

  function lab_budget_info_tab_historic()
  {
    ?>
    <label for="lab_budget_info_filter_year"><?php esc_html_e('Year','lab'); ?></label><select id="lab_budget_info_filter_year"></select>
    <table class="widefat fixed lab_keyring_table" id="lab_admin_budget_info_list_table">
      <thead>
        <th>id</th>
        <th><?php esc_html_e('Request date','lab'); ?></th>
        <th><?php esc_html_e('Command Number','lab'); ?></th>
        <th><?php esc_html_e('Command title','lab'); ?></th>
        <th><?php esc_html_e('Site','lab'); ?></th>
        <th><?php esc_html_e('For','lab'); ?></th>
        <th><?php esc_html_e('Info Manager','lab'); ?></th>
        <th><?php esc_html_e('Budget Manager','lab'); ?></th>
        <th><?php esc_html_e('Funds','lab'); ?></th>
        <th><?php esc_html_e('Amount','lab'); ?></th>
        <th><?php esc_html_e('Order date','lab'); ?></th>
        <th><?php esc_html_e('Delivery date','lab'); ?></th>
        <th><?php esc_html_e('Payement date','lab'); ?></th>
        <th>&nbsp;</th>
      </thead>
        <tbody id="lab_admin_budget_info_list_table_tbody">
        </tbody>
    </table>
      <?php
  }