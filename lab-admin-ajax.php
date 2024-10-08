<?php

include 'lab-admin-core.php';
include 'lib/vendor/autoload.php';
require_once("lab-admin-params.php");
require_once("core/lab_thematic.php");

use AdminParams as AdminParams;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


function lab_ajax_get_thematics()
{
  wp_send_json_success(lab_get_thematic());
}
function lab_ajax_get_thematics_csv()
{
  $csv_output = lab_get_thematic_csv();

  header("Content-type: application/force-download");
  header("Content-disposition: csv" . date("Y-m-d") . ".csv");
  header("Content-disposition: filename=thematic.csv");
  header('Content-Description: File Transfer');

  echo $csv_output;
  exit;
}

function lab_admin_ajax_user_info()
{
  $userId = $_POST['userId'];
  $fields = null;
  if (isset($_POST['fields'])) {
    $fields = $_POST['fields'];
  }
  wp_send_json_success(lab_admin_user_info($userId, $fields));
}

/********************************************************************************************
 * MISSION
 ********************************************************************************************/

function lab_mission_ajax_get_user_information()
{
  $userId = $_POST['userId'];
  wp_send_json_success(lab_mission_get_user_information($userId));
}

/**
 * Generate excel file
 *
 * @return excel filename generated
 */
function lab_mission_ajax_excel()
{
  $missionId = $_POST['id'];
  if (!isset($missionId) || empty($missionId)) {
    $missionId = null;
  }
  $filters = $_POST['filters'];
  if (!isset($filters) || empty($filters)) {
    $filters = null;
  }
  $groupIds = $_POST['groupIds'];
  if (!isset($groupIds) || empty($groupIds) || count($groupIds) == 0) {
    $groupIds = null;
  }
  wp_send_json_success(lab_mission_generate_excel($missionId, $filters, $groupIds));
}

function lab_request_list_all_ajax()
{
  $filters = null;
  if (isset($_POST['filters'])) {
    $filters = $_POST['filters'];
  }

  wp_send_json_success(lab_request_list_requests($filters));
}

function lab_mission_ajax_load()
{
  $missionId = null;
  $filters = null;
  $groupIds = null;
  if (isset($_POST['id'])) {
    $missionId = $_POST['id'];
    if (!isset($missionId) || empty($missionId)) {
      $missionId = null;
    }
  }
  if (isset($_POST['filters'])) {
    $filters = $_POST['filters'];
    if (!isset($filters) || empty($filters)) {
      $filters = null;
    }
  }
  if (isset($_POST['groupIds'])) {
    $groupIds = $_POST['groupIds'];
    if (!isset($groupIds) || empty($groupIds) || count($groupIds) == 0) {
      $groupIds = null;
    }
  }
  //wp_send_json_success($groupIds);
  wp_send_json_success(lab_mission_load($missionId, $filters, $groupIds));
}

function lab_mission_ajax_delete()
{
  $missionId = $_POST['id'];
  if (!isset($missionId) || empty($missionId)) {
    wp_send_json_success();
  } else {
    wp_send_json_success(lab_mission_delete($missionId));
  }
}

function lab_mission_ajax_tic()
{
  $missionId = $_POST['mission_id'];
  lab_mission_take_in_charge($missionId);
  wp_send_json_success();
}

function lab_mission_delete_notif()
{
  $missionId = $_POST['mission_id'];
  lab_mission_resetNotifs($missionId);
  wp_send_json_success();
}

function lab_mission_validate()
{
  $missionId = $_POST['mission_id'];
  lab_mission_set_status($missionId, AdminParams::MISSION_STATUS_VALIDATED_GROUP_LEADER);
  wp_send_json_success();
}

function lab_mission_refuse()
{
  $missionId = $_POST['mission_id'];
  lab_mission_set_status($missionId, AdminParams::MISSION_STATUS_REFUSED_GROUP_LEADER);
  wp_send_json_success();
}

function lab_mission_cancel()
{
  $missionId = $_POST['mission_id'];
  lab_mission_set_status($missionId, AdminParams::MISSION_STATUS_CANCEL);
  wp_send_json_success();
}

function lab_mission_complete()
{
  $missionId = $_POST['mission_id'];
  lab_mission_set_status($missionId, AdminParams::MISSION_STATUS_COMPLETE);
  wp_send_json_success();
}
/********************************************************************************************
 * REQUEST
 ********************************************************************************************/
function lab_request_save_ajax()
{
  $request_id      = $_POST['request_id'];
  $request_type    = $_POST['request_type'];
  $request_title   = $_POST['request_title'];
  $request_text    = $_POST['request_text'];
  $expenses_number = $_POST['expenses_number'];

  $previsional_date  = $_POST['request_previsional_date'];
  if (!isset($previsional_date) || $previsional_date == "") {
    $previsional_date = null;
  }
  $end_date  = $_POST['request_end_date'];
  if (!isset($end_date) || $end_date == "") {
    $end_date = null;
  }
  $expenses = null;
  if (isset($expenses_number)) {
    $expenses = array();
    for ($i = 0; $i < $expenses_number; $i++) {
      $expense = array();
      if (isset($_POST['expense_id_' . $i])) {
        $expense["id"] = $_POST['expense_id_' . $i];
      } else {
        $expense["id"] = null;
      }
      if ($_POST['expense_type_' . $i] == -1) {
        $expense["type"] = -1;
        $expense["financial_support"] = -1;
        $expense["object_id"] = -1;
      } else {
        $split = explode("_", $_POST['expense_type_' . $i]);
        $expense["type"] = $split[0];
        $expense["object_id"] = $split[1];
        $expense["financial_support"] = $_POST['expense_financial_support_' . $i];
      }
      $expense["name"] = $_POST['expense_name_' . $i];
      $expense["amount"] = $_POST['expense_value_' . $i];
      $expenses[] = $expense;
    }
  }
  //wp_send_json_success($end_date);
  $reqId = lab_request_save($request_id, get_current_user_id(), $request_type, $request_title, $request_text, $previsional_date, $end_date, $expenses);
  wp_send_json_success($reqId);
}

function lab_request_delete_histo_ajax()
{
  if (lab_is_admin()) {
    $id    = $_POST['id'];
    $histo = lab_request_get_historic($id);
    $request_id = $histo->request_id;
    lab_request_delete_historic_by_id($id);
    wp_send_json_success(lab_request_get_by_id($request_id));
    //*/
  } else {
    wp_send_json_error("Not admin");
  }
}

function lab_request_change_state_ajax()
{
  $request_id    = $_POST['id'];
  $state    = $_POST['state'];
  $user_id  = get_current_user_id();

  wp_send_json_success(lab_request_change_state($request_id, $user_id, $state));
}
function lab_request_load_files_ajax()
{
  $request_id    = $_POST['id'];
  wp_send_json_success(lab_request_load_files($request_id));
}
function lab_request_get_ajax()
{
  $request_id    = $_POST['id'];
  wp_send_json_success(lab_request_get_by_id($request_id));
}
function lab_request_cancel_ajax()
{
  $request_id    = $_POST['id'];
  if (!isset($request_id) || empty($request_id)) {
    wp_send_json_error("No id send");
  }
  $request_modify = lab_request_cancel($request_id);
  if ($request_modify) {
    wp_send_json_success($request_modify);
  } else {
    wp_send_json_error("Failed to modify request");
  }
}

function lab_request_delete_ajax()
{
  $request_id    = $_POST['id'];
  if (!isset($request_id) || empty($request_id)) {
    wp_send_json_error("No id send");
  }
  $request_modify = lab_request_delete($request_id);
  if ($request_modify) {
    wp_send_json_success($request_modify);
  } else {
    wp_send_json_error("Failed to delete request");
  }
}

function lab_request_delete_file_ajax()
{
  $fileId = $_POST['id'];
  $r = lab_request_delete_file($fileId);
  if ($r) {
    wp_send_json_success($r);
  } else {
    wp_send_json_error();
  }
}

function lab_request_load_own_request_ajax()
{
  wp_send_json_success(lab_request_get_own_requests());
}
/********************************************************************************************
 * BUDGET
 ********************************************************************************************/
function lab_ajax_admin_createTable_budget_info()
{
  lab_admin_createTable_budget_info();
  wp_send_json_success();
}

function lab_budget_info_ajax_delete()
{
  $budgetId = $_POST['id'];
  wp_send_json_success(lab_budget_info_delete($budgetId));
}


function lab_budget_info_ajax_save_order()
{
  $res = lab_budget_info_save_order($_POST['params']);
  if (strlen($res) == 0) {
    wp_send_json_success();
    return;
  }
  wp_send_json_error($res);
}

function lab_budget_info_ajax_load()
{
  $budgetId = null;
  if (isset($_POST['id'])) {
    $budgetId = $_POST['id'];
    if (!isset($budgetId) || empty($budgetId)) {
      $budgetId = null;
    }
  }
  $filters = null;
  if (isset($_POST['filters'])) {
    $filters = $_POST['filters'];
    if (!isset($filters) || empty($filters)) {
      $filters = null;
    }
  }
  wp_send_json_success(lab_budget_info_load($budgetId, $filters));
}

function budget_info_ajax_set_date()
{
  $budgetId = $_POST['id'];
  $datefield = $_POST['field'];
  if (!isset($budgetId) || empty($budgetId)) {
    wp_send_json_error("No Id");
  }
  if (!isset($datefield) || empty($datefield)) {
    wp_send_json_error("No Id");
  }
  if ($datefield == "order_date" || $datefield == "delivery_date" || $datefield == "payment_date") {
    wp_send_json_success(budget_info_ajax_date($budgetId, $datefield));
  }
}


/********************************************************************************************
 * THEMATIC
 ********************************************************************************************/

function canModifyThematic($userId)
{
  if ($userId == get_current_user_id()) {
    return true;
  } else {
    return current_user_can('edit_users');
  }
}

function ajax_thematic_fe_add()
{
  $userId = $_POST['user_id'];
  $thematic_id = $_POST['thematic_id'];
  if (canModifyThematic($userId)) {
    wp_send_json_success(lab_admin_thematic_add_to_user($userId, $thematic_id));
    return;
  } else {
    wp_send_json_error("Cant modify this user");
  }
}

function ajax_thematic_fe_del()
{
  $userId = $_POST['user_id'];
  if (canModifyThematic($userId)) {
    $thematic_id = $_POST['thematic_id'];
    wp_send_json_success(lab_admin_thematic_delete($thematic_id));
    return;
  } else {
    wp_send_json_error("Cant modify this user");
  }
}
function ajax_thematic_fe_get()
{
  $userId = $_POST['user_id'];
  wp_send_json_success(lab_admin_thematic_get_thematics_by_user($userId));
}

function ajax_thematic_get_thematics_by_user()
{
  $userId = $_POST['user_id'];
  wp_send_json_success(lab_admin_thematic_get_thematics_by_user($userId));
  return;
}

function lab_user_delThematic()
{
  $thematic_id = $_POST['thematic_id'];
  wp_send_json_success(lab_admin_thematic_delete($thematic_id));
  return;
}

function lab_ajax_get_phd_students()
{
  $filters = null;
  if (isset($_POST['filters'])) {
    $filters = $_POST['filters'];
  }
  $order = null;
  if (isset($_POST['order'])) {
    $order = $_POST['order'];
  }
  $page = 1;
  if (isset($_POST['page'])) {
    $page = $_POST['page'];
  }
  wp_send_json_success(lab_admin_get_phd_student($filters, $order, $page));
}

function lab_ajax_save_user_picture()
{
  $imgId = "";
  if (isset($_POST['imgId'])) {
    $imgId = $_POST['imgId'];
  }

  $userId = $_POST['userId'];
  if (lab_save_user_picture($imgId, $userId))
    wp_send_json_success();
  else
    wp_send_json_error();
}

