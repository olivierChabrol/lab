<?php
function lab_request($param) {
    $param = shortcode_atts(array(
        'debug' => get_option('debug'),
        'view' => get_option('view'),
        'id' => get_option('id'),
        ),
        $param, 
        "lab-request"
    );
    $request_id = "";
    if(isset($param["id"])) {
        $request_id = $param["id"];
    }
    $view = isset($param["view"]) && $param["view"];

    $currentUser = new labUser(get_current_user_id());
    su_query_asset( 'css', 'su-shortcodes' );
    su_query_asset( 'js', 'jquery' );
    su_query_asset( 'js', 'su-shortcodes' );
    $html = "";
    $html .= '<div id="lab_profile_card">
              <div id="loadingAjaxGif">
                <img src="/wp-content/plugins/lab/loading.gif" />
              </div>
              <input type="hidden" id="lab_request_id" value="'.$request_id.'">';
    if ($view) {
      $html .= lab_html_select_str("lab_request_type", "lab_request_type", "", "lab_admin_get_params_request_type", null, null, null, null, array("slug"=>"slug"));
      $html .= '<div class="container"><div class="get-quote"><div class="row"><div class="col-md-12 d-flex">';
      //$html .= '<h1 id="lab_request_view"></h1> <button type="button" class="btn" id="lab_resquest_state">Primary</button>';
      $html .= '<h1 id="lab_request_view"></h1> <p id="lab_resquest_state">Primary</p>';
      if (lab_is_manager() || lab_is_admin()) {
        
        $html .= '<div id="lab_resquest_admin_button" userId="'.get_current_user_id().'"></div>';
      }
      if (lab_is_group_leader() || lab_is_admin()) {
        $html .= '<div id="lab_resquest_group_leader_button" userId="'.get_current_user_id().'"></div>';
      }
      $html .= '</div></div></div>';
      $html .= '<div class="get-quote"><div class="row"><div class="col-md-12 d-flex">';
      $html .= '<h3>'.esc_html__('Applicant', 'lab').' : <span id="lab_request_applicant" class="small"></span>';
      $html .= '</h3></div></div></div>';
      $html .= '<h3>'.esc_html__('Request title', 'lab').' : <span id="lab_request_title" class="small"></span></h3>';
      $html .= '<h3>'.esc_html__('Request', 'lab').' : <span id="lab_request_text" class="small"></span></h3>';
      $html .= '<h3>'.esc_html__('Provisional date', 'lab').' : <span id="lab_request_previsional_date" class="small"></span></h3>';
      $html .= '<h4>'.esc_html__('Expenses', 'lab').' :</h4>';
      $html .= '<div id="lab_request_expenses"></div>';

      $html .= '<h4>'.esc_html__('Uploaded files', 'lab').' :</h4>';
      $html .= '<div id="lab_request_files"></div>';
      $html .= 'Save this files in :';
      $html .= '<div class="highlight"><pre><code id="lab_resquest_files_directory"></code></pre></div>';
      $html .= '<h4>'.esc_html__('Historics', 'lab').' :</h4>';
      $html .= '<div id="lab_request_historic"></div>';
      $html .= '</div>';
    }
    else {      
      $html .= "".$currentUser->first_name.' '.$currentUser->last_name;
      $html .= '<br /><label for="lab_request_type_request">'.esc_html__('Type of request', 'lab').'</label>';
      //$html .= lab_html_select_str("lab_request_type_request", "lab_request_type_request", "", "lab_admin_get_params_request_type", null, null, null, null, null);
      $html .= lab_html_select_str("lab_request_type", "lab_request_type", "", "lab_admin_get_params_request_type", null, null, null, null, array("slug"=>"slug"));
      $html .= '<label id="lab_request_date_label" for="lab_request_previsional_date">'.esc_html__('Provisional date', 'lab').' :</label> <input type="date" id="lab_request_previsional_date">';
      $html .= '<br />';
      $html .= '<label for="lab_request_title">'.esc_html__('Request title', 'lab').'</label>';
      $html .= '<input id="lab_request_title" type="text" size="50" placeholder="'.esc_html__('ex : European Congress of Mathematics', 'lab').'"></input><br/>';
      $html .= '<label for="lab_request_text">'.esc_html__('Request', 'lab').'</label>';
      $html .= '<textarea id="lab_request_text" placeholder="'.esc_html__('ex : Hi M Durant, i want to go to the European Congress of Mathematis by plane', 'lab').'" rows="5" cols="50"></textarea><br/>';
      $html .= '<h5>Estimated cost :</h5>';
      $html .= generate_expense_type("transport");
      $html .= generate_expense_type("hosting");
      $html .= generate_expense_type("fooding");
      $html .= '<h5>Liste des fichiers uploadés : <span id="lab_request_files"></span></h5><br/></<div><div>';

      $html .= nav_tabs();

      $html .= '<button type="button" class="btn btn-success" id="lab_request_send">'.esc_html__('Send', 'lab').'</button>';
      $html .= '<br/>Penser à consulter le <a href="/linstitut/informations-ressources/livrets-guides/guide-des-missions/">guide des missions</a>';
      $html .= '</div>';
    }
  return $html;
}

