<?php



function lab_admin_username_get($userId) {
    global $wpdb;
    $results = $wpdb->get_results( "SELECT * FROM `".$wpdb->prefix."usermeta` WHERE (meta_key = 'first_name' or meta_key='last_name' or meta_key LIKE 'lab_%') and user_id=".$userId  );
    $items = array();
    $items["id"] = $userId;

    foreach ( $results as $r )
    {
        if ($r->meta_key == 'first_name')
            $items['first_name'] = $r->meta_value;
        if ($r->meta_key == 'last_name')
            $items['last_name'] = $r->meta_value;
        if ($r->meta_key == 'lab_user_left') {
            $items['lab_user_left'] = array();
            $items['lab_user_left']['id'] = $r->umeta_id;
            $items['lab_user_left']['value'] = $r->meta_value;
        }
        if ($r->meta_key == 'lab_user_function') {
            $items['user_function'] = $r->meta_value;
        }
        if ($r->meta_key == 'lab_user_employer') {
            $items['user_employer'] = $r->meta_value;
        }
        if ($r->meta_key == 'lab_user_phone') {
            $items['user_phone'] = $r->meta_value;
        }
        if ($r->meta_key == 'lab_user_location') {
            $items['user_location'] = $r->meta_value;
        }
        if ($r->meta_key == 'lab_user_office_number') {
            $items['user_office_number'] = $r->meta_value;
        }
        if ($r->meta_key == 'lab_user_office_floor') {
            $items['user_office_floor'] = $r->meta_value;
        }
        if ($r->meta_key == 'lab_user_funding') {
            $items['user_funding'] = $r->meta_value;
        }
    }
    
    return $items;
}
function lab_admin_get_userLogin($user_id) {
    global $wpdb;
    $sql = "SELECT `user_login` FROM `".$wpdb->prefix."users` WHERE ID=$user_id";
    return $wpdb->get_var($sql);
}
function lab_admin_loadUserHistory($user_id) {
    global $wpdb;
    $sql = "SELECT * from `".$wpdb->prefix."lab_users_historic` WHERE `user_id`=$user_id;";
    $res = $wpdb->get_results($sql);
    if ($res==null) {
        return "<li>No history</li>";
    } else {
        return lab_admin_history($res);
    }
}
/*******************************************************************************************************
 * PARAM
 *******************************************************************************************************/

function lab_admin_param_save($paramType, $paramName, $color = null, $paramId = null)
{
    global $wpdb;
    if ($type == -1) {
      $type = 0;
    }
    if (!isset($color) || empty($color))
    {
        $color = null;
    }

    if (startsWith($color, "#"))
    {
        $color = substr($color, 1, strlen($color));
    }

    if ($paramId == null)
    {
        //return !lab_admin_param_exist($paramType, $paramName);
        if (lab_admin_param_exist($paramType, $paramName)) {
            return false;
        } else {
            //$sql = "INSERT INTO `".$wpdb->prefix."lab_params` (`id`, `type_param`, `value`) VALUES (NULL, '".$paramType."', '".$paramName."')";
            $wpdb->insert($wpdb->prefix.'lab_params', array("type_param"=>$paramType, "value"=>$paramName, "color"=>$color));
            //$results = $wpdb->get_results($sql);
            return $wpdb->insert_id;
            
        }
    }
    else
    {
        $wpdb->update($wpdb->prefix.'lab_params', array("type_param"=>$paramType, "value"=>$paramName, "color"=>$color), array("id"=>$paramId));
        return $paramId;
    }
}

/**
 * Update or create keyword to user
 *
 * @param [type] $userId
 * @param [type] $keyword
 * @param [type] $keywordNumber
 * @return void
 */
function lab_admin_hal_add_keyword_to_user($userId, $keyword, $keywordNumber)
{
    global $wpdb;
    $sql = "SELECT id FROM `".$wpdb->prefix."lab_hal_keywords` WHERE value='".str_replace("'","\'", $keyword)."'";
    $results = $wpdb->get_results($sql);
    if(count($results) == 1) {
        $keywordId = $results[0]->id;
    }
    else {
        $wpdb->insert($wpdb->prefix."lab_hal_keywords",array("value"=>$keyword));
        $keywordId = $wpdb->insert_id;
    }
    $sql = "SELECT id FROM `".$wpdb->prefix."lab_hal_keywords_user` WHERE user_id=".$userId." AND keyword_id=".$keywordId;
    $results = $wpdb->get_results($sql);
    if(count($results) == 1) {
        try {
            $wpdb->update($wpdb->prefix."lab_hal_keywords_user", array("number"=>$keywordNumber), array("id"=>$results[0]->id));
        } catch (Exception $e) {
            echo("\n\n################\n \$userId".$userId."\n\$keyword :".$keyword."\n\$keywordNumber :".$keywordNumber."\n#################\n");
            echo 'Caught exception: ',  $e->getMessage(), "\n";
            exit(1);
        }
        
    }
    else {
        $wpdb->insert($wpdb->prefix."lab_hal_keywords_user", array("user_id"=>$userId, "keyword_id"=>$keywordId, "number"=>$keywordNumber));
    }
}

function lab_admin_param_exist($paramType, $paramName)
{
    global $wpdb;
    $sql = "SELECT id FROM `".$wpdb->prefix."lab_params` WHERE `type_param` = ".$paramType." AND  `value` = '".$paramName."'";
    $results = $wpdb->get_results($sql);
    return count($results) == 1;
}

