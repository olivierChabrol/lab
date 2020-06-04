/* globals global 25 03 2020 */
const { __, _x, _n, sprintf } = wp.i18n;

jQuery(function($){
  var searchRequest;
  loadExistingKeys();


  if ($("#lab_ldapListBody")!=null && $("#lab_ldapListBody").length) {
    console.log("DANS lab_ldapListBody");
    LABLoadLDAPList();
  }

  if(!$("#lab_user_left").is(':checked')) {
    $("#lab_user_left_date").prop("disabled", true);
  }
  $("#lab_user_left").change(function() {
    $("#lab_user_left_date").prop("disabled", !$(this).is(":checked"));
    if (!$(this).is(":checked")) {
       jQuery("#lab_user_left_date").val("");
    }
  });
  //$( "#lab_user_left_date" ).datepicker();
  $("#lab_hal_delete_table").click(function () { 
    deleteHalTable();
  });
  $("#lab_hal_user").autocomplete({
    minChars: 2,
    source: function(term, suggest){
      try { searchRequest.abort(); } catch(e){}
      searchRequest = $.post(LAB.ajaxurl, { action: 'search_username',search: term, }, function(res) {
        suggest(res.data);
      });
      },
      select: function( event, ui ) {
        var label = ui.item.label;
        var value = ui.item.value;
        event.preventDefault();
        $("#lab_hal_user").val(label);

        loadHalJson(value);
      }
  });
  $("#wp_lab_group_name").autocomplete({
    minChars: 2,
    source: function(term, suggest){
      try { searchRequest.abort(); } catch(e){}
      searchRequest = $.post(LAB.ajaxurl, { action: 'search_group',search: term, }, function(res) {
        suggest(res.data);
      });
      },
      select: function( event, ui ) {
        var label = ui.item.label;
        var value = ui.item.value;
        event.preventDefault();
        $("#wp_lab_group_name").val(label);

        $("#lab_searched_group_id").val(value);
        setinfoToGroupEditionFields(ui.item.id, ui.item.acronym, ui.item.label, ui.item.chief_id,
          ui.item.parent_group_id, ui.item.group_type, ui.item.url);
      }
  });

  $("#lab_group_delete_button").click(function(){
      $.post(LAB.ajaxurl,
          {
            action : 'delete_group',
            id : jQuery("#lab_searched_group_id").val()
          },

          function(data){
            if (data.success) {
              toast_success("Le groupe a bien été supprimé.");
              resetGroupEdit();
            }
          }
      )
  });

  $("#lab_admin_reset_db").click(function() {
    $("#lab_admin_setting_delete_dialog").modal();
  });

  $("#lab_presence_create_table").click(function() {
    callAjax({action : 'lab_presence_create_table'}, "TABLE presence successfuly created", null, "Failed to create table presence", null);
  });


  $("#lab_admin_setting_delete_dialog_confirm").click(function() {
    callAjax({action : 'reset_lab_db'}, "LAB DB successfuly reset", null, "Failed to reset LAB DB", null);
  });

  $("#lab_admin_role_add_keyring").click(function() {
    callAjax({action : 'keyring_add_role'}, "Keyring role add", null, "Failed to add keyring role", null);
  });
  
  $('#wp_lab_event_title').autocomplete({
    minChars: 2,
    source: function(term, suggest){
      try { searchRequest.abort(); } catch(e){}
      searchRequest = $.post(LAB.ajaxurl, { action: 'search_event',search: term, }, function(res) {
        suggest(res.data);
      });
      },
    select: function( event, ui ) {
      var label = ui.item.label;
      var value = ui.item.value;
      event.preventDefault();
      $("#wp_lab_event_title").val(ui.item.label);
      
      $("#lab_user_left_date").val("");
      $("#lab_user_left_date").prop("disabled", true);
      $("#lab_user_left").prop("checked", false);
      $("#lab_searched_event_id").val(value);
      $("#lab_event_id").html(value);
      $("#wp_lab_event_label").text(label);
      
      loadEventCategory(value);
      return false;
    }
  });
  $('#lab_user_search').autocomplete({
    minChars: 3,
    source: function(term, suggest){
      try { searchRequest.abort(); } catch(e){}
      searchRequest = $.post(LAB.ajaxurl, { action: 'search_username',search: term, }, function(res) {
        suggest(res.data);
      });
      },
    select: function( event, ui ) {
      var label = ui.item.label;
      var value = ui.item.value;
      event.preventDefault();
      $("#lab_user_search").val(label);
      $("#lab_user_search_id").val(value);
      callbUser(value, loadUserMetaData);
      loadUserHistory();
      return false;
    }
  });

  $('#lab_directory_user_name').autocomplete({
    minChars: 1,
    source: function(term, suggest){
      try { searchRequest.abort(); } catch(e){}
      searchRequest = $.post(LAB.ajaxurl, { action: 'search_username2',search: term, },
      function(res) {
        suggest(res.data);
      });
    },
    select: function( event, ui ) {
      var firstname  = ui.item.firstname; // first name
      var lastname = ui.item.lastname; // last name
      var user_id  = ui.item.user_id;
      window.location.href = "/user/" + firstname + "." + lastname;
      event.preventDefault();
      $("#lab_directory_user_name").val(firstname + " " + lastname);
    }
  });
  $(".email").each(function() {
    var replaced = $(this).text().replace(/@/g, '[TA]');
    $(this).text(replaced);
  });
  
  $(".directory_row").click(function() {
    window.location.href = "http://stage.fr/user/" + $(this).attr('userId');
  });

  $("#lab_user_button_test").click(function() {
    test();
  });
  $("#lab_user_button_update_db").click(function() {
    updateUserMetaDb();
  });
  $('#lab-button-change-category').click(function() {
    var postId = $("#lab_searched_event_id").val();
    var categoryId = $('select[name="event_categories[]"]').val();
    saveEventCaterory(postId, categoryId);
  });
  $("#lab_settings_button_addKey").click(function() {
    var userId = $("#usermetadata_user_search_id").val();
    var key = $('#usermetadata_key').val();
    var value = $('#usermetadata_value').val();
    saveMetakey(userId, key, value);
  });
  $("#usermetadata_key_all").focusout(function() {
   check_metaKey_exist();
    //if ($("#usermetadata_key_all").)
  });
  $("#lab_settings_button_addKey_all").click(function() {
    var key = $('#usermetadata_key_all').val();
    var value = $('#usermetadata_value_all').val();
    createMetakeys(key, value);
  });
  $("#lab_settings_button_delete_keys_all").click(function() {
    var key = $('#usermetadata_keys').val();
    deleteMetaKeys(key);
  });

  $("#lab_user_button_save_left").click(function() {
    saveUserLeft($("#lab_user_left_date").val(), $("#lab_user_left").is(":checked"), $("#lab_user_location").val(), $("#lab_user_function").val(), $("#lab_user_office_number").val(), $("#lab_user_office_floor").val(), $("#lab_user_employer").val(), $("#lab_user_funding").val(), $("#lab_user_firstname").val(), $("#lab_user_lastname").val());
  });

  $("#lab_user_button_delete").click(function() {
    $("#lab_user_keep_data"). prop("checked", true);
    $("#lab_user_delete_modal").show();
  });
  
  $("#lab_user_delete_close").click(function() {
    $("#lab_user_delete_modal").hide();
  });
  $("#lab_user_delete_close_icon").click(function() {
    $("#lab_user_delete_modal").hide();
  });
  $("#lab_user_delete").click(function() {
    data = {
        'action': 'lab_ldap_delete_user',
        'user_id': $("#lab_user_search_id").val(),
        'keepData': $("#lab_user_keep_data").is(":checked"),
    };
    callAjax(data, __("User delete  success",'lab'),userDeleteSuccess, __("Failed to delete user",'lab'), null);
  });

  $("#lab_settings_correct_um").click(function() {
    correctUMFields();
  });
  $("#lab_createGroup_acronym").change(function() {
    var data = {
      'action' : 'group_search_ac',
      'ac' : $("#lab_createGroup_acronym").val()
    };
    jQuery.post(LAB.ajaxurl, data, function(response) {
      if (!response.success) {
        $("#lab_createGroup_acronym").css('border-color','red');
        $("#lab_createGroupe_acronym_hint").css("color","red");
        $("#lab_createGroupe_acronym_hint")[0].innerHTML="❌ Acronyme déjà utilisé par le groupe '"+response.data[0].group_name+"' (id: "+response.data[0].id+").";
        $("#lab_createGroup_create").attr('disabled');
        $("#lab_createGroup_create").css('color','lightgray');
      }
      else {
        $("#lab_createGroup_acronym").css('border-color','green');
        $("#lab_createGroupe_acronym_hint").css("color","green");
        $("#lab_createGroupe_acronym_hint")[0].innerHTML="✓ Acronyme disponible";
        $("#lab_createGroup_create").removeAttr('disabled');
        $("#lab_createGroup_create").css('color','#0071a1');
      }
    });
  });
  
  $("#lab_createGroup_chief").autocomplete({
    minChars: 3,
    source: function(term, suggest){
      try { searchRequest.abort(); } catch(e){}
      searchRequest = $.post(LAB.ajaxurl, { action: 'search_username',search: term, }, function(res) {
        suggest(res.data);
      });
      },
    select: function( event, ui ) {
      var value = ui.item.value;
      var label = ui.item.label;
      event.preventDefault();
      $("#lab_createGroup_chief").val(label);

      $("#lab_createGroup_chiefID").val(value);
    }
  });
  $("#wp_lab_group_chief_edit").autocomplete({
    minChars: 3,
    source: function(term, suggest){
      try { searchRequest.abort(); } catch(e){}
      searchRequest = $.post(LAB.ajaxurl, { action: 'search_username',search: term, }, function(res) {
        suggest(res.data);
      });
      },
    select: function( event, ui ) {
      var value = ui.item.value;
      var label = ui.item.label;
      event.preventDefault();
      $("#wp_lab_group_chief_edit").val(label);

      $("#lab_searched_chief_id").val(value);
    }
  });

  $("#lab_param_value_search").autocomplete({
    minChars: 3,
    source: function(term, suggest){
      try { searchRequest.abort(); } catch(e){}
      searchRequest = $.post(LAB.ajaxurl, { action: 'param_search_value',search: term, }, function(res) {
        suggest(res.data);
      });
      },
    select: function( event, ui ) {
      var value = ui.item.value;
      var label = ui.item.label;
      event.preventDefault();
      $("#wp_lab_param_id").val(value);

      $("#lab_param_value_search").val(label);
      let color = ui.item.color;
      if (color != null && !color.startsWith("#"))
      {
        color = "#" + color;
      }
      $("#wp_lab_param_color_edit").val(color);
      $("#wp_lab_param_color_edit").css("background-color", color);
      loadEditParam(value, ui.item.type, value);
      return false;
    }
  });

  $('#lab_tab_param_create_table').click(function(){
    var data = {
      'action' : 'param_create_table',
    };
    jQuery.post(LAB.ajaxurl, data, function(response) {
      console.log(response);
    });
  });

  $('#lab_tab_param_delete').click(function(){
    $("#lab_admin_param_modal_param_id").val($("#wp_lab_param_type").val());
    $("#lab_admin_param_delete").modal();
    //paramDelete($("#wp_lab_param_type").val());
  });

  $("#lab_tab_param_save").click(function(){
    saveParam(null, jQuery("#wp_lab_param_type").val(), jQuery("#wp_lab_param_value").val(), jQuery("#wp_lab_param_color").val(), load_params_type_after_new_param);
  });

  $("#lab_tab_param_save_edit").click(function(){
    saveParam(jQuery("#wp_lab_param_id").val(), jQuery("#wp_lab_param_type_edit").val(), jQuery("#lab_param_value_search").val(), jQuery("#wp_lab_param_color_edit").val(), resetParamEditFields);
  });

  $("#lab_tab_param_delete_edit").click(function(){
    $("#lab_admin_param_modal_param_name").val($("#lab_param_value_search").val());
    $("#lab_admin_param_modal_param_id").val($("#wp_lab_param_id").val());
    $("#lab_admin_param_delete").modal();
    //paramDelete($("#wp_lab_param_id").val());
    resetParamEditFields();
  });

  $("#lab_admin_param_delete_confirm").click(function (){
    paramDelete($("#lab_admin_param_modal_param_id").val());
  });

  $('#lab_createGroup_createRoot').click(function(){
    var data = {
      'action' : 'group_root',
    };
    jQuery.post(ajaxurl, data, function(response) {
      (response.success ? toast_success(__("Groupe créé avec succès","lab")) : toast_error(__("Erreur lors de la création du groupe : ","lab")+response.data));
    });
  });
  $('#lab_createGroup_createTable').click(function(){
    var data = {
      'action' : 'group_table',
    };
    jQuery.post(LAB.ajaxurl, data, function(response) {
      if (response==0) {
        toast_success("Table successfully created");
        $("#lab_group_noTableWarning").css("display","none");
        return;
      }
      toast_error("Error Creating table : "+response);
    });
  });
  $('#lab_user_group_create_table').click(function(){
    var data = {
      'action' : 'user_group_table',
    };
    jQuery.post(LAB.ajaxurl, data, function(response) {
      if (response==0) {
        toast_success("Table successfully created");
        $("#lab_group_noTableWarning").css("display","none");
        return;
      }
      toast_error("Error Creating table : "+response);
    });
  });
  $('#lab_createGroup_createTable_Sub').click(function(){
    var data = {
      'action' : 'group_sub_table',
    };
    jQuery.post(LAB.ajaxurl, data, function(response) {
      if (response==0) {
        toast_success("Table successfully created");
        $("#lab_group_noSubTableWarning").css("display","none");
        return;
      }
      toast_error("Error Creating table : "+response);
    });
  });
  $("#lab_createGroup_create").click(function() {
    valeurs = new Object();
    for (i of ['name', 'acronym', 'type', 'chiefID','parent','url']) {
      if ($("#lab_createGroup_"+i).val()=="") {
        $("#lab_createGroup_"+i).css("border-color","#F00");
        toast_error("Group couldn't be created :<br> The form isn't filled properly");
        return;
      }
      $("#lab_createGroup_"+i).css("border-color","");
      valeurs[i] = $("#lab_createGroup_"+i).val();
    }
    subs = $("#lab_createGroup_subID").attr('list').split(",");
    subs.shift(); //Supprime le dernier élément (vide)
    valeurs['subsList'] = subs;
    createGroup(valeurs);
    //On réinitialise tous les champs du formulaire
    clearFields('lab_createGroup_',['name', 'acronym', 'type', 'chiefID','chief','subInput','parent','url']);
    $("#lab_createGroup_subsList")[0].innerHTML="";
    $("#lab_createGroup_subID").attr('list','');
    $("#lab_createGroup_subsDelete").hide();
  })
  $("#lab_group_edit_add_substitute").autocomplete({
    minChars: 3,
    source: function(term, suggest){
      try { searchRequest.abort(); } catch(e){}
      searchRequest = $.post(LAB.ajaxurl, { action: 'search_username',search: term, }, function(res) {
        suggest(res.data);
      });
      },
    select: function( event, ui ) {
      var value = ui.item.value;
      var label = ui.item.label;
      event.preventDefault();
      $("#lab_group_edit_add_substitute").val(label);

      //$("#lab_group_edit_add_substitute_id").val(value);
      group_edit_saveNewSubstitute(value, jQuery("#lab_searched_group_id").val());
    }
  });

  $("#lab_createGroup_subInput").autocomplete({
    minChars: 3,
    source: function(term, suggest){
      try { searchRequest.abort(); } catch(e){}
      searchRequest = $.post(LAB.ajaxurl, { action: 'search_username',search: term, }, function(res) {
        suggest(res.data);
      });
      },
    select: function( event, ui ) {
      var value = ui.item.value;
      var label = ui.item.label;
      event.preventDefault();
      $("#lab_createGroup_subInput").val(label);
      $("#lab_createGroup_subID").val(value);
    }
  });
  $("#lab_createGroup_addSub").click(function(){
    $("#lab_createGroup_subsDelete").show(); //Affiche la 'croix' qui permet de vider la liste des suppléants
    if ($("#lab_createGroup_subID").attr('list').split(",").includes($("#lab_createGroup_subID").val())) {
      toast_error("Cette personne est déjà suppléant de ce groupe");
      return;
    }
    $("#lab_createGroup_subsList").append($("#lab_createGroup_subInput").val()+", ");
    $("#lab_createGroup_subID").attr("list",function() { return $(this).attr('list')+","+$(this).val()});
    $("#lab_createGroup_subInput").val("");
    $("#lab_createGroup_subID").val("");
  });
  $("#lab_createGroup_subsDelete").click(function () { 
    //Efface la liste des suppléants
    $("#lab_createGroup_subsList")[0].innerHTML="";
    $("#lab_createGroup_subID").attr('list','');
    //Se cache soi-même
    $("#lab_createGroup_subsDelete").hide();
   });
  $("#lab_admin_group_edit_button").click(function() {
    //console.log(jQuery("#wp_lab_group_chief_edit option:selected").val()); // /!\ sans option:selected, n'apparaît pas...
    $groupId = jQuery("#lab_searched_group_id").val();
    $acronym = jQuery("#wp_lab_group_acronym_edit").val();
    $name    = jQuery("#wp_lab_group_name_edit").val();
    $chief   = jQuery("#lab_searched_chief_id").val();
    $parent  = jQuery("#wp_lab_group_parent_edit").val();
    $type    = jQuery("#wp_lab_group_type_edit").val();
    $url     = jQuery("#wp_lab_group_url_edit").val();
    editGroup($groupId, $acronym, $name, $chief, $parent, $type, $url);
  });
  $("#lab_hal_create").click(function() {
    var data = {
      'action' : 'hal_create_table'
    }
    callAjax(data, "Table HAL succesfuly created", null, "Can't create table HAL", null);
  });
  ////////////////////// Onglet KeyRing //////////////////////
  $("#lab_keyring_create_table_keys").click(function () {
    var data = {
      'action' : 'keyring_table_keys',
    };
    jQuery.post(LAB.ajaxurl, data, function(response) {
      if (response==0) {
        toast_success("Table successfully created");
        $("#lab_keyring_noKeysTableWarning").css("display","none");
        return;
      }
      toast_error("Error Creating table : "+response);
    });
  });
  $("#lab_keyring_create_table_loans").click(function () {
    var data = {
      'action' : 'keyring_table_loans',
    };
    jQuery.post(LAB.ajaxurl, data, function(response) {
      if (response==0) {
        toast_success("Table successfully created");
        $("#lab_keyring_noLoansTableWarning").css("display","none");
        return;
      }
      toast_error("Error Creating table : "+response);
    });
  });
  $("#lab_settings_button_fill_hal_name_fields").click(function() {
    data = {
      'action': 'hal_fill_hal_name'
    };
    callAjax(data, "Fields succesfully filled", null, "Can't fill fields", null);
  });
  $("#lab_settings_button_fill_user_slug_fields").click(function() {
    data = {
      'action': 'usermeta_fill_user_slug'
    };
    callAjax(data, "Fields succesfully filled", null, "Can't fill fields", null);
  });

  $("#lab_all_users").click(function() 
  {
    reset_and_load_groups_users(!$("#lab_all_users").is(':checked'),  !$("#lab_no_users_left").is(':checked'));
  });

  $("#lab_no_users_left").click(function()
  {
    reset_and_load_groups_users(!$("#lab_all_users").is(':checked'),  !$("#lab_no_users_left").is(':checked'));
  });

  $(document).ready(function()
  {
    reset_and_load_groups_users(!$("#lab_all_users").is(':checked'), !$("#lab_no_users_left").is(':checked'));
  });


  $("#lab_add_users_groups").click(function()
  {
    var tab_users  = [];
    var tab_groups = [];
    $('#list_users option:selected').each(function(){ tab_users.push($(this).val()); });
    $('#list_groups option:selected').each(function(){ tab_groups.push($(this).val()); });
    $.post(LAB.ajaxurl,
      {
        action : 'add_users_groups',
        users  : tab_users,
        groups : tab_groups
      },
      function(response) 
      {
        if(response.success)
        {
          toast_success(__("Le(s) membre(s) a bien été ajouté au(x) groupe(s)", "lab"));
          reset_and_load_groups_users($("#lab_all_users").is(':checked'), $("#lab_no_users_left").is(':checked'));
        }
        else if(response == "warning")
        {
          toast_warn(__("Sélectionnez au moins un utilisateur et un groupe !","lab"));
        }
        else
        {
          toast_error(__("Erreur, la requête n'a pas pu aboutir", "lab"));
        }
      }
    )
  });
  $("#lab_setting_social_delete_button").click(function() {
    deleteAllSocial();
  });
  $("#lab_setting_social_create_button").click(function() {
    createAllSocial();
  });
  $("#lab_invite_create_table_prefGroups").click(function() {
    callAjax({"action":"invite_createTablePrefGroup"},__("Table PrefGroup créée avec succès",'lab'),null,__("Erreur lors de la création de la tables 'invitations' et 'guests'",'lab'),null);
  });
  $("#lab_invite_create_tables").click(function() {
    callAjax({"action":"invite_createTables"},__("Tables invitations et guests créées avec succès",'lab'),null,__("Erreur lors de la création des tables 'invitations' et 'guests'",'lab'),null);
  });
  $("#lab_admin_param_colorpicker").spectrum({
      color: $("#wp_lab_param_color").val(),
      move: function(tinycolor) {
          $("#wp_lab_param_color").css('background-color',tinycolor);
          $("#wp_lab_param_color").val(tinycolor.toHexString());
        //jQuery("#lab_profile_card").css('background-color',tinycolor);
      },
      change: function(tinycolor) {
        $("#wp_lab_param_color").css('background-color',tinycolor);
        $("#wp_lab_param_color").val(tinycolor.toHexString());
      },
      hide: function() {
      }
  });
  $("#lab_admin_param_colorpicker_edit").spectrum({
      color: $("#wp_lab_param_color_edit").val(),
      move: function(tinycolor) {
          $("#wp_lab_param_color_edit").css('background-color',tinycolor);
          $("#wp_lab_param_color_edit").val(tinycolor.toHexString());
        //jQuery("#lab_profile_card").css('background-color',tinycolor);
      },
      change: function(tinycolor) {
        $("#wp_lab_param_color_edit").css('background-color',tinycolor);
        $("#wp_lab_param_color_edit").val(tinycolor.toHexString());
      },
      hide: function() {
      }
  });
  $("#lab_historic_host").autocomplete({
    minChars: 3,
    source: function(term, suggest){
      try { searchRequest.abort(); } catch(e){}
      searchRequest = $.post(LAB.ajaxurl, { action: 'search_username',search: term, }, function(res) {
        suggest(res.data);
      });
      },
    select: function( event, ui ) {
      var label = ui.item.label;
      var value = ui.item.value;
      event.preventDefault();
      $("#lab_historic_host").val(label);
      $("#lab_historic_host").attr('host_id',value);
    }
  });
  $("#lab_create_table_historic").click(function() {
    $.post(LAB.ajaxurl,{'action':'lab_historic_createTable'},function (response) {
      if (response.success) {
        toast_success(__('Table créée avec succès','lab'));
      } else {
        toast_error(__('Échec lors de la création de la table','lab'));
      }
    });
  });
  $("#lab_history_edit_delete_confirm").click(function () {
    $.post(LAB.ajaxurl,{'action':'lab_historic_delete','entry_id':$(this).attr('entry_id')},function (response) {
      if (response.success) {
        loadUserHistory();
      } else {
        toast_error(__('Échec lors de la suppression<br/>')+response.data);
      }
    });
  });
});

