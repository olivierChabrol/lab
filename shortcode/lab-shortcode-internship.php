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
    $html .= '<div id="lab_profile_card">
              <div id="loadingAjaxGif">
                <img src="/wp-content/plugins/lab/loading.gif" />
              </div>';
    $html .= '<div id="lab_internship_add_intern" class="labModal">
    <input type="hidden" id="lab_internship_add_id">
    <label for="lab_internship_add_firstname">Pr&eacute;nom</label><input type="text" id="lab_internship_add_firstname">
    <label for="lab_internship_add_lastname">Nom</label><input type="text" id="lab_internship_add_lastname">
    <label for="lab_internship_add_email">Email</label><input type="text" id="lab_internship_add_email">
    <label for="lab_internship_add_training">Training</label><input type="text" id="lab_internship_add_training">
    <label for="lab_internship_add_establishment">Establishment</label><input type="text" id="lab_internship_add_establishment">
    <label for="lab_internship_add_begin">Date début du stage</label><input type="date" id="lab_internship_add_begin">
    <label for="lab_internship_add_end">Date fin du stage</label><input type="date" id="lab_internship_add_end">
    <label for="lab_internship_host_name">Encadrant</label><input type="text" id="lab_internship_host_name">
    <input type="hidden" id="lab_internship_add_host_id">
    <label for="lab_internship_add_convention_state">Convention</label><select id="lab_internship_add_convention_state"><option value="1">En cours</option><option value="2">Signée</option><option value="3">En cours</option></select>
    <div id="lab_internship_add_intern_options">
      <a href="#" id="lab_internship_add_intern_close">'. esc_html__('Cancel','lab') .'</a>
      <a href="#" rel="modal:close" id="lab_internship_add_confirm" keyid="">'. esc_html__('Confirm','lab') .'</a>
      </div>
    </div>';
    $html .= '<label for="lab_internship_year">Année : </label><select id="lab_internship_year">';
    foreach($years as $y) {
        $html .= '<option val="'.$y.'">'.$y.'</option>';
    }
    $html .= '<select/><button type="button" class="btn btn-primary" id="lab_internship_add_intern_button">Ajouter un·e stagiair·e</button>';
    $html .= '<table id="lab_internship_table" class="table"><tbody id="lab_internship_body"/></table>';
    return $html;
}