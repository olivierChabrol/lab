<?php


function lab_mission_status_to_value($missionStatus) {
    $status = array();
    $status["c"] = 4;
    $status["wmv"] = 3;
    $status["wlv"] = 2;
    $status["w"] = 1;
    return $status[$missionStatus];
}
    
function lab_mission_delete($missionId) {
    global $wpdb;
    $wpdb->delete($wpdb->prefix."lab_invite_comments", array('invite_id' => $missionId));
    $wpdb->delete($wpdb->prefix.'lab_invitations', array('id' => $missionId));
    $wpdb->delete($wpdb->prefix.'lab_mission_route', array('mission_id' => $missionId));
    return true;
}

function lab_mission_set_status($missionId, $status) {
    $statusCode = AdminParams::get_param_by_slug($status);
    lab_invitations_editInvitation($missionId, array("status"=>$statusCode));
}

function lab_mission_set_budget_manager($missionId, $managerId) {
    lab_invitations_editInvitation($missionId, array("manager_id"=>$managerId));
}

function lab_mission_get_budget_manager($missionId) {
    global $wpdb;
    $results = $wpdb->get_results("SELECT manager_id FROM `".$wpdb->prefix."lab_invitations` WHERE id=".$missionId);
    if (count($results) == 1) {
        return $results[0]->manager_id;
    }
    return -1;
}

function lab_mission_get_id_by_token($token) {
    global $wpdb;
    $sql = "SELECT id FROM `".$wpdb->prefix."lab_invitations` WHERE token = '".$token."'";
    $results = $wpdb->get_results($sql);
    if (count($results) > 0) {
        return $results[0]->id;
    }
    return null;
}

function lab_mission_take_in_charge($missionId)
{
    $currentUserId = get_current_user_id();
    $managerId = lab_mission_get_budget_manager($missionId);
    
    if ($managerId == -1 || $managerId != $currentUserId) 
    {
        lab_mission_set_budget_manager($missionId, $currentUserId);
        $user = lab_admin_userMetaDatas_get($currentUserId);
        date_default_timezone_set("Europe/Paris");
        $timeStamp=date("Y-m-d H:i:s",time());
        lab_invitations_addComment(array(
            'content'=> "¤Invitation prise en charge par ".$user['first_name']." ".$user['last_name'],
            'timestamp'=> $timeStamp,
            'author_id'=> 0,
            'author_type'=> 0,
            'invite_id'=> $missionId
        )); 
        lab_mission_set_status($missionId, "mswgm");
    }
}

function lab_mission_get_token_from_id($missionId) {
    global $wpdb;
    $sql = "SELECT token FROM `".$wpdb->prefix."lab_invitations` WHERE id = ".$missionId;
    return $wpdb->get_results($sql);
}