/*************************************************************************************************************************************************
 * FUNCTIONS
 *************************************************************************************************************************************************/

 function userDeleteSuccess() {
  resetUserTabFields();
  jQuery("#lab_user_delete_modal").hide();
  toast_success("User delete")
 }

function reset_and_load_groups_users(cond1, cond2) {
    jQuery.post(LAB.ajaxurl,
        {
        action : 'list_users_groups',
        check1 : cond1,
        check2 : cond2
        },
        function(response)
        {
          html_delete_select_options("#list_users");
          html_delete_select_options("#list_groups");

          for(var i = 0; i< response.data[0].length; ++i)
          {
              jQuery("#list_users").append(jQuery('<option/>', 
              { 
              value : response.data[0][i].user_id,
              text : response.data[0][i].last_name + " " + response.data[0][i].first_name
              }));
          }
          for(var i = 0; i< response.data[1].length; ++i)
          {
              jQuery("#list_groups").append(jQuery('<option/>', 
              {
              value : response.data[1][i].group_id, 
              text : response.data[1][i].group_name
              }));
          }
        }
    );
}


function setinfoToGroupEditionFields(groupId, acronym, groupName, chiefId, parent_group_id, group_type, url) {
  jQuery('#wp_lab_group_to_edit').val(groupId);
  jQuery('#wp_lab_group_acronym_edit').val(acronym);
  jQuery('#wp_lab_group_name_edit').val(groupName);
  jQuery('#lab_searched_chief_id').val(chiefId);
  jQuery('#wp_lab_group_chief_edit').val(callbUser(chiefId, loadUserName));
  jQuery('#wp_lab_group_parent_edit').val(parent_group_id);
  jQuery('#wp_lab_group_url_edit').val(url)

  jQuery('#wp_lab_group_type_edit').val(group_type);
  group_loadSubstitute();
}

