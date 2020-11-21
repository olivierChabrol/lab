var limitM=0;
var limitN=10;
var userId="";
var orderBy="";
var Year="";


$( document ).ready(function() {
	loadTableContent(limitM,limitN,userId,orderBy,Year);
	pagenation();
	missionYear();
});

function pagenation(){
var rowNum;
var totalPage;

	data = {
		"action": 'lab_labo1.5_transportation_getRowNum'
	}
	$.post(LAB.ajaxurl, data, function(response) {
		if (response.success) {
			rowNum = Object.values(response.data[0]);
			console.log("rowNum " +rowNum);
			totalPage = Math.ceil(rowNum/10); // 5 lignes par page
			console.log("numPage " + totalPage);
			let op = $('<option />').attr("value","").html("Page");
			$("#page").append(op);
			
			for(var i = 1;i<=totalPage; i++){
				let opI = $('<option />').attr("value",""+i).html(""+i);
				$("#page").append(opI);
			}
		}
	});
}

function changePage(obj){  
	var value = obj.options[obj.selectedIndex].value;
	if(value!=""){
		loadTableContent(10*(value-1),10*value,userId,orderBy,Year);}
	else
		{loadTableContent(limitM,limitN,userId,orderBy,Year);}
}

function missionYear(){
	var numMissionYear;

	data = {
		"action": 'lab_labo1.5_transportation_getMissionYear'
	}
	$.post(LAB.ajaxurl, data, function(response) {
		if (response.success) {
			numMissionYear = response.data.length
			let op = $('<option />').attr("value","").html("Année");
			$("#mission_year").append(op);
			console.log("numYear " + Object.values(response.data[0]));
			for(var i = 0; i < numMissionYear; i++){
				let opI = $('<option />').attr("value",""+ Object.values(response.data[i])).html(""+Object.values(response.data[i]));
				$("#mission_year").append(opI);
			}
		}
	});
}

function changeYear(obj){
	Year = obj.options[obj.selectedIndex].value;
	loadTableContent(limitM,limitN,userId,orderBy,Year);
}