function lab_admin_createTable_hal_keywords() {
    global $wpdb;
    $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."lab_hal_keywords` (
        `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
        `value` varchar(255) DEFAULT NULL,
        PRIMARY KEY (`id`)
      ) ENGINE=InnoDB";
    $wpdb->get_results($sql);
    
}
function lab_admin_createTable_hal_keywords_user() {
    global $wpdb;
    $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."lab_hal_keywords_user` (
        `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
        `user_id` bigint UNSIGNED NOT NULL,
        `keyword_id` bigint UNSIGNED NOT NULL,
        `number` int NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
      ) ENGINE=InnoDB";
    $wpdb->get_results($sql);
    
}
function lab_admin_createTable_users_historic() {
    global $wpdb;
    $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."lab_users_historic` (
        `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
        `user_id` bigint UNSIGNED NOT NULL,
        `ext` tinyint NOT NULL COMMENT '0 if insite wp_users, 1 if in lab_guests',
        `begin` datetime NOT NULL,
        `end` datetime NOT NULL,
        `host_id` bigint UNSIGNED NOT NULL COMMENT 'link with a user',
        `function` bigint UNSIGNED NOT NULL COMMENT 'link with parameter lab_user_function',
        PRIMARY KEY (`id`)
      ) ENGINE=InnoDB";
    $wpdb->get_results($sql);
    
}

function lab_admin_createTable_param() {
    global $wpdb;
    $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."lab_params` (
        `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
        `type_param` bigint UNSIGNED NOT NULL,
        `value` varchar(45) DEFAULT NULL,
        `color` varchar(8) DEFAULT NULL,
        PRIMARY KEY (`id`)
      ) ENGINE=InnoDB";
    $wpdb->get_results($sql);
}

function lab_admin_createTable_presence() {
    global $wpdb;
    $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."lab_presence` (
        `id` bigint NOT NULL AUTO_INCREMENT,
        `user_id` bigint NOT NULL,
        `hour_start` datetime NOT NULL,
        `hour_end` datetime NOT NULL,
        `site` int NOT NULL,
        `comment` VARCHAR(200) NOT NULL,
        `external` tinyint(1) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
      ) ENGINE=InnoDB;";
    return $wpdb->get_results($sql);
}

function lab_admin_initTable_param() {
    global $wpdb;
    $wpdb->query("DELETE FROM ".$wpdb->prefix."lab_params WHERE ID < 7");
    $sql = "INSERT INTO `".$wpdb->prefix."lab_params` (`id`, `type_param`, `value`, `color`) VALUES
            (1, 1, 'PARAM', NULL),
            (2, 1, 'GROUP TYPE', NULL),
            (3, 1, 'KEY TYPE', NULL),
            (4, 1, 'SITE', NULL),
            (5, 1, 'USER FUNCTION', NULL),
            (6, 1, 'MISSION', NULL),
            (7, 1, 'FUNDING', NULL),
            (8, 1, 'EMPLOYER', NULL),            
            (9, 1, 'LDAP TOKEN', NULL),
            (10, 1, 'LDAP HOST', NULL),
            (11, 1, 'LDAP BASE', NULL),
            (12, 1, 'LDAP LOGIN', NULL),
            (13, 1, 'LDAP PASSWORD', NULL),
            (14, 1, 'LDAP TLS', NULL),
            (15, 1, 'LDAP ENABLE', NULL),
            (NULL, 2, 'Equipe', NULL),
            (NULL, 2, 'Groupe', NULL),
            (NULL, 3, 'Clé', NULL),
            (NULL, 3, 'Badge', NULL),
            (NULL, 4, 'Luminy', '067BC2'),
            (NULL, 4, 'Saint-Charles', 'F75C03'),
            (NULL, 4, 'CMI', '04A777'),
            (NULL, 6, 'Séminaire', NULL),
            (NULL, 7, 'CNRS', NULL),
            (NULL, 7, 'AMU', NULL),
            (NULL, 7, 'ECM', NULL);";
    $wpdb->get_results($sql);
}

/**
 * Return all site defined
 *
 * @return [[id, value],...]
 */
function lab_admin_list_site()
{
    global $wpdb;
    $sql = "SELECT id, value, color FROM `".$wpdb->prefix."lab_params` WHERE type_param = 4 ORDER BY value";
    return $wpdb->get_results($sql);
}

/**
 * Return label of a site
 * @param $sideId : site ID
 * @return label of a site
 */
function lab_admin_getSite($sideId)
{
    global $wpdb;
    $sql = "SELECT id, value, color FROM `".$wpdb->prefix."lab_params` WHERE id = ".$sideId;
    return $wpdb->get_results($sql)[0]->value;
}

/**
 * Return presence of all user order by user_id, start_hour
 *
 * @param [type] $startDate
 * @param [type] $endDate
 * @return void
 */
function lab_admin_list_present_user($startDate, $endDate) {
    global $wpdb;
    $sql = "SELECT lp.id, lp.user_id, lp.hour_start, lp.hour_end, lp.site as site_id, lp.comment as comment, p.value as site, um1.meta_value as first_name, um2.meta_value as last_name, um4.meta_value as office, um5.meta_value as floor FROM `".$wpdb->prefix."lab_presence` AS lp JOIN ".$wpdb->prefix."lab_params as p ON p.id=lp.site JOIN ".$wpdb->prefix."usermeta AS um1 ON um1.user_id=lp.user_id JOIN ".$wpdb->prefix."usermeta AS um2 ON um2.user_id=lp.user_id JOIN ".$wpdb->prefix."usermeta AS um3 ON um3.user_id=lp.user_id JOIN ".$wpdb->prefix."usermeta AS um4 ON um4.user_id=lp.user_id JOIN ".$wpdb->prefix."usermeta AS um5 ON um5.user_id=lp.user_id WHERE lp.external=0 AND (lp.`hour_start` BETWEEN '".date("Y-m-d", $startDate)." 00:00:00' AND '".date("Y-m-d", $endDate)." 23:59:59') AND um1.meta_key='first_name' AND um2.meta_key='last_name' AND um3.meta_key='lab_user_location' AND um4.meta_key='lab_user_office_number' AND um5.meta_key='lab_user_office_floor' ORDER BY lp.user_id, lp.`hour_start`";
    $registredUser = $wpdb->get_results($sql);
    $sql = "SELECT lp.id, lp.user_id, lp.hour_start, lp.hour_end, lp.site as site_id, lp.comment as comment, p.value as site, lug.first_name as first_name, lug.last_name as last_name FROM `".$wpdb->prefix."lab_presence` AS lp JOIN ".$wpdb->prefix."lab_params as p ON p.id=lp.site JOIN ".$wpdb->prefix."lab_guests AS lug ON lug.id=lp.user_id WHERE lp.external=1 AND (lp.`hour_start` BETWEEN '".date("Y-m-d", $startDate)." 00:00:00' AND '".date("Y-m-d", $endDate)." 23:59:59') ORDER BY lp.user_id, lp.`hour_start`";
    //return $wpdb->get_results($sql);
    $externalUser = $wpdb->get_results($sql);
    foreach($externalUser as $u) {
        $registredUser[] = $u;
    }
    return $registredUser;
    //return $sql;
}

/**
 * Return the presency save for the same day
 *
 * @param [type] $userId
 * @param [type] $startDate
 * @param [type] $endDate
 * @param [type] $presenceId
 * @return array of the presency for the same day, except the presencyId ones
 */
function lab_admin_present_get_same_day_presency($userId, $startDate, $endDate, $presenceId)
{
    global $wpdb;
    $sql = "SELECT * FROM `".$wpdb->prefix."lab_presence` WHERE `user_id`=".$userId." AND `hour_start` BETWEEN '".date("Y-m-d", $startDate)." 00:00:00' AND '".date("Y-m-d", $endDate)." 23:59:59' AND id!=".$presenceId." ORDER BY `hour_start`";
    //return $sql;
    return $wpdb->get_results($sql);
}

function lab_admin_present_not_same_half_day($userId, $startDate, $endDate, $presenceId)
{
    global $wpdb;
    $sql = "SELECT * FROM `".$wpdb->prefix."lab_presence` WHERE `user_id`=".$userId." AND `hour_start` BETWEEN '".date("Y-m-d", $startDate)." 00:00:00' AND '".date("Y-m-d", $endDate)." 23:59:59' ORDER BY `hour_start`";
    
    $r = $wpdb->get_results($sql);
    
    if (count($r) > 0) 
    {
        $hour = 0;
        foreach($r as $presency)
        {
            $endHour   = intval(date("G", strtotime($presency->hour_end)));
            $startHour = intval(date("G", strtotime($presency->hour_start)));
            if ($endHour < 13) {
                $hour += 1;
            }
            else
            {
                if ( $startHour < 13) {
                    $hour += 5;
                }
                else {
                    $hour += 3;
                }
            }
        }
        //return array("success"=>false, "data"=>"hour :" .$hour);
        //return array("success"=>false, "data"=>"presency->hour_end:"+date("G", strtotime($presency->hour_end)));
        $startHour = intval(date("G", $startDate));
        $endHour   = intval(date("G", $endDate));


        // if a presency exist in the morning
        if ($hour < 3)
        {
            //return array("success"=>false, "data"=>"hour < 3 \$startHour : ". $startHour);
            if ($startHour < 13) {
                return array("success"=>false, "data"=>sprintf(esc_html("Apologize, we only manage a presency by half day, your already present in the morning of %s"), date("Y-m-d", strtotime($r[0]->hour_start))));
                //sprintf(__("Your are already present in %s the %s between %s and %s"), $siteLabel, date("Y-m-d", strtotime($r[0]->hour_start)), date("H:i", strtotime($r[0]->hour_start)), date("H:i", strtotime($r[0]->hour_end)));
            }
        }
        // all the day
        else if ($hour == 5 || $hour >= 10)
        {
            return array("success"=>false, "data"=>sprintf(esc_html("Apologize, we only manage a presency by half day, your already present all the day %s"), date("Y-m-d", strtotime($r[0]->hour_start))));
        }
        // two resa : 1 morning, 1 afternoon
        else if ($hour >= 3 && $hour < 5)
        {
            if ($hour == 3) {
                if ($endHour > 13) {
                    return array("success"=>false, "data"=>sprintf(esc_html("Apologize, we only manage a presency by half day, your already present in the afternoon of %s"), date("Y-m-d", strtotime($r[0]->hour_start))));
                }
            }
            else {
                return array("success"=>false, "data"=>sprintf(esc_html("Apologize, we only manage a presency by half day, your already present in the morning and the afternoon of %s"), date("Y-m-d", strtotime($r[0]->hour_start))));
            }
            
        }
    }
    return array("success"=>true, "data"=>null);
}

function lab_admin_present_check_overlap_presency($userId, $startDate, $endDate, $presenceId) {
    global $wpdb;
    $presence = "";
    if ($presenceId != null) {
        $presence = " AND id != ".$presenceId;
    }
    $sql = "SELECT lp.* FROM `".$wpdb->prefix."lab_presence` AS lp WHERE lp.`user_id` = $userId AND (('".date("Y-m-d H:i:s", $startDate)."'  BETWEEN lp.`hour_start` AND lp.`hour_end` ) OR ('".date("Y-m-d H:i:s", $endDate)."' BETWEEN lp.`hour_start` AND lp.`hour_end`))".$presence;
    
    $r1 = $wpdb->get_results($sql);
    $sql = "SELECT lp.* FROM `".$wpdb->prefix."lab_presence` AS lp WHERE lp.`user_id` = $userId AND ((lp.`hour_start` BETWEEN '".date("Y-m-d H:i:s", $startDate)."' AND '".date("Y-m-d H:i:s", $endDate)."') OR (lp.`hour_end` BETWEEN '".date("Y-m-d H:i:s", $startDate)."' AND '".date("Y-m-d H:i:s", $endDate)."'))".$presence;
    $r2 = $wpdb->get_results($sql);
    //return $sql;
    return $r1+$r2;
}

/**
 * Delete param key, can't delete param with id < 5, because param system
 *
 * @param [int] $paramId : param identifier
 * @return boolean
 */
function lab_admin_param_delete_by_id($paramId) {
    global $wpdb;
    // can't delete param Id 1, because it is the default parameter
    if ($paramId > 8 ) {
        $wpdb->delete($wpdb->prefix.'lab_params', array('id' => $paramId));
        return true;
    }
    else {
        return false;
    }
}

function lab_admin_param_search_by_value($value) {
    global $wpdb;
    $sql = "SELECT * FROM `".$wpdb->prefix."lab_params` WHERE `value` LIKE '%".$value."%'";
    return $wpdb->get_results($sql);
}

function lab_admin_firstname_lastname2($name){
    global $wpdb;
    $sql="SELECT DISTINCT um1.`user_id` AS id, um3.`meta_value` AS first_name, um2.`meta_value` as last_name, um4.`meta_value` as slug
            FROM `".$wpdb->prefix."usermeta` AS um1 
            JOIN `".$wpdb->prefix."usermeta` AS um2 ON um1.`user_id` = um2.`user_id` 
            JOIN `".$wpdb->prefix."usermeta` AS um3 ON um1.`user_id` = um3.`user_id`
            JOIN `".$wpdb->prefix."usermeta` AS um4 ON um1.`user_id` = um4.`user_id`
            JOIN `".$wpdb->prefix."users` AS um5 ON um1.`user_id` = um5.`ID`
            WHERE (um1.`meta_key`='first_name' OR um1.`meta_key`='last_name') 
            AND um2.`meta_key`='last_name' 
            AND um3.`meta_key`='first_name'   
            AND um4.`meta_key`='lab_user_slug'         
            AND (um1.`meta_value` LIKE '%" . $name . "%' OR um5.`user_email` LIKE '%" . $name . "%')";
            
    $results = $wpdb->get_results($sql);
    $nbResult = $wpdb->num_rows;
    $items = array();

    foreach ($results as $r) {
        $items[] = array('label' => $r->first_name . " " . $r->last_name , 'firstname' => $r->first_name , 'lastname' => $r->last_name , 'user_id' => $r->id, 'userslug' => $r->slug);
    }
    return $items;
}

/**
 * Initialise usermeta table with our lab fields
 *
 * @return void
 */
function lab_admin_initTable_usermeta()
{
    lab_userMetaData_create_metaKeys("user_function", "");
    lab_userMetaData_create_metaKeys("user_location", "");
    lab_userMetaData_create_metaKeys("user_office_number", "");
    lab_userMetaData_create_metaKeys("user_office_floor", "");
    lab_userMetaData_create_metaKeys("user_phone", "");
    lab_userMetaData_create_metaKeys("user_funding", "");
    lab_userMetaData_create_metaKeys("user_left", null);
    lab_userMetaData_create_metaKeys("user_slug", null);
    lab_userMetaData_create_metaKeys("user_position", null);
    lab_userMetaData_create_metaKeys("hal_id", null);
    lab_userMetaData_create_metaKeys("hal_name", null);
    lab_userMetaData_create_metaKeys("profile_bg_color", "#F2F2F2");
    lab_admin_usermeta_fill_hal_name();
    lab_admin_usermeta_fill_user_slug();
    lab_admin_createSocial();
}

function lab_admin_add_new_user_metadata($userId)
{
    lab_userMetaData_save_key($userId, "user_function", "");
    lab_userMetaData_save_key($userId, "user_location", "");
    lab_userMetaData_save_key($userId, "user_office_number", "");
    lab_userMetaData_save_key($userId, "user_office_floor", "");
    lab_userMetaData_save_key($userId, "user_phone", "");
    lab_userMetaData_save_key($userId, "user_left", null);
    lab_userMetaData_save_key($userId, "user_slug", null);
    lab_userMetaData_save_key($userId, "user_position", null);
    lab_userMetaData_save_key($userId, "hal_id", null);
    lab_userMetaData_save_key($userId, "hal_name", null);
    lab_userMetaData_save_key($userId, "profile_bg_color", "#F2F2F2");
    lab_admin_usermeta_fill_hal_name($userId);
    lab_admin_usermeta_fill_user_slug($userId);
    lab_admin_createSocial($userId);
}

function lab_admin_firstname_lastname($param, $name){
    global $wpdb;
    $sql = "SELECT um1.`user_id` AS id, um3.`meta_value` as first_name, um2.`meta_value` as last_name 
          FROM `".$wpdb->prefix."usermeta` AS um1 JOIN `".$wpdb->prefix."usermeta` AS um2 ON um1.`user_id` = um2.`user_id` JOIN `".$wpdb->prefix."usermeta` AS um3 ON um1.`user_id` = um3.`user_id` 
          WHERE (um1.`meta_key`='first_name' OR um1.`meta_key`='last_name') 
            AND um2.`meta_key`='last_name' 
            AND um3.`meta_key`='first_name'            
            AND um1.`meta_value`LIKE '%" . $name . "%'";

  $results = $wpdb->get_results($sql);
  $nbResult = $wpdb->num_rows;
  $items = array();

  foreach ($results as $r) {
    $items[] = array(label => $r->first_name . " " . $r->last_name , value => $r->id);
  }
  return $items;
}
function lab_admin_checkTable($tableName) {
    global $wpdb;
    $sql = "SHOW TABLES LIKE '".$wpdb->prefix.$tableName."';";
    $results = $wpdb->get_results($sql);
    if (count($results)) {
    return true;
    }
    return false;
  }
  
/********************************************************************************************
 * GROUPS
 ********************************************************************************************/

function lab_group_get_user_groups($userId)
{
    global $wpdb;
    return $wpdb->get_results("SELECT lg.group_name, lg.url
                                FROM `".$wpdb->prefix."lab_users_groups` as lug 
                                JOIN `".$wpdb->prefix."lab_groups` AS lg ON lg.id=lug.group_id 
                                WHERE lug.`user_id`=".$userId);
}

function lab_admin_delete_all_group() {
    global $wpdb;
    $sql = "SELECT id FROM `".$wpdb->prefix."lab_groups`";
    $results =  $wpdb->get_results($sql);
    foreach($results as $r) {
        lab_admin_delete_group($r);
    }
}

function lab_admin_delete_group($groupId) {
    lab_admin_delete_group_substitutes_by_groupId($groupId);
    lab_admin_delete_users_groups_by_groupId($groupId);
    global $wpdb;
    $wpdb->delete($wpdb->prefix."lab_groups", array("id"=>$groupId));
}

function lab_admin_delete_group_substitutes_by_groupId($groupId) {
    global $wpdb;
    $wpdb->delete($wpdb->prefix."lab_group_substitutes", array("group_id"=>$groupId));
}
function lab_admin_delete_users_groups_by_groupId($groupId) {
    global $wpdb;
    $wpdb->delete($wpdb->prefix."lab_users_groups", array("group_id"=>$groupId));
}

function lab_admin_search_group_by_acronym($ac) {
    global $wpdb;
    $sql = "SELECT group_name,id FROM `".$wpdb->prefix."lab_groups` WHERE acronym = '".$ac."';";
    $results = $wpdb->get_results($sql);
    $items = array();
    foreach ( $results as $r )
    {
      array_push($items,$r);
    }
    return $items;
}

function lab_admin_createUserGroupTable()
{
    global $wpdb;
    $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."lab_users_groups` (
        `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
        `group_id` bigint UNSIGNED NOT NULL,
        `user_id` bigint UNSIGNED NOT NULL,
        PRIMARY KEY(`id`)
      ) ENGINE=InnoDB;";
    $wpdb->get_results($sql);
}

function lab_admin_createGroupTable() {
    global $wpdb;
    $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."lab_groups`(
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `acronym` varchar(20) UNIQUE,
        `group_name` varchar(255) NOT NULL,
        `chief_id` BIGINT UNSIGNED NOT NULL,
        `group_type` TINYINT NOT NULL,
        `parent_group_id` BIGINT UNSIGNED,
        `url` varchar(255) NULL,
        PRIMARY KEY(`id`),
        FOREIGN KEY(`chief_id`) REFERENCES `".$wpdb->prefix."users`(`ID`),
        FOREIGN KEY(`parent_group_id`) REFERENCES `".$wpdb->prefix."lab_groups`(`id`)) ENGINE = INNODB;";
    $wpdb->get_results($sql);
}
function lab_admin_createSubTable() {
    global $wpdb;
    $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."lab_group_substitutes`(
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `group_id` BIGINT UNSIGNED NOT NULL ,
        `substitute_id` BIGINT UNSIGNED NOT NULL,
        PRIMARY KEY(`id`),
        FOREIGN KEY(`substitute_id`) REFERENCES `".$wpdb->prefix."users`(`ID`),
        FOREIGN KEY(`group_id`) REFERENCES `".$wpdb->prefix."lab_groups`(`id`)) ENGINE = INNODB;";
    $wpdb->get_results($sql);
}

function lab_admin_group_create($name,$acronym,$chief_id,$parent,$type,$url) {
    //$sql = "INSERT INTO `".$wpdb->prefix."lab_groups` (`id`, `acronym`, `group_name`, `chief_id`, `group_type`, `parent_group_id`) VALUES (NULL, '".$acronym."', '".$name."', '".$chief_id."', '".$type."', ".($parent == 0 ? "NULL" : "'".$parent."'").");";
    global $wpdb;
    $wpdb->hide_errors();
    if ( $wpdb->insert(
        $wpdb->prefix.'lab_groups',
        array(
            'acronym' => $acronym,
            'group_name' => stripslashes($name),
            'chief_id' => $chief_id,
            'group_type' => $type,
            'parent_group_id' => $parent == 0 ? NULL : $parent,
            'url' => $url
        )
    ) ) {
        $groupId = $wpdb->insert_id;
        // add chief ID to the group
        lab_admin_users_groups_check_and_add_user($chief_id, $groupId);
        //return "groupId :".$groupId;
    } else {
        return $wpdb -> last_error;
    }
}

function lab_admin_users_groups_check_and_add_user($userId, $groupId) {
    global $wpdb;
    $results = $wpdb->get_results("SELECT * from `".$wpdb->prefix."lab_users_groups` WHERE user_id=".$userId." AND group_id=".$groupId);
    if (count($results) == 0) {
        return lab_admin_users_groups_add_user($userId, $groupId);
    }
    return false;
}

function formatGroupsName($userId) {
    $groupNames = lab_group_get_user_groups($userId);
    if (count($groupNames) == 0) {
        return "";
    }
    $items = array();
    foreach($groupNames as $g) {
        $items[] ="<a href=\"$g->url\" target=\"_blank\">" . esc_html($g->group_name) . "</a>";
    }
    return join(", ", $items);
}

function lab_admin_users_groups_add_user($userId, $groupId) {
    global $wpdb;
    return $wpdb->insert($wpdb->prefix.'lab_users_groups', array("user_id"=>$userId, "group_id"=>$groupId));
}

function lab_admin_group_subs_add($groupId,$listUserId) {
    global $wpdb;
    $wpdb->hide_errors();
    foreach ($listUserId as $userId) {
        if (!$wpdb->insert($wpdb->prefix.'lab_group_substitutes', array('group_id' => $groupId, 'substitute_id' => $userId))) {
            return $wpdb -> last_error;
        } else {
            lab_admin_users_groups_check_and_add_user($userId, $groupId);
        }
    }
    return;
}
function lab_admin_get_groups_byChief($chief_id) {
    global $wpdb;
    $sql="SELECT * FROM `".$wpdb->prefix."lab_groups` WHERE `chief_id`=".$chief_id.";";
    return $wpdb->get_results($sql);
}
function lab_admin_get_chief_byGroup($group_id) {
    global $wpdb;
    $sql = "SELECT * FROM `".$wpdb->prefix."lab_groups` WHERE `id`=".$group_id.";";
    $res = $wpdb->get_results($sql)[0];
    return $res->chief_id;
}

function lab_prefGroups_add($user_id, $group_id) {
    global $wpdb;
    return $wpdb -> insert($wpdb->prefix."lab_prefered_groups",
                    array(
                        "group_id"=>$group_id,
                        "user_id"=>$user_id
                    ));
}
function lab_prefGroups_remove($user_id, $group_id) {
    global $wpdb;
    return $wpdb -> delete($wpdb->prefix."lab_prefered_groups",
                    array(
                        "group_id"=>$group_id,
                        "user_id"=>$user_id
                    ));
}
function lab_group_getById($id) {
    global $wpdb;
    return $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."lab_groups WHERE id=".$id.";")[0];
}
/********************************************************************************************
 * KeyRing
 ********************************************************************************************/
function lab_keyring_createTable_keys() {
    global $wpdb;
    $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."lab_keys` (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `number` varchar(10) NOT NULL,
        `office` varchar(20),
        `type` INT NOT NULL,
        `brand` varchar(20),
        `site` text,
        `commentary` text,
        `available` boolean NOT NULL DEFAULT TRUE,
        PRIMARY KEY(`id`)
        ) ENGINE=INNODB;";
    $wpdb->get_results($sql);
}
function lab_keyring_createTable_loans() {
    global $wpdb;
    $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."lab_key_loans` (
        `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
        `key_id` bigint UNSIGNED NOT NULL,
        `user_id` bigint UNSIGNED NOT NULL,
        `referent_id` bigint UNSIGNED NOT NULL,
        `start_date` date NOT NULL,
        `end_date` date DEFAULT NULL,
        `ended` boolean NOT NULL DEFAULT FALSE,
        `commentary` text,
        PRIMARY KEY(`id`),
        FOREIGN KEY (`key_id`) REFERENCES `".$wpdb->prefix."lab_keys` (`id`),
        FOREIGN KEY (`user_id`) REFERENCES `".$wpdb->prefix."users` (`ID`),
        FOREIGN KEY (`referent_id`) REFERENCES `".$wpdb->prefix."users` (`ID`)
      ) ENGINE=InnoDB;";
    $wpdb->get_results($sql);
}
function lab_keyring_create_key($params) {
    $params['commentary'] = strlen($params['commentary']) ? $params['commentary'] : NULL ;
    global $wpdb;
    $wpdb->hide_errors();
    if ( $wpdb->insert(
        $wpdb->prefix.'lab_keys',
        $params
        )
    ) {
        return;
    } else {
        return $wpdb -> last_error;
    }
}
function lab_keyring_create_loan($params) {
    $params['commentary'] = strlen($params['commentary']) ? $params['commentary'] : NULL ;
    $params['end_date'] = strlen($params['end_date']) ? $params['end_date'] : NULL ;
    global $wpdb;
    $wpdb->hide_errors();
    if ( $wpdb->insert(
        $wpdb->prefix.'lab_key_loans',
        $params
    ) ) {
        return;
    } else {
        return $wpdb->last_query.$wpdb -> last_error;
    }
}
function lab_keyring_search_byWord($word,$limit,$page) {
    global $wpdb;
    $offset = $page*$limit;
    $sql = "SELECT * from `".$wpdb->prefix."lab_keys`
            WHERE Concat_ws(`".$wpdb->prefix."lab_keys`.`number`,`".$wpdb->prefix."lab_keys`.`office`,`".$wpdb->prefix."lab_keys`.`site`,`".$wpdb->prefix."lab_keys`.`brand`,`".$wpdb->prefix."lab_keys`.`commentary`)
            LIKE '%".$word."%'
            ORDER BY ABS(`".$wpdb->prefix."lab_keys`.`number`)
            LIMIT ".$offset.", ".$limit.";";
    $count = "SELECT COUNT(*) from `".$wpdb->prefix."lab_keys`
              WHERE Concat_ws(`".$wpdb->prefix."lab_keys`.`number`,`".$wpdb->prefix."lab_keys`.`office`,`".$wpdb->prefix."lab_keys`.`site`,`".$wpdb->prefix."lab_keys`.`brand`,`".$wpdb->prefix."lab_keys`.`commentary`)
              LIKE '%".$word."%'";
    $results = $wpdb->get_results($sql);
    $total = $wpdb->get_var($count);
    $res = array(
        "total" => $total,
        "items" => $results
    );
    return $res;
}
function lab_keyring_search_key($id) {
    global $wpdb;
    $sql = "SELECT * from `".$wpdb->prefix."lab_keys`
            WHERE `id`=".$id.";";
    return $wpdb->get_results($sql);
}
function lab_keyring_edit_key($id,$fields) {
    $fields['commentary'] = strlen($fields['commentary']) ? $fields['commentary'] : NULL ;
    global $wpdb;
    return $wpdb->update(
        $wpdb->prefix.'lab_keys',
        $fields,
        array('id' => $id)
    );
}
function lab_keyring_delete_key($id) {
    global $wpdb;
    if ($wpdb->delete($wpdb->prefix.'lab_key_loans',array('key_id'=>$id))) {
        return $wpdb->delete(
            $wpdb->prefix.'lab_keys',
            array('id' => $id)
        );
    } else {
        return false;
    }
}

function lab_keyring_search_current_loans($user,$page,$limit) {
    global $wpdb;
    $offset = $page*$limit;
    $count = $user == 0 ? "SELECT COUNT(*) FROM `".$wpdb->prefix."lab_key_loans` WHERE `ended`=0;" : "SELECT COUNT(*) FROM `".$wpdb->prefix."lab_key_loans` WHERE `user_id`=".$user." AND `ended`=0;";
    $sql = $user == 0 ? "SELECT * FROM `".$wpdb->prefix."lab_key_loans` WHERE `ended`=0" : "SELECT * FROM `".$wpdb->prefix."lab_key_loans` WHERE `user_id`=".$user." AND `ended`=0";
    $sql .= " ORDER BY `start_date` DESC";
    $sql .= " LIMIT ".$offset.", ".$limit.";";
    $total = $wpdb->get_var($count);
    $results = $wpdb->get_results($sql);
    $res = array(
        "total" => $total,
        "items" => $results
    );
    return $res;
}
function lab_keyring_setKeyAvailable($id,$available) {
    global $wpdb;
    $wpdb->hide_errors();
    if ($wpdb->update(
        $wpdb->prefix.'lab_keys',
        array(
            'available'=>$available
        ),
        array('id' => $id)
    )) {
        return;
    } else {
        return $wpdb -> last_error;
    }
}
function lab_keyring_edit_loan($id,$params) {
    $params['end_date'] = strlen($params['end_date']) ? $params['end_date'] : NULL ;
    $params['commentary'] = strlen($params['commentary']) ? $params['commentary'] : NULL ;
    global $wpdb;
    return $wpdb->update(
        $wpdb->prefix.'lab_key_loans',
        $params,
        array('id' => $id)
    );
}
function lab_keyring_get_currentLoan_forKey($id) {
    global $wpdb;
    $sql = "SELECT * FROM `".$wpdb->prefix."lab_key_loans` WHERE `key_id`=".$id." AND ended=false";
    return $wpdb->get_results($sql);
}
function lab_keyring_end_loan($loan_id,$end_date, $key_id) {
    $avail = lab_keyring_setKeyAvailable($key_id,1);
    global $wpdb;
    if (strlen($avail)==0) {
        $res = lab_keyring_edit_loan($loan_id,array('ended'=> 1, 'end_date' => $end_date));
        return ($res === false) ? $wpdb->last_error : null;
    } else {
        return $avail;
    }
}
function lab_keyring_find_oldLoans($field, $id) {
    global $wpdb;
    $sql = "SELECT * from `".$wpdb->prefix."lab_key_loans` WHERE `".$field."`=".$id." ORDER BY `start_date` DESC";
    return $wpdb->get_results($sql);
}
function lab_keyring_get_loan($id) {
    global $wpdb;
    $sql = "SELECT * FROM `".$wpdb->prefix."lab_key_loans` WHERE `id`=".$id.";";
    return $wpdb->get_results($sql);
}

/**************************************************************************************************
 * SETTINGS
 *************************************************************************************************/

function userMetaData_get_userId_with_no_key($metadataKey) {
    global $wpdb;
    $sql = "SELECT ID FROM `".$wpdb->prefix."users` WHERE NOT EXISTS ( SELECT 1 FROM `".$wpdb->prefix."usermeta` WHERE `".$wpdb->prefix."usermeta`.`meta_key` = '".$metadataKey."' AND `".$wpdb->prefix."usermeta`.`user_id`=`".$wpdb->prefix."users`.`ID`)";
    $results = $wpdb->get_results($sql);
    $items = array();
    foreach($results as $r) {
        $items[] = $r->ID;       
    }
    return $items;
}
function lab_admin_usermeta_fill_user_slug($userId = null)
{
    global $wpdb;
    $sql = "SELECT u.id as user_id, um1.meta_value as first_name, um2.meta_value as last_name, um3.umeta_id as id FROM `".$wpdb->prefix."users` AS u JOIN `".$wpdb->prefix."usermeta` as um1 ON um1.user_id=u.ID JOIN `".$wpdb->prefix."usermeta` AS um2 ON um2.user_id=u.ID JOIN `".$wpdb->prefix."usermeta` AS um3 ON um3.user_id=u.ID WHERE um1.meta_key='first_name' AND um2.meta_key='last_name' AND um3.meta_key='lab_user_slug'";
    if ($userId != null) {
        $sql .= " AND u.id=".$userId;
    }
    $results = $wpdb->get_results($sql);
    $retour = array();
    foreach($results as $r) {
        $wpdb->update($wpdb->prefix."usermeta", array('meta_value'=>usermeta_format_name_to_slug($r->first_name, $r->last_name)), array('umeta_id'=> $r->id));
    }
}

function lab_userMetaData_create_metaKeys($metadataKey, $defaultValue) {
    $userIds = userMetaData_get_userId_with_no_key($metadataKey);
    //return $userIds;
    $errors = array();
    $ids = array();
    foreach($userIds as $userId) {
        if (lab_userMetaData_save_key($userId, $metadataKey, $defaultValue) == false) {
            $errors[] = $wpdb->last_error();
        }
    }
    if (count($errors) > 0) {
        return $errors;
    }
    return true;
}

/**
 * Save meta key, create e new one if metakey doesn't exist, update an existing one
 *
 * @param [type] $userId
 * @param [type] $metadataKey
 * @param [type] $defaultValue
 * @return Last insert id or update id, false otherwise
 */
function lab_userMetaData_save_key($userId, $metadataKey, $defaultValue) {
    global $wpdb;
    if ($defaultValue == 'null') {
        $defaultValue = null;
    }
    if (substr($metadataKey, 0, strlen(LAB_META_PREFIX)) !== LAB_META_PREFIX) {
        $metadataKey = LAB_META_PREFIX.$metadataKey;
    }


    $umId = userMetaData_exist_metakey_for_user($metadataKey, $userId);
    if (!$umId) {
        $wpdb->insert($wpdb->prefix."usermeta", array('meta_value'=>$defaultValue, 'umeta_id'=>null, 'user_id'=>$userId, 'meta_key'=>$metadataKey));
        $r = $wpdb->insert_id;
    } else {
        $wpdb->update($wpdb->prefix."usermeta", array('meta_value'=>$defaultValue), array('umeta_id'=>$umId, 'user_id'=>$userId, 'meta_key'=>$metadataKey));
        $r = $umId;
    }
    if (!$r) {
        return $wpdb->last_error();
    }
    return $r;
}

function userMetaData_delete_metaKey($metadataKey) {
    global $wpdb;
    $sql = "DELETE FROM `".$wpdb->prefix."usermeta` WHERE `".$wpdb->prefix."usermeta`.`meta_key` = '".LAB_META_PREFIX.$metadataKey."'";
    return $wpdb->get_results($sql);
}

function userMetaData_list_metakeys() {
    global $wpdb;
    $sql = "SELECT DISTINCT meta_key FROM `".$wpdb->prefix."usermeta` WHERE meta_key LIKE '".LAB_META_PREFIX."%'";
    $items = array();
    //foreach($wpdb->get_results($sql) as $r)
    $results = $wpdb->get_results($sql);
    if (count($results) > 0) {
        foreach($results as $r) {
            $items[] = $r->meta_key;
        }
    }
    return $items;
}

function userMetaData_delete_metakeys($metadataKey) {
    
    // theoricaly we only allow to delete metadatakey begin with LAB_META_PREFIX
    if (substr($metadataKey, 0, strlen(LAB_META_PREFIX)) !== LAB_META_PREFIX) {
        $metadataKey = LAB_META_PREFIX.$metadataKey;
    }
    global $wpdb;
    return $wpdb->delete($wpdb->prefix.'usermeta', array("meta_key"=>$metadataKey));
}

function userMetaData_exist_metakey_for_user($metadataKey, $userId) {
    if (substr($metadataKey, 0, strlen(LAB_META_PREFIX)) !== LAB_META_PREFIX) {
        $metadataKey = LAB_META_PREFIX.$metadataKey;
    }
    global $wpdb;
    $results = $wpdb->get_results("SELECT umeta_id FROM `".$wpdb->prefix."usermeta` WHERE `meta_key` = '".$metadataKey."' AND `user_id`=".$userId);
    if (count($results) > 0) {
        return $results[0]->umeta_id;
    }
    return false;
}

function userMetaData_exist_metakey($metadataKey) {
    if (substr($metadataKey, 0, strlen(LAB_META_PREFIX)) !== LAB_META_PREFIX) {
        $metadataKey = LAB_META_PREFIX.$metadataKey;
    }
    global $wpdb;
    $results = $wpdb->get_results("SELECT umeta_id FROM `".$wpdb->prefix."usermeta` WHERE `meta_key` = '".$metadataKey."'");
    if (count($results) > 0) {
        return $results[0]->umeta_id;
    }
    return false;
}
function lab_admin_createSocial($userId = null) {
    foreach (['facebook','instagram','linkedin','pinterest','twitter','tumblr','youtube'] as $reseau) {
        lab_userMetaData_create_metaKeys($reseau,'', $userId);
    }
}
/**************************************************************************************************
 * HAL
 *************************************************************************************************/
function lab_hal_createTable_hal() {
    global $wpdb;
    $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."lab_hal` (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `user_id` int NOT NULL,
        `docid` int NOT NULL COMMENT 'docid issus de hal',
        `citationFull_s` varchar(1000) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
        `title` varchar(200) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
        `url` varchar(200) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
        `producedDate_tdate` date,
        `journalTitle_s` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
        PRIMARY KEY(`id`)
      ) ENGINE=InnoDB";
    $wpdb->get_results($sql);
    $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."lab_hal_users` (
        `id` bigint UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
        `hal_id` bigint UNSIGNED NOT NULL,
        `user_id` bigint UNSIGNED NOT NULL
      ) ENGINE=InnoDB";
    return $wpdb->get_results($sql);

    lab_admin_createTable_hal_keywords_user();
    lab_admin_createTable_hal_keywords();
}

