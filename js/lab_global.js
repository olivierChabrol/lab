jQuery("#lab_event_submit_button").submit(function () {
    var isValid = jQuery("#lab_event_speaker_name").val();
    alert("isValid : " + isValid);
    if(!isValid) {
      e.preventDefault(); //prevent the default action
    }
});
jQuery(document).ready( function($){

  if ($("#lab_upload_image").length > 0) {
    $('#lab_upload_image').click(function() {
      if (this.window === undefined) {
				this.window = wp.media({
					title: 'Insert Image',
					library: {type: 'image'},
					multiple: false,
					button: {text: 'Insert Image'}
				});

				var self = this;
				this.window.on('select', function() {
					var response = self.window.state().get('selection').first().toJSON();

					$('.wp_attachment_id').val(response.id);
          console.log(response);
          console.log($('#lab_user_picture_display'));
          saveUserPicture(response.id, $('#lab_upload_image').attr("userId"));
					$('#lab_user_picture_display').attr('src', response.url);
          $('#lab_user_picture_display').show();
				});
			}

			this.window.open();
			return false;
    });

    $('#lab_delete_image').click(function() {
      deleteUserPicture($('#lab_upload_image').attr("userId"));
    });

    $('#lab_user_picture_btn_delete_image').on('click',function(e){
      e.preventDefault();
      $("#attachment_id").val("");
      $("#lab_user_picture_display").fadeTo(300,0,function(){
              $(this).animate({width:0},200,function(){
                      $(this).remove();
                  });
          });
    });
  }



  if ($("#lav_event_submit").length > 0) {
    $("#lav_event_submit").click(function (){
      let ok = true;
      if ($("#lab_event_speaker_name").val() == "")
      {
        $("#lab_event_speaker_name").focus();
        $("#lab_event_speaker_name_error").css("color", "red");
        $("#lab_event_speaker_name_error").html("Must be set");
        ok = false;
      }
      if ($("#lab_event_speaker_affiliation").val() == "")
      {
        $("#lab_event_speaker_affiliation").focus();
        $("#lab_event_speaker_affiliation_error").css("color", "red");
        $("#lab_event_speaker_affiliation_error").html("Must be set");
        ok = false;
      }
      if (ok) {
        $("#event-form").trigger('submit'); 
      }
      //e.preventDefault();
    });
  }
});

function deleteUserPicture(userId) {
  let data = {
    'action':'lab_save_user_picture',
    'imgId' :  "",
    'userId': userId
  };
  callAjax(data, "User image Save", deleteuserPictureSuccess, "Failed to save image", null);
}

function deleteuserPictureSuccess(data) {
  console.log("[deleteuserPictureSuccess]");
  console.log("[deleteuserPictureSuccess] src : " + jQuery('#lab_user_picture_display').attr("src"));
  jQuery('#lab_user_picture_display').attr("src", "https://www.gravatar.com/https://www.gravatar.com/avatar/ab8bfaf41e8f9f4c34cbf0f4c516e414?s=160&d=mp");
  console.log("[deleteuserPictureSuccess] src : " + jQuery('#lab_user_picture_display').attr("src"));
}

function saveUserPicture(imgId, userId) {
  let data = {
    'action':'lab_save_user_picture',
    'imgId' :  imgId,
    'userId': userId
  };
  callAjax(data, "User image Save", null, "Failed to save image", null);
}

function createTdUser(userId, data) {
  return createTd(getUserNames(userId, data));
}

function createTdGroup(groupId, data) {
  return createTd(getGroupNames(groupId, data));
}

function getUserNames(userId, data) {
  let f = "";
  if (userId != 0 && data.users[userId] != undefined)
  {
    let user = data.users[userId];
    f = user.first_name+" "+user.last_name;
  }
  return f;
}

function getGroupNames(groupId, data) {
  let f = "";
  if (groupId != 0 && data.groups[groupId] != undefined)
  {
    let group = data.groups[groupId];
    if (group.acronym) {
      f = group.acronym;
    }
    else {
      f = group.group_name;
    }
  }
  return f;
}

function createTd(str)
{
  return jQuery('<td />').html(str);
}

function createTdCurrency(valuemoney){
  valuemoney = new Intl.NumberFormat('fr-FR', {style : 'currency', currency : 'EUR'}).format(valuemoney);
  return jQuery('<td />').html(valuemoney);
}

