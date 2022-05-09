<?php
    !defined('LAB_REQUEST_HISTORIC_CANCEL') && define('LAB_REQUEST_HISTORIC_CANCEL', -1);
    !defined('LAB_REQUEST_HISTORIC_NEW') && define('LAB_REQUEST_HISTORIC_NEW', 1);
    !defined('LAB_REQUEST_HISTORIC_UPDATE') && define('LAB_REQUEST_HISTORIC_UPDATE', 2);
    !defined('LAB_REQUEST_HISTORIC_TAKE_IN_CHARGE') && define('LAB_REQUEST_HISTORIC_TAKE_IN_CHARGE', 10);
    !defined('LAB_REQUEST_HISTORIC_TAKE_IN_CHARGE') && define('LAB_REQUEST_HISTORIC_VALIDATE', 20);
    !defined('LAB_REQUEST_HISTORIC_TAKE_IN_CHARGE') && define('LAB_REQUEST_HISTORIC_CLOSE', 100);

function lab_request_get_own_requests() {
    global $wpdb;
    $sql = "SELECT lr.*, lrh.date FROM ".$wpdb->prefix."lab_request AS lr LEFT JOIN ".$wpdb->prefix."lab_request_historic AS lrh On lrh.request_id = lr.id WHERE lr.request_user_id = ".get_current_user_id()." AND lrh.historic_type=1 ";
    $results = $wpdb->get_results($sql);
    return $results;
}

function lab_request_list_requests($filters) {
    global $wpdb;
    $noFilter = FALSE;
    //return $filters;
    if ($filters == null) {
        $noFilter = TRUE;
        $filters = array();
    }
    $sql = "SELECT lr.*, lrh.date, um1.meta_value as last_name, um2.meta_value as first_name 
              FROM ".$wpdb->prefix."lab_request AS lr ";
    $join = " LEFT JOIN ".$wpdb->prefix."lab_request_historic AS lrh On lrh.request_id = lr.id LEFT JOIN ".$wpdb->prefix."usermeta AS um1 On um1.user_id=lr.request_user_id LEFT JOIN ".$wpdb->prefix."usermeta AS um2 On um2.user_id=lr.request_user_id ";
    $where = " WHERE lrh.historic_type=1 AND um1.meta_key='last_name' AND um2.meta_key='first_name' ";
    $isAdmin = lab_is_admin();
    $isGroupLeader = lab_is_group_leader();
    if (isset($filters["group"]) && $filters["group"] == "0") {
        unset($filters["group"]);
    }
    if (isset($filters["status"]) && $filters["status"] == "") {
        unset($filters["status"]);
    }
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
            if ($key == "status") {
                if (empty($where)) {
                    $where .= " WHERE ";
                }
                else {
                    $where .= " AND ";
                }
                $where .= " lr.request_state = ".$value;
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

function lab_request_expenses_load_by_request_all_infos($request_id) {
    global $wpdb;
    $results = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."lab_request_expenses WHERE request_id=".$request_id);
    foreach($results as $result) {
        switch ($result->type) {
            case 1:
                $result->type_string = "team";
                $result->support_name = lab_admin_get_group($result->object_id)->acronym;
                break;
            case 2:
                $result->type_string = "contract";
                $result->support_name = lab_admin_contract_get($result->object_id)->label;
                break;
            
            default:
                $result->type_string = "exterior";
                break;
        }
        switch ($result->financial_support) {
            case -1:
                $result->financial_support_string = "";
                break;
            default:
                $result->financial_support_string = AdminParams::lab_admin_get_params_budgetFunds($result->financial_support);
                break;
        }
    }
    return $results;
}

function lab_request_expenses_load($id) {
    global $wpdb;
    return $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."lab_request_expenses WHERE id=".$id);
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

function lab_request_save_expenses($request_id, $expenses) {
    if ($expenses == null) {
        return;
    }
    global $wpdb;
    $size = count($expenses);
    for ($i = 0 ; $i < $size ; $i++) {
        $expenses[$i]["request_id"] = $request_id;
        if ($expenses[$i]["id"] == null) {
            $wpdb->insert($wpdb->prefix."lab_request_expenses", $expenses[$i]);
        }
        else {
            $expenseId = $expenses[$i]["id"];
            unset($expenses[$i]["id"]);
            $wpdb->update($wpdb->prefix."lab_request_expenses", $expenses[$i], array("id"=>$expenseId));
        }
    }
}

if ( ! function_exists('write_log')) {
    function write_log ( $log )  {
       if ( is_array( $log ) || is_object( $log ) ) {
          error_log( print_r( $log, true ) );
       } else {
          error_log( $log );
       }
    }
 }

function lab_request_send_email_to_manager($request_id, $message) {
    global $wpdb;
    $results = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."lab_request WHERE id=".$request_id);
    if (count($results) == 1) {
        $request = $results[0];
        $user_id = $request->request_user_id;
        //return $user_id;
        $managers = lab_admin_group_get_managers_for_user($user_id);
        $adminManagers = $managers[1];
        $isGroupLeader = $managers[2];
        $to = [];
        foreach($adminManagers as $manager) {
            $to[] = $manager->email;
        }
        $userNames = lab_admin_usermeta_names($user_id);
        $headers[] = 'From: Olivier CHABROL <olivier.chabrol@univ-amu.fr>';
        $headers[] = 'Cc: olivier.chabrol@univ-amu.fr';
        $subject   = "[Demandes] ne pas tenir compte de ce message c'est un test";
        $message   = "Nouvelle demande à traiter de ".$userNames->first_name." ".$userNames->last_name;
        $message  .= '<br> <a href="https://www.i2m.univ-amu.fr/wp-admin/admin.php?page=lab_request_view&tab=entry&id='.$request_id.'&view=1">https://www.i2m.univ-amu.fr/wp-admin/admin.php?page=lab_request_view&tab=entry&id='.$request_id.'&view=1</a>';
        wp_mail($to,$subject,$message, $headers);
        /*
        write_log($to);
        write_log($headers);
        write_log($subject);
        write_log($message);
        //*/
    }
}

