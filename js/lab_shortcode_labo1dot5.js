
var varCount = 0;
function addNewTransporationLine(elm){
  varCount++;
  var num=varCount+1;
  $node = '<table class="table" id="trajet'+varCount+'">'
  +'<tr>'
  +'<th colspan="1">Trajet N°'+num+'</th>'
  +'<th colspan="3"><input type="button" id="removeVar" class="btn btn-danger" value="Supprimer ce trajet" onclick="removeTransporationLine(this)"></th>'
  +'</tr>'
  +'      <tr>'
  +'          <th>Pays de départ<span class="lab_form_required_star"> *</span></th>'
  +'          <td><input type="text" required id="country_from'+varCount+'" name="country_from'+varCount+'" class="form-control" style="text-transform:uppercase;"/></td>'
  +'          <th>Ville de départ<span class="lab_form_required_star"> *</span></th>'
  +'          <td><input type="text" required id="travel_from'+varCount+'" name="travel_from'+varCount+'" class="form-control" style="text-transform:uppercase;"/></td>'
  +'      </tr>'
  +'      <tr>'
  +'          <th>Pays d\'arrivee<span class="lab_form_required_star"> *</span></th>'
  +'          <td><input type="text" required id="country_to'+varCount+'" name="country_to'+varCount+'" class="form-control" style="text-transform:uppercase;"/></td>'
  +'          <th>Ville d\'arrivee<span class="lab_form_required_star"> *</span></th>'
  +'          <td><input type="text" required id="travel_to'+varCount+'" name="travel_to'+varCount+'" class="form-control" style="text-transform:uppercase;"/></td>'
  +'      </tr>'
  +'      <tr>'
  +'          <th>Date de départ<span class="lab_form_required_star"> *</span></th>'
  +'          <td><input type="date" required id="travel_date'+varCount+'" name="travel_date'+varCount+'" class="form-control"/></td>'
  +'          <th>Date de retour</th>'
  +'          <td><input type="date" id="travel_datereturn'+varCount+'" name="travel_datereturn'+varCount+'" class="form-control"/></td>'
  +'      </tr>'
  +'      <tr>'
  +'          <th>Mode de transport<span class="lab_form_required_star"> *</span></th>'
  +'          <td><select required id="means'+varCount+'" name="means'+varCount+'" class="form-control">'
  +'              <option value="">Choisissez une option</option>'
  +'              <option value="avion">Avion</option>'
  +'              <option value="train">Train</option>'
  +'              <option value="voiture personnelle">Voiture personnelle</option>'
  +'              <option value="taxi">Taxi</option>'
  +'              <option value="bus">Bus</option>'
  +'              <option value="tramway">Tramway</option>'
  +'              <option value="rer">RER</option>'
  +'              <option value="metro">Métro</option>'
  +'              <option value="ferry">Ferry</option>'
  +'              </select></td>'
  +'          <th>Nb de personne</th>'
  +'          <td><input type="text" id="nb_person'+varCount+'" name="nb_person'+varCount+'"class="form-control" placeholder="en cas voiture ou taxi"/></td>'
  +'      </tr>'
  +'<tr>'
  +'          <th>Un trajet aller/retour?<span class="lab_form_required_star"> *</span></th>'
  +'          <td><select required id="go_back'+varCount+'" name="go_back'+varCount+'"class="form-control">'
  +'              <option value="oui">Oui</option>'
  +'              <option value="non">Non</option>'
  +'              </select></td>'
  +'</tr>'
  +'  </table>'
  elm.before($node);

  $("#country_from"+varCount).countrySelect({
    preferredCountries: ['fr', 'de', 'it', 'es', 'us'],
  });
  $("#country_to"+varCount).countrySelect({
    preferredCountries: ['fr', 'de', 'it', 'es', 'us'],
  });
}

function removeTransporationLine(elm)
{
  var msg="supprimer ce trajet?";
  if (confirm(msg)==true){
    varCount--;
    $(elm).parent().parent().parent().remove();
  }
}

