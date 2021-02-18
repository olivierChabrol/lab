/* front end 21 04 2020 */
if(typeof __ === 'undefined') {
  const { __, _x, _n, sprintf } = wp.i18n;
}

jQuery(function($){
  /*** DIRECTORY ***/ 
  var travels = [];
  var meansOfTransport = new Array();
  var meansOfTransportReverse = new Array();

  
  $("#lab-directory-group-id").on('change', function() {
    loadDirectory();
  });
  $("#lab-directory-thematic").on('change', function() {
    loadDirectory();
  });
  $("#lab_mission_save").click(function() {
    invitation_submit(function() {
      loadAdminPanel();
      return;
    });
  });

  $("#lab_mission_validate").click(function() {
    data = {
      'action': 'lab_mission_validate',
      'mission_id': $("#lab_mission_id").val()
    };
    callAjax(data, null, loadAdminPanel, null, null);
  });

  $("#lab_mission_refuse").click(function() {
    data = {
      'action': 'lab_mission_refuse',
      'mission_id': $("#lab_mission_id").val()
    };
    callAjax(data, null, loadAdminPanel, null, null);
  });


  var dateClass='.datechk';
  $(document).ready(function ()
  {
    if (document.querySelector(dateClass) != null && document.querySelector(dateClass).type !== 'date') {  
      $(dateClass).datepicker({
        dateFormat : "yy-mm-dd"
      });
    }
    hideShowInvitationDiv();
  });

  function loadDirectory()
  {
    let letter = $("#letterSearch").val();
    let group = $("#lab-directory-group-id").val();
    let thematic = $("#lab-directory-thematic").val();
    href = "/linstitut/annuaire/?letter=%";
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
        $("#lab_alert").html(__('Your biography is too long (max 200 characters)','lab'));
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

/**************************************************************************************************************************************************************
 * MISSION
 **************************************************************************************************************************************************************/

function getEditTravelField() {
  return ["dateGoTo", "timeGoTo", "countryFrom", "cityFrom", "countryTo", "cityTo", "mean", "cost", "ref", "rt", "dateReturn", "timeReturn", "carbon_footprint", "loyalty_card_number", "loyalty_card_expiry_date", "nb_person", "travelId"];
}

function loadMeanOfTransportMap() {
  // load only once, store it inside the global variable meansOfTransport
  if (meansOfTransport.length == 0) {
    $("#lab_mission_edit_travel_div_mean option").each(function()
    {
      meansOfTransport[$(this).val()] = $(this).html();
      meansOfTransportReverse[$(this).html()] = $(this).val();
    });
  }
}

/**
 * Get means of transport inside the options of the #lab_mission_edit_travel_div_mean select
 */
function listMeanOfTransport() {
  loadMeanOfTransportMap();
  return meansOfTransport;
}

function getMeanOfTransportCode(mean) {
  loadMeanOfTransportMap();
  return meansOfTransportReverse[mean];
}

/**
 * Get fields in DIV values, and save them into the table
 */
function saveTravelModification(id) {
  console.log("[saveTravelModification] " + id);
  let f = {};
  let val = "";
  let fields = getEditTravelField();

  data = {
    'action' : 'lab_travel_save',
    'missionId' : $("#lab_mission_id").val(),
  }
  // just to reinject the db id inside the correct line id
  data['jsId'] = id;

  for (let i = 0 ; i < fields.length ; i++) {
    if(fields[i].startsWith("country")) {
      val = $("#lab_mission_edit_travel_div_" + fields[i]).countrySelect("getSelectedCountryData")['iso2'];
    }
    else if (fields[i] == "rt") {
      val = 0;
      if($('#lab_mission_edit_travel_div_rt').is(":checked")) {
        val = 1;
      }
    }
    else {
      val = $("#lab_mission_edit_travel_div_" + fields[i]).val();
    }
    console.log("[saveTravelModification] " +fields[i] + " '" + val + "'");
    f[fields[i]] = val;
    data[fields[i]] = val;
  }
  console.log(travels);
  console.log(id);
  if (travelExist(id)) {
    console.log("[saveTravelModification] [travel exist]");
    editTravelTd(id, f);
    sortTravelsByDate();
  }
  else {
    console.log("[saveTravelModification] [travel NEW]");
    addTravel(id, f, null, null);
  }

  // if edit existing mission with travels 
  if ($("#lab_mission_token").length && $("#lab_mission_token").val() != 0) {
    callAjax(data, "Travel updated", updateTravelIdFromDb, null, null);
  }
}

function updateTravelIdFromDb(data) {
  $("#travel_travelId_" + data.jsId).attr("tv", data.id);
}

function loadAdminPanel() {
  console.log("loadAdminPanel");
  window.location.href = "/wp-admin/admin.php?page=lab_admin_mission_manager";
}

function displayTravels(data) {

  $.each(data, function (i, obj){

    let strToGo = obj.travel_date.split(' ');
    if(obj.travel_datereturn != null) {
      var strReturn = obj.travel_datereturn.split(' ');
    }
    else {
      var strReturn = "";
    }
    let fields = {};
    fields["dateGoTo"]    = strToGo[0];
    fields["timeGoTo"]    = strToGo[1];
    fields["countryFrom"] = obj.country_from;
    fields["cityFrom"]    = obj.travel_from;
    fields["countryTo"]   = obj.country_to;
    fields["cityTo"]      = obj.travel_to;
    fields["mean"]        = obj.means_of_locomotion;
    fields["cost"]        = obj.estimated_cost;
    fields["ref"]         = obj.reference;
    fields["carbon_footprint"] = obj.carbon_footprint;
    fields["loyalty_card_number"] = obj.loyalty_card_number;
    fields["loyalty_card_expiry_date"] = obj.loyalty_card_expiry_date;
    fields["rt"]          = obj.round_trip;
    fields["dateReturn"]  = strReturn[0];
    fields["timeReturn"]  = strReturn[1];
    fields["nb_person"]   = obj.nb_person;
    fields["travelId"]    = obj.id;
    addTravel(getNewTravelId(), fields, obj.id, obj.mission_id);
  });
}

function travelExist(id) {
  return travels.includes(parseInt(id));
}

function addTravelId(id) {
  travels.push(parseInt(id));  
}

function deleteTravelId(id) {
  console.log(travels);
  travels.splice($.inArray(id,travels) ,1 );
  console.log(travels);
}

function getNewTravelId() {
  console.log("[getNewTravelId]");
  let max = 0;
  for (let i = 0 ; i < travels.length ; i++) {
    if (travels[i] > max) {
      max = parseInt(travels[i]);
    }
  }
  console.log(max + 1);
  return max + 1;
}

/*function incrNbRowBelow(val) {
  let lastRow = getNewTravelId() - 1;
  let travelsList = {};
  for(let i = val + 1 ; i <= lastRow ; i++) {
    travelsList[i] = getTravel(i);
    //travels[i]["travelId"] = travels[i]["travelId"] + 1;
    console.log("OUI " + travels[i]["travelId"]);
  }
}*/

function okHtmlField() {
  return '<i class="fa fa-check" aria-hidden="true" style="color: SpringGreen;"></i>';
}
function notOkHtmlField() {
  return '<i class="fa fa-times" aria-hidden="true" style="color: Tomato;"></i>';
}

function editTravelTd(id, fieldsVal) {
  console.log("[editTravelTd] " + id);
  let fields = getEditTravelField();
  for (let i = 0 ; i < fields.length ; i++) {
    let fieldId = "#travel_" + fields[i] + "_" + id;
    let val = fieldsVal[fields[i]];
    console.log("[editTravelTd] " + fieldId + " = '" + val + "'");

    if(fields[i].startsWith("country")) {
      $(fieldId).empty();
      $(fieldId).append(createCountryDiv(val));
    }
    else if (fields[i] == "rt") {
      let valDisplay = notOkHtmlField();
      if(val == "1") {
        valDisplay = okHtmlField();
      }
      $(fieldId).html(valDisplay);
    }
    else 
    {
      if (fields[i] == "cost") {
        $(fieldId).html(formatMoneyValue(val));
      } 
      else if (fields[i] == "travelId") {
        $(fieldId).val(val);
      } 
      else 
      {
        $(fieldId).html(val);
      }
    }
    $(fieldId).attr("tv", val);
  }
}

function emptyTravelDivFields() {
  $("#lab_mission_edit_travel_div_cityFrom" ).val(" ");
  $("#lab_mission_edit_travel_div_cityTo" ).val(" ");
  $("#lab_mission_edit_travel_div_dateGoTo" ).val(nowDay());
  $("#lab_mission_edit_travel_div_timeGoTo" ).val(nowHour());
  $("#lab_mission_edit_travel_div_dateReturn" ).val(null);
  $("#lab_mission_edit_travel_div_timeReturn" ).val(null);
  $("#lab_mission_edit_travel_div_ref" ).val(" ");
  $("#lab_mission_edit_travel_div_rt" ).val("false");
  $("#lab_mission_edit_travel_div_mean" ).val(getMeanOfTransportCode("Train"));
  $("#lab_mission_edit_travel_div_cost" ).val(0);
  $("#lab_mission_edit_travel_div_countryFrom" ).countrySelect("setCountry", "France");
  $("#lab_mission_edit_travel_div_countryTo" ).countrySelect("setCountry", "France");
  $("#lab_mission_edit_travel_div_carbon_footprint" ).val(" ");
  $("#lab_mission_edit_travel_div_loyalty_card_number" ).val(" ");
  $("#lab_mission_edit_travel_div_loyalty_card_expiry_date" ).val(" ");
  $("#lab_mission_edit_travel_div_nb_person" ).val("1");
  $("#lab_mission_edit_travel_div_travelId" ).val("");
}

function getTravel(id) {
  let fields = getEditTravelField();
  let travel = {};
  for (let i = 0 ; i < fields.length ; i++) {
    let fieldId = "#travel_" + fields[i] + "_" + id;
    //console.log("getTravel[" + id + "] " + fields[i] + " " +  $(fieldId).attr("tv"));
    travel[fields[i]] = $(fieldId).attr("tv");
  }
  return travel;
}

function editTravelDiv(id) {
  console.log("[editTravelDiv] id : " + id);
  let fields = getEditTravelField();
  console.log(fields)
  for (let i = 0 ; i < fields.length ; i++) {
    let fieldId = "#travel_" + fields[i] + "_" + id;
    //console.log("[editTravelDiv] id : " + fieldId + " " + $(fieldId).length);
    if ($(fieldId).length > 0) {
      let val = $(fieldId).html();
      //console.log("[editTravelDiv] fieldId (" + fieldId + ") = " + val);
      if(fields[i].startsWith("country")) {
        val = $(fieldId).attr("tv");
        $("#lab_mission_edit_travel_div_" + fields[i]).countrySelect("selectCountry", val);
      }
      else if(fields[i] == "rt") {
        if($(fieldId).attr("tv") == 1) {
          $("#lab_mission_edit_travel_div_rt").prop("checked", true);
          $("#returnSpanDate").show();
        }
        else {
          $("#lab_mission_edit_travel_div_rt").prop("checked", false);
          $("#returnSpanDate").hide();
        }
      }
      /*else if (fields[i] == "travelId") {
        val = $(fieldId).val();
        console.log("[editTravelDiv] #lab_mission_edit_travel_div_" + fields[i] + " = '" + val + "'");
        $("#lab_mission_edit_travel_div_" + fields[i]).val(val);
      }*/
      else 
      {
        if (fields[i] == "cost" || fields[i] == "travelId" || fields[i] == "carbon_footprint" || fields[i] == "nb_person" || fields[i] == "mean") {
          val = $(fieldId).attr("tv");
        }
        console.log("[editTravelDiv] #lab_mission_edit_travel_div_" + fields[i] + " = '" + val + "'");
        $("#lab_mission_edit_travel_div_" + fields[i]).val(val);
      }
    }
    else {
      console.log("[editTravelDiv] fieldId (" + fieldId + ")  NO VALUE ");
    }
  }
  console.log(travels);
  $("#lab_mission_edit_travel_save_button").attr("travelId", id);
  $("#lab_mission_edit_travel_div").show();

}

function deleteTravelTr(id, mission_id) {

  console.log("[deleteTravelTr] " + id);

  if ($("#lab_mission_token").length && $("#lab_mission_token").val() != 0) {
    data = {
      'action' : 'lab_travel_delete',
      'id' : $("#travel_travelId_" + id).attr("tv"),
      'mission_id' : mission_id
    }
    callAjax(data, "Travel deleted", null, null, null);
    $("#lab_mission_table_tr_"+id).remove();
  }
  else {
    $("#lab_mission_table_tr_"+id).remove();
  }
  deleteTravelId(id);
}

function addTravel(id, fields, travelId, mission_id) {
  console.log("[AddTravel] ");
  addTravelId(id);
  let tr = $("<tr/>").attr("id","lab_mission_table_tr_"+id);
  createDefaultTdToTr(tr, id, "dateGoTo", fields);
  createDefaultTdToTr(tr, id, "timeGoTo", fields);
  createTravelCountryTdToTr(tr, id, "countryFrom", fields);
  createDefaultTdToTr(tr, id, "cityFrom", fields);
  createTravelCountryTdToTr(tr, id, "countryTo", fields);
  createDefaultTdToTr(tr, id, "cityTo", fields);
  createSelectTdToTr(tr, id, "mean", fields, listMeanOfTransport());
  createTravelCostTdToTr(tr, id, "cost", fields);
  createDefaultTdToTr(tr, id, "ref", fields);
  createTravelBooleanField(tr, id, "rt", fields);
  createDefaultTdToTr(tr, id, "dateReturn", fields);
  createDefaultTdToTr(tr, id, "timeReturn", fields);
  createDefaultTdToTr(tr, id, "loyalty_card_number", fields);
  createDefaultTdToTr(tr, id, "loyalty_card_expiry_date", fields);
  let tdEdit = $("<td/>").attr("class", "pointer").attr("travelId",id).html('<i class="fa fa-pencil"  aria-hidden="true" travelId="'+id+'"></i>');
  let tdDel  = $("<td/>").attr("class", "pointer").attr({
    "id" : id,
    "travelId" : travelId,
    "missionId" : mission_id
  }).html('<i class="fa fa-trash-o" aria-hidden="true" travelId="'+id+'"></i>');

  tr.append(tdEdit);
  tr.append(tdDel);

  createTravelHiddenField(tdEdit, id, "carbon_footprint", fields);
  createTravelHiddenField(tdEdit, id, "nb_person", fields);
  createTravelHiddenField(tdEdit, id, "travelId", fields);

  tdEdit.click(function (e) {
    editTravelDiv($(this).attr("travelId"));
  });

  let append = true;

  tdDel.click(function (e) {
    deleteTravelTr($(this).attr("id"), $(this).attr("missionId"));
  });
  console.log("[AddTravel]");
  
  $("#lab_mission_travels_table_tbody").append(tr);

  sortTravelsByDate()
}

function sortTravelsByDate() {
  var travelsTr;
  travelsTr = document.querySelectorAll("#lab_mission_travels_table_tbody>tr");
  travelDateGoTo = travelsTr[0].querySelector("td").textContent;
  var tri = [].slice.call(travelsTr).sort(sortByDateTime);

  $("#lab_mission_travels_table_tbody").append(tri);
}

function sortByDateTime(a, b) {
  
  let dateA = new Date(a.querySelector("td").textContent + " " + a.querySelectorAll("td")[1].textContent);
  let dateB = new Date(b.querySelector("td").textContent + " " + b.querySelectorAll("td")[1].textContent);
  let dateDiff = dateA - dateB;
  return dateDiff;
}

function createTravelHiddenField(td, id, fieldName, fields) {
  //console.log("[createTravelHiddenField] " + fieldName + " value : '" + value + "'");
  let hi = $("<input/>").attr('type','hidden').attr("id", "travel_" + fieldName + "_" + id).attr("tv", fields[fieldName]);
  hi.val(fields[fieldName]);
  td.append(hi);
}

function createTravelBooleanField(tr, id, fieldName, fields) {
  let displayValue = notOkHtmlField();
  //if(fields[fieldName] === true || fields[fieldName] == "true") {
  if(fields[fieldName] == "1"){
    displayValue = okHtmlField();
  }
  createTravelTdToTr(tr, id, fieldName, displayValue, fields[fieldName]);
}

function createCountryDiv(value) {
  let divSelectedFlag = $("<div/>").addClass("country-select selected-flag");
  let div = $("<div/>").addClass("flag "+value);
  divSelectedFlag.append(div);
  return divSelectedFlag;
}

function createTravelCountryTdToTr(tr, id, fieldName, fields) {
  createTravelTdToTr(tr, id, fieldName, createCountryDiv(fields[fieldName]), fields[fieldName]);
}

function formatMoneyValue(value) {
  return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(value);
}

function createTravelCostTdToTr(tr, id, fieldName, fields) {
  createTravelTdToTr(tr, id, fieldName, formatMoneyValue(fields[fieldName]), fields[fieldName]);
}

function createSelectTdToTr(tr, id, fieldName, fields, displayMap) {
  console.log("[createSelectTdToTr]");
  console.log(displayMap);
  console.log(fields[fieldName]);
  console.log(displayMap[fields[fieldName]]);
  createTravelTdToTr(tr, id, fieldName, displayMap[fields[fieldName]], fields[fieldName]);
}

function createDefaultTdToTr(tr, id, fieldName, fields) {
  createTravelTdToTr(tr, id, fieldName, fields[fieldName], fields[fieldName]);
}

function createTravelTdToTr(tr, id, fieldName, displayVal, val) {
  let td = $("<td/>").attr("id","travel_" + fieldName + "_" + id).html(displayVal);
  if (val) {
    td.attr("tv", val);
  }
  tr.append(td);
}

function nowDay() {
  let today = new Date();
  let dd = String(today.getDate()).padStart(2, '0');
  let mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
  let yyyy = today.getFullYear();
  return yyyy+"-"+mm+"-"+dd;
}
function nowHour() {
  let today = new Date();
  let h = today.getHours();
  let m = today.getMinutes();

  if (h < 10) {
    h = "0"+h;
  }

  if (parseInt(m) < 10) {
    m = "0"+m;
  }

  return h+":"+m;
}

function addEmptyTravel(id) {  
  console.log("[addEmptyTravel]" + id);
  fields = {};
  fields["dateGoTo"]    = nowDay();
  fields["timeGoTo"]    = nowHour();
  fields["countryFrom"] = "fr";
  fields["cityFrom"]    = "Marseille";
  fields["countryTo"]   = "fr";
  fields["cityTo"]      = "Paris";
  fields["mean"]        = getMeanOfTransportCode("Train");
  fields["cost"]        = "0";
  fields["ref"]         = " ";
  fields["carbon_footprint"]         = "";
  fields["loyalty_card_number"] = "",
  fields["loyalty_card_expiry_date"] = "";
  fields["rt"]          = "false";
  fields["dateReturn"]  = null;
  fields["timeReturn"]  = null;
  fields["nb_person"]   = "1";
  fields["travelId"]    = "-1";
  console.log("[addEmptyTravel] " + id);
  console.log(fields);
  addTravel(id, fields, null, null);
}

function createFieldObj(val, displayVal) {
  let obj = new Object();
  obj.displayVal = displayVal;
  obj.val = val;
  return obj;
}

function hideShowInvitationDiv() {
  //console.log("[hideShowInvitationDiv]");
  if($("#lab_mission").length) {
    //console.log("[hideShowInvitationDiv] lab_mission exist");
    //console.log("[hideShowInvitationDiv] type : " + $("#lab_mission").prop('type'));
    if($("#lab_mission").prop('type') == 'select-one') {
      //console.log("[hideShowInvitationDiv] lab_mission de type select");
      if($("#lab_mission option:selected" ).text() == "Invitation") {
        $("#inviteDiv").show();
      }
      else{
        $("#inviteDiv").hide();
      }
    }
    else if($("#lab_mission").prop('type') == 'hidden') {
      //console.log("[hideShowInvitationDiv] lab_mission de type hidden");
      $("#inviteDiv").show();
    }
  }
  
}

function LABLoadInvitation() {
  //Plug-in international phone : https://github.com/jackocnr/intl-tel-input 
  var inputTel = document.querySelector("input[type=tel]");
  iti = window.intlTelInput(inputTel,({
    // utilsScript: "utils.js", //Inutile car utils JS chargé en dépendance
    initialCountry: "fr"
  }));
  console.log("[LABLoadInvitation]");
  console.log($("#lab_mission_token").val());
    if ($("#lab_mission_token").length && $("#lab_mission_token").val() != 0) {
      data = {
        'action' : 'lab_travels_load',
        'id' : $("#lab_mission_id").val(),
      }
      callAjax(data, null, displayTravels, null, null);
    }
    else {
      addEmptyTravel("0");
    }
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

    $("#lab_mission_validate").click(function(e) {
      console.log("[$(#lab_mission_validate).click]");
      invitation_submit(function() {
        return;
      });
      //*/
      /*
      let travelsFields = [];
      for (let i = 0 ; i < travels.length ; i++) {
        travelsFields.push(getTravel(travels[i]));
      }
      //*/
    });

    $("#lab_mission_edit_travel_save_button").click(function(e) {
      $("#lab_mission_edit_travel_div").hide();
      saveTravelModification($("#lab_mission_edit_travel_save_button").attr("travelId"));
    });

    $(".lab_fe_modal_close").click(function(e) {
      $("#lab_mission_edit_travel_div").hide();
    });

    if ($("#lab_mission option:selected" ).text() == "Invitation") {
      $("#inviteDiv").show();
    }

    $("#lab_mission").change(function (e) {
      console.log($("#lab_mission option:selected" ).text());
      hideShowInvitationDiv();
    });

    /*function hideShowInvitationDiv() {
      if($("#lab_mission option:selected" ).text() == "Invitation") {
        $("#inviteDiv").show();
      }
      else{
        $("#inviteDiv").hide();
      }
      
    }*/

    $("#missionForm h2").click(function() {
      if ( $("#missionForm").attr("wrapped")=="true" ) {
        $("#missionForm form").slideDown();
        $("#missionForm").attr("wrapped","false");
      } else {
        $("#missionForm form").slideUp();
        $("#missionForm").attr("wrapped","true");
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
    if ($("#missionForm").attr("newForm")==0) {
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
    $("#lab_mission_send_group_leader").click(function() {
      if ($("#missionForm").prop('submited')==null) {
        if (true) { //document.querySelector("#missionForm form").checkValidity()) {
          console.log("[$(#lab_mission_send_group_leader).click]");
          invitation_submit(function () {
            data = {
              'action': 'lab_invitations_complete',
              'token': $("#missionForm").attr("token")
            };
            jQuery.post(LAB.ajaxurl, data, function(response) {
              if (response.success) {
                jQuery("#missionForm").append("<br><h5>La demande a été complétée et transmise au responsable</h5>");
                jQuery("#missionForm").append(response.data);
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
          'token': $("#missionForm").attr("token")
        };
        jQuery.post(LAB.ajaxurl, data, function(response) {
          if (response.success) {
            jQuery("#missionForm").append("<br><h5>La demande a été complétée et transmise au responsable</h5>");
            jQuery("#missionForm").append(response.data);
            jQuery(".lab_send_group_chief").hide();
          }
        });
      }
    });
    $("#lab_send_manager").click(function() {
      if ($("#missionForm").prop('submited')==null) {
        if (true) { //document.querySelector("#missionForm form").checkValidity()) {
          console.log("[$(#lab_send_manager).click]");
          invitation_submit(function() {
            data = {
              'action': 'lab_invitations_validate',
              'token': $("#missionForm").attr("token")
            };
            jQuery.post(LAB.ajaxurl, data, function(response) {
              if (response.success) {
                jQuery("#missionForm").append(response.data);
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
          'token': $("#missionForm").attr("token")
        };
        jQuery.post(LAB.ajaxurl, data, function(response) {
          if (response.success) {
            jQuery("#missionForm").append("<br><h5>La demande a été validée et transmise au pôle budget</h5>");
            jQuery("#missionForm").append(response.data);
            jQuery(".lab_send_manager").hide();
          }
        });
      }
    });
}
console.log("$(\"#missionForm\").length :");
console.log($("#missionForm").length);

if ($("#missionForm").length > 0) {
  LABLoadInvitation();
}
function formAction() {
  console.log("[formAction]");
  invitation_submit(function() {
    return;
  });
}
function invitation_submit(callback) {
  console.log("[invitation_submit]" + travels);
  //document.querySelector("#primary-menu").scrollIntoView({behavior:"smooth"}); à faire correspondre au nouveau thème
  regex=/\"/g;
    $("#missionForm").prop('submited',true);
    charges = {
      'travel_to': $("#lab_cost_to").val()=='' ? null : $("#lab_cost_to").val(),
      'travel_from': $("#lab_cost_from").val()=='' ? null : $("#lab_cost_from").val(),
      'hostel': $("#lab_cost_hostel").val()=='' ? null : $("#lab_cost_hostel").val(),
      'meals': $("#lab_cost_meals").val()=='' ? null : $("#lab_cost_meals").val(),
      'taxi': $("#lab_cost_taxi").val()=='' ? null : $("#lab_cost_taxi").val(),
      'other': $("#lab_cost_other").val()=='' ? null : $("#lab_cost_other").val(),
    }

    let travelsFields = [];
    for (let i = 0 ; i < travels.length ; i++) {
      travelsFields.push(getTravel(travels[i]));
    }
    console.log("SALUT " + travelsFields[0]);
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
      'hostel_cost' : $("#lab_mission_hostel_cost").val(),
      'hostel_night' : $("#lab_mission_hostel_night").val(),
      'charges': charges,
      'travels': travelsFields,
    }
    if ($("#lab_email").attr('guest_id').length) {
      fields['guest_id'] = $("#lab_email").attr('guest_id');
    }
    if ($("#lab_mission_fund_origin").length) {
      fields['funding_source'] = $("#lab_mission_fund_origin").val();
      fields['host_group_id'] = $("#lab_group_name").val();
      //alert('$("#lab_group_name").val() : ' + $("#lab_group_name").val());
    }
    if ($("#missionForm").attr("hostForm")==1) {//La version invitant est affichée 
      fields['estimated_cost'] = $("#lab_estimated_cost").val();
      fields['maximum_cost'] = $("#lab_maximum_cost").val();
    }
    if ($("#missionForm").attr("newForm")==1) {//On crée une nouvelle invitation
      fields['comment'] = $("#lab_form_comment").val().replace(regex,"”").replace(/\'/g,"’");
      data = {
        'action': 'lab_invitations_new',
        'fields': fields
      };
      jQuery.post(LAB.ajaxurl, data, function(response) {
        if (response.success) {
          $("#missionForm").html(response.data);
          callback();
        }
      });
    } else { //On met à jour l'invitation existante
      fields['guest_id']=$("#lab_firstname").attr("guest_id");
      fields['token']=$("#missionForm").attr("token");
      data = {
        'action': 'lab_invitations_edit',
        'fields': fields
      };
      callAjax(data, null, callback, null, null);
    }
}
jQuery("#button_add_comment").click(function () {
  regex=/\"/g;
  jQuery(function ($) {
    data = {
      'action': 'lab_invitation_newComment',
      'token': $("#lab_invitation_newComment").attr("token"),
      'author_id' : $("#lab_comment_name").attr("user_id"),
      'content' : $("#lab_comment").val().replace(regex,"”").replace(/\'/g,"’")
    }
    jQuery.post(LAB.ajaxurl, data, function(response) {
      if (response.success) {
        console.log(response.data);
        $("#lab_invitation_oldComments").html(response.data);
        $("#lab_comment").val('');
      }
    });
  });
});

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

});