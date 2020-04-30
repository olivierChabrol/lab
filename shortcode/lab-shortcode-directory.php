<?php
/*
 * File Name: lab-shortcode-directory.php
 * Description: shortcode pour générer un annuaire
 * Authors: Astrid BEYER, Lucas URGENTI, Olivier CHABROL
 * Version: 1.0
*/

/*** 
 * Shortcode use : [lab-directory {as-left} {group}]
     as-left="yes" OR as-left="no"
     group="AA" or whatever group's acronym

     add this to CSS

    .labSelectedLetter {
        font-size: x-large;
    }
***/ 

function lab_directory($param) {
    $param = shortcode_atts(array(
        'display-left-user' => get_option('lab-directory'),
        'group' => get_option('lab-directory'),
        'display-group' => get_option('display-group'),
        'all-group' => get_option('all-group'),
        ),
        $param, 
        "lab-directory"
    );

    $displayLeftUser  = $param['display-left-user'];
    $displayAllgroup  = $param['all-group'];
    $group   = $param['group'];
    $displayGroupParam   = $param['display-group'];
    $displayGroup = false;

    $joinAsLeft  = "";
    $whereAsLeft = "";
    $joinGroup   = "";
    $whereGroup  = "";

    // if the searchedGroup is passed as an URL parameter
    $groupAsParameter = isset($displayGroupParam) && !empty($displayGroupParam) && $displayGroupParam=="true";
    // if searchedGroup is fixed By shortcode option
    $groupAsSCOption = isset($displayGroupParam) && !empty($group);
    // if $displayAllgroup is set to true, don't display alphabet
    $displayAllgroup  = isset($displayAllgroup) && !empty($displayAllgroup) && $displayAllgroup=="true";

    global $wpdb;
    if (!$displayAllgroup && !$groupAsSCOption) {
        if (isset( $_GET["group"] ) && !empty( $_GET["group"] ) ) {
            $group = $_GET["group"];
            $groupAsParameter = true;
        }
    }
    

    /*** FILTER FOR SHORTCODE PARAMETERS  ***/
    if(!empty($displayLeftUser)) {
        $displayLeftUserValue = ($displayLeftUser === 'true');
        $joinDisplayLeftUser = " JOIN `".$wpdb->prefix."usermeta` AS um6 ON um1.`user_id` = um6.`user_id` ";
        $whereDisplayLeftUser = " AND um6.`meta_key`='lab_user_left' "."AND um6.`meta_value` IS ".($displayLeftUserValue?"NOT":"")." NULL";
    }
    if(!empty($group)) {
        $joinGroup = " JOIN `".$wpdb->prefix."lab_users_groups`AS ug6 ON um1.`user_id` = ug6.`user_id` JOIN `".$wpdb->prefix."lab_groups` AS g7 ON ug6.`group_id` = g7.`id` ";
        $whereGroup = " AND g7.`acronym`  = '" . $group . "'";
    }

    if (isset($displayGroupParam) && !empty($displayGroupParam) && $displayGroup == "true") {
        $displayGroup = true;
    }

    $sql = "SELECT um1.`user_id` AS id, um3.`meta_value` AS first_name, um2.`meta_value` AS last_name, 
        u4.`user_email` AS mail, um5.`meta_value` AS phone, um8.`meta_value` AS slug 
        FROM `".$wpdb->prefix."usermeta` AS um1 
        JOIN `".$wpdb->prefix."usermeta` AS um2 ON um1.`user_id` = um2.`user_id` 
        JOIN `".$wpdb->prefix."usermeta` AS um3 ON um1.`user_id` = um3.`user_id` 
        JOIN `".$wpdb->prefix."users` AS u4 ON um1.`user_id` = u4.`ID` 
        JOIN `".$wpdb->prefix."usermeta` AS um5 ON um1.`user_id` = um5.`user_id`
        JOIN `".$wpdb->prefix."usermeta` AS um8 ON um1.`user_id` = um8.`user_id`
        ".$joinDisplayLeftUser.$joinGroup."
        WHERE   um1.`meta_key`='last_name' 
            AND um2.`meta_key`='last_name' 
            AND um3.`meta_key`='first_name' 
            AND um8.`meta_key`='lab_user_slug' 
            AND um5.`meta_key`='lab_user_phone'".$whereDisplayLeftUser.$whereGroup;

    if (!$displayAllgroup) {
        $currentLetter = $_GET["letter"];
        if (!isset($currentLetter) || empty($currentLetter)) {
            $currentLetter = 'A';
        }
        $sql .= " AND um1.`meta_value`LIKE '$currentLetter%'"; 
    }
    $sql .= "ORDER BY last_name";

    $results = $wpdb->get_results($sql);
    $nbResult = $wpdb->num_rows;
    $items = array();
    $directoryStr = "";
    //$directoryStr .= "**** LETTER :". $_GET["letter"]."<br>";
    //$directoryStr .= "**** GROUP :". $group."<br>";
    //$directoryStr .= $sql;
    if (!$displayAllgroup) 
    {
        $alphachar = array_merge(range('A', 'Z'));
        $url = explode('?', $_SERVER['REQUEST_URI']); // current url (without parameters)
        $directoryStr .= "<input type=\"hidden\" id=\"letterSearch\" value=\"".$currentLetter."\">";
        $directoryStr .= "<input type=\"hidden\" id=\"groupSearch\" value=\"".$group."\">";
        $directoryStr .= "<div class=\"alpha-links\" style=\"font-size:15px;\">";
        foreach ($alphachar as $element) {
            $letterClass = ($element == $currentLetter?"class=\"labSelectedLetter\"":"");
            $forwardUrl  = $url[0]."?letter=".$element;
            if ($groupAsParameter) {
                $forwardUrl .= "&group=".$group;
            }

            $directoryStr .= '<a href="' .$forwardUrl. '" '.$letterClass.'><b>' . $element . '</b></a>&nbsp;&nbsp;'; 
        } // letter's url
        $directoryStr .= "</div>"; // letters
        $directoryStr .= 
            "<br>
                <div id='user-srch' style='width:750px;' class=\"actions\">
                    <input type='text' id='lab_directory_user_name' name='dud_user_srch_val' style='' value='' maxlength='50' placeholder=\"" . __('Chercher un nom', 'lab') . "\"/>
                    <input type='hidden' id='lab_directory_user_id' value='' />
                ";
        if (!$groupAsSCOption && $groupAsParameter) {
            $directoryStr .= __('Show only group', 'lab')." : ";
            $directoryStr .= lab_html_select_str("lab-directory-group-id", "lab-directory-group-id", "", lab_admin_group_select_group, "acronym, group_name", array("value"=>0,"label"=>"None"), $group, array("id"=>"acronym", "value"=>"value"));
        }
        $directoryStr .= "</div>
            <br>"; // search field
    }
    $directoryStr .= 
        "<style>
            .email{
                unicode-bidi: bidi-override;
                direction: rtl;
            }
            .labSelectedLetter {
                font-size: x-large;
            }
        </style>"; // style for table (stripped colors)

    /* Table directory */
    $directoryStr .= "<div class=\"table-responsive\"><table  id=\"lab-table-directory\" class=\"table table-striped\"><thead class=\"thead-dark\"><tr><th>".esc_html__("Name", "lab")."</th><th>".esc_html__("User details", "lab")."</th>";
    // No need to display column group if a group is specified as a shortcode option
    if (!$groupAsSCOption) {
        $directoryStr .= "<th>".esc_html__("Group", "lab")."</th>";
    }
    $directoryStr .= "</thead><tbody>";
    foreach ($results as $r) {
        $directoryStr .= "<tr  userId='".esc_html($r->slug)."'>";
        $directoryStr .= "<td id='name_col'>".esc_html($r->last_name . " " . $r->first_name)."</td>";
        $directoryStr .= "<td><span class=\"email\">" . esc_html(strrev($r->mail))."</span><br>".correctNumber(esc_html($r->phone))."</td>";
        if (!$groupAsSCOption) {
            $directoryStr .= "<td>" . formatGroupsName($r->id) . "</td>";
        }
        $directoryStr .= "</tr>";
    }
    $directoryStr .= "</tbody></table></div>";
    return $directoryStr;
}
