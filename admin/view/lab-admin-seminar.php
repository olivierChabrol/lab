<?php
  function lab_admin_new_seminar() {
    $active_tab = 'new';
    if (isset($_GET['tab'])) {
      $active_tab = $_GET['tab'];
    }
    $seminarId = "";
    if (isset($_GET['id'])) {
      $seminarId = $_GET['id'];
    }
?>
<div id="loadingAjaxGif"><img src="/wp-content/plugins/lab/loading.gif"/></div>
    <div class="wrap">
      <h1 class="wp-heading-inline"><?php esc_html_e('Seminar management','lab'); ?></h1>
      <hr class="wp-header-end">
      <h2 class="nav-tab-wrapper">
        <a id="lab_keyring_default_tab_pointer" style="position: relative" class="nav-tab <?php echo $active_tab == 'new' ? 'nav-tab-active' : ''; ?>"   href="<?php echo add_query_arg(array('tab' => 'new'), $_SERVER['REQUEST_URI']); ?>"><?php esc_html_e('New Seminar','lab'); ?></a>
        <a id="lab_keyring_default_tab_pointer" style="position: relative" class="nav-tab <?php echo $active_tab == 'list' ? 'nav-tab-active' : ''; ?>"  href="<?php echo add_query_arg(array('tab' => 'list'), remove_query_arg("id", $_SERVER['REQUEST_URI']))  ; ?>"><?php esc_html_e('Seminar list','lab'); ?></a>
      </h2>
<?php

if (!lab_admin_checkTable("lab_seminar")) {
    echo "<p id='lab_keyring_noKeysTableWarning'>La table <em>lab_contract</em> n'a pas été trouvée dans la base, vous devez d'abord la créer ici : </p>";
    echo '<button class="lab_keyring_create_table_keys" id="lab_admin_contract_create_table">'.esc_html__('Créer la table Contrat','lab').'</button>';
}
if (!lab_admin_checkTable("lab_financial")) {
    echo "<p id='lab_keyring_noKeysTableWarning'>La table <em>lab_contract_user</em> n'a pas été trouvée dans la base, vous devez d'abord la créer ici : </p>";
    echo '<button class="lab_keyring_create_table_keys" id="lab_admin_contract_create_table">'.esc_html__('Créer la table Contrat user','lab').'</button>';
}
if ($active_tab == 'new') {
    lab_admin_seminar_new($seminarId);
} else if ($active_tab == 'list') {
    lab_admin_seminar_list();
} else {
    lab_admin_seminar_new($seminarId);
}
  
}

   

  function lab_admin_seminar_new($seminarId = "") {
?>
<input type="hidden" id="lab_admin_seminar_form" value="<?php echo $seminarId; ?>">
<table class="widefat fixed lab_keyring_table">
    <tbody>
        <tr>
            <td>
                <label for="lab_admin_contract_name"><?php esc_html_e('Name','lab') ?></label>
            </td>
            <td>
                <input type="text"   id="lab_admin_seminar_name" maxlength="500">
                <input type="hidden" id="lab_admin_seminar_id">
                <button class="btn" id="lab_admin_seminar_delete" disabled="true"><?php esc_html_e('Delete','lab'); ?></button>
            </td>
        </tr>
        <tr>
            <td>
                <label for="lab_admin_seminar_location"><?php esc_html_e('Location','lab') ?></label>
            </td>
            <td>
                <input type="text" id="lab_admin_seminar_location"maxlength="600">
            </td>
        </tr>
        <tr>
            <td>
                <label for="lab_admin_seminar_start"><?php esc_html_e('Start','lab') ?></label>
            </td>
            <td>
                <input type="date"   id="lab_admin_seminar_start">
            </td>
        </tr>
        <tr>
            <td>
                <label for="lab_admin_seminar_end"><?php esc_html_e('End','lab') ?></label>
            </td>
            <td>
                <input type="date"   id="lab_admin_seminar_end">
            </td>
        </tr>
        <tr>
            <td>
                <label for="lab_admin_seminar_funder_int"><?php esc_html_e('Seminar international funder','lab') ?></label>
            </td>
            <td>
                <input type="text"   id="lab_admin_seminar_funder_int" maxlength="255">
            </td>
        </tr>
        <tr>
            <td>
                <label for="lab_admin_seminar_funder_nat"><?php esc_html_e('Seminar national funder','lab') ?></label>
            </td>
            <td>
                <input type="text"   id="lab_admin_seminar_funder_nat" maxlength="255">
            </td>
        </tr>
        <tr>
            <td>
                <label for="lab_admin_seminar_funder_reg"><?php esc_html_e('Seminar regional funder','lab') ?></label>
            </td>
            <td>
                <input type="text"   id="lab_admin_seminar_funder_reg" maxlength="255">
            </td>
        </tr>  
        <tr>
            <td>
                <label for="lab_admin_seminar_funder_lab"><?php esc_html_e('Seminar labo funder','lab') ?></label>
            </td>
            <td>
                <input type="text"   id="lab_admin_seminar_funder_labo" maxlength="255">
            </td>
        </tr>
        <tr>
            <td>
                <label for="lab_admin_seminar_guests_number"><?php esc_html_e('Guests number(s)','lab') ?></label>
            </td>
            <td>
                <input type="number"   id="lab_admin_seminar_guests_number" >
            </td>
        </tr>
        <tr>
            <td>
                <label for="lab_admin_seminar_details"><?php esc_html_e('Details', 'lab') ?></label>
            </td>
            <td>
                <input type="text" id="lab_admin_seminar_details" maxlength="255">   
            </td>
        </tr>
        <tr>
          <td scope="col" colspan="2"><button class="page-title-action" id="lab_admin_seminar_create"><?php esc_html_e('Add','lab'); ?></button></td>
        </tr>
    </table>
<?php
  }


function lab_admin_seminar_list() {

}


// Create new seminar 
//  id userid(connected user) financialid(creates a linked financial) name location funderInt funderNat funderReg funderLab startDate endDate guestsNumber details