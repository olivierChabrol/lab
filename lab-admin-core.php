<?php

function lab_admin_username_get($userId) {
    global $wpdb;
    $results = $wpdb->get_results( "SELECT * FROM `wp_usermeta` WHERE (meta_key = 'first_name' or meta_key='last_name') and user_id=".$userId  );
    $items = array();
    $items["id"] = $userId;

    foreach ( $results as $r )
    {
        if ($r->meta_key == 'first_name')
            $items['first_name'] = $r->meta_value;
        if ($r->meta_key == 'last_name')
            $items['last_name'] = $r->meta_value;
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
    $sql = "SELECT group_name,id FROM `wp_lab_groups` WHERE acronym = '".$ac."';";
    global $wpdb;
    $results = $wpdb->get_results($sql);
    $items = array();
    foreach ( $results as $r )
    {
      array_push($items,$r);
    }
    return $items;
}

function lab_admin_createGroupTable() {
    $sql = "CREATE TABLE `wp_lab_groups`(
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `acronym` varchar(20) UNIQUE,
        `group_name` varchar(255) NOT NULL,
        `chief_id` BIGINT UNSIGNED NOT NULL,
        `group_type` TINYINT NOT NULL,
        `parent_group_id` BIGINT UNSIGNED,
        PRIMARY KEY(`id`),
        FOREIGN KEY(`chief_id`) REFERENCES `wp_users`(`ID`),
        FOREIGN KEY(`parent_group_id`) REFERENCES `wp_lab_groups`(`id`)) ENGINE = INNODB;";
    global $wpdb;
    $wpdb->get_results($sql);
}
function lab_admin_createSubTable() {
    $sql = "CREATE TABLE `wp_lab_group_substitutes`(
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `group_id` BIGINT UNSIGNED NOT NULL ,
        `substitute_id` BIGINT UNSIGNED NOT NULL,
        PRIMARY KEY(`id`),
        FOREIGN KEY(`substitute_id`) REFERENCES `wp_users`(`ID`),
        FOREIGN KEY(`group_id`) REFERENCES `wp_lab_groups`(`id`)) ENGINE = INNODB;";
    global $wpdb;
    $wpdb->get_results($sql);
}

function lab_admin_group_create($name,$acronym,$chief_id,$parent,$type) {
    //$sql = "INSERT INTO `wp_lab_groups` (`id`, `acronym`, `group_name`, `chief_id`, `group_type`, `parent_group_id`) VALUES (NULL, '".$acronym."', '".$name."', '".$chief_id."', '".$type."', ".($parent == 0 ? "NULL" : "'".$parent."'").");";
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
        $sql = "INSERT INTO `wp_lab_group_substitutes` (`id`, `group_id`, `substitute_id`) VALUES (NULL, '".$id."', '".$r."'); ";
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
    $sql = "CREATE TABLE `wp_lab_keys` (
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
    global $wpdb;
    $wpdb->get_results($sql);
}
function lab_keyring_createTable_loans() {
    $sql = "CREATE TABLE `wp_lab_key_loans` (
        `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
        `key_id` bigint UNSIGNED NOT NULL,
        `user_id` bigint UNSIGNED NOT NULL,
        `referent_id` bigint UNSIGNED NOT NULL,
        `start_date` date DEFAULT NULL,
        `end_date` date DEFAULT NULL,
        `commentary` text,
        PRIMARY KEY(`id`),
        FOREIGN KEY (`user_id`) REFERENCES `wp_users` (`ID`),
        FOREIGN KEY (`referent_id`) REFERENCES `wp_users` (`ID`)
      ) ENGINE=InnoDB;";
    global $wpdb;
    $wpdb->get_results($sql);
}