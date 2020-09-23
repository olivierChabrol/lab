<?php

include 'lab-admin-core.php';
include 'lib/vendor/autoload.php';
require_once("lab-admin-params.php");
require_once("core/lab_thematic.php");

use AdminParams as AdminParams;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/********************************************************************************************
 * THEMATIC
 ********************************************************************************************/

function canModifyThematic($userId)
{
  if ($userId == get_current_user_id())
  {
    return true;
  }
  else
  {
    return current_user_can('edit_users');
  }
}

function ajax_thematic_fe_add()
{
  $userId = $_POST['user_id'];
  $thematic_id = $_POST['thematic_id'];
  if (canModifyThematic($userId)) {
    wp_send_json_success( lab_admin_thematic_add_to_user($userId, $thematic_id));
    return;
  }
  else{
    wp_send_json_error("Cant modify this user");
  }
}

function ajax_thematic_fe_del()
{
  $userId = $_POST['user_id'];
  if (canModifyThematic($userId)) {
    $thematic_id = $_POST['thematic_id'];
    wp_send_json_success( lab_admin_thematic_delete($thematic_id));
    return;
  }
  else
  {
    wp_send_json_error("Cant modify this user");
  }
}
function ajax_thematic_fe_get()
{
  $userId = $_POST['user_id'];
  wp_send_json_success( lab_admin_thematic_get_thematics_by_user($userId));
}

function ajax_thematic_get_thematics_by_user()
{
  $userId = $_POST['user_id'];
  wp_send_json_success( lab_admin_thematic_get_thematics_by_user($userId));
  return;
}

function lab_user_delThematic()
{
  $thematic_id = $_POST['thematic_id'];
  wp_send_json_success( lab_admin_thematic_delete($thematic_id));
  return;
}

function lab_admin_ajax_users_thematic_set_main()
{
  $thematic_id = $_POST['thematic_id'];
  $value = $_POST['thematic_value'];
  $r = lab_admin_thematic_set_main($thematic_id, $value);
  if($r)
  {
    wp_send_json_success($r);
  }
  else
  {
    wp_send_json_error();
  }
}

function ajax_thematic_add()
{
  $userId = $_POST['user_id'];
  $thematic_id = $_POST['thematic_id'];
  wp_send_json_success( lab_admin_thematic_add_to_user($userId, $thematic_id));
  return;

}

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
  $type    = $_POST['type'];
  $value   = $_POST['value'];
  $slug    = $_POST['slug'];
  $color   = $_POST['color'];
  $shift   = $_POST['shift'];
  if (!isset($paramId) || $paramId == "")
  {
    $paramId = null;
  }

  if (!isset($shift) || $shift == "")
  {
    $shift = null;
  }
  if (!isset($slug) || $slug == "")
  {
    $slug = null;
  }
  
  //wp_send_json_error(" paramId : ".$paramId." type : ".$type." value : ".$value." slug : ".$slug." color : ".$color." shift : ".$shift);
  $ok = lab_admin_param_save($type, $value, $color, $paramId, $shift, $slug);
  //wp_send_json_success($ok);
  if ($ok["success"] !== false) {
    wp_send_json_success($ok["success"]);
  }
  else
  {
   // wp_send_json_error(sprintf(__("A param key with '%1s' already exist in db", "lab"), $value));
   wp_send_json_error($ok["data"]);
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
      $items[] = array("label" => $r->value, "value" => $r->id, "type"=>$r->type_param, "color"=>$r->color, "slug"=>$r->slug);
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
  wp_send_json_success(lab_admin_userMetaDatas_get($userId));
}
function lab_admin_usermeta_update_phone()
{
  wp_send_json_success(lab_usermeta_copy_existing_phone());
}

function lab_admin_correct_user_metadatas()
{
  $userId = $_POST['id'];
  if (isset($userId) && !empty($userId))
  {
    wp_send_json_success(correct_missing_usermeta_data($userId));
  }
  else
  {
    
  }
}

