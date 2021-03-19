/* globals global 25 03 2020 */
if(typeof __ === 'undefined') {
  const { __, _x, _n, sprintf } = wp.i18n;
}

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
    //resetNotifsNumber(data.id);
    callAjax(data, null, displayMission, null, null);

  }

  function displayMission(data) {
    //console.log("[displayMission]");
    //console.log(data);
    //var test1 = __("Plane", "lab");
    //alert(test1);
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

    $("#lab_admin_mission_list_table_tbody").children("tr").each(function () 
    {
      string = $(this).children("td:nth-child(2)").html();
      if(string.startsWith("Valid")) {
        $(this).css("background-color", "palegreen");
      } 
      else if (string.startsWith("Refus")) {
        $(this).css("background-color", "salmon");
      } 
      else if (string.startsWith("Waiting") || string.startsWith("En attente")) {
        $(this).css("background-color", "khaki");
      }
      else if (string.startsWith("New") || string.startsWith("Nouveau")) {
        $(this).css("background-color", "lightcyan");
      }
      else if (string.startsWith("Cancel") || string.startsWith("Annulée")) {
        $(this).css("background-color", "lightgrey");
        $(this).children("td:nth-child(9)").children("a:nth-child(1)").hide();
      }
      else if (string.startsWith("Compl")) {
        $(this).css("background-color", "aquamarine");
        $(this).children("td:nth-child(9)").children("a:nth-child(1)").hide();
      }
    })
  }

  function createTrMissionTableLine(mission, data) {
    let tr = $('<tr />');
    //console.log(mission);
    tr.append(createTd(mission.id));
    tr.append(createTdParam(mission.status, data));
    tr.append(createTd(mission.creation_time));
    tr.append(createTdUser(mission.host_id, data));
    tr.append(createTd(mission.site));
    tr.append(createTdGroup(mission.host_group_id, data));
    tr.append(createTdUser(mission.manager_id, data));
    tr.append(createTdParam(mission.mission_objective, data));
    tr.append(createEditButton(mission.id, mission.token, data));
    return tr;
  }

  function createEditButton(id, token, data) {
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
    let notifNumber = data.notifs[id][0].notifs_number;
    let notif;
    if(notifNumber != 0) {
      var span1 = $('<span />').attr("class", "lab-update-plugins count-" + notifNumber);
      var span2 = $('<span />').attr("class", "plugin-count").html(notifNumber);
      notif = span1.append(span2);
    }
    //console.log("->"+url);
    let userId = $("#lab_mission_user_id").val();
    //let aTic = $('<a />').attr("class", "lab-page-title-action lab_mission_tic").attr("userId", userId).attr("missionId", id).html("tic");
    let aEdit = $('<a />').attr("class", "lab-page-title-action lab_mission_edit").attr("href",url).attr("missionId", id).html("edit");
    let aDel = $('<a />').attr("class", "lab-page-title-action lab_budget_info_delete").attr("missionId", id).attr("id", "lab-delete-mission-button").html("X");

    $(aDel).click(function (){
      displayModalDeleteMission($(this).attr("missionId"));
    });
    /*$(aTic).click(function (){
      missionTakeInCharge($(this).attr("missionId"), $(this).attr("userId"));
    });*/
    $(notif).click(function (){
      deleteNotifs(id);
    });

    return $('<td />').attr("class", "lab_keyring_icon").append(notif).append(aEdit).append(aDel);   
  }

  function deleteNotifs(missionId) {
    data = {
      'action':"lab_mission_delete_notif",
      'mission_id': missionId
    };
    callAjax(data, null, applyFilter, null, null);

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
    let news = __("New", "lab");
    let cancelled = __("Cancelled", "lab");
    let validated = __("Validated by group leader", "lab");
    let refused = __("Refused by group leader", "lab");
    let waiting = __("Waiting group manager", "lab");
    let completed = __("Completed", "lab");
    let f = __(searchInParams(paramId, data),"lab");
    if (f == undefined) {
      f = "Not defined";
    }
    if (f == cancelled) {
      return createTd(cancelled);
    }
    else if (f == news) {
      return createTd(news);
    }
    else if (f == validated) {
      return createTd(validated);
    }
    else if (f == refused) {
      return createTd(refused);
    }
    else if (f == waiting) {
      return createTd(waiting);
    }
    else if (f == completed) {
      return createTd(completed);
    }
    return createTd(f);
  }
});