jQuery(function($) {
    $("#lab_ldap_newUser_firstName").blur(function () {
        $('#lab_ldap_newUser_uid').val( $("#lab_ldap_newUser_lastName").val().toLowerCase() + '.' + $(this).val().toLowerCase().charAt(0));
    });
});
function lab_ldap_getJson() {
    test = jQuery.get("/wp-content/plugins/lab/ldap.json",function(rep){
        console.log(rep);
    });
}
function lab_ldap_addUser() {
    jQuery(function($){ 
        data = {
            'action': 'lab_ldap_add_user',
            'first_name': $("#lab_ldap_newUser_firstName").val(),
            'last_name': $("#lab_ldap_newUser_lastName").val(),
            'email': $("#lab_ldap_newUser_email").val(),
            'organization': $("#lab_ldap_newUser_org").val(),
            'uid': $("#lab_ldap_newUser_uid").val(),
            'password': $("#lab_ldap_newUser_password").val(),
        };
        callAjax(data,__("Utilisateur créé avec succès",'lab'),null,__("Erreur lors de l'ajout de l'utilisateur",'lab'),null);
    });
}