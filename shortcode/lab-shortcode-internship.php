<?php
function lab_internship($param) {
    $param = shortcode_atts(array(
        'debug' => get_option('debug'),
        'view'  => get_option('view'),
        'id'    => get_option('id'),
        'year'  => get_option('year'),
        ),
        $param, 
        "lab-internship"
    );

    $year = "";
    if(isset($param["year"])) {
        $year = $param["year"];
    }
    else {
      $year = date("Y");
    }
    $years = list_internship_years();

    su_query_asset( 'css', 'su-shortcodes' );
    su_query_asset( 'js', 'jquery' );
    su_query_asset( 'js', 'su-shortcodes' );
    $html = "";
    // is group manager or user can manage budget (for MC Rigat for example) or is admin
    if(is_user_logged_in() && (lab_is_manager() || lab_user_has_role(get_current_user_id(), "lab_manager") || lab_is_admin() )) {
    //$html .= 'option : "'.get_option('login_page_url').'"';
    $html .= '<div id="lab_internship">
              <div id="loadingAjaxGif">
                <img src="/wp-content/plugins/lab/loading.gif" />
              </div>';
    $html .= '<div id="lab_internship_see_intern" class="labModal"></div>';
    $html .= '<div id="lab_internship_delete_ask_intern" class="labModalTest"><div class="labModalContent">
      <input type="hidden" id="lab_modal_obj_id">
      <span class="close closeModalAction" id="labModalDeleteAskContentClose" >&times;</span>
      <span>Voulez vous suprimer le stage de : <br/><span id="lab_internship_delete_text_content"></span><br/></span>
      <a href="#" class="closeModalAction" id="lab_internship_delete_intern_close" class="btn btn-light">'. esc_html__('Cancel','lab') .'</a>
      <a href="#" rel="modal:close" id="lab_internship_delete_confirm" keyid="" class="btn btn-success lab-btn-right">'. esc_html__('Confirm','lab') .'</a>
    </div></div>';
    $html .= '<div id="lab_internship_add_intern" class="labModalTest"><div class="labModalContent">

    <span class="close" id="labModalContentClose">&times;</span>
    <input type="hidden" id="lab_internship_add_user_id">
    <input type="hidden" id="lab_internship_add_id">
    <label for="lab_internship_add_email">Email</label><input type="text" id="lab_internship_add_email"><br>
    <label for="lab_internship_add_firstname">Pr&eacute;nom</label><input type="text" id="lab_internship_add_firstname">
    <label for="lab_internship_add_lastname">Nom</label><input type="text" id="lab_internship_add_lastname"><br>
    <label for="lab_internship_add_training">Parcours</label><input type="text" id="lab_internship_add_training">
    <label for="lab_internship_add_establishment">Etablissement</label><input type="text" id="lab_internship_add_establishment"><br>
    <label for="lab_internship_add_begin">Date début du stage</label><input type="date" id="lab_internship_add_begin">
    <label for="lab_internship_add_end">Date fin du stage</label><input type="date" id="lab_internship_add_end"><br>
    <label for="lab_internship_host_name">Encadrant</label><input type="text" id="lab_internship_host_name">
    <input type="hidden" id="lab_internship_add_host_id">
    <label for="lab_internship_add_convention_state">Convention</label><select id="lab_internship_add_convention_state"><option value="0">Non signée</option><option value="1">Signée</option><option value="2">En cours</option></select><br>';
    $html .= '<table><thead><td>Equipe</td><td>Tutelle</td><td>Nb mois</td><td>Montant</td></thead><tbody>';
    
    for ($i = 1 ; $i < 5 ; $i++) {
      $html .= '<tr>';
      $html .= '<td><input type="hidden" id="lab_internship_add_f_id_'.$i.'">'.lab_html_select_str("lab_internship_add_f_team_".$i, "lab_internship_add_f_team_".$i, null, "lab_admin_group_select_group", "acronym", array("value"=>0,"label"=>"None"))."</td>";
      $html .= '<td>'.lab_html_select_str("lab_internship_add_f_tutelage_".$i, "lab_internship_add_f_tutelage_".$i, null, "lab_admin_budget_funds", null, array("value"=>"0","label"=>"None"), "")."</td>";
      $html .= '<td><input type="text" size="2" id="lab_internship_add_f_nb_month_'.$i.'"></td>';
      $html .= '<td><input type="text" size="4" id="lab_internship_add_f_amount_'.$i.'"></td>';
      $html .= '<tr>';
    }
    //*/
    $html .= '</tbody></table><div id="lab_internship_add_intern_cost"></div><div id="lab_internship_add_intern_options">
      <a href="#" id="lab_internship_add_intern_close" class="btn btn-light">'. esc_html__('Cancel','lab') .'</a>
      <a href="#" rel="modal:close" id="lab_internship_add_confirm" keyid="" class="btn btn-success lab-btn-right">'. esc_html__('Confirm','lab') .'</a>
      </div>';
    $html .= '</div></div>';
    $html .= '<label for="lab_internship_year">Année : </label><select id="lab_internship_year">';
    foreach($years as $y) {
      $html .= '<option ';
      if ($year == $y) {
        $html .= " selected ";
      }
      $html .= ' val="'.$y.'"';
      $html .= '>'.$y.'</option>';
    }
    $html .= '<select/><button type="button" class="btn btn-primary" id="lab_internship_add_intern_button">Ajouter un·e stagiair·e</button>';
    $html .= '<table id="lab_internship_table" class="table table-striped table-hover"><thead class="thead-dark"><th>Stagiaire</th><th colspan="2">Debut</th><th>Fin</th><th>Encadrant</th><th>Convention</th><th>Equipe</th><th>Financier</th><th># mois</th><th>&euro;</th><th>Equipe</th><th>Financier</th><th># mois</th><th>&euro;</th><th>Equipe</th><th>Financier</th><th># mois</th><th>&euro;</th><th>Equipe</th><th>Financier</th><th># mois</th><th>&euro;</th><th>Actions</th></thead><tbody id="lab_internship_body"/></table>';
    }
    else {
      $html = "Vous ne possédez pas de droits suffisants pour consulter cette page";
    }

    return $html;
}