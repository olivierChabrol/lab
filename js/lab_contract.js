jQuery(function($){
    if ($("#lab_admin_contract_list_table").length) {
        loadAllContracts();
    }
    if ($("#lab_admin_contract_funder_list_table").length) {
        loadAllContractsFunder();
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

    $("#lab_admin_contract_funder_save").click(function() {
        let data = {
            'action':'lab_admin_contract_funder_save_data',
        };
        callAjax(data, null, null, null, null);
    });

    $("#lab_admin_contract_funder_create").click(function() {
        /*
        let data = {
            'action':'lab_admin_contract_funder_save',
            'label':$("#lab_admin_contract_name").val(),
            'type':0,
            'value':0,
            'parent':-1,
        };
        $("#lab_admin_contract_name").val("");
        callAjax(data, null, displayContractsFunder, null, null);
        //*/
        save_contract_funder_category($("#lab_admin_contract_name").val(), 0, 0, -1);
    });

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

    $("#lab_contract_funder_param").on("change", function() {
        let data = {
            'action':'param_load_by_type_value',
            'id':$("#lab_contract_funder_param").val(),
        }
        callAjax(data, null, displayParams, null, null);
    });

    $("#lab_contract_funder_delete_dialog_delete_exit").click(function() {
        console.log("click on #lab_contract_funder_delete_dialog_delete_exit");
        $('#lab_contract_funder_delete_dialog').modal('hide');
    });

    $("#lab_contract_funder_delete_dialog_delete_button").click(function() {
        console.log("click on #lab_contract_funder_delete_dialog_delete_button");
        $('#lab_contract_funder_delete_dialog').modal('hide');
        let data = {
            'action':'lab_admin_contract_funder_delete',
            'id':$("#lab_contract_funder_delete_dialog_id").val(),
        }
        callAjax(data, null, loadAllContractsFunder, null, null);
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

    function save_contract_funder_category(label, type, value, parent) {
        let data = {
            'action':'lab_admin_contract_funder_save',
            'label':label,
            'type':type,
            'value':value,
            'parent':parent,
        };
        $("#lab_admin_contract_name").val("");
        callAjax(data, null, displayContractsFunder, null, null);
    }

    function clearContractFunderParams() {
        $("#lab_contract_funder_params").empty();
    }

    function displayParams(data) {
        clearContractFunderParams();

        $.each(data, function(i, obj) {
            let a = $('<a />');
            a.html(obj.value).attr("paramId", obj.id);
            $("#lab_contract_funder_params").append(a);
            let br = $("<br/>");
            $("#lab_contract_funder_params").append(br);
        });
    }

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

    function loadAllContractsFunder() {
        let data = {
            'action':'lab_admin_contract_funder_list'
        }
        callAjax(data, null, displayContractsFunder, null, null);
    }

    function displayContractsFunder(data) {
        console.log("displayContractsFunder");
        clearContractsFunderTable();

        $.each(data, function(i, obj) {
            let tr = $('<tr />');
            let td = $('<td />').attr("id", "lab_contract_funder_name" + obj.id).html(obj.label).attr("obj_id", obj.id);
            
            let aEdit   = $('<a />').attr("class", "lab-page-title-action lab_contract_funder_add").attr("contractId", obj.id).html("Add");
            let aDelete = $('<a />').attr("class", "lab-page-title-action lab_contract_funder_delete").attr("contractId", obj.id).html("X");
            td.append(aDelete);
            let tdEdit = $('<td />').append(aEdit);
            let tdChild = $('<td />');
            $.each(obj.child, function(i, obj1)
             {
                let aDeleteInner = $('<a />').attr("class", "lab-page-title-action lab_contract_funder_delete").attr("contractId", obj1.id).html(" X");
                let childSpan = $("<span />");
                childSpan.attr("id", "lab_contract_funder_name" + obj1.id).append(obj1.label).append(aDeleteInner);
                tdChild.append(childSpan);
                tdChild.append($("<br/>"));
                aDeleteInner.click(function() {
                    console.log("click on " + aDeleteInner.attr("contractId"));
                    console.log("#lab_contract_funder_name" + obj1.id);
                    console.log($("#lab_contract_funder_name" + obj1.id).html());
                    $("#lab_contract_funder_delete_dialog_id").val(obj1.id);
                    $("#lab_contract_funder_delete_dialog_name").html($("#lab_contract_funder_name" + obj1.id).html());
                    $("#lab_contract_funder_delete_dialog").modal();
                });
            });
            $(tr).append(td);
            $(tr).append(tdChild);
            $(tr).append(tdEdit);
            aEdit.click(function() {
                console.log("click on " + aEdit.attr("contractId"));
                displayModalContractsFunderSubList(aEdit.attr("contractId"));
            });

            aDelete.click(function() {
                console.log("click on " + aDelete.attr("contractId"));
                console.log("#lab_contract_funder_name" + obj.id);
                console.log($("#lab_contract_funder_name" + obj.id).html());
                $("#lab_contract_funder_delete_dialog_id").val(obj.id);
                $("#lab_contract_funder_delete_dialog_name").html($("#lab_contract_funder_name" + obj.id).html());
                $("#lab_contract_funder_delete_dialog").modal();
            });
            $("#lab_admin_contract_funder_list_table").append(tr);
        });
    }

    function displayModalContractsFunderSubList(parentId) {
        console.log("[displayContractsFunderSubList] " + parentId);
        $("#lab_contract_funder_dialog_add").modal();
        $("#lab_contract_funder_dialog_parent").val(parentId);

        let data = {
            'action':'lab_admin_contract_funder_list_sub_funder',
            'id':$("#lab_contract_funder_dialog_parent").val(),
        }
        callAjax(data, null, displayContractsFunderSubList, null, null);
    }

    function displayContractsFunderSubList(data) {
        clearContractSubList();
        $.each(data, function(i, obj) {
            let a = $('<a />').html(obj.label).attr("objId", obj.id).attr("parent", obj.parent).attr("type", obj.type).attr("parent", obj.parent).attr("rel", "modal:close");
            let br = $('<br />');
            //lab_contract_funder_dialog_add_content.append(a).append(br);
            $("#lab_contract_funder_dialog_add_content").append(a).append(br);
            $(a).click(function (){
                //addContractFunderSubCategory($(this).attr("objId"), $(this).attr("parent"));
                save_contract_funder_category(a.html(), a.attr("type"), a.attr("objId"), a.attr("parent"));
              });
        });
    }

    function addContractFunderSubCategory(id, parent) {
        let data = {
            'action':'lab_admin_contract_funder_add_sub_category',
            'id':id,
            'parent':parent,
        }
        callAjax(data, null, displayContractsFunderSubList, null, null);
    }

    function clearContractSubList() {
        $("#lab_contract_funder_dialog_add_content").empty();
    }

    function clearContractsFunderTable() {
        $("#lab_admin_contract_funder_list_table").empty();
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

    function clearContractFunderFields() {
        $("#lab_admin_contract_name").val("");
        $("#lab_admin_contract_funder_list_table").empty();
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