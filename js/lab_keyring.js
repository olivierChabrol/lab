jQuery(function($){
  //$.getScript("lab_global.js");
  $("#lab_keyring_entry_user").autocomplete({
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
        $("#lab_keyring_entry_user_id").val(userId);
      }
    }
  );

  $("[id^=lab_keyring_entry_number").autocomplete({
    minChars: 2,
    source: function(term, suggest){
      try { searchRequest.abort(); } catch(e){}
      let index = $(this.element).attr("index");
      //alert("index :" + index);
      data = { action: 'keyring_search_key_number', type: $("#lab_keyring_entry_type_"+index).val(), search: term,};
      searchRequest = $.post(LAB.ajaxurl, data,
      function(res) {
        suggest(res.data);
      });
    },
    select: function( event, ui ) {
      let index = $(this).attr("index");
      $("#lab_keyring_entry_key_id"+index).val(ui.item.value);
      loadKeyInfo($("#lab_keyring_entry_key_id"+index).val(), index);
      //alert(ui.item.id);
    }
  });

  function loadKeyInfo(keyId, index)
  {
    data = { action: 'keyring_find_key', id: keyId,};
    
    jQuery.post(LAB.ajaxurl, data, function(response) {
      if (response.success) {
          toast_success(__("Loaded key",'lab'));
          let key = response.data[0];
          $("#lab_keyring_entry_number"+index).val(key.number);
          $("#lab_keyring_entry_office"+index).val(key.office);
          $("#lab_keyring_entry_brand"+index).val(key.brand);
          $("#lab_keyring_entry_site"+index).val(key.site);
          $("#lab_keyring_entry_commentary"+index).val(key.commentary);
          return;
      }
    });
  }
  //*/

  $("#lab_keyring_create_table_keys").click(function () {
      var data = {
      'action' : 'keyring_table_keys',
      };
      jQuery.post(LAB.ajaxurl, data, function(response) {
      if (response==0) {
          toast_success(__("Table successfully created",'lab'));
          $("#lab_keyring_noKeysTableWarning").css("display","none");
          return;
      }
      toast_error(__("Error when creating the table",'lab')+" : "+response);
      });
  });
  $("#lab_keyring_create_table_loans").click(function () {
      var data = {
      'action' : 'keyring_table_loans',
      };
      jQuery.post(LAB.ajaxurl, data, function(response) {
      if (response==0) {
          toast_success(__("Table successfully created",'lab'));
          $("#lab_keyring_noLoansTableWarning").css("display","none");
          return;
      }
      toast_error(__("Error while creating the table",'lab')+" : "+response);
      });
  });
  $("#lab_keyring_entry_create").click(function () {
    let userId = $("#lab_keyring_entry_user_id").val();
    let keyNumber = parseInt($("#lab_keyring_entry_number").val());
    let data = {action: "keyring_save_loans"};

    data['userId'] = userId;
    data['keyNumber'] = keyNumber;
    for (let i = 0 ; i < keyNumber ; i++)
    {
      let keyId = $("#lab_keyring_entry_key_id"+i).val();
      // add existing Key
      if (keyId != "")
      {
        data['key_id'+i] = keyId;
      }
      // add new Key
      else
      {
        data['key_id'+i] = -1;
        data['key_type'+i]   = $("#lab_keyring_entry_type_"+i).val();
        data['key_number'+i] = $("#lab_keyring_entry_number"+i).val();
        data['key_office'+i] = $("#lab_keyring_entry_office"+i).val();
        data['key_brand'+i]  = $("#lab_keyring_entry_brand"+i).val();
        data['key_site'+i]   = $("#lab_keyring_entry_site"+i).val();
        data['key_commentary'+i] = $("#lab_keyring_entry_commentary"+i).val();
      }
    }

    callAjax(data, null, clearAndReload, null, null);
  });

  function clearAndReload() {
    clearEntryField();
    let tab = $("#lab_keyring_tab").val();
    reloadKeyringPage(tab);
  }

  function reloadKeyringPage(tab) {
    window.location.reload(false); 
  }

  function clearEntryField()
  {
    $("#lab_keyring_entry_user").val("");
    $("#lab_keyring_entry_user_id").val("");
    let keyNumber = parseInt($("#lab_keyring_entry_number").val());
    for (let i = 0 ; i < keyNumber ; i++)
    {
      $("#lab_keyring_entry_number"+i).val("");
      $("#lab_keyring_entry_key_id"+i).val("");
      $("#lab_keyring_entry_office"+i).val("");
      $("#lab_keyring_entry_brand"+i).val("");
      $("#lab_keyring_entry_commentary"+i).val("");
      $("#lab_keyring_entry_site"+i).val("");
    }
  }

  $("#lab_keyring_newKey_create").click(function () {
      let params=Object();
      for (i of ['type','number','office','brand','site']) {
      if ($("#lab_keyring_newKey_"+i).val() == "") {
          $("#lab_keyring_newKey_"+i).css("border-color","#F00");
          toast_error(__("Error while creating the key","lab")+" :<br/>"+__("All required fields must be filled","lab"));
          return;
      }
      $("#lab_keyring_newKey_"+i).css("border-color","#0071a1");
      params[i] = $("#lab_keyring_newKey_"+i).val();
      }
      regex=/\"/g;
      params["commentary"] = $("#lab_keyring_newKey_commentary").val().replace(regex,"”").replace(/\'/g,"’");
      createKey(params);
      clearFields("lab_keyring_newKey_",['number','office','brand','commentary']);
  });
  $(".lab_keyring_table").on('click','.lab_keyring_key_lend',function() {
    //Réinitialise le formulaire
    hideLoanManagement(true);
    //Affiche l'historique des prêts pour cette clé :
    $("#lab_keyring_all_loans").attr('selector',$(this).attr('keyid'));
    allLoansLoad();
    //Remplit les champs du prêt : 
    getKeyInfo($(this).attr('keyid'), function(response) {
      if (response.success) {
        //Remplit le tableau Clé/Badge
        for (i of ['id','number', 'type', 'office', 'brand', 'site', 'commentary', 'available']) {
          if (i=="site") {
              $(".lab_keyring_loanform_key_sites[siteID="+response.data[i]+"]").show();
          } else if (i=='type') {
              $(".lab_keyring_loanform_type[typeID="+response.data[i]+"]").show();
          } else if (i == 'available') {
            if (response.data[i]==1) {
              //La clé est disponible, affiche le formulaire de création de prêt :
              showLoanManagement(false);
            } else {
              //Sinon on affiche le prêt en cours :
              getLoanForKey(response.data['id'], function(resp) {
                if (resp.success) {
                  for (i of ['commentary', 'start_date','end_date']) {
                    $("#lab_keyring_loanform_"+i).val(resp.data[i]);
                  }
                  $("#lab_keyring_loanform_referent").attr('referent_id',resp.data['referent_id']);
                  //Stocke l'ID du prêt pour les prochaines actions
                  $(".lab_keyring_loanform_actions").attr("loan_id",resp.data['id']);
                  //Stocke l'ID de l'utilisateur qui a la clé
                  $("#lab_keyring_loanform_user").attr("userid",resp.data['user_id']);
                  //Récupère le nom de l'utilisateur qui a la clé : 
                  getUserNames_fromID(resp.data['user_id'],function (rep) {
                    if (rep.success) {
                      $("#lab_keyring_loanform_user").val(rep.data['first_name']+" "+rep.data['last_name']);
                    }
                  });
                  //Récupère le nom de l'utilisateur référent :
                  getUserNames_fromID(resp.data['referent_id'],function (r) {
                    if (r.success) {
                      $("#lab_keyring_loanform_referent").val(r.data['first_name']+" "+r.data['last_name']);
                      showLoanManagement(true);
                    }
                  });
                } else {
                  toast_error(__("Impossible to find the loan corresponding to this key",'lab'));
                }
              });
            }
          } else {
            $("#lab_keyring_loanform_key_"+i)[0].innerHTML=response.data[i];
          }
        }
      } else {
        toast_error(__("Impossible to find the information for this key",'lab'));
      }
    });
  });
  $("#lab_keyring_keySearch").keyup(function() {
    data = {
    'action' : 'keyring_search_word',
    'search' : $(this).val(),
    'page' : $("#lab_keyring_page").val(),
    'limit' : ($("#lab_keyring_keysPerPage").val() == 'custom') ? $("#lab_keyring_keysPerPage_otherValue").val() : $("#lab_keyring_keysPerPage").val(),
    }
    jQuery.post(LAB.ajaxurl, data, function(response) {
      if (response.success) {
        //On calcule sur combien de pages s'étalent les lignes trouvées
        $("#lab_keyring_search_totalResults")[0].innerHTML=response.data[0]+" "+__("result(s)","lab")+".";
        if (response.data[0]==0) {//Aucun résultat trouvé, on cache le "next page" et on propose de créer une clé
          $("#lab_keyring_pageNav").hide();
          $("#lab_keyring_keysList")[0].innerHTML="<tr><td colspan='9'>"+__('No keys found. You can create one below','lab')+" :</td></tr>";
          $("#lab_keyring_newKey_number").val($("#lab_keyring_keySearch").val());
        } else {
          //Combien de lignes restantes à afficher ?
          nb = Math.ceil(response.data[0] / (jQuery("#lab_keyring_keysPerPage").val()=="custom" ? jQuery("#lab_keyring_keysPerPage_otherValue").val() : jQuery("#lab_keyring_keysPerPage").val()));
          output='';
          nb == 1 ? jQuery(".lab_keyring_pageNav").hide() : jQuery(".lab_keyring_pageNav").show();;
          for (i=1; i<=nb; i++) {
            output += "<option value="+(i-1)+">"+i+"</option>";
          }
          //Récupère la page sélectionnée
          bak = $("#lab_keyring_page")[0].selectedIndex;
          //Met à jour le nombre de pages nécessaires :
          $("#lab_keyring_page")[0].innerHTML=output;
          $("#lab_keyring_page")[0].selectedIndex = bak;

          //Affiche la liste des clés trouvées :
          $("#lab_keyring_keysList")[0].innerHTML=response.data[1];
          //Bind les fonctions aux boutons de modification :
          $(".lab_keyring_key_edit").click(function () {
              getKeyInfo($(this).attr('keyid'), function(response) {
              if (response.success) {
                  $("#lab_keyring_editForm").show();
                  $("#lab_keyring_editForm_submit").attr('keyid',response.data['id']);
                  for (i of ['number', 'office', 'type', 'brand', 'site', 'commentary']) {
                  $("#lab_keyring_edit_"+i).val(response.data[i]);
                  }
              }
              });
          });
          //Bind les fonctions aux boutons de suppression :
          $(".lab_keyring_key_delete").click(function() {
              $("#lab_keyring_keyDelete_confirm").attr('keyid',$(this).attr('keyid'));
          }); 
        }
        return;
      }
    });
  });
  if ( $("#lab_keyring_keysList").length ) {
    $("#lab_keyring_keySearch").keyup();
  }
  $("#lab_keyring_editForm_submit").click(function() {
      fields={};
      for (i of ['number','office']) {
      if ($("#lab_keyring_edit_"+i).val() == "") {
          $("#lab_keyring_edit_"+i).css("border-color","#F00");
          toast_error(__("Error while changing the key","lab")+" :<br/>"+__("All required fields must be filled","lab"));
          return;
      } else {
          $("#lab_keyring_edit_"+i).css("border-color","#0071a1");
      }
      }
      regex=/\"/g;
      for (i of ['type','number','office','brand','site','state','commentary']) {
      fields[i] = $("#lab_keyring_edit_"+i).val().replace(regex,"”").replace(/\'/g,"’");
      }
      data = {
      'action': 'keyring_edit_key',
      'id': $(this).attr('keyid'),
      'fields': fields
      };
      jQuery.post(LAB.ajaxurl, data, function(response) {
      if (response.success) {
          toast_success(__("Key successfully modified",'lab'));
          $("#lab_keyring_keySearch").keyup();
          clearFields("lab_keyring_edit_",['number','office','brand','commentary']);
          $("#lab_keyring_editForm").hide();
          return;
      }
      toast_error(__("Failed to change the key",'lab'));
      });
  });
  $("#lab_keyring_keyDelete_confirm").click(function() {
      data = {
      'action': 'keyring_delete_key',
      'id': $(this).attr('keyid')
      }
      jQuery.post(LAB.ajaxurl, data, function(response) {
      if (response.success) {
          toast_success(__('Key deleted successfully','lab'));
          $("#lab_keyring_keySearch").keyup();
          return;
      }
      toast_error(__("The key could not be deleted",'lab'));
      });
  });
  $("#lab_keyring_keysPerPage").change(function () {
      if ($(this).val()=="custom") {
        $("#lab_keyring_keysPerPage_otherValue").show();
      }
      else {
        $("#lab_keyring_keysPerPage_otherValue").hide();
      }
      $("#lab_keyring_keySearch").keyup();
      $("#lab_keyring_loanSearch").change();
  });
  $("#lab_keyring_keysPerPage_otherValue").change(function () {
      $("#lab_keyring_keySearch").keyup();
      $("#lab_keyring_loanSearch").change();
  });
  $(".lab_keyring_nextPage").click(function() {
      do {
      $("#lab_keyring_page")[0].selectedIndex++;
      } while ($("#lab_keyring_page")[0].selectedIndex == -1);
      $(".lab_keyring_prevPage").show();
      $("#lab_keyring_keySearch").keyup();
      $("#lab_keyring_loanSearch").change();
  });
  $(".lab_keyring_prevPage").click(function() {
      $("#lab_keyring_page")[0].selectedIndex--;
      if ($("#lab_keyring_page")[0].selectedIndex == -1) {
          $("#lab_keyring_page")[0].selectedIndex++;
          $(this).hide();
      };
      $("#lab_keyring_keySearch").keyup();
      $("#lab_keyring_loanSearch").change();
  });
  $("#lab_keyring_page").change(function () {
      $("#lab_keyring_loanSearch").change();
      $("#lab_keyring_keySearch").keyup();
  });
  $("#lab_keyring_loanform_referent").autocomplete({
      minChars: 3,
      source: function(term, suggest){
      try { searchRequest.abort(); } catch(e){}
      searchRequest = $.post(ajaxurl, { action: 'search_username',search: term, }, function(res) {
          suggest(res.data);
      });
      },
      select: function( event, ui ) {
      var value = ui.item.value;
      var label = ui.item.label;
      event.preventDefault();
      $("#lab_keyring_loanform_referent").val(label);
      $("#lab_keyring_loanform_referent").attr('referent_id',value);
      }
  });
  $("#lab_keyring_loanform_user").autocomplete({
      minChars: 3,
      source: function(term, suggest){
      try { searchRequest.abort(); } catch(e){}
      searchRequest = $.post(ajaxurl, { action: 'search_username',search: term, }, function(res) {
          suggest(res.data);
      });
      },
      select: function( event, ui ) {
      var value = ui.item.value;
      var label = ui.item.label;
      event.preventDefault();
      $("#lab_keyring_loanform_user").val(label);
      $("#lab_keyring_loanform_user").attr('userID',value);
      }
  });
  $("#lab_keyring_loanform_create").click(function(){
    params={};
    for (i of ['referent','user','start_date']) {
    if ($("#lab_keyring_loanform_"+i).val().length==0) {
        $("#lab_keyring_loanform_"+i).css("border-color","#F00");
        toast_error(__("Impossible to create the loan: the form is not filled in correctly",'lab'));
        return;
    } else {
        $("#lab_keyring_loanform_"+i).css("border-color","");
    }
    }
    regex=/\"/g;
    for (i of ['commentary', 'start_date','end_date']) {
    params[i] = $("#lab_keyring_loanform_"+i).val().replace(regex,"”").replace(/\'/g,"’");
    }
    params["referent_id"] = $("#lab_keyring_loanform_referent").attr("referent_id");
    params["key_id"] = $("#lab_keyring_loanform_key_id").text();
    params["user_id"] = $("#lab_keyring_loanform_user").attr('userid');
    createLoan(params);
    clearFields("lab_keyring_loanform_",['user','end_date','start_date','commentary','referent']);
    $("#lab_keyring_loanform_user").attr('userid','');
  });
  $("#lab_keyring_loanform_edit").click(function () { 
      params={};
      regex=/\"/g;
      for (i of ['commentary', 'start_date','end_date']) {
      params[i] = $("#lab_keyring_loanform_"+i).val().replace(regex,"”").replace(/\'/g,"’");
      }
      params["referent_id"] = $("#lab_keyring_loanform_referent").attr("referent_id");
      params["key_id"] = $("#lab_keyring_loanform_key_id").text();
      params["user_id"] = $("#lab_keyring_loanform_user").attr('userid');
      editLoan($(this).attr('loan_id'), params);
      clearFields("lab_keyring_loanform_",['user','end_date','start_date','commentary','referent']);
      $("#lab_keyring_loanform_user").attr('userid','');
  });
  $("#lab_keyring_loanform_end").click(function () {
      $("#lab_keyring_endLoan_dialog").modal();
      $("#lab_keyring_endLoan_confirm").attr('loan_id',$(this).attr('loan_id'));
      $("#lab_keyring_endLoan_date").text(defaultTodayDate($("#lab_keyring_loanform_end_date").val()));
  });
  $("#lab_keyring_endLoan_confirm").click(function() {
      endLoan($(this).attr('loan_id'),$("#lab_keyring_loanform_key_id").text(),defaultTodayDate($("#lab_keyring_loanform_end_date").val()));
  });
  $(".lab_keyring_loanContract").click(function() {
      loanContract($("#lab_keyring_loanform_key_id").text());
  });
  if ($("#lab_keyring_all_loans").length) {
    allLoansLoad();
  }
  /**********************  Second onglet :  ******************************/
  $("#lab_keyring_loanSearch").change(function() {
    if ($(this).val()=="") {
      $(this).attr('user_id',0);
    }
    loans_for_user($(this).attr('user_id'));
  });
  if ( $("#lab_keyring_loanSearch").length ) {
    $("#lab_keyring_loanSearch").change();
  }
  $("#lab_keyring_loanSearch").autocomplete({
      minChars: 3,
      source: function(term, suggest){
      try { searchRequest.abort(); } catch(e){}
      searchRequest = $.post(ajaxurl, { action: 'search_username',search: term, }, function(res) {
          suggest(res.data);
      });
      },
      select: function( event, ui ) {
      var value = ui.item.value;
      var label = ui.item.label;
      event.preventDefault();
      $(this).val(label);
      $(this).attr("user_id",value);
      loans_for_user(value);
      $("#lab_keyring_all_loans").attr('selector',value);
      allLoansLoad();
      $("#lab_keyring_all_loans").show();
      }
  });
  $("#lab_keyring_loanSearch").focus(function() {
    $(this).val('');
    $(this).change();
    hideLoanManagement(false);
  });
  $(".lab_keyring_table").on("click",".lab_keyring_loan_edit",function(){
    //Réinitialise le formulaire
    hideLoanManagement(true);
    //Remplit les champs du prêt : 
    getLoanForID($(this).attr('loan_id'), function(resp) {
      if (resp.success) {
        for (i of ['commentary', 'start_date','end_date']) {
          $("#lab_keyring_loanform_"+i).val(resp.data[i]);
        }
        $("#lab_keyring_loanform_referent").attr('referent_id',resp.data['referent_id']);
        //Stocke l'ID du prêt pour les prochaines actions
        $(".lab_keyring_loanform_actions").attr("loan_id",resp.data['id']);
        //Stocke l'ID de l'utilisateur qui a la clé
        $("#lab_keyring_loanform_user").attr("userid",resp.data['user_id']);
        //Récupère le nom de l'utilisateur qui a la clé : 
        getUserNames_fromID(resp.data['user_id'],function (rep) {
          if (rep.success) {
            $("#lab_keyring_loanform_user").val(rep.data['first_name']+" "+rep.data['last_name']);
          }
        });
        //Récupère le nom de l'utilisateur référent :
        getUserNames_fromID(resp.data['referent_id'],function (r) {
          if (r.success) {
            $("#lab_keyring_loanform_referent").val(r.data['first_name']+" "+r.data['last_name']);
          }
        });
        getKeyInfo(resp.data['key_id'],function (response) {
          if (response.success) {
            //Remplit le tableau Clé/Badge
            for (i of ['id','number', 'type', 'office', 'brand', 'site', 'commentary', 'available']) {
              if (i=="site") {
                $(".lab_keyring_loanform_key_sites[siteID="+response.data[i]+"]").show();
              } else if (i=='type') {
                $(".lab_keyring_loanform_type[typeID="+response.data[i]+"]").show();
              } else if (i!="available") {
                $("#lab_keyring_loanform_key_"+i)[0].innerHTML=response.data[i];
              }
            }
            showLoanManagement(true);
            (resp.data['ended']) == 0 ? $("#lab_keyring_loanform_end").show() : $("#lab_keyring_loanform_end").hide();
          } else {
            toast_error(__('Impossible to find the information for this key','lab'));
          }
        });
      } else {
        toast_error(__("Impossible to find the ID loan",'lab')+" : "+$(this).attr('loan_id'));
      }
    });
  });
  $("#lab_keyring_current_loans").on("click",".lab_keyring_loan_edit",function() {
    $("#lab_keyring_all_loans").attr('selector',$(this).attr('user_id'));
    allLoansLoad();
  });
  if ($("#lab_keyring_keysPerPage").val()=='custom') {
    $("#lab_keyring_keysPerPage_otherValue").show();
  }
});
function createKey(params) {
    data = {
      'action': 'keyring_create_key',
      'params': params 
    }
    jQuery.post(ajaxurl, data, function(response) {
      if (response.success) {
        toast_success(__("Key successfully created",'lab'));
        jQuery("#lab_keyring_keySearch").keyup();
        return;
      }
      toast_error(__("Error while creating a key",'lab'));
    });
}
function createLoan(params) {
  data = {
    'action': 'keyring_create_loan',
    'params': params
  }
  jQuery.post(ajaxurl, data, function(response) {
    if (response.success) {
      toast_success(__("Loan successfully created",'lab'));
      jQuery(function($) {
        $("#lab_keyring_keySearch").keyup();
        allLoansLoad();
        $("#lab_keyring_loanSearch").change();
      });
      hideLoanManagement(false);
      return;
    }
    toast_error(__("Error while creating the loan",'lab'));
  });
}
function editLoan(id, params) {
  data = {
    'action': 'keyring_edit_loan',
    'params': params,
    'id': id
  }
  jQuery.post(ajaxurl, data, function(response) {
    if (response.success) {
      toast_success(__("Loan successfully modified",'lab'));
      allLoansLoad();
      jQuery("#lab_keyring_loanSearch").change();
      hideLoanManagement(false);
      return;
    }
    toast_error(__("Loan modification error",'lab'));
  });
}
function endLoan(loan_id, key_id, date) {
  data = {
    'action': 'keyring_end_loan',
    'key_id': key_id,
    'loan_id': loan_id,
    'end_date': date
  }
  jQuery.post(ajaxurl, data, function(response) {
    if (response.success) {
      toast_success(__("Loan successfully completed",'lab'));
      hideLoanManagement(false);
      jQuery(function($){
        $("#lab_keyring_keySearch").keyup();
        allLoansLoad();
        $("#lab_keyring_loanSearch").change();
      });
      hideLoanManagement(false);
      return;
    }
    toast_error(__("Error when trying to end the loan",'lab'));
  });
}
function getKeyInfo(key_id,callback) {
  var data={
    'action': 'keyring_get_key',
    'id': key_id
  };
  jQuery.post(ajaxurl,data,callback);
}
function getLoanForKey(key_id, callback) {
  data={
    'action': 'keyring_find_loan_byKey',
    'key_id': key_id
  }
  jQuery.post(ajaxurl,data,callback);
}
function getUserNames_fromID(user_id,callback) {
  data = {
    'action': 'usermeta_names',
    'search': {'term':user_id}
  };
  jQuery.post(ajaxurl,data,callback);
}

function loanContract(key_id) {
  //Ouvre une fenêtre popup vide, sans barre d'outils etc.
  var win = window.open("", "", "toolbar=no,dependent=yes,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=800,height=500,top=0,left="+(screen.width/3));                  
  //Récupère les informations sur la clé, le prêt et les noms complets des utilisateurs
  getKeyInfo(key_id, function (key) { 
    if (key.success) {
      getLoanForKey(key_id, function(loan) {
        if (loan.success) {
          getUserNames_fromID(loan.data['referent_id'], function (ref) {
            if (ref.success) {
              referent = ref.data['first_name']+" "+ref.data['last_name'];
              getUserNames_fromID(loan.data['user_id'],function(userRep) {
                if (userRep.success) {
                  user = userRep.data['first_name']+" "+userRep.data['last_name'];
                  output='<html>\
                  <head>\
                    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">\
                    <link rel="stylesheet" id="KeyRingCSS-css" href="http://stage.fr/wp-content/plugins/lab/css/keyring.css?ver=5.4" media="all">\
                    <meta charset="UTF-8">\
                    <title>'+__('Loan receipt','lab')+' n'+loan.data["id"]+'</title>\
                    <script>document.onkeydown = function(e) {if (e.which== 27) {window.close();}}</script>\
                  </head>\
                  <body>\
                    <h2 id="loanContract_title">'+__('Loan receipt','lab')+'</h2>\
                    <div id="loanContract">\
                        <article>\
                          <h4>'+__('Loan ID','lab')+' : <u>'+loan.data["id"]+'</u></h4>\
                          <h4>'+__('Referent','lab')+' : <u>'+referent+'</u></h4>\
                          <p>'+__('The key/badge number','lab')+' : <b>'+key.data["number"]+' </b></p>\
                          <p>'+__('Has been lent to','lab')+' <b>'+user+'</b></p>';
                          start = new Date(loan.data["start_date"]).toLocaleDateString(navigator.language,{ year: 'numeric', month: 'long', day: 'numeric' });
                          end = new Date(loan.data["end_date"]).toLocaleDateString(navigator.language,{ year: 'numeric', month: 'long', day: 'numeric' })
                          output+='<p>Le <b>'+start+'</b></p>';
                          output+=(loan.data["end_date"]===null) ? '<p>Et devra être rendue.</p>' : '<p>'+__('And must be returned before','lab')+' <b>'+end+'</b></p>';
                          output+=(loan.data["commentary"]===null) ? '' : '<p>'+__('Comment','lab')+' : <i>'+loan.data["commentary"]+'</i></p><br/>';
                          output+='<p><u>'+__('Signature','lab')+' :</u></p>\
                        </article>';
                        output+= document.getElementById('lab_keyring_loanform_table').outerHTML;
                      output+='</div><button onclick="window.print();">'+__('Print','lab')+'</button>\
                    </body>\
                  </html>';
                  win.document.write(output);
                  win.print();
                }
              });
            }
          });
        }
      });    
    }
    });
}
function oldLoans(id) {
  data = {
    'action': 'keyring_find_old_loans'
  };
  //Selon l'onglet, affiche l'historique des prêts pour la clé ou pour l'utilisateur :
  (getUrlVars()['tab'] == null || getUrlVars()['tab'].split("#")[0] == 'default') ? data['key_id'] = id : data['user_id']= id;
  jQuery.post(ajaxurl, data, function(response) {
    jQuery(".lab_keyring_loansList")[0].innerHTML=response.data;
  });
}

function loans_for_user(user) {
  data = {
      'action': 'keyring_find_curr_loans',
      'user': user,
      'limit': jQuery("#lab_keyring_keysPerPage").val()=="custom" ? jQuery("#lab_keyring_keysPerPage_otherValue").val() : jQuery("#lab_keyring_keysPerPage").val() ,
      'page': jQuery("#lab_keyring_page").val(),
  }
  jQuery.post(ajaxurl, data, function(response) {
    if (response.success) {
      jQuery("#lab_keyring_search_totalResults")[0].innerHTML=response.data[0]+" "+__("result(s)","lab")+".";
      jQuery("#lab_keyring_currentLoans")[0].innerHTML=response.data[1];
      //Nombre de pages nécessaires pour afficher tous les résultats
      nb = Math.ceil(response.data[0] / (jQuery("#lab_keyring_keysPerPage").val()=="custom" ? jQuery("#lab_keyring_keysPerPage_otherValue").val() : jQuery("#lab_keyring_keysPerPage").val()));
      output='';
      nb == 1 ? jQuery(".lab_keyring_pageNav").hide() : jQuery(".lab_keyring_pageNav").show();;
      for (i=1; i<=nb; i++) {
        output += "<option value="+(i-1)+">"+i+"</option>";
      }
      bak = jQuery("#lab_keyring_page")[0].selectedIndex;
      jQuery("#lab_keyring_page")[0].innerHTML=output;
      jQuery("#lab_keyring_page").children().length <= bak ? jQuery("#lab_keyring_page")[0].selectedIndex=0 : jQuery("#lab_keyring_page")[0].selectedIndex = bak;
      jQuery(function($) {});
    }
  });
}
function showLoanManagement(existing) {
  jQuery(function($) {
    $("#lab_keyring_all_loans").show();
    if (existing) { //On affiche un prêt existant
      $(".lab_keyring_loan_current").show();
    } else { //On veut créer un nouveau prêt
      $(".lab_keyring_loan_new").show();
      //Sélectionne la date d'aujourd'hui
      $("#lab_keyring_loanform_start_date").val(defaultTodayDate(""));
      //Le référent est celui par défaut (l'utilisateur actuellement connecté)
      $("#lab_keyring_loanform_referent").val($("#lab_keyring_loanform_referent").attr('default'));
      $("#lab_keyring_loanform_referent").attr('referent_id',$("#lab_keyring_loanform_referent").attr('default_id'));
      $("#lab_keyring_loanform").show();
    }
    $("#lab_keyring_loading_gif").hide();
    $(".lab_keyring_loans_management").show();
  });
}
function hideLoanManagement(loading) {
  jQuery(function($) {
    //Cache le formulaire
    $(".lab_keyring_loans_management").hide();
    // affiche le gif de chargement : 
    loading ? $("#lab_keyring_loading_gif").show() : $("#lab_keyring_loading_gif").hide();
    $("#lab_keyring_loanform_user").attr('userid','');
    $("#lab_keyring_loanform_referent").attr('referent_id','');
    // Cache tous les éléments spécifiques
    $(".lab_keyring_loanform_key_sites").hide();
    $(".lab_keyring_loanform_type").hide();
    $(".lab_keyring_loan_new").hide();
    $(".lab_keyring_loan_current").hide();
    $(".lab_keyring_loan_new").hide();
  });
  //Vide les champs du formulaire
  clearFields("lab_keyring_loanform_",['user','end_date','start_date','commentary','referent']);
}
function getLoanForID(id, callback) {
  data={
    'action': 'keyring_find_loan_byID',
    'id': id
  }
  jQuery.post(ajaxurl,data,callback);
}
function getUrlVars() {
  var vars = {};
  var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
      vars[key] = value;
  });
  return vars;
}
function allLoansLoad() {
  if (getUrlVars()['tab']!=null) {
    if (getUrlVars()['tab'].split("#")[0] == 'second') {
      getUserNames_fromID(jQuery("#lab_keyring_all_loans").attr('selector'),function(r) {
        if (r.success) {
          jQuery("#lab_keyring_loans_title")[0].innerHTML=" <i>"+r.data['first_name']+" "+r.data['last_name']+"</i>";
        }
      });
    }
  }
  oldLoans(jQuery("#lab_keyring_all_loans").attr('selector'));
}
function defaultTodayDate(date) {
  return date.length == 0 ? new Date().toISOString().split("T")[0] : date;
}
