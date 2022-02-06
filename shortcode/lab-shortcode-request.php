<?php
function lab_request($param) {
    $param = shortcode_atts(array(
        'debug' => get_option('debug'),
        ),
        $param, 
        "lab-request"
    );
    $currentUser = new labUser(get_current_user_id());
    $html = '<div id="loadingAjaxGif"><img src="/wp-content/plugins/lab/loading.gif"/></div><input type="hidden" id="lab_request_type_id">';//"<h1>".esc_html__('Make a request', 'lab')."</h1><br/>";
    $html .= "".$currentUser->first_name.' '.$currentUser->last_name;
    $html .= '<br/><label for="lab_request_type_request">'.esc_html__('Type of request', 'lab').'</label>';
    //$html .= lab_html_select_str("lab_request_type_request", "lab_request_type_request", "", "lab_admin_get_params_request_type", null, null, null, null, null);
    $html .= lab_html_select_str("lab_request_type_request", "lab_request_type_request", "", "lab_admin_get_params_request_type", null, null, null, null, null);
    $html .= '<br/>';
    $html .= '<label for="lab_request_title">'.esc_html__('Request title', 'lab').'</label>';
    $html .= '<input id="lab_request_title" type="text" size="50"></input><br/>';
    $html .= '<label for="lab_request_text">'.esc_html__('Request', 'lab').'</label>';
    $html .= '<textarea id="lab_request_text"></textarea><br/><button type="button" class="btn btn-success" id="lab_request_send">'.esc_html__('Send', 'lab').'</button>';
    return $html;
}
?>