/* globals global 25 03 2020 */
//const { __, _x, _n, sprintf } = wp.i18n;

jQuery(function($){
  var searchRequest;
  //loadExistingKeys();

  $("#lab_user_co_supervision").countrySelect({
    defaultCountry: "fr",
    preferredCountries: ['fr', 'de', 'it', 'es', 'us'],
  });

  $("#lab_user_country").countrySelect({
    defaultCountry: "fr",
    preferredCountries: ['fr', 'de', 'it', 'es', 'us'],
  });

  if ($("#lab_ldapListBody")!=null && $("#lab_ldapListBody").length) {
    console.log("DANS lab_ldapListBody");
    LABLoadLDAPList();
  }


  $("#lab_user_button_correct_missing_usermetaDataFields").click(function () { 
    correctUserMetadaField($("#lab_user_search_id").val());
  });


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
        loadGroupManagers();
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
      $("#lab_user_search").val(label);

      $("#lab_user_id").html(value);
      $("#lab_user_search_id").val(value);
      callbUser(value, loadUserMetaData);
      loadUserHistory();
      loadUserRoles();
      loaduserThematic();
      loaduserGroup();
      loadMissingMetaData(value);
      return false;
      //*/
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

  $("#lab_admin_replace_event_tags").click(function() {
    data = {
      'action':"lab_replace_event_tags",
      'tagIdToReplace':$("#event_tag_to_replace").val(),
      'tagIdReplacement':$("#event_tag_replacement").val()
    };
    callAjax(data, __("Event tags replaced",'lab'), null, __("Failed to replace event tags",'lab'), null);
  });

  $("#lab_admin_correct_slug_name").click(function() {
    
    data = {
      'action':"usermeta_fill_user_slug",
      'userId':$("#lab_user_search_id").val(),
    };
    callAjax(data, __("User slug  successfuly modify",'lab'), reloadCurrentUser, __("Failed to modify user slug name"), null);
    //alert("data" + data);
  });

  $("#lab_user_button_user_save").click(function() {
    let fields = ["lab_user_slug", "lab_user_co_supervision", "lab_user_phd_support", "lab_user_phd_become"];
    let data = {};
    for(const elm of fields)
    {
      if (elm == "lab_user_co_supervision")
      {
        data[elm] = $("#"+elm).countrySelect("getSelectedCountryData")['iso2'];
      }
      else {
        data[elm] = $("#"+elm).val();
        console.log(elm + " -> " + $("#"+elm).val());
      }
    }
    console.log('lab_user_button_user_save").click(');
    console.log(data);
    saveUser($("#lab_user_left_date").val(), 
             $("#lab_user_left").is(":checked"), 
             $("#lab_user_location").val(), 
             $("#lab_user_function").val(), 
             $("#lab_user_office_number").val(), 
             $("#lab_user_office_floor").val(), 
             $("#lab_user_employer").val(), 
             $("#lab_user_funding").val(), 
             $("#lab_user_firstname").val(), 
             $("#lab_user_lastname").val(), 
             $("#lab_user_section_cn").val(), 
             $("#lab_user_section_cnu").val(), 
             $("#lab_user_phone").val(), 
             $("#lab_user_email").val(), 
             $("#lab_user_url").val(), 
             $("#lab_user_thesis_title").val(), 
             $("#lab_user_hdr_title").val(), 
             $("#lab_user_phd_school").val(),
             $("#lab_user_sex").val(), 
             $("#lab_user_country").countrySelect("getSelectedCountryData")['iso2'],
             $("#lab_user_hdr_date").val(),
             $("#lab_user_thesis_date").val(),
             data);
  });
  //   $("#lab_user_thesis_date").val(),

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
      $("#wp_lab_param_slug_edit").val(ui.item.slug);
      $("#wp_lab_param_color_edit").val(color);
      $("#wp_lab_param_color_edit").css("background-color", color);
      loadEditParam(value, ui.item.type, value);
      return false;
    }
  });

  $("#lab_tab_param_correct_all_user_slug_name").click(function() {
    var data = {
      'action' : 'correct_all_user_slug',
    };
    callAjax(data, "All user slug corrected", null, null, null);
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
    saveParam(null, jQuery("#wp_lab_param_type").val(), jQuery("#wp_lab_param_value").val(), jQuery("#wp_lab_param_slug").val(), jQuery("#wp_lab_param_color").val(), isCheck("#wp_lab_param_shift_param"), load_params_type_after_new_param);
  });

  $("#lab_tab_param_save_edit").click(function(){
    saveParam(jQuery("#wp_lab_param_id").val(), jQuery("#wp_lab_param_type_edit").val(), jQuery("#lab_param_value_search").val(), jQuery("#wp_lab_param_slug_edit").val(), jQuery("#wp_lab_param_color_edit").val(), false, resetParamEditFields);
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
      (response.success ? toast_success(__("Group successfully created","lab")) : toast_error(__("Error when creating the group : ","lab")+response.data));
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
  $("#lab_group_edit_add_manager").autocomplete({
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
      $("#lab_group_edit_add_manager").val(label);
      $("#lab_group_edit_add_manager_id").val(value);
    }
  });

  $("#lab_admin_group_manager_function").css("backgroundColor", "limegreen")

  $("#lab_admin_group_manager_function").children().each(function () {
    if($(this).val() == 1) {
      $(this).css("backgroundColor", "limegreen");
    }
    else if($(this).val() == 2) {
      $(this).css("backgroundColor", "red");
    }
    else if($(this).val() == 3) {
      $(this).css("backgroundColor", "gold");
    }
  });

  $("#lab_admin_group_manager_function").change(function () {
    var color = $('option:selected',this).css('background-color');
    $(this).css('background-color',color); 
  })

  $("#lab_group_edit_add_manager_button").click(function () {
    let groupId = $("#lab_searched_group_id").val();
    let managerId = $("#lab_group_edit_add_manager_id").val();
    let managerFunction = $("#lab_admin_group_manager_function").val();
    data = {
      'action':"lab_group_add_manager",
      'groupId':groupId,
      'userId':managerId,
      'userRole':managerFunction,
    };
    callAjax(data, null, successfulyAddManager, null, null);
  });

  function loadGroupManagers()
  {
    let groupId = $("#lab_searched_group_id").val();
    data = {
      'action':"lab_group_load_managers",
      'groupId':groupId
    };
    callAjax(data, null, displayGroupManager, null, null);
  }

  function successfulyAddManager(data) {
    toast_success("Manager added");
    loadGroupManagers();
    $("#lab_group_edit_add_manager").val("");
  }

  function displayGroupManager(data) {
    
    $("#lab_admin_group_managers").html("");
    $.each(data, function(i, obj) {
      //use obj.id and obj.name here, for example:
      //alert(obj.name);
      var span;
      if(obj.manager_type == 1) {
        span = $('<span />').attr('class', 'badge badge-success user-role-badge').html(obj.first_name+" "+obj.last_name+" ");
      } else if(obj.manager_type == 2) {
        span = $('<span />').attr('class', 'badge badge-danger user-role-badge').html(obj.first_name+" "+obj.last_name+" ");
      } else if(obj.manager_type == 3) {
        span = $('<span />').attr('class', 'badge badge-warning user-role-badge').html(obj.first_name+" "+obj.last_name+" ");
      } else {
        span = $('<span />').attr('class', 'badge badge-secondary user-role-badge').html(obj.first_name+" "+obj.last_name+" ");
      }
      let innerSpan = $('<span />').attr('class', 'lab_admin_group_delete').attr('objId', obj.id);
      let innerI = $('<i />').attr('class', 'fas fa-trash').attr('group_id', obj.id);
      innerSpan.append(innerI);
      span.append(innerSpan);
      $("#lab_admin_group_managers").append(span);

      $(".lab_admin_group_delete").click(function (){
        //console.log($(this).attr("objId"));
        
        data = {
          'action':'lab_group_delete_manager',
          'id':$(this).attr('objId'),
        };
        callAjax(data,"Manager remove ",loadGroupManagers,'Failed to delete manager from this group',null);
        //*/
      });
    });
  }

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

    if ($("#usermetadata_keys") != null && $("#usermetadata_keys").length) {
      console.log("[lab_admin.js] settings tab");
    }
  });

  $("#lab_no_users_left").click(function()
  {
    reset_and_load_groups_users(!$("#lab_all_users").is(':checked'),  !$("#lab_no_users_left").is(':checked'));
  });

  $(document).ready(function()
  {
    if ($("#list_users") != null && $("#list_users").length > 0) {
      reset_and_load_groups_users(!$("#lab_all_users").is(':checked'), !$("#lab_no_users_left").is(':checked'));
    }
    if ($("#usermetadata_keys") != null && $("#usermetadata_keys").length > 0) {
      console.log("[lab_admin.js][ready] usermetadata_keys present");
      loadExistingKeys();
    }
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
          toast_success(__("The member(s) has been added to the group(s)", "lab"));
          reset_and_load_groups_users($("#lab_all_users").is(':checked'), $("#lab_no_users_left").is(':checked'));
        }
        else if(response == "warning")
        {
          toast_warn(__("Sélect at least one user and one group !","lab"));
        }
        else
        {
          toast_error(__("Error, the application could not be done", "lab"));
        }
      }
    )
  });
  $("#lab_settings_button_update_params_translation_file").click(function() {
    updateParamsTranslation();
  })
  $("#lab_setting_social_delete_button").click(function() {
    deleteAllSocial();
  });
  $("#lab_setting_social_create_button").click(function() {
    createAllSocial();
  });
  $("#lab_invite_create_table_prefGroups").click(function() {
    callAjax({"action":"invite_createTablePrefGroup"},__("PrefGroup successfully created",'lab'),null,__("Erreur lors de la création de la tables 'invitations' et 'guests'",'lab'),null);
  });
  $("#lab_invite_create_tables").click(function() {
    callAjax({"action":"invite_createTables"},__("Invitations and guests tables successfully created",'lab'),null,__("Error when creating 'invitations' and 'guests' tables",'lab'),null);
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
  $("#lab_historic_host").focus( function(){
    $(this).val('');
    $(this).attr('host_id','');
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
        toast_success(__('Tables successfully created','lab'));
      } else {
        toast_error(__('Failed to create the table','lab'));
      }
    });
  });
  $("#lab_history_edit_delete_confirm").click(function () {
    $.post(LAB.ajaxurl,{'action':'lab_historic_delete','entry_id':$(this).attr('entry_id')},function (response) {
      if (response.success) {
        loadUserHistory();
      } else {
        toast_error(__('Failed to delete<br/>')+response.data);
      }
    });
  });
  $("#lab_historic_edit").click(function (e){
    e.preventDefault();
    lab_addHistoric(true,$(this).attr('entry_id'));
  });
  $("#lab_admin_add_role").click(function (e) {
    e.preventDefault();
    data = {
      'action':'lab_user_addRole',
      'user_id': $("#lab_user_search_id").val(),
      'role': $("#lab_allRoles").val()
    }
    callAjax(data,"Succès",loadUserRoles,"Erreur",null);
   });
   $("#lab_admin_add_thematic").click(function (e) {
     e.preventDefault();
     data = {
       'action':'lab_user_addThematic',
       'user_id': $("#lab_user_search_id").val(),
       'thematic_id': $("#lab_thematic").val()
     }
     callAjax(data,"Succès",loaduserThematic,"Erreur",null);
    });

   $("#lab_admin_add_group").click(function (e) {
    e.preventDefault();
    data = {
      'action':'lab_user_addGroup',
      'user_id': $("#lab_user_search_id").val(),
      'group_id': $("#lab_group").val()
    }
    callAjax(data,"Succès",loaduserGroup,"Erreur",null);
   })
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
  if (parent_group_id == null)
  {
    jQuery('#wp_lab_group_parent_edit').val('0');
  }
  else {
    jQuery('#wp_lab_group_parent_edit').val(parent_group_id);
  }
  jQuery('#wp_lab_group_url_edit').val(url)

  jQuery('#wp_lab_group_type_edit').val(group_type);
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

