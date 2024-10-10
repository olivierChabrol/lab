<?php

function lab_labo1_5($args){

    if ( ! is_user_logged_in() ) {
        return "Page accessible qu’aux utilisateurs connect&eacute;s";
    }
    ?>
    <form action="javascript:validate()">
    <h5><b>Informations personnelles et missions</b></h5>
    <br>
    <table class="table" id="info_person">
        <tr>
            <th>Prénom<span class="lab_form_required_star"> *</span></th>
            <td><input type="text" required id="user_firstname" class="form-control" value="" /></td>
            <th>Nom<span class="lab_form_required_star"> *</span></th>
            <td><input type="text" required id="user_lastname" class="form-control" value=""/></td>
        </tr>
        <tr>
            <th>Email<span class="lab_form_required_star"> *</span></th>
            <td><input type="text" required id="user_email" class="form-control" value=""/></td>
            <th>Groupe</th>
            <td><input type="text" id="user_group" class="form-control" value=""/></td>
        </tr> 
        <tr>
            <th>Motif<span class="lab_form_required_star"> *</span></th>
            <td><select id="mission_motif" required class="form-control">
                <option value="">Choisissez une option</option>
                <option value="Etude terrain">Etude terrain</option>
                <option value="Colloque-Congrès">Colloque-Congrès</option>
                <option value="Séminaire">Séminaire</option>
                <option value="Enseignement">Enseignement</option>
                <option value="Collaboration">Collaboration</option>
                <option value="Visite">Visite</option>
                <option value="Administration de la recherche">Administration de la recherche</option>
                <option value="Autre">Autre</option>
                </select>
            </td>
            <th>Frais</th>
            <td><select id="mission_cost" class="form-control">
                <option value="Avec frais">Avec frais</option>
                <option value="Sans frais">Sans frais</option>
                </select>
            </td>
        </tr>
    </table>
    <table class="table" id="info_mission">
        <tr>
            <th colspan="4">Prise en charge par le labo (après accord du responsable des crédits):
            <br>
            <br>
            <input type="checkbox" value="transport" name="cost_cover[]">&nbsp;Transport&nbsp;&nbsp;&nbsp;
            <input type="checkbox" value="hebergement" name="cost_cover[]">&nbsp;Hébergement&nbsp;&nbsp;&nbsp;
            <input type="checkbox" value="repas"  name="cost_cover[]">&nbsp;Repas&nbsp;&nbsp;&nbsp;
            <input type="checkbox" value="inscription"  name="cost_cover[]">&nbsp;Inscription
            </th>
        </tr>
        </tr>
            <tr>
            <th colspan="4">Frais annexes :&nbsp;&nbsp;&nbsp;
            <br>
            <br>
            <input type="checkbox" value="parking" name="cost_cover[]">&nbsp;Parking&nbsp;&nbsp;&nbsp;
            <input type="checkbox" value="transportcommun" name="cost_cover[]">&nbsp;Transport en commun&nbsp;&nbsp;&nbsp;
            <input type="checkbox" value="taxi" name="cost_cover[]">&nbsp;Taxi&nbsp;&nbsp;&nbsp;
            </th>
        </tr>
        <tr>
            <th colspan="">Préciser sur quels crédits</th>
            <td colspan="">
                <select id="mission_credit" class="form-control" onchange="mission_credit_onchange(this)">
                <option value="Crédits du groupe">Crédits du groupe</option>
                <option value="ANR">ANR</option>
                <option value="Contrat de recherche">Contrat de recherche</option>
                <option value="Autre">Autre</option>
                </select>
            </td>
        </tr>
        <tr>
            <th>Frais total estimé</th>
            <td><input type="text" id="cost_estimate" class="form-control" placeholder="en €"></td>
            <th>Carte de fidélité</th>
            <td><input type="text" id="mission_card" class="form-control"></td>
        </tr>
        <tr>
            <td style="height:100px" colspan="4" >
            <textarea style="width:100%;height:100%;" class="form-control"id="mission_comment" placeholder=" Commentaire (merci d’indiquer ici votre préférence horaire)"></textarea>
            </td>
        </tr>
    </table>
    <h5><b>Remplir les trajets</b></h5>
    <br>
    <table class="table" id="trajet0">
        <tr>
            <th colspan="4">Trajet N°1</th>
        </tr>
        <tr>
            <th>Pays de départ<span class="lab_form_required_star"> *</span></th>
            <td><input type="text" required id="country_from0" name="country_from0" class="form-control" style="text-transform:uppercase;"/></td>
            <th>Ville de départ<span class="lab_form_required_star"> *</span></th>
            <td><input type="text" required id="travel_from0" name="travel_from0" class="form-control" value="MARSEILLE" style="text-transform:uppercase;"/></td>
        </tr>
        <tr>
            <th>Pays d'arrivee<span class="lab_form_required_star"> *</span></th>
            <td><input type="text" required id="country_to0" name="country_to0" class="form-control" style="text-transform:uppercase;"/></td>
            <th>Ville d'arrivee<span class="lab_form_required_star"> *</span></th>
            <td><input type="text" required id="travel_to0" name="travel_to0" class="form-control" style="text-transform:uppercase;"/></td>
        </tr>
        <tr>
            <th>Date de départ<span class="lab_form_required_star"> *</span></th>
            <td><input type="date" required id="travel_date0" name="travel_date0" class="form-control"/></td>
            <th>Date de retour</th>
            <td><input type="date" id="travel_datereturn0" name="travel_datereturn0" class="form-control"/></td>
        </tr>
        <tr>
            <th>Mode de transport<span class="lab_form_required_star"> *</span></th>
            <td>
                <select id="means0" required name="means0" class="form-control">
                <option value="">Choisissez une option</option>
                <option value="Avion">Avion</option>
                <option value="Train">Train</option>
                <option value="Voiture personnelle">Voiture personnelle</option>
                <option value="Taxi">Taxi</option>
                <option value="Bus">Bus</option>
                <option value="Tramway">Tramway</option>
                <option value="RER">RER</option>
                <option value="Metro">Métro</option>
                <option value="Ferry">Ferry</option>
                </select>
            </td>
            <th>Nb de personnes</th>
            <td><input type="text" id="nb_person0" class="form-control" placeholder="Si voiture ou taxi" /></td>
        </tr>
        <tr>
        <th>Un trajet aller/retour?<span class="lab_form_required_star"> *</span></th>
            <td>
                <select id="go_back0" required class="form-control" onchange="go_back_onchange(this)">
                <option value="Oui">Oui</option>
                <option value="Non">Non</option>
                </select>
            </td>
        </tr> 
    </table>
    <input type="button" value="Ajouter un trajet"  class="btn btn-success" id="addVar"/>
    <br>
    <br>
    <input type="submit" value="Valider votre demande"  class="btn btn-success"/>
    </form >
<?php
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
            $isChief = isset($invitation->host_group_id) ? get_current_user_id()==(int)lab_admin_get_manager_byGroup_andType($invitation->host_group_id, 2): false;
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
        <h3>'.esc_html__("Personnal informations","lab").'</h3>
        <div class="lab_invite_field">
            <label for="lab_email1">'.esc_html__("Email","lab").'<span class="lab_form_required_star"> *</span></label>
            <input type="email" required id="lab_email1" guest_id="" name="lab_email"value="'.(!$newForm ? $guest->email : '').'">
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
                <label for="lab_country">'.esc_html__("Country","lab").'<span class="lab_form_required_star"> *</span></label>
                <input type="text" required id="lab_country" name="lab_country" countryCode="'.(!$newForm ? $guest->country : '').'">
            </div>
        </div>
        <div class="lab_invite_field">
            <label for="lab_hostname">'.esc_html__("Host name","lab").'<span class="lab_form_required_star"> *</span></label>
            <input type="text" required id="lab_hostname" name="lab_hostname" host_id="'.($host==0 ? '' : $host->id.'" value="'.$host->first_name.' '.$host->last_name).'">
        </div>
        <div class="lab_invite_field">
            <label for="lab_mission">'.esc_html__("Mission objective","lab").'</label>
            <select id="lab_mission" name="lab_mission">
                <option value="">'.esc_html__("Select an option","lab").'</option>';

    foreach(AdminParams::get_params_fromId(AdminParams::PARAMS_MISSION_ID) as $missionparam)
    {
        $invitationStr .= 
                '<option value="'.$missionparam->id.'">'.esc_html__($missionparam->value,"lab").'</option>';
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
            <label for="lab_hostel">'.esc_html__("Besoin d'un hôtel","lab").'</label>
        </div>
        <hr>
        <h3>'.esc_html__("Moyen de transport","lab").'</h3>
        <div id="lab_mean_travel" class="lab_invite_row">
            <div class="lab_invite_field">
                <label for="lab_transport_to">'.esc_html__("To the I2M","lab").'</label>
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
                <p>'.esc_html__("Moyen de transport depuis votre domicile vers notre laboratoire","lab").'</p>
                <label for="lab_cost_to">'.esc_html__("Travel estimated cost","lab").' :</label>
                <input type="number" min=0 step="0.1" id="lab_cost_to" '.(!$newForm ? 'value="'.$charges->travel_to.'"' : '').' name="lab_cost_to" placeholder="'.esc_html__("in €",'lab').'"/>
                <p>'.esc_html__("To be filled in only if the I2M will have to pay for this travel.",'lab').'</p>
            </div>
            <div class="lab_invite_field">
                <label for="lab_transport_from">'.esc_html__("From the I2M","lab").'</label>
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
                <p>'.esc_html__("Moyen de transport depuis notre laboratoire vers votre domicile","lab").'</p>
                <label for="lab_cost_from">'.esc_html__("Travel estimated cost","lab").' :</label>
                <input type="number" min=0 step="0.1" id="lab_cost_from" '.(!$newForm ? 'value="'.$charges->travel_from.'"' : '').' name="lab_cost_from" placeholder="'.esc_html__("in €",'lab').'"/>
                <p>'.esc_html__("To be filled in only if the I2M will have to pay for this journey.",'lab').'</p>
            </div>
        </div> 
        <div id="lab_date" class="lab_invite_row">
            <div class="lab_invite_field" >
                <label for="lab_arrival">'.esc_html__("Arrival date","lab").'</label>
                <input type="date" id="lab_arrival" name="lab_arrival" value="'.(!$newForm ? explode(" ",$invitation->start_date)[0] : '').'">
                <input type="time" step="60" id="lab_arrival_time" name="lab_arrival_time" value="'.(!$newForm ? explode(" ",$invitation->start_date)[1] : '').'">
                <p>'.esc_html__("Specify the date when you book the trip, the time is when you leave home.","lab").'</p>
            </div>
            <div class="lab_invite_field">
                <label for="lab_departure">'.esc_html__("Departure date","lab").'</label>
                <input type="date" id="lab_departure" name="lab_departure" value="'.(!$newForm ? explode(" ",$invitation->end_date)[0] : '').'">
                <input type="time" step="60" id="lab_departure_time" name="lab_departure_time" value="'.(!$newForm ? explode(" ",$invitation->end_date)[1] : '').'">
                <p>'.esc_html__("Specify the date when you book the trip, the time is when you leave the institute.","lab").'</p>
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
                <p>'.esc_html__("(e.g. your loyalty card numbers to be used when booking your trips + the expiry date if required)",'lab').'</p>
        </div>';
        }
        if ( $param["hostpage"] ) {//Affiche les champs supplémentaires, pour les responsables/invitants.
            $invitationStr .=

            '<h3>'.esc_html__("Fields for the host : ","lab").'</h3>
            <div class="lab_invite_row">
                <div class="lab_invite_field">
                    <label for="lab_group_name">'.esc_html__("Group name","lab").'<span class="lab_form_required_star"> *</span></label>
                    <select required id="lab_group_name" name="lab_group_name">';
                foreach ($host->groups as $g)
                {
                    $invitationStr .= '<option value="'.$g->id.'">'.$g->group_name.'</option>';
                }

                $invitationStr .=
                    '</select>
                </div>
                <div class="lab_invite_field">
                    <label for="lab_credit">'.esc_html__("Origin of credit","lab").'<span class="lab_form_required_star"> *</span></label>
                    <select required id="lab_credit" name="lab_credit">
                        <option value="">'.esc_html__("Select an option","lab").'</option>';
                    foreach(AdminParams::get_params_fromId(AdminParams::PARAMS_FUNDING_ID) as $creditparam)
                    {
                    $invitationStr .= 
                        '<option value="'.$creditparam->id.'">'.esc_html__($creditparam->value,"lab").'</option>';
                    }
                    $invitationStr .=
                        '<option value="other">'.esc_html__("Other","lab").'</option>
                    </select>
                    <input style="display:none" type="text" id="lab_credit_other" value="'.(!$newForm ? $invitation->funding_source : '').'">
                    <p style="display:none" id="lab_credit_other_desc">'.esc_html__("Specify the origin of the credit here.","lab").'</p>
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
                    <p>'.esc_html__("To be filled in by the host: estimated cost of expenses ","lab").'</p>
                </div>
                <div class="lab_invite_field">
                    <label for="lab_maximum_cost">'.esc_html__("Maximum cost (€)","lab").'</label>
                    <input type="text" id="lab_maximum_cost" value="'.(!$newForm ? $invitation->maximum_cost : '').'">
                    <p>'.esc_html__("To be completed by the the person in charge: maximum budget for this invitation ","lab").'</p>
                </div>
            </div>';
            if ($isChief) {
                $invitationStr .= '<div class="lab_invite_field">
                <input '.($invitation->status>10 ? 'disabled' : '').' type="submit" value="'.esc_html__("Save","lab").'">
                </div>'.($invitation->status>10 ? '<i>'.esc_html__("This invitation is already in the next step, to modify it, you have to send it back (via the button below)",'lab').'</i>' : '').
                '</form></div>
                <div class="lab_invite_row lab_send_manager"><p class="lab_invite_field">Cliquez ici pour valider la demande et la transmettre au pôle budget :</p><button id="lab_send_manager">'.esc_html__("Send to the administration",'lab').'</button></div>';
            } else {
                $invitationStr .= '<div class="lab_invite_field">
                <input '.($invitation->status>1 ? 'disabled' : '').' type="submit" value="'.esc_html__("Save","lab").'">
                </div>'.($invitation->status>1 ? '<i>'.esc_html__("This invitation is already in the next step, to modify it, you have to send it back (via the button below)",'lab').'</i>' : '').
                '</form></div>
                <div class="lab_invite_row lab_send_group_chief"><p class="lab_invite_field">Cliquez ici pour compléter la demande et la transmettre au responsable du groupe :</p><button id="lab_send_group_chief">'.esc_html__("Envoyer au responsable",'lab').'</button></div>';
            }
        }
        else {
            $invitationStr .= '<div class="lab_invite_field">
            <input type="submit" value="'.esc_html__("Confirm","lab").'">
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
            $listInvitationStr .= '<h5>'.esc_html__("Prefered groups",'lab').' :</h5>
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
                                    .($param['view']=='admin' ? '<th class="lab_column_name" name="host_group_id">'.esc_html__('Group','lab').'<i class="fas fa-caret-up"></i></th>' : '').
                                    '<th name="guest_id">'.esc_html__("Guest","lab").'</i></th>
                                    '.($param['view']!='host' ? '<th class="lab_column_name" name="host_id">'.esc_html__("Host","lab").'<i class="fas fa-caret-up"></i></th>' : '').
                                    '<th class="lab_column_name" name="mission_objective">'.esc_html__("Mission","lab").'<i class="fas fa-caret-up"></i></th>
                                    <th class="lab_column_name" sel="true" name="start_date" order="asc">'.esc_html__("Arrival date","lab").'<i class="fas fa-caret-up"></i></th>
                                    <th class="lab_column_name" name="end_date">'.esc_html__("Departure date","lab").'<i class="fas fa-caret-up"></i></th>
                                    <th class="lab_column_name" name="status">'.esc_html__("Status","lab").'<i class="fas fa-caret-up"></i></th>
                                    <th class="lab_column_name" name="estimated_cost">'.esc_html__("Estimated cost","lab").'<i class="fas fa-caret-up"></i></th>
                                    <th class="lab_column_name" name="maximum_cost">'.esc_html__("Maximum cost","lab").'<i class="fas fa-caret-up"></i></th>
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
            <p>'.esc_html__("Real budget",'lab').' : <b id="lab_invite_realCost"></b><form action="javascript:lab_submitRealCost()"><input token="" id="lab_invite_realCost_input" type="number" step="0.01" min=0/><input type="submit" value="Valider"><span id="lab_invite_realCost_message"></span></form></p>
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
                            .($view!='admin' ? '<td><a href="/invite/'. $invitation->token.'">'.esc_html__("Edit",'lab').'</a>' 
                            : '<td><button class="lab_invite_showDetail" token="'.$invitation->token.'">'.esc_html__("Details","lab").'</button>').
                            ($view=='admin'&& $invitation->status>1 ?
                            '<button title="'.esc_html__("Click to accept the invitation","lab").'" token="'.$invitation->token.'" class="lab_invite_takeCharge">Gérer</button></td>' : '</td>').
                        '</tr>';
        }
    } else {
        $listStr = "<tr><td colspan=42>".esc_html__("No invitation",'lab')."</td></tr>";
    }
    return $listStr;
}
function lab_labo_mail($type, $guest, $invite) {
    switch ($type) {
        case 1: //Envoi de mail récapitulatif à l'invité lorsqu'il crée sa demande d'invitation
            global $currLocale;
            $currLocale = get_locale();
            $dest = $guest["email"];
            $subj = esc_html__("Your request for an invitation to I2M",'lab');
            $date = date_create_from_format("Y-m-d H:i:s", $invite["creation_time"]);
            $content = "<p><i>".strftime('%A %d %B %G - %H:%M',$date->getTimestamp())."</i></p>";
            //$content .= "<p>".get_locale()."</p>";
            $content .= "<p>".esc_html__("Your invitation request has been taken into account",'lab').".<br>".esc_html__("It has been sent to your host",'lab').".</p>";
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
            $subj = esc_html__("Request for an invitation to I2M",'lab');
            $date = date_create_from_format("Y-m-d H:i:s", $invite["creation_time"]);
            $content = "<p><i>".strftime('%A %d %B %G - %H:%M',$date->getTimestamp())."</i></p>";
            $content .= "<p>".esc_html__("A request for an invitation to the I2M has been sent to you.",'lab')."<br>"
            .esc_html__("You can modify it by following",'lab')." <a href='http://stage.fr/invite/".$invite['token']."'>".esc_html__('this link','lab')."</a>.</p>";
            $content .= lab_laboForm('',$guest,$invite);
            break;
        case 10: //Envoi du mail au responsable du groupe une fois la demande complétée
            $subj = esc_html__("New invitation request to I2M",'lab');
            $chief = new LabUser(lab_admin_get_manager_byGroup_andType($invite['host_group_id'], 2));
            $dest = $chief->email;
            $date = date_create_from_format("Y-m-d H:i:s", $invite["completion_time"]);
            $content = "<p><i>".strftime('%A %d %B %G - %H:%M',$date->getTimestamp())."</i></p>";
            $content .= "<p>".esc_html__("A request for an invitation to the I2M has been completed.",'lab')."<br>"
            .esc_html__("You can consult it by following",'lab')." <a href='http://stage.fr/invite/".$invite['token']."'>".esc_html__('this link','lab')."</a><br>
            and modify the information if necessary. You can then validate it and send it to the budget center.</p>";
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
    $out = '<p><b>'.esc_html__("Summary of the invitation request",'lab').' : </b></p>
            <p><u>'.esc_html__("Guest's personal information",'lab').' :</u></p>
                <ul>
                <li><i>'.esc_html__("First name",'lab').' : </i>'.$guest['first_name'].'</li>
                <li><i>'.esc_html__("Last name",'lab').' : </i>'.$guest['last_name'].'</li>
                <li><i>'.esc_html__("Email",'lab').' : </i>'.$guest['email'].'</li>
                <li><i>'.esc_html__("Phone number",'lab').' : </i>'.$guest['phone'].'</li>
                <li><i>'.esc_html__("Country",'lab').' : </i>'.$guest['country'].'</li>
            </ul>
            <p><u>'.esc_html__("Context of the invitation",'lab').'</u>
            <ul>
                <li><i>'.esc_html__("Host's name",'lab').' : </i>'.$host->first_name.' '.$host->last_name.'</li>
                <li><i>'.esc_html__("Mission objective",'lab').' : </i>'.(is_numeric($invite['mission_objective']) ? AdminParams::get_param($invite['mission_objective']) : $invite['mission_objective']).'</li>
                <li><i>'.esc_html__("Need an hostel",'lab').' : </i>'.($invite['needs_hostel'] == 1 ? esc_html__('yes','lab') : esc_html__('no','lab')).'</li>
                <li><i>'.esc_html__("Means of transport",'lab').' :  </i>
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
                 <li><i>'.esc_html__("Research contract",'lab').' : </i>'.$invite['research_contract'].'</li>';
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
                    <h5>'.esc_html__("New comment",'lab').'</h5>
                    <form action="javascript:lab_submitComment()">
                        <label><i>'.esc_html__("Publish as",'lab')."</i> : <span id='lab_comment_name'>".$currentUser['first_name'].' '.$currentUser['last_name'].'</span></label>
                        <textarea row="1" cols="50" id="lab_comment" placeholder="Contenu du commentaire..."></textarea>
                        <input type="submit" value="'.esc_html__("Send comment","lab").'">
                    </form>
                </div>';
    return $html;
}
function lab_labo_getStatusName($status) {
    switch ($status) {
        case 1:
            return "<span style='color:#F75C03' class='lab_infoBulle' title='".esc_html__("This invitation has been created, you can now complete all the information and send it to the group leader for validation.","lab")."'>"
            .esc_html__("Created","lab")."</span>";
        break;
        case 10: 
            return "<span style='color:#00c49f' class='lab_infoBulle' title='".esc_html__("This invitation has been completed, the manager can now validate it and send it to the budget center.","lab")."'>"
            .esc_html__("Completed","lab")."</span>";
        break;
        case 20:
            return "<span style='color:#c00900' class='lab_infoBulle' title='".esc_html__("This invitation has been validated and sent to the budget center","lab")."'>"
            .esc_html__("Validated","lab")."</span>";
        break; 
        case 30:
            return "<span style='color:#289600' class='lab_infoBulle' title='".esc_html__("This invitation was taken up by an administrative officer from the budget center.","lab")."'>"
            .esc_html__("Taken over","lab")."</span>";
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
        $out = '<li>'.esc_html__("No prefered group found",'lab').'</li>';
    }
    return $out;
}

?>
