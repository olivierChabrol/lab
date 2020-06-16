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
  <form name="lab_admin_user_form">
    <table class="form-table" role="presentation">
      <tr class="user-rich-editing-wrap">
        <th scope="row">
          <label for="lab_user_name"><?php esc_html_e('Nom de l\'utilisateur','lab') ?></label>
        </th>
        <td>
          <input type="text"   id="lab_user_search"    value="" /><b>&nbsp;<span id="lab_user_id"></span></b><br>
          <input type="hidden" id="lab_user_search_id" value="" /><br>
        </td>
      </tr>
      <tr>
        <td>
          <input type="text" id="lab_user_firstname" value="" placeholder="<?php esc_html_e('First name','lab') ?>"/>
        </td>
        <td>
          <input type="text" id="lab_user_lastname" value=""  placeholder="<?php esc_html_e('Last name','lab') ?>"/>
          <select id="lab_user_sex">
            <option value=""><?php esc_html_e('Sex','lab') ?></option>
            <option value="F">Feminin</option>
            <option value="M">Masculin</option>
          </select>
        </td>
      </tr>
      <tr>
        <td colspan="2">
        <input type="text" id="lab_user_email" value="" size="40" placeholder="<?php esc_html_e('Email','lab') ?>"/> &nbsp <input type="text" id="lab_user_country">
        </td>
      </tr>
      <tr>
        <td colspan="2">
        <input type="text" id="lab_user_url" value="" size="40" placeholder="<?php esc_html_e('URL','lab') ?>"/>
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
          <label for="lab_user_funding"><?php esc_html_e('Funding','lab') ?></label>
        </td>
        <td>
          <?php lab_html_select("lab_user_funding", "lab_user_funding", "", lab_admin_get_params_userFunding, null, array("value"=>"","label"=>"None"), ""); ?>
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
          <label for="lab_user_section_cn"><?php esc_html_e('Section CN','lab') ?></label>
        </td>
        <td>
          <?php lab_html_select("lab_user_section_cn", "lab_user_section_cn", "", lab_admin_get_params_userSectionCn, null, array("value"=>"","label"=>"None"), ""); ?>
        </td>
      </tr>
      <tr>
        <td>
          <label for="lab_user_section_cnu"><?php esc_html_e('Section CNU','lab') ?></label>
        </td>
        <td>
          <?php lab_html_select("lab_user_section_cnu", "lab_user_section_cnu", "", lab_admin_get_params_userSectionCnu, null, array("value"=>"","label"=>"None"), ""); ?>
        </td>
      </tr>
      <tr>
        <td>
          <label for="lab_user_thesis_title"><?php esc_html_e('Thesis title','lab') ?></label>
        </td>
        <td>
        <input type="text" id="lab_user_thesis_title"/>
        <input type="date" id="lab_user_thesis_date"/>
        </td>
      </tr>
      <tr>
        <td>
          <label for="lab_user_hdr_title"><?php esc_html_e('HDR title','lab') ?></label>
        </td>
        <td>
        <input type="text" id="lab_user_hdr_title"/>
        <input type="date" id="lab_user_hdr_date"/>
        </td>
      </tr>
      <tr>
        <td>
          <label for="lab_user_phd_school"><?php esc_html_e('PHD school','lab') ?></label>
        </td>
        <td>
        <?php lab_html_select("lab_user_phd_school", "lab_user_phd_school", "", lab_admin_get_params_userPhdSchool, null, array("value"=>"","label"=>"None"), ""); ?>
        </td>
      </tr>
      <tr>
        <td>
          <label for="lab_user_left"><?php esc_html_e('Parti','lab') ?></label>
        </td>
        <td>
          <input type="checkbox" id="lab_user_left"> <label for="lab_user_left_date"><?php esc_html_e('Date de départ','lab') ?></label><input type="date" id="lab_user_left_date">
          <input type="hidden" id="lab_usermeta_id">
        </td>
      </tr>
      <tr>
        <td colspan="2">
          <a href="#" class="page-title-action" id="lab_user_button_user_save"><?php esc_html_e('Modifier le statut de l\'utilisateur','lab') ?></a>
          <a href="#" class="page-title-action" id="lab_user_button_delete"><?php esc_html_e('Delete user','lab') ?></a>
        </td>
      </tr>
    </table>
    <div> <!-- Rôles -->
      <h5><?php esc_html_e("Affecter des rôles à l'utilisateur",'lab') ?></h5>
      <div>
        <div id="lab_admin_user_roles">
        </div>
        <br/>
        <?php
        lab_html_select('lab_allRoles','lab_allRoles','lab_allRoles','lab_get_all_roles',null,array("value"=>0,"label"=>"--- Sélectionnez un rôle ---"),0);?>
        <button class="btn btn-primary" id="lab_admin_add_role"><?php esc_html_e("Ajouter","lab")?></button>
      </div>
    </div>
  </form>
  <div class="modal" tabindex="-1" role="dialog" id="lab_user_delete_modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><?php esc_html_e('Delete user','lab') ?></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"  id="lab_user_delete_close_icon">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p><?php esc_html_e('Keep user data in our web site ?','lab') ?><input type="checkbox" id="lab_user_keep_data"></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="lab_user_delete">Save changes</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal" id="lab_user_delete_close">Close</button>
        </div>
      </div>
    </div>
  </div>
  <form style="display:none" id="lab_admin_historic" action="javascript:lab_addHistoric(false);" style="flex-grow:1;">
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
          <input required type="date" id="lab_historic_start"/>
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
          <label required for="lab_historic_function"><?php esc_html_e('Fonction','lab') ?> : </label>
        </th>
        <td>
          <?php lab_html_select("lab_historic_function",
                                "lab_historic_function",
                                '',
                                'lab_admin_get_params_userFunction',
                                AdminParams::PARAMS_USER_FUNCTION_ID,
                                array("value"=>0,"label"=>"Sélectionnez une fonction"),0); ?>
        </td>
      </tr>
      <tr>
        <th>
          <label for="lab_historic_mobility"><?php esc_html_e('Établissement','lab') ?> : </label>
        </th>
        <td>
          <?php lab_html_select("lab_historic_mobility",
                                "lab_historic_mobility",
                                '',
                                'lab_admin_get_params_outgoingMobility',
                                AdminParams::PARAMS_OUTGOING_MOBILITY,
                                array("value"=>0,"label"=>"I2M"),0); ?>
        </td>
      </tr>
      <tr>
        <th>
          <label for="lab_historic_mobility_status"><?php esc_html_e('Outgoing mobility status','lab') ?> : </label>
        </th>
        <td>
          <?php lab_html_select("lab_historic_mobility_status",
                                "lab_historic_mobility_status",
                                '',
                                'lab_admin_get_params_outgoingMobilityStatus',
                                AdminParams::PARAMS_OUTGOING_MOBILITY_STATUS,
                                array("value"=>0,"label"=>"None"),0); ?>
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
      <tr>
        <td scope="row" colspan="2">
          <div id="lab_historic_actions">
            <input class="btn btn-primary" type="submit" id="lab_historic_add" value="<?php esc_html_e('Ajouter','lab')?>"/>
            <button style="display:none" class="btn btn-secondary" id="lab_historic_edit"><?php esc_html_e('Modifier','lab')?></button>
            <input type="reset" value="<?php esc_html_e('Annuler','lab')?>"/>
          </div>
        </td>
      </tr>
      <tr>
          <td scope="row" colspan="2">
              <div id="missingUserMetaData">
                  <h4>Missing Fields :</h4>
                  <div id="missingUserMetaDataContent"></div>
                  <a href="#" class="page-title-action" id="lab_user_button_correct_missing_usermetaDataFields"><?php esc_html_e('Correct missing metadata','lab') ?></a>
              </div>
          </td>
      </tr>
    </table>
  </form>
