jQuery(function($){
        $(".date-row").click(function() {
            var $this  = $(this);
            var $input = $('<input>', {
                value: $this.text(),
                type: 'date time', // ??
                placeholder: $this.text(this.value),
                blur: function() {
                   $this.text(this.value);
                },
                keyup: function(e) {
                   if (e.which === 10) $input.blur();
                }
            }).appendTo( $this.empty() ).focus();
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