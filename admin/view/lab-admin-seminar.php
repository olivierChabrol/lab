<?php
  function lab_admin_new_seminar() {
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
      <h1 class="wp-heading-inline"><?php esc_html_e('Seminar management','lab'); ?></h1>
      <hr class="wp-header-end">
      <h2 class="nav-tab-wrapper">
        <a id="lab_keyring_default_tab_pointer" style="position: relative" class="nav-tab <?php echo $active_tab == 'new' ? 'nav-tab-active' : ''; ?>"   href="<?php echo add_query_arg(array('tab' => 'new'), $_SERVER['REQUEST_URI']); ?>"><?php esc_html_e('New Seminar','lab'); ?></a>
        <a id="lab_keyring_default_tab_pointer" style="position: relative" class="nav-tab <?php echo $active_tab == 'list' ? 'nav-tab-active' : ''; ?>"  href="<?php echo add_query_arg(array('tab' => 'list'), remove_query_arg("id", $_SERVER['REQUEST_URI']))  ; ?>"><?php esc_html_e('Seminar list','lab'); ?></a>
      </h2>
<?php
  }

  if (!lab_admin_checkTable("lab_seminar")) {
    echo "<p id='lab_keyring_noKeysTableWarning'>La table <em>lab_contract</em> n'a pas été trouvée dans la base, vous devez d'abord la créer ici : </p>";
    echo '<button class="lab_keyring_create_table_keys" id="lab_admin_contract_create_table">'.esc_html__('Créer la table Contrat','lab').'</button>';
    }
    if (!lab_admin_checkTable("lab_financial")) {
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
  



  function lab_admin_seminar_new($contractId = "") {
?>




// Create new seminar 
//  id userid(connected user) financialid(creates a linked financial) name location funderInt funderNat funderReg funderLab startDate endDate guestsNumber details