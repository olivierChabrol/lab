
function addRow(tableID)
{
	var bodyObj=document.getElementById(tableID);

	var rowCount = bodyObj.rows.length;
	var cellCount = bodyObj.rows[0].cells.length;
	var newRow = bodyObj.insertRow(rowCount++);  
	for(var i=0;i<cellCount;i++)
	{
		 var cellHTML = bodyObj.rows[0].cells[i].innerHTML;
		 if(cellHTML.indexOf("none")>=0)
		 {
			 cellHTML = cellHTML.replace("none","");
		 }
		 newRow.insertCell(i).innerHTML=cellHTML;
	}
}