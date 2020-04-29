<?php

include 'lab-admin-core.php';


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
  $type   = $_POST['type'];
  $value = $_POST['value'];
    
  $ok = lab_admin_param_save($type, $value);
  if ($ok) {
    wp_send_json_success();
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

function lab_admin_test()
{ 
  global $wp_rewrite;
  wp_send_json_error($wp_rewrite);
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

function lab_admin_createSocial() {
  foreach (['facebook','instagram','linkedin','pinterest','twitter','tumblr','youtube'] as $reseau) {
    lab_userMetaData_create_metaKeys($reseau,'');
  }
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
function lab_invitations_formSubmit() {
  $fields = $_POST['fields'];
  $params = array (
    'first_name'=> $fields['guest_firstName'],
    'last_name'=> $fields['guest_lastName'],
    'email'=> $fields['guest_email'],
    'phone'=> $fields['guest_phone'],
    'country'=> $fields['guest_country']
  );
  do {
    $token = bin2hex(random_bytes(10));
  } while ( lab_invitations_getByToken($token)!=NULL );
  $invite = array (
    'guest_id'=>lab_invitations_createGuest($params),
    'token'=>$token,
    'needs_hostel'=>$fields['needs_hostel']=='true' ? 1 : 0
  );
  foreach (['host_group_id','host_id','mission_objective','start_date','end_date','travel_mean_to','travel_mean_from','funding_source'] as $champ) {
    $invite[$champ]=$fields[$champ];
  }
  lab_invitations_createInvite($invite);
  $html = "<p>Votre demande d'invitation a bien été prise en compte<br><a href='http://stage.fr/invite/".$token."'> lien d'édition par l'invitant</a></p>";
  wp_send_json_success($html);
}