</div>
<br/>
<div>
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
</div>
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
  <!-- Dialogue de confirmation modal s'affichant lorsque l'utilisateur essaie de supprimer une clé -->
  <div id="lab_historic_delete_dialog" class="modal">
    <p><?php esc_html_e('Voulez-vous vraiment supprimer cette période ?','lab');?></p>
    <div id="lab_historic_delete_dialog_options">
      <a href="#" rel="modal:close"><?php esc_html_e('Annuler','lab')?></a>
      <a href="#" rel="modal:close" id="lab_history_edit_delete_confirm" entry_id=""><?php esc_html_e('Confirmer','lab'); ?></a>
    </div>
  </div>
<?php
}

/**
 * @param array $list : id, ext, begin, end, host_id, function
 * @return string HTML results
 */
function lab_admin_history($list) {
  $out = '';
  foreach ($list as $elem) {
    $date_begin = date_create_from_format("Y-m-d", $elem->begin);
    $date_end = isset($elem->end) ? date_create_from_format("Y-m-d", $elem->end) : NULL;
    $host = $elem->host_id==null ? null : new LabUser($elem->host_id);
    $out .= "<li class='lab_history_li' ".( $elem->mobility==0 ? '': "style='background-color:#".AdminParams::get_paramWithColor($elem->mobility)->color)."'>
      <div class='lab_history_dates'><div>".strftime('%d %B %G',$date_begin->getTimestamp())."</div><div>".(isset($date_end) ? strftime('%d %B %G',$date_end->getTimestamp()) : ' ')."</div></div>
      <div class='lab_history_desc'>
        <div class='lab_history_topDesc'>
          <p>".($elem->mobility==0 ? "I2M" : AdminParams::get_param($elem->mobility))."</p>
          <p>".($host==null ? ' ': "Host : ".$host->first_name . ' ' . $host->last_name)."</p>
        </div>
        <h5>· ".AdminParams::get_param($elem->function)."</h5>
      </div>
      <div class='lab_history_actions'><a class='lab_history_edit' entry_id=\"$elem->id\" href=\"#\"><i class=\"fas fa-pen\"></i></a><a class='lab_history_edit_delete' entry_id=\"$elem->id\" href='#'><i class=\"fas fa-trash\"></i></a></div>
    </li>";
  }
  return $out;
}
function lab_admin_user_roles($user_id) {
  $user = new WP_User($user_id);
  $userRoles = $user->roles;
  $all_roles = lab_get_all_roles();
  if (count($userRoles)>0) {
    $out = '';
    foreach ($all_roles as $value) {
      if (in_array($value->id, $userRoles)) {
        $out .= "<span class='badge badge-secondary user-role-badge'>".$value->value." <span class='lab_role_delete' user_id='".$user_id."' role='".$value->id."'><i class='fas fa-trash'></i></span></span>";
      }
    }
  } else {
    $out = 'Aucun rôle';
  }
  return $out;
}