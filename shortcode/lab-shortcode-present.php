<?php
/*
 * File Name: lab-shortcode-present.php
 * Description: shortcode to display if your present
 * Authors: Olivier CHABROL
 * Version: 1.0
*/

/*** 
 * Shortcode use : [lab-directory {as-left} {group}]
     as-left="yes" OR as-left="no"
     group="AA" or whatever group's acronym

     add this to CSS

    .labSelectedLetter {
        font-size: x-large;
    }
***/ 

function lab_present_select($param) {
    $param = shortcode_atts(array(
        ),
        $param, 
        "lab-present-select"
    );
    $date = null;
    if (isset( $_GET["date"] ) && !empty( $_GET["date"] ) ) {
        $date = $_GET["date"];
    }
    $str = "";
    $dateObj = strtotime("now");

    if ($date != null) {
        $dateObj = strtotime($date);
    }
    $dayofweek = date('w', $dateObj);
    //echo $dayofweek."<br>";
    // if sunday
    if ($dayofweek < 1) {
        $startDay = strtotime('-6 days', $dateObj);
    }
    else if ($dayofweek >= 1) {
        $aStr = '-'.($dayofweek-1).' days';
        $startDay = strtotime($aStr, $dateObj);
    }
    
    //$dt_startDate->setTime(0, 0, 0);
    $endDay = strtotime('+5 days', $startDay);


    $nextWeek = strtotime('+7 days', $startDay);
    $previousWeek = strtotime('-7 days', $startDay);

    $usersPresent = lab_admin_list_present_user($startDay, $endDay);

    $sites = array();
    foreach($usersPresent as $user) {
        $sites[$user->site_id][] = $user;
    }

    $str .= "<a href=\"/presence/?date=".date("Y-m-d",$nextWeek)."\"><b>&lt;</b></a> Semaine du  : ".date("d-m-Y",$startDay)." au ".date("d-m-Y",$endDay)." <a href=\"/presence/?date=".date("Y-m-d",$nextWeek)."\"><b>&gt;</b></a><br>";
    
    $str .= "<table><thead class=\"thead-dark\"><tr><th>&nbsp;</th><th>Lundi</th><th>Mardi</th><th>Mercredi</th><th>Jeudi</th><th>Vendredi</th></tr></thead><tbody>";
    $listSite = lab_admin_list_site();
    
    foreach ($listSite as $site) 
    {
        $str .= "<tr><td>".$site->value."</td>";
        foreach($users as $sites[$site->id]) {
            
        }
        $str .= "<td></td><td></td><td></td><td></td><td></td></tr>";
    }

    $str .= "</tbody></table>";

    return $str;
}
