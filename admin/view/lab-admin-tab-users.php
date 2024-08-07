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
          <label for="lab_user_name"><?php esc_html_e('User name','lab') ?></label>
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
            <option value=""><?php esc_html_e('Gender','lab') ?></option>
            <option value="F"><?php esc_html_e('Female','lab') ?></option>
            <option value="M"><?php esc_html_e('Male','lab') ?></option>
          </select>
        </td>
      </tr>
      <tr>
        <td colspan="2">
          <input type="text" id="lab_user_slug" value="" size="40" placeholder="<?php esc_html_e('User slug','lab') ?>" disabled/>
          <a href="#" class="page-title-action" id="lab_admin_correct_slug_name"><?php esc_html_e("Correct slug name","lab")?></a>
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
          <?php lab_html_select("lab_user_function", "lab_user_function", "", "lab_admin_get_params_userFunction", null, array("value"=>"","label"=>"None"), ""); ?>
        </td>
      </tr>
      <tr>
        <td>
          <label for="lab_user_employer"><?php esc_html_e('Employer','lab') ?></label>
        </td>
        <td>
          <?php lab_html_select("lab_user_employer", "lab_user_employer", "", "lab_admin_get_params_userEmployer", null, array("value"=>"","label"=>"None"), ""); ?>
        </td>
      </tr>
      <tr>
        <td>
          <label for="lab_user_funding"><?php esc_html_e('Funding','lab') ?></label>
        </td>
        <td>
          <?php lab_html_select("lab_user_funding", "lab_user_funding", "", "lab_admin_get_params_userFunding", null, array("value"=>"","label"=>"None"), ""); ?>
        </td>
      </tr>
      <tr>
        <td>
          <label for="lab_user_location"><?php esc_html_e('User Location','lab') ?></label>
        </td>
        <td>
          <?php lab_html_select("lab_user_location", "lab_user_location", "", "lab_admin_get_params_userLocation", null, array("value"=>"","label"=>"None"), ""); ?>
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
          <label for="lab_user_section_cn"><?php esc_html_e('CN Section','lab') ?></label>
        </td>
        <td>
          <?php lab_html_select("lab_user_section_cn", "lab_user_section_cn", "", "lab_admin_get_params_userSectionCn", null, array("value"=>"","label"=>"None"), ""); ?>
        </td>
      </tr>
      <tr>
        <td>
          <label for="lab_user_section_cnu"><?php esc_html_e('CNU Section','lab') ?></label>
        </td>
        <td>
          <?php lab_html_select("lab_user_section_cnu", "lab_user_section_cnu", "", "lab_admin_get_params_userSectionCnu", null, array("value"=>"","label"=>"None"), ""); ?>
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
          <label for="lab_user_phd_support"><?php esc_html_e('PHD support','lab') ?></label>
        </td>
        <td>
        <?php lab_html_select("lab_user_phd_support", "lab_user_phd_support", "", "lab_admin_get_params_userPhdSupport", null, array("value"=>"","label"=>"None"), ""); ?>
        </td>
      </tr>
      <tr>
        <td>
          <label for="lab_user_phd_become"><?php esc_html_e('Post PHD become','lab') ?></label>
        </td>
        <td>
        <input type="text" id="lab_user_phd_become"/>
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
        <?php lab_html_select("lab_user_phd_school", "lab_user_phd_school", "", "lab_admin_get_params_userPhdSchool", null, array("value"=>"","label"=>"None"), ""); ?>
        </td>
      </tr>
      <tr>
        <td>
          <label for="lab_user_co_supervision"><?php esc_html_e('Co Supervision','lab') ?></label>
        </td>
        <td>
        <?php esc_html_e('France','lab') ?> / <input type="text" id="lab_user_co_supervision">
        </td>
      </tr>
      <tr>
        <td>
          <label for="lab_user_left"><?php esc_html_e('left','lab') ?></label>
        </td>
        <td>
          <input type="checkbox" id="lab_user_left"> <label for="lab_user_left_date"><?php esc_html_e('Departure date','lab') ?></label><input type="date" id="lab_user_left_date">
          <input type="hidden" id="lab_usermeta_id">
        </td>
      </tr>
      <tr>
        <td colspan="2">
          <a href="#" class="page-title-action" id="lab_user_button_user_save"><?php esc_html_e('Edit user status','lab') ?></a>
          <a href="#" class="page-title-action" id="lab_user_button_delete"><?php esc_html_e('Delete user','lab') ?></a>
        </td>
      </tr>
    </table>

    <div> <!-- Group -->
      <h5><?php esc_html_e("Add Group to user",'lab') ?></h5>
      <div>
        <div id="lab_admin_user_group">
        </div>
        <br/>
        <?php
        lab_html_select('lab_group','lab_group','lab_allRoles','lab_admin_group_load_all',null,array("value"=>0,"label"=>"--- Select Group ---"),0);?>
        <button class="btn btn-primary" id="lab_admin_add_group"><?php esc_html_e("Add","lab")?></button>
      </div>
    </div>
    <div> <!-- Thematics -->
      <h5><?php esc_html_e("Add thematic to user",'lab') ?></h5>
      <div>
        <div id="lab_admin_user_thematics">
        </div>
        <br/>
        <?php
        lab_html_select('lab_thematic','lab_thematic','lab_allRoles','lab_admin_thematic_load_all',null,array("value"=>0,"label"=>"--- Select thematic ---"),0);?>
        <button class="btn btn-primary" id="lab_admin_add_thematic"><?php esc_html_e("Add","lab")?></button>
      </div>
    </div>
    <div> <!-- Rôles -->
      <h5><?php esc_html_e("Assigning roles to the user",'lab') ?></h5>
      <div>
        <div id="lab_admin_user_roles">
        </div>
        <br/>
        <?php
        lab_html_select('lab_allRoles','lab_allRoles','lab_allRoles','lab_get_all_roles',null,array("value"=>0,"label"=>"--- Sélectionnez un rôle ---"),0);?>
        <button class="btn btn-primary" id="lab_admin_add_role"><?php esc_html_e("Add","lab")?></button>
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
    <h3><?php esc_html_e('User history','lab') ?></h3>
    <div>
        <ul id="lab_history_list">
          
        </ul>
    </div>
    <h4><?php esc_html_e('Add a period','lab') ?></h4>
    <table class="form-table" role="presentation">
      <tr>
        <th scope="row">
          <label for="lab_historic_start"><?php esc_html_e('Start date','lab') ?> : </label>
        </th>
        <td>
          <input required type="date" id="lab_historic_start"/>
        </td>
      </tr>
      <tr>
        <th>
          <label for="lab_historic_end"><?php esc_html_e('End date','lab') ?> : </label>
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
          <label for="lab_historic_mobility"><?php esc_html_e('Institution','lab') ?> : </label>
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
          <label for="lab_historic_host"><?php esc_html_e('Host','lab') ?> : </label>
        </th>
        <td>
          <input type="text" id="lab_historic_host"/>
        </td>
      </tr>
      <tr>
        <td scope="row" colspan="2">
          <div id="lab_historic_actions">
            <input class="btn btn-primary" type="submit" id="lab_historic_add" value="<?php esc_html_e('Add','lab')?>"/>
            <button style="display:none" class="btn btn-secondary" id="lab_historic_edit"><?php esc_html_e('Edit','lab')?></button>
            <input type="reset" value="<?php esc_html_e('Cancel','lab')?>"/>
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
<hr/>
<div id="ldap_menu_flex" style="display:flex; flex-wrap:wrap;">
  <form style="margin-right: 2em" id="lab_ldap_newUser" action="javascript:lab_ldap_addUser()">
    <h3><?php esc_html_e('Adding a user to the directory','lab') ?></h3>
    <table class="form-table" role="presentation">
      <tr class="user-rich-editing-wrap">
        <th scope="row">
          <label for="lab_ldap_queryAmu"><?php esc_html_e('User\'s email AMU :','lab') ?></label>
        </th>
        <td>
          <input type="email" id="lab_ldap_queryAmu"/>
        </td>
      </tr>
      <tr class="user-rich-editing-wrap">
        <th scope="row">
          <label for="lab_ldap_newUser_lastName"><?php esc_html_e('Last name','lab') ?><span class="lab_form_required_star"> *</span></label>
        </th>
        <td>
          <input required type="text" id="lab_ldap_newUser_lastName"/>
        </td>
      </tr>
      <tr class="user-rich-editing-wrap">
        <th scope="row">
          <label for="lab_ldap_newUser_firstName"><?php esc_html_e('First name','lab') ?><span class="lab_form_required_star"> *</span></label>
        </th>
        <td>
          <input required type="text" id="lab_ldap_newUser_firstName"/>
        </td>
      </tr>
      <tr class="user-rich-editing-wrap">
        <th scope="row">
          <label for="lab_ldap_newUser_email"><?php esc_html_e('Email','lab') ?><span class="lab_form_required_star"> *</span></label>
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
          <label for="lab_ldap_newUser_pass"><?php esc_html_e('Password','lab') ?><span class="lab_form_required_star"> *</span></label>
        </th>
        <td>
          <input required type="text" id="lab_ldap_newUser_pass"/>
        </td>
      </tr>
      <tr class="user-rich-editing-wrap">
        <th scope="row">
          <label for="lab_ldap_newUser_org"><?php esc_html_e('Organization','lab') ?></label>
        </th>
        <td>
          <input type="text" id="lab_ldap_newUser_org"/>
        </td>
      </tr>
      <tr class="user-rich-editing-wrap">
        <th scope="row">
          <label for="lab_ldap_newUser_addToWP"><?php esc_html_e('Add user to Wordpress','lab') ?></label>
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
    <p><?php esc_html_e('Do you really want to delete this period ?','lab');?></p>
    <div id="lab_historic_delete_dialog_options">
      <a href="#" rel="modal:close"><?php esc_html_e('Cancel','lab')?></a>
      <a href="#" rel="modal:close" id="lab_history_edit_delete_confirm" entry_id=""><?php esc_html_e('Confirm','lab'); ?></a>
    </div>
  </div>
