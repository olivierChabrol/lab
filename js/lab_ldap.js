jQuery(function($) {
    $("#lab_ldap_newUser_firstName").change(function () {
        $('#lab_ldap_newUser_uid').val( $("#lab_ldap_newUser_lastName").val().toLowerCase() + '.' + $(this).val().toLowerCase().charAt(0));
    });
    $("#lab_ldap_queryAmu").change(function() {
        $.post(LAB.ajaxurl,{'action':'lab_ldap_amu_lookup','query':$(this).val()},function (rep){
            if (rep.success) {
                $("#lab_ldap_newUser_email").val(rep.data['mail']);
                $("#lab_ldap_newUser_firstName").val(rep.data['first_name']);
                $("#lab_ldap_newUser_lastName").val(rep.data['last_name']);
                $("#lab_ldap_newUser_uid").val(rep.data['uid']);
                $("#lab_ldap_newUser_pass").val("--- Crypté ---")
                $("#lab_ldap_newUser_pass").attr('cryptedPass',rep.data['password']);
            }
        });
    });
    $("#lab_ldap_delete_search").autocomplete({
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
          $.post(LAB.ajaxurl,{action:'lab_admin_get_userLogin','user_id':ui.item.user_id},function (response) {
            if (response.success) {
                $("#lab_ldap_delete_res").html("ID "+ui.item.user_id + " - Login : " + response.data);
                $("#lab_ldap_delete_search").attr('user_id', ui.item.user_id);
                $("#lab_ldap_delete_button").prop("disabled",false);
            }
          });
        }
    });
    $("#lab_ldap_delete_button").prop("disabled",true);
    $("#lab_ldap_delete_button").click(function (){
        data = {
            'action': 'lab_ldap_delete_user',
            'user_id': $("#lab_ldap_delete_search").attr('user_id')
        };
        $.post(LAB.ajaxurl,data,function (response){
            if (response.success) {
                toast_success(__("Utilisateur supprimé avec succès",'lab'));
                return;
            }
            toast_error(__("Erreur lors de la suppression",'lab')+ "<br>"+response.data);
        });
    });


});
function lab_ldap_addUser() {
    jQuery(function($){ 
        data = {
            'action': 'lab_ldap_add_user',
            'first_name': $("#lab_ldap_newUser_firstName").val(),
            'last_name': $("#lab_ldap_newUser_lastName").val(),
            'email': $("#lab_ldap_newUser_email").val(),
            'organization': $("#lab_ldap_newUser_org").val(),
            'uid': $("#lab_ldap_newUser_uid").val(),
            'password': $("#lab_ldap_newUser_pass").val()=='--- Crypté ---' ? $("#lab_ldap_newUser_pass").attr('cryptedPass'): $("#lab_ldap_newUser_pass").val(),
            'addToWP': $("#lab_ldap_newUser_addToWP").prop('checked')
        };
        $.post(LAB.ajaxurl,data,function (response) {
            if(response.success) {
                toast_success(__("Utilisateur créé avec succès",'lab'));
            } else {
                toast_error(__("Erreur lors de l'ajout de l'utilisateur",'lab')+"<br>"+response.data);
                clearFields("lab_ldap_newUser_",['firstName','lastName','email','org','uid','pass','addToWP']);
            }
        });
    });
}