function displayLoadingGif()
{
  //jQuery("#loadingAjaxGif").show();
  jQuery("#loadingAjaxGif").addClass('show');
}

function hideLoadingGif()
{
  //jQuery("#loadingAjaxGif").hide();
  jQuery("#loadingAjaxGif").removeClass('show');
}

function callAjax(data, successMessage, callBackSuccess = null, errorMessage, callBackError = null) {
  let candisplayLoadingGif = false;
  console.log("[callAjax]");
  if (jQuery("#loadingAjaxGif").length) {
    candisplayLoadingGif = true;
  }
  if (candisplayLoadingGif) 
  {
    displayLoadingGif();
  }
  //console.log("[callAjax] url : " + LAB.ajaxurl);
  jQuery.post(LAB.ajaxurl, data, function(response) {
    if (response.success) {
      if (candisplayLoadingGif) 
      {
        hideLoadingGif();
      }
      if (successMessage != null) {
        toast_success(successMessage);
      }
      console.log("[callAjax] callBack" + response.data);
      if (callBackSuccess != null) {
        //console.log("[callAjax] callBackSuccess" + callBackSuccess);
        callBackSuccess(response.data);
      }
    }
    else {
      if (candisplayLoadingGif) 
      {
        hideLoadingGif();
      }

      if (errorMessage != null) {
        toast_error(errorMessage);
      } 
      else {
        if (response.data) {
          toast_error(response.data);
        }
      }
      if (callBackError != null) {
        callBackError(response.data);
      }
    }
    });
}

// Notifications "toast" affichant une erreur ou un succès lors de la requête de création de groupe.
function toast_error(message) {
  jQuery.toast({
    text: message,
    heading: 'Error',
    icon: 'error',
    showHideTransition: 'slide', 
    hideAfter: 7000, 
    position: 'bottom-right', // bottom-left or bottom-right or bottom-center or top-left or top-right or top-center or mid-center or an object representing the left, right, top, bottom values
  });

  /*
  jQuery(function($){
    //$.toaster({ priority : 'success', title : 'Title', message : 'Your message here'});
    $.toast({
      text: message,
      heading: 'Error',
      icon: 'error',
      showHideTransition: 'slide', 
      hideAfter: 7000, 
      position: 'bottom-center', // bottom-left or bottom-right or bottom-center or top-left or top-right or top-center or mid-center or an object representing the left, right, top, bottom values
    });
    
  });
  //*/
}
function toast_success(message) {
  jQuery(function($){
    $.toast({
      text: message, // Text that is to be shown in the toast
      heading: 'Success', // Optional heading to be shown on the toast
      icon: 'success', // Type of toast icon
      showHideTransition: 'slide', // fade, slide or plain
      hideAfter: 3000, // false to make it sticky or number representing the miliseconds as time after which toast needs to be hidden
      position: 'bottom-right', // bottom-left or bottom-right or bottom-center or top-left or top-right or top-center or mid-center or an object representing the left, right, top, bottom values
      loaderBg: '#9EC600',  // Background color of the toast loader
    });
  });
}
function toast_warn(message) {
  jQuery(function($){
    $.toast({
      text: message, // Text that is to be shown in the toast
      heading: 'Warning', // Optional heading to be shown on the toast
      icon: 'warning', // Type of toast icon
      showHideTransition: 'slide', // fade, slide or plain
      hideAfter: 5000, // false to make it sticky or number representing the miliseconds as time after which toast needs to be hidden
      position: 'bottom-right', // bottom-left or bottom-right or bottom-center or top-left or top-right or top-center or mid-center or an object representing the left, right, top, bottom values
      loaderBg: '#D1A600',  // Background color of the toast loader
    });
  });
}

function html_delete_select_options(fieldId) {
  jQuery(fieldId+" option").each(function() {
    jQuery(this).remove();
  });
}
function clearFields(prefix,list) { //Empties the values of all the fields in the list
  for (i of list) {
    jQuery('#'+prefix+i).val('');
  }
}
//Fonction permettant de récupérer les arguments de l'URL sous forme de liste
function getUrlVars() {
  var vars = {};
  var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
      vars[key] = value;
  });
  return vars;
}