function loadTableContent(limitM,limitN,userId,orderBy,Year) {

    data = {
		"action": 'lab_labo1.5_transportation_get_mission'
	}
	data["limitM"] = limitM;
	data["limitN"] = limitN;
	data["user_id"] = userId;
	data["orderBy"] = orderBy;
	data["missionYear"] = Year;

	    $.post(LAB.ajaxurl, data, function(response) {
		if (response.success) {
			deleteTableContent();
			$.each(response.data, function(i,item) {     //i?
				let tr = $('<tr />').attr("class","table-secondary");
				let td1 = $('<td />');
				let button_chargetrajet = $('<input />').attr("type","button").attr("value","+").attr("mission_id", item["mission_id"]).attr("id","chargetrajet" + item["mission_id"]);
				button_chargetrajet.click(function() {
					chargetrajet(this);
				});

				td1.append(button_chargetrajet);
				tr.append(td1);
				let td2 = $('<td />').attr("id", "mission_id"+item["mission_id"]).html(item["mission_id"]);
				tr.append(td2);
				let td3 = $('<td />').attr("id", "user_name"+item["mission_id"]).html(item["user_name"]);
				tr.append(td3);

				let td4 = $('<td />').attr("id", "mission_motif"+item["mission_id"]).html(item["mission_motif"]);
				let td4Input = $('<input />').attr("type","hidden").attr("value",item["mission_motif"]).attr("id","mission_InputMotif" + item["mission_id"]);
				td4.dblclick(function(){
					showTdInput(this,item["mission_motif"],"Motif",item["mission_id"]);
				});
				td4.append(td4Input);
				tr.append(td4);

				let td5 = $('<td />').attr("id", "mission_cost"+item["mission_id"]).html(item["mission_cost"]);
				let td5Input = $('<input />').attr("type","hidden").attr("value",item["mission_cost"]).attr("id","mission_InputCost" + item["mission_id"]);
				td5.dblclick(function(){
					showTdInput(this,item["mission_cost"],"Cost",item["mission_id"]);
				});
				td5.append(td5Input);
				tr.append(td5);

				let td13 = $('<td />').attr("id", "cost_estimate"+item["mission_id"]).html(item["cost_estimate"]);
				let td13Input = $('<input />').attr("type","hidden").attr("value",item["cost_estimate"]).attr("id","mission_InputCostEstimate" + item["mission_id"]);
				td13.dblclick(function(){
					showTdInput(this,item["cost_estimate"],"CostEstimate",item["mission_id"]);
				});
				td13.append(td13Input);
				tr.append(td13);

				let td6 = $('<td />').attr("id", "cost_cover"+item["mission_id"]).html(item["cost_cover"]);
				let td6Input = $('<input />').attr("type","hidden").attr("value",item["cost_cover"]).attr("id","mission_InputCover" + item["mission_id"]);
				td6.dblclick(function(){
					showTdInput(this,item["cost_cover"],"Cover",item["mission_id"]);
				});
				td6.append(td6Input);
				tr.append(td6);

				let td7 = $('<td />').attr("id", "mission_credit"+item["mission_id"]).html(item["mission_credit"]);
				let td7Input = $('<input />').attr("type","hidden").attr("value",item["mission_credit"]).attr("id","mission_InputCredit" + item["mission_id"]);
				td7.dblclick(function(){
					showTdInput(this,item["mission_credit"],"Credit",item["mission_id"]);
				});
				td7.append(td7Input);
				tr.append(td7);
				let td8 = $('<td />').attr("id", "mission_comment"+item["mission_id"]).html(item["mission_comment"]);
				let td8Input = $('<input />').attr("type","hidden").attr("value",item["mission_comment"]).attr("id","mission_InputComment" + item["mission_id"]);
				td8.dblclick(function(){
					showTdInput(this,item["mission_comment"],"Comment",item["mission_id"]);
				});
				td8.append(td8Input);
				tr.append(td8);
				let td9 = $('<td />').attr("id", "statut"+item["mission_id"]);
				let td9Select = $('<select />').attr("id","mission_select_statut" + item["mission_id"]).attr("class","form-control");
				let td9Option1 = $('<option />').attr("value","1").html("Validé");
				let td9Option2 = $('<option />').attr("value","0").html("Non validé");
				td9Select.append(td9Option1);
				td9Select.append(td9Option2);
				td9.append(td9Select);
				tr.append(td9);
				let td10 = $('<td />').attr("id", "date_submit"+item["mission_id"]).html(item["date_submit"]);
				tr.append(td10);
				let td11 = $('<td />');
				let td12 = $('<td />');
				let button_motify = $('<input />').attr("type","button").attr("value","➔").attr("class","btn btn-success btn-sm").attr("mission_id", item["mission_id"]).attr("id", "modify_mission" + item["mission_id"]);
				let button_del = $('<input />').attr("type","button").attr("value","✘").attr("class","btn btn-danger btn-sm").attr("mission_id", item["mission_id"]).attr("id", "del_mission" + item["mission_id"]);
				button_motify.click(function() {
					modify_mission(this);
				});
				button_del.click(function(){
					del_mission(this);
				})
				td11.append(button_motify);
				td12.append(button_del);
				tr.append(td11);
				tr.append(td12);
				$("#list_mission").append(tr);
				let tr2 = $('<tr />');
				let td21 = $('<td />').attr("colspan","11");
				let list_trajet = $('<tbody />').attr("mission_id", item["mission_id"]).attr("id","list_trajet" + item["mission_id"]);
				td21.append(list_trajet);
				tr2.append(td21);
				$("#list_mission").append(tr2);
				$("#mission_select_statut" + item["mission_id"]).val(item["statut"]);
			});
		}
	    });
}

