jQuery(function($){
    var style = document.createElement('style');
    style.innerHTML = `.fa-check {
                        color: green;
                      }`;
    document.head.appendChild(style);
  
    $(".icon-edit").click(function() {
        let editable = $(this).parents('tr').find('.edit');

        if( $(this).hasClass("fa-pen") ){
            $.each(editable, function() {
                let content = $(this).text();

                if( $(this).hasClass("date-row") ){
                    $(this).html(`<input type='date' class='date' value='${content}'/>`);
                } else if( $(this).hasClass("open") ){
                    $(this).html(`<input type='time' class ='first' value='${content}'/>`);
                } else if( $(this).hasClass("end") ){
                    $(this).html(`<input type='time' class ='last' value='${content}'/>`);
                } else if( $(this).hasClass("site-row") ){
                    let htmlSelect = $("#siteId")[0].outerHTML;
                    $(this).html(htmlSelect);
                    let selected = $(this).find(`option:contains("${content}")`);
                    selected.prop('selected', true);
                }
            });
        } 
        else 
        {
            let userId       = $(this).attr('userId');
            let idPresence = $(this).attr('editId');
            let date       = $(this).parents('tr').find('.date')     .val();
            let opening    = $(this).parents('tr').find('.first')    .val();
            let closing    = $(this).parents('tr').find('.last')     .val();
            let site       = $(this).parents('tr').find('select')    .val();
            console.log("id présence : " + idPresence + ", date : " + date + ", ouverture : "
             + opening + ", fermeture : " + closing + ", sur le site : " + site);

            savePresence(idPresence, userId, date, opening, closing, site);
            /*
            var data = {
                'action' : 'lab_presence_save',
                id :         idPresence,
                userId :     userId,    
                dateOpen :       date,
                hourOpen : opening,
                hourClose :   closing,
                siteId :       site
            };
            $.post(LAB.ajaxurl, data, function(response) {
                if (response.success) {
                    window.location.href = "/presence/";
                }
            });
            //*/
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
                editPresence(el.attr("presenceId"), el.attr("userId"), el.attr("date"), el.attr("hourStart"), el.attr("hourStart"), el.attr("siteId"), el.attr("title"));
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
            window.location.href = "/presence/";
        }
        });
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
});

/******************************* ShortCode Presence ******************************/

function savePresence(idPresence, userId, date, opening, closing, site, comment = "") {
    var data = {
        'action' : 'lab_presence_save',
        id :         idPresence,
        userId :     userId,    
        dateOpen :       date,
        hourOpen : opening,
        hourClose :   closing,
        hourClose :   closing,
        siteId :       site,
        comment: comment
    };
    $.post(LAB.ajaxurl, data, function(response) {
        if (response.success) {
            window.location.href = "/presence/";
        }
    });
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
        window.location.href = "/presence/";
      }
    });
  }