function group_edit_deleteSubstitute(id) {
  var data = {
    'action' : 'group_delete_substitutes',
    'id' : id
  };
  jQuery.post(LAB.ajaxurl, data, function(response) 
  {
    if(response.success) 
    {
      group_loadSubstitute();
    }
  });
}

function group_edit_saveNewSubstitute(userId, groupId) {
  var data = {
    'action' : 'group_add_substitutes',
    'userId' : userId,
    'groupId': groupId
  };
  jQuery.post(LAB.ajaxurl, data, function(response) 
  {
    jQuery("#lab_group_edit_substitutes").text("");
    if(response.success) 
    {
      group_loadSubstitute();
      $("#lab_group_edit_add_substitute").val("");
    }
  });
}

function group_loadSubstitute()
{
  var groupId = jQuery('#wp_lab_group_to_edit').val();
  var data = {
    'action' : 'group_load_substitutes',
    'id' : groupId
  };
  jQuery.post(LAB.ajaxurl, data, function(response) 
  {
    jQuery("#lab_group_edit_substitutes").text("");
    if(response.success) 
    {
      for (i = 0 ; i < response.data.length ; i++) 
      {
        jQuery("#lab_group_edit_substitutes").append(response.data[i]["first_name"] + " " + response.data[i]["last_name"] + " <a href=\"#\" onclick=\"group_edit_deleteSubstitute("+response.data[i]["id"]+");return false;\">delete</a>, ");
      }
    }
  });
}

