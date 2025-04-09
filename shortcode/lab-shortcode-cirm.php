<?php
/*
 * File Name: lab-shortcode-directory.php
 * Description: shortcode pour générer un annuaire
 * Authors: Astrid BEYER, Lucas URGENTI
 * Version: 1.0
*/


/**
 * Load new events from CIRM and save them in the database
 * 
 * @param boolean $debug if true, display debug information
 * @return void
 */
function lab_cirm_load_new_events($debug) {
    if ($debug) {
        echo "<h3>lab_cirm_load_new_events</h3>";
    }
    $events = array();
    $dateToday = date('Y-m-d');
    $token = get_cirm_token($debug);
    if ($debug) {
        echo "<h2>token :</h2><br>".$token."<br>";
    }
    if ($token == null) {
        echo "<h2>Erreur d'authentification</h2>";
        return;
    }
    $events = load_cirm_events($events, $token, $dateToday);

    $nb_events = count($events);
    $nb_jours = 0;
    while($nb_events < 10 && $nb_jours < 15) {
        //echo 'Nb Event: ' . count($events) . "<br>";
        $dateToday = date('Y-m-d', strtotime($dateToday . ' + 1 days'));
        //echo("<h3>Date : $dateToday</h3><br>");
        $events = load_cirm_events($events, $token, $dateToday);
        $nb_events = count($events);
        $nb_jours += 1;
    }

    //echo 'Nb Event: ' . count($events) . "<br>";
    foreach($events as $event) {
        save_event($event);
    }
}

/**
 * Save a CIRM event in the database
 * 
 * @param array $event Event to save. It should have the following keys:
 * - id: CIRM event id
 * - frenchTitle: Title of the event
 * - talks: Array of speakers
 * - date: Date of the event
 * 
 * @return void
 */
function save_event($event) {
    echo '<h2>Save event : ' . $event['frenchTitle'] . '</h2><br>';
    $date_split = explode('-', $event['date']);
    echo " date_split[0] : " . $date_split[0] . "<br>";
    echo " date_split[1] : " . $date_split[1] . "<br>";
    global $wpdb;
    $table_name = $wpdb->prefix . 'lab_cirm_events';
    
    $sql = "SELECT * FROM $table_name WHERE cirm_id = " . $event['id'];
    $results = $wpdb->get_results($sql);
    if (count($results) == 0) {
        $speakers = "";
        foreach($event['talks'] as $speaker) {
            $speakers .= $speaker;
            $speakers .= ', ';
        }
        $speakers = rtrim($speakers, ', ');
        $sql = "INSERT INTO $table_name (cirm_id, title, speakers, begin_date, end_date) VALUES (%s, %s, %s, %s, %s)";
        $wpdb->insert($table_name, array(
            'cirm_id' => $event['id'],
            'title' => $event['frenchTitle'],
            'speakers' => $speakers,
            'begin_date' => $event['date'],
            'end_date' => $event['date'],
        ));
    } else {
        echo "Event " . $event['frenchTitle'] . " already exists<br>";
    }
 
}
/**
 * Load events from the database that happen after the given date.
 * 
 * @param string $date Date in the format "YYYY-MM-DD".
 * @return void
 */
function load_local_cirm_events($date, $debug = false) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'lab_cirm_events';
    $sql = "SELECT * FROM $table_name WHERE begin_date >= '" . $date."' ORDER BY begin_date ASC";
    if($debug) {
        echo "<h3>load_local_cirm_events</h3>";
        echo "Date : $date<br>";
        echo "sql : $sql<br>";
    }

    $results = $wpdb->get_results($sql);
    if (count($results) == 0) {
        echo "Aucun événement trouvé<br>";
    } else {
        echo('<div class="em pixelbones em-list-widget em-events-widget"><ul>');
        foreach($results as $event) {
            $date_split = explode('-', $event->begin_date);
            echo('<li>');
            echo('<div class="bloc-cirm" style="padding-left: 4pt;">');
            echo('<div class="em-item-info">');
            echo('<span class="jour">'.$date_split[2].'</span>');
            echo('/'.$date_split[1].'/'.$date_split[0].' <span style="padding-left: 3em">&nbsp;</span>');
            echo('<div style="margin-top: -12pt; font-weight: bold">'.$event->title.'</div>');
            echo('</div></div>');
            echo('<div class="em-item-name">');
            echo('<strong>');
            echo($event->speakers);
            echo('</strong>');
            echo('</div>');
            echo('</li>');
        }
        echo('</ul></div>');
    }

}


/*** 
 * Shortcode use : [lab-cirm]
     load="yes" OR load="no"
     debug="yes" OR debug="no"
***/ 
function lab_cirm($atts) {
    $atts = shortcode_atts(array(
        'load'    => get_option('lab-cirm'),
        'debug'    => get_option('lab-cirm'),
    ), $atts, "lab-cirm");
    //var_dump($atts);
    $debug = false;
    if (isset($atts['debug'])) {
        if ($atts['debug'] == 'yes') {
            $debug = true;
        }
    }
    if($debug) {
        echo "<h3>lab_cirm</h3>";
        echo "Load : " . $atts['load'] . "<br>";
    }
    
    if ($atts['load'] == 'yes') {
        lab_cirm_load_new_events($debug);
    }
    else {
        $events = array();
        
        $dateToday = date('Y-m-d');
        load_local_cirm_events($dateToday);
    }
}

function load_cirm_events($events, $token, $date) {
    // echo "[load_cirm_events] Date : $date, nb events jusqu'ici : " . count($events) . "<br>";
    $apiHost = "https://app.cirm-math.fr/api";
    $apiUrl = "$apiHost/meetings?page=1&meetingDate=$date&isValidStatus=true";
  
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
                        //var_dump($member['talks']);
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

function get_cirm_token($debug = false) {
    if ($debug) {
        echo "<h3>get_cirm_token</h3>";
    }
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
        echo '[get_cirm_token] Erreur : ' . curl_error($ch);
    } else {
        if ($debug) {
            echo "<h2>get_cirm_token</h2>";
            echo '[get_cirm_token] Réponse : ' . $response;
        }
        $response = json_decode($response, true);
        if(isset($response["status"]))
        {
            if ($response["status"] == "500") {
                if ($debug) {
                    echo "[get_cirm_token] Erreur : " . $response["message"];
                }
            }
            else {
                if ($debug) {
                    echo "[get_cirm_token] Erreur : " . $response["status"];
                }
            }
            return null;
        }
        else {
            curl_close($ch);
            return $response['token'];
        }
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
