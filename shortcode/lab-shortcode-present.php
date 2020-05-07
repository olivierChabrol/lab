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
    $startDay = getStartDate();
    
    //$dt_startDate->setTime(0, 0, 0);
    $endDay = strtotime('+5 days', $startDay);


    $nextWeek = strtotime('+7 days', $startDay);
    $previousWeek = strtotime('-7 days', $startDay);

    $usersPresent = lab_admin_list_present_user($startDay, $endDay);

    /** struct of users : [firstName lastName][day][] */
    $users = array();
    $userId = 0;
    foreach($usersPresent as $user) {
        // convert dateString to timeStamp obj
        $user->hour_start = strtotime($user->hour_start);
        $user->hour_end   = strtotime($user->hour_end);

        if ($userId == 0 || $userId != $user->user_id) {
            $userId = $user->user_id;
            $users[$user->first_name." ".$user->last_name][date('d', $user->hour_start)][] = $user;
        }
        else if ($userId == $user->user_id) {
            $users[$user->first_name." ".$user->last_name][date('d', $user->hour_start)][] = $user;
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

    // .iconset_16px { height: 17px; width: 17px; background-color: #87ceeb; cursor: pointer; margin: 3px;}
    $str .= "\n<style>\n";
    $str .= ".wrapper { position: relative; }
    .actions { display:none;position: absolute; top: -10px; right: -10px; }
    .iconset_16px { height: 17px; width: 17px; cursor: pointer; margin: 3px;}
    .floatLeft  { float:left!important; };
    .gal_name { color:white; font-weight:bold;};";
    $str .= "</style>\n";

    $sum = array();

    foreach($listSite as $site) {
        $sum[$site->id] = array();
        for ($i = 0 ; $i < 10 ; $i++) {
            $sum[$site->id][$i] = 0;
        }
    }

    $dayInt = date('j', $startDay);

    $str .= "<table id=\"lab_presence_table1\" class=\"table table-bordered table-striped\">
                <thead class=\"thead-dark\">
                    <tr>
                        <th style=\"width: 16.66%\">&nbsp;</th>
                        <th colspan=\"2\" style=\"width: 16.66%\">" . esc_html__("Lundi", "lab")    . "Lundi "    . $dayInt   . "</th>
                        <th colspan=\"2\" style=\"width: 16.66%\">" . esc_html__("Mardi", "lab")    . "Mardi "    . ++$dayInt . "</th>
                        <th colspan=\"2\" style=\"width: 16.66%\">" . esc_html__("Mercredi", "lab") . "Mercredi " . ++$dayInt . "</th>
                        <th colspan=\"2\" style=\"width: 16.66%\">" . esc_html__("Jeudi", "lab")    . "Jeudi "    . ++$dayInt . "</th>
                        <th colspan=\"2\" style=\"width: 16.66%\">" . esc_html__("Vendredi", "lab") . "Vendredi " . ++$dayInt . "</th>
                    </tr>
                </thead>
                <tbody>";
    foreach($users as $k=>$v) {
        $str .="<tr>\n<td>".$k."</td>\n";
        
        for ($i = 0 ; $i < 5 ; $i++) {
            $currentDay   = strtotime('+'.$i.' days', $startDay);
            $currentDayDT = date('d', $currentDay);
            if($v[$currentDayDT]) {

                $nb = 0;
                // hours is ordoned
                foreach($v[$currentDayDT] as $hours) {
                    if (date('H', $hours->hour_start) < 13) {
                        // presence toute la journée
                        if (date('H', $hours->hour_end) >= 13) {
                            $sum[$hours->site_id][$i*2] = $sum[$i*2] + 1;
                            $sum[$hours->site_id][$i*2+1] = $sum[$i*2+1] + 1;
                            $str .= td($hours->hour_start,$hours->hour_end,false,"style=\"background-color:#".$colors[$hours->site_id].";color:white;\"", $hours->user_id, $hours->id, true);
                            //$str .= td($hours->hour_end,null,false,"style=\"background-color:#".$colors[$hours->site_id].";color:white;\"", $hours->user_id, $hours->id);
                            $nb = 2; 
                        }
                        // presence le matin
                        else {
                            $str .= td($hours->hour_start, $hours->hour_end,false,"style=\"background-color:#".$colors[$hours->site_id].";color:white;\"", $hours->user_id, $hours->id,false);
                            $sum[$hours->site_id][$i*2] += 1;
                            //$str .= td(null, null, true);
                        }
                    }
                    // presence l'aprem
                    else {
                        if ($nb == 0) {
                            $str .= td(null, null, true);
                            $nb++;
                        }
                        $str .= td($hours->hour_start, $hours->hour_end, null, "style=\"background-color:#".$colors[$hours->site_id].";color:white;\"", $hours->user_id, $hours->id,false);
                        $sum[$hours->site_id][$i*2+1] += 1;
                    }
                    $nb++;
                }
                if ($nb == 1) {
                    $str .= td(null, null, true);
                }
            }
            else {
                $str .= "<td colspan=\"2\">&nbsp;</td>\n";
            }
        }
        $str .= "</tr>\n";
    }
    $str .= "<tr><td colspan=\"11\">Total</td></tr><tr>";

    foreach($listSite as $site) {
        $str .= "<tr><td style=\"background-color:#".$colors[$site->id].";color:white;\">".$site->value."</td>";

        for ($i = 0 ; $i < 10 ; $i++) {
            $str .= "<td style=\"background-color:#".$colors[$site->id].";color:white;\"><b>";
            $str .= $sum[$site->id][$i];
            $str .= "</b></td>";
        }   
        $str .= "<tr>";
    }
    $str .= "</tbody></table>";
    return $str;
}

function lab_present_choice($param) {
    if (!is_user_logged_in())
    {
        return "";
    }
    $param = shortcode_atts(array(
        ),
        $param, 
        "lab-present-choice"
    );
    $startDay = getStartDate();
    
    $choiceStr = "<br/><hr><div>
        <h3>".esc_html__("Je serai présent·e", "lab")."</h3>
            <div class=\"input-group mb-3\">
            <input id='userId' name='userId' type='hidden' value='" . get_current_user_id() . "' />

            <label for='date-open'>".esc_html__("Le", "lab")."</label>
            <input type='date' name='date-open' id='date-open' />
            <label for='hour-open'></label>
            <input type='time' name='hour-open' id='hour-open' />
            <label for='hour-close'>".esc_html__("Jusqu'à", "lab")."</label>
            <input type='time' name='hour-close' id='hour-close' />
            <label for='site-selected'>".esc_html__("sur le site", "lab")."</label>
            " . lab_html_select_str("siteId", "siteName", "custom-select", lab_admin_list_site) . "</div>
            <div class=\"input-group mb-3\">
            <div class=\"form-group\">
                <label for='comment'>".esc_html__("Commentaire", "lab")."</label>
                <textarea id=\"comment\" rows=\"4\" cols=\"50\" class=\"form-control rounded-0\"></textarea>
            </div>
            </div>
            <button class=\"btn btn-success\" id=\"lab_presence_button_save\">".esc_html__("Sauvegarder", "lab")."</button>
        </div>";

    if (isset($_POST['envoi'])) {
        $userId    = $_POST['userId'];
        $date      = $_POST['date-open'];
        $hourOpen  = $_POST['hour-open'];
        $hourClose = $_POST['hour-close'];
        $site      = $_POST['siteName'];

        //requete pour envoyer la présence sur la bd
        global $wpdb;
        $data = array('user_id' => $userId, 'hour_start' => $date . ' ' . $hourOpen,
                'hour_end' => $date . ' ' . $hourClose, 'site' => $site);
        $format = array('%d','%s','%s','%d');
        $wpdb->insert('wp_lab_presence', $data, $format);
    }

    $choiceStr .= "<div style='margin-top: 2em'><h3>".esc_html__("Je souhaite modifier une de mes présences", "lab")."</h3>";

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
                                <th scope='col'>".esc_html__("Le", "lab")."</th>
                                <th scope='col'>".esc_html__("De","lab")."</th>
                                <th scope='col'>".esc_html__("Jusqu'à","lab")."</th>
                                <th scope='col'>".esc_html__("Sur","lab")."</th>
                                <th scope='col'>".esc_html__("Action","lab")."</th>
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
                            <span class='fas fa-pen icon-edit' style='cursor: pointer;' editId=" . $r->id . " userId=" . $r->user_id . "></span>
                        </td></tr>";
    }
    $choiceStr .= "</tbody></table></div>";

    return $choiceStr;
}

