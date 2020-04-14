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
    <p></p>
    <button class="lab_keyring_create_table_keys" id="lab_keyring_create_table_keys">Créer la table Keys</button>
    <button class="lab_keyring_create_table_loans" id="lab_keyring_create_table_loans">Créer la table Loans</button>
    <hr/>
    <h2>Liste des clés</h2>
    <input type="text" id="lab_keyring_keySearch" placeholder="Rechercher une clé"/>
    <hr/>
    <table class="widefat fixed" id="lab_keyring_table">
      <thead>
        <tr>
          <th scope="col" style="width:4em">ID</th>
          <th scope="col" style="width:6em">Type</th>
          <th scope="col" style="width:6em">Numéro</th>
          <th scope="col" style="width:6em">Bureau</th>
          <th scope="col">Marque</th>
          <th scope="col">Site</th>
          <th scope="col">Commentaire</th>
          <th scope="col">Dispo</th>
          <th scope="col">Actions</th>
        </tr>
      </thead>
      <tbody id="lab_keyring_keysList">
      </tbody>
      <tfoot>
        <tr id="lab_keyring_editForm">
        <td scope="col">Modifier :</td>
          <td scope="col">
            <select id="lab_keyring_edit_type">
              <?php //Récupère la liste des types de clés existants
                $output ="";
                $params = new AdminParams;
                foreach ( $params->get_params_fromId($params::PARAMS_KEYTYPE_ID) as $r ) {
                  $output .= "<option value =".$r->id.">".$r->value."</option>";
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
                  $output .= "<option value =".$r->id.">".$r->value."</option>";
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
                  $output .= "<option value =".$r->id.">".$r->value."</option>";
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
              <?php //Récupère la liste des types de clés existants
                $output ="";
                $params = new AdminParams;
                foreach ( $params->get_params_fromId($params::PARAMS_SITE_ID) as $r ) {
                  $output .= "<option value =".$r->id.">".$r->value."</option>";
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
                  $output .= '<td scope="col" class="lab_keyring_icon">✅<button class="page-title-action lab_keyring_key_lend">Prêter</button></td>'
                : $output.='<td scope="col" class="lab_keyring_icon">❌<button class="page-title-action lab_keyring_key_lend">Voir prêt</button></td>';
      $output .= '<td scope="col" class="lab_keyring_icon">
                    <a class="page-title-action lab_keyring_key_edit" href="#lab_keyring_newForm" keyid="'.$element->id.'">🖊Modifier</a>
                    <a class="page-title-action lab_keyring_key_delete" href="#lab_keyring_delete_dialog" rel="modal:open" keyid="'.$element->id.'">❌</a>
                  </td>
                </tr>';
    }
    return $output;
  }
?>