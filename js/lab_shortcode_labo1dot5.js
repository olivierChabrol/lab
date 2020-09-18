/*jQuery(function($){
    console.log("test");
    //$("[id^=add").click(function() {
    $(document).on( "click", "[id^=add]", function() {
        let id = $(this).attr("id");
        console.log(id);
        let index = id.substring(3);
        console.log(index);
        addNewLine(index);
    });
});

function addNewLine(previousLineNumber) {
    let newLineNumber = parseInt(previousLineNumber) + 1;
    let innerHtml = '<p id="p'+newLineNumber+'"><input type="text" id="from0" placeholder="Lieu de depart"><input type="text" id="to0" placeholder="Lieu de d\'arrivee">';

    $("#p"+previousLineNumber).after(innerHtml);
    
}*/
var varCount = 0;

function addNewTransporationLine(elm)
{
  varCount++;
  $node = '<div id="trajet'+varCount+'">'
        + '<p><label for="trajet'+varCount+'">Trajet:</label>'
        + '<input type="date" id="travel_date'+varCount+'" name="travel_date'+varCount+'">'
        + '<input type="text" id="country_from'+varCount+'" name="country-from'+varCount+'" placeholder="Pays de depart">'
        + '<input type="text" name="from'+varCount+'" id="from'+varCount+'" placeholder="Lieu de depart">'
        + '<input type="text" id="country_to'+varCount+'" name="country_to'+varCount+'" placeholder="Pays de d\'arrivee">'
        + '<input type="text" name="to'+varCount+'" id="to'+varCount+'" placeholder="Lieu de d\'arrivee">'
        + '<select id="lab_transport_to'+varCount+'" name="lab_transport_to'+varCount+'">'
        + '<option value="">'+__( 'Choisissez une option', 'lab' )+'</option>'
        + '<option value="car">'+__( 'Voiture', 'lab' )+'</option>'
        + '<option value="train">'+__( 'Train', 'lab' )+'</option>'
        + '<option value="plane">'+__( 'Avion', 'lab' )+'</option>'
        + '<option value="bus">'+__( 'Car', 'lab' )+'</option>'
        + '<option value="none">'+__( 'Aucun', 'lab' )+'</option>'
        + '<option value="other">'+__( 'Autre', 'lab' )+'</option>'
        + '</select>'
        + '<select id="go_back'+varCount+'" name="go_back'+varCount+'">'
        + '<option value="">Aller/Retour?</option>'
        + '<option value="gosimple">Aller simple</option>'
        + '<option value="goback">Aller Retour</option>'
        + '</select>'
        + '<button class="removeVar" index="'+varCount+'">Supprimer</button></p></div>';
          
  elm.parent().before($node);
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
});