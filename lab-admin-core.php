<?php
require_once("core/lab-admin-core-mission.php");

function lab_admin_user_info($userId, $fields) {
    global $wpdb;
    $results = $wpdb->get_results( "SELECT um.*, u.user_email, u.user_url, g.group_name, g.id AS group_id FROM `".$wpdb->prefix."usermeta` AS um JOIN `".$wpdb->prefix."users` AS u ON u.id=um.user_id JOIN `".$wpdb->prefix."lab_users_groups` AS ug ON u.id=ug.user_id JOIN `".$wpdb->prefix."lab_groups` AS g ON g.id=ug.group_id WHERE (um.meta_key = 'first_name' or um.meta_key='last_name' or um.meta_key LIKE 'lab_%') and um.user_id=".$userId  );
    $items = array();
    $items["id"] = $userId;

    foreach ( $results as $r )
    {
        $items['user_email'] = $r->user_email;
        $items['user_url']   = $r->user_url;
        $items['group_name'] = $r->group_name;
        $items['group_id'] = $r->group_id;
        if ($r->meta_key == 'first_name')
            $items['first_name'] = stripslashes($r->meta_value);
        if ($r->meta_key == 'last_name')
            $items['last_name'] = stripslashes($r->meta_value);
        if (beginsWith($r->meta_key, "lab_"))
        {
            if ($r->meta_key == 'lab_user_left') {
                $items['lab_user_left'] = array();
                $items['lab_user_left']['id'] = $r->umeta_id;
                $items['lab_user_left']['value'] = $r->meta_value;
            }
            else
            {
                $items[substr($r->meta_key, 4)] = stripslashes($r->meta_value);
            }
        }
    }
    
    return $items;
}

function lab_admin_userMetaDatas_get($userId) {
    global $wpdb;
    $results = $wpdb->get_results( "SELECT um.*, u.user_email, u.user_url FROM `".$wpdb->prefix."usermeta` AS um JOIN `".$wpdb->prefix."users` AS u ON u.id=um.user_id WHERE (um.meta_key = 'first_name' or um.meta_key='last_name' or um.meta_key LIKE 'lab_%') and um.user_id=".$userId  );
    $items = array();
    $items["id"] = $userId;

    foreach ( $results as $r )
    {
        $items['user_email'] = $r->user_email;
        $items['user_url']   = $r->user_url;
        if ($r->meta_key == 'first_name')
            $items['first_name'] = stripslashes($r->meta_value);
        if ($r->meta_key == 'last_name')
            $items['last_name'] = stripslashes($r->meta_value);
        if (beginsWith($r->meta_key, "lab_"))
        {
            if ($r->meta_key == 'lab_user_left') {
                $items['lab_user_left'] = array();
                $items['lab_user_left']['id'] = $r->umeta_id;
                $items['lab_user_left']['value'] = $r->meta_value;
            }
            else
            {
                $items[substr($r->meta_key, 4)] = stripslashes($r->meta_value);
            }
        }
    }
    
    return $items;
}
function lab_admin_get_userLogin($user_id) {
    global $wpdb;
    $sql = "SELECT `user_login` FROM `".$wpdb->prefix."users` WHERE ID=$user_id";
    return $wpdb->get_var($sql);
}

function lab_admin_loadUserHistory($user_id) {
    global $wpdb;
    $sql = "SELECT * from `".$wpdb->prefix."lab_users_historic` WHERE `user_id`=$user_id ORDER BY `begin` ASC";
    $res = $wpdb->get_results($sql);
    if ($res==null) {
        return "<li>No history</li>";
    } else {
        return lab_admin_history($res);
    }
}
function lab_admin_load_lastUserHistory($user_id) {
    global $wpdb;
    $sql = "SELECT * from `".$wpdb->prefix."lab_users_historic` WHERE `user_id`=$user_id ORDER BY `begin` DESC LIMIT 1";
    $res = $wpdb->get_results($sql);
    if (count($res) > 0) {
        return lab_admin_history_fields($res[0]);
    }
    return null;
}
/**
 * @param array $fields ('user_id'=>$user_id,
 *                        'ext'=>$end,
 *                        'begin'=>$begin,
 *                        'end'=>$end,
 *                        'mobility'=>$mobility,
 *                        'mobility_status'=>$mobility_status,
 *                        'host_id'=>$host_id,
 *                        'function'=>$function)
 */
function lab_admin_add_historic($fields) {
    global $wpdb;
    $wpdb->insert($wpdb->prefix.'lab_users_historic',$fields);
    $insertId = $wpdb->insert_id;
    $histos = lab_admin_historic_get_historics_ordered_by_date($fields["user_id"]);
    $hSize = count($histos);
    // set user left to the last historic end date defined
    if ($hSize > 0 )
    {
        lab_userMetaData_save_key($fields["user_id"], "user_left", $histos[$hSize - 1]->end);
        lab_userMetaData_save_key($fields["user_id"], "user_function", $histos[$hSize - 1]->function);
    }
    return true;
}
function lab_admin_historic_delete($entry_id) {
    global $wpdb;
    $histo = lab_admin_historic_get($entry_id);
    $date_user_left = is_user_left($histo->user_id);
    //return $date_user_left;
    if ($date_user_left != null && $histo->end == $date_user_left)
    {
        lab_userMetaData_save_key($histo->user_id, "user_left", null);
        lab_userMetaData_save_key($histo->user_id, "user_function", $histos[$hSize - 1]->function);
    }
    return $wpdb->delete($wpdb->prefix."lab_users_historic",array('id'=>$entry_id));
}
function lab_admin_historic_get($entry_id) {
    global $wpdb;
    return $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."lab_users_historic` WHERE `id`=".$entry_id.";")[0];
}
function lab_admin_historic_update($entry_id,$fields) {
    global $wpdb;
    $wpdb->update($wpdb->prefix."lab_users_historic",$fields,array('id'=>$entry_id));
    $histos = lab_admin_historic_get_historics_ordered_by_date($fields["user_id"]);
    $hSize = count($histos);
    
    // set user left to the last historic end date defined
    if ($hSize > 0 )
    {
        lab_userMetaData_save_key($fields["user_id"], "user_left", $histos[$hSize - 1]->end);
        lab_userMetaData_save_key($fields["user_id"], "user_function", $histos[$hSize - 1]->function);
    }
    return true;
}

/**
 * Return all historics of the user orderby date end
 *
 * @param [type] $userId
 * @return void
 */
function lab_admin_historic_get_historics_ordered_by_date($userId)
{
    global $wpdb;
    $sql = "SELECT * FROM ".$wpdb->prefix."lab_users_historic WHERE user_id=".$userId."  ORDER BY `end` DESC ";
    return $wpdb->get_results($sql);
}

function lab_get_all_roles() {
    $roles = new WP_Roles();
    $names = $roles->get_names();
    $result = array();
    foreach ($names as $key => $value) {
        array_push($result,(object)array('id'=>$key,'value'=>$value));
    }
    return $result;
}
/*******************************************************************************************************
 * PARAM
 *******************************************************************************************************/

 /**
  * Return the last id of the system param (where type = 1)
  *
  * @return int max id, 0 otherwise
  */
function lab_admin_param_last_param_system_id()
{
    global $wpdb;
    $sql = "SELECT MAX(id) AS max FROM `".$wpdb->prefix."lab_params` WHERE `type_param`=1";
    $results = $wpdb->get_results($sql);
    if (count($results) > 0)
    {
        return intval($results[0]->max);
    }
    return 0;
}

function lab_admin_param_get_by_id($paramId)
{
    global $wpdb;
    $sql = "SELECT * FROM `".$wpdb->prefix."lab_params` WHERE `id`=$paramId";
    $results = $wpdb->get_results($sql);
    if (count($results) == 1)
    {
        return $results[0];
    }
    else{
        return null;
    }
}

function lab_admin_param_clone($param)
{
    global $wpdb;
    if ($wpdb->insert($wpdb->prefix.'lab_params', array("type_param"=>$param->type_param, "value"=>$param->value, "color"=>$param->color)))
    {
        return $wpdb->insert_id;
    }
    else
    {
        return false;
    }
}

/***********************************************************************************************************
 * BUDGET
 ***********************************************************************************************************/
function lab_budget_info_save_order($params) {
    global $wpdb;
    if (isset($params["id"]) && $params["id"] != "") {
        $id = $params["id"];
        unset($params["id"]);
        $wpdb->update($wpdb->prefix."lab_budget_info", $params, array('id'=>$id));
    }
    else {
        $params["info_manager_id"] = get_current_user_id();
        $wpdb->insert($wpdb->prefix."lab_budget_info", $params);
    }
}

function lab_budget_info_delete($budgetId) {
    global $wpdb;
    $wpdb->delete($wpdb->prefix."lab_budget_info", array("id"=>$budgetId));
    return true;
}

function budget_info_ajax_date($budgetId, $datefield) {
    global $wpdb;
    $r = $wpdb->update($wpdb->prefix."lab_budget_info", array($datefield=>date('Y-m-d')), array('id'=>$budgetId)); 
    return $r;
}

function lab_budget_info_load($budgetId, $filters = null) {
    global $wpdb;
    $data = array();
    $data["filters"] = array();
    $sql = "SELECT bi.* from ".$wpdb->prefix."lab_budget_info AS bi";
    if ($budgetId != null OR $filters != null) {
        $sql .= " WHERE ";    
        if ($budgetId != null) {
            $sql .= "id=".$budgetId;
        }
        else {
            $nbFilter = 0;
            foreach($filters as $key=>$value) {
                if ($key == "year") {
                    $sql .= "(bi.`order_date` != '0000-00-00' AND YEAR(bi.`order_date`)=".$value.")";
                    $data["filters"]["year"] = $value;
                }
                else if ($key == "state") {
                    if ($nbFilter > 0) {
                        $sql .= " AND ";
                    }
                    if ($value == "c") {
                        $sql .= "bi.`payment_date` != '0000-00-00'";
                    }
                    else if ($value == "wo") {
                        $sql .= "bi.`order_date` = '0000-00-00'";
                    }
                    else if ($value == "wd") {
                        $sql .= "bi.`delivery_date` = '0000-00-00'";
                    }
                    else if ($value == "wp") {
                        $sql .= "bi.`payment_date` = '0000-00-00'";
                    }
                    else if ($value == "wf") {
                        $sql .= "bi.`fund_origin` = 0";
                    }
                    $data["filters"]["state"] = $value;

                }
                else if ($key == "fund_origin") {
                    if ($nbFilter > 0) {
                        $sql .= " AND ";
                    }
                    $sql .= "bi.`fund_origin` = ".$value;

                    $data["filters"]["fund_origin"] = $value;
                }
                else if ($key == "order_number") {
                    if ($nbFilter > 0) {
                        $sql .= " AND ";
                    }
                    $sql .= "(bi.`order_number` LIKE '%".$value."%' OR bi.`order_reference` LIKE '%".$value."%')";

                    $data["filters"]["command_number"] = $value;
                }
                else if ($key == "site") {
                    if ($nbFilter > 0) {
                        $sql .= " AND ";
                    }
                    $sql .= "bi.`site_id` LIKE '%".$value."%'";

                    $data["filters"]["site"] = $value;
                }
                else if ($key == "budget_manager") {
                    if ($nbFilter > 0) {
                        $sql .= " AND ";
                    }
                    $sql .= "bi.`budget_manager_id` LIKE '%".$value."%'";

                    $data["filters"]["budget_manager"] = $value;
                }
                $nbFilter += 1;
            }
        }
    }
    $sql .= " ORDER BY `request_date` DESC";
    $results = $wpdb->get_results($sql);
    $userIds = array();
    foreach($results as $r) {
        if(!isset($userIds[$r->user_id]) && $r->user_id != 0) {
            $userIds[$r->user_id] = lab_admin_usermeta_names($r->user_id);
            $groups = $wpdb->get_results("SELECT ug.user_id, g.acronym FROM `".$wpdb->prefix."lab_users_groups` AS ug JOIN ".$wpdb->prefix."lab_groups AS g ON g.id=ug.group_id WHERE ug.user_id=".$r->user_id);
            if (count($groups)> 0) {
                $userIds[$r->user_id]->group = $groups[0]->acronym;
            }
        }
        if(!isset($userIds[$r->info_manager_id]) && $r->info_manager_id != 0) {
            $userIds[$r->info_manager_id] = lab_admin_usermeta_names($r->info_manager_id);
        }
        if(!isset($userIds[$r->budget_manager_id]) && $r->budget_manager_id != 0) {
            $userIds[$r->budget_manager_id] = lab_admin_usermeta_names($r->budget_manager_id);
        }

    }
    if ($budgetId== null) {
        $data["results"] = $results;
    }
    else {
        $data["results"] = $results[0];
    }
    $data["users"] = $userIds;
    $params = array();
    $ps = array_merge(AdminParams::lab_admin_get_params_budgetInfoType(), AdminParams::lab_admin_get_params_budgetFunds());
    $ps = array_merge($ps, AdminParams::lab_admin_get_params_userLocation());
    foreach($ps as $p) {
        $params[$p->id] = $p;
    }
    $data["params"] = $params;

    $sql = "SELECT DISTINCT YEAR(`order_date`) AS year FROM `".$wpdb->prefix."lab_budget_info` ORDER BY `year` DESC";
    $results = $wpdb->get_results($sql);

    $years   = array();
    $currentYear = date("Y");
    //$years[] = "";
    //$years[] = esc_html("Year", "lab");
    $years[] = $currentYear;
    foreach ($results as $r) {
        if ($r->year != null && $r->year != $currentYear && $r->year != 0) {
            $years[] = $r->year;
        }
    }
    $data["years"] = $years;


    $contracts   = array();
    $results = $wpdb->get_results("SELECT id,name as 'value' FROM ".$wpdb->prefix."lab_contract ORDER BY 'value'");
    foreach($results as $r) {
        $contracts[$r->id] = $r;
    }

    $data["contracts"] = $contracts;
    //$data["ps"] = $ps;
    //return $results;
    return $data;
}

function lab_admin_budget_managers_list() {
    global $wpdb;
    $resultContract = $wpdb->get_results("SELECT DISTINCT cu.user_id,um1.meta_value AS first_name,um2.meta_value AS last_name FROM ".$wpdb->prefix."lab_contract_user AS cu JOIN `".$wpdb->prefix."usermeta` AS um1 ON um1.user_id=cu.user_id JOIN `".$wpdb->prefix."usermeta` AS um2 ON um2.user_id=cu.user_id WHERE cu.`user_type`=1 AND um1.meta_key='first_name' AND um2.meta_key='last_name'");
    $resultGroup    = $wpdb->get_results("SELECT DISTINCT cu.user_id,um1.meta_value AS first_name,um2.meta_value AS last_name FROM ".$wpdb->prefix."lab_group_manager AS cu JOIN `".$wpdb->prefix."usermeta` AS um1 ON um1.user_id=cu.user_id JOIN `".$wpdb->prefix."usermeta` AS um2 ON um2.user_id=cu.user_id WHERE cu.`manager_type`=1 AND um1.meta_key='first_name' AND um2.meta_key='last_name'");

    $managers = array();
    foreach($resultContract as $contractManager) {
        $c = new stdClass;
        $c->id = $contractManager->user_id;
        $c->value = $contractManager->first_name." ".$contractManager->last_name;
        $managers[$c->id] = $c;
    }
    foreach($resultGroup as $groupManager) {
        if (!isset($managers[$groupManager->user_id])) {
            $c = new stdClass;
            $c->id = $groupManager->user_id;
            $c->value = $groupManager->first_name." ".$groupManager->last_name;
            $managers[$c->id] = $c;
        }
    }
    return $managers;
}

