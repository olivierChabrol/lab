jQuery(function($){
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

    $("#lab_budget_info_create_table").click(function () {
      data = {
        'action':"lab_budget_info_create_tables",
      };
      callAjax(data, null, reloadPage, null, null);
    });

    $("#lab_budget_info_entry_create").click(function() {
      if (checkNewOrder())
      {
        saveNewOrder();
      }
    });

    function saveNewOrder() {
      let fields = ['expenditure_type', 'title', 'date_of_request', 'user_id', 'user_group_id', 'site', 'contract'];
      for (const element of fields) {
        console.log(element + " : "+ $("#lab_budget_info_"+element).val());
      }

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
        $("#lab_budget_info_site").val(data["user_location"]);
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
      $.each(data, function(i, obj) {
        str += obj.first_name+" "+obj.last_name;
        str += ", ";
      });
      if (str != "") {
        str = str.substr(0, str.length -2);
      }
      $("#lab_budget_info_managers").html(str);
    }
});