<?php
/*
 * File Name: lab-shortcode-ldap.php
 * Description: shortcode pour afficher une page de gestion des utilisateurs dans le LDAP
 * Authors: Ivan Ivanov, Lucas Urgenti
 * Version: 0.1
 */
function lab_ldap($args) {
    $BASE_DN = "ou=accounts,dc=i2m,dc=univ-amu,dc=fr";
    $ldap_link = ldap_connect("localhost");
    $result = ldap_search($ldap_link,$BASE_DN,"uid=chabrol");
    echo "<xmp>";
    var_dump(ldap_get_entries($ldap_link,$result)[0]);
    echo "</xmp>";
}
?>