function lab_admin_budget_funds() {
    global $wpdb;
    $budgetFunds = AdminParams::lab_admin_get_params_budgetFunds();
    $contracts   = $wpdb->get_results("SELECT c.id,c.name as 'value',p.value AS contract_type  FROM ".$wpdb->prefix."lab_contract AS c JOIN ".$wpdb->prefix."lab_params AS p ON p.id=c.contract_type ORDER BY 'value'");
    $funds = array();
    foreach($budgetFunds as $f) {
        $c = new stdClass;
        $c->id=$f->id;
        $c->value=$f->value;
        $funds[]=$c;
    }
    foreach($contracts as $f) {
        $c = new stdClass;
        $c->id=$f->id;
        $c->value="CONTRAT ".$f->contract_type." ".$f->value;
        $funds[]=$c;
    }
    return $funds;
}

/***********************************************************************************************************
 * CONTRACT
 ***********************************************************************************************************/
function lab_admin_contract_save($id, $name, $start, $end, $holders, $managers) {
    global $wpdb;
    // new contract
    if (!isset($id) || empty($id)) {
        if ($wpdb->insert($wpdb->prefix.'lab_contract', array("name"=>$name,"start"=>$start,"end"=>$end))) {
            $contractId = $wpdb->insert_id;
            foreach ($holders as $userId) {
                $wpdb->insert($wpdb->prefix.'lab_contract_user', array("contract_id"=>$contractId, "user_id"=>$userId,"user_type"=> 2));
            }
            foreach ($managers as $userId) {
                $wpdb->insert($wpdb->prefix.'lab_contract_user', array("contract_id"=>$contractId, "user_id"=>$userId,"user_type"=> 1));
            }
        }
    }
    else
    {
        $wpdb->update($wpdb->prefix.'lab_contract', array("name"=>$name,"start"=>$start,"end"=>$end), array("id"=>$id));
    }
}

/**
 * Get all the contracts for a specific user
 *
 * @param [int] $userId
 * @return void
 */
function lab_admin_contract_get_contracts_by_user($userId) {
    global $wpdb;
    $sql = "SELECT cu.*, c.contract_type as contract_type_id, p.value as contract_type, c.name, c.start, c.end  
              FROM `".$wpdb->prefix."lab_contract_user` AS cu 
              JOIN ".$wpdb->prefix."lab_contract AS c ON c.id = cu.contract_id 
              JOIN ".$wpdb->prefix."lab_params as p ON p.id = c.contract_type WHERE `user_id` = ".$userId;
    return $wpdb->get_results($sql);
}

function lab_admin_contract_get_managers($contractId) {
    global $wpdb;
    return $wpdb->get_results("SELECT user_id FROM ".$wpdb->prefix."lab_contract_user WHERE `contract_id` = ".$contractId." AND user_type=".AdminParams::CONTRACT_USER_TYPE_MANAGER);
}

function lab_admin_contract_search($contractName) {
    global $wpdb;
    return $wpdb->get_results("SELECT id, name as label, start, end FROM ".$wpdb->prefix."lab_contract WHERE `name`LIKE '%".$contractName."%'");
}

function lab_admin_contract_users_load($contractId) {
    global $wpdb;
    return $wpdb->get_results("SELECT cu.*,um1.meta_value AS first_name,um2.meta_value AS last_name FROM ".$wpdb->prefix."lab_contract_user AS cu JOIN `".$wpdb->prefix."usermeta` AS um1 ON um1.user_id=cu.user_id JOIN `".$wpdb->prefix."usermeta` AS um2 ON um2.user_id=cu.user_id WHERE `contract_id`=".$contractId." AND um1.meta_key='first_name' AND um2.meta_key='last_name'");
}

function lab_admin_contract_delete($contractId) {
    global $wpdb;
    $wpdb->delete($wpdb->prefix.'lab_contract_user', array("contract_id"=>$contractId));
    $wpdb->delete($wpdb->prefix.'lab_contract', array("id"=>$contractId));
    return true;
}


function lab_admin_contract_load() {
    global $wpdb;
    $results = $wpdb->get_results("SELECT c.*,p.value AS contract_type_label, cu.user_id, cu.user_type,um1.meta_value AS first_name, um2.meta_value AS last_name FROM `".$wpdb->prefix."lab_contract` AS c JOIN ".$wpdb->prefix."lab_contract_user AS cu ON cu.contract_id=c.id JOIN ".$wpdb->prefix."usermeta AS um1 ON um1.user_id=cu.user_id JOIN ".$wpdb->prefix."usermeta AS um2 ON um2.user_id=cu.user_id JOIN ".$wpdb->prefix."lab_params AS p ON p.id=c.contract_type WHERE um1.meta_key='first_name' AND um2.meta_key='last_name' ");
    $contracts = array();
    $lastId = -1;
    $nbLine = 0;
    $str = "";
    foreach ($results as $line) {
        $nbLine++;
        $str .= $nbLine." ".$line->name;
        if ($lastId == -1) {
            $str .= ' LA 1';
            $lastId = $line->id;
            $contracts[$lastId] = lab_admin_contract_inner_new_stdClass_contract($line);
        } else if ($lastId != $line->id) {
            $str .= ' LA 2';
            $lastId = $line->id;
            $contract = $contracts[$lastId];
            if (!isset($contract))
            {
                $contracts[$lastId] = lab_admin_contract_inner_new_stdClass_contract($line);
            }
            else {
                lab_admin_contract_inner_new_stdClass_add_user($line, $contracts[$lastId]);
            }
        } else {
            $str .= ' LA 3';
            $str .= 'user_type : '.$line->user_type.' last_name : '.$line->last_name;
            lab_admin_contract_inner_new_stdClass_add_user($line, $contracts[$line->id]);
            $str .= ' nb user manager : '.count($contracts[$line->id]->managers);
            $str .= ' nb user holders : '.count($contracts[$line->id]->holders);
        }
        $str .= '<br>';
        
    }

    return $contracts;
    //return $str;
}

function lab_admin_contract_inner_new_stdClass_contract($line) 
{
    $contract = new stdClass;
    $contract->id    = $line->id;
    $contract->name  = $line->name;
    $contract->start = $line->start;
    $contract->end   = $line->end;
    $contract->type   = $line->contract_type_label;
    $contract->holders  = array();
    $contract->managers = array();
    lab_admin_contract_inner_new_stdClass_add_user($line, $contract);

    return $contract;
}

function lab_admin_contract_inner_new_stdClass_add_user($line, $contract) {
    $user = new stdClass;
    $user->first_name = $line->first_name;
    $user->last_name  = $line->last_name;
    $user->id  = $line->user_id;

    if ($line->user_type == 2) {
        $contract->holders[] = $user;
    }

    if ($line->user_type == 1) { 
        $contract->managers[] = $user;
    }
}