function lab_request_save($request_id, $request_user_id, $request_type, $request_title, $request_text, $previsional_date, $expenses = null) {
    global $wpdb;
    if (isset($request_id) && $request_id) {
        $wpdb->update($wpdb->prefix."lab_request", array("request_type" => $request_type, "request_title"=>$request_title, "request_text" => $request_text, "request_previsional_date"=>$previsional_date), array("id" => $request_id));
        lab_request_add_historic_update_request($request_id, get_current_user_id());
        lab_request_save_expenses($request_id, $expenses);
        return lab_request_move_file($request_id);
        return $request_id;
    }
    else {
        $wpdb->insert($wpdb->prefix."lab_request", array("request_user_id"=>$request_user_id, "request_type" => $request_type, "request_title"=>$request_title, "request_text" => $request_text, "request_previsional_date"=>$previsional_date));
        $request_id = $wpdb->insert_id;
        lab_request_add_historic_new_request($request_id, get_current_user_id());
        lab_request_send_request_to_manager($request_id);
        lab_request_save_expenses($request_id, $expenses);
        lab_request_move_file($request_id);
        lab_request_send_email_to_manager($request_id, "test");
        //return lab_request_send_email_to_manager($request_id, "test");
        
        return $request_id;
    }
}

function lab_request_move_file($request_id) {
    $request = lab_request_get_by_id($request_id);
    $path = generatePath($request);
    //return $path;
    $need_db_update = FALSE;
    if (!is_dir($path)) {
        if (!mkdir($path, 0777, true)) {

        }
        else {
            $need_db_update = TRUE;
        }
    }
    $str = array();
    foreach($request->files as $file) {
        $fileName = substr($file->url, strrpos($file->url,'/',-1) + 1);
        $str[] = $path.$fileName;
        if (!file_exists($path.$fileName)) {
            $str[] = "N'existe pas";
            if (!copy(__DIR__.'/../requests/'.$fileName, $path.$fileName)) {
                $str[] = "Copie ne marche pas";
            }
            else {
                $str[] = "Copie marche";
                $str[] = lab_request_update_file_path($file, $path);
            }
        }
        else {
            $str[] = "Existe";
            //$str[] = lab_request_update_file_path($file, $path.$fileName);
        }
    }
    return $str;
}

function lab_request_update_file_path($file, $path)
{
    $fileName = substr($file->url, strrpos($file->url,'/',-1) + 1);
    $pattern = "requests";
    $relative_path = substr($path, strpos($path,$pattern) + strlen($pattern) + 1);

    $new_url = "/wp-content/plugins/lab/requests/".$relative_path.$fileName;
    if(lab_request_update_file($file->id, array("url"=>$new_url))) {
        $tmp_file = __DIR__.'/../requests/'.$fileName;
        if (file_exists($tmp_file)) {
            unlink($tmp_file);
        }
    }

}

