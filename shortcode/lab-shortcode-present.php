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


    $users = array();
    $userId = 0;
    foreach($usersPresent as $user) {
        if ($userId == 0 || $userId != $user->user_id) {
            $userId = $user->user_id;
            $users[$user->first_name." ".$user->last_name][] = $user;
        }
        else if ($userId == $user->user_id) {
            $users[$user->first_name." ".$user->last_name][] = $user;
        }
    }
    //var_dump($users);
    $str .= "<a href=\"/presence/?date=".date("Y-m-d",$previousWeek)."\"><b>&lt;</b></a> Semaine du  : ".date("d-m-Y",$startDay)." au ".date("d-m-Y",$endDay)." <a href=\"/presence/?date=".date("Y-m-d",$nextWeek)."\"><b>&gt;</b></a>";

    $listSite = lab_admin_list_site();
    $colors[] = array();
    $str .= "<table><tr>";
    foreach($listSite as $site) {
        $str .= "<td style=\"background-color:#".$site->color.";\">".$site->value."</td>";
        $colors[$site->id] = $site->color;
    }
    $str .= "</tr></table><br>";

    $str .= "<table id=\"lab_presence_table1\" class=\"table table-bordered table-striped\"><thead class=\"thead-dark\"><tr><th>&nbsp;</th><th colspan=\"2\">Lundi</th><th colspan=\"2\">Mardi</th><th colspan=\"2\">Mercredi</th><th colspan=\"2\">Jeudi</th><th colspan=\"2\">Vendredi</th></tr></thead><tbody>";
    foreach($users as $k=>$v) {
        $str .="<tr><td>".$k."</td>";
        for ($i = 0 ; $i < 5 ; $i++) {
            $currentDay = strtotime('+'.$i.' days', $startDay);
            $notPresent = true;
            
            foreach($v as $hours) {
                $dateStart = strtotime($hours->hour_start);
                $dateEnd   = strtotime($hours->hour_end);
                $dayH = date('d', $dateStart);
                $day  = date('d', $currentDay);
                if ($day == $dayH) {
                    $notPresent = false;
                    if (date('H', $dateStart) < 13) {
                        if (date('H', $dateEnd) > 13) {
                            $str .= "<td style=\"background-color:#".$colors[$hours->site_id].";color:white;\"><b>".date('H:i', $dateStart)."</b></td><td style=\"background-color:#".$colors[$hours->site_id].";color:white;\"><b>".date('H:i', $dateEnd)."</b></td>";
                        }
                        else {
                            $str .= "<td style=\"background-color:#".$colors[$hours->site_id].";color:white;\"><b>".date('H:i', $dateStart)."-".date('H:i', $dateEnd)."</b></td><td>&nbsp;</td>";
                        }
                    }
                    else {
                        $str .= "<td>&nbsp;</td><td style=\"background-color:#".$colors[$hours->site_id].";color:white;\"><b>".date('H:i', $dateStart)."-".date('H:i', $dateEnd)."</b></td>";
                    }
                }
            }
            if ($notPresent) {
                $str .= "<td colspan=\"2\">&nbsp;</td>";
            }
        }
        $str .= "</tr>";
    }
    $str .= "</tbody></table>";
/*
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
  //*/
    /*
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
//*/
    return $str;
}

function lab_present_choice($param) {
    $param = shortcode_atts(array(
        ),
        $param, 
        "lab-present-choice"
    );
    $choiceStr = "<br/><hr><div>
        <h3>".esc_html__("I will be there", "lab")."</h3>
            <div class=\"input-group mb-3\">
            <input id='userId' name='userId' type='hidden' value='" . get_current_user_id() . "' />

            <label for='date-open'>".esc_html__("From", "lab")."</label>
            <input type='date' name='date-open' id='date-open' />
            <label for='hour-open'></label>
            <input type='time' name='hour-open' id='hour-open' />
            <label for='hour-close'>".esc_html__("to", "lab")."</label>
            <input type='time' name='hour-close' id='hour-close' />
            <label for='site-selected'>".esc_html__("on the site", "lab")."</label>
            " . lab_html_select_str("siteId", "siteName", "custom-select", lab_admin_list_site) . "
            <button class=\"btn btn-success\" id=\"lab_presence_button_save\">".esc_html__("Save", "lab")."</button>
            </div>
        </div>";

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

    $choiceStr .= "<div style='margin-top: 2em'><h3>Je souhaite modifier une de mes présences</h3>";

    //requete pour connaitre les présences de l'utilisateur
    global $wpdb;
    $sql = "SELECT pre.*, par.value FROM `".$wpdb->prefix."lab_presence` AS pre
            JOIN ".$wpdb->prefix."lab_params AS par
                ON pre.site = par.id
            WHERE `user_id` = " . get_current_user_id() . "
            ORDER BY `hour_start`";

    $results = $wpdb->get_results($sql);
    $choiceStr .= "<table id='userTable' class='table table-striped table-hover'>
                        <thead>
                            <tr>
                                <th scope='col'>#</th>
                                <th scope='col'>Le</th>
                                <th scope='col'>De</th>
                                <th scope='col'>Jusqu'à</th>
                                <th scope='col'>Sur</th>
                                <th scope='col'>Action</th>
                            </tr>
                        </thead>
                        <tbody>";

    $increment = 0;
    foreach ($results as $r) {
        $choiceStr .= "<tr><th scope='row'>" . ++$increment . "</th>
                        <td class='date-row edit'>"     . esc_html(date("Y-m-d", strtotime($r->hour_start))) ."</td>
                        <td class='hour-row open edit'>". esc_html(date("H:i",   strtotime($r->hour_start))) ."</td>
                        <td class='hour-row end edit'>" . esc_html(date("H:i", strtotime($r->hour_end)))  ."</td>
                        <td class='site-row edit'>"     . esc_html($r->value) ."</td>
                        <td><a href=\"#\" id=\"delete_presence_".$r->id."\"><span class='fas fa-trash'></span></a>
                            <span class='fas fa-pen icon-edit' style='cursor: pointer;' editId=" . $r->id . "></span>
                        </td></tr>";
    }
    $choiceStr .= "</tbody></table></div>";

    // requête pour mettre à jour la bdd
    if ($userId == NULL) {
        $userId = get_current_user_id();// id user
    }
    $id         = $_POST['id'];         // id présence
    $date       = $_POST['date'];       // date de présence
    $hour_start = $_POST['hour_start']; // heure d'ouverture
    $hour_end   = $_POST['hour_end'];   // heure de fermeture
    $site       = $_POST['site'];       // lieu
    
    $date_start = $date . ' ' . $hour_start;
    $date_end   = $date . ' ' . $hour_end;

    //wp_send_json_success( $_POST );
   // print_r($_POST);
    /*var_dump("identifiant : " . $id . " date debut : " . $date_start .
    " date fin : " . $date_end . " site : " . $site);

    var_dump($id);
    var_dump($date_start);
    var_dump($date_end);
    var_dump($site);*/

    global $wpdb;
    $wpdb->update(
        $wpdb->prefix.'lab_presence',
        array('hour_start'  => $date_start,
              'hour_end'    => $date_end,
              'site'        => $site),
        array('id'          => $id,
		      'user_id'     => $userId)
    );

    return $choiceStr;
}