/**
 * List all the hal articles by groups and year
 *
 * @param [type] $groups type array
 * @param [type] $year
 * @return void
 */
function lab_hal_getPublication_by_group($groups, $year = null)
{
    global $wpdb;
    $sql = "";
    if (is_array($groups))
    {
        $sql = "SELECT lh.* FROM `".$wpdb->prefix."lab_hal` as lh JOIN `".$wpdb->prefix."lab_hal_users` AS lhu ON lhu.hal_id=lh.id WHERE lhu.user_id IN (";
        $sql .= "SELECT DISTINCT lug.user_id FROM `".$wpdb->prefix."lab_groups` AS lg JOIN `".$wpdb->prefix."lab_users_groups` AS lug ON lug.group_id=lg.id WHERE ";
        foreach($groups as $g) {
            $sql .= " lg.acronym='".$g."' OR";
        }
        $sql = substr($sql, 0, strlen($sql) - 2);

        $sql .= ")";
        if ($year != null)
        {
            $sql .= " AND lh.`producedDate_tdate` >= '".$year."-01-01'  AND lh.`producedDate_tdate` <= '".$year."-12-31'";
        }

    }
    else
    {
        $sql = "SELECT DISTINCT lh.id, lh.* FROM  `".$wpdb->prefix."lab_groups` AS lg JOIN `".$wpdb->prefix."lab_users_groups` AS lug ON lug.group_id=lg.id JOIN `".$wpdb->prefix."lab_hal_users` AS lhu ON lhu.user_id=lug.user_id JOIN `".$wpdb->prefix."lab_hal` as lh ON lh.id=lhu.hal_id WHERE lg.acronym='".$groups."'";
        if ($year != null)
        {
            $sql .= " AND lh.`producedDate_tdate` >= '".$year."-01-01'  AND lh.`producedDate_tdate` <= '".$year."-12-31'";
        }
    }
    $sql .= " ORDER BY `lh`.`producedDate_tdate` DESC";
    return $wpdb->get_results($sql);
    //return $sql;
}

