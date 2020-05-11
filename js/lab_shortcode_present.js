jQuery(function($){
    var style = document.createElement('style');
    style.innerHTML = `.fa-check {
                        color: green;
                      }`;
    document.head.appendChild(style);
  
    $(".icon-edit").click(function() {
        let editable = $(this).parents('tr').find('.edit');

        if( $(this).hasClass("fa-pen") ){
            let trId = $(this).attr("id");
            let id   = $(this).attr("editId")+"_"+$(this).attr("userId");
            let date = $("#date_"+id).text();
            let hOpen = $("#hOpen_"+id).text();
            let hEnd_ = $("#hEnd_"+id).text();
            let site = $("#site_"+id).attr("siteId");
            let comment =$(this).parents('tr').attr("title");
            console.log(site);
            editPresence($(this).attr("editId"), $(this).attr("userId"), date, hOpen, hEnd_, site, comment);
        } 
        else 
        {
            let userId     = $(this).attr('userId');
            let idPresence = $(this).attr('editId');
            let date       = $(this).parents('tr').find('.date') .val();
            let opening    = $(this).parents('tr').find('.first').val();
            let closing    = $(this).parents('tr').find('.last') .val();
            let site       = $(this).parents('tr').find('select').val();
            console.log("id présence : " + idPresence + ", date : " + date + ", ouverture : "
             + opening + ", fermeture : " + closing + ", sur le site : " + site);

            savePresence(idPresence, userId, date, opening, closing, site);
        }
        $(this).toggleClass("fa-pen fa-check");
    });

    $(".canDelete").mouseover(function () {
        el = $(this);
        let elId = el.attr("id");
        let actionId = "#action"+elId.substr(2,elId.length);
        let deleteId = "#delete"+elId.substr(2,elId.length);
        let editId   = "#edit"+elId.substr(2,elId.length);
        
        $(actionId).css('display', 'block');
        if (el.attr("userId")) {
            //let dPres = $(deleteId);
            $(deleteId).click(function() {
                deletePresence(el.attr("presenceId"), el.attr("userId"));
            });
            //let ePres = el.find("div.ePres");
            $(editId).click(function() {
                editPresence(el.attr("presenceId"), el.attr("userId"), el.attr("date"), el.attr("hourStart"), el.attr("hourEnd"), el.attr("siteId"), el.attr("title"));
            });
        }
    });
    $(".canDelete").mouseout(function () {
        $(".actions").css('display', 'none');
    });
    $("#lab_presence_button_save").click(function() {

        if ($("#comment").val()=="") {
            toast_error(__("Reason of your attendance is require", "lab"));
            $("#comment").focus();
            return;
        }
        /*
        var data = {
        'action' : 'lab_presence_save',
        'userId' : $("#userId").val(),
        'dateOpen' : $("#date-open").val(),
        'hourOpen' : $("#hour-open").val(),
        'hourClose' : $("#hour-close").val(),
        'comment' : $("#comment").val(),
        'siteId': $("#siteId").val(),
        };

        $.post(LAB.ajaxurl, data, function(response) {
        if (response.success) {
            window.location.reload(false); 
        }
        });
        //*/
        savePresence(null, $("#userId").val(), $("#date-open").val(), $("#hour-open").val(), $("#hour-close").val(), $("#siteId").val(), $("#comment").val());
    });
    $("#lab_presence_edit_dialog").modal("hide");
    $('#lab_presence_edit_dialog').on('shown.bs.modal', function () {
        $('#lab_presence_edit_date-open').trigger('focus');
      });
    $("#lab_presence_edit_save").click(function () {
        let userId = $("#lab_presence_edit_userId").val();
        let idPresence = $("#lab_presence_edit_presenceId").val();
        let date = $("#lab_presence_edit_date-open").val();
        let hourStart = $("#lab_presence_edit_hour-open").val();
        let hourEnd = $("#lab_presence_edit_hour-close").val();
        let comment = $("#lab_presence_edit_comment").val();
        let site = $("#lab_presence_edit_siteId").val();
        savePresence(idPresence, userId, date, hourStart, hourEnd, site, comment);
    });


    $("#lab_presence_delete_dialog").modal("hide");
    $("#lab_presence_delete_save").click(function () {
        console.log("test");
    });

    $("#date-open").click(function() {
        document.getElementById('hour-open').value = "08:00";
    });

    $('#hour-open').focusout(function () {
        $('#hour-close').val(getEndDate($(this).val()));
    });
    $('#lab_presence_edit_hour-open').focusout(function () {        
        $('#hour-close').val(getEndDate($(this).val()));
    });
    $('#lab_presence_ext_new_hour_open').focusout(function () {        
        $('#lab_presence_ext_new_hour_close').val(getEndDate($(this).val()));
    });

    $("#a_external_presency").click(function () {
        externalUserPresency();
    });

    $("#lab_presence_ext_new_save").click(function () {
        saveExternaluser();
    });
    //$( document ).ready(function() {
    //    $("#lab_presence_external_user_dialog").hide();
    //}
});

