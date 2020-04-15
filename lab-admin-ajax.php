<?php

include 'lab-admin-core.php';


/**
 * Fonction qui répond à la requete ajax de recherche d'evenement
 **/
function lab_admin_search_event() {
    $search = $_POST['search'];
    $title  = $search["term"];

    $sql = 'SELECT post_id, `event_name`,`event_start_date` FROM `wp_em_events` AS ee LEFT JOIN `wp_term_relationships` AS tr ON tr.`object_id`=ee.post_id WHERE tr.`object_id` IS NULL AND `event_name` LIKE \'%'.$title.'%\' LIMIT 30';
    global $wpdb;
    $results = $wpdb->get_results($sql);
    $nbResult = $wpdb->num_rows;
    $items = array();
  
    $url = esc_url(home_url('/'));
    foreach ( $results as $r )
    {
      $items[] = array(label=>$r->event_name." ".date("d/m/Y", strtotime($r->event_start_date)),value=>$r->post_id);
    }
    wp_send_json_success( $items );
}


/********************************************************************************************
 * GROUP
 ********************************************************************************************/

function group_delete_substitutes() 
{
  $id = $_POST['id'];
  global $wpdb;
  wp_send_json_success($wpdb->delete('wp_lab_group_substitutes', array('id' => $id)));
}

function group_add_substitutes()
{
  $userId = $_POST['userId'];
  $groupId = $_POST['groupId'];
  global $wpdb;
  wp_send_json_success($wpdb->insert('wp_lab_group_substitutes', array('group_id'=>$groupId,'substitute_id'=>$userId)));

}

function group_load_substitutes()
{
  $id = $_POST['id'];
  $sql = "SELECT lgs.id AS id, um1.meta_value AS last_name, um2.meta_value AS first_name FROM `wp_lab_group_substitutes` AS lgs JOIN wp_usermeta AS um1 ON um1.user_id=lgs.substitute_id JOIN wp_usermeta AS um2 ON um2.user_id=lgs.substitute_id WHERE lgs.`group_id`=33 AND um1.meta_key='last_name' AND um2.meta_key='first_name'";
  global $wpdb;
  $results = $wpdb->get_results($sql);
  $items = array();
  foreach ( $results as $r )
  {
    $items[] = array(id=>$r->id, first_name=>$r->first_name, last_name=>$r->last_name, );
  }
  wp_send_json_success( $items );
}

function lab_admin_search_group_acronym() {
  $ac = $_POST['ac'];
  $sql = "SELECT group_name FROM `wp_lab_groups` WHERE acronym = '".$ac."';";
  global $wpdb;
  $results = $wpdb->get_results($sql);
  $items = array();
  foreach ( $results as $r )
  {
    array_push($items,$r->group_name);
  }
  if (count($items)) {
    wp_send_json_error( $items );
  }
  else {
    wp_send_json_success();
  }
}
function lab_createGroupTable() {
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
    //echo $sql;
  global $wpdb;
  $wpdb->get_results($sql);
}
function lab_group_createRoot() {
  $sql = "INSERT INTO `wp_lab_groups` (`id`, `acronym`, `group_name`, `chief_id`, `group_type`, `parent_group_id`) VALUES (NULL, 'root', 'root', '1', '0', NULL);";
  //echo $sql;
  global $wpdb;
  $results = $wpdb->get_results($sql);
}
function lab_group_createGroup() {
  $name = $_POST['name'];
  $acronym = $_POST['acronym'];
  $chief_id = $_POST['chief_id'];
  $parent = $_POST['parent'];
  $type = $_POST['type'];
  $sql = "INSERT INTO `wp_lab_groups` (`id`, `acronym`, `group_name`, `chief_id`, `group_type`, `parent_group_id`) VALUES (NULL, '".$acronym."', '".$name."', '".$chief_id."', '".$type."', ".($parent == 0 ? "NULL" : "'".$parent."'").");";
  global $wpdb;
  $wpdb->get_results($sql);
}
/**
 * Fonction qui répond a la requete d'un recherche par nom de groupe
 */