function lab_admin_ajax_users_thematic_set_main()
{
  $thematic_id = $_POST['thematic_id'];
  $value = $_POST['thematic_value'];
  $r = lab_admin_thematic_set_main($thematic_id, $value);
  if ($r) {
    wp_send_json_success($r);
  } else {
    wp_send_json_error();
  }
}

function ajax_thematic_add()
{
  $userId = $_POST['user_id'];
  $thematic_id = $_POST['thematic_id'];
  wp_send_json_success(lab_admin_thematic_add_to_user($userId, $thematic_id));
  return;
}

/**
 * Fonction qui répond à la requete ajax de recherche d'evenement
 **/
function lab_admin_search_event()
{
  $search = $_POST['search'];
  $title  = $search["term"];

  $sql = "SELECT post_id, `event_name`,`event_start_date` FROM `" . $wpdb->prefix . "em_events` AS ee LEFT JOIN `" . $wpdb->prefix . "term_relationships` AS tr ON tr.`object_id`=ee.post_id WHERE tr.`object_id` IS NULL AND `event_name` LIKE \'%'.$title.'%\' LIMIT 30";
  global $wpdb;
  $results = $wpdb->get_results($sql);
  $nbResult = $wpdb->num_rows;
  $items = array();

  $url = esc_url(home_url('/'));
  foreach ($results as $r) {
    $items[] = array(label => $r->event_name . " " . date("d/m/Y", strtotime($r->event_start_date)), value => $r->post_id);
  }
  wp_send_json_success($items);
}

/********************************************************************************************
 * PARAMS
 ********************************************************************************************/
function lab_admin_param_create_table()
{
  lab_admin_createTable_param();
  lab_admin_initTable_param();
  wp_send_json_success();
}

function lab_admin_ajax_param_save()
{
  $paramId = $_POST['id'];
  $type    = $_POST['type'];
  $value   = $_POST['value'];
  $slug    = $_POST['slug'];
  $color   = $_POST['color'];
  $shift   = $_POST['shift'];
  if (!isset($paramId) || $paramId == "") {
    $paramId = null;
  }

  if (!isset($shift) || $shift == "") {
    $shift = null;
  }
  if (!isset($slug) || $slug == "") {
    $slug = null;
  }

  //wp_send_json_error(" paramId : ".$paramId." type : ".$type." value : ".$value." slug : ".$slug." color : ".$color." shift : ".$shift);
  $ok = lab_admin_param_save($type, $value, $color, $paramId, $shift, $slug);
  //wp_send_json_success($ok);
  if ($ok["success"] !== false) {
    wp_send_json_success($ok["success"]);
  } else {
    // wp_send_json_error(sprintf(__("A param key with '%1s' already exist in db", "lab"), $value));
    wp_send_json_error($ok["data"]);
  }
}

function lab_admin_param_load_param_type()
{
  return lab_admin_param_load_by_type(1);
}

function lab_admin_param_load_type()
{
  wp_send_json_success(lab_admin_param_load_param_type());
}

function lab_admin_param_load_by_type_ajax()
{
  $paramId   = $_POST['id'];
  if (isset($paramId) && !empty($paramId)) {
    wp_send_json_success(lab_admin_param_load_by_type($paramId));
  } else {
    wp_send_json_error(__("Cant get system param", "lab"));
  }
}
function lab_admin_param_load_by_type($type)
{
  global $wpdb;
  $sql = "SELECT id, value FROM `" . $wpdb->prefix . "lab_params` WHERE type_param = " . $type;
  $results = $wpdb->get_results($sql);
  return $results;
}

function lab_admin_param_delete()
{
  $paramId   = $_POST['id'];
  if (isset($paramId) && !empty($paramId)) {
    $deleteOk = lab_admin_param_delete_by_id($paramId);
    if ($deleteOk) {
      wp_send_json_success();
    } else {
      wp_send_json_error(__("Cant delete system param", "lab"));
    }
  } else {
    wp_send_json_error("No id send");
  }
}

