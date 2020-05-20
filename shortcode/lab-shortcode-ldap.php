<?php
/*
 * File Name: lab-shortcode-ldap.php
 * Description: shortcode pour afficher une page de gestion des utilisateurs dans le LDAP
 * Authors: Ivan Ivanov, Lucas Urgenti, Astrid Beyer
 * Version: 0.7
 * 
 */

function lab_ldap($args) {
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
                    <tbody id="lab_ldapListBody">
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
    $ldapStr .= editModal();
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

/**
 * Generate modal to edit
 *
 * @return void
 */
function editModal()
{
    $str = '<div class="modal" id="lab_admin_ldap_edit" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">

            <form action="javascript:lab_ldap_editUser()">
                <div class="modal-content">
                    <div class="modal-header">
                    <h5 class="modal-title">' . esc_html("Modifier une entrée LDAP","lab") . '</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    </div>
                    <div class="modal-body">
                        <p>
                            <input id="lab_ldap_edit_uid" name="uid" type="hidden"/>

                            <label for="lab_ldap_edit_givenName"><b>'.esc_html("Prénom","lab").'</b> (givenName) :</label>
                            <input type="text" id="lab_ldap_edit_givenName" name="givenName"/></br>

                            <label for="lab_ldap_edit_sn"><b>'.esc_html("Nom","lab").'</b> (sn) :</label>
                            <input type="text" id="lab_ldap_edit_sn" name="sn"/></br>

                            <label for="lab_ldap_edit_uidNumber"><b>'.esc_html("Numéro","lab").'</b> (uidNumber) :</label>
                            <input type="text" id="lab_ldap_edit_uidNumber" name="uidNumber"/></br>

                            <label for="lab_ldap_edit_homeDirectory"><b>Autohome</b> (homeDirectory) :</label>
                            <input type="text" id="lab_ldap_edit_homeDirectory" name="homeDirectory"/></br>

                            <label for="lab_ldap_edit_mail"><b>Mail</b> (mail) :</label>
                            <input type="text" id="lab_ldap_edit_mail" name="mail"/></br>
                        </p>
                    </div>
                    <div class="modal-footer">
                    <button type="button submit" class="btn btn-primary" id="saveEditLdapUser" data-dismiss="modal">'.esc_html("Sauvegarder","lab").'</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">'.esc_html("Fermer","lab").'</button>
                    </div>
                </div>
            </form>
            </div>
        </div>';
    return $str;
}