<?php
/*
 * File Name: lab-shortcode-present.php
 * Description: shortcode to display if you're present
 * Authors: Olivier CHABROL, Astrid BEYER
 * Version: 1.0
*/

/*** 
 * Shortcode use : [lab-directory {as-left} {group}]
     as-left="yes" OR as-left="no"
     group="AA" or whatever group's acronym

     add this to CSS

    .labopenLetter {
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
    $str .= "<style>
    .presence-tooltip {
      background-color: #333;
      color: white;
      padding: 5px 10px;
      border-radius: 4px;
      font-size: 13px;
    }
    .arrow,
    .arrow::before {
    position: absolute;
    width: 8px;
    height: 8px;
    z-index: -1;
    }

    .arrow::before {
    content: '';
    transform: rotate(45deg);
    background: #333;
    }
  </style>";
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
                        $str .= "<td id=\"lab_presence_td_".$site->id.$i."AM\" num=\"$nbAM\" title=\"$userPresentAM\">&nbsp;</td>";
                        
                    }
                    else {
                        $str .= "<td>&nbsp;</td>";
                    }
                    if ($nbPM>0) {
                        $str .= "<td id=\"lab_presence_td_".$site->id.$i."PM\" num=\"$nbPM\" title=\"$userPresentPM\">&nbsp;</td>";
                    }
                    else {
                        $str .= "<td>&nbsp;</td>";
                    }
                    if (!empty($userPresentAM)) {
                        $tootTip .= "<div id=\"lab_presence_div_".$site->id.$i."AM\" role=\"tooltip\" class=\"presence-tooltip\"/>".$userPresentAM."<div class=\"arrow\" data-popper-arrow></div></div>";
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

function lab_present_choice($param) {
    $param = shortcode_atts(array(
        ),
        $param, 
        "lab-present-choice"
    );
    $choiceStr = "<br/><hr><div class='container'><div>
        <h3>Je serai présent(e)...</h1>
        <form name='form' method='post' action=''>
            <input id='userId' name='userId' type='hidden' value='" . get_current_user_id() . "' />

            <label for='date-open'>Du</label>
            <input type='date' name='date-open' id='date-open' />
            <label for='hour-open'></label>
            <input type='time' name='hour-open' id='hour-open' />
            <label for='hour-close'>à</label>
            <input type='time' name='hour-close' id='hour-close' />
            <label for='site-selected'>sur le site</label>
            " . lab_html_select_str("siteId", "siteName", "class", lab_admin_list_site) . "<br/>
            <input type='submit' name='envoi' value='Envoyer'>
        </form></div>";

    if (isset($_POST['envoi'])) {
        $userId    = $_POST['userId'];
        $date      = $_POST['date-open'];
        $hourOpen  = $_POST['hour-open'];
        $hourClose = $_POST['hour-close'];
        $site      = $_POST['siteName'];
        printf("Bonjour $userId vous avez choisis de $date à $hourOpen jusqu'à ce même jour à $hourClose sur le site $site");

        //requete pour envoyer la présence sur la bd
        global $wpdb;
        $data = array('user_id' => $userId, 'hour_start' => $date . ' ' . $hourOpen,
                'hour_end' => $date . ' ' . $hourClose, 'site' => $site);
        $format = array('%d','%s','%s','%d');
        $wpdb->insert('wp_lab_presence', $data, $format);
    }

    $choiceStr .= "<div><h3>Je souhaite modifier une de mes présences</h3>";
    
    //requete pour connaitre les présences de l'utilisateur
    $sql = "SELECT * FROM `wp_lab_presence` WHERE user_id = '" . $userId . "';";
    $choiceStr .= "<table id='userTable' class='table'>
                        <thead>
                            <tr>
                                <th scope='col'>#</th>
                                <th scope='col'>Du</th>
                                <th scope='col'>Jusqu'au</th>
                                <th scope='col'>Sur</th>
                            </tr>
                        </thead> 
                        <tbody>
                            
                        </tbody>
                   </table>";

    $choiceStr .= "</div></div>";

    return $choiceStr;
}