function lab_admin_contract_create_table() {
    global $wpdb;
    $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."lab_contract` (
        `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
        `contract_type` bigint UNSIGNED NOT NULL,
        `name` varchar(500) NOT NULL,
        `start` date NOT NULL,
        `end` date NOT NULL,
        PRIMARY KEY (`id`)
      ) ENGINE=InnoDB";
    $wpdb->get_results($sql);
    $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."lab_contract_user` (
        `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
        `contract_id` bigint NOT NULL,
        `user_id` bigint NOT NULL,
        `user_type` bigint NOT NULL,
        PRIMARY KEY (`id`)
      ) ENGINE=InnoDB";
    return $wpdb->get_results($sql);
}

/***********************************************************************************************************
 * MISSION
 ***********************************************************************************************************/


function lab_mission_save($missionId, $travels) {
    $t = null;
    foreach ($travels as $travel) {
        $travel['missionId'] = $missionId;
        $t = lab_mission_save_travel($travel);
    }
    return $t;
}

function lab_mission_remap_fields($fields) {
    $a = array();
    $a["mission_id"] = $fields["missionId"];
    $a["country_from"] = $fields["countryFrom"];
    $a["travel_from"] = $fields["cityFrom"];
    $a["country_to"] = $fields["countryTo"];
    $a["travel_to"] = $fields["cityTo"];
    $a["travel_date"] = $fields["dateGoTo"]." ".$fields["timeGoTo"].":00";
    $a["means_of_locomotion"] = $fields["mean"];
    $a["estimated_cost"] = $fields["cost"];
    $a["round_trip"] = $fields["rt"]; //($fields["rt"]=="false"?0:1);
    $a["reference"] = $fields["ref"];
    $a["nb_person"] = $fields["nb_person"];
    $a["carbon_footprint"] = $fields["carbon_footprint"];
    $a["loyalty_card_number"] = $fileds["loyalty_card_number"];
    $a["loyalty_card_date"] = $fields["loyalty_card_expiry_date"];
    if($a["round_trip"] == 0) {
        $a["travel_datereturn"] = NULL;
    }
    else {
        $a["travel_datereturn"] = $fields["dateReturn"]." ".$fields["timeReturn"].":00";
    }
    return $a;
}

function lab_mission_save_travel($travel, $remap=True) {
    global $wpdb;
    $a = null;
    if ($remap) { 
        $a = lab_mission_remap_fields($travel);
    }
    else {
        $a = $travel;
    }
    
    $wpdb->insert($wpdb->prefix.'lab_mission_route', $a);
    return $wpdb->insert_id;
}

function lab_mission_load_travels($missionId) {
    global $wpdb;
    $results =$wpdb->get_results("SELECT * FROM `".$wpdb->prefix."lab_mission_route` WHERE mission_id=".$missionId);

    return $results;
}

function lab_mission_delete_travel($id, $missionId) {
    global $wpdb;
    $userId = get_current_user_id();
    $userMeta = lab_admin_usermeta_names($userId);
    $wpdb->delete($wpdb->prefix.'lab_mission_route', array('id' => $id));
    lab_invitations_addComment(array(
        'content' => "¤Trajet supprimé par " . $userMeta->first_name . " " . $userMeta->last_name,
        'timestamp'=> date("Y-m-d H:i:s",strtotime("+1 hour")),
        'author_id' => 0,
        'author_type' => 0,
        'invite_id' => $missionId
    ));
}

function lab_mission_update_travel($travelId, $travelFields){
    global $wpdb;
    $userId = get_current_user_id();
    $userMeta = lab_admin_usermeta_names($userId);
    $msg = "";
    $return = "";
    if (isset($travelId) && !empty($travelId)) {
        $wpdb->update($wpdb->prefix.'lab_mission_route', $travelFields, array('id' => $travelId));
        $msg = '¤Trajet modifié';
        $return = $travelId;
    }
    else {
        $msg = '¤Trajet ajouté';
        $return = lab_mission_save_travel($travelFields, False);
    }
    lab_invitations_addComment(array(
        'content' => $msg." par " . $userMeta->first_name . " " . $userMeta->last_name,
        'timestamp'=> date("Y-m-d H:i:s",strtotime("+1 hour")),
        'author_id' => 0,
        'author_type' => 0,
        'invite_id' => $travelFields["mission_id"]
    ));
    return $return;
}

function lab_admin_mission_create_table() {
    global $wpdb;
    $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."lab_mission_route` (
        `id` bigint NOT NULL AUTO_INCREMENT,
        `mission_id` bigint NOT NULL,
        `country_from` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
        `travel_from` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
        `country_to` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
        `travel_to` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
        `travel_date` datetime NOT NULL,
        `means_of_locomotion` bigint NOT NULL,
        `round_trip` BOOLEAN NOT NULL,
        `nb_person` int NOT NULL,
        `carbon_footprint` int NOT NULL,
        `loyalty_card_number` int NOT NULL,
        `loyalty_card_expiry_date` date NOT NULL,
        `travel_datereturn` datetime DEFAULT NULL,
        `estimated_cost` float NOT NULL,
        `real_cost float` NOT NULL,
        `reference varchar(255)` COLLATE utf8_bin NOT NULL,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB";
    return $wpdb->get_results($sql);
}

/***********************************************************************************************************
 * NOTIFICATION
 ***********************************************************************************************************/

function lab_admin_notification_create_table() {
    global $wpdb;
    $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."lab_notifs` (
        `id` bigint NOT NULL AUTO_INCREMENT,
        `user_id` bigint NOT NULL,
        `invite_id` bigint NOT NULL,
        `comment_id` bigint NOT NULL,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB";
    return $wpdb->get_results($sql);
}

/***********************************************************************************************************
 * USERMETA
 ***********************************************************************************************************/
/**
 * Return first_name, last_name, id of an user
 *
 * @param [type] $userId
 * @return void
 */
function lab_admin_usermeta_names($userId) {
    global $wpdb;
    $results =$wpdb->get_results("SELECT um1.user_id AS id,um1.meta_value AS first_name, um2.meta_value AS last_name FROM ".$wpdb->prefix."usermeta AS um1 JOIN ".$wpdb->prefix."usermeta AS um2 ON um1.user_id=um2.user_id WHERE um1.meta_key='first_name' AND um2.meta_key='last_name' AND um1.user_id=".$userId);
    if (count($results) == 1)
        return $results[0];
    else
        return null;
}

/***********************************************************************************************************
 * GROUP
 ***********************************************************************************************************/

function lab_admin_group_get_groups_of_manager($managerId) {
    global $wpdb;
    $results =$wpdb->get_results("SELECT group_id FROM `".$wpdb->prefix."lab_group_manager` WHERE `user_id`=".$managerId." AND `manager_type`=1");
    $groupIds = [];
    foreach ($results as $r) {
        $groupIds[] = $r->group_id;
    }
    return $groupIds;
}
function lab_admin_group_get_groups_of_leader($leaderId) {
    global $wpdb;
    $results =$wpdb->get_results("SELECT group_id FROM `".$wpdb->prefix."lab_group_manager` WHERE `user_id`=".$leaderId." AND manager_type=2");
    $groupIds = [];
    foreach ($results as $r) {
        $groupIds[] = $r->group_id;
    }
    return $groupIds;
}

function lab_admin_group_add_manager($groupId, $userId, $userRole) {
    global $wpdb;
    if ($wpdb->insert($wpdb->prefix.'lab_group_manager', array("group_id"=>$groupId, "user_id"=>$userId, "manager_type"=>$userRole))) {
        return $wpdb->insert_id;
    }
    else
    {
        return false;
    }
}

/**
 * Return groups where current user is the budget manager
 *
 * @param [int] $userId, if not provided, take get_current_user_id instead
 * @param [int] $managerType, if not provided, take budget manager : 1
 * @return array of group ids current user is the budget manager
 */
function lab_admin_group_is_manager($userId = null, $managerType = 1) {
    global $wpdb;
    if ($userId == null) {
        $userId = get_current_user_id();
    }
    $results = $wpdb->get_results("SELECT group_id FROM `".$wpdb->prefix."lab_group_manager` WHERE `user_id`=".$userId." AND manager_type=$managerType");
    $groupIds = [];
    foreach ($results as $r) {
        $groupIds[] = $r->group_id;
    }
    return $groupIds;
}

/**
 * Get the favorite group of a user, 
 *
 * @param bigint $userId
 * @return groupeId of the favorite user, the first one otherwise, -1 otherwise
 */
function lab_group_get_user_group($userId) {
    global $wpdb;
    $sql = "SELECT group_id, favorite FROM ".$wpdb->prefix."lab_users_groups WHERE user_id=".$userId;
    //return $sql;
    $results = $wpdb->get_results($sql);
    $groupId = -1;
    if (count($results) > 0) {        
        foreach ($results as $group) {
            if ($group->favorite == 1) {
                $groupId = $group->group_id;
            }
        }
        // if no favorite group defined, take the first one
        if ($groupId == -1) {
            $groupId = $results[0]->group_id;
        }
    }
    return $groupId;
}

function lab_group_delete_manager($id)
{
    global $wpdb;
    $wpdb->delete($wpdb->prefix.'lab_group_manager', array('id' => $id));
    return true;
}

function lab_admin_group_load_managers($groupId, $typeManager = null) {
    global $wpdb;
    if($typeManager == null) {
        return $wpdb->get_results("SELECT gm.id,gm.user_id,gm.manager_type,um1.meta_value AS first_name,um2.meta_value AS last_name FROM `".$wpdb->prefix."lab_group_manager` AS gm JOIN `".$wpdb->prefix."usermeta` AS um1 ON um1.user_id=gm.user_id JOIN `".$wpdb->prefix."usermeta` AS um2 ON um2.user_id=gm.user_id WHERE `group_id`=$groupId AND um1.meta_key='first_name' AND um2.meta_key='last_name'");
    } else {
        return $wpdb->get_results("SELECT gm.id,gm.user_id,gm.manager_type,um1.meta_value AS first_name,um2.meta_value AS last_name 
                                            FROM `".$wpdb->prefix."lab_group_manager` AS gm 
                                            JOIN `".$wpdb->prefix."usermeta` AS um1 ON um1.user_id=gm.user_id 
                                            JOIN `".$wpdb->prefix."usermeta` AS um2 ON um2.user_id=gm.user_id 
                                            WHERE `group_id`=$groupId 
                                                AND um1.meta_key='first_name' 
                                                AND um2.meta_key='last_name'
                                                AND gm.manager_type = ".$typeManager
                                        );
    }
}

/***********************************************************************************************************
 * PARAM
 ***********************************************************************************************************/

function lab_admin_param_change_id($oldId, $type, $newId)
{
    global $wpdb;
    if ($type == AdminParams::PARAMS_GROUPTYPE_ID)
    {
        $wpdb->update($wpdb->prefix.'lab_groups', array("group_type"=>$newId), array("group_type"=>$oldId));
    } 
    else if ($type == AdminParams::PARAMS_KEYTYPE_ID)
    {
        $wpdb->update($wpdb->prefix.'lab_keys', array("type"=>$newId), array("type"=>$oldId));
    } 
    else if ($type == AdminParams::PARAMS_SITE_ID)
    {
        $wpdb->update($wpdb->prefix.'usermeta', array("meta_value"=>$newId), array("meta_value"=>$oldId, "meta_key"=>"lab_user_location"));
        $wpdb->update($wpdb->prefix.'lab_keys', array("site"=>$newId), array("site"=>$oldId));
        $wpdb->update($wpdb->prefix.'lab_presence', array("site"=>$newId), array("site"=>$oldId));
    } 
    else if ($type == AdminParams::PARAMS_USER_FUNCTION_ID)
    {
        $wpdb->update($wpdb->prefix.'usermeta', array("meta_value"=>$newId), array("meta_value"=>$oldId, "meta_key"=>"lab_user_function"));
        $wpdb->update($wpdb->prefix.'lab_users_historic', array("function"=>$newId), array("function"=>$oldId));
    }
    else if ($type == AdminParams::PARAMS_MISSION_ID)
    {
        $wpdb->update($wpdb->prefix.'lab_invitations', array("mission_objective"=>$newId), array("mission_objective"=>$oldId));
    }
    else if ($type == AdminParams::PARAMS_FUNDING_ID)
    {
        $wpdb->update($wpdb->prefix.'lab_invitations', array("funding_source"=>$newId), array("funding_source"=>$oldId));
        $wpdb->update($wpdb->prefix.'usermeta', array("meta_value"=>$newId), array("meta_value"=>$oldId, "meta_key"=>"lab_user_funding"));
        
    }
    else if ($type == AdminParams::PARAMS_EMPLOYER)
    {
        $wpdb->update($wpdb->prefix.'usermeta', array("meta_value"=>$newId), array("meta_value"=>$oldId, "meta_key"=>"lab_user_employer"));
    }
}

function lab_admin_param_save($paramType, $paramName, $color = null, $paramId = null, $shift = null, $paramSlug = null)
{
    //return ["success"=>false, "data"=>(" paramId : ".$paramId." type : ".$paramType." value : ".$paramName." slug : ".$paramSlug." color : ".$color." shift : ".$shift)];
    global $wpdb;
    if ($type == -1) {
      $type = 0;
    }
    if (!isset($color) || empty($color))
    {
        $color = null;
    }

    if (startsWith($color, "#"))
    {
        $color = substr($color, 1, strlen($color));
    }

    if ($paramId == null)
    {
        //return "count > 0 : ".lab_admin_param_exist($paramType, $paramName);
        if (lab_admin_param_exist($paramType, $paramName)) {
            return ["success"=>false,"data"=>sprintf(__("A param key with '%1s' and type %1s already exist in db", "lab"), $paramName,AdminParams::get_param($paramType))];
        } else if (strpos($paramSlug, " ") > 0) {
            return ["success"=>false,"data"=>sprintf(__("Can't have space in param slug name '%1s'", "lab"), $paramSlug)];
        } else {
            // case of new param
            if ($shift != null && $shift == 'true' && $paramType == AdminParams::PARAMS_ID) {
                //return ["success"=>false, "data"=>"LA1"];
                $lastParamId = lab_admin_param_last_param_system_id();
                $newId = $lastParamId + 1;
                //return ["success"=>false, "data"=>"LA1 new ID : " . $newId ];

                $oldParam = lab_admin_param_get_by_id($newId);
                if ($oldParam != null)
                {
                    $cloneId  = lab_admin_param_clone($oldParam);
                    lab_admin_param_change_id($oldParam->id, $oldParam->type_param, $cloneId);
                    $wpdb->update($wpdb->prefix.'lab_params', array("type_param"=>AdminParams::PARAMS_ID, "value"=>$paramName,"slug"=>$paramSlug, "color"=>$color), array("id"=>$newId));
                }
                else {
                    $wpdb->insert($wpdb->prefix.'lab_params', array("id"=>$newId, "type_param"=>AdminParams::PARAMS_ID, "value"=>$paramName,"slug"=>$paramSlug, "color"=>$color));
                }
                return $cloneId;
            }
            else {
                //return ["success"=>false, "data"=>"LA2"];
                //return ["success"=>false, "data"=>(" paramId : ".$paramId." type : ".$paramType." value : ".$paramName." slug : ".$paramSlug." color : ".$color." shift : ".$shift)];
                $wpdb->insert($wpdb->prefix.'lab_params', array("slug"=>$paramSlug,"type_param"=>$paramType, "value"=>$paramName, "color"=>$color));
                return $wpdb->insert_id;
            }
            //*/
            
        }
    }
    else
    {
        //return ["success"=>false, "data"=>(" paramId : ".$paramId." type : ".$paramType." value : ".$paramName." slug : ".$paramSlug." color : ".$color." shift : ".$shift)];
        $wpdb->update($wpdb->prefix.'lab_params', array("type_param"=>$paramType, "value"=>$paramName, "slug"=>$paramSlug, "color"=>$color), array("id"=>$paramId));
        return $paramId;
    }
}

/**
 * Update or create keyword to user
 *
 * @param [type] $userId
 * @param [type] $keyword
 * @param [type] $keywordNumber
 * @return void
 */
function lab_admin_hal_add_keyword_to_user($userId, $keyword, $keywordNumber)
{
    global $wpdb;
    $sql = "SELECT id FROM `".$wpdb->prefix."lab_hal_keywords` WHERE value='".str_replace("'","\'", $keyword)."'";
    $results = $wpdb->get_results($sql);
    if(count($results) == 1) {
        $keywordId = $results[0]->id;
    }
    else {
        $wpdb->insert($wpdb->prefix."lab_hal_keywords",array("value"=>$keyword));
        $keywordId = $wpdb->insert_id;
    }
    $sql = "SELECT id FROM `".$wpdb->prefix."lab_hal_keywords_user` WHERE user_id=".$userId." AND keyword_id=".$keywordId;
    $results = $wpdb->get_results($sql);
    if(count($results) == 1) {
        try {
            $wpdb->update($wpdb->prefix."lab_hal_keywords_user", array("number"=>$keywordNumber), array("id"=>$results[0]->id));
        } catch (Exception $e) {
            echo("\n\n################\n \$userId".$userId."\n\$keyword :".$keyword."\n\$keywordNumber :".$keywordNumber."\n#################\n");
            echo 'Caught exception: ',  $e->getMessage(), "\n";
            exit(1);
        }
        
    }
    else {
        $wpdb->insert($wpdb->prefix."lab_hal_keywords_user", array("user_id"=>$userId, "keyword_id"=>$keywordId, "number"=>$keywordNumber));
    }
}

function lab_admin_param_exist($paramType, $paramName)
{
    global $wpdb;
    $sql = "SELECT id FROM `".$wpdb->prefix."lab_params` WHERE `type_param` = ".$paramType." AND  `value` = '".$paramName."'";
    $results = $wpdb->get_results($sql);
    //return count($results) == 1;
    //return $sql;
    if (count($results) == 1)
    {
        return true;
    }
    return 0;
}

function lab_admin_createTable_hal_keywords() {
    global $wpdb;
    $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."lab_hal_keywords` (
        `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
        `value` varchar(255) DEFAULT NULL,
        PRIMARY KEY (`id`)
      ) ENGINE=InnoDB";
    $wpdb->get_results($sql);
    
}


function lab_admin_createTable_budget_info() {
    global $wpdb;
    $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."lab_budget_info` (
        `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
        `expenditure_type` bigint NOT NULL,
        `title` varchar(255) NOT NULL,
        `request_date` date NOT NULL,
        `user_id` bigint NOT NULL,
        `info_manager_id` bigint NOT NULL,
        `site_id` bigint NOT NULL,
        `budget_manager_id` bigint NOT NULL,
        `fund_origin` bigint NOT NULL,
        `contract_title` varchar(255) NOT NULL,
        `amount` float NOT NULL,
        `order_number` varchar(255),
        `order_reference` varchar(255),
        `order_date` date NOT NULL,
        `delivery_date` date NOT NULL,
        `payment_date` date NOT NULL,
        PRIMARY KEY (`id`)
      ) ENGINE=InnoDB";
    $wpdb->get_results($sql);
    
}

function lab_admin_createTable_hal_keywords_user() {
    global $wpdb;
    $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."lab_hal_keywords_user` (
        `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
        `user_id` bigint UNSIGNED NOT NULL,
        `keyword_id` bigint UNSIGNED NOT NULL,
        `number` int NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
      ) ENGINE=InnoDB";
    $wpdb->get_results($sql);
    
}
function lab_admin_createTable_users_historic() {
    global $wpdb;
    $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."lab_users_historic` (
        `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
        `user_id` bigint UNSIGNED NOT NULL,
        `ext` tinyint NOT NULL COMMENT '0 if insite wp_users, 1 if in lab_guests',
        `begin` date NOT NULL,
        `end` date NULL,
        `mobility` bigint UNSIGNED NOT NULL DEFAULT 0,
        `mobility_status` bigint UNSIGNED NOT NULL DEFAULT 0,
        `host_id` bigint UNSIGNED NULL COMMENT 'link with a user',
        `function` bigint UNSIGNED NOT NULL COMMENT 'link with parameter lab_user_function',
        PRIMARY KEY (`id`)
      ) ENGINE=InnoDB";
    return $wpdb->query($sql);
}

function lab_admin_createTable_param() {
    global $wpdb;
    $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."lab_params` (
        `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
        `type_param` bigint UNSIGNED NOT NULL,
        `value` varchar(45) DEFAULT NULL,
        `color` varchar(8) DEFAULT NULL,
        `slug` varchar(8) DEFAULT NULL,
        PRIMARY KEY (`id`)
      ) ENGINE=InnoDB";
    $wpdb->get_results($sql);
}

function lab_admin_createTable_presence() {
    global $wpdb;
    $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."lab_presence` (
        `id` bigint NOT NULL AUTO_INCREMENT,
        `user_id` bigint NOT NULL,
        `hour_start` datetime NOT NULL,
        `hour_end` datetime NOT NULL,
        `site` int NOT NULL,
        `comment` VARCHAR(200) NOT NULL,
        `external` tinyint(1) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
      ) ENGINE=InnoDB;";
    $wpdb->get_results($sql);
    $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."lab_presence_users_workgroup` (
        `id` bigint NOT NULL AUTO_INCREMENT,
        `workgroup_id` bigint NOT NULL,
        `user_id` bigint NOT NULL,
        `external` tinyint(1) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB;";
    $wpdb->get_results($sql);
    $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."lab_presence_workgroup` (
        `id` bigint NOT NULL AUTO_INCREMENT, 
        `name` varchar(200) NOT NULL, 
        `owner_id` bigint NOT NULL, 
        `max` int NOT NULL, 
        `date` date NOT NULL DEFAULT '2020-06-05', 
        `hour_start` varchar(5) NOT NULL DEFAULT '08:00', 
        `hour_end` varchar(5) NOT NULL DEFAULT '09:00', 
        `presency_id` bigint NOT NULL, 
        PRIMARY KEY (`id`) ) ENGINE=InnoDB;";
    return $wpdb->get_results($sql);

}

function lab_admin_initTable_param() {
    global $wpdb;
    $wpdb->query("DELETE FROM ".$wpdb->prefix."lab_params WHERE ID < 7");
    $sql = "INSERT INTO `".$wpdb->prefix."lab_params` (`id`, `type_param`, `value`, `color`, `slug`) VALUES
            (1, 1, 'PARAM', NULL, NULL),
            (2, 1, 'GROUP TYPE', NULL, NULL),
            (3, 1, 'KEY TYPE', NULL, NULL),
            (4, 1, 'SITE', NULL, NULL),
            (5, 1, 'USER FUNCTION', NULL, NULL),
            (6, 1, 'MISSION', NULL, NULL),
            (7, 1, 'FUNDING', NULL, NULL),
            (8, 1, 'EMPLOYER', NULL, NULL),            
            (9, 1, 'LDAP TOKEN', NULL, NULL),
            (10, 1, 'LDAP HOST', NULL, NULL),
            (11, 1, 'LDAP BASE', NULL, NULL),
            (12, 1, 'LDAP LOGIN', NULL, NULL),
            (13, 1, 'LDAP PASSWORD', NULL, NULL),
            (14, 1, 'LDAP TLS', NULL, NULL),
            (15, 1, 'LDAP ENABLE', NULL, NULL),
            (16, 1, 'USER SECTION CN', NULL, NULL),
            (17, 1, 'USER SECTION CNU', NULL, NULL),
            (18, 1, 'OUTGOING MOBILITY', NULL, NULL),
            (19, 1, 'KEY STATE', NULL, NULL),
            (20, 1, 'ECOLE DOCTORALE', NULL, NULL),
            (21, 1, 'OUTGOING MOBILITY STATUS', NULL, NULL),
            (22, 1, 'THEMATIC', NULL, NULL),
            (23, 1, 'BUDGET INFO TYPE', NULL, NULL),
            (24, 1, 'BUDGET FUNDS', NULL, NULL),
            (25, 1, 'CONTRACT TYPE', NULL, NULL),
            (NULL, 2, 'Equipe', NULL, NULL),
            (NULL, 2, 'Groupe', NULL, NULL),
            (NULL, 3, 'Clé', NULL, 'key'),
            (NULL, 3, 'Badge', NULL, 'badge'),
            (NULL, 4, 'Luminy', '067BC2', NULL),
            (NULL, 4, 'Saint-Charles', 'F75C03', NULL),
            (NULL, 4, 'CMI', '04A777', NULL),
            (NULL, 6, 'Invitation', NULL, NULL),
            (NULL, 6, 'Mission', NULL, NULL),
            (NULL, 7, 'CNRS', NULL, NULL),
            (NULL, 7, 'AMU', NULL, NULL),
            (NULL, 7, 'ECM', NULL, NULL),
            (NULL, 15, 'false', NULL, NULL),
            (NULL, 19, 'OK', NULL, NULL),
            (NULL, 19, 'LOST', NULL, NULL),
            (NULL, 19, 'BROKEN', NULL, NULL);";
    $wpdb->get_results($sql);
}

/**
 * Return all site defined
 *
 * @return [[id, value],...]
 */
function lab_admin_list_site()
{
    global $wpdb;
    $sql = "SELECT id, value, color FROM `".$wpdb->prefix."lab_params` WHERE type_param = 4 ORDER BY value";
    return $wpdb->get_results($sql);
}

/**
 * Return label of a site
 * @param $sideId : site ID
 * @return label of a site
 */
function lab_admin_getSite($sideId)
{
    global $wpdb;
    $sql = "SELECT id, value, color FROM `".$wpdb->prefix."lab_params` WHERE id = ".$sideId;
    return $wpdb->get_results($sql)[0]->value;
}

function lab_admin_user_get_employer($userId)
{
    global $wpdb;
    $sql = "SELECT p.value FROM `".$wpdb->prefix."usermeta` AS um JOIN `".$wpdb->prefix."lab_params` AS p ON um.meta_value=p.id WHERE um.meta_key='lab_user_employer' AND um.user_id=".$userId;
    $r = $wpdb->get_results($sql);
    if (count($r) > 0)
    {
        return $r[0]->value;
    }
    return null;
}

/**
 * Return presence of all user order by user_id, start_hour
 *
 * @param [type] $startDate
 * @param [type] $endDate
 * @return void
 */
function lab_admin_list_present_user($startDate, $endDate) {
    global $wpdb;
    $sql = "SELECT lp.id, lp.user_id, lp.hour_start, lp.hour_end, lp.site as site_id, lp.comment as comment, p.value as site, um1.meta_value as first_name, um2.meta_value as last_name, um4.meta_value as office, um5.meta_value as floor, wg.name as wg_name, wg.id as wg_id, wg.owner_id as wg_user_id FROM `".$wpdb->prefix."lab_presence` AS lp JOIN ".$wpdb->prefix."lab_params as p ON p.id=lp.site JOIN ".$wpdb->prefix."usermeta AS um1 ON um1.user_id=lp.user_id JOIN ".$wpdb->prefix."usermeta AS um2 ON um2.user_id=lp.user_id JOIN ".$wpdb->prefix."usermeta AS um3 ON um3.user_id=lp.user_id JOIN ".$wpdb->prefix."usermeta AS um4 ON um4.user_id=lp.user_id JOIN ".$wpdb->prefix."usermeta AS um5 ON um5.user_id=lp.user_id LEFT JOIN wp_lab_presence_workgroup as wg on wg.presency_id=lp.id WHERE lp.external=0 AND (lp.`hour_start` BETWEEN '".date("Y-m-d", $startDate)." 00:00:00' AND '".date("Y-m-d", $endDate)." 23:59:59') AND um1.meta_key='first_name' AND um2.meta_key='last_name' AND um3.meta_key='lab_user_location' AND um4.meta_key='lab_user_office_number' AND um5.meta_key='lab_user_office_floor' ORDER BY lp.user_id, lp.`hour_start`";
    $registredUser = $wpdb->get_results($sql);
    $sql = "SELECT lp.id, lp.user_id, lp.hour_start, lp.hour_end, lp.site as site_id, lp.comment as comment, p.value as site, lug.first_name as first_name, lug.last_name as last_name FROM `".$wpdb->prefix."lab_presence` AS lp JOIN ".$wpdb->prefix."lab_params as p ON p.id=lp.site JOIN ".$wpdb->prefix."lab_guests AS lug ON lug.id=lp.user_id WHERE lp.external=1 AND (lp.`hour_start` BETWEEN '".date("Y-m-d", $startDate)." 00:00:00' AND '".date("Y-m-d", $endDate)." 23:59:59') ORDER BY lp.user_id, lp.`hour_start`";
    //return $wpdb->get_results($sql);
    $externalUser = $wpdb->get_results($sql);
    foreach($externalUser as $u) {
        $registredUser[] = $u;
    }
    return $registredUser;
    //return $sql;
}

/**
 * Return the presency save for the same day
 *
 * @param [type] $userId
 * @param [type] $startDate
 * @param [type] $endDate
 * @param [type] $presenceId
 * @return array of the presency for the same day, except the presencyId ones
 */
function lab_admin_present_get_same_day_presency($userId, $startDate, $endDate, $presenceId)
{
    global $wpdb;
    $sql = "SELECT * FROM `".$wpdb->prefix."lab_presence` WHERE `user_id`=".$userId." AND `hour_start` BETWEEN '".date("Y-m-d", $startDate)." 00:00:00' AND '".date("Y-m-d", $endDate)." 23:59:59' AND id!=".$presenceId." ORDER BY `hour_start`";
    //return $sql;
    return $wpdb->get_results($sql);
}

function lab_admin_present_not_same_half_day($userId, $startDate, $endDate, $presenceId)
{
    global $wpdb;
    $sql = "SELECT * FROM `".$wpdb->prefix."lab_presence` WHERE `user_id`=".$userId." AND `hour_start` BETWEEN '".date("Y-m-d", $startDate)." 00:00:00' AND '".date("Y-m-d", $endDate)." 23:59:59' ORDER BY `hour_start`";
    
    $r = $wpdb->get_results($sql);
    
    if (count($r) > 0) 
    {
        $hour = 0;
        foreach($r as $presency)
        {
            $endHour   = intval(date("G", strtotime($presency->hour_end)));
            $startHour = intval(date("G", strtotime($presency->hour_start)));
            if ($endHour < 13) {
                $hour += 1;
            }
            else
            {
                if ( $startHour < 13) {
                    $hour += 5;
                }
                else {
                    $hour += 3;
                }
            }
        }
        //return array("success"=>false, "data"=>"hour :" .$hour);
        //return array("success"=>false, "data"=>"presency->hour_end:"+date("G", strtotime($presency->hour_end)));
        $startHour = intval(date("G", $startDate));
        $endHour   = intval(date("G", $endDate));


        // if a presency exist in the morning
        if ($hour < 3)
        {
            //return array("success"=>false, "data"=>"hour < 3 \$startHour : ". $startHour);
            if ($startHour < 13) {
                return array("success"=>false, "data"=>sprintf(esc_html("Apologize, we only manage a presency by half day, your already present in the morning of %s"), date("Y-m-d", strtotime($r[0]->hour_start))));
                //sprintf(__("Your are already present in %s the %s between %s and %s"), $siteLabel, date("Y-m-d", strtotime($r[0]->hour_start)), date("H:i", strtotime($r[0]->hour_start)), date("H:i", strtotime($r[0]->hour_end)));
            }
        }
        // all the day
        else if ($hour == 5 || $hour >= 10)
        {
            return array("success"=>false, "data"=>sprintf(esc_html("Apologize, we only manage a presency by half day, your already present all the day %s"), date("Y-m-d", strtotime($r[0]->hour_start))));
        }
        // two resa : 1 morning, 1 afternoon
        else if ($hour >= 3 && $hour < 5)
        {
            if ($hour == 3) {
                if ($endHour > 13) {
                    return array("success"=>false, "data"=>sprintf(esc_html("Apologize, we only manage a presency by half day, your already present in the afternoon of %s"), date("Y-m-d", strtotime($r[0]->hour_start))));
                }
            }
            else {
                return array("success"=>false, "data"=>sprintf(esc_html("Apologize, we only manage a presency by half day, your already present in the morning and the afternoon of %s"), date("Y-m-d", strtotime($r[0]->hour_start))));
            }
            
        }
    }
    return array("success"=>true, "data"=>null);
}

function lab_admin_present_check_overlap_presency($userId, $startDate, $endDate, $presenceId) {
    global $wpdb;
    $presence = "";
    if ($presenceId != null) {
        $presence = " AND id != ".$presenceId;
    }
    $sql = "SELECT lp.* FROM `".$wpdb->prefix."lab_presence` AS lp WHERE lp.`user_id` = $userId AND (('".date("Y-m-d H:i:s", $startDate)."'  BETWEEN lp.`hour_start` AND lp.`hour_end` ) OR ('".date("Y-m-d H:i:s", $endDate)."' BETWEEN lp.`hour_start` AND lp.`hour_end`))".$presence;
    
    $r1 = $wpdb->get_results($sql);
    $sql = "SELECT lp.* FROM `".$wpdb->prefix."lab_presence` AS lp WHERE lp.`user_id` = $userId AND ((lp.`hour_start` BETWEEN '".date("Y-m-d H:i:s", $startDate)."' AND '".date("Y-m-d H:i:s", $endDate)."') OR (lp.`hour_end` BETWEEN '".date("Y-m-d H:i:s", $startDate)."' AND '".date("Y-m-d H:i:s", $endDate)."'))".$presence;
    $r2 = $wpdb->get_results($sql);
    //return $sql;
    return $r1+$r2;
}

/**
 * Delete param key, can't delete param with id < 5, because param system
 *
 * @param [int] $paramId : param identifier
 * @return boolean
 */
function lab_admin_param_delete_by_id($paramId) {
    global $wpdb;
    // can't delete param Id 1, because it is the default parameter
    if ($paramId > 8 ) {
        $wpdb->delete($wpdb->prefix.'lab_params', array('id' => $paramId));
        return true;
    }
    else {
        return false;
    }
}

function lab_admin_param_search_by_value($value) {
    global $wpdb;
    $sql = "SELECT * FROM `".$wpdb->prefix."lab_params` WHERE `value` LIKE '%".$value."%'";
    return $wpdb->get_results($sql);
}

function lab_admin_firstname_lastname2($name){
    global $wpdb;
    $sql="SELECT DISTINCT um1.`user_id` AS id, um3.`meta_value` AS first_name, um2.`meta_value` as last_name, um4.`meta_value` as slug
            FROM `".$wpdb->prefix."usermeta` AS um1 
            JOIN `".$wpdb->prefix."usermeta` AS um2 ON um1.`user_id` = um2.`user_id` 
            JOIN `".$wpdb->prefix."usermeta` AS um3 ON um1.`user_id` = um3.`user_id`
            JOIN `".$wpdb->prefix."usermeta` AS um4 ON um1.`user_id` = um4.`user_id`
            JOIN `".$wpdb->prefix."users` AS um5 ON um1.`user_id` = um5.`ID`
            WHERE (um1.`meta_key`='first_name' OR um1.`meta_key`='last_name') 
            AND um2.`meta_key`='last_name' 
            AND um3.`meta_key`='first_name'   
            AND um4.`meta_key`='lab_user_slug'         
            AND (um1.`meta_value` LIKE '%" . $name . "%' OR um5.`user_email` LIKE '%" . $name . "%')";
            
    $results = $wpdb->get_results($sql);
    $nbResult = $wpdb->num_rows;
    $items = array();

    foreach ($results as $r) {
        $items[] = array('label' => $r->first_name . " " . $r->last_name , 'firstname' => $r->first_name , 'lastname' => $r->last_name , 'user_id' => $r->id, 'userslug' => $r->slug);
    }
    return $items;
}

/**
 * Initialise usermeta table with our lab fields
 *
 * @return void
 */
function lab_admin_initTable_usermeta()
{
    lab_userMetaData_create_metaKeys("hal_id", null);
    lab_userMetaData_create_metaKeys("hal_name", null);
    lab_userMetaData_create_metaKeys("profile_bg_color", "#F2F2F2");
    lab_userMetaData_create_metaKeys("user_employer", "");
    lab_userMetaData_create_metaKeys("user_function", "");
    lab_userMetaData_create_metaKeys("user_funding", "");
    lab_userMetaData_create_metaKeys("user_left", null);
    lab_userMetaData_create_metaKeys("user_location", "");
    lab_userMetaData_create_metaKeys("user_office_floor", "");
    lab_userMetaData_create_metaKeys("user_office_number", "");
    lab_userMetaData_create_metaKeys("user_phone", "");
    lab_userMetaData_create_metaKeys("user_section_cn", "");
    lab_userMetaData_create_metaKeys("user_section_cnu", "");
    lab_userMetaData_create_metaKeys("user_slug", null);
    lab_userMetaData_create_metaKeys("user_position", null);
    lab_userMetaData_create_metaKeys("user_thesis_title", null);
    lab_userMetaData_create_metaKeys("user_hdr_title", null);
    lab_userMetaData_create_metaKeys("user_thesis_date", null);
    lab_userMetaData_create_metaKeys("user_hdr_date", null);
    lab_userMetaData_create_metaKeys("user_phd_school", null);
    lab_userMetaData_create_metaKeys("user_sex", null);
    lab_userMetaData_create_metaKeys("user_country", null);
    lab_admin_usermeta_fill_hal_name();
    lab_admin_usermeta_fill_user_slug();
    lab_admin_createSocial();
}

function lab_admin_add_new_user_metadata($userId)
{
    lab_userMetaData_save_key($userId, "hal_id", null);
    lab_userMetaData_save_key($userId, "hal_name", null);
    lab_userMetaData_save_key($userId, "profile_bg_color", "#F2F2F2");
    lab_userMetaData_save_key($userId, "user_employer", "");
    lab_userMetaData_save_key($userId, "user_function", "");
    lab_userMetaData_save_key($userId, "user_sex", "");
    lab_userMetaData_save_key($userId, "user_funding", "");
    lab_userMetaData_save_key($userId, "user_left", null);
    lab_userMetaData_save_key($userId, "user_location", "");
    lab_userMetaData_save_key($userId, "user_office_floor", "");
    lab_userMetaData_save_key($userId, "user_office_number", "");
    lab_userMetaData_save_key($userId, "user_phone", "");
    lab_userMetaData_save_key($userId, "user_section_cn", "");
    lab_userMetaData_save_key($userId, "user_section_cnu", "");
    lab_userMetaData_save_key($userId, "user_slug", null);
    lab_userMetaData_save_key($userId, "user_position", null);
    lab_userMetaData_save_key($userId, "user_thesis_title", null);
    lab_userMetaData_save_key($userId, "user_hdr_title", null);
    lab_userMetaData_save_key($userId, "user_phd_school", null);
    lab_userMetaData_save_key($userId, "user_thesis_date", null);
    lab_userMetaData_save_key($userId, "user_hdr_date", null);
    lab_userMetaData_save_key($userId, "user_country", null);
    lab_admin_usermeta_fill_hal_name($userId);
    lab_admin_usermeta_fill_user_slug($userId);
    lab_admin_createSocial($userId);
}

function correct_missing_usermeta_data($userId)
{
    $missingFields = check_missing_usermeta_data($userId);

    foreach($missingFields as $field)
    {
        if ($field == "hal_id" || $field == "hal_name" || $field == "user_left" || $field == "user_slug" || $field == "user_position")
        {
            lab_userMetaData_save_key($userId, $field, null);
            if ($field == "hal_name")
            {
                lab_admin_usermeta_fill_hal_name($userId);
            }
            if ($field == "user_slug")
            {
                lab_admin_usermeta_fill_user_slug($userId);
            }
        }
        else
        {
            lab_userMetaData_save_key($userId, $field, "");
        }
    }

}

function check_missing_usermeta_data($userId)
{
    $labFields = array("hal_id", "hal_name", "profile_bg_color", "user_employer", "user_sex", "user_function", "user_funding", "user_left", "user_location", "user_office_floor", "user_office_number", "user_phone", "user_section_cn", "user_section_cnu", "user_slug", "user_thesis_title", "user_thesis_date", "user_hdr_title", "user_hdr_date", "user_phd_school", "user_country");
    $missings = array();
    foreach($labFields as $field)
    {
        if (userMetaData_exist_metakey_for_user($field, $userId) === false)
        {
            $missings[] = $field;
        }
    }
    return $missings;
}

function lab_admin_complete_missing_user_metadata()
{

}

function lab_admin_firstname_lastname($param, $name){
    global $wpdb;
    $sql = "SELECT um1.`user_id` AS id, um3.`meta_value` as first_name, um2.`meta_value` as last_name 
          FROM `".$wpdb->prefix."usermeta` AS um1 JOIN `".$wpdb->prefix."usermeta` AS um2 ON um1.`user_id` = um2.`user_id` JOIN `".$wpdb->prefix."usermeta` AS um3 ON um1.`user_id` = um3.`user_id` 
          WHERE (um1.`meta_key`='first_name' OR um1.`meta_key`='last_name') 
            AND um2.`meta_key`='last_name' 
            AND um3.`meta_key`='first_name'            
            AND um1.`meta_value`LIKE '%" . $name . "%'";

  $results = $wpdb->get_results($sql);
  $nbResult = $wpdb->num_rows;
  $items = array();

  foreach ($results as $r) {
    $items[] = array(label => $r->first_name . " " . $r->last_name , value => $r->id);
  }
  return $items;
}
function lab_admin_checkTable($tableName) {
    global $wpdb;
    $sql = "SHOW TABLES LIKE '".$wpdb->prefix.$tableName."';";
    $results = $wpdb->get_results($sql);
    if (count($results)) {
    return true;
    }
    return false;
  }
  
/********************************************************************************************
 * GROUPS
 ********************************************************************************************/

function lab_admin_group_load_all()
{
    global $wpdb;
    $sql = "SELECT group_name AS value,id FROM `".$wpdb->prefix."lab_groups`;";
    $results = $wpdb->get_results($sql);
    $items = array();
    foreach ( $results as $r )
    {
      array_push($items,$r);
    }
    return $items;
}

function lab_admin_group_by_user($userId)
{
    $results = lab_group_get_user_groups($userId);
    $groups = [];
    foreach($results as $r)
    {
        $group = new \stdClass();
        $group->id = $r->id; 
        $group->name = $r->group_name;
        $group->favorite = $r->favorite;
        $groups[] = $group;
    }
    return $groups;
}

function lab_admin_group_get_user_groups_delete($groupId)
{
    global $wpdb;
    $wpdb->delete($wpdb->prefix.'lab_users_groups', array('id' => $groupId));
    return true;

}
function lab_group_get_user_groups($userId)
{
    global $wpdb;
    return $wpdb->get_results("SELECT lg.group_name, lg.url, lug.id, lug.favorite
                                FROM `".$wpdb->prefix."lab_users_groups` as lug 
                                JOIN `".$wpdb->prefix."lab_groups` AS lg ON lg.id=lug.group_id 
                                WHERE lug.`user_id`=".$userId);
}

function lab_admin_delete_all_group() {
    global $wpdb;
    $sql = "SELECT id FROM `".$wpdb->prefix."lab_groups`";
    $results =  $wpdb->get_results($sql);
    foreach($results as $r) {
        lab_admin_delete_group($r);
    }
}

function lab_admin_delete_group($groupId) {
    lab_admin_delete_users_groups_by_groupId($groupId);
    global $wpdb;
    $wpdb->delete($wpdb->prefix."lab_groups", array("id"=>$groupId));
}

function lab_admin_delete_users_groups_by_groupId($groupId) {
    global $wpdb;
    $wpdb->delete($wpdb->prefix."lab_users_groups", array("group_id"=>$groupId));
}

function lab_admin_search_group_by_acronym($ac) {
    global $wpdb;
    $sql = "SELECT group_name,id FROM `".$wpdb->prefix."lab_groups` WHERE acronym = '".$ac."';";
    $results = $wpdb->get_results($sql);
    $items = array();
    foreach ( $results as $r )
    {
      array_push($items,$r);
    }
    return $items;
}

function lab_admin_createUserGroupTable()
{
    global $wpdb;
    $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."lab_users_groups` (
        `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
        `group_id` bigint UNSIGNED NOT NULL,
        `user_id` bigint UNSIGNED NOT NULL,
        `favorite` int UNSIGNED NOT NULL,
        PRIMARY KEY(`id`)
      ) ENGINE=InnoDB;";
    $wpdb->get_results($sql);
}

function lab_admin_createUserThematicTable()
{
    global $wpdb;
    $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."lab_users_thematic` (
        `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
        `user_id` bigint UNSIGNED NOT NULL,
        `thematic_id_id` bigint UNSIGNED NOT NULL,
        PRIMARY KEY(`id`)
      ) ENGINE=InnoDB;";
    $wpdb->get_results($sql);
}

function lab_admin_createGroupTable() {
    global $wpdb;
    $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."lab_groups`(
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `acronym` varchar(20) UNIQUE,
        `group_name` varchar(255) NOT NULL,
        `group_type` TINYINT NOT NULL,
        `parent_group_id` BIGINT UNSIGNED,
        `url` varchar(255) NULL,
        PRIMARY KEY(`id`),
        FOREIGN KEY(`parent_group_id`) REFERENCES `".$wpdb->prefix."lab_groups`(`id`)) ENGINE = INNODB;";
    $wpdb->get_results($sql);
}

function lab_admin_create_group_manager_table() {
    global $wpdb;
    $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."lab_group_manager` (
        `id` bigint NOT NULL AUTO_INCREMENT,
        `user_id` bigint NOT NULL,
        `manager_type` int NOT NULL,
        PRIMARY KEY (`id`)
      ) ENGINE=InnoDB";
    $wpdb->get_results($sql);
}

function lab_admin_group_create($name,$acronym,$chief_id,$parent,$type,$url) {
    //$sql = "INSERT INTO `".$wpdb->prefix."lab_groups` (`id`, `acronym`, `group_name`, `chief_id`, `group_type`, `parent_group_id`) VALUES (NULL, '".$acronym."', '".$name."', '".$chief_id."', '".$type."', ".($parent == 0 ? "NULL" : "'".$parent."'").");";
    global $wpdb;
    $wpdb->hide_errors();
    if ( $wpdb->insert(
        $wpdb->prefix.'lab_groups',
        array(
            'acronym' => $acronym,
            'group_name' => stripslashes($name),
            //'chief_id' => $chief_id,
            'group_type' => $type,
            'parent_group_id' => $parent == 0 ? NULL : $parent,
            'url' => $url
        )
    ) ) {
        $groupId = $wpdb->insert_id;
        // add chief ID to the group
        lab_admin_users_groups_check_and_add_user($chief_id, $groupId);
        // add chief ID in manager table
        lab_admin_add_group_manager($chief_id, $groupId, 2);
        //return "groupId :".$groupId;
    } else {
        return $wpdb -> last_error;
    }
}

function lab_admin_users_groups_check_and_add_user($userId, $groupId) {
    global $wpdb;
    $results = $wpdb->get_results("SELECT * from `".$wpdb->prefix."lab_users_groups` WHERE user_id=".$userId." AND group_id=".$groupId);
    if (count($results) == 0) {
        return lab_admin_users_groups_add_user($userId, $groupId);
    }
    return false;
}

function lab_admin_add_group_manager($user_id, $group_id, $manager_type) {
    global $wpdb;
    return $wpdb->insert($wpdb->prefix.'lab_group_manager', array("group_id"=>$group_id, "user_id"=>$user_id, "manager_type"=>$manager_type));
}

function formatGroupsName($userId) {
    $groupNames = lab_group_get_user_groups($userId);
    if (count($groupNames) == 0) {
        return "";
    }
    $items = array();
    foreach($groupNames as $g) {
        $items[] ="<a href=\"$g->url\" target=\"_blank\">" . esc_html($g->group_name) . "</a>";
    }
    return join(", ", $items);
}

function lab_admin_users_groups_add_user($userId, $groupId) {
    global $wpdb;
    return $wpdb->insert($wpdb->prefix.'lab_users_groups', array("user_id"=>$userId, "group_id"=>$groupId));
}
function lab_admin_get_groups_byChief($chief_id) {
    global $wpdb;
    $sql = "SELECT * 
            FROM `".$wpdb->prefix."lab_groups` 
            WHERE `id` IN(SELECT `group_id` 
                                FROM `".$wpdb->prefix."lab_group_manager` 
                                WHERE `user_id`=".$chief_id." AND `manager_type`=2);";
    return $wpdb->get_results($sql);
}

function lab_admin_group_get_user_info($userId, $group_id) {
    global $wpdb;
    $sql = "SELECT * FROM `".$wpdb->prefix."lab_group_manager` WHERE `user_id`=".$userId." AND  `group_id`=".$group_id;
    return $wpdb->get_results($sql);
}

function lab_admin_get_manager_type($userId) {
    global $wpdb;
    $sql = "SELECT manager_type FROM `".$wpdb->prefix."lab_group_manager` WHERE `user_id`=".$userId.";";
    return $wpdb->get_results($sql);
}

function lab_admin_group_is_group_leader($userId, $groupId) {
    global $wpdb;
    $sql = "SELECT * FROM `".$wpdb->prefix."lab_group_manager` WHERE `user_id`=".$userId." AND `group_id`=".$groupId." AND `manager_type`=2";
    $res = $wpdb->get_results($sql);
    return count($res) > 0;
}

function lab_admin_get_manager_byGroup_andType($group_id, $manager_type) {
    global $wpdb;
    $sql = "SELECT `user_id` FROM `".$wpdb->prefix."lab_group_manager` WHERE `group_id`=".$group_id." AND `manager_type`=".$manager_type." ;";
    return $wpdb->get_results($sql)[0];
}

function lab_prefGroups_add($user_id, $group_id) {
    global $wpdb;
    return $wpdb -> insert($wpdb->prefix."lab_prefered_groups",
                    array(
                        "group_id"=>$group_id,
                        "user_id"=>$user_id
                    ));
}
function lab_prefGroups_remove($user_id, $group_id) {
    global $wpdb;
    return $wpdb -> delete($wpdb->prefix."lab_prefered_groups",
                    array(
                        "group_id"=>$group_id,
                        "user_id"=>$user_id
                    ));
}
function lab_group_getById($id) {
    global $wpdb;
    return $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."lab_groups WHERE id=".$id.";")[0];
}
/********************************************************************************************
 * KeyRing
 ********************************************************************************************/
function lab_keyring_createTable_keys() {
    global $wpdb;
    $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."lab_keys` (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `number` varchar(10) NOT NULL,
        `office` varchar(20),
        `type` INT NOT NULL,
        `brand` varchar(20),
        `site` text,
        `commentary` text,
        `available` boolean NOT NULL DEFAULT TRUE,
        `state` int NOT NULL COMMENT 'etat de la clef, perdue, cassée, volée'
        PRIMARY KEY(`id`)
        ) ENGINE=INNODB;";
    $wpdb->get_results($sql);
}
function lab_keyring_createTable_loans() {
    global $wpdb;
    $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."lab_key_loans` (
        `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
        `key_id` bigint UNSIGNED NOT NULL,
        `user_id` bigint UNSIGNED NOT NULL,
        `referent_id` bigint UNSIGNED NOT NULL,
        `start_date` date NOT NULL,
        `end_date` date DEFAULT NULL,
        `ended` boolean NOT NULL DEFAULT FALSE,
        `commentary` text,
        PRIMARY KEY(`id`),
        FOREIGN KEY (`key_id`) REFERENCES `".$wpdb->prefix."lab_keys` (`id`),
        FOREIGN KEY (`user_id`) REFERENCES `".$wpdb->prefix."users` (`ID`),
        FOREIGN KEY (`referent_id`) REFERENCES `".$wpdb->prefix."users` (`ID`)
      ) ENGINE=InnoDB;";
    $wpdb->get_results($sql);
}

function lab_keyring_create_key($params) {
    $params['commentary'] = strlen($params['commentary']) ? $params['commentary'] : NULL ;
    global $wpdb;
    $wpdb->hide_errors();
    if ( $wpdb->insert(
        $wpdb->prefix.'lab_keys',
        $params
        )
    ) {
        return $wpdb->insert_id;
    } else {
        return $wpdb -> last_error;
    }
}

function lab_keyring_default_key_state()
{
    global $wpdb;
    $keyStates = AdminParams::lab_admin_get_params_keyStates();
    $min = 1000000;
    foreach($keyStates as $r)
    {
        if ($r->id < $min)
        {
            $min = $r->id;
        }
    }
    return $min;
}

function lab_keyring_create_loan($params) {
    $params['commentary'] = strlen($params['commentary']) ? $params['commentary'] : NULL ;
    $params['end_date'] = strlen($params['end_date']) ? $params['end_date'] : NULL ;
    global $wpdb;
    $wpdb->hide_errors();
    if ( $wpdb->insert(
        $wpdb->prefix.'lab_key_loans',
        $params
    ) ) {
        return;
    } else {
        return $wpdb->last_query.$wpdb -> last_error;
    }
}
function lab_keyring_search_byWord($word,$limit,$page) {
    global $wpdb;
    $offset = $page*$limit;
    $sql = "SELECT * from `".$wpdb->prefix."lab_keys`
            WHERE Concat_ws(`".$wpdb->prefix."lab_keys`.`number`,`".$wpdb->prefix."lab_keys`.`office`,`".$wpdb->prefix."lab_keys`.`site`,`".$wpdb->prefix."lab_keys`.`brand`,`".$wpdb->prefix."lab_keys`.`commentary`)
            LIKE '%".$word."%'
            ORDER BY ABS(`".$wpdb->prefix."lab_keys`.`number`)
            LIMIT ".$offset.", ".$limit.";";
    $count = "SELECT COUNT(*) from `".$wpdb->prefix."lab_keys`
              WHERE Concat_ws(`".$wpdb->prefix."lab_keys`.`number`,`".$wpdb->prefix."lab_keys`.`office`,`".$wpdb->prefix."lab_keys`.`site`,`".$wpdb->prefix."lab_keys`.`brand`,`".$wpdb->prefix."lab_keys`.`commentary`)
              LIKE '%".$word."%'";
    $results = $wpdb->get_results($sql);
    $total = $wpdb->get_var($count);
    $res = array(
        "total" => $total,
        "items" => $results
    );
    return $res;
}

/*
function lab_keyring_find_key($id)
{
    global $wpdb;
    $sql = "SELECT * from `".$wpdb->prefix."lab_keys` WHERE `id` =".$id;
    return $wpdb->get_row($sql);

}
//*/

function lab_keyring_search_by_key_number($keyType, $keyNumber)
{
    global $wpdb;
    $sql = "SELECT * from `".$wpdb->prefix."lab_keys` WHERE `number` LIKE '%".$keyNumber."%' AND `type`='".$keyType."';";
    return $wpdb->get_results($sql);
    //return $sql;
}

function lab_keyring_search_key($id) {
    global $wpdb;
    $sql = "SELECT * from `".$wpdb->prefix."lab_keys` WHERE `id`=".$id.";";
    return $wpdb->get_results($sql);
}
function lab_keyring_edit_key($id,$fields) {
    $fields['commentary'] = strlen($fields['commentary']) ? $fields['commentary'] : NULL ;
    global $wpdb;
    return $wpdb->update(
        $wpdb->prefix.'lab_keys',
        $fields,
        array('id' => $id)
    );
}
function lab_keyring_delete_key($id) {
    global $wpdb;
    if ($wpdb->delete($wpdb->prefix.'lab_key_loans',array('key_id'=>$id))) {
        return $wpdb->delete(
            $wpdb->prefix.'lab_keys',
            array('id' => $id)
        );
    } else {
        return false;
    }
}

function lab_keyring_search_current_loans($user,$page,$limit) {
    global $wpdb;
    $offset = $page*$limit;
    $count = $user == 0 ? "SELECT COUNT(*) FROM `".$wpdb->prefix."lab_key_loans` WHERE `ended`=0;" : "SELECT COUNT(*) FROM `".$wpdb->prefix."lab_key_loans` WHERE `user_id`=".$user." AND `ended`=0;";
    $sql = $user == 0 ? "SELECT * FROM `".$wpdb->prefix."lab_key_loans` WHERE `ended`=0" : "SELECT * FROM `".$wpdb->prefix."lab_key_loans` WHERE `user_id`=".$user." AND `ended`=0";
    $sql .= " ORDER BY `start_date` DESC";
    $sql .= " LIMIT ".$offset.", ".$limit.";";
    $total = $wpdb->get_var($count);
    $results = $wpdb->get_results($sql);
    $res = array(
        "total" => $total,
        "items" => $results
    );
    return $res;
}
function lab_keyring_setKeyAvailable($id,$available) {
    global $wpdb;
    $wpdb->hide_errors();
    if ($wpdb->update(
        $wpdb->prefix.'lab_keys',
        array(
            'available'=>$available
        ),
        array('id' => $id)
    )) {
        return;
    } else {
        return $wpdb -> last_error;
    }
}
function lab_keyring_edit_loan($id,$params) {
    $params['end_date'] = strlen($params['end_date']) ? $params['end_date'] : NULL ;
    $params['commentary'] = strlen($params['commentary']) ? $params['commentary'] : NULL ;
    global $wpdb;
    return $wpdb->update(
        $wpdb->prefix.'lab_key_loans',
        $params,
        array('id' => $id)
    );
}
function lab_keyring_get_currentLoan_forKey($id) {
    global $wpdb;
    $sql = "SELECT * FROM `".$wpdb->prefix."lab_key_loans` WHERE `key_id`=".$id." AND ended=false";
    return $wpdb->get_results($sql);
}
function lab_keyring_end_loan($loan_id,$end_date, $key_id) {
    $avail = lab_keyring_setKeyAvailable($key_id,1);
    global $wpdb;
    if (strlen($avail)==0) {
        $res = lab_keyring_edit_loan($loan_id,array('ended'=> 1, 'end_date' => $end_date));
        return ($res === false) ? $wpdb->last_error : null;
    } else {
        return $avail;
    }
}
function lab_keyring_find_oldLoans($field, $id) {
    global $wpdb;
    $sql = "";
    if(empty($id)) 
    {
        $sql = "SELECT * from `".$wpdb->prefix."lab_key_loans` ORDER BY `start_date` DESC";
    }
    else
    {
        $sql = "SELECT * from `".$wpdb->prefix."lab_key_loans` WHERE `".$field."`=".$id." ORDER BY `start_date` DESC";
    }
    return $wpdb->get_results($sql);
}
function lab_keyring_get_loan($id) {
    global $wpdb;
    $sql = "SELECT * FROM `".$wpdb->prefix."lab_key_loans` WHERE `id`=".$id.";";
    return $wpdb->get_results($sql);
}

/**************************************************************************************************
 * SETTINGS
 *************************************************************************************************/
/**
 * List user with no metakey
 *
 * @param [type] $metadataKey
 * @return void
 */
function userMetaData_get_userId_with_no_key($metadataKey) {
    global $wpdb;
    $sql = "SELECT ID FROM `".$wpdb->prefix."users` WHERE NOT EXISTS ( SELECT 1 FROM `".$wpdb->prefix."usermeta` WHERE `".$wpdb->prefix."usermeta`.`meta_key` = '".$metadataKey."' AND `".$wpdb->prefix."usermeta`.`user_id`=`".$wpdb->prefix."users`.`ID`)";
    $results = $wpdb->get_results($sql);
    $items = array();
    foreach($results as $r) {
        $items[] = $r->ID;       
    }
    return $items;
}
function lab_admin_usermeta_fill_user_slug($userId = null)
{
    global $wpdb;
    $sql = "SELECT u.id as user_id, um1.meta_value as first_name, um2.meta_value as last_name, um3.umeta_id as id FROM `".$wpdb->prefix."users` AS u JOIN `".$wpdb->prefix."usermeta` as um1 ON um1.user_id=u.ID JOIN `".$wpdb->prefix."usermeta` AS um2 ON um2.user_id=u.ID JOIN `".$wpdb->prefix."usermeta` AS um3 ON um3.user_id=u.ID WHERE um1.meta_key='first_name' AND um2.meta_key='last_name' AND um3.meta_key='lab_user_slug'";
    if ($userId != null) {
        $sql .= " AND u.id=".$userId;
    }
    $results = $wpdb->get_results($sql);
    $retour = array();
    foreach($results as $r) {
        $wpdb->update($wpdb->prefix."usermeta", array('meta_value'=>usermeta_format_name_to_slug($r->first_name, $r->last_name)), array('umeta_id'=> $r->id));
    }
}

function lab_userMetaData_create_metaKeys($metadataKey, $defaultValue) {

    if (substr($metadataKey, 0, strlen(LAB_META_PREFIX)) !== LAB_META_PREFIX) {
        $metadataKey = LAB_META_PREFIX.$metadataKey;
    }
    $userIds = userMetaData_get_userId_with_no_key($metadataKey);
    //return $userIds;
    $errors = array();
    $ids = array();
    foreach($userIds as $userId) {
        if (lab_userMetaData_save_key($userId, $metadataKey, $defaultValue) == false) {
            $errors[] = $wpdb->last_error();
        }
    }
    if (count($errors) > 0) {
        return $errors;
    }
    return true;
}

/**
 * Save meta key, create e new one if metakey doesn't exist, update an existing one
 *
 * @param [type] $userId
 * @param [type] $metadataKey
 * @param [type] $defaultValue
 * @return Last insert id or update id, false otherwise
 */
function lab_userMetaData_save_key($userId, $metadataKey, $defaultValue) {
    global $wpdb;
    if ($defaultValue == 'null') {
        $defaultValue = null;
    }
    if (substr($metadataKey, 0, strlen(LAB_META_PREFIX)) !== LAB_META_PREFIX) {
        $metadataKey = LAB_META_PREFIX.$metadataKey;
    }


    $umId = userMetaData_exist_metakey_for_user($metadataKey, $userId);
    if (!$umId) {
        $wpdb->insert($wpdb->prefix."usermeta", array('meta_value'=>$defaultValue, 'umeta_id'=>null, 'user_id'=>$userId, 'meta_key'=>$metadataKey));
        $r = $wpdb->insert_id;
    } else {
        $wpdb->update($wpdb->prefix."usermeta", array('meta_value'=>$defaultValue), array('umeta_id'=>$umId, 'user_id'=>$userId, 'meta_key'=>$metadataKey));
        $r = $umId;
    }
    if (!$r) {
        return $wpdb->last_error();
    }
    return $r;
}

function userMetaData_delete_metaKey($metadataKey) {
    global $wpdb;
    $sql = "DELETE FROM `".$wpdb->prefix."usermeta` WHERE `".$wpdb->prefix."usermeta`.`meta_key` = '".LAB_META_PREFIX.$metadataKey."'";
    return $wpdb->get_results($sql);
}

function userMetaData_list_metakeys() {
    global $wpdb;
    $sql = "SELECT DISTINCT meta_key FROM `".$wpdb->prefix."usermeta` WHERE meta_key LIKE '".LAB_META_PREFIX."%'";
    $items = array();
    //foreach($wpdb->get_results($sql) as $r)
    $results = $wpdb->get_results($sql);
    if (count($results) > 0) {
        foreach($results as $r) {
            $items[] = $r->meta_key;
        }
    }
    return $items;
}

function userMetaData_delete_metakeys($metadataKey) {
    
    // theoricaly we only allow to delete metadatakey begin with LAB_META_PREFIX
    if (substr($metadataKey, 0, strlen(LAB_META_PREFIX)) !== LAB_META_PREFIX) {
        $metadataKey = LAB_META_PREFIX.$metadataKey;
    }
    global $wpdb;
    return $wpdb->delete($wpdb->prefix.'usermeta', array("meta_key"=>$metadataKey));
}

/**
 * Return usermeta data id, false if this usermetadata does not exist
 *
 * @param [type] $metadataKey
 * @param [type] $userId
 * @return Return usermeta data id, false if this usermetadata does not exist
 */
function userMetaData_exist_metakey_for_user($metadataKey, $userId) {
    if (substr($metadataKey, 0, strlen(LAB_META_PREFIX)) !== LAB_META_PREFIX) {
        $metadataKey = LAB_META_PREFIX.$metadataKey;
    }
    global $wpdb;
    $results = $wpdb->get_results("SELECT umeta_id FROM `".$wpdb->prefix."usermeta` WHERE `meta_key` = '".$metadataKey."' AND `user_id`=".$userId);
    if (count($results) > 0) {
        return $results[0]->umeta_id;
    }
    return false;
}

function userMetaData_exist_metakey($metadataKey) {
    if (substr($metadataKey, 0, strlen(LAB_META_PREFIX)) !== LAB_META_PREFIX) {
        $metadataKey = LAB_META_PREFIX.$metadataKey;
    }
    global $wpdb;
    $results = $wpdb->get_results("SELECT umeta_id FROM `".$wpdb->prefix."usermeta` WHERE `meta_key` = '".$metadataKey."'");
    if (count($results) > 0) {
        return $results[0]->umeta_id;
    }
    return false;
}
function lab_admin_createSocial($userId = null) {
    foreach (['facebook','instagram','linkedin','pinterest','twitter','tumblr','youtube'] as $reseau) {
        lab_userMetaData_create_metaKeys($reseau,'', $userId);
    }
}
/**************************************************************************************************
 * HAL
 *************************************************************************************************/
function lab_hal_createTable_hal() {
    global $wpdb;
    $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."lab_hal` (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `user_id` int NOT NULL,
        `docid` int NOT NULL COMMENT 'docid issus de hal',
        `citationFull_s` varchar(1000) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
        `title` varchar(200) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
        `url` varchar(200) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
        `producedDate_tdate` date,
        `journalTitle_s` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
        PRIMARY KEY(`id`)
      ) ENGINE=InnoDB";
    $wpdb->get_results($sql);
    $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."lab_hal_users` (
        `id` bigint UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
        `hal_id` bigint UNSIGNED NOT NULL,
        `user_id` bigint UNSIGNED NOT NULL
      ) ENGINE=InnoDB";
    return $wpdb->get_results($sql);

    lab_admin_createTable_hal_keywords_user();
    lab_admin_createTable_hal_keywords();
}

/**
 * List all the hal articles by groups and year
 *
 * @param [type] $groups type array
 * @param [type] $year
 * @return void
 */
function lab_hal_getPublication_by_group($groups, $year = null)
{
    global $wpdb;
    $sql = "";
    if (is_array($groups))
    {
        $sql = "SELECT lh.* FROM `".$wpdb->prefix."lab_hal` as lh JOIN `".$wpdb->prefix."lab_hal_users` AS lhu ON lhu.hal_id=lh.id WHERE lhu.user_id IN (";
        $sql .= "SELECT DISTINCT lug.user_id FROM `".$wpdb->prefix."lab_groups` AS lg JOIN `".$wpdb->prefix."lab_users_groups` AS lug ON lug.group_id=lg.id WHERE ";
        foreach($groups as $g) {
            $sql .= " lg.acronym='".$g."' OR";
        }
        $sql = substr($sql, 0, strlen($sql) - 2);

        $sql .= ")";
        if ($year != null)
        {
            $sql .= " AND lh.`producedDate_tdate` >= '".$year."-01-01'  AND lh.`producedDate_tdate` <= '".$year."-12-31'";
        }

    }
    else
    {
        $sql = "SELECT DISTINCT lh.id, lh.* FROM  `".$wpdb->prefix."lab_groups` AS lg JOIN `".$wpdb->prefix."lab_users_groups` AS lug ON lug.group_id=lg.id JOIN `".$wpdb->prefix."lab_hal_users` AS lhu ON lhu.user_id=lug.user_id JOIN `".$wpdb->prefix."lab_hal` as lh ON lh.id=lhu.hal_id WHERE lg.acronym='".$groups."'";
        if ($year != null)
        {
            $sql .= " AND lh.`producedDate_tdate` >= '".$year."-01-01'  AND lh.`producedDate_tdate` <= '".$year."-12-31'";
        }
    }
    $sql .= " ORDER BY `lh`.`producedDate_tdate` DESC";
    return $wpdb->get_results($sql);
    //return $sql;
}

function lab_hal_get_publication($userId, $year = null) {
    global $wpdb;
    
    if(!isset($userId) || $userId == null || empty($userId))
    {
        return null;
    }
    //*/
    $sql = "SELECT lh.* FROM `".$wpdb->prefix."lab_hal` as lh JOIN `".$wpdb->prefix."lab_hal_users` AS lhu ON lhu.hal_id=lh.id WHERE lhu.user_id=".$userId;
    
    if ($year != null)
    {
        $sql .= " AND `producedDate_tdate` >= '".$year."-01-01'  AND `producedDate_tdate` <= '".$year."-12-31'";
    }
    $sql .= " ORDER BY `lh`.`producedDate_tdate` DESC";
    //return $sql;
    return $wpdb->get_results($sql);
}

function format_spaced_string($s) {
    if (strpos($s, " ") > 0) {
        $ef = explode(" ", $s);
        for ($i = 0 ; $i < count($ef); $i++) {
            if ($i > 0) {
                $ef[$i] = ucfirst($ef[$i]);
            }
        }
        $s = join("", $ef);
    }
    return $s;
}

function replaceAccent($str)
{
    $search  = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'à', 'á', 'â', 'ã', 'ä', 'å', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ð', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ');
	$replace = array('A', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 'a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y');
	return str_replace($search, $replace, $str);
}

/**
 * Format user firstname and lastname to slug format (noSpace,fistname.lastName)
 *
 * @param [type] $f firstname
 * @param [type] $l lastname
 * @return slug
 */
function usermeta_format_name_to_slug($f, $l) {
    $f = strtolower($f);
    $l = strtolower($l);
    $f = format_spaced_string($f);
    $l = format_spaced_string($l);
    $f = replaceAccent($f);
    $l = replaceAccent($l);
    return $f.".".$l;
}

/**
 * Format name for hal request, replace space character by '+'
 */
function hal_format_name($name) {
    
    if (strpos($name, " ") > -1)
    {
        $t = explode(" ", $name);
        $arraylenght = count($t);
        $i = 0;
        $nameF = "";
        
        while ($i < $arraylenght) {
            $nameF .= ucfirst(strtolower($t[$i]));
            if ($i + 1 < $arraylenght) {
                $nameF .= "+";
            }
            $i++;
        }
        return $nameF;
        //*/
    }
    else {
        return ucfirst(strtolower($name));
    }
}

function lab_admin_usermeta_fill_hal_name($userId = null) {
    global $wpdb;
    $sql = "SELECT u.id as user_id, um1.meta_value as first_name, um2.meta_value as last_name, um3.umeta_id as id FROM `".$wpdb->prefix."users` AS u JOIN `".$wpdb->prefix."usermeta` as um1 ON um1.user_id=u.ID JOIN `".$wpdb->prefix."usermeta` AS um2 ON um2.user_id=u.ID JOIN `".$wpdb->prefix."usermeta` AS um3 ON um3.user_id=u.ID WHERE um1.meta_key='first_name' AND um2.meta_key='last_name' AND um3.meta_key='lab_hal_name'";
    if ($userId != null) {
        $sql .= " AND u.id=".$userId;
    }
    $results = $wpdb->get_results($sql);
    $retour = array();
    foreach($results as $r) {
        $wpdb->update($wpdb->prefix."usermeta", array('meta_value'=>hal_format_name($r->last_name).'+'.hal_format_name($r->first_name)), array('umeta_id'=> $r->id));
        //$retour[] = "wpdb->update(".$wpdb->prefix."usermeta, array('meta_value'=>".hal_format_name($r->last_name)."+".hal_format_name($r->first_name)."), array('umeta_id'=> ".$r->id."));";
        //$retour[] = "UPDATE ".$wpdb->prefix."usermeta SET meta_value='".ucfirst(strtolower($r->last_name)).'+'.ucfirst(strtolower($r->first_name))."' WHERE ".$r->id;
    }
    return $retour;
}

function get_hal_url($userId) {
    global $wpdb;
    $sql = "SELECT um1.meta_value as lab_hal_id, um2.meta_value as lab_hal_name FROM `".$wpdb->prefix."usermeta` AS um1 JOIN `".$wpdb->prefix."usermeta` AS um2 ON um2.user_id=um1.user_id WHERE um1.`user_id`=".$userId." AND um1.`meta_key`='lab_hal_id' AND um2.`meta_key`='lab_hal_name'";
    
    $results = $wpdb->get_results($sql);
    if (count($results) == 1) {
        $hal_id   = $results[0]->lab_hal_id;
        $hal_name = $results[0]->lab_hal_name;
        if ($hal_id != null) {
            //return "https://api.archives-ouvertes.fr/search/?q=*:*&fq=authIdHal_s:(".$hal_id.")&fl=docid,citationFull_s,producedDate_tdate,uri_s,title_s,journalTitle_s&sort=producedDate_tdate+desc&wt=json&json.nl=arrarr";
            //return "https://api.archives-ouvertes.fr/search/?q=*:*&fq=authIdHal_s:(".$hal_id.")&group=true&group.field=docType_s&group.limit=1000&&fl=docid,citationFull_s,producedDate_tdate,uri_s,title_s,journalTitle_s&facet.field=fr_domainAllCodeLabel_fs&facet.field=keyword_s&facet.field=journalIdTitle_fs&facet.field=producedDateY_i&facet.field=authIdLastNameFirstName_fs&facet.field=instStructIdName_fs&facet.field=labStructIdName_fs&facet.field=deptStructIdName_fs&facet.field=rteamStructIdName_fs&facet.mincount=1&facet=true&wt=json&json.nl=arrarr";
            return "https://api.archives-ouvertes.fr/search/?q=*:*&fq=authIdHal_s:(".$hal_id.")&fl=docid,citationFull_s,producedDate_tdate,uri_s,title_s,journalTitle_s&facet.field=fr_domainAllCodeLabel_fs&facet.field=keyword_s&facet.mincount=1&facet=true&wt=json&json.nl=arrarr";
        }
        else {
            return "https://api.archives-ouvertes.fr/search/?q=authLastNameFirstName_s:%22".$hal_name."%22&fl=docid,citationFull_s,producedDate_tdate,uri_s,title_s,journalTitle_s&facet.field=fr_domainAllCodeLabel_fs&facet.field=keyword_s&facet.mincount=1&facet=true&wt=json&json.nl=arrarr";
        }
    }
}

function saveHalProduction($docId, $citation, $productionDate, $title, $url, $journal) {
    global $wpdb;
    //$wpdb->show_errors(true);
    if ($wpdb->insert($wpdb->prefix."lab_hal", array('journalTitle_s'=>$journal, 'docid'=>$docId, 'citationFull_s'=>$citation, 'producedDate_tdate'=>$productionDate, 'title'=>$title, 'url'=>$url))) {
        //$results = $wpdb->get_results("SELECT id from `".$wpdb->prefix."lab_hal` WHERE docid='".$docId."'");
    }
    else{
        //echo "[".$docid."] : ".$wpdb->last_error."\n";
    }
    return $wpdb->insert_id;
}

function saveHalUsers($userId, $halId) {
    global $wpdb;
    $wpdb->insert($wpdb->prefix."lab_hal_users", array('user_id'=>$userId, 'hal_id'=>$halId));
    return $wpdb->insert_id;
}

/**
 * Download HAL info for all user in db
 */
function hal_download_all()
{
    global $wpdb;
    $sql = "SELECT u.id FROM `".$wpdb->prefix."users` AS u JOIN `".$wpdb->prefix."usermeta` AS um ON um.user_id=u.ID WHERE um.meta_key='lab_user_left' AND um.meta_value IS NULL";
    $results = $wpdb->get_results($sql);

    $docIds = array();
    foreach($results as $r) 
    {
        hal_download_1($r->id, $docIds);
    }
}


function get_hal_url_1($userId) {
    global $wpdb;
    $sql = "SELECT um1.meta_value as lab_hal_id, um2.meta_value as lab_hal_name FROM `".$wpdb->prefix."usermeta` AS um1 JOIN `".$wpdb->prefix."usermeta` AS um2 ON um2.user_id=um1.user_id WHERE um1.`user_id`=".$userId." AND um1.`meta_key`='lab_hal_id' AND um2.`meta_key`='lab_hal_name'";
    
    $results = $wpdb->get_results($sql);
    if (count($results) == 1) {
        $hal_id   = $results[0]->lab_hal_id;
        $hal_name = $results[0]->lab_hal_name;
        if ($hal_id != null) {
            //return "https://api.archives-ouvertes.fr/search/?q=*:*&fq=authIdHal_s:(".$hal_id.")&fl=docid,citationFull_s,producedDate_tdate,uri_s,title_s,journalTitle_s&sort=producedDate_tdate+desc&wt=json&json.nl=arrarr";
            //return "https://api.archives-ouvertes.fr/search/?q=*:*&fq=authIdHal_s:(".$hal_id.")&group=true&group.field=docType_s&group.limit=1000&&fl=docid,citationFull_s,producedDate_tdate,uri_s,title_s,journalTitle_s&facet.field=fr_domainAllCodeLabel_fs&facet.field=keyword_s&facet.field=journalIdTitle_fs&facet.field=producedDateY_i&facet.field=authIdLastNameFirstName_fs&facet.field=instStructIdName_fs&facet.field=labStructIdName_fs&facet.field=deptStructIdName_fs&facet.field=rteamStructIdName_fs&facet.mincount=1&facet=true&wt=json&json.nl=arrarr";
            //return "https://api.archives-ouvertes.fr/search/?q=*:*&fq=authIdHal_s:(".$hal_id.")&fl=docid,citationFull_s,producedDate_tdate,uri_s,title_s,journalTitle_s&facet.field=fr_domainAllCodeLabel_fs&facet.field=keyword_s&facet.mincount=1&facet=true&wt=json&json.nl=arrarr";
            return "https://api.archives-ouvertes.fr/search/hal/?omitHeader=true&wt=json&q=authIdHal_s:(".$hal_id.")&sort=producedDate_tdate+desc&fq=NOT+instance_s%3Asfo&fq=NOT+instance_s%3Adumas&fq=NOT+instance_s%3Amemsic&fq=NOT+instance_s%3Ahceres&fq=NOT+%28docType_s%3A%28THESE+OR+HDR%29+AND+submitType_s%3A%28notice+OR+annex%29%29&fq=NOT+docType_s%3A%28MEM+OR+PRESCONF+OR+MINUTES+OR+NOTE+OR+SYNTHESE+OR+OTHERREPORT+OR+REPACT+OR+BOOKREPORT%29&fq=NOT+status_i%3A111&defType=edismax&rows=1000&fl=halId_s%2Curi_s%2CdocType_s%2CdoiId_s%2CnntId_s%2Ctitle_s%2CsubTitle_s%2CauthFullName_s%2CproducedDate_s%2CjournalTitle_s%2CjournalPublisher_s%2Cvolume_s%2Cnumber_s%2Cpage_s%2CconferenceTitle_s%2CconferenceStartDate_s%2Ccountry_s%2Clanguage_s%2CinPress_bool%2Cdocid%2CjournalTitle_s%2CcitationFull_s%2Ckeyword_s%2CstructCode_s&sort=score+desc";
        }
        else {
            return "https://api.archives-ouvertes.fr/search/hal/?omitHeader=true&wt=json&q=authLastNameFirstName_s:%22".$hal_name."%22&sort=producedDate_tdate+desc&fq=NOT+instance_s%3Asfo&fq=NOT+instance_s%3Adumas&fq=NOT+instance_s%3Amemsic&fq=NOT+instance_s%3Ahceres&fq=NOT+%28docType_s%3A%28THESE+OR+HDR%29+AND+submitType_s%3A%28notice+OR+annex%29%29&fq=NOT+docType_s%3A%28MEM+OR+PRESCONF+OR+MINUTES+OR+NOTE+OR+SYNTHESE+OR+OTHERREPORT+OR+REPACT+OR+BOOKREPORT%29&fq=NOT+status_i%3A111&defType=edismax&rows=1000&fl=halId_s%2Curi_s%2CdocType_s%2CdoiId_s%2CnntId_s%2Ctitle_s%2CsubTitle_s%2CauthFullName_s%2CproducedDate_s%2CjournalTitle_s%2CjournalPublisher_s%2Cvolume_s%2Cnumber_s%2Cpage_s%2CconferenceTitle_s%2CconferenceStartDate_s%2Ccountry_s%2Clanguage_s%2CinPress_bool%2Cdocid%2CjournalTitle_s%2CcitationFull_s%2Ckeyword_s%2CstructCode_s&sort=score+desc";
            //return "https://api.archives-ouvertes.fr/search/?q=authLastNameFirstName_s:%22".$hal_name."%22&fl=docid,citationFull_s,producedDate_tdate,uri_s,title_s,journalTitle_s&facet.field=fr_domainAllCodeLabel_fs&facet.field=keyword_s&facet.mincount=1&facet=true&wt=json&json.nl=arrarr";
        }
    }
}

function is_user_left($userId)
{
    global $wpdb;
    $sql = "SELECT meta_value as gone FROM `".$wpdb->prefix."usermeta` WHERE `user_id`=".$userId." AND `meta_key`='lab_user_left'";
    $results = $wpdb->get_results($sql);
    return $results[0]->gone;
}

function hal_download_1($userId, &$docIds) {
    $url  = get_hal_url_1($userId);
    $left = is_user_left($userId) != null;
    
    if ($docIds == null) {
        $docId = array();
    }
    // keywords
    $kw = array();
    echo($url."\n");
    $json = lab_do_common_curl_call($url);
    if (!isset($json->response) || !$json->response) {
        echo "No data for user ".$userId."\n";
        return;
    }
    $docs = $json->response->docs;
    if(isset($json->response->docs) && $json->response->docs)
    {
        $c    = count($json->response->docs);
        echo("\$c:".$c."\n");
        $display = false;
        for ($i = 0; $i < $c; $i++) {
            $keep = false;
            $docId = $docs[$i]->docid;
            //echo "[".$i."]=".$docId."\n";
            $citation = $docs[$i]->citationFull_s;
            $title = $docs[$i]->title_s[0];

            if(isset($docs[$i]->producedDate_s))
            {
                $producedDate = $docs[$i]->producedDate_s;
                // sometime day is missing, we add it
                if (substr_count($docs[$i]->producedDate_s,"-") == 0)
                {
                    $producedDate .= "-01-01";
                }
                else if (substr_count($docs[$i]->producedDate_s,"-") == 1)
                {
                    $producedDate .= "-01";
                }
                echo $producedDate." - ".$title."\n";
                $producedDate = strtotime($producedDate);
            }
            else {
                echo "Pas de producedDate_s pour : ".$title."\n";
            }

            $url = $docs[$i]->uri_s;

            // if user left the lab we only keep articles with our lab signature
            $labSignatureFound = false;
            if ($left) {    
                if(isset($docs[$i]->structCode_s)) {
                    $strutures = $docs[$i]->structCode_s;
                    $structuresSize = count($strutures);
                    for ($s = 0; $s < $structuresSize; $s++) {
                        if ($strutures[$s] == "UMR7373" || $strutures[$s] == "UMR6632" || $strutures[$s] == "UMR6206") {
                            $labSignatureFound = true;
                            $s = $structuresSize;
                        }
                    }

                }
            }

            if (!$left || ($left && labSignatureFound))
            {
                if (isset($docs[$i]->journalTitle_s))
                {
                    $journal = $docs[$i]->journalTitle_s;
                }
                else {
                    $journal = null;
                }

                if (!array_key_exists ($docId, $docIds)) {
                    
                    $id = saveHalProduction($docId, $citation, date('Y-m-d', $producedDate), $title, $url, $journal);
                    
                    $docIds[$docId] = $id;
                    $halId = $id;            
                }

                if (isset($docs[$i]->keyword_s))
                {
                    $keywords = $docs[$i]->keyword_s;
                    $kwc = count($keywords);
                    for ($j = 0; $j < $kwc; $j++) {
                        //$keyword = preg_replace('/[\x00-\x1F\x7F]/u', '',$keywords[$j]);
                        $keyword = preg_replace( '/[^[:print:]]/', '',$keywords[$j]);
                        //echo $keyword."\n";
                        if(array_key_exists($keyword, $kw))
                        {
                            $kw[$keyword] = $kw[$keyword] + 1;
                        }
                        else
                        {
                            $kw[$keyword] = 1;
                        }
                    }
                }

                saveHalUsers($userId, $docIds[$docId]);
            }
            //echo "FIN : ".$docId."\n";
        }

        foreach($kw as $word=>$num)
        {
            lab_admin_hal_add_keyword_to_user($userId, $word, $num);
        }

    }

}

function hal_download($userId, &$docIds) {
    $url = get_hal_url($userId);
    
    if ($docIds == null) {
        $docId = array();
    }
    echo($url."\n");

    $json = lab_do_common_curl_call($url);
    if (!isset($json->response) || !$json->response) {
        echo "No data for user ".$userId."\n";
        return;
    }

    $docs = $json->response->docs;
    if(isset($json->response->docs) && $json->response->docs)
    {
        $c    = count($json->response->docs);
        echo("\$c:".$c."\n");
        
        $display = false;
        for ($i = 0; $i < $c; $i++) {
            $keep = false;
            $docId = $docs[$i]->docid;
            $citation = $docs[$i]->citationFull_s;
            $producedDate = strtotime($docs[$i]->producedDate_tdate);
            $title = $docs[$i]->title_s[0];
            $url = $docs[$i]->uri_s;
            //echo ($citation."\n");
            if (isset($docs[$i]->journalTitle_s))
            {
                $journal = $docs[$i]->journalTitle_s;
            }
            else {
                $journal = null;
            }

            if (!array_key_exists ($docId, $docIds)) {
                
                $id = saveHalProduction($docId, $citation, date('Y-m-d', $producedDate), $title, $url, $journal);
                
                $docIds[$docId] = $id;
                $halId = $id;            
            }
            saveHalUsers($userId, $docIds[$docId]);
        }
    }
    /*
    $fr_domainAllCodeLabel_fs = $json->facet_counts->facet_fields->fr_domainAllCodeLabel_fs;
    $c = count($fr_domainAllCodeLabel_fs);
    
    for ($i = 0; $i < $c; $i++) {
        $domain  = $fr_domainAllCodeLabel_fs[$i][0];
        $percent = $fr_domainAllCodeLabel_fs[$i][1];
        $name = explode("_FacetSep_", $domain);
        $code = $name[0];
        $translation = $name[1];
        echo($code."\n");
        echo($translation."\n");
        $fields = explode("/", $translation);
        foreach($fields as $field) {
            echo(preg_replace("/\[.*\]/","", $field)."\n");
        }
    }
    //*/

    $keywords = $json->facet_counts->facet_fields->keyword_s;
    $c = count($keywords);
    for ($i = 0; $i < $c; $i++) {
        $keyword = $keywords[$i][0];
        $keyWordPercent = $keywords[$i][1];
        //echo ($keyword." ".$keyWordPercent."\n");
        lab_admin_hal_add_keyword_to_user($userId, $keyword, $keyWordPercent);
    }

    return $url;
}

function delete_hal_table() {
    global $wpdb;
    $wpdb->query("TRUNCATE TABLE `".$wpdb->prefix."lab_hal_users`");
    $wpdb->query("TRUNCATE TABLE `".$wpdb->prefix."lab_hal_keywords_user`");
    $wpdb->query("TRUNCATE TABLE `".$wpdb->prefix."lab_hal_keywords`");
    $wpdb->query("TRUNCATE TABLE `".$wpdb->prefix."lab_hal`");//delete( $wpdb->prefix."lab_hal", array());
    $wpdb->get_results("ALTER TABLE `".$wpdb->prefix."lab_hal` AUTO_INCREMENT = 1");
    $wpdb->get_results("ALTER TABLE `".$wpdb->prefix."lab_hal_users` AUTO_INCREMENT = 1");
    $wpdb->get_results("ALTER TABLE `".$wpdb->prefix."lab_hal_keywords_user` AUTO_INCREMENT = 1");
    $wpdb->get_results("ALTER TABLE `".$wpdb->prefix."lab_hal_keywords` AUTO_INCREMENT = 1");
    return true;
}

/**
 * @param $url
 * @return object
 */

function lab_do_common_curl_call($url) {
    $ch = curl_init($url);
    // Options
    $options = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => array('Content-type: application/json'),
        CURLOPT_TIMEOUT => 10,
        CURLOPT_USERAGENT => "LAB Plugin Wordpress "
    );
    if(defined('WP_PROXY_HOST') && defined('WP_PROXY_PORT') && defined('WP_PROXY_USERNAME') && defined('WP_PROXY_PASSWORD')){
        curl_setopt($ch, CURLOPT_PROXY, WP_PROXY_HOST);
        curl_setopt($ch, CURLOPT_PROXYPORT, WP_PROXY_PORT);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_PROXYUSERPWD, WP_PROXY_USERNAME.':'.WP_PROXY_PASSWORD);
    }
    // Bind des options et de l'objet cURL que l'on va utiliser
    curl_setopt_array($ch, $options);
    // Récupération du résultat JSON
    $json = json_decode(curl_exec($ch));
    curl_close($ch);
    return $json;
}
//*/
function lab_hal_getLastArticles($number,$group) {
    global $wpdb;
    if (!isset($number) || empty($number)) {
        $number = 5;
    }
    if ($group == 0) {
        $sql = "SELECT * from `".$wpdb->prefix."lab_hal` ORDER BY `producedDate_tdate` DESC LIMIT ".$number.";";
    } else {
        $sql = "SELECT * FROM `".$wpdb->prefix."lab_hal` WHERE user_id IN (SELECT user_id from `".$wpdb->prefix."lab_users_groups` WHERE `".$wpdb->prefix."lab_users_groups`.`group_id` = ".$group.") ORDER BY `producedDate_tdate` DESC LIMIT ".$number;
    }
    return $wpdb->get_results($sql);
}

