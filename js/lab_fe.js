/* front end 21 04 2020 */

//alert(__("Algebraic geometry (zero and positive characteristic)","lab"));

jQuery(function($){
  /*** DIRECTORY ***/ 
  var descriptions = [];
  var typesOfDescription = new Array();
  var typesOfDescriptionReverse = new Array();
  var travels = [];
  var meansOfTransport = new Array();
  var meansOfTransportReverse = new Array();

  if ($("#lab_hal_tools_table").length) {
    labToolsLoad();
  }

  if ($("#lab_php_student_table").length) {
    loadPhpStudent();
  };

  $("#lab_internship_year").on('change', function() {
    loadInternship();
  });
  $("#lab_internship_add_intern_button").click(function() {
    displayAddInternDiv();
  });
  
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

  if ($("#lab_internship_funder_table_content").length) {
    loadContractFunders();
  }

  $("#lab_request_send").click(function() {
    if ($("#lab_request_title").val().trim() == "" || $("#lab_request_text").val().trim() == "") {
      highlight_empty_field($("#lab_request_title"));
      highlight_empty_field($("#lab_request_text"));
      /*
      if ($("#lab_request_title").val().trim() == "") {
        $("#lab_request_title").css('border-color', 'red');
      }
      else {
        $("#lab_request_title").css('border-color', '');
      }
      //*/
    }
    else
    {
      let data = {
        'action': 'lab_request_save',
        'request_id': $("#lab_request_id").val(),
        'request_previsional_date': $("#lab_request_previsional_date").val(),
        'request_end_date': $("#lab_request_end_date").val(),
        'request_type': $("#lab_request_type").val(),
        'request_title': $("#lab_request_title").val(),
        'request_text': $("#lab_request_text").val(),
      };
      request_get_expenses(data);
      callAjax(data, __("Request send", "lab"), forwardToRequestList, null, null);
      //callAjax(data, __("Request send", "lab"), null, null, null);
    }
  });




  $("#lab_mission_cancel").click(function() {
    data = {
      'action': 'lab_mission_cancel',
      'mission_id': $("#lab_mission_id").val()
    };
    callAjax(data, null, loadAdminPanel, null, null);
  });

  $("#lab_mission_complete").click(function() {
    data = {
      'action': 'lab_mission_complete',
      'mission_id': $("#lab_mission_id").val()
    };
    callAjax(data, null, loadAdminPanel, null, null);
  });

  $("#lab_mission_validate").click(function() {
    if($("#lab_hostname").attr("host_id") == $("#lab_mission_currentUser_id").val())
    {
      $("#lab_mission_question_dialog").modal();
      $("#lab_mission_question_title").html("Validate you own mission ?");
      $("#lab_mission_question_callback_fct").val("missionGLValidateMission");
    }
    else {
      missionGLValidateMission();
    }
  });

  $("#lab_mission_question_delete_confirm").click(function () {
    var callBackFct = $("#lab_mission_question_callback_fct").val();
    if (callBackFct != undefined && callBackFct != "") {
      var fn = window[callBackFct];
      fn();
    }
  });

  $("#lab_mission_refuse").click(function() {

    if($("#lab_hostname").attr("host_id") == $("#lab_mission_currentUser_id").val())
    {
      $("#lab_mission_question_dialog").modal();
      $("#lab_mission_question_title").html("Refuse you own mission ?");
      $("#lab_mission_question_callback_fct").val("missionGLRefuseMission");
    }
    else {
      missionGLRefuseMission();
    }
  });
  
  $("#lab_mission_tic").click(function() {
    data = {
      'action':'lab_mission_tic',
      'mission_id': $("#lab_mission_id").val()
    };
    callAjax(data, null, loadAdminPanel, null, null);

  });

  $("#lab_mission_description_file").click(function(e){
    uploadFile();
  });

  $("[id^=lab_request_description_file]").click(function(e){
    request_perform_click(null, $(this));
  });

  $("[id^=lab-request-list-admin_filter_]").change(function(e){
    loadRequests();
  });
  $("#lab_internship_add_email").focusout(function(e) {
    loadInternUser();
  });
  $("#labModalContentClose").click(function() {
    internShipCloseModal();
  });
  $("#lab_internship_add_intern_close").click(function() {
    console.log("clic annuler");
    internShipCloseModal();
  });
  $("#lab_internship_add_intern_close_icon").click(function() {
    internShipCloseModal();
  });
  $("#lab_internship_add_confirm").click(function() {
    internShipSaveModal();
  });
  $("#lab_internship_delete_confirm").click(function() {
    let d = labFindModalPrentDiv($(this));
    d.modal('hide');
    internship_delete($("#lab_modal_obj_id").val());
  });
  $(".closeModalAction").click(function() {
    let d = labFindModalPrentDiv($(this));
    d.modal('hide');
  });

  if ($("#lab_request_list_table").length) {
    loadOwnRequests();
  }

  if($("#lab_request_list_admin_table").length) {
    loadRequests();
  }

  if($("#lab_internship_body").length) {
    $(".entry-title").text("Stages");
   //$("#lab_internship_add_intern").hide();
    loadInternship();
  }

  if ($("#lab_request_ingo_tab_legal").length) {
    let ids = getRequestInfoTabs();
    ids.forEach(function (elm) {
      $("#"+elm).click(function(e){
        toggleTab($(this), getRequestInfoTabs);
      });
    });
    toggleTab($("#lab_request_ingo_tab_legal"), getRequestInfoTabs);
  }

  function loadPhpStudent(page = 1) {
    let data = {
      'action': 'lab_get_phd_student',
      'filters': null,
      'order': null,
      'page': page,
    };
    callAjax(data, null, displayPhdStudent, null, null);
  }

  function displayPhdStudent(data) {
    clearPhdStudent();
    console.log(data);
    data["data"].forEach(function(elm) {
      
      let user_id = elm["user_id"];
      let host_id = elm["host_id"];
      let tr = $("<tr/>");
      let td = $("<td/>");
      let user = data["users"][user_id];
      let host = data["users"][host_id];
      td.html(user['first_name'] + ' ' + user['last_name'].toUpperCase());
      tr.append(td);
      //console.log(elm);

      td = $("<td/>");
      td.html(user['lab_user_thesis_title']);
      tr.append(td);

      td = $("<td/>");
      if(host) {
        td.html(host['first_name'] + ' ' + host['last_name'].toUpperCase());
      }
      else {
        td.html("");
      }
      tr.append(td);
      td = $("<td/>");
      td.html(user['lab_user_phd_school']);
      tr.append(td);
      td = $("<td/>");
      td.html(user['lab_user_country']);
      tr.append(td);
      td = $("<td/>");
      td.html(" ");
      td.html(data["phd_support"][user['lab_user_phd_support']]['slug']);
      tr.append(td);
      td = $("<td/>");
      td.html(elm['begin']);
      tr.append(td);
      td = $("<td/>");
      td.html(user['lab_user_thesis_date']);
      tr.append(td);
      td = $("<td/>");
      td.html(user['lab_become']);
      tr.append(td);
      td = $("<td/>");
      group = "";
      user["group"].forEach(function(group_elm) {
        group += group_elm["acronym"];
      });
      td.html(group);
      tr.append(td);

      $("#lab_php_student_table_body").append(tr);
    });
    for (i = 0 ; i < data["total"]; i++) {
      let a = $("<a/>");
      a.attr("page", i+1);
      a.attr("href", "#");
      a.click(function () {
        loadPhpStudent($(this).attr("page"));
      });
      let lt = "" + (i+1);
      if (data["page"] == i+1) {
        lt = "<b>" + lt + "</b>";
      }
      a.html(lt);
      $("#lab_php_student_table_pagination").append(a);
    }
    
  }

  function clearPhdStudent() {
    $("#lab_php_student_table_body").empty();
    $("#lab_php_student_table_pagination").empty();
  }

  function labFindModalPrentDiv(obj) {
    return obj.parentsUntil(".labModalTest").parent();
  }

  function loadInternUser() {
    let data = {
      'action': 'lab_get_user_by_email',
      'email': $("#lab_internship_add_email").val(),
    };
    callAjax(data, null, internshipDisplayUser, null, null);
  }

  function internshipDisplayUser(data) {
    if (data["email"] != null && data["email"] != "") {
      $("#lab_internship_add_user_id").val(data["user_id"]);
      $("#lab_internship_add_email").val(data["email"]);
      $("#lab_internship_add_firstname").val(data["first_name"]);
      $("#lab_internship_add_lastname").val(data["last_name"]);
    }
  }



  function internShipSaveModal() {
    let data = {
      'action': 'lab_internship_save',
    };
    let fields = internShipModalFields();
    fields.forEach(element => data[element] = $("#lab_internship_add_"+element).val());
    let financialFields = internshipFinancialFields();
    let financialData = [];
    let i = 1;
    for (i = 1; i < 5; i++) {
      let d = {};
      financialFields.forEach(element => d[element] = $("#lab_internship_add_f_"+element+"_"+i).val());
      financialData.push(d);
    }
    data["financials"] =  financialData;
    //console.log(data);
    callAjax(data, null, internShipCloseModal, null, null);
    //callAjax(data, null, null, null, null);
  }

  function internShipClearFields() {
    let fields = internShipModalFields();
    fields.forEach(element => $("#lab_internship_add_"+element).val(""));
    $("#lab_internship_add_convention_state").val(1);
    $("#lab_internship_host_name").val("");
    let financialFields = internshipFinancialFields();
    let i = 1;
    for (i = 1; i < 5; i++) {
      financialFields.forEach(element => $("#lab_internship_add_f_"+element+"_"+i).val(""));
    }
    $("#lab_internship_add_intern_cost").empty();
  }

  function internShipCloseModal() {
    internShipClearFields();
    console.log("[internShipCloseModal]");
    $("#lab_internship_add_intern").modal('hide');
    loadInternship();
  }

  function internshipFinancialFields() {
    return ["id", "team", "tutelage", "nb_month", "amount"];
  }

  function internShipModalFields() {
    return ["id", "user_id", "firstname", "lastname", "firstname", "email", "training", "training", "establishment", "begin", "end", "host_id","convention_state"];
  }

  function displayAddInternDiv() {
    $("#lab_internship_add_intern_cost").empty();
    $("#lab_internship_add_intern").modal();
  }

  function loadInternship() {
    let data = {
      'action': 'lab_internship_load',
      'year' : $("#lab_internship_year").val(),
    };
    callAjax(data, null, displayInternshipList, null, null);
  }

  function loadContractFunders() {
    let data = {
      'action': 'lab_admin_contract_funder_list',
    };
    callAjax(data, null, displayContractFundersForDiv, null, null);
  }

  function displayContractFundersForDiv(data) {

    for (i = 1 ; i < 5; i++) {
      let select =  $("<select/>").attr("id", "lab_internship_add_f_team_"+ i);
      let subSelect = $("<select/>").attr("id", "lab_internship_add_f_tutelage_"+ i);
      let funders = {};
      //let first = data[0].param_value;
      let option = $("<option/>").attr("value", "0").html("--");
      select.append(option);
      $.each(data, function(i, obj) {
        option = $("<option/>").attr("value", obj.id).html(obj.label);
        select.append(option);
        funders[obj.id] = [];
        $.each(obj.child, function(i, obj1) {
          funders[obj.id].push(obj1);
        });
      });
      //reloadSubFunderSelect(first, subSelect, funders);
      let tr = $("<tr/>");
      let td1 = $("<td/>");
      let td2 = $("<td/>");
      let td3 = $("<td/>");
      let td4 = $("<td/>");
      td1.append(select);
      td2.append(subSelect);
      let id = $("<input />").attr("type", "hidden").attr("id", "lab_internship_add_f_id_"+ i);
      let nbMonth = $("<input />").attr("type", "text").attr("id", "lab_internship_add_f_nb_month_"+ i).attr("size", "2").attr("maxlength", "2");
      td3.append(nbMonth).append(id);
      let amount = $("<input />").attr("type", "text").attr("id", "lab_internship_add_f_amount_"+ i).attr("size", "4");
      td4.append(amount);
      tr.append(td1);
      tr.append(td2);
      tr.append(td3);
      tr.append(td4);
      console.log(funders);
      select.on( "change", function() {
        //console.log("[displayContractFundersForDiv] change");
        let val = "";
        $("#"+select.attr("id")+" option:selected").each( function() {
          val += $( this ).val();
        });
        reloadSubFunderSelect(val, subSelect, funders);
      });

      $("#lab_internship_funder_table_content").append(tr);
    }
  }

  function reloadSubFunderSelect(val, subSelect, funders) {
    //console.log("[reloadSubFunderSelect] add to : " + subSelect.attr("id") + " val : " + val);
    //console.log(funders);
    subSelect.empty();
    if (val != "" && val != "0") {
      $.each(funders[val], function(i, obj) {
        let option = $("<option/>").attr("value", obj.id).html(obj.label);
        subSelect.append(option);
        //console.log("[reloadSubFunderSelect] add : " + obj.label);
      });
    }
  }

  function displayInternship(data) {
    console.log("[displayInternship]");
    $("#lab_internship_add_intern").modal();
    let fields = internShipModalFields();
    //loadContractFunders();
    fields.forEach(element => $("#lab_internship_add_"+element).val(data[element]));
    $("#lab_internship_host_name").val(data["host"]["first_name"]+" "+data["host"]["last_name"]);
    $.each(data["financials"], function(i, obj) {
      $.each(obj, function(index, value){
        $("#lab_internship_add_f_" + index + "_"+(i+1)).val(value);
        console.log("[displayInternship] " + "lab_internship_add_f_" + index + "_"+(i+1) + " -> " + value);
        if (("lab_internship_add_f_" + index + "_"+(i+1)).startsWith('lab_internship_add_f_team')) {
          $("#lab_internship_add_f_" + index + "_"+(i+1)).change();
        }
      });
    });
    console.log(data);
    
    internLoadCostPerMonth(data["id"]);
  }

  function internUpdateCostPerMonth(id, field, value, internId) {
    console.log("[internUpdateCostPerMonth] id : " + id);
    let data = {
      'action' : 'lab_internship_update_cost',
      'id' : id,
      'field' : field,
      'value' : value,
      'internId' : internId,
    }
    callAjax(data, null, displayCostPerMonth, null, null);

  }

  function internLoadCostPerMonth(id) {
    console.log("[internLoadCostPerMonth] id : " + id);
    let data = {
      'action' : 'lab_internship_load_cost',
      'id' : id,
    }
    callAjax(data, null, displayCostPerMonth, null, null);
  }

  function displayCostPerMonth(data) {
    console.log("[displayCostPerMonth]");
    $("#lab_internship_add_intern_cost").empty();
    let table = $('<table class="table"/>');
    let thead = $('<thead/>');
    //let thNum = $('<th/>').html("#");
    let thHourlyRate = $('<th/>').html("Taux horaire");
    let thMonth = $('<th/>').html("Mois");
    let thOpenDay = $('<th/>').html("Jours ouvrés");
    let thAbsentDay = $('<th/>').html("Jours absent");
    let thCost = $('<th/>').html("Montant");
    let thPayed = $('<th/>').html("Payé ?");
    //thead.append(thNum);
    thead.append(thMonth);
    thead.append(thHourlyRate);
    thead.append(thOpenDay);
    thead.append(thAbsentDay);
    thead.append(thCost);
    thead.append(thPayed);
    table.append(thead);
    $.each(data["cost"], function(i, obj) {
      let tr = $('<tr/>');
      //let td = $('<td/>').html(obj.id);
      let tdMonth = $('<td/>').html(getMonth(obj.month));
      let tdHourlyRate = $('<td/>').append(createInternCostInputField(obj.month+"_hourly_rate",obj.id, "hourly_rate", obj.hourly_rate, 3));//('<input type="text" id="'+obj.month+'_hourly_rate" field="hourly_rate" objId="'+obj.id+'">'+obj.hourly_rate+'</i>');
      let tdNbOpenDay = $('<td/>').html(obj.nb_open_day);
      let tdNbAbsentDay = $('<td/>').html(createInternCostInputField(obj.month+"nb_day_absent",obj.id, "nb_day_absent", obj.nb_day_absent, 3));
      let tdCost = $('<td/>').html((obj.hourly_rate * 7 * (obj.nb_open_day - obj.nb_day_absent)).toFixed(2));

      let tdPayed = $('<td/>');
      let a = $('<a />').attr("objId", obj.id).attr("value", obj.payed);
      if(obj.payed == "1") {
        a.html('<i class="fa fa-toggle-on fa-xl pointer" aria-hidden="true"></i>');
      }
      else {
        a.html('<i class="fa fa-toggle-off fa-xl pointer" aria-hidden="true"></i>');
      }
      a.click(function() {
        let toggleValue = "0";
        console.log()
        if ($(this).attr("value") == "0") {
          toggleValue = 1;
        }
        internUpdateCostPerMonth(obj.id, "payed", toggleValue, $("#lab_internship_add_id").val());
      });
      tdPayed.append(a);
      //tr.append(td);
      tr.append(tdMonth);
      tr.append(tdHourlyRate);
      tr.append(tdNbOpenDay);
      tr.append(tdNbAbsentDay);
      tr.append(tdCost);
      tr.append(tdPayed);
      table.append(tr);
    });
    $("#lab_internship_add_intern_cost").append(table);
  }

  function createInternCostInputField(id, objId, field, value, nbDigit, internId) {
    let input = $('<input/>').attr("type", "text").attr("id", id).attr("objId", objId).attr("maxlength", nbDigit).attr("size", nbDigit).attr("field", field).val(value);
    input.focusout(function() {
      internUpdateCostPerMonth(objId, field, input.val(), $("#lab_internship_add_id").val());
    });
    return input;
  }

  function getMonth(index) {
    index = parseInt(index);
    switch(index) {
      case 1 :
      return "Janvier";
      case 2: 
      return "Février";
      case 3 :
      return "Mars";
      case 4: 
      return "Avril";
      case 5 :
      return "Mai";
      case 6: 
      return "Juin";
      case 7 :
      return "Juillet";
      case 8: 
      return "Aout";
      case 9 :
      return "Septembre";
      case 10: 
      return "Octobre";
      case 11 :
      return "Novembre";
      case 12: 
      return "Décembre";
      default:
      return "None!!!";
    }
  }

  function displayInternshipList(data)
  {
    console.log("[displayInternshipList]");
    $("#lab_internship_body").empty();
    $.each(data["results"], function(i, obj) {
      console.log("[displayInternshipList] " + obj.id);
      let tr = $('<tr />').attr("ObjId", obj.id);
      let tdName = $("<td />").attr("ObjId", obj.id).attr("id", "name_"+obj.id);

      let intern_name = "";

      if (data["users"][obj.user_id]) {
        intern_name = data["users"][obj.user_id]["first_name"]+" " + data["users"][obj.user_id]["last_name"];
        tdName.html(intern_name);
      }
      else {
        tdName.html(obj.user_id);
      }
      tr.append(tdName);
      let tdStart = $("<td />").attr("ObjId", obj.id).attr("id", "begin_"+obj.id).html(obj.begin);
      let tdEnd   = $("<td />").attr("ObjId", obj.id).attr("id", "end_"+obj.id).html(obj.end);
      //let tdId = $("<td />").attr("ObjId", obj.id).html(obj.id);
      let tdTo = $("<td />").attr("ObjId", obj.id).html(" -> ");
      let convention = "Non signée";
      switch(obj.convention_state) {
        case "0":
          convention="Non signée";break;
        case "1":
          convention="Signée";break;
        case "2":
          convention="En cours";break;
      }
      let tdConvention = $("<td />").attr("ObjId", obj.id).html(convention);

      let tdHost = $("<td />").attr("ObjId", obj.id);
      if (data["users"][obj.host_id]) {
        tdHost.html(data["users"][obj.host_id]["first_name"]+" " + data["users"][obj.host_id]["last_name"]);
      }
      else {
        tdHost.html("NaN");
      }

      //tr.append(tdId);  
      tr.append(tdName);
      tr.append(tdStart);
      tr.append(tdTo);
      tr.append(tdEnd);
      tr.append(tdHost);
      tr.append(tdConvention);
      //if (obj.financials.length > 0) {
      //  for (var i = 0 ; i < 4 ; i++) {
      $.each(obj.financials, function (i, value) {
          let tdTeam     = $("<td />").attr("ObjId", obj.id).html(data["teams"][value.team]);
          let tdTutelage = $("<td />").attr("ObjId", obj.id).html(data["tutelage"][value.tutelage]);
          let tdNbMonths = $("<td />").attr("ObjId", obj.id).html(value.nb_month);
          let tdAmmount  = $("<td />").attr("ObjId", obj.id).html(value.amount);
          tr.append(tdTeam);
          tr.append(tdTutelage);
          tr.append(tdNbMonths);
          tr.append(tdAmmount);
      });

      let aEdit   = $('<a />').attr("class", "lab-page-title-action lab_mission_edit").attr("objId", obj.id).html('<i class="fa fa-pencil fa-lg pointer" aria-hidden="true" title="Modifier le stage de '+intern_name+'"></i>&nbsp;');
      //let aSee    = $('<a />').attr("class", "lab-page-title-action lab_mission_see").attr("objId", obj.id).html('<i class="fa fa-eye fa-lg pointer" aria-hidden="true" title="Voir le stage de '+intern_name+'"></i>&nbsp;');
      let aDelete = $('<a />').attr("class", "lab-page-title-action lab_budget_info_delete").attr("objId", obj.id).attr("id", "lab-internship-list-delete-button"+obj.id).html('<i class="fa fa-trash fa-lg pointer" aria-hidden="true" title="Supprimer le stage de '+intern_name+'"></i>');
  
      $(aEdit).click(function () {
        internship_edit($(this).attr("objId"));
      });

      $(aDelete).click(function () {
        internship_display_delete_ask($(this).attr("objId"));
      });

      /*
      $(aSee).click(function () {
        internship_create_cost($(this).attr("objId"));
      });
      //*/

      $(aDelete).click(function (){
        //displayModalDeleteRequest($(this).attr("objId"));
      });
      let td = $('<td />').attr("class", "lab_keyring_icon").append(aEdit).append(aDelete);

      tr.append(td);
      $("#lab_internship_body").append(tr);
    });
  }

  function internship_create_cost(id) {
    let data = {
      'action' : 'lab_internship_create_cost',
      'id' : id,
    }
    callAjax(data, null, displayInternship, null, null);
  }

  function internship_edit(id) {
    let data = {
      'action' : 'lab_internship_get',
      'id' : id,
    }
    callAjax(data, null, displayInternship, null, null);
  }

  function internship_display_delete_ask(id) {
    $("#lab_modal_obj_id").val(id);
    $("#lab_internship_delete_text_content").empty();
    $("#lab_internship_delete_text_content").html($("#name_" + id).html() + " du " + $("#begin_" + id).html() + " au " + $("#end_" + id).html());
    $("#lab_internship_delete_ask_intern").modal();
  }

  function internship_delete(id) {
    let data = {
      'action' : 'lab_internship_delete',
      'id' : id,
    }
    callAjax(data, null, loadInternship, null, null);
  }

  function highlight_empty_field(field) {
    let fieldId = field.attr("id");
    let errorMsgId = fieldId + "ValidationMessage";
    $("."+errorMsgId).remove();
    if (field.val().trim() == "") {
      field.css('border-color', 'red');
      field.after("<span class='" + errorMsgId + "' style='color:red;'>Obligatoire</span>");
    }
    else {
      field.css('border-color', '');
    }
  }

  function request_get_expenses(data) {
    console.log("[request_get_expenses] ");
    let expenses_type = ["transport", "hosting", "fooding"];
    let expenses = {};
    let i = 0;
    data["expenses_number"] = expenses_type.length;
    expenses_type.forEach(element => {
      console.log("[request_get_expenses] element : " + element);
      if($("#lab_request_expense_" + element + "_id").val() != "") {
        data["expense_id_"+i] = $("#lab_request_expense_" + element + "_id").val();
      }
      data["expense_financial_support_"+i] = $("#lab_request_expense_financial_support_" + element).val();
      data["expense_name_"+i] = element;
      data["expense_value_"+i] = $("#lab_request_expense_" + element + "_amount").val();
      data["expense_type_"+i] = $("#lab_request_expense_" + element).val();
      i+=1;
    });
  }

  function request_save_before_upload(type) {
    console.log("[request_save_before_upload] type : " + type);
    data = {
      'action': 'lab_request_save',
      'request_id': $("#lab_request_id").val(),
      'request_previsional_date': $("#lab_request_previsional_date").val(),
      'request_end_date': $("#lab_request_end_date").val(),
      'request_type': $("#lab_request_type").val(),
      'request_title': $("#lab_request_title").val(),
      'request_text': $("#lab_request_text").val(),
    };

    if (type == 'nic') {
      fct = request_click_upload_nic;
    }
    else if (type == 'passport') {
      fct = request_click_upload_passport;
    }
    else if (type == 'rib') {
      fct = request_click_upload_rib;
    }
    else if (type == 'name') {
      fct = request_click_upload_name;
    }
    callAjax(data, __("Request send", "lab"), fct, null, null);
  }

  function request_click_upload_name(data)
  {
    console.log("[request_click_upload_name]");
    request_perform_click(data, $("#lab_request_description_file_name"), $("lab_request_add_file_name_name").val());
  }
  function request_click_upload_nic(data)
  {
    request_perform_click(data, $("#lab_request_description_file_nic"));
  }
  function request_click_upload_passport(data)
  {
    request_perform_click(data, $("#lab_request_description_file_passport"));
  }
  function request_click_upload_rib(data)
  {
    request_perform_click(data, $("#lab_request_description_file_rib"));
  }

  function request_perform_click(data, button, name = "") {
    if (data != null) {
      console.log(data);
      $("#lab_request_id").val(data);
    }
    
    if($("#lab_request_id").val()=="") {
      let buttonId = button.attr("id");
      let fileType = buttonId.substr(buttonId.lastIndexOf("_") + 1);
      console.log("Request not save, save it first : " + fileType);

      request_save_before_upload(fileType);
    }
    else {
      console.log("[request_perform_click] " + button.attr('id'));
      console.log("[request_perform_click] name : '" + name + "'");
      console.log(button.attr("file-type"));
      if (button.attr('id') == "lab_request_description_file_name") {
        name = $("#lab_request_add_file_name_name").val();
      }
      if (name != "") {
        request_uploadFileWithName("name", name);
      }
      else {
        request_uploadFileWithName(button.attr("file-type"));
      }
    }
    //*/
  }

  function getRequestInfoTabs() {
    return ["lab_request_ingo_tab_legal", "lab_request_ingo_tab_upload", "lab_request_ingo_tab_doc"];
  }

  function toggleTab(tab, idsList) {
    console.log("[toggleTab] click on " + $(tab).attr("id"));
    let ids = idsList();
    ids.forEach(function (elm) {
      $("#"+elm).removeClass("nav-tab-active");
      $("#"+elm).removeClass("lab-request-tab-active");
      $("#"+elm+"_div").hide();
    });
    tab.addClass("nav-tab-active");
    tab.addClass("lab-request-tab-active");
    $("#"+$(tab).attr("id")+"_div").show();
  }

  $("#lab_div_delete_confirm").click(function() {
    console.log($("#lab_contract_delete_dialog_contract_id").val());
    if($("#lab_request_delete_dialog_order").val() == "cancel") {
      deleteObj($("#lab_request_delete_dialog_request_id").val(),'lab_request_cancel');
    }
    else if ($("#lab_request_delete_dialog_order").val() == "delete") {
      deleteObj($("#lab_request_delete_dialog_request_id").val(),'lab_request_delete');
    }
  });

  $("#lab_request_upload_nic").click(function() {
    $("#lab_request_upload_dialog").modal();
  });


  if($("#lab_request_id").length && $("#lab_request_id").val() != "") {
    loadRequestById($("#lab_request_id").val());
  }
  if($("#lab_request_type").length) {
    //if ($("#lab_request_type").val() == )
    $("#lab_request_type").change(function(){
      request_hideDisplayPrevisionalDate($(this));
    });
  }

  if($("#tabs-request").length) {
    $("#tabs-request").tabs();
  }

  var dateClass='.datechk';
  $(document).ready(function ()
  {
    if (document.querySelector(dateClass) != null && document.querySelector(dateClass).type !== 'date') {  
      $(dateClass).datepicker({
        dateFormat : "yy-mm-dd"
      });
    }
    if ($("#lab_invitation_oldComments").length) {
      missionReloadComments();
    }
    hideShowInvitationDiv();
  });

  function request_hideDisplayPrevisionalDate(selectElm) {
    let optionSelected = $("option:selected", selectElm);
    let slug = optionSelected.attr("slug");
    if(slug == "rt_poe") {
      $("#lab_request_previsional_date_label").hide();
      $("#lab_request_previsional_date").hide();
      $("#lab_request_end_date_label").hide();
      $("#lab_request_end_date").hide();
    }
    else {
      $("#lab_request_previsional_date_label").show();
      $("#lab_request_previsional_date").show();
      $("#lab_request_end_date_label").show();
      $("#lab_request_end_date").show();
    }
  }

  function forwardToRequestList()
  {
    $(location).attr("href","/wp-admin/admin.php?page=lab_request_view");
  }

  function deleteObj(objId, action) {
    let data = {
      'action':action,
      'id':objId,
    }
    callAjax(data, null, loadOwnRequests, null, null);
  }

  function loadRequestById(id) {
    let data = {
        'action':'lab_request_get',
        'id':id,
    }
    callAjax(data, null, displayRequest, null, null);
  }

  function loadRequests() {
    
    let filtersElm = $("[id^=lab-request-list-admin_filter_]");
    let filters = {};
    console.log("[loadRequests]");
    $.each(filtersElm, function(i, obj) {
      let elmId = $(this).attr("id");
      let filter = elmId.substring(30);
      filters[filter] = $(this).val();
      console.log(elmId);
      console.log($(this).val());
    });
    console.log(filters);
    let data = {
      'action':'lab_request_list_all',
      'filters' : filters,
  }
    console.log(data);
    callAjax(data, null, displayAllRequests, null, null);
  }

  function loadOwnRequests() {
    let data = {
        'action':'lab_request_load_own_request'
    }
    callAjax(data, null, displayOwnRequests, null, null);
  }

  function deleteUploadedFile(fileId) {
    let data = {
        'action':'lab_request_delete_file',
        'id':fileId,
    };
    callAjax(data, null, request_emptyAndDisplayFiles, null, null);
  }

  function request_emptyAndDisplayFiles(data) {
    $("#lab_request_files").empty();
    let datas = {
      'action':'lab_request_load_files',
      'id':$("#lab_request_id").val(),
    };
    callAjax(datas, null, displayRequestEditFile, null, null);
  }

  function displayViewRequestHistoric(data) {
    console.log("[displayViewRequestHistoric]");
    console.log(data["admin"]);
    //console.log(data);
    $("#lab_request_historic").empty();
    let ul = $('<ul class="list-group" id="lab_resquest_list_historic"/>');
    $(data["historic"]).each(function( i, obj ) {
      let li = $('<li />').attr('class', 'list-group-item').attr("id", "lab_request_historic_"+obj.id);
      let text = obj.date + " ";
      if (obj.historic_type == 1) {
        text += " Created by ";
      }
      if (obj.historic_type == -1) {
        text += __(" Cancel request by ","lab");
      }
      if (obj.historic_type == 2) {
        text += __(" update by ","lab");
      }
      if (obj.historic_type == 10) {
        text += __(" take in charge by ","lab");
      }
      if (obj.historic_type == 20) {
        text += __(" validate by ","lab");
      }
      if (obj.user_id > 0) {
        text += data["users"][obj.user_id].first_name + " " + data["users"][obj.user_id].last_name;
      }
      li.html(text);
      if (data["admin"]) {
        let delButton = $("<button/>").attr("class", "btn btn-danger").html("Delete");
        li.append(delButton);
        $(delButton).click(function (){
          request_delete_historic(obj.id);
        });
      }
      
      ul.append(li);
    });
    $("#lab_request_historic").append(ul);
  }

  function request_delete_historic(histo_id) {
    let data = {
      'action' : 'lab_request_delete_histo',
      'id' : histo_id,
    };
    callAjax(data, null, displayViewRequestHistoric, null, null);
  }

  function displayViewRequestFiles(data) {
    console.log("[displayViewRequestFiles]");
    console.log(data);
    $("#lab_request_files").empty();
    let ul = $('<ul class="list-group" id="lab_resquest_list_files"/>');
    $(data).each(function( i, obj ) {
      //console.log("[displayViewRequestFiles] each");
      //console.log(obj);
      let li = $('<li />').attr('class', 'list-group-item').attr("id", "lab_request_file_"+obj.id);
      let fileLink = $("<a/>").attr("href", obj.url).attr("target","t"+i).html(obj.name);
      li.append(fileLink);
      ul.append(li);
    });
    $("#lab_request_files").append(ul);
  }

  function displayViewRequestExpenses(data) {
    console.log("[displayViewRequestExpenses]");
    console.log(data);
    $("#lab_request_expenses").empty();
    let ul = $('<ul class="list-group" id="lab_resquest_list_expenses"/>');
    $(data.expenses).each(function( i, obj ) {
      console.log("[displayViewRequestExpenses] each");
      console.log(obj);
      let li = $('<li />').attr('class', 'list-group-item').attr("id", "lab_request_expenses_"+obj.id);
      if (obj.type != -1) {
        let fs = data["financial_support"][obj.financial_support];
        li.html(obj.name + " " + obj.type_string + " " + obj.support_name + " " + obj.amount + " " + fs + " &euro;");
      }
      else {
        li.html(obj.name + " Exterior "  + obj.amount + " " + " &euro;");
      }
      ul.append(li);
    });
    $("#lab_request_files").append(ul);
  }

  function request_change_state(request_id, state) {
    let data = {
      'action' : 'lab_request_change_state',
      'id' : request_id,
      'state' : state,
    };
    callAjax(data, null, displayViewRequest, null, null);
  }

  function displayViewRequest(data) {
    $("#lab_request_type").hide();
    let options = {};
    $("#lab_request_type > option").each(function() {
      options[this.value] = this.text;
    });
    console.log(options);
    $("#lab_request_view").html(options[data["request_type"]]);
    $("#lab_request_title").html(data["request_title"]);
    $("#lab_request_text").html(data["request_text"]);
    $("#lab_request_applicant").html(data["first_name"] + " " + data["last_name"]);
    $("#lab_request_previsional_date").html(data["request_previsional_date"]);
    $("#lab_request_end_date").html(data["end_date"]);
    if (data["files"].length) {
      displayViewRequestFiles(data.files);
    }
    if (data["expenses"].length) {
      displayViewRequestExpenses(data);
    }
    if ($("#lab_resquest_admin_button").length) {
      //if (data["request_state"] < 10) {
        let budgetManagerButton = $("<button/>").attr("class", "btn btn-success").attr("objId", data.id).attr("id", "lab-request-change-state-button").html(__("Take in charge","lab"));
        $(budgetManagerButton).click(function (){
          request_change_state($(this).attr("objId"), 10);
        });
        $("#lab_resquest_admin_button").append(budgetManagerButton);
      //}
    }
    
    if ($("#lab_resquest_group_leader_button").length) {
      if (data["request_state"] < 20) {
        let budgetManagerButton = $("<button/>").attr("class", "btn btn-success").attr("objId", data.id).attr("id", "lab-request-change-state-button").html(__("Validate","lab"));
        $(budgetManagerButton).click(function (){
          request_change_state($(this).attr("objId"), 20);
        });
        $("#lab_resquest_group_leader_button").append(budgetManagerButton);
      }
    }
    if(data["request_state"]<0) {
      $("#lab_resquest_state").addClass("text-danger");
      $("#lab_resquest_state").html(__("Cancel", "lab"));
    }
    else if(data["request_state"]<10) {
      $("#lab_resquest_state").addClass("text-primary");
      $("#lab_resquest_state").html(__("New, pending", "lab"));
    }
    else if(data["request_state"] == 10) {
      $("#lab_resquest_state").addClass("text-primary");
      $("#lab_resquest_state").html(__("Take in charge", "lab"));
    }
    else if(data["request_state"] == 20) {
      $("#lab_resquest_state").addClass("text-primary");
      $("#lab_resquest_state").html(__("Validate", "lab"));
    }
    else {
      $("#lab_resquest_state").addClass("btn-outline-warning");
      $("#lab_resquest_state").html(__("???", "lab"));
    }
    $("#lab_resquest_files_directory").html(generatePath(data, options));
    if (data["historic"].length) {
      displayViewRequestHistoric(data);
    }
    //["request_previsional_date"], data["groups"].acronym, )
    //$("#lab_resquest_files_directory").html("/2022/"+data["groups"].acronym+"/"+data["first_name"]+'.'+data["last_name"]+'/'+options[data["request_type"]]+'/');
  }

  function generatePath(data, options) {
    console.log("[generatePath]");
    let dateStr = data.historic[0].date;
    let year = dateStr.substr(0, dateStr.indexOf("-"));
    //console.log(data);
    let path = "requests/";
    path += year;
    path += "/";
    path += data["groups"].acronym;
    path += "/";
    let userName = data["first_name"].toLowerCase()+'.'+data["last_name"].toLowerCase();
    userName = userName.replace(/\s/g,'');
    path += userName;
    path += "/";
    let requestType = options[data["request_type"]];
    const words = requestType.split(" ");
    for (let i = 0; i < words.length; i++) {
        words[i] = words[i][0].toUpperCase() + words[i].substr(1);
    }
    path += words.join("");
    path += "/";
    path += data.id;
    path += "/";
    return path;
  }

  function displayRequestEditFile(files) {
    let html = ""
    $(files).each(function( i, obj ) {
      console.log(obj.url);
      let fileLink = $("<a/>").attr("href", obj.url).attr("target","t"+i).html(obj.name);
      html += fileLink+", "
      $("#lab_request_files").append(fileLink);
      let aDel = $('<i />').attr('class', 'clickable fas fa-trash').attr("objId", obj.id).attr("id", "lab-delete-file-button-"+obj.id);

      $(aDel).click(function (){
        deleteUploadedFile($(this).attr("objId"));
      });
      $("#lab_request_files").append(aDel);
      $("#lab_request_files").append(", ");
    });
  }
  function displayRequestEditExpense(expenses) {
    $(expenses).each(function( i, obj ) {
      //console.log(obj.name);
      //console.log(obj.id);
      if (obj.id) {
        $("#lab_request_expense_" + obj.name + "_id").val(obj.id);
      }
      $("#lab_request_expense_" + obj.name + "_amount").val(obj.amount);
      if(obj.type==-1) {
        $("#lab_request_expense_" + obj.name).val(-1);
      }
      else {
        $("#lab_request_expense_" + obj.name).val(obj.type+"_"+obj.object_id);
      }
      $("#lab_request_expense_financial_support_" + obj.name).val(obj.financial_support);
    });
  }

  function displayRequest(data) {
    if ($("#lab_request_view").length) {
      displayViewRequest(data);
    }
    else {
      $("#lab_request_type").val(data["request_type"]);
      $("#lab_request_title").val(data["request_title"]);
      $("#lab_request_text").val(data["request_text"]);
      $("#lab_request_previsional_date").val(data["request_previsional_date"]);
      $("#lab_request_end_date").val(data["end_date"]);
      request_hideDisplayPrevisionalDate($("#lab_request_type"));
      displayRequestEditFile(data.files);
      displayRequestEditExpense(data.expenses);
    }
  }

  function displayRequests1(data, tableBody) {
    tableBody.empty();
    $.each(data, function(i, obj) {
        let tr = $('<tr />');
        let tdId = $('<td />').html(obj.id);
        let tdName = $('<td />').html(obj.first_name + " " + obj.last_name);
        let tdDate = $('<td />').html(obj.date);
        let tdTitle = $('<td />').html(obj.request_title);
        let tdState;
        if(obj.request_state < 0) {
          tdState = $('<td />').html(__("Cancel", "lab"));
        }
        else if (obj.request_state < 10) {
          tdState = $('<td />').html(__("New, pending", "lab"));
        }
        else if (obj.request_state == 10) {
          tdState = $('<td />').html(__("Take in charge", "lab"));
        }
        else if (obj.request_state == 20) {
          tdState = $('<td />').html(__("Validated", "lab"));
        }
        else {
          tdState = $('<td />').html(__("???", "lab"));
        }
        tr.append(tdId);
        tr.append(tdDate);
        tr.append(tdName);
        tr.append(tdTitle);
        tr.append(tdState);
        tableBody.append(tr);
        tr.append(createViewRequestButton(obj.id, obj));
    });
  }

  function displayRequests(data, tableBody) {
    console.log(tableBody);
    tableBody.empty();
    $.each(data, function(i, obj) {
        let tr = $('<tr />');
        let tdId = $('<td />').html(obj.id);
        let tdDate = $('<td />').html(obj.date);
        let tdTitle = $('<td />').html(obj.request_title);
        let tdState;
        if(obj.request_state < 0) {
          tdState = $('<td />').html(__("Cancel", "lab"));
        }
        else if (obj.request_state < 10) {
          tdState = $('<td />').html(__("New, pending", "lab"));
        }
        else if(obj.request_state == 10) {
          tdState = $('<td />').html(__("Take in charge", "lab"));
        }
        else if(obj.request_state == 20) {
          tdState = $('<td />').html(__("Validate", "lab"));
        }
        else {
          tdState = $('<td />').html(__("???", "lab"));
        }
        tr.append(tdId);
        tr.append(tdDate);
        tr.append(tdTitle);
        tr.append(tdState);
        tableBody.append(tr);
        tr.append(createEditRequestButton(obj.id, obj));
    });
  }

  function displayAllRequests(data) {
    {
      console.log("[displayAllRequests]");
      console.log(data["filters"]);
      console.log(data["filters"].length);

      $.each(data["filters"], function (key, value){ 
        $("#lab-request-list-admin_filter_" + key).val(value);
      });
      //*/
      displayRequests1(data["results"],$("#lab_request_list_table_tbody"));
    }
  }

  function displayOwnRequests(data)
  {
    console.log($("#lab_request_list_table_tbody"));
    displayRequests(data,$("#lab_request_list_table_tbody"));
  }

  function createEditRequestButton(id, data) {
    let url = window.location.href;
    console.log(url);
    if (url.indexOf("&") != -1) 
    {
      url = (""+url).substring(0, url.indexOf("&"));
    }
    url  += "&tab=entry&id="+id;
    return createActionRequestButton(id, data, "Edit", url);
  }

  function createViewRequestButton(id, data) {
    let url = window.location.href;
    console.log(url);
    if (url.indexOf("&") != -1) 
    {
      url = (""+url).substring(0, url.indexOf("&"));
    }
    url  += "&tab=entry&id="+id+"&view=1";
    return createActionRequestButton(id, data, "View", url);

  }

  function createActionRequestButton(id, data, editLabel, editValue) {   
    
    let userId  = $("#lab_request_user_id").val();
    let aEdit   = $('<a />').attr("class", "lab-page-title-action lab_mission_edit").attr("href",editValue).attr("objId", id).html(editLabel);
    let aCancel = $('<a />').attr("class", "lab-page-title-action lab_budget_info_delete").attr("objId", id).attr("id", "lab-cancel-div-button").html('<i class="fas fa-ban"></i>');
    let aDelete = $('<a />').attr("class", "lab-page-title-action lab_budget_info_delete").attr("objId", id).attr("id", "lab-delete-div-button").html('<i class="fas fa-trash"></i>');

    $(aCancel).click(function (){
      displayModalCancelRequest($(this).attr("objId"));
    });
    $(aDelete).click(function (){
      displayModalDeleteRequest($(this).attr("objId"));
    });
    let td = $('<td />').attr("class", "lab_keyring_icon").append(aEdit).append(aCancel);
    /*
    if(data["admin"]) {
      let aDelete = $('<a />').attr("class", "lab-page-title-action lab_budget_info_delete").attr("objId", id).attr("id", "lab-delete-div-button").html("X");
      td.append(aDelete);
    }
    //*/
    td.append(aDelete);

    return td;
  }

  function displayModalDeleteRequest(objId) {
    console.log("[displayModalCancelRequest] requestId : " + objId)
    $("#lab_request_delete_dialog").modal();
    $("#lab_request_delete_dialog_request_id").val(objId);
    $("#lab_request_delete_dialog_order").val("delete");
  }

  function displayModalCancelRequest(objId) {
      console.log("[displayModalCancelRequest] requestId : " + objId)
      $("#lab_request_delete_dialog").modal();
      $("#lab_request_delete_dialog_request_id").val(objId);
      $("#lab_request_delete_dialog_order").val("cancel");
  }

  function labToolsLoad()
  {
    data = {
      'action' : 'lab_hal_tools_load',
    }
    callAjax(data, "Travel updated", labToolsDisplay, null, null);
  }

  function labToolsDisplay(data) {
    //$("#lab_hal_tools_db").value(data);
    $.each(data, function (index, value){ 
      $("#lab_hal_tools_table_body").append("<tr><td>" + value["id"] + "</td><td></td><td>" + value["title"] + "</td><td>" + value["authors"] + "</td></tr>");
    });
  }

  function request_uploadFileWithName(name, fileName = "") {
    console.log("[request_uploadFileWithName] name:"+name);
    var file_data = $('#lab_request_add_file_'+name).prop('files')[0];
    var form_data = new FormData();
    form_data.append('file', file_data);
    if (fileName == "") {
      form_data.append('file_name_type', name);
    }
    else {
      form_data.append('file_name_type', fileName);
    }
    form_data.append('upload_from', 'request');
    form_data.append('action', 'md_support_save');
    form_data.append('request_id', $("#lab_request_id").val());
    $.ajax({
      url: '/wp-admin/admin-ajax.php',
      type: 'post',
      contentType: false,
      processData: false,
      data: form_data,
      success: function (response){
        console.log("[request_uploadFileWithName] success");
        console.log(response);
        $('.Success-div').html("Upload Successfull")
        displayViewRequestFiles(response.data);
        //$("#lab_request_file_"+name+"_url").val(response.data.url);
        //console.log(response.data.url);
      },
      error: function (response){
        console.log('error');
      }
    });
  }

  function uploadFile()
  {
    //console.log("[upload file]");
    var file_data = $('#lab_mission_description_PDF').prop('files')[0];
    var form_data = new FormData();
    form_data.append('file', file_data);
    form_data.append('file_name_type', 'nic');
    form_data.append('action', 'md_support_save');
    $.ajax({
      url: '/wp-admin/admin-ajax.php',
      type: 'post',
      contentType: false,
      processData: false,
      data: form_data,
      success: function (response){
        $('.Success-div').html("Upload PDF Successfully")
        $("#lab_mission_description_PDF_url").val(response.data.url);
        console.log(response.data.url);
      },
      error: function (response){
        console.log('error');
      }
    });
  }

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

  $("#lab_internship_host_name").autocomplete({
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
      $("#lab_internship_add_host_id").val(userId);
      event.preventDefault();
      $("#lab_internship_host_name").val(firstname + " " + lastname);
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
          let li = $('<li />').html(__(value["name"],'lab'));
          //let li = $('<li />').html(value["name"]);

          let thematicCssClass = 'lab_thematic_order';
          if (value["main"] == "1") {
            thematicCssClass += " lab_thematic_main";
          }
          let innerSpanStar = $('<span />').attr('class', thematicCssClass).attr('thematic_id', value['id']).attr('thematic_value', value["main"]);
          //let innerIStar = $('<i />').attr('class', 'fas fa-star').attr('thematic_id', value['id']).attr("title",__('Change main thematic','lab'));
          let innerIStar = $('<i />').attr('class', 'fas fa-star').attr('thematic_id', value['id']).attr("title",'Change main thematic');
          innerSpanStar.append(innerIStar);
          li.append(innerSpanStar);
          
          let innerSpanDelete = $('<span />').attr('class', 'lab_profile_edit delete_thematic').attr('thematic_id', value['id']);
          //let innerI = $('<i />').attr('class', 'fas fa-trash').attr('thematic_id', value['id']).attr("title",__('Delete thematic','lab'));
          let innerI = $('<i />').attr('class', 'fas fa-trash').attr('thematic_id', value['id']).attr("title",'Delete thematic');
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
    if ($(".entry-title").length) {
    //  $(".entry-title").text("Profil de "+$('#lab_profile_name_span').text().replace("• "," "))
    }
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
  return ["dateGoTo", "timeGoTo", "countryFrom", "cityFrom","stationFrom", "countryTo", "cityTo","stationTo", "mean", "company", "cost", "ref", "rt", "dateReturn", "timeReturn", "carbon_footprint", "loyalty_card_number", "loyalty_card_expiry_date", "nb_person", "travelId"];
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

function loadTypeOfDescriptionMap(){
  if (typesOfDescription.length == 0) {
    $("#lab_mission_edit_description_div_type option").each(function()
    {
      typesOfDescription[$(this).val()] = $(this).html();
      typesOfDescriptionReverse[$(this).html()] = $(this).val();
    });
  }
}

function listTypeOfDescription(){
  loadTypeOfDescriptionMap();
  return typesOfDescription;
}

function getTypeOfDescriptionCode(type){
  loadTypeOfDescriptionMap();
  return typesOfDescriptionReverse[type];
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
    fields["stationFrom"] = obj.station_from;
    fields["countryTo"]   = obj.country_to;
    fields["cityTo"]      = obj.travel_to;
    fields["stationTo"]   = obj.station_to;
    fields["mean"]        = obj.means_of_locomotion;
    fields["company"]     = obj.company;
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
  console.log(travels);
}

function arrayIndexOf(tab, id) {
  for (let i = 0 ; i < tab.length ; i++) {
    if (tab[i] == id) {
      return i;
    }
  }
  return -1;
}

function deleteTravelId(id) {
  console.log("deleteTravelId " + id);
  console.log(travels.length);
  console.log(travels);
  console.log("inArray " + arrayIndexOf(travels, id));
  travels.splice(arrayIndexOf(travels, id), 1);
  console.log(travels);
  console.log("deleteTravelIdEnd " + id);
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
  $("#lab_mission_edit_travel_div_stationFrom" ).val(" ");
  $("#lab_mission_edit_travel_div_cityTo" ).val(" ");
  $("#lab_mission_edit_travel_div_stationTo" ).val(" ");
  $("#lab_mission_edit_travel_div_dateGoTo" ).val(nowDay());
  $("#lab_mission_edit_travel_div_timeGoTo" ).val(nowHour());
  $("#lab_mission_edit_travel_div_dateReturn" ).val(null);
  $("#lab_mission_edit_travel_div_timeReturn" ).val(null);
  $("#lab_mission_edit_travel_div_ref" ).val(" ");
  $("#lab_mission_edit_travel_div_rt" ).val("false");
  $("#lab_mission_edit_travel_div_mean" ).val(getMeanOfTransportCode("Train"));
  $("#lab_mission_edit_travel_div_company" ).val(" ");
  $("#lab_mission_edit_travel_div_cost" ).val(0);
  $("#lab_mission_edit_travel_div_countryFrom" ).countrySelect("setCountry", "France");
  $("#lab_mission_edit_travel_div_countryTo" ).countrySelect("setCountry", "France");
  $("#lab_mission_edit_travel_div_carbon_footprint" ).val(" ");
  $("#lab_mission_edit_travel_div_loyalty_card_number" ).val(" ");
  $("#lab_mission_edit_travel_div_loyalty_card_expiry_date" ).val("");
  $("#lab_mission_edit_travel_div_nb_person" ).val("1");
  $("#lab_mission_edit_travel_div_travelId" ).val("");
}

function getTravel(id) {
  let fields = getEditTravelField();
  let travel = {};
  for (let i = 0 ; i < fields.length ; i++) {
    let fieldId = "#travel_" + fields[i] + "_" + id;
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
  console.log(travels);

  if ($("#lab_mission_token").length && $("#lab_mission_token").val() != 0) {
    console.log("[deleteTravelTr] " + id + " oui");
    data = {
      'action' : 'lab_travel_delete',
      'id' : $("#travel_travelId_" + id).attr("tv"),
      'mission_id' : mission_id
    }
    callAjax(data, "Travel deleted", null, null, null);
    $("#lab_mission_table_tr_"+id).remove();
  }
  else {
    console.log("[deleteTravelTr] " + id + " non");
    $("#lab_mission_table_tr_"+id).remove();
  }
  deleteTravelId(id);
}

///////////////////DESCRIPTION MISSION///////////////////////////////////

function getEditDescriptionField(){
  return ["type", "value", "descriptionId", "slug"];
}

function saveDescription(jsId, descriptionId){
  console.log("[saveDesc] --> NEW : " + jsId);
  let f = {};
  let fields = getEditDescriptionField();

  data = {
    'action' : 'lab_description_save',
    'missionId' : $("#lab_mission_id").val(),
  }

  //let typeDesc = $("#lab_mission_edit_description_div_type").val();
  let optionSelected = $("#lab_mission_edit_description_div_type").find("option:selected");
  let typeDesc = optionSelected.attr("slug");
  if (typeDesc == 'tdpdf') {
    f["type"] = typeDesc.val();
    f["value"] = $("#lab_mission_description_PDF_url").val();
    data["type"] = typeDesc.val();
    data["value"] = $("#lab_mission_description_PDF_url").val();
  }
  else {
    for (let i = 0; i < fields.length ; i++){
      let val = $("#lab_mission_edit_description_div_" + fields[i]).val();
      f[fields[i]] = val;
      data[fields[i]] = val;
      console.log("["+fields[i]+"] = " + val);
    }
  }
  f["descriptionId"] = descriptionId;
  data["descriptionId"] = descriptionId;

  //console.log("[descriptionExist(id)] = " + descriptionExist(id));
  if (descriptionExist(jsId)){
    console.log("[saveDescriptionModification] [description exist]");
    editDescriptionTd(jsId, f);
  }
  else {
    console.log("[saveDescriptionModification] [description NEW]");
    addDescription(jsId, f, null, null);
  }

  if($("#lab_mission_token").length && $("#lab_mission_token").val() != 0){
    callAjax(data, "Description updated", updateDescriptionIdFromDb, null, null);
  }
  
}

function updateDescriptionIdFromDb(data){
  $("#description_descriptionId_" + data.descriptionId).attr("tv", data.id);
}

function editDescriptionTd(id, fieldsVal) {
  console.log("[editDescriptionTd] " + id);
  let fields = getEditDescriptionField();
  for (let i = 0 ; i < fields.length ; i++) {
    let fieldId = "#description_" + fields[i] + "_" + id;
    let val = fieldsVal[fields[i]];
    console.log("[editDescriptionTd] " + fieldId + " = '" + val + "'");

    if (fields[i] == "descriptionId") {
      $(fieldId).val(val);
    } 
    else 
    {
      $(fieldId).html(val);
    }
    $(fieldId).attr("tv", val);
  }
}

function displayDescriptions(data){
  $.each(data, function (i, obj){
    let fields = {};
    fields["type"] = obj.description_type;
    fields["value"] = obj.description_value;
    fields["descriptionId"] = obj.id;
    addDescription(getNewDescriptionsId(), fields, obj.id, obj.mission_id);
  });
}

function addDescriptionId(id){
  descriptions.push(parseInt(id));  
}

function descriptionExist(jsId) {
  return descriptions.includes(parseInt(jsId));
}

function deleteDescriptionId(jsId){
  console.log("deleteDescriptionId " + jsId);
  console.log(descriptions.length);
  console.log(descriptions);
  console.log("inArray " + arrayIndexOf(descriptions, jsId));
  descriptions.splice(arrayIndexOf(descriptions, jsId), 1);
  console.log(descriptions);
  console.log("deleteDescriptionIdEnd " + jsId);
}

function getNewDescriptionsId() {
  let max = 0;
  for (let i = 0 ; i < descriptions.length ; i++) {
    if (descriptions[i] > max) {
      max = parseInt(descriptions[i]);
    }
  }
  return max + 1;
}

function emptyDescriptionDivFields(){
  $("#lab_mission_edit_description_div_type" ).val(getTypeOfDescriptionCode("None"));
  $("#lab_mission_edit_description_div_value" ).val(" ");
}

function getDescription(id){
  let fields = getEditDescriptionField();
  let description = {};
  for (let i = 0 ; i < fields.length ; i++){
    let fieldId = "#description_" + fields[i] + "_" + id;
    description[fields[i]] = $(fieldId).attr("tv");
  }
  return description;
}

function editDescriptionDiv(id, descriptionId){
  let fields = getEditDescriptionField();
  for (let i=0; i < fields.length; i++){
    let fieldId = "#description_" + fields[i] + "_" + id;
    if ($(fieldId).length > 0){
      let val = $(fieldId).html();
      if (fields[i] == "descriptionId") {
        val = $(fieldId).attr("tv");
      }
        console.log("[editDescriptionDiv] #lab_mission_edit_description_div_" + fields[i] + " = '" + val + "'");
        if ($("#description_type_"+ id).attr("tv") == '279'){
          $("#lab_mission_add_description_pdf").show();
          $("#lab_mission_add_description_comment").hide();
        }
        else if ($("#description_type_"+ id).attr("tv") == '280' || $("#description_type_"+ id).attr("tv") == '282'){
          $("#lab_mission_add_description_comment").show();
          $("#lab_mission_add_description_pdf").hide();
        }
        $("#lab_mission_edit_description_div_" + fields[i]).val(val);
    }
    else {
      console.log("[editDescriptionsDiv] fieldId (" + fieldId + ")  NO VALUE ");
    }
  }
    $("#lab_mission_edit_description_save_button").attr("jsId", id).attr("descriptionId",descriptionId);
    $("#lab_mission_edit_description_div").show();
}

function deleteDescriptionTr(jsId, mission_id){
  console.log("[deleteDescriptionTr] " + jsId);
  console.log ("VALUE = " + $("#description_value_" + jsId).attr("tv"));
  if ($("#lab_mission_token").length && $("#lab_mission_token").val() != 0){
    console.log("[deleteDescriptionTr] " + jsId + " oui");
    data = {
      'action' : 'lab_description_delete',
      'jsId' : $("#description_descriptionId_" + jsId).attr("tv"),
      'mission_id' : mission_id
    }
    callAjax(data, "Description deleted", null, null, null);
    $("#lab_mission_description_table_tr_" + jsId).remove();
  }
  else {
    console.log("[deleteDescriptionTr] " + jsId + " non");
    $("#lab_mission_description_table_tr_" + jsId).remove();
  }
  deleteDescriptionId(jsId);
}

$("#lab_mission_edit_description_div_type").change(function(){
  console.log("[lab_mission_edit_description_div_type] change : " + $("option:selected", this).attr("slug"));
  let selectedOption = $("option:selected", this);
  if (selectedOption.attr("slug") == 'tdpdf'){
  $("#lab_mission_add_description_pdf").show();
  $("#lab_mission_edit_description_div_value").val(null);
  $("#lab_mission_add_description_comment").hide();
  $("#lab_mission_edit_description_save_button").show();
  
  } else if (selectedOption.attr("slug") == 'td_com' || selectedOption.attr("slug") == 'tdu') {
  $("#lab_mission_add_description_comment").show();
  $("#lab_mission_add_description_pdf").hide();
  $("#lab_mission_description_PDF").val(null);
  $("#lab_mission_edit_description_save_button").show();

  } else {
  $("#lab_mission_add_description_comment").hide();
  $("#lab_mission_add_description_pdf").hide();
  $("#lab_mission_edit_description_div_value").val(null);
  $("#lab_mission_edit_description_save_button").hide();
  $("#lab_mission_description_PDF").val(null);
  $("#lab_mission_edit_description_save_button").hide();
}
});

$("#lab_mission_edit_description_save_button").click(function(event){
  event.preventDefault();
  console.log($("#lab_mission_edit_description_save_button").attr("descriptionId"));
  saveDescription($("#lab_mission_edit_description_save_button").attr("jsId"), $("#lab_mission_edit_description_save_button").attr("descriptionId"));
  $("#lab_mission_edit_description_div").hide();
});

function addDescription(id, fields, descriptionId ,mission_id){
  console.log("addDescription()");
  addDescriptionId(id);
  let tr = $("<tr/>").attr("id","lab_mission_description_table_tr_"+id);
  createSelectDescriptionTdToTr(tr, id, "type", fields, listTypeOfDescription());
  createDefaultDescriptionTdToTr(tr, id, "value", fields);
  let fileView = fields["value"];
  console.log("[fileView1] " + fileView);
  let tdView = $("<td/>").attr("class", "pointer").attr("jsId",id).attr("descriptionId", descriptionId).html('<a href='+ fileView +' target="_blank" style="color:#212529"><i class="fas fa-search" aria-hidden="true" descriptionId ="'+id+'"></i></a>');
  let tdEdit = $("<td/>").attr("class", "pointer").attr("jsId",id).attr("descriptionId",descriptionId).html('<i class="fa fa-pencil"  aria-hidden="true" descriptionId="'+id+'"></i>');
  let tdDel  = $("<td/>").attr("class", "pointer").attr({
    "jsId" : id,
    "descriptionId" : descriptionId,
    "missionId" : mission_id
  }).html('<i class="fa fa-trash-o" aria-hidden="true" descriptionId="'+id+'"></i>');

  tr.append(tdEdit);
  tr.append(tdDel);

  let optionSelected = $("#lab_mission_edit_description_div_type").find("option:selected");
  let typeDesc = optionSelected.attr("slug");

  //if (typeDesc == 'tdpdf' || fields["type"] == '279' || $("#lab_mission_edit_description_div_type").val() == '280' || fields["type"] == '280'){
    tr.append(tdView);
  //}

  createDescriptionHiddenField(tdEdit, id, "descriptionId", fields);

  tdView.click(function (e){
    console.log("[fileView] " + fileView);
    console.log($("#description_value_"+ id).attr("tv"));
    console.log($("#lab_mission_description_PDF_url").val());
  });

  tdEdit.click(function (e) {
    editDescriptionDiv($(this).attr("jsId"), $(this).attr("descriptionId"));
    $('#lab_mission_edit_description_div_type option[value="' + $("#description_type_"+ id).attr("tv")+ '"]').prop('selected', true);
  });

  tdDel.click(function (e) {
    deleteDescriptionTr($(this).attr("jsId"), $(this).attr("missionId"));
  });

  $("#lab_mission_description_table_tbody").append(tr);
}

function createDescriptionHiddenField(td, id, fieldName, fields) {
  let hi = $("<input/>").attr('type','hidden').attr("id", "description_" + fieldName + "_" + id).attr("tv", fields[fieldName]);
  hi.val(fields[fieldName]);
  td.append(hi);
}

function createDefaultDescriptionTdToTr(tr, id, fieldName, fields) {
  createDescriptionTdToTr(tr, id, fieldName, fields[fieldName], fields[fieldName]);
}

function addTravel(id, fields, travelId, mission_id) {
  console.log("[AddTravel] ");
  console.log(fields);
  addTravelId(id);
  let tr = $("<tr/>").attr("id","lab_mission_table_tr_"+id);
  createDefaultTravelTdToTr(tr, id, "dateGoTo", fields);
  createDefaultTravelTdToTr(tr, id, "timeGoTo", fields);
  createTravelCountryTdToTr(tr, id, "countryFrom", fields);
  createDefaultTravelTdToTr(tr, id, "cityFrom", fields);
  createDefaultTravelTdToTr(tr, id, "stationFrom", fields);
  createTravelCountryTdToTr(tr, id, "countryTo", fields);
  createDefaultTravelTdToTr(tr, id, "cityTo", fields);
  createDefaultTravelTdToTr(tr, id, "stationTo", fields);
  createSelectTravelTdToTr(tr, id, "mean", fields, listMeanOfTransport());
  //createDefaultTravelTdToTr(tr, id, "company", fields);
  createTravelCostTdToTr(tr, id, "cost", fields);
  createDefaultTravelTdToTr(tr, id, "ref", fields);
  createTravelBooleanField(tr, id, "rt", fields);
  createDefaultTravelTdToTr(tr, id, "dateReturn", fields);
  createDefaultTravelTdToTr(tr, id, "timeReturn", fields);
  createDefaultTravelTdToTr(tr, id, "loyalty_card_number", fields);
  createDefaultTravelTdToTr(tr, id, "loyalty_card_expiry_date", fields);
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
  createTravelHiddenField(tdEdit, id, "company", fields);

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

function createSelectTravelTdToTr(tr, id, fieldName, fields, displayMap) {
  console.log("[createSelectTravelTdToTr]");
  console.log(displayMap);
  console.log(fields[fieldName]);
  console.log(displayMap[fields[fieldName]]);
  createTravelTdToTr(tr, id, fieldName, displayMap[fields[fieldName]], fields[fieldName]);
}

function createSelectDescriptionTdToTr(tr, id, fieldName, fields, displayMap){
  console.log("[createSelectDescriptionTdToTr]");
  console.log(displayMap);
  console.log(fields[fieldName]);
  console.log(displayMap[fields[fieldName]]);
  createDescriptionTdToTr(tr, id, fieldName, displayMap[fields[fieldName]], fields[fieldName]);
}

function createDefaultTravelTdToTr(tr, id, fieldName, fields) {
  createTravelTdToTr(tr, id, fieldName, fields[fieldName], fields[fieldName]);
}

function createTravelTdToTr(tr, id, fieldName, displayVal, val) {
  createGenericTdToTr(tr, id, "travel", fieldName, displayVal, val);
}

function createDescriptionTdToTr(tr, id, fieldName, displayVal, val) {
  createGenericTdToTr(tr, id, "description", fieldName, displayVal, val);
}

function createGenericTdToTr(tr, id, prefix, fieldName, displayVal, val) {
  let td = $("<td/>").attr("id",prefix + "_" + fieldName + "_" + id).html(displayVal);
  if (val != null) {
    td.attr("tv", val);
  }
  //console.log("[createTravelTdToTr] fieldName : " + fieldName + " val : '" + val + "'(" + td.attr("tv") + ")");
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
  fields["stationFrom"] = "";
  fields["countryTo"]   = "fr";
  fields["cityTo"]      = "Paris";
  fields["stationTo"]   = "";
  fields["company"]     = "";
  fields["mean"]        = getMeanOfTransportCode("Train");
  fields["cost"]        = "0";
  fields["ref"]         = " ";
  fields["carbon_footprint"]         = "";
  fields["loyalty_card_number"]         = "";
  fields["loyalty_card_expiry_date"]         = "";
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

      data = {
        'action' : 'lab_descriptions_load',
        'id' : $("#lab_mission_id").val(),
      }
      callAjax(data, null, displayDescriptions, null, null);
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

    $("#addDescription").click(function(e){
      emptyDescriptionDivFields();
      editDescriptionDiv(getNewDescriptionsId(), "");
    })

    $("#addTravel").click(function(e) {
      emptyTravelDivFields();
      editTravelDiv(getNewTravelId());
    });

    $("#lab_mission_submit").click(function(e) {
      invitation_submit(function(data) {
        toast_success(data);
        missionReloadComments();
        return;
      });
    });

    $("#lab_mission_edit_travel_save_button").click(function(e) {
      $("#lab_mission_edit_travel_div").hide();
      saveTravelModification($("#lab_mission_edit_travel_save_button").attr("travelId"));
    });

    $(".lab_fe_modal_close").click(function(e) {
      $("#lab_mission_edit_travel_div").hide();
      $("#lab_mission_edit_description_div").hide();
    });

    if ($("#lab_mission option:selected" ).text() == "Invitation") {
      $("#inviteDiv").show();
    }

    $("#lab_mission").change(function (e) {
      console.log($("#lab_mission option:selected" ).text());
      hideShowInvitationDiv();
    });

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
        $("#lab_invitationComments #lab_invitation_newComment").slideDown();
        $("#lab_invitationComments").attr("wrapped","false");
      } else {
        $("#lab_invitationComments #lab_invitation_oldComments").slideUp();
        $("#lab_invitationComments #lab_invitation_newComment").slideUp();
        $("#lab_invitationComments").attr("wrapped","true");
      }
    });
    $("#lab_mission_guest_email").change(function(){
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
        missionReloadUserInfo(ui.item.user_id);
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

function missionReloadComments() {
  $token = $("#missionForm").attr("token");
  console.log("Token : " + $token);
  if($token != undefined && $token != "" && $token != "0")
  {
    data = {
      'action': 'lab_mission_load_comments_json',
      'token': $token
    };
    callAjax(data, null, displayComments, null, null);
  }
}

function displayComments(data) {
  jQuery("#lab_invitation_oldComments").empty();
  jQuery.each(data, function(i, obj) {
    let box = jQuery("<div/>").attr("class", "lab_comment_box");
    let who = jQuery("<p/>");
    let msg = jQuery("<p/>");
    if (obj.author_type == 0)
    {
      who.attr("class", "lab_comment_author auto");
      msg.attr("class", "lab_comment auto");
    }
    else
    {
      who.attr("class", "lab_comment_author");
      msg.attr("class", "lab_comment");
    }
    who.html(obj.author);
    box.append(who);
    let msgHour = jQuery("<i/>");
    msgHour.html(obj.timestamp);
    msg.append(msgHour);
    msg.append(jQuery("<br/>"));
    msg.append(obj.content);
    box.append(msg);
    jQuery("#lab_invitation_oldComments").append(box);
  });
  jQuery("#lab_invitation_oldComments").append();
  generateNewCommentHtml(jQuery("#lab_invitation_oldComments"),jQuery("#lab_mission_token").val());
}

function generateNewCommentHtml(htmlElm, token)
{
  let div = jQuery("<div/>").attr("token", token).attr("id", "lab_invitation_newComment");
  let title = jQuery("<h5/>");
  title.html("New comment");
  let form = jQuery("<form>").attr("id", "form_new_comment");
  //let str = "<label><i>Publish as</i> : <span id='lab_comment_name' user_id='0'";
  let lab = jQuery("<label>");
  lab.html("<i>Publish as</i> :");

  let spanInLabel = jQuery("<span>").attr("id", "lab_comment_name");
  
  if (jQuery("#lab_mission_edit_as_guest").length) {
    spanInLabel.attr("user_id", jQuery("#lab_mission_guest_email").attr('guest_id'));
    spanInLabel.html(jQuery("#lab_firstname").val() + " " + jQuery("#lab_lastname").val());
  }
  else {
    spanInLabel.attr("user_id", jQuery("#lab_hostname").attr("host_id"));
    spanInLabel.html(jQuery("#lab_hostname").val());
  }
  lab.append(spanInLabel);

  //let label = jQuery(str);
  //let ta    = jQuery("<textarea row=\"1\" cols=\"50\" id=\"lab_comment\" placeholder=\"Comment content...\"></textarea>");
  //let inp   = jQuery("<input id=\"button_add_comment\" type=\"button\" value=\"Send commend\">");
  let taComment = jQuery("<textarea>").attr("row", "1").attr("cols", "50").attr("id", "lab_comment").attr("placeholder","Comment content...");
  let buttonSendComment = jQuery("<input>").attr("type", "button").attr("id", "button_add_comment").attr("value","Send commend");
  form.append(lab);
  form.append(taComment);
  form.append(buttonSendComment);
  div.append(title);
  div.append(form);
  htmlElm.append(div);

  jQuery(buttonSendComment).click(function () {
    addComment();
  });
}

function missionReloadUserInfo(userId) {
  data = {
    'action': 'lab_mission_get_user_information',
    'userId': userId
  };
  callAjax(data, null, changeGroupAndFunding, null, null);
}

function changeGroupAndFunding(data) {
  //console.log(data);
  if (data["groups"]) {
    $("#lab_group_name option").each(function() {
      $(this).remove();
    });
    $.each(data["groups"], function (index, value){
      let opt = '<option ';
      if (value.favorite == 1) {
        opt += "selected ";
      }
      opt += 'value="'+value.id+'">'+value.name+'</option>'
      $("#lab_group_name").append(opt);
    });
  }
  if (data["contracts"]) {
    if (data["contracts"].length > 0)
    {
      let select = undefined;
      if ($("#lab_mission_user_funding").prev().is("select")) {
        $("#lab_mission_user_funding option").each(function() {
          $(this).remove();
        });
        select = $("#lab_mission_user_funding");
      }
      else {
        let parent = $("#lab_mission_user_funding").parent();
        $("#lab_mission_user_funding").remove();
        $("#lab_mission_group_funding").remove();
        select = $('<select/>');
        select.attr("id", "lab_mission_user_funding");
        parent.append(select);
      }
      let defaultOpt = $('<option value="0">Financements du groupe</option>');
      select.append(defaultOpt);
      $.each(data["contracts"], function (index, value){
        let opt = $('<option value="'+value.id+'">'+value.name+'</option>');
        select.append(opt);
      });
    }
    // user do not have their own contract
    else {
      //console.log("No contracts");
      let parent = $("#lab_mission_user_funding").parent();
      if ($("#lab_mission_user_funding").is("select")) {
        //console.log("lab_mission_user_funding est un select");
        $("#lab_mission_user_funding").remove();
        if (!$("#lab_mission_group_funding").length) {
          parent.append($('<span id="lab_mission_group_funding">Financements du groupe</span>'));
        }
        $("#lab_mission_group_funding").html("Financements du groupe");
        parent.append($('<input type="hidden" id="lab_mission_user_funding" value="0"></input>'));
      }
    }
  }
}

function getSumCostTravels() {
  var output = 0.0;
  $("#lab_mission_travels_table_tbody").children("tr").each(function () {
    output +=  parseFloat($(this).children('[id^=travel_cost_]').attr("tv"));
  })
  console.log("SUUUUUUUUU " + output);
  return  output;
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

    let descriptionsFields = [];
    for (let i = 0 ; i < descriptions.length ; i++) {
      descriptionsFields.push(getDescription(descriptions[i]));
    }

    console.log("Travels to save:");
    console.log(travelsFields);
    console.log("Descriptions to save:");
    console.log(descriptionsFields);
    fields = {
      'guest_firstName': $("#lab_firstname").val(),
      'guest_lastName': $("#lab_lastname").val(),
      'guest_email': $("#lab_mission_guest_email").val(),
      'guest_phone': $("#lab_phone").attr('phoneval'),
      'guest_language': $("#guest_language").countrySelect("getSelectedCountryData")['iso2'],
      'guest_residence_country': $("#residence_country").countrySelect("getSelectedCountryData")['iso2'],
      'guest_residence_city': $("#residence_city").val(),
      'host_id': $("#lab_hostname").attr('host_id'),      
      'mission_objective': $("#lab_mission").val()=="other" ? $("#lab_mission_other").val().replace(regex,"”").replace(/\'/g,"’") : $("#lab_mission").val(),
      'descriptions' : descriptionsFields,
      'title' : $("#lab_title").val(),
      'needs_hostel' : $("#lab_hostel").prop('checked'),
      'hostel_cost' : $("#lab_mission_hostel_cost").val(),
      'hostel_night' : $("#lab_mission_hostel_night").val(),
      'funding': $("#lab_mission_user_funding").val(),
      'charges': charges,
      'travels': travelsFields,
      'host_group_id': $("#lab_group_name").val(),
      'no_charge': $("#lab_no_charge_mission").prop('checked'),
    }
    console.log($("#lab_mission_user_funding").val());
    if ($("#lab_mission_guest_email").length && $("#lab_mission_guest_email").attr('guest_id').length) {
      fields['guest_id'] = $("#lab_mission_guest_email").attr('guest_id');
    }
    if ($("#lab_mission_fund_origin").length) {
      fields['funding_source'] = $("#lab_mission_fund_origin").val();
      //fields['host_group_id'] = $("#lab_group_name").val();
      //alert('$("#lab_group_name").val() : ' + $("#lab_group_name").val());
    }
    if ($("#missionForm").attr("hostForm")==1) {//La version invitant est affichée 
      fields['estimated_cost'] = parseFloat($("#lab_mission_hostel_cost").val()) + getSumCostTravels();
      fields['maximum_cost'] = $("#lab_maximum_cost").val();
    }
    //On crée une nouvelle invitation
    if ($("#missionForm").attr("newForm")==1) 
    {
      fields['comment'] = $("#lab_form_comment").val().replace(regex,"”").replace(/\'/g,"’");
      data = {
        'action': 'lab_invitations_new',
        'fields': fields
      };
      //alert("toto");
      jQuery.post(LAB.ajaxurl, data, function(response) {
        console.log(data);
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
      //callAjax(data, null, callback, null, null);
      callAjax(data, null, null, null, null);
    }
}
jQuery("#button_add_comment").click(function () {
  addComment();
});

function addComment()
{
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
        //console.log(response.data);
        //$("#lab_invitation_oldComments").html(response.data);
        //$("#lab_comment").val('');
        //callback();
        missionReloadComments();
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
/*
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
//*/

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

function missionGLValidateMission()
{
  data = {
    'action': 'lab_mission_validate',
    'mission_id': jQuery("#lab_mission_id").val()
  };
  callAjax(data, null, loadAdminPanel, null, null);
}

function missionGLRefuseMission()
{

  data = {
    'action': 'lab_mission_refuse',
    'mission_id': jQuery("#lab_mission_id").val()
  };
  callAjax(data, null, loadAdminPanel, null, null);
}
