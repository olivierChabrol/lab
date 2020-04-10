<?php
/*
 * File Name: lab-shortcode-directory.php
 * Description: shortcode pour générer un annuaire
 * Authors: Astrid BEYER, Lucas URGENTI
 * Version: 0.1
*/

/* TODO: Titre; 26 lettres (par défaut sur A); champ de recherche
 * Afficher les gens qui ont un NOM qui commence par telle lettre
 * Afficher leurs numéros de tel (si il y en a un) et leur mail
 * /!\ mail : empecher de rendre le mail clickable (image à la place du @ par exemple)
 * pour plus tard : afficher que les membres d'un certain groupe, ceux qui sont partis...
*/

function lab_directory($param) {
    extract(shortcode_atts(array(
        'name' => get_option('name'),
        'group' => get_option('group')
    ),
        $param
    ));
    
    $sql = "SELECT um1.`user_id`    AS id, um3.`meta_value` AS first_name, um2.`meta_value` AS last_name,
                   um4.`user_email` AS mail
            FROM `wp_usermeta` AS um1 
            JOIN `wp_usermeta` AS um2 ON um1.`user_id` = um2.`user_id` 
            JOIN `wp_usermeta` AS um3 ON um1.`user_id` = um3.`user_id` 
            JOIN `wp_users`    AS um4 ON um1.`user_id` = um4.`ID`
            WHERE um1.`meta_key`='last_name' 
                AND um2.`meta_key`='last_name' 
                AND um3.`meta_key`='first_name'";

    $currentLetter = $_GET["letter"];
    if (isset($currentLetter) && $currentLetter != "") {
        $sql .= " AND um1.`meta_value`LIKE '$currentLetter%'
                ORDER BY last_name";
    }
    else {
        $sql .= " AND um1.`meta_value`LIKE 'A%'
                ORDER BY last_name";
    } // if there's no letter selected, it's by default on A

    global $wpdb;
    $results = $wpdb->get_results($sql);
    $nbResult = $wpdb->num_rows;
    $items = array();
    $alphachar = array_merge(range('A', 'Z'));
    $url = explode('/?', $_SERVER['REQUEST_URI']); // current url (without parameters)
    foreach ($alphachar as $element) {
        echo '<a href="' . $url[0] . '?letter=' . $element . '"><b>' . $element . '</b></a><span style="padding-right:12px;"></span>'; 
    } //url des lettres

    $directoryStr = "<h1>Annuaire</h1>"; //titre
    $directoryStr .= "<div class=\"alpha-links\" style=\"font-size:15px;\">"; // lettres
    $directoryStr .= 
        "<br><form id='dud_user_srch' method='post'>
            <div id='user-srch' style='width:350px;'>
                <input type='text' id='dud_user_srch_val' name='dud_user_srch_val' style='' value='' maxlength='50' placeholder='Chercher un nom'/>
                <button type='submit' id='dud_user_srch_submit' name='dud_user_srch_submit' value=''>
                    <i class='fa fa-search fa-lg' aria-hidden='true'></i>
                </button>
            </div>
        </form><br>"; // champ de recherche + bouton de recherche
    $directoryStr .= 
        "<style>
            tr:nth-child(even) {
                background-color: #f2f2f2;
            }
        </style>"; // style pour le tableau (lignes de couleurs différentes)

    /* Tableau annuaire */
    $directoryStr .= "<table>";
    foreach ($results as $r) {
        $directoryStr .= "<tr>";
        $directoryStr .= "<td>" . esc_html($r->first_name . " " . $r->last_name . "     " . $r->mail) . "</td>";
        $directoryStr .= "</tr>";
    }
    $directoryStr .= "</table>";
    return $directoryStr; 
}