/**************************************************************************************************
 * Plug-in Setup and Uninstallation
 *************************************************************************************************/

function lab_activation_hook() {
    lab_create_roles();
    create_all_tables();
}
function lab_uninstall_hook() {
    delete_all_tables();
}

function lab_admin_setting_reset_tables()
{
    delete_all_tables();
    create_all_tables();
}



function create_all_tables() {
    lab_admin_createTable_param();
    lab_admin_initTable_param();
    lab_hal_createTable_hal();
    lab_admin_createGroupTable();
    lab_admin_create_group_manager_table();
    lab_admin_createUserGroupTable();
    lab_admin_createUserThematicTable();
    lab_keyring_createTable_keys();
    lab_keyring_createTable_loans();
    lab_admin_initTable_usermeta();
    lab_admin_createTable_presence();
    lab_invitations_createTables();
    lab_admin_createTable_users_historic();
    lab_admin_createTable_budget_info();
    lab_admin_contract_create_table();
    lab_admin_mission_create_table();
    lab_admin_notification_create_table();
}

function delete_all_tables() {
    //lab_admin_delete_group(0);
    lab_admin_delete_all_group();
    drop_table("lab_prefered_groups");
    drop_table("lab_users_groups");
    drop_table("lab_params");
    drop_table("lab_key_loans");
    drop_table("lab_keys");
    drop_table("lab_hal");
    drop_table("lab_hal_keywords");
    drop_table("lab_hal_keywords_user");
    drop_table("lab_hal_users");
    drop_table("lab_groups");
    drop_table("lab_presence");
    drop_table("lab_invitations");
    drop_table("lab_guests");
    drop_table("lab_invite_comments");
    drop_table("lab_presence");
    drop_table("lab_budget_info");
    drop_table("lab_contract");
    drop_table("lab_contract_user");
    drop_table("lab_mission_route");
    drop_table("lab_mission_comment_notifs");
}

