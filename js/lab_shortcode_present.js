jQuery(function($){
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
        } else {
            //let idPresence = $(this).parents('tr').find('#id-presence').val();
            let idPresence = $(this).attr('editId');
            let date       = $(this).parents('tr').find('.date')     .val();
            let opening    = $(this).parents('tr').find('.first')    .val();
            let closing    = $(this).parents('tr').find('.last')     .val();
            let site       = $(this).parents('tr').find('select')    .val();
            console.log("id présence : " + idPresence + ", date : " + date + ", ouverture : "
             + opening + ", fermeture : " + closing + ", sur le site : " + site);
            /*$.ajax({
                type:'post',
                url: '../shortcode/lab-shortcode-present.php',
                data: {
                    id:         idPresence,
                    date:       date,
                    hour_start: opening,
                    hour_end:   closing,
                    site:       site
                }
            });*/

            $.post(LAB.ajaxurl,
                {
                    id:         idPresence,
                    date:       date,
                    hour_start: opening,
                    hour_end:   closing,
                    site:       site
                }
            );

            $.each(editable, function() {
                let content = "";

                if( $(this).hasClass("site-row") ){
                    content = $(this).find(":selected").text();
                } else {
                    content = $(this).find("input").val();
                }
                $(this).text(`${content}`);
            });
        }
        $(this).toggleClass("fa-pen fa-check")
    });
});