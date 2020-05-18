<?php
/*
 * File Name: lab-shortcode-ldap.php
 * Description: shortcode pour afficher une page de gestion des utilisateurs dans le LDAP
 * Authors: Ivan Ivanov, Lucas Urgenti, Astrid Beyer
 * Version: 0.1
 * 
 * TODO : à partir d'un e-mail donné -> ramener les champs suivants pour la fonction d'Ivan : 
 *  nom, prénom, mail, login (uid)
 */
function lab_ldap($args) {
    $BASE = "dc=i2m,dc=univ-amu,dc=fr";
    $lc = ldap_connect("localhost","389")
        or die ("Impossible de se connecter au serveur LDAP.");
    ldap_set_option($lc, LDAP_OPT_PROTOCOL_VERSION,3);
    $lb = ldap_bind($lc, 'cn=admin,'.$BASE,'root');

    /* **** CONNEXION TEST - SUCCESS! [do not erase if you want to test] ****
    if($lb) {
        echo("Connecté avec succès ! ");
    } else {
        $errname = ldap_error($lc);
        $errno   = ldap_errno($lc);
        echo "Problème à la connexion : " . $errno . " - " . $errname . "</br>";
    }
    // [do not erase if you want to test] */

    //TODO : pagination, bouton détail (récupère attributs LDAP)
    $ldapStr = '
    <div>    
        <label for="lab_results_number">'.esc_html__("Nombre de résultats par page","lab").' : </label>
        <select id="lab_results_number">
            <option selected value="5">5</option>
            <option value="10">10</option>
            <option value="20">20</option>
            <option value="50">50</option>
        </select>
    </div>
    <div class="table-responsive">
        <table id="lab-table-directory" class="table table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>'.esc_html__("Nom", "lab").'</th>
                    <th>'.esc_html__("Action", "lab").'</th>
                <tr>
            </thead>
        <tbody>';
    
    $ldapStr .= lab_ldap_list_update($lc,$BASE);

    $ldapStr .= '           
        </tbody>
    </table>
    </div>';

    $ldapStr .= '<div id="lab_pages">'.lab_ldap_pagination(1,1).'</div><br/><br/>';
        
    return $ldapStr;

}
function lab_ldap_pagination($pages, $currentPage) {
    $out = '<ul id="pagination-digg">';
    $out .= '<li class="page_previous'.($currentPage>1 ? '">' : ' gris">').'« Précédent</li>';
    for ($i=1; $i<=$pages; $i++) {
        $out .= '<li page='.$i.' class="page_number"'.($currentPage!=$i ? ">$i" : " id='active'>$i").'</li>';
    }
    $out .= '<li class="page_next'.($pages>1 && $currentPage<$pages ? '">' : ' gris">').'Suivant »</li>';
    $out .= '</ul>';
    return $out;
}
function getLdapDataFromMail($mail) {
    /*
     * TODO : à partir d'un e-mail donné -> ramener les champs suivants pour la fonction d'Ivan : 
     *  nom, prénom, mail, login (uid)
     * */

    $ds = ldap_connect("localhost","389");
    $dn = "cn=admin,dc=i2m,dc=univ-amu,dc=fr";
    $filter = "(objectclass=" . $mail . ")";
    $justThese = array("givenName", "sn", "mail", "uid");
    $sr = ldap_read($ds, $dn, $filter, $justThese);
    $entry = ldap_get_entries($ds,$sr);

    /*
    $search   = array("givenName","sn","mail","uid"); // what we are looking for
    $attrRead = ldap_read("localhost", "cn=admin,dc=i2m,dc=univ-amu,dc=fr", 
                "(objectclass=" . $mail .  ")", $search);
    $entry    = ldap_get_entries("localhost","cn=admin,dc=i2m,dc=univ-amu,dc=fr");
    */
    echo $entry[0]["sn"][0] . " est le nom de famille recherché via mail";
}


getLdapDataFromMail("macclavio@gmail.com");
?>