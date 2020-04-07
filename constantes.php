<?php
/**
 * Created by PhpStorm.
 * User: baptiste
 * Date: 26/05/14
 * Time: 10:10
 */

// Constante pour gérer certaines facet
define('lab_delimiter', '_FacetSep_');

// Constante pour l'api utilisé
define('lab_api', 'http://api.archives-ouvertes.fr/search/hal/');

// Constante pour le webservice des autheurs utilisé
define('lab_urlauthor', 'http://api.archives-ouvertes.fr/ref/author/');

// Constante pour le CV HAL d'un auteur ayant un IdHAl
define('lab_cvhal', 'http://cv.archives-ouvertes.fr/');

// Constante pour la redirection vers le site halv3 onglet recherche
define('lab_halv3', 'https://hal.archives-ouvertes.fr/search/index/');

// Constante pour la redirection vers le site halv3 onglet accueil
define('lab_site', 'https://hal.archives-ouvertes.fr/');

// Constante pour le tri par date
define('lab_producedDateY', urlencode('producedDate_tdate desc'));

// Constante de langue
define('lab_locale', get_locale());

// Constante de Version USERAGENT
define('lab_version', '2.0.8');

// absolute path to this directory
define('LAB_DIR', dirname( __FILE__ ));
