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
        <label for="lab_user_name">Nom de l'utilisateur</label>
      </th>
      <td>
        <input type="text" name="lab_user_email" id="lab_user_search" value="" size="80" /><span id="lab_user_id"></span><br>
        <input type="hidden" id="lab_user_search_id" name="lab_user_search_id" value="" /><br>
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
  <div style="display:flex;">
    <form method='post'>
    <?php
    $sqlUser = "SELECT um1.`user_id`, um3.`meta_value` AS first_name, um2.`meta_value` AS last_name
            FROM `wp_usermeta` AS um1
            JOIN `wp_usermeta` AS um2 ON um1.`user_id` = um2.`user_id` 
            JOIN `wp_usermeta` AS um3 ON um1.`user_id` = um3.`user_id`
            WHERE um1.`meta_key`='last_name' 
              AND um2.`meta_key`='last_name' 
              AND um3.`meta_key`='first_name'
              AND um1.`user_id` NOT IN (SELECT `user_id` FROM `wp_lab_users_groups`)
              ORDER BY last_name";
    $sqlGroup = "SELECT `id` AS group_id, `group_name` 
                FROM wp_lab_groups";
    global $wpdb;
    $resultsUsers = $wpdb->get_results($sqlUser);
    $resultsGroups = $wpdb->get_results($sqlGroup);
    
    /*** CHOIX USER ***/ 
    $userSettingsStr  = "<div style='float: left; maring-right:50px;'>
                          <label for='users'>Choisissez une ou plusieurs personne(s) à affecter :
                          </label><br/><br/>";
    $userSettingsStr .= "<select name='users[]' multiple style='height:300px;'>";
    foreach ($resultsUsers as $r) {
      $userSettingsStr .= "<option value='" . $r->user_id . "'>";
      $userSettingsStr .= esc_html($r->first_name . " " . $r->last_name);
      $userSettingsStr .= "</option>";
    }
    $userSettingsStr   .= "</select></div>";

    /*** CHOIX GROUP ***/ 
    $userSettingsStr .= "<div style='float: right; margin-left:50px'>
                          <label for'groups'>Choisissez le ou les groupe(s) au(x)quel(s) vous allez affecter des personnes :
                          <label><br/><br/>";
    $userSettingsStr .= "<select name='groups[]' multiple style='height:150px;'>";
    foreach ($resultsGroups as $r) {
      $userSettingsStr .= "<option value='" . $r->group_id . "'>";
      $userSettingsStr .= esc_html($r->group_name);
      $userSettingsStr .= "</option>";
    } 
    $userSettingsStr .= "</select></div>";
    
    echo $userSettingsStr;
    $users = array();
    $groups = array();
    if(isset($_POST['submit'])) {
      if(isset($_POST['users']) && isset($_POST['groups'])){ 
        $selected_values = $_POST['users'];
        foreach($selected_values as $SV) { // users selected 
          $users[] = $SV; 
        } 
        $selected_values = $_POST['groups'];
        foreach($selected_values as $SV){ // groups selected
          $groups[] = $SV; 
        }
      }
      else {
        echo "Vous devez choisir au moins un utilisateur et un groupe !"; //TODO : burger pop-up
      }
    }

    foreach($groups as $g) {
      foreach($users as $u) {
        $wpdb->insert(
          $wpdb->prefix.'lab_users_groups',
          array(
            'group_id' => $g,
            'user_id' => $u
          )
        );
      }
      echo "Utilisateur(s) correctement(s) ajouté(s) au groupe";
    }
    ?>
    
    </div>
  <input type = 'submit' style='margin-top:10px;' name = 'submit' value = Submit>
  </form>
  <?php
}
