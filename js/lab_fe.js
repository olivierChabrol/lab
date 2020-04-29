/* front end 21 04 2020 */
const { __, _x, _n, sprintf } = wp.i18n;

/*** DIRECTORY ***/

jQuery(function($){

  $("#lab-directory-group-id").on('change', function() {
    $("#groupSearch").val($(this).val());
    letter = $("#letterSearch").val();
    group = $("#groupSearch").val();
    href = "/linstitut/annuaire/";
    if (letter != "") {
      href += "?letter="+letter;
    }
    if (group != "") {
      if (letter != "") {
        href += "&";
      } else {
        href += "?";
      }
      href += "group="+group;
    }
    window.location.href = href;
  });

  $("#lab-table-directory tr").click(function() {
    window.location.href = "/user/" + $(this).attr('userId');
  });

  $(".email").each(function() {
    var replaced = $(this).text().replace(/@/g, '[TA]');
    $(this).text(replaced);
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
      var userslug = ui.item.userslug;
      console.log(userslug);
      window.location.href = "/user/" + userslug;
      event.preventDefault();
      $("#lab_directory_user_name").val(firstname + " " + lastname);
    }
  });
});

/*******************************  */
function LABloadProfile() {
  socialURLS = {
    'facebook': 'https://www.facebook.com/@',
    'twitter': 'https://twitter.com/@',
    'pinterest': 'https://www.pinterest.com/@',
    'linkedin': 'https://www.linkedin.com/in/@',
    'instagram': 'https://instagram.com/@',
    'youtube': 'https://www.youtube.com/user/@',
    'tumblr': 'https://@.tumblr.com/'
  };
  HalID_URL = "https://api.archives-ouvertes.fr/search/?authIdHal_s:(@)&fl=docid,citationFull_s,producedDate_tdate,uri_s,title_s,journalTitle_s&sort=producedDate_tdate+desc&wt=json&json.nl=arrarr";
	HalName_URL = "https://api.archives-ouvertes.fr/search/?q=authLastNameFirstName_s:%22@%22&fl=docid,citationFull_s,producedDate_tdate,uri_s,title_s,journalTitle_s&sort=producedDate_tdate+desc&wt=json&json.nl=arrarr";
  jQuery(function($) {
    //Attribue la couleur de l'utilisateur à l'arrière plan
    $("#lab_profile_card").css('background-color',$("#lab_profile_card").attr('bg-color'));
    $("#lab_profile_colorpicker").spectrum({
      color: $("#lab_profile_card").attr('bg-color'),
      move: function(tinycolor) {
        jQuery("#lab_profile_card").css('background-color',tinycolor);
      },
      change: function(tinycolor) {
        jQuery("#lab_profile_card").attr('bg-color',tinycolor.toHexString());
      },
      hide: function() {
        jQuery("#lab_profile_card").css('background-color',jQuery("#lab_profile_card").attr('bg-color'));
      }
    });
    //Remplace le titre de la page par "Profil de "+Nom Prénom
    $(".entry-title").text("Profil de "+$('#lab_profile_name_span').text().replace("• "," "))
    //Fonction d'édition du profil
    $("#lab_profile_edit").click( function() {
      //Cache le bouton d'édition et les champs actuels
      $(this).hide();
      $(".lab_current").hide();
      //Affiche les champs à remplir
      $(".lab_profile_edit").show();
      $("#lab_confirm_change").show();
      //Affiche toutes les icones de réseau sociaux
      $(".lab_profile_social").show();
      $(".lab_profile_social").click(function (e) {
        e.preventDefault()
        //Le clic sur une icône permet de choisir le réseau à modifier
        $("#lab_profile_edit_social").val($(this).attr('href'));
        $("#lab_profile_edit_social").attr('social',$(this).attr('social'));
        $("#lab_profile_edit_social").attr('placeholder',$(this).attr('social'));
      });
    });

    $("#lab_confirm_change").click(function(){
      if ($("#lab_profile_edit_bio").val().length > 200)
      {
        $("#lab_alert").html(_('Votre biographie est trop longue (max 200 caractères)','lab'));
      }
      else{
        //Cache tous les champs de modification
        $(".lab_profile_edit").hide();
        $("#lab_confirm_change").hide();
        //Cache les icônes des réseaux non définis
        $(".lab_profile_social[href='']").hide();
        //Affiche le bouton modifier et les champs actuels
        $("#lab_profile_edit").show();
        $(".lab_current").show();
        //Remplit le tableau socials avec les réseaux sociaux modifiés ^^
        socials={};
        $(".lab_profile_social[modified=true]").each(function() {
          socials[$(this).attr('social')]=$(this).attr('href');
        });
        regex=/\"/g;
        lab_profile_edit($(this).attr('user_id'),
                         $("#lab_profile_edit_phone").val(),
                         $("#lab_profile_edit_url").val(),
                         $("#lab_profile_edit_bio").val().replace(regex,"”").replace(/\'/g,"’"),
                         $("#lab_profile_card").attr('bg-color'),
                         $("#lab_profile_edit_halID").val(),
                         $("#lab_profile_edit_halName").val(),
                         socials);
      }
    });
    $(".lab_profile_social").each(function (index) {
      if($(this).attr('href').length) {
        $(this).show();
      }
    });
    $("#lab_profile_edit_social").keyup(function(){
      if ( $("#lab_profile_edit_social").val().startsWith('http') || $("#lab_profile_edit_social").val().length==0) {
        $(".lab_profile_social[social="+$(this).attr('social')+"]").attr('href',$(this).val());
      } else {
        $(".lab_profile_social[social="+$(this).attr('social')+"]").attr('href',socialURLS[$(this).attr('social')].replace('@',$(this).val()));
      }
      $(".lab_profile_social[social="+$(this).attr('social')+"]").attr('modified','true');
    });
    $("#lab_profile_edit_halID").keyup(function() {
      $("#lab_profile_testHal_id").attr("href",HalID_URL.replace('@',$(this).val()));
    });
    $("#lab_profile_edit_halName").keyup(function() {
      $("#lab_profile_testHal_name").attr("href",HalName_URL.replace('@',$(this).val()));
    });
  });
}
if ( jQuery( "#lab_profile_card" ).length ) {
  LABloadProfile();
}
function lab_profile_edit(user_id,phone,url,bio,color,hal_id,hal_name) {
  data = {
    'action' : 'lab_profile_edit',
    'phone' : phone,
    'user_id' : user_id,
    'url' : url,
    'description' : bio,
    'bg_color': color,
    'hal_id': hal_id,
    'hal_name': hal_name,
    'socials' : socials,
  }
  jQuery.post(LAB.ajaxurl, data, function(response) {
    jQuery("#lab_profile_card")[0].outerHTML=response.data;
    LABloadProfile();
  });
}