function getEndDate(startDate) {
    let timeElements = startDate.split(":");
    let theHour      = parseInt(timeElements[0]);
    let theMinute    = timeElements[1];
    let newHour      = theHour + 1;
    if (newHour > 23) {
        newHour = "00";
    } else if (newHour < 10) {
        newHour = "0"+newHour;
    }

    let val = newHour + ":" + theMinute;
    return val;
}

/******************************* ShortCode Presence ******************************/

function saveExternaluser() {
    let firstName = $("#lab_presence_ext_new_user_firstname").val();
    let lastName  = $("#lab_presence_ext_new_user_lastname").val();
    let email     = $("#lab_presence_ext_new_user_email").val();
    let date      = $("#lab_presence_ext_new_date_open").val();
    let hOpen     = $("#lab_presence_ext_new_hour_open").val();
    let hClose    = $("#lab_presence_ext_new_hour_close").val();
    let comment   = $("#lab_presence_ext_new_comment").val();
    let siteId    = $("#lab_presence_ext_new_siteId").val();
    var data = {
        'action' : 'lab_presence_save_ext',
        firstName :         firstName,
        lastName :     lastName,    
        email :       email,
        date : date,
        hourOpen : hOpen,
        hourClose :   hClose,
        siteId :       siteId,
        comment: comment
    };
    console.log(data);
    $.post(LAB.ajaxurl, data, function(response) {
        if (response.success) {
            //window.location.reload(false); 
            console.log("[saveExternaluser]" + response.data);
            window.location.reload(false); 
        }
    });
}

function savePresence(idPresence, userId, date, opening, closing, site, comment = "") {
    var data = {
        'action' : 'lab_presence_save',
        id :         idPresence,
        userId :     userId,    
        dateOpen :       date,
        hourOpen : opening,
        hourClose :   closing,
        siteId :       site,
        comment: comment
    };
    $.post(LAB.ajaxurl, data, function(response) {
        if (response.success) {
            $('input[type="date"]').val('');
            $('input[type="time"]').val('');
            $('#siteId').val('');
            $('#comment').val('');
            window.location.reload(false);
            //alert(response.data);
        }
        else {
            toast_error(response.data);
        }
    });
}

function externalUserPresency() {
    $("#lab_presence_external_user_dialog").modal('show');
}

function editPresence(presenceId, userId = null, date, hourStart, hourEnd,site,comment) {
    $("#lab_presence_edit_userId").val(userId);
    $("#lab_presence_edit_presenceId").val(presenceId);
    $("#lab_presence_edit_date-open").val(date);
    $("#lab_presence_edit_hour-open").val(hourStart);
    $("#lab_presence_edit_hour-close").val(hourEnd);
    $("#lab_presence_edit_comment").val(comment);
    $("#lab_presence_edit_siteId").val(site);
    $("#lab_presence_edit_dialog").modal('show');
}

function deletePresence(presenceId, userId = null) {
    $("#lab_presence_delete_dialog").modal('show');
    $(".delButton").click(function () {
        var data = null;
        if (userId != null) {
          data = {
            'action' : 'lab_presence_delete',
            'id' : presenceId,
            'userId' : userId
          }
        }
        else {
          data = {
            'action' : 'lab_presence_delete',
            'id' : presenceId
          }
        }
        jQuery.post(LAB.ajaxurl, data, function(response) {
          if (response.success) {
            //$("#invitationForm")[0].outerHTML=response.data;
            window.location.reload(false); 
          }
        });
    });
  }