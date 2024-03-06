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

/**
 * Generate URL for directory shortcode
 *
 * @param [type] $letter
 * @param [type] $group
 * @param [type] $thematic
 * @return URL
 */
function directoryUrl($baseUrl, $letter, $group, $thematic)
{
    $url = $baseUrl;
    if ($letter != "") {
        $url .= "?letter=".$letter;
    }
    if ($group != "0") {
      if ($letter != "") {
        $url .= "&";
      } else {
        $url .= "?";
      }
      $url .= "group=".$group;
    }
    if ($thematic != "0") {
      if ($letter != "" || $group != "") {
        $url .= "&";
      } else {
        $url .= "?";
      }
      $url .= "thematic=".$thematic;
    }
    return $url;
}

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
    $joinThematicUser  = "";
    $whereFunctionUser = "";
    $whereThematicUser = "";
    $thematic          = "0";

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

    if (isset( $_GET["thematic"] ) && !empty( $_GET["thematic"] ) ) 
    {
        $thematic = $_GET["thematic"];
        $joinThematicUser  = " INNER JOIN `wp_lab_users_thematic` AS ut ON um1.`user_id` = ut.`user_id` ";
        $whereThematicUser = " AND ut.`thematic_id`=" . $_GET["thematic"];
    }

    global $wpdb;
    if ($byFunction)
    {
        $functions = array();
        if (strpos($functionParam, ",") > 0)
        {
            $functions = explode(",", $functionParam);
        }
        else
        {
            $functions[] = $functionParam;
        }
        //$fieldsFunctionUser = " ,pm0.value as `function`, pm0.slug as `function_slug` ";
        //$joinFunctionUser   = " JOIN `".$wpdb->prefix."usermeta` AS um9 ON um1.`user_id` = um9.`user_id` LEFT JOIN `".$wpdb->prefix."lab_params` AS pm0 ON pm0.id=um9.meta_value";
        $whereFunctionUser  = " AND (";
        $debugFct = "";
        foreach($functions as $fct)
        {
            $debugFct .= $fct."<br>";
            $params = AdminParams::get_param_by_slug($fct);
            if ($params != NULL)
            {
                if (is_countable($params) && count($params) > 1)
                {
                    $debugFct .= "Nb Params : ".count($params)."<br>";
                    foreach($params as $param)
                    {
                        $debugFct .= "param : ".$param->id." ".$param->value." ".$param->slug."<br>";
                        $whereFunctionUser .= "um9.`meta_value` = '".$param->id."' OR ";
                    }
                }
                else{
                    $debugFct .= "Nb Params : only one <br>";
                    $debugFct .= "param : ".$params->id." ".$params->value." ".$params->slug."<br>";
                    $whereFunctionUser .= "um9.`meta_value` = '".$params->id."' OR ";
                }
            }
            else {
                $debugFct .= "Nb Params : 0<br>";
            }
            $debugFct .= "<br>";
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

    $directoryStr = "<div class=\"lab_directory\">";
    if ($debug)
    {
        $directoryStr .= "<div class=\"lab_directory_debug\">";
        //$directoryStr .= "**** LETTER :". $_GET["letter"]."<br>";
        $directoryStr .= "<div class=\"lab_directory_debugFct\" : '". $debugFct."'</div>";
        $directoryStr .= "<div class=\"lab_directory_onlyLeftUserParam\" : '". $onlyLeftUserParam."'</div>";
        $directoryStr .= "<div class=\"lab_directory_displayOnlyLeftUser\" : '". $displayOnlyLeftUser."'</div>";
        $directoryStr .= "<div class=\"lab_directory_displayLeftUser\" : '". $displayLeftUser."'</div>";
        $directoryStr .= "<div class=\"lab_directory_function_param\" : '". $functionParam."'</div>";
        $directoryStr .= "<div class=\"lab_directory_thematic_param\" : '". $whereThematicUser."'</div>";
        $directoryStr .= "<div class=\"lab_directory_thematic\" : '". $thematic."'</div>";
        $directoryStr .= "<div class=\"lab_directory_dump\">";
        var_dump($functions);
        $directoryStr .= "</div>"; // fin lab_directory_dump
        $directoryStr .= "</div>"; // fin lab_directory_debug
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

    $sql = "SELECT um1.`user_id` AS id, um3.`meta_value` AS first_name, um1.`meta_value` AS last_name, 
        u4.`user_email` AS mail, um5.`meta_value` AS phone, um8.`meta_value` AS slug
         ,pm0.value as `function`, pm0.slug as `function_slug` 
        FROM `".$wpdb->prefix."usermeta` AS um1  
        JOIN `".$wpdb->prefix."usermeta` AS um3 ON um1.`user_id` = um3.`user_id` 
        JOIN `".$wpdb->prefix."users` AS u4 ON um1.`user_id` = u4.`ID` 
        JOIN `".$wpdb->prefix."usermeta` AS um5 ON um1.`user_id` = um5.`user_id`
        JOIN `".$wpdb->prefix."usermeta` AS um8 ON um1.`user_id` = um8.`user_id`
        JOIN `".$wpdb->prefix."usermeta` AS um9 ON um1.`user_id` = um9.`user_id` 
        LEFT JOIN `".$wpdb->prefix."lab_params` AS pm0 ON pm0.id=um9.meta_value
        ".$joinDisplayLeftUser.$joinGroup.$joinFunctionUser.$joinThematicUser."
        WHERE   um1.`meta_key`='last_name' 
            AND um3.`meta_key`='first_name' 
            AND um8.`meta_key`='lab_user_slug' 
            AND um5.`meta_key`='lab_user_phone'
            AND um9.`meta_key`='lab_user_function'".$whereDisplayLeftUser.$whereGroup.$whereFunctionUser.$whereThematicUser;

    if (!$displayAllgroup) {
        if (isset($_GET["letter"])) {
            $currentLetter = $_GET["letter"];
            if (empty($currentLetter)) {
                $currentLetter = 'A';
            }
        }
        else {
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
    $directoryStr .= "<div class='lab_directory_filters'>";
    $directoryStr .= "<div class='lab_directory_filters_alphabet'>";
    if (!$displayAllgroup) 
    {
        $alphachar = array_merge(range('A', 'Z'));
        $url = explode('?', $_SERVER['REQUEST_URI']); // current url (without parameters)
        $directoryStr .= "<input type=\"hidden\" id=\"letterSearch\" value=\"".$currentLetter."\">";
        $directoryStr .= "<input type=\"hidden\" id=\"groupSearch\" value=\"".$group."\">";
        $directoryStr .= "<div class=\"alpha-links\" style=\"font-size:15px;\">";
        foreach ($alphachar as $element) {
            $letterClass = ($element == $currentLetter?"class=\"labSelectedLetter\"":"");
            $forwardUrl = directoryUrl($url[0], $element, $group, $thematic);
            $directoryStr .= '<a href="' .$forwardUrl. '" '.$letterClass.'><b>' . $element . '</b></a>&nbsp;&nbsp;'; 
        } // letter's url
        
        $letterClass = ('%' == $currentLetter?"class=\"labSelectedLetter\"":"");
        $forwardUrl = directoryUrl($url[0], '%', $group, $thematic);
        $directoryStr .= '<a href="' .$forwardUrl. '" '.$letterClass.'><b>'.__('All', 'lab').'</b></a>&nbsp;&nbsp;'; 
        $directoryStr .= "</div>"; // letters
	$directoryStr .= "</div>"; // fin lab_directory_filters_alphabet

	$filterSearchContent = "<div class='lab_directory_filters_search'>";
        if (!$displayOnlyLeftUser) {
            $filterSearchContent .= "<br><a href=\"/linstitut/annuaire/personnels-partis/\">".__('People who have left', 'lab')."</a>";
        }
        else{
            $filterSearchContent .= "<br><a href=\"/linstitut/annuaire/\">".__('People present', 'lab')."</a>";
        }
        $filterSearchContent .= 
            "<br>
                <div id='user-srch' class=\"actions\">
                    <input type='text' id='lab_directory_user_name' name='dud_user_srch_val' style='' value='' maxlength='50' placeholder=\"" . __('Search a name', 'lab') . "\"/>
                    <input type='hidden' id='lab_directory_user_id' value='' />
                ";
        if (!$groupAsSCOption && $groupAsParameter) {
		//$filterSearchContent .= __('Show only group', 'lab')." : ";

		$filterSearchContent .= "<div class='lab_directory_group_filters_search_label'>Filtrer par groupe :</div>";
		$filterSearchContent .= "<div class='lab_directory_group_filters_search'>";
            $filterSearchContent .= lab_html_select_str("lab-directory-group-id", "lab-directory-group-id", "", "lab_admin_group_select_group", "acronym, group_name", array("value"=>0,"label"=>"None"), $group, array("id"=>"acronym", "value"=>"value"));
        }
        $filterSearchContent .= "</div>";
	$filterSearchContent .= "<div class='lab_directory_thematic_filters_search_label'>Filtrer par thématique :</div>";
	$filterSearchContent .= "<div class='lab_directory_thematic_filters_search'>";
	$filterSearchContent .= lab_html_select_str("lab-directory-thematic", "lab-directory-thematic", "", "lab_admin_thematic_load_all", null, array("value"=>0,"label"=>"--- Select thematic ---"),$thematic, null, null, 60);
	$filterSearchContent .= "</div>"; // end lab_directory_thematic_filters_search
        $filterSearchContent .= "</div><br>"; // search field
        $filterSearchContent .= "</div>"; // fin lab_directory_filters_search
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
    //return lab_directory_old_display($directoryStr, $results, $groupAsSCOption);
    return lab_directory_new_display($directoryStr, $results, $groupAsSCOption, $filterSearchContent);
}

function lab_directory_new_display($directoryStr, $results, $groupAsSCOption, $filterSearchContent) {

    /* Table directory */
    $labDirectoryContent = "<div  id=\"lab-table-directory\" class=\"lab_directory_table\">";
    $acronymList = array();
    $groupList = array();
    foreach ($results as $r) {
        $labDirectoryContent .= "<div class='lab_directory_table_row lab_clickable_user' user_id='".esc_html($r->slug)."'>";
        $labDirectoryContent .= "<div class='lab_directory_table_row_name'>".esc_html(strtoupper($r->last_name) . " " . $r->first_name)."</div>";
        $labDirectoryContent .= "<div class='lab_directory_table_row_function'>" . $r->function . "</div>";
        $labDirectoryContent .= "<div class='lab_directory_table_row_phone'>" . correctNumber(esc_html($r->phone)) . "</div>";
        $labDirectoryContent .= "<div class='lab_directory_table_row_email'>" . esc_html(strrev($r->mail))."</div>";
        if (!$groupAsSCOption) {
            $labDirectoryContent .= "<div class='lab_directory_table_row_group'>" . formatGroupsName($r->id) . "</div>";
        }
        if (!key_exists(formatGroupsName($r->id), $groupList)) {
            $groupList[formatGroupsName($r->id)] = formatGroupsName($r->id);
        }
        if (!key_exists($r->function_slug, $acronymList)) {
            //$acronymList[$r->function_slug] = $r->function;
            $acronymList[$r->function_slug] = array();
        }
        if (!key_exists($r->function, $acronymList[$r->function_slug])) {
            $acronymList[$r->function_slug][$r->function] = $r->function;
        }

        //fin lab_directory_table_row
        $labDirectoryContent .= "</div>";
    }
    // fin lab_directory_table
    $labDirectoryContent .= "</div>";

    /////////////////////// LEGEND //////////////////////////////////
    $legendContent = "<div class=\"lab_directory_table_legend\">";
    ksort($acronymList);
    $i = 0;
    foreach($acronymList as $k=>$v) {
        $legendContent .= "<div class='lab_directory_table_legend_row'><div class='lab_directory_table_legend_row_acronym'>".$k."</div><div  class='lab_directory_table_legend_row_value'>";
        $size = count($v);
        foreach($v as $fctKey=>$fctVal)
        {
            $legendContent .= $fctKey;
            if ($i + 1 < $size) {
                $legendContent .= "/";
            }
            $i++;
        }
        //fin lab_directory_table_legend_row_value
        $legendContent .= "</div>";
        //fin lab_directory_table_legend_row
        $legendContent .= "</div>";
    }

    $legendContent .= "</div>"; // fin lab_directory_table_legend

    $directoryStr .= $labDirectoryContent;

    $tableGroupContent = "<div class=\"lab_directory_table_group\">";
    foreach($groupList as $k=>$v) {
        $tableGroupContent .= "<div class='lab_directory_table_group_row'>";
        $tableGroupContent .= "<div class='lab_directory_table_group_row_name'>";
        $tableGroupContent .= $k;
        //fin lab_directory_table_group_row_name
        $tableGroupContent .= "</div>";
        //fin lab_directory_table_group_row
        $tableGroupContent .= "</div>";

    }
    $tableGroupContent .= "</div>"; // lab_directory_table_group


    $directoryStr .= "\n<div class=\"side_search\">\n";
    $directoryStr .= "\t".$filterSearchContent."\n";
    //$directoryStr .= "\t".$tableGroupContent."\n";
    //$directoryStr .= "\t".$legendContent."\n";
    $directoryStr .= "\n</div>\n";

    // fin lab_directory
    $directoryStr .= "</div>";
    //var_dump($acronymList);
    return $directoryStr;
}

function lab_directory_old_display($directoryStr, $results, $groupAsSCOption) {

    /* Table directory */
    $directoryStr .= "<div class=\"table-responsive\"><table  id=\"lab-table-directory\" class=\"table table-striped  table-hover\"><thead class=\"thead-dark\"><tr><th>".esc_html__("Name", "lab")."</th>";

    $directoryStr .= "<th>".esc_html__("Function", "lab")."</th>";
    $directoryStr .= "<th>".esc_html__("User details", "lab")."</th>";
    // No need to display column group if a group is specified as a shortcode option
    if (!$groupAsSCOption) {
        $directoryStr .= "<th>".esc_html__("Group", "lab")."</th>";
    }
    $directoryStr .= "</thead><tbody>";
    $acronymList = array();
    foreach ($results as $r) {
        $directoryStr .= "<tr  userId='".esc_html($r->slug)."'>";
        $directoryStr .= "<td id='name_col'>".esc_html(strtoupper($r->last_name) . " " . $r->first_name)."</td>";
        $directoryStr .= "<td>" . $r->function_slug . "</td>";
        $directoryStr .= "<td><span class=\"email\">" . esc_html(strrev($r->mail))."</span><br>".correctNumber(esc_html($r->phone))."</td>";
        if (!$groupAsSCOption) {
            $directoryStr .= "<td>" . formatGroupsName($r->id) . "</td>";
        }
        if (!key_exists($r->function_slug, $acronymList)) {
            //$acronymList[$r->function_slug] = $r->function;
            $acronymList[$r->function_slug] = array();
        }
        if (!key_exists($r->function, $acronymList[$r->function_slug])) {
            $acronymList[$r->function_slug][$r->function] = $r->function;
        }

        $directoryStr .= "</tr>";
    }
    $directoryStr .= "</tbody></table><p>Legend</p><table class=\"table table-striped  table-hover\"><thead class=\"thead-dark\"><tr><th>Acronym</th><th>Display</th></tr></thead><tbody>";
    ksort($acronymList);
    $i = 0;
    foreach($acronymList as $k=>$v) {
        $directoryStr .= "<tr><td>".$k."</td><td>";
        $size = count($v);
        foreach($v as $fctKey=>$fctVal)
        {
            $directoryStr .= $fctKey;
            if ($i + 1 < $size) {
                $directoryStr .= "/";
            }
            $i++;
        }
        $directoryStr .= "</td></tr>";
    }

    $directoryStr .= "</tbody></table></div>";
    //var_dump($acronymList);
    return $directoryStr;
}