function lab_admin_check_missing_usermeta_data()
{
  $userId = $_POST['id'];
  wp_send_json_success(check_missing_usermeta_data($userId));
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
  $userId           = $_POST["userId"];
  $dateLeft         = $_POST["dateLeft"];
  $userFunction     = $_POST["function"];
  $userEmployer     = $_POST["employer"];
  $userFunding      = $_POST["funding"];
  $firstname        = $_POST["firstname"];
  $lastname         = $_POST["lastname"];
  $userLocation     = $_POST["location"];
  $officeNumber     = $_POST["officeNumber"];
  $officeFloor      = $_POST["officeFloor"];
  $phone            = $_POST["phone"];
  $userSectionCn    = $_POST["sectionCn"];
  $userSectionCnu   = $_POST["sectionCnu"];
  $userThesisTitle  = $_POST["thesisTitle"];
  $userHdrTitle     = $_POST["hdrTitle"];
  $userPhdSchool    = $_POST["phdSchool"];
  $email            = $_POST["email"];
  $url              = $_POST["url"];
  $user_country     = $_POST["user_country"];
  $user_sex         = $_POST["user_sex"];
  $user_hdr_date    = $_POST["user_hdr_date"];
  $user_thesis_date = $_POST["user_thesis_date"];
  lab_usermeta_update($userId, $dateLeft, $userFunction, $userLocation, $officeNumber, $officeFloor, $userEmployer, $phone, $userFunding, $firstname, $lastname, $userSectionCn, $userSectionCnu, $email, $url, $userThesisTitle, $userHdrTitle, $userPhdSchool, $user_country, $user_sex, $user_thesis_date, $user_hdr_date);
  wp_send_json_success($user_thesis_date);
}

