jQuery(function($){
        $(".date-row").click(function() {
            var $td  = $(this);
            var formatText = $td.text()
            //console.log($td.text());
            var $input = $('<input>', {
                value: $td.text(),
                type: 'date',
                focusout: function() {
                    $td.text($(this).val());
                    $(this).empty();
                }
            });
            $input.appendTo($td.empty()).focus();
        });

        $(".site-row").click(function() {
            var $this  = $(this);
            var $select = $('<select>', {
                value: $this.text(),
                blur: function() {
                    $this.text(this.value);
                 },
                 keyup: function(e) {
                    if (e.which === 10) $select.blur();
                 }
             }).appendTo( $this.empty() ).focus();
        });
});

//change tous les fields automatiquement 
//pouvoir totalement effacer toute la ligne et réecrire 
//avec un bouton edit qui devient un vérif