function lab_admin_param_search_value()
{
  $search = $_POST['search'];
  $paramValue  = $search["term"];
  if (isset($paramValue) && !empty($paramValue)) {
    $results = lab_admin_param_search_by_value($paramValue);
    $items = array();

    foreach ($results as $r) {
      $items[] = array("label" => $r->value, "value" => $r->id, "type" => $r->type_param, "color" => $r->color, "slug" => $r->slug);
    }
    wp_send_json_success($items);
  } else {
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

function lab_admin_ajax_usermeta_names()
{
  $search = $_POST['search'];
  $userId  = $search["term"];
  if (isset($userId) && $userId !== '') {

    wp_send_json_success(lab_admin_userMetaDatas_get($userId));
  } else {
    wp_send_json_error("No user found");
  }
}
function lab_admin_usermeta_update_phone()
{
  wp_send_json_success(lab_usermeta_copy_existing_phone());
}

function lab_admin_correct_user_metadatas()
{
  $userId = $_POST['id'];
  if (isset($userId) && !empty($userId)) {
    wp_send_json_success(correct_missing_usermeta_data($userId));
  } else {
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
  $sql = "SELECT p.ID, p.`post_title`, p.`post_date`, t.term_id, t.name, t.slug FROM `" . $wpdb->prefix . "posts` AS p JOIN `" . $wpdb->prefix . "term_relationships` AS tr ON tr.`object_id`=p.ID JOIN `" . $wpdb->prefix . "term_taxonomy` AS tt ON tt.`term_taxonomy_id`=tr.`term_taxonomy_id` JOIN `" . $wpdb->prefix . "terms` AS t ON t.term_id=tt.term_id WHERE p.ID=" . $postId;
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
  $sql    = "SELECT * FROM wp_usermeta WHERE user_id=" . $userId;
  global $wpdb;
  $results = $wpdb->get_results($sql);
  $items = array();

  foreach ($results as $r) {
    $items[$r->meta_key] = array(id => $r->umeta_id, key => $r->meta_key, value => $r->meta_value);
  }
  wp_send_json_success($items);
}

function lab_get_user_by_email()
{
  $email = $_POST['email'];
  global $wpdb;
  $sql = "SELECT ID,user_email FROM `" . $wpdb->prefix . "users`  WHERE user_email = '" . $email . "'";

  $user = $wpdb->get_results($sql);
  $data = array();
  if (count($user) == 1) {
    $data["email"] = $user[0]->user_email;
    $data["user_id"] = $user[0]->ID;
    $names = lab_admin_usermeta_names($user[0]->ID);
    $data["first_name"] = $names->first_name;
    $data["last_name"] = $names->last_name;
  }
  wp_send_json_success($data);
  return;
}

function lab_admin_search_user_email()
{
  $search = $_POST['search'];
  $email  = $search["term"];
  $sql = "SELECT ID, user_email FROM `" . $wpdb->prefix . "users`  WHERE user_email LIKE '%" . $email . "%'";
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


  $sql = "SELECT um.* FROM `" . $wpdb->prefix . "users` AS u JOIN `" . $wpdb->prefix . "usermeta` AS um ON u.`ID`=um.user_id WHERE u.user_email='" . $email . "'";
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
  $sql = "SELECT DISTINCT user_id FROM `" . $wpdb->prefix . "usermeta` AS m WHERE user_id NOT IN ( SELECT 1 FROM `" . $wpdb->prefix . "usermeta` AS e WHERE e.user_id = m.user_id AND meta_key = 'lab_user_left' )";
  global $wpdb;
  $results = $wpdb->get_results($sql);
  $nbResult = $wpdb->num_rows;
  $items = array();
  foreach ($results as $r) {
    $user_id = $r->user_id;
    $items[] =  $user_id;
    $wpdb->insert($wpdb->prefix . 'usermeta', array(
      'umeta_id' => NULL,
      'user_id' => $user_id,
      'meta_key' => 'lab_user_left',
      'meta_value' => NULL,
    ));
  }
  wp_send_json_success($items);
}

function lab_admin_replace_event_tags()
{
  global $wpdb;
  $tagToReplaceId = $_POST["tagIdToReplace"];
  $tagReplacementId = $_POST["tagIdReplacement"];
  $query = "UPDATE `" . $wpdb->prefix . "term_relationships` SET `term_taxonomy_id` = %d WHERE `term_taxonomy_id` = %d";
  $sql = $wpdb->prepare($query, array($tagReplacementId, $tagToReplaceId));
  if ($wpdb->query($sql) !== false)
    wp_send_json_success("Tag n°" . $tagToReplaceId . " replaced by tag n°" . $tagReplacementId);
  else
    wp_send_json_error("Error while replacing tag n°" . $tagToReplaceId . " by tag n°" . $tagReplacementId);
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
  $user_phd_support = $_POST["lab_user_phd_support"];
  $user_co_supervision = isset($_POST["lab_user_co_supervision"]) ? $_POST["lab_user_co_supervision"] : null;
  $datas = array();
  $datas["lab_become"] = $_POST["lab_user_phd_become"];
  lab_usermeta_update($userId, $dateLeft, $userFunction, $userLocation, $officeNumber, $officeFloor, $userEmployer, $phone, $userFunding, $firstname, $lastname, $userSectionCn, $userSectionCnu, $email, $url, $userThesisTitle, $userHdrTitle, $userPhdSchool, $user_country, $user_sex, $user_thesis_date, $user_hdr_date, $user_co_supervision, $user_phd_support, $datas);
  wp_send_json_success($user_thesis_date);
}

function lab_usermeta_update($userId, $left, $userFunction, $userLocation, $officeNumber, $officeFloor, $userEmployer, $user_phone, $userFunding, $firstname, $lastname, $userSectionCn, $userSectionCnu, $email = null, $url = null, $userThesisTitle = null, $userHdrTitle = null, $userPhdSchool = null, $userCountry = null, $userSex = null, $user_thesis_date = null, $user_hdr_date = null, $user_co_supervision = null, $user_phd_support = null, $datas = array())
{
  global $wpdb;
  $sql = "";
  if ($left != null || !empty($left)) {
    $sql = "UPDATE `" . $wpdb->prefix . "usermeta` SET `meta_value` = '" . $left . "' WHERE `user_id` = " . $userId . " AND meta_key='lab_user_left'";
    // if left is set to a date, remove acces to user to the web site
    removeAllRoleToUser($userId);
  } else {
    $sql = "UPDATE `" . $wpdb->prefix . "usermeta` SET `meta_value` = NULL WHERE `user_id` = " . $userId . " AND meta_key='lab_user_left'";
    setDefaultRole($userId);
  }
  $sql = $wpdb->prepare($sql);
  $wpdb->query($sql);
  $wpdb->update($wpdb->prefix . "usermeta", array("meta_value" => $userFunction), array("user_id" => $userId, "meta_key" => "lab_user_function"));
  $wpdb->update($wpdb->prefix . "usermeta", array("meta_value" => $userLocation), array("user_id" => $userId, "meta_key" => "lab_user_location"));
  $wpdb->update($wpdb->prefix . "usermeta", array("meta_value" => $officeNumber), array("user_id" => $userId, "meta_key" => "lab_user_office_number"));
  $wpdb->update($wpdb->prefix . "usermeta", array("meta_value" => $officeFloor), array("user_id" => $userId, "meta_key" => "lab_user_office_floor"));
  $wpdb->update($wpdb->prefix . "usermeta", array("meta_value" => $userEmployer), array("user_id" => $userId, "meta_key" => "lab_user_employer"));
  $wpdb->update($wpdb->prefix . "usermeta", array("meta_value" => $user_phone), array("user_id" => $userId, "meta_key" => "lab_user_phone"));
  $wpdb->update($wpdb->prefix . "usermeta", array("meta_value" => $userFunding), array("user_id" => $userId, "meta_key" => "lab_user_funding"));
  $wpdb->update($wpdb->prefix . "usermeta", array("meta_value" => $firstname), array("user_id" => $userId, "meta_key" => "first_name"));
  $wpdb->update($wpdb->prefix . "usermeta", array("meta_value" => $lastname), array("user_id" => $userId, "meta_key" => "last_name"));
  $wpdb->update($wpdb->prefix . "usermeta", array("meta_value" => $userSectionCn), array("user_id" => $userId, "meta_key" => "lab_user_section_cn"));
  $wpdb->update($wpdb->prefix . "usermeta", array("meta_value" => $userSectionCnu), array("user_id" => $userId, "meta_key" => "lab_user_section_cnu"));
  $wpdb->update($wpdb->prefix . "usermeta", array("meta_value" => $userThesisTitle), array("user_id" => $userId, "meta_key" => "lab_user_thesis_title"));
  $wpdb->update($wpdb->prefix . "usermeta", array("meta_value" => $user_phd_support), array("user_id" => $userId, "meta_key" => "lab_user_phd_support"));
  $wpdb->update($wpdb->prefix . "usermeta", array("meta_value" => $userHdrTitle), array("user_id" => $userId, "meta_key" => "lab_user_hdr_title"));
  $wpdb->update($wpdb->prefix . "usermeta", array("meta_value" => $userPhdSchool), array("user_id" => $userId, "meta_key" => "lab_user_phd_school"));
  $wpdb->update($wpdb->prefix . "usermeta", array("meta_value" => $userCountry), array("user_id" => $userId, "meta_key" => "lab_user_country"));
  $wpdb->update($wpdb->prefix . "usermeta", array("meta_value" => $userSex), array("user_id" => $userId, "meta_key" => "lab_user_sex"));
  $wpdb->update($wpdb->prefix . "usermeta", array("meta_value" => $user_hdr_date), array("user_id" => $userId, "meta_key" => "lab_user_hdr_date"));
  $wpdb->update($wpdb->prefix . "usermeta", array("meta_value" => $user_thesis_date), array("user_id" => $userId, "meta_key" => "lab_user_thesis_date"));
  $wpdb->update($wpdb->prefix . "usermeta", array("meta_value" => $user_co_supervision), array("user_id" => $userId, "meta_key" => "lab_user_co_supervision"));

  foreach ($datas as $key => $value) {
    $wpdb->update($wpdb->prefix . "usermeta", array("meta_value" => $value), array("user_id" => $userId, "meta_key" => $key));
  }
  // $wpdb->update($wpdb->prefix."usermeta", array("meta_value"=>$user_phd_support)   , array("user_id"=>$userId, "meta_key"=>"lab_user_phd_support"));

  if ($email != null) {
    $wpdb->update($wpdb->prefix . "users", array("user_email" => $email), array("ID" => $userId));
  }
  if ($url != null) {
    $wpdb->update($wpdb->prefix . "users", array("user_url" => $url), array("ID" => $userId));
  }
}

function lab_usermeta_update_lab_left_key($usermetaId, $left)
{
  global $wpdb;
  $sql = "";
  if ($left != null || !empty($left)) {
    $sql = "UPDATE `" . $wpdb->prefix . "usermeta` SET `meta_value` = '" . $left . "' WHERE `" . $wpdb->prefix . "usermeta`.`umeta_id` = " . $usermetaId;
  } else {
    $sql = "UPDATE `" . $wpdb->prefix . "usermeta` SET `meta_value` = NULL WHERE `" . $wpdb->prefix . "usermeta`.`umeta_id` = " . $usermetaId;
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
  $sql = "INSERT INTO `" . $wpdb->prefix . "usermeta` (`umeta_id`, `user_id`, `meta_key`, `meta_value`) VALUES (NULL, '" . $userId . "', 'lab_user_left', NULL)";
  $wpdb->insert($wpdb->prefix . 'usermeta', array(
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
  $sql = "SELECT * FROM `" . $wpdb->prefix . "usermeta` WHERE `user_id` = " . $userId . " AND `meta_key` = 'lab_user_left'";
  global $wpdb;
  $results = $wpdb->get_results($sql);
  $nbResult = $wpdb->num_rows;
  //return $nbResult;
  return $nbResult == 1;
}


/********************************************************************************************
 * TEST
 ********************************************************************************************/
function lab_changeLocale($locale)
{
  return 'fr_FR';
}
function lab_admin_test()
{
  //wp_send_json_success(get_current_user_id());
  wp_send_json_success(AdminParams::get_param_by_slug("mswgm")->id);
  return;
}

/********************************************************************************************
 * CONTRACT 
 ********************************************************************************************/

function lab_admin_contract_ajax_create_table()
{
  wp_send_json_success(lab_admin_contract_create_table());
}
function lab_admin_contract_ajax_get_managers()
{
  $id = $_POST['id'];
  wp_send_json_success(lab_admin_contract_get_managers($id));
}

function lab_admin_contract_ajax_load()
{
  wp_send_json_success(lab_admin_contract_load());
}

function lab_admin_contract_funder_delete_ajax()
{
  $id = $_POST['id'];
  lab_admin_contract_funder_delete($id);
  wp_send_json_success(lab_admin_contract_funder_list());
}

function lab_admin_contract_funder_list_sub_funder_ajax()
{
  $parentId = $_POST['id'];
  wp_send_json_success(lab_admin_contract_funder_sub_funder_list($parentId));
}

function lab_admin_contract_funder_save_ajax()
{
  $id = $_POST['id'];
  $label   = $_POST['label'];
  $type    = $_POST['type'];
  $value   = $_POST['value'];
  $parent  = $_POST['parent'];
  lab_admin_contract_funder_save($id, $label, $type, $value, $parent);
  wp_send_json_success(lab_admin_contract_funder_list());
}

function lab_admin_contract_funder_list_ajax()
{
  wp_send_json_success(lab_admin_contract_funder_list());
}

function lab_admin_contract_ajax_save()
{
  $id = $_POST['id'];
  $contractName  = $_POST['name'];
  $contractStart = $_POST['start'];
  $contractType  = $_POST['contract_type'];
  $contractTutelage  = $_POST['contract_tutelage'];
  $contractEnd   = $_POST['end'];
  $holders       = $_POST["holders"];
  $managers      = $_POST["managers"];
  wp_send_json_success(lab_admin_contract_save($id, $contractName, $contractType, $contractTutelage, $contractStart, $contractEnd, $holders, $managers));
}

function lab_admin_contract_ajax_search()
{
  $search = $_POST['search'];
  $contractName  = $search["term"];
  wp_send_json_success(lab_admin_contract_search($contractName));
}


function lab_admin_contract_ajax_get()
{
  $contractId = $_POST['id'];
  wp_send_json_success(lab_admin_contract_get($contractId));
}

function lab_admin_contract_ajax_users_load()
{
  $contractId = $_POST['id'];
  wp_send_json_success(lab_admin_contract_users_load($contractId));
}

function lab_admin_contract_ajax_delete()
{
  $contractId = $_POST['id'];
  wp_send_json_success(lab_admin_contract_delete($contractId));
}

/********************************************************************************************
 * GROUPS
 ********************************************************************************************/

function lab_admin_ajax_group_delete_manager()
{
  $id = $_POST['id'];
  if (!isset($id) || empty($id)) {
    wp_send_json_error("No id send");
  }
  lab_group_delete_manager($id);
  wp_send_json_success();
}

function lab_admin_ajax_group_add_manager()
{
  $groupId = $_POST['groupId'];
  $userId = $_POST['userId'];
  $userRole = $_POST['userRole'];
  if (lab_admin_group_add_manager($groupId, $userId, $userRole)) {
    wp_send_json_success();
  } else {
    wp_send_json_error();
  }
}

function lab_admin_ajax_group_load_managers()
{
  $groupId = $_POST['groupId'];
  wp_send_json_success(lab_admin_group_load_managers($groupId));
}

/**
 * Fonction qui répond a la requete d'un recherche par nom de groupe
 */
function lab_admin_group_search()
{
  $search = $_POST['search'];
  $groupName  = $search["term"];

  global $wpdb;
  $sql = "SELECT * FROM `" . $wpdb->prefix . "lab_groups` WHERE `group_name` LIKE '%" . $groupName . "%' ";
  $results = $wpdb->get_results($sql);
  $items = array();
  $url = esc_url(home_url('/'));
  foreach ($results as $r) {
    $items[] = array("label" => $r->group_name, "value" => $r->id, "id" => $r->id, "group_name" => $r->group_name, "group_type" => $r->group_type, "acronym" => $r->acronym, "chief_id" => $r->chief_id, "parent_group_id" => $r->parent_group_id, "url" => $r->url);
  }
  wp_send_json_success($items);
}

/********************************************************************************************
 * INTERSHIP
 ********************************************************************************************/

function lab_internship_load_ajax()
{
  $year = $_POST['year'];
  return wp_send_json_success(list_intern($year));
}

function lab_internship_load_cost_ajax()
{
  $internship_id = $_POST['id'];
  return wp_send_json_success(array("cost" => intern_cost_load($internship_id)));
}

function lab_internship_update_cost_ajax()
{
  $id            = $_POST['id'];
  $internship_id = $_POST['internId'];
  $field         = $_POST['field'];
  $value         = $_POST['value'];
  intern_cost_update($id, $field, $value);
  return wp_send_json_success(array("cost" => intern_cost_load($internship_id)));
}

function lab_internship_create_cost_ajax()
{
  $internship_id = $_POST['id'];
  $internship = lab_internship_get($internship_id);
  return wp_send_json_success($internship["begin"]);
  //return wp_send_json_success();
}

function lab_internship_delete_ajax()
{
  if (isset($_POST['id'])) {
    $id = $_POST['id'];
    wp_send_json_success(lab_internship_delete($id));
  } else {
    wp_send_json_error("No id send");
  }
}

function lab_internship_get_ajax()
{
  if (isset($_POST['id'])) {
    $id = $_POST['id'];
    wp_send_json_success(lab_internship_get($id));
  } else {
    wp_send_json_error("No id send");
  }
}

function lab_internship_save_ajax()
{
  $fields = array("id", "user_id", "firstname", "lastname", "firstname", "email", "training", "training", "establishment", "begin", "end", "host_id", "convention_state", "financials");
  $data = array();
  foreach ($fields as $field) {
    if (isset($_POST[$field])) {
      if ($field != "financials") {
        $data[$field] = stripslashes($_POST[$field]);
      } else {
        $data[$field] = $_POST[$field];
      }
    }
  }
  wp_send_json_success(save_intern($data));
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
  if (!isset($userId) || $userId == "") {
    wp_send_json_error("[lab_admin_ajax_group_by_user] No user defined");
  }
  $results = lab_group_get_user_groups($userId);
  $groups = [];
  foreach ($results as $r) {
    $group = new \stdClass();
    $group->id = $r->id;
    $group->ugid = $r->ugid;
    $group->name = $r->group_name;
    $groups[] = $group;
  }
  wp_send_json_success($groups);
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

function lab_admin_group_delete()
{
  $group_id = $_POST['id'];
  lab_admin_delete_group($group_id);
  wp_send_json_success();
}

function lab_group_editGroup()
{
  $id = $_POST['groupId'];
  $acronym = $_POST['acronym'];
  $groupName = $_POST['groupName'];
  $chiefId = $_POST['chiefId'];
  $parent = $_POST['parent'];
  $type = $_POST['group_type'];
  $url = delete_http_and_domain($_POST['url']);
  if (!isset($parent) || $parent == '' || $parent == '0') {
    $parent = 'NULL';
  } else {
    $parent = "'" . $parent . "'";
  }

  global $wpdb;
  $sql = "UPDATE `" . $wpdb->prefix . "lab_groups` SET `group_name` = '$groupName', `acronym` = '$acronym',
  `chief_id` = '$chiefId', `group_type` = '$type', `parent_group_id` = $parent, `url`='$url'
    WHERE id= '$id';";

  wp_send_json_success($wpdb->get_results($sql));
  //wp_send_json_success($sql);
}

/*function group_load_substitutes()
{
  global $wpdb;
  $id = $_POST['id'];
  $results = lab_admin_group_load_managers($id, 3);
  $items = array();
  foreach ( $results as $r )
  {
    $items[] = array(id=>$r->id, first_name=>$r->first_name, last_name=>$r->last_name, );
  }
  wp_send_json_success($items);
}*/

function lab_admin_group_availableAc()
{
  //Vérifie la disponibilité de l'acronyme
  $res = lab_admin_search_group_by_acronym($_POST['ac']);
  if (count($res)) {
    wp_send_json_error($res);
    return;
  }
  wp_send_json_success();
}

function delete_http_and_domain($url)
{
  $pos = strpos($url, '//');
  if ($pos > 0) {
    $url2 = substr($url, $pos + 2); // on efface http://
    $pos2 = strpos($url2, '/');
    return substr($url2, $pos2); // on efface le nom de domaine
  }
  return $url;
}

function lab_admin_group_createReq()
{
  $url = $_POST['url'];
  $res = lab_admin_group_create($_POST['name'], $_POST['acronym'], $_POST['chiefID'], $_POST['parent'], $_POST['type'], delete_http_and_domain($url));
  if (strlen($res) == 0) {
    wp_send_json_success(lab_admin_search_group_by_acronym($_POST['acronym']));
    return;
  }
  wp_send_json_error($res);
}
function lab_admin_group_createRoot()
{
  $res = lab_admin_group_create('root', 'root', '1', '0', '0', '/');
  if (strlen($res) == 0) {
    wp_send_json_success();
    return;
  }
  wp_send_json_error($res);
}
function lab_admin_group_subs_addReq()
{
  $res = lab_admin_group_subs_add($_POST['id'], $_POST['subList']);
  if (strlen($res) == 0) {
    wp_send_json_success();
    return;
  }
  wp_send_json_error($res);
}
/********************************************************************************************
 * Users Settings
 ********************************************************************************************/
function wp_send_json_warning()
{
  $warning = "warning";
  wp_send_json($warning);
}

function lab_admin_list_users_groups()
{
  $condNotInGroup = $_POST['check1'];
  $condIsLeft = $_POST['check2'];

  $notInGroup  = "";
  $joinIsLeft  = "";
  $whereIsLeft = "";

  global $wpdb;
  /*** FILTER FOR SELECT FIELDS ***/
  if ($condNotInGroup == 'true') {
    $notInGroup = "AND um1.`user_id` NOT IN (SELECT `user_id` FROM `" . $wpdb->prefix . "lab_users_groups`)";
  }

  if ($condIsLeft == 'true') {
    $joinIsLeft  = "JOIN `" . $wpdb->prefix . "usermeta` AS um6 ON um1.`user_id` = um6.`user_id`";
    $whereIsLeft = "AND um6.`meta_key`='lab_user_left' " . "AND um6.`meta_value` IS NULL ";
  }

  $sqlUser = "SELECT um1.`user_id`, um3.`meta_value` AS first_name, um2.`meta_value` AS last_name
              FROM `" . $wpdb->prefix . "usermeta` AS um1
              JOIN `" . $wpdb->prefix . "usermeta` AS um2 ON um1.`user_id` = um2.`user_id` 
              JOIN `" . $wpdb->prefix . "usermeta` AS um3 ON um1.`user_id` = um3.`user_id`"
    . $joinIsLeft .
    "WHERE um1.`meta_key`='last_name' 
                AND um2.`meta_key`='last_name' 
                AND um3.`meta_key`='first_name'"
    . $notInGroup . $whereIsLeft .
    "ORDER BY last_name";

  $sqlGroup = "SELECT `id` AS group_id, `group_name` 
               FROM " . $wpdb->prefix . "lab_groups";
  $resultsUsers = $wpdb->get_results($sqlUser);
  $resultsGroups = $wpdb->get_results($sqlGroup);
  wp_send_json_success(array($resultsUsers, $resultsGroups));
}

function lab_admin_add_users_groups()
{
  $users  = $_POST['users'];
  $groups = $_POST['groups'];
  $condition = count($users) * count($groups);
  global $wpdb;
  $count_rows = 0;
  foreach ($groups as $g) {
    foreach ($users as $u) {
      $count_rows += $wpdb->insert(
        $wpdb->prefix . 'lab_users_groups',
        array(
          'group_id' => $g,
          'user_id' => $u
        )
      );
    }
  }
  if ($count_rows == $condition && $condition > 0) {
    wp_send_json_success();
  } else if ($condition == 0) {
    wp_send_json_warning();
  } else {
    wp_send_json_error();
  }
}
function lab_admin_loadUserHistory_Req()
{
  wp_send_json_success(lab_admin_loadUserHistory($_POST['user_id']));
}
/********************************************************************************************
 * KeyRing
 ********************************************************************************************/
function lab_keyring_add_role_ajax()
{
  lab_create_roles();
  wp_send_json_success();
}

function lab_keyring_create_keyReq()
{
  $res = lab_keyring_create_key($_POST['params']);
  if (strlen($res) == 0) {
    wp_send_json_success();
    return;
  }
  wp_send_json_error($res);
}
function lab_keyring_search_byWordReq()
{
  $res = lab_keyring_search_byWord($_POST['search'], $_POST['limit'], $_POST['page']);
  if (count($res) == 0) {
    wp_send_json_error();
    return;
  }
  $html = lab_keyringtableFromKeysList($res['items']);
  wp_send_json_success([$res['total'], $html]);
}
function lab_keyring_findKey_Req()
{
  $res = lab_keyring_search_key($_POST['id']);
  if (count($res)) {
    wp_send_json_success($res[0]);
    return;
  }
  wp_send_json_error();
}
function lab_keyring_editKey_Req()
{
  $res = lab_keyring_edit_key($_POST['id'], $_POST['fields']);
  if ($res === false) {
    wp_send_json_error();
    return;
  }
  wp_send_json_success();
}
function lab_keyring_deleteKey_Req()
{
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
function lab_ajax_userMetaData_new_key()
{
  wp_send_json_success(lab_userMetaData_save_key($_POST['userId'], $_POST['key'], $_POST['value']));
}
function lab_ajax_userMetaData_complete_keys()
{
  //wp_send_json_success("OK");
  wp_send_json_success(lab_userMetaData_create_metaKeys($_POST['key'], $_POST['value']));
}
function lab_ajax_userMetaData_list_keys()
{
  wp_send_json_success(userMetaData_list_metakeys());
}
function lab_ajax_userMetaData_delete_key()
{
  if (isset($_POST['key']) && !empty($_POST['key'])) {
    wp_send_json_success(userMetaData_delete_metakeys($_POST['key']));
    //wp_send_json_success($_POST['key']);
  } else {
    wp_send_json_success("No key specified");
  }
}
/**
 * Return false, if key exist, true otherwise
 */
function lab_ajax_userMeta_key_not_exist()
{
  if (isset($_POST['key']) && !empty($_POST['key'])) {
    if (userMetaData_exist_metakey($_POST['key'])) {
      wp_send_json_error("");
    } else {
      wp_send_json_success("");
    }
  } else {
    wp_send_json_success("No key specified");
  }
}

function lab_ajax_userMeta_um_correction()
{
  wp_send_json_success(lab_usermeta_correct_um_fields());
}

function lab_ajax_admin_usermeta_fill_user_slug()
{
  if (isset($_POST['userId'])) {
    wp_send_json_success(lab_admin_usermeta_fill_user_slug($_POST['userId']));
  } else {
    wp_send_json_success(lab_admin_usermeta_fill_user_slug());
  }
}

function lab_admin_update_paramsTranslation()
{
  $allParams = getAllParamsValue();
  $all_params = json_decode(json_encode($allParams), true);
  $filename = "../wp-content/plugins/lab/lab-params.php";
  $file = fopen($filename, "w");
  if (!$file) {
    wp_send_json_error();
  }
  fwrite($file, "<?php\n");
  for ($cpt = 0; $cpt < count($all_params); $cpt++) {
    fwrite($file, "\tesc_html__('" . $all_params[$cpt]["value"] . "', 'lab');\n");
  }
  fwrite($file, "?>");
  fclose($file);

  wp_send_json_success("toto");
}

/********************************************************************************************
 * HAL10
 ********************************************************************************************/
function lab_ajax_hal_create_table()
{
  wp_send_json_success(lab_hal_createTable_hal());
}
function lab_ajax_hal_fill_fields()
{
  wp_send_json_success(lab_admin_usermeta_fill_hal_name());
}
function lab_ajax_hal_download()
{
  if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    wp_send_json_success(hal_download($_POST['userId']));
  } else {
    wp_send_json_success("No key specified");
  }
}
function lab_ajax_delete_hal_table()
{
  delete_hal_table();
  wp_send_json_success("Hal table deleted");
}
/**********************************************************************************************
 * KEYRING
 **********************************************************************************************/
function lab_keyring_find_key()
{
  $keyId = $_POST['id'];

  wp_send_json_success(lab_keyring_search_key($keyId));
}

function lab_keyring_search_key_number()
{
  $type = $_POST['type'];
  $search = $_POST['search'];
  $keyNumber = $search['term'];
  $keys = lab_keyring_search_by_key_number($type, $keyNumber);


  $items = array();

  foreach ($keys as $r) {
    $items[] = array(label => $r->number, value => $r->id);
  }
  wp_send_json_success($items);
}

function lab_hal_tools_ajax_load()
{
  wp_send_json_success(lab_hal_tools_load());
}

function lab_keyring_save_loans()
{
  $userId = $_POST["userId"];
  if (!isset($userId) || empty($userId)) {
    wp_send_json_error("No user selected");
  }
  $keyNumber = $_POST["keyNumber"];
  if (!isset($keyNumber) || empty($keyNumber)) {
    wp_send_json_error("No keys number contact admin");
  }
  $keyNumber = intval($keyNumber);
  $keyFields = ["type", "number", "brand", "site", "office", "commentary"];
  $ret = array();

  $ret["keyNumber"] = $keyNumber;
  $ret["keys"] = array();

  for ($i = 0; $i < $keyNumber; $i++) {
    $keyId = $_POST["key_id" . $i];
    $ret["keys"][$i] = array();
    $ret["keys"][$i]["i"] = $i;
    $ret["keys"][$i]["keyId"] = $keyId;

    if ($keyId == -1) {
      $param = array();
      for ($f = 0; $f < sizeof($keyFields); $f++) {
        $param[$keyFields[$f]] = $_POST["key_" . $keyFields[$f] . $i];
      }
      $param["available"] = 0;
      $param["state"] = lab_keyring_default_key_state();
      $keyId = lab_keyring_create_key($param);
      //$ret .= $
      //$ret .= $param;
      $ret["keys"][$i]["min state"] = $param["state"];
      $ret["keys"][$i]["Clef"] = $keyId . " create";
    } else {
      lab_keyring_setKeyAvailable($keyId, 0);
    }
    $loanParams = array();
    $loanParams["referent_id"] = get_current_user_id();
    $loanParams["start_date"] = date("Y-m-d");
    $loanParams["user_id"] = $userId;
    $loanParams["key_id"] = $keyId;
    $loanParams["commentary"] = $_POST["key_commentary" . $i];
    $resLoan = lab_keyring_create_loan($loanParams);
    if (strlen($res) != 0) {
      wp_send_json_error("LOAN : " . $res);
    }
  }
  wp_send_json_success($ret);
}

function lab_keyring_create_loanReq()
{
  $params = $_POST['params'];
  $res = lab_keyring_create_loan($params);
  if (strlen($res) != 0) {
    wp_send_json_error("LOAN : " . $res);
  } else {
    $res = lab_keyring_setKeyAvailable($params['key_id'], 0);
    (strlen($res) == 0) ? wp_send_json_success() : wp_send_json_error("KEY :" . $res);
  }
}
function lab_keyring_find_loan_byKey()
{
  $res = lab_keyring_get_currentLoan_forKey($_POST['key_id']);
  if (count($res)) {
    wp_send_json_success($res[0]);
    return;
  }
  wp_send_json_error($res);
}

function lab_keyring_edit_loanReq()
{
  $res = lab_keyring_edit_loan($_POST['id'], $_POST['params']);
  if ($res === false) {
    wp_send_json_error();
    return;
  }
  wp_send_json_success();
}
function lab_keyring_end_loanReq()
{
  $res = lab_keyring_end_loan($_POST['loan_id'], $_POST['end_date'], $_POST['key_id']);
  if (strlen($res) == 0) {
    wp_send_json_success();
    return;
  }
  wp_send_json_error($res);
}
function lab_keyring_find_oldLoansReq()
{
  if (isset($_POST['key_id'])) {
    $res = lab_keyring_find_oldLoans('key_id', $_POST['key_id']);
  } else if (isset($_POST['user_id'])) {
    $res = lab_keyring_find_oldLoans('user_id', $_POST['user_id']);
  }
  if (count($res) == 0) {
    wp_send_json_error("<tr><td colspan='9'>" . __('No loan found', 'lab') . "</td></tr>");
    return;
  } else {
    wp_send_json_success(lab_keyringtableFromLoansList($res));
  }
}
/// Second onglet : 

function lab_keyring_search_current_loans_Req()
{
  $res = lab_keyring_search_current_loans($_POST["user"], $_POST["page"], $_POST["limit"]);
  if (count($res) == 0) {
    wp_send_json_error("<tr><td colspan='9'>" . __('No loan found', 'lab') . "</td></tr>");
    return;
  } else {
    $html = lab_keyringtableFromLoansList($res['items']);
    wp_send_json_success([$res['total'], $html]);
  }
}
function lab_keyring_get_loan_Req()
{
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
function lab_profile_edit()
{
  $user_id = $_POST['user_id'];
  $phone = $_POST['phone'];
  $url = $_POST['url'];
  $description = $_POST['description'];
  $bg_color = $_POST['bg_color'];
  $hal_id = $_POST['hal_id'];
  $hal_name = $_POST['hal_name'];
  $socials = null;
  if (isset($_POST['socials'])) {
    $socials = $_POST['socials'];
  }
  if (get_current_user_id() == $user_id || current_user_can('edit_users')) {
    lab_profile_set_MetaKey($user_id, 'description', $description);
    lab_profile_setURL($user_id, $url);
    lab_profile_set_MetaKey($user_id, 'lab_user_phone', $phone);
    lab_profile_set_MetaKey($user_id, 'lab_hal_id', $hal_id);
    lab_profile_set_MetaKey($user_id, 'lab_hal_name', $hal_name);
    lab_profile_set_MetaKey($user_id, 'lab_profile_bg_color', $bg_color);
    if ($socials != null) {
      foreach (array_keys($socials) as $key) {
        lab_profile_set_MetaKey($user_id, "lab_$key", $socials[$key]);
      }
    }
    wp_send_json_success(lab_profile($user_id));
    return;
  }
  wp_send_json_error();
}

function lab_admin_createSocial_Req()
{
  lab_admin_createSocial();
  wp_send_json_success();
}
function lab_admin_deleteSocial()
{
  foreach (['facebook', 'instagram', 'linkedin', 'pinterest', 'twitter', 'tumblr', 'youtube'] as $reseau) {
    userMetaData_delete_metakeys($reseau);
  }
  wp_send_json_success();
}

/********************************************************************************************
 * Lab_Invitations
 ********************************************************************************************/
function lab_invitations_createTables_Req()
{
  $res = lab_invitations_createTables();
  if (strlen($res) > 0) {
    return $res;
  }
  return;
}

function lab_descriptions_ajax_load()
{
  $missionId = $_POST['id'];
  wp_send_json_success(lab_mission_load_descriptions($missionId));
}

function lab_travels_ajax_load()
{
  $missionId = $_POST['id'];
  wp_send_json_success(lab_mission_load_travels($missionId));
}

function lab_travel_ajax_delete()
{
  $travelId = $_POST['id'];
  $missionId = $_POST['mission_id'];
  wp_send_json_success(lab_mission_delete_travel($travelId, $missionId));
}

function lab_description_ajax_delete()
{
  $descriptionId = $_POST['jsId'];
  $missionId = $_POST['mission_id'];
  wp_send_json_success(lab_mission_delete_description($descriptionId, $missionId));
}

function lab_description_ajax_save()
{
  $descriptionId = null;
  if (isset($_POST['descriptionId'])) {
    $descriptionId = $_POST['descriptionId'];
  }
  $missionId = $_POST['missionId'];
  $descriptionFields = lab_mission_remap_fields_description($_POST);
  $return = array();
  if ($descriptionId != null) {
    $return["id"] = lab_mission_update_description($descriptionId, $descriptionFields, $missionId);
    $return["descriptionId"] = $_POST['descriptionId'];
  } else {
    $return["id"] = lab_mission_save_description($descriptionFields, false);
  }
  wp_send_json_success($return);
}

function lab_travel_ajax_save()
{
  $travelId  = $_POST['travelId'];
  $missionId = $_POST['missionId'];
  $travelFields = lab_mission_remap_fields($_POST);

  $retrun = array();
  $retrun["id"] = lab_mission_update_travel($travelId, $travelFields, $missionId);
  $retrun["jsId"] = $_POST['jsId'];
  wp_send_json_success($retrun);
}

function md_support_save()
{

  if (!function_exists('wp_handle_upload')) {
    require_once(ABSPATH . 'wp-admin/includes/file.php');
  }
  $file = $_FILES['file'];
  $fileNameType = $_POST['file_name_type'];
  $uploadFrom   = $_POST['upload_from'];
  if (!isset($_POST['request_id'])) {
    wp_send_json_error("Save Request first");
  }
  $request_id   = $_POST['request_id'];

  $ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
  $reqDir = __DIR__ . '/requests';
  if (!file_exists($reqDir)) {
    mkdir($reqDir, 0777, true);
  }

  // generate an unique file name
  $filename = "";
  while (true) {
    $filename = uniqid('lr_', true) . '.' . $ext;
    if (!file_exists(sys_get_temp_dir() . $filename)) break;
  }

  $newFile_name = $reqDir . '/' . $filename;
  $move_new_file = @move_uploaded_file($file['tmp_name'], $newFile_name);

  if ($move_new_file) {
    $url = '/wp-content/plugins/lab/requests/' . $filename;
    $fileId = lab_request_save_file($request_id, $url, $fileNameType);
    //$request_id
    //$file1 = array("id"=>$fileId,"url"=>$url, "name"=>$fileNameType);
    /*
    $reponse = array();
    $reponse["files"] = lab_request_get_associated_files($request_id);
    $reponse["tmp_name"] = $file['tmp_name'];
    $reponse["to_name"] = $newFile_name;
    wp_send_json_success($reponse);
    //*/
    wp_send_json_success(lab_request_get_associated_files($request_id));
  } else {
    wp_send_json_error($newFile_name);
  }
}


function rename_file($dir, $name, $ext)
{
  $dir = "../../../mission/";
  $tokenFile = generate_token();
  return $dir . $tokenFile . $ext;
}

function generate_token()
{
  do {
    $token = bin2hex(random_bytes(10));
  } while (lab_invitations_getByToken($token) != NULL);
  return $token;
}

function lab_invitations_new()
{
  $fields = $_POST['fields'];
  $guest = array(
    'first_name' => $fields['guest_firstName'],
    'last_name' => $fields['guest_lastName'],
    'email' => $fields['guest_email'],
    'phone' => $fields['guest_phone'],
    'residence_country' => $fields['guest_residence_country'],
    'residence_city' => $fields['guest_residence_city'],
  );
  if (isset($fields['guest_country'])) {
    $guest['language'] = $fields['guest_country'];
  }
  $travels = $fields["travels"];
  $descriptions = $fields["descriptions"];
  $token = generate_token();
  date_default_timezone_set("Europe/Paris");
  $timeStamp = date("Y-m-d H:i:s", time());
  $invite = array(
    'token' => $token,
    'needs_hostel' => $fields['needs_hostel'] == 'true' ? 1 : 0,
    'no_charge' => $fields['no_charge'] == 'true' ? 1 : 0,
    'creation_time' => $timeStamp,
    'status' => AdminParams::get_param_by_slug(AdminParams::MISSION_STATUS_NEW)->id
  );
  $missionType = AdminParams::get_param($fields['mission_objective']);
  if ($missionType == 'Invitation') {
    if (isset($fields['guest_id'])) {
      lab_invitations_editGuest($fields['guest_id'], $guest);
      $invite['guest_id'] = $fields['guest_id'];
    } else {
      $invite['guest_id'] = lab_invitations_createGuest($guest);
    }
  } else {
    $fields['guest_id'] = 0;
  }
  if (!isset($fields['host_group_id'])) {
    $hostGroupId = lab_group_get_user_group($fields['host_id']);
    $fields['host_group_id'] = $hostGroupId;
  }
  $managerId = lab_admin_group_get_manager($fields['host_group_id']);
  //wp_send_json_error("host_group_id manager id : " . $managerId);
  //wp_send_json_success("toto " . $managerId);
  $fields['manager_id'] = $managerId;

  foreach (['host_group_id', 'host_id', 'manager_id', 'estimated_cost', 'title', 'hostel_cost', 'hostel_night', 'funding', 'mission_objective', 'funding_source', 'research_contract', 'no_charge'] as $champ) {
    if (isset($fields[$champ])) {
      $invite[$champ] = $fields[$champ];
    }
  }
  if (isset($fields["charges"])) {
    $invite["charges"] = json_encode($fields["charges"]);
  } else {
    $invite["charges"] = null;
  }
  $missionId = lab_mission_create($invite);
  if (!is_numeric($missionId)) {
    wp_send_json_error($missionId);
  }
  $invite["id"] = $missionId;
  //lab_mission_save($missionId, $travels, $descriptions);
  wp_send_json_success(lab_mission_save($missionId, $travels, $descriptions, $token));

  if (strlen($fields['comment']) > 0) {
    if (isset($fields['guest_firstname']) && isset($fields['guest_lastname'])) {
      lab_invitations_addComment(array(
        'content' => $fields['comment'],
        'timestamp' => $timeStamp,
        'author_id' => lab_invitations_getGuest_byName($fields['guest_firstname'], $fields['guest_lastname']),
        'author_type' => 1,
        'invite_id' => $missionId
      ));
    } else {
      lab_invitations_addComment(array(
        'content' => $fields['comment'],
        'timestamp' => $timeStamp,
        'author_id' => get_current_user_id(),
        'author_type' => 2,
        'invite_id' => $missionId
      ));
    }
  }
  $html = '<p>' . esc_html__("Your request has been taken into account", 'lab') . '</p>';
  if ($fields['mission_objective'] != 251) {
    $html .= "<hr><h5>" . __('e-mail send to guest', 'lab') . " : </h5>";
    $html .= lab_invitations_mail(1, $guest, $invite);
    $html .= "<hr><h5>" . __('e-mail send to host', 'lab') . " : </h5>";
    $html .= lab_invitations_mail(5, $guest, $invite);
  } else {
    $html .= "<hr><h5>" . __('e-mail send to manager', 'lab') . " : </h5>";
    $html .= lab_invitations_mail(2, $guest, $invite);
  }
  wp_send_json_success($html);
}

function lab_mission_ajax_lab_mission_load_comments()
{
  $fields = $_POST['token'];
}

function lab_invitations_edit()
{
  global $wpdb;
  $fields = $_POST['fields'];
  for ($i = 0; $i < 20; $i++) {
    if ($fields['descriptions'][$i]['type'] == '279') {
      $description = $fields['descriptions'][$i];
      $token = $fields['token'];
      $value = rename_token($description, $token);
      //$fields['descriptions'][$i]['value'] = rename_token($description, $token);
      //$wpdb->update($wpdb->prefix.'lab_mission_description', $value, arary('id' => $missionId));
    }
  }
  if (!isset($fields['host_group_id'])) {
    if (!isset($fields['host_id']) && isset($fields["token"])) {
      $mission = lab_mission_by_token($fields["token"]);
      //wp_send_json_error($mission);
      //return;
      $fields['host_id'] = $mission->host_id;
    }
    $hostGroupId = lab_group_get_user_group($fields['host_id']);
    $fields['host_group_id'] = $hostGroupId;
  }
  $managerId = lab_admin_group_get_manager($fields['host_group_id']);
  $fields['manager_id'] = $managerId;

  $currentUserId = get_current_user_id();
  $isHost = false;
  $canModify = false;
  $isBudgetManager = false;
  $isGroupLeader = false;
  $isGuest = false;
  $isAdmin = false;
  // if modify by guest
  if ($currentUserId == 0 && isset($fields["token"])) {
    $isGuest = true;
    $canModify = true;
  }
  // if your not a guest check your rights
  else {
    $isHost = $currentUserId == $fields['host_id'];
    $userGroupInfo = lab_admin_group_get_user_info($currentUserId, $fields['host_group_id']);

    foreach ($userGroupInfo as $gi) {
      if (!$isBudgetManager) {
        $isBudgetManager = $gi->manager_type == 1;
      }
      if (!$isGroupLeader) {
        $isGroupLeader = $gi->manager_type == 2;
      }
    }

    // an admin can modify a mission
    $isAdmin = current_user_can('manage_options');

    // all budget Managers can modify a mission
    $canModify = $isBudgetManager;

    // if i am the group leader 
    if (!$canModify) {
      $canModify = $isGroupLeader;
    }

    // if it's i am the owner of the mission
    if (!$canModify) {
      $canModify = $currentUserId == $fields['host_id'];
    }
  }

  if ($canModify || $isAdmin) {
    $guest = array(
      'first_name' => $fields['guest_firstName'],
      'last_name' => $fields['guest_lastName'],
      'email' => $fields['guest_email'],
      'phone' => $fields['guest_phone'],
      'language' => $fields['guest_language'],
      'residence_country' => $fields['guest_residence_country'],
      'residence_city' => $fields['guest_residence_city'],
    );
    lab_invitations_editGuest($fields['guest_id'], $guest);
    date_default_timezone_set("Europe/Paris");
    $timeStamp = date("Y-m-d H:i:s", time());
    $invite = array(
      'needs_hostel' => $fields['needs_hostel'] == 'true' ? 1 : 0,
      //'no_charge'=>$fields['no_charge']=='true' ? 1 : 0,
      'completion_time' => $timeStamp
    );
    foreach (['host_group_id', 'estimated_cost', 'maximum_cost', 'title', 'host_id', 'funding', 'mission_objective', 'hostel_night', 'hostel_cost', 'funding_source', 'research_contract'] as $champ) {
      if (isset($fields[$champ])) {
        $invite[$champ] = $fields[$champ];
      }
    }
    if (isset($fields['no_charge'])) {
      $invite['no_charge'] = $fields['no_charge'] == 'true' ? 1 : 0;
    }

    if ($isGuest) {
      foreach (['hostel_night', 'hostel_cost'] as $champ) {
        if (isset($fields[$champ])) {
          $invite[$champ] = $fields[$champ];
        }
      }
    } else {
      foreach (['host_group_id', 'estimated_cost', 'maximum_cost', 'host_id', 'funding', 'mission_objective', 'hostel_night', 'hostel_cost', 'funding_source', 'research_contract'] as $champ) {
        if (isset($fields[$champ])) {
          $invite[$champ] = $fields[$champ];
        }
      }
      if (isset($fields["charges"])) {
        $invite["charges"] = json_encode($fields["charges"]);
      }
      if ($isGroupLeader && ((float)$fields['maximum_cost']) > 0) {
        $invite["status"] = AdminParams::get_param_by_slug(AdminParams::MISSION_STATUS_VALIDATED_GROUP_LEADER)->id;
      }
    }

    $missionId = lab_mission_get_id_by_token($fields['token']);
    lab_invitations_editInvitation($missionId, $invite);
    $html = "<p>" . esc_html__("Your invitation has been modified", 'lab') . "<br>à $timeStamp</p>";

    wp_send_json_success($html);
    //*/
  } else {
    wp_send_json_error('Vous n\'avez par la permission de modifier cette invitation');
  }
}



function lab_invitations_complete()
{
  $token = $_POST['token'];
  $missionId = lab_mission_get_id_by_token($token);
  $paramWaitingGroupLeader = AdminParams::MISSION_STATUS_WAITING_GROUP_LEADER;
  lab_mission_set_status($missionId, $paramWaitingGroupLeader);
  $html = 'Un mail récapitulatif a été envoyé au responsable du groupe pour validation';
  $invite = lab_invitations_getByToken($token);
  $Iarray = json_decode(json_encode($invite), true);
  if ($invite->guest_id != null) {
    $Garray = json_decode(json_encode(lab_invitations_getGuest($invite->guest_id)), true);
  }
  $html .= lab_invitations_mail(10, $Garray, $Iarray);
  date_default_timezone_set("Europe/Paris");
  $timeStamp = date("Y-m-d H:i:s", time());
  lab_invitations_addComment(array(
    'content' => "¤Invitation complétée",
    'timestamp' => $timeStamp,
    'author_id' => 0,
    'author_type' => 0,
    'invite_id' => $invite->id
  ));
  wp_send_json_success($html);
}
function lab_invitations_validate()
{
  $token = $_POST['token'];
  $missionId = lab_mission_get_id_by_token($token);
  date_default_timezone_set("Europe/Paris");
  $timeStamp = date("Y-m-d H:i:s", time());
  lab_invitations_addComment(array(
    'content' => "¤Invitation validée",
    'timestamp' => $timeStamp,
    'author_id' => 0,
    'author_type' => 0,
    'invite_id' => $missionId
  ));
  wp_send_json_success('La demande a été transmise à l\'administration');
}

function lab_mission_ajax_set_manager()
{
  $missionId = $_POST['id'];
  $managerId = $_POST['managerId'];
  lab_invitations_editInvitation($missionId, ["manager_id" => $managerId]);
  wp_send_json_success();
}

function lab_invitation_newComment()
{
  $mission = lab_invitations_getByToken($_POST['token']);
  //wp_send_json_success($mission);
  $id = $mission->id;
  $authorId = $_POST['author_id'];
  // case it's a guest
  if (get_current_user_id() == 0) {
    $author_type = 1;
    //$authorId = $_POST['author_id'];
    $authorId = $mission->guest_id;
  } else {
    $author_type = 2;
    $authorId = get_current_user_id();
  }
  date_default_timezone_set("Europe/Paris");
  $timeStamp = date("Y-m-d H:i:s", time());
  lab_invitations_addComment(array(
    'content' => $_POST['content'],
    'timestamp' => $timeStamp,
    'author_id' => $authorId,
    'author_type' => $author_type,
    'invite_id' => $id
  ));
  //$html = lab_inviteComments($id);
  wp_send_json_success($id);
}

function lab_prefGroups_addReq()
{
  $user = isset($_POST['user_id']) ? $_POST['user_id'] : get_current_user_id();
  if (lab_prefGroups_add($user, $_POST['group_id']) === false) {
    wp_send_json_error();
  } else {
    wp_send_json_success();
  }
}
function lab_prefGroups_removeReq()
{
  $user = isset($_POST['user_id']) ? $_POST['user_id'] : get_current_user_id();
  if (lab_prefGroups_remove($user, $_POST['group_id']) === false) {
    wp_send_json_error();
  } else {
    global $wpdb;
    wp_send_json_success();
  }
}
function lab_prefGroups_update()
{
  $user = isset($_POST['user_id']) ? $_POST['user_id'] : get_current_user_id();
  wp_send_json_success(lab_invite_prefGroupsList($user));
}
function lab_invitations_chiefList_update()
{
  $sortBy = isset($_POST['sortBy']) ? $_POST['sortBy'] : 'start_date';
  $value = isset($_POST['value']) ? $_POST['value'] : '5';
  $page = isset($_POST['page']) ? $_POST['page'] : '1';
  $status = isset($_POST['status']) ? $_POST['status'] : [1, 10, 20, 30];
  $year = isset($_POST['year']) && strlen($_POST['year']) == 4 ? $_POST['year'] : 'all';
  $statusList = '(';
  foreach ($status as $elem) {
    $statusList .= $elem . ',';
  }
  $statusList = substr($statusList, 0, -1) . ')';
  if (! in_array($sortBy, array("start_date", "host_group_id", "guest_id", "host_id", "mission_objective", "end_date", "estimated_cost", "status", "maximum_cost"))) {
    //On prévient les injections SQL en empêchant tout argument qui n'est pas un nom de colonne
    $sortBy = 'start_date';
  }
  $order = (isset($_POST['order']) && $_POST['order'] == 'asc') ? 'ASC' : 'DESC';
  $list = lab_invitations_getByGroup($_POST['group_id'], array('order' => $order, 'sortBy' => $sortBy, 'page' => $page, 'value' => $value, 'status' => $statusList, 'year' => $year));
  wp_send_json_success([$list[0], lab_invitations_interface_fromList($list[1], 'chief')]);
}

function lab_invitations_adminList_update()
{
  $sortBy = isset($_POST['sortBy']) ? $_POST['sortBy'] : 'start_date';
  $value = isset($_POST['value']) ? $_POST['value'] : '5';
  $page = isset($_POST['page']) ? $_POST['page'] : '1';
  $status = isset($_POST['status']) ? $_POST['status'] : [1, 10, 20, 30];
  $year = isset($_POST['year']) && strlen($_POST['year']) == 4 ? $_POST['year'] : 'all';
  $statusList = '(';
  foreach ($status as $elem) {
    $statusList .= $elem . ',';
  }
  $statusList = substr($statusList, 0, -1) . ')';
  if (! in_array($sortBy, array("start_date", "host_group_id", "guest_id", "host_id", "mission_objective", "end_date", "estimated_cost", "status", "maximum_cost"))) {
    //On prévient les injections SQL en empêchant tout argument qui n'est pas un nom de colonne
    $sortBy = 'start_date';
  }
  $order = (isset($_POST['order']) && $_POST['order'] == 'asc') ? 'ASC' : 'DESC';
  if (count($_POST['group_ids']) > 0) {
    $list = lab_invitations_getByGroups($_POST['group_ids'], array('order' => $order, 'sortBy' => $sortBy, 'page' => $page, 'value' => $value, 'status' => $statusList, 'year' => $year));
    wp_send_json_success([$list[0], lab_invitations_interface_fromList($list[1], 'admin')]);
  } else {
    wp_send_json_error([0, "<tr><td colspan=42>" . esc_html__("No invitation", 'lab') . "</td></tr>"]);
  }
}
function lab_invitations_hostList_update()
{
  $sortBy = isset($_POST['sortBy']) ? $_POST['sortBy'] : 'start_date';
  $value = isset($_POST['value']) ? $_POST['value'] : '5';
  $page = isset($_POST['page']) ? $_POST['page'] : '1';
  $status = isset($_POST['status']) ? $_POST['status'] : [1, 10, 20, 30];
  $year = isset($_POST['year']) && strlen($_POST['year']) == 4 ? $_POST['year'] : 'all';
  $statusList = '(';
  foreach ($status as $elem) {
    $statusList .= $elem . ',';
  }
  $statusList = substr($statusList, 0, -1) . ')';
  if (! in_array($sortBy, array("start_date", "host_group_id", "guest_id", "host_id", "mission_objective", "end_date", "estimated_cost", "status", "maximum_cost"))) {
    //On prévient les injections SQL en empêchant tout argument qui n'est pas un nom de colonne
    $sortBy = 'start_date';
  }
  $order = (isset($_POST['order']) && $_POST['order'] == 'asc') ? 'ASC' : 'DESC';
  $list = lab_invitations_getByHost(get_current_user_id(), array('order' => $order, 'sortBy' => $sortBy, 'page' => $page, 'value' => $value, 'status' => $statusList, 'year' => $year));
  wp_send_json_success([$list[0], lab_invitations_interface_fromList($list[1], "host")]);
}
function lab_invitations_summary()
{
  $token = $_POST['token'];
  $invite = json_decode(json_encode(lab_invitations_getByToken($token)), true);
  $guest = json_decode(json_encode(lab_invitations_getGuest($invite['guest_id'])), true);
  wp_send_json_success(lab_InviteForm('admin', $guest, $invite));
}

function lab_invitations_comments()
{
  $token = $_POST['token'];
  $missionId = lab_invitations_getByToken($_POST['token'])->id;
  $string = lab_inviteComments($missionId);
  $string .= lab_newComments(lab_admin_userMetaDatas_get(get_current_user_id()), $token);
  wp_send_json_success($string);
}

function lab_invitations_comments_json()
{
  $token = $_POST['token'];
  $missionId = lab_invitations_getByToken($_POST['token'])->id;
  //$string = lab_inviteComments($missionId);
  //$string .= lab_newComments(lab_admin_userMetaDatas_get(get_current_user_id()), $token);
  wp_send_json_success(lab_inviteComments_json($missionId));
}

function lab_invitations_realCost()
{
  wp_send_json_success(lab_invitations_getByToken($_POST['token'])->real_cost != null ? lab_invitations_getByToken($_POST['token'])->real_cost : "(" . esc_html__("undefined", 'lab') . ")");
}

function lab_invitations_add_realCost()
{
  $token = $_POST['token'];
  $missionId = lab_mission_get_id_by_token($token);
  $param = $_POST['value'];
  $forward_carbon_footprint = $_POST['forward_carbon_footprint'];
  $return_carbon_footprint = $_POST['return_carbon_footprint'];
  lab_invitations_editInvitation($missionId, array('real_cost' => $param, 'return_carbon_footprint' => $return_carbon_footprint, 'forward_carbon_footprint' => $forward_carbon_footprint));
  wp_send_json_success();
}
function lab_invitations_guestInfo()
{
  $guest = lab_invitations_guest_email_exist($_POST['email']);
  if (!$guest) {
    wp_send_json_error();
  } else {
    wp_send_json_success($guest);
  }
}
function lab_invitations_pagination_Req()
{
  wp_send_json_success(lab_invitations_pagination($_POST['pages'], $_POST['currentPage']));
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
    $guest = array(
      'first_name' => $firstName,
      'last_name' => $lastName,
      'email' => $email,
      'phone' => "",
      'language' => "FR",
      'residence_country' => "",
      'residence_city' => ""
    );
    $guestId = lab_invitations_createGuest($guest);
  } else {
    $guestId = $guestId->id;
  }
  $str = "presenceId :" . $presenceId . "\n";
  $str .= "guestId :" . $guestId . "\n";
  $str .= "dateOpen :" . $date . " " . $hourOpen . "\n";
  $str .= "dateEnd  :" . $date . " " . $hourClose . "\n";
  $str .= "siteId  :" . $siteId . "\n";
  $str .= "comment  :" . $comment . "\n";

  if ($worgroupFollow != "") {
    $canInsert = check_can_follow_workgroup($worgroupFollow, $guestId, 1);
    //wp_send_json_error("canInsert : ". $canInsert["success"]);
    if ($canInsert["success"]) {

      $res = lab_admin_presence_save(null, $guestId, $date . " " . $hourOpen, $date . " " . $hourClose, $siteId, $comment, 1);
      if ($res["success"]) {
        save_workgroup_follow($worgroupFollow, $guestId, 1);
        wp_send_json_success($str);
      } else {
        wp_send_json_error($res["data"] . " str :" . $str);
      }
    } else {
      wp_send_json_error("[105]" + $canInsert["data"]);
    }
  } else {
    if (lab_admin_presence_save(null, $guestId, $date . " " . $hourOpen, $date . " " . $hourClose, $siteId, $comment, 1)) {
      wp_send_json_success($str);
    } else {
      wp_send_json_error($str);
    }
  }
}

function lab_admin_presence_save_ajax()
{
  $presenceId  = null;
  if (isset($_POST['id'])) {
    $presenceId = $_POST['id'];
  }

  $userId = $_POST['userId'];
  $currentUserId = get_current_user_id();

  $dateOpen  = $_POST['dateOpen'];
  $hourOpen  = $_POST['hourOpen'];
  $hourClose = $_POST['hourClose'];
  $siteId    = $_POST['siteId'];
  $comment   = $_POST['comment'];
  $external   = $_POST['external'];
  $workgroup   = $_POST['workgroup'];
  $worgroupFollow   = $_POST['worgroupFollow'];

  if (!isset($external) || $external == null || $external == "") {
    $external = null;
  }
  if (!isset($workgroup) || $workgroup == "" || empty($workgroup)) {
    $workgroup = null;
  }
  if (!isset($worgroupFollow) || $worgroupFollow == null || $worgroupFollow == "") {
    $worgroupFollow = null;
  }

  if (!isset($userId)) {
    $userId = $currentUserId;
  }

  if (!current_user_can('administrator')) {
    // not admin and a user send
    if ($userId != $currentUserId) {
      wp_send_json_error("[10-3]" + esc_html__("Can only modify your own presency", "lab"));
    }
  }

  $newDateStart = strtotime($dateOpen . " " . $hourOpen);
  $newDateEnd   = strtotime($dateOpen . " " . $hourClose);

  if (nonWorkingDay($newDateStart)) {
    wp_send_json_error("[10-2]" + sprintf(esc_html__("%s is a no working day", "lab"), $dateOpen));
    return;
  }

  if ($presenceId == null) {
    //wp_send_json_error("DEBUG presenceId == null");
    //wp_send_json_error("presenceId != null");
    //return;
    $sameDay = lab_admin_present_not_same_half_day($userId, $newDateStart, $newDateEnd, $presenceId);
    //wp_send_json_error($sameDay);
    //return;
    if (!$sameDay["success"]) {
      wp_send_json_error("[10-1]" + $sameDay["data"]);
      return;
    }

    $r = lab_admin_present_check_overlap_presency($userId, $newDateStart, $newDateEnd, $presenceId);


    if (count($r) > 0) {
      $siteLabel = lab_admin_getSite($r[0]->site);
      $errMsg = sprintf(__("Your are already present in %s the %s between %s and %s"), $siteLabel, date("Y-m-d", strtotime($r[0]->hour_start)), date("H:i", strtotime($r[0]->hour_start)), date("H:i", strtotime($r[0]->hour_end)));
      wp_send_json_error("[100]" + $errMsg);
      return;
    }
  }
  // try to modify existing presency
  else {
    $ps = lab_admin_present_get_same_day_presency($userId, $newDateStart, $newDateEnd, $presenceId);

    $newHourStart   = intval(date("G", $newDateStart));
    $newHourEnd     = intval(date("G", $newDateEnd));
    foreach ($ps as $p) {
      $storeDateStart = strtotime($p->hour_start);
      $storeDateEnd   = strtotime($p->hour_end);
      $storeHourStart = intval(date("G", $storeDateStart));
      $storeHourEnd   = intval(date("G", $storeDateStart));
      // check overlap
      if ($newHourStart > $storeHourStart) {
        if ($newHourStart < $storeHourEnd) {
          $siteLabel = lab_admin_getSite($p->site);
          $errMsg = sprintf(__("Your new schedules overlap to an existing one : %s the %s between %s and %s"), $siteLabel, date("Y-m-d", $storeDateStart), date("H:i", $storeDateStart), date("H:i", $storeDateEnd));
          wp_send_json_error("[101]" + $errMsg);
          return;
        }
      } else {
        if ($newHourEnd > $storeHourStart) {
          $siteLabel = lab_admin_getSite($p->site);
          $errMsg = sprintf(__("Your new schedules overlap to an existing one : %s the %s between %s and %s"), $siteLabel, date("Y-m-d", $storeDateStart), date("H:i", $storeDateStart), date("H:i", $storeDateEnd));
          wp_send_json_error("[102]" + $errMsg);
          return;
        }
      }
      // check same half day
      // presency exist in the morning
      if ($storeHourStart < 13) {
        if ($newHourStart < 13) {
          $errMsg = sprintf(esc_html("(modify existing schedule) Apologize, we only manage a presency by half day, your already present in the morning of %s"), date("Y-m-d", strtotime($r[0]->hour_start)));
          wp_send_json_error("[103]" + $errMsg);
          return;
        }
      } else {
        if ($storeHourEnd >= 13 && $newHourEnd >= 13) {
          $errMsg = sprintf(esc_html("(modify existing schedule) Apologize, we only manage a presency by half day, your already present in the afternoon of %s"), date("Y-m-d", strtotime($r[0]->hour_start)));
          wp_send_json_error("[104]" + $errMsg);
          return;
        }
      }
    }
  }
  if ($workgroup != null) {
    $wgId = save_new_workgroup($workgroup, $newDateStart, $userId, $hourOpen, $hourClose, 10);
    $res = lab_admin_presence_save($presenceId, $userId, $dateOpen . " " . $hourOpen, $dateOpen . " " . $hourClose, $siteId, $comment, $external);
    workgroup_update_presencyId($wgId, $res["data"]);
  } else if ($worgroupFollow != null) {
    $canInsert = check_can_follow_workgroup($worgroupFollow, $userId);
    if ($canInsert["success"]) {

      $res = lab_admin_presence_save($presenceId, $userId, $dateOpen . " " . $hourOpen, $dateOpen . " " . $hourClose, $siteId, $comment, $external);
      save_workgroup_follow($worgroupFollow, $userId);
    } else {
      wp_send_json_error("[105]" + $canInsert["data"]);
    }
  } else {
    $res = lab_admin_presence_save($presenceId, $userId, $dateOpen . " " . $hourOpen, $dateOpen . " " . $hourClose, $siteId, $comment, $external);
    // wp_send_json_error("DEBUG * else");
  }

  if ($res["success"]) {
    wp_send_json_success();
  } else {
    wp_send_json_error("[106]" + $res["data"]);
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
function check_can_follow_workgroup($workgroupId, $userId, $external = 0)
{
  $workGroup = workgroup_get($workgroupId);
  //return ["success"=>false,"data"=>$workGroup];
  $usersFolloginGroup = load_workgroup_follow($workgroupId);
  if (count($usersFolloginGroup) + 1 > intval($workGroup->max)) {
    return ["success" => false, "data" => "Exceed maximum group capacity (" . $workGroup->max . ")"];
  } else {
    $alreadyPresent = false;
    foreach ($usersFolloginGroup as $user) {
      if ($user->user_id == $userId && $external == $user->external) {
        $alreadyPresent = true;
      }
    }
    if ($alreadyPresent) {
      return ["success" => false, "data" => "User already present in workgroup"];
    }
  }
  return ["success" => true, "data" => ""];
}

function lab_admin_presence_update_ajax()
{
  $presenceId = $_POST['id'];
  $userId = get_current_user_id();
  wp_send_json_success(lab_admin_presence_delete($presenceId, $userId));
}

function lab_admin_presence_delete_ajax()
{
  $presenceId = $_POST['id'];
  $userId = get_current_user_id();
  wp_send_json_success(lab_admin_presence_delete($presenceId, $userId));
}

/**************************************************************************************************************
 * LDAP
 **************************************************************************************************************/
function lab_ldap_pagination_Req()
{
  wp_send_json_success(lab_ldap_pagination($_POST['pages'], $_POST['currentPage']));
}

function lab_ldap_list_update()
{
  $itemPerPage = isset($_POST['value']) ? $_POST['value'] : '5';
  $page = isset($_POST['page']) ? $_POST['page'] : '1';

  $ldap_obj = LAB_LDAP::getInstance(
    AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_HOST)[0]->value,
    AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_BASE)[0]->value,
    AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_LOGIN)[0]->value,
    AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_PASSWORD)[0]->value,
    true
  );
  $result   = $ldap_obj->searchAccounts();
  $count    = $ldap_obj->countResults($result);
  if ($page > ceil($count / $itemPerPage)) {
    $page = ceil($count / $itemPerPage);
  }
  $pageVar = ($page - 1) * $itemPerPage;
  for ($i = $pageVar; $i < ($itemPerPage + $pageVar) && $i < $count; $i++) {
    $ldapResult .= '<tr><td>' . $ldap_obj->getEntries($result, $i, 'cn') . '</td>
                        <td><button id="lab_ldap_detail_button_' . $ldap_obj->getEntries($result, $i, 'uid') . '">Détails</button>
                            <span id="eraseLdap" class="fas fa-trash-alt" style="cursor: pointer;"></span>
                            <span id="editLdap" 
                              uid="' . $ldap_obj->getEntries($result, $i, 'uid') . '"
                              givenName="' . $ldap_obj->getEntries($result, $i, 'givenname') . '"
                              sn="' . $ldap_obj->getEntries($result, $i, 'sn') . '"
                              uidNumber="' . $ldap_obj->getEntries($result, $i, 'uidnumber') . '"
                              homeDirectory="' . $ldap_obj->getEntries($result, $i, 'homedirectory') . '"
                              mail="' . $ldap_obj->getEntries($result, $i, 'mail') . '"
                              class="fas fa-pen-alt" style="cursor: pointer;"></span>
                        </td>
                    </tr>';
  }
  wp_send_json_success(array($count, $ldapResult, $page));
}
function lab_ldap_add_user()
{
  $ldap_obj = LAB_LDAP::getInstance(
    AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_HOST)[0]->value,
    AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_BASE)[0]->value,
    AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_LOGIN)[0]->value,
    AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_PASSWORD)[0]->value,
    true
  );
  $results = $ldap_obj->get_info_from_mail($_POST['email']);
  // if already exist in ldap, only set to WP
  if ($results != null && $results["mail"] != null) {
    $wpRes = lab_ldap_new_WPUser(strtoupper($results["lastname"]), $results["firstname"], $results["mail"], $results["password"], $results['uid']);
    if ($wpRes === true) {
      wp_send_json_success("Already exists in LDAP, added to WP");
    } else {
      wp_send_json_error("WordPress : " . $wpRes);
    }
  } else {
    $ldapRes = lab_ldap_addUser($ldap_obj, $_POST['first_name'], $_POST['last_name'], $_POST['email'], $_POST['password'], $_POST['uid'], $_POST['organization']);
    if ($ldapRes == 0) {
      if ($_POST['addToWP'] == 'true') {
        $wpRes = lab_ldap_new_WPUser(strtoupper($_POST["last_name"]), $_POST["first_name"], $_POST['email'], $_POST['password'], $_POST['uid']);
        if ($wpRes == true) {
          wp_send_json_success();
        } else {
          wp_send_json_error("WordPress : " . $wpRes);
        }
      } else {
        wp_send_json_success();
      }
    } else {
      wp_send_json_error("LDAP : " . ldap_err2str($ldapRes));
    }
  }
}
function lab_ldap_amu_lookup()
{
  $url = "http://" . AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_HOST)[0]->value . "/getAMUUser.php?token=" . AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_TOKEN)[0]->value . "&query=" . $_POST['query'];
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
  if ($res->count == 0) {
    wp_send_json_error();
  } else {
    $entry = 0;
    wp_send_json_success(array(
      'mail' => $res->$entry->mail->$entry,
      'uid' => $res->$entry->uid->$entry,
      'password' => $res->$entry->userpassword->$entry,
      'first_name' => $res->$entry->givenname->$entry,
      'last_name' => $res->$entry->sn->$entry
    ));
  }
}