<?php
}


function lab_admin_history_fields($elem) {
  if ($elem == null) {
    return null;
  }
  //var_dump($elem->begin);
  $histoObj = new StdClass();
  $histoObj->begin = date_create_from_format("Y-m-d", $elem->begin);
  $histoObj->end   = isset($elem->end) ? date_create_from_format("Y-m-d", $elem->end) : NULL;
  
  $host = $elem->host_id==null ? null : new LabUser($elem->host_id);
  $histoObj->host  = $host==null ? null : $host->first_name . ' ' . $host->last_name;
  $histoObj->function = $elem->function;
  $histoObj->mobility        = $elem->mobility!=0?AdminParams::get_param($elem->mobility):null;
  $histoObj->mobility_status = $elem->mobility_status!=0?AdminParams::get_param($elem->mobility_status):null;
  //*/
  return $histoObj;
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
    //var_dump($elem->end);
    $host = $elem->host_id==null ? null : new LabUser($elem->host_id);
    $out .= "<li class='lab_history_li' ".( $elem->mobility==0 ? '': "style='background-color:#".AdminParams::get_paramWithColor($elem->mobility)->color)."'>
      <div class='lab_history_dates'><div>".$elem->begin."</div><div>".(isset($date_end) ? $elem->end : ' ')."</div></div>
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