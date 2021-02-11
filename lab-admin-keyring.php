<?php
  function lab_keyring() {
    $active_tab = 'entry';
    if (isset($_GET['tab'])) {
      $active_tab = $_GET['tab'];
    }
    ?>
    <div class="wrap">
      <h1 class="wp-heading-inline"><?php esc_html_e('Key management','lab'); ?></h1>
      <hr class="wp-header-end">
      <h2 class="nav-tab-wrapper">
        <a id="lab_keyring_default_tab_pointer" style="position: relative" class="nav-tab <?php echo $active_tab == 'entry' ? 'nav-tab-active' : ''; ?>"   href="<?php echo add_query_arg(array('tab' => 'entry')  , $_SERVER['REQUEST_URI']); ?>"><?php esc_html_e('keyring entry','lab'); ?></a>
        <a id="lab_keyring_default_tab_pointer" style="position: relative" class="nav-tab <?php echo $active_tab == 'default' ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg(array('tab' => 'default'), $_SERVER['REQUEST_URI']); ?>"><?php esc_html_e('Key','lab'); ?></a>
        <a id="lab_keyring_second_tab_pointer"  style="position: relative" class="nav-tab <?php echo $active_tab == 'second' ? 'nav-tab-active' : ''; ?>"  href="<?php echo add_query_arg(array('tab' => 'second') , $_SERVER['REQUEST_URI']); ?>"><?php esc_html_e('Loans','lab'); ?></a>
      </h2>
      <?php
      if (!lab_admin_checkTable("lab_keys")) {
        echo "<p id='lab_keyring_noKeysTableWarning'>La table <em>wp_lab_keys</em> n'a pas été trouvée dans la base, vous devez d'abord la créer ici : </p>";
        echo '<button class="lab_keyring_create_table_keys" id="lab_keyring_create_table_keys">'.esc_html__('Create table Keys','lab').'</button>';
      }
      if (!lab_admin_checkTable("lab_key_loans")) {
        echo "<p id='lab_keyring_noLoansTableWarning'>La table <em>wp_lab_key_loans</em> n'a pas été trouvée dans la base, vous devez d'abord la créer ici : </p>";
        echo '<button class="lab_keyring_create_table_loans" id="lab_keyring_create_table_loans">'.esc_html__('Create table Loans','lab').'</button>';
      }
      if ($active_tab == 'default') {
        lab_keyring_tab_keys();
      } else if ($active_tab == 'entry') {
        lab_keyring_tab_entry();
      } else {
        lab_keyring_tab_users();
      }
  }

  function lab_keyring_tab_entry() {
?>
<table class="widefat fixed lab_keyring_table">
  <tbody>
    <tr id="lab_keyring_who">
          <td scope="col"><?php esc_html_e('User','lab'); ?> :</td>
          <td scope="col" colspan="6">
            <input type="text" id="lab_keyring_entry_user" placeholder="<?php esc_html_e('User name','lab'); ?>"/>
            <input type="hidden" id="lab_keyring_entry_user_id" value="">
          </td>
    </tr>
<?php //Récupère la liste des types de clés existants
  $output ="";
  $params = new AdminParams;
  $i = 0;
  foreach ( $params->get_params_fromId($params::PARAMS_KEYTYPE_ID) as $r ) {
?>
    <tr id="lab_keyring_addKey<?php echo $i ?>">
          <td scope="col"><?php esc_html_e('New','lab'); ?> :</td>
          <td scope="col"><?php print($r->value); ?><input type="hidden" id="lab_keyring_entry_type_<?php echo $i ?>" value="<?php echo $r->id?>"></td>
          <td scope="col">
            <input type="text" id="lab_keyring_entry_number<?php echo $i ?>" placeholder="123" index="<?php echo $i ?>"/>
            <input type="hidden" id="lab_keyring_entry_key_id<?php echo $i ?>" value="">
          </td>
          <td scope="col">
            <input type="text" id="lab_keyring_entry_office<?php echo $i ?>" placeholder="Numero de Bureau"/>
          </td>
          <td scope="col">
            <input type="text" id="lab_keyring_entry_brand<?php echo $i ?>" placeholder="<?php esc_html_e('Brand','lab'); ?>"/>
          </td>
          <!-- 
          <?php esc_html_e('ok','lab'); ?>
          <?php esc_html_e('lost','lab'); ?>
          <?php esc_html_e('broken','lab'); ?>
          -->
          <td scope="col">
            <select id="lab_keyring_entry_site<?php echo $i ?>">
              <?php //Récupère la liste des Sites existants
                $output ="";
                $params = new AdminParams;
                foreach ( $params->get_params_fromId($params::PARAMS_SITE_ID) as $r ) {
                  $output .= "<option value=".$r->id.">".$r->value."</option>";
                }
                echo $output;
              ?>
            </select>
          </td>
          <td scope="col" colspan="2">
          <input type="text" id="lab_keyring_entry_commentary<?php echo $i ?>" placeholder="<?php echo esc_html__('Comment','lab').'('.esc_html__('optional','lab').')'; ?>"/>
          </td>
    </tr>
  <?php
    $i++;
  }
  ?>
        <tr>
          <td scope="col"><button class="page-title-action" id="lab_keyring_entry_create"><?php esc_html_e('Create','lab'); ?></button></td>
        </tr>
      </tbody>
    </table>
<?php
    $html = '<input id="lab_keyring_entry_number" type="hidden" value="'.$i.'">';
    $html .= '<div id="loadingAjaxGif"><img src="/wp-content/plugins/lab/loading.gif"/></div>';
    echo $html;
  }

  function lab_keyring_tab_keys() {
    ?>
    <!-- Dialogue de confirmation modal s'affichant lorsque l'utilisateur essaie de supprimer une clé -->
    <div id="lab_keyring_delete_dialog" class="modal">
      <p><?php esc_html_e('Do you really want to delete this key?','lab');?></p>
      <div id="lab_keyring_delete_dialog_options">
        <a href="#" rel="modal:close"><?php esc_html_e('Cancel','lab')?></a>
        <a href="#" rel="modal:close" id="lab_keyring_keyDelete_confirm" keyid=""><?php esc_html_e('Confirm','lab'); ?></a>
      </div>
    </div>
    <!-- Dialogue de confirmation modal s'affichant lorsque l'utilisateur essaie de terminer un prêt -->
    <div id="lab_keyring_endLoan_dialog" class="modal">
      <p><?php esc_html_e('Voulez-vous vraiment terminer ce prêt ?','lab'); ?></p>
      <p><?php esc_html_e('Date de rendu','lab'); ?> : <span id="lab_keyring_endLoan_date"><?php esc_html_e("Aujourd'hui",'lab') ?></span></p>
      <div id="lab_keyring_delete_dialog_options">
        <a href="#" rel="modal:close"><?php esc_html_e('Cancel','lab'); ?></a>
        <a href="#" rel="modal:close" id="lab_keyring_endLoan_confirm" keyid=""><?php esc_html_e('Confirm','lab'); ?></a>
      </div>
    </div>
    <!-- Prêts en cour -->
    <h2><?php esc_html_e('Liste des clés','lab'); ?></h2>
    <div id="lab_keyring_search_options">
      <input type="text" id="lab_keyring_keySearch" placeholder="<?php esc_html_e('Rechercher une clé','lab'); ?>"/>
      <div>
        <label><?php esc_html_e('Page','lab'); ?> :</label>
        <select id="lab_keyring_page">
          <option value="0">1</option>
        </select>
      </div>
      <div>
        <label><?php esc_html_e('Nombre de résultats par page','lab'); ?> :</label>
        <select id="lab_keyring_keysPerPage">
          <option value="10">10</option>
          <option value="25">25</option>
          <option value="50">50</option>
          <option value="custom"><?php esc_html_e('Autre','lab'); ?> </option>
        </select>
        <input type="text" id="lab_keyring_keysPerPage_otherValue" style="width:5em" hidden placeholder="100"/>
      </div>
    </div>
    <p id="lab_keyring_search_totalResults"></p>
    <hr/>
    <table class="widefat fixed lab_keyring_table">
      <thead>
        <tr>
          <th scope="col"><?php esc_html_e('ID','lab'); ?></th>
          <th scope="col"><?php esc_html_e('Type','lab'); ?></th>
          <th scope="col"><?php esc_html_e('Numéro','lab'); ?></th>
          <th scope="col"><?php esc_html_e('Bureau','lab'); ?></th>
          <th scope="col"><?php esc_html_e('Marque','lab'); ?></th>
          <th scope="col"><?php esc_html_e('Site','lab'); ?></th>
          <th scope="col"><?php esc_html_e('State','lab'); ?></th>
          <th scope="col"><?php esc_html_e('Commentaire','lab'); ?></th>
          <th scope="col" class="lab_keyring_icon"><?php esc_html_e('Dispo','lab'); ?></th>
          <th scope="col" class="lab_keyring_icon"><?php esc_html_e('Actions','lab'); ?></th>
        </tr>
      </thead>
      <tbody id="lab_keyring_keysList">
      </tbody>
      <tfoot>
        <tr style="display:none;" class="lab_keyring_pageNav">
          <td scope="col" class="lab_keyring_icon" colspan='9'>
            <span class="lab_keyring_prevPage"><?php esc_html_e('Page précédente','lab'); ?> &#8656;</span>
            <span class="lab_keyring_nextPage">&#8658; <?php esc_html_e('Page suivante','lab'); ?></span>
          </td>
        </tr>
        <tr style="display:none;" id="lab_keyring_editForm">
        <td scope="col"><?php esc_html_e('Modifier','lab'); ?> :</td>
          <td scope="col">
            <select id="lab_keyring_edit_type">
              <?php //Récupère la liste des types de clés existants
                $output ="";
                foreach ( AdminParams::get_params_fromId(AdminParams::PARAMS_KEYTYPE_ID) as $r ) {
                  $output .= "<option value=".$r->id.">".$r->value."</option>";
                }
                echo $output;
              ?>
            </select>
          </td>
          <td scope="col">
            <input type="text" id="lab_keyring_edit_number" placeholder="<?php esc_html_e('numéro','lab'); ?>"/>
          </td>
          <td scope="col">
            <input type="text" id="lab_keyring_edit_office" placeholder="<?php esc_html_e('bureau','lab'); ?>"/>
          </td>
          <td scope="col">
            <input type="text" id="lab_keyring_edit_brand" placeholder="<?php esc_html_e('Marque','lab'); ?>"/>
          </td>
          <td scope="col">
            <select id="lab_keyring_edit_site">
              <?php //Récupère la liste des types de clés existants
                $output ="";
                $params = new AdminParams;
                foreach ( $params->get_params_fromId($params::PARAMS_SITE_ID) as $r ) {
                  $output .= "<option value=".$r->id.">".$r->value."</option>";
                }
                echo $output;
              ?>
            </select>
          </td>
          <td scope="col">
            <select id="lab_keyring_edit_state">
              <?php //Récupère la liste des types de clés existants
                $output ="";
                $params = new AdminParams;
                foreach ( $params->get_params_fromId($params::PARAMS_KEY_STATE) as $r ) {
                  $output .= "<option value=".$r->id.">".esc_html__($r->value, "lab")."</option>";
                }
                echo $output;
              ?>
            </select>
          </td>
          <td scope="col" colspan="2">
          <input type="text" id="lab_keyring_edit_commentary" placeholder="<?php echo esc_html__('Commentaire','lab').'('.esc_html__('facultatif','lab').')'; ?>"/>
          </td>
          <td scope="col"><button class="page-title-action" id="lab_keyring_editForm_submit" keyid=""><?php esc_html_e('Modifier','lab'); ?></button></td>
        </tr>
        <tr id="lab_keyring_newForm">
          <td scope="col"><?php esc_html_e('Nouvelle','lab'); ?> :</td>
          <td scope="col">
            <select id="lab_keyring_newKey_type">
              <?php //Récupère la liste des types de clés existants
                $output ="";
                $params = new AdminParams;
                foreach ( $params->get_params_fromId($params::PARAMS_KEYTYPE_ID) as $r ) {
                  $output .= "<option value=".$r->id.">".$r->value."</option>";
                }
                echo $output;
              ?>
            </select>
          </td>
          <td scope="col">
            <input type="text" id="lab_keyring_newKey_number" placeholder="123"/>
          </td>
          <td scope="col">
            <input type="text" id="lab_keyring_newKey_office" placeholder="102A"/>
          </td>
          <td scope="col">
            <input type="text" id="lab_keyring_newKey_brand" placeholder="<?php esc_html_e('Marque','lab'); ?>"/>
          </td>
          <!-- 
          <?php esc_html_e('ok','lab'); ?>
          <?php esc_html_e('lost','lab'); ?>
          <?php esc_html_e('broken','lab'); ?>
          -->
          <td scope="col">
            <select id="lab_keyring_newKey_site">
              <?php //Récupère la liste des Sites existants
                $output ="";
                $params = new AdminParams;
                foreach ( $params->get_params_fromId($params::PARAMS_SITE_ID) as $r ) {
                  $output .= "<option value=".$r->id.">".$r->value."</option>";
                }
                echo $output;
              ?>
            </select>
          </td>
          <td scope="col" colspan="2">
          <input type="text" id="lab_keyring_newKey_commentary" placeholder="<?php echo esc_html__('Commentaire','lab').'('.esc_html__('facultatif','lab').')'; ?>"/>
          </td>
          <td scope="col"><button class="page-title-action" id="lab_keyring_newKey_create"><?php esc_html_e('Créer','lab'); ?></button></td>
        </tr>
      </tfoot>
    </table>
    <hr/>
    <br/>
    <?php lab_keyring_loanForm("default"); ?>
  </div>
    <?php
  }
  function lab_keyring_tab_users() {
    ?>
    <div id="lab_keyring_endLoan_dialog" class="modal">
      <p><?php esc_html_e('Voulez-vous vraiment terminer ce prêt ?','lab'); ?></p>
      <p><?php esc_html_e('Date de rendu : ','lab'); ?><span id="lab_keyring_endLoan_date"><?php esc_html_e("Aujourd'hui",'lab') ?></span></p>
      <div id="lab_keyring_delete_dialog_options">
        <a href="#" rel="modal:close"><?php esc_html_e('Cancel','lab'); ?></a>
        <a href="#" rel="modal:close" id="lab_keyring_endLoan_confirm" keyid=""><?php esc_html_e('Confirm','lab'); ?></a>
      </div>
    </div>
    <h2><?php esc_html_e('Liste des prêts en cours','lab'); ?></h2>
    <div id="lab_keyring_search_options">
      <input type="text" id="lab_keyring_loanSearch" placeholder="<?php esc_html_e('Rechercher un utilisateur','lab'); ?>"/>
      <div>
        <label><?php esc_html_e('Page','lab'); ?> :</label>
        <select id="lab_keyring_page">
          <option value="0">1</option>
        </select>
      </div>
      <div>
        <label><?php esc_html_e('Nombre de résultats par page','lab'); ?> :</label>
        <select id="lab_keyring_keysPerPage">
          <option value="10">10</option>
          <option value="25">25</option>
          <option value="50">50</option>
          <option value="custom"><?php esc_html_e('Autre','lab'); ?> :</option>
        </select>
        <input type="text" id="lab_keyring_keysPerPage_otherValue" style="width:5em" hidden placeholder="100"/>
      </div>
    </div>
    <p id="lab_keyring_search_totalResults"></p>
    <hr/>
    <div id="lab_keyring_current_loans">
      <table class="widefat fixed lab_keyring_table">
        <thead>
        <?php lab_keyring_loansHead();?>
        </thead>
        <tbody id="lab_keyring_currentLoans">
        </tbody>
        <tfoot>
          <tr style="display:none;" class="lab_keyring_pageNav">
            <td scope="col" class="lab_keyring_icon" colspan='9'>
              <span class="lab_keyring_prevPage"><?php esc_html_e('Page précédente','lab'); ?> &#8656;</span>
              <span class="lab_keyring_nextPage">&#8658; <?php esc_html_e('Page suivante','lab'); ?></span>
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
    <hr/>
    <br/>
    <?php lab_keyring_loanForm("second"); ?>
    </div>
    <?php
  }
  function lab_keyring_loansHead() {
    ?>
    <tr>
      <th scope="col"><?php esc_html_e('ID','lab'); ?></th>
      <th scope="col"><?php esc_html_e('Clé','lab'); ?></th>
      <th scope="col"><?php esc_html_e('Référent','lab'); ?></th>
      <th scope="col"><?php esc_html_e('Utilisateur','lab'); ?></th>
      <th scope="col"><?php esc_html_e('Début','lab'); ?></th>
      <th scope="col"><?php esc_html_e('Échéance','lab'); ?></th>
      <th scope="col"><?php esc_html_e('Commentaire','lab'); ?></th>
      <th scope="col" class="lab_keyring_icon"><?php esc_html_e('Terminé','lab'); ?></th>
      <th scope="col" class="lab_keyring_icon"><?php esc_html_e('Actions','lab'); ?></th>
    </tr>
    <?php
  }
  function lab_keyring_loanForm($tab) {
    ?>
    <h2 id="lab_keyring_loan_title"><?php esc_html_e('Gestion des prêts','lab'); ?></h2>
    <img id="lab_keyring_loading_gif" width="60" style="display:none" height="60" src="https://i.ya-webdesign.com/images/loading-png-gif.gif"/>
    <div class="lab_keyring_loans_management" style="display: none;">
      <h3 style="display:none;" class="lab_keyring_loan_new"><?php esc_html_e('Nouveau prêt','lab'); ?></h3>
      <div style="display:none;" class="lab_keyring_loan_current">
        <h3><?php esc_html_e('Prêt en cours','lab'); ?></h3>
        <h4><button class="lab_keyring_loanContract lab_keyring_loanform_actions"><?php esc_html_e('Afficher Reçu','lab'); ?></button></h4>
      </div>
      <div id="lab_keyring_loanform">
        <table id="lab_keyring_loanform_table">
          <thead>
            <tr><th colspan="2">
            <?php //Récupère la liste des types de clés existants
                  $output ="";
                  $params = new AdminParams;
                  foreach ( $params->get_params_fromId($params::PARAMS_KEYTYPE_ID) as $r ) {
                    $output .= "<span style='display:none;' class='lab_keyring_loanform_type' typeID=".$r->id.">".$r->value."</span>";
                  }
                  echo $output;
                ?>
            </th></tr>
          </thead>
          <tbody>
            <tr>
              <th><?php esc_html_e('ID','lab'); ?></th>
              <td id="lab_keyring_loanform_key_id"></td>
            </tr>
            <tr>
              <th><?php esc_html_e('Numéro','lab'); ?></th>
              <td id="lab_keyring_loanform_key_number"></td>
            </tr>
            <tr>
              <th><?php esc_html_e('Bureau','lab'); ?></th>
              <td id="lab_keyring_loanform_key_office"></td>
            </tr>
            <tr>
              <th><?php esc_html_e('Marque','lab'); ?></th>
              <td id="lab_keyring_loanform_key_brand"></td>
            </tr>
            <tr>
              <th><?php esc_html_e('Site','lab'); ?></th>
              <td id="lab_keyring_loanform_key_site">
              <?php //Récupère la liste des types de clés existants
                  $output ="";
                  $params = new AdminParams;
                  foreach ( $params->get_params_fromId($params::PARAMS_SITE_ID) as $r ) {
                    $output .= "<span class='lab_keyring_loanform_key_sites' style='display:none' siteID=".$r->id.">".$r->value."</span>";
                  }
                  echo $output;
                ?>
              </td>
            </tr>
            <tr>
              <th><?php esc_html_e('Commentaire','lab'); ?></th>
              <td id="lab_keyring_loanform_key_commentary"></td>
            </tr>
          </tbody>
        </table>
        <div id="lab_keyring_loanform_Useroptions">
          <label for="lab_keyring_loanform_referent"><?php esc_html_e('Référent','lab'); ?> : </label>
          <input id="lab_keyring_loanform_referent" type="text" default_id="<?php echo get_current_user_id();?>" default="<?php echo wp_get_current_user()->display_name;?>"/>
          <label for="lab_keyring_loanform_user"><?php esc_html_e('Usager','lab'); ?> :</label>
          <input type="text" name="lab_keyring_loanform_user" id="lab_keyring_loanform_user"/>
        </div>
        <div id="lab_keyring_loanform_dateOptions">
          <label for="lab_keyring_loanform_start_date"><?php esc_html_e('Date de début','lab'); ?> :</label>
          <input type="date" id="lab_keyring_loanform_start_date"/>
          <label for=""><?php esc_html_e('Date de fin','lab'); ?> : <em class="lab_keyring_loan_new">(<?php esc_html_e('facultatif','lab'); ?>)</em> </label>
          <input type="date" id="lab_keyring_loanform_end_date"/>
        </div>
        <div id="lab_keyring_loanform_actions">
          <textarea id="lab_keyring_loanform_commentary" placeholder="<?php esc_html_e('Commentaire faculatif','lab'); ?>..."></textarea>
          <label><?php esc_html_e('Actions','lab'); ?></label>
          <button class="page-title-action lab_keyring_loanform_actions lab_keyring_loan_new" style="display:none" id="lab_keyring_loanform_create"><?php esc_html_e('Créer le prêt','lab'); ?></button>
          <button class="page-title-action lab_keyring_loanform_actions lab_keyring_loan_current" style="display:none" id="lab_keyring_loanform_edit"><?php esc_html_e('Modifier le prêt','lab'); ?></button>
          <button class="page-title-action lab_keyring_loanform_actions lab_keyring_loan_current" style="display:none" id="lab_keyring_loanform_end"><?php esc_html_e('Marquer comme rendu','lab'); ?></button>
        </div>
      </div>
    </div>
    <div id="lab_keyring_all_loans" selector="" style="display: none;">
    <h3><?php $tab == 'default' ? esc_html_e('Historique des prêts pour cette clé','lab') : esc_html_e("Historique des prêts de l'utilisateur",'lab') ?><span id="lab_keyring_loans_title"></span> :</h3>
    <table class="widefat fixed lab_keyring_table">
      <thead>
        <?php lab_keyring_loansHead();?>
      </thead>
      <tbody class="lab_keyring_loansList">
      </tbody>
      <tfoot>
      </tfoot>
    </table>
    </div>
  <?php
  }
  function lab_keyringtableFromKeysList($list) {
    $output='';
    $adminParams = new AdminParams;
    foreach ($list as $element) {
      $output .= '<tr>';
      foreach (['id','type','number', 'office','brand','site', 'state','commentary'] as $field) {
        if ($field == "site") {  
          $output .= '<td scope="col">'.AdminParams::get_param($element->site).'</td>';
        } elseif ( $field =='type' ) {
          $output .= '<td scope="col">'.AdminParams::get_param($element->type).'</td>';
        } elseif ( $field =='state' ) {
          $output .= '<td scope="col">'.AdminParams::get_param($element->state).'</td>';
        } else {
          $output.='<td scope="col">'.$element->$field.'</td>';
        }
      }
      $element->available == 1 ?
                  $output .= '<td scope="col" class="lab_keyring_icon"><span style="color:#00cf00; font-size: 2em">✓</span><a href="#lab_keyring_loan_title" class="page-title-action lab_keyring_key_lend" keyid="'.$element->id.'">'.esc_html__("Prêter",'lab').'</a></td>'
                : $output.='<td scope="col" class="lab_keyring_icon">❌<a href="#lab_keyring_loan_title" class="page-title-action lab_keyring_key_lend" keyid="'.$element->id.'">'.esc_html__("Voir prêt",'lab').'</a></td>';
      $output .= '<td scope="col" class="lab_keyring_icon">
                    <a class="page-title-action lab_keyring_key_edit" href="#lab_keyring_newForm" keyid="'.$element->id.'">🖊</a>
                    <a class="page-title-action lab_keyring_key_delete" href="#lab_keyring_delete_dialog" rel="modal:open" keyid="'.$element->id.'">❌</a>
                  </td>
                </tr>';
    }
    return $output;
  }
  function lab_keyringtableFromLoansList($list) {
    foreach ($list as $element) {
      $output .= '<tr>';
      foreach (['id','key_id', 'referent_id','user_id', 'start_date','end_date','commentary','ended'] as $field) {
        if ($field == "ended") {  
          $output .= '<td scope="col" class="lab_keyring_icon">'.($element->$field == 1 ? "<span style='color:#00cf00; font-size: 2em'>✓</span>" : "❌").'</td>';
        } elseif ( $field =='user_id' || $field =='referent_id' ) {
          $user = lab_admin_userMetaDatas_get($element->$field);
          $output .= '<td scope="col">'.$user['first_name'].' '.$user['last_name'].'</td>';
        } elseif ( $field == 'key_id' ) {
          $key = lab_keyring_search_key($element->$field)[0];
          $output .= '<td scope="col">';
          $output .= AdminParams::get_param_all($key->type)->slug == 'key' ? '🔑' : '💳';
          //$output .= AdminParams::get_param_all($key->type)->slug;
          $output .= ' '.$key->number.'</td>';
        } elseif ( substr($field,-4,4) == 'date') {
          $date = strlen($element->$field)>0 ? date_format(date_create_from_format("Y-m-d", $element->$field),'d/m/Y') : '';
          $output.='<td scope="col">'.$date.'</td>';
        } else {
          $output.='<td scope="col">'.$element->$field.'</td>';
        }
      }
      $output.= '<td scope="col" class="lab_keyring_icon">
      <a class="page-title-action lab_keyring_loan_edit" href="#lab_keyring_loan_title" user_id="'.$element->user_id.'" loan_id="'.$element->id.'">🖊 '.esc_html__("Modifier","lab").'</a>
      </td>';
    }
    return $output;
  }
?>