/**
 * Return date of the first day of the week, when a date is put in the query
 *
 * @return void
 */
function getStartDate()
{
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
        return strtotime('-6 days', $dateObj);
    }
    else {
        $aStr = '-'.($dayofweek-1).' days';
        return strtotime($aStr, $dateObj);
    }
}

function td($dateStart = null, $dateEnd = null, $empty = false, $site = null, $userId = null, $presenceId=null, $allDay = false, $text= null) {
    if ($empty) {
        $str .= "<td>&nbsp;</td>";
    } else {
        $canDelete = '';
        
        if ($userId != null && $presenceId != null) {
            
            $admin = current_user_can('administrator');
            $currentUserId = get_current_user_id();
            $colSpan = "";

            if ($admin || $userId == $currentUserId) {
                $canDelete = ' class="canDelete" userId="'.$userId.'" presenceId="'.$presenceId.'" ';
            }
            if ($allDay) {
                $colSpan = " colspan=\"2\" ";
            }
        }
        $id = $userId."_".$presenceId;
        $tdId = " id=\"td_".$id."\" ";
        $actionId = " id=\"action_".$id."\" ";
        $deleteId = " id=\"delete_".$id."\" ";
        $editId   = " id=\"edit_".$id."\" ";

        $str .= '<td '.$canDelete.' '.($site!=null?$site:'').$colSpan.$tdId.'><div class="wrapper"><div class="actions"'.$actionId.'><div title="Update" '.$editId.' class="floatLeft iconset_16px"><i class="fas fa-pen fa-xs"></i></div><div title="delete" '.$deleteId.' class="floatLeft iconset_16px"><i class="fas fa-trash fa-xs"></i></div></div><div class="gal_name">'.date('H:i', $dateStart);
        if ($dateEnd != null) {
            $str .= " - ".date('H:i', $dateEnd);
        }
        if ($text != null) {
            if (is_array($text)) {
                for ($i = 0 ; $i < sizeof($text) ; $i++)
                {
                    $str .= $text[$i];
                    if ($i + 1 < sizeof($text) ) {
                        $str . ", ";
                    }

                }
            }
        }
        $str .= '</div><div></td>';
    }
    $str .= "\n";
    return $str;
}