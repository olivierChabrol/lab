<?php
/*
 * File Name: lab-shortcode-invitation.php
 * Description: shortcode pour afficher un formulaire de création d'invitation
 * Authors: Ivan IVANOV, Lucas URGENTI, Olivier CHABROL
 * Version: 1.2
 */

function lab_mission($args) {
    $param = shortcode_atts(array(
        'hostpage' => 0 //0 pour invité, 1 pour invitant/responsable
        ),
        $args, 
        "lab-invitation"
    );
    global $wp;
    $invitationStr ='';
    $url = $wp->request;
    $host = null;
    $invitationStr = "";
    $token='0';
    if ( isset($param['hostpage']) ) {
        $explode = explode("/",$url);
        $a = null;
        if (count($explode) > 1) {
            $a = explode("/",$url)[1];
        }
        if ( ! isset($a)) { //Aucun token, donc l'invitant crée lui-même une nouvelle invitation
            $host = new labUser(get_current_user_id());
            $invitationStr = "<h3>Pas de token</h3>";
        } else {//Token fournit, récupère les informations existantes
            $token = $a;
            //$travels = lab_mission_route_get($token);

            $invitationStr = "<h3>token : ".$token."</h3>";
            $invitation    = lab_invitations_getByToken($token);
            $invitationStr .= '<input type="hidden" id="lab_mission_token" value="'.$token.'"/>';
            $invitationStr .= '<input type="hidden" id="lab_mission_id" value="'.$invitation->id.'"/>';
            $budget_manager_ids = lab_group_budget_manager($invitation->host_group_id);
            //$travels       = 
            //var_dump($invitation);
            
            $charges = json_decode($invitation->charges);
            if (!isset($invitation)) {
                return esc_html__("Token d'invitation invalide",'lab');
            }
            $guest = lab_invitations_getGuest($invitation->guest_id);
            //var_dump($guest);
            $host = new labUser($invitation->host_id);
            //Qui modifie, l'invitant ou le responsable ?
            $isChief = isset($invitation->host_group_id) ? get_current_user_id()==(int)lab_admin_get_chief_byGroup($invitation->host_group_id): false;
            $isManager = false;
            foreach($budget_manager_ids as $bm) {
                if (get_current_user_id() == $bm) {
                    $isManager = true;
                }
            }
            if ( $isChief ) {
                $invitationStr .= '<p><i>Vous pouvez modifier cette invitation en tant que responsable de groupe</i></p>';
                $invitationStr .= '<p><i>Statut de l\'invitation : </i>'.lab_invitations_getStatusName($invitation->status).'</p>';
                
            } else if ( get_current_user_id()==$invitation->host_id ) { 
                $invitationStr .= '<p><i>Vous pouvez modifier cette invitation en tant qu\'invitant</i></p>';
                $invitationStr .= '<p><i>Statut de l\'invitation : </i>'.lab_invitations_getStatusName($invitation->status).'</p>';
            
            } 
            else if ( $isManager ) {
                $invitationStr .= '<p><i>Vous pouvez modifier cette invitation en tant que responsable budget</i></p>';
                $invitationStr .= '<p><i>Statut de l\'invitation : </i>'.lab_invitations_getStatusName($invitation->status).'</p>';
            } else {
                var_dump($budget_manager_id->user_id);
                die('Vous ne pouvez pas modifier cette invitation');
            }
        }
    } else {
        $invitationStr = "<h3>\$param['hostpage']".$param['hostpage']."</h3>";
        $host = isset(explode("/",$url)[1]) ? new labUser(lab_profile_getID(explode("/",$url)[1])) : 0 ;
        if ($host == 0) {
            $host = new labUser(get_current_user_id());
        }
    }
    $newForm = (/*(!$param['hostpage'] ||*/ $token=='0') ? true : false ; //Le formulaire est-il nouveau ? Si non, remplit les champs avec les infos existantes
    $invitationStr .= '<div id="invitationForm" hostForm='.$param['hostpage'].' token="'.(($param['hostpage'] && strlen($token)>1) ? $token : '').'" newForm='.$newForm.'>
                      <h2>'.esc_html__("Form","lab").'<i class="fas fa-arrow-up"></i></h2>'.$invitationStr;
    $invitationStr .= '
        <!-- <form action="javascript:formAction()"> -->
        <h3>'.esc_html__("Personnal informations","lab").'</h3>

        <div class="lab_invite_field">
            <input type="text" required id="lab_hostname" name="lab_hostname" host_id="'.($host==null ? '' : $host->id.'" value="'.$host->first_name.' '.$host->last_name).'">
        </div>';
    $groups = lab_admin_group_by_user($host->id);
    if (count($groups) == 1) {
        $invitationStr .= '<input type="hidden" id="lab_group_name" value="'.$groups[0]->id.'">';
    }
    else {
        $invitationStr .= '<div class="lab_invite_field"><label for="lab_group_name">'.esc_html__("Group","lab").'</label>';
        $invitationStr .= '<select id="lab_group_name">';
        $selectedGroup = !$newForm ? $invitation->host_group_id: '';
        foreach($groups as $group) {
            $select = "";
            if ($selectedGroup) {
                if ($selectedGroup == $group->id) {
                    $select = " selected";
                }
            }
            else {
                if($group->favorite == 1) {
                    $select = " selected";
                }
            }
            $invitationStr .= '<option value="'.$group->id.'"'.$select.'>'.$group->name.'</option>';
        }
        $invitationStr .= '</select></div>';

    }
    $invitationStr .= '
        <div class="lab_invite_field">
            <label for="lab_mission">'.esc_html__("Reason for the mission","lab").'<span class="lab_form_required_star">
            <select id="lab_mission" name="lab_mission">';
    foreach(AdminParams::get_params_fromId(AdminParams::PARAMS_MISSION_ID) as $missionparam)
    {
        $selectedGroup = "";
        if(isset($invitation)) {
            $selectedGroup = ($invitation->mission_objective==$missionparam->value)?'selected="selected"':"";
        } 
        else {
            $selectedGroup = ($missionparam->value == "Mission" ? 'selected="selected"':"");
        }
        $invitationStr .= '<option value="'.$missionparam->id.'" '.$selectedGroup.'>'.esc_html__($missionparam->value,"lab")                                                                                                                                                                                      .'</option>';
    }
    $invitationStr .= '</select>
            <input style="display:none" type="text" id="lab_mission_other" value="'.($newForm ? '' : $invitation->mission_objective).'">
            <p style="display:none" id="lab_mission_other_desc">'.esc_html__("Specify the nature of your mission here.","lab").'</p>
        </div>
        <div id="inviteDiv">
        <hr>
        <h3>'.esc_html__("Guest Informations","lab").'</h3>
        <div class="lab_invite_field">
            <label for="lab_email">'.esc_html__("Email","lab").'<span class="lab_form_required_star"> *</span></label>
            <input type="email" required id="lab_email" guest_id="" name="lab_email"value="'.(!$newForm ? $guest->email : '').'">
        </div>
        <div class="lab_invite_row" id="lab_fullname">
            <div class="lab_invite_field">
                <label for="lab_firstname">'.esc_html__("Firstname","lab").'<span class="lab_form_required_star"> *</span></label>
                <input type="text" required id="lab_firstname" name="lab_firstname" guest_id="'.(!$newForm ? $guest->id : '').'" value="'.(!$newForm ? $guest->first_name : '').'">
            </div>
            <div class="lab_invite_field">
                <label for="lab_lastname">'.esc_html__("Lastname","lab").'<span class="lab_form_required_star"> *</span></label>
                <input type="text" required id="lab_lastname" name="lab_lastname" value="'.(!$newForm ? $guest->last_name : '').'">
            </div>
        </div>
        <div id="lab_phone_country">
            <div class="lab_invite_field">
                <label for="lab_phone">'.esc_html__("Phone Number","lab").'</label>
                <input type="tel" id="lab_phone" phoneval="'.(!$newForm ? $guest->phone : '').'">
            </div>
            <div class="lab_invite_field">
                <label for="guest_language">'.esc_html__("Language","lab").'<span class="lab_form_required_star"> *</span></label>
                <input type="text" required id="guest_language" name="guest_language" countryCode="'.(!$newForm ? $guest->language : '').'">
            </div>
            <div class="lab_invite_row" id="lab_residence">
                <div class="lab_invite_field">
                    <label for="residence_city">'.esc_html__("City of residence","lab").'</label>
                    <input type="text" required id="residence_city" name="residence_city" value="'.(!$newForm ? $guest->residence_city : '').'">
                </div>
                <div class="lab_invite_field">
                    <label for="residence_country">'.esc_html__("Country of residence","lab").'</label>
                    <input type="text" required id="residence_country" name="residence_country" countryCode="'.(!$newForm ? $guest->residence_country : '').'">
                </div>
            </div>
        </div>
        <div class="lab_invite_row">
            <input type="checkbox" id="lab_hostel" name="lab_hostel" ';

        if($param['hostpage'] && $invitation->needs_hostel == 1)
        {
            $invitationStr .= 'checked';
        }
            
        $invitationStr .= 
            '>
            <label for="lab_hostel">'.esc_html__("Need a hostel","lab").'</label>
        </div>
        </div><!-- end invite div -->
        <hr>
        <h3>'.esc_html__("Journeys","lab").'</h3>
        <div id="lab_mission_mean_travel">
            <input type="hidden" id="lab_mission_travels" value="">
            <table id="lab_mission_travels_table" class="table">
                <thead>
                    <td colspan="2">'.esc_html__("Departure date","lab").'</td>
                    <td colspan="2">'.esc_html__("From","lab").'</td>
                    <td colspan="2">'.esc_html__("To","lab").'</td>
                    <td>'.esc_html__("Mean","lab").'</td>
                    <td>'.esc_html__("Cost","lab").'</td>
                    <td>'.esc_html__("Ref","lab").'</td>
                    <td>'.esc_html__("RT","lab").'</td>
                    <td colspan="2">'.esc_html__("Return date if RT","lab").'</td>
                    <td colspan="2"><i id="addTravel" class="fa fa-plus pointer" aria-hidden="true" title="Add travel"></i></td>
                </thead>
                <tbody id="lab_mission_travels_table_tbody"/>
            </table>
        </div>
        <div id="lab_mission_edit_travel_div" class="lab_fe_modal">
            <div class="lab_fe_modal-content">
                <span class="lab_fe_modal_close">&times;</span>
                <label for="lab_mission_edit_travel_div_dateGoTo">'.esc_html__("Departure date","lab").'</label>
                <input type="date" class="datechk" placeholder="yyyy-mm-dd" id="lab_mission_edit_travel_div_dateGoTo">
                <input type="time" placeholder="hh:mm" step="60" id="lab_mission_edit_travel_div_timeGoTo" name="lab_arrival_time" value="">
                <br/>
                <label for="lab_mission_edit_travel_div_countryFrom">'.esc_html__("City departure","lab").'</label>
                <input type="text" id="lab_mission_edit_travel_div_countryFrom"  countryCode="FR">
                <input type="text" id="lab_mission_edit_travel_div_cityFrom" value="">
                <br/>
                <label for="lab_mission_edit_travel_div_countryTo">'.esc_html__("City arrival","lab").'</label>
                <input type="text" id="lab_mission_edit_travel_div_countryTo"  countryCode="FR">
                <input type="text" id="lab_mission_edit_travel_div_cityTo" value="">
                <br/>
                <label for="lab_mission_edit_travel_div_mean">'.esc_html__("Mean of transport","lab").'</label>';
                $invitationStr .= lab_html_select_str("lab_mission_edit_travel_div_mean", "lab_mission_edit_travel_div_mean", "", "lab_admin_get_params_meanOfTransport", null, array("value"=>"0","label"=>"None"), "");;
                $invitationStr .=
                '<br/>
                <label for="lab_mission_edit_travel_div_nb_person">'.esc_html__("Number of persons","lab").'</label>
                <input type="text" id="lab_mission_edit_travel_div_nb_person" >
                <br/>
                <label for="lab_mission_edit_travel_div_ref">'.esc_html__("Travel reference","lab").'</label>
                <input type="text" id="lab_mission_edit_travel_div_ref" >
                <br/>
                <label for="lab_mission_edit_travel_div_cost">'.esc_html__("Estimated cost","lab").'</label>
                <input type="text" id="lab_mission_edit_travel_div_cost" >
                <br/>
                <label for="lab_mission_edit_travel_div_rt">'.esc_html__("Round trip","lab").'</label>
                <input type="checkbox" id="lab_mission_edit_travel_div_rt" >
                <br/>
                <span id="returnSpanDate">
                    <label for="lab_mission_edit_travel_div_dateReturn">'.esc_html__("Return date","lab").'</label>
                    <input type="date" class="datechk" id="lab_mission_edit_travel_div_dateReturn">
                    <input type="time" placeholder="hh:mm" step="60" id="lab_mission_edit_travel_div_timeReturn" value="">

                </span>
                <br/>
                <label for="lab_mission_edit_travel_div_carbon_footprint">'.esc_html__("Carbon footprint","lab").'</label>
                <input type="text" id="lab_mission_edit_travel_div_carbon_footprint" >
                <input type="hidden" id="lab_mission_edit_travel_div_travelId" value="" >
                <input type="hidden" id="lab_mission_edit_travel_div_trId" value="" >
                <br/>
                <button id="lab_mission_edit_travel_save_button" travelId="">'.esc_html__("Save","lab").'</button>
            </div>
        </div>
        <hr>';
        if ( $newForm ) {// Affiche le champ pour ajouter un commentaire lors de la création
            $invitationStr .= 
            '<div class="lab_invite_field">
                <label for="lab_form_comment">'.esc_html__("Comments",'lab').'</label>
                <textarea row="1" id="lab_form_comment" name="lab_form_comment"></textarea>
                <p>'.esc_html__("(par exemple vos numéros de carte de fidélité à utiliser lors de la réservation de vos voyages + la date d'expiration si nécessaire)",'lab').'</p>
        </div>';
        }
        if ( $param["hostpage"] ) {//Affiche les champs supplémentaires, pour les responsables/invitants.
            $invitationStr .=

            '<h3>'.esc_html__("Inviting fields : ","lab").'</h3>
            <div class="lab_invite_row">
                <div class="lab_invite_field">
                    <label for="lab_group_name">'.esc_html__("Group name","lab").'<span class="lab_form_required_star"> *</span></label>
                    <select required id="lab_group_name" name="lab_group_name">';
                    
                foreach ($host->groups as $g)
                {
                    $selectedGroup = ($invitation->host_group_id==$g->id)?'selected="selected"':"";
                    $invitationStr .= '<option value="'.$g->id.'" '.$selectedGroup.'>'.$g->group_name.'</option>';
                }

                $invitationStr .=
                    '</select>
                </div>
                <div class="lab_invite_field">
                    <label for="lab_credit">'.esc_html__("Origin of credits","lab").'<span class="lab_form_required_star"> *</span></label>
                    <select required id="lab_credit" name="lab_credit">
                        <option value="">'.esc_html__("Choose an option","lab").'</option>';
                    foreach(AdminParams::get_params_fromId(AdminParams::PARAMS_FUNDING_ID) as $creditparam)
                    {
                        $selectedGroup = ($invitation->funding_source==$creditparam->id)?'selected="selected"':"";
                    $invitationStr .= 
                        '<option value="'.$creditparam->id.'" '.$selectedGroup.'>'.esc_html__($creditparam->value,"lab").'</option>';
                    }
                    $invitationStr .=
                        '<option value="other">'.esc_html__("Other","lab").'</option>
                    </select>
                    <input style="display:none" type="text" id="lab_credit_other" value="'.(!$newForm ? $invitation->funding_source : '').'">
                    <p style="display:none" id="lab_credit_other_desc">'.esc_html__("Specify the origin of credit here.","lab").'</p>
                </div>
            </div>
            <div class="lab_invite_field">
                    <label for="lab_research_contrat">'.esc_html__("Research contracts","lab").'</label>
                    <input type="text" id="lab_research_contrat" name="lab_research_contrat" value="'.(!$newForm ? $invitation->research_contract : '').'">
            </div>
            <div class="lab_invite_row">
                <div class="lab_invite_field">
                    <label for="lab_estimated_cost">'.esc_html__("Estimated cost (€)","lab").'</label>
                    <input type="text" id="lab_estimated_cost" value="'.(!$newForm ? $invitation->estimated_cost : '').'">
                    <p>'.esc_html__("À remplir par l'invitant : coût estimé du défraiement ","lab").'</p>
                </div>
                <div class="lab_invite_field">
                    <label for="lab_maximum_cost">'.esc_html__("Maximum cost (€)","lab").'</label>
                    <input type="text" id="lab_maximum_cost" value="'.(!$newForm ? $invitation->maximum_cost : '').'">
                    <p>'.esc_html__("To be filled in by the person in charge: maximum budget allocated to this invitation ","lab").'</p>
                </div>
            </div>';
            if ($isChief) {
                $invitationStr .= '<div class="lab_invite_field">
                <input '.($invitation->status>10 ? 'disabled' : '').' type="submit" value="'.esc_html__("Save","lab").'">
                </div>'.($invitation->status>10 ? '<i>'.esc_html__("This invitation is already in the next step, to modify it, you must resend it (via the button below)",'lab').'</i>' : '').
                '<!-- </form>--></div>
                <div class="lab_invite_row lab_send_manager"><p class="lab_invite_field">Cliquez ici pour valider la demande et la transmettre au pôle budget :</p><button id="lab_send_manager">'.esc_html__("Send to administration",'lab').'</button></div>';
            } else {
                $invitationStr .= '<div class="lab_invite_field">
                <input '.($invitation->status>1 ? 'disabled' : '').' type="submit" value="'.esc_html__("Save","lab").'">
                </div>'.($invitation->status>1 ? '<i>'.esc_html__("This invitation is already in the next step, to modify it, you must resend it (via the button below)",'lab').'</i>' : '').
                '<!-- </form>--></div>
                <div class="lab_invite_row lab_send_group_chief"><p class="lab_invite_field">Cliquez ici pour compléter la demande et la transmettre au responsable du groupe :</p><button id="lab_send_group_chief">'.esc_html__("Send to responsible",'lab').'</button></div>';
            }
        }
        else {
            $invitationStr .= '<div class="lab_invite_field">
            <button id="lab_mission_validate">'.esc_html__("Submit","lab").'</button>
        </div>';
        }
        if (!$newForm) {
            $currentUser = lab_admin_userMetaDatas_get(get_current_user_id());
            $invitationStr .= '
        <div id="lab_invitationComments">
            <h2>Commentaires <i class="fas fa-arrow-up"></i></h2>
                '.lab_inviteComments($token).'
                '.lab_newComments($currentUser,$token).'
        </div><!-- end div lab_invitationComments -->';
        }
    return $invitationStr;
}