function isCheck(elmId) {
  if (jQuery(elmId).is(":checked")) {
    return true;
  }
  return false;
  
}

function load_params_type_after_new_param() {
  resetParamFields();
  load_params_type('#wp_lab_param_type');
}

function saveParam(paramId, paramType, paramValue, paramSlug, paramColor, paramshift, callAfterComplete) {
  //console.log("paramshift : " + paramshift);
  //return;
  var data = {
    'action' : 'save_param',
    'type' : paramType,
    'value' : paramValue,
    'slug' : paramSlug,
    'color' : paramColor,
    'shift' : paramshift,
  };
  if (paramId != null) {
    data = {
      'action' : 'save_param',
      'id' : paramId,
      'type' : paramType,
      'value' : paramValue,
      'slug' : paramSlug,
      'color' : paramColor,
      'shift' : paramshift,
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
      resetParamFields();
      load_params_type('#wp_lab_param_type', null);
      toast_success("Param deleted succesfully");
    }
    else {
      toast_error(response.data);
    }
  });
}

function resetParamFields() {
  jQuery("#wp_lab_param_value").val("");
  jQuery("#wp_lab_param_slug").val("");
  jQuery("#wp_lab_param_color").val("");
  jQuery("#wp_lab_param_color").css("background-color","#FFFFFF");
}
function resetParamEditFields() {
  jQuery("#wp_lab_param_id").val("");
  jQuery("#lab_param_value_search").val("");
  jQuery("#wp_lab_param_color_edit").val("");
  jQuery("#wp_lab_param_slug_edit").val("");
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

function saveUser(date, isChecked, location, userFunction, userOfficeNumber, userOfficeFloor, employer, funding, firstname, lastname, sectionCn, sectionCnu, phone, email, url, thesisTitle, hdrTitle, phdSchool, user_sex, userCountry, user_hdr_date, user_thesis_date, datas) {
  
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
               'sectionCn' : sectionCn,
               'phone' : phone,
               'sectionCnu' : sectionCnu,
               'email' : email,
               'url' : url,
               'thesisTitle' : thesisTitle,
               'hdrTitle' : hdrTitle,
               'phdSchool' : phdSchool,
               'user_country' : userCountry,
               'user_sex' : user_sex,
               'user_hdr_date' : user_hdr_date,
               'user_thesis_date' : user_thesis_date,
  };
  console.log("[saveUser] dateLeft : " + c);
  console.log("[saveUser] location : " + location);
  console.log("[saveUser] userFunction : " + userFunction);
  console.log("[saveUser] funding : " + funding);
  console.log("[saveUser] employer : " + employer);
  console.log("[saveUser] userOfficeNumber : " + userOfficeNumber);
  console.log("[saveUser] userOfficeFloor : " + userOfficeFloor);
  console.log("[saveUser] firstname : " + firstname);
  console.log("[saveUser] lastname : " + lastname);
  console.log("[saveUser] sectionCn : " + sectionCn);
  console.log("[saveUser] sectionCnu : " + sectionCnu);
  console.log("[saveUser] phone : " + phone);
  console.log("[saveUser] email : " + email);
  console.log("[saveUser] url : " + url);
  console.log("[saveUser] thesisTitle : " + thesisTitle);
  console.log("[saveUser] hdrTitle : " + hdrTitle);
  console.log("[saveUser] phdSchool : " + phdSchool);
  console.log("[saveUser] userCountry : " + userCountry);
  console.log("[saveUser] user_sex : " + user_sex);
  console.log("[saveUser] user_hdr_date : " + user_hdr_date);
  console.log("[saveUser] user_thesis_date : " + user_thesis_date)
  console.log(datas);
  for ( let key in datas)
  {
    console.log(key+"/"+datas[key]);
    data[key] = datas[key];
  }
  console.log(data);
  callAjax(data, "User saved", resetUserTabFields, "Failed to save user", null);
}
function resetUserTabFields()
{
  jQuery("#lab_user_search").val("");
  jQuery("#lab_user_id").html("");
  jQuery("#lab_user_search_id").val("");
  jQuery("#lab_user_left").prop("checked", false);
  jQuery("#lab_user_left_date").val("");
  jQuery("#lab_user_location").val("");
  jQuery("#lab_user_function").val("");
  jQuery("#lab_user_office_number").val("");
  jQuery("#lab_user_office_floor").val("");
  jQuery("#lab_user_firstname").val("");
  jQuery("#lab_user_lastname").val("");
  jQuery("#lab_user_slug").val("");
  jQuery("#lab_user_function").val("");
  jQuery("#lab_user_employer").val("");
  jQuery("#lab_user_funding").val("");
  jQuery("#lab_user_phone").val("");
  jQuery("#lab_user_section_cn").val("");
  jQuery("#lab_user_section_cnu").val("");
  jQuery("#lab_user_email").val("");
  jQuery("#lab_user_url").val("");
  jQuery("#lab_user_thesis_title").val("");
  jQuery("#lab_user_phd_support").val("");
  jQuery("#lab_user_hdr_title").val("");
  jQuery("#lab_user_phd_school").val("");
  jQuery("#lab_user_sex").val("");
  jQuery("#lab_user_country").countrySelect("selectCountry","fr");
  jQuery("#lab_user_co_supervision").countrySelect("selectCountry","fr");
  jQuery("#lab_user_hdr_date").val("YYYY-mm-dd");
  jQuery("#lab_user_thesis_date").val("YYYY-mm-dd");
  document.forms['lab_admin_historic'].reset();
  jQuery("#lab_admin_historic").hide();
  jQuery("#lab_admin_user_thematics").html("");
  jQuery("#lab_admin_user_roles").html("");
  jQuery("#lab_admin_user_group").html("");
}

