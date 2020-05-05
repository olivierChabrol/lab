<?php



function lab_admin_username_get($userId) {
    global $wpdb;
    $results = $wpdb->get_results( "SELECT * FROM `".$wpdb->prefix."usermeta` WHERE (meta_key = 'first_name' or meta_key='last_name' or meta_key='lab_user_left') and user_id=".$userId  );
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
    }
    
    return $items;
}

/*******************************************************************************************************
 * PARAM
 *******************************************************************************************************/

function lab_admin_param_save($paramType, $paramName)
{
    global $wpdb;
    if ($type == -1) {
      $type = 0;
    }
    return !lab_admin_param_exist($paramType, $paramName);
    if (lab_admin_param_exist($paramType, $paramName)) {
        return false;
    } else {
        $sql = "INSERT INTO `".$wpdb->prefix."lab_params` (`id`, `type_param`, `value`) VALUES (NULL, '".$paramType."', '".$paramName."');";
        $results = $wpdb->get_results($sql);
        return $wpdb->insert_id;
    }
}

function lab_admin_param_exist($paramType, $paramName)
{
    global $wpdb;
    $sql = "SELECT id FROM `".$wpdb->prefix."lab_params` WHERE `type_param` = ".$paramType." AND  `value` = '".$paramName."'";
    $results = $wpdb->get_results($sql);
    return count($results) == 1;
}

function lab_admin_createTable_param() {
    global $wpdb;
    $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."lab_params` (
        `id` bigint UNSIGNED NOT NULL,
        `type_param` bigint UNSIGNED NOT NULL,
        `value` varchar(20) DEFAULT NULL,
        `color` varchar(8) DEFAULT NULL,
      ) ENGINE=InnoDB";
    $wpdb->get_results($sql);
}

function lab_admin_createTable_presence() {
    global $wpdb;
    $sql = "CREATE TABLE `".$wpdb->prefix."lab_presence` (
        `id` bigint NOT NULL AUTO_INCREMENT,
        `user_id` bigint NOT NULL,
        `hour_start` datetime NOT NULL,
        `hour_end` datetime NOT NULL,
        `site` int NOT NULL,
        PRIMARY KEY (`id`)
      ) ENGINE=InnoDB;";
    $wpdb->get_results($sql);
}

function lab_admin_initTable_param() {
    global $wpdb;
    $wpdb->query("DELETE FROM ".$wpdb->prefix."lab_params WHERE ID < 7");
    $sql = "INSERT INTO `".$wpdb->prefix."lab_params` (`id`, `type_param`, `value`) VALUES
            (1, 1, 'PARAM'),
            (2, 1, 'GROUP TYPE'),
            (3, 1, 'KEY TYPE'),
            (4, 1, 'SITE'),
            (5, 1, 'USER FUNCTION'),
            (6, 1, 'MISSION'),
            (7, 1, 'FUNDING'),
            (8, 2, 'Equipe'),
            (9, 2, 'Groupe'),
            (10, 3, 'Clé'),
            (11, 3, 'Badge'),
            (12, 4, 'Luminy'),
            (13, 4, 'I2M'),
            (14, 6, 'Séminaire'),
            (15, 7, 'CNRS'),
            (16, 7, 'AMU');";
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
    $sql = "SELECT id, value, color FROM `".$wpdb->prefix."lab_params` WHERE type_param = 4";
    return $wpdb->get_results($sql);
}

