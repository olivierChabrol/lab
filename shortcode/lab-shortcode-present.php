<?php
/*
 * File Name: lab-shortcode-present.php
 * Description: shortcode to display if you're present
 * Authors: Olivier CHABROL, Astrid BEYER
 * Version: 1.0
*/

/*** 
  * Shortcode use : [lab-present {allow-external}]
     allow-external=true OR allow-external=false

  * Shortcode use : [lab-present-choice]
     no parameters
***/ 

function lab_present_select($param) {
    $param = shortcode_atts(array(
        'allow-external' => get_option('lab-incoming-event')
        ),
        $param, 
        "lab-present-select"
    );
    $externalUserAllowed = false;
    if (isset($param['allow-external']) && $param['allow-external'] == "true") {
        $externalUserAllowed = true;
    }
    $str = "";
    //$str.= "\$param['allow-external'] : ".$param['allow-external']." \ $externalUserAllowed : ". $externalUserAllowed."<br>";
    $startDay = getStartDate();
    
    //$dt_startDate->setTime(0, 0, 0);
    $endDay = strtotime('+5 days', $startDay);


    $nextWeek = strtotime('+7 days', $startDay);
    $previousWeek = strtotime('-7 days', $startDay);

    $usersPresent = lab_admin_list_present_user($startDay, $endDay);

    //var_dump(strtotime($hs));

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

    global $wp;
    // get current url with query string.
    $current_url =  home_url( $wp->request ); 

    $str .= "<a href=\"".$current_url."/?date=".date("Y-m-d",$previousWeek)."\"><i class='fas fa-chevron-circle-left'></i></a> Semaine du  : ".date("d-m-Y",$startDay)." au ".date("d-m-Y",$endDay)." <a href=\"".$current_url."/?date=".date("Y-m-d",$nextWeek)."\"><i class='fas fa-chevron-circle-right'></i></a>";
    $str .= "&nbsp;<a href=\"/wp-content/plugins/lab/lab_export.php?do=presentOfTheWeek&filename=present.xlsx&param=".date("d-m-Y",$startDay)."\" targer=\"_export\">export</a>";
    if (!is_user_logged_in() && $externalUserAllowed) {
        $str .=  "<div id=\"a_external_presency\" class=\"float-right\"><a href=\"#\" title=\"Add your presency\">" . esc_html("Ajouter une présence en tant qu'invité", "lab") . "<i class=\"fas fa-plus-circle fa-3x text-success\"></i></a></div>";
    }
    $listSite = lab_admin_list_site();
    $colors[] = array();
    $str .= "<table><tr>";
    foreach($listSite as $site) {
        $str .= "<td style=\"background-color:#".$site->color."; color: white; font-weight:bold;\">&nbsp".$site->value."&nbsp</td>";
        $colors[$site->id] = $site->color;
    }
    $str .= "</tr></table><br>";

    // .iconset_16px { height: 17px; width: 17px; background-color: #87ceeb; cursor: pointer; margin: 3px;}
    $str .= "\n<style>\n";
    $str .= ".wrapper { position: relative; }
    .planningHeader {width: 16.66%; text-align: center;}
    .actions { display:none;position: absolute; top: -10px; right: -10px; }
    .iconset_16px { height: 17px; width: 17px; cursor: pointer; margin: 3px;}
    .floatLeft  { float:left!important; };
	.striped {
        background: repeating-linear-gradient(
          45deg,
          #FFFFFF,
          #FFFFFF 10px,
          #DDDDDD 10px,
          #DDDDDD 20px
      )};
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
    $dayInMonth = date('t', $startDay);

    $datesOfTheWeek = array();
    $datesOfTheWeek[] = $startDay;
    for ($i = 1 ; $i < 5 ; $i++)
    {
        $datesOfTheWeek[] = strtotime("+1 days", $datesOfTheWeek[$i -1]);
    }
    

    $str .= "<table id=\"lab_presence_table1\" class=\"table table-bordered table-striped\">
                <thead class=\"thead-dark\">
                    <tr>
                        <th style=\"width: 16.66%\">&nbsp;</th>
                        <th colspan=\"2\" class=\"planningHeader\">" . esc_html__("Lundi", "lab")    . " " . date("d", $datesOfTheWeek[0]) . "</th>
                        <th colspan=\"2\" class=\"planningHeader\">" . esc_html__("Mardi", "lab")    . " " . date("d", $datesOfTheWeek[1]) . "</th>
                        <th colspan=\"2\" class=\"planningHeader\">" . esc_html__("Mercredi", "lab") . " " . date("d", $datesOfTheWeek[2]) . "</th>
                        <th colspan=\"2\" class=\"planningHeader\">" . esc_html__("Jeudi", "lab")    . " " . date("d", $datesOfTheWeek[3]) . "</th>
                        <th colspan=\"2\" class=\"planningHeader\">" . esc_html__("Vendredi", "lab") . " " . date("d", $datesOfTheWeek[4]) . "</th>
                    </tr>
                </thead>
                <tbody>";
    foreach($users as $k=>$v) {
        $str .="<tr>\n<td>".$k."</td>\n";
        
        for ($i = 0 ; $i < 5 ; $i++) {
            $isNonWorkingDay = nonWorkingDay($datesOfTheWeek[$i]);
            $nwdClass = $isNonWorkingDay?" class=\"striped\"":"";
            $nwdTitle = $isNonWorkingDay?" title=\"".esc_html__("Non working day","lab")."\"":"";
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
                            $str .= td($hours->hour_start,$hours->hour_end,$hours->site_id, false,"style=\"background-color:#".$colors[$hours->site_id].";color:white;\"", $hours->user_id, $hours->id, true, $hours->comment);
                            //$str .= td($hours->hour_end,null,false,"style=\"background-color:#".$colors[$hours->site_id].";color:white;\"", $hours->user_id, $hours->id);
                            $nb = 2; 
                        }
                        // presence le matin
                        else {
                            $str .= td($hours->hour_start, $hours->hour_end,$hours->site_id, false,"style=\"background-color:#".$colors[$hours->site_id].";color:white;\"", $hours->user_id, $hours->id,false, $hours->comment);
                            $sum[$hours->site_id][$i*2] += 1;
                            //$str .= td(null, null, true);
                        }
                    }
                    // presence l'aprem
                    else {
                        if ($nb == 0) {
                            $str .= td(null, null, null, true);
                            $nb++;
                        }
                        $str .= td($hours->hour_start, $hours->hour_end, $hours->site_id, false, "style=\"background-color:#".$colors[$hours->site_id].";color:white;\"", $hours->user_id, $hours->id,false, $hours->comment);
                        $sum[$hours->site_id][$i*2+1] += 1;
                    }
                    $nb++;
                }
                if ($nb == 1) {
                    $str .= td(null, null, null, true);
                }
            }
            else {
                $str .= "<td colspan=\"2\" $nwdClass $nwdTitle>&nbsp;</td>\n";
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
    if (!is_user_logged_in() && $externalUserAllowed) {
        $str .=  newUserDiv();
    }
    return $str;
}