function lab_ldap_edit_user()
{
  $uid = $_POST['uid'];
  if (!isset($uid) || $uid == "") {
    wp_send_json_error("No UID");
  }
  $ldap = LAB_LDAP::getInstance(
    AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_HOST)[0]->value,
    AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_BASE)[0]->value,
    AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_LOGIN)[0]->value,
    AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_PASSWORD)[0]->value,
    true
  );
  $ldapRes = $ldap_obj->editUser($_POST['uid'], $_POST['givenname'], $_POST['sn'], $_POST['uidnumber'], $_POST['homeDirectory'], $_POST['mail']);
  wp_send_json_success();
}
function lab_admin_get_userLogin_Req()
{
  $res = lab_admin_get_userLogin($_POST['user_id']);
  if ($res == null) {
    wp_send_json_error();
  } else {
    wp_send_json_success($res);
  }
}
function lab_ldap_user_details()
{
  $uid = $_POST['uid'];
  $ldap = LAB_LDAP::getInstance(
    AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_HOST)[0]->value,
    AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_BASE)[0]->value,
    AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_LOGIN)[0]->value,
    AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_PASSWORD)[0]->value,
    true
  );
  $result   = $ldap_obj->get_info_from_uid($uid);
  wp_send_json_success($result);
}

/**
 * If no role, add a default
 */
