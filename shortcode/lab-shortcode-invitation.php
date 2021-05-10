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
    $url = $wp->request;
    $host = null;
    $invitationStr = "";
    $missionInformation = "";
    $token='0';
    $isGuest   = false;
    $isAdmin = false;
    $guest = null;
    $invitation = null;
    $isgroupLeader = false;

    $isAdmin = current_user_can( 'manage_options' );
    $groupManagerIds = lab_admin_group_get_groups_of_manager(get_current_user_id());
    $isManager = count($groupManagerIds) > 0;

    if ( isset($param['hostpage']) ) {
        $explode = explode("/",$url);
        $a = null;
        if (count($explode) > 1) {
            $a = explode("/",$url)[1];
        }
        if (!isset($url) && empty($url)) {
            $a = $args["token"];
        }
        //Aucun token, donc l'invitant crée lui-même une nouvelle invitation
        if ( ! isset($a) || empty($a)) { 
            $host = new labUser(get_current_user_id());
        } 
        //Token fournit, récupère les informations existantes
        else {
            $token = $a;

            $invitation     = lab_invitations_getByToken($token);
            if (!isset($invitation)) {
                return esc_html__("Invalid invitation token",'lab');
            }
            $missionInformation .= '<input type="hidden" id="lab_mission_token" value="'.$token.'"/>';
            $missionInformation .= '<input type="hidden" id="lab_mission_id" value="'.$invitation->id.'"/>';
            $budget_manager_ids = lab_group_manager(1);
            
            $charges = json_decode($invitation->charges);
            $guest = lab_invitations_getGuest($invitation->guest_id);
            $missionType = AdminParams::get_param($invitation->mission_objective);

            $host = new labUser($invitation->host_id);
            //Qui modifie, l'invitant ou le responsable ?
            $isgroupLeader = false;
            if (isset($invitation->host_group_id)) {
                $isgroupLeader = lab_admin_group_is_group_leader(get_current_user_id(), $invitation->host_group_id);
            };

            $isGuest   = !$isgroupLeader && !$isManager && $missionType == "Invitation";
            
            if ( $isgroupLeader ) {
                $missionInformation .= '<p><i>'.esc_html__('You can edit this invitation as a group leader','lab').'</i></p>';
                $missionInformation .= '<p><i>'.esc_html__('Invitation status : ','lab').'</i>'.lab_invitations_getStatusName($invitation->status).'</p>';
                
            }
            else if ( get_current_user_id()==$invitation->host_id ) { 
                $missionInformation .= '<p><i>'.esc_html__('You can edit this invitation as a host','lab').'</i></p>';
                $missionInformation .= '<p><i>'.esc_html__('Invitation status : ','lab').'</i>'.lab_invitations_getStatusName($invitation->status).'</p>';
            
            } 
            else if ( $isManager ) {
                $missionInformation .= '<p><i>'.esc_html__('You can edit this invitation as a budget manager','lab').'</i></p>';
                $missionInformation .= '<p><i>'.esc_html__('Invitation status : ','lab').'</i>'.lab_invitations_getStatusName($invitation->status).'</p>';
            }
            else if ( $isAdmin ) {
                $missionInformation .= '<p><i>'.esc_html__('You can edit this invitation as an administrator','lab').'</i></p>';
                $missionInformation .= '<p><i>'.esc_html__('Invitation status : ','lab').'</i>'.lab_invitations_getStatusName($invitation->status).'</p>';
            }
            //possibly the guest
            else if ($isGuest) {
                $missionInformation .= '<p><i>'.esc_html__('You can edit this invitation as a guest','lab').'/i></p>';
            }
            else {
                die(esc_html__('You cannot edit this invitation','lab'));
            }
        }
    } else {
        $missionInformation = "<h3>\$param['hostpage']".$param['hostpage']."</h3>";
        $host = isset(explode("/",$url)[1]) ? new labUser(lab_profile_getID(explode("/",$url)[1])) : 0 ;
        if ($host == 0) {
            $host = new labUser(get_current_user_id());
        }
    }
    $newForm = $token=='0'; //Le formulaire est-il nouveau ? Si non, remplit les champs avec les infos existantes
    $invitationStr = '<div id="missionForm" hostForm='.$param['hostpage'].' token="'.(($param['hostpage'] && strlen($token)>1) ? $token : '').'" newForm='.$newForm.'>';
    $invitationStr .= '<h2>'.esc_html__("Form","lab").'<i class="fas fa-arrow-up"></i></h2>'.$missionInformation;
    
    if (!$isGuest) {
        $invitationStr .= '
            <!-- <form action="javascript:formAction()"> -->
            <h3>'.esc_html__("Personnal informations","lab").'</h3>
            <div class="lab_invite_field">
            <label for="lab_hostname">'.esc_html__("Host name","lab")." : ".'</label>';

            if ($isAdmin || $isManager){
                $invitationStr .= '           
                <input type="text" required id="lab_hostname" name="lab_hostname" host_id="'.($host==null ? '' : $host->id.'" value="'.$host->first_name.' '.$host->last_name).'"/>';

            }
            else {
                $invitationStr .= '
                <h6 id="lab_hostname" name="lab_hostname" host_id="'.($host==null ? '' : $host->id.'" >'.$host->first_name.' '.$host->last_name).'</h6>';
            }
            $invitationStr .=    '</div><div class="lab_invite_row_left">';
            $invitationStr .= mission_display_userGroup($host->id, $invitation);
            $invitationStr .= mission_user_funding($host->id, $invitation, $isManager);
            $invitationStr .= '</div>
            <div class="lab_invite_row">
            <div class="lab_invite_field">
                <label for="lab_mission">'.esc_html__("Reason for the mission","lab")." : ". '<span class="lab_form_required_star"/></label>
                <select id="lab_mission" name="lab_mission">';
        foreach(AdminParams::get_params_fromId(AdminParams::PARAMS_MISSION_ID) as $missionparam)
        {
            $selectedGroup = "";
            if(isset($invitation)) {
                $selectedGroup = ($invitation->mission_objective==$missionparam->id)?'selected="selected"':"";
            } 
            else {
                $selectedGroup = ($missionparam->value == "Mission" ? 'selected="selected"':"");
            }
            $invitationStr .= '<option value="'.$missionparam->id.'" '.$selectedGroup.'>'.esc_html__($missionparam->value,"lab").'</option>';
        }
        $invitationStr .= '</select></div>
            <div class="lab_invite_field">
                &nbsp;<label for="lab_no_charge_mission">
                <input type="checkbox" id="lab_no_charge_mission"';

        if($param['hostpage'] && $invitation && $invitation->no_charge == 1)
        {
            $invitationStr .= 'checked';
        }
        $invitationStr .= '>'.esc_html__("No charge mission","lab").' </label>
            
            </div>
        </div>';

    }
    else {
        $invitationStr .= '<h3>'.esc_html__("Invited by","lab").' : '.$host->first_name.' '.$host->last_name.'</h3><br>'.$host->email."<br>";
        $invitationStr .= '<div class="lab_invite_field">';
        $invitationStr .= '</div>';
        $invitationStr .= '<input type="hidden" id="lab_mission" value="'.($newForm ? '' : $invitation->mission_objective).'">';
    }
    
    $invitationStr .= '
        <div id="inviteDiv">
            <hr>
            <h3>'.esc_html__("Guest Informations","lab").'</h3>
            <div class="lab_invite_field">
                <label for="lab_email">'.esc_html__("Email","lab").'<span class="lab_form_required_star"> *</span></label>
                <input type="email" required id="lab_email" guest_id="" name="lab_email"value="'.getGuestValue($newForm, $guest,'email').'">
            </div>
            <div class="lab_invite_row" id="lab_fullname">
                <div class="lab_invite_field">
                    <label for="lab_firstname">'.esc_html__("First name","lab").'<span class="lab_form_required_star"> *</span></label>
                    <input type="text" required id="lab_firstname" name="lab_firstname" guest_id="'.getGuestValue($newForm, $guest,'id').'" value="'.getGuestValue($newForm, $guest,'first_name').'">
                </div>
                <div class="lab_invite_field">
                    <label for="lab_lastname">'.esc_html__("Last name","lab").'<span class="lab_form_required_star"> *</span></label>
                    <input type="text" required id="lab_lastname" name="lab_lastname" value="'.getGuestValue($newForm, $guest,'last_name').'">
                </div>
            </div>
            <div id="lab_phone_country">
                <div class="lab_invite_field">
                    <label for="lab_phone">'.esc_html__("Phone Number","lab").'</label>
                    <input type="tel" id="lab_phone" phoneval="'.getGuestValue($newForm, $guest,'phone').'">
                </div>
                <div class="lab_invite_field">
                    <label for="guest_language">'.esc_html__("Language","lab").'<span class="lab_form_required_star"> *</span></label>
                    <input type="text" required id="guest_language" name="guest_language" countryCode="'.getGuestValue($newForm, $guest,'language').'">
                </div>
                <div class="lab_invite_row" id="lab_residence">
                    <div class="lab_invite_field">
                        <label for="residence_city">'.esc_html__("City of residence","lab").'</label>
                        <input type="text" required id="residence_city" name="residence_city" value="'.getGuestValue($newForm, $guest,'residence_city').'">
                    </div>
                    <div class="lab_invite_field">
                        <label for="residence_country">'.esc_html__("Country of residence","lab").'</label>
                        <input type="text" required id="residence_country" name="residence_country" countryCode="'.getGuestValue($newForm, $guest,'residence_country').'">
                    </div>
                </div>
            </div>
        </div><!-- end invite div -->
        <hr>
        <h3>'.esc_html__("Hostel","lab").'</h3>
        <div class="lab_invite_row">
                    <label for="lab_hostel">
                    <input type="checkbox" id="lab_hostel" name="lab_hostel" ';

            if($param['hostpage'] && $invitation && $invitation->needs_hostel == 1)
            {
                $invitationStr .= 'checked';
            }
                
            $invitationStr .= '>'.esc_html__("Need a hostel","lab").'</label>
                <label for="lab_mission_hostel_night">'.esc_html__("Number of night(s)", "lab").'</label>
                <input type="number" id="lab_mission_hostel_night" value="'.(!$newForm?$invitation->hostel_night:1).'">
                <label for="lab_mission_hostel_cost">'.esc_html__("Estimated cost (€)","lab").'</label>
                <input type="text" id="lab_mission_hostel_cost" value="'.(!$newForm?$invitation->hostel_cost:0).'"></div>
        </div>
        <hr>
        <h3>'.esc_html__("Journeys","lab").'</h3>
        <div id="lab_mission_mean_travel">
            <input type="hidden" id="lab_mission_travels" value="">
            <table id="lab_mission_travels_table" class="table">
                <thead>
                    <td colspan="2">'.esc_html__("Departure date","lab").'</td>
                    <td colspan="3">'.esc_html__("From","lab").'</td>
                    <td colspan="3">'.esc_html__("To","lab").'</td>
                    <td>'.esc_html__("Mean","lab").'</td>
                    <td>'.esc_html__("Cost","lab").'</td>
                    <td>'.esc_html__("Ref","lab").'</td>
                    <td>'.esc_html__("RT","lab").'</td>
                    <td colspan="2">'.esc_html__("Return date if RT","lab").'</td>
                    <td>'.esc_html__("Loyalty card number","lab").'</td>
                    <td>'.esc_html__("Expiry date","lab").'</td>
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
                <input type="text" id="lab_mission_edit_travel_div_stationFrom" value="" placeholder = '.esc_html__("Station from","lab").'>
                <br/>
                <label for="lab_mission_edit_travel_div_countryTo">'.esc_html__("City arrival","lab").'</label>
                <input type="text" id="lab_mission_edit_travel_div_countryTo"  countryCode="FR">
                <input type="text" id="lab_mission_edit_travel_div_cityTo" value="">
                <input type="text" id="lab_mission_edit_travel_div_stationTo" value="" placeholder = '.esc_html__("Station to","lab").'>
                <br/>
                <label for="lab_mission_edit_travel_div_mean">'.esc_html__("Mean of transport ","lab").'</label>';
                $invitationStr .= lab_html_select_str("lab_mission_edit_travel_div_mean", "lab_mission_edit_travel_div_mean", "", "lab_admin_get_params_meanOfTransport", null, array("value"=>"0","label"=>"None"), "");
                $invitationStr .=
                '<input type="text" id="lab_mission_edit_travel_div_company" value="" placeholder='.esc_html__("Travel company","lab").'>
                <br/>
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
                <input type="text" id="lab_mission_edit_travel_div_carbon_footprint" ><br/>
                <label for="lab_mission_edit_travel_div_loyalty_card_number">'.esc_html__("Loyalty card number", "lab").'</label>
                <input type="text" id="lab_mission_edit_travel_div_loyalty_card_number" ><br/>
                <label for="lab_mission_edit_travel_div_loyalty_card_expiry_date">'.esc_html__("Loyalty card expiry date", "lab").'</label>
                <input type="date" class="datechk" placeholder="yyyy-mm-dd" id="lab_mission_edit_travel_div_loyalty_card_expiry_date">
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
                <p>'.esc_html__("(e.g. your loyalty card numbers to be used when booking your trips + the expiry date if required)",'lab').'</p>
        </div><hr>';
        }
        $a = lab_admin_group_is_manager(get_current_user_id(), 1);
        $b = lab_admin_group_is_manager(get_current_user_id(), 2);
        $c = lab_admin_group_is_manager(get_current_user_id(), 3);
        if ($c > 0 || $a > 0 || $b > 0) {//Affiche les champs supplémentaires, pour les responsables.
            $invitationStr .= '<h3>'.esc_html__("Leader fields : ","lab").'</h3>
            <div class="lab_invite_row_left">';                    
            $invitationStr .= '<div class="lab_invite_field"><label for="lab_mission_fund_origin">'.esc_html__("Funds","lab").'<span class="lab_form_required_star"/></label>';
            $invitationStr .= lab_html_select_str("lab_mission_fund_origin", "lab_mission_fund_origin", "", "lab_admin_budget_funds", null, array("value"=>"0","label"=>"None"), ($newForm ? '' : $invitation->funding_source));
            $invitationStr .= '</div>
            </div>
            <div class="lab_invite_row">
                <div class="lab_invite_field">
                    <label for="lab_estimated_cost">'.esc_html__("Estimated cost (€)","lab").'</label>
                    <input type="text" id="lab_estimated_cost" value="'.(!$newForm ? $invitation->estimated_cost : '').'">
                </div>
                <div class="lab_invite_field">
                    <label for="lab_maximum_cost">'.esc_html__("Maximum cost (€)","lab").'</label>
                    <input type="text" id="lab_maximum_cost" value="'.(!$newForm ? $invitation->maximum_cost : '').'">
                    <p>'.esc_html__("To be filled in by the person in charge: maximum budget allocated to this invitation ","lab").'</p>
                </div>
            </div>';
            /*
            if ($isgroupLeader) {
                $invitationStr .= '<div class="lab_invite_field">
                <input '.($invitation->status>10 ? 'disabled' : '').' type="submit" value="'.esc_html__("Save","lab").'">
                </div>'.($invitation->status>10 ? '<i>'.esc_html__("This invitation is already in the next step, to modify it, you must resend it (via the button below)",'lab').'</i>' : '').
                '</div>
                <div class="lab_invite_row lab_send_manager"><p class="lab_invite_field">Cliquez ici pour valider la demande et la transmettre au pôle budget :</p><button id="lab_send_manager">'.esc_html__("Send to administration",'lab').'</button></div>';
            } else {
                $invitationStr .= '<div class="lab_invite_field">
                <input '.($invitation && $invitation->status>1 ? 'disabled' : '').' type="submit" value="'.esc_html__("Save","lab").'">
                </div>'.($invitation && $invitation->status>1 ? '<i>'.esc_html__("This invitation is already in the next step, to modify it, you must resend it (via the button below)",'lab').'</i>' : '').
                '</div>
                <div class="lab_invite_row lab_send_group_chief"><p class="lab_invite_field">Cliquez ici pour compléter la demande et la transmettre au responsable du groupe :</p><button id="lab_mission_send_group_leader">'.esc_html__("Send to responsible",'lab').'</button></div>';
            }
            $invitationStr .= '-->';
            //*/
            $invitationStr .= '<div class="lab_invite_row_right"><button id="lab_mission_save" type="button" class="btn btn-primary">'.esc_html__("Update",'lab').'</button>&nbsp&nbsp';
            $managerType = 0;
            $budget_manager_ids = lab_group_manager(1);
            $group_leader_ids = lab_group_manager(2);
            $substitute_ids = lab_group_manager(3);
            foreach($budget_manager_ids as $bm) {
                if (get_current_user_id() == $bm) {
                    $managerType = 1;
                }
            }
            if($managerType == 0) {
                foreach($group_leader_ids as $gl) {
                    if (get_current_user_id() == $gl) {
                        $managerType = 2;
                    }
                }
                foreach($substitute_ids as $s) {
                    if (get_current_user_id() == $gl) {
                        $managerType = 3;
                    }
                }
            }
            // if group leader
            if($managerType == 2 || $managerType == 3) {
                $invitationStr .= '<button id="lab_mission_validate" type="button" class="btn btn-success">'.esc_html__("Validate", "lab").'</button>&nbsp&nbsp
                                   <button id="lab_mission_refuse" type="button" class="btn btn-danger">'.esc_html__("Refuse","lab").'</button>';
            }
            // if administrative manager
            else if($managerType == 1) {
                $invitationStr .= '<button id="lab_mission_tic" type="button" class="btn btn-info">'.esc_html__("Take in charge","lab").'</button>&nbsp&nbsp
                                   <button id="lab_mission_complete" type="button" class="btn btn-success">'.esc_html__("Complete","lab").'</button>';
            }
            else {
                $invitationStr .= '<button id="lab_mission_cancel" type="button" class="btn btn-warning">'.esc_html__("Cancel","lab").'</button>';
            }
            $invitationStr .= '</div>';                                
        }
        else if($param['hostpage']) {

            if (!$newForm) {
                $invitationStr .= '<div class="lab_invite_row_right"><button id="lab_mission_save" type="button" class="btn btn-primary">'.esc_html__("Update",'lab').'</button>&nbsp&nbsp';
                $invitationStr .= '<button id="lab_mission_cancel" type="button" class="btn btn-warning">'.esc_html__("Cancel","lab").'</button>';
                $invitationStr .= '</div>';
            }
            else {
                $invitationStr .= '<div class="lab_invite_row_right"><button id="lab_mission_save" type="button" class="btn btn-primary">'.esc_html__("Save",'lab').'</button>';
                $invitationStr .= '</div>';
            }
        }
        else {
            $invitationStr .= '<div class="lab_invite_field">
            <button id="lab_mission_submit">'.esc_html__("Submit","lab").'</button>
        </div>';
        }
        if (!$newForm) {
            $currentUser = lab_admin_userMetaDatas_get(get_current_user_id());
            $invitationStr .= '<div id="lab_invitationComments"><h2>'.esc_html__("Comments","lab").' <i class="fas fa-arrow-up"></i></h2>'.lab_inviteComments($invitation->id);
            if(!$isGuest) {
                $invitationStr .= lab_newComments($currentUser,$token);
            }
            $invitationStr .= '</div><!-- end div lab_invitationComments -->';
        }
    return $invitationStr;
}
/**
 * Display user funding option by default it's groups, but can be an ANR, or different kind of contract
 *
 * @param [int] $userId
 * @param [sql result] $mission from db
 * @param [inbooleant] $isBudgetManager, can be edited by the budget manager
 * @return string html
 */
