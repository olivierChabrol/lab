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

  $("[id^=lab_presence_div_]").each(function() {
    $(this).hide();
  });
  $("#lab_presence_table td[id^=lab_presence_td_]").each(function() {
    num = $(this).attr("num");
    //console.log("id " + $(this).attr("id") + " " + num);
    if (num == 1) {
      $(this).css("background-color", "#c6e48b");
    }
    if (num == 2) {
      $(this).css("background-color", "#7bc96f");
    }
    if (num == 3) {
      $(this).css("background-color", "#239a3b");
    }
    if (num > 3) {
      $(this).css("background-color", "#196127");
    }
  });

  var tdColor = "";
  $("#lab_presence_table td[id^=lab_presence_td_]").mouseover(function() {
    /*
    var id = $(this).attr("id");
    var pattern = "lab_presence_td_";
    var realId = id.substring(pattern.length, id.length);
    realId = "lab_presence_div_" + realId;
//    console.log(realId);
    $("#"+realId).show();
    //Popper.createPopper($(this), $("#"+realId));
    Popper.createPopper($(this),$("#"+realId), {
      placement: 'right',
    });
//*/
    //Popper.createPopper($(this), tooltip);
    tdColor = $(this).css("background-color");
    $(this).css("background-color","yellow");
  });

  $("#lab_presence_table td[id^=lab_presence_td_]").mouseout(function() {
    /*
    var id = $(this).attr("id");
    var pattern = "lab_presence_td_";
    var realId = id.substring(pattern.length, id.length);
    realId = "lab_presence_div_" + realId;
    //console.log(realId);
    $("#"+realId).hide();;
    //*/
    $(this).css("background-color",tdColor);
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
      window.location.href = "/user/" + userslug;
      event.preventDefault();
      $("#lab_directory_user_name").val(firstname + " " + lastname);
    }
  });

});

/******************************* ShortCode Profile *******************************/
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

/******************************* ShortCode Guest Invitation *******************************/
function LABLoadInvitation() {
  //Plug-in international phone : https://github.com/jackocnr/intl-tel-input 
  var inputTel = document.querySelector("input[type=tel]");
  iti = window.intlTelInput(inputTel,({
    // utilsScript: "utils.js", //Inutile car utils JS chargé en dépendance
    initialCountry: "fr"
  }));
  jQuery(function($) {
    //Plug-in country selector : https://github.com/mrmarkfrench/country-select-js
    $("#lab_country").countrySelect({
      defaultCountry: "fr",
      preferredCountries: ['fr', 'de', 'it', 'es', 'us'],
    });
    if ($("#lab_country").attr('countryCode')!="") {
      $("#lab_country").countrySelect("selectCountry",$("#lab_country").attr('countryCode'));
    }
    $("#lab_phone").keyup(function() {
      if ( !iti.isValidNumber() && iti.getValidationError()!=0) {
        $(this).css("border-color","#FF0000");
      } else {
        $(this).css("border-color","");   
      }
    });
    $("#lab_phone").blur(function() {
      $("#lab_phone").keyup();
      iti.setNumber(iti.getNumber());
      $("#lab_phone").attr('phoneVal',iti.getNumber());
    });
    $("#lab_mission").change(function(){
      if ($(this).val() == "other") {
        $("#lab_mission_other").show();
        $("#lab_mission_other_desc").show();    
      } else {
        $("#lab_mission_other").hide();
        $("#lab_mission_other_desc").hide();
      }
    });
    $("#lab_credit").change(function(){
      if ($(this).val() == "other")
      {
        $("#lab_credit_other").show();
        $("#lab_credit_other_desc").show();    
      }
      else
      {
        $("#lab_credit_other").hide();
        $("#lab_credit_other_desc").hide();  
      }
    });
    $("#lab_transport_to").change(function(){
      if ($(this).val() == "other") {
        $("#lab_transport_to_other").show();
      } else {
        $("#lab_transport_to_other").hide();
      }
    });
    $("#lab_transport_from").change(function(){
      if ($(this).val() == "other") {
        $("#lab_transport_from_other").show();
      } else {
        $("#lab_transport_from_other").hide();
      }
    });
    $('#lab_hostname').autocomplete({
      minChars: 2,
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
        event.preventDefault();
        $("#lab_hostname").val(firstname + " " + lastname);
        $("#lab_hostname").attr('host_id', ui.item.user_id);
      }
    });
    // $("input[type=submit]").click(function (e) {
    //   e.preventDefault();
    //   new_invitation_submit();
    // });
    if ($("#invitationForm").attr("newForm")==0) {
      if($('#lab_mission option[value="' + $("#lab_mission_other").val() + '"]').length > 0)
      {
        $('#lab_mission option[value="' + $("#lab_mission_other").val() + '"]').prop('selected', true); 
      }
      else
      {
        $('#lab_mission option[value="other"]').prop('selected', true);
        $("#lab_mission_other").show();
      }

      if($('#lab_credit option[value="' + $("#lab_credit_other").val() + '"]').length > 0)
      {
        $('#lab_credit option[value="' + $("#lab_credit_other").val() + '"]').prop('selected', true); 
      }
      else
      {
        $('#lab_credit option[value="other"]').prop('selected', true);
        $("#lab_credit_other").show();
      }
      
      if($('#lab_transport_to option[value="' + $("#lab_transport_to_other").val() + '"]').length > 0)
      {
        $('#lab_transport_to option[value="' + $("#lab_transport_to_other").val() + '"]').prop('selected', true); 
      }
      else
      {
        $('#lab_transport_to option[value="other"]').prop('selected', true);
        $("#lab_transport_to_other").show();
      }

      if($('#lab_transport_from option[value="' + $("#lab_transport_from_other").val() + '"]').length > 0)
      {
        $('#lab_transport_from option[value="' + $("#lab_transport_from_other").val() + '"]').prop('selected', true); 
      }
      else
      {
        $('#lab_transport_from option[value="other"]').prop('selected', true);
        $("#lab_transport_from_other").show();
      }
      if($('#lab_phone').attr('phoneval').length>0) {
        iti.setNumber($('#lab_phone').attr('phoneval'));
      }
    }
  });
}

