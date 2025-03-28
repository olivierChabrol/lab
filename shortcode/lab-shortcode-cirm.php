<?php
/*
 * File Name: lab-shortcode-directory.php
 * Description: shortcode pour générer un annuaire
 * Authors: Astrid BEYER, Lucas URGENTI
 * Version: 1.0
*/

/*** 
 * Shortcode use : [lab-cirm]
     as-left="yes" OR as-left="no"
     group="AA" or whatever group's acronym
***/ 

function lab_cirm($atts) {
    $atts = shortcode_atts(array(
        'id'    => get_option('lab-hal'),
        'um'    => get_option('lab-hal'),
        'group' => get_option('lab-hal'),
        'year'  => get_option('lab-hal'),
    ), $atts, "lab-cirm");

    $events = array();
    
    $userId = $atts['id'];
    $um     = $atts['um'];
    $group  = $atts['group'];
    $year   = $atts['year'];
    $dateToday = date('Y-m-d');
    $token = getToken();
    //echo("Date : $dateToday<br>");
    $events = getEvents($events, $token, $dateToday);
    
    $nb_events = count($events);
    $nb_jours = 0;
    while($nb_events < 10 && $nb_jours < 2) {
        //echo 'Nb Event: ' . count($events) . "<br>";
        $dateToday = date('Y-m-d', strtotime($dateToday . ' + 1 days'));
        //echo("<h3>Date : $dateToday</h3><br>");
        $events = getEvents($events, $token, $dateToday);
        $nb_events = count($events);
        $nb_jours += 1;
    }
    //echo 'Nb Event: ' . count($events) . "<br>";
    echo('<div class="em pixelbones em-list-widget em-events-widget"><ul>');
    foreach($events as $event) {
        $date_split = explode('-', $event['date']);
        echo('<li>');
        echo('<div class="bloc-bleu" style="padding-left: 4pt;">');
        echo('<div class="em-item-info">');
        echo('<span class="jour">'.$date_split[2].'</span>');
        echo('/'.$date_split[1].'/'.$date_split[0].' <span style="padding-left: 3em">14h00 - 17h00</span>');
        echo('<div style="margin-top: -12pt; font-weight: bold">'.$event['frenchTitle'].'</div>');
        echo('<div>CIRM</div>');
        echo('</div>');
        echo('<div class="em-item-name">');
        echo('<strong>');
        foreach($event['talks'] as $talk) {
            echo($talk);
            echo(', ');
        }
        echo('</strong>');
        echo('</div>');
        echo('</li>');
    }
    echo('</ul></div>');
    //*/
}

function getEvents($events, $token, $date) {

    $apiHost = "https://app.cirm-math.fr/api";
    $apiUrl = "$apiHost/meetings?page=1&meetingDate=$date&isValidStatus=true";
    //echo "Token : $token";
    //$token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3NDI1NDk1NjMsImV4cCI6MTc0MjU1MzE2Miwicm9sZXMiOlsiUk9MRV9VU0VSIiwiZXZlcnlib2R5Il0sInVzZXJuYW1lIjoib2xpdmllci5jaGFicm9sIiwiZW1haWwiOiJvbGl2aWVyLmNoYWJyb2xAdW5pdi1hbXUuZnIifQ.yBaUYyCT4Nz19dbANQ1xcSG_r_5E9Yu9FkQ0P7FJ0w5uiLbjFUHIEploj-mgxp-IdMjVJmJw3qW_PULYwDXy_34cy5Jo6WoFGbi_Jl9kIXxqccL9PeOHvDeyQuOqvCcn2brVRzKG11KSh4KmwJM07dkBukMN65awx23FIb-J5LZ_LcsR0rntXte8Eo2FAgLBd7zM1vQhZbCBWYYgVCavpeKJkwmuHOKEgggOurMjMYrvKm_c2HFiJIgYCcQCDUQWBuwRnOqIj5M7sATXTsOlpv59WgCWIWMu9nnBO_3W5mXKg2WMQw1yQ_R0zHm8Rn96qApCAXfrQY2CfjEYFZoACA";
    //$token   = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3NDI1NTA2MDksImV4cCI6MTc0MjU1NDIwOSwicm9sZXMiOlsiUk9MRV9VU0VSIiwiZXZlcnlib2R5Il0sInVzZXJuYW1lIjoib2xpdmllci5jaGFicm9sIiwiZW1haWwiOiJvbGl2aWVyLmNoYWJyb2xAdW5pdi1hbXUuZnIifQ.wAclogxmVwvqep3RkEfyZe46PeKAyIObQhnMWQLhavQjgryz5WaPSWuBT3_4JGhg9hUPy8pvr0hLjj_jdZVabzFX5_-l6IieM4UBo7f0DxLzk26zJ0cTp_XNDR6RZjclpYslD5Suf8EUOrsDQqArGW6OJfiZqxBSozBmtuwQxHFXxZDRp36ePMHCa-Q7xZNE0fozt2owVdbZrHjNXVb8GmA9RoJfUYclL_qpwkl4DAUEDogXIQ9OaUXlfJ9Km2qEc-ETVjXmethnDkgcX3qcS_aMqno13gWLgZnV7OmiWQPt7a0B0pqEv2Q8VAzUz36WjdJXy3UBJzu47Hcr_d9hEg";

    $headers = array(
        'Accept: application/ld+json',
        'Authorization: Bearer '.$token,
    );

    $curl = curl_init($apiUrl);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($curl);

    if (curl_errno($curl)) {
        echo 'Error apiHost: ' . $apiHost . "<br>";
        echo 'Error apiUrl: ' . $apiUrl . "<br>";;
        echo 'Error: ' . curl_error($curl) . "<br>";;
    } else {
        //echo "Données reponses : $response<br>";
        $data = json_decode($response, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            // Traitez les données décodées
            //echo "Données parsées :<br>";
            //echo creerHtml($data);
            if (isset($data['member'])) {
                $nb_evt = count($data['member']);
                
                foreach ($data['member'] as $member) {
                    //echo "id : " . $member['id'] . "<br>";
                    //echo "<b>Titre : " . $member['frenchTitle'] . "</b><br>";
                    //echo "Nombre de jour : " . $member['numberOfDays'] . "<br>";
                    //echo "Debut : " . $member['beginMeetingDate'] . "<br>";
                    $member['date'] = $date;
                    
                    $member['talks'] = array();
                    foreach ($member['organisers'] as $organiser) {
                        //echo "Organisateur : " . $organiser['person'] . "<br>";
                        $member['talks'][] = getPerson($token, $organiser['person']);
                        var_dump($member['talks']);
                    }
                    if (!isset($events[$member['id']])) {
                        $events[$member['id']] = $member;
                    }
                    //*/
                }
            } else {
                echo "Aucun membre trouvé<br>";
            }
        } else {
            echo "Erreur de parsing JSON request : " . $apiUrl . "<br>";
            echo "Erreur de parsing JSON : " . json_last_error_msg();
        }
    }

    curl_close($curl);
    return $events;
}