/**
 * DROP TABLE 
 * @param : table name without prefix
 */
function drop_table($tableName) {
    global $wpdb;
    $sql = "DROP TABLE `".$wpdb->prefix.$tableName."`";
    $wpdb->get_results($sql);

}

function lab_create_roles() {
    add_role(
        'mission_budget_manager',
        'Mission Budget Manager',
        [
            'read'      => true
        ]
    );
    $role = get_role('mission_budget_manager');
    $role ->add_cap('budget_mission',true);
    $role = get_role('administrator');
    $role ->add_cap('budget_mission',true);
    add_role(
        'mission_manager',
        'Mission Manager',
        [
            'read'      => true
        ]
    );
    $role = get_role('mission_manager');
    $role ->add_cap('mission_manager',true);
    $role = get_role('administrator');
    $role ->add_cap('mission_manager',true);
    add_role(
        'budget_info_manager',
        'Budget Info Manager',
        [
            'read'      => true
        ]
    );
    $role = get_role('budget_info_manager');
    $role ->add_cap('budget_info',true);
    $role = get_role('administrator');
    $role ->add_cap('budget_info',true);
    add_role(
        'key_manager',
        'Key Manager',
        [
            'read'      => true
        ]
    );
    $role = get_role('key_manager');
    $role ->add_cap('keyring',true);
    $role = get_role('administrator');
    $role ->add_cap('keyring',true);
    add_role(
        'lab_user_manager',
        'User Manager',
        [
            'read'  => true
        ]
    );
    $role = get_role('lab_user_manager');
    $role ->add_cap('lab_user_manager',true);
    $role = get_role('administrator');
    $role ->add_cap('lab_user_manager',true);
    add_role(
        'lab_manager',
        'Budget Manager',
        [
            'read'  => true
        ]
    );
    $role = get_role('lab_manager');
    $role ->add_cap('lab_manager',true);
    $role = get_role('administrator');
    $role ->add_cap('lab_manager',true);
}

