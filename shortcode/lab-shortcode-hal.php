<?php
/*
 * File Name: lab-shortcode-directory.php
 * Description: shortcode pour générer un annuaire
 * Authors: Astrid BEYER, Lucas URGENTI
 * Version: 1.0
*/

/*** 
 * Shortcode use : [lab-hal]
     as-left="yes" OR as-left="no"
     group="AA" or whatever group's acronym
***/ 

function lab_hal($param) {
    $param = shortcode_atts(array(
        'id' => get_option('lab-hal'),
        'um' => get_option('lab-hal'),
        ),
        $param, 
        "lab-hal"
    );
    global $wp;
    $userId  = $param['id'];
    $um  = $param['um'];
    $useUltimaterMemberPlugin = false;
    $userId = null;

    if (isset($um) && !empty($um)) {
        $useUltimaterMemberPlugin = true;
    }
    $html  = "<h1>".__('Publications HAL','lab')."</h1>";
    if ($useUltimaterMemberPlugin) {
        // if begins with user/ 
        $userPattern = "user/";
        if (beginWith($wp->request, $userPattern)) {
            $umUser = substr ($wp->request, strlen($userPattern));
            global $wpdb;
            $sql = "SELECT user_id FROM `".$wpdb->prefix."usermeta` WHERE meta_key='um_user_profile_url_slug_name' AND meta_value='".$umUser."'";
            $results = $wpdb->get_results($sql);
            if (count($results) == 1) {
                $userId = $results[0]->user_id;
            }
        }
    }
    $publications = lab_hal_get_publication($userId);
    $i = 0;
    foreach($publications as $p) {
        $i++;
        $html .= $i." - <a href=\"".$p->url."\">".$p->title."</a><br><br>";
    }
    
    return $html;
}