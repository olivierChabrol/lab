<?php
/*
 * File Name: lab-shortcode-directory.php
 * Description: shortcode pour générer un annuaire
 * Authors: Astrid BEYER, Lucas URGENTI
 * Version: 0.1
*/

function lab_directory($param) {
    $param = shortcode_atts(array(
        'as-left' => false,
        'group' => 0,
        ),
        $param, 
        "lab-directory"
    );

    $asLeft  = $param['as-left'];
    
    if(isset($asLeft)) {
        $asLeft  = $asLeft === 'true'? true: false;
    } else {
        $asLeft = false;
    }
    $directoryStr = "<h1>Annuaire</h1>";

    if($asLeft)
    {
        $sql = "SELECT um1.`user_id`    AS id, um3.`meta_value` AS first_name, um2.`meta_value` AS last_name,
                   um4.`user_email` AS mail, um5.`meta_value` AS phone 
            FROM `wp_usermeta` AS um1 
            JOIN `wp_usermeta` AS um2 ON um1.`user_id` = um2.`user_id` 
            JOIN `wp_usermeta` AS um3 ON um1.`user_id` = um3.`user_id` 
            JOIN `wp_users`    AS um4 ON um1.`user_id` = um4.`ID`
            JOIN `wp_usermeta` AS um5 ON um1.`user_id` = um5.`user_id`
            JOIN `wp_usermeta` AS um6 ON um1.`user_id` = um6.`user_id`
            WHERE um1.`meta_key`='last_name' 
                AND um2.`meta_key`='last_name' 
                AND um3.`meta_key`='first_name'
                AND um5.`meta_key`='lab_user_phone'
                AND um6.`meta_key`='lab_user_left'
                AND um6.`meta_value` IS NULL";
    }
    else 
    {
        $sql = "SELECT um1.`user_id`    AS id, um3.`meta_value` AS first_name, um2.`meta_value` AS last_name,
                   um4.`user_email` AS mail, um5.`meta_value` AS phone
            FROM `wp_usermeta` AS um1 
            JOIN `wp_usermeta` AS um2 ON um1.`user_id` = um2.`user_id` 
            JOIN `wp_usermeta` AS um3 ON um1.`user_id` = um3.`user_id` 
            JOIN `wp_users`    AS um4 ON um1.`user_id` = um4.`ID`
            JOIN `wp_usermeta` AS um5 ON um1.`user_id` = um5.`user_id`
            WHERE um1.`meta_key`='last_name' 
                AND um2.`meta_key`='last_name' 
                AND um3.`meta_key`='first_name'
                AND um5.`meta_key`='lab_user_phone'";
    }

    $currentLetter = $_GET["letter"];
    if (isset($currentLetter) && $currentLetter != "") {
        $sql .= " AND um1.`meta_value`LIKE '$currentLetter%'
                ORDER BY last_name";
    }
    else {
        $sql .= " AND um1.`meta_value`LIKE 'A%'
                ORDER BY last_name";
    } // if there's no letter selected, it's by default on A

    global $wpdb;
    $results = $wpdb->get_results($sql);
    $nbResult = $wpdb->num_rows;
    $items = array();
    $alphachar = array_merge(range('A', 'Z'));
    $url = explode('?', $_SERVER['REQUEST_URI']); // current url (without parameters)
    foreach ($alphachar as $element) {
        $directoryStr .= '<a href="' . $url[0] . '?letter=' . $element . '"><b>' . $element . '</b></a><span style="padding-right:12px;"></span>'; 
    } // letter's url
    $directoryStr .= "<div class=\"alpha-links\" style=\"font-size:15px;\">"; // letters
    $directoryStr .= 
        "<br>
            <div id='user-srch' style='width:350px;'>
                <input type='text' id='dud_user_srch_val' name='dud_user_srch_val' style='' value='' maxlength='50' placeholder='Chercher un nom'/>
                <input type='hidden' id='lab_searched_directory' value='' />
            </div>
        <br>"; // search field
    $directoryStr .= 
        "<style>
            tr:nth-child(even) {
                background-color: #f2f2f2;
            }
        </style>"; // style for table (stripped colors)

    /* Table directory */
    $directoryStr .= "<table>";
    foreach ($results as $r) {
        $directoryStr .= "<tr>";
        $directoryStr .= "<td><a>" . esc_html($r->first_name . " " . $r->last_name) . "</a></td>";
        $directoryStr .= "<td>" . esc_html($r->mail) . "</td>";
        $directoryStr .= "<td>" . esc_html($r->phone) . "</td>";
        $directoryStr .= "</tr>";
    }
    $directoryStr .= "</table>";
    return $directoryStr;
}