function lab_admin_group_search() {
    $search = $_POST['search'];
    $groupName  = $search["term"];

    $sql = "SELECT * FROM `wp_lab_groups` WHERE `group_name` LIKE '%".$groupName."%' ";
    global $wpdb;
    $results = $wpdb->get_results($sql);
    $items = array();
    $url = esc_url(home_url('/'));
    foreach ( $results as $r )
    {
      $items[] = array(label=>$r->group_name, value=>$r->id, id=>$r->id, group_name=>$r->group_name, group_type=>$r->group_type, acronym=>$r->acronym, chief_id=>$r->chief_id, parent_group_id=>$r->parent_group_id);
    }
    wp_send_json_success( $items ); 
}

function lab_admin_group_delete(){
    $group_id = $_POST['id'];
    global $wpdb;
    $wpdb->delete('wp_lab_group_substitutes', array('group_id' => $group_id));
    $wpdb->delete('wp_lab_groups', array('id' => $group_id));
    wp_send_json_success();
}

function lab_group_editGroup() {
  $id = $_POST['groupId'];
  $acronym = $_POST['acronym'];
  $groupName = $_POST['groupName'];
  $chiefId = $_POST['chiefId'];
  $parent = $_POST['parent'];
  $type = $_POST['group_type'];

  $sql = "UPDATE `wp_lab_groups` SET `group_name` = '$groupName', `acronym` = '$acronym',
   `chief_id` = '$chiefId', `group_type` = '$type', `parent_group_id` = '$parent'
    WHERE id= '$id';";
  global $wpdb;
  
  wp_send_json_success($wpdb->get_results($sql));
}





/********************************************************************************************
 * PARAMS
 ********************************************************************************************/
function lab_admin_param_create_table() {
  $sql = "CREATE TABLE `wp_lab_params`(
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `type_param` BIGINT UNSIGNED NOT NULL,
    `value` varchar(20),
    PRIMARY KEY(`id`)) ENGINE = INNODB;";
  global $wpdb;
  $results = $wpdb->get_results($sql);
  $sql = "INSERT INTO `wp_lab_params` (`id`, `type_param`, `value`) VALUES (NULL, 1, 'PARAM');";
  $results = $wpdb->get_results($sql);
  wp_send_json_success();
}

function lab_admin_param_save() {
  $type   = $_POST['type'];
  $value = $_POST['value'];
  global $wpdb;
  if ($type == -1) {
    $type = 0;
  }
  $sql = "INSERT INTO `wp_lab_params` (`id`, `type_param`, `value`) VALUES (NULL, '".$type."', '".$value."');";
  $results = $wpdb->get_results($sql);
  wp_send_json_success();
}

function lab_admin_param_load_param_type() {
  global $wpdb;
  $sql = "SELECT id, value FROM `wp_lab_params` WHERE type_param = 1";
  $results = $wpdb->get_results($sql);
  return $results;
}

function lab_admin_param_load_type() {
  wp_send_json_success(lab_admin_param_load_param_type());
}

function lab_admin_param_delete() {
  $paramId   = $_POST['id'];
  if (isset($paramId) && !empty($paramId)) {
    wp_send_json_success(lab_admin_param_delete_by_id($paramId));
  }
  else {
    wp_send_json_error("No id send");
  }
}

function lab_admin_param_search_value() {
  $search = $_POST['search'];
  $paramValue  = $search["term"];
  if (isset($paramValue) && !empty($paramValue)) {
    $results = lab_admin_param_search_by_value($paramValue);
    $items = array();
  
    foreach ($results as $r) {
      $items[] = array(label => $r->value, value => $r->id, type=>$r->type_param);
    }
    wp_send_json_success($items);
  }
  else {
    wp_send_json_error("No param send");
  }
}

function lab_admin_search_username()
{
  $search = $_POST['search'];
  $name  = $search["term"];
  wp_send_json_success(lab_admin_firstname_lastname($search, $name));
}

function lab_admin_search_username2()
{
  $search = $_POST['search'];
  $name  = $search["term"];
  wp_send_json_success(lab_admin_firstname_lastname2($name));
}

