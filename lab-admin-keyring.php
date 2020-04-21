<?php
  function lab_admin_tab_keyring() {
    echo "<h1>".__('Gestion des clés','lab')."</h1>";
    if (!lab_admin_checkTable("lab_keys")) {
      echo "<p id='lab_keyring_noKeysTableWarning'>La table <em>wp_lab_keys</em> n'a pas été trouvée dans la base, vous devez d'abord la créer ici : </p>";
      echo '<button class="lab_keyring_create_table_keys" id="lab_keyring_create_table_keys">'.esc_html__('Créer la table Keys','lab').'</button>';
    }
    if (!lab_admin_checkTable("lab_key_loans")) {
      echo "<p id='lab_keyring_noLoansTableWarning'>La table <em>wp_lab_key_loans</em> n'a pas été trouvée dans la base, vous devez d'abord la créer ici : </p>";
      echo '<button class="lab_keyring_create_table_loans" id="lab_keyring_create_table_loans">'.esc_html__('Créer la table Loans','lab').'</button>';
    }
    ?>
    <!-- Dialogue de confirmation modal s'affichant lorsque l'utilisateur essaie de supprimer une clé-->
    <div id="lab_keyring_delete_dialog" class="modal">
      <p><?php esc_html_e('Voulez-vous vraiment supprimer cette clé ?','lab');?></p>
      <div id="lab_keyring_delete_dialog_options">
        <a href="#" rel="modal:close"><?php esc_html_e('Annuler','lab')?></a>
        <a href="#" rel="modal:close" id="lab_keyring_keyDelete_confirm" keyid=""><?php esc_html_e('Confirmer','lab'); ?></a>
      </div>
    </div>
    <div id="lab_keyring_endLoan_dialog" class="modal">
      <p><?php esc_html_e('Voulez-vous vraiment terminer ce prêt ?','lab'); ?></p>
      <p><?php esc_html_e('Date de rendu : ','lab'); ?><span id="lab_keyring_endLoan_date"><?php esc_html_e("Aujourd'hui",'lab') ?></span></p>
      <div id="lab_keyring_delete_dialog_options">
        <a href="#" rel="modal:close"><?php esc_html_e('Annuler','lab'); ?></a>
        <a href="#" rel="modal:close" id="lab_keyring_endLoan_confirm" keyid=""><?php esc_html_e('Confirmer','lab'); ?></a>
      </div>
    </div>
    <p></p>
    <hr/>
    <h2><?php esc_html_e('Liste des clés','lab'); ?></h2>
    <div id="lab_keyring_search_options">
      <input type="text" id="lab_keyring_keySearch" placeholder="<?php esc_html_e('Rechercher une clé','lab'); ?>"/>
      <div>
        <label><?php esc_html_e('Page :','lab'); ?></label>
        <select id="lab_keyring_page">
          <option value="0">1</option>
        </select>
      </div>
      <div>
        <label><?php esc_html_e('Nombre de clés par page :','lab'); ?></label>
        <select id="lab_keyring_keysPerPage">
          <option value="10">10</option>
          <option value="25">25</option>
          <option value="50">50</option>
          <option value="custom"><?php esc_html_e('Autre :','lab'); ?></option>
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
          <th scope="col"><?php esc_html_e('Commentaire','lab'); ?></th>
          <th scope="col" class="lab_keyring_icon"><?php esc_html_e('Dispo','lab'); ?></th>
          <th scope="col" class="lab_keyring_icon"><?php esc_html_e('Actions','lab'); ?></th>
        </tr>
      </thead>
      <tbody id="lab_keyring_keysList">
      </tbody>
      <tfoot>
        <tr style="display:none;" id ="lab_keyring_nextPage">
          <td scope="col" class="lab_keyring_icon" colspan='9' id='lab_keyring_nextPage_button'>➢ <?php esc_html_e('Page suivante','lab'); ?></td>
        </tr>
        <tr style="display:none;" id="lab_keyring_editForm">
        <td scope="col"><?php esc_html_e('Modifier','lab'); ?> :</td>
          <td scope="col">
            <select id="lab_keyring_edit_type">
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
          <td scope="col" colspan="2">
          <input type="text" id="lab_keyring_edit_commentary" placeholder="<?php esc_html_e('Commentaire (facultatif)','lab'); ?>"/>
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
          <input type="text" id="lab_keyring_newKey_commentary" placeholder="<?php esc_html_e('Commentaire (facultatif)','lab'); ?>"/>
          </td>
          <td scope="col"><button class="page-title-action" id="lab_keyring_newKey_create"><?php esc_html_e('Créer','lab'); ?></button></td>
        </tr>
      </tfoot>
    </table>
    <hr/>
    <br/>
    <h2 id="lab_keyring_loan_title"><?php esc_html_e('Gestion des prêts','lab'); ?></h2>
    <!--<a href="admin.php?page=wp-lab.php&tab=loan-contract" target="_blank">​​​​​​​​​​​​​​​​​print pdf</a>-->
    <h3 style="display:none;" class="lab_keyring_loan_new"><?php esc_html_e('Nouveau prêt','lab'); ?></h3>
    <div style="display:none;" class="lab_keyring_loan_current">
      <h3><?php esc_html_e('Prêt en cours','lab'); ?></h3>
      <h4><button id="lab_keyring_loanContract" class="lab_keyring_loanform_actions"><?php esc_html_e('Afficher Reçu','lab'); ?></button></h4>
    </div>
    <div style="display:none;" id="lab_keyring_loanform">
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
        <label for=""><?php esc_html_e('Date de fin','lab'); ?> : <em class="lab_keyring_loan_new"><?php esc_html_e('(facultatif)','lab'); ?></em> </label>
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
    <div id="lab_keyring_all_loans" style="display: none;">
      <h3><?php esc_html_e('Historique des prêts pour cette clé','lab'); ?> :</h3>
      <table class="widefat fixed lab_keyring_table">
        <thead>
          <tr>
            <th scope="col"><?php esc_html_e('ID','lab'); ?></th>
            <th scope="col"><?php esc_html_e('Clé','lab'); ?></th>
            <th scope="col"><?php esc_html_e('Référent','lab'); ?></th>
            <th scope="col"><?php esc_html_e('Utilisateur','lab'); ?></th>
            <th scope="col"><?php esc_html_e('Début','lab'); ?></th>
            <th scope="col"><?php esc_html_e('Échéance','lab'); ?></th>
            <th scope="col"><?php esc_html_e('Commentaire','lab'); ?></th>
            <th scope="col" class="lab_keyring_icon"><?php esc_html_e('Terminé','lab'); ?></th>
          </tr>
        </thead>
        <tbody id="lab_keyring_loansList">
        </tbody>
        <tfoot>
        </tfoot>
      </table>
    </div>
    <?php
  }
  function wp_lab_keyring_tableFromKeysList($list) {
    $output='';
    $adminParams = new AdminParams;
    foreach ($list as $element) {
      $output .= '<tr>';
      foreach (['id','type','number', 'office','brand','site','commentary'] as $field) {
        if ($field == "site") {  
          $output .= '<td scope="col">'.$adminParams->get_param($element->site).'</td>';
        } elseif ( $field =='type' ) {
          $output .= '<td scope="col">'.$adminParams->get_param($element->type).'</td>';
        } else {
          $output.='<td scope="col">'.$element->$field.'</td>';
        }
      }
      $element->available == 1 ?
                  $output .= '<td scope="col" class="lab_keyring_icon">✅<a href="#lab_keyring_loan_title" class="page-title-action lab_keyring_key_lend" keyid="'.$element->id.'">'.esc_html__("Prêter",'lab').'</a></td>'
                : $output.='<td scope="col" class="lab_keyring_icon">❌<a href="#lab_keyring_loan_title" class="page-title-action lab_keyring_key_lend" keyid="'.$element->id.'">'.esc_html__("Voir prêt",'lab').'</a></td>';
      $output .= '<td scope="col" class="lab_keyring_icon">
                    <a class="page-title-action lab_keyring_key_edit" href="#lab_keyring_newForm" keyid="'.$element->id.'">🖊</a>
                    <a class="page-title-action lab_keyring_key_delete" href="#lab_keyring_delete_dialog" rel="modal:open" keyid="'.$element->id.'">❌</a>
                  </td>
                </tr>';
    }
    return $output;
  }
  function wp_lab_keyring_tableFromLoansList($list) {
    foreach ($list as $element) {
      $output .= '<tr>';
      foreach (['id','key_id', 'referent_id','user_id', 'start_date','end_date','commentary','ended'] as $field) {
        if ($field == "ended") {  
          $output .= '<td scope="col" class="lab_keyring_icon">'.($element->$field == 1 ? "✅" : "❌").'</td>';
        } elseif ( $field =='user_id' || $field =='referent_id' ) {
          $user = lab_admin_username_get($element->$field);
          $output .= '<td scope="col">'.$user['first_name'].' '.$user['last_name'].'</td>';
        } else {
          $output.='<td scope="col">'.$element->$field.'</td>';
        }
      }
    }
    return $output;
  }
?>