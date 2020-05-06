jQuery(function($){
    var style = document.createElement('style');
    style.innerHTML = `.fa-check {
                        color: green;
                      }`;
    document.head.appendChild(style);
  
    $(".icon-edit").click(function() {
        let editable = $(this).parents('tr').find('.edit');
        console.log("click edit");


        if( $(this).hasClass("fa-pen") ){
            console.log("click edit fa-pen");
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
            console.log("check");
            //let idPresence = $(this).parents('tr').find('#id-presence').val();
            let userId       = $(this).attr('userId');
            let idPresence = $(this).attr('editId');
            let date       = $(this).parents('tr').find('.date')     .val();
            let opening    = $(this).parents('tr').find('.first')    .val();
            let closing    = $(this).parents('tr').find('.last')     .val();
            let site       = $(this).parents('tr').find('select')    .val();
            console.log("id présence : " + idPresence + ", date : " + date + ", ouverture : "
             + opening + ", fermeture : " + closing + ", sur le site : " + site);

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
        }
        $(this).toggleClass("fa-pen fa-check");
    });

    $(".canDelete").mouseover(function () {
        el = $(this);
        $(".actions").css('display', 'block');
        if (el.attr("userId")) {
            dPres = el.find("div.dPres");
            dPres.attr("userId", el.attr("userId"));
            dPres.attr("presenceId", el.attr("presenceId"));
            dPres.click(function() {
                deletePresence(dPres.attr("presenceId"), dPres.attr("userId"));
            });
        }
    });
    $(".canDelete").mouseout(function () {
        $(".actions").css('display', 'none');
    });
    $("#lab_presence_button_save").click(function() {
        var data = {
        'action' : 'lab_presence_save',
        'userId' : $("#userId").val(),
        'dateOpen' : $("#date-open").val(),
        'hourOpen' : $("#hour-open").val(),
        'hourClose' : $("#hour-close").val(),
        'siteId': $("#siteId").val(),
        };

        $.post(LAB.ajaxurl, data, function(response) {
        if (response.success) {
            window.location.href = "/presence/";
        }
        });
    });
});