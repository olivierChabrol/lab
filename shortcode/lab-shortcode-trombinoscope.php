<?php
/*
 * File Name: lab-shortcode-invitation.php
 * Description: shortcode pour afficher un formulaire de création d'invitation
 * Authors: Ivan IVANOV, Lucas URGENTI, Olivier CHABROL
 * Version: 1.2
 */

function lab_trombinoscope($args) {
    $param = shortcode_atts(array(
        'hostpage' => 0 //0 pour invité, 1 pour invitant/responsable
        ),
        $args, 
        "lab-trombinoscope"
    );
    $users = lab_admin_trombinoscope();

    $html = "<table>";
    foreach($users as $user) {
        $html .= "<tr>";
        $html .= $user->imgUrl;
        $html .= "<br>";
        $html .= $user->firstName." ".$user->lastName;
        $html .= "<td>";
        $html .= "</td></tr>";
    }
    $html .= "</table>";
    return $html;
}