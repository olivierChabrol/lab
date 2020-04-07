jQuery(document).ready( function($){
	var searchRequest;
	$('#wp_lab_event_title').autoComplete({
		minChars: 2,
		source: function(term, suggest){
			try { searchRequest.abort(); } catch(e){}
			searchRequest = $.post(global.ajax, { search: term, action: 'search_site' }, function(res) {
				suggest(res.data);
			});
		}
	});
});
