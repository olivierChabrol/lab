<?php
function lab_ldap($args) {
    $BASE_DN = "ou=accounts,dc=i2m,dc=univ-amu,dc=fr";
    var_dump($ldap_link = ldap_connect("localhost"));
    var_dump(ldap_search($ldap_link,$BASE_DN,"uid=chabrol"));
}
?>