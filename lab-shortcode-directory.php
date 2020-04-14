<?php
/*
 * File Name: lab-shortcode-directory.php
 * Description: shortcode directory ?
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

function lab_directory($param) {
    extract(shortcode_atts(array(
        'attribut1' => get_option('option_directory'),
        'attribut2' => get_option('option_directory')
    ),
        $param
  ));
  // SQL
}