/*function addTravel($travel, $id) {

    $stringToGo = explode (" ", $travel->travel_date);
    $stringReturn = explode (" ", $travel->travel_datereturn);

    

    $html = '<tr>';
    $html .= '<td id="travel_dateGoTo_'.$id.'">'.$stringToGo[0].'</td>';//date
    $html .= '<td id="travel_timeGoTo_'.$id.'">'.$stringToGo[1].'</td>';// heure
    $html .= '<td id="travel_country_from_'.$id.'">'.$travel->country_from.'</td>';
    $html .= '<td id="travel_city_from_'.$id.'">'.$travel->city_from.'</td>';
    $html .= '<td id="travel_country_to_'.$id.'">'.$travel->country_to.'</td>';
    $html .= '<td id="travel_city_to_'.$id.'">'.$travel->travel_to.'</td>';
    $html .= '<td id="travel_mean_'.$id.'">'.$travel->mean_of_locomotion.'</td>';
    $html .= '<td id="travel_ref_'.$id.'"></td>';
    $html .= '<td id="travel_bt_'.$id.'">'.$travel->round_trip.'</td>';
    $html .= '<td id="travel_date_return_'.$id.'">'.$stringReturn[0].'</td>';
    $html .= '<td id="travel_time_return_'.$id.'">'.$stringReturn[1].'</td>';
    $html .= '</tr>';
    return $html;
}*/