function load_usermeta_dateLeft() {
  var data = {
    'action' : 'lab_admin_usermeta_names',
    'search[term]' : userId
  };
  callAjax(data, null, loadUserMetaData, null, null);
}

function reloadCurrentUser()
{
  callbUser(jQuery("#lab_user_search_id").val(), loadUserMetaData);
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

    setField("#lab_user_firstname", response.data["first_name"]);
    setField("#lab_user_lastname", response.data["last_name"]);
    setField("#lab_user_email", response.data["user_email"]);
    setField("#lab_user_url", response.data["user_url"]);
    setField("#lab_user_slug", response.data["user_slug"]);

    setField("#lab_user_thesis_title", response.data["user_thesis_title"]);
    setField("#lab_user_phd_support", response.data["user_phd_support"]);
    setField("#lab_user_phd_become", response.data["become"]);
    setField("#lab_user_hdr_title" , response.data["user_hdr_title"]);
    setField("#lab_user_hdr_date"  , response.data["user_hdr_date"]);
    setField("#lab_user_phd_school", response.data["user_phd_school"]);
    //console.log("[lab_admin.js] [loadUserMetaData ]: "+ response.data["user_thesis_date"]);
    setField("#lab_user_thesis_date"  , response.data["user_thesis_date"]);

    $country = response.data["user_country"];
    $co_supervision_country = response.data["user_co_supervision"];
    //console.log($country);
    if ($country == null || $country == "")
    {
      $country = "fr";
    }
    if ($co_supervision_country == null || $co_supervision_country == "")
    {
      $co_supervision_country = "fr";
    }
    console.log($country);
    console.log($co_supervision_country);
    jQuery("#lab_user_country").countrySelect("selectCountry",$country);
    jQuery("#lab_user_co_supervision").countrySelect("selectCountry",$co_supervision_country);
    //setField("#lab_user_country", $country);
    console.log("[lab_admin.js][loadUserMetaData] user_sex : " + response.data["user_sex"]);
    setField("#lab_user_sex", response.data["user_sex"]);
    setField("#lab_user_function", response.data["user_function"]);
    setField("#lab_user_funding", response.data["user_funding"]);
    setField("#lab_user_employer", response.data["user_employer"]);
    setField("#lab_user_location", response.data["user_location"]);
    setField("#lab_user_office_floor", response.data["user_office_floor"]);
    setField("#lab_user_office_number", response.data["user_office_number"]);
    setField("#lab_user_phone", response.data["user_phone"]);

    if (response.data["lab_user_left"]) {
      jQuery("#lab_usermeta_id").val(response.data["lab_user_left"]["id"]);
    }
    if (response.data["lab_user_left"]["value"] != null) {
      jQuery("#lab_user_left").prop("checked", true);
      jQuery("#lab_user_left_date").prop("disabled", false);
      jQuery("#lab_user_left_date").val(response.data["lab_user_left"]["value"]);
    } else {
	    jQuery("#lab_user_left").prop("checked", false);
      jQuery("#lab_user_left_date").val("YYY-mm-dd");
      jQuery("#lab_user_left_date").prop("disabled", true);
      jQuery("#lab_user_left_date").val("");
    }
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
  jQuery("#wp_lab_group_url_edit").val("");
  jQuery("#lab_group_edit_add_manager").val("");
  jQuery("#lab_admin_group_managers").html("");
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
  callAjax(data, "MetaKey delete for all user", loadExistingKeys, "failed to delete key '" + key + "'", null);
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

function updateParamsTranslation() {
  var data = {
    'action' : 'update_paramsTranslation',
  }
  callAjax(data, "File updated successfully", null, "Failed to update the file", null);
}

function loadUserHistory() {
  data = {
    'action':'lab_admin_loadUserHistory',
    'user_id':jQuery("#lab_user_search_id").val()
  }
  jQuery(function($){
    $("#lab_admin_historic").show();
    $.post(LAB.ajaxurl,data,function (response){
      if (response.success) {
        $("#lab_history_list").html(response.data);
        $(".lab_history_edit").click(function (event){
          event.preventDefault();
          $("#lab_historic_edit").show();
          $("#lab_historic_edit").attr('entry_id',$(this).attr('entry_id'));
          $.post(LAB.ajaxurl,{'action':'lab_historic_getEntry','entry_id':$(this).attr('entry_id')},function (response) {
            if (response.success) {
              $("#lab_historic_start").val(response.data['begin']);
              $("#lab_historic_end").val(response.data['end']);
              $("#lab_historic_function option[value="+response.data['function']+"]").prop('selected','true');
              $("#lab_historic_mobility option[value="+response.data['mobility']+"]").prop('selected','true');
              $("#lab_historic_mobility_status option[value="+response.data['mobility_status']+"]").prop('selected','true');
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

function lab_addHistoric(update,entry_id=null) {
  jQuery(function ($) {
    data = {
      'user_id': $("#lab_user_search_id").val(),
      'begin': $("#lab_historic_start").val(),
      'end': $("#lab_historic_end").val(),
      'function': $("#lab_historic_function").val(),
      'mobility': $("#lab_historic_mobility").val(),
      'mobility_status': $("#lab_historic_mobility_status").val()
    }
    if ($("#lab_historic_host").attr('host_id')!=null && $("#lab_historic_host").attr('host_id').length) {
      data['host_id']=$("#lab_historic_host").attr('host_id');
    }
    if (update) {
      data['action'] = 'lab_historic_update';
      data['entry_id'] = entry_id;
    } else {
      data['action']= 'lab_historic_add';
    }
    $.post(LAB.ajaxurl,data,function (response) {
      if (response.success) {
        update ? toast_success("Période modifiée avec succès") : toast_success("Période ajoutée avec succès");
        document.forms['lab_admin_historic'].reset();
        $("#lab_historic_edit").hide();
        loadUserHistory();
      } else {
        toast_error("Erreur lors de l'ajout de la période <br>"+response.data);
      }
    });
  });
}

function loadMissingMetaData(userId)
{
  callAjax({'action':'missing_user_metadata','id':userId}, null, displayMissingMetaData, null, hideMissingMetaData);
}

function correctUserMetadaField(userId)
{
  callAjax({'action':'correct_user_metadatas','id':userId}, "Data corrected", null, "Failed to correct metadata fields", null);
}

function displayMissingMetaData(data)
{
  jQuery('missingUserMetaDataContent').empty();
  var list = jQuery('<ul/>').appendTo('#missingUserMetaDataContent');
  for (var i = 0; i < data.length; i++) {
    // New <li> elements are created here and added to the <ul> element.
    list.append('<li>'+data[i]+'</li>');
  }
}
function hideMissingMetaData(data)
{
  $("#missingUserMetaData").hide();
}

function loaduserGroup() {
  jQuery(function ($) {
    $.post(LAB.ajaxurl,{'action':'lab_admin_group_by_user','user_id':$("#lab_user_search_id").val()},function (response) {
      if (response.success) {
        $("#lab_admin_user_group").html("");
        $.each(response.data, function(i, obj) {
          //use obj.id and obj.name here, for example:
          //alert(obj.name);
          let span = $('<span />').attr('class', 'badge badge-secondary user-role-badge').html(obj.name+" ");
          let innerSpan = $('<span />').attr('class', 'lab_group_delete').attr('group_id', obj.id).attr('user_group_id', obj.ugid);
          let innerI = $('<i />').attr('class', 'fas fa-trash').attr('group_id', obj.id);
          innerSpan.append(innerI);
          span.append(innerSpan);
          $("#lab_admin_user_group").append(span);
        });
        $(".lab_group_delete").click(function (){
          data = {
            'action':'lab_user_delGroup',
            'group_id':$(this).attr('user_group_id'),
          };
          callAjax(data,"Group "+$(this).attr('role')+" delete !",loaduserGroup,'Failed to delete group',null);
        });
      }
    })
  });
}
function loaduserThematic() {
  jQuery(function ($) {
    $.post(LAB.ajaxurl,{'action':'lab_user_getThematics_by_user','user_id':$("#lab_user_search_id").val()},function (response) {
      if (response.success) {
        $("#lab_admin_user_thematics").html("");
        $.each(response.data, function(i, obj) {
          //use obj.id and obj.name here, for example:
          //alert(obj.name);
          let span = $('<span />').attr('class', 'badge badge-secondary user-role-badge').html(obj.name+" ");
          let innerSpan = $('<span />').attr('class', 'lab_thematic_delete').attr('thematic_id', obj.id);
          let thematicCssClass = 'lab_thematic_order';
          if (obj.main == "1") {
            thematicCssClass += " lab_thematic_main";
          }
          let innerSpanMain = $('<span />').attr('class', thematicCssClass).attr('thematic_id', obj.id).attr('thematic_value', obj.main);
          let innerI = $('<i />').attr('class', 'fas fa-trash').attr('thematic_id', obj.id);
          let innerIMain = $('<i />').attr('class', 'fa fa-star').attr('thematic_id', obj.id);
          innerSpan.append(innerI);
          innerSpanMain.append(innerIMain);
          span.append(innerSpan);
          span.append(innerSpanMain);
          $("#lab_admin_user_thematics").append(span);
        });
        //$("#lab_admin_user_thematics").html(response.data);
        //<span class='lab_role_delete' user_id='".$user_id."' role='".$value->id."'><i class='fas fa-trash'></i></span></span>
        $(".lab_thematic_delete").click(function (){
          data = {
            'action':'lab_user_delThematic',
            'thematic_id':$(this).attr('thematic_id'),
          };
          callAjax(data,"Thematic "+$(this).attr('role')+" delete !",loaduserThematic,'Failed to delete thematic',null);
        });
        $(".lab_thematic_order").click(function (){
          data = {
            'action':'lab_user_setMainThematic',
            'thematic_id':$(this).attr('thematic_id'),
            'thematic_value':$(this).attr('thematic_value'),
          };
          console.log(data);
          callAjax(data,"Thematic "+$(this).parent().text()+" set as main",loaduserThematic,'Failed to set main thematic',null);
        });
      }
    })
  });
}

function loadUserRoles() {
  jQuery(function ($) {
    $.post(LAB.ajaxurl,{'action':'lab_user_getRoles','user_id':$("#lab_user_search_id").val()},function (response) {
      if (response.success) {
        $("#lab_admin_user_roles").html(response.data);
        $(".lab_role_delete").click(function (){
          data = {
            'action':'lab_user_delRole',
            'user_id':$(this).attr('user_id'),
            'role': $(this).attr('role'),
          };
          callAjax(data,"Rôle "+$(this).attr('role')+" supprimé !",loadUserRoles,'Erreur lors de la suppression du rôle',null);
        });
      }
    })
  })
}
function lab_admin_ldap_settings() {
  jQuery(function ($) {
    data = {
      'action': 'lab_admin_ldap_settings',
      'enable': [$("#lab_admin_tab_ldap_enable").prop('checked').toString(), $("#lab_admin_tab_ldap_enable").attr('param_id').length > 0 ? $("#lab_admin_tab_ldap_enable").attr('param_id') : null],
      'host': [$("#lab_admin_tab_ldap_host").val(), $("#lab_admin_tab_ldap_host").attr('param_id').length > 0 ? $("#lab_admin_tab_ldap_host").attr('param_id') : null],
      'token': [$("#lab_admin_tab_ldap_token").val(), $("#lab_admin_tab_ldap_token").attr('param_id').length > 0 ? $("#lab_admin_tab_ldap_token").attr('param_id') : null],
      'base': [$("#lab_admin_tab_ldap_base").val(), $("#lab_admin_tab_ldap_base").attr('param_id').length > 0 ? $("#lab_admin_tab_ldap_base").attr('param_id') : null],
      'login': [$("#lab_admin_tab_ldap_login").val(), $("#lab_admin_tab_ldap_login").attr('param_id').length > 0 ? $("#lab_admin_tab_ldap_login").attr('param_id') : null],
      'password': [$("#lab_admin_tab_ldap_pass").val(), $("#lab_admin_tab_ldap_pass").attr('param_id').length > 0 ? $("#lab_admin_tab_ldap_pass").attr('param_id') : null],
      'tls': [$("#lab_admin_tab_ldap_tls").prop('checked').toString(), $("#lab_admin_tab_ldap_tls").attr('param_id').length > 0 ? $("#lab_admin_tab_ldap_tls").attr('param_id') : null],
    }
    callAjax(data,__('Settings updated','lab'),lab_admin_reload_ldap_settings,__('Error when updating settings'),null);
  });
}
function lab_admin_reload_ldap_settings(data) {
  document.querySelector('#lab_admin_ldap_tab').outerHTML=data;
}