function lab_usermeta_update($userId, $left, $userFunction, $userLocation, $officeNumber, $officeFloor, $userEmployer, $user_phone, $userFunding, $firstname, $lastname, $userSectionCn, $userSectionCnu, $email = null, $url = null, $userThesisTitle = null, $userHdrTitle = null, $userPhdSchool = null, $userCountry = null, $userSex = null, $user_thesis_date = null, $user_hdr_date = null)
{
  global $wpdb;
  $sql = "";
  if ($left != null || !empty($left)) {
    $sql = "UPDATE `".$wpdb->prefix."usermeta` SET `meta_value` = '" . $left . "' WHERE `user_id` = " . $userId. " AND meta_key='lab_user_left'";
    // if left is set to a date, remove acces to user to the web site
    removeAllRoleToUser($userId);
  } else {
    $sql = "UPDATE `".$wpdb->prefix."usermeta` SET `meta_value` = NULL WHERE `user_id` = " . $userId. " AND meta_key='lab_user_left'";
    setDefaultRole($userId);
  }
  $sql = $wpdb->prepare($sql);
  $wpdb->query($sql);
  $wpdb->update($wpdb->prefix."usermeta", array("meta_value"=>$userFunction)    , array("user_id"=>$userId, "meta_key"=>"lab_user_function"));
  $wpdb->update($wpdb->prefix."usermeta", array("meta_value"=>$userLocation)    , array("user_id"=>$userId, "meta_key"=>"lab_user_location"));
  $wpdb->update($wpdb->prefix."usermeta", array("meta_value"=>$officeNumber)    , array("user_id"=>$userId, "meta_key"=>"lab_user_office_number"));
  $wpdb->update($wpdb->prefix."usermeta", array("meta_value"=>$officeFloor)     , array("user_id"=>$userId, "meta_key"=>"lab_user_office_floor"));
  $wpdb->update($wpdb->prefix."usermeta", array("meta_value"=>$userEmployer)    , array("user_id"=>$userId, "meta_key"=>"lab_user_employer"));
  $wpdb->update($wpdb->prefix."usermeta", array("meta_value"=>$user_phone)      , array("user_id"=>$userId, "meta_key"=>"lab_user_phone"));
  $wpdb->update($wpdb->prefix."usermeta", array("meta_value"=>$userFunding)     , array("user_id"=>$userId, "meta_key"=>"lab_user_funding"));
  $wpdb->update($wpdb->prefix."usermeta", array("meta_value"=>$firstname)       , array("user_id"=>$userId, "meta_key"=>"first_name"));
  $wpdb->update($wpdb->prefix."usermeta", array("meta_value"=>$lastname)        , array("user_id"=>$userId, "meta_key"=>"last_name"));
  $wpdb->update($wpdb->prefix."usermeta", array("meta_value"=>$userSectionCn)   , array("user_id"=>$userId, "meta_key"=>"lab_user_section_cn"));
  $wpdb->update($wpdb->prefix."usermeta", array("meta_value"=>$userSectionCnu)  , array("user_id"=>$userId, "meta_key"=>"lab_user_section_cnu"));
  $wpdb->update($wpdb->prefix."usermeta", array("meta_value"=>$userThesisTitle) , array("user_id"=>$userId, "meta_key"=>"lab_user_thesis_title"));
  $wpdb->update($wpdb->prefix."usermeta", array("meta_value"=>$userHdrTitle)    , array("user_id"=>$userId, "meta_key"=>"lab_user_hdr_title"));
  $wpdb->update($wpdb->prefix."usermeta", array("meta_value"=>$userPhdSchool)   , array("user_id"=>$userId, "meta_key"=>"lab_user_phd_school"));
  $wpdb->update($wpdb->prefix."usermeta", array("meta_value"=>$userCountry)     , array("user_id"=>$userId, "meta_key"=>"lab_user_country"));
  $wpdb->update($wpdb->prefix."usermeta", array("meta_value"=>$userSex)         , array("user_id"=>$userId, "meta_key"=>"lab_user_sex"));
  $wpdb->update($wpdb->prefix."usermeta", array("meta_value"=>$user_hdr_date)   , array("user_id"=>$userId, "meta_key"=>"lab_user_hdr_date"));
  $wpdb->update($wpdb->prefix."usermeta", array("meta_value"=>$user_thesis_date), array("user_id"=>$userId, "meta_key"=>"lab_user_thesis_date"));

  if ($email != null)
  {
    $wpdb->update($wpdb->prefix."users", array("user_email"=>$email),array("ID"=>$userId));
  }
  if ($url != null)
  {
    $wpdb->update($wpdb->prefix."users", array("user_url"=>$url),array("ID"=>$userId));
  }
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
  wp_send_json_success(lab_admin_thematic_load_all());
  return;
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

/**
 * get all the groups by user
 *
 * @param [type] $userId
 * @return void
 */
function lab_admin_ajax_group_by_user($userId)
{
  $userId = $_POST['user_id'];
  if(!isset($userId) || $userId == "")
  {
    wp_send_json_error("[lab_admin_ajax_group_by_user] No user defined");
  }
  $results = lab_group_get_user_groups($userId);
  $groups = [];
  foreach($results as $r)
  {
      $group = new \stdClass();
      $group->id = $r->id; 
      $group->name = $r->group_name;
      $groups[] = $group;
  }
  wp_send_json_success( $groups);
}

function lab_admin_ajax_users_group_delete()
{
  $groupId = $_POST['group_id'];
  lab_admin_group_get_user_groups_delete($groupId);
  wp_send_json_success();
}

function lab_admin_ajax_group_add()
{
  $userId = $_POST['user_id'];
  $groupId = $_POST['group_id'];
  lab_admin_users_groups_add_user($userId, $groupId);
  wp_send_json_success();
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
function lab_admin_loadUserHistory_Req() {
  wp_send_json_success(lab_admin_loadUserHistory($_POST['user_id']));
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
function lab_ajax_userMetaData_complete_keys() {
  //wp_send_json_success("OK");
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
    wp_send_json_error("<tr><td colspan='9'>".__('Aucun prêt trouvé','lab')."</td></tr>");
    return;
  } else {
    wp_send_json_success(lab_keyringtableFromLoansList($res));
  }
}
/// Second onglet : 

function lab_keyring_search_current_loans_Req() {
  $res = lab_keyring_search_current_loans($_POST["user"],$_POST["page"],$_POST["limit"]);
  if (count($res)==0) {
    wp_send_json_error("<tr><td colspan='9'>".__('Aucun prêt trouvé','lab')."</td></tr>");
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
  $user = lab_admin_userMetaDatas_get(get_current_user_id());
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
  $string .= lab_newComments(lab_admin_userMetaDatas_get(get_current_user_id()), $token);
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
  $worgroupFollow  = $_POST['worgroupFollow'];


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

  if ($worgroupFollow != "")
  {
    $canInsert = check_can_follow_workgroup($worgroupFollow,$guestId, 1);
    //wp_send_json_error("canInsert : ". $canInsert["success"]);
    if ($canInsert["success"]) {

      $res = lab_admin_presence_save(null, $guestId, $date." ".$hourOpen, $date." ".$hourClose, $siteId, $comment, 1);
      if ($res["success"]) 
      {
        save_workgroup_follow($worgroupFollow, $guestId, 1);
        wp_send_json_success($str);
      }
      else 
      {
        wp_send_json_error($res["data"]." str :".$str);
      }
    }
    else
    {
      wp_send_json_error("[105]"+ $canInsert["data"]);
    }
  }
  else
  {
    if (lab_admin_presence_save(null, $guestId, $date." ".$hourOpen, $date." ".$hourClose, $siteId, $comment, 1)) {
      wp_send_json_success($str);
    } else {
      wp_send_json_error($str);
    }
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
  $workgroup   = $_POST['workgroup'];
  $worgroupFollow   = $_POST['worgroupFollow'];

  if (!isset($external) || $external == null || $external== "")
  {
    $external = null;
  }
  if (!isset($workgroup) || $workgroup == "" || empty($workgroup))
  {
    $workgroup = null;
  }
  if (!isset($worgroupFollow) || $worgroupFollow == null || $worgroupFollow== "")
  {
    $worgroupFollow = null;
  }
 
  if (!isset($userId)) {
    $userId= $currentUserId;
  }
 
  if (!current_user_can('administrator'))
  {
    // not admin and a user send
    if ($userId != $currentUserId) {
      wp_send_json_error("[10-3]"+ esc_html("Can only modify your own presency", "lab"));

    }
  }
  
  $newDateStart = strtotime($dateOpen." ".$hourOpen);
  $newDateEnd   = strtotime($dateOpen." ".$hourClose);

  if (nonWorkingDay($newDateStart))
  {
    wp_send_json_error("[10-2]"+ sprintf(esc_html__("%s is a non wordking day", "lab"), $dateOpen));
    return;
  }

  if ($presenceId == null)
  {
    //wp_send_json_error("DEBUG presenceId == null");
    //wp_send_json_error("presenceId != null");
    //return;
    $sameDay = lab_admin_present_not_same_half_day($userId, $newDateStart, $newDateEnd, $presenceId);
    //wp_send_json_error($sameDay);
    //return;
    if (!$sameDay["success"]) {
      wp_send_json_error("[10-1]"+ $sameDay["data"]);
      return;
    }

    $r = lab_admin_present_check_overlap_presency($userId, $newDateStart, $newDateEnd, $presenceId);
  

    if (count($r) > 0)
    {
      $siteLabel = lab_admin_getSite($r[0]->site);
      $errMsg = sprintf(__("Your are already present in %s the %s between %s and %s"), $siteLabel, date("Y-m-d", strtotime($r[0]->hour_start)), date("H:i", strtotime($r[0]->hour_start)), date("H:i", strtotime($r[0]->hour_end)));
      wp_send_json_error("[100]"+ $errMsg);
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
          wp_send_json_error("[101]"+ $errMsg);
          return;
        }
      }
      else
      {
        if ($newHourEnd > $storeHourStart) {
          $siteLabel = lab_admin_getSite($p->site);
          $errMsg = sprintf(__("Your new schedules overlap to an existing one : %s the %s between %s and %s"), $siteLabel, date("Y-m-d", $storeDateStart), date("H:i", $storeDateStart), date("H:i", $storeDateEnd));
          wp_send_json_error("[102]"+ $errMsg);
          return;
        }
      }
      // check same half day
      // presency exist in the morning
      if ($storeHourStart < 13)
      {
        if ($newHourStart < 13) {
          $errMsg = sprintf(esc_html("(modify existing schedule) Apologize, we only manage a presency by half day, your already present in the morning of %s"), date("Y-m-d", strtotime($r[0]->hour_start)));
          wp_send_json_error("[103]"+ $errMsg);
          return;
        }
      }
      else
      {
        if ($storeHourEnd >= 13 && $newHourEnd >= 13) {
          $errMsg = sprintf(esc_html("(modify existing schedule) Apologize, we only manage a presency by half day, your already present in the afternoon of %s"), date("Y-m-d", strtotime($r[0]->hour_start)));
          wp_send_json_error("[104]"+ $errMsg);
          return;
        }
      }
    }
  }
  if ($workgroup != null)
  {
    $wgId = save_new_workgroup($workgroup, $newDateStart, $userId, $hourOpen, $hourClose, 10);
    $res = lab_admin_presence_save($presenceId, $userId, $dateOpen." ".$hourOpen, $dateOpen." ".$hourClose, $siteId, $comment, $external);
    workgroup_update_presencyId($wgId, $res["data"]);
  }
  else if ($worgroupFollow != null)
  {
    $canInsert = check_can_follow_workgroup($worgroupFollow,$userId);
    if ($canInsert["success"]) {

      $res = lab_admin_presence_save($presenceId, $userId, $dateOpen." ".$hourOpen, $dateOpen." ".$hourClose, $siteId, $comment, $external);
      save_workgroup_follow($worgroupFollow, $userId);
    }
    else
    {
      wp_send_json_error("[105]"+ $canInsert["data"]);
    }
  }
  else
  {
    $res = lab_admin_presence_save($presenceId, $userId, $dateOpen." ".$hourOpen, $dateOpen." ".$hourClose, $siteId, $comment, $external);
   // wp_send_json_error("DEBUG * else");
  }
  
  if ($res["success"]) {
    wp_send_json_success();
  } else {
    wp_send_json_error("[106]"+ $res["data"]);
  }
}

/**
 * Check if user is not already present in the group et don't exceed max group number
 *
 * @param [type] $workgroupId
 * @param [type] $userId
 * @param integer $external
 * @return void
 */
function check_can_follow_workgroup($workgroupId, $userId, $external=0)
{
  $workGroup = workgroup_get($workgroupId);
  //return ["success"=>false,"data"=>$workGroup];
  $usersFolloginGroup = load_workgroup_follow($workgroupId);
  if (count($usersFolloginGroup) + 1 > intval($workGroup->max))
  {
    return ["success"=>false,"data"=>"Exceed maximum group capacity (".$workGroup->max.")"];
  }
  else
  {
    $alreadyPresent = false;
    foreach($usersFolloginGroup as $user)
    {
      if ($user->user_id == $userId && $external == $user->external)
      {
        $alreadyPresent = true;
      }
    }
    if ($alreadyPresent)
    {
      return ["success"=>false,"data"=>"User already present in workgroup"];
    }
  }
  return ["success"=>true,"data"=>""];
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

  $ldap_obj = LAB_LDAP::getInstance(AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_HOST)[0]->value,
                        AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_BASE)[0]->value,
                        AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_LOGIN)[0]->value,
                        AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_PASSWORD)[0]->value,
                      true);
  $result   = $ldap_obj->searchAccounts();
  $count    = $ldap_obj->countResults($result);
  if($page > ceil($count/$itemPerPage)) {
    $page = ceil($count/$itemPerPage);
  }
  $pageVar = ($page - 1) * $itemPerPage;
  for($i = $pageVar; $i < ($itemPerPage+$pageVar) && $i < $count; $i++)
  {
    $ldapResult .= '<tr><td>'. $ldap_obj->getEntries($result,$i, 'cn').'</td>
                        <td><button id="lab_ldap_detail_button_'.$ldap_obj->getEntries($result,$i, 'uid').'">Détails</button>
                            <span id="eraseLdap" class="fas fa-trash-alt" style="cursor: pointer;"></span>
                            <span id="editLdap" 
                              uid="'.$ldap_obj->getEntries($result,$i,'uid').'"
                              givenName="'.$ldap_obj->getEntries($result,$i,'givenname').'"
                              sn="'.$ldap_obj->getEntries($result,$i,'sn').'"
                              uidNumber="'.$ldap_obj->getEntries($result,$i,'uidnumber').'"
                              homeDirectory="'.$ldap_obj->getEntries($result,$i,'homedirectory').'"
                              mail="'.$ldap_obj->getEntries($result,$i,'mail').'"
                              class="fas fa-pen-alt" style="cursor: pointer;"></span>
                        </td>
                    </tr>';
  }
  wp_send_json_success(array($count,$ldapResult,$page));
}
function lab_ldap_add_user() {
  $ldap_obj = LAB_LDAP::getInstance(AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_HOST)[0]->value,
                        AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_BASE)[0]->value,
                        AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_LOGIN)[0]->value,
                        AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_PASSWORD)[0]->value,
                      true);
  $results = $ldap_obj->get_info_from_mail($_POST['email']);
  // if already exist in ldap, only set to WP
  if ($results != null && $results["mail"] != null)
  {
    $wpRes = lab_ldap_new_WPUser(strtoupper($results["lastname"]),$results["firstname"],$results["mail"],$results["password"],$results['uid']);
    if ($wpRes===true) {
      wp_send_json_success("Already exists in LDAP, added to WP");
    } else {
      wp_send_json_error("WordPress : ".$wpRes);
    }
  }
  else
  {
    $ldapRes = lab_ldap_addUser($ldap_obj, $_POST['first_name'],$_POST['last_name'],$_POST['email'],$_POST['password'],$_POST['uid'],$_POST['organization']);
    if ($ldapRes == 0 ) {
      if ($_POST['addToWP'] == 'true') {
        $wpRes = lab_ldap_new_WPUser(strtoupper($_POST["last_name"]),$results["first_name"],$_POST['email'],$_POST['password'],$_POST['uid']);
        if ($wpRes==true) {
          wp_send_json_success();
        } else {
          wp_send_json_error("WordPress : ".$wpRes);
        }
      } else {
        wp_send_json_success();
      }
    } else {
      wp_send_json_error("LDAP : ".ldap_err2str($ldapRes));
    }
  }
}
function lab_ldap_amu_lookup() {
  $url = "http://".AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_HOST)[0]->value."/getAMUUser.php?token=".AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_TOKEN)[0]->value."&query=".$_POST['query'];
  // create curl resource
  $ch = curl_init();
  // set url
  curl_setopt($ch, CURLOPT_URL, $url);
  //return the transfer as a string
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  // $output contains the output string
  $output = curl_exec($ch);
  // close curl resource to free up system resources
  curl_close($ch);
  $res = json_decode($output);
  if ($res->count==0) {
    wp_send_json_error();
  } else { 
    $entry = 0;
    wp_send_json_success(array(
      'mail'=>$res->$entry->mail->$entry,
      'uid'=>$res->$entry->uid->$entry,
      'password'=>$res->$entry->userpassword->$entry,
      'first_name'=>$res->$entry->givenname->$entry,
      'last_name'=>$res->$entry->sn->$entry
    ));
  }
}

