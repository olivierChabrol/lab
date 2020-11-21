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
        $("#lab_budget_info_site").val(data["user_location"]);
    }
});