function validate(){
  var msg="Valider votre demande de mission?";
  if (confirm(msg)==true){
    var cost_cover=document.getElementsByName("cost_cover[]");
    var str="";
    for(i=0;i<cost_cover.length;i++){
      if (cost_cover[i].checked){
        str=str + cost_cover[i].value + "_";
      }
    }
    var user_name=$("#user_firstname").val() + " " + $("#user_lastname").val();
    data = {
      "action": 'lab_labo1.5_save_mission',
      "length" : varCount,
    }

    data["mission_motif"]=$("#mission_motif").val();
    data["mission_cost"]=$("#mission_cost").val();
    data["cost_cover"]=str;
    data["mission_credit"]=$("#mission_credit").val();
    data["mission_comment"]=$("#mission_comment").val();
    data["cost_estimate"]=$("#cost_estimate").val();
    data["user_name"]=user_name;
    for(i = 0; i <= varCount;i++){
      data["country_from"+i] = $("#country_from"+i).val();
      data["from"+i] = $("#travel_from"+i).val();
      data["country_to"+i] = $("#country_to"+i).val();
      data["to"+i] = $("#travel_to"+i).val();
      data["travel_date"+i] = $("#travel_date"+i).val();
      data["travel_datereturn"+i] = $("#travel_datereturn"+i).val();
      data["means"+i] = $("#means"+i).val();
      data["nb_person"+i] = $("#nb_person"+i).val();
      data["go_back"+i] = $("#go_back"+i).val();
    }

    jQuery.post(LAB.ajaxurl, data, function(response) {
      if (response.success) {
      console.log("OK succeful");
      alert("Votre demande a été enregistré");
      }
  });
}
}

$(function () {
  var user_firstname;
  var user_lastname;
  var user_email;
  var user_group;
  data = {
    "action":'lab_labo1.5_initial'
  }
  $.post(LAB.ajaxurl, data, function(response) {
		if (response.success) {
      console.log(response.data);
      user_firstname=Object.values(response.data[0]);
      $("#user_firstname").val(user_firstname);
      user_lastname=Object.values(response.data[1]);
      $("#user_lastname").val(user_lastname);
      user_email=Object.values(response.data[2]);
      $("#user_email").val(user_email);
      user_group=Object.values(response.data[3]);
      $("#user_group").val(user_group);
		}
  });
  

  $('#addVar').on('click', function(){
    addNewTransporationLine( $(this));
  });

  $("#country_from0").countrySelect({
    preferredCountries: ['fr', 'de', 'it', 'es', 'us'],
  });
  $("#country_to0").countrySelect({
    preferredCountries: ['fr', 'de', 'it', 'es', 'us'],
  });
});

function go_back_onchange(obj){  
	var value = obj.options[obj.selectedIndex].value;
	if(value == "Non"){
    addNewTransporationLine($('#addVar'));}
  else{}; 
}

function addNewANRContrat(elm){
  $node =  '<th id=mission_contract_th>Choisir le nom</th>'
          +'<td colspan="">'  
          + '<select id="mission_contract" class="form-control">'
          + '<option value="ANR1">ANR1</option>'
          + '<option value="ANR2">ANR2</option>'
          + '<option value="contrat1">Contrat1</option>'
          + '<option value="contrat2">Contrat2</option>'
          + '</select>'
          + '</td>'
      elm.parent().after($node);
}
function mission_credit_onchange(obj){  
  var value = obj.options[obj.selectedIndex].value;
	if(value == "ANR" ||value == "Contrat de recherche"){
    if(document.getElementById("mission_contract")==null){
    addNewANRContrat($('#mission_credit'));
    }
  }
  else{
    if(document.getElementById("mission_contract")!=null){
      var elm1 = document.getElementById("mission_contract");
      var elm2 = document.getElementById("mission_contract_th");
      $(elm1).parent().remove();
      $(elm2).remove();
      //element.parentNode.removeChild(element);
    }
  }
}