/**
 * Correct the um_user_profile_url_slug_name of the ultimate member pluggin
 */
function lab_usermeta_correct_um_fields() {
    global $wpdb;
    $sql = "UPDATE `".$wpdb->prefix."usermeta` SET `meta_value`=substr(meta_value,1,length(meta_value)-3) WHERE `meta_key` LIKE 'um_user_profile_url_slug_name' AND `meta_value` LIKE '%2A'";
    return $wpdb->get_results($sql);
}

/**
 * copy data from usermetadata dbem_phone to lab_user_phone fields in usermeta TABLE
 */
function lab_usermeta_copy_existing_phone() {
    global $wpdb;
    $sql = "select user_id, meta_value FROM `".$wpdb->prefix."usermeta` WHERE meta_key='dbem_phone'";
    $results = $wpdb->get_results($sql);
    foreach($results as $r) {
        $wpdb->update($wpdb->prefix."usermeta", array("meta_value"=>$r->meta_value), array("user_id"=>$r->user_id,"meta_key"=>"lab_user_phone"));
    }
    return true;
}

/**
 * return True if $string begins with $pattern
 * @param : $string 
 * @param : $pattern
 */
function beginWith($string, $pattern) {
    return substr($string, 0, strlen($pattern)) === $pattern;
}

/**************************************************************************************************************************************
 * PRESENCE
 *************************************************************************************************************************************/
