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

function lab_admin_param_delete_by_id($paramId) {
    global $wpdb;
    // can't delete param Id 1, because it is the default parameter
    if ($paramId != 1) {
        return $wpdb->delete('wp_lab_params', array('id' => $paramId));
    }
}

function lab_admin_param_search_by_value($value) {
    global $wpdb;
    $sql = "SELECT * FROM wp_lab_params WHERE `value` LIKE '%".$value."%'";
    return $wpdb->get_results($sql);
}

function lab_admin_firstname_lastname2($name){
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
    $items[] = array(label => $r->first_name . " " . $r->last_name , firstname => $r->first_name , lastname => $r->last_name , user_id => $r->id);
  }
  return $items;
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
    $sql = "SHOW TABLES LIKE '".$tableName."';";
    global $wpdb;
    $results = $wpdb->get_results($sql);
    if (count($results)) {
    return true;
    }
    return false;
  }
  
/********************************************************************************************
 * GROUPS
 ********************************************************************************************/

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
    $sql = "CREATE TABLE `".$wpdb->prefix."lab_users_groups` (
        `id` bigint UNSIGNED NOT NULL,
        `group_id` bigint UNSIGNED NOT NULL,
        `user_id` bigint UNSIGNED NOT NULL
      ) ENGINE=InnoDB;";
    $wpdb->get_results($sql);
}

function lab_admin_createGroupTable() {
    global $wpdb;
    $sql = "CREATE TABLE `".$wpdb->prefix."lab_groups`(
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `acronym` varchar(20) UNIQUE,
        `group_name` varchar(255) NOT NULL,
        `chief_id` BIGINT UNSIGNED NOT NULL,
        `group_type` TINYINT NOT NULL,
        `parent_group_id` BIGINT UNSIGNED,
        PRIMARY KEY(`id`),
        FOREIGN KEY(`chief_id`) REFERENCES `".$wpdb->prefix."users`(`ID`),
        FOREIGN KEY(`parent_group_id`) REFERENCES `".$wpdb->prefix."lab_groups`(`id`)) ENGINE = INNODB;";
    $wpdb->get_results($sql);
}
function lab_admin_createSubTable() {
    global $wpdb;
    $sql = "CREATE TABLE `".$wpdb->prefix."lab_group_substitutes`(
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `group_id` BIGINT UNSIGNED NOT NULL ,
        `substitute_id` BIGINT UNSIGNED NOT NULL,
        PRIMARY KEY(`id`),
        FOREIGN KEY(`substitute_id`) REFERENCES `".$wpdb->prefix."users`(`ID`),
        FOREIGN KEY(`group_id`) REFERENCES `".$wpdb->prefix."lab_groups`(`id`)) ENGINE = INNODB;";
    $wpdb->get_results($sql);
}

function lab_admin_group_create($name,$acronym,$chief_id,$parent,$type) {
    //$sql = "INSERT INTO `".$wpdb->prefix."lab_groups` (`id`, `acronym`, `group_name`, `chief_id`, `group_type`, `parent_group_id`) VALUES (NULL, '".$acronym."', '".$name."', '".$chief_id."', '".$type."', ".($parent == 0 ? "NULL" : "'".$parent."'").");";
    global $wpdb;
    $wpdb->hide_errors();
    if ( $wpdb->insert(
        'wp_lab_groups',
        array(
            'acronym' => $acronym,
            'group_name' => $name,
            'chief_id' => $chief_id,
            'group_type' => $type,
            'parent_group_id' => $parent == 0 ? NULL : $parent
        )
    ) ) {
        return;
    } else {
        return $wpdb -> last_error;
    }
}

function lab_admin_group_subs_add($id,$list) {
    global $wpdb;
    $wpdb->hide_errors();
    foreach ($list as $r) {
        $sql = "INSERT INTO `".$wpdb->prefix."lab_group_substitutes` (`id`, `group_id`, `substitute_id`) VALUES (NULL, '".$id."', '".$r."'); ";
        if (!$wpdb->insert(
            'wp_lab_group_substitutes',
            array(
                'group_id' => $id,
                'substitute_id' => $r)) 
            ) {
            return $wpdb -> last_error;
        }
    }
    return;
}


/********************************************************************************************
 * KeyRing
 ********************************************************************************************/
