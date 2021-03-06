/* front end 21 04 2020 */
const { __, _x, _n, sprintf } = wp.i18n;

/*** DIRECTORY ***/ 
var travels = [];

jQuery(function($){

  $("#lab-directory-group-id").on('change', function() {
    loadDirectory();
  });
  $("#lab-directory-thematic").on('change', function() {
    loadDirectory();
  });

  function loadDirectory()
  {
    let letter = $("#letterSearch").val();
    let group = $("#lab-directory-group-id").val();
    let thematic = $("#lab-directory-thematic").val();
    href = "/linstitut/annuaire/?letter=%";
    /*
    if (letter != "") {
      href += "?letter="+letter;
    }
    else{
      href += "?letter=%"
    }
    //*/
    if (group != "0") {
      if (letter != "") {
        href += "&";
      } else {
        href += "?";
      }
      href += "group="+group;
    }
    if (thematic != "0") {
      if (letter != "" || group != "") {
        href += "&";
      } else {
        href += "?";
      }
      href += "thematic="+thematic;
    }
    window.location.href = href;
  }

  $("[id^=delete_presence_]").each(function() {
    $(this).click(function() {
      //delete_presence_
      var attrId = $(this).attr("id");
      var pattern = "delete_presence_";
      var id = attrId.substring(pattern.length, attrId.length);
      //console.log(id);
      deletePresence(id);
    })
  });

  addDeleteThematicListener();

  $("#lab_fe_add_thematic").click(function() {
    data = {
      'action': 'lab_fe_thematic_add',
      'thematic_id': $("#lab_fe_thematic").val(),
      'user_id' : $("#userId").val()
    };
    $.post(LAB.ajaxurl,data,function(response){
      if (response.success) {
        deleteThematics();
      }
    });
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
      let userId =  ui.item.user_id;
      window.location.href = "/user/" + userslug;
      event.preventDefault();
      $("#lab_directory_user_name").val(firstname + " " + lastname);
    }
  });

});

function addDeleteThematicListener() {
  $(".delete_thematic").click(function() {
    data = {
      'action': 'lab_fe_thematic_del',
      'thematic_id': $(this).attr("thematic_id"),
      'user_id' : $("#userId").val()
    };
    $.post(LAB.ajaxurl,data,function(response){
      if (response.success) {
        deleteThematics();
      }
    });
  });
}

function addChangeMainThematicListener() {
  $(".lab_thematic_order").click(function() {
    data = {
      'action': 'lab_fe_thematic_togle_main',
      'thematic_id': $(this).attr("thematic_id"),
      'thematic_value': $(this).attr("thematic_value"),
      'user_id' : $("#userId").val()
    };
    $.post(LAB.ajaxurl,data,function(response){
      if (response.success) {
        deleteThematics();
      }
    });
  });
}

function loadThematics() {
  data = {
    'action': 'lab_fe_thematic_get',
    'user_id': $("#userId").val()
  };
  $.post(LAB.ajaxurl,data,function(response){
    if (response.success) {
      console.log("[loadThematics]] success");
      jQuery.each(response.data, function (index, value){
        console.log("[loadThematics] value : " + value["name"]);
        let li = $('<li />').html('*'+value["name"]);

        let thematicCssClass = 'lab_thematic_order';
        if (value["main"] == "1") {
          thematicCssClass += " lab_thematic_main";
        }
        let innerSpanStar = $('<span />').attr('class', thematicCssClass).attr('thematic_id', value['id']).attr('thematic_value', value["main"]);
        let innerIStar = $('<i />').attr('class', 'fas fa-star').attr('thematic_id', value['id']).attr("title",__('Change main thematic','lab'));
        innerSpanStar.append(innerIStar);
        li.append(innerSpanStar);
        
        let innerSpanDelete = $('<span />').attr('class', 'lab_profile_edit delete_thematic').attr('thematic_id', value['id']);
        let innerI = $('<i />').attr('class', 'fas fa-trash').attr('thematic_id', value['id']).attr("title",__('Delete thematic','lab'));
        innerSpanDelete.append(innerI);
        li.append(innerSpanDelete);

        $("#lab_profile_thematics").append(li);
        //li += '&nbsp;<span class="lab_profile_edit delete_thematic" thematic_id="' + value['id'] + '"><i thematic_id="' + value['id'] + '" class="fa fa-trash"></i></span>';
        
        $('.delete_thematic').show();
      }); 
      addDeleteThematicListener();
      addChangeMainThematicListener();
    }
  });
}

