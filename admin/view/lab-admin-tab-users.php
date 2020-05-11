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
        <label for="lab_user_name"><?php esc_html_e('Nom de l\'utilisateur','lab') ?></label>
      </th>
      <td>
        <input type="text"   id="lab_user_search"    value="" size="80" /><br>
        <input type="hidden" id="lab_user_search_id" value="" /><br>
      </td>
    </tr>
    <tr>
      <td>
        <label for="lab_user_left"><?php esc_html_e('Parti','lab') ?></label>
      </td>
      <td>
        <input type="checkbox" id="lab_user_left"> <label for="lab_user_left_date"><?php esc_html_e('Date de départ','lab') ?></label><input type="text" id="lab_user_left_date">
        <input type="hidden" id="lab_usermeta_id">
      </td>
    </tr>
  </table>
  <a href="#" class="page-title-action" id="lab_user_button_save_left"><?php esc_html_e('Modifier le statut de l\'utilisateur','lab') ?></a>
  <br/><br/>
  <h3><?php esc_html_e('Affecter des utilisateurs à des groupes','lab') ?></h3>
  
  <label for="lab_all_users"><b><?php esc_html_e('Afficher aussi les personnes qui ont déjà un groupe', 'lab') ?></b></label>
  <input type="checkbox" id="lab_all_users"/><br/>
  <label for="lab_no_users_left"><b><?php esc_html_e('Afficher aussi les utilisateurs qui ont quitté l\'Institut','lab') ?></b></label>
  <input type="checkbox" id="lab_no_users_left"/>
  <br/><br/>

  <div style="display:flex;">
    
    <!-- CHOIX USER -->

    <div style='float: left; margin-right:50px;'>
                          <label for='users'><?php esc_html_e('Choisissez une ou plusieurs personne(s) à affecter :','lab') ?>
                          </label><br/><br/>
    <select id='list_users' name='users[]' multiple style='height:300px;'></select></div>
    
    <!-- CHOIX GROUP -->

    <div style='float: right; margin-left:50px'>
                          <label for='groups'><?php esc_html_e('Choisissez le ou les groupe(s) au(x)quel(s) vous allez affecter des personnes :', 'lab') ?>
                          </label><br/><br/>
    <select id='list_groups' name='groups[]' multiple style='height:150px;'></select></div>
  
  </div>
  <button style='margin-top:10px;' id='lab_add_users_groups'><?php esc_html_e('envoyer','lab') ?></button>
  <?php
}