function lab_admin_list_present_user($startDate, $endDate) {
    global $wpdb;
    $sql = "SELECT lp.id, lp.user_id, lp.hour_start, lp.hour_end, lp.site as site_id, p.value as site, um1.meta_value as first_name, um2.meta_value as last_name FROM `".$wpdb->prefix."lab_presence` AS lp JOIN ".$wpdb->prefix."lab_params as p ON p.id=lp.site JOIN ".$wpdb->prefix."usermeta AS um1 ON um1.user_id=lp.user_id JOIN ".$wpdb->prefix."usermeta AS um2 ON um2.user_id=lp.user_id WHERE (lp.`hour_start` BETWEEN '".date("Y-m-d", $startDate)." 00:00:00' AND '".date("Y-m-d", $endDate)." 23:59:59') AND um1.meta_key='first_name' AND um2.meta_key='last_name' ORDER BY lp.user_id, lp.`hour_start`";
    return $wpdb->get_results($sql);
    //return $sql;
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
    if ($paramId < 6 ) {
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
    lab_userMetaData_create_metaKeys("user_phone", "");
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
        $items[] ="<a href=\"$g->url\">" . esc_html($g->group_name) . "</a>";
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
function lab_admin_usermeta_fill_user_slug()
{
    global $wpdb;
    $sql = "SELECT u.id as user_id, um1.meta_value as first_name, um2.meta_value as last_name, um3.umeta_id as id FROM `".$wpdb->prefix."users` AS u JOIN `".$wpdb->prefix."usermeta` as um1 ON um1.user_id=u.ID JOIN `".$wpdb->prefix."usermeta` AS um2 ON um2.user_id=u.ID JOIN `".$wpdb->prefix."usermeta` AS um3 ON um3.user_id=u.ID WHERE um1.meta_key='first_name' AND um2.meta_key='last_name' AND um3.meta_key='lab_user_slug'";
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
    $sql = "CREATE TABLE `".$wpdb->prefix."lab_hal_users` (
        `id` bigint UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
        `hal_id` bigint UNSIGNED NOT NULL,
        `user_id` bigint UNSIGNED NOT NULL
      ) ENGINE=InnoDB";
    return $wpdb->get_results($sql);
}

function lab_hal_get_publication($userId) {
    global $wpdb;
    $sql = "SELECT lh.* FROM `".$wpdb->prefix."lab_hal` as lh JOIN `".$wpdb->prefix."lab_hal_users` AS lhu ON lhu.hal_id=lh.id WHERE lhu.user_id=".$userId;
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

function lab_admin_usermeta_fill_hal_name() {
    global $wpdb;
    $sql = "SELECT u.id as user_id, um1.meta_value as first_name, um2.meta_value as last_name, um3.umeta_id as id FROM `".$wpdb->prefix."users` AS u JOIN `".$wpdb->prefix."usermeta` as um1 ON um1.user_id=u.ID JOIN `".$wpdb->prefix."usermeta` AS um2 ON um2.user_id=u.ID JOIN `".$wpdb->prefix."usermeta` AS um3 ON um3.user_id=u.ID WHERE um1.meta_key='first_name' AND um2.meta_key='last_name' AND um3.meta_key='lab_hal_name'";
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
            return "https://api.archives-ouvertes.fr/search/?authIdHal_s:(".$hal_id.")&fl=docid,citationFull_s,producedDate_tdate,uri_s,title_s,journalTitle_s&sort=producedDate_tdate+desc&wt=json&json.nl=arrarr";
        }
        else {
            return "https://api.archives-ouvertes.fr/search/?q=authLastNameFirstName_s:%22".$hal_name."%22&fl=docid,citationFull_s,producedDate_tdate,uri_s,title_s,journalTitle_s&sort=producedDate_tdate+desc&wt=json&json.nl=arrarr";
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


    $json = lab_do_common_curl_call($url);
    $c =count($json->response->docs);
    $display = false;
    for ($i = 0; $i < $c; $i++) {
        $keep = false;
        $docId = $json->response->docs[$i]->docid;
        $citation = $json->response->docs[$i]->citationFull_s;
        $producedDate = strtotime($json->response->docs[$i]->producedDate_tdate);
        $title = $json->response->docs[$i]->title_s[0];
        $url = $json->response->docs[$i]->uri_s;
        if (isset($json->response->docs[$i]->journalTitle_s))
        {
            $journal = $json->response->docs[$i]->journalTitle_s;
        }
        else {
            $journal = null;
        }

        $display = $docId == "2508732";
        

        if (!array_key_exists ($docId, $docIds)) {
            $id = saveHalProduction($docId, $citation, date('Y-m-d', $producedDate), $title, $url, $journal);
            //echo "id=".$id."\n";
            $docIds[$docId] = $id;
            $halId = $id;
            if ($display) {
                echo "[$userId] La clef n'existe pas on la crée docIds[".$docId."]=".$id."\n";
            }
            //echo "La clef n'existe pas on la crée docIds[".$docId."]=".$id."\n";
        }
        else {
            if ($display) {
                echo "[".$userId."] La clef existe deja\n";
            }
        }
        saveHalUsers($userId, $docIds[$docId]);
        

        //$content .= '<li>' . $json->response->docs[$i]->citationFull_s . '</li>';
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
    drop_table("lab_hal_users");
    drop_table("lab_groups");
    drop_table("lab_presence");
    drop_table("lab_invitations");
    drop_table("lab_guests");
    drop_table("lab_invite_comments");
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
function lab_admin_presence_save($userId, $dateOpen, $dateEnd, $siteId) {
    global $wpdb;
    return $wpdb->insert($wpdb->prefix."lab_presence", array("user_id"=>$userId, "hour_start"=>$dateOpen, "hour_end"=>$dateEnd, "site"=>$siteId));
}

function lab_admin_presence_delete($presenceId, $userId) {
    global $wpdb;
    return $wpdb->delete($wpdb->prefix."lab_presence", array("id"=>$presenceId,"user_id"=>$userId));
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