/* front end 21 04 2020 */
const { __, _x, _n, sprintf } = wp.i18n;

/*** DIRECTORY ***/

jQuery(function($){
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
    window.location.href = "/user/" + $(this).attr('userId');
  });

});

/*******************************  */
function LABloadProfile() {
  console.log("loaded by"+this);
  jQuery(function($) {
    $(".entry-title").text("Profil de "+$('#lab_profile_name').text().replace("• "," "))
    $("#lab_profile_edit").click( function() {
      $(".lab_profile_edit").show();
      $("#lab_profile_edit").hide();
      $("#lab_confirm_change").show();
      $(".lab_current").hide();
    });

    $("#lab_confirm_change").click(function(){
      $(".lab_profile_edit").hide();
      $("#lab_profile_edit").show();
      $("#lab_confirm_change").hide();
      $(".lab_current").show();
      lab_profile_edit($(this).attr('user_id'), $("#lab_profile_edit_phone").val(), $("#lab_profile_edit_url").val(), $("#lab_profile_edit_bio").val())
    })
  });
}
if ( jQuery( "#lab_profile_card" ).length ) {
  LABloadProfile();
}
function lab_profile_edit($user_id,$phone,$url,$bio) {
  data = {
    'action' : 'lab_profile_edit',
    'phone' : $phone,
    'user_id' : $user_id,
    'url' : $url,
    'description' : $bio
  }
  jQuery.post(LAB.ajaxurl, data, function(response) {
    jQuery("#lab_profile_card")[0].outerHTML=response.data;
    LABloadProfile();
  });
}