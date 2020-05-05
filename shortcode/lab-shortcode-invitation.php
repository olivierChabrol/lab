<?php
/*
 * File Name: lab-shortcode-directory.php
 * Description: shortcode pour générer un annuaire
 * Authors: Ivan Ivanov, Lucas Urgenti
 * Version: 0.8
*/

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
        if ( ! isset(explode("/",$url)[1])) {//Aucun token, donc l'invitant crée lui-même une nouvelle invitation
            $token='0';
        } else {//Token fournit, récupère les informations existantes
            $token = explode("/",$url)[1];
            $invitation=lab_invitations_getByToken($token);
            if (!isset($invitation)) {
                return esc_html__("Token d'invitation invalide",'lab');
            }
            $guest = lab_invitations_getGuest($invitation->guest_id);
            $host = new labUser($invitation->host_id);
            //Qui modifie, l'invitant ou le responsable ?
            $isChief = isset($invitation->host_group_id) ? get_current_user_id()==(int)lab_admin_get_chief_byGroup($invitation->host_group_id): false;
            if ( $isChief ) {
                $invitationStr .= '<p><i>Vous pouvez modifier cette invitation en tant que responsable de groupe</i></p>';
            } else if ( get_current_user_id()==$invitation->host_id ) { 
                $invitationStr .= '<p><i>Vous pouvez modifier cette invitation en tant qu\'invitant</i></p>';
            } else {
                die('Vous ne pouvez pas modifier cette invitation');
            }
        }
    } else {
        $host = isset(explode("/",$url)[1]) ? new labUser(lab_profile_getID(explode("/",$url)[1])) : 0 ;
    }
    $newForm = (!$param['hostpage'] || $token=='0') ? 1 : 0 ; //Le formulaire est-il nouveau ? Si non, remplit les champs avec les 
    $invitationStr = '<div id="invitationForm" hostForm='.$param['hostpage'].' token="'.(($param['hostpage'] && strlen($token)>1) ? $token : '').'" newForm='.$newForm.'>'.$invitationStr;
    $invitationStr .= '
        <h3>'.esc_html__("Informations personnelles","lab").'</h3>
        <form action="javascript:formAction()">
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
            <input type="text" required id="lab_hostname" name="lab_hostname" host_id="'.($host==0 ? '' : $host->id.'" value="'.$host->first_name.' '.$host->last_name).'">
        </div>
        <div class="lab_invite_field">
            <label for="lab_mission">'.esc_html__("Objectif de mission","lab").'</label>
            <select id="lab_mission" name="lab_mission">
                <option value="">'.esc_html__("Choisissez une option","lab").'</option>';

    foreach(AdminParams::get_params_fromId(AdminParams::PARAMS_MISSION_ID) as $missionparam)
    {
        $invitationStr .= 
                '<option value="'.$missionparam->id.'">'.esc_html__($missionparam->value,"lab").'</option>';
    }
    $invitationStr .= 
                '<option value="other">'.esc_html__("Autre","lab").'</option>
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
        if ( $param["hostpage"] ) {//Affiche les champs supplémentaires, pour les responsables/invitants.
            $invitationStr .=

            '<h3>'.esc_html__("Champs pour l'invitant : ","lab").'</h3>
            <div class="lab_invite_row">
                <div class="lab_invite_field">
                    <label for="lab_group_name">'.esc_html__("Nom du groupe","lab").'</label>
                    <select required id="lab_group_name" name="lab_group_name">';
                foreach ($host->groups as $g)
                {
                    $invitationStr .= '<option value="'.$g->id.'">'.$g->group_name.'</option>';
                }

                $invitationStr .=
                    '</select>
                </div>
                <div class="lab_invite_field">
                    <label for="lab_credit">'.esc_html__("Origine des crédits","lab").'</label>
                    <select required id="lab_credit" name="lab_credit">
                        <option value="">'.esc_html__("Choisissez une option","lab").'</option>';
                    foreach(AdminParams::get_params_fromId(AdminParams::PARAMS_FUNDING_ID) as $creditparam)
                    {
                    $invitationStr .= 
                        '<option value="'.$creditparam->id.'">'.esc_html__($creditparam->value,"lab").'</option>';
                    }
                    $invitationStr .=
                        '<option value="other">'.esc_html__("Autre","lab").'</option>
                    </select>
                    <input hidden type="text" id="lab_credit_other" value="'.(!$newForm ? $invitation->funding_source : '').'">
                    <p style="display:none" id="lab_credit_other_desc">'.esc_html__("Précisez l'origine de crédit ici.","lab").'</p>
                </div>
            </div>
            <div class="lab_invite_row">
                <div class="lab_invite_field">
                    <label for="lab_estimated_cost">'.esc_html__("Coût estimé (en €)","lab").'</label>
                    <input type="text" id="lab_estimated_cost" value="'.(!$newForm ? $invitation->estimated_cost : '').'">
                    <p>'.esc_html__("À remplir par l'invitant : coût estimé du défraiement ","lab").'</p>
                </div>
                <div class="lab_invite_field">
                    <label for="lab_maximum_cost">'.esc_html__("Coût maximum (en €)","lab").'</label>
                    <input type="text" id="$" value="'.(!$newForm ? $invitation->maximum_cost : '').'">
                    <p>'.esc_html__("À remplir par le responsable : budget maximal allouable à cette invitation ","lab").'</p>
                </div>
            </div>';
                // if ( isset($invitation->host_group_id) && get_current_user_id()==(int)lab_admin_get_chief_byGroup($invitation->host_group_id) ) {
                //     $invitationStr .= '';
                // } 
                $invitationStr .=
            '<div class="lab_invite_row">
                <input type="submit" value="'.esc_html__("Enregistrer","lab").'">
            </div>
            </form></div>';
            if ($isChief) {
                $invitationStr .= '<div><button id="lab_send_manager">'.esc_html__("Envoyer à l'administration",'lab').'</button></div>';
            } else {
                $invitationStr .= '<div><button id="lab_send_group_chief">'.esc_html__("Envoyer au responsable",'lab').'</button></div>';
            }
        }
        else {
            $invitationStr .= '<div class="lab_invite_row">
            <input type="submit" value="'.esc_html__("Valider","lab").'">
        </div>';
        }
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
function lab_invitations_mail($type=1, $guest, $invite) {
    switch ($type) {
        case 1: //Envoi de mail récapitulatif à l'invité lorsqu'il crée sa demande d'invitation
            $dest = $guest["email"];
            $subj = esc_html__("Votre demande d'invitation à l'I2M",'lab');
            $date = date_create_from_format("Y-m-d H:i:s", $invite["creation_time"]);
            $content = "<p><i>".strftime('%A %d %B %G - %H:%M',$date->getTimestamp())."</i></p>";
            $content .= "<p>".esc_html__("Votre demande d'invitation a bien été prise en compte",'lab').".<br>".esc_html__("Elle a été transmise à votre invitant",'lab').".</p>";
            $content .= lab_InviteForm('',$guest,$invite);
            break;
        case 5: //Envoi de mail récapitulatif à l'invitant lorsque l'invité a créé une invitation
            $host = new LabUser($invite['host_id']);
            $dest = $host->email;
            $subj = esc_html__("Demande d'invitation à l'I2M",'lab');
            $date = date_create_from_format("Y-m-d H:i:s", $invite["creation_time"]);
            $content = "<p><i>".strftime('%A %d %B %G - %H:%M',$date->getTimestamp())."</i></p>";
            $content .= "<p>".esc_html__("Une demande d'invitation à l'I2M vous a été transmise.",'lab')."<br>"
            .esc_html__("Vous pouvez la modifier en suivant",'lab')." <a href='http://stage.fr/invite/".$invite['token']."'>".esc_html__('ce lien','lab')."</a>.</p>";
            $content .= lab_InviteForm('',$guest,$invite);
            break;
        case 10: //Envoi du mail au responsable du groupe une fois la demande complétée
            $subj = esc_html__("Nouvelle demande d'invitation à l'I2M",'lab');
            $chief = new LabUser(lab_admin_get_chief_byGroup($invite['host_group_id']));
            $dest = $chief->email;
            $date = date_create_from_format("Y-m-d H:i:s", $invite["completion_time"]);
            $content = "<p><i>".strftime('%A %d %B %G - %H:%M',$date->getTimestamp())."</i></p>";
            $content .= "<p>".esc_html__("Une demande d'invitation à l'I2M a été complétée.",'lab')."<br>"
            .esc_html__("Vous pouvez la consulter en suivant",'lab')." <a href='http://stage.fr/invite/".$invite['token']."'>".esc_html__('ce lien','lab')."</a><br>
            et modifier les informations si besoin. Vous pouvez ensuite la valider pour la transmettre au pôle budget.</p>";
            $content .= lab_InviteForm('host',$guest,$invite);
            break;
        default:
            return 'unkown mail type';
            break;
    }
    wp_mail($dest,$subj,$content);
    return $content;
}
function lab_InviteForm($who,$guest,$invite) {
    $host = new LabUser($invite['host_id']);
    $out = '<p>'.esc_html__("Récapitulatif de la demande d'invitation",'lab').' : </p>
            <p>'.esc_html__("Informations personnelles de l'invité",'lab').' :</p>
                <ul>
                <li>'.esc_html__("Prénom",'lab').' : '.$guest['first_name'].'</li>
                <li>'.esc_html__("Nom",'lab').' : '.$guest['last_name'].'</li>
                <li>'.esc_html__("Email",'lab').' : '.$guest['email'].'</li>
                <li>'.esc_html__("Téléphone",'lab').' : '.$guest['phone'].'</li>
                <li>'.esc_html__("Pays",'lab').' : '.$guest['country'].'</li>
            </ul>
            <p>Contexte de l\'invitation
            <ul>
                <li>'.esc_html__("Nom de l'invitant",'lab').' : '.$host->first_name.' '.$host->last_name.'</li>
                <li>'.esc_html__("Objectif de mission",'lab').' : '.$invite['mission_objective'].'</li>
                <li>'.esc_html__("Besoin d'un hotel",'lab').' : '.($invite['needs_hostel'] == 1 ? esc_html__('oui','lab') : esc_html__('non','lab')).'</li>
                <li>'.esc_html__("Moyen de transport",'lab').' :   <ul>
                                                <li>'.esc_html__("Vers l'I2M",'lab').' : '.$invite['travel_mean_to'].'</li>
                                                <li>'.esc_html__("Depuis l'I2M",'lab').' : '.$invite['travel_mean_from'].'</li>
                                            </ul></li>
                <li>'.esc_html__("Date d'arrivée",'lab').' : '.$invite['start_date'].'</li>
                <li>'.esc_html__("Date de départ",'lab').' : '.$invite['end_date'].'</li>';
                
    if($who=='host')
    {
        $out .= '<li>'.esc_html__("Estimation du coût",'lab').' : '.$invite['estimated_cost'].'</li>
                 <li>'.esc_html__("Origine du crédit",'lab').' : '.$invite[''].'</li>';
    }
    $out .= '</ul>';
    return $out;
}

?>