function generatePath($request) {
    $year = 0;
    $year = date("Y");
    $groupAcronym = "None";
    foreach ($request->historic as $h) {
        if ($h->historic_type == LAB_REQUEST_HISTORIC_NEW) {
            $date = $h->date;
            break;
        }
    }
    $date = new DateTime($date);
    $year = $date->format("Y");
    //var_dump($request->groups);
    //foreach($request->groups as $group) {
    //    $groupAcronym = $group->acronym;
    //}
    $groupAcronym = $request->groups->acronym;
    $firstName = preg_replace("/\s+/", "", (strtolower($request->first_name)));
    $lastName  = preg_replace("/\s+/", "", (strtolower($request->last_name)));
    $request_type = preg_replace("/\s+/", "", ucwords(AdminParams::get_paramWithColor($request->request_type)->value));
    return __DIR__.'/../requests/'.$year."/".$groupAcronym."/".$firstName.".".$lastName."/".$request_type."/".$request->id."/";
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

function lab_request_update_file($file_id, $values) {
    global $wpdb;
    return $wpdb->update($wpdb->prefix."lab_request_files", $values, array("id"=> $file_id));
}

function lab_request_load_files($request_id) {
    return lab_request_get_associated_files($request_id);
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

function lab_request_delete_historic_by_id($id) {
    global $wpdb;
    return $wpdb->delete($wpdb->prefix."lab_request_historic", array("id"=>$id));
}

/**
 * Undocumented function
 *
 * @param [bigint] $id historic ID
 * @return lab_request_historic line, null otherwise
 */
function lab_request_get_historic($id) {
    global $wpdb;
    $results = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."lab_request_historic WHERE id=".$id);
    if (count($results) == 1) {
        return $results[0];
    }
    return null;
}

function lab_request_generate_financial_support_map() {
    $a = array();
    $supports = get_financial_support();
    foreach($supports as $fs) {
        $a[$fs->id] = $fs->value;
    }

    return $a;
}


function lab_request_get_by_id($request_id) {
    global $wpdb;
    $results = $wpdb->get_results("SELECT lr.*, um1.meta_value as last_name, um2.meta_value as first_name FROM ".$wpdb->prefix."lab_request as lr LEFT JOIN ".$wpdb->prefix."usermeta AS um1 On um1.user_id=lr.request_user_id LEFT JOIN ".$wpdb->prefix."usermeta AS um2 On um2.user_id=lr.request_user_id WHERE id=".$request_id." AND um1.meta_key='last_name' AND um2.meta_key='first_name'");
    if(isset($results) && count($results) == 1) {
        $results[0]->files    = lab_request_get_associated_files($request_id);
        $results[0]->groups   = lab_group_get_user_group_information($results[0]->request_user_id);
        $results[0]->historic = lab_request_historic_load($request_id);
        $results[0]->expenses = lab_request_expenses_load_by_request_all_infos($request_id);
        $results[0]->path     = generatePath($results[0]);
        $results[0]->users    = lab_request_generateUsers($results[0]);
        $results[0]->financial_support = lab_request_generate_financial_support_map();
        $results[0]->admin = lab_is_admin();
        return $results[0];
    }
    else{
        return null;
    }
}

function lab_request_generateUsers($request) {
    //return "ok";
    $users = array();//"test"=>"olivier");
    //$users[0] = $request->request_user_id;
    $users[$request->request_user_id] = lab_admin_usermeta_names($request->request_user_id);
    
    foreach($request->historic as $historic) {
        if (!isset($users[$historic->user_id])) {
            $users[$historic->user_id] = lab_admin_usermeta_names($historic->user_id);
        }
    }
    
    return $users;
}

function lab_request_change_state($request_id, $user_id, $state) {
    global $wpdb;
    lab_request_update_state($request_id, $state);
    lab_request_add_historic($request_id, $state, $user_id);
    return lab_request_get_by_id($request_id);
}

function lab_request_take_in_charge($request_id, $user_id) {
    return lab_request_change_state($request_id, $user_id, LAB_REQUEST_HISTORIC_TAKE_IN_CHARGE);
}

function lab_request_cancel($request_id) {
    return lab_request_change_state($request_id, $user_id, LAB_REQUEST_HISTORIC_CANCEL);
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

function lab_request_historic_load($request_id) {
    global $wpdb;
    return $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."lab_request_historic WHERE request_id=".$request_id);

}

function lab_request_send_request_to_manager($request_id) {

}
?>