function lab_invitation($args) {
    $param = shortcode_atts(array(
        'hostpage' => 0 //0 pour invité, 1 pour invitant/responsable
        ),
        $args, 
        "lab-invitation"
    );
    global $wp;
    $invitationStr ='';
    $url = $wp->request;
    if ( $param['hostpage'] ) {
        if ( ! isset(explode("/",$url)[1])) { //Aucun token, donc l'invitant crée lui-même une nouvelle invitation
            $token='0';
            $host = new labUser(get_current_user_id());
        } else {//Token fournit, récupère les informations existantes
            $token = explode("/",$url)[1];
            $invitation=lab_invitations_getByToken($token);
            //var_dump($invitation);
            $charges = json_decode($invitation->charges);
            if (!isset($invitation)) {
                return esc_html__("Token d'invitation invalide",'lab');
            }
            $guest = lab_invitations_getGuest($invitation->guest_id);
            //var_dump($guest);
            $host = new labUser($invitation->host_id);
            //Qui modifie, l'invitant ou le responsable ?
            $isChief = isset($invitation->host_group_id) ? get_current_user_id()==(int)lab_admin_get_chief_byGroup($invitation->host_group_id): false;
            if ( $isChief ) {
                $invitationStr .= '<p><i>Vous pouvez modifier cette invitation en tant que responsable de groupe</i></p>';
                $invitationStr .= '<p><i>Statut de l\'invitation : </i>'.lab_invitations_getStatusName($invitation->status).'</p>';
                
            } else if ( get_current_user_id()==$invitation->host_id ) { 
                $invitationStr .= '<p><i>Vous pouvez modifier cette invitation en tant qu\'invitant</i></p>';
                $invitationStr .= '<p><i>Statut de l\'invitation : </i>'.lab_invitations_getStatusName($invitation->status).'</p>';
            } else {
                die('Vous ne pouvez pas modifier cette invitation');
            }
        }
    } else {
        $host = isset(explode("/",$url)[1]) ? new labUser(lab_profile_getID(explode("/",$url)[1])) : 0 ;
    }
    $newForm = (!$param['hostpage'] || $token=='0') ? 1 : 0 ; //Le formulaire est-il nouveau ? Si non, remplit les champs avec les infos existantes
    $invitationStr = '<div id="invitationForm" hostForm='.$param['hostpage'].' token="'.(($param['hostpage'] && strlen($token)>1) ? $token : '').'" newForm='.$newForm.'>
                      <h2>'.esc_html__("Form","lab").'<i class="fas fa-arrow-up"></i></h2>'.$invitationStr;
    $invitationStr .= '
        <form action="javascript:formAction()">
        <h3>'.esc_html__("Informations personnelles","lab").'</h3>
        <div class="lab_invite_field">
            <label for="lab_email">'.esc_html__("Email","lab").'<span class="lab_form_required_star"> *</span></label>
            <input type="email" required id="lab_email" guest_id="" name="lab_email"value="'.(!$newForm ? $guest->email : '').'">
        </div>
        <div class="lab_invite_row" id="lab_fullname">
            <div class="lab_invite_field">
                <label for="lab_firstname">'.esc_html__("First name","lab").'<span class="lab_form_required_star"> *</span></label>
                <input type="text" required id="lab_firstname" name="lab_firstname" guest_id="'.(!$newForm ? $guest->id : '').'" value="'.(!$newForm ? $guest->first_name : '').'">
            </div>
            <div class="lab_invite_field">
                <label for="lab_lastname">'.esc_html__("Last name","lab").'<span class="lab_form_required_star"> *</span></label>
                <input type="text" required id="lab_lastname" name="lab_lastname" value="'.(!$newForm ? $guest->last_name : '').'">
            </div>
        </div>
        <div id="lab_phone_country">
            <div class="lab_invite_field">
                <label for="lab_phone">'.esc_html__("Phone number","lab").'</label>
                <input type="tel" id="lab_phone" phoneval="'.(!$newForm ? $guest->phone : '').'">
            </div>
            <div class="lab_invite_field">
                <label for="guest_language">'.esc_html__("Language","lab").'<span class="lab_form_required_star"> *</span></label>
                <input type="text" required id="guest_language" name="guest_language" countryCode="'.(!$newForm ? $guest->language : '').'">
            </div>
            <div class="lab_invite_row" id="lab_residence">
                <div class="lab_invite_field">
                    <label for="residence_city">'.esc_html__("City of residence","lab").'</label>
                    <input type="text" required id="residence_city" name="residence_city" value="'.(!$newForm ? $guest->residence_city : '').'">
                </div>
                <div class="lab_invite_field">
                    <label for="residence_country">'.esc_html__("Country of residence","lab").'</label>
                    <input type="text" required id="residence_country" name="residence_country" countryCode="'.(!$newForm ? $guest->residence_country : '').'">
                </div>
            </div>
        </div>
        <div class="lab_invite_field">
            <label for="lab_hostname">'.esc_html__("Inviting name","lab").'<span class="lab_form_required_star"> *</span></label>
            <input type="text" required id="lab_hostname" name="lab_hostname" host_id="'.($host==0 ? '' : $host->id.'" value="'.$host->first_name.' '.$host->last_name).'">
        </div>
        <div class="lab_invite_field">
            <label for="lab_mission">'.esc_html__("Reason for the mission","lab").'</label>
            <select id="lab_mission" name="lab_mission">
                <option value="">'.esc_html__("Select an option","lab").'</option>';

    foreach(AdminParams::get_params_fromId(AdminParams::PARAMS_MISSION_ID) as $missionparam)
    {

        $selectedGroup = ($invitation->mission_objective==$missionparam->value)?'selected="selected"':"";
        $invitationStr .= '<option value="'.$missionparam->id.'" '.$selectedGroup.'>'.esc_html__($missionparam->value,"lab").'</option>';
    }
    $invitationStr .= 
                '<option value="other">'.esc_html__("Other","lab").'</option>
            </select>
            <input style="display:none" type="text" id="lab_mission_other" value="'.(!$newForm ? $invitation->mission_objective : '').'">
            <p style="display:none" id="lab_mission_other_desc">'.esc_html__("Specify the nature of your mission here.","lab").'</p>
        </div>
        <hr>
        <div class="lab_invite_row">
            <input type="checkbox" id="lab_hostel" name="lab_hostel" ';

        if($param['hostpage'] && $invitation->needs_hostel == 1)
        {
            $invitationStr .= 'checked';
        }
            
        $invitationStr .=
            '>
            <label for="lab_hostel">'.esc_html__("Need a hostel","lab").'</label>
        </div>
        <hr>
        <h3>'.esc_html__("Mean of transport","lab").'</h3>
        <div id="lab_mean_travel" class="lab_invite_row">
            <div class="lab_invite_field">
                <label for="lab_transport_to">'.esc_html__("To the institute","lab").'</label>
                <select id="lab_transport_to" name="lab_transport_to" value="'.(!$newForm ? $invitation->travel_mean_to : '').'">
                <option value="">'.esc_html__("Select an option","lab").'</option>
                    <option value="car">'.esc_html__("Car","lab").'</option>
                    <option value="train">'.esc_html__("Train","lab").'</option>
                    <option value="plane">'.esc_html__("Plane","lab").'</option>
                    <option value="bus">'.esc_html__("Bus","lab").'</option>
                    <option value="none">'.esc_html__("None","lab").'</option>
                    <option value="other">'.esc_html__("Other","lab").'</option>
                </select>
                <input hidden type="text" id="lab_transport_to_other" value="'.(!$newForm ? $invitation->travel_mean_to : '').'">
                <p>'.esc_html__("Mean of transport from your home to our laboratory","lab").'</p>
                <label for="forward_start_station">'.esc_html__("Departure station name","lab").'</label>
                <input type="text" id="forward_start_station" value="'.(!$newForm ? $invitation->forward_start_station : '').'">
                <label for="lab_cost_to">'.esc_html__("Estimated travel cost","lab").' :</label>
                <input type="number" min=0 step="0.1" id="lab_cost_to" '.(!$newForm ? 'value="'.$charges->travel_to.'"' : '').' name="lab_cost_to" placeholder="'.esc_html__("in €",'lab').'"/>
                <p>'.esc_html__("To be completed only if the I2M will have to pay for this trip.",'lab').'</p>
                <label for="forward_travel_reference">'.esc_html__("Forward travel reference","lab").'</label>
                <input type="text" id="forward_travel_reference" placeholder="'.esc_html__("Fight number, train reference, ...","lab").'" value="'.(!$newForm ? $invitation->forward_travel_reference : '').'">
            </div>
            <div class="lab_invite_field">
                <label for="lab_transport_from">'.esc_html__("From I2M","lab").'</label>
                <select id="lab_transport_from" name="lab_transport_from" value="'.(!$newForm ? $invitation->travel_mean_from : '').'">
                    <option value="">'.esc_html__("Select an option","lab").'</option>
                    <option value="car">'.esc_html__("Car","lab").'</option>
                    <option value="train">'.esc_html__("Train","lab").'</option>
                    <option value="plane">'.esc_html__("Plane","lab").'</option>
                    <option value="bus">'.esc_html__("Bus","lab").'</option>
                    <option value="none">'.esc_html__("None","lab").'</option>
                    <option value="other">'.esc_html__("Other","lab").'</option>
                </select>
                <input hidden type="text" id="lab_transport_from_other" value="'.(!$newForm ? $invitation->travel_mean_from : '').'">
                <p>'.esc_html__("Mean of transport from our laboratory to your home","lab").'</p>
                <label for="return_end_station">'.esc_html__("Arrival station name","lab").'</label>
                <input type="text" id="return_end_station"  value="'.(!$newForm ? $invitation->return_end_station : '').'">
                <label for="lab_cost_from">'.esc_html__("Estimated travel cost","lab").' :</label>
                <input type="number" min=0 step="0.1" id="lab_cost_from" '.(!$newForm ? 'value="'.$charges->travel_from.'"' : '').' name="lab_cost_from" placeholder="'.esc_html__("in €",'lab').'"/>
                <p>'.esc_html__("To be completed only if the I2M will have to pay for this trip.",'lab').'</p>
                <label for="return_travel_reference">'.esc_html__("Return travel reference","lab").'</label>
                <input type="text" id="return_travel_reference" placeholder="'.esc_html__("Fight number, train reference, ...","lab").'" value="'.(!$newForm ? $invitation->return_travel_reference : '').'">
            </div>
        </div> 
        <div id="lab_date" class="lab_invite_row">
            <div class="lab_invite_field" >
                <label for="lab_arrival">'.esc_html__("Arrival date","lab").'</label>
                <input type="date" id="lab_arrival" name="lab_arrival" value="'.(!$newForm ? explode(" ",$invitation->start_date)[0] : '').'">
                <input type="time" step="60" id="lab_arrival_time" name="lab_arrival_time" value="'.(!$newForm ? explode(" ",$invitation->start_date)[1] : '').'">
                <p>'.esc_html__("Specify the date when you book the trip, the time is when you leave home","lab").'</p>
            </div>
            <div class="lab_invite_field">
                <label for="lab_departure">'.esc_html__("Departure date","lab").'</label>
                <input type="date" id="lab_departure" name="lab_departure" value="'.(!$newForm ? explode(" ",$invitation->end_date)[0] : '').'">
                <input type="time" step="60" id="lab_departure_time" name="lab_departure_time" value="'.(!$newForm ? explode(" ",$invitation->end_date)[1] : '').'">
                <p>'.esc_html__("Specify the date of travel booking, the time is when you leave the lab.","lab").'</p>
            </div>
        </div>
        <h3>'.esc_html__("Other costs","lab").'</h3>
        <p>'.esc_html__("Specify the costs to be covered by the I2M.",'lab').'</p>
        <div class="lab_invite_row">
            <div class="lab_invite_field">
            <label for="lab_cost_hostel">'.esc_html__("Hostel","lab").' :</label>
                <input type="number" min=0 step="0.1" id="lab_cost_hostel" '.(!$newForm ? 'value="'.$charges->hostel.'"' : '').' name="lab_cost_hostel" placeholder="'.esc_html__("in €",'lab').'"/>
                <label for="lab_cost_meals">'.esc_html__("Lunch","lab").' :</label>
                <input type="number" min=0 step="0.1" id="lab_cost_meals" name="lab_cost_meals" '.(!$newForm ? 'value="'.$charges->meals.'"' : '').' placeholder="'.esc_html__("in €",'lab').'"/>
            </div>
            <div class="lab_invite_field">
                <label for="lab_cost_taxi">'.esc_html__("Taxi","lab").' :</label>
                <input type="number" min=0 step="0.1" id="lab_cost_taxi" name="lab_cost_taxi" '.(!$newForm ? 'value="'.$charges->taxi.'"' : '').' placeholder="'.esc_html__("in €",'lab').'"/>
                <label for="lab_cost_other">'.esc_html__("Other","lab").' :</label>
                <input type="number" min=0 step="0.1" id="lab_cost_other" name="lab_cost_other" '.(!$newForm ? 'value="'.$charges->other.'"' : '').' placeholder="'.esc_html__("in €",'lab').'"/>
            </div>
        </div>
        <hr>';
        if ( $newForm ) {// Affiche le champ pour ajouter un commentaire lors de la création
            $invitationStr .= 
            '<div class="lab_invite_field">
                <label for="lab_form_comment">'.esc_html__("Comments",'lab').'</label>
                <textarea row="1" id="lab_form_comment" name="lab_form_comment"></textarea>
                <p>'.esc_html__("(e.g. your loyalty card numbers to be used when booking your trips + the expiry date if necessary)",'lab').'</p>
        </div>';
        }
        if ( $param["hostpage"] ) {//Affiche les champs supplémentaires, pour les responsables/invitants.
            $invitationStr .=

            '<h3>'.esc_html__("Inviting fields : ","lab").'</h3>
            <div class="lab_invite_row">
                <div class="lab_invite_field">
                    <label for="lab_group_name">'.esc_html__("Group name","lab").'<span class="lab_form_required_star"> *</span></label>
                    <select required id="lab_group_name" name="lab_group_name">';
                    
                foreach ($host->groups as $g)
                {
                    $selectedGroup = ($invitation->host_group_id==$g->id)?'selected="selected"':"";
                    $invitationStr .= '<option value="'.$g->id.'" '.$selectedGroup.'>'.$g->group_name.'</option>';
                }

                $invitationStr .=
                    '</select>
                </div>
                <div class="lab_invite_field">
                    <label for="lab_credit">'.esc_html__("Origin of credits","lab").'<span class="lab_form_required_star"> *</span></label>
                    <select required id="lab_credit" name="lab_credit">
                        <option value="">'.esc_html__("Select an option","lab").'</option>';
                    foreach(AdminParams::get_params_fromId(AdminParams::PARAMS_FUNDING_ID) as $creditparam)
                    {
                        $selectedGroup = ($invitation->funding_source==$creditparam->id)?'selected="selected"':"";
                    $invitationStr .= 
                        '<option value="'.$creditparam->id.'" '.$selectedGroup.'>'.esc_html__($creditparam->value,"lab").'</option>';
                    }
                    $invitationStr .=
                        '<option value="other">'.esc_html__("Other","lab").'</option>
                    </select>
                    <input style="display:none" type="text" id="lab_credit_other" value="'.(!$newForm ? $invitation->funding_source : '').'">
                    <p style="display:none" id="lab_credit_other_desc">'.esc_html__("Specify the origin of credit here.","lab").'</p>
                </div>
            </div>
            <div class="lab_invite_field">
                    <label for="lab_research_contrat">'.esc_html__("Research contracts","lab").'</label>
                    <input type="text" id="lab_research_contrat" name="lab_research_contrat" value="'.(!$newForm ? $invitation->research_contract : '').'">
            </div>
            <div class="lab_invite_row">
                <div class="lab_invite_field">
                    <label for="lab_estimated_cost">'.esc_html__("Estimated cost (€)","lab").'</label>
                    <input type="text" id="lab_estimated_cost" value="'.(!$newForm ? $invitation->estimated_cost : '').'">
                    <p>'.esc_html__("À remplir par l'invitant : coût estimé du défraiement ","lab").'</p>
                </div>
                <div class="lab_invite_field">
                    <label for="lab_maximum_cost">'.esc_html__("Maximum cost (€)","lab").'</label>
                    <input type="text" id="lab_maximum_cost" value="'.(!$newForm ? $invitation->maximum_cost : '').'">
                    <p>'.esc_html__("To be completed by the person in charge: maximum budget for this invitation ","lab").'</p>
                </div>
            </div>';
            if ($isChief) {
                $invitationStr .= '<div class="lab_invite_field">
                <input '.($invitation->status>10 ? 'disabled' : '').' type="submit" value="'.esc_html__("Save","lab").'">
                </div>'.($invitation->status>10 ? '<i>'.esc_html__("This invitation is already at the next step, to modify it, you have to send it back (via the button below)",'lab').'</i>' : '').
                '</form></div>
                <div class="lab_invite_row lab_send_manager"><p class="lab_invite_field">'.esc_html__("Click here to validate the request and send it to the budget department.","lab").' :</p><button id="lab_send_manager">'.esc_html__("Send to administration",'lab').'</button></div>';
            } else {
                $invitationStr .= '<div class="lab_invite_field">
                <input '.($invitation->status>1 ? 'disabled' : '').' type="submit" value="'.esc_html__("Save","lab").'">
                </div>'.($invitation->status>1 ? '<i>'.esc_html__("This invitation is already at the next step, to modify it, you have to send it back (via the button below)",'lab').'</i>' : '').
                '</form></div>
                <div class="lab_invite_row lab_send_group_chief"><p class="lab_invite_field">Cliquez ici pour compléter la demande et la transmettre au responsable du groupe :</p><button id="lab_send_group_chief">'.esc_html__("Send to responsible",'lab').'</button></div>';
            }
        }
        else {
            $invitationStr .= '<div class="lab_invite_field">
            <input type="submit" value="'.esc_html__("Confirm","lab").'">
        </div>';
        }
        if (!$newForm) {
            $currentUser = lab_admin_userMetaDatas_get(get_current_user_id());
            $invitationStr .= '
        <div id="lab_invitationComments">
            <h2>Commentaires <i class="fas fa-arrow-up"></i></h2>
                '.lab_inviteComments($token).'
                '.lab_newComments($currentUser,$token).'
        </div><!-- end div lab_invitationComments -->';
        }
    return $invitationStr;
}
function lab_invitations_filters() {
    $out = '<div id="lab_filter">
            <h5>'.esc_html__("Filters",'lab').'</h5>
            <form id="lab_status_filter"><u>'.esc_html__("Status",'lab').' :</u> ';
    foreach ([1,10,20,30] as $status) {
        $out .= "<input type='checkbox' id='lab_filter_status_$status' value='$status'><label for='lab_filter_status_$status'>".lab_invitations_getStatusName($status)."</label><br/>";
    }
    $nextYear = date('Y')+1;
    $years = '';
    while (2020 <= $nextYear) {
        $years .= "<option value=$nextYear>$nextYear</option>";
        $nextYear--;
    }
    $out .= '</form>
    <form id="lab_select_filters">
        <div>
            &nbsp<label for="lab_results_number">'.esc_html__("Year","lab").' : </label>
            <select id="lab_filter_year">
                <option selected value="all">'.esc_html__("All",'lab')."</option>".$years.'
            </select>
        </div>
        <div>    
            &nbsp<label for="lab_results_number">'.esc_html__("Results per page","lab").' : </label>
            <select id="lab_results_number">
                <option selected value="5">5</option>
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="50">50</option>
            </select>
        </div>
    </form>'; 
    $out .= '</div>';
    return $out;
}
function lab_invitations_interface($args) {
    //var_dump(setlocale(LC_ALL,'fr_FR'));
    $param = shortcode_atts(array(
        'view' => 'host' //host, chief or admin 
        ),
        $args, 
        "lab-invite-interface"
    );
    $listInvitationStr = '';
    switch ($param['view']) {
        case 'host':
            $list = lab_invitations_getByHost(get_current_user_id())[1];
            break;
        case 'chief':
            $groups = lab_admin_get_groups_byChief(get_current_user_id());
            if (sizeof($groups) > 0)
            {
                $listInvitationStr .= '<h5>Groupes dont vous êtes le responsable :</h5>';
                $listInvitationStr .= '<select id="lab_groupSelect">';

                foreach ($groups as $g)
                {
                    $listInvitationStr .= '<option value="'.$g->id.'">'.$g->group_name.'</option>';
                }
                $listInvitationStr .='</select>';
                $list = lab_invitations_getByGroup(lab_admin_get_groups_byChief(get_current_user_id())[0]->id)[1];
            }
            else
            {
                $listInvitationStr .= '<h5>Vous n\'êtes responsable d\'aucun groupe</h5>';
                return $listInvitationStr;
                //$list = array();

            }
            break;
        case 'admin':
            $listInvitationStr .= '<h5>'.esc_html__("Preferred Groups",'lab').' :</h5>
            <div id="lab_prefGroupsForm">
                <select id="lab_prefGroupsSelect"><option value="">Sélectionnez un groupe</option></select>
                <button id="lab_addPrefGroup">Ajouter aux préférés</button>
                <p id="lab_group_add_warning"></p>';
            $listInvitationStr .= '<ul id="lab_curr_prefGroups">';
            $listInvitationStr .= lab_invite_prefGroupsList(get_current_user_id());
            $listInvitationStr .='</ul></div>';
            $prefGroups = lab_invitations_getPrefGroups(get_current_user_id());
            if (count($prefGroups)>0) {
                $groups_ids = array();
                foreach ($prefGroups as $g) {
                    array_push($groups_ids, $g->group_id);
                }
            } else {
                $list = array();
            }
            break;
    }
    $listInvitationStr .= lab_invitations_filters();
    //$listInvitationStr .= '<div id="loadingAjaxGif" style="display:none;"><img src="/wp-content/plugins/lab/loading.gif"/></div>';
    $listInvitationStr .= '<div id="loadingAjaxGif"><img src="/wp-content/plugins/lab/loading.gif"/></div>';
    $listInvitationStr .= '<table view="'.$param['view'].'" id="lab_invite_list" class="table">
                            <thead>
                                <tr id="lab_list_header">'
                                    .($param['view']=='admin' ? '<th class="lab_column_name" name="host_group_id">'.esc_html__('Group','lab').'<i class="fas fa-caret-up"></i></th>' : '').
                                    '<th name="guest_id">'.esc_html__("Guest","lab").'</i></th>
                                    '.($param['view']!='host' ? '<th class="lab_column_name" name="host_id">'.esc_html__("Inviting","lab").'<i class="fas fa-caret-up"></i></th>' : '').
                                    '<th class="lab_column_name" name="mission_objective">'.esc_html__("Reason for the mission","lab").'<i class="fas fa-caret-up"></i></th>
                                    <th class="lab_column_name" sel="true" name="start_date" order="asc">'.esc_html__("Arrival date","lab").'<i class="fas fa-caret-up"></i></th>
                                    <th class="lab_column_name" name="end_date">'.esc_html__("Departure date","lab").'<i class="fas fa-caret-up"></i></th>
                                    <th class="lab_column_name" name="status">'.esc_html__("Status","lab").'<i class="fas fa-caret-up"></i></th>
                                    <th class="lab_column_name" name="estimated_cost">'.esc_html__("Estimated budget","lab").'<i class="fas fa-caret-up"></i></th>
                                    <th class="lab_column_name" name="maximum_cost">'.esc_html__("Maximum budget","lab").'<i class="fas fa-caret-up"></i></th>
                                    <th>'.esc_html__("Actions","lab").'</th>
                                </tr>
                            </thead>
                            <tbody id="lab_invitesListBody">';
    
    //$listInvitationStr .= lab_invitations_interface_fromList($list,$param['view']);
    $listInvitationStr .=   '</tbody>
                          </table>';
    $listInvitationStr .=  '<div id="lab_pages">'.lab_invitations_pagination(1,1).'</div><br/><br/>';
    if ($param['view']=='admin') {
        $listInvitationStr .=
        '<h2 id="lab_invite_detail_title">'.esc_html__("Invitation details",'lab').'<i class="fas fa-arrow-up"></i></h2>
        <div id="lab_invite_budget" style="display:none">
            <p><form action="javascript:lab_submitRealCost()">
                <label for="lab_invite_realCost_input">'.esc_html__("Real budget",'lab').' : <b id="lab_invite_realCost"></b></label>
                <input token="" id="lab_invite_realCost_input" type="number" step="0.01" min=0/><b>&nbsp;&euro;</b><br>
                <label for="forward_carbon_footprint">'.esc_html__("Forward carbon footprint","lab").'</label>
                <input type="text" id="forward_carbon_footprint" placeholder="'.esc_html__("Carbon footprint of the forward travel ...","lab").'">0.0</input><br>
                <label for="return_carbon_footprint">'.esc_html__("Return carbon footprint","lab").'</label>
                <input type="text" id="return_carbon_footprint" placeholder="'.esc_html__("Carbon footprint of the return travel ...","lab").'">0.0</input><br>
                <input type="submit" value="Valider">
                <span id="lab_invite_realCost_message"></span>
                </form></p>
        </div> 
        <div style="display:none" id="lab_invite_details">
            <div id="lab_invite_gauche">
                <div id="lab_invite_summary">
                </div>
            </div>
            <div id="lab_invite_droite">
            </div>
        </div>';
    }
    return $listInvitationStr;
}
function lab_invitations_pagination($pages, $currentPage) {
    $out = '<ul id="pagination-digg">';
    $out .= '<li class="page_previous'.($currentPage>1 ? '">' : ' gris">').'« Précédent</li>';
    for ($i=1; $i<=$pages; $i++) {
        $out .= '<li page='.$i.' class="page_number"'.($currentPage!=$i ? ">$i" : " id='active'>$i").'</li>';
    }
    $out .= '<li class="page_next'.($pages>1 && $currentPage<$pages ? '">' : ' gris">').'Suivant »</li>';
    $out .= '</ul>';
    return $out;
}
function lab_invitations_switchLocale($oldLocale) {
    global $currLocale;
    if ($currLocale == 'fr_FR') {
        $currLocale = 'en_GB';
    } else {
        $currLocale = 'fr_FR';
    }
    return $currLocale;
}
function lab_invitations_interface_fromList($list,$view) {
    $listStr = '';
    if (count($list)>0) {
        foreach ($list as $invitation) {
            $guest = lab_invitations_getGuest($invitation->guest_id);
            $host = new LabUser($invitation->host_id);
            $date_arr = date_create_from_format("Y-m-d H:i:s", $invitation->start_date);
            $date_dep = date_create_from_format("Y-m-d H:i:s", $invitation->end_date);
            $listStr .= '<tr>'
                            .($view=='admin' ? '<td>'.lab_group_getById($invitation->host_group_id)->group_name.'</td>' : '').
                            '<td><a href="mailto:'.$guest->email.'">'. $guest->first_name . ' ' . $guest->last_name .'</a></td>'
                            .($view!='host' ? '<td><a href="mailto:'.$host->email.'">'. $host->first_name . ' ' . $host->last_name .'</a></td>':'');
            if(is_numeric($invitation->mission_objective))
            {   
                $listStr .='<td>'. AdminParams::get_param($invitation->mission_objective) .'</td>';
            }
            else
            {
                $listStr .='<td>'. $invitation->mission_objective .'</td>'; 
            }
            $listStr .=    '<td>'. strftime('%d %B %G',$date_arr->getTimestamp()).'</td>
                            <td>'. strftime('%d %B %G',$date_dep->getTimestamp()).'</td>
                            <td>'. lab_invitations_getStatusName($invitation->status) .'</td>
                            <td>'. $invitation->estimated_cost.'</td>
                            <td>'. $invitation->maximum_cost.'</td>'
                            .($view!='admin' ? '<td><a href="/invite/'. $invitation->token.'/">'.esc_html__("Edit",'lab').'</a>' 
                            : '<td><button class="lab_invite_showDetail" token="'.$invitation->token.'">'.esc_html__("Details","lab").'</button>').
                            ($view=='admin'&& $invitation->status>1 ?
                            '<button title="'.esc_html("Click to take over the invitation","lab").'" token="'.$invitation->token.'" class="lab_invite_takeCharge">Gérer</button></td>' : '</td>').
                        '</tr>';
        }
    } else {
        $listStr = "<tr><td colspan=42>".esc_html__("No invitation",'lab')."</td></tr>";
    }
    return $listStr;
}
function lab_invitations_mail($type=1, $guest, $invite) {
    switch ($type) {
        case 1: //Envoi de mail récapitulatif à l'invité lorsqu'il crée sa demande d'invitation
            global $currLocale;
            $currLocale = get_locale();
            $dest = $guest["email"];
            $subj = esc_html__("Your invitation request to I2M",'lab');
            $date = date_create_from_format("Y-m-d H:i:s", $invite["creation_time"]);
            $content = "<p><i>".strftime('%A %d %B %G - %H:%M',$date->getTimestamp())."</i></p>";
            //$content .= "<p>".get_locale()."</p>";
            $content .= "<p>".esc_html__("Votre demande d'invitation a bien été prise en compte",'lab').".<br>".esc_html__("It has been forwarded to your inviting",'lab').".</p>";
            $content .= lab_InviteForm('',$guest,$invite);
            // unload_textdomain("lab");
            // add_filter('locale','lab_invitations_switchLocale',10);
            // myplugin_load_textdomain();
            // $content .= "<h5>Translated version :</h5>";
            // $content .= "<p>".get_locale()."</p>";
            // $content .= "<p>".esc_html__("Votre demande d'invitation a bien été prise en compte",'lab').".<br>".esc_html__("Elle a été transmise à votre invitant",'lab').".</p>";
            // $content .= lab_InviteForm('',$guest,$invite);
            // unload_textdomain("lab");
            // add_filter('locale','lab_invitations_switchLocale',10);
            // myplugin_load_textdomain();
            break;
        case 2:
            $subj = esc_html__("Mission request",'lab');
            $date = date_create_from_format("Y-m-d H:i:s", $invite["creation_time"]);
            $content .= "<p>".esc_html__("Your mission request has been taken into account",'lab').".<br>".esc_html__("It has been forwarded to your responsible",'lab').".</p>";
            $content .= lab_mission_summary($invite);
            break;
        case 5: //Envoi de mail récapitulatif à l'invitant lorsque l'invité a créé une invitation
            $host = new LabUser($invite['host_id']);
            $dest = $host->email;
            $subj = esc_html__("Invitation request to I2M",'lab');
            $date = date_create_from_format("Y-m-d H:i:s", $invite["creation_time"]);
            $content = "<p><i>".strftime('%A %d %B %G - %H:%M',$date->getTimestamp())."</i></p>";
            $content .= "<p>".esc_html__("A request for an invitation to the I2M has been sent to you.",'lab')."<br>"
            .esc_html__("You can modify it by following",'lab')." <a href='".get_site_url()."";
            if($invite["mission_objective"] == "255") {
                $content .= "" . "/invitation/". "";
            }
            else {
                $content .= "" . "/mission/". "";
            }
            $content .= "" . $invite['token']."/'>".esc_html__('this link','lab')."</a>.</p>";
            $content .= lab_InviteForm('',$guest,$invite);
            break;
        case 10: //Envoi du mail au responsable du groupe une fois la demande complétée
            $subj = esc_html__("New invitation request to I2M",'lab');
            $chief = new LabUser(lab_admin_get_chief_byGroup($invite['host_group_id']));
            $dest = $chief->email;
            $date = date_create_from_format("Y-m-d H:i:s", $invite["completion_time"]);
            $content = "<p><i>".strftime('%A %d %B %G - %H:%M',$date->getTimestamp())."</i></p>";
            $content .= "<p>".esc_html__("A request for an invitation to the I2M has been completed.",'lab')."<br>"
            .esc_html__("You can consult it by following",'lab')." <a href='".get_site_url()."";
            if($invite["mission_objective"] == "255") {
                $content .= "" . "/invitation/". "";
            }
            else {
                $content .= "" . "/mission/". "";
            }
            $content .= "" . $invite['token']."/'>".esc_html__('this link','lab')."</a><br>
            et modifier les informations si besoin. Vous pouvez ensuite la valider pour la transmettre au pôle budget.</p>";
            $content .= lab_InviteForm('host',$guest,$invite);
            break;
        default:
            return 'unknown mail type';
            break;
    }
    apply_filters( 'wp_mail_content_type', "text/html" );
    $headers = array('Content-Type: text/html; charset=UTF-8');
    wp_mail($dest,$subj,$content, $headers);
    return $content;
}

