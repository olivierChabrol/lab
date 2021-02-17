var d_mission_id="";

$(function(){

    data = {
        "action":'lab_labo1.5_resp_initial'
    }
    data["mission_id"]=d_mission_id;

    $.post(LAB.ajaxurl, data, function(response) {
        if (response.success) {
        console.log("OK succeful");
        
        }
    });


    $("#d_mission_id").html("1");
});