function setDefaultRole($userId)
{
  $user = new WP_User($userId);
  if (count($user->get_role_caps()) == 0) {
    $user->add_role('subscriber');
  }
}

function removeAllRoleToUser($userId)
{
  $user = new WP_User($userId);

  if (!empty($user->roles) && is_array($user->roles)) {
    foreach ($user->roles as $role_key => $role_details) {

      $user->remove_role($role_details);
    }
  }
}

function lab_ldap_delete_userReq()
{
  $uid = lab_admin_get_userLogin($_POST['user_id']);
  $keepData = true;
  if (isset($_POST['keepData']) && $_POST['keepData'] == 'false') {
    $keepData = false;
  }
  if (!$keepData) {
    wp_delete_user($_POST['user_id'], 1);
  } else {
    removeAllRoleToUser($_POST['user_id']);
  }

  if (lab_admin_param_is_ldap_enable()) {
    $ldap = LAB_LDAP::getInstance(
      AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_HOST)[0]->value,
      AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_BASE)[0]->value,
      AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_LOGIN)[0]->value,
      AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_PASSWORD)[0]->value,
      true
    );
    $res = ldap_delete_user($ldap, $uid);
    if ($res == 0) {
      wp_send_json_success();
    } else {
      wp_send_json_error("LDAP : " . ldap_err2str($res));
    }
  } else {
    wp_send_json_success();
  }
}

