
var varCount = 0;

function addNewTransporationLine(elm)
{
  varCount++;
  $node = '<div id="trajet'+varCount+'">'
  +'      <label for="trajet'+varCount+'">Trajet:</label>'  
  +'      <div>'
  +'          <label for="country_from'+varCount+'">Pays de départ:<span class="lab_form_required_star"> *</span></label>'
  +'          <input type="text" required id="country_from'+varCount+'" name="country_from'+varCount+'">'
  +'          <label for="from'+varCount+'">Ville de départ :<span class="lab_form_required_star"> *</span></label>'
  +'          <input type="text" required id="from'+varCount+'" name="from'+varCount+'">'
  +'      </div>'
  +'      <div>'
  +'          <label for="country_to'+varCount+'">Pays d\'arrivee:<span class="lab_form_required_star"> *</span>&nbsp&nbsp</label>'
  +'         <input type="text" required id="country_to'+varCount+'" name="country_to'+varCount+'">'
  +'          <label for="to'+varCount+'">Ville d\'arrivee :<span class="lab_form_required_star"> *</span>&nbsp&nbsp</label>'
  +'          <input type="text" required id="to'+varCount+'" name="to'+varCount+'">'
  +'      </div>'
  +'      <div>'
  +'          <label for="travel_date'+varCount+'"> Date de départ <span class="lab_form_required_star"> *</span>&nbsp</label>'
  +'          <input type="date" id="travel_date'+varCount+'" name="travel_date'+varCount+'">'
  +'          <label for="lab_transport_to'+varCount+'">Mode de transport<span class="lab_form_required_star"> *</span></label>'
  +'          <select id="lab_transport_to'+varCount+'" name="lab_transport_to'+varCount+'">'
  +'          <option value="">'+__("Choisissez une option","lab")+'</option>'
  +'          <option value="car">'+__("Voiture","lab")+'</option>'
  +'          <option value="train">'+__("Train","lab")+'</option>'
  +'          <option value="plane">'+__("Avion","lab")+'</option>'
  +'          <option value="bus">'+__("Car","lab")+'</option>'
  +'          <option value="none">'+__("Aucun","lab")+'</option>'
  +'          <option value="other">'+__("Autre","lab")+'</option>'
  +'          </select>'
  +'     </div>'
  +'     <div>'
  +'          <label for="go_back'+varCount+'">Un trajet aller/retour?<span class="lab_form_required_star"> *</span></label>'
  +'          <select id="go_back'+varCount+'" name="go_back'+varCount+'">'
  +'          <option value="">Aller/Retour?</option>'
  +'          <option value="gosimple">Aller simple</option>'
  +'          <option value="goback">Aller Retour</option>'
  +'          </select>'
  +'     </div>'
  +'     <button class="removeVar" index="'+varCount+'">Supprimer ce trajet</button><p></p>';
  +'</div>'       
  elm.parent().before($node);

  $("#country_from"+varCount).countrySelect({
    preferredCountries: ['fr', 'de', 'it', 'es', 'us'],
  });
  $("#country_to"+varCount).countrySelect({
    preferredCountries: ['fr', 'de', 'it', 'es', 'us'],
  });
}

function removeTransporationLine(elm)
{
  console.log("remove "+ elm.attr("index"));
  $(elm).parent().remove();
}

$(function () {   
  $('#addVar').on('click', function(){
    addNewTransporationLine( $(this));
  });
  
  $('#validate').on('click', function(){
    
    data = {
      "action": 'lab_save_transportation',
      "length" : varCount,
    }
    for(i = 0; i <= varCount;i++){
      data["travel_date"+i] = $("#travel_date"+i).val();
      data["from"+i] = $("#from"+i).val();
      data["to"+i] = $("#to"+i).val();
      data["lab_transport_to"+i] = $("#lab_transport_to"+i).val();
      data["country_from"+i] = $("#country_from"+i).val();
      data["country_to"+i] = $("#country_to"+i).val();
      data["go_back"+i] = $("#go_back"+i).val();
    }

    jQuery.post(LAB.ajaxurl, data, function(response) {
      if (response.success) {
      console.log("OK succeful");}
    });
  });
          
$(document).on('click', '.removeVar', function(){
  //console.log("clic on removeVar");
  //varCount--;
  removeTransporationLine($(this));
  });

$("#country_from0").countrySelect({
  preferredCountries: ['fr', 'de', 'it', 'es', 'us'],
});
$("#country_to0").countrySelect({
  preferredCountries: ['fr', 'de', 'it', 'es', 'us'],
});
});