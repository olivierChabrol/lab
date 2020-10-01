

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
				let td2 = $('<td />').attr("id", "travel_id"+item["travel_id"]).html(item["travel_id"]);
				tr.append(td2);
				let td3 = $('<td />').attr("id", "country_from"+item["travel_id"]).html(item["country_from"]);
				tr.append(td3);
				let td4 = $('<td />').attr("id", "travel_from"+item["travel_id"]).html(item["travel_from"]);
				tr.append(td4);
				let td5 = $('<td />').attr("id", "country_to"+item["travel_id"]).html(item["country_to"]);
				tr.append(td5);
				let td6 = $('<td />').attr("id", "travel_to"+item["travel_id"]).html(item["travel_to"]);
				tr.append(td6);
				let td7 = $('<td />').attr("id", "travel_date"+item["travel_id"]).html(item["travel_date"]);
				tr.append(td7);
				let td8 = $('<td />').attr("id", "means"+item["travel_id"]).html(item["means"]);
				tr.append(td8);
				let td9 = $('<td />').attr("id", "go_back"+item["travel_id"]).html(item["go_back"]);
				tr.append(td9);
				let td10 = $('<td />').attr("id", "status"+item["travel_id"]).html(item["status"]);
				tr.append(td10);
				let td11 = $('<td />');
				let button_motify = $('<input />').attr("type","button").attr("value","Modifier").attr("class","btn btn-info btn-sm").attr("travel_id", item["travel_id"]).attr("id", "modify" + item["travel_id"]);
				let button_del = $('<input />').attr("type","button").attr("value","Supprimer").attr("onclick","del(this)").attr("class","btn btn-danger btn-sm").attr("travel_id", item["travel_id"]).attr("id", "modify" + item["travel_id"]);
				button_motify.click(function() {
					modify(this);
				  });
				td11.append(button_motify);
				td11.append(button_del);
				tr.append(td11);
				$("#list_travel").append(tr);
			});
		}
	  });
}

function loadTableContent2(userId) {
	
    data = {
		"action": 'lab_labo1.5_transportation_get2'
	  }
	data["user_id"] = userId;

	  $.post(LAB.ajaxurl, data, function(response) {
		if (response.success) {
			deleteTableContent();
			$.each(response.data, function(i,item) {     //i?
				let tr = $('<tr />');
				let td1 = $('<td />');
				let checkbox = $('<input />').attr("type","checkbox").attr("name","checkbox");
				td1.append(checkbox);
				tr.append(td1);
				let td2 = $('<td />').attr("id", "travel_id"+item["travel_id"]).html(item["travel_id"]);
				tr.append(td2);
				let td3 = $('<td />').attr("id", "country_from"+item["travel_id"]).html(item["country_from"]);
				tr.append(td3);
				let td4 = $('<td />').attr("id", "travel_from"+item["travel_id"]).html(item["travel_from"]);
				tr.append(td4);
				let td5 = $('<td />').attr("id", "country_to"+item["travel_id"]).html(item["country_to"]);
				tr.append(td5);
				let td6 = $('<td />').attr("id", "travel_to"+item["travel_id"]).html(item["travel_to"]);
				tr.append(td6);
				let td7 = $('<td />').attr("id", "travel_date"+item["travel_id"]).html(item["travel_date"]);
				tr.append(td7);
				let td8 = $('<td />').attr("id", "means"+item["travel_id"]).html(item["means"]);
				tr.append(td8);
				let td9 = $('<td />').attr("id", "go_back"+item["travel_id"]).html(item["go_back"]);
				tr.append(td9);
				let td10 = $('<td />').attr("id", "status"+item["travel_id"]).html(item["status"]);
				tr.append(td10);
				let td11 = $('<td />');
				let button_motify = $('<input />').attr("type","button").attr("value","Modifier").attr("class","btn btn-info btn-sm").attr("travel_id", item["travel_id"]).attr("id", "modify" + item["travel_id"]);
				let button_del = $('<input />').attr("type","button").attr("value","Supprimer").attr("onclick","del(this)").attr("class","btn btn-danger btn-sm").attr("travel_id", item["travel_id"]).attr("id", "modify" + item["travel_id"]);
				button_motify.click(function() {
					modify(this);
				  });
				td11.append(button_motify);
				td11.append(button_del);
				tr.append(td11);
				$("#list_travel").append(tr);
			});
		}
	  });
}

