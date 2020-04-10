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

/* TODO: Barre de recherche sur le prénom/nom (utiliser la fonction dans lab-admin-core)
 * qui filtre les résultats dans l'onglet de la lettre sélectionée.
*/
//include 'lab-admin-core.php';

function lab_directory($param) {
    extract(shortcode_atts(array(
        'name' => get_option('name'),
        //'group' => get_option('group')
    ),
        $param
    ));
    $url = explode('?', $_SERVER['REQUEST_URI']); // current url (without parameters)
    $sql = "SELECT um1.`user_id` AS id, um3.`meta_value` as first_name, um2.`meta_value` as last_name 
            FROM `wp_usermeta` AS um1 JOIN `wp_usermeta` AS um2 ON um1.`user_id` = um2.`user_id` 
            JOIN `wp_usermeta` AS um3 ON um1.`user_id` = um3.`user_id` 
            WHERE um1.`meta_key`='last_name' 
                AND um2.`meta_key`='last_name' 
                AND um3.`meta_key`='first_name' 
                AND um1.`meta_value`LIKE 'A%'";
    global $wpdb;
    $results = $wpdb->get_results($sql);
    //$nbResult = $wpdb->num_rows;
    $items = array();
    foreach ($results as $r) {
        $items[] = array(label => $r->first_name . " " . $r->last_name , value => $r->id);
    }
    //$directoryStr = "<h1>Annuaire</h1>";
    $alphachar = array_merge(range('A', 'Z'));
    foreach ($alphachar as $element) {
        echo '<a href="' . $url[0] . '?letter=' . $element . '"><b>' . $element . '</b></a><span style="padding-right:12px;"></span>'; 
    } //url des lettres
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
    for($i = 0; )
    return $directoryStr; 
}

// exemple d'utilisation : [lab_old_event year=2001]
/* // exemple de shortcode :
function lab_old_event($param)
{
  extract(shortcode_atts(array(
        'slug' => get_option('option_event'),
        'year' => get_option('option_event')
    ),
        $param
  ));
   $sqlYearCondition = "";
   if (isset($year) && $year != "") {
     $sqlYearCondition = " AND YEAR(`p`.`event_end_date`)=".$year." ";
  }
  $eventCaterory = get_option('option_event');
  $sql = "SELECT p.* FROM `wp_terms` AS t JOIN `wp_term_relationships` AS tr ON
     tr.`term_taxonomy_id`=t.`term_id` JOIN `wp_em_events` as p ON p.`post_id`=tr.`object_id` 
    WHERE t.slug='".$slug."'".$sqlYearCondition." AND `p`.`event_end_date` < NOW() 
    ORDER BY `p`.`event_end_date` DESC ";
  global $wpdb;
  $results = $wpdb->get_results($sql);
  $listEventStr = "<table>";
  $url = esc_url(home_url('/'));
  foreach ( $results as $r )
  {
    $listEventStr .= "<tr>";
    $listEventStr .= "<td>".esc_html($r->event_start_date)."</td><td><a href=\"".$url."event/".
        $r->event_slug."\">".$r->event_name."</a></td>";
    $listEventStr .= "</tr>";
  }
  $listEventStr .= "</table>";
  //return "category de l'evenement : ".$event."<br>".$sql."<br>".$listEventStr;
  return $listEventStr;  */