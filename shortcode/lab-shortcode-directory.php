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
        'group' => get_option('lab-directory'),
        'display-group' => get_option('display-group'),
        'all-group' => get_option('all-group'),
        'include-left-user' => get_option('include-left-user'),
        'only-left-user' => get_option('only-left-user'),
        'debug' => get_option('debug'),
        'function' => get_option('function'),
        ),
        $param, 
        "lab-directory"
    );

    $includeLeftUserParam  = $param['include-left-user'];
    $onlyLeftUserParam     = $param['only-left-user'];
    $displayAllgroup       = $param['all-group'];
    $group                 = $param['group'];
    $displayGroupParam     = $param['display-group'];
    $debugParam            = $param['debug'];
    $functionParam         = $param['function'];
    $displayGroup = false;
    $displayLeftUser = false;
    $displayOnlyLeftUser = false;
    $debug               = false;
    $byFunction          = false;

    $joinAsLeft  = "";
    $whereAsLeft = "";
    $joinGroup   = "";
    $whereGroup  = "";
    $fieldsFunctionUser = "";
    $joinFunctionUser  = "";
    $whereFunctionUser = "";

    // if the searchedGroup is passed as an URL parameter
    $groupAsParameter = isset($displayGroupParam) && !empty($displayGroupParam) && $displayGroupParam=="true";
    // if searchedGroup is fixed By shortcode option
    $groupAsSCOption = isset($displayGroupParam) && !empty($group);
    // if $displayAllgroup is set to true, don't display alphabet
    $displayAllgroup  = isset($displayAllgroup) && !empty($displayAllgroup) && $displayAllgroup=="true";
    // if $displayAllgroup is set to true, display user left, otherwise display only user present
    $displayLeftUser  = isset($includeLeftUserParam) && !empty($includeLeftUserParam) && $includeLeftUserParam=="true";

    $displayOnlyLeftUser = isset($onlyLeftUserParam) && !empty($onlyLeftUserParam) && $onlyLeftUserParam=="true";
    $debug = isset($debugParam) && !empty($debugParam) && $debugParam=="true";
    $byFunction = isset($functionParam) && !empty($functionParam);
    $functions = "";

    global $wpdb;
    if ($byFunction)
    {
        $functions = explode(",", $functionParam);
        $fieldsFunctionUser = " ,pm0.value as `function` ";
        $joinFunctionUser   = " JOIN `".$wpdb->prefix."usermeta` AS um9 ON um1.`user_id` = um9.`user_id` LEFT JOIN `".$wpdb->prefix."lab_params` AS pm0 ON pm0.id=um9.meta_value";
        $whereFunctionUser  = " AND um9.`meta_key`='lab_user_function' AND (";
        foreach($functions as $fct)
        {
            $params = AdminParams::get_param_by_slug($fct);
            if (count($param) > 0)
            {
                foreach($params as $param)
                {
                    $whereFunctionUser .= "um9.`meta_value` = '".$param->id."' OR ";
                }
            }
        }
        $whereFunctionUser = substr($whereFunctionUser, 0, strlen($whereFunctionUser) - 3);
        $whereFunctionUser .= ") ";
    }

    if (!$displayAllgroup && !$groupAsSCOption) {
        if (isset( $_GET["group"] ) && !empty( $_GET["group"] ) ) {
            $group = $_GET["group"];
            $groupAsParameter = true;
        }
    }

    $directoryStr = "";
    if ($debug)
    {
        //$directoryStr .= "**** LETTER :". $_GET["letter"]."<br>";
        $directoryStr .= "**** onlyLeftUserParam :". $onlyLeftUserParam."<br>";
        $directoryStr .= "**** displayOnlyLeftUser :". $displayOnlyLeftUser."<br>";
        $directoryStr .= "**** includeLeftUserParam :". $includeLeftUserParam."<br>";
        $directoryStr .= "**** displayLeftUser :". $displayLeftUser."<br>";
    }
    

    /*** FILTER FOR SHORTCODE PARAMETERS  ***/
    if(!$displayLeftUser && !$displayOnlyLeftUser) {
        //$displayLeftUserValue = ($displayLeftUser === 'true');
        $joinDisplayLeftUser = " JOIN `".$wpdb->prefix."usermeta` AS um6 ON um1.`user_id` = um6.`user_id` ";
        $whereDisplayLeftUser = " AND um6.`meta_key`='lab_user_left' "."AND um6.`meta_value` IS  NULL";
    }
    else if ($displayOnlyLeftUser) {
        $joinDisplayLeftUser = " JOIN `".$wpdb->prefix."usermeta` AS um6 ON um1.`user_id` = um6.`user_id` ";
        $whereDisplayLeftUser = " AND um6.`meta_key`='lab_user_left' "."AND um6.`meta_value` IS NOT NULL";
    }

    if(!empty($group)) {
        $joinGroup = " JOIN `".$wpdb->prefix."lab_users_groups`AS ug6 ON um1.`user_id` = ug6.`user_id` JOIN `".$wpdb->prefix."lab_groups` AS g7 ON ug6.`group_id` = g7.`id` ";
        $whereGroup = " AND g7.`acronym`  = '" . $group . "'";
    }

    if (isset($displayGroupParam) && !empty($displayGroupParam) && $displayGroup == "true") {
        $displayGroup = true;
    }

    $sql = "SELECT um1.`user_id` AS id, um3.`meta_value` AS first_name, um2.`meta_value` AS last_name, 
        u4.`user_email` AS mail, um5.`meta_value` AS phone, um8.`meta_value` AS slug".$fieldsFunctionUser."
        FROM `".$wpdb->prefix."usermeta` AS um1 
        JOIN `".$wpdb->prefix."usermeta` AS um2 ON um1.`user_id` = um2.`user_id` 
        JOIN `".$wpdb->prefix."usermeta` AS um3 ON um1.`user_id` = um3.`user_id` 
        JOIN `".$wpdb->prefix."users` AS u4 ON um1.`user_id` = u4.`ID` 
        JOIN `".$wpdb->prefix."usermeta` AS um5 ON um1.`user_id` = um5.`user_id`
        JOIN `".$wpdb->prefix."usermeta` AS um8 ON um1.`user_id` = um8.`user_id`
        ".$joinDisplayLeftUser.$joinGroup.$joinFunctionUser."
        WHERE   um1.`meta_key`='last_name' 
            AND um2.`meta_key`='last_name' 
            AND um3.`meta_key`='first_name' 
            AND um8.`meta_key`='lab_user_slug' 
            AND um5.`meta_key`='lab_user_phone'".$whereDisplayLeftUser.$whereGroup.$whereFunctionUser;

    if (!$displayAllgroup) {
        $currentLetter = $_GET["letter"];
        if (!isset($currentLetter) || empty($currentLetter)) {
            $currentLetter = 'A';
        }
        $sql .= " AND um1.`meta_value`LIKE '$currentLetter%'"; 
    }
    $sql .= " ORDER BY last_name";
    if ($debug)
    {
        $directoryStr .= $sql."<br>";
    }

    $results = $wpdb->get_results($sql);
    $nbResult = $wpdb->num_rows;
    $items = array();
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
    $directoryStr .= "<div class=\"table-responsive\"><table  id=\"lab-table-directory\" class=\"table table-striped  table-hover\"><thead class=\"thead-dark\"><tr><th>".esc_html__("Name", "lab")."</th><th>".esc_html__("User details", "lab")."</th>";
    // No need to display column group if a group is specified as a shortcode option
    if (!$groupAsSCOption) {
        $directoryStr .= "<th>".esc_html__("Group", "lab")."</th>";
    }
    if ($byFunction) {
        $directoryStr .= "<th>".esc_html__("Function", "lab")."</th>";
    }
    $directoryStr .= "</thead><tbody>";
    foreach ($results as $r) {
        $directoryStr .= "<tr  userId='".esc_html($r->slug)."'>";
        $directoryStr .= "<td id='name_col'>".esc_html($r->last_name . " " . $r->first_name)."</td>";
        $directoryStr .= "<td><span class=\"email\">" . esc_html(strrev($r->mail))."</span><br>".correctNumber(esc_html($r->phone))."</td>";
        if (!$groupAsSCOption) {
            $directoryStr .= "<td>" . formatGroupsName($r->id) . "</td>";
        }
        if ($byFunction) {
            $directoryStr .= "<td>" . $r->function . "</td>";
        }
        $directoryStr .= "</tr>";
    }
    $directoryStr .= "</tbody></table></div>";
    return $directoryStr;
}