function lab_mission_summary($invite) {
    $user = new LabUser($invite['host_id']);

    $travels = lab_mission_load_travels($invite["id"]);

    $chargesList = '<ul>';
    $charges = json_decode($invite['charges']);
    foreach ($charges as $el => $value) {
        $chargesList .= "<li><i>$el : </i>$value €</li>";
    }
    $chargesList .= '</ul>';
    $out = '<div class="lab_invite_field"><h2>'.esc_html__("Récapitulatif de la demande de mission",'lab').' : <i class="fas fa-arrow-up"></i></h2>';
    $out .= '<p><u>'.esc_html__("Informations personnelles",'lab').' :</u></p>
                <ul>
                <li><i>'.esc_html__("Prénom",'lab').' : </i>'.$user->first_name.'</li>
                <li><i>'.esc_html__("Nom",'lab').' : </i>'.$user->last_name.'</li>
                <li><i>'.esc_html__("Email",'lab').' : </i>'.$user->email.'</li>
            </ul>';
    $out .= lab_mission_display_travels($travels);
    $out .= '</div>';
    return $out;
}

function paramToMap($params) {
    $map = array();
    foreach($params as $param) {
        $map[$param->id] = $param->value;
    }
    return $map;
}

function lab_mission_display_travels($travels) {
    $meansOfLocomotion = paramToMap(lab_admin_get_params_meanOfTransport());
    $out .= '<h2>'.esc_html__("Travels",'lab').' :</h2><table class="table">';
    foreach($travels as $travel) {
        $out .= "<tr>";
        $out .= '<td>'.$travel->travel_date;
        $out .= '<td><div class="country-select selected-flag"><div class="flag '.$travel->country_from.'"/></div></td>';
        $out .= '<td>'.$travel->travel_from.'</td>';
        $out .= '<td><div class="country-select selected-flag"><div class="flag '.$travel->country_to.'"/></div></td>';
        $out .= '<td>'.$travel->travel_to.'</td>';
        if ($travel->means_of_locomotion != "") {
            $out .= '<td>'.$meansOfLocomotion[$travel->means_of_locomotion].'</td>';
        }
        else {
            $out .= '<td>&nbsp;</td>';
        }
        $out .= '<td>'.$travel->estimated_cost.' &euro;</td>';
        $out .= '<td>'.$travel->reference.'</td>';
        if ($travel->round_trip == 0) {
            $out .= '<td>'.esc_html__("One way",'lab').'</td>';
            $out .= '<td>&nbsp;</td>';
        }
        else {
            $out .= '<td>'.esc_html__("Return",'lab').'</td>';
            $out .= '<td>'.$travel->travel_datereturn.'</td>';
        }
        $out .= "</tr>";
    }
    $out .= '</table>';
    return $out;
}

