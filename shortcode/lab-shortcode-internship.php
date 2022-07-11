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
    $html .= '<label for="lab_internship_year">Année : </label><select id="lab_internship_year">';
    foreach($years as $y) {
        $html .= '<option val="'.$y.'">'.$y.'</option>';
    }
    $html .= '<select/>';
    $html .= '<table id="lab_internship_table" class="table"><tbody id="lab_internship_body"/></table>';
    return $html;
}