function lab_mission_load($missionToken, $filters = null, $groupIds = null) {
    global $wpdb;
    $data = array();
    $data["filters"] = array();

    $budgetManagerGroupIds = lab_admin_group_is_manager();
    $leaderManagerGroupIds = lab_admin_group_is_manager(null, 2);
    $isBudgetManager  = count($budgetManagerGroupIds) > 0;
    $isLeaderOfAGroup = count($leaderManagerGroupIds) > 0;

    // by default load current year
    if ($filters == null)
    {
        $filters["year"] = date("Y");
    }
    
    $select = "SELECT m.*,param.value AS site ";
    $from = " FROM `".$wpdb->prefix."lab_invitations` as m"; 
    $join = " JOIN ".$wpdb->prefix."usermeta AS site ON site.user_id = m.host_id 
    JOIN ".$wpdb->prefix."lab_params AS param ON param.id = site.meta_value";
    $where = " WHERE site.meta_key='lab_user_location'";
    if ($groupIds != null && isset($groupIds)) {
        $where .= " AND (";
        foreach ($groupIds as $groupId) {
            $where .= "m.host_group_id=";
            $where .= $groupId;
            $where .= " OR ";
        }
        $where = substr($where, 0, strlen($where) - 4);
        $where .= ")";
    }
    if ($missionToken != null OR $filters != null) {
        $where .= " AND ";
          
        if ($missionToken != null) {
            $where .= "token='".$missionToken."'";
        }
        else {

            $nbFilter = 0;
            foreach($filters as $key=>$value) {
                if ($key == "year") {
                    if ($nbFilter > 0) {
                        $where .= " AND ";
                    }
                    $where .= "YEAR(m.`creation_time`)=".$value."";
                    $data["filters"]["year"] = $value;
                }
                if ($key == "site") {
                    if ($nbFilter > 0) {
                        $where .= " AND ";
                    }
                    $where .= "YEAR(m.`creation_time`)=".$value."";
                    $data["filters"]["year"] = $value;
                }
                else if ($key == "budget_manager") {
                    if ($nbFilter > 0) {
                        $where .= " AND ";
                    }
                    $select .= ", m.manager_id";
                    $where .= "m.manager_id=".$value."";
                    $data["filters"]["budget_manager"] = $value;
                }
                else if ($key == "state") {
                    if ($nbFilter > 0) {
                        $where .= " AND ";
                    }
                    $where .= "m.status=".lab_mission_status_to_value($value)."";
                    $data["filters"]["state"] = $value;
                }
                $nbFilter += 1;
            }
        }
    }
    $order = " ORDER BY m.`creation_time` DESC";
    if (!$isBudgetManager) {
        if (!$isLeaderOfAGroup) {
            $where .= " AND m.host_id=".get_current_user_id();
        }
    }
    $sql = $select. $from .$join. $where. $order;
    $results = $wpdb->get_results($sql);

    // load user info
    $userIds = array();
    $params = array();
    $paramsToGet = ["mission_objective", "status"];
    $notifs = array();
    $current_user_id = get_current_user_id();
    foreach($results as $r) {
        $notifs[$r->id] = lab_admin_mission_getNotifs($current_user_id, $r->id);
        if(!isset($userIds[$r->host_id]) && $r->host_id != 0) {
            $userIds[$r->host_id] = lab_admin_usermeta_names($r->host_id);
            /*
            $groups = $wpdb->get_results("SELECT ug.user_id, g.acronym, gm.user_id as manager_id FROM `".$wpdb->prefix."lab_users_groups` AS ug JOIN ".$wpdb->prefix."lab_groups AS g ON g.id=ug.group_id JOIN ".$wpdb->prefix."lab_group_manager AS gm ON gm.group_id=g.id WHERE ug.user_id=".$r->host_id." AND gm.manager_type=1");
            if (count($groups)> 0) {
                $userIds[$r->host_id]->group = $groups[0]->acronym;
                $userIds[$r->host_id]->manager_id = $groups[0]->manager_id;
                if(!isset($userIds[$groups[0]->manager_id]) && $groups[0]->manager_id != 0) {
                    $userIds[$groups[0]->manager_id] = lab_admin_usermeta_names($groups[0]->manager_id);
                }
            }
            //*/
            if ($r->manager_id != null) {
                $userIds[$r->manager_id] = lab_admin_usermeta_names($r->manager_id);
            }
        }
        $r->group = $userIds[$r->host_id]->group;
        //$r->manager_id = $userIds[$r->host_id]->manager_id;

        # get all params associated to the mission see @$paramsToGet
        foreach($paramsToGet as $ptg) {
            if (!isset($params[$r->$ptg])) {
                $params[$r->$ptg] = AdminParams::get_full_param($r->$ptg);
            }

        }
    }


    if ($budgetId== null) {
        $data["results"] = $results;
    }
    else {
        $data["results"] = $results[0];
    }
    $data["users"] = $userIds;
    $data["params"] = $params;
    $data["notifs"] = $notifs;

    $sqlYear = "SELECT DISTINCT YEAR(`creation_time`) AS year FROM `".$wpdb->prefix."lab_invitations` ORDER BY `year` DESC";
    $results = $wpdb->get_results($sqlYear);

    $years   = array();
    $currentYear = date("Y");
    $years[] = $currentYear;
    foreach ($results as $r) {
        if ($r->year != null && $r->year != $currentYear && $r->year != 0) {
            $years[] = $r->year;
        }
    }
    $data["years"] = $years;
    $data["sql"] = $sql;
    return $data;
}