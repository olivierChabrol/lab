<?php
/*
 * File Name: lab-shortcode-directory.php
 * Description: shortcode pour générer un annuaire
 * Authors: Ivan Ivanov, Lucas Urgenti
 * Version: 0.5
*/

function lab_invitation($args) { 
    $param = shortcode_atts(array(
        'hostpage' => 0 //0 pour invité, 1 pour invitant
        ),
        $args, 
        "lab-invitation"
    );
    global $wp;
    $url = $wp->request;
    if ( $param['hostpage'] ) {
        if ( ! isset(explode("/",$url)[1])) {
            $token='0';
        } else {
            $token = explode("/",$url)[1];
            $invitation=lab_invitations_getByToken($token);
            if (!isset($invitation)) {
                return esc_html__("Token d'invitation invalide",'lab');
            }
            $guest = lab_invitations_getGuest($invitation->guest_id);
            $host = new labUser($invitation->host_id);
        }
    } else {
        $host = isset(explode("/",$url)[1]) ? new labUser(lab_profile_getID(explode("/",$url)[1])) : 0 ;
    }
    $newForm = (!$param['hostpage'] || $token=='0') ? 1 : 0 ;
    $invitationStr = '
    <div id="invitationForm" hostForm='.$param['hostpage'].' token="'.(($param['hostpage'] && strlen($token)>1) ? $token : '').'" newForm='.$newForm.'>
        <h3>'.esc_html__("Informations personnelles","lab").'</h3>
        <form action="javascript:invitation_submit()">
        <div class="lab_invite_row" id="lab_fullname">
            <div class="lab_invite_field">
                <label for="lab_firstname">'.esc_html__("Prénom","lab").'</label>
                <input type="text" required id="lab_firstname" name="lab_firstname" guest_id="'.(!$newForm ? $guest->id : '').'" value="'.(!$newForm ? $guest->first_name : '').'">
            </div>
            <div class="lab_invite_field">
                <label for="lab_lastname">'.esc_html__("Nom","lab").'</label>
                <input type="text" required id="lab_lastname" name="lab_lastname" value="'.(!$newForm ? $guest->last_name : '').'">
            </div>
        </div>
        <div class="lab_invite_field">
            <label for="lab_email">'.esc_html__("Email","lab").'</label>
            <input type="email" required id="lab_email" name="lab_email"value="'.(!$newForm ? $guest->email : '').'">
        </div>
        <div id="lab_phone_country">
            <div class="lab_invite_field">
                <label for="lab_phone">'.esc_html__("Numéro de téléphone","lab").'</label>
                <input type="tel" required id="lab_phone" phoneval="'.(!$newForm ? $guest->phone : '').'">
            </div>
            <div class="lab_invite_field">
                <label for="lab_country">'.esc_html__("Pays","lab").'</label>
                <input type="text" required id="lab_country" name="lab_country" countryCode="'.(!$newForm ? $guest->country : '').'">
            </div>
        </div>
        <div class="lab_invite_field">
            <label for="lab_hostname">'.esc_html__("Nom de l'invitant","lab").'</label>
            <input type="text" required id="lab_hostname" name="lab_hostname" host_id='.($host==0 ? '': $host->id).' value="'.($host==0 ? '' : $host->first_name.' '.$host->last_name).'">
        </div>
        <div class="lab_invite_field">
            <label for="lab_mission">'.esc_html__("Objectif de mission","lab").'</label>
            <select id="lab_mission" name="lab_mission">
                <option value="">'.esc_html__("Choisissez une option","lab").'</option>
                <option value="seminar">'.esc_html__("Séminaire","lab").'</option>
                <option value="other">'.esc_html__("Autre","lab").'</option>
            </select>
            <input hidden type="text" id="lab_mission_other" value="'.(!$newForm ? $invitation->mission_objective : '').'">
            <p style="display:none" id="lab_mission_other_desc">'.esc_html__("Précisez la nature de votre mission ici.","lab").'</p>
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
            <label for="lab_hostel">'.esc_html__("Besoin d'un hôtel","lab").'</label>
        </div>
        <hr>
        <h3>'.esc_html__("Moyen de transport","lab").'</h3>
        <div id="lab_mean_travel" class="lab_invite_row">
            <div class="lab_invite_field">
                <label for="lab_transport_to">'.esc_html__("Vers l'I2M","lab").'</label>
                <select id="lab_transport_to" name="lab_transport_to" value="'.(!$newForm ? $invitation->travel_mean_to : '').'">
                <option value="">'.esc_html__("Choisissez une option","lab").'</option>
                    <option value="car">'.esc_html__("Voiture","lab").'</option>
                    <option value="train">'.esc_html__("Train","lab").'</option>
                    <option value="plane">'.esc_html__("Avion","lab").'</option>
                    <option value="bus">'.esc_html__("Car","lab").'</option>
                    <option value="none">'.esc_html__("Aucun","lab").'</option>
                    <option value="other">'.esc_html__("Autre","lab").'</option>
                </select>
                <input hidden type="text" id="lab_transport_to_other" value="'.(!$newForm ? $invitation->travel_mean_to : '').'">
                <p>'.esc_html__("Moyen de transport depuis votre domicile vers notre laboratoire","lab").'</p>
            </div>
            <div class="lab_invite_field">
                <label for="lab_transport_from">'.esc_html__("Depuis l'I2M","lab").'</label>
                <select id="lab_transport_from" name="lab_transport_from" value="'.(!$newForm ? $invitation->travel_mean_from : '').'">
                    <option value="">'.esc_html__("Choisissez une option","lab").'</option>
                    <option value="car">'.esc_html__("Voiture","lab").'</option>
                    <option value="train">'.esc_html__("Train","lab").'</option>
                    <option value="plane">'.esc_html__("Avion","lab").'</option>
                    <option value="bus">'.esc_html__("Car","lab").'</option>
                    <option value="none">'.esc_html__("Aucun","lab").'</option>
                    <option value="other">'.esc_html__("Autre","lab").'</option>
                </select>
                <input hidden type="text" id="lab_transport_from_other" value="'.(!$newForm ? $invitation->travel_mean_from : '').'">
                <p>'.esc_html__("Moyen de transport depuis notre laboratoire vers votre domicile","lab").'</p>
            </div>
        </div> 
        <div id="lab_date" class="lab_invite_row">
            <div class="lab_invite_field" >
                <label for="lab_arrival">'.esc_html__("Date d'arrivée","lab").'</label>
                <input type="date" id="lab_arrival" name="lab_arrival" value="'.(!$newForm ? explode(" ",$invitation->start_date)[0] : '').'">
                <input type="time" step="60" id="lab_arrival_time" name="lab_arrival_time" value="'.(!$newForm ? explode(" ",$invitation->start_date)[1] : '').'">
                <p>'.esc_html__("Précisez la date de réservation du voyage, l'heure est quand vous quittez votre domicile","lab").'</p>
            </div>
            <div class="lab_invite_field">
                <label for="lab_departure">'.esc_html__("Date de départ","lab").'</label>
                <input type="date" id="lab_departure" name="lab_departure" value="'.(!$newForm ? explode(" ",$invitation->end_date)[0] : '').'">
                <input type="time" step="60" id="lab_departure_time" name="lab_departure_time" value="'.(!$newForm ? explode(" ",$invitation->end_date)[1] : '').'">
                <p>'.esc_html__("Précisez la date de réservation du voyage, l'heure est quand vous quittez le labo","lab").'</p>
            </div>
        </div>
        <hr>';
        if ( $param["hostpage"] ) {
            $invitationStr .=

            '<h3>'.esc_html__("Champs pour l'invitant : ","lab").'</h3>
            <div required class="lab_invite_field">
                <label for="lab_group_name">'.esc_html__("Nom du groupe","lab").'</label>
                <select id="lab_group_name" name="lab_group_name">';
            foreach ($host->groups as $g)
            {
                $invitationStr .= '<option value="'.$g->id.'">'.$g->group_name.'</option>';
            }

            $invitationStr .=
                '</select>
            </div>
            <div id="lab_inviting_fields" class="lab_invite_row">
                <div class="lab_invite_field">
                    <label for="lab_estimated_cost">'.esc_html__("Coût estimé (en €)","lab").'</label>
                    <input type="text" id="lab_estimated_cost" value="'.(!$newForm ? $invitation->estimated_cost : '').'">
                </div>
                <div class="lab_invite_field">
                    <label for="lab_credit">'.esc_html__("Origine des crédits","lab").'</label>
                    <select required id="lab_credit" name="lab_credit">
                        <option value="">'.esc_html__("Choisissez une option","lab").'</option>
                        <option value="cnrs">'.esc_html__("CNRS","lab").'</option>
                        <option value="amu">'.esc_html__("AMU","lab").'</option>
                        <option value="other">'.esc_html__("Autre","lab").'</option>
                    </select>
                    <input hidden type="text" id="lab_credit_other" value="'.(!$newForm ? $invitation->funding_source : '').'">
                    <p style="display:none" id="lab_credit_other_desc">'.esc_html__("Précisez l'origine de crédit ici.","lab").'</p>
                </div>
            </div>';
        }
        $invitationStr .=
        '<div>
            <input type="submit" value="'.esc_html__("Valider","lab").'">
        </div>
    </div>';
    return $invitationStr;
}

