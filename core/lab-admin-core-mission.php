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

function lab_mission_load($missionToken, $filters = null, $groupIds = null) {
    global $wpdb;
    $data = array();
    $data["filters"] = array();
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
                    $select .= ", gm.user_id as manager_id";
                    $join .= " JOIN ".$wpdb->prefix."lab_group_manager AS gm ON gm.group_id = m.host_group_id";
                    $where .= "gm.manager_type=1 AND gm.user_id=".$value."";
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
    $sql = $select. $from .$join. $where. $order;
    $results = $wpdb->get_results($sql);

    // load user info
    $userIds = array();
    $params = array();
    foreach($results as $r) {
        if(!isset($userIds[$r->host_id]) && $r->host_id != 0) {
            $userIds[$r->host_id] = lab_admin_usermeta_names($r->host_id);
            $groups = $wpdb->get_results("SELECT ug.user_id, g.acronym, gm.user_id as manager_id FROM `".$wpdb->prefix."lab_users_groups` AS ug JOIN ".$wpdb->prefix."lab_groups AS g ON g.id=ug.group_id JOIN ".$wpdb->prefix."lab_group_manager AS gm ON gm.group_id=g.id WHERE ug.user_id=".$r->host_id." AND gm.manager_type=1");
            if (count($groups)> 0) {
                $userIds[$r->host_id]->group = $groups[0]->acronym;
                $userIds[$r->host_id]->manager_id = $groups[0]->manager_id;
                if(!isset($userIds[$groups[0]->manager_id]) && $groups[0]->manager_id != 0) {
                    $userIds[$groups[0]->manager_id] = lab_admin_usermeta_names($groups[0]->manager_id);
                }
            }
        }
        $r->group = $userIds[$r->host_id]->group;
        $r->manager_id = $userIds[$r->host_id]->manager_id;

        if (!isset($params[$r->mission_objective])) {
            $params[$r->mission_objective] = AdminParams::get_full_param($r->mission_objective);
        }
        /*
        if(!isset($userIds[$r->info_manager_id]) && $r->info_manager_id != 0) {
            $userIds[$r->info_manager_id] = lab_admin_usermeta_names($r->info_manager_id);
        }
        if(!isset($userIds[$r->budget_manager_id]) && $r->budget_manager_id != 0) {
            $userIds[$r->budget_manager_id] = lab_admin_usermeta_names($r->budget_manager_id);
        }
        //*/
    }


    if ($budgetId== null) {
        $data["results"] = $results;
    }
    else {
        $data["results"] = $results[0];
    }
    $data["users"] = $userIds;
    $data["params"] = $params;

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