/* globals global 25 03 2020 */
//const { __, _x, _n, sprintf } = wp.i18n;

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

  $("#lab_budget_info_amount").focusout(function () {
    let amount = $("#lab_budget_info_amount").val();
    if (amount == "")
    {
      amount = "0.0";
    }
    else {
      if (amount.indexOf(",")) {
        amount = amount.replace(",",".");
      }
      if (amount.indexOf(" ")) {
        amount = amount.replace(" ","");
      }
    }
    $("#lab_budget_info_amount").val(amount);
  });

  /**
   * Filter
   */
  $("#lab_budget_info_filter_order_number").keyup(function (e) {
    applyFilter();
  });
  $("#lab_budget_info_filter_year").change(function () {
    applyFilter();
  });
  $("#lab_budget_info_filter_state").change(function () {
    applyFilter();
  });
  $("#lab_budget_info_filter_fund_origin").change(function () {
    applyFilter();
  });
  $("#lab_budget_info_filter_site").change(function () {
    applyFilter();
  });
  $("#lab_budget_info_filter_budget_manager").change(function () {
    applyFilter();
  });

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

  function applyFilter() {
    data = {
      'action':"lab_budget_info_load",
    };
    let filterFields = ["year", "state", "fund_origin", "order_number", "site", "budget_manager"];
    for (let i = 0; i < filterFields.length ; i++) {
      let filter = filterFields[i];
      if ($("#lab_budget_info_filter_" + filter).val() != "") {
        data["filters"] = {};
        data["filters"][filter] = $("#lab_budget_info_filter_"+filter).val();
      }

    }
/*
    if ($("#lab_budget_info_filter_state").val() != "") {
      if (!data["filters"]) {
        data["filters"] = {};
      }
      data["filters"]['state'] = $("#lab_budget_info_filter_state").val();
    }
    if ($("#lab_budget_info_filter_fund_origin").val() != "") {
      if (!data["filters"]) {
        data["filters"] = {};
      }
      data["filters"]['funds'] = $("#lab_budget_info_filter_fund_origin").val();
    }
    if ($("#lab_budget_info_filter_command_number").val() != "") {
      if (!data["filters"]) {
        data["filters"] = {};
      }
      data["filters"]['command_number'] = $("#lab_budget_info_filter_command_number").val();
    }
    //*/
    callAjax(data, null, displayBudget, null, null);

  }

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
    let budgetSum = {};
    let origins = [];
    let sites   = [];
    let sumPerOrigin = {};
    $("#lab_admin_budget_info_list_table_tbody").empty();
    $("#lab_budget_info_filter_year").empty();
    $("#lab_budget_info_filter_year").append(new Option(__("Year",'lab'),""));
    $.each(data.years, function(i, obj) {
      //let option = $('<options />').attr("value",obj).html(obj);
      $("#lab_budget_info_filter_year").append(new Option(obj, obj));
    });

    if (data.filters["year"])
    {
        $("#lab_budget_info_filter_year").val(data.filters["year"]);
    }
    if (data.filters["funds"])
    {
        $("#lab_budget_info_filter_fund_origin").val(data.filters["funds"]);
    }
    $.each(data.results, function(i, obj) {
        
        let tr = $('<tr />');
        if (data.params[obj.fund_origin] == undefined) {
          // red
          tr.css("background-color", "#ffadad");

        }
        else if (obj.payment_date != "0000-00-00" && obj.delivery_date != "0000-00-00" && obj.order_date != "0000-00-00") {
          // vert
          tr.css("background-color", "#caffbf");
        } 
        else if (obj.delivery_date == "0000-00-00") {
          if (obj.payment_date == "0000-00-00") {
            // orange
            tr.css("background-color", "#ffd6a5");
          }
          else {
            // yellow
            tr.css("background-color", "#fdffb6");
          }
        }
        else if (obj.payment_date == "0000-00-00") {
          if (obj.delivery_date == "0000-00-00") {
            // orange
            tr.css("background-color", "#ffd6a5");
          }
          else {
            // yellow
            tr.css("background-color", "#fdffb6");
          }
        }
        tr.append(createTd(obj.id));
        tr.append(createTd(obj.request_date));
        tr.append(createTd(obj.order_number));
        tr.append(createTd(obj.order_reference));
        tr.append(createTd(obj.title));
        tr.append(displayTdParam(obj.site_id, data));
        tr.append(displayTdUser(obj.user_id, obj, data));
        tr.append(displayTdGroup(obj.user_id, obj, data));
        tr.append(displayTdUser(obj.info_manager_id, obj, data));
        tr.append(displayTdUser(obj.budget_manager_id, obj, data));
        //console.log(obj.budget_manager_id);
        tr.append(displayTdParam(obj.fund_origin, data));
        tr.append(createTdMoney(obj.amount));
        tr.append(createTdDate(obj.order_date, obj.id, "order_date"));
        tr.append(createTdDate(obj.delivery_date, obj.id, "delivery_date"));
        tr.append(createTdDate(obj.payment_date, obj.id, "payment_date"));
        tr.append(createEditButton(obj.id));

        let fo = getFundOriginString(obj.fund_origin, data);
        let s  = getFundOriginString(obj.site_id, data);
        if (!budgetSum[fo]) {
          budgetSum[fo] = {}
          budgetSum[fo][s] = parseFloat(obj.amount);
          //nbOriginFund += 1;
          if(!origins.includes(fo)) {
            origins.push(fo);
          }

          if (!sites.includes(s)) {
            sites.push(s);
          }
        }
        else {
          if(!budgetSum[fo][s]) {
            budgetSum[fo][s] = parseFloat(obj.amount);
            if (!sites.includes(s)) {
              sites.push(s);
            }
          }
          else {
            budgetSum[fo][s] += parseFloat(obj.amount);
          }
        }
        
        $("#lab_admin_budget_info_list_table_tbody").append(tr);
    });
    //console.log(budgetSum);
    console.log(origins);
    console.log(sites);
    console.log(budgetSum);
    $("#lab_admin_budget_info_sum_table_thead").empty();
    $("#lab_admin_budget_info_sum_table_tbody").empty();
    let th = $('<th />').html("Origine des crédits");
    $("#lab_admin_budget_info_sum_table_thead").append(th);
    $.each(budgetSum, function(key, value) {
      console.log(value);
      let th = $('<th />').html(key);
      $("#lab_admin_budget_info_sum_table_thead").append(th);
    });
    th = $('<th />').html("Total");
    $("#lab_admin_budget_info_sum_table_thead").append(th);

    $.each(sites, function(i, item) {
      let tr = $('<tr />');
      let j = 0;
      let td = $('<td />').html(item);
      tr.append(td);
      let sumPerSite = 0;
      for (j = 0 ; j < origins.length ; j++) {
        let amount = 0;
        if (budgetSum[origins[j]][item]) {
          amount = budgetSum[origins[j]][item];
          if (!sumPerOrigin[origins[j]]) {
            sumPerOrigin[origins[j]] = 0.0;
          }
          sumPerOrigin[origins[j]] += parseFloat(amount);
        }
        sumPerSite += amount;
        let td = $('<td />').html(formatMoney(amount));
        tr.append(td);
      }
      let b = $('<b />').html(formatMoney(sumPerSite))
      td = $('<td />').append(b);
      tr.append(td);

      $("#lab_admin_budget_info_sum_table_tbody").append(tr);
    });

    let tr = $('<tr />');
    let b = $('<b />').html("Total");
    let td = $('<td />').append(b);
    tr.append(td);
    let sum = 0.0;
    for (j = 0 ; j < origins.length ; j++) {
      sum += sumPerOrigin[origins[j]];
      let b = $('<b />').html(formatMoney(sumPerOrigin[origins[j]]));
      let td = $('<td />').append(b);
      tr.append(td);
    }
    b = $('<b />').html(formatMoney(sum));
    td = $('<td />').append(b);
    tr.append(td);
    $("#lab_admin_budget_info_sum_table_tbody").append(tr);

  }

  function createTdDate(date, budget_info_id, field) {
    //let str = date;
    let td = undefined;
    if(date == "0000-00-00") {
      //str += '<br><a href="#">set date</a>';
      let aSetDate = $('<a />').attr("href", "#").attr("budgetId", budget_info_id).attr("field", field).html("Set today");
      $(aSetDate).click(function (e){
        e.preventDefault();
        setToday($(this).attr("budgetId"), $(aSetDate).attr("field"));
      });
      td = createTd(date);
      td.append($("<br>"));
      td.append(aSetDate);
      //return aSetDate;
    }
    else {
      td = createTd(date);
    }
    return td;
  }

  function setToday(budget_info_id, field) {
    data = {
      'action':"lab_budget_info_set_date",
      'id': budget_info_id,
      'field': field,
    };
    callAjax(data, null, loadAllBudgetInfo, null, null);
    //console.log("[setToday] " + budget_info_id + " / " + field);
    //callAjax(data, null, null, null, null);
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

  function createTdMoney(str) {
    return $('<td />').attr("align", "right").html(formatMoney(str));

  }

  function createTd(str)
  {
    return $('<td />').html(str);
  }

  function getFundOriginString(paramId, data) {
    let f = "Not defined";
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

  function displayTdParam(paramId, data) {
    return createTd(getFundOriginString(paramId, data));
  }

  function displayTdGroup(userId, obj, data) {
    let f = "";
    if (userId != 0 && data.users[userId] != undefined)
    {
      let user = data.users[userId];
      f = user.group;
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
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(value);
  }

  function selectManager(data) {
    $("#lab_budget_info_budget_manager_id").val(data[0]["user_id"]);
  }

  function getFields() {
    return ['expenditure_type', 'title', 'request_date', 'site_id', 'user_id', 'budget_manager_id', 'fund_origin', 'amount', 'order_number', 'order_date', 'delivery_date', 'payment_date', 'order_reference'];
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
    //callAjax(data, "Order Save", null, null, null);
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
      $("#lab_budget_info_budget_manager_id").val(managerId);
    }
});