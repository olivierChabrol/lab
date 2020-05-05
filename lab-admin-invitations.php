<?php
function lab_invitations_createTables() {
  global $wpdb;
  $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."lab_guests` (
      `id` bigint UNSIGNED PRIMARY KEY AUTO_INCREMENT,
      `first_name` varchar(50),
      `last_name` varchar(50),
      `email` varchar(100),
      `phone` varchar(20),
      `country` varchar(30)
    );";
  $res = $wpdb->get_results($sql);
  if (!strlen($res)==0) {
    wp_send_json_error();
    return;
  }
  $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."lab_invitations` (
      `id` bigint UNSIGNED PRIMARY KEY AUTO_INCREMENT,
      `guest_id` bigint UNSIGNED,
      `host_id` bigint UNSIGNED,
      `host_group_id` bigint,
      `mission_objective` varchar(255),
      `token` varchar(20),
      `needs_hostel` boolean,
      `start_date` datetime,
      `end_date` datetime,
      `travel_mean_to` varchar(50),
      `travel_mean_from` varchar(50),
      `funding_source` varchar(200),
      `estimated_cost` float,
      `maximum_cost` float,
      `real_cost` float,
      `status` tinyint,
      `creation_time` datetime,
      `completion_time` datetime,
      `validation_time` datetime,
      FOREIGN KEY (`guest_id`) REFERENCES `".$wpdb->prefix."lab_guests` (`id`),
      FOREIGN KEY (`host_id`) REFERENCES `".$wpdb->prefix."users` (`ID`)
    );";
  $res = $wpdb->get_results($sql);
  if (!strlen($res)==0) {
    wp_send_json_error();
    return;
  }
  $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."lab_invite_comments` (
    `id` BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `invite_id` BIGINT UNSIGNED,
    `author` varchar(40),
    `timestamp` datetime,
    `content` text,
    FOREIGN KEY (`invite_id`) REFERENCES `".$wpdb->prefix."lab_invitations`(`id`));";
  $res = $wpdb->get_results($sql);
  if (!strlen($res)==0) {
    wp_send_json_error();
    return;
  }
  $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."lab_prefered_groups` (
    `id` int PRIMARY KEY AUTO_INCREMENT,
    `group_id` bigint UNSIGNED,
    `user_id` bigint UNSIGNED,
    FOREIGN KEY (`group_id`) REFERENCES `".$wpdb->prefix."lab_groups` (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `".$wpdb->prefix."users` (`id`)
  );";
  if (strlen($wpdb->get_results($sql))==0) {
    wp_send_json_success();
    return;
  } else {
    wp_send_json_error();
  }
}
function lab_invitations_createGuest($params) {
  global $wpdb;
  $wpdb->hide_errors();
  if ( $wpdb->insert(
      $wpdb->prefix.'lab_guests',
      $params
      )
  ) {
      return $wpdb->insert_id;
  } else {
      return $wpdb -> last_error;
  }
}
function lab_invitations_createInvite($params) {
  global $wpdb;
  if ( $wpdb->insert(
      $wpdb->prefix.'lab_invitations',
      $params
      )
  ) {
      return;
  } else {
      return $wpdb -> last_error;
  }
}
function lab_invitations_editGuest($id, $params) {
  global $wpdb;
  return $wpdb->update(
    $wpdb->prefix.'lab_guests',
    $params,
    array('id' => $id)
  );
}
function lab_invitations_editInvitation($token, $params) {
  global $wpdb;
  return $wpdb->update(
    $wpdb->prefix.'lab_invitations',
    $params,
    array('token' => $token)
  );
}
function lab_invitations_getByToken($token) {
  global $wpdb;
  $sql = "SELECT * FROM `".$wpdb->prefix."lab_invitations` WHERE token='".$token."';";
  $res = $wpdb->get_results($sql);
  return $res[0];
}
function lab_invitations_getGuest($id) {
  global $wpdb;
  $sql = "SELECT * FROM `".$wpdb->prefix."lab_guests` WHERE id='".$id."';";
  $res = $wpdb->get_results($sql);
  return $res[0];
}
function lab_invitations_getByGroup($group_id,$params=array()) {
  global $wpdb;
  $sql = "SELECT * FROM `".$wpdb->prefix."lab_invitations` WHERE `host_group_id`=".$group_id.";";
  $res = $wpdb->get_results($sql);
  return $res;
}
function lab_invitations_getByHost($host_id,$params=array()) {
  global $wpdb;
  $sql = "SELECT * FROM `".$wpdb->prefix."lab_invitations` WHERE `host_id`=".$host_id.";";
  $res = $wpdb->get_results($sql);
  return $res;
}
function lab_invitations_getPrefGroups($user_id,$params=array()) {
  global $wpdb;
  $sql = "SELECT * FROM `".$wpdb->prefix."lab_groups`, `".$wpdb->prefix."ab_prefered_groups`
  WHERE `".$wpdb->prefix."lab_groups`.`id`=`".$wpdb->prefix."lab_prefered_groups`.`group_id`
  AND `".$wpdb->prefix."lab_prefered_groups`.`user_id`=".$user_id.";";
// $sql2 = "SELECT g1.* AS all
//     FROM `wp_lab_groups` AS g1 
//     JOIN `wp_lab_prefered_groups` ON `wp_lab_groups`.`id`=`wp_lab_prefered_groups`.`group_id`
//     WHERE `wp_lab_prefered_groups`.`user_id`=1;";
  $res = $wpdb->get_results($sql);
  return $res;
}
function lab_invitations_getComments($id) {
  global $wpdb;
  $sql = "SELECT * FROM `".$wpdb->prefix."lab_invite_comments` WHERE `invite_id`=".$id.";";
  $res = $wpdb->get_results($sql);
  return $res;
}
function lab_invitations_addComment($fields) {
  global $wpdb;
  return $wpdb->insert($wpdb->prefix."lab_invite_comments",
  $fields);
}
?>