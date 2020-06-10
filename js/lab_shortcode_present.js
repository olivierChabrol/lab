jQuery(function($){
    var style = document.createElement('style');
    style.innerHTML = `.fa-check {
                        color: green;
                      }`;
    document.head.appendChild(style);

    $("#workGroupFollow").change(function() {
        if ($(this).val() != "") {
            
            setDay($( "#workGroupFollow option:selected" ).attr("date"), $( "#workGroupFollow option:selected" ).attr("hour_start"), $( "#workGroupFollow option:selected" ).attr("hour_end"));
            $("#siteId").val($( "#workGroupFollow option:selected" ).attr("site"));
            $("#comment").val(__("I participate in the working group","lab") + " " + $( "#workGroupFollow option:selected" ).attr("name"));
            $("#divNewWorkingGroup").hide();
        }
        else
        {
            $("#divNewWorkingGroup").show();
            resetFields();
        }
    });
  
    $(".icon-edit").click(function() {
        let editable = $(this).parents('tr').find('.edit');

        if( $(this).hasClass("icon-edit") ){
            let trId    = $(this).attr("id");
            let id      = $(this).attr("editId")+"_"+$(this).attr("userId");
            let date    = $("#date_"+id).text();
            let hOpen   = $("#hOpen_"+id).text();
            let hEnd    = $("#hEnd_"+id).text();
            let site    = $("#site_"+id).attr("siteId");
            let comment =$(this).parents('tr').attr("title");

            editPresence($(this).attr("editId"), $(this).attr("userId"), date, hOpen, hEnd, site, comment);
        } 
        else 
        {
            let userId     = $(this).attr('userId');
            let idPresence = $(this).attr('editId');
            let date       = $(this).parents('tr').find('.date') .val();
            let opening    = $(this).parents('tr').find('.first').val();
            let closing    = $(this).parents('tr').find('.last') .val();
            let site       = $(this).parents('tr').find('select').val();

            if (checkPresenceInputs($(this).parents('tr').find('.date'))) {
                savePresence(idPresence, userId, date, opening, closing, site, "");
            }
        }
        $(this).toggleClass("icon-edit fa-check");
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
        if (checkPresenceInputs('date-open', 'hour-open', 'hour-close')) {
            savePresence(null, $("#userId").val(), $("#date-open").val(), $("#hour-open").val(), $("#hour-close").val(), $("#siteId").val(), $("#comment").val(), 0, $("#workGroupName").val(), $("#workGroupFollow").val() );
        }
    });

    $("#lab_presence_edit_dialog").modal("hide");
    $('#lab_presence_edit_dialog').on('shown.bs.modal', function () {
        $('#lab_presence_edit_date-open').trigger('focus');
      });
    $("#lab_presence_edit_save").click(function () {
        regex=/\"/g;
        let userId = $("#lab_presence_edit_userId").val();
        let idPresence = $("#lab_presence_edit_presenceId").val();
        let date = $("#lab_presence_edit_date-open").val();
        let hourStart = $("#lab_presence_edit_hour-open").val();
        let hourEnd = $("#lab_presence_edit_hour-close").val();
        let comment = $("#lab_presence_edit_comment").val().replace(regex,"”").replace(/\'/g,"’");
        let site = $("#lab_presence_edit_siteId").val();
        if (checkPresenceInputs('lab_presence_edit_date-open')) {
            savePresence(idPresence, userId, date, hourStart, hourEnd, site, comment, "");
        }
    });


    $("#lab_presence_delete_dialog").modal("hide");

    $("#lab_presence_ext_new_date_open").click(function() {
        $('#lab_presence_ext_new_hour_open').val("08:00");
        $('#lab_presence_ext_new_hour_close').val(getEndDate($('#lab_presence_ext_new_hour_open').val()));
    });

    $("#date-open").click(function() {
        $('#hour-open').val("08:00");
        $('#hour-close').val(getEndDate($('#hour-open').val()));
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
    $("#lab_presence_del_button").click(function () {
        let presenceId = $("#lab_presence_del_presenceId").val();
        let userId = $("#lab_presence_del_userId").val();
        var data = null;
        if (userId != null || userId == "") {
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
        $.post(LAB.ajaxurl, data, function(response) {
          if (response.success) {
            resetFields();
            //$("#invitationForm")[0].outerHTML=response.data;
            window.location.reload(false); 
          }
        });
    });
});

function resetFields()
{
    /*
    $("#date-open").val("YYYY-MM-DD");
    $("#hour-open").val("--:--");
    $("#hour-close").val("--:--");
    //*/

    $('input[type="date"]').val('');
    $('input[type="time"]').val('');
    $('#siteId').val('');
    $('#comment').val('');
    $("#workGroupFollow").val("");
    $("#workGroupName").val("");
}

function setDay(date, start, end)
{
    $("#date-open").val(date);
    $("#hour-open").val(start);
    $("#hour-close").val(end);
}

/**
 * convertie un string de format hh:: en minutes hh*60+mm
 * @param {string} str 
 * @return {int} minutes
 */
function stringHourToMinutes(str)
{
    let temps = str.split(':');
    return ((parseInt(temps[0]) * 60) + parseInt(temps[1]));
}

function checkPresenceInputs(dateElm, openElm, closeElm) {

    let valueDate       = $("#"+dateElm).val();
    let valueHourOpen   = $("#"+openElm).val();
    let valueHourClose  = $("#"+closeElm).val();

    // verif hour not vide et not --:--
    if (!valueHourOpen || !valueHourClose) {
        $("#"+openElm).addClass('is-invalid');
        $("#"+closeElm).addClass('is-invalid');
        $('#messErr_'+closeElm).text("Veuillez remplir correctement les heures");
        retour = false;
    }

    let debut = stringHourToMinutes(valueHourOpen);
    let fin   = stringHourToMinutes(valueHourClose);

    let checkHours = debut < fin;
    console.log(" DEB : " + debut);
    console.log(" FIN : " + fin);
    console.log("pour la date du " + valueDate + " commençant à " + valueHourOpen + " ("+debut+") et finissant à " + valueHourClose + " ("+fin+") checkHours : " + checkHours);
   
    

    let checkDate = Date.parse(valueDate);
    let retour = true;

    if (!checkDate)
    {
        $("#"+dateElm).addClass('is-invalid');
        $('#messErr_'+dateElm).text("La date n'est pas correcte");
        retour = false;
    }

    if (!checkHours)
    {
        $("#"+openElm).addClass('is-invalid');
        $("#"+closeElm).addClass('is-invalid');
        $('#messErr_'+closeElm).text("l'heure de fin est antérieure à l'heure de début");
        retour = false;
    }

    return retour;
}

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
    regex=/\"/g;
    let firstName = $("#lab_presence_ext_new_user_firstname").val();
    let lastName  = $("#lab_presence_ext_new_user_lastname").val();
    let email     = $("#lab_presence_ext_new_user_email").val();
    let date      = $("#lab_presence_ext_new_date_open").val();
    let hOpen     = $("#lab_presence_ext_new_hour_open").val();
    let hClose    = $("#lab_presence_ext_new_hour_close").val();
    let comment   = $("#lab_presence_ext_new_comment").val().replace(/\"/g,"”").replace(/\'/g,"’");
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
            resetFields();
            //window.location.reload(false); 
            console.log("[saveExternaluser]" + response.data);
            window.location.reload(false); 
        }
    });
}

function savePresence(idPresence, userId, date, opening, closing, site, comment = "", external = "", workgroup="",worgroupFollow="") {
    /*
    console.log("[savePresence] idPresence : '" + idPresence + "'");
    console.log("[savePresence] userId : '" + userId + "'");
    console.log("[savePresence] date : '" + date + "'");
    console.log("[savePresence] opening : '" + opening + "'");
    console.log("[savePresence] closing : '" + closing + "'");
    console.log("[savePresence] site : '" + site + "'");
    console.log("[savePresence] external : '" + external + "'");
    //*/
    var data = {
        'action' : 'lab_presence_save',
        id :         idPresence,
        userId :     userId,    
        dateOpen :       date,
        hourOpen : opening,
        hourClose :   closing,
        siteId :       site,
        external :       external,
        workgroup :       workgroup,
        worgroupFollow :       worgroupFollow,
        comment: comment.replace(/\"/g,"”").replace(/\'/g,"’")
    };
    $.post(LAB.ajaxurl, data, function(response) {
        if (response.success) {
            resetFields();
            window.location.reload(false);
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
    $("#lab_presence_del_presenceId").val(presenceId);
    $("#lab_presence_del_userId").val(userId);
    $("#lab_presence_delete_dialog").modal('show');
  }