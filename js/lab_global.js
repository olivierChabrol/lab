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
  $('#wp_lab_event_title').autocomplete({
    minChars: 2,
    source: function(term, suggest){
      try { searchRequest.abort(); } catch(e){}
      searchRequest = $.post("https://test.i2m.univ-amu.fr/wp-admin/admin-ajax.php", { action: 'search_event',search: term, }, function(res) {
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
      searchRequest = $.post("https://test.i2m.univ-amu.fr/wp-admin/admin-ajax.php", { action: 'search_user_email',search: term, }, function(res) {
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
});

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
