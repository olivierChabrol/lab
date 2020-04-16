<?php
  function lab_admin_tab_keyring() {
    echo "<h1>Gestion des clés</h1>";
    if (!lab_admin_checkTable("wp_lab_keys")) {
      echo "<p id='lab_keyring_noKeysTableWarning'>La table <em>wp_lab_keys</em> n'a pas été trouvée dans la base, vous devez d'abord la créer ici : </p>";
    }
    if (!lab_admin_checkTable("wp_lab_key_loans")) {
      echo "<p id='lab_keyring_noLoansTableWarning'>La table <em>wp_lab_key_loans</em> n'a pas été trouvée dans la base, vous devez d'abord la créer ici : </p>";
    }
    ?>
    <!-- Dialogue de confirmation modal s'affichant lorsque l'utilisateur essaie de supprimer une clé-->
    <div id="lab_keyring_delete_dialog" class="modal">
      <p>Voulez-vous vraiment supprimer cette clé ?</p>
      <div id="lab_keyring_delete_dialog_options">
        <a href="#" rel="modal:close">Annuler</a>
        <a href="#" rel="modal:close" id="lab_keyring_keyDelete_confirm" keyid="">Confirmer</a>
      </div>
    </div>
    <div id="lab_keyring_endLoan_dialog" class="modal">
      <p>Voulez-vous vraiment terminer ce prêt ?</p>
      <p>Date de rendu : <span id="lab_keyring_endLoan_date">Aujourd'hui</span></p>
      <div id="lab_keyring_delete_dialog_options">
        <a href="#" rel="modal:close">Annuler</a>
        <a href="#" rel="modal:close" id="lab_keyring_endLoan_confirm" keyid="">Confirmer</a>
      </div>
    </div>
    <p></p>
    <button class="lab_keyring_create_table_keys" id="lab_keyring_create_table_keys">Créer la table Keys</button>
    <button class="lab_keyring_create_table_loans" id="lab_keyring_create_table_loans">Créer la table Loans</button>
    <hr/>
    <h2>Liste des clés</h2>
    <div id="lab_keyring_search_options">
      <input type="text" id="lab_keyring_keySearch" placeholder="Rechercher une clé"/>
      <div>
        <label>Page :</label>
        <select id="lab_keyring_page">
          <option value="0">1</option>
        </select>
      </div>
      <div>
        <label>Nombre de clés par page :</label>
        <select id="lab_keyring_keysPerPage">
          <option value="10">10</option>
          <option value="25">25</option>
          <option value="50">50</option>
          <option value="custom">Autre :</option>
        </select>
        <input type="text" id="lab_keyring_keysPerPage_otherValue" style="width:5em" hidden placeholder="100"/>
      </div>
    </div>
    <p id="lab_keyring_search_totalResults"></p>
    <hr/>
    <table class="widefat fixed lab_keyring_table">
      <thead>
        <tr>
          <th scope="col">ID</th>
          <th scope="col">Type</th>
          <th scope="col">Numéro</th>
          <th scope="col">Bureau</th>
          <th scope="col">Marque</th>
          <th scope="col">Site</th>
          <th scope="col">Commentaire</th>
          <th scope="col" class="lab_keyring_icon">Dispo</th>
          <th scope="col" class="lab_keyring_icon">Actions</th>
        </tr>
      </thead>
      <tbody id="lab_keyring_keysList">
      </tbody>
      <tfoot>
        <tr style="display:none;" id ="lab_keyring_nextPage">
          <td scope="col" class="lab_keyring_icon" colspan='9' id='lab_keyring_nextPage_button'>➢ Page suivante</td>
        </tr>
        <tr style="display:none;" id="lab_keyring_editForm">
        <td scope="col">Modifier :</td>
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
            <input type="text" id="lab_keyring_edit_number" placeholder="numéro"/>
          </td>
          <td scope="col">
            <input type="text" id="lab_keyring_edit_office" placeholder="bureau"/>
          </td>
          <td scope="col">
            <input type="text" id="lab_keyring_edit_brand" placeholder="Marque"/>
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
          <input type="text" id="lab_keyring_edit_commentary" placeholder="Commentaire (facultatif)"/>
          </td>
          <td scope="col"><button class="page-title-action" id="lab_keyring_editForm_submit" keyid="">Modifier</button></td>
        </tr>
        <tr id="lab_keyring_newForm">
          <td scope="col">Nouvelle :</td>
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
            <input type="text" id="lab_keyring_newKey_brand" placeholder="Marque"/>
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
          <input type="text" id="lab_keyring_newKey_commentary" placeholder="Commentaire (facultatif)"/>
          </td>
          <td scope="col"><button class="page-title-action" id="lab_keyring_newKey_create">Créer</button></td>
        </tr>
      </tfoot>
    </table>
    <hr/>
    <br/>
    <h2 id="lab_keyring_loan_title">Gestion des prêts</h2>
    <!--<a href="admin.php?page=wp-lab.php&tab=loan-contract" target="_blank">​​​​​​​​​​​​​​​​​print pdf</a>-->
    <h3 style="display:none;" class="lab_keyring_loan_new">Nouveau prêt</h3>
    <div style="display:none;" class="lab_keyring_loan_current">
      <h3>Prêt en cours</h3>
      <h4><button id="lab_keyring_loanContract" class="lab_keyring_loanform_actions">Afficher Reçu</button></h4>
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
            <th>ID</th>
            <td id="lab_keyring_loanform_key_id"></td>
          </tr>
          <tr>
            <th>Numéro</th>
            <td id="lab_keyring_loanform_key_number"></td>
          </tr>
          <tr>
            <th>Bureau</th>
            <td id="lab_keyring_loanform_key_office"></td>
          </tr>
          <tr>
            <th>Marque</th>
            <td id="lab_keyring_loanform_key_brand"></td>
          </tr>
          <tr>
            <th>Site</th>
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
            <th>Commentaire</th>
            <td id="lab_keyring_loanform_key_commentary"></td>
          </tr>
        </tbody>
      </table>
      <div id="lab_keyring_loanform_Useroptions">
        <label for="lab_keyring_loanform_referent">Référent : </label>
        <input id="lab_keyring_loanform_referent" type="text" default_id="<?php echo get_current_user_id();?>" default="<?php echo wp_get_current_user()->display_name;?>"/>
        <label for="lab_keyring_loanform_user">Usager :</label>
        <input type="text" name="lab_keyring_loanform_user" id="lab_keyring_loanform_user"/>
      </div>
      <div id="lab_keyring_loanform_dateOptions">
        <label for="lab_keyring_loanform_start_date">Date de début :</label>
        <input type="date" id="lab_keyring_loanform_start_date"/>
        <label for="">Date de fin : <em class="lab_keyring_loan_new">(facultatif)</em> </label>
        <input type="date" id="lab_keyring_loanform_end_date"/>
      </div>
      <div id="lab_keyring_loanform_actions">
        <textarea id="lab_keyring_loanform_commentary" placeholder="Commentaire faculatif..."></textarea>
        <label>Actions</label>
        <button class="page-title-action lab_keyring_loanform_actions lab_keyring_loan_new" style="display:none" id="lab_keyring_loanform_create">Créer le prêt</button>
        <button class="page-title-action lab_keyring_loanform_actions lab_keyring_loan_current" style="display:none" id="lab_keyring_loanform_edit">Modifier le prêt</button>
        <button class="page-title-action lab_keyring_loanform_actions lab_keyring_loan_current" style="display:none" id="lab_keyring_loanform_end">Marquer comme rendu</button>
      </div>
    </div>
    <div id="lab_keyring_all_loans" style="display: none;">
      <h3>Tous les prêts :</h3>
      <table class="widefat fixed lab_keyring_table">
        <thead>
          <tr>
            <th scope="col">ID</th>
            <th scope="col">Clé</th>
            <th scope="col">Référent</th>
            <th scope="col">Utilisateur</th>
            <th scope="col">Début</th>
            <th scope="col">Échéance</th>
            <th scope="col">Commentaire</th>
            <th scope="col" class="lab_keyring_icon">Terminé</th>
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
                  $output .= '<td scope="col" class="lab_keyring_icon">✅<a href="#lab_keyring_loan_title" class="page-title-action lab_keyring_key_lend" keyid="'.$element->id.'">Prêter</a></td>'
                : $output.='<td scope="col" class="lab_keyring_icon">❌<a href="#lab_keyring_loan_title" class="page-title-action lab_keyring_key_lend" keyid="'.$element->id.'">Voir prêt</a></td>';
      $output .= '<td scope="col" class="lab_keyring_icon">
                    <a class="page-title-action lab_keyring_key_edit" href="#lab_keyring_newForm" keyid="'.$element->id.'">🖊Modifier</a>
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