function checkAddPresency($userId, $dateOpen, $dateEnd)
{

}

/**
 * Save or update presence, WARNING, if $external == null, no update $external field in DB
 *
 * @param [type] $id
 * @param [type] $userId
 * @param [type] $dateOpen
 * @param [type] $dateEnd
 * @param [type] $siteId
 * @param [type] $comment
 * @param integer $external
 * @return array("success"=>true, "data"=>null);
 */
function lab_admin_presence_save($id, $userId, $dateOpen, $dateEnd, $siteId, $comment, $external=0) {
    //return array("success"=>false, "data"=>"\$id : ".$id." \$dateOpen : " . $dateOpen . " \$dateEnd : " . $dateEnd);
    global $wpdb;
    if ($id == null) {
        $wpdb->insert($wpdb->prefix."lab_presence", array("user_id"=>$userId, "hour_start"=>$dateOpen, "hour_end"=>$dateEnd, "site"=>$siteId, "comment"=>$comment, "external"=>$external));
    }
    else {
        if ($external == null)
        {
            $wpdb->update($wpdb->prefix."lab_presence", array("hour_start"=>$dateOpen, "hour_end"=>$dateEnd, "site"=>$siteId, "comment"=>$comment), array("id"=>$id));
        }
        else{
            $wpdb->update($wpdb->prefix."lab_presence", array("hour_start"=>$dateOpen, "hour_end"=>$dateEnd, "site"=>$siteId, "comment"=>$comment, "external"=>$external), array("id"=>$id));
        }
        
    }
    return array("success"=>true, "data"=>$wpdb->insert_id);
}
function lab_admin_presence_delete($presenceId, $userId) {
    global $wpdb;
    if (current_user_can('administrator')) {
        workgroup_delete_by_presenceId($presenceId);
        return $wpdb->delete($wpdb->prefix."lab_presence", array("id"=>$presenceId));
    } 
    else {
        workgroup_delete_by_presenceId($presenceId);
        return $wpdb->delete($wpdb->prefix."lab_presence", array("id"=>$presenceId,"user_id"=>$userId));
    }
}

