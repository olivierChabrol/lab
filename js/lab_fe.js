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
      var userslug = ui.item.userslug;
      console.log(userslug);
      window.location.href = "/user/" + userslug;
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
    $("#lab_profile_card").css('background-color',$("#lab_profile_card").attr('bg-color'));
    $("#lab_profile_colorpicker").spectrum({
      color: "#f2f2f2",
      move: function(tinycolor) {
        jQuery("#lab_profile_card").css('background-color',tinycolor);
      },
      change: function(tinycolor) {
        jQuery("#lab_profile_card").attr('bg-color',tinycolor.toHexString());
      }
    });
    $(".entry-title").text("Profil de "+$('#lab_profile_name').text().replace("• "," "))
    $("#lab_profile_edit").click( function() {
      $(".lab_profile_edit").show();
      $("#lab_profile_edit").hide();
      $("#lab_confirm_change").show();
      $(".lab_current").hide();
    });

    $("#lab_confirm_change").click(function(){
      if ($("#lab_profile_edit_bio").val().length > 200)
      {
        $("#lab_alert").html("Votre biographie est trop longue (max 200 caractères).")
      }
      else{
        $(".lab_profile_edit").hide();
        $("#lab_profile_edit").show();
        $("#lab_confirm_change").hide();
        $(".lab_current").show();
        regex=/\"/g;
        lab_profile_edit($(this).attr('user_id'), $("#lab_profile_edit_phone").val(), $("#lab_profile_edit_url").val(), $("#lab_profile_edit_bio").val().replace(regex,"”").replace(/\'/g,"’"),jQuery("#lab_profile_card").attr('bg-color'));
      }
    })
  });
}
if ( jQuery( "#lab_profile_card" ).length ) {
  LABloadProfile();
}
function lab_profile_edit($user_id,$phone,$url,$bio,$color) {
  data = {
    'action' : 'lab_profile_edit',
    'phone' : $phone,
    'user_id' : $user_id,
    'url' : $url,
    'description' : $bio,
    'bg_color': $color
  }
  jQuery.post(LAB.ajaxurl, data, function(response) {
    jQuery("#lab_profile_card")[0].outerHTML=response.data;
    LABloadProfile();
  });
}