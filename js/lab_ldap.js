jQuery(function($) {
    $("#lab_ldap_newUser_firstName").blur(function () {
        $('#lab_ldap_newUser_uid').val( $("#lab_ldap_newUser_lastName").val().toLowerCase() + '.' + $(this).val().toLowerCase().charAt(0));
    });
    $("#lab_ldap_queryAmu").change(function() {
        $.post(LAB.ajaxurl,{'action':'lab_ldap_amu_lookup','query':$(this).val()},function (rep){
            console.log('responded');
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
            'password': $("#lab_ldap_newUser_pass").val(),
            'addToWP': $("#lab_ldap_newUser_addToWP").prop('checked')
        };
        callAjax(data,__("Utilisateur créé avec succès",'lab'),clearLdapFields,__("Erreur lors de l'ajout de l'utilisateur",'lab'),null);
    });
}
function clearLdapFields() {
    clearFields("lab_ldap_newUser_",['firstName','lastName','email','org','uid','pass','addToWP']);
}