function loadTableContentSort(orderBy) {
	
    data = {
		"action": 'lab_labo1.5_transportation_get_sort'
	  }
	data["orderBy"] = orderBy;

	  $.post(LAB.ajaxurl, data, function(response) {
		if (response.success) {
			deleteTableContent();
			$.each(response.data, function(i,item) {     //i?
				let tr = $('<tr />');
				let td1 = $('<td />');
				let checkbox = $('<input />').attr("type","checkbox").attr("name","checkbox");
				td1.append(checkbox);
				tr.append(td1);
				let td2 = $('<td />').attr("id", "travel_id"+item["travel_id"]).html(item["travel_id"]);
				tr.append(td2);
				let td3 = $('<td />').attr("id", "country_from"+item["travel_id"]).html(item["country_from"]);
				tr.append(td3);
				let td4 = $('<td />').attr("id", "travel_from"+item["travel_id"]).html(item["travel_from"]);
				tr.append(td4);
				let td5 = $('<td />').attr("id", "country_to"+item["travel_id"]).html(item["country_to"]);
				tr.append(td5);
				let td6 = $('<td />').attr("id", "travel_to"+item["travel_id"]).html(item["travel_to"]);
				tr.append(td6);
				let td7 = $('<td />').attr("id", "travel_date"+item["travel_id"]).html(item["travel_date"]);
				tr.append(td7);
				let td8 = $('<td />').attr("id", "means"+item["travel_id"]).html(item["means"]);
				tr.append(td8);
				let td9 = $('<td />').attr("id", "go_back"+item["travel_id"]).html(item["go_back"]);
				tr.append(td9);
				let td10 = $('<td />').attr("id", "status"+item["travel_id"]).html(item["status"]);
				tr.append(td10);
				let td11 = $('<td />');
				let button_motify = $('<input />').attr("type","button").attr("value","Modifier").attr("class","btn btn-info btn-sm").attr("travel_id", item["travel_id"]).attr("id", "modify" + item["travel_id"]);
				let button_del = $('<input />').attr("type","button").attr("value","Supprimer").attr("onclick","del(this)").attr("class","btn btn-danger btn-sm").attr("travel_id", item["travel_id"]).attr("id", "modify" + item["travel_id"]);
				button_motify.click(function() {
					modify(this);
				  });
				td11.append(button_motify);
				td11.append(button_del);
				tr.append(td11);
				$("#list_travel").append(tr);
			});
		}
	  });
}

function orderBy(obj){  
	var value = obj.options[obj.selectedIndex].value;
	loadTableContentSort(value);
}

function deleteTableContent(){
	$("#list_travel").html("");
}

function del(obj){
	data = {
		"action": 'lab_delete_transportation_admin'		  
	}
	data["travel_id"] = $(obj).attr("travel_id");

	jQuery.post(LAB.ajaxurl, data, function(response) {
		if (response.success) {
		console.log("OK succeful");}
	  });
	  loadTableContent();
}

/*function delAll(){
	var checkboxs = document.getElementsByName('checkbox');
		if(checkboxs[i].checked){
			del();
	}

}*/

function modify(obj){
	let id = $(obj).attr("travel_id");
	console.log("ID : " + id);
	$("#travel_id").val(id);
	$("#country_from").val($("#country_from" + id).html());
	$("#travel_from").val($("#travel_from" + id).html());
	$("#country_to").val($("#country_to" + id).html());
	$("#travel_to").val($("#travel_to" + id).html());
	$("#travel_date").val($("#travel_date" + id).html());
	$("#means").val($("#means" + id).html());
	$("#go_back").val($("#go_back" + id).html());
	$("#status").val($("#status" + id).html());
}

function update(){

		data = {
		"action": 'lab_update_transportation_admin'		  
		}
		data["travel_id"] = $("#travel_id").val();
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

	$('#filter_user_name').autocomplete({
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
		  //var userslug = ui.item.userslug;
		  //window.location.href = "/user/" + userslug;
		  var userid = ui.item.user_id;
		  console.log(userid);

		  event.preventDefault();
		  $("#filter_user_name").val(firstname + " " + lastname);
		  loadTableContent2(userid);
		}
	});

	$("#country_from").countrySelect({
		preferredCountries: ['fr', 'de', 'it', 'es', 'us'],
	});
	$("#country_to").countrySelect({
		preferredCountries: ['fr', 'de', 'it', 'es', 'us'],
	});
});

function checkAll(c){
	var status = c.checked;
	var oCheckbox = document.getElementsByName('checkbox');
	for(var i=0;i<oCheckbox.length;i++){
		oCheckbox[i].checked = status;
	}
}