function lab_InviteForm($who,$guest,$invite) {
    $host = new LabUser($invite['host_id']);
    $chargesList = '<ul>';
    $charges = json_decode($invite['charges']);
    lab_mission_load_travels($invite["id"]);
    foreach ($charges as $el => $value) {
        $chargesList .= "<li><i>$el : </i>$value €</li>";
    }
    $chargesList .= '</ul>';
    $out = '<p><b>'.esc_html__("Invitation request summary",'lab').' : </b></p>
            <p><u>'.esc_html__("Guest's personal information",'lab').' :</u></p>
                <ul>
                <li><i>'.esc_html__("First name",'lab').' : </i>'.$guest['first_name'].'</li>
                <li><i>'.esc_html__("Last name",'lab').' : </i>'.$guest['last_name'].'</li>
                <li><i>'.esc_html__("Email",'lab').' : </i>'.$guest['email'].'</li>
                <li><i>'.esc_html__("Phone",'lab').' : </i>'.$guest['phone'].'</li>
                <li><i>'.esc_html__("Country",'lab').' : </i>'.$guest['country'].'</li>
            </ul>
            <p><u>'.esc_html__("Context of the invitation",'lab').'</u>
            <ul>
                <li><i>'.esc_html__("Inviting name",'lab').' : </i>'.$host->first_name.' '.$host->last_name.'</li>
                <li><i>'.esc_html__("Mission objective",'lab').' : </i>'.(is_numeric($invite['mission_objective']) ? AdminParams::get_param($invite['mission_objective']) : $invite['mission_objective']).'</li>
                <li><i>'.esc_html__("Need a hostel",'lab').' : </i>'.($invite['needs_hostel'] == 1 ? esc_html__('oui','lab') : esc_html__('non','lab')).'</li>
                <li><i>'.esc_html__("Mean of transport",'lab').' :  </i>
                <ul>
                    <li><i>'.esc_html__("To the I2M",'lab').' : </i>'.$invite['travel_mean_to'].'</li>
                    <li><i>'.esc_html__("From the I2M",'lab').' : </i>'.$invite['travel_mean_from'].'</li>
                </ul></li>
                <li><i>'.esc_html__("Arrival date",'lab').' : </i>'.$invite['start_date'].'</li>
                <li><i>'.esc_html__("Departure date",'lab').' : </i>'.$invite['end_date'].'</li>
                <li><u>'.esc_html__('Costs','lab').' : </u>'.$chargesList.'</li>';
    if($who=='host' || $who=='admin')
    {
        $out .= '<li><i>'.esc_html__("Estimated cost",'lab').' : </i>'.$invite['estimated_cost'].'</li>
                 <li><i>'.esc_html__("Origin of credit",'lab').' : </i>'.(is_numeric($invite['funding_source']) ? AdminParams::get_param($invite['funding_source']) : $invite['funding_source']).'</li>
                 <li><i>'.esc_html__("Research Contract",'lab').' : </i>'.$invite['research_contract'].'</li>';
    }
    $out .= '</ul>';
    return $out;
}
function lab_inviteComments($token) {
    $comments= lab_invitations_getComments(lab_invitations_getByToken($token)->id);
    $out = '<div id="lab_invitation_oldComments">';
    if (count($comments)> 0) {
        foreach ( $comments as $comment) {
            $date = date_create_from_format("Y-m-d H:i:s", $comment->timestamp);
            $out .= "<div class='lab_comment_box'>
                        <p class='lab_comment_author".($comment->author=="System" ? ' auto' : '')."'>$comment->author</p>
                        <p class='lab_comment".(substr($comment->content,0,2)=="¤" ? ' auto' : '' )."'><i>"
                        .strftime('%d %B %G - %H:%M',$date->getTimestamp())."</i><br>"
                        .(substr($comment->content,0,2)=="¤" ? substr($comment->content,2) : $comment->content )."</p>
                    </div>";
        }
    } else {
        $out .= '<p><i>Aucun commentaire pour cette invitation</i></p>';
    }
    $out.='</div>';
    return $out;
}
function lab_newComments($currentUser, $token)
{
    $html =     '<div token="'.$token.'" id="lab_invitation_newComment">
                    <h5>'.esc_html__("New comment",'lab').'</h5>
                    <form action="javascript:lab_submitComment()">
                        <label><i>'.esc_html__("Publish as",'lab')."</i> : <span id='lab_comment_name'>".$currentUser['first_name'].' '.$currentUser['last_name'].'</span></label>
                        <textarea row="1" cols="50" id="lab_comment" placeholder="Comment content..."></textarea>
                        <input type="submit" value="'.esc_html__("Send comment","lab").'">
                    </form>
                </div>';
    return $html;
}
function lab_invitations_getStatusName($status) {
    switch ($status) {
        case 1:
            return "<span style='color:#F75C03' class='lab_infoBulle' title='".esc_html__("This invitation has been created, you can now complete all the information and send it to the group leader for validation.","lab")."'>"
            .esc_html__("Created","lab")."</span>";
        break;
        case 10: 
            return "<span style='color:#00c49f' class='lab_infoBulle' title='".esc_html__("This invitation has been completed, the person in charge can now validate it to send it to the budget department.","lab")."'>"
            .esc_html__("Completed","lab")."</span>";
        break;
        case 20:
            return "<span style='color:#c00900' class='lab_infoBulle' title='".esc_html__("This invitation has been validated and sent to the budget department.","lab")."'>"
            .esc_html__("Validated","lab")."</span>";
        break; 
        case 30:
            return "<span style='color:#289600' class='lab_infoBulle' title='".esc_html__("CThis invitation was taken care of by an administrative staff member of the budget department.","lab")."'>"
            .esc_html__("Taken care of","lab")."</span>";
        break; 
        default:
            # code...
            break;
    }
}
function lab_invite_prefGroupsList($user_id) {
    $prefGroups = lab_invitations_getPrefGroups($user_id);
    if (count($prefGroups)>0) {
        $out = '';
        foreach ($prefGroups as $g){
            $out .= "<li title='$g->group_name' class='lab_prefGroup_element'>$g->acronym <i group_id='$g->group_id' class='fas fa-trash lab_prefGroup_del'></i></li>";
        }
    } else {
        $out = '<li>'.esc_html__("No favorite group found",'lab').'</li>';
    }
    return $out;
}

?>