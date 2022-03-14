<?php
    !defined('LAB_REQUEST_HISTORIC_CANCEL') && define('LAB_REQUEST_HISTORIC_CANCEL', -1);
    !defined('LAB_REQUEST_HISTORIC_NEW') && define('LAB_REQUEST_HISTORIC_NEW', 1);
    !defined('LAB_REQUEST_HISTORIC_NEW') && define('LAB_REQUEST_HISTORIC_UPDATE', 2);
    !defined('LAB_REQUEST_HISTORIC_TAKE_IN_CHARGE') && define('LAB_REQUEST_HISTORIC_TAKE_IN_CHARGE', 10);

function lab_request_get_own_requests() {
    global $wpdb;
    $sql = "SELECT lr.*, lrh.date FROM ".$wpdb->prefix."lab_request AS lr LEFT JOIN ".$wpdb->prefix."lab_request_historic AS lrh On lrh.request_id = lr.id WHERE lr.request_user_id = ".get_current_user_id()." AND lrh.historic_type=1 ";
    $results = $wpdb->get_results($sql);
    return $results;
}

function lab_request_list_requests($filters) {
    global $wpdb;
    $sql = "SELECT lr.*, lrh.date, um1.meta_value as last_name, um2.meta_value as first_name 
              FROM ".$wpdb->prefix."lab_request AS lr ";
    $join = " LEFT JOIN ".$wpdb->prefix."lab_request_historic AS lrh On lrh.request_id = lr.id LEFT JOIN ".$wpdb->prefix."usermeta AS um1 On um1.user_id=lr.request_user_id LEFT JOIN ".$wpdb->prefix."usermeta AS um2 On um2.user_id=lr.request_user_id ";
    $where = " WHERE lrh.historic_type=1 AND um1.meta_key='last_name' AND um2.meta_key='first_name' ";
    $isAdmin = lab_is_admin();
    $isGroupLeader = lab_is_group_leader();

    if (!$isAdmin && $isGroupLeader) {
        $filters["group"]=$isGroupLeader[0];
    }
    if(isset($filters)) {
        foreach($filters as $key => $value) {
            if ($key == "group") {
                if (empty($where)) {
                    $where .= " WHERE ";
                }
                else {
                    $where .= " AND ";
                }
                $join  .= " LEFT JOIN wp_lab_users_groups AS lug On lug.user_id=lr.request_user_id ";
                $where .= " lug.group_id=".$value;
            }
        }
    }
    $sql .= $join.$where;

    $results = $wpdb->get_results($sql);
    return ["results"=>$results, "sql"=>$sql, "filters"=>$filters];
}

function lab_request_delete_file($fileId) {
    //TODO faire le control de qui peut supprimer des fichiers
    global $wpdb;
    $files = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."lab_request_files WHERE id=".$fileId);
    if (count($files) != 1) {
        return false;
    }
    else {
        $reqDir = __DIR__;
        $fileName = $reqDir ."/../". substr($files[0]->url, strpos($files[0]->url, "requests/"));
        //return $reqDir . substr($files[0]["url"], strpos($files[0]["url"], "requests/"));

        //$fileName = $reqDir . substr($files[0]["url"], strpos($files[0]["url"], "requests/"));

        if (!unlink($fileName)) {
            return false;
        }
        else {
            return $wpdb->delete($wpdb->prefix."lab_request_files", array("id"=>$fileId));
        } 
    }
}

function lab_request_expenses_delete_by_request($request_id) {
    global $wpdb;
    return $wpdb->get_results("DELETE FROM ".$wpdb->prefix."lab_request_expenses WHERE request_id=".$request_id);
}
function lab_request_expenses_delete($id) {
    global $wpdb;
    return $wpdb->get_results("DELETE FROM ".$wpdb->prefix."lab_request_expenses WHERE id=".$request_id);
}

function lab_request_expenses_load_by_request($request_id) {
    global $wpdb;
    return $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."lab_request_expenses WHERE request_id=".$request_id);
}
function lab_request_expenses_load($id) {
    global $wpdb;
    return $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."lab_request_expenses WHERE id=".$request_id);
}

function lab_request_expenses_save($id, $request_id, $type, $name, $amount) {
    global $wpdb;
    if(isset($id)) {
        $wpdb->update($wpdb->prefix."lab_request_expenses", array("request_id"=>$request_id, "type"=>$type, "name"=>$name, "amount"=>$amount), array("id"=>$id));
        return $id;
    }
    else {
        $wpdb->insert($wpdb->prefix."lab_request_expenses", array("request_id"=>$request_id, "type"=>$type, "name"=>$name, "amount"=>$amount));
        return $wpdb->insert_id;
    }

}

function lab_request_save_file($request_id, $url, $name) {
    global $wpdb;
    $wpdb->insert($wpdb->prefix."lab_request_files", array("request_id"=>$request_id, "url"=>$url, "name"=>$name));
    return $wpdb->insert_id;
}