function lab_ldap_edit_user() {
  $uid = $_POST['uid'];
  if (!isset($uid) || $uid == "")
  {
    wp_send_json_error("No UID");
  }
  $ldap = LAB_LDAP::getInstance(AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_HOST)[0]->value,
                        AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_BASE)[0]->value,
                        AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_LOGIN)[0]->value,
                        AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_PASSWORD)[0]->value,
                      true);
  $ldapRes = $ldap_obj->editUser($_POST['uid'], $_POST['givenname'], $_POST['sn'], $_POST['uidnumber'], $_POST['homeDirectory'], $_POST['mail']);
  wp_send_json_success();
}
function lab_admin_get_userLogin_Req() {
  $res = lab_admin_get_userLogin($_POST['user_id']);
  if ($res==null) {
    wp_send_json_error();
  } else {
    wp_send_json_success($res);
  }
}
function lab_ldap_user_details() {
  $uid = $_POST['uid'];
  $ldap = LAB_LDAP::getInstance(AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_HOST)[0]->value,
                        AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_BASE)[0]->value,
                        AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_LOGIN)[0]->value,
                        AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_PASSWORD)[0]->value,
                      true);
  $result   = $ldap_obj->get_info_from_uid($uid);
  wp_send_json_success($result);
}

function setDefaultRole($userId) {
  $user = new WP_User($userId);
  if(count($user->role() == 0)) {
    $user->add_role('subscriber');
  }

}

