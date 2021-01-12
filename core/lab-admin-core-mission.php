<?php


function lab_mission_status_to_value($missionStatus) {
    $status = array();
    $status["c"] = 4;
    $status["wmv"] = 3;
    $status["wlv"] = 2;
    $status["w"] = 1;
    return $status[$missionStatus];
}

function lab_mission_load($missionToken, $filters = null) {
    global $wpdb;
    $data = array();
    $data["filters"] = array();
    $sql = "SELECT m.* from ".$wpdb->prefix."lab_invitations AS m";
    if ($missionToken != null OR $filters != null) {
        $sql .= " WHERE ";
          
        if ($missionToken != null) {
            $sql .= "token='".$missionToken."'";
        }
        else {

            $nbFilter = 0;
            foreach($filters as $key=>$value) {
                if ($key == "year") {
                    if ($nbFilter > 0) {
                        $sql .= " AND ";
                    }
                    $sql .= "YEAR(m.`creation_time`)=".$value."";
                    $data["filters"]["year"] = $value;
                }
                else if ($key == "state") {
                    if ($nbFilter > 0) {
                        $sql .= " AND ";
                    }
                    $sql .= "status=".lab_mission_status_to_value($value)."";
                    $data["filters"]["state"] = $value;
                }
                $nbFilter += 1;
            }
        }
    }
    $sql .= " ORDER BY `creation_time` DESC";
    $results = $wpdb->get_results($sql);

    // load user info
    $userIds = array();
    $params = array();
    foreach($results as $r) {
        if(!isset($userIds[$r->host_id]) && $r->host_id != 0) {
            $userIds[$r->host_id] = lab_admin_usermeta_names($r->host_id);
            $groups = $wpdb->get_results("SELECT ug.user_id, g.acronym FROM `".$wpdb->prefix."lab_users_groups` AS ug JOIN ".$wpdb->prefix."lab_groups AS g ON g.id=ug.group_id WHERE ug.user_id=".$r->host_id);
            if (count($groups)> 0) {
                $userIds[$r->host_id]->group = $groups[0]->acronym;
            }
        }
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