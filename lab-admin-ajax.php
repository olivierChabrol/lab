<?php

include 'lab-admin-core.php';
include 'lib/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Fonction qui répond à la requete ajax de recherche d'evenement
 **/
function lab_admin_search_event() {
    $search = $_POST['search'];
    $title  = $search["term"];

    $sql = "SELECT post_id, `event_name`,`event_start_date` FROM `".$wpdb->prefix."em_events` AS ee LEFT JOIN `".$wpdb->prefix."term_relationships` AS tr ON tr.`object_id`=ee.post_id WHERE tr.`object_id` IS NULL AND `event_name` LIKE \'%'.$title.'%\' LIMIT 30";
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
 * PARAMS
 ********************************************************************************************/
function lab_admin_param_create_table() {
  lab_admin_createTable_param();
  lab_admin_initTable_param();
  wp_send_json_success();
}

function lab_admin_ajax_param_save() {
  $paramId = $_POST['id'];
  $type  = $_POST['type'];
  $value = $_POST['value'];
  $color = $_POST['color'];
  if (!isset($paramId) || $paramId == "")
  {
    $paramId = null;
  }
  
    
  $ok = lab_admin_param_save($type, $value, $color, $paramId);
  if ($ok) {
    wp_send_json_success($ok);
  }
  else
  {
    wp_send_json_error(sprintf(__("A param key with '%1s' already exist in db", "lab"), $value));
  }
}

function lab_admin_param_load_param_type() {
  global $wpdb;
  $sql = "SELECT id, value FROM `".$wpdb->prefix."lab_params` WHERE type_param = 1";
  $results = $wpdb->get_results($sql);
  return $results;
}

function lab_admin_param_load_type() {
  wp_send_json_success(lab_admin_param_load_param_type());
}

function lab_admin_param_delete() {
  $paramId   = $_POST['id'];
  if (isset($paramId) && !empty($paramId)) {
    $deleteOk = lab_admin_param_delete_by_id($paramId);
    if ($deleteOk) {
      wp_send_json_success();
    }
    else {
      wp_send_json_error(__("Cant delete system param", "lab"));
    }
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
      $items[] = array("label" => $r->value, "value" => $r->id, "type"=>$r->type_param, "color"=>$r->color);
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
  wp_send_json_success(lab_admin_username_get($userId));
}
function lab_admin_usermeta_update_phone()
{
  wp_send_json_success(lab_usermeta_copy_existing_phone());
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
  $sql = "SELECT p.ID, p.`post_title`, p.`post_date`, t.term_id, t.name, t.slug FROM `".$wpdb->prefix."posts` AS p JOIN `".$wpdb->prefix."term_relationships` AS tr ON tr.`object_id`=p.ID JOIN `".$wpdb->prefix."term_taxonomy` AS tt ON tt.`term_taxonomy_id`=tr.`term_taxonomy_id` JOIN `".$wpdb->prefix."terms` AS t ON t.term_id=tt.term_id WHERE p.ID=" . $postId;
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
  $sql = "SELECT ID, user_email FROM `".$wpdb->prefix."users`  WHERE user_email LIKE '%" . $email . "%'";
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


  $sql = "SELECT um.* FROM `".$wpdb->prefix."users` AS u JOIN `".$wpdb->prefix."usermeta` AS um ON u.`ID`=um.user_id WHERE u.user_email='" . $email . "'";
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
  $sql = "SELECT DISTINCT user_id FROM `".$wpdb->prefix."usermeta` AS m WHERE user_id NOT IN ( SELECT 1 FROM `".$wpdb->prefix."usermeta` AS e WHERE e.user_id = m.user_id AND meta_key = 'lab_user_left' )";
  global $wpdb;
  $results = $wpdb->get_results($sql);
  $nbResult = $wpdb->num_rows;
  $items = array();
  foreach ($results as $r) {
    $user_id = $r->user_id;
    $items[] =  $user_id;
    $wpdb->insert($wpdb->prefix.'usermeta', array(
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
  $userId       =  $_POST["userId"];
  $dateLeft     = $_POST["dateLeft"];
  $userFunction = $_POST["function"];
  $userLocation = $_POST["location"];
  $officeNumber = $_POST["officeNumber"];
  $officeFloor = $_POST["officeFloor"];
  lab_usermeta_update($userId, $dateLeft, $userFunction, $userLocation, $officeNumber, $officeFloor);
  wp_send_json_success("");
}

function lab_usermeta_update($userId, $left, $userFunction, $userLocation, $officeNumber, $officeFloor)
{
  global $wpdb;
  $sql = "";
  if ($left != null || !empty($left)) {
    $sql = "UPDATE `".$wpdb->prefix."usermeta` SET `meta_value` = '" . $left . "' WHERE `user_id` = " . $userId. " AND meta_key='lab_user_left'";
  } else {
    $sql = "UPDATE `".$wpdb->prefix."usermeta` SET `meta_value` = NULL WHERE `user_id` = " . $userId. " AND meta_key='lab_user_left'";
  }
  $sql = $wpdb->prepare($sql);
  $wpdb->query($sql);
  $wpdb->update($wpdb->prefix."usermeta", array("meta_value"=>$userFunction),array("user_id"=>$userId, "meta_key"=>"lab_user_function"));
  $wpdb->update($wpdb->prefix."usermeta", array("meta_value"=>$userLocation),array("user_id"=>$userId, "meta_key"=>"lab_user_location"));
  $wpdb->update($wpdb->prefix."usermeta", array("meta_value"=>$officeNumber),array("user_id"=>$userId, "meta_key"=>"lab_user_office_number"));
  $wpdb->update($wpdb->prefix."usermeta", array("meta_value"=>$officeFloor),array("user_id"=>$userId, "meta_key"=>"lab_user_office_floor"));
}

function lab_usermeta_update_lab_left_key($usermetaId, $left)
{
  global $wpdb;
  $sql = "";
  if ($left != null || !empty($left)) {
    $sql = "UPDATE `".$wpdb->prefix."usermeta` SET `meta_value` = '" . $left . "' WHERE `".$wpdb->prefix."usermeta`.`umeta_id` = " . $usermetaId;
  } else {
    $sql = "UPDATE `".$wpdb->prefix."usermeta` SET `meta_value` = NULL WHERE `".$wpdb->prefix."usermeta`.`umeta_id` = " . $usermetaId;
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
  $sql = "INSERT INTO `".$wpdb->prefix."usermeta` (`umeta_id`, `user_id`, `meta_key`, `meta_value`) VALUES (NULL, '" . $userId . "', 'lab_user_left', NULL)";
  $wpdb->insert($wpdb->prefix.'usermeta', array(
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
  $sql = "SELECT * FROM `".$wpdb->prefix."usermeta` WHERE `user_id` = " . $userId . " AND `meta_key` = 'lab_user_left'";
  global $wpdb;
  $results = $wpdb->get_results($sql);
  $nbResult = $wpdb->num_rows;
  //return $nbResult;
  return $nbResult == 1;
}


/********************************************************************************************
 * TEST
 ********************************************************************************************/
function lab_changeLocale($locale) {
  return 'fr_FR';
}
function lab_admin_test()
{ 
  $timestamp = strtotime("2020-05-21");
  wp_send_json_success("NonWorkingDay : ".nonWorkingDay($timestamp));
  return;
}


function nonWorkingDay1($timestamp)
{
    $jour = date("d", $timestamp);
    $mois = date("m", $timestamp);
    $annee = date("Y", $timestamp);
    $EstFerie = false;
    // dates fériées fixes
    if($jour == 1 && $mois == 1) $EstFerie = true; // 1er janvier
    if($jour == 1 && $mois == 5) $EstFerie = true; // 1er mai
    if($jour == 8 && $mois == 5) $EstFerie = true; // 8 mai
    if($jour == 14 && $mois == 7) $EstFerie = true; // 14 juillet
    if($jour == 15 && $mois == 8) $EstFerie = true; // 15 aout
    if($jour == 1 && $mois == 11) $EstFerie = true; // 1 novembre
    if($jour == 11 && $mois == 11) $EstFerie = true; // 11 novembre
    if($jour == 25 && $mois == 12) $EstFerie = true; // 25 décembre
    // fetes religieuses mobiles
    $pak = easter_date($annee);
    $jp = date("d", $pak);
    $mp = date("m", $pak);
    if (date("L", $pak))
    {
        $pak = strtotime("+1 days", $pak);
    }
    if($jp == $jour && $mp == $mois){ $EstFerie = true;} // Pâques
    $lpk = mktime(date("H", $pak), date("i", $pak), date("s", $pak), date("m", $pak) , date("d", $pak) +1, date("Y", $pak) );
    $jp = date("d", $lpk);
    $mp = date("m", $lpk);

    if($jp == $jour && $mp == $mois){ $EstFerie = true; }// Lundi de Pâques
    $asc = mktime(date("H", $pak), date("i", $pak), date("s", $pak), date("m", $pak), date("d", $pak) + 39, date("Y", $pak) );
    $jp = date("d", $asc);
    $mp = date("m", $asc);
    $dates[] = date("d/m/Y", $asc);
    if($jp == $jour && $mp == $mois){ $EstFerie = true;}//ascension
    $pe = mktime(date("H", $pak), date("i", $pak), date("s", $pak), date("m", $pak),
    date("d", $pak) + 49, date("Y", $pak) );
    $jp = date("d", $pe);
    $mp = date("m", $pe);
    if($jp == $jour && $mp == $mois) {$EstFerie = true;}// Pentecôte
    $lp = mktime(date("H", $asc), date("i", $pak), date("s", $pak), date("m", $pak),
    date("d", $pak) + 50, date("Y", $pak) );
    $jp = date("d", $lp);
    $mp = date("m", $lp);
    if($jp == $jour && $mp == $mois) {$EstFerie = true;}// lundi Pentecôte
    // Samedis et dimanches
    $jour_sem = jddayofweek(unixtojd($timestamp), 0);
    if($jour_sem == 0 || $jour_sem == 6) $EstFerie = true;
    return $EstFerie;
}


/********************************************************************************************
 * GROUPS
 ********************************************************************************************/
/**
 * Fonction qui répond a la requete d'un recherche par nom de groupe
 */
function lab_admin_group_search() {
  $search = $_POST['search'];
  $groupName  = $search["term"];

  global $wpdb;
  $sql = "SELECT * FROM `".$wpdb->prefix."lab_groups` WHERE `group_name` LIKE '%".$groupName."%' ";
  $results = $wpdb->get_results($sql);
  $items = array();
  $url = esc_url(home_url('/'));
  foreach ( $results as $r )
  {
    $items[] = array(label=>$r->group_name, value=>$r->id, id=>$r->id, group_name=>$r->group_name, group_type=>$r->group_type, acronym=>$r->acronym, chief_id=>$r->chief_id, parent_group_id=>$r->parent_group_id, url=>$r->url);
  }
  wp_send_json_success( $items ); 
}

function lab_admin_group_delete(){
  $group_id = $_POST['id'];
  lab_admin_delete_group($group_id);
  wp_send_json_success();
}

function lab_group_editGroup() {
  $id = $_POST['groupId'];
  $acronym = $_POST['acronym'];
  $groupName = $_POST['groupName'];
  $chiefId = $_POST['chiefId'];
  $parent = $_POST['parent'];
  $type = $_POST['group_type'];
  $url = delete_http_and_domain($_POST['url']);
  
  global $wpdb;
  $sql = "UPDATE `".$wpdb->prefix."lab_groups` SET `group_name` = '$groupName', `acronym` = '$acronym',
  `chief_id` = '$chiefId', `group_type` = '$type', `parent_group_id` = '$parent', `url`='$url'
    WHERE id= '$id';";

  wp_send_json_success($wpdb->get_results($sql));
}
function group_delete_substitutes() 
{
  $id = $_POST['id'];
  global $wpdb;
  wp_send_json_success($wpdb->delete($wpdb->prefix.'lab_group_substitutes', array('id' => $id)));
}

function group_add_substitutes()
{
  $userId = $_POST['userId'];
  $groupId = $_POST['groupId'];
  global $wpdb;
  wp_send_json_success($wpdb->insert($wpdb->prefix.'lab_group_substitutes', array('group_id'=>$groupId,'substitute_id'=>$userId)));

}

function group_load_substitutes()
{
  $id = $_POST['id'];
  $sql = "SELECT lgs.id AS id, um1.meta_value AS last_name, um2.meta_value AS first_name FROM `".$wpdb->prefix."lab_group_substitutes`  AS lgs JOIN `".$wpdb->prefix."usermeta` AS um1 ON um1.user_id=lgs.substitute_id JOIN `".$wpdb->prefix."usermeta` AS um2 ON um2.user_id=lgs.substitute_id WHERE lgs.`group_id`=33 AND um1.meta_key='last_name' AND um2.meta_key='first_name'";
  //$sql = "SELECT lgs.id AS id, um1.meta_value AS last_name, um2.meta_value AS first_name FROM `".$wpdb->prefix."lab_group_substitutes` AS lgs JOIN `".$wpdb->prefix."usermeta` AS um1 ON um1.user_id=lgs.substitute_id JOIN `".$wpdb->prefix."usermeta AS um2 ON um2.user_id=lgs.substitute_id WHERE lgs.`group_id`=33 AND um1.meta_key='last_name' AND um2.meta_key='first_name'";
  global $wpdb;
  $results = $wpdb->get_results($sql);
  $items = array();
  foreach ( $results as $r )
  {
    $items[] = array(id=>$r->id, first_name=>$r->first_name, last_name=>$r->last_name, );
  }
  wp_send_json_success( $items );
}

function lab_admin_group_availableAc() {
  //Vérifie la disponibilité de l'acronyme
  $res = lab_admin_search_group_by_acronym($_POST['ac']);
  if (count($res)) {
    wp_send_json_error($res);
    return;
  }
  wp_send_json_success();
}

function delete_http_and_domain($url) {
  $pos = strpos($url, '//');
  if ($pos > 0) {
    $url2 = substr($url, $pos + 2); // on efface http://
    $pos2 = strpos($url2, '/');
    return substr($url2, $pos2); // on efface le nom de domaine
  }
  return $url;
}

function lab_admin_group_createReq() {
  $url = $_POST['url'];
  $res = lab_admin_group_create($_POST['name'],$_POST['acronym'],$_POST['chiefID'],$_POST['parent'],$_POST['type'],delete_http_and_domain($url));
  if (strlen($res)==0) {
    wp_send_json_success(lab_admin_search_group_by_acronym($_POST['acronym']));
    return;
  }
  wp_send_json_error($res);
}
function lab_admin_group_createRoot() { 
  $res = lab_admin_group_create('root', 'root', '1', '0', '0', '/');
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
 * Users Settings
 ********************************************************************************************/
function wp_send_json_warning() {
  $warning = "warning";
  wp_send_json($warning);
}

function lab_admin_list_users_groups() {
  $condNotInGroup = $_POST['check1'];
  $condIsLeft = $_POST['check2'];
  
  $notInGroup  = "";
  $joinIsLeft  = "";
  $whereIsLeft = "";

  global $wpdb;
  /*** FILTER FOR SELECT FIELDS ***/
  if($condNotInGroup == 'true')
  {
    $notInGroup = "AND um1.`user_id` NOT IN (SELECT `user_id` FROM `".$wpdb->prefix."lab_users_groups`)";
  }

  if($condIsLeft == 'true')
  {
    $joinIsLeft  = "JOIN `".$wpdb->prefix."usermeta` AS um6 ON um1.`user_id` = um6.`user_id`";
    $whereIsLeft = "AND um6.`meta_key`='lab_user_left' "."AND um6.`meta_value` IS NULL ";
  }

  $sqlUser = "SELECT um1.`user_id`, um3.`meta_value` AS first_name, um2.`meta_value` AS last_name
              FROM `".$wpdb->prefix."usermeta` AS um1
              JOIN `".$wpdb->prefix."usermeta` AS um2 ON um1.`user_id` = um2.`user_id` 
              JOIN `".$wpdb->prefix."usermeta` AS um3 ON um1.`user_id` = um3.`user_id`"
              . $joinIsLeft . 
              "WHERE um1.`meta_key`='last_name' 
                AND um2.`meta_key`='last_name' 
                AND um3.`meta_key`='first_name'"
                . $notInGroup . $whereIsLeft .
              "ORDER BY last_name";

  $sqlGroup = "SELECT `id` AS group_id, `group_name` 
               FROM ".$wpdb->prefix."lab_groups";
    $resultsUsers = $wpdb->get_results($sqlUser);
    $resultsGroups = $wpdb->get_results($sqlGroup);
    wp_send_json_success(array($resultsUsers, $resultsGroups));
}

function lab_admin_add_users_groups() {
  $users  = $_POST['users'];
  $groups = $_POST['groups'];
  $condition = count($users) * count($groups);
  global $wpdb;
  $count_rows = 0;
  foreach($groups as $g) {
    foreach($users as $u) {
      $count_rows += $wpdb->insert(
        $wpdb->prefix.'lab_users_groups',
        array(
          'group_id' => $g,
          'user_id' => $u
        )
      );
    }
  }
  if ($count_rows == $condition && $condition > 0)
  {
    wp_send_json_success();
  }
  else if ($condition == 0)
  {
    wp_send_json_warning();
  }
  else
  {
    wp_send_json_error();
  }
}
/********************************************************************************************
 * KeyRing
 ********************************************************************************************/
function lab_keyring_add_role_ajax() {
  lab_create_roles();
  wp_send_json_success();
}

function lab_keyring_create_keyReq() {
  $res = lab_keyring_create_key($_POST['params']); 
  if (strlen($res)==0) {
    wp_send_json_success();
    return;
  }
  wp_send_json_error($res);
}
function lab_keyring_search_byWordReq() {
  $res = lab_keyring_search_byWord($_POST['search'],$_POST['limit'],$_POST['page']);
  if (count($res)==0) {
    wp_send_json_error();
    return;
  }
  $html = lab_keyringtableFromKeysList($res['items']);
  wp_send_json_success([$res['total'],$html]);
}
function lab_keyring_findKey_Req() {
  $res = lab_keyring_search_key($_POST['id']);
  if (count($res)) {
    wp_send_json_success($res[0]);
    return;
  }
  wp_send_json_error();
}
function lab_keyring_editKey_Req() {
  $res = lab_keyring_edit_key($_POST['id'],$_POST['fields']);
  if ($res === false) {
    wp_send_json_error();
    return;
  }
  wp_send_json_success();
}
function lab_keyring_deleteKey_Req() {
  $res = lab_keyring_delete_key($_POST['id']);
  if ($res === false) {
    wp_send_json_error();
    return;
  }
  wp_send_json_success();
}
/********************************************************************************************
 * Settings
 ********************************************************************************************/
function lab_ajax_userMetaData_new_key() {
  wp_send_json_success(lab_userMetaData_save_key($_POST['userId'],$_POST['key'],$_POST['value']));
}
function lab_ajax_userMetaData_create_keys() {
  wp_send_json_success(lab_userMetaData_create_metaKeys($_POST['key'],$_POST['value']));
}
function lab_ajax_userMetaData_list_keys() {
  wp_send_json_success(userMetaData_list_metakeys());
}
function lab_ajax_userMetaData_delete_key() {
  if (isset($_POST['key']) && !empty($_POST['key'])) {
    wp_send_json_success(userMetaData_delete_metakeys($_POST['key']));
    //wp_send_json_success($_POST['key']);
  }
  else {
    wp_send_json_success("No key specified");
  }
}
/**
 * Return false, if key exist, true otherwise
 */
function lab_ajax_userMeta_key_not_exist() {
  if (isset($_POST['key']) && !empty($_POST['key'])) {
    if (userMetaData_exist_metakey($_POST['key'])) {
      wp_send_json_error("");
    }
    else
    {
      wp_send_json_success("");
    }
  }
  else {
    wp_send_json_success("No key specified");
  }
}

function lab_ajax_userMeta_um_correction() {
  wp_send_json_success(lab_usermeta_correct_um_fields());
}

function lab_ajax_admin_usermeta_fill_user_slug() {
  wp_send_json_success(lab_admin_usermeta_fill_user_slug());
}

/********************************************************************************************
 * HAL10
 ********************************************************************************************/
function lab_ajax_hal_create_table() {
  wp_send_json_success(lab_hal_createTable_hal());
}
function lab_ajax_hal_fill_fields() {
  wp_send_json_success(lab_admin_usermeta_fill_hal_name());
}
function lab_ajax_hal_download(){
  if (isset($_POST['userId']) && !empty($_POST['userId'])) 
  {
    wp_send_json_success(hal_download($_POST['userId']));
  }
  else
  {
    wp_send_json_success("No key specified");
  }
}
function lab_ajax_delete_hal_table() {
  delete_hal_table();
  wp_send_json_success("Hal table deleted");
}
function lab_keyring_create_loanReq() {
  $params = $_POST['params'];
  $res = lab_keyring_create_loan($params);
  if (strlen($res)!=0) {
    wp_send_json_error("LOAN : ".$res);
  } else {
    $res = lab_keyring_setKeyAvailable($params['key_id'],0);
    (strlen($res)==0) ? wp_send_json_success() : wp_send_json_error("KEY :".$res);
  }
}
function lab_keyring_find_loan_byKey() {
  $res = lab_keyring_get_currentLoan_forKey($_POST['key_id']);
  if (count($res)) {
    wp_send_json_success($res[0]);
    return;
  }
  wp_send_json_error($res);
}

function lab_keyring_edit_loanReq() {
  $res = lab_keyring_edit_loan($_POST['id'],$_POST['params']);
  if ($res === false) {
    wp_send_json_error();
    return;
  }
  wp_send_json_success();
}
function lab_keyring_end_loanReq() {
  $res = lab_keyring_end_loan($_POST['loan_id'],$_POST['end_date'], $_POST['key_id']);
  if (strlen($res)==0) {
    wp_send_json_success();
    return;
  }
  wp_send_json_error($res);
}
function lab_keyring_find_oldLoansReq() {
  if (isset($_POST['key_id'])) {
    $res = lab_keyring_find_oldLoans('key_id',$_POST['key_id']);
  } else if (isset($_POST['user_id'])) {
    $res = lab_keyring_find_oldLoans('user_id',$_POST['user_id']);
  } 
  if (count($res)==0) {
    wp_send_json_error("<tr><td colspan='9'>No loans found</td></tr>");
    return;
  } else {
    wp_send_json_success(lab_keyringtableFromLoansList($res));
  }
}
/// Second onglet : 

function lab_keyring_search_current_loans_Req() {
  $res = lab_keyring_search_current_loans($_POST["user"],$_POST["page"],$_POST["limit"]);
  if (count($res)==0) {
    wp_send_json_error("<tr><td colspan='9'>No loans found</td></tr>");
    return;
  } else {
    $html = lab_keyringtableFromLoansList($res['items']);
    wp_send_json_success([$res['total'],$html]);
  }
}
function lab_keyring_get_loan_Req() {
  $res = lab_keyring_get_loan($_POST['id']);
  if (count($res)) {
    wp_send_json_success($res[0]);
    return;
  }
  wp_send_json_error($res);
}

/********************************************************************************************
 * Lab_Profile
 ********************************************************************************************/
function lab_profile_edit() {
  $user_id=$_POST['user_id'];
  $phone = $_POST['phone'];
  $url = $_POST['url'];
  $description = $_POST['description'];
  $bg_color = $_POST['bg_color'];
  $hal_id = $_POST['hal_id'];
  $hal_name = $_POST['hal_name'];
  $socials = $_POST['socials'];
  if (get_current_user_id()==$user_id || current_user_can('edit_users')) {
    lab_profile_set_MetaKey($user_id,'description',$description);
    lab_profile_setURL($user_id,$url);
    lab_profile_set_MetaKey($user_id,'lab_user_phone',$phone);
    lab_profile_set_MetaKey($user_id,'lab_hal_id',$hal_id);
    lab_profile_set_MetaKey($user_id,'lab_hal_name',$hal_name);
    lab_profile_set_MetaKey($user_id,'lab_profile_bg_color',$bg_color);
    foreach (array_keys($socials) as $key) {
      lab_profile_set_MetaKey($user_id,"lab_$key",$socials[$key]);
    }
    wp_send_json_success(lab_profile($user_id));
    return;
  }
  wp_send_json_error();
}

function lab_admin_createSocial_Req() {
  lab_admin_createSocial();
  wp_send_json_success();
}
function lab_admin_deleteSocial() {
  foreach (['facebook','instagram','linkedin','pinterest','twitter','tumblr','youtube'] as $reseau) {
    userMetaData_delete_metakeys($reseau);
  }
  wp_send_json_success();
}

/********************************************************************************************
 * Lab_Invitations
 ********************************************************************************************/
function lab_invitations_createTables_Req() {
  $res = lab_invitations_createTables();
  if (strlen($res)>0) {
    return $res;
  }
  return;
}
function lab_invitations_new() {
  $fields = $_POST['fields'];
  $guest = array (
    'first_name'=> $fields['guest_firstName'],
    'last_name'=> $fields['guest_lastName'],
    'email'=> $fields['guest_email'],
    'phone'=> $fields['guest_phone'],
    'country'=> $fields['guest_country']
  );
  do {//Génère un token jusqu'à ce qu'il soit unique (on sait jamais)
    $token = bin2hex(random_bytes(10));
  } while ( lab_invitations_getByToken($token)!=NULL );
  date_default_timezone_set("Europe/Paris");
  $timeStamp=date("Y-m-d H:i:s",time());
  $invite = array (
    'token'=>$token,
    'needs_hostel'=>$fields['needs_hostel']=='true' ? 1 : 0,
    'creation_time' => $timeStamp,
    'status' => 1
  );
  if (isset($fields['guest_id'])) {
    lab_invitations_editGuest($fields['guest_id'],$guest);
    $invite['guest_id']=$fields['guest_id'];
  } else {
    $invite['guest_id']=lab_invitations_createGuest($guest);
  }
  foreach (['host_group_id','host_id', 'estimated_cost', 'mission_objective','start_date','end_date','travel_mean_to','travel_mean_from','funding_source','research_contract'] as $champ) {
    $invite[$champ]=$fields[$champ];
  }
  $invite["charges"]=json_encode($fields["charges"]);
  $invite_id = lab_invitations_createInvite($invite);
  if (strlen($fields['comment'])>0) {
    lab_invitations_addComment(array(
      'content'=> $fields['comment'],
      'timestamp'=> $timeStamp,
      'author'=>$fields['guest_firstName'].' '.$fields['guest_lastName'],
      'invite_id'=>$invite_id
    )); 
  }
  $html = '<p>'.esc_html__("Votre demande a bien été prise en compte",'lab').'</p>';
  $html .= "<hr><h5>e-mail envoyé à l'invité : </h5>";
  $html .= lab_invitations_mail(1,$guest,$invite);
  $html .= "<hr><h5>e-mail envoyé à l'invitant : </h5>";
  $html .= lab_invitations_mail(5,$guest,$invite);
  wp_send_json_success($html);
}
function lab_invitations_edit() {
  $fields = $_POST['fields'];
  if (get_current_user_id()==$fields['host_id'] || isset($fields['host_group_id']) && get_current_user_id()==(int)lab_admin_get_chief_byGroup($fields['host_group_id'])) {
    $guest = array (
      'first_name'=> $fields['guest_firstName'],
      'last_name'=> $fields['guest_lastName'],
      'email'=> $fields['guest_email'],
      'phone'=> $fields['guest_phone'],
      'country'=> $fields['guest_country']
    );
    lab_invitations_editGuest($fields['guest_id'],$guest);
    date_default_timezone_set("Europe/Paris");
    $timeStamp=date("Y-m-d H:i:s",time());
    $invite = array (
      'needs_hostel'=>$fields['needs_hostel']=='true' ? 1 : 0,
      'completion_time' => $timeStamp
    );
    foreach (['host_group_id', 'estimated_cost', 'maximum_cost', 'host_id','mission_objective','start_date','end_date','travel_mean_to','travel_mean_from','funding_source','research_contract'] as $champ) {
      $invite[$champ]=$fields[$champ];
    }
    $invite["charges"]=json_encode($fields["charges"]);
    lab_invitations_editInvitation($fields['token'],$invite);
    $html = "<p>".esc_html__("Votre invitation a bien été modifiée",'lab')."<br>à $timeStamp</p>";
    wp_send_json_success($html);
  } else {
    wp_send_json_error('Vous n\'avez par la permission de modifier cette invitation');
  }
}
function lab_invitations_complete() {
  $token = $_POST['token'];
  lab_invitations_editInvitation($token,array('status'=>10));
  $html = 'Un mail récapitulatif a été envoyé au responsable du groupe pour validation';
  $invite = lab_invitations_getByToken($token);
  $Iarray = json_decode(json_encode($invite), true);
  $Garray = json_decode(json_encode(lab_invitations_getGuest($invite->guest_id)), true);
  $html .= lab_invitations_mail(10,$Garray,$Iarray);
  date_default_timezone_set("Europe/Paris");
  $timeStamp=date("Y-m-d H:i:s",time());
  lab_invitations_addComment(array(
    'content'=> "¤Invitation complétée",
    'timestamp'=> $timeStamp,
    'author'=>'System',
    'invite_id'=>$invite->id
  )); 
  wp_send_json_success($html);
}
function lab_invitations_validate() {
  $token = $_POST['token'];
  date_default_timezone_set("Europe/Paris");
  $timeStamp=date("Y-m-d H:i:s",time());
  lab_invitations_addComment(array(
    'content'=> "¤Invitation validée",
    'timestamp'=> $timeStamp,
    'author'=>'System',
    'invite_id'=>lab_invitations_getByToken($token)->id
  )); 
  lab_invitations_editInvitation($token,array('status'=>20));
  wp_send_json_success('La demande a été transmise à l\'administration');
}
// Invitation prise en charge
function lab_invitations_assume() {
  $user = lab_admin_username_get(get_current_user_id());
  $token = $_POST['token'];
  date_default_timezone_set("Europe/Paris");
  $timeStamp=date("Y-m-d H:i:s",time());
  lab_invitations_addComment(array(
    'content'=> "¤Invitation prise en charge par ".$user['first_name']." ".$user['last_name'],
    'timestamp'=> $timeStamp,
    'author'=>'System',
    'invite_id'=>lab_invitations_getByToken($token)->id
  )); 
  lab_invitations_editInvitation($token,array('status'=>30));
  wp_send_json_success();
}

function lab_invitation_newComment() {
  $id = lab_invitations_getByToken($_POST['token'])->id;
  date_default_timezone_set("Europe/Paris");
  $timeStamp=date("Y-m-d H:i:s",time());
  lab_invitations_addComment(array(
    'content'=> $_POST['content'],
    'timestamp'=> $timeStamp,
    'author'=>$_POST['author'],
    'invite_id'=>$id
  )); 
  $html = lab_inviteComments($_POST['token']);
  wp_send_json_success($html);
}

function lab_prefGroups_addReq() {
  $user = isset($_POST['user_id']) ? $_POST['user_id'] : get_current_user_id();
  if (lab_prefGroups_add($user,$_POST['group_id'])===false) {
    wp_send_json_error();
  } else {
    wp_send_json_success();
  }
}
function lab_prefGroups_removeReq() {
  $user = isset($_POST['user_id']) ? $_POST['user_id'] : get_current_user_id();
  if (lab_prefGroups_remove($user,$_POST['group_id'])===false) {
    wp_send_json_error();
  } else {
    global $wpdb;
    wp_send_json_success();
  }
}
function lab_prefGroups_update() {
  $user = isset($_POST['user_id']) ? $_POST['user_id'] : get_current_user_id();
  wp_send_json_success(lab_invite_prefGroupsList($user));
}
function lab_invitations_chiefList_update() {
  $sortBy = isset($_POST['sortBy']) ? $_POST['sortBy'] : 'start_date' ;
  $value = isset($_POST['value']) ? $_POST['value'] : '5' ;
  $page = isset($_POST['page']) ? $_POST['page'] : '1' ;
  $status = isset($_POST['status']) ? $_POST['status'] : [1,10,20,30] ;
  $year = isset($_POST['year']) && strlen($_POST['year'])==4 ? $_POST['year'] : 'all' ;
  $statusList = '(';
  foreach ($status as $elem) {
    $statusList .= $elem.',';
  }
  $statusList = substr($statusList, 0, -1).')';
  if ( ! in_array($sortBy, array("start_date","host_group_id","guest_id","host_id","mission_objective","end_date","estimated_cost","status","maximum_cost")) ) {
    //On prévient les injections SQL en empêchant tout argument qui n'est pas un nom de colonne
    $sortBy = 'start_date';
  }
  $order = (isset($_POST['order']) && $_POST['order'] == 'asc') ? 'ASC' : 'DESC';
  $list = lab_invitations_getByGroup($_POST['group_id'],array('order'=>$order, 'sortBy'=>$sortBy, 'page'=>$page, 'value'=>$value, 'status'=>$statusList, 'year'=>$year));
  wp_send_json_success([$list[0],lab_invitations_interface_fromList($list[1],'chief')]);
}

function lab_invitations_adminList_update() {
  $sortBy = isset($_POST['sortBy']) ? $_POST['sortBy'] : 'start_date' ;
  $value = isset($_POST['value']) ? $_POST['value'] : '5' ;
  $page = isset($_POST['page']) ? $_POST['page'] : '1' ;
  $status = isset($_POST['status']) ? $_POST['status'] : [1,10,20,30] ;
  $year = isset($_POST['year']) && strlen($_POST['year'])==4 ? $_POST['year'] : 'all' ;
  $statusList = '(';
  foreach ($status as $elem) {
    $statusList .= $elem.',';
  }
  $statusList = substr($statusList, 0, -1).')';
  if ( ! in_array($sortBy, array("start_date","host_group_id","guest_id","host_id","mission_objective","end_date","estimated_cost","status","maximum_cost")) ) {
    //On prévient les injections SQL en empêchant tout argument qui n'est pas un nom de colonne
    $sortBy = 'start_date';
  }
  $order = (isset($_POST['order']) && $_POST['order'] == 'asc') ? 'ASC' : 'DESC';
  if (count($_POST['group_ids'])>0) {
    $list = lab_invitations_getByGroups($_POST['group_ids'],array('order'=>$order, 'sortBy'=>$sortBy,'page'=>$page,'value'=>$value, 'status'=>$statusList, 'year'=>$year));
    wp_send_json_success([$list[0],lab_invitations_interface_fromList($list[1],'admin')]);
  } else {
    wp_send_json_error([0,"<tr><td colspan=42>".esc_html__("Aucune invitation",'lab')."</td></tr>"]);
  }
}
function lab_invitations_hostList_update() {
  $sortBy = isset($_POST['sortBy']) ? $_POST['sortBy'] : 'start_date' ;
  $value = isset($_POST['value']) ? $_POST['value'] : '5' ;
  $page = isset($_POST['page']) ? $_POST['page'] : '1' ;
  $status = isset($_POST['status']) ? $_POST['status'] : [1,10,20,30] ;
  $year = isset($_POST['year']) && strlen($_POST['year'])==4 ? $_POST['year'] : 'all' ;
  $statusList = '(';
  foreach ($status as $elem) {
    $statusList .= $elem.',';
  }
  $statusList = substr($statusList, 0, -1).')';
  if ( ! in_array($sortBy, array("start_date","host_group_id","guest_id","host_id","mission_objective","end_date","estimated_cost","status","maximum_cost")) ) {
    //On prévient les injections SQL en empêchant tout argument qui n'est pas un nom de colonne
    $sortBy = 'start_date';
  }
  $order = (isset($_POST['order']) && $_POST['order'] == 'asc') ? 'ASC' : 'DESC';
  $list = lab_invitations_getByHost(get_current_user_id(),array('order'=>$order, 'sortBy'=>$sortBy, 'page'=>$page, 'value'=>$value, 'status'=>$statusList, 'year'=>$year));
  wp_send_json_success([$list[0],lab_invitations_interface_fromList($list[1],"host")]);
}
function lab_invitations_summary() {
  $token = $_POST['token'];
  $invite = json_decode(json_encode(lab_invitations_getByToken($token)), true);
  $guest = json_decode(json_encode(lab_invitations_getGuest($invite['guest_id'])), true);
  wp_send_json_success(lab_InviteForm('admin',$guest,$invite));
}

function lab_invitations_comments(){
  $token = $_POST['token'];
  $string = lab_inviteComments($token);
  $string .= lab_newComments(lab_admin_username_get(get_current_user_id()), $token);
  wp_send_json_success($string);
}

function lab_invitations_realCost() {
  wp_send_json_success( lab_invitations_getByToken($_POST['token'])->real_cost!=null ? lab_invitations_getByToken($_POST['token'])->real_cost : "(".esc_html__("indéfini",'lab').")");
}

function lab_invitations_add_realCost() {
  $token = $_POST['token'];
  $param = $_POST['value'];
  lab_invitations_editInvitation($token,array('real_cost'=>$param));
  wp_send_json_success();
}
function lab_invitations_guestInfo() {
  $guest = lab_invitations_guest_email_exist($_POST['email']);
  if (!$guest) {
    wp_send_json_error(); 
  } else {
    wp_send_json_success($guest);
  }
}
function lab_invitations_pagination_Req() {
  wp_send_json_success(lab_invitations_pagination($_POST['pages'],$_POST['currentPage']));
}

/**************************************************************************************************************
 * PRESENCE
 **************************************************************************************************************/

function lab_admin_presence_save_ext_ajax()
{
  $firstName = $_POST['firstName'];
  $lastName  = $_POST['lastName'];
  $email     = $_POST['email'];
  $date      = $_POST['date'];
  $hourOpen  = $_POST['hourOpen'];
  $hourClose = $_POST['hourClose'];
  $siteId    = $_POST['siteId'];
  $comment   = $_POST['comment'];
  $external  = $_POST['external'];


  $str = $_POST;

  $guestId = lab_invitations_guest_email_exist($email);
  if (!$guestId) {
    $guest = array (
      'first_name'=> $firstName,
      'last_name'=> $lastName,
      'email'=> $email,
      'phone'=> "",
      'country'=> "FR"
    );
    $guestId = lab_invitations_createGuest($guest);
  } else {
    $guestId = $guestId->id;
  }
  $str = "presenceId :".$presenceId."\n";
  $str .= "guestId :".$guestId."\n";
  $str .= "dateOpen :".$date." ".$hourOpen."\n";
  $str .= "dateEnd  :".$date." ".$hourClose."\n"; 
  $str .= "siteId  :".$siteId."\n"; 
  $str .= "comment  :".$comment."\n"; 

  if (lab_admin_presence_save(null, $guestId, $date." ".$hourOpen, $date." ".$hourClose, $siteId, $comment, 1)) {
    wp_send_json_success($str);
  } else {
    wp_send_json_error($str);
  }
}

function lab_admin_presence_save_ajax()
{
  $presenceId  = null;
  if(isset($_POST['id'])) {
    $presenceId = $_POST['id'];
  }

  $userId=$_POST['userId'];
  $currentUserId = get_current_user_id();

  $dateOpen  = $_POST['dateOpen'];
  $hourOpen  = $_POST['hourOpen'];
  $hourClose = $_POST['hourClose'];
  $siteId    = $_POST['siteId'];
  $comment   = $_POST['comment'];
  $external   = $_POST['external'];

  if (!isset($external) || $external == null || $external== "")
  {
    $external = null;
  }
 
  if (!isset($userId)) {
    $userId= $currentUserId;
  }
 

  if (!current_user_can('administrator'))
  {
    // not admin and a user send
    if ($userId != $currentUserId) {
      wp_send_json_error(esc_html("Can only modify your own presency", "lab"));

    }
  }
  
  $newDateStart = strtotime($dateOpen." ".$hourOpen);
  $newDateEnd   = strtotime($dateOpen." ".$hourClose);

  if (nonWorkingDay($newDateStart))
  {
    wp_send_json_error(sprintf(esc_html__("%s is a non wordking day", "lab"), $dateOpen));
    return;
  }

  if ($presenceId == null)
  {
    //wp_send_json_error("presenceId != null");
    //return;
    $sameDay = lab_admin_present_not_same_half_day($userId, $newDateStart, $newDateEnd, $presenceId);
    //wp_send_json_error($sameDay);
    //return;
    if (!$sameDay["success"]) {
      wp_send_json_error($sameDay["data"]);
      return;
    }

    $r = lab_admin_present_check_overlap_presency($userId, $newDateStart, $newDateEnd, $presenceId);
  

    if (count($r) > 0)
    {
      $siteLabel = lab_admin_getSite($r[0]->site);
      $errMsg = sprintf(__("Your are already present in %s the %s between %s and %s"), $siteLabel, date("Y-m-d", strtotime($r[0]->hour_start)), date("H:i", strtotime($r[0]->hour_start)), date("H:i", strtotime($r[0]->hour_end)));
      wp_send_json_error($errMsg);
      return;
    }
  }
  // try to modify existing presency
  else
  {
    $ps = lab_admin_present_get_same_day_presency($userId, $newDateStart, $newDateEnd, $presenceId);
    
    $newHourStart   = intval(date("G", $newDateStart));
    $newHourEnd     = intval(date("G", $newDateEnd));
    foreach($ps as $p)
    {
      $storeDateStart = strtotime($p->hour_start);
      $storeDateEnd   = strtotime($p->hour_end);
      $storeHourStart = intval(date("G", $storeDateStart));
      $storeHourEnd   = intval(date("G", $storeDateStart));
      // check overlap
      if ($newHourStart > $storeHourStart )
      {
        if ($newHourStart < $storeHourEnd) {
          $siteLabel = lab_admin_getSite($p->site);
          $errMsg = sprintf(__("Your new schedules overlap to an existing one : %s the %s between %s and %s"), $siteLabel, date("Y-m-d", $storeDateStart), date("H:i", $storeDateStart), date("H:i", $storeDateEnd));
          wp_send_json_error($errMsg);
          return;
        }
      }
      else
      {
        if ($newHourEnd > $storeHourStart) {
          $siteLabel = lab_admin_getSite($p->site);
          $errMsg = sprintf(__("Your new schedules overlap to an existing one : %s the %s between %s and %s"), $siteLabel, date("Y-m-d", $storeDateStart), date("H:i", $storeDateStart), date("H:i", $storeDateEnd));
          wp_send_json_error($errMsg);
          return;
        }
      }
      // check same half day
      // presency exist in the morning
      if ($storeHourStart < 13)
      {
        if ($newHourStart < 13) {
          $errMsg = sprintf(esc_html("(modify existing schedule) Apologize, we only manage a presency by half day, your already present in the morning of %s"), date("Y-m-d", strtotime($r[0]->hour_start)));
          wp_send_json_error($errMsg);
          return;
        }
      }
      else
      {
        if ($storeHourEnd >= 13 && $newHourEnd >= 13) {
          $errMsg = sprintf(esc_html("(modify existing schedule) Apologize, we only manage a presency by half day, your already present in the afternoon of %s"), date("Y-m-d", strtotime($r[0]->hour_start)));
          wp_send_json_error($errMsg);
          return;
        }
      }
    }
  }
  $res = lab_admin_presence_save($presenceId, $userId, $dateOpen." ".$hourOpen, $dateOpen." ".$hourClose, $siteId, $comment, $external);
  //wp_send_json_error($res);
  if ($res["success"]) {
    wp_send_json_success();
  } else {
    wp_send_json_error($res["data"]);
  }

}

function lab_admin_presence_update_ajax() {
  $presenceId = $_POST['id'];
  $userId = get_current_user_id();
  wp_send_json_success(lab_admin_presence_delete($presenceId, $userId));
}

function lab_admin_presence_delete_ajax() {
  $presenceId = $_POST['id'];
  $userId = get_current_user_id();
  wp_send_json_success(lab_admin_presence_delete($presenceId, $userId));
}

/**************************************************************************************************************
 * LDAP
 **************************************************************************************************************/
function lab_ldap_pagination_Req() {
  wp_send_json_success(lab_ldap_pagination($_POST['pages'],$_POST['currentPage']));
}

function lab_ldap_list_update() {
  $itemPerPage = isset($_POST['value']) ? $_POST['value'] : '5' ;
  $page = isset($_POST['page']) ? $_POST['page'] : '1' ;

  $ldap_obj = LAB_LDAP::getInstance();
  $result   = $ldap_obj->searchAccounts("*");
  $count    = $ldap_obj->countResults($result);
  if($page > ceil($count/$itemPerPage)) {
    $page = ceil($count/$itemPerPage);
  }
  $pageVar = ($page - 1) * $itemPerPage;
  for($i = $pageVar; $i < ($itemPerPage+$pageVar) && $i < $count; $i++)
  {
      $ldapResult .= '<tr><td id="lab_ldap_cn">'. $ldap_obj->getEntries($result,$i, 'cn').'</td>
                    <td><button id="lab_ldap_detail_button_'.$ldap_obj->getEntries($result,$i, 'uid').'" style="cursor: pointer;">Détails</button>&nbsp<span id="eraseLdap" class="fas fa-trash-alt" style="cursor: pointer;">&nbsp</span>
                    <span id="editLdap"  class="fas fa-pen-alt" style="cursor: pointer;"></span></td>
                </tr>';
  }
  wp_send_json_success(array($count,$ldapResult,$page));
}
function lab_ldap_add_user() {
  $ldapRes = lab_ldap_addUser($_POST['first_name'],$_POST['last_name'],$_POST['email'],$_POST['password'],$_POST['uid'],$_POST['organization']);
  if ($ldapRes == 0 ) {
    if ($_POST['addToWP'] == 'true') {
      $wpRes = lab_ldap_new_WPUser(strtoupper($_POST['last_name'])." ".$_POST['first_name'],$_POST['email'],$_POST['password'],$_POST['uid']);
      if ($wpRes==true) {
        wp_send_json_success();
      } else {
        wp_send_json_error($wpRes);
      }
    } else {
      wp_send_json_success();
    }
  } else {
    wp_send_json_error(ldap_err2str($ldapRes));
  }
}
function lab_ldap_amu_lookup() {
  $url = "http://ldap.i2m.univ-amu.fr/getAMUUser.php?token=".AdminParams::get_param(AdminParams::PARAMS_LDAP_TOKEN)."&query=".$_POST['query'];
  // create curl resource
  $ch = curl_init();
  echo($url.'<br>\n');
  var_dump($ch);
  // set url
  curl_setopt($ch, CURLOPT_URL, $url);
  //return the transfer as a string
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  // $output contains the output string
  $output = curl_exec($ch);
  // close curl resource to free up system resources
  curl_close($ch);      
  $res = json_decode($output);
  if ($res['count']==0) {
    wp_send_json_error();
  } else {
    wp_send_json_success($res);
  }
}
function lab_ldap_user_details() {
  $uid = $_POST['uid'];
  $ldap_obj = LAB_LDAP::getInstance();
  $result   = $ldap_obj->get_info_from_uid($uid);
  wp_send_json_success($result);
}