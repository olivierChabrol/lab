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
        //alert(ui.item.option);
        var label = ui.item.label;
        var value = ui.item.value;
      }

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
      //alert(ui.item.option);
      var label = ui.item.label;
      var value = ui.item.value;
      //alert(value);
      //$("#lab_searched_event_id").val("toto");
      //alert($("#lab_searched_event_id").val());
      $("#lab_user_left_date").val("");
      $("#lab_user_left_date").prop("disabled", true);
      $("#lab_user_left").prop("checked", false);
      $("#wp_lab_event_title").val(ui.item.label);
      $("#lab_searched_event_id").val(value);
      $("#lab_event_id").html(value);
      $("#wp_lab_event_label").text(label);
      //alert(label);
      loadEventCategory(value);
      return false;
    }
  });
  $('#lab_user_email').autocomplete({
    minChars: 3,
    source: function(term, suggest){
      try { searchRequest.abort(); } catch(e){}
      searchRequest = $.post("/wp-admin/admin-ajax.php", { action: 'search_user_email',search: term, }, function(res) {
        suggest(res.data);
      });
      },
    select: function( event, ui ) {
      //alert(ui.item.option);
      var label = ui.item.label;
      var value = ui.item.value;
      //$('#lab_user_email"').val(label);
      $("#lab_searched_user_id").val(value);
      loadUserMetadata(value);
      return false;
    }
  });
  $("#lab_user_button_test").click(function() {
    test();
  });
  $("#lab_user_button_update_db").click(function() {
    updateUserMetaDb();
  });
  $('#lab-button-change-category').click(function() {
    //alert( "Handler for .click() called." );
    var postId = $("#lab_searched_event_id").val();
    var categoryId = $('select[name="event_categories[]"]').val();
    //alert(categoryId);
    saveEventCaterory(postId, categoryId);
  });
  $("#lab_user_button_save_left").click(function() {
    saveUserLeft($("#lab_searched_user_id").val(), $("#lab_user_left_date").val(), $("#lab_user_left").is(":checked"));
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
        $("#lab_createGroupe_acronym_hint")[0].innerHTML="❌ Acronyme déjà utilisé par le groupe '"+response.data[0]+"'.";
        $("#lab_createGroup_create").attr('disabled',true);
      }
      else {
        $("#lab_createGroup_acronym").css('border-color','green');
        $("#lab_createGroupe_acronym_hint").css("color","green");
        $("#lab_createGroupe_acronym_hint")[0].innerHTML="✓ Acronyme disponible";
        $("#lab_createGroup_create").attr('disabled',false);
      }
    });
  })
  $("#lab_createGroup_chief").autocomplete({
    minChars: 3,
    source: function(term, suggest){
      try { searchRequest.abort(); } catch(e){}
      searchRequest = $.post(ajaxurl, { action: 'search_user_email',search: term, }, function(res) {
        suggest(res.data);
      });
      },
    select: function( event, ui ) {
      var value = ui.item.value;
      var label = ui.item.label;
      $("#lab_createGroup_chiefID").val(value);
      $("#lab_createGroup_chief").val(label);
      return false;
    }
  });
  $('#lab_createGroup_createRoot').click(function(){
    var data = {
      'action' : 'group_root',
    };
    jQuery.post(ajaxurl, data, function(response) {
      (response == 0 ? toast_success("Group successfully created") : toast_error("Error Creating Group : "+response));
    });
  });
  $('#lab_createGroup_createTable').click(function(){
    var data = {
      'action' : 'group_table',
    };
    jQuery.post(ajaxurl, data, function(response) {
      if (response) {
        toast_success("Table successfully created");
        $("#lab_group_noTableWarning").css("display","none");
        return;
      }
      toast_error("Error Creating table : "+response);
    });
  })
  $("#lab_createGroup_create").click(function() {
    let params = [$("#lab_createGroup_name"),$("#lab_createGroup_acronym"),$("#lab_createGroup_type"),$("#lab_createGroup_chiefID"),$("#lab_createGroup_parent"),$("#lab_createGroup_chief")];
    let values = Array();
    params.forEach(element => {
      if (element.val().length==0) {
        element.css('border-color','red');
      }
      else {
        element.css('border-color','');
        values.push(element.val());
      }
    });
    values.pop();
    if (values.length == 5) {
      createGroup(values);
      $("#lab_createGroup_name").val("");
      $("#lab_createGroup_acronym").val("");
    }
    else {
      toast_error("Group couldn't be created :<br> The form isn't filled properly");
    }
  })
});
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
  var data = {
    'action' : 'group_search_ac',
    'ac' : params[1]
  };
  jQuery.post(ajaxurl, data, function(response) {
    if (!response.success) {
      toast_error("Group couldn't be created : the acronym is already in use");
      return false;
    }
    var data = {
      'action' : 'group_create',
      'name' : params[0],
      'acronym' : params[1],
      'type' : params[2],
      'chief_id' : params[3],
      'parent' : params[4]
    };
    jQuery.post(ajaxurl, data, function(response) {
      (response == 0 ? toast_success("Group successfully created") : toast_error("Error Creating Group : "+response));
    });
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
  alert("userId : " + userId + " date " + date + " is checked : " + isChecked + " c : " + c );
  var data = {
               'action' : 'update_user_metadata',
               'userMetaId' : umd,
               'dateLeft' : c,
  };
  jQuery.post(ajaxurl, data, function(response) {
    if(response.data) {
      alert("Sauver");
    }
  });
}

function loadUserMetadata(userId) {
  var data = {
               'action' : 'search_user_metadata',
               'userId' : userId
  };
  jQuery.post(ajaxurl, data, function(response) {
    if(response.data) {
      //alert(response.data["first_name"]["value"]);
      jQuery("#lab_user_firstname").val(response.data["first_name"]["value"]);
      jQuery("#lab_user_lastname").val(response.data["last_name"]["value"]);
      jQuery("#lab_usermeta_id").val(response.data["lab_user_left"]["id"]);
      if (response.data["lab_user_left"]["value"] != null) {
         jQuery("#lab_user_left").prop("checked", true);
         jQuery("#lab_user_left_date").prop("disabled", false);
         jQuery("#lab_user_left_date").val(response.data["lab_user_left"]["value"]);
      }
      else
      {
	 jQuery("#lab_user_left").prop("checked", false);
         jQuery("#lab_user_left_date").prop("disabled", true);
         jQuery("#lab_user_left_date").val("");
      }
    }
    else
    {

    }
  });
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
