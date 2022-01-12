jQuery(function($){

    $("#lab_admin_seminar_create").click(function() {
        if ($("#lab_admin_seminar_name").val() != "")
        {
            let data = {
                'action':'lab_admin_seminar_save',
                'id':$("#lab_admin_seminar_id").val(),
                'user_id':$("#lab_admin_seminar_id").val(),
                'name':$("#lab_admin_seminar_name").val(),
                'location':$("#lab_admin_seminar_location").val(),
                'start':$("#lab_admin_seminar_start").val(),
                'end':$("#lab_admin_seminar_end").val(),
                'funder_int':$("#lab_admin_seminar_funder_int").val(),
                'funder_nat':$("#lab_admin_seminar_funder_nat").val(),
                'funder_reg':$("#lab_admin_seminar_funder_reg").val(),
                'funder_lab':$("#lab_admin_seminar_funder_lab").val(),
                'guests_number':$("#lab_admin_seminar_guests_number").val(),
                'seminar_details':$("#lab_admin_seminar_details").val()
            };
            console.log(data);
            callAjax(data, null, clearNewFields, null, null);
        }
        else {
            toast_error("Seminar name mandatory");
        }
    });

    function clearNewFields() {
        $("#lab_admin_seminar_id").val("");
        $("#lab_admin_seminar_name").val("");
        $("#lab_admin_seminar_start").val("");
        $("#lab_admin_seminar_end").val("");
        $("#lab_admin_seminar_location").val("");
        $("#lab_admin_seminar_funder_int").val("");
        $("#lab_admin_seminar_funder_nat").val("");
        $("#lab_admin_seminar_funder_reg").val("");
        $("#lab_admin_seminar_guests_number").val("");
        $("#lab_admin_seminar_details").val("")
      /*  $("#lab_admin_seminar_delete").prop('disabled', true); */
    }


})