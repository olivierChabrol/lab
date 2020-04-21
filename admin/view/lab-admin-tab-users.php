<?php
/*
 * File Name: lab-admin-tab-users.php
 * Description: interface de paramètres utilisateurs, affecter un groupe à un utilisateur
 * Authors: Olivier CHABROL, Astrid BEYER, Lucas URGENTI
 * Version: 1.0
*/

function lab_admin_tab_user() {
?>
  <table class="form-table" role="presentation">
    <tr class="user-rich-editing-wrap">
      <th scope="row">
        <label for="lab_user_name">Nom de l'utilisateur 1</label>
      </th>
      <td>
        <input type="text"   id="lab_user_search"    value="" size="80" /><br>
        <input type="hidden" id="lab_user_search_id" value="" /><br>
      </td>
    </tr>
    <tr>
      <td>
        <label for="lab_user_left">Parti</label>
      </td>
      <td>
        <input type="checkbox" id="lab_user_left"> <label for="lab_user_left_date">Date de départ</label><input type="text" id="lab_user_left_date">
        <input type="hidden" id="lab_usermeta_id">
      </td>
    </tr>
  </table>
  <a href="#" class="page-title-action" id="lab_user_button_save_left">Modifier le statut de l'utilisateur</a>
  <br/><br/>
  <h3>Affecter des utilisateurs à des groupes</h3>
  
  <label for="lab_all_users"><b>Afficher aussi les personnes qui ont déjà un groupe</b></label>
  <input type="checkbox" id="lab_all_users"/><br/>
  <label for="lab_no_users_left"><b>Afficher aussi les utilisateurs qui ont quitté l'Institut</b></label>
  <input type="checkbox" id="lab_no_users_left"/>
  <br/><br/>

  <div style="display:flex;">
    
    <!-- CHOIX USER -->

    <div style='float: left; maring-right:50px;'>
                          <label for='users'>Choisissez une ou plusieurs personne(s) à affecter :
                          </label><br/><br/>
    <select id='list_users' name='users[]' multiple style='height:300px;'></select></div>
    
    <!-- CHOIX GROUP -->

    <div style='float: right; margin-left:50px'>
                          <label for='groups'>Choisissez le ou les groupe(s) au(x)quel(s) vous allez affecter des personnes :
                          </label><br/><br/>
    <select id='list_groups' name='groups[]' multiple style='height:150px;'></select></div>
  
  </div>
  <button style='margin-top:10px;' id='lab_add_users_groups'>submit</button>
  <?php
}