function getDay($dayInt, $dayInMonth, $year = 0) {
    $dayInt = ($dayInt) % $dayInMonth;
    if ($dayInt == 0) {
        $dayInt = $dayInMonth;
    }
    return $dayInt;
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
            <input id=\"external\" type=\"hidden\"  val=\"0\"/>

            <label for='date-open'>".esc_html__("Le", "lab")."</label>
            <input type='date' name='date-open' id='date-open' class='form-control'/>
            <div id='messErr_date-open' class='invalid-feedback'></div>
           
            <label for='hour-open'></label>
            <input type='time' name='hour-open' id='hour-open' min='07:00' max='20:00' required class='form-control'/>
            <div id='messErr_hour-open' class='invalid-feedback'></div>
            <label for='hour-close'>".esc_html__("Jusqu'à", "lab")."</label>
            <input type='time' name='hour-close' id='hour-close' min='07:00' max='20:00' required class='form-control'/>
            <div id='messErr_hour-close' class='invalid-feedback'></div>
            <label for='site-selected'>".esc_html__("sur le site", "lab")."</label>
            " . lab_html_select_str("siteId", "siteName", "custom-select", lab_admin_list_site) . "</div>
            <div class=\"input-group mb-3\">
            <div class=\"form-group\">
                <label for='comment'>".esc_html__("Commentaire", "lab")."</label>
                <textarea id=\"comment\" rows=\"4\" cols=\"50\" class=\"form-control rounded-0\" maxlength=\"200\" placeholder=\"200 caractères maximum\"></textarea>
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
        $comment   = $_POST['comment'];

        //requete pour envoyer la présence sur la bd
        global $wpdb;
        $comment = preg_replace("\'", "’", $comment);
        $data = array('user_id' => $userId, 'hour_start' => $date . ' ' . $hourOpen,
                'hour_end' => $date . ' ' . $hourClose, 'site' => $site, 'comment' => $comment);
        $format = array('%d','%s','%s','%d','%s');
        $wpdb->insert('wp_lab_presence', $data, $format);
    }

    $choiceStr .= "<div style='margin-top: 2em'><h3>".esc_html__("Je souhaite modifier une de mes présences", "lab")."</h3>";

    //requete pour connaitre les présences de l'utilisateur
    global $wpdb;
    $sql = "SELECT pre.*, par.value FROM `".$wpdb->prefix."lab_presence` AS pre
            JOIN `".$wpdb->prefix."lab_params` AS par
                ON pre.site = par.id
            WHERE `user_id` = " . get_current_user_id() . "
                AND pre.`hour_start` >= '" . date("Y-m-d", $startDay) . " 00:00:00'
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
        $choiceStr .= "<tr title=\"".$r->comment."\"><th scope='row'>" . ++$increment . "</th>
                        <td class='date-row edit' id=\"date_".$r->id."_".$r->user_id."\">"     . esc_html(date("Y-m-d", strtotime($r->hour_start))) ."</td>
                        <td class='hour-row open edit' id=\"hOpen_".$r->id."_".$r->user_id."\">". esc_html(date("H:i",   strtotime($r->hour_start))) ."</td>
                        <td class='hour-row end edit' id=\"hEnd_".$r->id."_".$r->user_id."\">" . esc_html(date("H:i", strtotime($r->hour_end)))  ."</td>
                        <td class='site-row edit' id=\"site_".$r->id."_".$r->user_id."\" siteId=\"".$r->site."\">"     . esc_html($r->value) ."</td>
                        <td><a id=\"delete_presence_".$r->id."\"><span class='fas fa-trash'></span></a>
                            <span class='fas fa-pen icon-edit' style='cursor: pointer;' editId=" . $r->id . " userId=" . $r->user_id . "></span>
                        </td></tr>";
    }
    $choiceStr .= "</tbody></table></div>";
    $choiceStr .= editDiv();
    $choiceStr .= deleteDiv();

    return $choiceStr;
}


