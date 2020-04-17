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
        'as-left' => get_option('lab-directory'),
        'group' => get_option('lab-directory')
        ),
        $param, 
        "lab-directory"
    );

    $asLeft  = $param['as-left'];
    $group   = $param['group'];
    $directoryStr = "<h1>Annuaire</h1>"; // title

/*** FILTER FOR SHORTCODE PARAMETERS  ***/
    if(!empty($asLeft) && !empty($group))
    {
        $sql = "SELECT DISTINCT um1.`user_id` AS id, um3.`meta_value` AS first_name, um2.`meta_value` AS last_name, 
        u4.`user_email` AS mail, um5.`meta_value` AS phone 
        FROM `wp_usermeta` AS um1 
        JOIN `wp_usermeta` AS um2 ON um1.`user_id` = um2.`user_id` 
        JOIN `wp_usermeta` AS um3 ON um1.`user_id` = um3.`user_id` 
        JOIN `wp_users` AS u4 ON um1.`user_id` = u4.`ID` 
        JOIN `wp_usermeta` AS um5 ON um1.`user_id` = um5.`user_id`
        JOIN `wp_usermeta` AS um6 ON um1.`user_id` = um6.`user_id` 
        JOIN `wp_lab_users_groups`AS ug6 ON um1.`user_id` = ug6.`user_id` 
        JOIN `wp_lab_groups` AS g7 ON ug6.`group_id` = g7.`id` 
        WHERE   um1.`meta_key`='last_name' 
            AND um2.`meta_key`='last_name' 
            AND um3.`meta_key`='first_name' 
            AND um5.`meta_key`='lab_user_phone'
            AND um6.`meta_key`='lab_user_left' 
            AND g7.`acronym`  = '" . $group . "'";
        if($asLeft == "yes")
        {
            $sql .= "AND um6.`meta_value` IS NOT NULL";
        }
        else if($asLeft == "no")
        {
            $sql .= "AND um6.`meta_value` IS NULL";
        } 
    }
    else if(!empty($asLeft) xor !empty($group))
    {
        if(!empty($asLeft))
        {
            $sql = "SELECT um1.`user_id` AS id, um3.`meta_value` AS first_name, um2.`meta_value` AS last_name,
                                u4.`user_email` AS mail, um5.`meta_value` AS phone 
                        FROM `wp_usermeta` AS um1 
                        JOIN `wp_usermeta` AS um2 ON um1.`user_id` = um2.`user_id` 
                        JOIN `wp_usermeta` AS um3 ON um1.`user_id` = um3.`user_id` 
                        JOIN `wp_users`    AS u4  ON um1.`user_id` = u4.`ID`
                        JOIN `wp_usermeta` AS um5 ON um1.`user_id` = um5.`user_id`
                        JOIN `wp_usermeta` AS um6 ON um1.`user_id` = um6.`user_id`
                        WHERE   um1.`meta_key`='last_name'
                            AND um2.`meta_key`= 'last_name'
                            AND um3.`meta_key`='first_name'
                            AND um5.`meta_key`='lab_user_phone'
                            AND um6.`meta_key`='lab_user_left'";
            if($asLeft == "yes")
            {
                $sql .= "AND um6.`meta_value` IS NOT NULL";
            }
            else if($asLeft == "no")
            {
                $sql .= "AND um6.`meta_value` IS NULL";
            }
        }
        else
        {
            $sql = "SELECT DISTINCT um1.`user_id` AS id, um3.`meta_value` AS first_name, um2.`meta_value` AS last_name, 
                    u4.`user_email` AS mail, um5.`meta_value` AS phone 
                    FROM `wp_usermeta` AS um1 
                    JOIN `wp_usermeta` AS um2 ON um1.`user_id` = um2.`user_id` 
                    JOIN `wp_usermeta` AS um3 ON um1.`user_id` = um3.`user_id` 
                    JOIN `wp_users` AS u4 ON um1.`user_id` = u4.`ID` 
                    JOIN `wp_usermeta` AS um5 ON um1.`user_id` = um5.`user_id` 
                    JOIN `wp_lab_users_groups`AS ug6 ON um1.`user_id` = ug6.`user_id` 
                    JOIN `wp_lab_groups` AS g7 ON ug6.`group_id` = g7.`id` 
                    WHERE   um1.`meta_key`='last_name' 
                        AND um2.`meta_key`='last_name' 
                        AND um3.`meta_key`='first_name' 
                        AND um5.`meta_key`='lab_user_phone' 
                        AND g7.`acronym`  ='" . $group . "'";
        }
    }
    else
    {
        $sql = "SELECT um1.`user_id` AS id, um3.`meta_value` AS first_name, um2.`meta_value` AS last_name,
                   u4.`user_email` AS mail, um5.`meta_value` AS phone
                FROM `wp_usermeta` AS um1 
                JOIN `wp_usermeta` AS um2 ON um1.`user_id` = um2.`user_id` 
                JOIN `wp_usermeta` AS um3 ON um1.`user_id` = um3.`user_id` 
                JOIN `wp_users`    AS u4 ON um1.`user_id` = u4.`ID`
                JOIN `wp_usermeta` AS um5 ON um1.`user_id` = um5.`user_id`
                WHERE um1.`meta_key`='last_name' 
                    AND um2.`meta_key`='last_name' 
                    AND um3.`meta_key`='first_name'
                    AND um5.`meta_key`='lab_user_phone'";
    }

    $currentLetter = $_GET["letter"];
    if (!isset($currentLetter) || empty($currentLetter)) {
        $currentLetter = 'A';
    }
    $sql .= " AND um1.`meta_value`LIKE '$currentLetter%'
                ORDER BY last_name";

    global $wpdb;
    $results = $wpdb->get_results($sql);
    $nbResult = $wpdb->num_rows;
    $items = array();
    $directoryStr = "<h1>Annuaire</h1>"; // title
    $alphachar = array_merge(range('A', 'Z'));
    $url = explode('?', $_SERVER['REQUEST_URI']); // current url (without parameters)
    foreach ($alphachar as $element) {
        $directoryStr .= '<a href="' . $url[0] . '?letter=' . $element . '"><b>' . $element . '</b></a><span style="padding-right:12px;"></span>'; 
    } // letter's url
    $directoryStr .= "<div class=\"alpha-links\" style=\"font-size:15px;\">"; // letters
    $directoryStr .= 
        "<br>
            <div id='user-srch' style='width:350px;'>
                <input type='text' id='lab_directory_user_name' name='dud_user_srch_val' style='' value='' maxlength='50' placeholder='Chercher un nom'/>
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

    /* Table directory */
    $directoryStr .= "<table>";
    foreach ($results as $r) {
        $directoryStr .= "<tr class='directory_row' userId=".$r->first_name.".".$r->last_name.">";
        $directoryStr .= "<td id='name_col'>" . 
                        esc_html($r->first_name . " " . $r->last_name) . 
                        "</td>";
        $directoryStr .= "<td class='email'>" . esc_html(strrev($r->mail)) . "</td>";
        $directoryStr .= "<td>" . esc_html($r->phone) . "</td>";
        $directoryStr .= "</tr>";
    }
    $directoryStr .= "</table>";
    return $directoryStr;
}