function generate_expense_type($suffix) {
  $html = '<label for="lab_request_expense_'.$suffix.'">'.esc_html__($suffix.' costs', 'lab').' : </label>';
  $html .= '<input type="hidden" id="lab_request_expense_'.$suffix.'_id" value="">';
  $html .= generate_expense_combobox($suffix);
  $html .= generate_financial_support($suffix);
  $html .= '&nbsp;<input type="text" id="lab_request_expense_'.$suffix.'_amount" value="0.0"></input>&euro;<br/>';
  return $html;
}

function get_financial_support() {
  return AdminParams::lab_admin_get_params_budgetFunds();
}

function generate_financial_support($suffix) {
  $html = "";
  if (lab_is_admin() || lab_is_manager()) {
    $html .= lab_html_select_str("lab_request_expense_financial_support_".$suffix, "lab_request_expense_financial_support_".$suffix, "", "get_financial_support", null, array("value"=>"-1","label"=>"None"), null);
  }
  else {
    $html .= '<input type="hidden" id="lab_request_expense_financial_support_'.$suffix.'_id" value="-1">';
  }
  return $html;
}

function generate_expense_combobox($suffix) {
  $html = '<select id="lab_request_expense_'.$suffix.'">';
  $html .= '<option value="-1">'.esc_html__('Exterior', 'lab').'</option>';
  $groups = null;
  $contracts = null;
  if (lab_is_admin() || lab_is_manager()) {
    $groups = lab_admin_group_load();
    $contracts = lab_admin_contract_get_all_contracts();
  }
  else {
    $host = new labUser(get_current_user_id());
    $groups = lab_admin_group_by_user($host->id);
    $contracts = lab_admin_contract_get_contracts_by_user($host->id);
  }
  foreach($groups as $group) {
    $html .= '<option value="1_'.$group->id.'">Equipe '.$group->acronym.'</option>';
  }
  foreach($contracts as $contract) {
    $html .= '<option value="2_'.$contract->contract_id.'">Contact '.$contract->name.'</option>';
  }
  $html .= '</select>';
  return $html;
}

function nav_tabs() {
  $html ="";
  $html .= '<h2 class="nav-tab-wrapper lab-request-tab-wrapper">';
  $html .= '<a id="lab_request_ingo_tab_legal" style="position: relative" class="nav-tab lab-request-tab" href="#">legal information</a>';
  $html .= '<a id="lab_request_ingo_tab_upload" style="position: relative" class="nav-tab lab-request-tab" href="#">Upload Files</a>';
  $html .= '<a id="lab_request_ingo_tab_doc" style="position: relative" class="nav-tab lab-request-tab" href="#">Documents</a>';
  $html .= '</h2>';
  $html .= '<div id="lab_request_ingo_tab_legal_div"><br><br>
    <p>Les informations recueillies font l’objet d’un traitement informatique destiné à la commande de 
    billets de transport et de nuitées d\'hôtels pris en charge par l\'I2M. Les destinataires des 
    données sont les services concernés de l\'I2M et les groupements titulaires du marché mission. 
    Conformément à la loi "Informatique et Libertés" du 6 janvier 1978, vous bénéficiez d’un droit 
    d’accès et de rectification aux informations qui vous concernent. Si vous souhaitez exercer 
    ce droit et obtenir communication des informations vous concernant, veuillez-vous adresser à 
    Direction Déléguée aux Achats et à l’Innovation (DDAI), Bât. 1, 1 place Aristide BRIAND - 
    92195 MEUDON cedex. Vous pouvez également, pour des motifs légitimes, 
    vous opposer au traitement des données vous concernant.</p>
  </div>';
  $html .= '<div id="lab_request_ingo_tab_upload_div"><br><br>';
  $html .= generate_upload_code('nic', esc_html__('Add National identity card', 'lab'));
  $html .= generate_upload_code('passport', esc_html__('Add Passport', 'lab'));
  $html .= generate_upload_code('rib', esc_html__('Add Bank statement of identity', 'lab'));
  $html .= generate_upload_code_with_name();
  $html .= '</div>
  <div id="lab_request_ingo_tab_doc_div"><br><br>
    <p>Penser à consulter le <a href="/linstitut/informations-ressources/livrets-guides/guide-des-missions/">guide des missions</a></p>
    <p>Liste des documents a remplir pour partir en mission</p>
    <p>Liste des documents a remplir pour partir en mission à l\'étranger</p>
  </div>';
  return $html;
}

