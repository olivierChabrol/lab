/* globals global 25 03 2020 */
jQuery(function($){
  var searchRequest;
  if(!$("#lab_user_left").is(':checked')) {
    $("#lab_user_left_date").prop("disabled", true);
  }
  $("#lab_user_left").change(function() {
    $("#lab_user_left_date").prop("disabled", !$(this).is(":checked"));
    if (!$(this).is(":checked")) {
       jQuery("#lab_user_left_date").val("");
    }
  });
  $( "#lab_user_left_date" ).datepicker();

  $("#wp_lab_group_name").autocomplete({
    minChars: 2,
    source: function(term, suggest){
      try { searchRequest.abort(); } catch(e){}
      searchRequest = $.post("/wp-admin/admin-ajax.php", { action: 'search_group',search: term, }, function(res) {
        suggest(res.data);
      });
      },
      select: function( event, ui ) {
        var label = ui.item.label;
        var value = ui.item.value;
        event.preventDefault();
        $("#wp_lab_group_name").val(label);

        $("#lab_searched_group_id").val(value);
        setinfoToGroupEditionFields(ui.item.id, ui.item.acronym, ui.item.label, ui.item.chief_id,
          ui.item.parent_group_id, ui.item.group_type);
      }
  });

  $("#lab_group_delete_button").click(function(){
      $.post("/wp-admin/admin-ajax.php",
          {
            action : 'delete_group',
            id : jQuery("#lab_searched_group_id").val()
          },

          function(data){
            if (data.success) {
              toast_success("Le groupe a bien été supprimé.");
              resetGroupEdit();
            }
          }
      )
  });
  
  $('#wp_lab_event_title').autocomplete({
    minChars: 2,
    source: function(term, suggest){
      try { searchRequest.abort(); } catch(e){}
      searchRequest = $.post("/wp-admin/admin-ajax.php", { action: 'search_event',search: term, }, function(res) {
        suggest(res.data);
      });
      },
    select: function( event, ui ) {
      var label = ui.item.label;
      var value = ui.item.value;
      event.preventDefault();
      $("#wp_lab_event_title").val(ui.item.label);
      
      $("#lab_user_left_date").val("");
      $("#lab_user_left_date").prop("disabled", true);
      $("#lab_user_left").prop("checked", false);
      $("#lab_searched_event_id").val(value);
      $("#lab_event_id").html(value);
      $("#wp_lab_event_label").text(label);
      
      loadEventCategory(value);
      return false;
    }
  });
  $('#lab_user_search').autocomplete({
    minChars: 3,
    source: function(term, suggest){
      try { searchRequest.abort(); } catch(e){}
      searchRequest = $.post("/wp-admin/admin-ajax.php", { action: 'search_username',search: term, }, function(res) {
        suggest(res.data);
      });
      },
    select: function( event, ui ) {
      var label = ui.item.label;
      var value = ui.item.value;
      event.preventDefault();
      $("#lab_user_search").val(label);

      $("#lab_user_search_id").val(value);
      callbUser(value, loadUserMetaData);
      return false;
    }
  });
  $("#lab_user_button_test").click(function() {
    test();
  });
  $("#lab_user_button_update_db").click(function() {
    updateUserMetaDb();
  });
  $('#lab-button-change-category').click(function() {
    var postId = $("#lab_searched_event_id").val();
    var categoryId = $('select[name="event_categories[]"]').val();
    saveEventCaterory(postId, categoryId);
  });
  $("#lab_user_button_save_left").click(function() {
    saveUserLeft($("#lab_user_search_id").val(), $("#lab_user_left_date").val(), $("#lab_user_left").is(":checked"));
  });
  $("#lab_createGroup_acronym").change(function() {
    var data = {
      'action' : 'group_search_ac',
      'ac' : $("#lab_createGroup_acronym").val()
    };
    jQuery.post(ajaxurl, data, function(response) {
      if (!response.success) {
        $("#lab_createGroup_acronym").css('border-color','red');
        $("#lab_createGroupe_acronym_hint").css("color","red");
        $("#lab_createGroupe_acronym_hint")[0].innerHTML="❌ Acronyme déjà utilisé par le groupe '"+response.data[0].group_name+"' (id: "+response.data[0].id+").";
        $("#lab_createGroup_create").attr('disabled');
        $("#lab_createGroup_create").css('color','lightgray');
      }
      else {
        $("#lab_createGroup_acronym").css('border-color','green');
        $("#lab_createGroupe_acronym_hint").css("color","green");
        $("#lab_createGroupe_acronym_hint")[0].innerHTML="✓ Acronyme disponible";
        $("#lab_createGroup_create").removeAttr('disabled');
        $("#lab_createGroup_create").css('color','#0071a1');
      }
    });
  })
  $("#lab_createGroup_chief").autocomplete({
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
      $("#lab_createGroup_chief").val(label);

      $("#lab_createGroup_chiefID").val(value);
    }
  });
  $("#wp_lab_group_chief_edit").autocomplete({
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
      $("#wp_lab_group_chief_edit").val(label);

      $("#lab_searched_chief_id").val(value);
    }
  });

  $("#lab_param_value_search").autocomplete({
    minChars: 3,
    source: function(term, suggest){
      try { searchRequest.abort(); } catch(e){}
      searchRequest = $.post(ajaxurl, { action: 'param_search_value',search: term, }, function(res) {
        suggest(res.data);
      });
      },
    select: function( event, ui ) {
      var value = ui.item.value;
      var label = ui.item.label;
      event.preventDefault();
      $("#wp_lab_param_id").val(value);

      $("#lab_param_value_search").val(label);
      loadEditParam(value, ui.item.type, value)
      return false;
    }
  });

  $('#lab_tab_param_create_table').click(function(){
    var data = {
      'action' : 'param_create_table',
    };
    jQuery.post(ajaxurl, data, function(response) {
      console.log(response);
    });
  });

  $('#lab_tab_param_delete').click(function(){
    paramDelete($("#wp_lab_param_type").val());
  });

  $("#lab_tab_param_save").click(function(){
    saveParam(null, jQuery("#wp_lab_param_type").val(), jQuery("#wp_lab_param_value").val(), load_params_type_after_new_param);
  });

  $("#lab_tab_param_save_edit").click(function(){
    saveParam(jQuery("#wp_lab_param_id").val(), jQuery("#wp_lab_param_type_edit").val(), jQuery("#lab_param_value_search").val(), resetParamEditFields);
  });

  $("#lab_tab_param_delete_edit").click(function(){
    paramDelete($("#wp_lab_param_id").val());
    resetParamEditFields();
  });

  $('#lab_createGroup_createRoot').click(function(){
    var data = {
      'action' : 'group_root',
    };
    jQuery.post(ajaxurl, data, function(response) {
      (response.success ? toast_success("Group successfully created") : toast_error("Error Creating Group : "+response.data));
    });
  });
  $('#lab_createGroup_createTable').click(function(){
    var data = {
      'action' : 'group_table',
    };
    jQuery.post(ajaxurl, data, function(response) {
      if (response==0) {
        toast_success("Table successfully created");
        $("#lab_group_noTableWarning").css("display","none");
        return;
      }
      toast_error("Error Creating table : "+response);
    });
  });
  $('#lab_createGroup_createTable_Sub').click(function(){
    var data = {
      'action' : 'group_sub_table',
    };
    jQuery.post(ajaxurl, data, function(response) {
      if (response==0) {
        toast_success("Table successfully created");
        $("#lab_group_noSubTableWarning").css("display","none");
        return;
      }
      toast_error("Error Creating table : "+response);
    });
  });
  $("#lab_createGroup_create").click(function() {
    valeurs = new Object();
    for (i of ['name', 'acronym', 'type', 'chiefID','parent']) {
      if ($("#lab_createGroup_"+i).val()=="") {
        $("#lab_createGroup_"+i).css("border-color","#F00");
        toast_error("Group couldn't be created :<br> The form isn't filled properly");
        return;
      }
      $("#lab_createGroup_"+i).css("border-color","");
      valeurs[i] = $("#lab_createGroup_"+i).val();
    }
    subs = $("#lab_createGroup_subID").attr('list').split(",");
    console.log(subs);
    subs.shift(); //Supprime le dernier élément (vide)
    valeurs['subsList'] = subs;
    createGroup(valeurs);
    //On réinitialise tous les champs du formulaire
    clearFields('lab_createGroup_',['name', 'acronym', 'type', 'chiefID','chief','subInput','parent']);
    $("#lab_createGroup_subsList")[0].innerHTML="";
    $("#lab_createGroup_subID").attr('list','');
    $("#lab_createGroup_subsDelete").hide();
  })
  $("#lab_group_edit_add_substitute").autocomplete({
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
      $("#lab_group_edit_add_substitute").val(label);

      //$("#lab_group_edit_add_substitute_id").val(value);
      group_edit_saveNewSubstitute(value, jQuery("#lab_searched_group_id").val());
    }
  });

  $("#lab_createGroup_subInput").autocomplete({
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
      $("#lab_createGroup_subInput").val(label);
      $("#lab_createGroup_subID").val(value);
    }
  });
  $("#lab_createGroup_addSub").click(function(){
    $("#lab_createGroup_subsDelete").show(); //Affiche la 'croix' qui permet de vider la liste des suppléants
    if ($("#lab_createGroup_subID").attr('list').split(",").includes($("#lab_createGroup_subID").val())) {
      console.log("Déjà présent");
      toast_error("Cette personne est déjà suppléant de ce groupe");
      return;
    }
    $("#lab_createGroup_subsList").append($("#lab_createGroup_subInput").val()+", ");
    $("#lab_createGroup_subID").attr("list",function() { return $(this).attr('list')+","+$(this).val()});
    $("#lab_createGroup_subInput").val("");
    $("#lab_createGroup_subID").val("");
  });
  $("#lab_createGroup_subsDelete").click(function () { 
    //Efface la liste des suppléants
    $("#lab_createGroup_subsList")[0].innerHTML="";
    $("#lab_createGroup_subID").attr('list','');
    //Se cache soi-même
    $("#lab_createGroup_subsDelete").hide();
   });
  $("#lab_admin_group_edit_button").click(function() {
    //console.log(jQuery("#wp_lab_group_chief_edit option:selected").val()); // /!\ sans option:selected, n'apparaît pas...
    $groupId = jQuery("#lab_searched_group_id").val();
    $acronym = jQuery("#wp_lab_group_acronym_edit").val();
    $name    = jQuery("#wp_lab_group_name_edit").val();
    $chief   = jQuery("#lab_searched_chief_id").val();
    $parent  = jQuery("#wp_lab_group_parent_edit").val();
    $type    = jQuery("#wp_lab_group_type_edit").val();
    editGroup($groupId, $acronym, $name, $chief, $parent, $type);
  });

  ////////////////////// Onglet KeyRing //////////////////////
  $("#lab_keyring_create_table_keys").click(function () {
    var data = {
      'action' : 'keyring_table_keys',
    };
    jQuery.post(ajaxurl, data, function(response) {
      if (response==0) {
        toast_success("Table successfully created");
        $("#lab_keyring_noKeysTableWarning").css("display","none");
        return;
      }
      toast_error("Error Creating table : "+response);
    });
  });
  $("#lab_keyring_create_table_loans").click(function () {
    var data = {
      'action' : 'keyring_table_loans',
    };
    jQuery.post(ajaxurl, data, function(response) {
      if (response==0) {
        toast_success("Table successfully created");
        $("#lab_keyring_noLoansTableWarning").css("display","none");
        return;
      }
      toast_error("Error Creating table : "+response);
    });
  });
  $("#lab_keyring_newKey_create").click(function () {
    let params=Object();
    for (i of ['type','number','office','brand','site']) {
      if ($("#lab_keyring_newKey_"+i).val() == "") {
        $("#lab_keyring_newKey_"+i).css("border-color","#F00");
        toast_error("Error creating key :<br/>All the required fields must be filled.");
        return;
      }
      $("#lab_keyring_newKey_"+i).css("border-color","#0071a1");
      params[i] = $("#lab_keyring_newKey_"+i).val();
    }
    params["commentary"] = $("#lab_keyring_newKey_commentary").val();
    createKey(params);
    clearFields("lab_keyring_newKey_",['number','office','brand','commentary']);
  });
  $("#lab_keyring_keySearch").keyup(function() {
    data = {
      'action' : 'keyring_search_word',
      'search' : $(this).val(),
      'page' : $("#lab_keyring_page").val(),
      'limit' : ($("#lab_keyring_keysPerPage").val() == 'custom') ? $("#lab_keyring_keysPerPage_otherValue").val() : $("#lab_keyring_keysPerPage").val(),
    }
    jQuery.post(ajaxurl, data, function(response) {
      if (response.success) {
        //On calcule sur combien de pages s'étalent les lignes trouvées
        $("#lab_keyring_search_totalResults")[0].innerHTML=response.data[0]+" résultats.";
        if (response.data[0]==0) {//Aucun résultat trouvé, on cache le "next page" et on propose de créer une clé
          $("#lab_keyring_nextPage").hide();
          console.log("oui");
          $("#lab_keyring_keysList")[0].innerHTML="<tr><td colspan='9'>Aucune clé trouvée. Vous pouvez en créer une ci-dessous :</td></tr>";
          $("#lab_keyring_newKey_number").val($("#lab_keyring_keySearch").val());
        } else {
          //Combien de lignes restantes à afficher ?
          rowsLeft = response.data[0]-data['limit'];
          output = "<option value='0'>1</option>";
          i = 1;
          $("#lab_keyring_nextPage").hide();
          while (rowsLeft > 0) {
            $("#lab_keyring_nextPage").show();
            rowsLeft -= data['limit'];
            output += "<option value="+i+">"+(i+1)+"</option>";
            i++;
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
            console.log($(this).attr('keyid'));
            $("#lab_keyring_keyDelete_confirm").attr('keyid',$(this).attr('keyid'));
          }); 
          //Fonction de prêt : 
          $(".lab_keyring_key_lend").click(function () {
            $("#lab_keyring_loanform").show();
            $("#lab_keyring_all_loans").show();
            oldLoans($(this).attr('keyid'));
            //Vide les champs du formulaire :
            clearFields("lab_keyring_loanform_",['user','end_date','start_date','commentary','referent']);
            $("#lab_keyring_loanform_user").attr('userid','');
            $("#lab_keyring_loanform_referent").attr('referent_id','');
            // Cache tous les éléments spécifiques
            $(".lab_keyring_loanform_key_sites").hide();
            $(".lab_keyring_loanform_type").hide();
            $(".lab_keyring_loan_new").hide();
            $(".lab_keyring_loan_current").hide();
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
                      //La clé est disponible : on veut créer un nouveau prêt
                      $(".lab_keyring_loan_new").show();
                      //Sélectionne la date d'aujourd'hui
                      $("#lab_keyring_loanform_start_date").val(defaultTodayDate(""));
                      //Le référent est celui par défaut (l'utilisateur actuellement connecté)
                      $("#lab_keyring_loanform_referent").val($("#lab_keyring_loanform_referent").attr('default'));
                      $("#lab_keyring_loanform_referent").attr('referent_id',$("#lab_keyring_loanform_referent").attr('default_id'));
                    } else {
                      //Sinon on affiche le prêt en cours :
                      $(".lab_keyring_loan_current").show();
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
                            }
                          });
                        } else {
                          toast_error("Couldn't find current key loan");
                        }
                      });
                    }
                  }
                  else {
                    $("#lab_keyring_loanform_key_"+i)[0].innerHTML=response.data[i];
                  }
                }
              } else {
                toast_error("impossible de trouver les informations de cette clé");
              }
            });
          });
        }
        return;
      }
    });
  });
  $("#lab_keyring_keySearch").keyup();//Déclenche la recherche au chargement de la page pour afficher la liste de toutes les clés
  $("#lab_keyring_editForm_submit").click(function() {
    fields={};
    for (i of ['number','office']) {
      if ($("#lab_keyring_edit_"+i).val() == "") {
        $("#lab_keyring_edit_"+i).css("border-color","#F00");
        toast_error("Error editing key :<br/>All the required fields must be filled.");
        return;
      } else {
        $("#lab_keyring_edit_"+i).css("border-color","#0071a1");
      }
    }
    for (i of ['type','number','office','brand','site','commentary']) {
      fields[i] = $("#lab_keyring_edit_"+i).val();
    }
    data = {
      'action': 'keyring_edit_key',
      'id': $(this).attr('keyid'),
      'fields': fields
    };
    jQuery.post(ajaxurl, data, function(response) {
      if (response.success) {
        toast_success("Clé modifiée avec succès");
        $("#lab_keyring_keySearch").keyup();
        clearFields("lab_keyring_edit_",['number','office','brand','commentary']);
        $("#lab_keyring_editForm").hide();
        return;
      }
      toast_error("Key couldn't be edited");
    });
  });
  $("#lab_keyring_keyDelete_confirm").click(function() {
    data = {
      'action': 'keyring_delete_key',
      'id': $(this).attr('keyid')
    }
    jQuery.post(ajaxurl, data, function(response) {
      if (response.success) {
        toast_success("Clé supprimée avec succès");
        $("#lab_keyring_keySearch").keyup();
        return;
      }
      toast_error("Key couldn't be deleted");
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
  });
  $("#lab_keyring_keysPerPage_otherValue").change(function () {
    $("#lab_keyring_keySearch").keyup();
  });
  $("#lab_keyring_nextPage_button").click(function() {
    do {
      $("#lab_keyring_page")[0].selectedIndex++;
    } while ($("#lab_keyring_page")[0].selectedIndex == -1);
    $("#lab_keyring_keySearch").keyup();
  });
  $("#lab_keyring_page").change(function () {
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
        toast_error("Couldn't create loan : the form isn't filled correctly.");
        return;
      } else {
        console.log("changement de :"+i);
        $("#lab_keyring_loanform_"+i).css("border-color","");
      }
    }
    for (i of ['commentary', 'start_date','end_date']) {
      params[i] = $("#lab_keyring_loanform_"+i).val();
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
    for (i of ['commentary', 'start_date','end_date']) {
      params[i] = $("#lab_keyring_loanform_"+i).val();
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
  $("#lab_keyring_loanContract").click(function() {
    loanContract($("#lab_keyring_loanform_key_id").text());
  });
});


function setinfoToGroupEditionFields(groupId, acronym, groupName, chiefId, parent_group_id, group_type) {
  jQuery('#wp_lab_group_to_edit').val(groupId);
  jQuery('#wp_lab_group_acronym_edit').val(acronym);
  jQuery('#wp_lab_group_name_edit').val(groupName);
  jQuery('#lab_searched_chief_id').val(chiefId);
  jQuery('#wp_lab_group_chief_edit').val(callbUser(chiefId, loadUserName));
  jQuery('#wp_lab_group_parent_edit').val(parent_group_id);

  jQuery('#wp_lab_group_type_edit').val(group_type);
  group_loadSubstitute();
}

function group_edit_deleteSubstitute(id) {
  var data = {
    'action' : 'group_delete_substitutes',
    'id' : id
  };
  jQuery.post(ajaxurl, data, function(response) 
  {
    if(response.success) 
    {
      group_loadSubstitute();
    }
  });
}

function group_edit_saveNewSubstitute(userId, groupId) {
  var data = {
    'action' : 'group_add_substitutes',
    'userId' : userId,
    'groupId': groupId
  };
  jQuery.post(ajaxurl, data, function(response) 
  {
    jQuery("#lab_group_edit_substitutes").text("");
    if(response.success) 
    {
      group_loadSubstitute();
      $("#lab_group_edit_add_substitute").val("");
    }
  });
}

function group_loadSubstitute()
{
  var groupId = jQuery('#wp_lab_group_to_edit').val();
  var data = {
    'action' : 'group_load_substitutes',
    'id' : groupId
  };
  jQuery.post(ajaxurl, data, function(response) 
  {
    jQuery("#lab_group_edit_substitutes").text("");
    if(response.success) 
    {
      for (i = 0 ; i < response.data.length ; i++) 
      {
        jQuery("#lab_group_edit_substitutes").append(response.data[i]["first_name"] + " " + response.data[i]["last_name"] + " <a href=\"#\" onclick=\"group_edit_deleteSubstitute("+response.data[i]["id"]+");return false;\">delete</a>, ");
      }
    }
  });
}

// Notifications "toast" affichant une erreur ou un succès lors de la requête de création de groupe.
function toast_error(message) {
  jQuery(function($){
    $.toast({
      text: message,
      heading: 'Error',
      icon: 'error',
      showHideTransition: 'slide', 
      hideAfter: 7000, 
      position: 'bottom-center', // bottom-left or bottom-right or bottom-center or top-left or top-right or top-center or mid-center or an object representing the left, right, top, bottom values
    });
  });
}
function toast_success(message) {
  jQuery(function($){
    $.toast({
      text: message, // Text that is to be shown in the toast
      heading: 'Success', // Optional heading to be shown on the toast
      icon: 'success', // Type of toast icon
      showHideTransition: 'slide', // fade, slide or plain
      hideAfter: 3000, // false to make it sticky or number representing the miliseconds as time after which toast needs to be hidden
      position: 'bottom-center', // bottom-left or bottom-right or bottom-center or top-left or top-right or top-center or mid-center or an object representing the left, right, top, bottom values
      loaderBg: '#9EC600',  // Background color of the toast loader
    });
  });
}
function toast_warn(message) {
  jQuery(function($){
    $.toast({
      text: message, // Text that is to be shown in the toast
      heading: 'Warning', // Optional heading to be shown on the toast
      icon: 'warning', // Type of toast icon
      showHideTransition: 'slide', // fade, slide or plain
      hideAfter: 5000, // false to make it sticky or number representing the miliseconds as time after which toast needs to be hidden
      position: 'bottom-center', // bottom-left or bottom-right or bottom-center or top-left or top-right or top-center or mid-center or an object representing the left, right, top, bottom values
      loaderBg: '#D1A600',  // Background color of the toast loader
    });
  });
}
function createGroup(params) {
  console.log(params);
  //On vérifie d'abord que l'acronyme est bien unique
  var data = {
    'action' : 'group_search_ac',
    'ac' : params['acronym']
  };
  console.log(data);
  jQuery.post(ajaxurl, data, function(response) {
    if (!response.success) {
      toast_error("Group couldn't be created : the acronym is already in use");
      return false;
    }
    //On essaie ensuite de rajouter l'entrée dans la table groups 
    params['action']='group_create';
    jQuery.post(ajaxurl, params, function(response) {
      if (response.success) {
        //Enfin, on ajoute les entrées dans la table suppléants
        var data3 = {
          'action' : 'group_subs_add',
          'id' : response.data[0].id,
          'subList' : params['subsList']
        };
        jQuery.post(ajaxurl, data3, function(resp) {
          if (!resp.success) {
            console.log("failSubs");
            toast_warn("Group created, but couldn't add substitutes : <br/>"+resp.data);
            return false;
          }
          toast_success("Group successfully created");
        });
      } else {
        toast_error("Group couldn't be created :<br/>"+response.data);
      }
    });
  });
}

function load_params_type_after_new_param() {
  jQuery("#wp_lab_param_value").val("");
  load_params_type('#wp_lab_param_type');
}

function saveParam(paramId, paramType, paramValue, callAfterComplete) {
  var data = {
    'action' : 'save_param',
    'type' : paramType,
    'value' : paramValue,
  };
  if (paramId != null) {
    data = {
      'action' : 'save_param',
      'id' : paramId,
      'type' : paramType,
      'value' : paramValue,
    };
  }
  jQuery.post(ajaxurl, data, function(response) {
    if(response.success) {
      callAfterComplete();
    }
  });
}

function loadEditParam(paramId, paramType, paramValue) {
  load_params_type('#wp_lab_param_type_edit', paramType);
}

function paramDelete(paramId) {
  var data = {
    'action' : 'param_delete',
    'id' : paramId,
  };
  jQuery.post(ajaxurl, data, function(response) {
    if(response.success) {
      load_params_type('#wp_lab_param_type', null);
    }
  });
}

function resetParamEditFields() {
  jQuery("#lab_param_value_search").val("");
  jQuery("#wp_lab_param_id").val("");
  jQuery("#lab_param_value_search").val("");
  jQuery("#lab_param_value_search").val("");
}

function load_params_type(select, selectedId = null) {
  var data = {
    'action' : 'load_param_type',
  };
  jQuery.post(ajaxurl, data, function(response) {
    if(response.success) {
      //alert("Sauver");
      jQuery(select+" option").each(function() {
        jQuery(this).remove();
      });

      jQuery.each(response.data, function (index, value){

        jQuery(select).append(jQuery('<option/>', { 
          value: value['id'],
          text : value['value'],
          selected : value['id']==selectedId 
      }));
      });
    }
  });
}

function test() {
  var data = {
               'action' : 'test',
  };
  jQuery.post(ajaxurl, data, function(response) {
    if(response.data) {
      alert("Sauver");
    }
  });
}
function updateUserMetaDb() {
  var data = {
               'action' : 'update_user_metadata_db',
  };
  jQuery.post(ajaxurl, data, function(response) {
    if(response.data) {
      alert("Sauver");
    }
  });
}

function saveUserLeft(userId, date, isChecked) {
  var c = isChecked?date:null;
  var umd = jQuery("#lab_usermeta_id").val();
  //alert("userId : " + userId + " date " + date + " is checked : " + isChecked + " c : " + c );
  var data = {
               'action' : 'update_user_metadata',
               'userMetaId' : umd,
               'dateLeft' : c,
  };
  jQuery.post(ajaxurl, data, function(response) {
    if(response.data) {
      toast_success("User saved");
    }
  });
}

function callbUser(userId, callback) { // function with callback to operate with userId
  var data = {
               'action' : 'usermeta_names',
               'search[term]' : userId
  };
  jQuery.post(ajaxurl, data, function(response) {
    callback(response);
  });
}

function loadUserName(response) {
  if(response.data) {
    jQuery("#wp_lab_group_chief_edit").val(response.data["first_name"] + " " + response.data["last_name"]);
  }
  else
  {

  }
}

function loadUserMetaData(response) {
  if(response.data) {
    //alert(response.data["first_name"]["value"]);
    jQuery("#lab_user_firstname").val(response.data["first_name"]["value"]);
    jQuery("#lab_user_lastname").val(response.data["last_name"]["value"]);
    jQuery("#lab_usermeta_id").val(response.data["lab_user_left"]["id"]);
    if (response.data["lab_user_left"]["value"] != null) {
      jQuery("#lab_user_left").prop("checked", true);
      jQuery("#lab_user_left_date").prop("disabled", false);
      jQuery("#lab_user_left_date").val(response.data["lab_user_left"]["value"]);
    } else {
	    jQuery("#lab_user_left").prop("checked", false);
      jQuery("#lab_user_left_date").prop("disabled", true);
      jQuery("#lab_user_left_date").val("");
    }
  }
  else
  {

  }
}

function saveEventCaterory(postId,categoryId) {
  var data = {
              'action': 'save_event_category',
              'postId': postId,
              'categoryId': categoryId
             };
  jQuery.post(ajaxurl, data, function(response) {
    if (response.data) {
      alert("Evenement Modifie");
      jQuery("#wp_lab_event_title").val("");
      jQuery("#lab_searched_event_id").val("");
      jQuery("#lab_event_id").html("");
      jQuery("#wp_lab_event_label").text("");
    }
  });
}

function loadEventCategory(postId) {
  var data = {
	      'action': 'search_event_category',
	      'postId': postId
	     };
  jQuery.post(ajaxurl, data, function(response) {
    if (response.data) {
      //alert('Got this from the server: ' + response.data["ID"]);
      jQuery("#wp_lab_event_label").html("<b>"+response.data["name"]+"</b>");
      jQuery("#wp_lab_event_date").html(response.data["post_date"]);
    }
    else
    {
      jQuery("#wp_lab_event_label").html("<b>Pas de categorie</b>");
    }
  });
}

function editGroup(groupId, acronym, groupName, chiefId, parent, group_type) {
  var data = {
               'action' : 'edit_group',
               'groupId' : groupId,
               'acronym' : acronym,
               'groupName' : groupName,
               'chiefId' : chiefId,
               'parent' : parent,
               'group_type' : group_type
  };
  jQuery.post(ajaxurl, data, function(response) {
    if (response.success) {
      toast_success("Group saved");
      resetGroupEdit();
    }
    else {
      toast_error("Failed to save group");
    }
  });
  //TODO: IHM pour que que l'utilisateur sache que ce qu'il a fait a modifié quelque chose
}

function resetGroupEdit()
{
  jQuery("#lab_searched_group_id").val("");
  jQuery("#wp_lab_group_acronym_edit").val("");
  jQuery("#wp_lab_group_name_edit").val("");
  jQuery("#lab_searched_chief_id").val("");
  jQuery("#wp_lab_group_chief_edit").val("");
  jQuery("#wp_lab_group_parent_edit").val("");
  jQuery("#wp_lab_group_type_edit").val("0");
  jQuery("#wp_lab_group_name").val("");
  jQuery("#lab_group_edit_substitutes").text("");
}
function clearFields(prefix,list) {
  for (i of list) {
    jQuery('#'+prefix+i).val('');
  }
}
function createKey(params) {
  data = {
    'action': 'keyring_create_key',
    'params': params 
  }
  jQuery.post(ajaxurl, data, function(response) {
    if (response.success) {
      toast_success("Clé créée avec succès !");
      jQuery("#lab_keyring_keySearch").keyup();
      return;
    }
    toast_error("Erreur de création de clé");
  });
}
function createLoan(params) {
  data = {
    'action': 'keyring_create_loan',
    'params': params
  }
  jQuery.post(ajaxurl, data, function(response) {
    if (response.success) {
      toast_success("Prêt créé avec succès.");
      jQuery("#lab_keyring_keySearch").keyup();
      oldLoans(params['key_id']);
      jQuery("#lab_keyring_loanform").hide();
      return;
    }
    toast_error("Erreur de création du prêt.");
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
      toast_success("Prêt modifié avec succès.");
      oldLoans(params['key_id']);
      return;
    }
    toast_error("Erreur de modification du prêt.");
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
      toast_success("Prêt terminé avec succès.");
      jQuery("#lab_keyring_loanform").hide();
      jQuery("#lab_keyring_keySearch").keyup();
      oldLoans(key_id);
      return;
    }
    toast_error("Erreur lors de la tentative de fin du prêt.");
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
                    <title>Reçu du prêt n'+loan.data["id"]+'</title>\
                    <script>document.onkeydown = function(e) {if (e.which== 27) {window.close();}}</script>\
                  </head>\
                  <body>\
                    <h2 id="loanContract_title">Reçu de prêt</h2>\
                    <div id="loanContract">\
                        <article>\
                          <h4>ID du prêt : <u>'+loan.data["id"]+'</u></h4>\
                          <h4>Référent : <u>'+referent+'</u></h4>\
                          <p>La clé/badge numéro : <b>'+key.data["number"]+' </b></p>\
                          <p>A été prêtée à <b>'+user+'</b></p>';
                          start = new Date(loan.data["start_date"]).toLocaleDateString("fr-FR",{ year: 'numeric', month: 'long', day: 'numeric' });
                          end = new Date(loan.data["end_date"]).toLocaleDateString("fr-FR",{ year: 'numeric', month: 'long', day: 'numeric' })
                          output+='<p>Le <b>'+start+'</b></p>';
                          output+=(loan.data["end_date"]===null) ? '<p>Et devra être rendue.</p>' : '<p>Et devra être rendue avant le <b>'+end+'</b></p>';
                          output+=(loan.data["commentary"]===null) ? '' : '<p>Commentaire : <i>'+loan.data["commentary"]+'</i></p><br/>';
                          output+='<p><u>Signature :</u></p>\
                        </article>';
                        output+= document.getElementById('lab_keyring_loanform_table').outerHTML;
                      output+='</div><button onclick="window.print();">Imprimer</button>\
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
function oldLoans(key_id) {
  data = {
    'action': 'keyring_find_old_loans',
    'key_id': key_id
  }
  jQuery.post(ajaxurl, data, function(response) {
    jQuery("#lab_keyring_loansList")[0].innerHTML=response.data;
  });
}
function defaultTodayDate(date) {
  return date.length == 0 ? new Date().toISOString().split("T")[0] : date;
}