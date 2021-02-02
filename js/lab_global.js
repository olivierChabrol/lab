
function createTdUser(userId, data) {
  return createTd(getUserNames(userId, data));
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

function createTd(str)
{
  return jQuery('<td />').html(str);
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
      if (callBackSuccess != null) {
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