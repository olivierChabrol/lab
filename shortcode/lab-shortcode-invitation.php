<?php
/*
 * File Name: lab-shortcode-directory.php
 * Description: shortcode pour générer un annuaire
 * Authors: Ivan Ivanov, Lucas Urgenti
 * Version: 0.1
*/

function lab_invitation()
{
    $invitationStr = '
    <div id="invitationForm">
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
            <label for="lab_hostname">'.esc_html__("Nom de l'invitant","lab").'</label>
            <input type="text" id="lab_hostname" name="lab_hostname">
        </div>
        <div class="lab_invite_field">
            <label for="lab_email">'.esc_html__("Email","lab").'</label>
            <input type="email" id="lab_email" name="lab_email">
        </div>
        <div class="lab_invite_field">
            <label for="lab_mission">'.esc_html__("Objectif de mission","lab").'</label>
            <select id="lab_mission" name="lab_mission">
                <option value="dog">Dog</option>
                <option value="cat">Cat</option>
                <option value="hamster">Hamster</option>
                <option value="parrot">Parrot</option>
                <option value="spider">Spider</option>
                <option value="goldfish">Goldfish</option>
                <option value="other">'.esc_html__("Autre","lab").'</option>
            </select>
            <input hidden type="text" id="lab_mission_other">
        </div>
        <div id="lab_phone_country">
            <div  class="lab_invite_field">
                <label for="lab_phone">'.esc_html__("Numéro de téléphone","lab").'</label>
                <input type="tel" pattern="^((\+\d{in,3}(-| )?\(?\d\)?(-| )?\d{in,5})|(\(?\d{2,6}\)?))(-| )?(\d{3,4})(-| )?(\d{4})(( x| ext)\d{in,5}){0,in}$">
            </div>
            <div  class="lab_invite_field">
                <label for="lab_country">'.esc_html__("Pays","lab").'</label>
                <input type="text" id="lab_country" name="lab_country">
            </div>
        </div>
        <hr>
        <div>
            <label for="lab_hostel">'.esc_html__("Besoin d'un hotel","lab").'</label>
            <input type="checkbox" id="lab_hostel" name="lab_hostel">
        </div>
        <hr>
        <h3>'.esc_html__("Moyen de transport","lab").'</h3>
        <div id="lab_mean_travel" class="lab_invite_row">
            <div  class="lab_invite_field">
                <label for="lab_forward_path">'.esc_html__("Vers l'I2M","lab").'</label>
                <select id="lab_forward_path" name="lab_forward_path">
                    <option value="dog">Dog</option>
                    <option value="cat">Cat</option>
                    <option value="hamster">Hamster</option>
                    <option value="parrot">Parrot</option>
                    <option value="spider">Spider</option>
                    <option value="goldfish">Goldfish</option>
                </select>
                <p>'.esc_html__("Moyen de transport depuis votre domicile vers notre laboratoire","lab").'</p>
            </div>
            <div class="lab_invite_field">
                <label for="lab_return_path">'.esc_html__("Depuis l'I2M","lab").'</label>
                <select id="lab_return_path" name="lab_return_path">
                    <option value="dog">Dog</option>
                    <option value="cat">Cat</option>
                    <option value="hamster">Hamster</option>
                    <option value="parrot">Parrot</option>
                    <option value="spider">Spider</option>
                    <option value="goldfish">Goldfish</option>
                </select>
                <p>'.esc_html__("Moyen de transport depuis notre laboratoire vers votre domicile","lab").'</p>
            </div>
        </div> 
        <div id="lab_date" class="lab_invite_row">
            <div class="lab_invite_field" >
                <label for="lab_arrival">'.esc_html__("Date d'arrivée","lab").'</label>
                <input type="datetime" id="lab_arrival" name="lab_arrival">
                <p>'.esc_html__("Précisez la date de réservation du voyage, l'heure est quand vous quittez votre domicile","lab").'</p>
            </div>
            <div class="lab_invite_field">
                <label for="lab_departure">'.esc_html__("Date de départ","lab").'</label>
                <input type="datetime" id="lab_departure" name="lab_departure">
                <p>'.esc_html__("Précisez la date de réservation du voyage","lab").'</p>
            </div>
        </div>
        <hr>
        <div>
            <input type="submit" value="'.esc_html__("Valider","lab").'">
        </div>
    </div>';
    return $invitationStr;
}

?>


