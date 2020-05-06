jQuery(function($){
    $(".icon-edit").click(function() {
        let editable = $(this).parents('tr').find('.edit');
        
        if( $(this).hasClass("fa-pen") ){
            $.each(editable, function() {
                let content = $(this).text();

                if( $(this).hasClass("date-row") ){
                    $(this).html(`<input type='date' value='${content}'/>`);
                } else if( $(this).hasClass("hour-row") ){
                    $(this).html(`<input type='time' value='${content}'/>`);
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
            let date       = $(this).parents('tr').find('.date-row').val();
            let opening    = $(this).parents('tr').find('.open').val();
            let closing    = $(this).parents('tr').find('.end').val();
            let site       = $(this).parents('tr').find('select').val();
            console.log("id présence : " + idPresence + ", date : " + date + ", ouverture :"
             + opening + ", fermeture :" + closing + ", sur le site : " + site);
            $.ajax({
                type:'post',
                url: '../shortcode/lab-shortcode-present.php',
                data: {
                    id:         idPresence,
                    date:       date,
                    hour_start: opening,
                    hour_end:   closing,
                    site:       site
                }
            });

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
            
            $(this).toggleClass("ui-icon-pencil ui-icon-check")
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
            //$(this).children("div").remove();
            $(".actions").css('display', 'none');
        });
});