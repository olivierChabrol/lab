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
    

    $html = "<table>";
    foreach($thematics as $theme) {

        $html .= "<tr><td colspan=\"3\"><b>";
        $param = AdminParams::get_param($theme->thematic_id);
        $html .= $param;
        $html .= "</b></td></tr>";
        $users = get_user_by_thematics($theme->thematic_id);
        foreach($users as $user) {
            $html .= "<tr>";
            $html .= "<td>";
            $html .= $user->firstName." ".$user->lastName;
            $html .= "</td><td>";
            $html .= $user->fonction;
            $html .= "</td><td>";
            $html .= $user->main;
            $html .= "</td></tr>";
        }
        //*/
    }
    $html .= "</table>";
    return $html;
}