function lab_hal_get_publication($userId, $year = null) {
    global $wpdb;
    $sql = "SELECT lh.* FROM `".$wpdb->prefix."lab_hal` as lh JOIN `".$wpdb->prefix."lab_hal_users` AS lhu ON lhu.hal_id=lh.id WHERE lhu.user_id=".$userId;
    
    if ($year != null)
    {
        $sql .= " AND `producedDate_tdate` >= '".$year."-01-01'  AND `producedDate_tdate` <= '".$year."-12-31'";
    }
    $sql .= " ORDER BY `lh`.`producedDate_tdate` DESC";
    return $wpdb->get_results($sql);
}

function format_spaced_string($s) {
    if (strpos($s, " ") > 0) {
        $ef = explode(" ", $s);
        for ($i = 0 ; $i < count($ef); $i++) {
            if ($i > 0) {
                $ef[$i] = ucfirst($ef[$i]);
            }
        }
        $s = join("", $ef);
    }
    return $s;
}

/**
 * Format user firstname and lastname to slug format (noSpace,fistname.lastName)
 *
 * @param [type] $f firstname
 * @param [type] $l lastname
 * @return slug
 */
function usermeta_format_name_to_slug($f, $l) {
    $f = strtolower($f);
    $l = strtolower($l);
    $f = format_spaced_string($f);
    $l = format_spaced_string($l);
    return $f.".".$l;
}

