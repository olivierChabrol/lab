

jQuery(function($){
    $.getScript("lab_global.js");
    $("#lab_keyring_create_table_keys").click(function () {
        var data = {
        'action' : 'keyring_table_keys',
        };
        jQuery.post(LAB.ajaxurl, data, function(response) {
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
        jQuery.post(LAB.ajaxurl, data, function(response) {
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
        jQuery.post(LAB.ajaxurl, data, function(response) {
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
    $("#lab_settings_button_fill_hal_name_fields").click(function() {
        data = {
        'action': 'hal_fill_hal_name'
        };
        callAjax(data, "Fields succesfully filled", null, "Can't fill fields", null);
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
        jQuery.post(LAB.ajaxurl, data, function(response) {
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
        jQuery.post(LAB.ajaxurl, data, function(response) {
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
                      <title>'+__('Reçu de prêt','lab')+' n'+loan.data["id"]+'</title>\
                      <script>document.onkeydown = function(e) {if (e.which== 27) {window.close();}}</script>\
                    </head>\
                    <body>\
                      <h2 id="loanContract_title">'+__('Reçu de prêt','lab')+'</h2>\
                      <div id="loanContract">\
                          <article>\
                            <h4>'+__('ID du prêt','lab')+' : <u>'+loan.data["id"]+'</u></h4>\
                            <h4>'+__('Référent','lab')+' : <u>'+referent+'</u></h4>\
                            <p>'+__('La clé/badge numéro','lab')+' : <b>'+key.data["number"]+' </b></p>\
                            <p>'+__('A été prêtée à','lab')+' <b>'+user+'</b></p>';
                            start = new Date(loan.data["start_date"]).toLocaleDateString("fr-FR",{ year: 'numeric', month: 'long', day: 'numeric' });
                            end = new Date(loan.data["end_date"]).toLocaleDateString("fr-FR",{ year: 'numeric', month: 'long', day: 'numeric' })
                            output+='<p>Le <b>'+start+'</b></p>';
                            output+=(loan.data["end_date"]===null) ? '<p>Et devra être rendue.</p>' : '<p>'+__('Et devra être rendue avant le','lab')+' <b>'+end+'</b></p>';
                            output+=(loan.data["commentary"]===null) ? '' : '<p>'+__('Commentaire','lab')+' : <i>'+loan.data["commentary"]+'</i></p><br/>';
                            output+='<p><u>'+__('Signature','lab')+' :</u></p>\
                          </article>';
                          output+= document.getElementById('lab_keyring_loanform_table').outerHTML;
                        output+='</div><button onclick="window.print();">'+__('Imprimer','lab')+'</button>\
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