function jq_tabs() {
  $html =  
  '<div id="tabs-request">
    <ul>
      <li><a href="#tabs-1">Legal informations</a></li>
      <li><a href="#tabs-2">Files</a></li>
      <li><a href="#tabs-3">Documents</a></li>
    </ul>
    <div id="tabs-1">
      <p>Les informations recueillies font l’objet d’un traitement informatique destiné à la commande de 
      billets de transport et de nuitées d\'hôtels pris en charge par l\'I2M. Les destinataires des 
      données sont les services concernés de l\'I2M et les groupements titulaires du marché mission. 
      Conformément à la loi "Informatique et Libertés" du 6 janvier 1978, vous bénéficiez d’un droit 
      d’accès et de rectification aux informations qui vous concernent. Si vous souhaitez exercer 
      ce droit et obtenir communication des informations vous concernant, veuillez-vous adresser à 
      Direction Déléguée aux Achats et à l’Innovation (DDAI), Bât. 1, 1 place Aristide BRIAND - 
      92195 MEUDON cedex. Vous pouvez également, pour des motifs légitimes, 
      vous opposer au traitement des données vous concernant.</p>
    </div>
    <div id="tabs-2">
      <p>';
      $html .= generate_upload_code('nic', esc_html__('National identity card', 'lab'));
      $html .= generate_upload_code('passport', esc_html__('Add Passport', 'lab'));
      $html .= generate_upload_code('rib', esc_html__('Add Bank statement of identity', 'lab'));
      $html .= generate_upload_code_with_name();
      $html .='</p>
    </div>
    <div id="tabs-3">
      <p>Penser à consulter le <a href="/linstitut/informations-ressources/livrets-guides/guide-des-missions/">guide des missions</a></p>
      <p>Liste des documents a remplir pour partir en mission</p>
      <p>Liste des documents a remplir pour partir en mission à l\'étranger</p>
    </div>
  </div>';
return $html;
}

function generate_upload_code_with_name() {
  return '<div id="lab_request_add_file_name_div" style="display:show">
                <label for="lab_request_add_file_name">Add another file</label>
                <input type="text" id="lab_request_add_file_name_name">
                <input type="file" id="lab_request_add_file_name" accept="image/*,.pdf">
                <input type="hidden" id="lab_request_file_name_url" value="">
                <button id="lab_request_description_file_name" file-type="name">'.esc_html__("Upload","lab").'</button>
                <input type="hidden" id="lab_request_description_div_descriptionId_name" value="">
            </div>';
}

function generate_upload_code($fileName,$label) {
    return '<div id="lab_request_add_file_'.$fileName.'_div" style="display:show">
                <label for="lab_request_add_file_'.$fileName.'">'.$label.'</label>
                <input type="file" id="lab_request_add_file_'.$fileName.'" accept="image/*,.pdf">
                <input type="hidden" id="lab_request_file_'.$fileName.'_url" value="">
                <button id="lab_request_description_file_'.$fileName.'" file-type="'.$fileName.'">'.esc_html__("Upload","lab").'</button>
                <input type="hidden" id="lab_request_description_div_descriptionId_'.$fileName.'" value="">
            </div>';
}

function generate_cnil() {
    return '<div class="su-spoiler su-spoiler-style-default su-spoiler-icon-caret" data-scroll-offset="0" data-anchor-in-url="no">
              <div class="su-spoiler-title" tabindex="0" role="button"><span class="su-spoiler-icon"></span>Mentions légales/CNIL</div>
              <div class="su-spoiler-content su-u-clearfix su-u-trim">
              Les informations recueillies font l’objet d’un traitement informatique destiné à la commande de 
              billets de transport et de nuitées d\'hôtels pris en charge par l\'I2M. Les destinataires des 
              données sont les services concernés de l\'I2M et les groupements titulaires du marché mission. 
              Conformément à la loi "Informatique et Libertés" du 6 janvier 1978, vous bénéficiez d’un droit 
              d’accès et de rectification aux informations qui vous concernent. Si vous souhaitez exercer 
              ce droit et obtenir communication des informations vous concernant, veuillez-vous adresser à 
              Direction Déléguée aux Achats et à l’Innovation (DDAI), Bât. 1, 1 place Aristide BRIAND - 
              92195 MEUDON cedex. Vous pouvez également, pour des motifs légitimes, 
              vous opposer au traitement des données vous concernant.
              </div>
            </div>';
}
?>