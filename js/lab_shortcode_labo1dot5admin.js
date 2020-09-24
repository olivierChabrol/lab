

$( document ).ready(function() {
    loadTableContent();
});

function loadTableContent() {
	
    data = {
		"action": 'lab_labo1.5_transportation_get'
	  }
  
	  $.post(LAB.ajaxurl, data, function(response) {
		if (response.success) {
			deleteTableContent();
			$.each(response.data, function(i, item) {
				let tr = $('<tr />');
				let td1 = $('<td />');
				let checkbox = $('<input />').attr("type","checkbox").attr("name","item");
				td1.append(checkbox);
				tr.append(td1);
				let td2 = $('<td />').html(item["travel_id"]);
				tr.append(td2);
				let td3 = $('<td />').html(item["country_from"]);
				tr.append(td3);
				let td4 = $('<td />').html(item["travel_from"]);
				tr.append(td4);
				let td5 = $('<td />').html(item["country_to"]);
				tr.append(td5);
				let td6 = $('<td />').html(item["travel_to"]);
				tr.append(td6);
				let td7 = $('<td />').html(item["travel_date"]);
				tr.append(td7);
				let td8 = $('<td />').html(item["means"]);
				tr.append(td8);
				let td9 = $('<td />').html(item["go_back"]);
				tr.append(td9);
				let td10 = $('<td />').html(item["status"]);
				tr.append(td10);
				let td11 = $('<td />');
				let button_motify = $('<input />').attr("type","button").attr("value","Modifier").attr("onclick","modify(this)").attr("class","btn btn-info btn-sm");
				let button_del = $('<input />').attr("type","button").attr("value","Supprimer").attr("onclick","del(this)").attr("class","btn btn-danger btn-sm");
				td11.append(button_motify);
				td11.append(button_del);
				tr.append(td11);
				$("#list_travel").append(tr);
			});
		}
	  });
}

function deleteTableContent()
{
	$("#list_travel").html("");
}


$(function () {

	$('#add').on('click', function(){
    
		data = {
		  "action": 'lab_save_transportation_admin'		  
		}
		data["country_from"] = $("#country_from").val();
		data["from"] = $("#travel_from").val();
		data["country_to"] = $("#country_to").val();
		data["to"] = $("travel_to").val();
		data["travel_date"] = $("#travel_date").val();
		data["lab_transport_to"] = $("#means").val();
		data["go_back"] = $("#go_back").val();
		data["status"] = $("#status").val();
	
		jQuery.post(LAB.ajaxurl, data, function(response) {
		  if (response.success) {
		  console.log("OK succeful");}
		});
	  });
	$("#country_from").countrySelect({
		preferredCountries: ['fr', 'de', 'it', 'es', 'us'],
	});
	$("#country_to").countrySelect({
		preferredCountries: ['fr', 'de', 'it', 'es', 'us'],
	});
});