/**
 * Generate div for edition
 *
 * @return void
 */
function newUserDiv()
{
    $str = 
    '<div id="lab_presence_external_user_dialog" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">'.esc_html("Edit", "lab").'</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <h4 class="modal-title">'.esc_html("User information", "lab").'</h4>
                    <input id="lab_presence_ext_new_user_ext" type="hidden"  val="1"/>
                    <div class="form-row">
                        <div class="col">
                        <input type="text" class="form-control" id="lab_presence_ext_new_user_firstname" placeholder="First name (mandatory)">
                        </div>
                        <div class="col">
                        <input type="text" class="form-control" id="lab_presence_ext_new_user_lastname" placeholder="Last name (mandatory)"/>
                        </div>
                    </div>
                    <div class="form-row">
                        <label for="date-lab_presence_ext_new_user_email">'.esc_html("E-mail", "lab").'</label>
                        <input type="email" class="form-control" id="lab_presence_ext_new_user_email" aria-describedby="emailHelp" placeholder="Enter email (mandatory)">
                    </div>
                    <small id="emailHelp" class="form-text text-muted">We\'ll never share your email with anyone else.</small>
                    <div class="h-divider"></div>
                    <label for="date-open">'.esc_html("From", "lab").'</label>
                    <input type="date" id="lab_presence_ext_new_date_open" />
                    <label for="hour-open"></label>
                    <input type="time" id="lab_presence_ext_new_hour_open" />
                    <label for="hour-close">'.esc_html("to", "lab").'</label>
                    <input type="time" id="lab_presence_ext_new_hour_close" />
                    <div class="input-group mb-3">
                        <label for="site-selected">'.esc_html("on the site", "lab").'</label>'. lab_html_select_str("lab_presence_ext_new_siteId", "siteName", "custom-select", lab_admin_list_site).'
                    </div>
                    <div class="input-group mb-3">
                        <div class="form-group">
                            <label for="comment">'.esc_html__("Comment", "lab").'</label>
                            <textarea id="lab_presence_ext_new_comment" rows="4" cols="50" class="form-control rounded-0"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="close" data-dismiss="modal">'.esc_html('Annuler','lab').'</button>
                    <button type="button" class="close" data-dismiss="modal" id="lab_presence_ext_new_save" keyid="">'.esc_html('Save','lab').'</button>
                </div>
            </div>
        </div>
    </div>';
    //$str .= '<button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#lab_presence_edit_dialog">Open Modal</button>';
    return $str;
}