jQuery(document).ready(function() {
  if ( jQuery( "#invitationForm" ).length ) {
    LABLoadInvitation();
  }
});
function invitation_submit() {
  console.log("submitted");
  document.querySelector("#primary-menu").scrollIntoView({behavior:"smooth"});
  jQuery(function($) {
    fields = { 
      'guest_firstName': $("#lab_firstname").val(),
      'guest_lastName': $("#lab_lastname").val(),
      'guest_email': $("#lab_email").val(),
      'guest_phone': $("#lab_phone").attr('phoneval'),
      'guest_country': $("#lab_country").countrySelect("getSelectedCountryData")['iso2'],
      'host_id': $("#lab_hostname").attr('host_id'),
      'mission_objective': $("#lab_mission").val()=="other" ? $("#lab_mission_other").val() : $("#lab_mission").val(),
      'needs_hostel' : $("#lab_hostel").prop('checked'),
      'travel_mean_from':  $("#lab_transport_from").val()=="other" ? $("#lab_transport_from_other").val() : $("#lab_transport_from").val(),
      'travel_mean_to':  $("#lab_transport_to").val()=="other" ? $("#lab_transport_to_other").val() : $("#lab_transport_to").val(),
      'start_date': $("#lab_arrival").val()+" "+$("#lab_arrival_time").val(),
      'end_date': $("#lab_departure").val()+" "+$("#lab_departure_time").val(),
    }
    if ($("#invitationForm").attr("hostForm")==1) {//La version invitant est affichée 
      fields['host_group_id'] = $("#lab_group_name").val();
      fields['funding_source'] = $("#lab_credit").val()=="other" ? $("#lab_credit_other").val() : $("#lab_credit").val();
      fields['estimated_cost'] = $("#lab_estimated_cost").val();
    }
    if ($("#invitationForm").attr("newForm")==1) {//On crée une nouvelle invitation
      data = {
        'action': 'lab_invitations_new',
        'fields': fields
      };
      jQuery.post(LAB.ajaxurl, data, function(response) {
        if (response.success) {
          $("#invitationForm")[0].outerHTML=response.data;
        }
      });
    } else { //On met à jour l'invitation existante
      fields['guest_id']=$("#lab_firstname").attr("guest_id");
      fields['token']=$("#invitationForm").attr("token");
      data = {
        'action': 'lab_invitations_edit',
        'fields': fields
      };
      jQuery.post(LAB.ajaxurl, data, function(response) {
        if (response.success) {
          jQuery("#invitationForm")[0].outerHTML=response.data;
        }
      });
    }
  });
}