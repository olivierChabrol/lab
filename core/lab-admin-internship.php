<?php

function list_internship_years() {
    global $wpdb;
    $sql = "SELECT DISTINCT YEAR(uh.end) AS y FROM `wp_lab_params` as p JOIN wp_lab_users_historic AS uh ON uh.function=p.id WHERE p.slug = 'STG'";
    $beginYears = $wpdb->get_results($sql);
    $years = array();
    $currentYear = date('Y');
    $isCurrentYearInside = FALSE;
    foreach($beginYears as $y) {
        $years[] = $y->y;
        if ($y->y == $currentYear) {
            $isCurrentYearInside = TRUE;
        }
    }
    if (!$isCurrentYearInside) {
        $years[] =  $currentYear;
    }
    sort($years);
    return $years;
}

function save_intern($data) {
    global $wpdb;
    if (isset($data["id"]) && !empty($data["id"])) {
        $wpdb->update($wpdb->prefix.'lab_users_historic', $data, array('id' => $data["id"]));
        return $data[id];
    }
    else {
        $user_id = addNewUser(generateLogin($data["lastname"], $data["firstname"]),null,$data["email"],$data["firstname"],$data["lastname"]);
        $histo = array();
        $histo["user_id"] = $user_id;
        $histo["ext"] = 0;
        $histo["begin"] = $data["begin"];
        $histo["end"] = $data["end"];
        $histo["mobility"] = 0;
        $histo["mobility_status"] = 0;
        $histo["host_id"] = $data["host_id"];
        $histo["training"] = $data["training"];
        $histo["establishment"] = $data["establishment"];
        $fctStgs = AdminParams::get_param_by_slug("STG");
        $fctStg = 0;
        if (count($fctStgs) > 1) {
            $fctStg = $fctStgs[0]->id;
        }
        else {
            $fctStg = $fctStgs->id;
        }
        $histo["function"] = $fctStg;
        $wpdb->insert($wpdb->prefix.'lab_users_historic', $histo);
        return $user_id;
    }
}

function generateLogin($lastname, $firstname) {
    $baseLogin = strtoupper($lastname).".".substr($firstname,-1,1);
    $login = $baseLogin."";
    global $wpdb;
    $sql = "SELECT user_login FROM ".$wpdb->prefix."users WHERE user_login = '".$login."'";
    $res = $wpdb->get_results($sql);
    $i=0;
    while(count($res) > 0) {
        $i += 1;
        $login = $baseLogin.".".$i;
        $sql = "SELECT user_login FROM ".$wpdb->prefix."users WHERE user_login = '".$login."'";
        $res = $wpdb->get_results($sql);
    }
    return $login;
}

function addNewUser($login, $password, $email, $firstname, $lastname) {
    $passwd = "definedIt";
    if (isset($passwd)) {
        $passwd = substr($password,0,7)=='{CRYPT}' ? 'hahaha' : substr($password,0,7);
    }
    $userData = array(
        'user_login'=>$login,
        'user_pass'=>$passwd,
        'user_email'=>$email,
        'user_registered'=>date("Y-m-d H:i:s",time()),
        'first_name'=>$firstname,
        'last_name'=>$lastname,
        'display_name'=>$firstname." ".$lastname,
        'role'=>'subscriber');
      $user_id = wp_insert_user($userData);
      lab_admin_add_new_user_metadata($user_id);
      return $user_id;
}

function list_intern($year)
{
    global $wpdb;
    $sql = "SELECT uh.* FROM `wp_lab_params` as p JOIN wp_lab_users_historic AS uh ON uh.function=p.id WHERE p.slug = 'STG' AND ((YEAR(uh.end) = ".$year." OR YEAR(uh.begin) = ".$year."))";
    $historics = $wpdb->get_results($sql);
    

    $data    = array();
    $data["results"] = $historics;
    $data["sql"]     = $sql;
    $userIds = array();
    foreach($historics as $historic) {
        if(!isset($userIds[$historic->user_id]) && $historic->user_id != 0) {
            $userIds[$historic->user_id] = lab_admin_usermeta_names($historic->user_id);
        }
        if(!isset($userIds[$historic->host_id]) && $historic->host_id != 0) {
            $userIds[$historic->host_id] = lab_admin_usermeta_names($historic->host_id);
        }
    }
    $data["users"] = $userIds;
    return $data;
}

?>