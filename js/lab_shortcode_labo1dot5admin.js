
last_tr = $("#travel tr:last");
first_td = last_tr.find("td:first");
intindex = first_td.text();

function addList(){

	var ocountry_from = document.getElementById('country_from').value;
	var ofrom = document.getElementById('from').value;
	var ocountry_to = document.getElementById('country_to').value;
	var oto = document.getElementById('to').value;
	var otravel_date = document.getElementById('travel_date').value;
	var olab_transport_to = document.getElementById('lab_transport_to').value;
	var ogo_back = document.getElementById('go_back').value;
	var ostatus = document.getElementById('status').value;
	var oTr = document.createElement('tr');
	var oTd1 = document.createElement('td');
	var oInput = document.createElement('input');
	oTd1.appendChild(oInput);
	oInput.setAttribute('type','checkbox');
	oInput.setAttribute('name','item');
	var oTd2 = document.createElement('td');
	oTd2.innerHTML = intindex;
	var oTd3 = document.createElement('td');
	oTd3.innerHTML = ocountry_from;
	var oTd4 = document.createElement('td');
	oTd4.innerHTML = ofrom;
	var oTd5 = document.createElement('td');
	oTd5.innerHTML = ocountry_to;
	var oTd6 = document.createElement('td');
	oTd6.innerHTML = oto;
	var oTd7 = document.createElement('td');
	oTd7.innerHTML = otravel_date;
	var oTd8 = document.createElement('td');
	oTd8.innerHTML = olab_transport_to;
	var oTd9 = document.createElement('td');
	oTd9.innerHTML = ogo_back;
	var oTd10 = document.createElement('td');
	oTd10.innerHTML = ostatus;
	var oTd11 = document.createElement('td');
	var oInput2 = document.createElement('input');
	var oInput3 = document.createElement('input');
	oInput2.setAttribute('type','button');
	oInput2.setAttribute('value','Supprimer');
	oInput2.setAttribute('onclick','del(this)');
	oInput2.className = 'btn btn-danger';
	oInput3.setAttribute('type','button');
	oInput3.setAttribute('value','Modifier');
	oInput3.setAttribute('onclick','modify(this)');
	oInput3.className = 'btn btn-info';
	oTd11.appendChild(oInput2);
	oTd11.appendChild(oInput3);
	oTr.appendChild(oTd1);
	oTr.appendChild(oTd2);
	oTr.appendChild(oTd3);
	oTr.appendChild(oTd4);
	oTr.appendChild(oTd5);
	oTr.appendChild(oTd6);
	oTr.appendChild(oTd7);
	oTr.appendChild(oTd8);
	oTr.appendChild(oTd9);
	oTr.appendChild(oTd10);
	oTr.appendChild(oTd11);
	var olistTable = document.getElementById('list_travel');
	olistTable.appendChild(oTr);
}