function showTdInput(obj,valu,type,mission_id){
	obj.innerHTML = "<input type='text' value='"+valu+"' id='mission_Input"+type+""+mission_id+"' class='form-control'>";
}
function showTdInputTravel(obj,type,valu,name,travel_id){
	obj.innerHTML = "<input type='"+type+"' value='"+valu+"' id='travel_Input"+name+""+travel_id+"' class='form-control'>";
	if(name=="CF"||name=="CT"){
	$("#travel_Input" + name + travel_id).countrySelect({
		preferredCountries: ['fr', 'de', 'it', 'es', 'us'],
	});};
}
function chargetrajet(obj){
	let id = $(obj).attr("mission_id");
	console.log("mission id:" + id);
	if ( $("#chargetrajet" + id).val() == "+"){
		$("#chargetrajet" + id).val("-");
		let tr1 = $('<tr />');
		let th1 = $('<th />');
		tr1.append(th1);
		let th2 = $('<th />').html("N°Trajet");
		tr1.append(th2);
		let th3 = $('<th />').html("Pays de départ");
		tr1.append(th3);
		let th4 = $('<th />').html("Ville de départ");
		tr1.append(th4);
		let th5 = $('<th />').html("Pays d'arrivee");
		tr1.append(th5);
		let th6 = $('<th />').html("Ville d'arrivee");
		tr1.append(th6);
		let th7 = $('<th />').html("Date de départ");
		tr1.append(th7);
		let th8 = $('<th />').html("Date de retour");
		tr1.append(th8);
		let th9 = $('<th />').html("Mode de transport");
		tr1.append(th9);
		let th10 = $('<th />').html("Nb de personne");
		tr1.append(th10);
		let th11 = $('<th />').html("Aller/Retour");
		tr1.append(th11);
		let th12 = $('<th />').attr("colspan","2");
		let button_addTrajet = $('<input />').attr("type","button").attr("value","+").attr("class","btn btn-info btn-sm").attr("id","buttonAddTrajet" + id).attr("mission_id", id);
		button_addTrajet.click(function() {
			buttonAddTrajet(this);
		});
		th12.append(button_addTrajet);
		tr1.append(th12);
		$("#list_trajet" + id).append(tr1);
		data = {
			"action": 'lab_labo1.5_transportation_get_trajet'
		}
		data["mission_id"] = id;

		$.post(LAB.ajaxurl, data, function(response) {
			if (response.success) {
				$.each(response.data, function(i,item) {
					let tr2 = $('<tr />');
					let td1 = $('<td />');
					tr2.append(td1);
					let td2 = $('<td />').attr("id", "travel_id"+item["travel_id"]).html(item["travel_id"]);
					tr2.append(td2);
					let td3 = $('<td />').attr("id", "country_from"+item["country_from"]).html(item["country_from"]);
					let td3Input = $('<input />').attr("type","hidden").attr("value",item["country_from"]).attr("id","travel_InputCF" + item["travel_id"]);
					td3.dblclick(function(){
					showTdInputTravel(this,"text",item["country_from"],"CF",item["travel_id"]);
					});
					td3.append(td3Input);
					tr2.append(td3);
					let td4 = $('<td />').attr("id", "travel_from"+item["travel_from"]).html(item["travel_from"]);
					let td4Input = $('<input />').attr("type","hidden").attr("value",item["travel_from"]).attr("id","travel_InputTF" + item["travel_id"]);
					td4.dblclick(function(){
					showTdInputTravel(this,"text",item["travel_from"],"TF",item["travel_id"]);
					});
					td4.append(td4Input);
					tr2.append(td4);
					let td5 = $('<td />').attr("id", "country_to"+item["country_to"]).html(item["country_to"]);
					let td5Input = $('<input />').attr("type","hidden").attr("value",item["country_to"]).attr("id","travel_InputCT" + item["travel_id"]);
					td5.dblclick(function(){
					showTdInputTravel(this,"text",item["country_to"],"CT",item["travel_id"]);
					});
					td5.append(td5Input);
					tr2.append(td5);
					let td6 = $('<td />').attr("id", "travel_to"+item["travel_to"]).html(item["travel_to"]);
					let td6Input = $('<input />').attr("type","hidden").attr("value",item["travel_to"]).attr("id","travel_InputTT" + item["travel_id"]);
					td6.dblclick(function(){
					showTdInputTravel(this,"text",item["travel_to"],"TT",item["travel_id"]);
					});
					td6.append(td6Input);
					tr2.append(td6);
					let td7 = $('<td />').attr("id", "travel_date"+item["travel_date"]).html(item["travel_date"]);
					let td7Input = $('<input />').attr("type","hidden").attr("value",item["travel_date"]).attr("id","travel_InputDate" + item["travel_id"]);
					td7.dblclick(function(){
					showTdInputTravel(this,"date",item["travel_date"],"Date",item["travel_id"]);
					});
					td7.append(td7Input);
					tr2.append(td7);
					let td13 = $('<td />').attr("id", "travel_datereturn"+item["travel_datereturn"]).html(item["travel_datereturn"]);
					let td13Input = $('<input />').attr("type","hidden").attr("value",item["travel_datereturn"]).attr("id","travel_InputDatereturn" + item["travel_id"]);
					td13.dblclick(function(){
					showTdInputTravel(this,"date",item["travel_datereturn"],"Datereturn",item["travel_id"]);
					});
					td13.append(td13Input);
					tr2.append(td13);
					let td8 = $('<td />').attr("id", "means"+item["means"]).html(item["means"]);
					let td8Input = $('<input />').attr("type","hidden").attr("value",item["means"]).attr("id","travel_InputMeans" + item["travel_id"]);
					td8.dblclick(function(){
					showTdInputTravel(this,"text",item["means"],"Means",item["travel_id"]);
					});
					td8.append(td8Input);
					tr2.append(td8);
					let td9 = $('<td />').attr("id", "nb_person"+item["nb_person"]).html(item["nb_person"]);
					let td9Input = $('<input />').attr("type","hidden").attr("value",item["nb_person"]).attr("id","travel_InputNP" + item["travel_id"]);
					td9.dblclick(function(){
					showTdInputTravel(this,"text",item["nb_person"],"NP",item["travel_id"]);
					});
					td9.append(td9Input);
					tr2.append(td9);
					let td10 = $('<td />').attr("id", "go_back"+item["go_back"]).html(item["go_back"]);
					let td10Input = $('<input />').attr("type","hidden").attr("value",item["go_back"]).attr("id","travel_InputGB" + item["travel_id"]);
					td10.dblclick(function(){
					showTdInputTravel(this,"text",item["go_back"],"GB",item["travel_id"]);
					});
					td10.append(td10Input);
					tr2.append(td10);
					let td11 = $('<td />');
					let td12 = $('<td />');
					let button_motify = $('<input />').attr("type","button").attr("value","➔").attr("class","btn btn-success btn-sm").attr("mission_id",item["mission_id"]).attr("travel_id", item["travel_id"]).attr("id", "modify_trajet" + item["travel_id"]);
					let button_del = $('<input />').attr("type","button").attr("value","✘").attr("class","btn btn-danger btn-sm").attr("mission_id",item["mission_id"]).attr("travel_id", item["travel_id"]).attr("id", "del_trajet" + item["travel_id"]);
					button_motify.click(function() {
						modify_trajet(this);
					});
					button_del.click(function(){
						delTrajet(this);
					})
					td11.append(button_motify);
					td12.append(button_del);
					tr2.append(td11);
					tr2.append(td12);
					$("#list_trajet" + id).append(tr2);
			});
		}
		});
	}else{
		$("#chargetrajet"+id).val("+");
		$("#list_trajet" + id).html("");
	}

}

