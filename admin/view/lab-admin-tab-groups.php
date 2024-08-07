<?php

/**
 * Function for the groups management
 */
function lab_admin_tab_groups() {
  ?>
  <div id="loadingAjaxGif"><img src="/wp-content/plugins/lab/loading.gif"/></div>
  <div>
    <label for="wp_lab_group_name"><?php esc_html_e('Group name','lab'); ?></label>
    <input type="text" name="wp_lab_group_name" id="wp_lab_group_name" value="" size="80"/>
    <button class="page-title-action" id="lab_group_delete_button"><?php esc_html_e('Delete group','lab'); ?></button><br>
    <input type="hidden" id="lab_searched_group_id" name="lab_searched_group_id" value=""/>
    
  </div>
  <div id="suppr_result"></div>
<hr>
 <!-- Modifier un groupe -->
  <div class="wp_lab_editGroup_form">
    <h3><?php esc_html_e("Modify group", "lab")?></h3>
    <label for="wp_lab_group_acronym_edit"><?php esc_html_e("Modify acronym",'lab'); ?> :</label>
    <input type="text" name="wp_lab_acronym" id="wp_lab_group_acronym_edit" value="" size=10 placeholder="AA"/>
    <label for="wp_lab_group_name_edit"><?php esc_html_e("New group name",'lab'); ?> :</label>
    <input type="text" name="wp_lab_group_name" id="wp_lab_group_name_edit" value="" size=50 placeholder="Nouveau nom"/><br /><br />
    <!--<label for="wp_lab_group_chief_edit"><?php/* esc_html_e("Define another group leader",'lab'); */?> :</label>
    <input required type="text" name="wp_lab_group_chief" id="wp_lab_group_chief_edit" placeholder="Group leader"/>
    <label for="lab_group_edit_substitutes"><?php/* esc_html_e("Co-reponsible",'lab'); */?> : </label><span id="lab_group_edit_substitutes"></span>
    <br /><br />
    <label for="lab_group_edit_substitutes"><?php/* esc_html_e("Add a co-responsible",'lab'); */?> : </label><input type="text" name="lab_group_edit_add_substitute" id="lab_group_edit_add_substitute" value="" size=50 placeholder="Substitute"/>
    <input type="hidden" id="lab_group_edit_add_substitute_id">
    <br /><br />
    <input type="hidden" id="lab_searched_chief_id" name="lab_searched_chief_id" />-->
    <label for="wp_lab_group_parent_edit"><?php esc_html_e("Modify parent group",'lab'); ?> :</label>
    <?php lab_html_select("wp_lab_group_parent_edit", "wp_lab_group_parent", "", "lab_admin_group_select_group", "group_name", array("value"=>0,"label"=>"None")); ?>
    <label for="wp_lab_group_type_edit"><?php esc_html_e("Modify type",'lab'); ?> :</label>
    <?php lab_html_select("wp_lab_group_type_edit", "wp_lab_group_type", "", "lab_admin_get_params_groupTypes"); ?>
    <br /></br />
    <label for="wp_lab_group_url_edit"><?php esc_html_e("Group webpage",'lab');?> :</label>
    <input type="text" name="wp_lab_group_url" id="wp_lab_group_url_edit" value="" size="80" placeholder="Url"/>
    
    <br />

    <div> <!-- Manager -->
      <h5><?php esc_html_e("Add manager to group",'lab') ?></h5>
      <div>
        <div id="lab_admin_group_managers">
        </div>
        <br/>
        <input type="text"  id="lab_group_edit_add_manager" value="" size=50 placeholder="Manager"/>
        <input type="hidden" id="lab_group_edit_add_manager_id">
        <?php
        //lab_html_select('lab_thematic','lab_thematic','lab_allRoles','lab_admin_thematic_load_all',null,array("value"=>0,"label"=>"--- Select thematic ---"),0);?>
        <select id="lab_admin_group_manager_function">
          <option value="1"><?php esc_html_e("Budget manager","lab")?></option>
          <option value="2"><?php esc_html_e("Group leader")?></option>
          <option value="3"><?php esc_html_e("Group substitute")?></option>
        </select>
        <button class="btn btn-primary" id="lab_group_edit_add_manager_button"><?php esc_html_e("Add","lab")?></button>
      </div>
    </div>
    
    
    <br /><a href="#" class="page-title-action" id="lab_admin_group_edit_button">Save</a>
  </div>
  <hr/>
  <table class="form-table" role="presentation">
  <h3><?php esc_html_e("Create a group",'lab'); ?> : </h3>
  <form action="javascript:void(0);un
	<tbody>
    <tr class="form-field form-required">
      <th scope="row"><label for="lab_createGroup_name"><?php esc_html_e("Group name",'lab'); ?>* : </label></th>
      <td><input type="text" id="lab_createGroup_name" name="lab_createGroup_name" placeholder="ex: Analyse Appliquée"/></td>
    </tr class="form-field form-required">
      <th scope="row"><label for="lab_createGroup_acronym"><?php esc_html_e("Acronym",'lab'); ?>* <span class="description">(unique)</span> : </label></th>
      <td>
        <input type="text" id="lab_createGroup_acronym" name="lab_createGroup_acronym" placeholder="Acronym"/>
        <label style="padding-left:2em;" id="lab_createGroupe_acronym_hint"></span>
      </td>
    </tr>
    <tr class="form-field">
      <th scope="row"><label for="lab_createGroup_parentGroup"><?php esc_html_e("Parent group",'lab'); ?> :</label></th>
      <td>
        <?php lab_html_select("lab_createGroup_parent", "lab_createGroup_parent", "", "lab_admin_group_select_group", "acronym", array("value"=>0,"label"=>"None")); ?>
      </td>
    </tr>
    <tr class="form-field">
      <th scope="row"><label for="lab_createGroup_type"><?php esc_html_e("Type",'lab'); ?> :</label></th>
      <td>
      <?php lab_html_select("lab_createGroup_type", "lab_createGroup_Type", "", "lab_admin_get_params_groupTypes"); ?>
      </td>
    </tr>
    <tr class="form-field">
      <th scope="row"><label for="lab_createGroup_url"><?php esc_html_e("Group webpage",'lab'); ?> :</label></th>
      <td>
      <input type="text" id="lab_createGroup_url" name="lab_createGroup_url" placeholder="Url"/>
      </td>
    </tr>
    <tr class="form-field">
      <th scope="row"><label for="lab_createGroup_chief"><?php esc_html_e("Group leader",'lab'); ?> :</label></th>
      <td>
        <input id="lab_createGroup_chief" type="text" name="lab_createGroup_chief" placeholder="Pascal HUBERT"/>
        <input type="hidden" id="lab_createGroup_chiefID"/>
      </td>
    </tr>
    <tr class="form-field">
      <th scope="row"><label for="lab_createGroup_subInput"><?php esc_html_e("Substitute",'lab'); ?> <span class="description">(<?php esc_html_e("Optional",'lab'); ?>) </span>:</label></th>
      <td colspan="2">
        <p id="lab_createGroup_subsList"></p><span style="display:none; cursor:pointer;" id="lab_createGroup_subsDelete">❌ <?php esc_html_e("Clear list",'lab'); ?></span><span style="display: none;" id="lab_createGroup_subsIDList"></span>
      </td>
    </tr>
    <tr class="form-field">
      <td colspan="2" >
        <input id="lab_createGroup_subInput" type="text" name="lab_createGroup_subInput" placeholder="<?php esc_html_e("Don't forget to click on add",'lab'); ?>"/>
        <input type="hidden" id="lab_createGroup_subID" list=""/> 
      </td>
      <td><input type="button" id="lab_createGroup_addSub" class="page-title-action" value="<?php esc_html_e("+ Add a substitute",'lab'); ?>"/></td>
    </tr>
    <tr class="form-field">
      <td><input class="page-title-action" type="submit" id="lab_createGroup_create" value="<?php esc_html_e("Create group",'lab'); ?>"/></td>
    </tr>
	</tbody></form></table>
  <br />
  <hr />
<br/>
<div>
  <h3><?php esc_html_e('Assign users to groups','lab') ?></h3>

  <label for="lab_all_users"><b><?php esc_html_e('Also display people who already have a group', 'lab') ?></b></label>
  <input type="checkbox" id="lab_all_users"/><br/>
  <label for="lab_no_users_left"><b><?php esc_html_e('Also display people who left the institute','lab') ?></b></label>
  <input type="checkbox" id="lab_no_users_left"/>
  <br/><br/>

  <div style="display:flex;">
    
    <!-- CHOIX USER -->

    <div style='float: left; margin-right:50px;'>
                          <label for='users'><?php esc_html_e('Choose one or more people to assign :','lab') ?>
                          </label><br/><br/>
    <select id='list_users' name='users[]' multiple style='height:300px;'></select></div>
    
    <!-- CHOIX GROUP -->

    <div style='float: right; margin-left:50px'>
                          <label for='groups'><?php esc_html_e('Choose the group(s) you want to assign people to :', 'lab') ?>
                          </label><br/><br/>
    <select id='list_groups' name='groups[]' multiple style='height:150px;'></select></div>

  </div>
  <button style='margin-top:10px;' id='lab_add_users_groups'><?php esc_html_e('Submit','lab') ?></button>
</div>

<?php
}