function lab_ldap_reconnect()
{
  if (lab_admin_param_is_ldap_enable()) {
    $ldap = LAB_LDAP::getInstance(
      AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_HOST)[0]->value,
      AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_BASE)[0]->value,
      AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_LOGIN)[0]->value,
      AdminParams::get_params_fromId(AdminParams::PARAMS_LDAP_PASSWORD)[0]->value,
      true
    );
    $str = "URL : " . $ldap->getURL() . " <br> ";
    $str .= "Base : " . $ldap->getBase() . " <br> ";
    $str .= "Login : " . $ldap->getLogin() . " <br> ";
    $str .= "Passwd : " . $ldap->getPassword() . " <br> ";
    if ($ldap->bindAdmin()) {
      wp_send_json_success("Connection to LDAP server successfull");
    } else {
      wp_send_json_error("Failed to connect to LDAP server :<br>" . $str);
    }
  }
  //*/
}

function lab_historic_createTable()
{
  global $wpdb;
  if (lab_admin_createTable_users_historic() === false) {
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
function lab_historic_add()
{
  $res = lab_admin_add_historic(array(
    'user_id' => $_POST['user_id'],
    'ext' => false,
    'begin' => $_POST['begin'],
    'end' => (strlen($_POST['end']) > 1 ? $_POST['end'] : NULL),
    'mobility' => $_POST['mobility'],
    'mobility_status' => $_POST['mobility_status'],
    'host_id' => $_POST['host_id'],
    'function' => $_POST['function'],
  ));
  if ($res === false) {
    global $wpdb;
    wp_send_json_error($wpdb->last_error());
  } else {
    wp_send_json_success();
  }
}

function lab_historic_delete()
{
  //wp_send_json_error(lab_admin_historic_delete($_POST['entry_id']));
  if (lab_admin_historic_delete($_POST['entry_id']) === false) {
    global $wpdb;
    wp_send_json_error($wpdb->last_error);
  } else {
    wp_send_json_success();
  }
}
function lab_historic_getEntry()
{
  $res = lab_admin_historic_get($_POST['entry_id']);
  if ($res === false) {
    wp_send_json_error();
  } else {
    wp_send_json_success($res);
  }
}

function lab_historic_update()
{
  $res = lab_admin_historic_update($_POST['entry_id'], array(
    'user_id' => $_POST['user_id'],
    'ext' => false,
    'begin' => $_POST['begin'],
    'end' => (strlen($_POST['end']) > 1 ? $_POST['end'] : NULL),
    'mobility' => $_POST['mobility'],
    'mobility_status' => $_POST['mobility_status'],
    'host_id' => $_POST['host_id'],
    'function' => $_POST['function']
  ));
  if ($res === false) {
    global $wpdb;
    wp_send_json_error($wpdb->last_error);
  } else {
    wp_send_json_success();
  }
}
function lab_user_getRoles()
{
  if (isset($_POST['user_id'])) {
    wp_send_json_success(lab_admin_user_roles($_POST['user_id']));
  } else {
    wp_send_json_error();
  }
}
function lab_user_addRole()
{
  if (isset($_POST['user_id']) && isset($_POST['role'])) {
    $user = new WP_USER($_POST['user_id']);
    $user->add_role($_POST['role']);
    wp_send_json_success();
  } else {
    wp_send_json_error();
  }
}
function lab_user_delRole()
{
  if (isset($_POST['user_id']) && isset($_POST['role'])) {
    $user = new WP_USER($_POST['user_id']);
    $user->remove_role($_POST['role']);
    wp_send_json_success();
  } else {
    wp_send_json_error();
  }
}
function lab_admin_ldap_settings()
{
  $associations = array(
    'host' => AdminParams::PARAMS_LDAP_HOST,
    'enable' => AdminParams::PARAMS_LDAP_ENABLE,
    'token' => AdminParams::PARAMS_LDAP_TOKEN,
    'base' => AdminParams::PARAMS_LDAP_BASE,
    'login' => AdminParams::PARAMS_LDAP_LOGIN,
    'password' => AdminParams::PARAMS_LDAP_PASSWORD,
    'tls' => AdminParams::PARAMS_LDAP_TLS
  );
  foreach ($associations as $key => $value) {
    if (strlen($_POST[$key][0]) > 0) {
      //@todo corriger le retour de la fonction lab_admin_param_save
      if (lab_admin_param_save($value, $_POST[$key][0], '000001', ($_POST[$key][1]) === false, null)) {
        wp_send_json_error();
      }
    }
  }
  wp_send_json_success(lab_admin_tab_ldap());
}
