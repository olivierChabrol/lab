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
    /* **** CONNEXION TEST - SUCCESS! [do not erase if you want to test] ****
    if($lb) {
        echo("Connecté avec succès ! ");
    } else {
        $errname = ldap_error($lc);
        $errno   = ldap_errno($lc);
        echo "Problème à la connexion : " . $errno . " - " . $errname . "</br>";
    }
    // [do not erase if you want to test] */
    $ldapStr = '
    <div class="d-flex justify-content-between bd-highlight mb-3">
        <div class="p-2">
            <h3>Parcourir l\'annuaire LDAP</h3>
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
    
    $ldapStr .= lab_ldap_list_update(LAB_LDAP::getLink(),LAB_LDAP::LDAP_BASE);

    $ldapStr .= '           
                </tbody>
                </table>
            </div>';

    $ldapStr .= '<div id="lab_pages">'.lab_ldap_pagination(1,1).'</div></div>
    <div class="p-2"> <br/><br/></div>';
    ldap_close(LAB_LDAP::getLink());
    
    $ldapStr .= '<div class="p-2"><h3>Chercher une personne dans LDAP</h3>
    <label for="">Précisez le mail : </label><br/>
    <input type="mail"></input></div>';
    return $ldapStr;
    //echo lab_ldap_addUser("JEAN EUDE", "Michel-Pierre","jemp@univ-amu.fr",'$P$B0v6kIJqQ.AN.VF.QxLmRyqAhvLOEt1',"i12345678",random_int(10000,11000),"TestOrg");
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

//get_ldap_data_from_mail("asaf1985@hotmail.com");

?>