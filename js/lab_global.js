/* globals global 25 03 2020 */
jQuery(function($){
  var searchRequest;
  if(!$("#lab_user_left").is(':checked')) {
    $("#lab_user_left_date").prop("disabled", true);
  }
  $("#lab_user_left").change(function() {
    $("#lab_user_left_date").prop("disabled", !$(this).is(":checked"));
    if (!$(this).is(":checked")) {
       jQuery("#lab_user_left_date").val("");
    }
  });
  $( "#lab_user_left_date" ).datepicker();

  $("#wp_lab_group_name").autocomplete({
    minChars: 2,
    source: function(term, suggest){
      try { searchRequest.abort(); } catch(e){}
      searchRequest = $.post("/wp-admin/admin-ajax.php", { action: 'search_group',search: term, }, function(res) {
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
          ui.item.parent_group_id, ui.item.group_type);
      }
  });

  $("#lab_group_delete_button").click(function(){
      $.post("/wp-admin/admin-ajax.php",
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
  
  $('#wp_lab_event_title').autocomplete({
    minChars: 2,
    source: function(term, suggest){
      try { searchRequest.abort(); } catch(e){}
      searchRequest = $.post("/wp-admin/admin-ajax.php", { action: 'search_event',search: term, }, function(res) {
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
      searchRequest = $.post("/wp-admin/admin-ajax.php", { action: 'search_username',search: term, }, function(res) {
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
      return false;
    }
  });

  $('#dud_user_srch_val').autocomplete({
    minChars: 1,
    source: function(term, suggest){
      try { searchRequest.abort(); } catch(e){}
      searchRequest = $.post("/wp-admin/admin-ajax.php", { action: 'search_username',search: term, }, function(res) {
        suggest(res.data);
      });
    },
    select: function( event, ui ) {
      var label = ui.item.label;
      var value = ui.item.value;
      event.preventDefault();
      $("#dud_user_srch_val").val(label);
      $("#lab_searched_directory").val(value);
      callbUser(value, loadUserMetaData);
      return false;
    }
  });

  $("#bouton_beau").click(function(){
    document.location.href = "https://www.youtube.com";
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
  $("#lab_user_button_save_left").click(function() {
    saveUserLeft($("#lab_user_search_id").val(), $("#lab_user_left_date").val(), $("#lab_user_left").is(":checked"));
  });
  $("#lab_createGroup_acronym").change(function() {
    var data = {
      'action' : 'group_search_ac',
      'ac' : $("#lab_createGroup_acronym").val()
    };
    jQuery.post(ajaxurl, data, function(response) {
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
  })
  $("#lab_createGroup_chief").autocomplete({
    minChars: 3,
    source: function(term, suggest){
      try { searchRequest.abort(); } catch(e){}
      searchRequest = $.post(ajaxurl, { action: 'search_username',search: term, }, function(res) {
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
      searchRequest = $.post(ajaxurl, { action: 'search_username',search: term, }, function(res) {
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
      searchRequest = $.post(ajaxurl, { action: 'param_search_value',search: term, }, function(res) {
        suggest(res.data);
      });
      },
    select: function( event, ui ) {
      var value = ui.item.value;
      var label = ui.item.label;
      event.preventDefault();
      $("#wp_lab_param_id").val(value);

      $("#lab_param_value_search").val(label);
      loadEditParam(value, ui.item.type, value)
      return false;
    }
  });

  $('#lab_tab_param_create_table').click(function(){
    var data = {
      'action' : 'param_create_table',
    };
    jQuery.post(ajaxurl, data, function(response) {
      console.log(response);
    });
  });

  $('#lab_tab_param_delete').click(function(){
    paramDelete($("#wp_lab_param_type").val());
  });

  $("#lab_tab_param_save").click(function(){
    saveParam(null, jQuery("#wp_lab_param_type").val(), jQuery("#wp_lab_param_value").val(), load_params_type_after_new_param);
  });

  $("#lab_tab_param_save_edit").click(function(){
    saveParam(jQuery("#wp_lab_param_id").val(), jQuery("#wp_lab_param_type_edit").val(), jQuery("#lab_param_value_search").val(), resetParamEditFields);
  });

  $("#lab_tab_param_delete_edit").click(function(){
    paramDelete($("#wp_lab_param_id").val());
    resetParamEditFields();
  });

  $('#lab_createGroup_createRoot').click(function(){
    var data = {
      'action' : 'group_root',
    };
    jQuery.post(ajaxurl, data, function(response) {
      (response.success ? toast_success("Group successfully created") : toast_error("Error Creating Group : "+response.data));
    });
  });
  $('#lab_createGroup_createTable').click(function(){
    var data = {
      'action' : 'group_table',
    };
    jQuery.post(ajaxurl, data, function(response) {
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
    jQuery.post(ajaxurl, data, function(response) {
      if (response==0) {
        toast_success("Table successfully created");
        $("#lab_group_noSubTableWarning").css("display","none");
        return;
      }
      toast_error("Error Creating table : "+response);
    });
  });
  $("#lab_createGroup_create").click(function() {
    let params = [$("#lab_createGroup_name"),$("#lab_createGroup_acronym"),$("#lab_createGroup_type"),$("#lab_createGroup_chiefID"),$("#lab_createGroup_parent"),$("#lab_createGroup_chief")];
    let values = Array();
    //let subs = $("#lab_createGroup_subsIDList")[0].innerHTML.split(",");
    
    //$('input[id^='lab_createGroup_'] ')
    params.forEach(element => {
      if (element.attr("id") != 'lab_createGroup_chiefID' && element.attr("id") != 'lab_createGroup_subID') {
        if (element.val().length==0) {
          element.css('border-color','red');
        }
        else {
          element.css('border-color','');
          values.push(element.val());
        }
      }
      else if (element.attr("id") != 'lab_createGroup_chiefID') {
        values.push(element.val());
      }
    });
    let subs = $("#lab_createGroup_subID").attr("list").split(",");
    console.log($("#lab_createGroup_subID").attr("list"));
    console.log(subs);
    subs.pop();
    console.log(subs);
    values.push(subs);
    console.log("values.length : " + values.length);
    if (values.length == 6) {
      createGroup(values);
      //On réinitialise tous les champs du formulaire
      $("#lab_createGroup_name").val("");
      $("#lab_createGroup_acronym").val("");
      $("#lab_createGroup_chiefID").val("");
      $("#lab_createGroup_chief").val("");
      $("#lab_createGroup_subInput").val("");
      $("#lab_createGroup_subID").val("");
      $("#lab_createGroup_subsList")[0].innerHTML="";
      $("#lab_createGroup_subsIDList")[0].innerHTML="";
    }
    else {
      toast_error("Group couldn't be created :<br> The form isn't filled properly");
    }
  })
  $("#lab_group_edit_add_substitute").autocomplete({
    minChars: 3,
    source: function(term, suggest){
      try { searchRequest.abort(); } catch(e){}
      searchRequest = $.post(ajaxurl, { action: 'search_username',search: term, }, function(res) {
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
      searchRequest = $.post(ajaxurl, { action: 'search_username',search: term, }, function(res) {
        suggest(res.data);
      });
      },
    select: function( event, ui ) {
      var value = ui.item.value;
      var label = ui.item.label;
      event.preventDefault();
      $("#lab_createGroup_subInput").val(label);

      $("#lab_createGroup_subID").val(value);
      //$("#lab_createGroup_subID").attr('list', value+","+$("#lab_createGroup_subID").attr('list'));
    }
  });
  $("#lab_createGroup_addSub").click(function(){
    $("#lab_createGroup_subsDelete").show();
    if ($("#lab_createGroup_subID").val()!= "" && $("#lab_createGroup_subID").attr("list").split(",").includes($("#lab_createGroup_subID").val())) {
      toast_error("Cette personne est déjà suppléant de ce groupe");
      return;
    }
    $("#lab_createGroup_subsList").append($("#lab_createGroup_subInput").val()+", ");
    $("#lab_createGroup_subID").attr('list', $("#lab_createGroup_subID").val()+","+$("#lab_createGroup_subID").attr('list'));
    $("#lab_createGroup_subInput").val("");
    $("#lab_createGroup_subID").val("");
  });
  $("#lab_createGroup_subsDelete").click(function () { 
    //Efface la liste des suppléants
    $("#lab_createGroup_subsList")[0].innerHTML="";
    $("#lab_createGroup_subsIDList")[0].innerHTML="";
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
    editGroup($groupId, $acronym, $name, $chief, $parent, $type);
  });

  ////////////////////// Onglet KeyRing //////////////////////
  $("#lab_keyring_create_table_keys").click(function () {
    var data = {
      'action' : 'keyring_table_keys',
    };
    jQuery.post(ajaxurl, data, function(response) {
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
    jQuery.post(ajaxurl, data, function(response) {
      if (response==0) {
        toast_success("Table successfully created");
        $("#lab_keyring_noLoansTableWarning").css("display","none");
        return;
      }
      toast_error("Error Creating table : "+response);
    });
  });
});


function setinfoToGroupEditionFields(groupId, acronym, groupName, chiefId, parent_group_id, group_type) {
  jQuery('#wp_lab_group_to_edit').val(groupId);
  jQuery('#wp_lab_group_acronym_edit').val(acronym);
  jQuery('#wp_lab_group_name_edit').val(groupName);
  jQuery('#lab_searched_chief_id').val(chiefId);
  jQuery('#wp_lab_group_chief_edit').val(callbUser(chiefId, loadUserName));
  jQuery('#wp_lab_group_parent_edit').val(parent_group_id);

  jQuery('#wp_lab_group_type_edit').val(group_type);
  group_loadSubstitute();
}

function group_edit_deleteSubstitute(id) {
  var data = {
    'action' : 'group_delete_substitutes',
    'id' : id
  };
  jQuery.post(ajaxurl, data, function(response) 
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
  jQuery.post(ajaxurl, data, function(response) 
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
  jQuery.post(ajaxurl, data, function(response) 
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

// Notifications "toast" affichant une erreur ou un succès lors de la requête de création de groupe.
function toast_error(message) {
  jQuery(function($){
    $.toast({
      text: message,
      heading: 'Error',
      icon: 'error',
      showHideTransition: 'slide', 
      hideAfter: 7000, 
      position: 'bottom-center', // bottom-left or bottom-right or bottom-center or top-left or top-right or top-center or mid-center or an object representing the left, right, top, bottom values
    });
  });
}
function toast_success(message) {
  jQuery(function($){
    $.toast({
      text: message, // Text that is to be shown in the toast
      heading: 'Success', // Optional heading to be shown on the toast
      icon: 'success', // Type of toast icon
      showHideTransition: 'slide', // fade, slide or plain
      hideAfter: 3000, // false to make it sticky or number representing the miliseconds as time after which toast needs to be hidden
      position: 'bottom-center', // bottom-left or bottom-right or bottom-center or top-left or top-right or top-center or mid-center or an object representing the left, right, top, bottom values
      loaderBg: '#9EC600',  // Background color of the toast loader
    });
  });
}
function toast_warn(message) {
  jQuery(function($){
    $.toast({
      text: message, // Text that is to be shown in the toast
      heading: 'Warning', // Optional heading to be shown on the toast
      icon: 'warning', // Type of toast icon
      showHideTransition: 'slide', // fade, slide or plain
      hideAfter: 5000, // false to make it sticky or number representing the miliseconds as time after which toast needs to be hidden
      position: 'bottom-center', // bottom-left or bottom-right or bottom-center or top-left or top-right or top-center or mid-center or an object representing the left, right, top, bottom values
      loaderBg: '#D1A600',  // Background color of the toast loader
    });
  });
}
function createGroup(params) {
  console.log(params);
  //On vérifie d'abord que l'acronyme est bien unique
  var data = {
    'action' : 'group_search_ac',
    'ac' : params[1]
  };
  jQuery.post(ajaxurl, data, function(response) {
    if (!response.success) {
      toast_error("Group couldn't be created : the acronym is already in use");
      return false;
    }
    //On essaie ensuite de rajouter l'entrée dans la table groups 
    var data2 = {
      'action' : 'group_create',
      'name' : params[0],
      'acronym' : params[1],
      'type' : params[2],
      'chief_id' : params[3],
      'parent' : params[4],
    };
    jQuery.post(ajaxurl, data2, function(response) {
      if (response.success) {
        //Enfin, on ajoute les entrées dans la table suppléants
        var data3 = {
          'action' : 'group_subs_add',
          'id' : response.data[0].id,
          'subList' : params[5]
        };
        jQuery.post(ajaxurl, data3, function(resp) {
          if (!resp.success) {
            console.log("failSubs");
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
  load_params_type('#wp_lab_param_type');
}

function saveParam(paramId, paramType, paramValue, callAfterComplete) {
  var data = {
    'action' : 'save_param',
    'type' : paramType,
    'value' : paramValue,
  };
  if (paramId != null) {
    data = {
      'action' : 'save_param',
      'id' : paramId,
      'type' : paramType,
      'value' : paramValue,
    };
  }
  jQuery.post(ajaxurl, data, function(response) {
    if(response.success) {
      callAfterComplete();
    }
  });
}

function loadEditParam(paramId, paramType, paramValue) {
  load_params_type('#wp_lab_param_type_edit', paramType);
}

function paramDelete(paramId) {
  var data = {
    'action' : 'param_delete',
    'id' : paramId,
  };
  jQuery.post(ajaxurl, data, function(response) {
    if(response.success) {
      load_params_type('#wp_lab_param_type', null);
    }
  });
}

function resetParamEditFields() {
  jQuery("#lab_param_value_search").val("");
  jQuery("#wp_lab_param_id").val("");
  jQuery("#lab_param_value_search").val("");
  jQuery("#lab_param_value_search").val("");
}

function load_params_type(select, selectedId = null) {
  var data = {
    'action' : 'load_param_type',
  };
  jQuery.post(ajaxurl, data, function(response) {
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
  jQuery.post(ajaxurl, data, function(response) {
    if(response.data) {
      alert("Sauver");
    }
  });
}
function updateUserMetaDb() {
  var data = {
               'action' : 'update_user_metadata_db',
  };
  jQuery.post(ajaxurl, data, function(response) {
    if(response.data) {
      alert("Sauver");
    }
  });
}

function saveUserLeft(userId, date, isChecked) {
  var c = isChecked?date:null;
  var umd = jQuery("#lab_usermeta_id").val();
  //alert("userId : " + userId + " date " + date + " is checked : " + isChecked + " c : " + c );
  var data = {
               'action' : 'update_user_metadata',
               'userMetaId' : umd,
               'dateLeft' : c,
  };
  jQuery.post(ajaxurl, data, function(response) {
    if(response.data) {
      toast_success("User saved");
    }
  });
}

function callbUser(userId, callback) { // function with callback to operate with userId
  var data = {
               'action' : 'usermeta_names',
               'search[term]' : userId
  };
  jQuery.post(ajaxurl, data, function(response) {
    callback(response);
  });
}

function loadUserName(response) {
  if(response.data) {
    jQuery("#wp_lab_group_chief_edit").val(response.data["first_name"] + " " + response.data["last_name"]);
  }
  else
  {

  }
}

function loadUserMetaData(response) {
  if(response.data) {
    //alert(response.data["first_name"]["value"]);
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
  }
  else
  {

  }
}

function saveEventCaterory(postId,categoryId) {
  var data = {
              'action': 'save_event_category',
              'postId': postId,
              'categoryId': categoryId
             };
  jQuery.post(ajaxurl, data, function(response) {
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
  jQuery.post(ajaxurl, data, function(response) {
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

function editGroup(groupId, acronym, groupName, chiefId, parent, group_type) {
  var data = {
               'action' : 'edit_group',
               'groupId' : groupId,
               'acronym' : acronym,
               'groupName' : groupName,
               'chiefId' : chiefId,
               'parent' : parent,
               'group_type' : group_type
  };
  jQuery.post(ajaxurl, data, function(response) {
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
}
function clearFields(prefix,list) {
  for (i of list) {
    jQuery('#'+prefix+i).val('');
  }
}