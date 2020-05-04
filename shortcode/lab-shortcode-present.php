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
    /*
    var_dump($usersPresent);
    echo "<br>";
    //*/
    $sites = new StdClass();
    foreach($usersPresent as $user) {
        if (!isset($sites->{$user->site_id})) {
            $sites->{$user->site_id} = new StdClass();
            //echo "add site ".$user->site_id."<br>";
        }
        $days = $sites->{$user->site_id};

        $days_between = floor(abs(strtotime($user->hour_start) - $startDay) / 86400);
        //echo "\$days_between ".$days_between."<br>";
        //if (!array_key_exists($days_between, $sites[$user->site_id])) {
        if (!isset($days->{$days_between})) {
            $days->{$days_between} = array();
            //echo "add days_between ".$days_between."<br>";
        }
       
        //echo "sites[$user->site_id][$days_between]add user ".$user->last_name." -> ".sizeof($sites[$user->site_id.""][$days_between.""])."<br>";
        $days->{$days_between}[] = $user;
        //echo "sites[$user->site_id][$days_between.\"\"]add user ".$user->last_name." -> ".sizeof($days->{$days_between})."<br>";

        //$sites[$user->site_id][] = $user;
        //$user->hour_start
    }
    $listSite = lab_admin_list_site();
    /*
    echo "<br>";
    var_dump($sites);
    echo "<br>";


    //$sites = (array) $sites;
    foreach ($listSite as $site) 
    {
        echo $site->id." ".$site->value."<br>";
        if (isset($sites->{$site->id})) {
            $days = $sites->{$site->id};
            for ($i = 0 ; $i < 5 ; $i++) {
                if (isset($days->{$i})) {
                    echo "\$days[$i] : ".sizeof($days->{$i})."<br>";
                }else {
                    echo "\$days[$i] : NO ID<br>";
                }
            }
        }   
    }
    //*/

    $str .= "<a href=\"/presence/?date=".date("Y-m-d",$previousWeek)."\"><b>&lt;</b></a> Semaine du  : ".date("d-m-Y",$startDay)." au ".date("d-m-Y",$endDay)." <a href=\"/presence/?date=".date("Y-m-d",$nextWeek)."\"><b>&gt;</b></a><br>";
    $tootTip = "";
    $str .= "<table id=\"lab_presence_table\" class=\"table table-bordered table-striped\"><thead class=\"thead-dark\"><tr><th>&nbsp;</th><th colspan=\"2\">Lundi</th><th colspan=\"2\">Mardi</th><th colspan=\"2\">Mercredi</th><th colspan=\"2\">Jeudi</th><th colspan=\"2\">Vendredi</th></tr></thead><tbody>";
    
    $divAM = "";
    $divPM = "";
    foreach ($listSite as $site) 
    {
        $str .= "<tr><td>".$site->value."</td>";
        if (isset($sites->{$site->id})) {
            $days = $sites->{$site->id};
            for ($i = 0 ; $i < 5 ; $i++) {
                if (isset($days->{$i})) {
                    $userPresentAM = "";
                    $userPresentPM = "";
                    $nbAM = 0;
                    $nbPM = 0;
                    foreach($days->{$i} as $u) {
                        $endHour = strtotime($u->hour_end);
                        $startHour = strtotime($u->hour_start);
                        if (date('H', $endHour) < 14) {
                            $nbAM += 1;
                            if (!empty($userPresentAM)) {
                                $userPresentAM .= ", ";
                            }
                            $userPresentAM .= $u->first_name." ".$u->last_name;
                        } else if (date('H', $startHour) > 12) {
                            $nbPM += 1;
                            if (!empty($userPresentPM)) {
                                $userPresentPM .= ", ";
                            }
                            $userPresentPM .= $u->first_name." ".$u->last_name;
                        } else {
                            $nbAM += 1;
                            $nbPM += 1;
                            if (!empty($userPresentAM)) {
                                $userPresentAM .= ", ";
                            }
                            $userPresentAM .= $u->first_name." ".$u->last_name;
                            if (!empty($userPresentPM)) {
                                $userPresentPM .= ", ";
                            }
                            $userPresentPM .= $u->first_name." ".$u->last_name;
                        }

                    }
                    if ($nbAM>0) {
                        $str .= "<td id=\"lab_presence_td_".$site->id.$i."AM\" num=\"$nbAM\">&nbsp;</td>";
                        
                    }
                    else {
                        $str .= "<td>&nbsp;</td>";
                    }
                    if ($nbPM>0) {
                        $str .= "<td id=\"lab_presence_td_".$site->id.$i."PM\" num=\"$nbPM\">&nbsp;</td>";
                    }
                    else {
                        $str .= "<td>&nbsp;</td>";
                    }
                    if (!empty($userPresentAM)) {
                        $tootTip .= "<div id=\"lab_presence_div_".$site->id.$i."AM\" role=\"tooltip\"/>".$userPresentAM."</div>";
                    }
                    if (!empty($userPresentPM)) {
                        $tootTip .= "<div id=\"lab_presence_div_".$site->id.$i."PM\" role=\"tooltip\"/>".$userPresentPM."</div>";
                    }

                }else {
                    $str .= "<td colspan=\"2\">&nbsp;</td>";
                }
            }
        }
        else {
            $str .="<td colspan=\"2\">&nbsp;</td><td colspan=\"2\">&nbsp;</td><td colspan=\"2\">&nbsp;</td><td colspan=\"2\">&nbsp;</td><td colspan=\"2\">&nbsp;</td>";
        }
        $str .= "</tr>";
    }

    $str .= "</tbody></table>".$tootTip;

    return $str;
}