function removeAllRoleToUser($userId)
{
  $user = new WP_User($userId);
  
  if ( !empty( $user->roles ) && is_array( $user->roles ) ) {
    foreach ($user->roles as $role_key => $role_details) {

      $user->remove_role($role_details);
    }
  }
}

function lab_ldap_delete_userReq() {
  $uid = lab_admin_get_userLogin($_POST['user_id']);
  $keepData = true;
  if(isset($_POST['keepData']) && $_POST['keepData'] == 'false') {
    $keepData = false;
  }
  if (!$keepData) {
    wp_delete_user($_POST['user_id'],1);
  }
  else 
  {
    removeAllRoleToUser($_POST['user_id']);
  }

  if (lab_admin_param_is_ldap_enable())
  {
    $ldap = LAB_LDAP::getInstance(AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_HOST)[0]->value,
                          AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_BASE)[0]->value,
                          AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_LOGIN)[0]->value,
                          AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_PASSWORD)[0]->value,
                        true);
    $res = ldap_delete_user($ldap, $uid);
    if ($res==0) {
      wp_send_json_success();
    } else {
      wp_send_json_error("LDAP : ".ldap_err2str($res));
    }
  }
  else
  {
    wp_send_json_success();
  }
}

function lab_ldap_reconnect() {
  if (lab_admin_param_is_ldap_enable())
  {
    $ldap = LAB_LDAP::getInstance(AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_HOST)[0]->value,
                          AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_BASE)[0]->value,
                          AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_LOGIN)[0]->value,
                          AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_PASSWORD)[0]->value,
                        true);
    $str = "URL : ".$ldap->getURL()." <br> ";
    $str .= "Base : ".$ldap->getBase()." <br> ";
    $str .= "Login : ".$ldap->getLogin()." <br> ";
    $str .= "Passwd : ".$ldap->getPassword()." <br> ";
    if($ldap->bindAdmin())
    {
      wp_send_json_success("Connection to LDAP server successfull");
    }
    else {
      wp_send_json_error("Failed to connect to LDAP server :<br>" .$str);
    }
  }
  //*/
}

