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
            
            //Envoyer a la bdd en recuperant les values

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
        //<i class="fas fa-pen"></i>
        $(this).toggleClass("fa-pen fa-check")
    });
});