jQuery(function($){

  if ($("#lab_admin_budget_info_list_table").length) {
    loadAllBudgetInfo();
  }
  $( document ).ready(function() {
    if ($("#lab_budget_info_id").length && $("#lab_budget_info_id").val() != "") {
      loadBudgetInfo();
    }
  });

  $("#lab_budget_info_user").autocomplete({
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
        $("#lab_budget_info_user_id").val(userId);
        loadUserInfo();
      }
    }
  );

  $("#lab_budget_info_fund_origin").change(function() {
    data = {
      'action':"lab_admin_contract_get_managers",
      'id': $(this).val(),
    };
    callAjax(data, null, selectManager, null, null);
  });

  $("#lab_budget_info_create_table").click(function () {
    data = {
      'action':"lab_budget_info_create_tables"
    };
    callAjax(data, null, reloadPage, null, null);
  });

  $("#lab_budget_info_entry_create").click(function() {
    if (checkNewOrder())
    {
      saveNewOrder();
    }
  });

  function loadAllBudgetInfo() {
    data = {
      'action':"lab_budget_info_load",
    };
    callAjax(data, null, displayBudget, null, null);

  }

  function loadBudgetInfo()
  {
    
    data = {
      'action':"lab_budget_info_load",
      'id':$("#lab_budget_info_id").val(),
    };
    callAjax(data, null, displayEditBudget, null, null);
  }

  function displayEditBudget(data) {
    let fields = getFields();
    let params=Object();
    for (const element of fields) {
      $("#lab_budget_info_"+element).val(data.results[element]);
      //console.log("set #lab_budget_info_"+element+" : " + data.results[element]);
    }
    let u = data.users[data.results["user_id"]];
    $("#lab_budget_info_user").val(u.first_name+" "+u.last_name);
    
  }

  function displayBudget(data)
  {
    $.each(data.years, function(i, obj) {
      //let option = $('<options />').attr("value",obj).html(obj);
      $("#lab_budget_info_filter_year").append(new Option(obj, obj));
    });
    $.each(data.results, function(i, obj) {
        let tr = $('<tr />');           
        tr.append(createTd(obj.id));
        tr.append(createTd(obj.request_date));
        tr.append(createTd(obj.order_number));
        tr.append(createTd(obj.title));
        tr.append(displayTdParam(obj.site_id, data));
        tr.append(displayTdUser(obj.user_id, obj, data));
        tr.append(displayTdUser(obj.info_manager_id, obj, data));
        tr.append(displayTdUser(obj.budget_manager_id, obj, data));
        console.log(obj.budget_manager_id);
        tr.append(displayTdParam(obj.fund_origin, data));
        tr.append(createTd(formatMoney(obj.amount)));
        tr.append(createTd(obj.order_date));
        tr.append(createTd(obj.delivery_date));
        tr.append(createTd(obj.payment_date));
        tr.append(createEditButton(obj.id));
        
        $("#lab_admin_budget_info_list_table_tbody").append(tr);
    });
  }

  function createEditButton(id) {
    let url = window.location.href;
    url = url.substr(0, url.lastIndexOf("tab=")) + "tab=entry&id="+id;
    let aEdit = $('<a />').attr("class", "page-title-action lab_keyring_key_edit").attr("href",url).attr("budgetId", id).html("edit");
    let aDel = $('<a />').attr("class", "page-title-action lab_budget_info_delete").attr("budgetId", id).html("X");

    $(aDel).click(function (){
      deleteBudget($(this).attr("budgetId"));
    });

    return $('<td />').attr("class", "lab_keyring_icon").append(aEdit).append(aDel);
  }

  function deleteBudget(id) {
    data = {
      'action':"lab_budget_info_delete",
      'id': id,
    };
    callAjax(data, null, goToHistoric, null, null);
  }

  function createTd(str)
  {
    return $('<td />').html(str);
  }

  function displayTdParam(paramId, data) {
    let f = "("+ paramId + ") ";
    if (data.params[paramId] != undefined)
    {
      let param = data.params[paramId];
      f = param.value;
    }
    else {
      let param = data.contracts[paramId];
      f = param.value;

    }
    return createTd(f);
  }

  function displayTdUser(userId, obj, data) {
    let f = "";
    if (userId != 0 && data.users[userId] != undefined)
    {
      let user = data.users[userId];
      f = user.first_name+" "+user.last_name;
    }
    return createTd(f);
  }

  function formatMoney(value) {
    return new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(value);
  }

  function selectManager(data) {
    $("#lab_budget_info_budget_manager_id").val(data[0]["user_id"]);
  }

  function getFields() {
    return ['expenditure_type', 'title', 'request_date', 'site_id', 'user_id', 'budget_manager_id', 'fund_origin', 'amount', 'order_number', 'order_date', 'delivery_date', 'payment_date'];
  }

  function saveNewOrder() {
    let fields = getFields();
    let params=Object();
    for (const element of fields) {
      //console.log(element + " : "+ $("#lab_budget_info_"+element).val());
      params[element] = $("#lab_budget_info_"+element).val();
    }
    if ($("#lab_budget_info_id").length && $("#lab_budget_info_id").val() != "") {
      params["id"] = $("#lab_budget_info_id").val();
    }

    data = {
      'action':"lab_budget_info_save_order",
      'params': params,
    };
    callAjax(data, "Order Save", goToHistoric, null, null);
  }

  function goToHistoric(data) {
    let url = window.location.href;
    url = url.substr(0, url.lastIndexOf("tab=")) + "tab=historic";
    location.href=url;

  }

  function checkNewOrder() {
    let isOk = true;
    if ($("#lab_budget_info_expenditure_type").val() == "") {
      toast_error("Select new order type");
      isOk = false;
    }
    return isOk;
  }

    function reloadPage() {
      location.reload();
    }

    function loadUserInfo() {
        data = {
          'action':"lab_user_info",
          'userId':$("#lab_budget_info_user_id").val(),
        };
        callAjax(data, null, displayUserInfo, null, null);
    }

    function displayUserInfo(data)
    {
        $("#lab_budget_info_group_name").html(data["group_name"]);
        $("#lab_budget_info_user_group_id").val(data["group_id"]);
        $("#lab_budget_info_site_id").val(data["user_location"]);
        loadGroupManager(data["group_id"])
    }

    function loadGroupManager(groupId) {
      let data = {
        'action':"lab_group_load_managers",
        'groupId':groupId
      };
      callAjax(data, null, displayManagerInfo, null, null);
    }

    function displayManagerInfo(data) {
      let str = ""
      let managerId = 0;
      $.each(data, function(i, obj) {
        str += obj.first_name+" "+obj.last_name;
        str += ", ";
        managerId = obj.user_id;
      });
      if (str != "") {
        str = str.substr(0, str.length -2);
      }
      $("#lab_budget_info_managers").html(str);
      //console.log("[displayManagerInfo] managerId : " + managerId);
      $("#lab_budget_info_manger_id").val(managerId);
    }
});