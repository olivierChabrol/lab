/* front end 21 04 2020 */
const { __, _x, _n, sprintf } = wp.i18n;

/*** DIRECTORY ***/

jQuery(function($){
   $('#lab_directory_user_name').autocomplete({
    minChars: 1,
    source: function(term, suggest){
      try { searchRequest.abort(); } catch(e){}
      searchRequest = $.post(LAB.ajaxurl, { action: 'search_username2',search: term, },
      function(res) {
        suggest(res.data);
      });
    },
    select: function( event, ui ) {
      var firstname  = ui.item.firstname; // first name
      var lastname = ui.item.lastname; // last name
      var user_id  = ui.item.user_id;
      window.location.href = "/user/" + firstname + "." + lastname;
      event.preventDefault();
      $("#lab_directory_user_name").val(firstname + " " + lastname);
    }
  });
  $(".email").each(function() {
    var replaced = $(this).text().replace(/@/g, '[TA]');
    $(this).text(replaced);
  });

  $(".directory_row").click(function() {
    window.location.href = "http://stage.fr/user/" + $(this).attr('userId');
  });

});