/**
 * Format name for hal request, replace space character by '+'
 */
function hal_format_name($name) {
    
    if (strpos($name, " ") > -1)
    {
        $t = explode(" ", $name);
        $arraylenght = count($t);
        $i = 0;
        $nameF = "";
        
        while ($i < $arraylenght) {
            $nameF .= ucfirst(strtolower($t[$i]));
            if ($i + 1 < $arraylenght) {
                $nameF .= "+";
            }
            $i++;
        }
        return $nameF;
        //*/
    }
    else {
        return ucfirst(strtolower($name));
    }
}

function lab_admin_usermeta_fill_hal_name($userId = null) {
    global $wpdb;
    $sql = "SELECT u.id as user_id, um1.meta_value as first_name, um2.meta_value as last_name, um3.umeta_id as id FROM `".$wpdb->prefix."users` AS u JOIN `".$wpdb->prefix."usermeta` as um1 ON um1.user_id=u.ID JOIN `".$wpdb->prefix."usermeta` AS um2 ON um2.user_id=u.ID JOIN `".$wpdb->prefix."usermeta` AS um3 ON um3.user_id=u.ID WHERE um1.meta_key='first_name' AND um2.meta_key='last_name' AND um3.meta_key='lab_hal_name'";
    if ($userId != null) {
        $sql .= " AND u.id=".$userId;
    }
    $results = $wpdb->get_results($sql);
    $retour = array();
    foreach($results as $r) {
        $wpdb->update($wpdb->prefix."usermeta", array('meta_value'=>hal_format_name($r->last_name).'+'.hal_format_name($r->first_name)), array('umeta_id'=> $r->id));
        //$retour[] = "wpdb->update(".$wpdb->prefix."usermeta, array('meta_value'=>".hal_format_name($r->last_name)."+".hal_format_name($r->first_name)."), array('umeta_id'=> ".$r->id."));";
        //$retour[] = "UPDATE ".$wpdb->prefix."usermeta SET meta_value='".ucfirst(strtolower($r->last_name)).'+'.ucfirst(strtolower($r->first_name))."' WHERE ".$r->id;
    }
    return $retour;
}

