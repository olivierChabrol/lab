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