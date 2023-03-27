jQuery(function($){
    if ($("#lab_admin_contract_list_table").length) {
        loadAllContracts();
    }

    if($("#lab_contract_delete_dialog_contract_id").length && $("#lab_contract_delete_dialog_contract_id").val() != "") {
        loadContractById($("#lab_contract_delete_dialog_contract_id").val());
    }
    $("#lab_admin_contract_name").autocomplete({
        minLength: 3,
        source: function(term, suggest){
          try { searchRequest.abort(); } catch(e){}
          searchRequest = $.post(LAB.ajaxurl, { action: 'lab_admin_contract_search',search: term, },
          function(res) {
            suggest(res.data);
          });
        },
        select: function( event, ui ) {
          event.preventDefault();
          displayOneContact(ui.item);
        }
    });

    $("#lab_admin_contract_holder").autocomplete({
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
          var userslug = ui.item.userslug;
          let userId =  ui.item.user_id;
          event.preventDefault();
          $("#lab_admin_contract_holder_id").val(userId);
          //loadUserInfo();
          addUserTocontract(firstname, lastname, userId, "holder");
        }
      }
    );
    $("#lab_admin_contract_manager").autocomplete({
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
          var userslug = ui.item.userslug;
          let userId =  ui.item.user_id;
          event.preventDefault();
          $("#lab_admin_contract_manager_id").val(userId);
          addUserTocontract(firstname, lastname, userId, "manager");
        }
      }
    );
    $("#lab_admin_contract_create").click(function() {
        if ($("#lab_admin_contract_name").val() != "")
        {
            let data = {
                'action':'lab_admin_contract_save',
                'id':$("#lab_admin_contract_id").val(),
                'name':$("#lab_admin_contract_name").val(),
                'contract_type':$("#lab_admin_contract_type").val(),
                'contract_tutelage':$("#lab_admin_contract_tutelage").val(),
                'start':$("#lab_admin_contract_start").val(),
                'end':$("#lab_admin_contract_end").val(),
            };
            let holders  = [];
            let managers = [];
            $(".lab_admin_contract_delete").each(function() {
                let parent = $(this).parent();
                if (parent.attr("userType") == "holder") {
                    holders.push(parent.attr("userId"));
                } else if (parent.attr("userType") == "manager") {
                    managers.push(parent.attr("userId"));
                }
            });
            data["holders"]  = holders;
            data["managers"] = managers;
            console.log(data);
            callAjax(data, null, clearNewFields, null, null);
        }
        else {
            toast_error("Contract name mandatory");
        }
    });

    $("#lab_contract_delete_confirm").click(function() {
        console.log($("#lab_contract_delete_dialog_contract_id").val());
        deleteContract($("#lab_contract_delete_dialog_contract_id").val());
    });

    $("#lab_admin_contract_delete").click(function () {
        deleteContract($("#lab_admin_contract_id").val());
    });
    $("#lab_admin_contract_create_table").click(function () {
        
        let data = {
            'action':'lab_admin_contract_create_table'
        }
        callAjax(data, null, reloadPageContract, null, null);
    });

    function loadContractById(contractId) {
        let data = {
            'action':'lab_admin_contract_get',
            'id' : contractId,
        }
        callAjax(data, null, displayOneContact, null, null);
    }

    function displayOneContact(data) {
        $("#lab_admin_contract_id").val(data.id);
          
        $("#lab_contract_delete_dialog_contract_id").val($("#lab_admin_contract_id").val());
        $("#lab_admin_contract_name").val(data.label);
        $("#lab_admin_contract_type").val(data.contract_type);
        $("#lab_admin_contract_tutelage").val(data.contract_tutelage);
        $("#lab_admin_contract_start").val(data.start);
        $("#lab_admin_contract_end").val(data.end);
        loadContractUsers(data.id);
        //displayDeleteButton();
        $("#lab_admin_contract_delete").prop('disabled', false);
    }

    function reloadPageContract(data) {
        location.reload();
      }

    function loadAllContracts() {
        let data = {
            'action':'lab_admin_contract_load'
        }
        callAjax(data, null, displayContracts, null, null);
    }

    function displayContracts(data) {
        $("#lab_admin_contract_list_table_tbody").empty();
        $.each(data, function(i, obj) {
            let tr = $('<tr />');
            let tdId = $('<td />').html(obj.id);
            let tdContractName = $('<td />').html(obj.name);
            let tdContractType = $('<td />').html(obj.type);
            let tdContractTutelage = $('<td />').html(obj.tutelage);
            tr.append(tdId);
            tr.append(tdContractName);
            tr.append(tdContractType);
            tr.append(tdContractTutelage);
            let holdersStr = "";
            $.each(obj.holders, function(i, usr) {
                holdersStr += usr.first_name + " " + usr.last_name+", ";
            });
            holdersStr = holdersStr.substr(0, holdersStr.length - 2);
            let tdContractHolders = $('<td />').html(holdersStr);
            tr.append(tdContractHolders);
            let managersStr = "";
            $.each(obj.managers, function(i, usr) {
                managersStr += usr.first_name + " " + usr.last_name+", ";
            });
            managersStr = managersStr.substr(0, managersStr.length - 2);
            let tdContractManagers = $('<td />').html(managersStr);
            tr.append(tdContractManagers);

            $("#lab_admin_contract_list_table_tbody").append(tr);
            tr.append(createEditContractButton(obj.id, obj));
        });
    }


  function createEditContractButton(id, data) {   
    let url = window.location.href;
    console.log(url);
    if (url.indexOf("&") != -1) 
    {
      url = (""+url).substr(0, url.indexOf("&"));
    }
    url  += "&tab=entry&id="+id;

    //console.log("->"+url);
    let userId = $("#lab_mission_user_id").val();
    let aEdit = $('<a />').attr("class", "lab-page-title-action lab_mission_edit").attr("href",url).attr("contractId", id).html("edit");
    let aDel = $('<a />').attr("class", "lab-page-title-action lab_budget_info_delete").attr("contractId", id).attr("id", "lab-delete-mission-button").html("X");

    $(aDel).click(function (){
      displayModalDeleteContract($(this).attr("contractId"));
    });

    return $('<td />').attr("class", "lab_keyring_icon").append(aEdit).append(aDel);   
  }

  function displayModalDeleteContract(missionId) {
      console.log("[displayModalDeleteContract] missionId : " + missionId)
    $("#lab_contract_delete_dialog").modal();
    $("#lab_contract_delete_dialog_contract_id").val(missionId);
  }

  function deleteContract(contractId = null) {
    data = {
      'action':"lab_admin_contract_delete",
      'id':contractId,
    };
    callAjax(data, null, loadAllContracts, null, null);
  }

    function loadContractUsers(contractId) {
        let data = {
            'action':'lab_admin_contract_users_load',
            'id':contractId,
        }
        callAjax(data, null, displayContractUsers, null, null);
    }

    function displayContractUsers(data) {
        clearContractUsersFields();
        $.each(data, function(i, obj) {
            if (obj.user_type == 1) {
                addUserTocontract(obj.first_name, obj.last_name, obj.user_id, "manager");
            }
            else if (obj.user_type == 2) {
                addUserTocontract(obj.first_name, obj.last_name, obj.user_id, "holder");
            }
        });
    }

    function clearNewFields() {
        $("#lab_admin_contract_id").val("");
        $("#lab_admin_contract_name").val("");
        $("#lab_admin_contract_start").val("");
        $("#lab_admin_contract_end").val("");
        clearContractUsersFields();
        $("#lab_admin_contract_delete").prop('disabled', true);
    }

    function clearContractUsersFields() {
        $(".lab_admin_contract_delete").each(function() {
            $(this).parent().remove();
        });
    }

    function addUserTocontract(firstName, lastName, userId, userType) {
        //console.log("[addUserTocontract] " + firstName + " / " + lastName + " / " + userId + " / " + userType);
        addUserNameTocontract(firstName+" "+lastName, userId, userType);
    }
    function addUserNameTocontract(userName, userId, userType) {
        let span = $('<span />').attr('class', 'badge badge-secondary user-role-badge').attr('userId', userId).attr("userType",userType).html(userName);
        let innerSpan = $('<span />').attr('class', 'lab_admin_contract_delete').attr('userId', userId);
        let innerI = $('<i />').attr('class', 'fas fa-trash').attr('userId', userId);
        innerSpan.append(innerI);
        span.append(innerSpan);
        $("#lab_admin_contract_"+userType+"s").append(span);
        $("#lab_admin_contract_"+userType+"_id").val("");
        $("#lab_admin_contract_"+userType).val("");
        //console.log("#lab_admin_contract_"+userType + " : " + $("#lab_admin_contract_"+userType).val());
        $(".lab_admin_contract_delete").click(function (){
          if ($("#lab_admin_contract_id").val() != "") {
              data = {
                  'action':'lab_admin_contract_delete_'+userType,
                  'userId':$(this).attr('userId'),
                  'contractId':$("#lab_admin_contract_id").val(),
              };
              callAjax(data,userType + " remove ",loadGroupManagers,'Failed to delete '+userType+' from this contract',null);
          }
          else {
              $(this).parent().remove();
          }});
    }
});