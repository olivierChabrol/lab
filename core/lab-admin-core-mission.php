<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


function lab_mission_status_to_value($missionStatus) {
    $status = array();
    $status["c"] = 265;
    $status["ca"] = 268;
    $status["wgm"] = 264;
    $status["n"] = 261;
    $status["vgl"] = 266;
    $status["rgl"] = 267;
    return $status[$missionStatus];
}
    
function lab_mission_delete($missionId) {
    global $wpdb;
    $wpdb->delete($wpdb->prefix."lab_mission_comments", array('invite_id' => $missionId));
    $wpdb->delete($wpdb->prefix.'lab_mission', array('id' => $missionId));
    $wpdb->delete($wpdb->prefix.'lab_mission_route', array('mission_id' => $missionId));
    $wpdb->delete($wpdb->prefix.'lab_mission_comment_notifs', array('invite_id' => $missionId));
    return true;
}

function lab_mission_set_status($missionId, $status) {
    $statusCode = AdminParams::get_param_by_slug($status)->id;
    lab_invitations_editInvitation($missionId, array("status"=>$statusCode));
}

function lab_mission_set_budget_manager($missionId, $managerId) {
    lab_invitations_editInvitation($missionId, array("manager_id"=>$managerId));
}

function lab_mission_get_budget_manager($missionId) {
    global $wpdb;
    $results = $wpdb->get_results("SELECT manager_id FROM `".$wpdb->prefix."lab_mission` WHERE id=".$missionId);
    if (count($results) == 1) {
        return $results[0]->manager_id;
    }
    return -1;
}

function lab_mission_get_id_by_token($token) {
    global $wpdb;
    $sql = "SELECT id FROM `".$wpdb->prefix."lab_mission` WHERE token = '".$token."'";
    $results = $wpdb->get_results($sql);
    if (count($results) > 0) {
        return $results[0]->id;
    }
    return null;
}

function lab_mission_take_in_charge($missionId)
{
    $currentUserId = get_current_user_id();
    //$managerId = lab_mission_get_budget_manager($missionId);
    $isManager = lab_admin_group_is_manager($currentUserId) > 0;
    
    if ($isManager) 
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
        ), $currentUserId);
        lab_mission_set_status($missionId, AdminParams::MISSION_STATUS_WAITING_GROUP_MANAGER);
    }
}

function lab_mission_get_token_from_id($missionId) {
    global $wpdb;
    $sql = "SELECT token FROM `".$wpdb->prefix."lab_mission` WHERE id = ".$missionId;
    return $wpdb->get_results($sql);
}