function save_new_workgroup($name, $day, $owner, $hourStart, $hourEnd, $max, $presencyId = -1)
{
    global $wpdb;
    $wpdb->insert($wpdb->prefix."lab_presence_workgroup",array("name"=>$name, "date"=>date('Y-m-d', $day), "owner_id"=>$owner, "max"=>$max, "hour_start"=>$hourStart, "hour_end"=>$hourEnd, "presency_id"=> $presencyId));
    $id = $wpdb->insert_id;
    save_workgroup_follow($id, $owner);
    return $id;
    //$wpdb->insert($wpdb->prefix."lab_presence_users_workgroup",array("workgroup_id"=>$wpdb->insert_id, "user_id"=>$owner));
}
function save_workgroup_follow($groupId, $userId, $external = 0)
{
    global $wpdb;
    //$wpdb->insert($wpdb->prefix."lab_presence_workgroup",array("name"=>$name, "date"=>date('Y-m-d', $day), "owner_id"=>$owner, "max"=>$max));
    $wpdb->insert($wpdb->prefix."lab_presence_users_workgroup",array("workgroup_id"=>$groupId, "external"=>$external, "user_id"=>$userId));
}

function workgroup_update_presencyId($workgroupId, $presencyId, $external = 0)
{
    global $wpdb;
    $wpdb->update($wpdb->prefix."lab_presence_workgroup", array("presency_id"=>$presencyId), array("id"=>$workgroupId));
}

function workgroup_delete_by_presenceId($presencyId)
{
    global $wpdb;
    $sql = "SELECT id FROM ".$wpdb->prefix."lab_presence_workgroup WHERE presency_id=".$presencyId;
    $rs = $wpdb->get_results($sql);
    foreach($rs as $r)
    {
        workgroup_delete($r->id);
    }
}

function workgroup_users_list($groupId)
{
    global $wpdb;
    $sql = "SELECT lpuw.user_id,um1.meta_value AS last_name,um2.meta_value AS first_name FROM `".$wpdb->prefix."lab_presence_users_workgroup` AS lpuw LEFT JOIN ".$wpdb->prefix."usermeta AS um1 ON um1.user_id=lpuw.user_id LEFT JOIN ".$wpdb->prefix."usermeta AS um2 ON um2.user_id=lpuw.user_id WHERE workgroup_id=".$groupId." AND external=0 AND um1.meta_key='last_name' AND um2.meta_key='first_name'";
    $rs = $wpdb->get_results($sql);
    $sql = "SELECT lpuw.user_id,g.first_name AS first_name,g.last_name AS last_name FROM `".$wpdb->prefix."lab_presence_users_workgroup` AS lpuw LEFT JOIN ".$wpdb->prefix."lab_guests AS g ON g.id=lpuw.user_id WHERE workgroup_id=".$groupId." AND external=1";
    $rs1 = $wpdb->get_results($sql);
    return array_merge($rs, $rs1);
}

function workgroup_users_count($groupId)
{
    global $wpdb;
    $sql = "SELECT count(*) AS c FROM `".$wpdb->prefix."lab_presence_users_workgroup` WHERE workgroup_id=".$groupId;
    $rs = $wpdb->get_results($sql);
    return $rs[0]->c;
}

function workgroup_delete($groupId)
{
    global $wpdb;
    $wpdb->delete($wpdb->prefix."lab_presence_workgroup", array("id"=>$groupId));
    $wpdb->delete($wpdb->prefix."lab_presence_users_workgroup", array("workgroup_id"=>$groupId));
}

function workgroup_get($groupId)
{
    global $wpdb;
    $sql = "SELECT * FROM ".$wpdb->prefix."lab_presence_workgroup WHERE id = ".$groupId;
    return $wpdb->get_results($sql)[0];
}

function load_workgroup_follow($groupId)
{
    global $wpdb;
    $sql = "SELECT * FROM ".$wpdb->prefix."lab_presence_users_workgroup WHERE workgroup_id = ".$groupId;
    return $wpdb->get_results($sql);
}

function get_workgroup_of_the_week($day)
{
    global $wpdb;
    $startDayOfTheWeek = getFirstDayOfTheWeek($day);
    $lastDayOfTheWeek  = strtotime("+5 days", $startDayOfTheWeek);
    $sql = "SELECT wg.*,p.site, param.value AS site_name FROM ".$wpdb->prefix."lab_presence_workgroup AS wg JOIN ".$wpdb->prefix."lab_presence AS p ON p.id = wg.presency_id JOIN ".$wpdb->prefix."lab_params AS param ON param.id = p.site WHERE date >= '".date('Y-m-d', $startDayOfTheWeek)."' AND date <= '".date('Y-m-d', $lastDayOfTheWeek)."'";
    //return $sql;
    return $wpdb->get_results($sql);
}

/**************************************************************************************************************************************
 * PRESENCE
 *************************************************************************************************************************************/


/**************************************************************************************************************************************
 * UTILS
 *************************************************************************************************************************************/

/**
 * Display numbers correctly
 *
 * @param [type] $currentNumber
 * @return void
 */
function correctNumber($currentNumber) { // currentNumber = esc_html($r->phone)
    $currentNumber = str_replace(" ", "", $currentNumber);
    $currentNumber = str_replace(".", "", $currentNumber);
    $currentNumber = chunk_split($currentNumber, 2, ' ');
    return $currentNumber;
}
function startsWith ($string, $startString) 
{ 
    $len = strlen($startString); 
    return (substr($string, 0, $len) === $startString); 
}

function getFirstDayOfTheWeek($dateObj) {
    $dayofweek = date('w', $dateObj);
    //echo $dayofweek."<br>";
    // if sunday
    if ($dayofweek < 1) {
        return strtotime('-6 days', $dateObj);
    }
    else {
        $aStr = '-'.($dayofweek-1).' days';
        return strtotime($aStr, $dateObj);
    }
}