/* globals global 25 03 2020 */
//const { __, _x, _n, sprintf } = wp.i18n;

jQuery(function($){

  $("#lab_mission_filter_year").change(function () {
    applyFilter();
  });
  $("#lab_mission_filter_state").change(function () {
    applyFilter();
  });
  $("#lab_mission_filter_budget_manager").change(function () {
    applyFilter();
  });
  $("#lab_mission_filter_site").change(function () {
    applyFilter();
  });

  if ($("#lab_admin_mission_list_table").length) {
    loadMissions();
  }



  $("#lab_mission_delete_confirm").click(function() {
      //endLoan($(this).attr('loan_id'),$("#lab_keyring_loanform_key_id").text(),defaultTodayDate($("#lab_keyring_loanform_end_date").val()));
      deleteMission($("#lab_mission_delete_dialog_mission_id").val());
  });

  function applyFilter() {
    let action        = "lab_mission_load";
    let filterPattern = "lab_mission_filter_";
    let filterFields  = ["year","state", "budget_manager", "site"];
    let callBackFct   = displayMission;

    data = {
      'action': action,
    };
    for (let i = 0; i < filterFields.length ; i++) {
      let filter = filterFields[i];
      if ($("#" + filterPattern + filter).val() != "") {
        data["filters"] = {};
        data["filters"][filter] = $("#" + filterPattern + filter).val();
      }
    }
    console.log(data);
    callAjax(data, null, callBackFct, null, null);
  }

  function loadMissions() {

    $leaderGroupIds = null;
    if ($("#lab_mission_group_leader").length)
    {
      console.log(decodeURIComponent($("#lab_mission_group_leader").val()));
      $leaderGroupIds = JSON.parse(decodeURIComponent($("#lab_mission_group_leader").val()));
    }

    data = {
      'action':"lab_mission_load",
      'groupIds': $leaderGroupIds,
    };
    console.log(data);
    callAjax(data, null, displayMission, null, null);

  }

  function displayMission(data) {
    //console.log("[displayMission]");
    //console.log(data);

    $("#lab_mission_filter_year").empty();
    $("#lab_mission_filter_year").append(new Option(__("Year",'lab'),""));
    $.each(data.years, function(i, obj) {
      $("#lab_mission_filter_year").append(new Option(obj, obj));
    });

    if (data.filters["year"])
    {
        $("#lab_mission_filter_year").val(data.filters["year"]);
    }


    $("#lab_admin_mission_list_table_tbody").empty();
    $.each(data.results, function(i, obj) {
      $("#lab_admin_mission_list_table_tbody").append(createTrMissionTableLine(obj, data));
    });
  }

  function createTrMissionTableLine(mission, data) {
    let tr = $('<tr />');
    //console.log(mission);
    tr.append(createTd(mission.id));
    tr.append(createTd(mission.creation_time));
    tr.append(createTdUser(mission.host_id, data));
    tr.append(createTd(mission.site));
    tr.append(createTd(mission.group));
    tr.append(createTdUser(mission.manager_id, data));
    tr.append(createTdParam(mission.mission_objective, data));
    tr.append(createEditButton(mission.id, mission.token));
    return tr;
  }

  function createEditButton(id, token) {
    /*
    let url = window.location.href;
    url = window.location.origin
    url += "/mission/"+token+"/";
    //*/
    let url = window.location.href;
    console.log(url);
    if (url.indexOf("&") != -1) 
    {
      url = (""+url).substr(0, url.indexOf("&"));
    }
    url  += "&tab=entry&token="+token;
    
    //console.log("->"+url);
    let aEdit = $('<a />').attr("class", "page-title-action lab_keyring_key_edit").attr("href",url).attr("missionId", id).html("edit");
    //let aEdit = $('<a />').attr("class", "page-title-action lab_keyring_key_edit").attr("missionId", id).html("edit");
    let aDel = $('<a />').attr("class", "page-title-action lab_budget_info_delete").attr("missionId", id).html("X");

    $(aDel).click(function (){
      displayModalDeleteMission($(this).attr("missionId"));
    });

    return $('<td />').attr("class", "lab_keyring_icon").append(aEdit).append(aDel);   
  }

  function displayModalDeleteMission(missionId) {
    $("#lab_mission_delete_dialog").modal();
    $("#lab_mission_delete_dialog_mission_id").val(missionId);
  }

  function deleteMission(missionId) {
    data = {
      'action':"lab_mission_delete",
      'id':missionId,
    };
    callAjax(data, null, loadMissions, null, null);
  }

  function searchInParams(paramId, data) {
    let f = undefined;
    if (data.params[paramId] != undefined)
    {
      let param = data.params[paramId];
      f = param.value;
    }
    else {
      let param = data.contracts[paramId];
      if (param) {
        f = param.value;
      }
    }
    return f;
  }

  function createTdParam(paramId, data) {
    let f = searchInParams(paramId, data);
    if (f == undefined) {
      f = "Not defined";
    }
    return createTd(f);
  }
});