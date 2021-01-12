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
        'group' => get_option('lab-hal'),
        'year' => get_option('lab-hal'),
        ),
        $param, 
        "lab-hal"
    );
    global $wp;
    $userId = $param['id'];
    $um     = $param['um'];
    $group  = $param['group'];
    $year   = $param['year'];
    $useUltimaterMemberPlugin = false;
    $userId = null;

    if (!isset($year) || empty($year)) {
        $year = null;
    }

    if (isset($um) && !empty($um)) {
        $useUltimaterMemberPlugin = true;
    }

    $publications = null;
    if (isset($group) && !empty($group)) {
        if (strpos($group,",") === false) {
            $publications = lab_hal_getPublication_by_group($group, $year);
        } else {
            $publications = lab_hal_getPublication_by_group(explode (",", $group), $year);
        }
    }
    else{
        $html  = "<h1>".__('Publications HAL','lab')."</h1>";
        if ($useUltimaterMemberPlugin) {
            // if begins with user/ 
            $userPattern = "user/";
            if (beginWith($wp->request, $userPattern)) {
                $umUser = substr ($wp->request, strlen($userPattern));
                global $wpdb;
                $sql = "SELECT user_id FROM `".$wpdb->prefix."usermeta` WHERE meta_key='lab_user_slug' AND meta_value='".$umUser."'";
                $results = $wpdb->get_results($sql);
                if (count($results) == 1) {
                    $userId = $results[0]->user_id;
                }
            }
        }
        $publications = lab_hal_get_publication($userId);
        //$html .= "USER ID : ".$userId."<br>";
        //$html .= count($publications)." <br>";
    }
    if ($publications != null) {
        foreach($publications as $p) {
            $html .= date("Y/m", strtotime($p->producedDate_tdate))." <i>".($p->journalTitle_s !=null?$p->journalTitle_s :"")."</i> - <a href=\"".$p->url."\"  target=\"".$p->docid."\">".$p->title."</a><br>";
            $html .= "<br>";
        }
    }
    
    return $html;
}