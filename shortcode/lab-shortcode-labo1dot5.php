<?php


function lab_labo1_5($args) {
    $html = '
    <h3>Remplir les trajets</h3>
    <div id="trajet0">
        <label for="trajet0">Trajet:</label>     
        <div>
            <label for="country_from0">Pays de départ:<span class="lab_form_required_star"> *</span></label>
            <input type="text" required id="country_from0" name="country_from0">
            <label for="from0">Ville de départ :<span class="lab_form_required_star"> *</span></label>
            <input type="text" required id="from0" name="from0">
        </div>
        <div>
            <label for="country_to0">Pays d\'arrivee:<span class="lab_form_required_star"> *</span>&nbsp&nbsp</label>
            <input type="text" required id="country_to0" name="country_to0">
            <label for="to0">Ville d\'arrivee :<span class="lab_form_required_star"> *</span>&nbsp&nbsp</label>
            <input type="text" required id="to0" name="to0">
        </div>
        <div>
            <label for="travel_date0"> Date de départ <span class="lab_form_required_star"> *</span>&nbsp</label>
            <input type="date" id="travel_date0" name="travel_date0">
            <label for="lab_transport_to0">Mode de transport<span class="lab_form_required_star"> *</span></label>
            <select id="lab_transport_to0" name="lab_transport_to0">
            <option value="">'.esc_html__("Choisissez une option","lab").'</option>
            <option value="car">'.esc_html__("Voiture","lab").'</option>
            <option value="train">'.esc_html__("Train","lab").'</option>
            <option value="plane">'.esc_html__("Avion","lab").'</option>
            <option value="bus">'.esc_html__("Car","lab").'</option>
            <option value="none">'.esc_html__("Aucun","lab").'</option>
            <option value="other">'.esc_html__("Autre","lab").'</option>
            </select>
        </div>
        <div>
            <label for="go_back0">Un trajet aller/retour?<span class="lab_form_required_star"> *</span></label>
            <select id="go_back0" name="go_back0">
            <option value="">Aller/Retour?</option>
            <option value="gosimple">Aller simple</option>
            <option value="goback">Aller Retour</option>
            </select>
        </div>
        <button class="removeVar" index="0">Supprimer ce trajet</button><p></p>
    </div>
    <p><button id="addVar">Ajouter un nouveau trajet</button></p>
    <p><button id="validate">Valider</button></p>';

    return $html;
}