function get_hal_url($userId) {
    global $wpdb;
    $sql = "SELECT um1.meta_value as lab_hal_id, um2.meta_value as lab_hal_name FROM `".$wpdb->prefix."usermeta` AS um1 JOIN `".$wpdb->prefix."usermeta` AS um2 ON um2.user_id=um1.user_id WHERE um1.`user_id`=".$userId." AND um1.`meta_key`='lab_hal_id' AND um2.`meta_key`='lab_hal_name'";
    
    $results = $wpdb->get_results($sql);
    if (count($results) == 1) {
        $hal_id   = $results[0]->lab_hal_id;
        $hal_name = $results[0]->lab_hal_name;
        if ($hal_id != null) {
            //return "https://api.archives-ouvertes.fr/search/?q=*:*&fq=authIdHal_s:(".$hal_id.")&fl=docid,citationFull_s,producedDate_tdate,uri_s,title_s,journalTitle_s&sort=producedDate_tdate+desc&wt=json&json.nl=arrarr";
            //return "https://api.archives-ouvertes.fr/search/?q=*:*&fq=authIdHal_s:(".$hal_id.")&group=true&group.field=docType_s&group.limit=1000&&fl=docid,citationFull_s,producedDate_tdate,uri_s,title_s,journalTitle_s&facet.field=fr_domainAllCodeLabel_fs&facet.field=keyword_s&facet.field=journalIdTitle_fs&facet.field=producedDateY_i&facet.field=authIdLastNameFirstName_fs&facet.field=instStructIdName_fs&facet.field=labStructIdName_fs&facet.field=deptStructIdName_fs&facet.field=rteamStructIdName_fs&facet.mincount=1&facet=true&wt=json&json.nl=arrarr";
            return "https://api.archives-ouvertes.fr/search/?q=*:*&fq=authIdHal_s:(".$hal_id.")&fl=docid,citationFull_s,producedDate_tdate,uri_s,title_s,journalTitle_s&facet.field=fr_domainAllCodeLabel_fs&facet.field=keyword_s&facet.mincount=1&facet=true&wt=json&json.nl=arrarr";
        }
        else {
            return "https://api.archives-ouvertes.fr/search/?q=authLastNameFirstName_s:%22".$hal_name."%22&fl=docid,citationFull_s,producedDate_tdate,uri_s,title_s,journalTitle_s&facet.field=fr_domainAllCodeLabel_fs&facet.field=keyword_s&facet.mincount=1&facet=true&wt=json&json.nl=arrarr";
        }
    }
}