function createGroup(params) {
  //On vérifie d'abord que l'acronyme est bien unique
  var data = {
    'action' : 'group_search_ac',
    'ac' : params['acronym']
  };
  jQuery.post(LAB.ajaxurl, data, function(response) {
    if (!response.success) {
      toast_error("Group couldn't be created : the acronym is already in use");
      return false;
    }
    //On essaie ensuite de rajouter l'entrée dans la table groups 
    params['action']='group_create';
    jQuery.post(LAB.ajaxurl, params, function(respons) {
      if (respons.success) {
        //Enfin, on ajoute les entrées dans la table suppléants
        var data3 = {
          'action' : 'group_subs_add',
          'id' : respons.data[0].id,
          'subList' : params['subsList']
        };
        jQuery.post(LAB.ajaxurl, data3, function(resp) {
          if (!resp.success) {
            toast_warn("Group created, but couldn't add substitutes : <br/>"+resp.data);
            return false;
          }
          toast_success("Group successfully created");
        });
      } else {
        toast_error("Group couldn't be created :<br/>"+response.data);
      }
    });
  });
}

function load_params_type_after_new_param() {
  jQuery("#wp_lab_param_value").val("");
  jQuery("#wp_lab_param_color").val("");
  jQuery("#wp_lab_param_color").css("background-color","#FFFFFF");
  load_params_type('#wp_lab_param_type');
}

