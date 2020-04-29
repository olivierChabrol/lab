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
      `id` bigint PRIMARY KEY AUTO_INCREMENT,
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
      `real_cost` float,
      `status` tinyint,
      `creation_time` datetime,
      `completion_time` datetime,
      `validation_time` datetime,
      FOREIGN KEY (`guest_id`) REFERENCES `".$wpdb->prefix."lab_guests` (`id`),
      FOREIGN KEY (`host_id`) REFERENCES `".$wpdb->prefix."users` (`ID`)
    );";
  $wpdb->get_results($sql);
  wp_send_json_success();
}
function lab_invitations_createPrefGroupTable() {
  global $wpdb;
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
  }
  wp_send_json_error();
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
?>