function saveHalProduction($docId, $citation, $productionDate, $title, $url, $journal) {
    global $wpdb;
    //$wpdb->show_errors(true);
    if ($wpdb->insert($wpdb->prefix."lab_hal", array('journalTitle_s'=>$journal, 'docid'=>$docId, 'citationFull_s'=>$citation, 'producedDate_tdate'=>$productionDate, 'title'=>$title, 'url'=>$url))) {
        //$results = $wpdb->get_results("SELECT id from `".$wpdb->prefix."lab_hal` WHERE docid='".$docId."'");
    }
    else{
        //echo "[".$docid."] : ".$wpdb->last_error."\n";
    }
    return $wpdb->insert_id;
}

function saveHalUsers($userId, $halId) {
    global $wpdb;
    $wpdb->insert($wpdb->prefix."lab_hal_users", array('user_id'=>$userId, 'hal_id'=>$halId));
    return $wpdb->insert_id;
}

/**
 * Download HAL info for all user in db
 */
function hal_download_all()
{
    global $wpdb;
    $sql = "SELECT u.id FROM `".$wpdb->prefix."users` AS u JOIN `".$wpdb->prefix."usermeta` AS um ON um.user_id=u.ID WHERE um.meta_key='lab_user_left' AND um.meta_value IS NULL";
    $results = $wpdb->get_results($sql);

    $docIds = array();
    foreach($results as $r) 
    {
        hal_download($r->id, $docIds);
    }
}


function hal_download($userId, &$docIds) {
    $url = get_hal_url($userId);
    
    if ($docIds == null) {
        $docId = array();
    }
    //echo($url."\n");

    $json = lab_do_common_curl_call($url);
    if (!isset($json->response) || !$json->response) {
        echo "No data for user ".$userId."\n";
        return;
    }

    $docs = $json->response->docs;
    if(isset($json->response->docs) && $json->response->docs)
    {
        //echo("\$c:".$c."\n");
        $c    = count($json->response->docs);
        
        $display = false;
        for ($i = 0; $i < $c; $i++) {
            $keep = false;
            $docId = $docs[$i]->docid;
            $citation = $docs[$i]->citationFull_s;
            $producedDate = strtotime($docs[$i]->producedDate_tdate);
            $title = $docs[$i]->title_s[0];
            $url = $docs[$i]->uri_s;
            //echo ($citation."\n");
            if (isset($docs[$i]->journalTitle_s))
            {
                $journal = $docs[$i]->journalTitle_s;
            }
            else {
                $journal = null;
            }

            if (!array_key_exists ($docId, $docIds)) {
                
                $id = saveHalProduction($docId, $citation, date('Y-m-d', $producedDate), $title, $url, $journal);
                
                $docIds[$docId] = $id;
                $halId = $id;            
            }
            saveHalUsers($userId, $docIds[$docId]);
        }
    }
    /*
    $fr_domainAllCodeLabel_fs = $json->facet_counts->facet_fields->fr_domainAllCodeLabel_fs;
    $c = count($fr_domainAllCodeLabel_fs);
    
    for ($i = 0; $i < $c; $i++) {
        $domain  = $fr_domainAllCodeLabel_fs[$i][0];
        $percent = $fr_domainAllCodeLabel_fs[$i][1];
        $name = explode("_FacetSep_", $domain);
        $code = $name[0];
        $translation = $name[1];
        echo($code."\n");
        echo($translation."\n");
        $fields = explode("/", $translation);
        foreach($fields as $field) {
            echo(preg_replace("/\[.*\]/","", $field)."\n");
        }
    }
    //*/

    $keywords = $json->facet_counts->facet_fields->keyword_s;
    $c = count($keywords);
    for ($i = 0; $i < $c; $i++) {
        $keyword = $keywords[$i][0];
        $keyWordPercent = $keywords[$i][1];
        //echo ($keyword." ".$keyWordPercent."\n");
        lab_admin_hal_add_keyword_to_user($userId, $keyword, $keyWordPercent);
    }

    return $url;
}