function lab_historic_createTable() {
  global $wpdb;
  if (lab_admin_createTable_users_historic()===false) {
    wp_send_json_error($wpdb->last_error);
  }
  wp_send_json_success();
}

/**
 * @param array $fields ('user_id'=>$user_id,
 *                        'ext'=>$end,
 *                        'begin'=>$begin,
 *                        'end'=>$end,
 *                        'mobility'=>$mobility,
 *                        'host_id'=>$host_id,
 *                        'function'=>$function)
 */
function lab_historic_add() {
  $res = lab_admin_add_historic(array(
    'user_id'=>$_POST['user_id'],
    'ext'=>false,
    'begin'=>$_POST['begin'],
    'end'=>(strlen($_POST['end'])>1 ? $_POST['end'] : NULL),
    'mobility'=>$_POST['mobility'],
    'mobility_status'=>$_POST['mobility_status'],
    'host_id'=>$_POST['host_id'],
    'function'=>$_POST['function'],
  ));
  if ($res===false) {
    global $wpdb;
    wp_send_json_error($wpdb->last_error());
  } else {
    wp_send_json_success();
  }
}

function lab_historic_delete() {
  //wp_send_json_error(lab_admin_historic_delete($_POST['entry_id']));
  if (lab_admin_historic_delete($_POST['entry_id'])===false) {
    global $wpdb;
    wp_send_json_error($wpdb->last_error);
  } else {
    wp_send_json_success();
  }
}
function lab_historic_getEntry() {
  $res = lab_admin_historic_get($_POST['entry_id']);
  if ($res===false) {
    wp_send_json_error();
  } else {
    wp_send_json_success($res);
  }
}