function deleteThematics(){
  $("#lab_profile_thematics").empty();
  loadThematics();
}

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
      color: $("#wp_lab_param_color").val(),
      move: function(tinycolor) {
        jQuery("#lab_profile_card").css('background-color',tinycolor.toHexString());
      },
      change: function(tinycolor) {
        if (tinycolor != null) {
          jQuery("#lab_profile_card").attr('bg-color',tinycolor.toHexString());
        }
      },
      hide: function() {
        jQuery("#lab_profile_card").css('background-color',jQuery("#lab_profile_card").attr('bg-color'));
      }
    });
    //Remplace le titre de la page par "Profil de "+Nom Prénom
    $(".entry-title").text("Profil de "+$('#lab_profile_name_span').text().replace("• "," "))
    //Fonction d'édition du profil
    $("#lab_profile_edit").click( function() {
      deleteThematics();
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
                         //*/
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

function getEditTravelField() {
  return ["dateGoTo", "timeGoTo", "countryFrom", "cityFrom", "countryTo", "cityTo", "mean", "cost", "ref", "rt", "dateReturn", "timeReturn"];
}

function saveTravelModification(id) {
  console.log("[saveTravelModification] " + id);
  let fields = getEditTravelField();
  let f = {};
  for (let i = 0 ; i < fields.length ; i++) {
    f[fields[i]] = $("#lab_mission_edit_travel_div_" + fields[i]).val();
  }
  if (travelExist(id)) {
    editTravelTd(id);
  }
  else {
    addTravelTd(id, f);
  }
}

function travelExist(id) {
  return travels.includes(id);
}

function addTravelId(id) {
  travels.push(id);  
}

function deleteTravelId(id) {
  console.log(travels);
  travels.splice($.inArray(id,y) ,1 );
  console.log(travels);
}

function getNewTravelId() {
  console.log("[getNewTravelId]");
  let max = 0;
  for (let i = 0 ; i < travels.length ; i++) {
    if (travels[i] > max) {
      max = travels[i];
    }
  }
  console.log(max + 1);
  return max + 1;
}

function okHtmlField() {
  return '<i class="fa fa-check" aria-hidden="true" style="color: SpringGreen;"></i>';
}
function notOkHtmlField() {
  return '<i class="fa fa-times" aria-hidden="true" style="color: Tomato;"></i>';
}

function editTravelTd(id) {
  console.log("[editTravelTd] " + id);
  let fields = getEditTravelField();
  for (let i = 0 ; i < fields.length ; i++) {
    let fieldId = "#travel_" + fields[i] + "_" + id;
    let val = "";
    if(fields[i].startsWith("country")) {
      val = $("#lab_mission_edit_travel_div_" + fields[i]).countrySelect("getSelectedCountryData")['iso2'];
      $(fieldId).empty();
      $(fieldId).attr("value", val);
      $(fieldId).append(createCountryDiv(val));
      //.html($("#lab_mission_edit_travel_div_" + fields[i]).countrySelect("getSelectedCountryData")['iso2']);
      //console.log("[editTravelTd] " + fields[i] + " " + val);
    }
    else if (fields[i] == "rt") {
      val = $('#lab_mission_edit_travel_div_rt').is(":checked");
      let valDisplay = notOkHtmlField();
      //console.log("[editTravelTd] " + fields[i] + " " + val);
      if(val) {
        valDisplay = okHtmlField();
      }
      $(fieldId).html(valDisplay);
    }
    else 
    {
      val = $("#lab_mission_edit_travel_div_" + fields[i]).val();

      //console.log("[editTravelTd] " + fields[i] + " " + );
      if (fields[i] == "cost") {
        $(fieldId).html(formatMoneyValue(val));
      } 
      else 
      {
        $(fieldId).html(val);
      }
    }
    $(fieldId).attr("value", val);
  }
}

function emptyTravelDivFields() {
  let fields = getEditTravelField();
  for (let i = 0 ; i < fields.length ; i++) {
    $("#lab_mission_edit_travel_div_" + fields[i]).val("");
  }
  $("#lab_mission_edit_travel_div_countryFrom" ).val("France");
  $("#lab_mission_edit_travel_div_countryTo" ).val("France");
}

function editTravelDiv(id) {
  console.log("[editTravelDiv] id : " + id);
  let fields = getEditTravelField();
  for (let i = 0 ; i < fields.length ; i++) {
    let fieldId = "#travel_" + fields[i] + "_" + id;
    if ($(fieldId).length > 0) {
      let val = $(fieldId).html();
      //console.log("[editTravelDiv] fieldId (" + fieldId + ") = " + val);
      if(fields[i].startsWith("country")) {
        val = $(fieldId).attr("value");
        $("#lab_mission_edit_travel_div_" + fields[i]).countrySelect("selectCountry", val);
      }
      else 
      {
        if (fields[i] == "cost") {
          val = $(fieldId).attr("value");
        }
        $("#lab_mission_edit_travel_div_" + fields[i]).val(val);
      }
    }
    else {
      console.log("[editTravelDiv] fieldId (" + fieldId + ")  NO VALUE ");
    }
  }
  $("#lab_mission_edit_travel_save_button").attr("travelId", id);
  $("#lab_mission_edit_travel_div").show();
}

function deleteTravelTr(id) {
  $("#lab_mission_table_tr_"+id).remove();
  deleteTravelId(id);
}

function addTravelTd(id, fields) {
  addTravel(id, fields["dateGoTo"],fields["timeGoTo"], fields["countryFrom"], fields["cityFrom"], fields["countryTo"], fields["cityTo"], fields["mean"], fields["cost"], fields["ref"], fields["rt"], fields["dateReturn"], fields["timeReturn"]);
}

function addTravel(id, dateGoTo, timeGoTo, countryFrom, from, countryTo, to, mean, cost, ref, rt, dateReturn, timeReturn) {
  addTravelId(id);
  let tr = $("<tr/>").attr("id","lab_mission_table_tr_"+id);
  createTravelTdToTr(tr, id, "dateGoTo", dateGoTo);
  createTravelTdToTr(tr, id, "timeGoTo", timeGoTo);
  createTravelCountryTdToTr(tr, id, "countryFrom", countryFrom.toLowerCase());
  createTravelTdToTr(tr, id, "cityFrom", from);
  createTravelCountryTdToTr(tr, id, "countryTo", countryTo.toLowerCase());
  createTravelTdToTr(tr, id, "cityTo", to);
  createTravelTdToTr(tr, id, "mean", mean);
  createTravelCostTdToTr(tr, id, "cost", cost);
  createTravelTdToTr(tr, id, "ref", ref);
  console.log("ar : " + rt);
  createTravelBooleanField(tr, id, "rt", rt);
  createTravelTdToTr(tr, id, "dateReturn", dateReturn);
  createTravelTdToTr(tr, id, "timeReturn", timeReturn);
  let tdEdit = $("<td/>").attr("class", "pointer").attr("travelId",id).html('<i class="fa fa-pencil"  aria-hidden="true" travelId="'+id+'"></i>');
  let tdDel  = $("<td/>").attr("class", "pointer").attr("travelId",id).html('<i class="fa fa-trash-o" aria-hidden="true" travelId="'+id+'"></i>');
  let tdAdd  = $("<td/>").attr("class", "pointer").attr("travelId",id).html('<i class="fa fa-plus" aria-hidden="true" travelId="'+id+'"></i>');
  tr.append(tdEdit);
  tr.append(tdDel);
  tr.append(tdAdd);

  tdEdit.click(function (e) {
    editTravelDiv($(this).attr("travelId"));
  });
  tdAdd.click(function (e) {
    emptyTravelDivFields();
    editTravelDiv(getNewTravelId());
  });
  tdDel.click(function (e) {
    deleteTravelTr($(this).attr("travelId"));
  });
  $("#lab_mission_travels_table_tbody").append(tr);
}

function createTravelBooleanField(tr, id, fieldName, value) {
  let displayValue = notOkHtmlField();
  if(value) {
    displayValue = okHtmlField();
  }
  createTravelTdToTr(tr, id, fieldName, displayValue, value);
}

function createCountryDiv(value) {
  let divSelectedFlag = $("<div/>").addClass("country-select selected-flag");
  let div = $("<div/>").addClass("flag "+value);
  divSelectedFlag.append(div);
  return divSelectedFlag;
}

function createTravelCountryTdToTr(tr, id, fieldName, value) {
  let td = $("<td/>").attr("id","travel_" + fieldName + "_" + id).attr("value", value);
  td.append(createCountryDiv(value));
  tr.append(td);
}

function formatMoneyValue(value) {
  return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(value);
}

function createTravelCostTdToTr(tr, id, fieldName, value) {
  createTravelTdToTr(tr, id, fieldName, formatMoneyValue(value), value);
}

function createTravelTdToTr(tr, id, fieldName, value, realValue = undefined) {
  let td = $("<td/>").attr("id","travel_" + fieldName + "_" + id).html(value);
  if (realValue != undefined) {
    td.attr("value", realValue);
  }
  tr.append(td);
}

function addEmptyTravel(id) {
  let today = new Date();
  let dd = String(today.getDate()).padStart(2, '0');
  let mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
  let yyyy = today.getFullYear();

  let h = today.getHours();
  let m = today.getMinutes();
  if (h < 10) {
    h = "0"+h;
  }
  
  if (parseInt(m) < 10) {
    m = "0"+m;
  }
  

  addTravel(id, yyyy+"-"+mm+"-"+dd, h+":"+m, "FR","Marseille", "FR", "Paris", "Train", 0.0, "", false, "0000-00-00", "00:00");
}

function LABLoadInvitation() {
  //Plug-in international phone : https://github.com/jackocnr/intl-tel-input 
  var inputTel = document.querySelector("input[type=tel]");
  iti = window.intlTelInput(inputTel,({
    // utilsScript: "utils.js", //Inutile car utils JS chargé en dépendance
    initialCountry: "fr"
  }));
  jQuery(function($) {
    addEmptyTravel("0");
    $("#inviteDiv").hide();
    $("#lab_mission_edit_travel_div").hide();
    $("#returnSpanDate").hide();
    $("#lab_mission_edit_travel_div_rt").change(function(e) {
      if ($('#lab_mission_edit_travel_div_rt').is(":checked"))
      {
        if($("#lab_mission_edit_travel_div_dateReturn").val() == "") {
          $("#lab_mission_edit_travel_div_dateReturn").val($("#lab_mission_edit_travel_div_dateGoTo").val());
          $("#lab_mission_edit_travel_div_timeReturn").val($("#lab_mission_edit_travel_div_timeGoTo").val());
        }
        $("#returnSpanDate").show();
      }
      else {
        $("#returnSpanDate").hide();
      }
    });

    $("#addTravel").click(function(e) {
      emptyTravelDivFields();
      editTravelDiv(getNewTravelId());
    });

    $("#lab_mission_edit_travel_save_button").click(function(e) {
      $("#lab_mission_edit_travel_div").hide();
      saveTravelModification($("#lab_mission_edit_travel_save_button").attr("travelId"));
    });

    $(".lab_fe_modal_close").click(function(e) {
      $("#lab_mission_edit_travel_div").hide();
    });

    $("#lab_mission").change(function (e) {
      if($("#lab_mission").val() == 250) {
        $("#inviteDiv").show();
      }
      else{
        $("#inviteDiv").hide();
      }
    });

    $("#invitationForm h2").click(function() {
      if ( $("#invitationForm").attr("wrapped")=="true" ) {
        $("#invitationForm form").slideDown();
        $("#invitationForm").attr("wrapped","false");
      } else {
        $("#invitationForm form").slideUp();
        $("#invitationForm").attr("wrapped","true");
      }
    });
    $("#lab_invitationComments h2").click(function() {
      if ( $("#lab_invitationComments").attr("wrapped")=="true" ) {
        $("#lab_invitationComments #lab_invitation_oldComments").slideDown();
        $("#lab_invitationComments").attr("wrapped","false");
      } else {
        $("#lab_invitationComments #lab_invitation_oldComments").slideUp();
        $("#lab_invitationComments").attr("wrapped","true");
      }
    });
    $("#lab_email").change(function(){
      data = {
        'action': 'lab_invitations_guestInfo',
        'email': $(this).val()
      };
      $.post(LAB.ajaxurl,data,function(response){
        if (response.success) {
          $(this).attr('guest_id',response.data['id']);
          $("#lab_firstname").val(response.data['first_name']);
          $("#lab_lastname").val(response.data['last_name']);
          iti.setNumber(response.data["phone"]);
          $("#residence_country").countrySelect("selectCountry",response.data['residence_country']);
          $("#residence_city").val(response.data['country']);
          $("#lab_language").countrySelect("selectCountry",response.data['language']);
        } else {
          $(this).attr('guest_id','');
        }
      });
    }); 
    //Plug-in country selector : https://github.com/mrmarkfrench/country-select-js
    $("#residence_country").countrySelect({
      defaultCountry: "fr",
      preferredCountries: ['fr', 'de', 'it', 'es', 'us'],
    });
    $("#lab_mission_edit_travel_div_countryFrom").countrySelect({
      defaultCountry: "fr",
      preferredCountries: ['fr', 'de', 'it', 'es', 'us'],
    });
    $("#lab_mission_edit_travel_div_countryTo").countrySelect({
      defaultCountry: "fr",
      preferredCountries: ['fr', 'de', 'it', 'es', 'us'],
    });
    if ($("#residence_country").attr('countryCode')!="") {
      $("#residence_country").countrySelect("selectCountry",$("#residence_country").attr('countryCode'));
    }
    //Plug-in country selector : https://github.com/mrmarkfrench/country-select-js
    $("#guest_language").countrySelect({
      defaultCountry: "fr",
      preferredCountries: ['fr', 'de', 'it', 'es', 'us'],
    });
    if ($("#guest_language").attr('countryCode')!="") {
      $("#guest_language").countrySelect("selectCountry",$("#guest_language").attr('countryCode'));
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
    //Affichage des champs autres lorsque l'option "autre" est sélectionnée
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
    //Autocomplete du nom de l'invitant
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
    //Si le formulaire contient déjà des informations, sélectionne les bonnes options
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
    else {
      if ($("#lab_hostname").attr('host_id')=='' && $("#lab_hostname").val()!='') {
        $("#lab_hostname").val('')
      };
    }
    //Boutons de validation
    $("#lab_send_group_chief").click(function() {
      if ($("#invitationForm").prop('submited')==null) {
        if (document.querySelector("#invitationForm form").checkValidity()) {
          invitation_submit(function () {
            data = {
              'action': 'lab_invitations_complete',
              'token': $("#invitationForm").attr("token")
            };
            jQuery.post(LAB.ajaxurl, data, function(response) {
              if (response.success) {
                jQuery("#invitationForm").append("<br><h5>La demande a été complétée et transmise au responsable</h5>");
                jQuery("#invitationForm").append(response.data);
                jQuery(".lab_send_group_chief").hide();
              }
            });
          });
        }
        else {
          alert("Vous devez d'abord compléter le formulaire");
        }
      } else {
        data = {
          'action': 'lab_invitations_complete',
          'token': $("#invitationForm").attr("token")
        };
        jQuery.post(LAB.ajaxurl, data, function(response) {
          if (response.success) {
            jQuery("#invitationForm").append("<br><h5>La demande a été complétée et transmise au responsable</h5>");
            jQuery("#invitationForm").append(response.data);
            jQuery(".lab_send_group_chief").hide();
          }
        });
      }
    });
    $("#lab_send_manager").click(function() {
      if ($("#invitationForm").prop('submited')==null) {
        if (document.querySelector("#invitationForm form").checkValidity()) {
          invitation_submit(function() {
            data = {
              'action': 'lab_invitations_validate',
              'token': $("#invitationForm").attr("token")
            };
            jQuery.post(LAB.ajaxurl, data, function(response) {
              if (response.success) {
                jQuery("#invitationForm").append(response.data);
                jQuery(".lab_send_manager").hide();
              }
            });  
          });
        } else {
          alert("Vous devez d'abord compléter le formulaire");
        }
      } else {
        data = {
          'action': 'lab_invitations_validate',
          'token': $("#invitationForm").attr("token")
        };
        jQuery.post(LAB.ajaxurl, data, function(response) {
          if (response.success) {
            jQuery("#invitationForm").append("<br><h5>La demande a été validée et transmise au pôle budget</h5>");
            jQuery("#invitationForm").append(response.data);
            jQuery(".lab_send_manager").hide();
          }
        });
      }
    });
  });
}
if (document.querySelector("#invitationForm")!=null) {
  LABLoadInvitation();
}
function formAction() {
  invitation_submit(function() {
    return;
  });
}
function invitation_submit(callback) {
  //document.querySelector("#primary-menu").scrollIntoView({behavior:"smooth"}); à faire correspondre au nouveau thème
  regex=/\"/g;
  jQuery(function($) {
    $("#invitationForm").prop('submited',true);
    charges = {
      'travel_to': $("#lab_cost_to").val()=='' ? null : $("#lab_cost_to").val(),
      'travel_from': $("#lab_cost_from").val()=='' ? null : $("#lab_cost_from").val(),
      'hostel': $("#lab_cost_hostel").val()=='' ? null : $("#lab_cost_hostel").val(),
      'meals': $("#lab_cost_meals").val()=='' ? null : $("#lab_cost_meals").val(),
      'taxi': $("#lab_cost_taxi").val()=='' ? null : $("#lab_cost_taxi").val(),
      'other': $("#lab_cost_other").val()=='' ? null : $("#lab_cost_other").val(),
    }
    fields = {
      'guest_firstName': $("#lab_firstname").val(),
      'guest_lastName': $("#lab_lastname").val(),
      'guest_email': $("#lab_email").val(),
      'guest_phone': $("#lab_phone").attr('phoneval'),
      'guest_language': $("#guest_language").countrySelect("getSelectedCountryData")['iso2'],
      'guest_residence_country': $("#residence_country").countrySelect("getSelectedCountryData")['iso2'],
      'guest_residence_city': $("#residence_city").val(),
      'host_id': $("#lab_hostname").attr('host_id'),
      'mission_objective': $("#lab_mission").val()=="other" ? $("#lab_mission_other").val().replace(regex,"”").replace(/\'/g,"’") : $("#lab_mission").val(),
      'needs_hostel' : $("#lab_hostel").prop('checked'),
      'travel_mean_from':  $("#lab_transport_from").val()=="other" ? $("#lab_transport_from_other").val() : $("#lab_transport_from").val(),
      'travel_mean_to':  $("#lab_transport_to").val()=="other" ? $("#lab_transport_to_other").val() : $("#lab_transport_to").val(),
      'forward_start_station':  $("#forward_start_station").val(),
      'return_end_station':  $("#return_end_station").val(),
      'forward_travel_reference':  $("#forward_travel_reference").val(),
      'return_travel_reference':  $("#return_travel_reference").val(),
      'start_date': $("#lab_arrival").val()+" "+$("#lab_arrival_time").val(),
      'end_date': $("#lab_departure").val()+" "+$("#lab_departure_time").val(),
      'charges': charges
    }
    if ($("#lab_email").attr('guest_id').length) {
      fields['guest_id'] = $("#lab_email").attr('guest_id');
    }
    if ($("#invitationForm").attr("hostForm")==1) {//La version invitant est affichée 
      fields['research_contract']= $("#lab_research_contrat").val().replace(regex,"”").replace(/\'/g,"’");
      fields['host_group_id'] = $("#lab_group_name").val();
      fields['funding_source'] = $("#lab_credit").val()=="other" ? $("#lab_credit_other").val() : $("#lab_credit").val();
      fields['estimated_cost'] = $("#lab_estimated_cost").val();
      fields['maximum_cost'] = $("#lab_maximum_cost").val();
      fields['search_contract']= $("#lab_research_contrat").val().replace(regex,"”").replace(/\'/g,"’");
    }
    if ($("#invitationForm").attr("newForm")==1) {//On crée une nouvelle invitation
      fields['comment'] = $("#lab_form_comment").val().replace(regex,"”").replace(/\'/g,"’");
      data = {
        'action': 'lab_invitations_new',
        'fields': fields
      };
      jQuery.post(LAB.ajaxurl, data, function(response) {
        if (response.success) {
          $("#invitationForm").html(response.data);
          callback();
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
          jQuery("#invitationForm").html(response.data);
          callback();
        }
      });
    }
  });
}
function lab_submitComment() {
  regex=/\"/g;
  jQuery(function ($) {
    data = {
      'action': 'lab_invitation_newComment',
      'token': $("#lab_invitation_newComment").attr("token"),
      'author' : $("#lab_comment_name").text(),
      'content' : $("#lab_comment").val().replace(regex,"”").replace(/\'/g,"’")
    }
    jQuery.post(LAB.ajaxurl, data, function(response) {
      if (response.success) {
        $("#lab_invitation_oldComments").html(response.data);
        $("#lab_comment").val('');
      }
    });
  });
}

/************ Liste des invitation ***********************/ 
function LABLoadInviteList() {
  jQuery(function ($){
    $("#lab_groupSelect").change(function() {
      lab_update_invitesList();
    });
    $("#lab_addPrefGroup").click(function() {
      group_ids = [];
      $(".lab_prefGroup_del").each(function() {
        group_ids.push($(this).attr('group_id'));
      });
      if (group_ids.includes(($("#lab_prefGroupsSelect").val())) ) {
        $("#lab_group_add_warning").html('Déjà préféré');
      } else {
        $("#lab_group_add_warning").html('');
        jQuery.post(LAB.ajaxurl,{
            action : 'lab_prefGroups_add',
            group_id: $("#lab_prefGroupsSelect").val()
            },
          function(response) {
            lab_updatePrefGroups();
          }
        );
      }
    });
    $("#lab_filter input").change(function(){
      lab_update_invitesList();
    });
    $("#lab_filter select").change(function(){
      lab_update_invitesList();
    });
    $("#lab_invite_detail_title").click(function(){
      if($("#lab_invite_details").attr("wrapped")=="true"){
        $("#lab_invite_details").slideDown();
        $("#lab_invite_details").attr("wrapped","false");
      } else {
          $("#lab_invite_details").slideUp();
          $("#lab_invite_details").attr("wrapped","true");
      }
    });
    if ($("#lab_prefGroupsSelect").children().length<2) {
      lab_updatePrefGroups();
      jQuery.post(LAB.ajaxurl,{action : 'list_users_groups'},
        function(response) {
          for(var i = 0; i< response.data[1].length; ++i)
          {
            $("#lab_prefGroupsSelect").append(jQuery('<option/>', 
            {
            value : response.data[1][i].group_id, 
            text : response.data[1][i].group_name
            }));
          }
        }
      );
    }
  });
  $(".lab_column_name").click(function(){
    $(".lab_column_name").attr("sel","");
    $(this).attr("sel","true");
    if($(this).attr("order") == null || $(this).attr("order") == 'desc' ) {
      $(this).attr("order","asc");
    } else {
      $(this).attr("order","desc");
    }
    lab_update_invitesList();
  });
}
function lab_updatePrefGroups() {
  jQuery.post(LAB.ajaxurl,
  {action: 'lab_prefGroups_update'},
  function(response) {
    jQuery("#lab_curr_prefGroups").html(response.data);
    lab_update_invitesList();
    jQuery(".lab_prefGroup_del").click( function() {
      jQuery.post(LAB.ajaxurl,{
          action : 'lab_prefGroups_remove',
          group_id: $(this).attr('group_id')
        },
        function(response) {
          lab_updatePrefGroups();
        }
      );
      $("#lab_group_add_warning").html('');
    });
  });
}

function displayLoadingGif()
{
  //jQuery("#loadingAjaxGif").show();
  $("#loadingAjaxGif").addClass('show');
}

function hideLoadingGif()
{
  //jQuery("#loadingAjaxGif").hide();
  $("#loadingAjaxGif").removeClass('show');
}

function lab_update_invitesList() {
  jQuery(function($) {
    statuses =[];
    $("#lab_status_filter input[type=checkbox]").each(function(){
      if ($(this).prop('checked')) {
        statuses.push($(this).val());
      }
    });
    data = {
      sortBy: $(".lab_column_name[sel=true]").attr('name'),
      order: $(".lab_column_name[sel=true]").attr('order'),
      page: $("#active").attr("page"),
      value: $("#lab_results_number").val(),
      status: statuses,
      year: $("#lab_filter_year").val(),
    };
    displayLoadingGif();
    switch ($("#lab_invite_list").attr('view')) {
      case 'admin':
        group_ids = [];
        $(".lab_prefGroup_del").each(function() {
          group_ids.push($(this).attr('group_id'));
        });
        data['action']='lab_invitations_adminList_update';
        data['group_ids']= group_ids;
        //Demande la liste mise à jour
        $.post(LAB.ajaxurl,data, function(response) {
          //Remplit le tableau avec la liste
          $("#lab_invitesListBody").html(response.data[1]);
          pages = Math.ceil(response.data[0]/data['value']);
          currentPage = data['page']<=pages ? data['page'] : pages;
          lab_pagination(pages,currentPage);
          hideLoadingGif();
          //Affecte les fonctions aux actions
          $(".lab_invite_showDetail").click(function(){
            $("#lab_invite_realCost_input").attr('token',$(this).attr('token'));
            $("#lab_invite_details").show();
            //Descend jusqu'à la partie "details"
            document.querySelector("#lab_invite_detail_title").scrollIntoView({behavior:"smooth"});
            //Récupère les commentaires
            displayLoadingGif();
            $.post(LAB.ajaxurl,{
              action : 'lab_invitations_comments',
              token : $(this).attr('token')
              },
              function (response) {
                $("#lab_invite_droite").html(response.data);
                hideLoadingGif();
              }
            );
            displayLoadingGif();
            //Récupère le résumé
            $.post(LAB.ajaxurl,{
              action : 'lab_invitations_summary',
              token : $(this).attr('token')
              },
              function (response) {
                $("#lab_invite_summary").html(response.data);
                hideLoadingGif();
              }
            );
            $("#lab_invite_budget").show();

            displayLoadingGif();
            jQuery.post(LAB.ajaxurl,{
              action : 'lab_invitations_realCost',
              token : $(this).attr('token')
              },
              function (response) {
                $("#lab_invite_realCost").html(response.data+"€");
                hideLoadingGif();
              }
            );
          });
          $(".lab_invite_takeCharge").click(function() {
            displayLoadingGif();
            jQuery.post(LAB.ajaxurl,{
              action : 'lab_invitations_assume',
              token : $(this).attr('token')
              },
              function (response) {
                lab_update_invitesList();
                hideLoadingGif();
              }
            );
          });
        });
      break;
      case 'chief':
        data['action'] = 'lab_invitations_chiefList_update';
        data['group_id']= $("#lab_groupSelect").val();
        console.log($("#lab_groupSelect").val());
        console.log(data['group_id']);
        if (data['group_id'] != null)
        {
          console.log("data['group_id'] != null");
          $.post(LAB.ajaxurl,data, function(response) {
            $("#lab_invitesListBody").html(response.data[1]);
            pages = Math.ceil(response.data[0]/data['value']);
            currentPage = data['page']<=pages ? data['page'] : pages;
            lab_pagination(pages,currentPage);
            hideLoadingGif();
          });
        }
        else{
          hideLoadingGif();
        }
      break;
      case 'host':
        data['action'] = 'lab_invitations_hostList_update';
        $.post(LAB.ajaxurl,data, function(response) {
          $("#lab_invitesListBody").html(response.data[1]);
          pages = Math.ceil(response.data[0]/data['value']);
          currentPage = data['page']<=pages ? data['page'] : pages;
          lab_pagination(pages,currentPage);
          hideLoadingGif();
        });
        break;
    }
  });
}
function lab_submitRealCost() {
  data = {
    'action':'lab_invitations_add_realCost',
    'value' : $("#lab_invite_realCost_input").val(),
    'forward_carbon_footprint' : $("#forward_carbon_footprint").val(),
    'return_carbon_footprint' : $("#return_carbon_footprint").val(),
    'token' : $("#lab_invite_realCost_input").attr("token")
  }
  jQuery.post(LAB.ajaxurl,data,
    function(response)
    {
      if(response.success)
      {
        $("#lab_invite_realCost_input").val('');
        jQuery.post(LAB.ajaxurl,{
          action : 'lab_invitations_realCost',
          token : $("#lab_invite_realCost_input").attr('token')
          },
          function (response) {
            $("#lab_invite_realCost").html(response.data+"€");
          }
        );
      }
    })
}
if (document.querySelector("#lab_invite_list")!=null) {
  LABLoadInviteList();
}
function lab_pagination(pages, currentPage) {
  data = {
    'action': 'lab_invitations_pagination',
    'pages': pages,
    'currentPage': currentPage
  };
  jQuery.post(LAB.ajaxurl,data,function(response){
    if (response.success) {
      jQuery("#pagination-digg")[0].outerHTML=response.data;
      $(".page_previous:not(.gris)").click(function() {
        currentPage = parseInt($("#active").attr("page"));
        $("#active").attr("id","");
        $(".page_number[page=" + (currentPage - 1) + "]").attr("id","active");
        lab_update_invitesList();
      });
      $(".page_next:not(.gris)").click(function() {
        currentPage = parseInt($("#active").attr("page"));
        $("#active").attr("id","");
        $(".page_number[page=" + (currentPage + 1) + "]").attr("id","active");
        lab_update_invitesList();
      });
      $(".page_number").click(function() {
        $("#active").attr("id","");
        $(this).attr("id","active");
        lab_update_invitesList();
      });
    }
  });
}