function getPerson($token, $id) {
    $apiHost = "https://app.cirm-math.fr";
    $apiUrl = $apiHost.$id;
    
    $headers = array(
        'Accept: application/ld+json',
        'Authorization: Bearer '.$token,
    );

    $curl = curl_init($apiUrl);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($curl);

    if (curl_errno($curl)) {
        echo 'Error apiHost: ' . $apiHost . "<br>";
        echo 'Error apiUrl: ' . $apiUrl . "<br>";;
        echo 'Error: ' . curl_error($curl) . "<br>";;
    } else {
        //echo "URL : $apiUrl<br>";
        //echo "Données reponses : $response<br>";
        $data = json_decode($response, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            // Traitez les données décodées
            //echo "Données parsées :<br>";
            //echo creerHtml($data);
            return $data["firstName"]." ".strtoupper($data["lastName"]);
        } else {
            echo "Erreur de parsing JSON request : " . $apiUrl . "<br>";
            echo "Erreur de parsing JSON : " . json_last_error_msg();
        }
    }
}

/**
 * Retrieves an authentication token from the CIRM API.
 *
 * Uses cURL to send a POST request with email and password to the CIRM API
 * authentication endpoint and returns the token from the API response.
 *
 * @return string The authentication token.
 * @throws Exception If there is an error in the cURL request.
 */

function getToken() {
    $url = 'https://app.cirm-math.fr/api/auth';

    // Données à envoyer dans la requête POST
    $data = [
        'email' => get_option('lab-cirm-email'),
        'password' => get_option('lab-cirm-password'),
    ];

    // Initialiser cURL
    $ch = curl_init($url);

    // Options pour la requête cURL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'accept: application/json',
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // Encoder les données en JSON

    // Exécuter la requête et récupérer la réponse
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo '[getToken] Erreur : ' . curl_error($ch);
    } else {
        echo '[getToken] Réponse : ' . $response;
        //echo 'Réponse : ' . $response;
        $response = json_decode($response, true);
    }

    // Fermer la connexion cURL
    curl_close($ch);
    return $response['token'];
}

function afficherJsonEnHtml($json) {
    // Décoder le JSON en tableau ou objet
    $data = json_decode($json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return "Erreur de parsing JSON : " . json_last_error_msg();
    }

    

    // Créer le HTML à partir des données décodées
    return creerHtml($data);
}

// Fonction récursive pour créer des listes HTML à partir de l'objet/du tableau
function creerHtml($element) {
    $html = '';
    if (is_array($element)) {
        $html .= '<ul>';
        foreach ($element as $cle => $valeur) {
            $html .= '<li>';
            $html .= '<strong>' . htmlspecialchars($cle) . ':</strong> ';
            $html .= creerHtml($valeur);
            $html .= '</li>';
        }
        $html .= '</ul>';
    } elseif (is_object($element)) {
        $html .= '<ul>';
        foreach ($element as $cle => $valeur) {
            $html .= '<li>';
            $html .= '<strong>' . htmlspecialchars($cle) . ':</strong> ';
            $html .= creerHtml($valeur);
            $html .= '</li>';
        }
        $html .= '</ul>';
    } else {
        $html .= htmlspecialchars((string) $element);
    }
    return $html;
}