function saveParam(paramId, paramType, paramValue, paramColor, callAfterComplete) {
  var data = {
    'action' : 'save_param',
    'type' : paramType,
    'value' : paramValue,
    'color' : paramColor,
  };
  if (paramId != null) {
    data = {
      'action' : 'save_param',
      'id' : paramId,
      'type' : paramType,
      'value' : paramValue,
      'color' : paramColor,
    };
  }
  callAjax(data, "Param " + paramValue + " successfully created", callAfterComplete, null, null);
}

function loadEditParam(paramId, paramType, paramValue) {
  load_params_type('#wp_lab_param_type_edit', paramType);
}

function paramDelete(paramId) {
  var data = {
    'action' : 'param_delete',
    'id' : paramId,
  };
  jQuery.post(LAB.ajaxurl, data, function(response) {
    if(response.success) {
      load_params_type('#wp_lab_param_type', null);
      toast_success("Param deleted succesfully");
    }
    else {
      toast_error(response.data);
    }
  });
}

function resetParamEditFields() {
  jQuery("#wp_lab_param_id").val("");
  jQuery("#lab_param_value_search").val("");
  jQuery("#wp_lab_param_color_edit").val("");
  jQuery("#wp_lab_param_color_edit").css("background-color","#FFFFFF");
}