function mission_user_funding($userId, $mission, $isBudgetManager) {
    $html = "";
    $contracts = null;
    if ($isBudgetManager) {
        $contracts = lab_admin_contract_get_all_contracts();
    }
    else {
        $contracts = lab_admin_contract_get_contracts_by_user($userId);
    }
    $html = '<div class="lab_invite_field"><label for="lab_mission_user_funding">'.esc_html__("Fundings","lab").' :</label>';
    if (count($contracts) > 0) {
        $html .= '<select id="lab_mission_user_funding">';
        $selectedFunding = $mission != null ? $mission->funding: '';
        $select = $mission != null && $selectedFunding == 0 ? " selected": '';
        $html .= '<option value="0"'.$select.'>'.esc_html__("Group fundings","lab").'</option>';
        // @TODO changer le funding
        foreach($contracts as $contract) {
            $select = "";
            if ($selectedFunding) {
                if ($selectedFunding == $contract->id) {
                    $select = " selected";
                }
            }
            $html .= '<option value="'.$contract->id.'"'.$select.'>'.$contract->contract_type." ".$contract->name.'</option>';
        }
        $html .= '</select>';
    }
    else {
        $html .= '<input type="hidden" id="lab_mission_user_funding" value="0"><span id="lab_mission_group_funding">';
        $html .= esc_html__("Group fundings","lab");
        $html .= "<span>";
    }
    $html .= '</div>';
    return $html;
}
/**
 * Display user group selection, 
 *
 * @param [int] $userId
 * @param [sql result] $mission from db
 * @return string html
 */
