

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
			$.each(response.data, function(i,item) {     //i?
				let tr = $('<tr />');
				let td1 = $('<td />');
				let checkbox = $('<input />').attr("type","checkbox").attr("name","checkbox");
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

function deleteTableContent(){
	$("#list_travel").html("");
}

function del(obj){
	data = {
		"action": 'lab_delete_transportation_admin'		  
	}
	data["travel_id"] = $(obj).parents("tr").find("td").eq(1).text();

	jQuery.post(LAB.ajaxurl, data, function(response) {
		if (response.success) {
		console.log("OK succeful");}
	  });
	  loadTableContent();
}

function modify(obj){
	var ocountry_from=document.getElementById('country_from');
	var otravel_from=document.getElementById('travel_from');
	var ocountry_to=document.getElementById('country_to');
	var otravel_to=document.getElementById('travel_to');
	var otravel_date=document.getElementById('travel_date');
	var omeans=document.getElementById('means');
	var ogo_back=document.getElementById('go_back');
	var ostatus=document.getElementById('status');

	ocountry_from.value=$(obj).parents("tr").find("td").eq(2).text();
	otravel_from.value=$(obj).parents("tr").find("td").eq(3).text();
	ocountry_to.value=$(obj).parents("tr").find("td").eq(4).text();
	otravel_to.value=$(obj).parents("tr").find("td").eq(5).text();
	otravel_date.value=$(obj).parents("tr").find("td").eq(6).text();
	omeans.value=$(obj).parents("tr").find("td").eq(7).text();
	ogo_back.value=$(obj).parents("tr").find("td").eq(8).text();
	ostatus.value=$(obj).parents("tr").find("td").eq(9).text();
}


$(function () {

	$('#add').on('click', function(){
    
		data = {
		  "action": 'lab_save_transportation_admin'		  
		}
		data["country_from"] = $("#country_from").val();
		data["travel_from"] = $("#travel_from").val();
		data["country_to"] = $("#country_to").val();
		data["travel_to"] = $("#travel_to").val();
		data["travel_date"] = $("#travel_date").val();
		data["means"] = $("#means").val();
		data["go_back"] = $("#go_back").val();
		data["status"] = $("#status").val();
	
		jQuery.post(LAB.ajaxurl, data, function(response) {
		  if (response.success) {
		  console.log("OK succeful");}
		});
		loadTableContent();
	});

	/*('#ressst').on('click',function(){

		document.getElementById('country_from').reset();
		document.getElementById('travel_from').reset();
		document.getElementById('country_to').reset();
		document.getElementById('travel_to').reset();
		document.getElementById('travel_date').reset();
		document.getElementById('means').reset();
		document.getElementById('go_back').reset();
		document.getElementById('status').reset();
	});*/

	$("#country_from").countrySelect({
		preferredCountries: ['fr', 'de', 'it', 'es', 'us'],
	});
	$("#country_to").countrySelect({
		preferredCountries: ['fr', 'de', 'it', 'es', 'us'],
	});
});