function load_params_type(select, selectedId = null) {
  var data = {
    'action' : 'load_param_type',
  };
  jQuery.post(LAB.ajaxurl, data, function(response) {
    if(response.success) {
      //alert("Sauver");
      jQuery(select+" option").each(function() {
        jQuery(this).remove();
      });

      jQuery.each(response.data, function (index, value){

        jQuery(select).append(jQuery('<option/>', { 
          value: value['id'],
          text : value['value'],
          selected : value['id']==selectedId 
      }));
      });
    }
  });
}

function test() {
  var data = {
               'action' : 'test',
  };
  jQuery.post(LAB.ajaxurl, data, function(response) {
    if(response.data) {
      alert("Sauver");
    }
  });
}
function updateUserMetaDb() {
  var data = {
               'action' : 'update_user_metadata_db',
  };
  jQuery.post(LAB.ajaxurl, data, function(response) {
    if(response.data) {
      alert("Sauver");
    }
  });
}

function saveUserMetaData(userId, date, isChecked, location, userFunction) {
  var c = isChecked?date:null;
  var data = {
               'action' : 'update_user_metadata',
               'userId' : userId,
               'dateLeft' : c,
               'location' :location,
               'function' : userFunction,
  };
  callAjax(data, "User saved", resetUserTabFields, "Failed to save user", null);

}

function saveUserLeft(date, isChecked, location, userFunction, userOfficeNumber, userOfficeFloor, employer, funding, firstname, lastname) {
  var c = isChecked?date:null;
  var data = {
               'action' : 'update_user_metadata',
               'userId' : jQuery("#lab_user_search_id").val(),
               'dateLeft' : c,
               'location' :location,
               'function' : userFunction,
               'funding' : funding,
               'employer' : employer,
               'officeNumber' : userOfficeNumber,
               'officeFloor' : userOfficeFloor,
               'firstname' : firstname,
               'lastname' : lastname,
  };
  callAjax(data, "User saved", resetUserTabFields, "Failed to save user", null);
}
function resetUserTabFields()
{
  jQuery("#lab_user_search").val("");
  jQuery("#lab_user_search_id").val("");
  jQuery("#lab_user_left").prop("checked", false);
  jQuery("#lab_user_left_date").val("");
  jQuery("#lab_user_location").val("");
  jQuery("#lab_user_function").val("");
  jQuery("#lab_user_office_number").val("");
  jQuery("#lab_user_office_floor").val("");
  jQuery("#lab_user_firstname").val("");
  jQuery("#lab_user_lastname").val("");
  jQuery("#lab_user_function").val("");
  jQuery("#lab_user_employer").val("");
  jQuery("#lab_user_funding").val("");
  jQuery("#lab_user_phone").val("");
}

function load_usermeta_dateLeft() {
  var data = {
    'action' : 'lab_admin_usermeta_names',
    'search[term]' : userId
  };
  callAjax(data, null, loadUserMetaData, null, null);
}

function callbUser(userId, callback) { // function with callback to operate with userId
  var data = {
               'action' : 'usermeta_names',
               'search[term]' : userId
  };
  jQuery.post(LAB.ajaxurl, data, function(response) {
    callback(response);
  });
}

function loadUserName(response) {
  if(response.data) {
    jQuery("#wp_lab_group_chief_edit").val(response.data["first_name"] + " " + response.data["last_name"]);
  }
}

function loadUserMetaData(response) {
  if(response.data) {
    resetUserMetaFields();
    jQuery("#lab_user_firstname").val(response.data["first_name"]["value"]);
    jQuery("#lab_user_lastname").val(response.data["last_name"]["value"]);
    jQuery("#lab_usermeta_id").val(response.data["lab_user_left"]["id"]);
    if (response.data["lab_user_left"]["value"] != null) {
      jQuery("#lab_user_left").prop("checked", true);
      jQuery("#lab_user_left_date").prop("disabled", false);
      jQuery("#lab_user_left_date").val(response.data["lab_user_left"]["value"]);
    } else {
	    jQuery("#lab_user_left").prop("checked", false);
      jQuery("#lab_user_left_date").prop("disabled", true);
      jQuery("#lab_user_left_date").val("");
    }

    if (response.data["user_function"] != null) {
      jQuery("#lab_user_function").val(response.data["user_function"]);
    }
    if (response.data["user_funding"] != null) {
      jQuery("#lab_user_funding").val(response.data["user_funding"]);
    }
    if (response.data["user_employer"] != null) {
      jQuery("#lab_user_employer").val(response.data["user_employer"]);
    }
    if (response.data["user_location"] != null) {
      jQuery("#lab_user_location").val(response.data["user_location"]);
    }
    if (response.data["user_office_floor"] != null) {
      jQuery("#lab_user_office_floor").val(response.data["user_office_floor"]);
    }
    if (response.data["user_office_number"] != null) {
      jQuery("#lab_user_office_number").val(response.data["user_office_number"]);
    }
    if (response.data["user_phone"] != null) {
      jQuery("#lab_user_phone").val(response.data["user_phone"]);
    }
    setField("#lab_user_firstname", response.data["first_name"]);
    setField("#lab_user_lastname", response.data["last_name"]);
  }
  else
  {

  }
}

function setField(fieldId, value){
  if (value != null) {
    jQuery(fieldId).val(value);
  }
}

function saveEventCaterory(postId,categoryId) {
  var data = {
              'action': 'save_event_category',
              'postId': postId,
              'categoryId': categoryId
             };
  jQuery.post(LAB.ajaxurl, data, function(response) {
    if (response.data) {
      alert("Evenement Modifie");
      jQuery("#wp_lab_event_title").val("");
      jQuery("#lab_searched_event_id").val("");
      jQuery("#lab_event_id").html("");
      jQuery("#wp_lab_event_label").text("");
    }
  });
}

function loadEventCategory(postId) {
  var data = {
	      'action': 'search_event_category',
	      'postId': postId
	     };
  jQuery.post(LAB.ajaxurl, data, function(response) {
    if (response.data) {
      //alert('Got this from the server: ' + response.data["ID"]);
      jQuery("#wp_lab_event_label").html("<b>"+response.data["name"]+"</b>");
      jQuery("#wp_lab_event_date").html(response.data["post_date"]);
    }
    else
    {
      jQuery("#wp_lab_event_label").html("<b>Pas de categorie</b>");
    }
  });
}

