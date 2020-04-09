<?php


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

/**
 * Fonction qui répond a la requete d'un recherche par nom de groupe
 */
function lab_admin_group_search() {
    $search = $_POST['search'];
    $groupName  = $search["term"];

    $sql = "SELECT id, group_name FROM `wp_lab_groups` WHERE `group_name` LIKE '%".$groupName."%' ";
    global $wpdb;
    $results = $wpdb->get_results($sql);
    $items = array();
    $url = esc_url(home_url('/'));
    foreach ( $results as $r )
    {
      $items[] = array(label=>$r->group_name, value=>$r->id);
    }
    wp_send_json_success( $items ); 
}

function lab_admin_group_delete(){
    $group_id = $_POST['id'];
    global $wpdb;
    $wpdb->delete('wp_lab_groups', array('id' => $group_id));
    wp_send_json_success();
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