function buttonAddTrajet(obj){
	let id = $(obj).attr("mission_id");
	let tr = $('<tr />');
	let td1 = $('<td />');
	tr.append(td1);
	let td2 = $('<td />');
	tr.append(td2);
	let td3 = $('<td />');
	let td3input = $('<input />').attr("type","text").attr("id","addtrajet_country_from" + id).attr("class","form-control");
	td3.append(td3input);
	tr.append(td3);
	let td4 = $('<td />');
	let td4input = $('<input />').attr("type","text").attr("id","addtrajet_travel_from" + id).attr("class","form-control");
	td4.append(td4input);
	tr.append(td4);
	let td5 = $('<td />');
	let td5input = $('<input />').attr("type","text").attr("id","addtrajet_country_to" + id).attr("class","form-control");
	td5.append(td5input);
	tr.append(td5);
	let td6 = $('<td />');
	let td6input = $('<input />').attr("type","text").attr("id","addtrajet_travel_to" + id).attr("class","form-control");
	td6.append(td6input);
	tr.append(td6);
	let td7 = $('<td />');
	let td7input = $('<input />').attr("type","date").attr("id","addtrajet_travel_date" + id).attr("class","form-control");
	td7.append(td7input);
	tr.append(td7);
	let td12 = $('<td />');
	let td12input = $('<input />').attr("type","date").attr("id","addtrajet_travel_datereturn" + id).attr("class","form-control");
	td12.append(td12input);
	tr.append(td12);
	let td8 = $('<td />');
	let td8select = $('<select />').attr("type","text").attr("id","addtrajet_means" + id).attr("class","form-control");
	let td8option1 = $('<option />').attr("value","Avion").html("Avion"); 
	td8select.append(td8option1);
	let td8option2 = $('<option />').attr("value","Train").html("Train"); 
	td8select.append(td8option2);
	let td8option3 = $('<option />').attr("value","Voiture personnelle").html("Voiture personnelle");
	td8select.append(td8option3);
	let td8option4 = $('<option />').attr("value","Taxi").html("Taxi");
	td8select.append(td8option4);
	let td8option5 = $('<option />').attr("value","Bus").html("Bus");
	td8select.append(td8option5);
	let td8option6 = $('<option />').attr("value","Tramway").html("Tramway");
	td8select.append(td8option6);
	let td8option7 = $('<option />').attr("value","RER").html("RER");
	td8select.append(td8option7);
	let td8option8 = $('<option />').attr("value","Metro").html("Metro");
	td8select.append(td8option8);
	let td8option9 = $('<option />').attr("value","Ferry").html("Ferry");
	td8select.append(td8option9);
	td8.append(td8select);
	tr.append(td8);
	let td9 = $('<td />');
	let td9input = $('<input />').attr("type","text").attr("id","addtrajet_nb_person" + id).attr("class","form-control");
	td9.append(td9input);
	tr.append(td9);
	let td10 = $('<td />');
	let td10select = $('<select />').attr("type","text").attr("id","addtrajet_go_back" + id).attr("class","form-control");
	let td10option1 = $('<option />').attr("value","Oui").html("Oui"); 
	td10select.append(td10option1);
	let td10option2 = $('<option />').attr("value","Non").html("Non"); 
	td10select.append(td10option2);
	td10.append(td10select);
	tr.append(td10);
	let td11 = $('<th />').attr("colspan","2");
	let button_addTrajet = $('<input />').attr("type","button").attr("value","➔").attr("class","btn btn-success btn-sm").attr("id","buttonAddNewTrajet" + id).attr("mission_id", id);
	button_addTrajet.click(function() {
		AddNewTrajet(this);
	});
	td11.append(button_addTrajet);
	tr.append(td11);
	$("#list_trajet" + id).append(tr);

	$("#addtrajet_country_from" + id).countrySelect({
		preferredCountries: ['fr', 'de', 'it', 'es', 'us'],
	});
	$("#addtrajet_country_to" + id).countrySelect({
		preferredCountries: ['fr', 'de', 'it', 'es', 'us'],
	});
}
function orderByFunction(obj){  
	orderBy = obj.options[obj.selectedIndex].value;
	loadTableContent(limitM,limitN,userId,orderBy,Year);
}

