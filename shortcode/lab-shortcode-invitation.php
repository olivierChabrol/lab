<?php
/*
 * File Name: lab-shortcode-directory.php
 * Description: shortcode pour générer un annuaire
 * Authors: Ivan Ivanov, Lucas Urgenti
 * Version: 0.2
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
            return esc_html__("Token d'invitation manquant.",'lab');
        } else {
            $token = explode("/",$url)[1];
            $invitation=lab_invitations_getByToken($token);
        }
    } else {
        $host = isset(explode("/",$url)[1]) ? new labUser(lab_profile_getID(explode("/",$url)[1])) : 0 ;
    }
    $invitationStr = '
    <div id="invitationForm" hostForm='.$param['hostpage'].'>
        <h3>'.esc_html__("Informations personnelles","lab").'</h3>
        <form action="">
        <div class="lab_invite_row" id="lab_fullname">
            <div class="lab_invite_field">
                <label for="lab_firstname">'.esc_html__("Prénom","lab").'</label>
                <input type="text" id="lab_firstname" name="lab_firstname">
            </div>
            <div class="lab_invite_field">
                <label for="lab_lastname">'.esc_html__("Nom","lab").'</label>
                <input type="text" id="lab_lastname" name="lab_lastname">
            </div>
        </div>
        <div class="lab_invite_field">
            <label for="lab_email">'.esc_html__("Email","lab").'</label>
            <input type="email" id="lab_email" name="lab_email">
        </div>
        <div id="lab_phone_country">
            <div class="lab_invite_field">
                <label for="lab_phone">'.esc_html__("Numéro de téléphone","lab").'</label>
                <input type="tel" id="lab_phone">
            </div>
            <div class="lab_invite_field">
                <label for="lab_country">'.esc_html__("Pays","lab").'</label>
                <input type="text" id="lab_country" name="lab_country">
            </div>
        </div>
        <div class="lab_invite_field">
            <label for="lab_hostname">'.esc_html__("Nom de l'invitant","lab").'</label>
            <input type="text" id="lab_hostname" name="lab_hostname" host_id='.($host==0 ? '': $host->id).' value="'.($host==0 ? '' : $host->first_name.' '.$host->last_name).'">
        </div>
        <div class="lab_invite_field">
            <label for="lab_mission">'.esc_html__("Objectif de mission","lab").'</label>
            <select id="lab_mission" name="lab_mission">
                <option value="seminar">'.esc_html__("Séminaire","lab").'</option>
                <option value="other">'.esc_html__("Autre","lab").'</option>
            </select>
            <input hidden type="text" id="lab_mission_other">
            <p style="display:none" id="lab_mission_other_desc">'.esc_html__("Précisez la nature de votre mission ici.","lab").'</p>
        </div>
        <hr>
        <div class="lab_invite_field">
            <input type="checkbox" id="lab_hostel" name="lab_hostel">
            <label for="lab_hostel">'.esc_html__("Besoin d'un hôtel","lab").'</label>
        </div>
        <hr>
        <h3>'.esc_html__("Moyen de transport","lab").'</h3>
        <div id="lab_mean_travel" class="lab_invite_row">
            <div class="lab_invite_field">
                <label for="lab_transport_to">'.esc_html__("Vers l'I2M","lab").'</label>
                <select id="lab_transport_to" name="lab_transport_to">
                    <option value="car">'.esc_html__("Voiture","lab").'</option>
                    <option value="train">'.esc_html__("Train","lab").'</option>
                    <option value="plane">'.esc_html__("Avion","lab").'</option>
                    <option value="bus">'.esc_html__("Car","lab").'</option>
                    <option value="none">'.esc_html__("Aucun","lab").'</option>
                    <option value="other">'.esc_html__("Autre","lab").'</option>
                </select>
                <input hidden type="text" id="lab_transport_to_other">
                <p>'.esc_html__("Moyen de transport depuis votre domicile vers notre laboratoire","lab").'</p>
            </div>
            <div class="lab_invite_field">
                <label for="lab_transport_from">'.esc_html__("Depuis l'I2M","lab").'</label>
                <select id="lab_transport_from" name="lab_transport_from">
                    <option value="car">'.esc_html__("Voiture","lab").'</option>
                    <option value="train">'.esc_html__("Train","lab").'</option>
                    <option value="plane">'.esc_html__("Avion","lab").'</option>
                    <option value="bus">'.esc_html__("Car","lab").'</option>
                    <option value="none">'.esc_html__("Aucun","lab").'</option>
                    <option value="other">'.esc_html__("Autre","lab").'</option>
                </select>
                <input hidden type="text" id="lab_transport_from_other">
                <p>'.esc_html__("Moyen de transport depuis notre laboratoire vers votre domicile","lab").'</p>
            </div>
        </div> 
        <div id="lab_date" class="lab_invite_row">
            <div class="lab_invite_field" >
                <label for="lab_arrival">'.esc_html__("Date d'arrivée","lab").'</label>
                <input type="date" id="lab_arrival" name="lab_arrival">
                <p>'.esc_html__("Précisez la date de réservation du voyage, l'heure est quand vous quittez votre domicile","lab").'</p>
            </div>
            <div class="lab_invite_field">
                <label for="lab_departure">'.esc_html__("Date de départ","lab").'</label>
                <input type="date" id="lab_departure" name="lab_departure">
                <p>'.esc_html__("Précisez la date de réservation du voyage","lab").'</p>
            </div>
        </div>
        <hr>';
        if ( $param["hostpage"] ) {
            $invitationStr .=
            '<div id="lab_inviting_fields" class="lab_invite_row">
                <div class="lab_invite_field">
                    <label for="lab_group_name">'.esc_html__("Nom du groupe","lab").'</label>
                    <select id="lab_group_name" name="lab_group_name">';
            foreach ($host->groups as $g)
            {
                $invitationStr .= '<option value="'.$g->id.'">'.$g->group_name.'</option>';
            }

            $invitationStr .=
                    '</select>
                </div>
                <div class="lab_invite_field">
                    <label for="lab_credit">'.esc_html__("Origine des crédits","lab").'</label>
                    <select id="lab_credit" name="lab_credit">
                        <option value="cnrs">'.esc_html__("CNRS","lab").'</option>
                        <option value="amu">'.esc_html__("AMU","lab").'</option>
                        <option value="other">'.esc_html__("Autre","lab").'</option>
                    </select>
                    <input hidden type="text" id="lab_credit_other">
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

?>


