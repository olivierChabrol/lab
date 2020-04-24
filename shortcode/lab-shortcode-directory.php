<?php
/*
 * File Name: lab-shortcode-directory.php
 * Description: shortcode pour générer un annuaire
 * Authors: Astrid BEYER, Lucas URGENTI
 * Version: 1.0
*/

/*** 
 * Shortcode use : [lab-directory {as-left} {group}]
     as-left="yes" OR as-left="no"
     group="AA" or whatever group's acronym
***/ 

function lab_directory($param) {
    $param = shortcode_atts(array(
        'display-left-user' => get_option('lab-directory'),
        'group' => get_option('lab-directory'),
        'display-group' => get_option('display-group'),
        ),
        $param, 
        "lab-directory"
    );

    $displayLeftUser  = $param['display-left-user'];
    $group   = $param['group'];
    $displayGroupParam   = $param['display-group'];
    $displayGroup = false;

    $joinAsLeft  = "";
    $whereAsLeft = "";
    $joinGroup   = "";
    $whereGroup  = "";

    /*** FILTER FOR SHORTCODE PARAMETERS  ***/
    if(!empty($displayLeftUser)) {
        $displayLeftUserValue = ($displayLeftUser === 'true');
        $joinDisplayLeftUser = " JOIN `wp_usermeta` AS um6 ON um1.`user_id` = um6.`user_id` ";
        $whereDisplayLeftUser = " AND um6.`meta_key`='lab_user_left' "."AND um6.`meta_value` IS ".($displayLeftUserValue?"NOT":"")." NULL";
    }
    if(!empty($group)) {
        $joinGroup = " JOIN `wp_lab_users_groups`AS ug6 ON um1.`user_id` = ug6.`user_id` JOIN `wp_lab_groups` AS g7 ON ug6.`group_id` = g7.`id` ";
        $whereGroup = " AND g7.`acronym`  = '" . $group . "'";
    }

    if (isset($displayGroupParam) && !empty($displayGroupParam) && $displayGroup == "true") {
        $displayGroup = true;
    }

    global $wpdb;
    $sql = "SELECT um1.`user_id` AS id, um3.`meta_value` AS first_name, um2.`meta_value` AS last_name, 
        u4.`user_email` AS mail, um5.`meta_value` AS phone 
        FROM `".$wpdb->prefix."usermeta` AS um1 
        JOIN `".$wpdb->prefix."usermeta` AS um2 ON um1.`user_id` = um2.`user_id` 
        JOIN `".$wpdb->prefix."usermeta` AS um3 ON um1.`user_id` = um3.`user_id` 
        JOIN `".$wpdb->prefix."users` AS u4 ON um1.`user_id` = u4.`ID` 
        JOIN `".$wpdb->prefix."usermeta` AS um5 ON um1.`user_id` = um5.`user_id`
        ".$joinDisplayLeftUser.$joinGroup."
        WHERE   um1.`meta_key`='last_name' 
            AND um2.`meta_key`='last_name' 
            AND um3.`meta_key`='first_name' 
            AND um5.`meta_key`='lab_user_phone'".$whereDisplayLeftUser.$whereGroup;

    $currentLetter = $_GET["letter"];
    if (!isset($currentLetter) || empty($currentLetter)) {
        $currentLetter = 'A';
    }
    $sql .= " AND um1.`meta_value`LIKE '$currentLetter%'
                ORDER BY last_name";

    $results = $wpdb->get_results($sql);
    $nbResult = $wpdb->num_rows;
    $items = array();
    $directoryStr = "";//"<h1>".__("Annuaire","lab")."</h1>"; // title
    //$directoryStr .= $sql;
    $alphachar = array_merge(range('A', 'Z'));
    $url = explode('?', $_SERVER['REQUEST_URI']); // current url (without parameters)
    $directoryStr .= "<div class=\"alpha-links\" style=\"font-size:15px;\">";
    foreach ($alphachar as $element) {
        $directoryStr .= '<a href="' . $url[0] . '?letter=' . $element . '"><b>' . $element . '</b></a>'; 
    } // letter's url
    $directoryStr .= "</div>"; // letters
    $directoryStr .= 
        "<br>
            <div id='user-srch' style='width:350px;'>
                <input type='text' id='lab_directory_user_name' name='dud_user_srch_val' style='' value='' maxlength='50' placeholder=\"" . __('Chercher un nom', 'lab') . "\"/>
                <input type='hidden' id='lab_directory_user_id' value='' />
            </div>
        <br>"; // search field
    $directoryStr .= 
        "<style>
            .directory_row{
                cursor: pointer;
            }
            .directory_row:hover {
                background-color: #DADBDD;
            }
            tr:nth-child(even) {
                background-color: #f2f2f2;
            }
            .email{
                unicode-bidi: bidi-override;
                direction: rtl;
            }
        </style>"; // style for table (stripped colors)

    /* Display numbers correctly */
    function correctNumber($currentNumber) { // currentNumber = esc_html($r->phone)
        $currentNumber = str_replace(" ", "", $currentNumber);
        $currentNumber = str_replace(".", "", $currentNumber);
        $currentNumber = chunk_split($currentNumber, 2, ' ');
        return $currentNumber;
    }

    /* Table directory */
    $directoryStr .= "<table class=\"directory\"><thead><tr><td>Name</td><td>mail & phone</td><td>groupe</td></tr></thead><tbody>";
    foreach ($results as $r) {
        $directoryStr .= "<tr class='directory_row' userId='".esc_html($r->first_name).".".esc_html($r->last_name)."'>";
        $directoryStr .= "<td id='name_col'>".esc_html($r->last_name . " " . $r->first_name)."</td>";
        $directoryStr .= "<td><span class=\"email\">" . esc_html(strrev($r->mail))."</span><br>".correctNumber(esc_html($r->phone))."</td>";
        //$directoryStr .= "<td>" . correctNumber(esc_html($r->phone)) . "</td>";
        $directoryStr .= "<td>" . formatGroupsName($r->id) . "</td>";
        $directoryStr .= "</tr>";
    }
    $directoryStr .= "</tbody></table>";
    return $directoryStr;
}