function deleteTableContent(){
	$("#list_mission").html("");
}

function delTrajet(obj){
	var r=confirm("Supprimer ce trajet?")
	if (r==true){
	data = {
		"action": 'lab_admin_del_travel'		  
	}
	data["travel_id"] = $(obj).attr("travel_id");
	data["mission_id"] = $(obj).attr("mission_id");

	jQuery.post(LAB.ajaxurl, data, function(response) {
		if (response.success) {
		console.log("OK succeful");
		loadTableContent(limitM,limitN,userId,orderBy);}
	  });
	};
}

function del_mission(obj){
	var r=confirm("Supprimer cette mission?")
	if (r==true){
	data = {
		"action": 'lab_admin_del_mission'		  
	}
	data["mission_id"] = $(obj).attr("mission_id");

	jQuery.post(LAB.ajaxurl, data, function(response) {
		if (response.success) {
		console.log("OK succeful");
		loadTableContent(limitM,limitN,userId,orderBy,Year);}
	  });
	};
}

function modify_mission(obj){
	var r=confirm("Enregistrer les modifications?")
	if (r==true){
		let id = $(obj).attr("mission_id");
		data = {
			"action" : 'lab_admin_modify_mission'
		}
		data["mission_id"] = id;
		data["mission_motif"] = $("#mission_InputMotif" + id).val();
		data["mission_cost"] = $("#mission_InputCost"+ id).val();
		data["cost_estimate"] = $("#mission_InputCostEstimate"+ id).val();
		data["cost_cover"] = $("#mission_InputCover"+ id).val();
		data["mission_credit"] = $("#mission_InputCredit"+ id).val();
		data["mission_comment"] = $("#mission_InputComment"+ id).val();
		data["mission_statut"] = $("#mission_select_statut"+ id).val();

		jQuery.post(LAB.ajaxurl, data, function(response) {
			if (response.success) {
				alert("Votre modifications a été enregistré");
				console.log("OK succeful");
				loadTableContent(limitM,limitN,userId,orderBy,Year);}
			});
	};

}