/**
 * Generate div for edition
 *
 * @return void
 */
function editDiv()
{
    $str = 
    '<div id="lab_presence_edit_dialog" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">'.esc_html("Edit", "lab").'</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input id="lab_presence_edit_userId" name="userId" type="hidden"/>
                    <input id="lab_presence_edit_presenceId" name="userId" type="hidden"/>
                    <label for="lab_presence_edit_date-open">'.esc_html("From", "lab").'</label>
                    <input type="date" id="lab_presence_edit_date-open" />
                    <div id="messErr_lab_presence_edit_date" class="invalid-feedback"></div>
                    <label for="hour-open"></label>
                    <input type="time" id="lab_presence_edit_hour-open" min="07:00"  max="20:00" required />
                    <label for="hour-close">'.esc_html("to", "lab").'</label>
                    <input type="time" id="lab_presence_edit_hour-close" min="07:00" max="20:00"/>
                    <div class="input-group mb-3">
                        <label for="site-selected">'.esc_html("on the site", "lab").'</label>'. lab_html_select_str("lab_presence_edit_siteId", "siteName", "custom-select", lab_admin_list_site).'
                    </div>
                    <div class="input-group mb-3">
                        <div class="form-group">
                            <label for="comment">'.esc_html__("Comment", "lab").'</label>
                            <textarea id="lab_presence_edit_comment" rows="4" cols="50" class="form-control rounded-0"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="close" data-dismiss="modal">'.esc_html('Annuler','lab').'</button>
                    <button type="button" class="close" data-dismiss="modal" id="lab_presence_edit_save" keyid="">'.esc_html('Save','lab').'</button>
                </div>
            </div>
        </div>
    </div>';
    //$str .= '<button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#lab_presence_edit_dialog">Open Modal</button>';
    return $str;
}
/**
 * Generate modal div to delete
 *
 * @return void
 */
function deleteDiv() {
    $str = 
    '<div id="lab_presence_delete_dialog" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">'.esc_html__("Supprimer", "lab").'</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input id="lab_presence_del_presenceId" name="userId" type="hidden"/>
                    <input id="lab_presence_del_userId" name="userId" type="hidden"/>
                    ' . esc_html__("Êtes vous certain·e de vouloir supprimer cette présence ?", "lab") . '
                </div>
                <div class="modal-footer">
                    <button type="button" id="lab_presence_del_button" class="btn btn-danger delButton" data-dismiss="modal">'. esc_html__("Oui", "lab")   .'</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">'. esc_html__("Annuler","lab").'</button>
                </div>
            </div>
        </div>
    </div>';
    return $str;
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

/**
 * Generate a td for presence visualisation
 *
 * @param [type] $dateStart
 * @param [type] $dateEnd
 * @param boolean $empty
 * @param [type] $site
 * @param [type] $userId
 * @param [type] $presenceId
 * @param boolean $allDay
 * @param [type] $comment
 * @return void
 */
function td($dateStart = null, $dateEnd = null, $siteId = null, $empty = false, $site = null, $userId = null, $presenceId=null, $allDay = false, $comment= null) {
    if ($empty) {
        $str .= "<td >&nbsp;</td>";
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
        $title    = ($comment!=null?" title=\"".$comment."\"":"");
        $date     = " date=\"".date("Y-m-d", $dateStart)."\"";
        $siteId   = " siteId=\"".$siteId."\"";
        $hourStart = " hourStart=\"".date("H:i", $dateStart)."\"";
        $hourEnd   = " hourEnd=\"".date("H:i", $dateEnd)."\"";

        $str .= '<td '.$canDelete.' '.($site!=null?$site:'').$colSpan.$tdId.$date.$title.$siteId.$hourStart.$hourEnd.'><div class="wrapper"><div class="actions"'.$actionId.'><div title="Update" '.$editId.' class="floatLeft iconset_16px"><i class="fas fa-pen fa-xs"></i></div><div title="delete" '.$deleteId.' class="floatLeft iconset_16px"><i class="fas fa-trash fa-xs"></i></div></div><div class="gal_name">'.date('H:i', $dateStart);
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