function editGroup(groupId, acronym, groupName, chiefId, parent, group_type, url) {
  var data = {
               'action' : 'edit_group',
               'groupId' : groupId,
               'acronym' : acronym,
               'groupName' : groupName,
               'chiefId' : chiefId,
               'parent' : parent,
               'group_type' : group_type,
               'url' : url
  };
  jQuery.post(LAB.ajaxurl, data, function(response) {
    if (response.success) {
      toast_success("Group saved");
      resetGroupEdit();
    }
    else {
      toast_error("Failed to save group");
    }
  });
}

function resetGroupEdit()
{
  jQuery("#lab_searched_group_id").val("");
  jQuery("#wp_lab_group_acronym_edit").val("");
  jQuery("#wp_lab_group_name_edit").val("");
  jQuery("#lab_searched_chief_id").val("");
  jQuery("#wp_lab_group_chief_edit").val("");
  jQuery("#wp_lab_group_parent_edit").val("");
  jQuery("#wp_lab_group_type_edit").val("0");
  jQuery("#wp_lab_group_name").val("");
  jQuery("#lab_group_edit_substitutes").text("");
  jQuery("#wp_lab_group_url_edit").val("");
}
function clearFields(prefix,list) {
  for (i of list) {
    jQuery('#'+prefix+i).val('');
  }
}

function loadExistingKeys() {
  var data = {
    'action' : 'list_metakeys'
  };
  callAjax(data, null, loadExistingKeysFields, "Can't load usermeta keys", null);
}

function loadExistingKeysFields(data) {
  // delete existing option before loading new ones
  jQuery("#usermetadata_keys options").each(function() {
    $(this).remove();
  });
  for(i in data) {
    jQuery("#usermetadata_keys").append(new Option(data[i], data[i]));
  }
}

function createMetakeys(key, value) {
  var data = {
    'action' : 'add_new_metakeys',
    'key' : key,
    'value' : value
  };
  console.log(data);
  callAjax(data, "keys create for all users", resetUserMetaFields, "Error when saving key '" + key + "'", null);
}
function deleteMetaKeys(key) {
  var data = {
    'action' : 'delete_metakey',
    'key' : key
  };
  callAjax(data, "MetaKey delete for all user", loadExistingKeysFields, "failed to delete key '" + key + "'", null);
}
function correctUMFields() {
  var data = {
    'action' : 'um_correct'
  };
  callAjax(data, "UM Field corrected", null, "failed to correct UM fields", null);
}
function saveMetakey(userId, key, value) {
  var data = {
    'action' : 'add_new_metakey',
    'userId' : userId,
    'key' : key,
    'value' : value
  };
  callAjax(data, "MetaKey save", resetUserMetaFields, "Error when saving key '" + key + "'", null);
}
function resetUserMetaFields(data) {
  jQuery("#usermetadata_user_id").val("");
  jQuery("#usermetadata_key").val("");
  jQuery("#usermetadata_value").val("");
  jQuery("#usermetadata_user_search").val("");
  jQuery("#usermetadata_key_all").val("");
  jQuery("#usermetadata_value_all").val("");
}

function check_metaKey_exist() {
  var data = {
    'action' : 'not_exist_metakey',
    'key' : jQuery("#usermetadata_key_all").val()
  };
  callAjax(data, null, enabledAddKeyAllButton, "Key " + jQuery("#usermetadata_key_all").val() + " already exist in DB", disabledAddKeyAllButton);
}

function disabledAddKeyAllButton(data) {
  jQuery("#lab_settings_button_addKey_all").prop("disabled",true);
  jQuery("#usermetadata_key_all").focus();
  jQuery("#usermetadata_key_all").select();
}

function enabledAddKeyAllButton(data) {
  jQuery("#lab_settings_button_addKey_all").prop("disabled",false);
}

function callAjax(data, successMessage, callBackSuccess = null, errorMessage, callBackError = null) {
  jQuery.post(LAB.ajaxurl, data, function(response) {
    if (response.success) {
      if (successMessage != null) {
        toast_success(successMessage);
      }
      if (callBackSuccess != null) {
        callBackSuccess(response.data);
      }
    }
    else {
      if (errorMessage != null) {
        toast_error(errorMessage);
      } 
      else {
        if (response.data) {
          toast_error(response.data);
        }
      }
      if (callBackError != null) {
        callBackError(response.data);
      }
    }
    });
}

function loadHalJson(userId) {
  var data = {
    'action' : 'hal_download',
    'userId' : userId
  };
  callAjax(data, null, enabledAddKeyAllButton, "Key " + jQuery("#usermetadata_key_all").val() + " already exist in DB", disabledAddKeyAllButton); 
}

function deleteHalTable() {
  var data = {
    'action' : 'hal_empty_table'
  };
  callAjax(data, "HAL table deleted", null, "Failed to delete HAL table in DB", null); 
}

function deleteAllSocial() {
  var data = {
    'action': 'delete_social'
  }
  callAjax(data, "All social metakeys deleted", null, "Failed to delete social metakeys in DB", null); 
}

function createAllSocial() {
  var data = {
    'action': 'create_social'
  }
  callAjax(data, "All social metakeys created", null, "Failed to create social metakeys in DB", null); 
}