function lab_historic_update() {
  $res = lab_admin_historic_update($_POST['entry_id'],array(
    'user_id'=>$_POST['user_id'],
    'ext'=>false,
    'begin'=>$_POST['begin'],
    'end'=>(strlen($_POST['end'])>1 ? $_POST['end'] : NULL),
    'mobility'=>$_POST['mobility'],
    'mobility_status'=>$_POST['mobility_status'],
    'host_id'=>$_POST['host_id'],
    'function'=>$_POST['function']));
  if ($res === false) {
    global $wpdb;
    wp_send_json_error($wpdb->last_error);
  } else {
    wp_send_json_success();
  }
}
function lab_user_getRoles() {
  if (isset($_POST['user_id'])) {
    wp_send_json_success(lab_admin_user_roles($_POST['user_id']));
  } else {
    wp_send_json_error();
  }
}
function lab_user_addRole() {
  if (isset($_POST['user_id']) && isset($_POST['role'])) {
    $user = new WP_USER($_POST['user_id']);
    $user->add_role($_POST['role']);
    wp_send_json_success();
  } else {
    wp_send_json_error();
  }
}
function lab_user_delRole() {
  if (isset($_POST['user_id']) && isset($_POST['role'])) {
    $user = new WP_USER($_POST['user_id']);
    $user->remove_role($_POST['role']);
    wp_send_json_success();
  } else {
    wp_send_json_error();
  }
}
function lab_admin_ldap_settings() {
  $associations = array(
    'host'=>AdminParams::PARAMS_LDAP_HOST,
    'enable'=>AdminParams::PARAMS_LDAP_ENABLE,
    'token'=>AdminParams::PARAMS_LDAP_TOKEN,
    'base'=>AdminParams::PARAMS_LDAP_BASE,
    'login'=>AdminParams::PARAMS_LDAP_LOGIN,
    'password'=>AdminParams::PARAMS_LDAP_PASSWORD,
    'tls'=>AdminParams::PARAMS_LDAP_TLS
  );
  foreach ($associations as $key => $value) {
    if (strlen($_POST[$key][0])>0) {
      if (lab_admin_param_save($value, $_POST[$key][0],'000001',($_POST[$key][1])===false, null)) {
        wp_send_json_error();
      }
    }
  }
  wp_send_json_success(lab_admin_tab_ldap());
}