function lab_mission_load($missionToken, $filters = null, $groupIds = null) {
    global $wpdb;
    $data = array();
    $data["filters"] = array();

    $budgetManagerGroupIds = lab_admin_group_get_manager_groups(get_current_user_id());
    $leaderManagerGroupIds = lab_admin_group_get_manager_groups(null, 2);
    $isBudgetManager  = count($budgetManagerGroupIds) > 0;
    $isLeaderOfAGroup = count($leaderManagerGroupIds) > 0;
    $isAdmin = current_user_can( 'manage_options' );

    // by default load current year
    if ($filters == null)
    {
        $filters["year"] = date("Y");
    }
    
    $select = "SELECT m.*,param.value AS site ";
    $from = " FROM `".$wpdb->prefix."lab_mission` as m"; 
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
                    if($value != "*") {
                        if ($nbFilter > 0) {
                            $where .= " AND ";
                        }
                        $where .= "YEAR(m.`creation_time`)=".$value."";
                    }
                    else {
                        $where = substr($where, 0, -4);
                    }
                    $data["filters"]["year"] = $value;
                    
                }
                if ($key == "site") {
                    if ($nbFilter > 0) {
                        $where .= " AND ";
                    }
                    $where .= "param.id =".$value."";
                    $data["filters"]["site"] = $value;
                }
                if ($key == "budget_manager") {
                    if ($nbFilter > 0) {
                        $where .= " AND ";
                    }
                    $select .= ", m.manager_id";
                    $where .= "m.manager_id=".$value."";
                    $data["filters"]["budget_manager"] = $value;
                }
                if ($key == "status") {
                    if ($nbFilter > 0) {
                        $where .= " AND ";
                    }
                    $where .= "m.status=".lab_mission_status_to_value($value)."";
                    $data["filters"]["status"] = $value;
                }
                $nbFilter += 1;
            }
        }
    }
    $order = " ORDER BY m.`creation_time` DESC";
    if (!$isAdmin) {
        if (!$isBudgetManager) {
            if (!$isLeaderOfAGroup) {
                $where .= " AND m.host_id=".get_current_user_id();
            }
        }
    }
    $sql = $select. $from .$join. $where. $order;
    $missions = $wpdb->get_results($sql);

    // load user info
    $userIds = array();
    $params = array();
    $groups = array();
    $paramsToGet = ["mission_objective", "status"];
    $notifs = array();
    $current_user_id = get_current_user_id();
    foreach($missions as $mission) {
        $notifs[$mission->id] = lab_admin_mission_getNotifs($current_user_id, $mission->id);
        if(!isset($userIds[$mission->host_id]) && $mission->host_id != 0) {
            $userIds[$mission->host_id] = lab_admin_usermeta_names($mission->host_id);
            
        }
        if ($mission->manager_id != null) {
            $userIds[$mission->manager_id] = lab_admin_usermeta_names($mission->manager_id);
        }
        if (isset($userIds[$mission->host_id]->group)) {
            $mission->group = $userIds[$mission->host_id]->group;
        }
        else {
            $mission->group = "";
        }
        //$mission->manager_id = $userIds[$mission->host_id]->manager_id;
        $mission->routes=lab_mission_load_travels($mission->id);
        $groups[$mission->host_group_id] = lab_admin_get_group_name($mission->host_group_id);
        # get all params associated to the mission see @$paramsToGet
        foreach($paramsToGet as $ptg) {
            if (!isset($params[$mission->$ptg])) {
                $params[$mission->$ptg] = AdminParams::get_full_param($mission->$ptg);
            }

        }
    }


    //if ($budgetId== null) {
    if (count($missions) > 0) {
        $data["results"] = $missions;
    }
    else {
        $data["results"] = $missions[0];
    }
    $data["users"] = $userIds;
    $data["groups"] = $groups;
    $data["params"] = $params;
    $data["notifs"] = $notifs;

    $sqlYear = "SELECT DISTINCT YEAR(`creation_time`) AS year FROM `".$wpdb->prefix."lab_mission` ORDER BY `year` DESC";
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

