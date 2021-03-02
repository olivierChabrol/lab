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
    return $res;
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
      `charges` JSON,
      `research_contract` text,
      FOREIGN KEY (`guest_id`) REFERENCES `".$wpdb->prefix."lab_guests` (`id`),
      FOREIGN KEY (`host_id`) REFERENCES `".$wpdb->prefix."users` (`ID`)
    );";
  $res = $wpdb->get_results($sql);
  if (!strlen($res)==0) {
    return $res;
  }
  $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."lab_invite_comments` (
    `id` BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `invite_id` BIGINT UNSIGNED,
    `author_id` BIGINT UNSIGNED,
    `author_type` tinyint,
    `timestamp` datetime,
    `content` text,
    FOREIGN KEY (`invite_id`) REFERENCES `".$wpdb->prefix."lab_invitations`(`id`));";
  $res = $wpdb->get_results($sql);
  if (!strlen($res)==0) {
    return $res;
  }
  $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."lab_prefered_groups` (
    `id` int PRIMARY KEY AUTO_INCREMENT,
    `group_id` bigint UNSIGNED,
    `user_id` bigint UNSIGNED,
    FOREIGN KEY (`group_id`) REFERENCES `".$wpdb->prefix."lab_groups` (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `".$wpdb->prefix."users` (`id`)
  );";
  if (strlen($wpdb->get_results($sql))==0) {
    //wp_send_json_success();
    return;
  } else {
    return $res;
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
function lab_mission_create($params) {
  global $wpdb;
  if ( $wpdb->insert(
      $wpdb->prefix.'lab_invitations',
      $params
      )
  ) {
    return $wpdb->insert_id;
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
function lab_invitations_editInvitation($missionId, $params) {
  global $wpdb;
  return $wpdb->update(
    $wpdb->prefix.'lab_invitations',
    $params,
    array('id' => $missionId)
  );
}

function lab_invitations_getByToken($token, $deleteNotif = true) {
  global $wpdb;
  $sql = "SELECT * FROM `".$wpdb->prefix."lab_invitations` WHERE token='".$token."';";
  $res = $wpdb->get_results($sql);
  $missionId = $res[0]->id;
  if($deleteNotif) {
    lab_mission_resetNotifs($missionId);
  }
  if(lab_invitations_getBudgetManager()) {
    //lab_mission_take_in_charge($missionId);
  }
  return $res[0];
}

function lab_group_manager($type) {
  global $wpdb;
  $sql = "SELECT user_id FROM `".$wpdb->prefix."lab_group_manager` WHERE manager_type = ".$type;
  $res = $wpdb->get_results($sql);
  $tab = array();
  foreach($res as $r) {
    $tab[] = $r->user_id;
  }
  return $tab;
}


//fonction qui récupère les trajets d'une mission à partir du token (inutilisée)
/*function lab_mission_route_get($token)
{
  global $wpdb;
  $sql = "SELECT * FROM `".$wpdb->prefix."lab_mission_route` WHERE 'mission_id' IN (SELECT id FROM `".$wpdb->prefix."lab_invitations` WHERE token='".$token."';";
  $res = $wpdb->get_results($sql);
  return $res;
}*/

/**
 * Check if guest user exist by his email
 *
 * @param [type] $email
 * @return false, if this email not present in DB, guest object otherwise
 */
function lab_invitations_guest_email_exist($email) {
  global $wpdb;
  $sql = "SELECT * FROM `".$wpdb->prefix."lab_guests` WHERE email='".$email."';";
  $res = $wpdb->get_results($sql);
  if (count($res) == 0)
  {
    return false;
  }
  else {
    return $res[0];
  }
}
function lab_invitations_getGuest($id) {
  if ($id == null) {
    return null;
  }
  global $wpdb;
  $sql = "SELECT * FROM `".$wpdb->prefix."lab_guests` WHERE id='".$id."';";
  $res = $wpdb->get_results($sql);
  return $res[0];
}
function lab_invitations_getGuest_byName($firstname, $lastname) {
  global $wpdb;
  $sql = "SELECT * FROM `".$wpdb->prefix."lab_guests` WHERE first_name='".$firstname."' AND last_name='".$lastname."';";
  $res = $wpdb->get_results($sql);
  return $res[0];
}
function lab_invitations_getByGroup($group_id,$params=array("sortBy"=>"start_date","order"=>"DESC","page"=>"1","value"=>"5","status"=>"(1,10,20,30)","year"=>"all")) {
  $page_nb = ($params['page'] - 1) * $params['value'];
  global $wpdb;
  $sql = "SELECT * FROM `".$wpdb->prefix."lab_invitations` 
          WHERE `host_group_id`=".$group_id." 
          AND `status` in ".$params['status']
          .($params['year']=="all" ? ' ' : " AND `start_date` BETWEEN '".$params['year']."-01-01' AND '".$params['year']."-12-31' ")."
          ORDER BY ".$params['sortBy'].' '.$params['order']." 
          LIMIT ".$page_nb.", ".$params['value']." ;";
  $count = "SELECT COUNT(*) FROM `".$wpdb->prefix."lab_invitations` 
            WHERE `host_group_id`=".$group_id." 
            AND `status` in ".$params['status']
            .($params['year']=="all" ? ' ' : " AND `start_date` BETWEEN '".$params['year']."-01-01' AND '".$params['year']."-12-31' ").";";
  $res_sql = $wpdb->get_results($sql);
  $res_count = $wpdb->get_var($count);
  $result = array($res_count, $res_sql);
  return $result;
}
function lab_invitations_getByGroups($groups_ids,$params=array("sortBy"=>"start_date","order"=>"DESC","page"=>"1","value"=>"5","status"=>"(1,10,20,30)","year"=>"all")) {
  $page_nb = ($params['page'] - 1) * $params['value'];
  global $wpdb;
  foreach ($groups_ids as $g) {
    $str .= ' host_group_id='.$g." OR";
  }
  $str = '('.substr($str,0, -3).')'; //Enlève le dernier OR, rajoute des parenthèses
  $sql = "SELECT * FROM `".$wpdb->prefix."lab_invitations` 
          WHERE".$str." 
          AND `status` in ".$params['status']
          .($params['year']=="all" ? ' ' : " AND `start_date` BETWEEN '".$params['year']."-01-01' AND '".$params['year']."-12-31' ")."
          ORDER BY ".$params['sortBy']." ".$params['order']." 
          LIMIT ".$page_nb.", ".$params['value']." ;";
  $count = "SELECT COUNT(*) FROM `".$wpdb->prefix."lab_invitations` 
            WHERE".$str." 
            AND `status` in ".$params['status']
            .($params['year']=="all" ? ' ' : " AND `start_date` BETWEEN '".$params['year']."-01-01' AND '".$params['year']."-12-31' ").";";
  $res_sql = $wpdb->get_results($sql);
  $res_count = $wpdb->get_var($count);
  $result = array($res_count, $res_sql);
  return $result;
}
function lab_invitations_getByHost($host_id,$params=array("sortBy"=>"start_date","order"=>"DESC","page"=>"1","value"=>"5","status"=>"(1,10,20,30)","year"=>"all")) {
  $page_nb = ($params['page'] - 1) * $params['value'];
  global $wpdb;
  $sql = "SELECT * FROM `".$wpdb->prefix."lab_invitations` 
          WHERE `host_id`=".$host_id." 
          AND `status` in ".$params['status']
          .($params['year']=="all" ? ' ' : " AND `start_date` BETWEEN '".$params['year']."-01-01' AND '".$params['year']."-12-31' ")."
          ORDER BY ".$params['sortBy']." ".$params['order']." LIMIT ".$page_nb.", ".$params['value']." ;";
  $count = "SELECT COUNT(*) FROM `".$wpdb->prefix."lab_invitations` 
            WHERE `host_id`=".$host_id." 
            AND `status` in ".$params['status']
            .($params['year']=="all" ? ' ' : " AND `start_date` BETWEEN '".$params['year']."-01-01' AND '".$params['year']."-12-31' ").";";
  $res_sql = $wpdb->get_results($sql);
  $res_count = $wpdb->get_var($count);
  $result = array($res_count, $res_sql);
  return $result;
}
function lab_invitations_getPrefGroups($user_id,$params=array()) {
  global $wpdb;
  $sql = "SELECT * FROM `".$wpdb->prefix."lab_groups`, `".$wpdb->prefix."lab_prefered_groups`
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
function lab_invitations_addComment($fields, $commentGeneratedBy = 0) {
  global $wpdb;
  $sql = $wpdb->insert($wpdb->prefix."lab_invite_comments", $fields);
  $last_comment_id = $wpdb->insert_id;
  $concerned = lab_invitations_getConcernedId($fields["invite_id"]);
  
  foreach($concerned as $key){
    // if generated by system
    if ($fields["author_id"] == 0 )
    {
      if ($commentGeneratedBy != $key)
      {
        lab_invitations_comment_notif($key, $fields["invite_id"], $last_comment_id);
      }
    }
    else if($fields["author_id"] != $key) {
      lab_invitations_comment_notif($key, $fields["invite_id"], $last_comment_id);
    }
  }
  return $concerned;
}
function lab_invitations_comment_notif($user_id, $invite_id, $comment_id) {
  global $wpdb;
  return $wpdb->insert($wpdb->prefix."lab_mission_comment_notifs", array("user_id"=>$user_id, "invite_id"=>$invite_id, "comment_id"=>$comment_id));
}

function lab_invitations_getConcernedId($invite_id) {
  global $wpdb;
  $sql = "SELECT inv.host_id, inv.guest_id, m.user_id AS `manager_id`, inv.manager_id AS `mission_manager_id`
          FROM `".$wpdb->prefix."lab_invitations` AS inv
          JOIN `".$wpdb->prefix."lab_group_manager` AS m ON  m.group_id = inv.host_group_id
          WHERE inv.id = ".$invite_id.";";
  $res = $wpdb->get_results($sql);

  $tab_ids = array();
  foreach($res as $c) {
    $host_id = $c->host_id;
    $guest_id = $c->guest_id;
    $manager_id = $c->manager_id;
    $mission_manager_id = $c->mission_manager_id;
    $tab_ids[$host_id] = "";
    if(!is_null($guest_id)) {
      $tab_ids[$guest_id] = "";
    }
    if ($mission_manager_id != 0) {
      $tab_ids[$mission_manager_id] = "";
    }
    $tab_ids[$manager_id] = "";
  }
  $retour = array();
  foreach($tab_ids as $key=>$value){
    $retour[] = $key;
  }

  return $retour;
}

function lab_invitations_getBudgetManager() {
  global $wpdb;
  $sql = "SELECT m.user_id AS `manager_id`
          FROM `".$wpdb->prefix."lab_group_manager` AS m
          WHERE m.manager_type = 1";
  $res = $wpdb->get_results($sql);
  $tab = array();
  foreach($res as $r) {
    $tab[] = (int)($r->manager_id);
  }
  return $tab;
}

function lab_invitation_is_budget_manager() {
  global $wpdb;
  $sql = "SELECT * 
          FROM `".$wpdb->prefix."lab_group_manager` AS m
          WHERE m.manager_type = 1 AND user_id = ".get_current_user_id();
  $res = $wpdb->get_results($sql);
  return count($res) > 0;
}








function lab_admin_mission_getNotifs($user_id, $mission_id = null) {
  global $wpdb;
  $sql = "SELECT COUNT(*) AS notifs_number FROM `".$wpdb->prefix."lab_mission_comment_notifs` WHERE `user_id`=".$user_id;
  if($mission_id != null) {
    $sql .= " AND `invite_id`=".$mission_id;
  }
  $res = $wpdb->get_results($sql);
  return $res;
}

function lab_mission_resetNotifs($mission_id) {
  $user_id = get_current_user_id();
  lab_admin_mission_user_resetNotifs($user_id, $mission_id);
}

function lab_admin_mission_user_resetNotifs($user_id, $mission_id) {
  global $wpdb;
  return $wpdb->delete($wpdb->prefix."lab_mission_comment_notifs", array("user_id"=>$user_id, "invite_id"=>$mission_id));
}
?>