function lab_labo1_5_old($args) {

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
            $charges = json_decode($invitation->charges);
            if (!isset($invitation)) {
                //return esc_html__("Token d'invitation invalide",'lab');
                return esc_html__("Test Hongda",'lab');
            }
            $guest = lab_invitations_getGuest($invitation->guest_id);
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
                      <h2>'.esc_html__("Formulaire","lab").'<i class="fas fa-arrow-up"></i></h2>'.$invitationStr;
    $invitationStr .= '
        <form action="javascript:formAction()">
        <h3>'.esc_html__("Informations personnelles","lab").'</h3>
        <div class="lab_invite_field">
            <label for="lab_email">'.esc_html__("Email","lab").'<span class="lab_form_required_star"> *</span></label>
            <input type="email" required id="lab_email" guest_id="" name="lab_email"value="'.(!$newForm ? $guest->email : '').'">
        </div>
        <div class="lab_invite_row" id="lab_fullname">
            <div class="lab_invite_field">
                <label for="lab_firstname">'.esc_html__("Prénom","lab").'<span class="lab_form_required_star"> *</span></label>
                <input type="text" required id="lab_firstname" name="lab_firstname" guest_id="'.(!$newForm ? $guest->id : '').'" value="'.(!$newForm ? $guest->first_name : '').'">
            </div>
            <div class="lab_invite_field">
                <label for="lab_lastname">'.esc_html__("Nom","lab").'<span class="lab_form_required_star"> *</span></label>
                <input type="text" required id="lab_lastname" name="lab_lastname" value="'.(!$newForm ? $guest->last_name : '').'">
            </div>
        </div>
        <div id="lab_phone_country">
            <div class="lab_invite_field">
                <label for="lab_phone">'.esc_html__("Numéro de téléphone","lab").'</label>
                <input type="tel" id="lab_phone" phoneval="'.(!$newForm ? $guest->phone : '').'">
            </div>
            <div class="lab_invite_field">
                <label for="lab_country">'.esc_html__("Pays","lab").'<span class="lab_form_required_star"> *</span></label>
                <input type="text" required id="lab_country" name="lab_country" countryCode="'.(!$newForm ? $guest->country : '').'">
            </div>
        </div>
        <div class="lab_invite_field">
            <label for="lab_hostname">'.esc_html__("Nom de l'invitant","lab").'<span class="lab_form_required_star"> *</span></label>
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
            <input style="display:none" type="text" id="lab_mission_other" value="'.(!$newForm ? $invitation->mission_objective : '').'">
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
                <label for="lab_cost_to">'.esc_html__("Coût estimé du trajet","lab").' :</label>
                <input type="number" min=0 step="0.1" id="lab_cost_to" '.(!$newForm ? 'value="'.$charges->travel_to.'"' : '').' name="lab_cost_to" placeholder="'.esc_html__("en €",'lab').'"/>
                <p>'.esc_html__("À ne remplir que si l'I2M devra vous défrayer ce trajet.",'lab').'</p>
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
                <label for="lab_cost_from">'.esc_html__("Coût estimé du trajet","lab").' :</label>
                <input type="number" min=0 step="0.1" id="lab_cost_from" '.(!$newForm ? 'value="'.$charges->travel_from.'"' : '').' name="lab_cost_from" placeholder="'.esc_html__("en €",'lab').'"/>
                <p>'.esc_html__("À ne remplir que si l'I2M devra vous défrayer ce trajet.",'lab').'</p>
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
        <h3>'.esc_html__("Autres frais","lab").'</h3>
        <p>'.esc_html__("Précisez les frais qui devront être pris en charge par l'I2M.",'lab').'</p>
        <div class="lab_invite_row">
            <div class="lab_invite_field">
            <label for="lab_cost_hostel">'.esc_html__("Hôtel","lab").' :</label>
                <input type="number" min=0 step="0.1" id="lab_cost_hostel" '.(!$newForm ? 'value="'.$charges->hostel.'"' : '').' name="lab_cost_hostel" placeholder="'.esc_html__("en €",'lab').'"/>
                <label for="lab_cost_meals">'.esc_html__("Repas","lab").' :</label>
                <input type="number" min=0 step="0.1" id="lab_cost_meals" name="lab_cost_meals" '.(!$newForm ? 'value="'.$charges->meals.'"' : '').' placeholder="'.esc_html__("en €",'lab').'"/>
            </div>
            <div class="lab_invite_field">
                <label for="lab_cost_taxi">'.esc_html__("Taxi","lab").' :</label>
                <input type="number" min=0 step="0.1" id="lab_cost_taxi" name="lab_cost_taxi" '.(!$newForm ? 'value="'.$charges->taxi.'"' : '').' placeholder="'.esc_html__("en €",'lab').'"/>
                <label for="lab_cost_other">'.esc_html__("Autre","lab").' :</label>
                <input type="number" min=0 step="0.1" id="lab_cost_other" name="lab_cost_other" '.(!$newForm ? 'value="'.$charges->other.'"' : '').' placeholder="'.esc_html__("en €",'lab').'"/>
            </div>
        </div>
        <hr>';
        if ( $newForm ) {// Affiche le champ pour ajouter un commentaire lors de la création
            $invitationStr .= 
            '<div class="lab_invite_field">
                <label for="lab_form_comment">'.esc_html__("Commentaire",'lab').'</label>
                <textarea row="1" id="lab_form_comment" name="lab_form_comment"></textarea>
                <p>'.esc_html__("(par exemple vos numéros de carte de fidélité à utiliser lors de la réservation de vos voyages + la date d'expiration si nécessaire)",'lab').'</p>
        </div>';
        }
        if ( $param["hostpage"] ) {//Affiche les champs supplémentaires, pour les responsables/invitants.
            $invitationStr .=

            '<h3>'.esc_html__("Champs pour l'invitant : ","lab").'</h3>
            <div class="lab_invite_row">
                <div class="lab_invite_field">
                    <label for="lab_group_name">'.esc_html__("Nom du groupe","lab").'<span class="lab_form_required_star"> *</span></label>
                    <select required id="lab_group_name" name="lab_group_name">';
                foreach ($host->groups as $g)
                {
                    $invitationStr .= '<option value="'.$g->id.'">'.$g->group_name.'</option>';
                }

                $invitationStr .=
                    '</select>
                </div>
                <div class="lab_invite_field">
                    <label for="lab_credit">'.esc_html__("Origine des crédits","lab").'<span class="lab_form_required_star"> *</span></label>
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
                    <input style="display:none" type="text" id="lab_credit_other" value="'.(!$newForm ? $invitation->funding_source : '').'">
                    <p style="display:none" id="lab_credit_other_desc">'.esc_html__("Précisez l'origine de crédit ici.","lab").'</p>
                </div>
            </div>
            <div class="lab_invite_field">
                    <label for="lab_research_contrat">'.esc_html__("Contrats de recherche","lab").'</label>
                    <input type="text" id="lab_research_contrat" name="lab_research_contrat" value="'.(!$newForm ? $invitation->research_contract : '').'">
            </div>
            <div class="lab_invite_row">
                <div class="lab_invite_field">
                    <label for="lab_estimated_cost">'.esc_html__("Coût estimé (en €)","lab").'</label>
                    <input type="text" id="lab_estimated_cost" value="'.(!$newForm ? $invitation->estimated_cost : '').'">
                    <p>'.esc_html__("À remplir par l'invitant : coût estimé du défraiement ","lab").'</p>
                </div>
                <div class="lab_invite_field">
                    <label for="lab_maximum_cost">'.esc_html__("Coût maximum (en €)","lab").'</label>
                    <input type="text" id="lab_maximum_cost" value="'.(!$newForm ? $invitation->maximum_cost : '').'">
                    <p>'.esc_html__("À remplir par le responsable : budget maximal allouable à cette invitation ","lab").'</p>
                </div>
            </div>';
            if ($isChief) {
                $invitationStr .= '<div class="lab_invite_field">
                <input '.($invitation->status>10 ? 'disabled' : '').' type="submit" value="'.esc_html__("Enregistrer","lab").'">
                </div>'.($invitation->status>10 ? '<i>'.esc_html__("Cette invitation est déjà à l'étape suivante, pour la modifier, vous devez la renvoyer (via le bouton ci-dessous)",'lab').'</i>' : '').
                '</form></div>
                <div class="lab_invite_row lab_send_manager"><p class="lab_invite_field">Cliquez ici pour valider la demande et la transmettre au pôle budget :</p><button id="lab_send_manager">'.esc_html__("Envoyer à l'administration",'lab').'</button></div>';
            } else {
                $invitationStr .= '<div class="lab_invite_field">
                <input '.($invitation->status>1 ? 'disabled' : '').' type="submit" value="'.esc_html__("Enregistrer","lab").'">
                </div>'.($invitation->status>1 ? '<i>'.esc_html__("Cette invitation est déjà à l'étape suivante, pour la modifier, vous devez la renvoyer (via le bouton ci-dessous)",'lab').'</i>' : '').
                '</form></div>
                <div class="lab_invite_row lab_send_group_chief"><p class="lab_invite_field">Cliquez ici pour compléter la demande et la transmettre au responsable du groupe :</p><button id="lab_send_group_chief">'.esc_html__("Envoyer au responsable",'lab').'</button></div>';
            }
        }
        else {
            $invitationStr .= '<div class="lab_invite_field">
            <input type="submit" value="'.esc_html__("Valider","lab").'">
        </div>';
        }
        if (!$newForm) {
            $currentUser = lab_admin_username_get(get_current_user_id());
            $invitationStr .= '
        <div id="lab_invitationComments">
            <h2>Commentaires <i class="fas fa-arrow-up"></i></h2>
                '.lab_laboComments($token).'
                '.lab_laboNewComments($currentUser,$token).'
            </div>
        </div>';
        }
    return $invitationStr;
}
function lab_labo_filters() {
    $out = '<div id="lab_filter">
            <h5>'.esc_html__("Filtres",'lab').'</h5>
            <form id="lab_status_filter"><u>'.esc_html__("Statuts",'lab').' :</u> ';
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
            &nbsp<label for="lab_results_number">'.esc_html__("Année","lab").' : </label>
            <select id="lab_filter_year">
                <option selected value="all">'.esc_html__("Toutes",'lab')."</option>".$years.'
            </select>
        </div>
        <div>    
            &nbsp<label for="lab_results_number">'.esc_html__("Nombre de résultats par page","lab").' : </label>
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
function lab_labo_interface($args) {
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
            $listInvitationStr .= '<h5>Groupes dont vous êtes le chef :</h5><select id="lab_groupSelect">';
            foreach (lab_admin_get_groups_byChief(get_current_user_id()) as $g)
                {
                    $listInvitationStr .= '<option value="'.$g->id.'">'.$g->group_name.'</option>';
                }
            $listInvitationStr .='</select id="lab_invite_groupSelect">';
            $list = lab_invitations_getByGroup(lab_admin_get_groups_byChief(get_current_user_id())[0]->id)[1];
            break;
        case 'admin':
            $listInvitationStr .= '<h5>'.esc_html__("Groupes Préférés",'lab').' :</h5>
            <div id="lab_prefGroupsForm">
                <select id="lab_prefGroupsSelect"><option value="">Sélectionnez un groupe</option></select>
                <button id="lab_addPrefGroup">Ajouter aux préférés</button>
                <p id="lab_group_add_warning"></p>';
            $listInvitationStr .= '<ul id="lab_curr_prefGroups">';
            $listInvitationStr .= lab_labo_prefGroupsList(get_current_user_id());
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
    $listInvitationStr .= '<table view="'.$param['view'].'" id="lab_invite_list" class="table">
                            <thead>
                                <tr id="lab_list_header">'
                                    .($param['view']=='admin' ? '<th class="lab_column_name" name="host_group_id">'.esc_html__('Groupe','lab').'<i class="fas fa-caret-up"></i></th>' : '').
                                    '<th name="guest_id">'.esc_html__("Invité","lab").'</i></th>
                                    '.($param['view']!='host' ? '<th class="lab_column_name" name="host_id">'.esc_html__("Invitant","lab").'<i class="fas fa-caret-up"></i></th>' : '').
                                    '<th class="lab_column_name" name="mission_objective">'.esc_html__("Mission","lab").'<i class="fas fa-caret-up"></i></th>
                                    <th class="lab_column_name" sel="true" name="start_date" order="asc">'.esc_html__("Date d'arrivée","lab").'<i class="fas fa-caret-up"></i></th>
                                    <th class="lab_column_name" name="end_date">'.esc_html__("Date de départ","lab").'<i class="fas fa-caret-up"></i></th>
                                    <th class="lab_column_name" name="status">'.esc_html__("Statut","lab").'<i class="fas fa-caret-up"></i></th>
                                    <th class="lab_column_name" name="estimated_cost">'.esc_html__("Budget estimé","lab").'<i class="fas fa-caret-up"></i></th>
                                    <th class="lab_column_name" name="maximum_cost">'.esc_html__("Budget max.","lab").'<i class="fas fa-caret-up"></i></th>
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
        '<h2 id="lab_invite_detail_title">'.esc_html__("Détails de l'invitation",'lab').'<i class="fas fa-arrow-up"></i></h2>
        <div id="lab_invite_budget" style="display:none">
            <p>'.esc_html__("Budget réel",'lab').' : <b id="lab_invite_realCost"></b><form action="javascript:lab_submitRealCost()"><input token="" id="lab_invite_realCost_input" type="number" step="0.01" min=0/><input type="submit" value="Valider"><span id="lab_invite_realCost_message"></span></form></p>
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
function lab_labo_pagination($pages, $currentPage) {
    $out = '<ul id="pagination-digg">';
    $out .= '<li class="page_previous'.($currentPage>1 ? '">' : ' gris">').'« Précédent</li>';
    for ($i=1; $i<=$pages; $i++) {
        $out .= '<li page='.$i.' class="page_number"'.($currentPage!=$i ? ">$i" : " id='active'>$i").'</li>';
    }
    $out .= '<li class="page_next'.($pages>1 && $currentPage<$pages ? '">' : ' gris">').'Suivant »</li>';
    $out .= '</ul>';
    return $out;
}
function lab_labo_switchLocale($oldLocale) {
    global $currLocale;
    if ($currLocale == 'fr_FR') {
        $currLocale = 'en_GB';
    } else {
        $currLocale = 'fr_FR';
    }
    return $currLocale;
}
function lab_labo_interface_fromList($list,$view) {
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
                            .($view!='admin' ? '<td><a href="/invite/'. $invitation->token.'">'.esc_html__("Modifier",'lab').'</a>' 
                            : '<td><button class="lab_invite_showDetail" token="'.$invitation->token.'">'.esc_html__("Détails","lab").'</button>').
                            ($view=='admin'&& $invitation->status>1 ?
                            '<button title="'.esc_html("Cliquez pour prendre en charge l'invitation","lab").'" token="'.$invitation->token.'" class="lab_invite_takeCharge">Gérer</button></td>' : '</td>').
                        '</tr>';
        }
    } else {
        $listStr = "<tr><td colspan=42>".esc_html__("Aucune invitation",'lab')."</td></tr>";
    }
    return $listStr;
}
function lab_labo_mail($type=1, $guest, $invite) {
    switch ($type) {
        case 1: //Envoi de mail récapitulatif à l'invité lorsqu'il crée sa demande d'invitation
            global $currLocale;
            $currLocale = get_locale();
            $dest = $guest["email"];
            $subj = esc_html__("Votre demande d'invitation à l'I2M",'lab');
            $date = date_create_from_format("Y-m-d H:i:s", $invite["creation_time"]);
            $content = "<p><i>".strftime('%A %d %B %G - %H:%M',$date->getTimestamp())."</i></p>";
            //$content .= "<p>".get_locale()."</p>";
            $content .= "<p>".esc_html__("Votre demande d'invitation a bien été prise en compte",'lab').".<br>".esc_html__("Elle a été transmise à votre invitant",'lab').".</p>";
            $content .= lab_laboForm('',$guest,$invite);
            // unload_textdomain("lab");
            // add_filter('locale','lab_invitations_switchLocale',10);
            // myplugin_load_textdomain();
            // $content .= "<h5>Translated version :</h5>";
            // $content .= "<p>".get_locale()."</p>";
            // $content .= "<p>".esc_html__("Votre demande d'invitation a bien été prise en compte",'lab').".<br>".esc_html__("Elle a été transmise à votre invitant",'lab').".</p>";
            // $content .= lab_laboForm('',$guest,$invite);
            // unload_textdomain("lab");
            // add_filter('locale','lab_invitations_switchLocale',10);
            // myplugin_load_textdomain();
            break;
        case 5: //Envoi de mail récapitulatif à l'invitant lorsque l'invité a créé une invitation
            $host = new LabUser($invite['host_id']);
            $dest = $host->email;
            $subj = esc_html__("Demande d'invitation à l'I2M",'lab');
            $date = date_create_from_format("Y-m-d H:i:s", $invite["creation_time"]);
            $content = "<p><i>".strftime('%A %d %B %G - %H:%M',$date->getTimestamp())."</i></p>";
            $content .= "<p>".esc_html__("Une demande d'invitation à l'I2M vous a été transmise.",'lab')."<br>"
            .esc_html__("Vous pouvez la modifier en suivant",'lab')." <a href='http://stage.fr/invite/".$invite['token']."'>".esc_html__('ce lien','lab')."</a>.</p>";
            $content .= lab_laboForm('',$guest,$invite);
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
            $content .= lab_laboForm('host',$guest,$invite);
            break;
        default:
            return 'unkown mail type';
            break;
    }
    apply_filters( 'wp_mail_content_type', "text/html" );
    wp_mail($dest,$subj,$content);
    return $content;
}
function lab_laboForm($who,$guest,$invite) {
    $host = new LabUser($invite['host_id']);
    $chargesList = '<ul>';
    $charges = json_decode($invite['charges']);
    foreach ($charges as $el => $value) {
        $chargesList .= "<li><i>$el : </i>$value €</li>";
    }
    $chargesList .= '</ul>';
    $out = '<p><b>'.esc_html__("Récapitulatif de la demande d'invitation",'lab').' : </b></p>
            <p><u>'.esc_html__("Informations personnelles de l'invité",'lab').' :</u></p>
                <ul>
                <li><i>'.esc_html__("Prénom",'lab').' : </i>'.$guest['first_name'].'</li>
                <li><i>'.esc_html__("Nom",'lab').' : </i>'.$guest['last_name'].'</li>
                <li><i>'.esc_html__("Email",'lab').' : </i>'.$guest['email'].'</li>
                <li><i>'.esc_html__("Téléphone",'lab').' : </i>'.$guest['phone'].'</li>
                <li><i>'.esc_html__("Pays",'lab').' : </i>'.$guest['country'].'</li>
            </ul>
            <p><u>'.esc_html__("Contexte de l'invitation",'lab').'</u>
            <ul>
                <li><i>'.esc_html__("Nom de l'invitant",'lab').' : </i>'.$host->first_name.' '.$host->last_name.'</li>
                <li><i>'.esc_html__("Objectif de mission",'lab').' : </i>'.(is_numeric($invite['mission_objective']) ? AdminParams::get_param($invite['mission_objective']) : $invite['mission_objective']).'</li>
                <li><i>'.esc_html__("Besoin d'un hotel",'lab').' : </i>'.($invite['needs_hostel'] == 1 ? esc_html__('oui','lab') : esc_html__('non','lab')).'</li>
                <li><i>'.esc_html__("Moyen de transport",'lab').' :  </i>
                <ul>
                    <li><i>'.esc_html__("Vers l'I2M",'lab').' : </i>'.$invite['travel_mean_to'].'</li>
                    <li><i>'.esc_html__("Depuis l'I2M",'lab').' : </i>'.$invite['travel_mean_from'].'</li>
                </ul></li>
                <li><i>'.esc_html__("Date d'arrivée",'lab').' : </i>'.$invite['start_date'].'</li>
                <li><i>'.esc_html__("Date de départ",'lab').' : </i>'.$invite['end_date'].'</li>
                <li><u>'.esc_html__('Frais','lab').' : </u>'.$chargesList.'</li>';
    if($who=='host' || $who=='admin')
    {
        $out .= '<li><i>'.esc_html__("Estimation du coût",'lab').' : </i>'.$invite['estimated_cost'].'</li>
                 <li><i>'.esc_html__("Origine du crédit",'lab').' : </i>'.(is_numeric($invite['funding_source']) ? AdminParams::get_param($invite['funding_source']) : $invite['funding_source']).'</li>
                 <li><i>'.esc_html__("Contrat de recherche",'lab').' : </i>'.$invite['research_contract'].'</li>';
    }
    $out .= '</ul>';
    return $out;
}
function lab_laboComments($token) {
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
function lab_laboNewComments($currentUser, $token)
{
    $html =     '<div token="'.$token.'" id="lab_invitation_newComment">
                    <h5>'.esc_html__("Nouveau commentaire",'lab').'</h5>
                    <form action="javascript:lab_submitComment()">
                        <label><i>'.esc_html__("Publier sous le nom de",'lab')."</i> : <span id='lab_comment_name'>".$currentUser['first_name'].' '.$currentUser['last_name'].'</span></label>
                        <textarea row="1" cols="50" id="lab_comment" placeholder="Contenu du commentaire..."></textarea>
                        <input type="submit" value="'.esc_html__("Envoyer commentaire","lab").'">
                    </form>
                </div>';
    return $html;
}
function lab_labo_getStatusName($status) {
    switch ($status) {
        case 1:
            return "<span style='color:#F75C03' class='lab_infoBulle' title='".esc_html__("Cette invitation a été créée, vous pouvez maintenant en compléter toutes les informations et l'envoyer pour validation au responsable du groupe.","lab")."'>"
            .esc_html__("Créée","lab")."</span>";
        break;
        case 10: 
            return "<span style='color:#00c49f' class='lab_infoBulle' title='".esc_html__("Cette invitation a été complétée, le responsable peut maintenant la valider pour l'envoyer au pôle budget.","lab")."'>"
            .esc_html__("Complétée","lab")."</span>";
        break;
        case 20:
            return "<span style='color:#c00900' class='lab_infoBulle' title='".esc_html__("Cette invitation a été validée et envoyée au pôle budget.","lab")."'>"
            .esc_html__("Validée","lab")."</span>";
        break; 
        case 30:
            return "<span style='color:#289600' class='lab_infoBulle' title='".esc_html__("Cette invitation a été prise en charge par un administratif du pôle budget.","lab")."'>"
            .esc_html__("Prise en charge","lab")."</span>";
        break; 
        default:
            # code...
            break;
    }
}
function lab_labo_prefGroupsList($user_id) {
    $prefGroups = lab_invitations_getPrefGroups($user_id);
    if (count($prefGroups)>0) {
        $out = '';
        foreach ($prefGroups as $g){
            $out .= "<li title='$g->group_name' class='lab_prefGroup_element'>$g->acronym <i group_id='$g->group_id' class='fas fa-trash lab_prefGroup_del'></i></li>";
        }
    } else {
        $out = '<li>'.esc_html__("Aucun groupe préféré trouvé",'lab').'</li>';
    }
    return $out;
}

?>