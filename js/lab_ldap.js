jQuery(function($) {
});
function lab_ldap_getJson() {
    test = jQuery.get("/wp-content/plugins/lab/ldap.json",function(rep){
        console.log(rep);
    });
}