function loadUserHistory() {
  data = {
    'action':'lab_admin_loadUserHistory',
    'user_id':jQuery("#lab_user_search_id").val()
  }
  jQuery(function($){
    $.post(LAB.ajaxurl,data,function (response){
      if (response.success) {
        $("#lab_history_list").html(response.data);
        $(".lab_history_edit").click(function (event){
          event.preventDefault();
          $.post(LAB.ajaxurl,{'action':'lab_historic_getEntry','entry_id':$(this).attr('entry_id')},function (response) {
            if (response.success) {
              $("#lab_historic_start").val(response.data['begin']);
              $("#lab_historic_end").val(response.data['end']);
              $("#lab_historic_function option[value="+response.data['function']+"]").prop('selected','true');
              $("#lab_historic_mobility option[value="+response.data['mobility']+"]").prop('selected','true');
              $("#lab_historic_host").attr('host_id',response.data['host_id']);
              callbUser(response.data['host_id'],loadHostNames);
            }
          });
        });
        $(".lab_history_edit_delete").click(function(){
          event.preventDefault();
          $("#lab_historic_delete_dialog").modal("show");
          $("#lab_history_edit_delete_confirm").attr('entry_id',$(this).attr('entry_id'));
        });
      }
    });
  });
}
function loadHostNames(response) {
  if (response.data) {
    jQuery("#lab_historic_host").val(response.data["first_name"] + " " + response.data["last_name"]);
  }
}

/********** LDAP ***********/

function lab_pagination_ldap(pages, currentPage) {
  data = {
    'action': 'lab_ldap_pagination',
    'pages': pages,
    'currentPage': currentPage
  };
  jQuery.post(LAB.ajaxurl,data,function(response){
    if (response.success) {
      jQuery("#pagination-digg")[0].outerHTML=response.data;
      jQuery(".page_previous:not(.gris)").click(function() {
        currentPage = parseInt(jQuery("#active").attr("page"));
        jQuery("#active").attr("id","");
        jQuery(".page_number[page=" + (currentPage - 1) + "]").attr("id","active");
        lab_update_ldap_list();
      });
      jQuery(".page_next:not(.gris)").click(function() {
        currentPage = parseInt(jQuery("#active").attr("page"));
        jQuery("#active").attr("id","");
        jQuery(".page_number[page=" + (currentPage + 1) + "]").attr("id","active");
        lab_update_ldap_list();
      });
      jQuery(".page_number").click(function() {
        jQuery("#active").attr("id","");
        jQuery(this).attr("id","active");
        lab_update_ldap_list();
      });
    }
  });
}

function lab_update_ldap_list() {
  jQuery(function($) {
    data = {
      action: 'lab_ldap_list_update',
      page: $("#active").attr("page"),
      value: $("#lab_results_number").val(),
    };
   
    console.log(data);
    $.post(LAB.ajaxurl,data, function(response) {
      console.log(response);
      $("#lab_ldapListBody").html(response.data[1]);
      pages = Math.ceil(response.data[0]/data['value']);
      currentPage = data['page']<=pages ? data['page'] : pages;
      lab_pagination_ldap(pages,currentPage);

      $("[id^=lab_ldap_detail_button_]").click(function() {
        data = {
          uid : $(this).attr("id").substring(23)
        }
        data['action'] = 'lab_ldap_user_details';
        $.post(LAB.ajaxurl, data, function(response) {
          $("#lab_ldap_name").html(response.data[0]);
          $("#lab_ldap_surname").html(response.data[1]);
          $("#lab_ldap_email").html(response.data[2]);
          $("#lab_ldap_uidNumber").html(response.data[3]);
          $("#lab_ldap_homeDirectory").html(response.data[4]);
          $("#lab_ldap_detail_title").show();
          $("#lab_ldap_details").slideDown();
          $("#lab_ldap_details_container").attr("wrapped","false");
        });
      });

      $(".fa-pen-alt").click(function() {
        $("#lab_ldap_edit_uid").val($(this).attr("uid"));
        $("#lab_ldap_edit_givenName").val($(this).attr("givenName"));
        $("#lab_ldap_edit_sn").val($(this).attr("sn"));
        $("#lab_ldap_edit_uidNumber").val($(this).attr("uidNumber"));
        $("#lab_ldap_edit_homeDirectory").val($(this).attr("homeDirectory"));
        $("#lab_ldap_edit_mail").val($(this).attr("mail"));
        
        $("#lab_admin_ldap_edit").modal("show");        
      });

      $("#saveEditLdapUser").click(function() {
        // $_POST['uid'], $_POST['givenname'], $_POST['sn'], $_POST['uidnumber'], $_POST['homeDirectory'], $_POST['mail']
        data = {
          action: "lab_ldap_edit_user",
          uid: $("#lab_ldap_edit_uid").val(),
          givenname: $("#lab_ldap_edit_givenName").val(),
          sn: $("#lab_ldap_edit_sn").val(),
          uidnumber: $("#lab_ldap_edit_uidNumber").val(),
          homedirectory: $("#lab_ldap_edit_homeDirectory").val(),
          mail: $("#lab_ldap_edit_mail").val(),
        };
        $.post(LAB.ajaxurl, data, function(response) {
          if (response.success) {
            toast_success("User modified in LDAP");
          }
          else {
            toast_error(response.data);
          }
        });
      });

    });

    
  });
};

function LABLoadLDAPList()
{
  lab_update_ldap_list();
  jQuery(function($) {
    $("#lab_results_number").change(function() {
      lab_update_ldap_list();
    });
    
    $("#lab_ldap_detail_title").click(function(){
      if($("#lab_ldap_details_container").attr("wrapped")=="true"){
        $("#lab_ldap_details").slideDown();
        $("#lab_ldap_details_container").attr("wrapped","false");
      } else {
          $("#lab_ldap_details").slideUp();
          $("#lab_ldap_details_container").attr("wrapped","true");
      }
    });
  });
};

function lab_addHistoric() {
  jQuery(function ($) {
    data = {
      'action': 'lab_historic_add',
      'user_id': $("#lab_user_search_id").val(),
      'begin': $("#lab_historic_start").val(),
      'end': $("#lab_historic_end").val(),
      'function': $("#lab_historic_function").val(),
      'mobility': $("#lab_historic_mobility").val(),
      'host_id': $("#lab_historic_host").attr('host_id')
    }
    $.post(LAB.ajaxurl,data,function (response) {
      if (response.success) {
        toast_success("Période ajoutée avec succès");
        loadUserHistory();
      } else {
        toast_error("Erreur lors de l'ajout de la période <br>"+response.data);
      }
    });
  });
}