function lab_invitations_interface($args) {
    $param = shortcode_atts(array(
        'view' => 'host' //host, chief or admin 
        ),
        $args, 
        "lab-invite-interface"
    );
    $listInvitationStr = '';
    switch ($param['view']) {
        case 'host':
            $list = lab_invitations_getByHost(get_current_user_id());
            break;
        case 'chief':
            $listInvitationStr .= '<h3>Groupes dont vous êtes le chef :</h3><select>';
            foreach (lab_admin_get_groups_byChief(get_current_user_id()) as $g)
                {
                    $listInvitationStr .= '<option value="'.$g->id.'">'.$g->group_name.'</option>';
                }
            $listInvitationStr .='</select>';
            $list = lab_invitations_getByGroup(lab_admin_get_groups_byChief(get_current_user_id())[0]->id);
            break;
        case 'admin':
            $listInvitationStr .= '<h3>Groupes Préférés :</h3><select>';
            foreach (lab_invitations_getPrefGroups(get_current_user_id()) as $g)
                {
                    $listInvitationStr .= '<option value="'.$g->id.'">'.$g->group_name.'</option>';
                }
            $listInvitationStr .='</select>';
            $list = lab_invitations_getByGroup(lab_invitations_getPrefGroups(get_current_user_id())[0]);
            var_dump($list);
            break;
    }
    $listInvitationStr .= '<table>
                            <thead>
                                <tr id="lab_list_header">
                                <th>'.esc_html__("Nom de l'invité","lab").'</th>
                                '.($param['view']!='host' ? '<th>'.esc_html__("Invitant","lab").'</th>' : '').
                                '<th>'.esc_html__("Mission","lab").'</th>
                                <th>'.esc_html__("Date de création","lab").'</th>
                                <th>'.esc_html__("Statut","lab").'</th>
                                <th>'.esc_html__("Actions","lab").'</th>
                                </tr>
                            </thead>
                            <tbody>';
    
    $listInvitationStr .= lab_invitations_interface_fromList($list,$param['view']);
    $listInvitationStr .=   '</tbody>
                          </table>';
    
    return $listInvitationStr;
}

function lab_invitations_interface_fromList($list,$view) {
    $listStr = '';
    foreach ($list as $invitation) {
        $guest = lab_invitations_getGuest($invitation->guest_id);
        $host = new LabUser($invitation->host_id);
        $listStr .= '<tr>
                        <td><a href="mailto:'.$guest->email.'">'. $guest->first_name . ' ' . $guest->last_name .'</a></td>'
                        .($view!='host' ? '<td><a href="mailto:'.$host->email.'">'. $host->first_name . ' ' . $host->last_name .'</a></td>':'').
                        '<td>'. $invitation->mission_objective .'</td>
                        <td>'. $invitation->creation_time .'</td>
                        <td>'. $invitation->status .'</td>
                        <td><a href="/invite/'. $invitation->token.'">'.esc_html__("Lien de modification",'lab').'</a></td>
                    </tr>';
    }
    return $listStr;
}

?>


