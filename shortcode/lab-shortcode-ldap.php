<?php
/*
 * File Name: lab-shortcode-ldap.php
 * Description: shortcode pour afficher une page de gestion des utilisateurs dans le LDAP
 * Authors: Ivan Ivanov, Lucas Urgenti
 * Version: 0.1
 */
function lab_ldap($args) {
    $BASE = "dc=i2m,dc=univ-amu,dc=fr";
    $ldap_link = ldap_connect("localhost");
    $ldap_bind = ldap_bind($ldap_link, 'cn=admin,'.$BASE,'aze');
    var_dump($ldap_bind);
    $result = ldap_search($ldap_link,'ou=accounts,'.$BASE,"uid=chabrol");
    echo "<xmp>";
    var_dump(ldap_get_entries($ldap_link,$result)[0]);
    echo "</xmp>";
}

$ldapStr = '<div id="lab_ldap_form">
                <h2>'.esc_html__("Créer ou modifier un utilisateur","lab").'</h2>';
$ldapStr .= '
    <form action="">
            '
?>