function lab_keyring_createTable_keys() {
    global $wpdb;
    $sql = "CREATE TABLE `".$wpdb->prefix."lab_keys` (
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
    $sql = "CREATE TABLE `".$wpdb->prefix."lab_key_loans` (
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
        'wp_lab_keys',
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
        'wp_lab_key_loans',
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
        'wp_lab_keys',
        $fields,
        array('id' => $id)
    );
}
function lab_keyring_delete_key($id) {
    global $wpdb;
    return $wpdb->delete(
        'wp_lab_keys',
        array('id' => $id)
    );
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

function lab_userMetaData_create_metaKeys($metadataKey, $defaultValue) {
    $userIds = userMetaData_get_userId_with_no_key($metadataKey);
    //return $userIds;
    $errors = array();
    $ids = array();
    foreach($userIds as $userId) {
        if (lab_userMetaData_new_key($userId, $metadataKey, $defaultValue) == false) {
            $errors[] = $wpdb->last_error();
        }
    }
    if (count($errors) > 0) {
        return $errors;
    }
    return true;
}

function lab_userMetaData_new_key($userId, $metadataKey, $defaultValue) {
    global $wpdb;
    if ($defaultValue == 'null') {
        $defaultValue = null;
    }
    $r = $wpdb->insert('wp_usermeta', array('umeta_id'=>null, 'user_id'=>$userId, 'meta_key'=>LAB_META_PREFIX.$metadataKey,'meta_value'=>$defaultValue));
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
    return $wpdb->delete('wp_usermeta', array("meta_key"=>$metadataKey));
}

function userMetaData_exist_metakey($metadataKey) {
    if (substr($metadataKey, 0, strlen(LAB_META_PREFIX)) !== LAB_META_PREFIX) {
        $metadataKey = LAB_META_PREFIX.$metadataKey;
    }
    global $wpdb;
    //return "SELECT umeta_id FROM `".$wpdb->prefix."usermeta` WHERE `meta_key` = '".$metadataKey."'";
    $results = $wpdb->get_results("SELECT umeta_id FROM `".$wpdb->prefix."usermeta` WHERE `meta_key` = '".$metadataKey."'");
    return count($results) > 0;
}
/**************************************************************************************************
 * HAL
 *************************************************************************************************/
function createTableHal() {
    global $wpdb;
    $sql = "CREATE TABLE `".$wpdb->prefix."lab_hal` (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `user_id` int NOT NULL,
        `docid` int NOT NULL COMMENT 'docid issus de hal',
        `citationFull_s` varchar(1000) NOT NULL,
        `title` varchar(200) DEFAULT NULL,
        `url` varchar(200) DEFAULT NULL,
        `producedDate_tdate` date,
        PRIMARY KEY(`id`)
      ) ENGINE=InnoDB";
    return $wpdb->get_results($sql);
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

function usermeta_fill_hal_field() {
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
            return "https://api.archives-ouvertes.fr/search/?authIdHal_s:(".$hal_id.")&fl=docid,citationFull_s,producedDate_tdate,uri_s,title_s&sort=producedDate_tdate+desc&wt=json&json.nl=arrarr";
        }
        else {
            return "https://api.archives-ouvertes.fr/search/?q=authLastNameFirstName_s:%22".$hal_name."%22&fl=docid,citationFull_s,producedDate_tdate,uri_s,title_s&sort=producedDate_tdate+desc&wt=json&json.nl=arrarr";
        }
    }
}

function saveHalProduction($userId, $docId, $citation, $productionDate, $title, $url) {
    global $wpdb;
    return $wpdb->insert($wpdb->prefix."lab_hal", array('user_id'=>$userId, 'docid'=>$docId, 'citationFull_s'=>$citation, 'producedDate_tdate'=>$productionDate, 'title'=>$title, 'url'=>$url));
}

/**
 * Download HAL info for all user in db
 */
function hal_download_all()
{
    global $wpdb;
    $sql = "SELECT id FROM `".$wpdb->prefix."users`";
    $results = $wpdb->get_results($sql);
    foreach($results as $r) 
    {
        hal_download($r->id);
    }
}

function hal_download($userId) {
    $url = get_hal_url($userId);
    
    $json = do_common_curl_call($url);
    $c =count($json->response->docs);
    for ($i = 0; $i < $c; $i++) {
        $docId = $json->response->docs[$i]->docid;
        $citation = $json->response->docs[$i]->citationFull_s;
        $producedDate = strtotime($json->response->docs[$i]->producedDate_tdate);
        $title = $json->response->docs[$i]->title_s[0];
        $url = $json->response->docs[$i]->uri_s;
        
        saveHalProduction($userId, $docId, $citation, date('Y-m-d', $producedDate), $title, $url);
        //$content .= '<li>' . $json->response->docs[$i]->citationFull_s . '</li>';
    }
    return $url;
}

function delete_hal_table() {
    global $wpdb;
    $wpdb->query("TRUNCATE TABLE ".$wpdb->prefix."lab_hal");//delete( $wpdb->prefix."lab_hal", array());
    $wpdb->get_results("ALTER TABLE ".$wpdb->prefix."lab_hal AUTO_INCREMENT = 1");
    return true;
}

/**
 * @param $url
 * @return object
 */
/*
function do_common_curl_call($url) {
    $ch = curl_init($url);
    // Options
    $options = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => array('Content-type: application/json'),
        CURLOPT_TIMEOUT => 10,
        CURLOPT_USERAGENT => "HAL Plugin Wordpress " . version
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
function lab_keyring_setKeyAvailable($id,$available) {
    global $wpdb;
    $wpdb->hide_errors();
    if ($wpdb->update(
        'wp_lab_keys',
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
        'wp_lab_key_loans',
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
function lab_keyring_find_oldLoans($key_id) {
    global $wpdb;
    $sql = "SELECT * from `".$wpdb->prefix."lab_key_loans` WHERE `key_id`=".$key_id." ORDER BY `start_date` DESC";
    return $wpdb->get_results($sql);
}
function lab_hal_getLastArticles($number,$group) {
    global $wpdb;
    if ($group == 0) {
        $sql = "SELECT * from `".$wpdb->prefix."lab_hal` ORDER BY `producedDate_tdate` DESC LIMIT ".$number.";";
    } else {
        $sql = "SELECT * FROM `".$wpdb->prefix."lab_hal` WHERE user_id IN (SELECT user_id from `".$wpdb->prefix."lab_users_groups` WHERE `".$wpdb->prefix."lab_users_groups`.`group_id` = ".$group.") ORDER BY `producedDate_tdate` DESC LIMIT ".$number;
    }
    return $wpdb->get_results($sql);
}