<?php
/*
 * File Name: lab-shortcode-invitation.php
 * Description: shortcode pour afficher un formulaire de création d'invitation
 * Authors: Ivan IVANOV, Lucas URGENTI, Olivier CHABROL
 * Version: 1.2
 */

function lab_trombinoscope($args) {
    $param = shortcode_atts(array(
        'hostpage' => 0 //0 pour invité, 1 pour invitant/responsable
        ),
        $args, 
        "lab-trombinoscope"
    );
    $users = lab_admin_trombinoscope();

    $html = "<table>";
    foreach($users as $user) {
        $html .= "<tr>";
        $html .= $user->imgUrl;
        $html .= "<br>";
        $html .= $user->firstName." ".$user->lastName;
        $html .= "<td>";
        $html .= "</td></tr>";
    }
    $html .= "</table>";
    return $html;
}

function get_distinct_thematic() {
    global $wpdb;
    $sql = "SELECT DISTINCT thematic_id FROM `".$wpdb->prefix."lab_users_thematic`";
    $res = $wpdb->get_results($sql);
    return $res;
}

function get_user_by_thematics($thematic) {
    global $wpdb;
    $sql = "SELECT lut.user_id,lut.main, um1.meta_value as firstName, um2.meta_value as lastName,p.slug as fonction FROM `".$wpdb->prefix."lab_users_thematic` AS lut
    LEFT JOIN `".$wpdb->prefix."usermeta` AS um1 ON um1.user_id=lut.user_id
    LEFT JOIN `".$wpdb->prefix."usermeta` AS um2 ON um2.user_id=lut.user_id
    LEFT JOIN `".$wpdb->prefix."usermeta` AS um3 ON um3.user_id=lut.user_id
    LEFT JOIN `".$wpdb->prefix."lab_params` AS p ON p.id=um3.meta_value
    WHERE lut.`thematic_id` = ".$thematic."  AND um1.meta_key='first_name' AND um2.meta_key='last_name' AND um3.meta_key='lab_user_function'";
    $res = $wpdb->get_results($sql);
    return $res;
}

/*
SELECT lut.user_id,lut.main, um1.meta_value as firstName, um2.meta_value as lastName,p.slug as fonction FROM `wp_lab_users_thematic` AS lut
 LEFT JOIN `wp_usermeta` AS um1 ON um1.user_id=lut.user_id
 LEFT JOIN `wp_usermeta` AS um2 ON um2.user_id=lut.user_id
 LEFT JOIN `wp_usermeta` AS um3 ON um3.user_id=lut.user_id
 LEFT JOIN `wp_lab_params` AS p ON p.id=um3.meta_value
 WHERE lut.`thematic_id` = 201 AND um1.meta_key='first_name' AND um2.meta_key='last_name' AND um3.meta_key='lab_user_function'
 //*/
function lab_users_by_thematic($args) {
    $param = shortcode_atts(array(
        'hostpage' => 0 //0 pour invité, 1 pour invitant/responsable
        ),
        $args, 
        "lab-trombinoscope"
    );
    $thematics = get_distinct_thematic();
    

    $html = "<div class='lab_thematic_table'>";
    foreach($thematics as $theme) {

        $html .= "<div class='lab_thematic_row'><div class='lab_thematic_row_title'>";
        $param = AdminParams::get_param($theme->thematic_id);
        $html .= esc_html__($param,'lab');
        $html .= "</div></div>";
        $users = get_user_by_thematics($theme->thematic_id);
        foreach($users as $user) {
            $html .= "<div class='lab_thematic_row_user'>";
            $html .= "<div class='lab_thematic_row_user_firstname'>";
            $html .= $user->firstName;
            $html .= "</div><div class='lab_thematic_row_user_lastname'>";
            $html .= $user->lastName;
            $html .= "</div><div class='lab_thematic_row_user_function'>";
            $html .= $user->fonction;
            $html .= "</div><div class='lab_thematic_row_user_main'>";
            $html .= $user->main;
            $html .= "</div>"; // fin lab_thematic_row_user_main
            $html .= "</div>"; // fin lab_thematic_row_user
        }
    }
    $html .= "</div>";
    return $html;
}


function lab_hal_tools($args) {
    $param = shortcode_atts(array(
        'debug' => 0 
        ),
        $args, 
        "lab-hal-tools"
    );
    $html = '<input type="text" id="lab_hal_tools_search"><input type="hidden" id="lab_hal_tools_db">';
    $html.= '<table class="table" id="lab_hal_tools_table"><thead><tr><th>id</th><th>biblio</th><th>Title</th><th>Author</th></tr></thead><tbody id="lab_hal_tools_table_body"></tbody>';
    $html.= '</table>';
    return $html;
}