function modify_trajet(obj){
	var r=confirm("Enregistrer les modifications?")
	if (r==true){
		let id = $(obj).attr("travel_id");
		let miid = $(obj).attr("mission_id");
		data = {
			"action": 'lab_admin_modify_travel'		  
			}
			data["travel_id"] = id;
			data["mission_id"] = miid;
			data["country_from"] = $("#travel_InputCF" + id).val();
			data["travel_from"] = $("#travel_InputTF" + id).val();
			data["country_to"] = $("#travel_InputCT" + id).val();
			data["travel_to"] = $("#travel_InputTT" + id).val();
			data["travel_date"] = $("#travel_InputDate" + id).val();
			data["travel_datereturn"] = $("#travel_InputDatereturn" + id).val();
			data["means"] = $("#travel_InputMeans" + id).val();
			data["go_back"] = $("#travel_InputGB" + id).val();
			data["nb_person"] = $("#travel_InputNP" + id).val();
		
			jQuery.post(LAB.ajaxurl, data, function(response) {
			if (response.success) {
				alert("Votre modifications a été enregistré");
				console.log("OK succeful");
				loadTableContent(limitM,limitN,userId,orderBy,Year);}
			});
	}
}
function AddNewTrajet(obj){
	var r=confirm("Enregistrer les modifications?")
	if (r==true){
		let id = $(obj).attr("mission_id");
		data = {
			"action": 'lab_admin_add_New_travel'		  
			}
			data["mission_id"] = id;
			data["country_from"] = $("#addtrajet_country_from" + id).val();
			data["travel_from"] = $("#addtrajet_travel_from" + id).val();
			data["country_to"] = $("#addtrajet_country_to" + id).val();
			data["travel_to"] = $("#addtrajet_travel_to" + id).val();
			data["travel_date"] = $("#addtrajet_travel_date" + id).val();
			data["travel_datereturn"] = $("#addtrajet_travel_datereturn" + id).val();
			data["means"] = $("#addtrajet_means" + id).val();
			data["go_back"] = $("#addtrajet_go_back" + id).val();
			data["nb_person"] = $("#addtrajet_nb_person" + id).val();
		
			jQuery.post(LAB.ajaxurl, data, function(response) {
			if (response.success) {
			console.log("OK succeful");
			loadTableContent(limitM,limitN,userId,orderBy,Year);}
			});
		}
}
$(function () {
	$('#update_mission').on('click',function(){
		var limitM=0;
		var limitN=5;
		var userId="";
		var orderBy="";
		loadTableContent(limitM,limitN,userId,orderBy,Year);
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
		  var firstname  = ui.item.firstname; 
		  var lastname = ui.item.lastname; 
		  userId = ui.item.user_id;
		  event.preventDefault();
		  $("#filter_user_name").val(firstname + " " + lastname);
		  loadTableContent(limitM,limitN,userId,orderBy,Year);
		}
	});

	$("#country_from").countrySelect({
		preferredCountries: ['fr', 'de', 'it', 'es', 'us'],
	});
	$("#country_to").countrySelect({
		preferredCountries: ['fr', 'de', 'it', 'es', 'us'],
	});
});