function lab_admin_usermeta_names()
{
  $search = $_POST['search'];
  $userId  = $search["term"];
  //wp_send_json_success($userId);
  wp_send_json_success(lab_admin_username_get($userId));
}
/********************************************************************************************
 * EVENT
 ********************************************************************************************/

/**
 *
 **/
function lab_admin_save_event_actegory()
{
  $postId  = $_POST["postId"];
  $categories  = $_POST["categoryId"];
  global $wpdb;
  foreach ($categories as $id => $categoryId) {
    $table = $wpdb->prefix . 'term_relationships';
    $data = array('object_id' => $postId, 'term_taxonomy_id' => $categoryId);
    $format = array('%d', '%d');
    $wpdb->insert($table, $data, $format);
  }
  wp_send_json_success(1);
}
/**
 *
 **/
function lab_admin_get_event_category()
{
  $postId  = $_POST["postId"];
  $sql = 'SELECT p.ID, p.`post_title`, p.`post_date`, t.term_id, t.name, t.slug FROM `wp_posts` AS p JOIN `wp_term_relationships` AS tr ON tr.`object_id`=p.ID JOIN `wp_term_taxonomy` AS tt ON tt.`term_taxonomy_id`=tr.`term_taxonomy_id` JOIN `wp_terms` AS t ON t.term_id=tt.term_id WHERE p.ID=' . $postId;
  global $wpdb;

  $results = $wpdb->get_row($wpdb->prepare($sql));

  wp_send_json_success($results);
  //wp_send_json_success( $sql );
}

/********************************************************************************************
 * USER
 ********************************************************************************************/
function lab_admin_search_user_metadata()
{
  $userId = $_POST["userId"];
  //wp_send_json_success(lab_usermeta_lab_check_and_create($userId));
  lab_usermeta_lab_check_and_create($userId);
  //  var_dump($_POST);
  $sql    = "SELECT * FROM wp_usermeta WHERE user_id=".$userId;
  global $wpdb;
  $results = $wpdb->get_results($sql);
  $items = array();

  foreach ($results as $r) {
    $items[$r->meta_key] = array(id => $r->umeta_id, key => $r->meta_key, value => $r->meta_value);
  }
  wp_send_json_success($items);
}

function lab_admin_search_user_email()
{
  $search = $_POST['search'];
  $email  = $search["term"];
  $sql = "SELECT ID, user_email FROM `wp_users`  WHERE user_email LIKE '%" . $email . "%'";
  global $wpdb;
  $results = $wpdb->get_results($sql);
  $nbResult = $wpdb->num_rows;
  $items = array();

  $url = esc_url(home_url('/'));
  foreach ($results as $r) {
    $items[] = array(label => $r->user_email, value => $r->ID);
  }
  wp_send_json_success($items);
}

function lab_admin_search_user()
{
  $search = $_POST['search'];
  $email  = $search["term"];


  $sql = "SELECT um.* FROM `wp_users` AS u JOIN `wp_usermeta` AS um ON u.`ID`=um.user_id WHERE u.user_email='" . $email . "'";
  global $wpdb;
  $results = $wpdb->get_results($sql);
  $nbResult = $wpdb->num_rows;
  $items = array();

  $item["user_id"] = $result[0]->user_id;
  foreach ($results as $r) {
    $items[$r->meta_key] = $r->meta;
  }
  wp_send_json_success($items);
}

function lab_admin_update_user_metadata_db()
{
  $sql = "SELECT DISTINCT user_id FROM `wp_usermeta` AS m WHERE user_id NOT IN ( SELECT 1 FROM wp_usermeta AS e WHERE e.user_id = m.user_id AND meta_key = 'lab_user_left' )";
  global $wpdb;
  $results = $wpdb->get_results($sql);
  $nbResult = $wpdb->num_rows;
  $items = array();
  foreach ($results as $r) {
    $user_id = $r->user_id;
    $items[] =  $user_id;
    $wpdb->insert('wp_usermeta', array(
      'umeta_id' => NULL,
      'user_id' => $user_id,
      'meta_key' => 'lab_user_left',
      'meta_value' => NULL,
    ));
  }
  wp_send_json_success($items);
}