function lab_request_save($request_id, $request_user_id, $request_type, $request_title, $request_text, $previsional_date, $expenses = null) {
    global $wpdb;
    if (isset($request_id) && $request_id) {
        $wpdb->update($wpdb->prefix."lab_request", array("request_type" => $request_type, "request_title"=>$request_title, "request_text" => $request_text, "request_previsional_date"=>$previsional_date), array("id" => $request_id));
        lab_request_add_historic_update_request($request_id, get_current_user_id());

        foreach($expenses as $expense) {
            lab_request_expenses_save(null, $request_id, $expense["type"], $expense["name"], $expense["amount"]);
        }
        return $request_id;
    }
    else {
        $wpdb->insert($wpdb->prefix."lab_request", array("request_user_id"=>$request_user_id, "request_type" => $request_type, "request_title"=>$request_title, "request_text" => $request_text, "request_previsional_date"=>$previsional_date));
        $request_id = $wpdb->insert_id;
        lab_request_add_historic_new_request($request_id, get_current_user_id());
        lab_request_send_request_to_manager($request_id);
        if(isset($expenses)) {
            foreach($expenses as $expense) {
                lab_request_expenses_save(null, $request_id, $expense["type"], $expense["name"], $expense["amount"]);
            }
        }
        return $request_id;
    }
}

function lab_request_update_state($request_id, $new_state) {
    global $wpdb;
    $wpdb->update($wpdb->prefix."lab_request", array("request_state"=>$new_state), array("id" => $request_id));
    return $request_id;
}

function lab_request_delete($request_id) {
    global $wpdb;
    lab_request_delete_historic($request_id);
    lab_request_expenses_delete_by_request($request_id);
    $wpdb->delete($wpdb->prefix."lab_request", array("request_id"=>$request_id));
}

function lab_request_delete_files($request_id) {
    global $wpdb;
    $wpdb->delete($wpdb->prefix."lab_request_files", array("request_id"=>$request_id));
}

function lab_request_get_associated_files($request_id) {
    global $wpdb;
    return $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."lab_request_files WHERE request_id=".$request_id);
}

function lab_request_delete_historic($request_id) {
    global $wpdb;
    $wpdb->delete($wpdb->prefix."lab_request_historic", array("request_id"=>$request_id));
}

function lab_request_get_by_id($request_id) {
    global $wpdb;
    $results = $wpdb->get_results("SELECT lr.*, um1.meta_value as last_name, um2.meta_value as first_name FROM ".$wpdb->prefix."lab_request as lr LEFT JOIN ".$wpdb->prefix."usermeta AS um1 On um1.user_id=lr.request_user_id LEFT JOIN ".$wpdb->prefix."usermeta AS um2 On um2.user_id=lr.request_user_id WHERE id=".$request_id." AND um1.meta_key='last_name' AND um2.meta_key='first_name'");
    if(isset($results) && count($results) == 1) {
        lab_request_add_historic_cancel_request($request_id, get_current_user_id());
        $results[0]->files = lab_request_get_associated_files($request_id);
        $results[0]->groups = lab_group_get_user_group_information($results[0]->request_user_id);
        $results[0]->historic = lab_request_hisoric_load($request_id);
        $reqults[0]->expenses = lab_request_expenses_load_by_request($request_id);
        return $results[0];
    }
    else{
        return null;
    }
}

function lab_request_cancel($request_id) {
    global $wpdb;
    lab_request_update_state($request_id, LAB_REQUEST_HISTORIC_CANCEL);
    return lab_request_get_by_id($request_id);
}

function lab_request_add_historic_cancel_request($request_id, $user_id) {
    return lab_request_add_historic($request_id, LAB_REQUEST_HISTORIC_CANCEL, $user_id);
}
function lab_request_add_historic_new_request($request_id, $user_id) {
    return lab_request_add_historic($request_id, LAB_REQUEST_HISTORIC_NEW, $user_id);
}
function lab_request_add_historic_update_request($request_id, $user_id) {
    return lab_request_add_historic($request_id, LAB_REQUEST_HISTORIC_UPDATE, $user_id);
}

function lab_request_add_historic_take_in_charge($request_id, $user_id) {
    return lab_request_add_historic($request_id, LAB_REQUEST_HISTORIC_TAKE_IN_CHARGE, $user_id);
}

function lab_request_add_historic($request_id, $historic_type, $user_id) {
    global $wpdb;
    $wpdb->insert($wpdb->prefix."lab_request_historic", array("request_id"=>$request_id, "date"=> current_time('mysql', 1), "historic_type"=>$historic_type, "user_id"=>$user_id));
    return $wpdb->insert_id;
}

function lab_request_hisoric_load($request_id) {
    global $wpdb;
    return $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."lab_request_historic WHERE request_id=".$request_id);

}

function lab_request_send_request_to_manager($request_id) {

}
?>