<?php

/**
 * Function for the groups management
 */
function lab_admin_tab_groups() {
  ?>
  <div>
    <label for="wp_lab_group_name">Nom du groupe</label>
    <input type="text" name="wp_lab_group_name" id="wp_lab_group_name" value="" size="80"/>
    <button class="page-title-action" id="lab_group_delete_button">Supprimer le groupe</button><br>
    <input type="hidden" id="lab_searched_group_id" name="lab_searched_group_id" value=""/>
    
  </div>
  <div id="suppr_result"></div>
<hr>
 <!-- Modifier un groupe -->
  <div class="wp_lab_editGroup_form">
    <h3>Modifier un groupe</h3>
    <label for="wp_lab_group_acronym_edit">Modifier l'acronyme :</label>
    <input type="text" name="wp_lab_acronym" id="wp_lab_group_acronym_edit" value="" size=10 placeholder="AA"/>
    <label for="wp_lab_group_name_edit">Nouveau nom du groupe :</label>
    <input type="text" name="wp_lab_group_name" id="wp_lab_group_name_edit" value="" size=50 placeholder="Nouveau nom"/><br /><br />
    <label for="wp_lab_group_chief_edit">Définir un autre chef du groupe :</label>
    <input required type="text" name="wp_lab_group_chief" id="wp_lab_group_chief_edit" placeholder="Group leader"/>
    <label for="lab_group_edit_substitutes">Co responsable : </label><span id="lab_group_edit_substitutes"></span>
    <br /><br />
    <label for="lab_group_edit_substitutes">Add substitute : </label><input type="text" name="lab_group_edit_add_substitute" id="lab_group_edit_add_substitute" value="" size=50 placeholder="Substitute"/>
    <input type="hidden" id="lab_group_edit_add_substitute_id">
    <br /><br />
    <input type="hidden" id="lab_searched_chief_id" name="lab_searched_chief_id" />
    <label for="wp_lab_group_parent_edit">Modifier le groupe parent :</label>
    <?php lab_html_select("wp_lab_group_parent_edit", "wp_lab_group_parent", "", lab_admin_group_select_group, "group_name", array("value"=>0,"label"=>"None")); ?>
    <label for="wp_lab_group_type_edit">Modifier le type :</label>
    <?php lab_html_select("wp_lab_group_type_edit", "wp_lab_group_type", "", lab_admin_get_params_groupTypes); ?>
    <br />
    
    <br />
    
    
    <br /><a href="#" class="page-title-action" id="lab_admin_group_edit_button">Save</a>
  </div>
  <hr>
  <!-- Gestion des tables -->
  <?php
    if (!lab_admin_checkTable("wp_lab_groups")) {
      echo "<p id='lab_group_noTableWarning'>La table <em>wp_lab_groups</em> n'a pas été trouvée dans la base, vous devez d'abord la créer ici : </p>";
    }
    if (!lab_admin_checkTable("wp_lab_group_substitutes")) {
      echo "<p id='lab_group_noSubTableWarning'>La table <em>wp_lab_group_substitutes</em> n'a pas été trouvée dans la base, vous devez d'abord la créer ici : </p>";
    }
  ?>
  <button class="page-title-action" id="lab_createGroup_createTable">Créer la table Groups</button>
  <button class="page-title-action" id="lab_createGroup_createTable_Sub">Créer la table Substitutes</button>
  <button class="page-title-action" id="lab_createGroup_createRoot">Créer groupe root</button>
  <hr/>
  <table class="form-table" role="presentation">
  <h3>Create group : </h3>
  <form action="javascript:void(0);">
	<tbody>
    <tr class="form-field form-required">
      <th scope="row"><label for="lab_createGroup_name">Nom du groupe* : </label></th>
      <td><input type="text" id="lab_createGroup_name" name="lab_createGroup_name" placeholder="ex: Analyse Appliquée"/></td>
    </tr class="form-field form-required">
      <th scope="row"><label for="lab_createGroup_acronym">Acronyme* <span class="description">(unique)</span> : </label></th>
      <td>
        <input type="text" id="lab_createGroup_acronym" name="lab_createGroup_acronym" placeholder="Acronym"/>
        <label style="padding-left:2em;" id="lab_createGroupe_acronym_hint"></span>
      </td>
    </tr>
    <tr class="form-field">
      <th scope="row"><label for="lab_createGroup_parentGroup">Groupe parent :</label></th>
      <td>
        <?php lab_html_select("lab_createGroup_parent", "lab_createGroup_parent", "", lab_admin_group_select_group, "acronym", array("value"=>0,"label"=>"None")); ?>
      </td>
    </tr>
    <tr class="form-field">
      <th scope="row"><label for="lab_createGroup_type">Type :</label></th>
      <td>
      <?php lab_html_select("lab_createGroup_type", "lab_createGroup_Type", "", lab_admin_get_params_groupTypes); ?>
      </td>
    </tr>
    <tr class="form-field">
      <th scope="row"><label for="lab_createGroup_chief">Responsable du groupe :</label></th>
      <td>
        <input id="lab_createGroup_chief" type="text" name="lab_createGroup_chief" placeholder="Pascal HUBERT"/>
        <input type="hidden" id="lab_createGroup_chiefID"/>
      </td>
    </tr>
    <tr class="form-field">
      <th scope="row"><label for="lab_createGroup_subInput">Suppléants <span class="description">(facultatif) </span>:</label></th>
      <td colspan="2">
        <p id="lab_createGroup_subsList"></p><span style="display:none; cursor:pointer;" id="lab_createGroup_subsDelete">❌ Vider la liste</span><span style="display: none;" id="lab_createGroup_subsIDList"></span>
      </td>
    </tr>
    <tr class="form-field">
      <td colspan="2" >
        <input id="lab_createGroup_subInput" type="text" name="lab_createGroup_subInput" placeholder="N'oubliez pas d'appuyer sur ajouter"/>
        <input type="hidden" id="lab_createGroup_subID" list=""/> 
      </td>
      <td><input type="button" id="lab_createGroup_addSub" class="page-title-action" value="+ Ajouter un suppléant"/></td>
    </tr>
    <tr class="form-field">
      <td><input class="page-title-action" type="submit" id="lab_createGroup_create" value="Créer le groupe"/></td>
    </tr>
	</tbody></form></table>
  <br />
  <hr />

<?php
}