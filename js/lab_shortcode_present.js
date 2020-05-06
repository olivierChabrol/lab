jQuery(function($){
        $(".icon-edit").click(function() {
            let editable = $(this).parents('tr').find('.edit');
            
            if( $(this).hasClass("ui-icon-pencil") ){
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
            
            $(this).toggleClass("ui-icon-pencil ui-icon-check")
        });

        $(".canDelete").mouseover(function () {
            console.log($(this).attr("userId") + " " + $(this).attr("presentId"));
            /*
            var a = $('<a>' , {
                text:"",
                style: "display:block;position:absolute;top:0;right:0;width:30px;height:30px;background:red",
            });
            a.appendTo($(this));
            //*/
            //$(this).append("<div style=\"position: relative; top : 100; left: 0;\"><span class=\"ui-icon ui-icon-trash\"></span></div>");
            $el = $(this);
		    	var newDiv = $("<div />", {
		    		"class": "innerWrapper",
		    		"css"  : {
		    			"height"  : "30px",
		    			"width"   : "30px",
                        "position": "absolute",
                        "top" : $el.height,
		    		}
                });
                newDiv.wrapInner("<span class=\"ui-icon ui-icon-trash\"></span>");
		    	$el.append(newDiv);
        });
        $(".canDelete").mouseout(function () {
            $(this).children("div").remove();
        });
});