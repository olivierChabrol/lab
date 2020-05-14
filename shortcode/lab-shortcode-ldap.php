<?php
function lab_ldap($args) {
    $BASE_DN = "ou=accounts,dc=i2m,dc=univ-amu,dc=fr";
    $ldap_link = ldap_connect("localhost");
    $result = ldap_search($ldap_link,$BASE_DN,"uid=chabrol");
    echo "<xmp>";
    var_dump(ldap_get_entries($ldap_link,$result)[0]);
    echo "</xmp>";
}
?>