function lab_admin_update_user_metadata()
{
  $userMetaDataId =  $_POST["userMetaId"];
  $dateLeft       = $_POST["dateLeft"];
  lab_usermeta_update_lab_left_key($userMetaDataId, $dateLeft);
  wp_send_json_success("");
}

function lab_usermeta_update_lab_left_key($usermetaId, $left)
{
  global $wpdb;
  $sql = "";
  if ($left != null || !empty($left)) {
    $sql = "UPDATE `wp_usermeta` SET `meta_value` = '" . $left . "' WHERE `wp_usermeta`.`umeta_id` = " . $usermetaId;
  } else {
    $sql = "UPDATE `wp_usermeta` SET `meta_value` = NULL WHERE `wp_usermeta`.`umeta_id` = " . $usermetaId;
  }
  $sql = $wpdb->prepare($sql);
  $wpdb->query($sql);
}

function lab_usermeta_lab_check_and_create($userId)
{
  // si la clef n'existe pas on la cree
  if (!lab_usermeta_lab_left_key_exist($userId)) {
    return lab_usermeta_create_left_key($userId);
  }
  return -1;
}

function lab_usermeta_create_left_key($userId)
{
  global $wpdb;
  $sql = "INSERT INTO `wp_usermeta` (`umeta_id`, `user_id`, `meta_key`, `meta_value`) VALUES (NULL, '" . $userId . "', 'lab_user_left', NULL)";
  $wpdb->insert('wp_usermeta', array(
    'umeta_id' => NULL,
    'user_id' => $userId,
    'meta_key' => 'lab_user_left',
    'meta_value' => NULL,
  ));
  return $wpdb->insert_id;
}

function lab_usermeta_lab_left_key_exist($userId)
{
  if (!isset($userId) || $userId == NULL) {
    return false;
  }
  $sql = "SELECT * FROM `wp_usermeta` WHERE `user_id` = " . $userId . " AND `meta_key` = 'lab_user_left'";
  global $wpdb;
  $results = $wpdb->get_results($sql);
  $nbResult = $wpdb->num_rows;
  //return $nbResult;
  return $nbResult == 1;
}


/********************************************************************************************
 * TEST
 ********************************************************************************************/

function lab_admin_test()
{ 
  $group_id = 1;

  global $wpdb;
  $wpdb->delete('wp_lab_groups', array('id' => $group_id));
  //$user = 1;
  //wp_send_json_success("UM()->options()->get( 'author_redirect' ) : " . UM()->options()->get('author_redirect') . " /  um_fetch_user($user) : " . um_fetch_user(1));
}


/********************************************************************************************
 * GROUPS
 ********************************************************************************************/
function lab_admin_group_availableAc() {
  //Vérifie la disponibilité de l'acronyme
  $res = lab_admin_search_group_by_acronym($_POST['ac']);
  if (count($res)) {
    wp_send_json_error($res);
    return;
  }
  wp_send_json_success();
}
function lab_admin_group_createReq() {
  $res = lab_admin_group_create($_POST['name'],$_POST['acronym'],$_POST['chief_id'],$_POST['parent'],$_POST['type']);
  if (strlen($res)==0) {
    wp_send_json_success(lab_admin_search_group_by_acronym($_POST['acronym']));
    return;
  }
  wp_send_json_error($res);
}
function lab_admin_group_createRoot() {
  $res = lab_admin_group_create('root', 'root', '1', '0', '0');
  if (strlen($res)==0) {
    wp_send_json_success();
    return;
  }
  wp_send_json_error($res);
}
function lab_admin_group_subs_addReq() {
  $res = lab_admin_group_subs_add($_POST['id'],$_POST['subList']);
  if (strlen($res)==0) {
    wp_send_json_success();
    return;
  }
  wp_send_json_error($res);
}
/********************************************************************************************
 * KeyRing
 ********************************************************************************************/
function lab_keyring_create_keyReq() {
  $res = lab_keyring_create_key($_POST['number'],$_POST['office'],$_POST['type'],$_POST['brand'],$_POST['site'],$_POST['commentary']); 
  if (strlen($res)==0) {
    wp_send_json_success();
    return;
  }
  wp_send_json_error($res);
}