function delete_hal_table() {
    global $wpdb;
    $wpdb->query("TRUNCATE TABLE `".$wpdb->prefix."lab_hal_users`");
    $wpdb->query("TRUNCATE TABLE `".$wpdb->prefix."lab_hal`");//delete( $wpdb->prefix."lab_hal", array());
    $wpdb->get_results("ALTER TABLE `".$wpdb->prefix."lab_hal` AUTO_INCREMENT = 1");
    $wpdb->get_results("ALTER TABLE `".$wpdb->prefix."lab_hal_users` AUTO_INCREMENT = 1");
    return true;
}

/**
 * @param $url
 * @return object
 */

function lab_do_common_curl_call($url) {
    $ch = curl_init($url);
    // Options
    $options = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => array('Content-type: application/json'),
        CURLOPT_TIMEOUT => 10,
        CURLOPT_USERAGENT => "LAB Plugin Wordpress "
    );
    if(defined('WP_PROXY_HOST') && defined('WP_PROXY_PORT') && defined('WP_PROXY_USERNAME') && defined('WP_PROXY_PASSWORD')){
        curl_setopt($ch, CURLOPT_PROXY, WP_PROXY_HOST);
        curl_setopt($ch, CURLOPT_PROXYPORT, WP_PROXY_PORT);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_PROXYUSERPWD, WP_PROXY_USERNAME.':'.WP_PROXY_PASSWORD);
    }
    // Bind des options et de l'objet cURL que l'on va utiliser
    curl_setopt_array($ch, $options);
    // Récupération du résultat JSON
    $json = json_decode(curl_exec($ch));
    curl_close($ch);
    return $json;
}
//*/
function lab_hal_getLastArticles($number,$group) {
    global $wpdb;
    if (!isset($number) || empty($number)) {
        $number = 5;
    }
    if ($group == 0) {
        $sql = "SELECT * from `".$wpdb->prefix."lab_hal` ORDER BY `producedDate_tdate` DESC LIMIT ".$number.";";
    } else {
        $sql = "SELECT * FROM `".$wpdb->prefix."lab_hal` WHERE user_id IN (SELECT user_id from `".$wpdb->prefix."lab_users_groups` WHERE `".$wpdb->prefix."lab_users_groups`.`group_id` = ".$group.") ORDER BY `producedDate_tdate` DESC LIMIT ".$number;
    }
    return $wpdb->get_results($sql);
}

/**************************************************************************************************
 * Plug-in Setup and Uninstallation
 *************************************************************************************************/

function lab_activation_hook() {
    lab_create_roles();
    create_all_tables();
}
function lab_uninstall_hook() {
    delete_all_tables();
}

function lab_admin_setting_reset_tables()
{
    delete_all_tables();
    create_all_tables();
}


function create_all_tables() {
    lab_admin_createTable_param();
    lab_admin_initTable_param();
    lab_hal_createTable_hal();
    lab_admin_createGroupTable();
    lab_admin_createUserGroupTable();
    lab_admin_createSubTable();
    lab_keyring_createTable_keys();
    lab_keyring_createTable_loans();
    lab_admin_initTable_usermeta();
    lab_admin_createTable_presence();
    lab_invitations_createTables();
    lab_admin_createTable_users_historic();
}

function delete_all_tables() {
    //lab_admin_delete_group(0);
    lab_admin_delete_all_group();
    drop_table("lab_group_substitutes");
    drop_table("lab_prefered_groups");
    drop_table("lab_users_groups");
    drop_table("lab_params");
    drop_table("lab_key_loans");
    drop_table("lab_keys");
    drop_table("lab_hal");
    drop_table("lab_hal_keywords");
    drop_table("lab_hal_keywords_user");
    drop_table("lab_hal_users");
    drop_table("lab_groups");
    drop_table("lab_presence");
    drop_table("lab_invitations");
    drop_table("lab_guests");
    drop_table("lab_invite_comments");
    drop_table("lab_presence");
}

/**
 * DROP TABLE 
 * @param : table name without prefix
 */
function drop_table($tableName) {
    global $wpdb;
    $sql = "DROP TABLE `".$wpdb->prefix.$tableName."`";
    $wpdb->get_results($sql);

}

function lab_create_roles() {
    add_role(
        'key_manager',
        'Key Manager',
        [
            'read'      => true
        ]
    );
    $role = get_role('key_manager');
    $role ->add_cap('keyring',true);
    $role = get_role('administrator');
    $role ->add_cap('keyring',true);
}

/**
 * Correct the um_user_profile_url_slug_name of the ultimate member pluggin
 */
function lab_usermeta_correct_um_fields() {
    global $wpdb;
    $sql = "UPDATE `".$wpdb->prefix."usermeta` SET `meta_value`=substr(meta_value,1,length(meta_value)-3) WHERE `meta_key` LIKE 'um_user_profile_url_slug_name' AND `meta_value` LIKE '%2A'";
    return $wpdb->get_results($sql);
}

/**
 * copy data from usermetadata dbem_phone to lab_user_phone fields in usermeta TABLE
 */
function lab_usermeta_copy_existing_phone() {
    global $wpdb;
    $sql = "select user_id, meta_value FROM `".$wpdb->prefix."usermeta` WHERE meta_key='dbem_phone'";
    $results = $wpdb->get_results($sql);
    foreach($results as $r) {
        $wpdb->update($wpdb->prefix."usermeta", array("meta_value"=>$r->meta_value), array("user_id"=>$r->user_id,"meta_key"=>"lab_user_phone"));
    }
    return true;
}

/**
 * return True if $string begins with $pattern
 * @param : $string 
 * @param : $pattern
 */
function beginWith($string, $pattern) {
    return substr($string, 0, strlen($pattern)) === $pattern;
}

/**************************************************************************************************************************************
 * PRESENCE
 *************************************************************************************************************************************/
function checkAddPresency($userId, $dateOpen, $dateEnd)
{

}

/**
 * Save or update presence, WARNING, if $external == null, no update $external field in DB
 *
 * @param [type] $id
 * @param [type] $userId
 * @param [type] $dateOpen
 * @param [type] $dateEnd
 * @param [type] $siteId
 * @param [type] $comment
 * @param integer $external
 * @return array("success"=>true, "data"=>null);
 */
function lab_admin_presence_save($id, $userId, $dateOpen, $dateEnd, $siteId, $comment, $external=0) {
    //return array("success"=>false, "data"=>"\$external : " + $external);
    global $wpdb;
    if ($id == null) {
        $wpdb->insert($wpdb->prefix."lab_presence", array("user_id"=>$userId, "hour_start"=>$dateOpen, "hour_end"=>$dateEnd, "site"=>$siteId, "comment"=>$comment, "external"=>$external));
    }
    else {
        if ($external == null)
        {
            $wpdb->update($wpdb->prefix."lab_presence", array("hour_start"=>$dateOpen, "hour_end"=>$dateEnd, "site"=>$siteId, "comment"=>$comment), array("id"=>$id));
        }
        else{
            $wpdb->update($wpdb->prefix."lab_presence", array("hour_start"=>$dateOpen, "hour_end"=>$dateEnd, "site"=>$siteId, "comment"=>$comment, "external"=>$external), array("id"=>$id));
        }
        
    }
    return array("success"=>true, "data"=>null);
}

function lab_admin_presence_delete($presenceId, $userId) {
    global $wpdb;
    if (current_user_can('administrator')) {
        return $wpdb->delete($wpdb->prefix."lab_presence", array("id"=>$presenceId));
    } 
    else {
        return $wpdb->delete($wpdb->prefix."lab_presence", array("id"=>$presenceId,"user_id"=>$userId));
    }
}

/**************************************************************************************************************************************
 * UTILS
 *************************************************************************************************************************************/

/**
 * Display numbers correctly
 *
 * @param [type] $currentNumber
 * @return void
 */
function correctNumber($currentNumber) { // currentNumber = esc_html($r->phone)
    $currentNumber = str_replace(" ", "", $currentNumber);
    $currentNumber = str_replace(".", "", $currentNumber);
    $currentNumber = chunk_split($currentNumber, 2, ' ');
    return $currentNumber;
}
function startsWith ($string, $startString) 
{ 
    $len = strlen($startString); 
    return (substr($string, 0, $len) === $startString); 
} 