function lab_mission_generate_excel($missionToken = null, $filters = null, $groupIds = null) {
    $data = lab_mission_load($missionToken, $filters, $groupIds);
    
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet()->setTitle('RecapMissions')->setAutoFilter('A1:N1');

    $styleBold = [
        'font' => [
         'bold' => true,
    ]
];
    $sheet->getStyle('A1:N1' )->applyFromArray($styleBold);

    $sheet->setCellValue('A1', esc_html__('Id Mission', 'lab'));
    $sheet->setCellValue('B1', esc_html__('Etat', 'lab'));
    $sheet->setCellValue('C1', esc_html__('Date of demand', 'lab'));
    $sheet->setCellValue('D1', esc_html__('Departure date', 'lab'));
    $sheet->setCellValue('E1', esc_html__('User', 'lab'));
    $sheet->setCellValue('F1', esc_html__('Site', 'lab'));
    $sheet->setCellValue('G1', esc_html__('Group', 'lab'));
    $sheet->setCellValue('H1', esc_html__('Budjet Manager', 'lab'));
    $sheet->setCellValue('I1', esc_html__('Mission Type', 'lab'));
    $sheet->setCellValue('J1', __('Hostel Night', 'lab'));
    $sheet->setCellValue('K1', esc_html__('Estimation cost Travel', 'lab'));
    $sheet->setCellValue('L1', __('Estimation cost Hostel', 'lab'));
    $sheet->setCellValue('M1', esc_html__('Total estimation', 'lab'));
    $sheet->setCellValue('N1', esc_html__('Real cost', 'lab'));

    define('LAB_DIR_PATH', dirname(__FILE__));
    $line = 1;

    $data = lab_mission_load(null, null, null);


    foreach ($data["results"] as $mission){
        $line++;
        $user = $data["users"][$mission->host_id];
        $gestion = $data["users"][$mission->manager_id];
        $group = $data["groups"][$mission->host_group_id];
        $sheet->setCellValue('A'.$line, $mission->id);
        $sheet->setCellValue('B'.$line, esc_html__(AdminParams::get_param($mission->status), 'lab'));

        if (AdminParams::get_param($mission->status) == "Validate"){
            $sheet->getStyle($line)
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('98FB98');
        }
        if (AdminParams::get_param($mission->status) == "Refused") {
            $sheet->getStyle($line)
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('FA8072');
        }

        $sheet->setCellValue('C'.$line, $mission->creation_time);

        foreach ($mission->routes as $route){
            $sheet->setCellValue('D'.$line, $route->travel_date);
            break;
        }
        $sheet->setCellValue('E'.$line, $user->first_name." ".$user->last_name);
        $sheet->setCellValue('F'.$line, $mission->site);
        $sheet->setCellValue('G'.$line, $group->group_name);
        $sheet->setCellValue('H'.$line, $gestion->first_name." ".$gestion->last_name);
        $sheet->setCellValue('I'.$line, AdminParams::get_param($mission->mission_objective));
        $sheet->setCellValue('J'.$line, $mission->hostel_night);

        $currency = numfmt_create('fr_FR', NumberFormatter::CURRENCY);

        if ($mission->estimated_cost == 0 || $mission->estimated_cost == null){
            $sumcosttravel = 0;
            $mission->estimated_cost = $mission->hostel_cost;
        } else {
            $sumcosttravel = $mission->estimated_cost - $mission->hostel_cost;
        }

        $sheet->setCellValue('K'.$line, numfmt_format_currency($currency, $sumcosttravel, "EUR"));

        $sheet->setCellValue('L'.$line, numfmt_format_currency($currency, $mission->hostel_cost, "EUR"));
        $sheet->setCellValue('M'.$line, numfmt_format_currency($currency, $mission->estimated_cost, "EUR"));

        if ($mission->real_cost == null){
            $sheet->setCellValue('N'.$line, numfmt_format_currency($currency, 0, "EUR"));
        } else {
            $sheet->setCellValue('N'.$line, numfmt_format_currency($currency, $mission->real_cost, "EUR"));
        }
    

        $estimationcostxls += $mission->estimated_cost;
        if ($mission->real_cost == null){
            $realcostxls = 0;
        } else {
            $realcostxls = $mission->real_cost;
        }

        $deltaxls = $realcostxls - $estimationcostxls;
    }

    $line += 2;

    $sheet->getStyle("A$line:D$line")->applyFromArray($styleBold);
    
    $sheet->setCellValue('A'.$line, esc_html__('Total', 'lab'));
    $sheet->setCellValue('B'.$line, esc_html__('Estimation cost', 'lab'));
    $sheet->setCellValue('C'.$line, esc_html__('Real cost', 'lab'));
    $sheet->setCellValue('D'.$line, esc_html__('Delta', 'lab'));

    $line++;

    if ($deltaxls >= 0){
        $sheet->getStyle("A$line:D$line")
                ->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB('98FB98');
    }
    else{
        $sheet->getStyle("A$line:D$line")
                ->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB('FA8072');
    }

    $sheet->setCellValue('B'.$line, numfmt_format_currency($currency, $estimationcostxls, "EUR"));
    $sheet->setCellValue('C'.$line, numfmt_format_currency($currency, $realcostxls, "EUR"));
    $sheet->setCellValue('D'.$line, numfmt_format_currency($currency, $deltaxls, "EUR"));

    $sheetMission = $spreadsheet->createSheet()->setTitle('Missions');
    $sheetMission->getStyle('A1:S1' )->applyFromArray($styleBold);
    $sheetMission->setCellValue('A1', esc_html__('Id Mission', 'lab'));
    $sheetMission->setCellValue('B1', esc_html__('Etat', 'lab'));
    $sheetMission->setCellValue('C1', esc_html__('User', 'lab'));
    $sheetMission->setCellValue('D1', esc_html__('Departure date', 'lab'));
    $sheetMission->setCellValue('E1', esc_html__('Travel from', 'lab'));
    $sheetMission->setCellValue('F1', __('Travel to', 'lab'));
    $sheetMission->setCellValue('G1', esc_html__('Means of locomotion', 'lab'));
    $sheetMission->setCellValue('H1', esc_html__('Number of people', 'lab'));    
    $sheetMission->setCellValue('I1', esc_html__('Round trip', 'lab'));
    $sheetMission->setCellValue('J1', esc_html__('Return date', 'lab'));
    $sheetMission->setCellValue('K1', esc_html__('Path reference', 'lab'));
    $sheetMission->setCellValue('L1', esc_html__('Carbon footprint', 'lab'));
    $sheetMission->setCellValue('M1', esc_html__('Loyalty card number', 'lab'));
    $sheetMission->setCellValue('N1', __('Loyalty card number expiry date', 'lab'));
    $sheetMission->setCellValue('O1', __('Hostel Night', 'lab'));
    $sheetMission->setCellValue('P1', esc_html__('Estimation cost Travel', 'lab'));
    $sheetMission->setCellValue('Q1', __('Estimation cost Hostel', 'lab'));
    $sheetMission->setCellValue('R1', esc_html__('Total estimation', 'lab'));
    $sheetMission->setCellValue('S1', esc_html__('Real cost', 'lab'));

    $line = 1;
    $sumcosttravel = 0;
    
    foreach ($data["results"] as $mission){
        $line++;
        $user = $data["users"][$mission->host_id];
        $sheetMission->setCellValue('A'.$line, $mission->id);
        $sheetMission->setCellValue('B'.$line, esc_html__(AdminParams::get_param($mission->status), 'lab'));

        if (AdminParams::get_param($mission->status) == "Validate"){
            $sheetMission->getStyle("A$line:S$line")
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('98FB98');
        }
        if (AdminParams::get_param($mission->status) == "Refused") {
            $sheetMission->getStyle("A$line:S$line")
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('FA8072');
        }


        $sheetMission->setCellValue('C'.$line, $user->first_name." ".$user->last_name);
        $sheetMission->setCellValue('O'.$line, $mission->hostel_night);

        

        $currency = numfmt_create ('fr_FR', NumberFormatter::CURRENCY);

        $sumcosttravel = $mission->estimated_cost - $mission->hostel_cost;

        $sheetMission->setCellValue('P'.$line, numfmt_format_currency($currency, $sumcosttravel, "EUR"));
        $sheetMission->setCellValue('Q'.$line, numfmt_format_currency($currency, $mission->hostel_cost, "EUR"));
        $sheetMission->setCellValue('R'.$line, numfmt_format_currency($currency, $mission->estimated_cost, "EUR"));

        if ($mission->real_cost == null){
            $sheetMission->setCellValue('S'.$line, numfmt_format_currency($currency, 0, "EUR"));
        } else {
            $sheetMission->setCellValue('S'.$line, numfmt_format_currency($currency, $mission->real_cost, "EUR"));
        }

        $line++;

        foreach ($mission->routes as $route){
            
            $sheetMission->setCellValue('D'.$line, $route->travel_date);
            $sheetMission->setCellValue('E'.$line, $route->travel_from);
            $sheetMission->setCellValue('F'.$line, $route->travel_to);
            $sheetMission->setCellValue('G'.$line, AdminParams::get_param($route->means_of_locomotion));
            $sheetMission->setCellValue('H'.$line, $route->nb_person);
            $sheetMission->setCellValue('M'.$line, $route->loyalty_card_number);
            $sheetMission->setCellValue('N'.$line, $route->loyalty_card_expiry_date);

            if ($route->round_trip == 0){
                $sheetMission->setCellValue('I'.$line, esc_html__('No', 'lab'));
            }
            else
            {
                $sheetMission->setCellValue('I'.$line, esc_html__('Yes', 'lab'));
                $sheetMission->setCellValue('J'.$line, $route->travel_datereturn);
            }
            
            $sheetMission->setCellValue('K'.$line, $route->reference);

            if ($route->carbon_footprint == null){
                $sheetMission->setCellValue('L'.$line, 0);
            }
            else
            {
                $sheetMission->setCellValue('L'.$line, $route->carbon_footprint);
            }
            $sheetMission->setCellValue('P'.$line, numfmt_format_currency($currency, $route->estimated_cost, "EUR"));
            $sheetMission->setCellValue('S'.$line, numfmt_format_currency($currency, $route->real_cost, "EUR"));


            $line++;
        }
    }



    $writer = new Xlsx($spreadsheet);
    $filename = "missions_".uniqid().".xls";
    $name = LAB_DIR_PATH."files/excel/".$filename;
    try {
        $writer->save($name);
    }
    catch( Exception $e ) {
        return $e->getMessage();
    }
    
    return get_site_url()."/wp-content/plugins/lab/files/excel/".$filename;
}