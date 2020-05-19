<?php
/*
 * File Name: lab-shortcode-ldap.php
 * Description: shortcode pour afficher une page de gestion des utilisateurs dans le LDAP
 * Authors: Ivan Ivanov, Lucas Urgenti, Astrid Beyer
 * Version: 0.5
 * 
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
    */

    //TODO : pagination, bouton détail (récupère attributs LDAP)
    $ldapStr = '
    <div class="d-flex justify-content-between bd-highlight mb-3">
        <div class="p-2">
            <h3>' . esc_html("Parcourir l'annuaire LDAP", "lab") . '</h3>
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
                <table id="lab-table-ldap" class="table table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>'.esc_html__("Nom", "lab").'</th>
                            <th>'.esc_html__("Action", "lab").'</th>
                        <tr>
                    </thead>
                <tbody id="lab_ldapListBody">';
    
    //$ldapStr .= lab_ldap_list_update($lc,$BASE);

    $ldapStr .= '           
        </tbody>
    </table>
    </div>';

    $ldapStr .= '<div id="lab_pages">'.lab_ldap_pagination(1,1).'</div></div>
    <div class="p-2"> <br/><br/></div>';
    ldap_close($lc);
    
    $ldapStr .= '<div class="p-2"><h3>' . esc_html("Chercher une personne dans LDAP", "lab") .'</h3>
        <form action="" method="post">
            <label for="mailLdap">'     . esc_html("Précisez le mail : ", "lab") . '</label><br/>
            <input type="mail" id="mailLdap" name="mailLdap" placeholder="mail@exemple.com"></input>
        
            <input type="submit" value="Envoyer">
        </form></br>';
    
    $mail = $_POST['mailLdap'];
    if (isset($mail)) {
        $ldapStr .= '<h5>Résultat de la recherche pour ' . $mail . '</h5>
                    <ul>
                        <li><b>'.esc_html("Nom","lab").'</b> : '      . (get_ldap_data_from_mail($mail)[0]) . '</li>
                        <li><b>'.esc_html("Prénom","lab").'</b> : '   . (get_ldap_data_from_mail($mail)[1]) . '</li>
                        <li><b>'.esc_html("Login","lab").'</b> : '    . (get_ldap_data_from_mail($mail)[2]) . '</li>
                    </ul></div>';
    }

    return $ldapStr;

}
function lab_ldap_pagination($pages, $currentPage) {
    $out = '<ul id="pagination-digg">';
    $out .= '<li class="page_previous'.($currentPage>1 ? '">' : ' gris">').'« Précédent</li>';
    if($pages <= 10)
    {
        for ($i=1; $i<=$pages; $i++) {
            $out .= '<li page='.$i.' class="page_number"'.($currentPage!=$i ? ">$i" : " id='active'>$i").'</li>';
        }
    }
    else
    {
        for ($i=1; $i<=$pages; $i++) {
            if($i == $currentPage -2 || $i == $currentPage -1 || $i == $currentPage || $i == $currentPage +1 || $i == $currentPage +2){
                $out .= '<li page='.$i.' class="page_number"'.($currentPage!=$i ? ">$i" : " id='active'>$i").'</li>';
            }
            else{
                if($i % 10 == 0 || $i == 1 || $i == $pages) {
                    $out .= '<li page='.$i.' class="page_number"'.($currentPage!=$i ? ">$i" : " id='active'>$i").'</li>';
                }

            }
        }
    }
    $out .= '<li class="page_next'.($pages>1 && $currentPage<$pages ? '">' : ' gris">').'Suivant »</li>';
    $out .= '</ul>';
    return $out;
}

//get_ldap_data_from_mail("asaf1985@hotmail.com");

?>