function mission_display_userGroup($userId, $mission) {
    $newForm = $mission == null;
    $invitationStr = '<div class="lab_invite_field"><label for="lab_group_name">'.esc_html__("Group","lab").' :</label>';
    $groups = lab_admin_group_by_user($userId);
    //var_dump($groups);
    if (count($groups) == 1) {
        if($newForm) {
            $invitationStr .= '<input type="hidden" id="lab_group_name" value="'.$groups[0]->id.'">';
        }
        else {
            $invitationStr .= '<input type="hidden" id="lab_group_name" value="'.$mission->host_group_id.'">';
        }
        $invitationStr .= $groups[0]->name;
    }
    else {
        $invitationStr .= '<select id="lab_group_name">';
        $selectedGroup = !$newForm ? $mission->host_group_id: '';
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
        $invitationStr .= '</select>';
    }
    $invitationStr .= '</div>';
    return $invitationStr;
}
function getGuestValue($newForm, $guest, $field) {
    if ($newForm || $guest == null) {
        return "";
    }
    else if ($guest != null)
    {
        return $guest->$field;
    }

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
                                    '.($param['view']!='host' ? '<th class="lab_column_name" name="host_id">'.esc_html__("Host","lab").'<i class="fas fa-caret-up"></i></th>' : '').
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
    $content = "";
    $dest = "";
    switch ($type) {
        case 1: //Envoi de mail récapitulatif à l'invité lorsqu'il crée sa demande d'invitation
            global $currLocale;
            $currLocale = get_locale();
            $dest = $guest["email"];
            $subj = esc_html__("Your invitation request to I2M",'lab');
            $date = date_create_from_format("Y-m-d H:i:s", $invite["creation_time"]);
            $content .= "<p><i>".strftime('%A %d %B %G - %H:%M',$date->getTimestamp())."</i></p>";
            //$content .= "<p>".get_locale()."</p>";
            $content .= "<p>".esc_html__("Your invitation request has been taken into account",'lab').".<br>".esc_html__("It has been forwarded to your host",'lab').".</p>";
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
            $host = new LabUser($invite['host_id']);
            $dest = $host->email;
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
            $chief = new LabUser(lab_admin_get_manager_byGroup_andType($invite['host_group_id'], 2));
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
    if($charges)
    {
        foreach ($charges as $el => $value) {
            $chargesList .= "<li><i>$el : </i>$value €</li>";
        }
    }
    $chargesList .= '</ul>';
    $out = '<div class="lab_invite_field"><h2>'.esc_html__("Summary of the mission request",'lab').' : <i class="fas fa-arrow-up"></i></h2>';
    $out .= '<p><u>'.esc_html__("Personnal informations",'lab').' :</u></p>
                <ul>
                <li><i>'.esc_html__("First name",'lab').' : </i>'.$user->first_name.'</li>
                <li><i>'.esc_html__("Last name",'lab').' : </i>'.$user->last_name.'</li>
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
    $out = '<h2>'.esc_html__("Travels",'lab').' :</h2><table class="table">';
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
                <li><i>'.esc_html__("Host name",'lab').' : </i>'.$host->first_name.' '.$host->last_name.'</li>
                <li><i>'.esc_html__("Mission objective",'lab').' : </i>'.(is_numeric($invite['mission_objective']) ? AdminParams::get_param($invite['mission_objective']) : $invite['mission_objective']).'</li>
                <li><i>'.esc_html__("Need a hostel",'lab').' : </i>'.($invite['needs_hostel'] == 1 ? esc_html__('yes','lab') : esc_html__('no','lab')).'</li>
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
function lab_inviteComments($missionId) {
    $comments= lab_invitations_getComments($missionId);
    $out = '<div id="lab_invitation_oldComments">';
    if (count($comments)> 0) {
        foreach ( $comments as $comment) {
            $date = date_create_from_format("Y-m-d H:i:s", $comment->timestamp);
            $author = "";
            if($comment->author_type == 0) {
                $author = "System";
            } else if($comment->author_type == 1) {
                $author = lab_invitations_getGuest($comment->author_id)->first_name . " " . lab_invitations_getGuest($comment->author_id)->last_name;
            } else {
                $author = lab_admin_userMetaDatas_get($comment->author_id)["first_name"] . " " . lab_admin_userMetaDatas_get($comment->author_id)["last_name"];
            }
            $out .= "<div class='lab_comment_box'>
                        <p class='lab_comment_author".($author=="System" ? ' auto' : '')."'>$author</p>
                        <p class='lab_comment".(substr($comment->content,0,2)=="¤" ? ' auto' : '' )."'><i>"
                        .strftime('%d %B %G - %H:%M',$date->getTimestamp())."</i><br>"
                        .(substr($comment->content,0,2)=="¤" ? substr($comment->content,2) : $comment->content )."</p>
                    </div>";
        }
    } else {
        $out .= '<p><i>'.esc_html__("No comment","lab").'</i></p>';
    }
    $out.='</div>';
    return $out;
}
function lab_newComments($currentUser, $token)
{
    $html =     '<div token="'.$token.'" id="lab_invitation_newComment">
                    <h5>'.esc_html__("New comment",'lab').'</h5>
                    <form id="form_new_comment">
                        <label><i>'.esc_html__("Publish as",'lab')."</i> : <span id='lab_comment_name' user_id='".get_current_user_id()."'>".$currentUser['first_name'].' '.$currentUser['last_name'].'</span></label>
                        <textarea row="1" cols="50" id="lab_comment" placeholder="Comment content..."></textarea>
                        <input id="button_add_comment" type="button" value="'.esc_html__("Send comment","lab").'">
                    </form>
                </div>';
    return $html;
}
function lab_invitations_getStatusName($status) {
    $statusSlug = AdminParams::get_param_slug($status);
    if ($statusSlug == "msn") {
        return "<span style='color:#F75C03' class='lab_infoBulle' title='".esc_html__("This invitation has been created, you can now complete all the information and send it to the group leader for validation.","lab")."'>"
            .esc_html__("Created","lab")."</span>";
    }
    else if ($statusSlug == "mswgl") {
        return "<span style='color:yellow' class='lab_infoBulle' title='".esc_html__("This invitation has been completed, the person in charge can now validate it to send it to the budget department.","lab")."'>"
            .esc_html__("Waiting group leader","lab")."</span>";
    }
    else if ($statusSlug == "mswgm") {
        return "<span style='color:yellow' class='lab_infoBulle' title='".esc_html__("This invitation was taken care of by an administrative staff member of the budget department.","lab")."'>"
            .esc_html__("Taken care of","lab")."</span>";
    }
    else if ($statusSlug == "msc") {
        return "<span style='color:cyan' class='lab_infoBulle' title='".esc_html__("This invitation is complete.","lab")."'>"
            .esc_html__("Complete","lab")."</span>";
    }
    else if ($statusSlug == "msvbgl") {
        return "<span style='color:green' class='lab_infoBulle' title='".esc_html__("This invitation is complete.","lab")."'>"
            .esc_html__("Validated by group leader","lab")."</span>";
    }
    else if ($statusSlug == "msrbgl") {
        return "<span style='color:red' class='lab_infoBulle' title='".esc_html__("This invitation is complete.","lab")."'>"
            .esc_html__("Refused by group leader","lab")."</span>";
    }
    else if ($statusSlug == "msca") {
        return "<span style='color:grey' class='lab_infoBulle' title='".esc_html__("This invitation is complete.","lab")."'>"
            .esc_html__("Cancelled","lab")."</span>";
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