<?php
/*
 * File Name: lab-admin-tab-users.php
 * Description: interface de paramètres utilisateurs, affecter un groupe à un utilisateur
 * Authors: Olivier CHABROL, Astrid BEYER, Lucas URGENTI
 * Version: 1.0
*/

function lab_admin_tab_user() {
?>
  <div style="display:flex; flex-wrap:wrap;">
  <form>
    <table class="form-table" role="presentation">
      <tr class="user-rich-editing-wrap">
        <th scope="row">
          <label for="lab_user_name"><?php esc_html_e('Nom de l\'utilisateur','lab') ?></label>
        </th>
        <td>
          <input type="text"   id="lab_user_search"    value="" /><br>
          <input type="hidden" id="lab_user_search_id" value="" /><br>
        </td>
      </tr>
      <tr>
        <td>
          <label for="lab_user_function"><?php esc_html_e('User function','lab') ?></label>
        </td>
        <td>
          <?php lab_html_select("lab_user_function", "lab_user_function", "", lab_admin_get_params_userFunction, null, array("value"=>"","label"=>"None"), ""); ?>
        </td>
      </tr>
      <tr>
        <td>
          <label for="lab_user_employer"><?php esc_html_e('Employer','lab') ?></label>
        </td>
        <td>
          <?php lab_html_select("lab_user_employer", "lab_user_employer", "", lab_admin_get_params_userEmployer, null, array("value"=>"","label"=>"None"), ""); ?>
        </td>
      </tr>
      <tr>
        <td>
          <label for="lab_user_location"><?php esc_html_e('User Location','lab') ?></label>
        </td>
        <td>
          <?php lab_html_select("lab_user_location", "lab_user_location", "", lab_admin_get_params_userLocation, null, array("value"=>"","label"=>"None"), ""); ?>
        </td>
      </tr>
      <tr>
        <td>
          <label for="lab_user_office_number"><?php esc_html_e('User office number','lab') ?></label>
        </td>
        <td>
          <input type="text"   id="lab_user_office_number"/>
        </td>
      </tr>
      <tr>
        <td>
          <label for="lab_user_office_floor"><?php esc_html_e('User office floor','lab') ?></label>
        </td>
        <td>
          <input type="text" id="lab_user_office_floor"/>
        </td>
      </tr>
      <tr>
        <td>
          <label for="lab_user_phone"><?php esc_html_e('Phone','lab') ?></label>
        </td>
        <td>
          <input type="text" id="lab_user_phone"/>
        </td>
      </tr>
      <tr>
        <td>
          <label for="lab_user_left"><?php esc_html_e('Parti','lab') ?></label>
        </td>
        <td>
          <input type="checkbox" id="lab_user_left"> <label for="lab_user_left_date"><?php esc_html_e('Date de départ','lab') ?></label><input type="text" id="lab_user_left_date">
          <input type="hidden" id="lab_usermeta_id">
        </td>
      </tr>
      <tr>
        <td colspan="2">
          <a href="#" class="page-title-action" id="lab_user_button_save_left"><?php esc_html_e('Modifier le statut de l\'utilisateur','lab') ?></a>
        </td>
      </tr>
    </table>
  </form>
  <form style="flex-grow:1;">
    <h3><?php esc_html_e('Historique de l\'utilisateur','lab') ?></h3>
    <div>
        <ul id="lab_history_list">
          
        </ul>
    </div>
    <h4><?php esc_html_e('Ajouter une période','lab') ?></h4>
    <table class="form-table" role="presentation">
      <tr>
        <th scope="row">
          <label for="lab_historic_start"><?php esc_html_e('Date de début','lab') ?> : </label>
        </th>
        <td>
          <input type="date" id="lab_historic_start"/>
        </td>
      </tr>
      <tr>
        <th>
          <label for="lab_historic_end"><?php esc_html_e('Date de fin','lab') ?> : </label>
        </th>
        <td>
          <input type="date" id="lab_historic_end"/>
        </td>
      </tr>
      <tr>
        <th>
          <label for="lab_historic_function"><?php esc_html_e('Fonction','lab') ?> : </label>
        </th>
        <td>
          <?php lab_html_select("lab_history_function",
                                "lab_history_function",
                                '',
                                'lab_admin_get_params_userFunction',
                                AdminParams::PARAMS_USER_FUNCTION_ID,
                                array("value"=>0,"label"=>"Sélectionnez une fonction"),0); ?>
        </td>
      </tr>
      <tr>
        <th>
          <label for="lab_historic_host"><?php esc_html_e('Hôte','lab') ?> : </label>
        </th>
        <td>
          <input type="text" id="lab_historic_host"/>
        </td>
      </tr>
    </table>
  </form>
  </div>
  <br/><br/>
  <h3><?php esc_html_e('Affecter des utilisateurs à des groupes','lab') ?></h3>
  
  <label for="lab_all_users"><b><?php esc_html_e('Afficher aussi les personnes qui ont déjà un groupe', 'lab') ?></b></label>
  <input type="checkbox" id="lab_all_users"/><br/>
  <label for="lab_no_users_left"><b><?php esc_html_e('Afficher aussi les utilisateurs qui ont quitté l\'Institut','lab') ?></b></label>
  <input type="checkbox" id="lab_no_users_left"/>
  <br/><br/>

  <div style="display:flex;">
    
    <!-- CHOIX USER -->

    <div style='float: left; margin-right:50px;'>
                          <label for='users'><?php esc_html_e('Choisissez une ou plusieurs personne(s) à affecter :','lab') ?>
                          </label><br/><br/>
    <select id='list_users' name='users[]' multiple style='height:300px;'></select></div>
    
    <!-- CHOIX GROUP -->

    <div style='float: right; margin-left:50px'>
                          <label for='groups'><?php esc_html_e('Choisissez le ou les groupe(s) au(x)quel(s) vous allez affecter des personnes :', 'lab') ?>
                          </label><br/><br/>
    <select id='list_groups' name='groups[]' multiple style='height:150px;'></select></div>
  
  </div>
  <button style='margin-top:10px;' id='lab_add_users_groups'><?php esc_html_e('envoyer','lab') ?></button>
  <hr/>
  <div id="ldap_menu_flex" style="display:flex; flex-wrap:wrap;">
    <form style="margin-right: 2em" id="lab_ldap_newUser" action="javascript:lab_ldap_addUser()">
      <h3><?php esc_html_e('Ajouter un utilisateur dans l\'annuaire','lab') ?></h3>
      <table class="form-table" role="presentation">
        <tr class="user-rich-editing-wrap">
          <th scope="row">
            <label for="lab_ldap_queryAmu"><?php esc_html_e('E-Mail d\'utilisateur AMU :','lab') ?></label>
          </th>
          <td>
            <input type="email" id="lab_ldap_queryAmu"/>
          </td>
        </tr>
        <tr class="user-rich-editing-wrap">
          <th scope="row">
            <label for="lab_ldap_newUser_lastName"><?php esc_html_e('Nom','lab') ?><span class="lab_form_required_star"> *</span></label>
          </th>
          <td>
            <input required type="text" id="lab_ldap_newUser_lastName"/>
          </td>
        </tr>
        <tr class="user-rich-editing-wrap">
          <th scope="row">
            <label for="lab_ldap_newUser_firstName"><?php esc_html_e('Prénom','lab') ?><span class="lab_form_required_star"> *</span></label>
          </th>
          <td>
            <input required type="text" id="lab_ldap_newUser_firstName"/>
          </td>
        </tr>
        <tr class="user-rich-editing-wrap">
          <th scope="row">
            <label for="lab_ldap_newUser_email"><?php esc_html_e('E-Mail','lab') ?><span class="lab_form_required_star"> *</span></label>
          </th>
          <td>
            <input required type="email" id="lab_ldap_newUser_email"/>
          </td>
        </tr>
        <tr class="user-rich-editing-wrap">
          <th scope="row">
            <label for="lab_ldap_newUser_uid"><?php esc_html_e('Login (uid)','lab') ?><span class="lab_form_required_star"> *</span></label>
          </th>
          <td>
            <input required type="text" id="lab_ldap_newUser_uid"/>
          </td>
        </tr>
        <tr class="user-rich-editing-wrap">
          <th scope="row">
            <label for="lab_ldap_newUser_pass"><?php esc_html_e('Mot de passe','lab') ?><span class="lab_form_required_star"> *</span></label>
          </th>
          <td>
            <input required type="text" id="lab_ldap_newUser_pass"/>
          </td>
        </tr>
        <tr class="user-rich-editing-wrap">
          <th scope="row">
            <label for="lab_ldap_newUser_org"><?php esc_html_e('Organisation','lab') ?></label>
          </th>
          <td>
            <input type="text" id="lab_ldap_newUser_org"/>
          </td>
        </tr>
        <tr class="user-rich-editing-wrap">
          <th scope="row">
            <label for="lab_ldap_newUser_addToWP"><?php esc_html_e('Ajouter l\'utilisateur à WordPress','lab') ?></label>
          </th>
          <td>
            <input type="checkbox" id="lab_ldap_newUser_addToWP"/>
          </td>
        </tr>
        <tr class="user-rich-editing-wrap">
          <td scope="row" colspan="2">
            <input type="submit" value="Valider"/>
          </td>
        </tr>
      </table>
    </form>
    <form>
      <h3><?php esc_html_e('Supprimer un utilisateur','lab') ?></h3>
      <table class="form-table" role="presentation">
        <tr class="user-rich-editing-wrap">
          <th scope="row">
            <label for="lab_ldap_delete_search"><?php esc_html_e('Nom - Prénom - mail :','lab') ?></label>
          </th>
          <td>
            <input type="email" id="lab_ldap_delete_search"/>
          </td>
        </tr>
        <tr>
          <td colspan="2">
            <p id="lab_ldap_delete_res"></p>
          </td>
        </tr>
        <tr class="user-rich-editing-wrap">
          <td scope="row" colspan="2">
            <input type="button" id="lab_ldap_delete_button" value="Supprimer"/>
          </td>
        </tr>
      </table>
    </form>
  <?php
}

/**
 * @param array $list : id, ext, begin, end, host_id, function
 * @return string HTML results
 */
function lab_admin_history($list) {
  $out = '';
  foreach ($list as $elem) {
    $date_begin = date_create_from_format("Y-m-d H:i:s", $elem->begin);
    $date_end = date_create_from_format("Y-m-d H:i:s", $elem->end);
    $host = new LabUser($elem->host_id);
    $out .= "<li class='lab_history_li'>
      <div class='lab_history_dates'><div>".strftime('%d %B %G',$date_begin->getTimestamp())."</div><div>".strftime('%d %B %G',$date_end->getTimestamp())."</div></div>
      <div class='lab_history_desc'>".AdminParams::get_param($elem->function)." host : ".$host->first_name . ' ' . $host->last_name."</div>
      <div class='lab_history_actions'><a id='lab_history_edit' entry_id=$elem->id href='#'>🖊</a><a id='lab_history_edit_delete' entry_id=$elem->id href='#'>❌</a></div>
    </li>";
  }
  return $out;
}